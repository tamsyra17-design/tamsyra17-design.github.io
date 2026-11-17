'use client';

import { useTranslations } from 'next-intl';
import Price from '@/components/product/Price';
import type { Locale } from '@/i18n/routing';
import { GOVERNORATES, type Order } from '@/lib/orders';
import { translate } from '@/lib/types';

/** Fulfilment steps shown as a timeline. Other statuses (e.g. cancelled) show as a notice. */
const STEPS = ['pending', 'processing', 'shipped', 'delivered'] as const;

const BANK_FIELDS = [
  'bank_name',
  'account_name',
  'account_number',
  'iban',
  'swift',
  'reference',
] as const;

function itemName(item: Order['items'][number], locale: Locale): string {
  const product = translate(item.name.product, locale);
  const variant = item.name.variant ? translate(item.name.variant, locale) : '';
  return variant ? `${product} — ${variant}` : product;
}

function StatusTimeline({ status }: { status: string }) {
  const t = useTranslations('order');
  const current = STEPS.indexOf(status as (typeof STEPS)[number]);

  if (current === -1) {
    return (
      <p
        data-testid="order-status"
        data-status={status}
        className="rounded-md bg-brand-sand/40 px-4 py-3 font-medium"
      >
        {t('statusLabel')}: {t.has(`status.${status}`) ? t(`status.${status}`) : status}
      </p>
    );
  }

  return (
    <ol
      aria-label={t('timeline')}
      data-testid="order-status"
      data-status={status}
      className="grid grid-cols-4 gap-2"
    >
      {STEPS.map((step, index) => {
        const done = index <= current;
        return (
          <li
            key={step}
            aria-current={index === current ? 'step' : undefined}
            className="flex flex-col items-center gap-2 text-center text-xs sm:text-sm"
          >
            <span
              aria-hidden="true"
              className={`flex size-8 items-center justify-center rounded-full border-2 font-semibold ${
                done
                  ? 'border-brand-green bg-brand-green text-white'
                  : 'border-brand-sand text-zinc-500'
              }`}
            >
              {index + 1}
            </span>
            <span
              className={
                index === current ? 'font-semibold' : done ? undefined : 'text-zinc-500'
              }
            >
              {t(`status.${step}`)}
            </span>
          </li>
        );
      })}
    </ol>
  );
}

/** Order confirmation / tracking body: status, items, totals, address, payment. */
export default function OrderDetail({ order, locale }: { order: Order; locale: Locale }) {
  const t = useTranslations('order');
  const tCheckout = useTranslations('checkout');
  const governorate =
    GOVERNORATES.find((item) => item.value === order.address.governorate)?.name[locale] ??
    order.address.governorate;
  const instructions = order.payment?.instructions;
  const placedAt = order.placed_at
    ? new Intl.DateTimeFormat(locale === 'ar' ? 'ar-OM' : 'en-OM', {
        dateStyle: 'medium',
        timeStyle: 'short',
      }).format(new Date(order.placed_at))
    : null;
  const methodKey = `methods.${order.payment_method}.label`;
  const paymentStatusKey = `paymentStatus.${order.payment_status}`;

  return (
    <div className="flex flex-col gap-8">
      <div className="flex flex-col gap-1">
        <p className="text-sm text-zinc-600 dark:text-zinc-400">{t('number')}</p>
        <p
          data-testid="order-number"
          dir="ltr"
          className="text-start text-2xl font-bold tracking-wide text-brand-green dark:text-brand-sand"
        >
          {order.number}
        </p>
        {placedAt && (
          <p className="text-sm text-zinc-600 dark:text-zinc-400">
            {t('placedAt', { date: placedAt })}
          </p>
        )}
      </div>

      <StatusTimeline status={order.status} />

      <div className="grid gap-8 lg:grid-cols-[1fr_20rem]">
        <section aria-labelledby="order-items-heading" className="flex flex-col gap-3">
          <h2 id="order-items-heading" className="text-lg font-semibold">
            {t('items')}
          </h2>
          <ul className="flex flex-col">
            {order.items.map((item, index) => (
              <li
                key={`${item.sku}:${index}`}
                data-testid="order-item"
                className="flex justify-between gap-4 border-b border-brand-sand py-3"
              >
                <span>
                  {itemName(item, locale)} × {item.qty}
                </span>
                <Price baisa={item.line_total_baisa} locale={locale} className="font-normal" />
              </li>
            ))}
          </ul>
          <dl className="flex flex-col gap-2 text-sm">
            <div className="flex justify-between gap-4">
              <dt>{t('subtotal')}</dt>
              <dd>
                <Price baisa={order.subtotal_baisa} locale={locale} className="font-normal" />
              </dd>
            </div>
            <div className="flex justify-between gap-4">
              <dt>{t('shipping')}</dt>
              <dd>
                <Price baisa={order.shipping_baisa} locale={locale} className="font-normal" />
              </dd>
            </div>
            <div className="flex justify-between gap-4 text-base font-semibold">
              <dt>{t('total')}</dt>
              <dd data-testid="order-total">
                <Price baisa={order.total_baisa} locale={locale} />
              </dd>
            </div>
          </dl>
        </section>

        <div className="flex flex-col gap-4">
          <section
            aria-labelledby="order-address-heading"
            className="flex flex-col gap-1 rounded-lg border border-brand-sand p-5 text-sm"
          >
            <h2 id="order-address-heading" className="mb-1 text-lg font-semibold">
              {t('shippingAddress')}
            </h2>
            <p className="font-medium">{order.customer.name}</p>
            <p dir="ltr" className="text-start">
              {order.customer.phone}
            </p>
            <p>{order.address.line1}</p>
            {order.address.line2 && <p>{order.address.line2}</p>}
            <p>{t('place', { wilayat: order.address.wilayat, governorate })}</p>
          </section>

          <section
            aria-labelledby="order-payment-heading"
            className="flex flex-col gap-2 rounded-lg border border-brand-sand p-5 text-sm"
          >
            <h2 id="order-payment-heading" className="mb-1 text-lg font-semibold">
              {t('payment')}
            </h2>
            <p>
              {tCheckout.has(methodKey) ? tCheckout(methodKey) : order.payment_method}
              {' · '}
              {t.has(paymentStatusKey) ? t(paymentStatusKey) : order.payment_status}
            </p>
            {instructions && (
              <dl data-testid="bank-instructions" className="flex flex-col gap-1">
                {BANK_FIELDS.map((key) =>
                  instructions[key] ? (
                    <div key={key} className="flex justify-between gap-3">
                      <dt className="text-zinc-600 dark:text-zinc-400">{t(`bank.${key}`)}</dt>
                      <dd dir="ltr" className="font-mono">
                        {String(instructions[key])}
                      </dd>
                    </div>
                  ) : null,
                )}
              </dl>
            )}
          </section>
        </div>
      </div>
    </div>
  );
}
