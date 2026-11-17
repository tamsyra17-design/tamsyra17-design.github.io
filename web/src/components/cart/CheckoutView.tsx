'use client';

import { useTranslations } from 'next-intl';
import { useEffect } from 'react';
import Price from '@/components/product/Price';
import { Link, useRouter } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { useCartStore } from '@/stores/cart';
import { useCartHydrated } from '@/stores/useCartHydrated';
import { lineName } from './CartLine';
import CartSummary from './CartSummary';
import { CartSkeleton } from './CartView';

const fieldClass =
  'rounded-md border border-brand-sand bg-background px-3 py-2 disabled:cursor-not-allowed disabled:opacity-60';

/**
 * Checkout shell. Address and payment are placeholders and submission stays
 * disabled until the order flow lands (Task 14). An empty cart is sent back
 * to the cart page.
 */
export default function CheckoutView({ locale }: { locale: Locale }) {
  const t = useTranslations('checkout');
  const tCart = useTranslations('cart');
  const router = useRouter();
  const hydrated = useCartHydrated();
  const items = useCartStore((state) => state.items);
  const isEmpty = hydrated && items.length === 0;

  useEffect(() => {
    if (isEmpty) {
      router.replace('/cart');
    }
  }, [isEmpty, router]);

  if (!hydrated) {
    return <CartSkeleton label={tCart('loading')} />;
  }

  if (isEmpty) {
    return (
      <p role="status" data-testid="checkout-empty">
        {t('redirecting')}
      </p>
    );
  }

  return (
    <form
      onSubmit={(event) => event.preventDefault()}
      className="grid gap-8 lg:grid-cols-[1fr_20rem]"
    >
      <div className="flex flex-col gap-6">
        <fieldset
          disabled
          className="flex flex-col gap-3 rounded-lg border border-brand-sand p-5"
        >
          <legend className="px-1 text-lg font-semibold">{t('address')}</legend>
          {(
            [
              ['fullName', 'name'],
              ['phone', 'tel'],
              ['governorate', 'address-level1'],
              ['addressLine', 'street-address'],
            ] as const
          ).map(([key, autoComplete]) => (
            <label key={key} className="flex flex-col gap-1 text-sm">
              <span className="font-medium">{t(key)}</span>
              <input
                name={key}
                autoComplete={autoComplete}
                type={key === 'phone' ? 'tel' : 'text'}
                className={fieldClass}
              />
            </label>
          ))}
        </fieldset>

        <fieldset
          disabled
          className="flex flex-col gap-2 rounded-lg border border-brand-sand p-5"
        >
          <legend className="px-1 text-lg font-semibold">{t('payment')}</legend>
          <p className="text-sm text-zinc-600 dark:text-zinc-400">
            {t('paymentPlaceholder')}
          </p>
        </fieldset>
      </div>

      <CartSummary locale={locale}>
        <ul className="flex flex-col gap-1 border-t border-brand-sand pt-3 text-sm">
          {items.map((item) => (
            <li
              key={`${item.productId}:${item.variantId ?? 0}`}
              className="flex justify-between gap-3"
            >
              <span>
                {lineName(item, locale)} × {item.qty}
              </span>
              <Price
                baisa={item.priceBaisa * item.qty}
                locale={locale}
                className="font-normal"
              />
            </li>
          ))}
        </ul>
        <button
          type="submit"
          data-testid="place-order"
          disabled
          aria-describedby="checkout-coming-soon"
          className="rounded-md bg-brand-green px-5 py-3 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
        >
          {t('placeOrder')}
        </button>
        <p id="checkout-coming-soon" className="text-xs text-zinc-600 dark:text-zinc-400">
          {t('comingSoon')}
        </p>
        <Link href="/cart" className="text-center text-sm hover:text-brand-green">
          {t('backToCart')}
        </Link>
      </CartSummary>
    </form>
  );
}
