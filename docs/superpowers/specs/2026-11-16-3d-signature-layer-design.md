# 3D Signature Layer — Design

**Date:** 2026-11-16
**Status:** draft for approval
**Project:** Omani heritage shop (`web/` — Next.js 16.4.6, React 19.3, Tailwind 4, next-intl)

## 1. Intent

The shop should feel like a heritage gallery, not a template. Three-dimensional
scene work is the differentiator: the craft being sold (khanjar engraving, musar
weave, the drape of a bisht) is exactly what flat product photos undersell.

The request was "use 3D for the design of all pages". Taken literally that is a
full WebGL design system, which conflicts with the other hard requirement — a
2,000-SKU, mobile-first shop that loads fast on Omani mobile data. The chosen
reading (user decision, 2026-11-16) is **B: signature moments** — every page has
a designed, deliberate character, and 3D appears wherever it earns its cost, at a
weight matched to the page's job. Pages that must not be slow (checkout, auth,
legal) are deliberately free of 3D; that restraint is part of the design, not an
omission.

## 2. Goals

- The homepage makes a visitor stop: one scroll-driven 3D moment, no library
  clutter, arriving fast enough to be seen rather than skipped.
- A product page can be turned and inspected — by drag, by keyboard — when a
  real model exists, and still looks intentional when it does not.
- The rental section communicates fabric: weave, sheen, drape.
- Every 3D element degrades to a designed static poster, never to a blank box.
- Nothing 3D ever delays a purchase.

## 3. Success criteria

- Homepage hero is visible within ~2.5 s of first paint on a mid-range Android
  over 4G, and does not block interaction with the header or the first card.
- Scroll stays at or above 50 fps on the "medium" quality tier (see §7).
- Product viewer is operable with mouse, touch and keyboard, and announces the
  item name to a screen reader.
- `prefers-reduced-motion: reduce` yields zero **autonomous** animation: no
  autoplay, no scroll-driven motion, no idle spin. The product viewer is still
  available to those users, but only after an explicit "View in 3D" activation
  (see §9).
- The checkout route ships no 3D code at all (verified by test, not by memory).

## 4. Non-goals

- 3D models for all 2,000 products. Only hero items, admin-uploaded, later.
- AR / virtual try-on / VR showrooms.
- Replacing photography. 3D is additive; photos remain the primary truth.
- A 3D admin dashboard or 3D charts.
- Physically accurate cloth simulation (a shader illusion, not a solver).

## 5. Anti-goals (failures even if everything "works")

- An LCP dominated by the WebGL bundle on any page.
- A canvas that keeps rendering while off-screen, hidden, or reduced-motion.
- Motion that ignores `prefers-reduced-motion`.
- A page that is only usable with a real GPU.
- Three different 3D implementations with three different quality behaviours.

## 6. Constraints

- **Stack:** Next.js 16.4.6 App Router, React 19.3.0, Tailwind 4.3.3 via the
  Turbopack loader, next-intl 4.15, Zustand 5, TanStack Query 5.
- **Versions (peer-verified):** `three@0.187.0`, `@react-three/fiber@9.8.2`
  (peer `react >=19 <19.4`), `@react-three/drei@10.7.9` (peer `react ^19`,
  `fiber ^9`).
- **Locales:** Arabic default, RTL; English LTR. All 3D-adjacent copy comes from
  `messages/{ar,en}.json`.
- **Dev machine:** 8 GB RAM. `next dev` plus multiple Playwright workers already
  OOM occasionally; `playwright.config.ts` currently passes `--disable-gpu`.
  Any design that assumes a GPU rejects itself.
- **Server rendering:** 3D never participates in SSR. The site must remain fully
  functional with JavaScript's 3D chunk blocked.
- **Money/data rules unchanged:** integer baisa, OMR formatting, guest checkout,
  Oman-only shipping.

## 7. Approaches considered

| | Approach | Why not |
|---|---|---|
| A | One ambient WebGL canvas behind every page (shader dunes/particles/arabesque) | Uniform look across 2,000 pages becomes wallpaper; every page pays the frame cost; no page is special |
| **B** | **Signature moments, weight matched to page job** | **Chosen** |
| C | Full 3D design system (3D card grids, 3D nav transitions, 3D checkout) | Jank on mid-range phones, largest bundle on the heaviest pages, accessibility and RTL cost multiplies; trades the shop's primary job for novelty |

B was chosen because it satisfies "3D on all pages" as a design language while
keeping the commerce path untouched. A hybrid (B plus a thin slice of A) was
offered and declined in favour of the cleaner B.

## 8. The design, page by page

| Route | Moment | Technique | Weight |
|---|---|---|---|
| `/` home | Scroll-driven hero: a khanjar turning above drifting sand, camera settling as the page scrolls into the first section. **Desktop (≥1024px): the hero pins for ~1.2 viewports and scrubs.** **Mobile/tablet: an in-flow parallax band (~70vh)** — the object turns only while visible, then the page continues normally. No scroll-jacking on touch. | R3F scene, procedural `LatheGeometry` + PBR gold, instanced dust points, scroll progress → rotation/position | Lazy chunk, no post-processing, `pointer-events: none` on the canvas |
| `/shop` listing | Cards gain depth on pointer: subtle tilt and shadow shift | **CSS 3D transforms only** (`perspective`, `rotateX/Y`) — no WebGL, mirrors under RTL automatically | Zero JS cost |
| `/product/[slug]` | Inspectable object: drag or arrow-key to rotate; zoom; reset | `ProductModelViewer` — GLB via drei `useGLTF` (Draco/meshopt/KTX2) when `model_3d_url` exists; otherwise a procedural silhouette for the product's category | Lazy chunk, renders only when scrolled into view |
| `/rental/[slug]` | Fabric plane: woven surface with anisotropic sheen and a slow drape | Custom GLSL fragment/vertex shader, product photo as texture maps | Lazy chunk, 1 plane |
| `/rental` listing | Rental cards reuse the `/shop` CSS tilt | CSS | Zero |
| `/cart`, `/checkout`, `/account`, `/auth` | None — calm, fast, static | — | Zero |
| 404, error, locale switch | None | — | Zero |

**Deliberate detail:** the object viewer's drag direction follows the finger
(drag right → object turns right), in both locales. Mirroring drag in RTL would
feel broken on touch. Only UI chrome (arrows in the hint text) mirrors.

## 9. Cross-cutting system

One implementation, consumed four ways:

- **`SceneRoot`** — client-only (`next/dynamic`, `ssr:false`), owns the canvas
  lifecycle for every page that needs one.
- **Pause rules** — `IntersectionObserver` (stop when off-screen) plus
  `visibilitychange` (stop when the tab is hidden). A paused scene renders zero
  frames.
- **Quality tiers** — `high | medium | low`, chosen once at mount from
  `devicePixelRatio`, `navigator.hardwareConcurrency`, `navigator.deviceMemory`,
  and a first-frame timing probe (downgrade if a frame exceeds 33 ms twice).
  `low` = no post-processing, fewer dust particles, `dpr` capped at 1;
  `high` = post-processing permitted (post-processing is enabled only in the
  product viewer — the home hero never enables it, see §15).
- **Tier persistence** — the tier is chosen once per **session**, held in a
  small sessionStorage-backed store, and re-probed only when the user explicitly
  resets quality. A phone that downgraded on `/` must not re-enter `high` on a
  product page and stutter again.
- **Fallback ladder** — no WebGL2 → designed poster image. Reduced motion →
  poster with an explicit "View in 3D" button; no autonomous frames. Slow first
  frame → drop a tier before the user notices.
- **Layout safety** — the canvas box is reserved with a CSS aspect ratio so
  hydration cannot shift the page.
- **Accessibility** — canvas is `aria-hidden`; adjacent real text names the
  object; the viewer is focusable with a visible ring and supports
  arrows (rotate), `+`/`-` (zoom), `R` (reset); a paragraph describes the object
  for screen readers.
- **Copy** — every hint/label lives in both message catalogs.

## 10. Assets

- Launch depends on **zero external 3D assets**: the khanjar is generated from a
  profile curve in code, the fabric is a shader over the product photo.
- When real scans exist, they arrive per product as `model_3d_url` (plus a
  poster). Pipeline: `web/scripts/optimize-glb.mjs` using `gltf-transform` —
  Draco or meshopt, KTX2 textures, target ≤ 1.5 MB per model.
- Admin gains nullable `model_3d_url` / `model_3d_poster` fields on products
  (Filament upload), added when Phase B lands.
- If no model exists, the viewer shows the procedural silhouette for the
  product's category — never an empty canvas. **Three silhouette families, not
  twenty:** `metal` (khanjar, saif, jewellery), `woven` (musar, shawl, asa),
  `layered` (bisht, garment). Each category maps to one family in
  `web/src/lib/three/families.ts`. Twenty per-category meshes would cost more
  than the hero and buy little; three well-made families read as intentional.

## 11. Budgets (hard, enforced at review)

- **3D JavaScript chunk ≤ 300 KB gzipped**, counted as *all* three.js-related JS
  loaded by a route: `three`, R3F, the drei slices actually imported, loaders,
  and any decoder glue. Decoder **wasm** counts against the payload budget, not
  the JS budget.
- **Decoder choice:** meshopt (≈10 KB decoder) is the default; Draco is used only
  if a supplied model requires it, because its decoder is ~200 KB and would eat
  most of the JS budget on its own. Decoders are self-hosted from `web/public/3d/`
  — never a CDN, so the shop does not depend on a third party at runtime.
- **Route payload:** home ≤ 400 KB beyond current; **product route ≤ 2.5 MB total
  including the GLB and poster**. A GLB above 500 KB is gated behind an explicit
  "Load 3D model" tap so the first view is always the poster.
- No canvas on `/cart`, `/checkout`, `/account`, `/auth` — asserted by test.
- "Low" tier frame target ≤ 33 ms on a throttled mid-range profile.
- Exceeding a budget is a cut, not a "we'll optimise later".
- **Enforcement:** there is no CI in this repo yet. Phase A adds `npm run budget`
  (reads the built route manifests and fails over the JS/payload ceilings) and
  Vitest; both run locally and are part of the phase's acceptance. A GitHub
  Actions workflow is added when the repo gets CI — not invented for one gate.

## 12. Testing

- **Playwright** never asserts canvas pixels (software GL in CI). It asserts:
  poster fallback renders, canvas has `aria-hidden`, viewer is keyboard
  focusable and rotates on arrow keys, checkout has no `<canvas>`, reduced-motion
  produces no animation frames.
- **Vitest** covers the quality-tier chooser, scroll-progress mapping, and the
  RTL drag-direction decision.
- **Manual:** one real mid-range Android and one iPhone, 4× CPU throttle, plus a
  bundle-size report on every PR touching `web/src/components/three`.

## 13. Sequencing

Lands **after** the 28 commerce tasks, on real pages rather than guesses:

- **Phase A** — `SceneRoot`, quality tiers, fallback ladder, home hero, **and the
  home page composition itself**. The commerce plan only ever builds a two-line
  placeholder home, so there is nothing to hero-ise and nothing for the scroll to
  reveal. Phase A therefore owns the homepage sections around the canvas: hero
  band, category strip, featured pieces, story band. This is real scope and is
  called out here rather than discovered mid-plan.
- **Phase B** — `ProductModelViewer`, the admin `model_3d_*` fields (the
  `products` table exists by then), and the GLB pipeline script.
- **Phase C** — rental fabric shader.

Each phase gets its own plan tasks and review; none of them may regress the
commerce paths.

## 14. Left to the builder

- The khanjar profile curve, gold material parameters, and lighting rig.
- Particle counts per tier and the exact downgrade thresholds.
- Whether drei's `Environment` ships an HDR or we bake a small gradient envmap.
- Texture format choice (KTX2 vs WebP) for the fabric.
- Exact poster artwork for each fallback.

## 15. Decisions settled after the builder check

These were open or contradictory in the first draft; they are resolved here and
the earlier sections are to be read through them.

- **Dark mode stays.** `globals.css` already carries a `prefers-color-scheme`
  dark palette and the existing components use `dark:` variants. Each 3D moment
  therefore gets **two poster variants** (light/dark, swapped with `<picture>`
  or a CSS media query) and a scene tint that follows the theme through one
  uniform; the lighting rig keeps a single envmap whose intensity is adjusted,
  not a second environment.
- **Post-processing:** the home hero never enables it. Bloom is permitted only
  in the product viewer, only on the `high` tier. (This resolves the §8/§9
  conflict.)
- **Reduced motion** forbids autonomous animation only; inspection remains
  available behind an explicit activation. (Resolves the §3/§9 conflict.)
- **Assets at launch are same-origin**, served from `web/public/3d/`. Only when
  admin model uploads land does a GLB come from the API's storage, and it is
  proxied through the web origin (or a CDN) so textures never fail on CORS.
- **The admin side belongs to Phase B** of this design, not to the 28 commerce
  tasks: the `products` table and Filament resource exist by then, and the two
  nullable columns are added in the same task that consumes them.
- **Tier is session-scoped**, in one sessionStorage-backed store, not a
  per-mount probe. (Resolves the §9 ambiguity.)
- **Silhouettes are three material families**, not one per category. (§10.)
- **No CI exists**, so enforcement is `npm run budget` plus Vitest, run locally
  and recorded in the phase review. (§11.)
- **Hero behaviour is breakpoint-split** (user decision, 2026-11-16): pinned
  scrub at ≥1024px, in-flow parallax below it. The pinned variant owns its own
  a11y story (canvas `pointer-events: none`, the section remains a landmark, the
  scroll length is fixed at ~1.2 viewports and snapping is **not** used), and the
  touch variant is the default implementation — desktop is the enhancement, so a
  failed desktop pin degrades to a working mobile hero rather than a broken page.
