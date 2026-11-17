import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import HeroBand from '@/components/home/HeroBand';
import CategoryStrip from '@/components/home/CategoryStrip';
import { routing } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import { translate, type Category, type Collection, type Product } from '@/lib/types';

type HomePageProps = PageProps<'/[locale]'>;

export default async function HomePage({ params }: HomePageProps) {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);

  return (
    <>
      <HeroBand />
      <Suspense fallback={null}>
        <HomeCatalog locale={locale} />
      </Suspense>
    </>
  );
}

async function HomeCatalog({ locale }: { locale: (typeof routing.locales)[number] }) {
  const t = await getTranslations('home');
  let featured: Product[] = [];
  let categories: Category[] = [];
  try {
    const [productsRes, categoriesRes] = await Promise.all([
      apiGet<Collection<Product>>('/api/v1/products', { per_page: 8 }),
      apiGet<Collection<Category>>('/api/v1/categories'),
    ]);
    featured = productsRes.data;
    categories = categoriesRes.data;
  } catch {
    featured = [];
    categories = [];
  }

  return (
    <>
      <section className="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6">
        <CategoryStrip categories={categories} locale={locale} />
      </section>
      <section className="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6" data-testid="featured-pieces">
        <h2 className="mb-4 text-2xl font-bold text-brand-green dark:text-brand-sand">
          {t('featured')}
        </h2>
        <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
          {featured.map((product) => (
            <li key={product.id} className="flex">
              <a
                href={`/${locale}/products/${product.slug}`}
                data-testid="product-card"
                className="flex w-full flex-col rounded-lg border border-brand-sand p-4"
              >
                {translate(product.name, locale)}
              </a>
            </li>
          ))}
        </ul>
      </section>
    </>
  );
}
