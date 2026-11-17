'use client';

import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
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
      className="relative flex min-h-[85vh] flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-brand-sand/40 via-background to-background px-4 py-20 text-center"
    >
      <SceneRoot
        poster={{ light: '/hero-poster-light.svg', dark: '/hero-poster-dark.svg' }}
        ariaLabel={t('hero')}
      >
        {() => children ?? null}
      </SceneRoot>
      <div className="relative z-10 flex max-w-3xl flex-col items-center gap-6">
        <span className="rounded-full border border-brand-gold/40 bg-brand-gold/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-brand-gold">
          Omani Heritage
        </span>
        <h1 className="text-5xl font-bold tracking-tight text-brand-green dark:text-brand-sand sm:text-7xl">
          {t('hero')}
        </h1>
        <p className="max-w-xl text-lg leading-relaxed text-brand-muted">
          {t('subtitle')}
        </p>
        <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
          <Link
            href="/shop"
            className="rounded-md bg-brand-green px-6 py-3 font-semibold text-white shadow-sm transition-all hover:bg-brand-green-dark hover:shadow-md"
          >
            Shop now
          </Link>
          <Link
            href="/rental"
            className="rounded-md border border-brand-green px-6 py-3 font-semibold text-brand-green transition-colors hover:bg-brand-green/10"
          >
            Rent a piece
          </Link>
        </div>
      </div>
    </section>
  );
}
