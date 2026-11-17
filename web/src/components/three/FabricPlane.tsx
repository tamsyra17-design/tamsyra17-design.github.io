'use client';

import { useFrame, useThree } from '@react-three/fiber';
import { useEffect, useMemo, useRef, useState } from 'react';
import * as THREE from 'three';
import SceneRoot from './SceneRoot';
import { fabricUniforms } from '@/lib/three/fabric';
import type { QualityTier } from '@/lib/three/quality';
import { useQualityTier } from '@/lib/three/use-quality-tier';

interface FabricPlaneProps {
  /** Product photo mapped onto the cloth; a woven sand base when absent. */
  textureUrl?: string | null;
  /** Shown until (or instead of) the canvas. */
  posterUrl: { light: string; dark: string };
  /** Real text that names the surface (the canvas itself is aria-hidden). */
  label: string;
}

const SHEEN = new THREE.Color('#c9a227');
const BASE = new THREE.Color('#e8dcc4');

const vertexShader = /* glsl */ `
  uniform float uTime;
  uniform float uStrength;
  uniform float uWidth;
  varying vec2 vUv;
  varying vec3 vNormal;
  varying vec3 vViewDir;

  void main() {
    vUv = uv;
    float phase = uv.x * 18.0 + uTime * 0.6;
    vec3 p = position;
    p.z += sin(phase) * uStrength + sin(uv.y * 5.0 + uTime * 0.35) * uStrength * 0.25;

    // Analytic normal of the drape along x (uv.x spans the plane width).
    float slope = cos(phase) * uStrength * 18.0 / uWidth;
    vec3 n = normalize(vec3(-slope, 0.0, 1.0));

    vec4 mv = modelViewMatrix * vec4(p, 1.0);
    vNormal = normalize(normalMatrix * n);
    vViewDir = normalize(-mv.xyz);
    gl_Position = projectionMatrix * mv;
  }
`;

const fragmentShader = /* glsl */ `
  uniform sampler2D uMap;
  uniform float uHasMap;
  uniform vec3 uBase;
  uniform vec3 uSheen;
  varying vec2 vUv;
  varying vec3 vNormal;
  varying vec3 vViewDir;

  void main() {
    vec3 n = normalize(vNormal);
    vec3 base = mix(uBase, texture2D(uMap, vUv).rgb, uHasMap);

    // Soft key light so the drape reads, then the weave and the sheen.
    float diffuse = 0.78 + 0.22 * max(dot(n, normalize(vec3(0.3, 0.5, 1.0))), 0.0);
    vec2 w = fract(vUv * 180.0);
    float thread = step(0.82, max(w.x, w.y));
    vec3 color = base * diffuse * (1.0 - 0.09 * thread);

    float sheen = pow(1.0 - max(dot(n, normalize(vViewDir)), 0.0), 3.0);
    color += uSheen * sheen * 0.55;

    gl_FragColor = vec4(color, 1.0);
  }
`;

function Cloth({ tier, textureUrl }: { tier: QualityTier; textureUrl?: string | null }) {
  const { uSegments, uStrength } = fabricUniforms(tier);
  const viewport = useThree((s) => s.viewport);
  const invalidate = useThree((s) => s.invalidate);
  const width = viewport.width * 0.92;
  const height = viewport.height * 0.92;

  const [reducedMotion] = useState(
    () => window.matchMedia('(prefers-reduced-motion: reduce)').matches,
  );

  const geometry = useMemo(
    () => new THREE.PlaneGeometry(width, height, uSegments, uSegments),
    [width, height, uSegments],
  );
  useEffect(() => () => geometry.dispose(), [geometry]);

  // three.js owns the uniforms; React holds them through the material ref and
  // writes them in effects and frames, never during render.
  const materialRef = useRef<THREE.ShaderMaterial>(null);
  const uniforms = useMemo(
    () => ({
      uTime: { value: 0 },
      uStrength: { value: 0 },
      uWidth: { value: 1 },
      uMap: { value: null as THREE.Texture | null },
      uHasMap: { value: 0 },
      uBase: { value: BASE },
      uSheen: { value: SHEEN },
    }),
    [],
  );

  useEffect(() => {
    const material = materialRef.current;
    if (!material) return;
    material.uniforms.uStrength.value = uStrength;
    material.uniforms.uWidth.value = width;
    invalidate();
  }, [uStrength, width, invalidate]);

  useEffect(() => {
    const material = materialRef.current;
    if (!textureUrl || !material) return;
    let texture: THREE.Texture | null = null;
    let cancelled = false;
    const loader = new THREE.TextureLoader();
    loader.setCrossOrigin('anonymous');
    loader.load(
      textureUrl,
      (loaded) => {
        if (cancelled) {
          loaded.dispose();
          return;
        }
        loaded.colorSpace = THREE.SRGBColorSpace;
        texture = loaded;
        material.uniforms.uMap.value = loaded;
        material.uniforms.uHasMap.value = 1;
        invalidate();
      },
      undefined,
      // A blocked or missing photo keeps the woven base colour.
      () => undefined,
    );
    return () => {
      cancelled = true;
      material.uniforms.uMap.value = null;
      material.uniforms.uHasMap.value = 0;
      texture?.dispose();
    };
  }, [textureUrl, invalidate]);

  // Under reduced motion uTime stays frozen at 0; SceneRoot also renders on demand only.
  useFrame(({ clock }) => {
    const material = materialRef.current;
    if (!material || reducedMotion || uStrength === 0) return;
    material.uniforms.uTime.value = clock.elapsedTime;
  });

  return (
    <mesh geometry={geometry}>
      <shaderMaterial
        ref={materialRef}
        vertexShader={vertexShader}
        fragmentShader={fragmentShader}
        uniforms={uniforms}
      />
    </mesh>
  );
}

/**
 * Rental fabric surface: the garment photo on a gently draping plane with a
 * gold sheen and weave lines. Detail follows the session quality tier; the
 * tier's segment count is exposed on the wrapper for tests and debugging.
 */
export default function FabricPlane({ textureUrl, posterUrl, label }: FabricPlaneProps) {
  const tier = useQualityTier();
  const { uSegments } = fabricUniforms(tier);

  return (
    <figure
      data-testid="fabric-plane"
      data-segments={uSegments}
      className="overflow-hidden rounded-lg bg-brand-sand/40"
    >
      <SceneRoot poster={posterUrl} ariaLabel="">
        {(sceneTier) => <Cloth tier={sceneTier} textureUrl={textureUrl} />}
      </SceneRoot>
      <figcaption className="sr-only">{label}</figcaption>
    </figure>
  );
}
