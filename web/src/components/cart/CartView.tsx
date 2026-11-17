'use client';

import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { useCartStore } from '@/stores/cart';
import { useCartHydrated } from '@/stores/useCartHydrated';
import CartLine from './CartLine';
import CartSummary from './CartSummary';

/** Client body of the cart page; waits for the persisted cart to load. */
export default function CartView({ locale }: { locale: Locale }) {
  const t = useTranslations('cart');
  const hydrated = useCartHydrated();
  const items = useCartStore((state) => state.items);

  if (!hydrated) {
    return <CartSkeleton label={t('loading')} />;
  }

  if (items.length === 0) {
    return (
      <div data-testid="cart-empty" className="flex flex-col items-start gap-4">
        <p className="text-zinc-700 dark:text-zinc-300">{t('empty')}</p>
        <Link
          href="/shop"
          className="rounded-md bg-brand-green px-5 py-2.5 font-semibold text-white hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-gold"
        >
          {t('continueShopping')}
        </Link>
      </div>
    );
  }

  return (
    <div className="grid gap-8 lg:grid-cols-[1fr_20rem]">
      <ul className="flex flex-col">
        {items.map((item) => (
          <CartLine
            key={`${item.productId}:${item.variantId ?? 0}`}
            item={item}
            locale={locale}
          />
        ))}
      </ul>

      <CartSummary locale={locale}>
        <Link
          href="/checkout"
          data-testid="checkout-link"
          className="rounded-md bg-brand-green px-5 py-3 text-center font-semibold text-white hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-gold"
        >
          {t('checkout')}
        </Link>
      </CartSummary>
    </div>
  );
}

export function CartSkeleton({ label }: { label: string }) {
  return (
    <div role="status" className="grid gap-8 lg:grid-cols-[1fr_20rem]">
      <span className="sr-only">{label}</span>
      <div aria-hidden="true" className="flex flex-col gap-3">
        <div className="h-16 animate-pulse rounded bg-brand-sand/60" />
        <div className="h-16 animate-pulse rounded bg-brand-sand/60" />
      </div>
      <div aria-hidden="true" className="h-40 animate-pulse rounded-lg bg-brand-sand/60" />
    </div>
  );
}
