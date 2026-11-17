import { Suspense } from 'react';
import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import LocaleSwitcher from './LocaleSwitcher';

const navItems = [
  { href: '/', key: 'home' },
  { href: '/shop', key: 'shop' },
  { href: '/rental', key: 'rental' },
  { href: '/cart', key: 'cart' },
  { href: '/account', key: 'account' },
] as const;

export default function Header() {
  const t = useTranslations('nav');
  const tA11y = useTranslations('a11y');
  const tSite = useTranslations('site');

  return (
    <header className="sticky top-0 z-50 border-b border-brand-border bg-background/90 backdrop-blur-md">
      <a href="#main-content" className="skip-link">
        {tA11y('skipToContent')}
      </a>

      <div className="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <Link
          href="/"
          className="flex items-center gap-2 text-xl font-bold tracking-tight text-brand-green dark:text-brand-sand"
        >
          <span className="inline-block size-8 rounded-full bg-brand-green text-center text-sm leading-8 text-white">
            ت
          </span>
          {tSite('name')}
        </Link>

        <div className="flex items-center gap-6">
          <nav aria-label={t('mainLabel')} className="hidden md:block">
            <ul className="flex items-center gap-1">
              {navItems.map((item) => (
                <li key={item.key}>
                  <Link
                    href={item.href}
                    className="rounded-md px-3 py-2 text-sm font-medium text-foreground/80 transition-colors hover:bg-brand-sand/60 hover:text-brand-green dark:hover:text-brand-gold"
                  >
                    {t(item.key)}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>

          <Suspense fallback={null}>
            <LocaleSwitcher />
          </Suspense>
        </div>
      </div>

      {/* Mobile nav */}
      <nav aria-label={t('mainLabel')} className="border-t border-brand-border px-4 py-2 md:hidden">
        <ul className="flex items-center justify-around gap-1 text-xs font-medium">
          {navItems.map((item) => (
            <li key={item.key}>
              <Link
                href={item.href}
                className="rounded-md px-2 py-1.5 text-foreground/70 transition-colors hover:text-brand-green dark:hover:text-brand-gold"
              >
                {t(item.key)}
              </Link>
            </li>
          ))}
        </ul>
      </nav>
    </header>
  );
}
