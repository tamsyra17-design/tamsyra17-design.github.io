'use client';

import { useLocale, useTranslations } from 'next-intl';
import { useId, type ReactNode } from 'react';
import { useFormContext, type FieldError } from 'react-hook-form';
import type { Locale } from '@/i18n/routing';
import { GOVERNORATES } from '@/lib/orders';
import type { CheckoutValues } from './schema';

export const fieldClass =
  'rounded-md border border-brand-sand bg-background px-3 py-2 aria-invalid:border-red-600 disabled:cursor-not-allowed disabled:opacity-60';

interface FieldProps {
  id: string;
  label: string;
  error?: FieldError;
  children: ReactNode;
}

function Field({ id, label, error, children }: FieldProps) {
  return (
    <div className="flex flex-col gap-1 text-sm">
      <label htmlFor={id} className="font-medium">
        {label}
      </label>
      {children}
      {error?.message && (
        <p id={`${id}-error`} className="text-red-700 dark:text-red-400">
          {error.message}
        </p>
      )}
    </div>
  );
}

/** Contact and delivery fields. Must render inside a react-hook-form FormProvider. */
export default function AddressForm() {
  const t = useTranslations('checkout');
  const locale = useLocale() as Locale;
  const prefix = useId();
  const {
    register,
    formState: { errors },
  } = useFormContext<CheckoutValues>();

  const fieldProps = (name: keyof CheckoutValues) => ({
    id: `${prefix}-${name}`,
    'aria-invalid': errors[name] ? true : undefined,
    'aria-describedby': errors[name] ? `${prefix}-${name}-error` : undefined,
    className: fieldClass,
    ...register(name),
  });

  return (
    <fieldset className="flex flex-col gap-3 rounded-lg border border-brand-sand p-5">
      <legend className="px-1 text-lg font-semibold">{t('delivery')}</legend>

      <Field id={`${prefix}-name`} label={t('fullName')} error={errors.name}>
        <input type="text" autoComplete="name" {...fieldProps('name')} />
      </Field>

      <Field id={`${prefix}-phone`} label={t('phone')} error={errors.phone}>
        <input
          type="tel"
          autoComplete="tel"
          inputMode="tel"
          dir="ltr"
          placeholder="+968 9XXX XXXX"
          {...fieldProps('phone')}
        />
      </Field>

      <Field id={`${prefix}-email`} label={t('email')} error={errors.email}>
        <input type="email" autoComplete="email" dir="ltr" {...fieldProps('email')} />
      </Field>

      <Field id={`${prefix}-governorate`} label={t('governorate')} error={errors.governorate}>
        <select autoComplete="address-level1" {...fieldProps('governorate')}>
          <option value="">{t('selectGovernorate')}</option>
          {GOVERNORATES.map((item) => (
            <option key={item.value} value={item.value}>
              {item.name[locale]}
            </option>
          ))}
        </select>
      </Field>

      <Field id={`${prefix}-wilayat`} label={t('wilayat')} error={errors.wilayat}>
        <input type="text" autoComplete="address-level2" {...fieldProps('wilayat')} />
      </Field>

      <Field id={`${prefix}-line1`} label={t('addressLine')} error={errors.line1}>
        <input type="text" autoComplete="street-address" {...fieldProps('line1')} />
      </Field>
    </fieldset>
  );
}
