import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import CartView from '@/components/cart/CartView';
import { routing } from '@/i18n/routing';

export default async function CartPage({ params }: PageProps<'/[locale]/cart'>) {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('cart');

  return (
    <section className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6">
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('title')}
      </h1>
      {/* The cart lives in localStorage, so its contents render client-side. */}
      <CartView locale={locale} />
    </section>
  );
}
