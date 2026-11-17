'use client';

import { useTranslations } from 'next-intl';
import { useMemo, useState, useSyncExternalStore } from 'react';
import type { Locale } from '@/i18n/routing';
import { recallOrder, type Order } from '@/lib/orders';
import OrderDetail from './OrderDetail';
import OrderLookupForm from './OrderLookupForm';

const subscribeNoop = () => () => {};

/**
 * Order page body. Shows the order handed over by checkout or the tracking
 * form (sessionStorage); otherwise asks for the phone before fetching it.
 */
export default function OrderView({ number, locale }: { number: string; locale: Locale }) {
  const t = useTranslations('order');
  const [looked, setLooked] = useState<Order | null>(null);
  // sessionStorage exists only in the browser; server and first paint report
  // "not on client" so hydration matches.
  const onClient = useSyncExternalStore(
    subscribeNoop,
    () => true,
    () => false,
  );
  const stored = useMemo(() => (onClient ? recallOrder(number) : null), [onClient, number]);
  const order = looked ?? stored;

  if (!onClient) {
    return null;
  }

  if (order) {
    return (
      <div className="flex flex-col gap-6">
        <p
          role="status"
          className="rounded-md bg-brand-green/10 px-4 py-3 text-brand-green dark:text-brand-sand"
        >
          {t('thanks')}
        </p>
        <OrderDetail order={order} locale={locale} />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <p>{t('verify')}</p>
      <OrderLookupForm number={number} onFound={setLooked} />
    </div>
  );
}
