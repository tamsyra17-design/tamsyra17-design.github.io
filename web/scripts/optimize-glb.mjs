#!/usr/bin/env node
/**
 * GLB pipeline for product models (3D spec §11, plan Task 8).
 *
 *   npm run glb:optimize -- <in.glb> [out.glb]   optimise with meshopt + WebP textures
 *   npm run glb:optimize -- --check [file.glb]   only check a file against the ceiling
 *
 * Runs `gltf-transform optimize <in> <out> --compress meshopt
 * --texture-compress webp`, prints the before/after size, and refuses any
 * result above 1.5 MB (the output is deleted and the exit code is 1). The
 * meshopt decoder the viewer needs is self-hosted from `public/3d/`.
 */
import { spawnSync } from 'node:child_process';
import { existsSync, readFileSync, rmSync, statSync } from 'node:fs';
import { findPackageJSON } from 'node:module';
import { basename, dirname, extname, join, resolve } from 'node:path';

const LIMIT = 1.5 * 1024 * 1024;
const USAGE = [
  'usage: npm run glb:optimize -- <in.glb> [out.glb]',
  '       npm run glb:optimize -- --check [file.glb]',
  `Outputs above ${mb(LIMIT)} are refused.`,
].join('\n');

function mb(bytes) {
  return `${(bytes / 1024 / 1024).toFixed(2)} MB`;
}

const args = process.argv.slice(2);
const check = args.includes('--check');
const [input, output] = args.filter((a) => a !== '--check');

if (args.includes('--help') || args.includes('-h') || !input) {
  console.log(USAGE);
  process.exit(0);
}

const inPath = resolve(input);
if (!existsSync(inPath)) {
  console.error(`glb:optimize: input not found: ${input}`);
  process.exit(2);
}
const before = statSync(inPath).size;

if (check) {
  if (before > LIMIT) {
    console.error(`glb:optimize: ${basename(inPath)} is ${mb(before)} — over the ${mb(LIMIT)} limit.`);
    process.exit(1);
  }
  console.log(`glb:optimize: ${basename(inPath)} is ${mb(before)} — within the ${mb(LIMIT)} limit.`);
  process.exit(0);
}

const outPath = resolve(
  output ?? join(dirname(inPath), `${basename(inPath, extname(inPath))}.opt.glb`),
);
if (outPath === inPath) {
  console.error('glb:optimize: output must differ from input.');
  process.exit(2);
}

// Run the CLI's own entry through this Node binary: no shell, no .cmd shim.
// The package does not export ./package.json, so locate it via module resolution.
const pkgPath = findPackageJSON('@gltf-transform/cli', import.meta.url);
const pkg = JSON.parse(readFileSync(pkgPath, 'utf8'));
const binRel = typeof pkg.bin === 'string' ? pkg.bin : pkg.bin['gltf-transform'];
const bin = join(dirname(pkgPath), binRel);

const result = spawnSync(
  process.execPath,
  [bin, 'optimize', inPath, outPath, '--compress', 'meshopt', '--texture-compress', 'webp'],
  { stdio: 'inherit' },
);
if (result.status !== 0 || !existsSync(outPath)) {
  console.error('glb:optimize: gltf-transform failed.');
  process.exit(result.status || 1);
}

const after = statSync(outPath).size;
const delta = before ? (((before - after) / before) * 100).toFixed(1) : '0.0';
console.log(`glb:optimize: ${mb(before)} → ${mb(after)} (${delta}% smaller) → ${outPath}`);

if (after > LIMIT) {
  rmSync(outPath);
  console.error(`glb:optimize: refused — ${mb(after)} is over the ${mb(LIMIT)} limit. Simplify the mesh or shrink textures.`);
  process.exit(1);
}
