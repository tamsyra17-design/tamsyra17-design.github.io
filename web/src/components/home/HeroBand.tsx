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
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const mq = window.matchMedia('(min-width: 1024px)');
    const update = () => setPinned(mq.matches);
    update();
    mq.addEventListener('change', update);
    return () => mq.removeEventListener('change', update);
  }, []);

  useEffect(() => {
    setIsVisible(true);
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
        <span
          className={`rounded-full border border-brand-gold/40 bg-brand-gold/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-brand-gold transition-all duration-700 ${
            isVisible
              ? 'opacity-100 translate-y-0'
              : 'opacity-0 translate-y-4'
          }`}
        >
          Omani Heritage
        </span>
        <h1
          className={`text-5xl font-bold tracking-tight text-brand-green dark:text-brand-sand sm:text-7xl transition-all duration-700 delay-100 ${
            isVisible
              ? 'opacity-100 scale-100'
              : 'opacity-0 scale-95'
          }`}
        >
          {t('hero')}
        </h1>
        <p
          className={`max-w-xl text-lg leading-relaxed text-brand-muted transition-all duration-700 delay-200 ${
            isVisible
              ? 'opacity-100 translate-y-0'
              : 'opacity-0 translate-y-4'
          }`}
        >
          {t('subtitle')}
        </p>
        <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
          <Link
            href="/shop"
            className={`rounded-md bg-brand-green px-6 py-3 font-semibold text-white shadow-sm transition-all duration-700 delay-300 hover:bg-brand-green-dark hover:shadow-lg hover:scale-105 active:scale-95 ${
              isVisible
                ? 'opacity-100 translate-y-0'
                : 'opacity-0 translate-y-4'
            }`}
          >
            Shop now
          </Link>
          <Link
            href="/rental"
            className={`rounded-md border border-brand-green px-6 py-3 font-semibold text-brand-green transition-all duration-700 delay-[400ms] hover:bg-brand-green/10 hover:scale-105 active:scale-95 ${
              isVisible
                ? 'opacity-100 translate-y-0'
                : 'opacity-0 translate-y-4'
            }`}
          >
            Rent a piece
          </Link>
        </div>
      </div>
      <style jsx>{`
        @keyframes float {
          0%, 100% {
            transform: translateY(0px);
          }
          50% {
            transform: translateY(-10px);
          }
        }

        @keyframes glow {
          0%, 100% {
            box-shadow: 0 0 20px rgba(139, 101, 51, 0.3);
          }
          50% {
            box-shadow: 0 0 40px rgba(139, 101, 51, 0.6);
          }
        }

        @keyframes shimmer {
          0% {
            background-position: -1000px 0;
          }
          100% {
            background-position: 1000px 0;
          }
        }

        h1 {
          animation: ${isVisible ? 'float' : 'none'} 3s ease-in-out infinite;
        }
      `}</style>
    </section>
  );
}
