import { Suspense } from 'react';
import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import CartCount from '@/components/cart/CartCount';
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
    <header className="sticky top-0 z-50 border-b border-brand-sand bg-background/95 backdrop-blur">
      <a href="#main-content" className="skip-link">
        {tA11y('skipToContent')}
      </a>

      <div className="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-3 sm:px-6">
        <Link
          href="/"
          className="text-lg font-semibold tracking-tight text-brand-green dark:text-brand-sand"
        >
          {tSite('name')}
        </Link>

        <div className="flex flex-wrap items-center gap-x-6 gap-y-2">
          <nav aria-label={t('mainLabel')}>
            <ul className="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm font-medium">
              {navItems.map((item) => (
                <li key={item.key}>
                  <Link
                    href={item.href}
                    className="inline-flex items-center rounded-sm text-foreground/80 transition-colors hover:text-brand-green dark:hover:text-brand-gold"
                  >
                    {t(item.key)}
                    {item.key === 'cart' && <CartCount />}
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
    </header>
  );
}
