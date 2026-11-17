'use client';

import { useTranslations } from 'next-intl';
import { useFormContext } from 'react-hook-form';
import type { PaymentMethod } from '@/lib/orders';
import type { CheckoutValues } from './schema';

/** Radio list of the payment methods the store has enabled. */
export default function PaymentMethodPicker({ methods }: { methods: PaymentMethod[] }) {
  const t = useTranslations('checkout');
  const { register } = useFormContext<CheckoutValues>();

  return (
    <fieldset className="flex flex-col gap-3 rounded-lg border border-brand-sand p-5">
      <legend className="px-1 text-lg font-semibold">{t('payment')}</legend>
      {methods.map((method) => (
        <label
          key={method}
          className="flex cursor-pointer items-start gap-3 rounded-md border border-brand-sand p-3 has-checked:border-brand-green has-checked:bg-brand-sand/30"
        >
          <input
            type="radio"
            value={method}
            className="mt-1 accent-brand-green"
            data-testid={`payment-${method}`}
            {...register('paymentMethod')}
          />
          <span className="flex flex-col">
            <span className="font-medium">{t(`methods.${method}.label`)}</span>
            <span className="text-sm text-zinc-600 dark:text-zinc-400">
              {t(`methods.${method}.hint`)}
            </span>
          </span>
        </label>
      ))}
    </fieldset>
  );
}
