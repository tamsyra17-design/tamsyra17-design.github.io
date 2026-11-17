import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import OrderView from '@/components/orders/OrderView';
import { routing } from '@/i18n/routing';

type OrderPageProps = PageProps<'/[locale]/orders/[number]'>;

export default function OrderPage({ params }: OrderPageProps) {
  // The order number is only known at request time, so the body streams.
  return (
    <section className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6">
      <Suspense fallback={null}>
        <OrderPageView params={params} />
      </Suspense>
    </section>
  );
}

async function OrderPageView({ params }: Pick<OrderPageProps, 'params'>) {
  const { locale, number } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('order');

  return (
    <>
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('title')}
      </h1>
      {/* Reading an order needs the shopper's phone, so it loads client-side. */}
      <OrderView number={decodeURIComponent(number)} locale={locale} />
    </>
  );
}
