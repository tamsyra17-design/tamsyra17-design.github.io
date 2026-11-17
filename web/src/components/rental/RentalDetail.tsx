'use client';

import { useTranslations } from 'next-intl';
import { useEffect, useState } from 'react';
import type { Locale } from '@/i18n/routing';
import { apiGet, apiPost } from '@/lib/api';
import { formatOmr } from '@/lib/money';
import { translate, type Product, type Resource } from '@/lib/types';
import RentalCheckoutForm from './RentalCheckoutForm';

interface RentalDetailProps {
  slug: string;
  locale: Locale;
}

interface RentalQuote {
  days: number;
  base_fee_baisa: number;
  extra_days: number;
  extra_fee_baisa: number;
  deposit_baisa: number;
  total_baisa: number;
}

export default function RentalDetail({ slug, locale }: RentalDetailProps) {
  const t = useTranslations('rental');
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [product, setProduct] = useState<Product | null>(null);
  const [quote, setQuote] = useState<RentalQuote | null>(null);
  const [availability, setAvailability] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;
    apiGet<Resource<Product>>(`/api/v1/products/${slug}`).then((res) => {
      if (!cancelled) setProduct(res.data);
    });
    return () => {
      cancelled = true;
    };
  }, [slug]);

  const fetchQuote = async (nextFrom: string, nextTo: string) => {
    if (!nextFrom || !nextTo) {
      setQuote(null);
      setAvailability(null);
      return;
    }
    setError(null);
    try {
      const [avail, q] = await Promise.all([
        apiGet<{ available: number }>(
          `/api/v1/rental/products/${slug}/availability`,
          { from: nextFrom, to: nextTo },
        ),
        apiPost<RentalQuote>('/api/v1/rental/quote', {
          product_slug: slug,
          from: nextFrom,
          to: nextTo,
        }),
      ]);
      setAvailability(avail.available);
      setQuote(q);
    } catch {
      setError(t('quoteError'));
      setQuote(null);
    }
  };

  return (
    <div className="flex flex-col gap-6">
      <p className="text-zinc-700 dark:text-zinc-300">
        {product ? translate(product.short_description, locale) : ''}
      </p>

      <div className="grid gap-4 sm:grid-cols-2">
        <label className="flex flex-col gap-1 text-sm font-medium">
          {t('from')}
          <input
            type="date"
            data-testid="rental-from"
            value={from}
            onChange={(event) => {
              setFrom(event.target.value);
              fetchQuote(event.target.value, to);
            }}
            className="rounded-md border border-brand-sand bg-background px-3 py-2"
          />
        </label>
        <label className="flex flex-col gap-1 text-sm font-medium">
          {t('to')}
          <input
            type="date"
            data-testid="rental-to"
            value={to}
            onChange={(event) => {
              setTo(event.target.value);
              fetchQuote(from, event.target.value);
            }}
            className="rounded-md border border-brand-sand bg-background px-3 py-2"
          />
        </label>
      </div>

      {availability !== null && (
        <p className="text-sm text-zinc-600 dark:text-zinc-400">
          {availability > 0
            ? t('available', { count: availability })
            : t('unavailable')}
        </p>
      )}

      {error && <p className="text-sm text-red-600">{error}</p>}

      {quote && (
        <div
          data-testid="rental-total"
          className="rounded-lg border border-brand-sand p-4"
        >
          <p>
            {t('days')}: {quote.days}
          </p>
          <p>
            {t('baseFee')}: {formatOmr(quote.base_fee_baisa, locale)}
          </p>
          <p>
            {t('extraDays')}: {quote.extra_days} ({formatOmr(quote.extra_fee_baisa, locale)})
          </p>
          <p>
            {t('deposit')}: {formatOmr(quote.deposit_baisa, locale)}
          </p>
          <p className="font-semibold">
            {t('total')}: {formatOmr(quote.total_baisa, locale)}
          </p>
        </div>
      )}

      <RentalCheckoutForm
        slug={slug}
        from={from}
        to={to}
        locale={locale}
      />
    </div>
  );
}
