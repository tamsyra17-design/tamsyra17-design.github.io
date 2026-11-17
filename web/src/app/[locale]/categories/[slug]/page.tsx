import { hasLocale } from 'next-intl';
import { setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import CategoryNav from '@/components/product/CategoryNav';
import ProductGrid, {
  ProductGridSkeleton,
} from '@/components/product/ProductGrid';
import { routing } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import {
  translate,
  type Category,
  type Collection,
  type Paginated,
  type Product,
} from '@/lib/types';

type CategoryPageProps = PageProps<'/[locale]/categories/[slug]'>;

export default function CategoryPage({ params }: CategoryPageProps) {
  // The slug is only known at request time, so the whole body streams.
  return (
    <section className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6">
      <Suspense fallback={<ProductGridSkeleton />}>
        <CategoryCatalog params={params} />
      </Suspense>
    </section>
  );
}

async function CategoryCatalog({
  params,
}: Pick<CategoryPageProps, 'params'>) {
  const { locale, slug } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);

  const [categories, products] = await Promise.all([
    apiGet<Collection<Category>>('/api/v1/categories'),
    apiGet<Paginated<Product>>('/api/v1/products', { category: slug }),
  ]);

  const category = categories.data.find((item) => item.slug === slug);

  if (!category) {
    notFound();
  }

  return (
    <>
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {translate(category.name, locale)}
      </h1>
      <CategoryNav
        categories={categories.data}
        locale={locale}
        activeSlug={slug}
      />
      <ProductGrid products={products.data} locale={locale} />
    </>
  );
}
