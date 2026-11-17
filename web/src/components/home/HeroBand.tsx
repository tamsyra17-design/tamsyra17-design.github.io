'use client';

import { useTranslations } from 'next-intl';
import { useEffect, useState, type ReactNode } from 'react';
import SceneRoot from '@/components/three/SceneRoot';

interface HeroBandProps {
  children?: ReactNode;
}

export default function HeroBand({ children }: HeroBandProps) {
  const t = useTranslations('home');
  const [pinned, setPinned] = useState(false);

  useEffect(() => {
    const mq = window.matchMedia('(min-width: 1024px)');
    const update = () => setPinned(mq.matches);
    update();
    mq.addEventListener('change', update);
    return () => mq.removeEventListener('change', update);
  }, []);

  return (
    <section
      data-testid="home-hero"
      data-pinned={pinned ? 'true' : 'false'}
      className="relative flex min-h-[70vh] flex-col items-center justify-center overflow-hidden px-4 py-16 text-center"
    >
      <SceneRoot
        poster={{ light: '/hero-poster-light.svg', dark: '/hero-poster-dark.svg' }}
        ariaLabel={t('hero')}
      >
        {() => children ?? null}
      </SceneRoot>
      <div className="relative z-10 flex flex-col items-center gap-4">
        <h1 className="text-4xl font-bold tracking-tight text-brand-green dark:text-brand-sand sm:text-6xl">
          {t('hero')}
        </h1>
        <p className="max-w-xl text-lg text-zinc-700 dark:text-zinc-300">
          {t('subtitle')}
        </p>
      </div>
    </section>
  );
}
