import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import { Link } from '@/i18n/navigation';
import { routing } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import { translate, type Collection, type Product } from '@/lib/types';

export default async function RentalPage({ params }: PageProps<'/[locale]/rental'>) {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('rental');

  return (
    <section className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6">
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('title')}
      </h1>
      <Suspense fallback={null}>
        <RentalList locale={locale} />
      </Suspense>
    </section>
  );
}

async function RentalList({ locale }: { locale: (typeof routing.locales)[number] }) {
  const t = await getTranslations('rental');
  const products = await apiGet<Collection<Product>>('/api/v1/products', {
    rentable: 1,
  });

  if (products.data.length === 0) {
    return (
      <p className="py-16 text-center text-zinc-600 dark:text-zinc-400">
        {t('empty')}
      </p>
    );
  }

  return (
    <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      {products.data.map((product) => (
        <li key={product.id} className="flex">
          <Link
            href={`/rental/${product.slug}`}
            data-testid="rental-card"
            className="flex w-full flex-col overflow-hidden rounded-lg border border-brand-sand bg-white/60 transition-shadow hover:shadow-md dark:bg-white/5"
          >
            <div
              aria-hidden="true"
              className="relative flex aspect-square items-center justify-center bg-brand-sand text-4xl font-semibold text-brand-green/40"
            >
              {translate(product.name, locale).charAt(0)}
              <span className="absolute top-2 start-2 rounded-full bg-brand-gold px-2 py-0.5 text-xs font-medium text-white">
                {t('forRent')}
              </span>
            </div>
            <div className="flex flex-1 flex-col gap-1 p-4">
              <h3 className="font-medium text-foreground">
                {translate(product.name, locale)}
              </h3>
              <p className="text-sm font-semibold text-brand-green">
                {translate(product.short_description, locale)}
              </p>
            </div>
          </Link>
        </li>
      ))}
    </ul>
  );
}
