'use client';

import { zodResolver } from '@hookform/resolvers/zod';
import { useTranslations } from 'next-intl';
import { useId, useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { fieldClass } from '@/components/checkout/AddressForm';
import { lookupOrder, type Order } from '@/lib/orders';

interface OrderLookupFormProps {
  /** Prefills (and locks) the order number, e.g. on `/orders/{number}`. */
  number?: string;
  onFound: (order: Order) => void;
}

/** Guest tracking form: order number + the phone used at checkout. */
export default function OrderLookupForm({ number, onFound }: OrderLookupFormProps) {
  const t = useTranslations('track');
  const prefix = useId();
  const [result, setResult] = useState<'notFound' | 'failed' | null>(null);

  const schema = useMemo(
    () =>
      z.object({
        number: z.string().trim().min(1, { error: t('errors.number') }).max(64),
        phone: z
          .string()
          .trim()
          .regex(/^\+?[0-9 ]{7,20}$/, { error: t('errors.phone') }),
      }),
    [t],
  );

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: { number: number ?? '', phone: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setResult(null);

    try {
      const order = await lookupOrder(values.number.toUpperCase(), values.phone);
      if (order) {
        onFound(order);
      } else {
        setResult('notFound');
      }
    } catch {
      setResult('failed');
    }
  });

  return (
    <form noValidate onSubmit={onSubmit} className="flex max-w-md flex-col gap-4">
      <div className="flex flex-col gap-1 text-sm">
        <label htmlFor={`${prefix}-number`} className="font-medium">
          {t('number')}
        </label>
        <input
          id={`${prefix}-number`}
          type="text"
          dir="ltr"
          placeholder="ORD-…"
          readOnly={number !== undefined}
          aria-invalid={errors.number ? true : undefined}
          className={fieldClass}
          {...register('number')}
        />
        {errors.number && (
          <p className="text-red-700 dark:text-red-400">{errors.number.message}</p>
        )}
      </div>
      <div className="flex flex-col gap-1 text-sm">
        <label htmlFor={`${prefix}-phone`} className="font-medium">
          {t('phone')}
        </label>
        <input
          id={`${prefix}-phone`}
          type="tel"
          dir="ltr"
          autoComplete="tel"
          aria-invalid={errors.phone ? true : undefined}
          className={fieldClass}
          {...register('phone')}
        />
        {errors.phone && (
          <p className="text-red-700 dark:text-red-400">{errors.phone.message}</p>
        )}
      </div>
      {result && (
        <p
          role="alert"
          data-testid="track-error"
          className="text-sm text-red-700 dark:text-red-400"
        >
          {t(result)}
        </p>
      )}
      <button
        type="submit"
        data-testid="track-submit"
        disabled={isSubmitting}
        className="rounded-md bg-brand-green px-5 py-3 font-semibold text-white hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-gold disabled:cursor-not-allowed disabled:opacity-50"
      >
        {t('submit')}
      </button>
    </form>
  );
}
