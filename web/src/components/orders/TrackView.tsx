'use client';

import { useRouter } from '@/i18n/navigation';
import { rememberOrder } from '@/lib/orders';
import OrderLookupForm from './OrderLookupForm';

/** `/track`: look up an order, then open its page. */
export default function TrackView() {
  const router = useRouter();

  return (
    <OrderLookupForm
      onFound={(order) => {
        rememberOrder(order);
        router.push(`/orders/${encodeURIComponent(order.number)}`);
      }}
    />
  );
}
