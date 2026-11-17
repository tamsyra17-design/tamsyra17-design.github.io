# 3D Signature Layer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the 3D signature layer — a scroll-driven home hero, an inspectable product viewer, and a fabric shader on rentals — without slowing the shop or breaking its fallbacks.

**Architecture:** One client-only `SceneRoot` owns every WebGL canvas, one session-scoped quality store drives all scenes, and every scene degrades to a designed poster. Scenes are procedurally generated (no external models at launch), lazy-loaded per route, and never mounted on commerce routes.

**Tech Stack:** Next.js 16.4.6 App Router, React 19.3.0, `three@0.187.0`, `@react-three/fiber@9.8.2`, `@react-three/drei@10.7.9`, Tailwind 4.3.3, next-intl 4.15, Vitest (installed by commerce Task 12), Playwright 1.65.

**Spec:** `docs/superpowers/specs/2026-11-16-3d-signature-layer-design.md`

## Global Constraints

- Runs **after** the 28 commerce tasks; every route it touches already exists.
- Versions are pinned: `three@0.187.0`, `@react-three/fiber@9.8.2`, `@react-three/drei@10.7.9`. Do not bump peers.
- Arabic is the default locale; `dir` is `rtl` for `ar`. All copy comes from `messages/{ar,en}.json` in both locales.
- 3D JS per route ≤ 300 KB gzipped including decoder glue. Product route payload ≤ 2.5 MB including GLB and poster. Home ≤ 400 KB beyond the current baseline.
- meshopt is the decoder; Draco only for a model that demands it. Decoders self-hosted from `web/public/3d/`.
- No `<canvas>` may exist on `/cart`, `/checkout`, `/account`, `/auth` — asserted by test.
- No autonomous animation under `prefers-reduced-motion`. User-activated inspection is allowed.
- Canvas is always `aria-hidden="true"`; adjacent real text carries the meaning.
- Canvas `pointer-events: none` wherever it sits behind interactive UI.
- Home hero: pinned scrub at ≥1024px, in-flow parallax below. Touch is the default implementation.
- Post-processing only in the product viewer, only on tier `high`. Never in the hero.
- Commit per task. Never commit `node_modules/`, `.next/`, Playwright artifacts, or GLB binaries above 1.5 MB.
- The tests name two products, `khanjar-classic` and `musar-classic`. They must exist in the demo seed from commerce Task 28; if the seed uses different slugs, change the tests to match the seed rather than inventing fixtures.

## Review Focus

1. **WebGL2 unavailable or blocked** (privacy extension, old Android, enterprise policy): the page must show the poster and remain fully usable — no empty box, no console error, no blocked scroll.
2. **`prefers-reduced-motion: reduce`**: zero autonomous frames, yet the "View in 3D" activation still works and the viewer still rotates by drag and keyboard.
3. **iOS Safari dynamic viewport**: the pinned desktop hero must never trap scroll; resizing the URL bar must not leave a stuck 1.2-viewport spacer on touch.
4. **Slow connection / JavaScript 3D chunk blocked**: commerce still works; the hero shows its poster, cards remain clickable.
5. **RTL with keyboard**: drag follows the pointer in both locales, arrow hints mirror, focus ring visible on the viewer.

## File Structure

- `web/src/lib/three/quality.ts` — tier type, `chooseTier()`, session persistence.
- `web/src/lib/three/webgl.ts` — `hasWebGL2()` probe.
- `web/src/lib/three/families.ts` — category → silhouette family map.
- `web/src/lib/three/scroll-progress.ts` — pure scroll mapping used by the hero.
- `web/src/components/three/SceneRoot.tsx` — the only canvas owner: mount, pause, tier, fallback.
- `web/src/components/three/HeroScene.tsx` — khanjar + dust + camera settle.
- `web/src/components/three/silhouettes/{Metal,Woven,Layered}.tsx` — three families.
- `web/src/components/three/ProductModelViewer.tsx` — drag/keyboard inspection, GLB or silhouette.
- `web/src/components/three/FabricPlane.tsx` — rental fabric shader.
- `web/src/components/home/{HeroBand,CategoryStrip,FeaturedPieces,StoryBand}.tsx` — the home composition the commerce plan never built.
- `web/src/components/product/TiltCard.tsx` — CSS-depth card (no WebGL).
- `web/scripts/budget.mjs` — route bundle/payload gate.
- `web/scripts/optimize-glb.mjs` — gltf-transform pipeline (meshopt + KTX2).

---

### Task 1: 3D foundation — SceneRoot, quality tiers, fallback ladder

**Files:**
- Create: `web/src/lib/three/quality.ts`, `web/src/lib/three/webgl.ts`
- Create: `web/src/components/three/SceneRoot.tsx`
- Test: `web/src/lib/three/quality.test.ts`, `web/e2e/three-fallback.spec.ts`
- Modify: `web/package.json` (deps), `web/playwright.config.ts` (no change expected — verify only)

**Interfaces:**
- Produces: `type QualityTier = 'low' | 'medium' | 'high'`
- Produces: `chooseTier(input: { deviceMemory?: number; hardwareConcurrency?: number; dpr: number }): QualityTier`
- Produces: `hasWebGL2(): boolean`
- Produces: `<SceneRoot poster={{ light: string; dark: string }} ariaLabel={string} children={(tier: QualityTier) => ReactNode} />` — renders the poster until the canvas is in view and WebGL2 exists.

- [ ] **Step 1: Install pinned dependencies**

Run: `cd web; npm install three@0.187.0 @react-three/fiber@9.8.2 @react-three/drei@10.7.9`
Expected: installs with no peer warnings against React 19.3.0.

- [ ] **Step 2: Write the failing tier test**

```ts
// web/src/lib/three/quality.test.ts
import { describe, expect, it } from 'vitest';
import { chooseTier } from './quality';

describe('chooseTier', () => {
  it('drops to low on a weak phone', () => {
    expect(chooseTier({ deviceMemory: 2, hardwareConcurrency: 2, dpr: 3 })).toBe('low');
  });
  it('gives a strong desktop high', () => {
    expect(chooseTier({ deviceMemory: 8, hardwareConcurrency: 8, dpr: 2 })).toBe('high');
  });
  it('defaults to medium when the browser hides deviceMemory', () => {
    expect(chooseTier({ hardwareConcurrency: 4, dpr: 2 })).toBe('medium');
  });
});
```

- [ ] **Step 3: Run it to verify it fails**

Run: `cd web; npx vitest run src/lib/three/quality.test.ts`
Expected: FAIL — cannot resolve `./quality`.

- [ ] **Step 4: Implement `chooseTier` and `hasWebGL2`**

In `quality.ts`: `low` when `deviceMemory < 4` or `hardwareConcurrency < 4`; `high` when `deviceMemory >= 8` and `hardwareConcurrency >= 8`; otherwise `medium`. Persist in `sessionStorage` under key `os:quality-tier`, exposing `readTier()` / `writeTier()`; `chooseTier` is pure and never touches storage.
In `webgl.ts`: create a throwaway canvas and return `!!canvas.getContext('webgl2')` inside try/catch, returning `false` on throw.

- [ ] **Step 5: Write the failing fallback test**

```ts
// web/e2e/three-fallback.spec.ts
import { test, expect } from '@playwright/test';

test('commerce routes never mount a canvas', async ({ page }) => {
  for (const path of ['/en/cart', '/en/account', '/en/auth/login']) {
    await page.goto(path);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('canvas')).toHaveCount(0);
  }
});
```

- [ ] **Step 6: Run it to verify it fails or passes for the wrong reason**

Run: `cd web; npx playwright test e2e/three-fallback.spec.ts`
Expected: PASS today (no canvas exists yet) — record that this is a guard, then add the assertion below so the test also proves `SceneRoot` never leaks onto those routes. Add to the same file:

```ts
test('SceneRoot is not imported by commerce routes', async ({ page }) => {
  const requests: string[] = [];
  page.on('request', (r) => requests.push(r.url()));
  await page.goto('/en/checkout');
  await page.waitForLoadState('networkidle');
  expect(requests.some((u) => /three|fiber/i.test(u))).toBe(false);
});

test('no WebGL2 means the poster and no canvas', async ({ page }) => {
  await page.addInitScript(() => {
    const original = HTMLCanvasElement.prototype.getContext;
    HTMLCanvasElement.prototype.getContext = function (type: string, ...rest: unknown[]) {
      if (type === 'webgl2' || type === 'webgl') return null;
      return (original as any).call(this, type, ...rest);
    } as any;
  });
  await page.goto('/en');
  await expect(page.getByTestId('home-hero').locator('img')).toBeVisible();
  await expect(page.locator('canvas')).toHaveCount(0);
});
```

- [ ] **Step 7: Implement `SceneRoot`**

Client component (`'use client'`). Renders `<picture>` with the light/dark posters and `alt=""`, `aria-hidden="true"`. On mount: `hasWebGL2()` false → keep poster, render nothing else. True → `IntersectionObserver` mounts the `<Canvas>` only when the wrapper is ≥10% visible; on exit, unmount and show the poster again. A `visibilitychange` listener stops `frameloop` when hidden. Under `prefers-reduced-motion: reduce`, never mount automatically; render the poster plus a `<button>` labelled from `messages` (`three.viewIn3D`) that mounts on activation. `dpr={[1, tier === 'high' ? 1.5 : 1]}`. Wrapper reserves its box with a CSS aspect ratio so no layout shift occurs.

- [ ] **Step 8: Add the translated strings**

Add to both catalogs under `three`: `viewIn3D`, `rotateHint`, `resetHint`, `loadModel` (`"View in 3D"` / `"عرض ثلاثي الأبعاد"`, `"Drag to rotate"` / `"اسحب للتدوير"`, `"Press R to reset"` / `"اضغط R لإعادة الضبط"`, `"Load 3D model"` / `"تحميل المجسم"`).

- [ ] **Step 9: Run tests to verify they pass**

Run: `cd web; npx vitest run src/lib/three/quality.test.ts; npx playwright test e2e/three-fallback.spec.ts`
Expected: PASS (3 unit, 2 e2e).

- [ ] **Step 10: Commit**

```bash
git add web
git commit -m "feat: add three.js foundation with quality tiers and poster fallback"
```

---

### Task 2: Home page composition (no 3D yet)

The commerce plan only ever ships a two-line placeholder home. The hero needs sections to scroll into, so this task builds them without any WebGL — a reviewer can reject this task while approving the scene work, and vice versa.

**Files:**
- Create: `web/src/components/home/HeroBand.tsx`, `CategoryStrip.tsx`, `FeaturedPieces.tsx`, `StoryBand.tsx`
- Modify: `web/src/app/[locale]/page.tsx`
- Test: `web/e2e/home.spec.ts`

**Interfaces:**
- Consumes: `apiGet<T>` and `formatOmr(baisa, locale)` from commerce Task 11.
- Produces: `HeroBand` renders `<section data-testid="home-hero">` sized `min-h-[70vh]` and accepts `children?: ReactNode` (the scene slot Task 3 fills).
- Produces: `CategoryStrip` renders `<section data-testid="category-strip">` from `GET /api/v1/categories`.
- Produces: `FeaturedPieces` renders `<section data-testid="featured-pieces">` reusing `ProductCard`.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/home.spec.ts
import { test, expect } from '@playwright/test';

test('home has hero, categories and featured pieces in both locales', async ({ page }) => {
  for (const locale of ['ar', 'en']) {
    await page.goto(`/${locale}`);
    await expect(page.getByTestId('home-hero')).toBeVisible();
    await expect(page.getByTestId('category-strip').locator('a').first()).toBeVisible();
    await expect(page.getByTestId('featured-pieces').getByTestId('product-card').first()).toBeVisible();
    await expect(page.locator('html')).toHaveAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr');
  }
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `cd web; npx playwright test e2e/home.spec.ts`
Expected: FAIL — `home-hero` not found.

- [ ] **Step 3: Implement the four sections**

`HeroBand` holds the locale title/subtitle from the existing `home` namespace plus the scene slot; its text sits above the canvas and stays readable without it. `CategoryStrip` is a horizontally scrollable list of category links using logical properties (`ms-`, `me-`, `gap-`) so it mirrors under RTL. `FeaturedPieces` shows the first eight products. `StoryBand` is a short static heritage paragraph with a link to the about page. All four fetch server-side with `cache: 'no-store'` in development, matching commerce Task 11.

- [ ] **Step 4: Run it to verify it passes**

Run: `cd web; npx playwright test e2e/home.spec.ts`
Expected: PASS (2 locale iterations in one test).

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add home page composition for the hero to inhabit"
```

---

### Task 3: Home hero scene — procedural khanjar, dust, scroll settle

**Files:**
- Create: `web/src/lib/three/scroll-progress.ts`, `web/src/components/three/HeroScene.tsx`
- Modify: `web/src/components/home/HeroBand.tsx`
- Test: `web/src/lib/three/scroll-progress.test.ts`, `web/e2e/hero.spec.ts`

**Interfaces:**
- Produces: `scrollProgress(scrollY: number, top: number, height: number): number` returning a clamped `0..1`.
- Produces: `HeroScene` mounted only inside `HeroBand`'s slot, via `SceneRoot`.
- Produces: `data-pinned="true"` on the hero wrapper at ≥1024px, `"false"` below it.

- [ ] **Step 1: Write the failing mapping test**

```ts
// web/src/lib/three/scroll-progress.test.ts
import { expect, it } from 'vitest';
import { scrollProgress } from './scroll-progress';

it('clamps before, inside and after the section', () => {
  expect(scrollProgress(0, 500, 800)).toBe(0);
  expect(scrollProgress(900, 500, 800)).toBeCloseTo(0.5);
  expect(scrollProgress(2000, 500, 800)).toBe(1);
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `cd web; npx vitest run src/lib/three/scroll-progress.test.ts`
Expected: FAIL — cannot resolve `./scroll-progress`.

- [ ] **Step 3: Implement `scrollProgress`**

`(scrollY - top) / height`, clamped to `0..1` with `Math.min`/`Math.max`. No side effects, no DOM access.

- [ ] **Step 4: Implement `HeroScene`**

Khanjar from a `LatheGeometry` profile defined inline (hilt, guard, curved blade approximated by a lathe profile plus a bent `TubeGeometry`), `MeshStandardMaterial` with `metalness: 1`, `roughness: 0.25`, colour `#c9a227`. Two hundred instanced dust points inside a 6×6×3 box, drifting on a slow sine. Lighting: one directional key, one hemisphere fill; no shadow maps. Rotation and camera z follow `scrollProgress`. Theme: one `uThemeMix` uniform (`0` light, `1` dark) drives fill intensity and the gold's emissive, read from `matchMedia('(prefers-color-scheme: dark)')`, so a dark-mode visitor does not get a scene lit for cream. Everything reads its tier from `SceneRoot`'s render prop: `low` halves the particle count and disables the camera settle.

- [ ] **Step 5: Write the failing hero test**

```ts
// web/e2e/hero.spec.ts
import { test, expect } from '@playwright/test';

test('hero pins on desktop and parallaxes on mobile', async ({ page }) => {
  await page.setViewportSize({ width: 1280, height: 800 });
  await page.goto('/en');
  await expect(page.getByTestId('home-hero')).toHaveAttribute('data-pinned', 'true');
  await expect(page.getByTestId('home-hero')).toBeVisible();

  await page.setViewportSize({ width: 390, height: 844 });
  await page.reload();
  await expect(page.getByTestId('home-hero')).toHaveAttribute('data-pinned', 'false');
});

test('reduced motion shows the poster and no automatic canvas', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto('/en');
  await expect(page.getByTestId('home-hero').locator('img')).toBeVisible();
  await expect(page.getByTestId('home-hero').locator('canvas')).toHaveCount(0);
});
```

- [ ] **Step 6: Run it to verify it fails**

Run: `cd web; npx playwright test e2e/hero.spec.ts`
Expected: FAIL — `data-pinned` missing.

- [ ] **Step 7: Wire the breakpoint split**

`HeroBand` sets `data-pinned` from a `matchMedia('(min-width: 1024px)')` listener, defaulting to `false` so the touch path is what renders first. Pinned mode: wrapper is `height: 220vh` with an inner `position: sticky; top: 0; height: 100dvh` (not `100vh`) and no scroll snapping. Canvas keeps `pointer-events: none`. Below 1024px: single `min-h-[70vh]` band, no sticky, no spacer.

- [ ] **Step 8: Run tests to verify they pass**

Run: `cd web; npx vitest run src/lib/three/scroll-progress.test.ts; npx playwright test e2e/hero.spec.ts`
Expected: PASS. Under software GL the canvas may not paint — the test asserts structure and attributes, not pixels.

- [ ] **Step 9: Commit**

```bash
git add web
git commit -m "feat: add scroll-driven khanjar hero with desktop pin and mobile parallax"
```

---

### Task 4: Budget gate

**Files:**
- Create: `web/scripts/budget.mjs`
- Modify: `web/package.json` (script `budget`)
- Test: run in CI-by-hand mode (documented), no unit test

**Interfaces:**
- Produces: `npm run budget` — exits non-zero when a route exceeds its ceiling, printing route, actual and limit.

- [ ] **Step 1: Write the script**

Read `.next/app-build-manifest.json` and the built route manifests; sum gzipped JS per route by reading each chunk file and gzipping it. Ceilings: any route containing 3D ≤ 300 KB; `/product/[slug]` payload ≤ 2.5 MB including `public/3d/**`; home added JS ≤ 400 KB. Routes without 3D are exempt from the 300 KB rule but still listed.

- [ ] **Step 2: Run it against a fresh build**

Run: `cd web; npm run build; npm run budget`
Expected: prints a per-route table and exits 0. If it exits non-zero, cut the feature that exceeds the ceiling — do not raise the limit.

- [ ] **Step 3: Commit**

```bash
git add web/scripts/budget.mjs web/package.json
git commit -m "chore: add route budget gate for 3D payloads"
```

---

### Task 5: CSS depth on catalog cards

Deliberately not WebGL: 2,000 cards at 60 fps is a CSS problem, not a three.js one.

**Files:**
- Create: `web/src/components/product/TiltCard.tsx`
- Modify: `web/src/components/product/ProductCard.tsx`
- Test: `web/src/lib/three/tilt.test.ts`, `web/e2e/tilt.spec.ts`

**Interfaces:**
- Produces: `tiltTransform(offsetX: number, offsetY: number, max = 6): string` returning a `rotateX(...) rotateY(...)` string clamped to `max` degrees.
- Produces: `TiltCard` with `data-tilt="on" | "off"`; `off` under reduced motion or touch-only pointers.

- [ ] **Step 1: Write the failing tilt test**

```ts
// web/src/lib/three/tilt.test.ts
import { expect, it } from 'vitest';
import { tiltTransform } from './tilt';

it('clamps to the maximum angle', () => {
  expect(tiltTransform(1, 1)).toBe('rotateX(-6deg) rotateY(6deg)');
  expect(tiltTransform(0, 0)).toBe('rotateX(0deg) rotateY(0deg)');
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `cd web; npx vitest run src/lib/three/tilt.test.ts`
Expected: FAIL — cannot resolve `./tilt`.

- [ ] **Step 3: Implement `tiltTransform` and `TiltCard`**

Pure math in `tilt.ts` (`offset` in `-1..1`, clamped, rounded to whole degrees so the test is exact). `TiltCard` is rendered **by `ProductCard` as its own root element**, so the element carrying `data-testid="product-card"` also carries `data-tilt` — not a wrapper around it. It adds `perspective-1000` on a parent div and applies the string to `style.transform` on `pointermove`, resetting on `pointerleave`; it disables itself when `(prefers-reduced-motion: reduce)` or `(hover: none)`.

- [ ] **Step 4: Write the failing e2e check**

```ts
// web/e2e/tilt.spec.ts
import { test, expect } from '@playwright/test';

test('cards tilt on desktop and stay flat under reduced motion', async ({ page }) => {
  await page.goto('/en/shop');
  const card = page.getByTestId('product-card').first();
  await expect(card).toHaveAttribute('data-tilt', 'on');
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.reload();
  await expect(page.getByTestId('product-card').first()).toHaveAttribute('data-tilt', 'off');
});
```

- [ ] **Step 5: Run both to verify they pass**

Run: `cd web; npx vitest run src/lib/three/tilt.test.ts; npx playwright test e2e/tilt.spec.ts`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add web
git commit -m "feat: add CSS depth tilt to catalog cards"
```

---

### Task 6: Product silhouette families

**Files:**
- Create: `web/src/lib/three/families.ts`, `web/src/components/three/silhouettes/Metal.tsx`, `Woven.tsx`, `Layered.tsx`
- Test: `web/src/lib/three/families.test.ts`

**Interfaces:**
- Produces: `type SilhouetteFamily = 'metal' | 'woven' | 'layered'`
- Produces: `familyFor(slug: string): SilhouetteFamily` — `khanjar|saif|jewellery|ring|dagger` → `metal`; `musar|shawl|asa|scarf|turban` → `woven`; everything else → `layered`.
- Produces: `<Metal />`, `<Woven />`, `<Layered />` — each a `<group>` of procedural meshes, no external assets.

- [ ] **Step 1: Write the failing mapping test**

```ts
// web/src/lib/three/families.test.ts
import { expect, it } from 'vitest';
import { familyFor } from './families';

it('maps heritage categories to three families', () => {
  expect(familyFor('khanjar')).toBe('metal');
  expect(familyFor('musar')).toBe('woven');
  expect(familyFor('bisht')).toBe('layered');
  expect(familyFor('unknown-thing')).toBe('layered');
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `cd web; npx vitest run src/lib/three/families.test.ts`
Expected: FAIL — cannot resolve `./families`.

- [ ] **Step 3: Implement the map and three silhouettes**

`familyFor` lowercases the slug and matches by substring against the two lists, defaulting to `layered`. `Metal` = one `LatheGeometry` hilt + one tapered `CylinderGeometry` blade. `Woven` = a `PlaneGeometry` with 12 segments and a vertex displacement sine. `Layered` = three stacked `CylinderGeometry` shells with decreasing radius. All share a single `MeshStandardMaterial` per family with colours from the token set (`--brand-gold`, `--brand-sand`, `--brand-green`) passed as hex constants in `families.ts`.

- [ ] **Step 4: Run it to verify it passes**

Run: `cd web; npx vitest run src/lib/three/families.test.ts`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add three procedural silhouette families"
```

---

### Task 7: Product model viewer

**Files:**
- Create: `web/src/components/three/ProductModelViewer.tsx`
- Modify: `web/src/app/[locale]/product/[slug]/page.tsx`
- Test: `web/e2e/viewer.spec.ts`

**Interfaces:**
- Consumes: `familyFor` (Task 6), `SceneRoot` (Task 1), `messages.three.*` (Task 1).
- Produces: `ProductModelViewer({ modelUrl, poster, family, name })` — renders `<figure data-testid="model-viewer" data-rotation={number}>`.
- Keyboard: `ArrowLeft`/`ArrowRight` rotate ±15°, `+`/`-` zoom, `R` resets; focusable `role="application"` with the translated hint as `aria-describedby`.

- [ ] **Step 1: Write the failing test**

```ts
// web/e2e/viewer.spec.ts
import { test, expect } from '@playwright/test';

test('viewer rotates by keyboard and resets', async ({ page }) => {
  await page.goto('/en/product/khanjar-classic');
  const viewer = page.getByTestId('model-viewer');
  await viewer.scrollIntoViewIfNeeded();
  await expect(viewer).toHaveAttribute('data-rotation', '0');
  await viewer.focus();
  await page.keyboard.press('ArrowRight');
  await expect(viewer).toHaveAttribute('data-rotation', '15');
  await page.keyboard.press('r');
  await expect(viewer).toHaveAttribute('data-rotation', '0');
});

test('viewer still works in Arabic and under reduced motion', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto('/ar/product/khanjar-classic');
  const viewer = page.getByTestId('model-viewer');
  await viewer.scrollIntoViewIfNeeded();
  await page.getByRole('button', { name: 'عرض ثلاثي الأبعاد' }).click();
  await viewer.focus();
  await page.keyboard.press('ArrowRight');
  await expect(viewer).toHaveAttribute('data-rotation', '15');
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `cd web; npx playwright test e2e/viewer.spec.ts`
Expected: FAIL — `model-viewer` not found.

- [ ] **Step 3: Implement the viewer**

`data-rotation` lives on the `<figure>`, written from the same state that drives `group.rotation.y` (degrees, normalised to `0..359`), so the test reads real state, not a mirror. Drag uses pointer events with `pointerId` capture; drag right increases rotation in both locales. When `modelUrl` is set, load via drei `useGLTF(url, true)` with the meshopt decoder served from `web/public/3d/meshopt_decoder.js` (self-hosted, referenced through `useGLTF.setDecoderPath('/3d/')`); when absent, render the family silhouette. Above 500 KB of model, render only the poster plus the `three.loadModel` button before any fetch. Camera: 45° fov, object fitted to a 1.2 unit sphere.

- [ ] **Step 4: Run it to verify it passes**

Run: `cd web; npx playwright test e2e/viewer.spec.ts`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add web
git commit -m "feat: add product model viewer with drag and keyboard inspection"
```

---

### Task 8: Admin model fields and API exposure

**Files:**
- Create: `api/database/migrations/*_add_model_3d_to_products.php`
- Create: `web/scripts/optimize-glb.mjs`
- Modify: `api/app/Models/Product.php`, `api/app/Filament/Resources/ProductResource.php`, `api/app/Http/Resources/ProductResource.php`, `web/package.json` (script `glb:optimize`)
- Test: `api/tests/Feature/ProductModel3dTest.php`

**Interfaces:**
- Produces: nullable `model_3d_url`, `model_3d_poster` on `products`; both exposed in the product API resource.
- Produces: Filament upload fields `FileUpload::make('model_3d_url')->disk('public')->acceptedFileTypes(['model/gltf-binary'])` and an image upload for the poster.

- [ ] **Step 1: Write the failing API test**

```php
<?php
// api/tests/Feature/ProductModel3dTest.php
it('exposes model fields on the product resource', function () {
    $p = \App\Models\Product::factory()->create([
        'model_3d_url' => 'https://example.test/model.glb',
        'model_3d_poster' => 'https://example.test/model.webp',
    ]);

    $this->getJson("/api/v1/products/{$p->slug}")
        ->assertOk()
        ->assertJsonPath('data.model_3d_url', 'https://example.test/model.glb')
        ->assertJsonPath('data.model_3d_poster', 'https://example.test/model.webp');
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `cd api; php artisan test --filter=ProductModel3dTest`
Expected: FAIL — unknown column / null path.

- [ ] **Step 3: Add the migration, fillable fields and Filament inputs**

Two nullable strings, `model_3d_url` (max 2048) and `model_3d_poster` (max 2048), added after `is_rentable`. Add both to `$fillable` and to the API resource. Filament: the upload stores to the `public` disk; the API resource returns the absolute URL via `Storage::disk('public')->url(...)` when the stored value is not already absolute.

- [ ] **Step 4: Run it to verify it passes**

Run: `cd api; php artisan migrate; php artisan test --filter=ProductModel3dTest`
Expected: PASS.

- [ ] **Step 5: Add the GLB pipeline script**

Run: `cd web; npm install -D @gltf-transform/cli`
Create `web/scripts/optimize-glb.mjs` invoking `gltf-transform optimize <in> <out> --compress meshopt --texture-compress webp`. Add `"glb:optimize": "node scripts/optimize-glb.mjs"` to `web/package.json`. The script refuses any output above 1.5 MB and prints the before/after size.

- [ ] **Step 6: Verify the script on a sample**

Run: `cd web; node scripts/optimize-glb.mjs --check`
Expected: prints the usage line and exits 0 when no input is given; with an input over 1.5 MB it exits non-zero.

- [ ] **Step 7: Commit**

```bash
git add api web/scripts/optimize-glb.mjs web/package.json
git commit -m "feat: add 3D model fields to products and admin"
```

---

### Task 9: Rental fabric shader

**Files:**
- Create: `web/src/components/three/FabricPlane.tsx`
- Modify: `web/src/app/[locale]/rental/[slug]/page.tsx`
- Test: `web/src/lib/three/fabric.test.ts`, `web/e2e/fabric.spec.ts`

**Interfaces:**
- Produces: `fabricUniforms(tier: QualityTier): { uSegments: number; uStrength: number }` — `high` → `{ 96, 0.08 }`, `medium` → `{ 48, 0.05 }`, `low` → `{ 24, 0 }`.
- Produces: `<FabricPlane textureUrl posterUrl />` rendering `<div data-testid="fabric-plane" data-segments={number}>`.

- [ ] **Step 1: Write the failing uniform test**

```ts
// web/src/lib/three/fabric.test.ts
import { expect, it } from 'vitest';
import { fabricUniforms } from './fabric';

it('reduces detail by tier and disables drape on low', () => {
  expect(fabricUniforms('high')).toEqual({ uSegments: 96, uStrength: 0.08 });
  expect(fabricUniforms('low')).toEqual({ uSegments: 24, uStrength: 0 });
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `cd web; npx vitest run src/lib/three/fabric.test.ts`
Expected: FAIL — cannot resolve `./fabric`.

- [ ] **Step 3: Implement `fabricUniforms` and `FabricPlane`**

Plane sized by the available width, segments from the uniform. Vertex shader: `sin(uv.x * 18.0) * uStrength` on `position.z` plus a slow `uTime` term. Fragment shader: the product photo as `map`, anisotropic sheen via `pow(1.0 - dot(normal, viewDir), 3.0)` tinted `#c9a227`, weave lines from `fract(uv * 180.0)`. Under reduced motion `uTime` is frozen at 0 and the loop stops.

- [ ] **Step 4: Write the failing e2e check**

```ts
// web/e2e/fabric.spec.ts
import { test, expect } from '@playwright/test';

test('rental detail shows the fabric surface with the tier segment count', async ({ page }) => {
  await page.goto('/en/rental/musar-classic');
  const plane = page.getByTestId('fabric-plane');
  await plane.scrollIntoViewIfNeeded();
  await expect(plane).toHaveAttribute('data-segments', /^(24|48|96)$/);
  const errors: string[] = [];
  page.on('pageerror', (e) => errors.push(e.message));
  await page.waitForTimeout(500);
  expect(errors).toEqual([]);
});
```

- [ ] **Step 5: Run both to verify they pass**

Run: `cd web; npx vitest run src/lib/three/fabric.test.ts; npx playwright test e2e/fabric.spec.ts`
Expected: PASS.

- [ ] **Step 6: Run the full gate**

Run: `cd web; npm run lint; npx tsc --noEmit; npm run build; npm run budget; npx playwright test`
Expected: all pass; budget table within ceilings; no test regressions in the commerce suites.

- [ ] **Step 7: Commit**

```bash
git add web
git commit -m "feat: add rental fabric shader with tiered detail"
```

---

## Sequencing note

Tasks 1–4 are Phase A, 5–8 are Phase B, 9 is Phase C. Every task ends with a working app; any phase can ship alone. Task 4 (budget) must land before Task 3 is accepted, so a scene that blows the ceiling is caught in the same phase.
