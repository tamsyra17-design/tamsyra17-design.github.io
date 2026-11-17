'use client';

import { useSearchParams } from 'next/navigation';
import { useTranslations } from 'next-intl';
import { useEffect, useState } from 'react';
import type { Locale } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import { formatOmr } from '@/lib/money';
import { translate } from '@/lib/types';

interface RentalConfirmationProps {
  number: string;
  locale: Locale;
}

interface RentalData {
  number: string;
  status: string;
  payment_status: string;
  start_date: string;
  end_date: string;
  days: number;
  base_fee_baisa: number;
  extra_fee_baisa: number;
  deposit_baisa: number;
  total_baisa: number;
  items: { name: { ar: string; en: string }; line_total_baisa: number }[];
}

export default function RentalConfirmation({
  number,
  locale,
}: RentalConfirmationProps) {
  const t = useTranslations('rental');
  const searchParams = useSearchParams();
  const phone = searchParams.get('phone') ?? '';
  const [rental, setRental] = useState<RentalData | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!phone) return;
    let cancelled = false;
    apiGet<{ data: RentalData }>(`/api/v1/rentals/${number}`, { phone })
      .then((res) => {
        if (!cancelled) setRental(res.data);
      })
      .catch(() => {
        if (!cancelled) setError(t('notFound'));
      });
    return () => {
      cancelled = true;
    };
  }, [number, phone, t]);

  if (!phone) {
    return <p className="text-sm text-zinc-600">{t('needPhone')}</p>;
  }
  if (error) {
    return <p className="text-sm text-red-600">{error}</p>;
  }
  if (!rental) {
    return <p className="text-sm text-zinc-600">{t('loading')}</p>;
  }

  return (
    <div className="flex flex-col gap-4">
      <p data-testid="rental-number" className="text-2xl font-bold">
        {rental.number}
      </p>
      <p>
        {t('status')}: {rental.status}
      </p>
      <p>
        {t('deposit')}: {formatOmr(rental.deposit_baisa, locale)} ({rental.payment_status})
      </p>
      <p className="font-semibold">
        {t('total')}: {formatOmr(rental.total_baisa, locale)}
      </p>
      <p>
        {rental.start_date} → {rental.end_date} ({rental.days} {t('days')})
      </p>
      <ul className="flex flex-col gap-1">
        {rental.items.map((item, i) => (
          <li key={i}>
            {translate(item.name, locale)} — {formatOmr(item.line_total_baisa, locale)}
          </li>
        ))}
      </ul>
    </div>
  );
}
