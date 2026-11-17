'use client';

import { useTranslations } from 'next-intl';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import type { Locale } from '@/i18n/routing';
import { apiPost } from '@/lib/api';

interface RentalCheckoutFormProps {
  slug: string;
  from: string;
  to: string;
  locale: Locale;
}

export default function RentalCheckoutForm({
  slug,
  from,
  to,
  locale,
}: RentalCheckoutFormProps) {
  const t = useTranslations('rental');
  const router = useRouter();
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const canSubmit = from && to && name.trim() && phone.trim();

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!canSubmit) return;
    setSubmitting(true);
    setError(null);
    try {
      const res = await apiPost<{ data: { number: string } }>(
        '/api/v1/rentals',
        {
          product_slug: slug,
          from,
          to,
          customer: { name, phone, email: email || null },
        },
      );
      router.push(
        `/${locale}/rentals/${res.data.number}?phone=${encodeURIComponent(phone)}`,
      );
    } catch {
      setError(t('bookError'));
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-3">
      <label className="flex flex-col gap-1 text-sm font-medium">
        {t('name')}
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="rounded-md border border-brand-sand bg-background px-3 py-2"
          required
        />
      </label>
      <label className="flex flex-col gap-1 text-sm font-medium">
        {t('phone')}
        <input
          type="tel"
          value={phone}
          onChange={(e) => setPhone(e.target.value)}
          className="rounded-md border border-brand-sand bg-background px-3 py-2"
          required
        />
      </label>
      <label className="flex flex-col gap-1 text-sm font-medium">
        {t('email')}
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className="rounded-md border border-brand-sand bg-background px-3 py-2"
        />
      </label>
      {error && <p className="text-sm text-red-600">{error}</p>}
      <button
        type="submit"
        data-testid="book-rental"
        disabled={!canSubmit || submitting}
        className="self-start rounded-md bg-brand-green px-6 py-3 font-semibold text-white disabled:opacity-50"
      >
        {t('bookRental')}
      </button>
    </form>
  );
}
