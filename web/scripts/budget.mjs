#!/usr/bin/env node
/**
 * Route budget gate for the 3D signature layer (spec §11).
 *
 * Reads the production build in `.next/` (run `npm run build` first) and, for
 * every app route, sums the gzipped size of the client JS that route loads:
 *
 *   - the shared app shell (`build-manifest.json` → `rootMainFiles`),
 *   - the route's entry chunks (`page_client-reference-manifest.js` →
 *     `entryJSFiles` for the page and every layout above it),
 *   - every chunk a client component on the route can pull in
 *     (`clientModules[*].chunks`), and
 *   - any chunk those files reference by path (lazy `import()` splits).
 *
 * Ceilings (a failure is a cut, never a raised limit):
 *   - 3D JS per route ≤ 300 KB gzipped: every chunk containing three.js /
 *     R3F / drei code, plus self-hosted decoder JS in `public/3d/` when the
 *     route loads 3D. Decoder wasm counts toward payload, not JS.
 *   - Product route payload ≤ 2.5 MB: route JS (gzipped) + everything in
 *     `public/3d/**` (GLBs, posters, decoders).
 *   - Home JS beyond the app shell ≤ 400 KB gzipped. The shell is what
 *     every route shares (root main files + the locale layout), so this
 *     counts everything the home page adds on top of the commerce baseline.
 *
 * Routes without 3D are exempt from the 3D rule but still listed.
 * Exits 1 when any ceiling is exceeded, printing route, actual and limit.
 */
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative, sep } from 'node:path';
import { fileURLToPath } from 'node:url';
import { createContext, runInContext } from 'node:vm';
import { gzipSync } from 'node:zlib';

const KB = 1024;
const LIMITS = {
  threeJs: 300 * KB,
  productPayload: 2.5 * KB * KB,
  homeAdded: 400 * KB,
};

const root = fileURLToPath(new URL('..', import.meta.url));
const nextDir = join(root, '.next');
const publicThreeDir = join(root, 'public', '3d');

/** Markers that only appear in chunks bundling three.js or its React layers. */
const THREE_MARKERS = [/WebGLRenderer/, /@react-three/, /react-three-fiber/, /MeshoptDecoder/, /DRACOLoader/];

function fail(message) {
  console.error(`budget: ${message}`);
  process.exit(2);
}

if (!existsSync(join(nextDir, 'BUILD_ID'))) {
  fail('no production build found in .next/ — run `npm run build` first.');
}

const buildManifest = JSON.parse(readFileSync(join(nextDir, 'build-manifest.json'), 'utf8'));
const appPaths = JSON.parse(readFileSync(join(nextDir, 'app-path-routes-manifest.json'), 'utf8'));

// ---------------------------------------------------------------- chunk info

const chunkCache = new Map();

/** @returns {{ gz: number, is3d: boolean, refs: string[] } | null} */
function chunk(file) {
  if (chunkCache.has(file)) return chunkCache.get(file);
  const abs = join(nextDir, file);
  let info = null;
  if (existsSync(abs)) {
    const buf = readFileSync(abs);
    const text = buf.toString('utf8');
    const refs = [...new Set(text.match(/static\/chunks\/[\w.-]+\.js/g) ?? [])].filter((r) => r !== file);
    info = {
      gz: gzipSync(buf, { level: 9 }).length,
      is3d: THREE_MARKERS.some((re) => re.test(text)),
      refs,
    };
  }
  chunkCache.set(file, info);
  return info;
}

/** Expands a set of chunk paths with every chunk they reference, transitively. */
function closure(files) {
  const seen = new Set();
  const queue = [...files];
  while (queue.length) {
    const file = queue.pop();
    if (seen.has(file)) continue;
    const info = chunk(file);
    if (!info) continue;
    seen.add(file);
    queue.push(...info.refs);
  }
  return seen;
}

function normalise(file) {
  // Manifests use `/_next/static/...` in clientModules and `static/...` elsewhere.
  return file.replace(/^\/?_next\//, '').replace(/^\//, '');
}

// ------------------------------------------------------ route manifests

function loadClientManifest(entry) {
  const file = join(nextDir, 'server', 'app', `${entry}_client-reference-manifest.js`);
  if (!existsSync(file)) return null;
  const sandbox = { self: {}, globalThis: {} };
  sandbox.globalThis = sandbox;
  createContext(sandbox);
  runInContext(readFileSync(file, 'utf8'), sandbox);
  const manifests = sandbox.__RSC_MANIFEST ?? sandbox.self.__RSC_MANIFEST ?? {};
  return manifests[entry] ?? Object.values(manifests)[0] ?? null;
}

const shell = new Set((buildManifest.rootMainFiles ?? []).map(normalise));

function routeFiles(entry) {
  const manifest = loadClientManifest(entry);
  const files = new Set(shell);
  const layoutFiles = new Set(shell);
  if (manifest) {
    for (const [segment, list] of Object.entries(manifest.entryJSFiles ?? {})) {
      for (const f of list) {
        files.add(normalise(f));
        if (/\/layout$/.test(segment)) layoutFiles.add(normalise(f));
      }
    }
    for (const mod of Object.values(manifest.clientModules ?? {})) {
      for (const f of mod.chunks ?? []) if (f.endsWith('.js')) files.add(normalise(f));
    }
  }
  return { files: closure(files), layoutFiles: closure(layoutFiles) };
}

function sum(files, predicate = () => true) {
  let total = 0;
  for (const f of files) {
    const info = chunk(f);
    if (info && predicate(info)) total += info.gz;
  }
  return total;
}

// ------------------------------------------------------ public/3d assets

function walk(dir) {
  if (!existsSync(dir)) return [];
  return readdirSync(dir).flatMap((name) => {
    const abs = join(dir, name);
    return statSync(abs).isDirectory() ? walk(abs) : [abs];
  });
}

const publicThree = walk(publicThreeDir).map((abs) => ({
  path: relative(root, abs).split(sep).join('/'),
  bytes: statSync(abs).size,
  gz: abs.endsWith('.js') ? gzipSync(readFileSync(abs), { level: 9 }).length : 0,
}));
const decoderJsGz = publicThree.reduce((n, a) => n + a.gz, 0);
const publicThreeBytes = publicThree.reduce((n, a) => n + a.bytes, 0);

// ------------------------------------------------------------- evaluate

const fmt = (n) => `${(n / KB).toFixed(1)} KB`;
const rows = [];
const violations = [];

const entries = Object.entries(appPaths)
  .filter(([entry]) => entry.endsWith('/page'))
  .sort(([, a], [, b]) => a.localeCompare(b));

for (const [entry, route] of entries) {
  const { files, layoutFiles } = routeFiles(entry);
  const total = sum(files);
  const three = sum(files, (i) => i.is3d);
  const has3d = three > 0;
  const threeWithDecoder = has3d ? three + decoderJsGz : 0;
  const checks = [];

  if (has3d) {
    checks.push({ rule: '3D JS', actual: threeWithDecoder, limit: LIMITS.threeJs });
  }
  if (/\/products?\/\[slug\]$/.test(route)) {
    checks.push({ rule: 'payload', actual: total + publicThreeBytes, limit: LIMITS.productPayload });
  }
  if (route === '/[locale]') {
    checks.push({ rule: 'home added JS', actual: total - sum(layoutFiles), limit: LIMITS.homeAdded });
  }

  const failed = checks.filter((c) => c.actual > c.limit);
  for (const c of failed) violations.push({ route, ...c });

  rows.push({
    route,
    'JS (gz)': fmt(total),
    '3D JS (gz)': has3d ? fmt(threeWithDecoder) : '—',
    checks: checks.length ? checks.map((c) => `${c.rule} ${fmt(c.actual)} / ${fmt(c.limit)}`).join('; ') : 'exempt',
    status: failed.length ? 'OVER' : 'ok',
  });
}

console.log(`Route budget — build ${readFileSync(join(nextDir, 'BUILD_ID'), 'utf8').trim()}`);
console.table(rows);
if (publicThree.length) {
  console.log(`public/3d: ${publicThree.length} file(s), ${fmt(publicThreeBytes)} raw, decoder JS ${fmt(decoderJsGz)} gz`);
}

if (violations.length) {
  console.error('\nBudget exceeded — cut the feature, do not raise the limit:');
  for (const v of violations) {
    console.error(`  ${v.route}  ${v.rule}: ${fmt(v.actual)} > limit ${fmt(v.limit)}`);
  }
  process.exit(1);
}
console.log('\nAll routes within budget.');
