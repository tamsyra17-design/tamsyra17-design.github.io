'use client';

import { useTranslations } from 'next-intl';
import Price from '@/components/product/Price';
import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { translate } from '@/lib/types';
import { MAX_QTY, useCartStore, type CartItem } from '@/stores/cart';

interface CartLineProps {
  item: CartItem;
  locale: Locale;
}

/** Localized name of a cart line, falling back to the name stored at add time. */
export function lineName(item: CartItem, locale: Locale): string {
  return (item.names && translate(item.names, locale)) || item.name;
}

const stepButton =
  'flex size-9 items-center justify-center text-lg font-semibold transition-colors hover:bg-brand-sand/50 focus-visible:outline-2 focus-visible:outline-brand-gold disabled:cursor-not-allowed disabled:opacity-40';

export default function CartLine({ item, locale }: CartLineProps) {
  const t = useTranslations('cart');
  const setQty = useCartStore((state) => state.setQty);
  const removeItem = useCartStore((state) => state.removeItem);
  const name = lineName(item, locale);

  return (
    <li
      data-testid="cart-line"
      className="flex flex-col gap-3 border-b border-brand-sand py-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div className="flex flex-col gap-1">
        {item.slug ? (
          <Link
            href={`/products/${item.slug}`}
            className="font-medium hover:text-brand-green"
          >
            {name}
          </Link>
        ) : (
          <span className="font-medium">{name}</span>
        )}
        <span className="text-sm text-zinc-600 dark:text-zinc-400">
          {t('unitPrice')}:{' '}
          <Price baisa={item.priceBaisa} locale={locale} className="font-normal" />
        </span>
      </div>

      <div className="flex items-center gap-4">
        <div
          role="group"
          aria-label={t('quantity')}
          className="flex items-center rounded-md border border-brand-sand"
        >
          <button
            type="button"
            data-testid="qty-minus"
            aria-label={t('decrease', { name })}
            onClick={() => setQty(item.productId, item.variantId, item.qty - 1)}
            disabled={item.qty <= 1}
            className={stepButton}
          >
            −
          </button>
          <output
            data-testid="qty-value"
            aria-live="polite"
            className="min-w-8 text-center tabular-nums"
          >
            {item.qty}
          </output>
          <button
            type="button"
            data-testid="qty-plus"
            aria-label={t('increase', { name })}
            onClick={() => setQty(item.productId, item.variantId, item.qty + 1)}
            disabled={item.qty >= MAX_QTY}
            className={stepButton}
          >
            +
          </button>
        </div>

        <div className="flex min-w-28 flex-col items-end">
          <span className="sr-only">{t('lineTotal')}</span>
          <Price
            baisa={item.priceBaisa * item.qty}
            locale={locale}
            className="text-base"
          />
        </div>

        <button
          type="button"
          data-testid="cart-remove"
          aria-label={t('removeLabel', { name })}
          onClick={() => removeItem(item.productId, item.variantId)}
          className="rounded-sm text-sm text-red-700 underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-brand-gold dark:text-red-400"
        >
          {t('remove')}
        </button>
      </div>
    </li>
  );
}
