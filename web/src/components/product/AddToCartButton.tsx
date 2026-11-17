'use client';

import { useTranslations } from 'next-intl';
import { useId, useState } from 'react';
import type { Locale } from '@/i18n/routing';
import { translate, type ProductVariant } from '@/lib/types';
import { useCartStore } from '@/stores/cart';
import { useCartHydrated } from '@/stores/useCartHydrated';
import Price from './Price';

interface AddToCartButtonProps {
  productId: number;
  slug: string;
  name: string;
  priceBaisa: number;
  variants: ProductVariant[];
  inStock: boolean;
  locale: Locale;
}

export default function AddToCartButton({
  productId,
  slug,
  name,
  priceBaisa,
  variants,
  inStock,
  locale,
}: AddToCartButtonProps) {
  const t = useTranslations('product');
  const selectId = useId();
  const hydrated = useCartHydrated();
  const addItem = useCartStore((state) => state.addItem);
  const [variantId, setVariantId] = useState<number | null>(
    variants[0]?.id ?? null,
  );
  const [added, setAdded] = useState(false);

  const variant = variants.find((item) => item.id === variantId);
  const linePrice = variant?.price_baisa ?? priceBaisa;
  const variantName = variant ? translate(variant.name, locale) : '';

  const handleAdd = () => {
    addItem({
      productId,
      variantId: variant?.id ?? null,
      name: variantName ? `${name} — ${variantName}` : name,
      priceBaisa: linePrice,
      qty: 1,
      slug,
    });
    setAdded(true);
  };

  return (
    <div className="flex flex-col gap-3">
      {variants.length > 0 && (
        <div className="flex flex-col gap-1">
          <label htmlFor={selectId} className="text-sm font-medium">
            {t('variant')}
          </label>
          <select
            id={selectId}
            value={variantId ?? ''}
            onChange={(event) => {
              setVariantId(Number(event.target.value));
              setAdded(false);
            }}
            className="rounded-md border border-brand-sand bg-background px-3 py-2"
          >
            {variants.map((item) => (
              <option key={item.id} value={item.id}>
                {translate(item.name, locale)}
              </option>
            ))}
          </select>
          {linePrice !== priceBaisa && (
            <Price baisa={linePrice} locale={locale} className="text-lg" />
          )}
        </div>
      )}

      <button
        type="button"
        data-testid="add-to-cart"
        onClick={handleAdd}
        // Wait for the persisted cart to load so a click can't overwrite it.
        disabled={!inStock || !hydrated}
        className="rounded-md bg-brand-green px-6 py-3 font-semibold text-white transition-opacity hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-gold disabled:cursor-not-allowed disabled:opacity-50"
      >
        {inStock ? t('addToCart') : t('outOfStock')}
      </button>

      <p role="status" className="min-h-5 text-sm text-brand-green">
        {added ? t('added') : ''}
      </p>
    </div>
  );
}
