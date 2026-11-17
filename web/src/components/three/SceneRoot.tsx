'use client';

import { Canvas } from '@react-three/fiber';
import { useTranslations } from 'next-intl';
import { Component, useEffect, useRef, useState, type ReactNode } from 'react';
import { hasWebGL2 } from '@/lib/three/webgl';
import { chooseTier, readTier, writeTier, type QualityTier } from '@/lib/three/quality';

interface SceneRootProps {
  poster: { light: string; dark: string };
  ariaLabel: string;
  children: (tier: QualityTier) => ReactNode;
}

class CanvasErrorBoundary extends Component<
  { fallback: ReactNode; children: ReactNode },
  { hasError: boolean }
> {
  constructor(props: { fallback: ReactNode; children: ReactNode }) {
    super(props);
    this.state = { hasError: false };
  }
  static getDerivedStateFromError() {
    return { hasError: true };
  }
  render() {
    if (this.state.hasError) return this.props.fallback;
    return this.props.children;
  }
}

export default function SceneRoot({ poster, ariaLabel, children }: SceneRootProps) {
  const t = useTranslations('three');
  const wrapperRef = useRef<HTMLDivElement>(null);
  const [mounted, setMounted] = useState(false);
  const [tier, setTier] = useState<QualityTier>('medium');
  const [reducedMotion, setReducedMotion] = useState(false);
  const [inView, setInView] = useState(false);

  /* eslint-disable react-hooks/set-state-in-effect */
  useEffect(() => {
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
    setReducedMotion(mq.matches);
    const handler = (e: MediaQueryListEvent) => setReducedMotion(e.matches);
    mq.addEventListener('change', handler);
    return () => mq.removeEventListener('change', handler);
  }, []);

  useEffect(() => {
    const stored = readTier();
    const chosen =
      stored ??
      chooseTier({
        deviceMemory: (navigator as { deviceMemory?: number }).deviceMemory,
        hardwareConcurrency: navigator.hardwareConcurrency,
        dpr: window.devicePixelRatio,
      });
    setTier(chosen);
    writeTier(chosen);
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

  useEffect(() => {
    if (reducedMotion) return;
    if (!hasWebGL2()) return;
    if (!inView) return;
    setMounted(true);
  }, [reducedMotion, inView]);

  useEffect(() => {
    if (!inView) setMounted(false);
  }, [inView]);
  /* eslint-enable react-hooks/set-state-in-effect */

  const showCanvas = mounted && !reducedMotion && hasWebGL2();

  const posterEl = (
    <picture>
      <source media="(prefers-color-scheme: dark)" srcSet={poster.dark} />
      <img
        src={poster.light}
        alt={ariaLabel}
        className="h-full w-full object-cover"
        loading="lazy"
      />
    </picture>
  );

  return (
    <div
      ref={wrapperRef}
      data-testid="scene-root"
      className="relative w-full"
      style={{ aspectRatio: '16 / 9' }}
      aria-hidden="true"
    >
      {showCanvas ? (
        <CanvasErrorBoundary fallback={posterEl}>
          <Canvas
            dpr={[1, tier === 'high' ? 1.5 : 1]}
            frameloop={inView ? 'always' : 'never'}
            gl={{ antialias: tier !== 'low' }}
          >
            {children(tier)}
          </Canvas>
        </CanvasErrorBoundary>
      ) : (
        posterEl
      )}
      {reducedMotion && (
        <button
          type="button"
          onClick={() => setMounted(true)}
          className="absolute bottom-3 end-3 rounded-md bg-brand-green px-6 py-1.5 text-xs font-semibold text-white"
        >
          {t('viewIn3D')}
        </button>
      )}
    </div>
  );
}
