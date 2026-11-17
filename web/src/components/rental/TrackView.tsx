'use client';

import { useTranslations } from 'next-intl';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import type { Locale } from '@/i18n/routing';

export default function TrackView({ locale }: { locale: Locale }) {
  const t = useTranslations('rental');
  const router = useRouter();
  const [number, setNumber] = useState('');
  const [phone, setPhone] = useState('');

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    if (!number.trim() || !phone.trim()) return;
    router.push(
      `/${locale}/rentals/${encodeURIComponent(number.trim())}?phone=${encodeURIComponent(phone.trim())}`,
    );
  };

  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-3">
      <label className="flex flex-col gap-1 text-sm font-medium">
        {t('rentalNumber')}
        <input
          type="text"
          value={number}
          onChange={(e) => setNumber(e.target.value)}
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
      <button
        type="submit"
        className="self-start rounded-md bg-brand-green px-6 py-3 font-semibold text-white"
      >
        {t('track')}
      </button>
    </form>
  );
}
