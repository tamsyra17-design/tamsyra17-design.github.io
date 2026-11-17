'use client';

import { useTranslations } from 'next-intl';
import type { ReactNode } from 'react';
import { formatOmr } from '@/lib/money';
import type { Locale } from '@/i18n/routing';
import { useCartStore } from '@/stores/cart';

interface CartSummaryProps {
  locale: Locale;
  /** Optional call to action rendered under the totals (e.g. checkout link). */
  children?: ReactNode;
}

export default function CartSummary({ locale, children }: CartSummaryProps) {
  const t = useTranslations('cart');
  const subtotal = useCartStore((state) =>
    state.items.reduce((sum, line) => sum + line.priceBaisa * line.qty, 0),
  );

  return (
    <aside
      aria-labelledby="cart-summary-heading"
      className="flex flex-col gap-4 rounded-lg border border-brand-sand p-5"
    >
      <h2 id="cart-summary-heading" className="text-lg font-semibold">
        {t('summary')}
      </h2>
      <dl className="flex flex-col gap-2 text-sm">
        <div className="flex justify-between gap-4">
          <dt>{t('subtotal')}</dt>
          <dd
            data-testid="cart-subtotal"
            dir={locale === 'ar' ? 'rtl' : 'ltr'}
            className="font-semibold tabular-nums"
          >
            {formatOmr(subtotal, locale)}
          </dd>
        </div>
        <div className="flex justify-between gap-4 text-zinc-600 dark:text-zinc-400">
          <dt>{t('shipping')}</dt>
          <dd data-testid="cart-shipping">{t('shippingAtCheckout')}</dd>
        </div>
      </dl>
      {children}
    </aside>
  );
}
