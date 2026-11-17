'use client';

import { useTranslations } from 'next-intl';
import { useCartStore } from '@/stores/cart';
import { useCartHydrated } from '@/stores/useCartHydrated';

/** Live item count for the header cart link, read from the Zustand cart. */
export default function CartCount() {
  const t = useTranslations('cart');
  const hydrated = useCartHydrated();
  const count = useCartStore((state) =>
    state.items.reduce((sum, line) => sum + line.qty, 0),
  );
  const shown = hydrated ? count : 0;

  return (
    <>
      <span
        data-testid="cart-count"
        aria-hidden="true"
        className="ms-1.5 inline-flex min-w-5 items-center justify-center rounded-full bg-brand-green px-1.5 text-xs font-semibold tabular-nums text-white"
      >
        {shown}
      </span>
      <span className="sr-only">{t('countLabel', { count: shown })}</span>
    </>
  );
}
