'use client';

import { zodResolver } from '@hookform/resolvers/zod';
import { useTranslations } from 'next-intl';
import { useEffect, useMemo, useState } from 'react';
import { FormProvider, useForm, type Path } from 'react-hook-form';
import AddressForm from '@/components/checkout/AddressForm';
import PaymentMethodPicker from '@/components/checkout/PaymentMethodPicker';
import { checkoutSchema, type CheckoutValues } from '@/components/checkout/schema';
import Price from '@/components/product/Price';
import { Link, useRouter } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import {
  OrderValidationError,
  placeOrder,
  rememberOrder,
  type PaymentMethod,
} from '@/lib/orders';
import { useCartStore } from '@/stores/cart';
import { useCartHydrated } from '@/stores/useCartHydrated';
import { lineName } from './CartLine';
import CartSummary from './CartSummary';
import { CartSkeleton } from './CartView';

/** API error keys → form fields. Item errors are shown as a form-level message. */
const API_FIELDS: Record<string, Path<CheckoutValues>> = {
  'customer.name': 'name',
  'customer.phone': 'phone',
  'customer.email': 'email',
  'address.governorate': 'governorate',
  'address.wilayat': 'wilayat',
  'address.line1': 'line1',
  payment_method: 'paymentMethod',
};

interface CheckoutViewProps {
  locale: Locale;
  /** Methods enabled in store settings; the first is preselected. */
  paymentMethods: PaymentMethod[];
}

/**
 * Guest checkout: address + payment form, posted to `POST /api/v1/orders`.
 * On success the cart is cleared and the shopper lands on the order page.
 * An empty cart is sent back to the cart page.
 */
export default function CheckoutView({ locale, paymentMethods }: CheckoutViewProps) {
  const t = useTranslations('checkout');
  const tCart = useTranslations('cart');
  const router = useRouter();
  const hydrated = useCartHydrated();
  const items = useCartStore((state) => state.items);
  const clearCart = useCartStore((state) => state.clear);
  const [formError, setFormError] = useState<string | null>(null);
  // Set once the order exists. The cart is cleared only after this commits,
  // so the empty-cart redirect below never races the confirmation redirect.
  const [placedNumber, setPlacedNumber] = useState<string | null>(null);

  const schema = useMemo(() => checkoutSchema(t), [t]);
  const form = useForm<CheckoutValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: '',
      phone: '',
      email: '',
      governorate: '',
      wilayat: '',
      line1: '',
      paymentMethod: paymentMethods[0] ?? 'cod',
    },
  });

  const isEmpty = hydrated && items.length === 0;
  // Lines saved before the product had a sellable variant cannot be ordered.
  const unorderable = items.filter((item) => item.variantId === null);

  useEffect(() => {
    if (isEmpty && placedNumber === null) {
      router.replace('/cart');
    }
  }, [isEmpty, placedNumber, router]);

  useEffect(() => {
    if (placedNumber !== null) {
      clearCart();
      router.push(`/orders/${encodeURIComponent(placedNumber)}`);
    }
  }, [placedNumber, clearCart, router]);

  if (!hydrated) {
    return <CartSkeleton label={tCart('loading')} />;
  }

  if (isEmpty) {
    return (
      <p role="status" data-testid="checkout-empty">
        {placedNumber !== null ? t('placing') : t('redirecting')}
      </p>
    );
  }

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(null);

    try {
      const order = await placeOrder({
        customer: {
          name: values.name,
          phone: values.phone,
          ...(values.email ? { email: values.email } : {}),
        },
        address: {
          governorate: values.governorate,
          wilayat: values.wilayat,
          line1: values.line1,
        },
        items: items.flatMap((item) =>
          item.variantId === null ? [] : [{ variant_id: item.variantId, qty: item.qty }],
        ),
        payment_method: values.paymentMethod as PaymentMethod,
      });

      rememberOrder(order);
      setPlacedNumber(order.number);
    } catch (error) {
      if (error instanceof OrderValidationError) {
        let itemError: string | null = null;

        for (const [key, messages] of Object.entries(error.errors)) {
          const field = API_FIELDS[key];
          if (field) {
            form.setError(field, { message: messages[0] });
          } else {
            itemError ??= messages[0];
          }
        }

        setFormError(itemError ?? t('errors.invalid'));
        return;
      }

      setFormError(t('errors.network'));
    }
  });

  const submitting = form.formState.isSubmitting;

  return (
    <FormProvider {...form}>
      <form
        noValidate
        onSubmit={onSubmit}
        className="grid gap-8 lg:grid-cols-[1fr_20rem]"
      >
        <div className="flex flex-col gap-6">
          <AddressForm />
          <PaymentMethodPicker methods={paymentMethods} />
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

          {unorderable.length > 0 && (
            <p role="alert" className="text-sm text-red-700 dark:text-red-400">
              {t('errors.unorderable', {
                names: unorderable.map((item) => lineName(item, locale)).join(', '),
              })}
            </p>
          )}

          {formError && (
            <p role="alert" data-testid="checkout-error" className="text-sm text-red-700 dark:text-red-400">
              {formError}
            </p>
          )}

          <button
            type="submit"
            data-testid="place-order"
            disabled={submitting || placedNumber !== null || unorderable.length > 0}
            aria-busy={submitting || undefined}
            className="rounded-md bg-brand-green px-5 py-3 font-semibold text-white transition-opacity hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-gold disabled:cursor-not-allowed disabled:opacity-50"
          >
            {submitting ? t('placing') : t('placeOrder')}
          </button>
          <p className="text-xs text-zinc-600 dark:text-zinc-400">{t('shippingNote')}</p>
          <Link href="/cart" className="text-center text-sm hover:text-brand-green">
            {t('backToCart')}
          </Link>
        </CartSummary>
      </form>
    </FormProvider>
  );
}
