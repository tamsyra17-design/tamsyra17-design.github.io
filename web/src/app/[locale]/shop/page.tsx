import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import CategoryNav from '@/components/product/CategoryNav';
import ProductGrid, {
  ProductGridSkeleton,
} from '@/components/product/ProductGrid';
import { routing, type Locale } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import type { Category, Collection, Paginated, Product } from '@/lib/types';

export default async function ShopPage({
  params,
}: PageProps<'/[locale]/shop'>) {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('catalog');

  return (
    <section className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6">
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('shopTitle')}
      </h1>

      {/* Catalog data is fetched fresh per request and streamed in. */}
      <Suspense fallback={<ProductGridSkeleton />}>
        <ShopCatalog locale={locale} />
      </Suspense>
    </section>
  );
}

async function ShopCatalog({ locale }: { locale: Locale }) {
  const [categories, products] = await Promise.all([
    apiGet<Collection<Category>>('/api/v1/categories'),
    apiGet<Paginated<Product>>('/api/v1/products'),
  ]);

  return (
    <>
      <CategoryNav categories={categories.data} locale={locale} />
      <ProductGrid products={products.data} locale={locale} />
    </>
  );
}
