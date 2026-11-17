/* eslint-disable react-hooks/set-state-in-effect */
'use client';

import { Canvas } from '@react-three/fiber';
import { useTranslations } from 'next-intl';
import { useEffect, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import { hasWebGL2 } from '@/lib/three/webgl';
import type { QualityTier } from '@/lib/three/quality';
import { useQualityTier } from '@/lib/three/use-quality-tier';

interface SceneRootProps {
  poster: { light: string; dark: string };
  ariaLabel: string;
  children: (tier: QualityTier) => ReactNode;
}

/**
 * Owns the only WebGL canvas a page may have. Renders a designed poster
 * until the wrapper is on screen and WebGL2 exists. Under reduced motion it
 * never mounts on its own: the visitor opts in with "View in 3D", and the
 * canvas then renders on demand only (no autonomous frames). Pauses when
 * off-screen or the tab is hidden.
 */
export default function SceneRoot({ poster, ariaLabel, children }: SceneRootProps) {
  const t = useTranslations('three');
  const wrapperRef = useRef<HTMLDivElement>(null);
  const tier = useQualityTier();
  const [webgl, setWebgl] = useState(false);
  const [mounted, setMounted] = useState(false);
  const [reducedMotion, setReducedMotion] = useState(false);
  const [inView, setInView] = useState(false);
  const [pageVisible, setPageVisible] = useState(true);

  useEffect(() => {
    setWebgl(hasWebGL2());
  }, []);

  useEffect(() => {
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
    setReducedMotion(mq.matches);
    const handler = (e: MediaQueryListEvent) => setReducedMotion(e.matches);
    mq.addEventListener('change', handler);
    return () => mq.removeEventListener('change', handler);
  }, []);

  useEffect(() => {
    const update = () => setPageVisible(document.visibilityState !== 'hidden');
    update();
    document.addEventListener('visibilitychange', update);
    return () => document.removeEventListener('visibilitychange', update);
  }, []);

  useEffect(() => {
    const node = wrapperRef.current;
    if (!node) return;
    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) setInView(entry.isIntersecting);
      },
      { threshold: 0.1 },
    );
    observer.observe(node);
    return () => observer.disconnect();
  }, []);

  // Automatic mount only with motion allowed; leaving the viewport unmounts.
  useEffect(() => {
    if (!inView) setMounted(false);
    else if (webgl && !reducedMotion) setMounted(true);
  }, [inView, webgl, reducedMotion]);

  const showCanvas = mounted && webgl;
  const frameloop = !inView || !pageVisible ? 'never' : reducedMotion ? 'demand' : 'always';

  return (
    <div
      ref={wrapperRef}
      data-testid="scene-root"
      className="relative w-full"
      style={{ aspectRatio: '16 / 9' }}
    >
      <div aria-hidden="true" className="absolute inset-0">
        {showCanvas ? (
          <Canvas
            dpr={[1, tier === 'high' ? 1.5 : 1]}
            frameloop={frameloop}
            gl={{ antialias: tier !== 'low' }}
          >
            {children(tier)}
          </Canvas>
        ) : (
          <picture>
            <source media="(prefers-color-scheme: dark)" srcSet={poster.dark} />
            <img
              src={poster.light}
              alt={ariaLabel}
              className="h-full w-full object-cover"
              loading="lazy"
            />
          </picture>
        )}
      </div>
      {reducedMotion && webgl && !showCanvas && (
        <button
          type="button"
          onClick={() => setMounted(true)}
          className="absolute bottom-3 end-3 rounded-md bg-brand-green px-3 py-1.5 text-xs font-semibold text-white"
        >
          {t('viewIn3D')}
        </button>
      )}
    </div>
  );
}
