import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import CheckoutView from '@/components/cart/CheckoutView';
import { routing } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import { isPaymentMethod, type PaymentMethod } from '@/lib/orders';

/** Enabled payment methods from store settings; COD if settings are unreachable. */
async function paymentMethods(): Promise<PaymentMethod[]> {
  try {
    const { data } = await apiGet<{ data: { payment_methods?: unknown[] } }>(
      'api/v1/settings/public',
    );
    const methods = (data.payment_methods ?? []).filter(isPaymentMethod);
    return methods.length > 0 ? methods : ['cod'];
  } catch {
    return ['cod'];
  }
}

export default async function CheckoutPage({ params }: PageProps<'/[locale]/checkout'>) {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('checkout');
  const methods = await paymentMethods();

  return (
    <section className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6">
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('title')}
      </h1>
      {/* The cart lives in localStorage, so its contents render client-side. */}
      <CheckoutView locale={locale} paymentMethods={methods} />
    </section>
  );
}
