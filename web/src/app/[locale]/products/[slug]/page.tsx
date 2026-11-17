import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import AddToCartButton from '@/components/product/AddToCartButton';
import Price from '@/components/product/Price';
import ProductGallery from '@/components/product/ProductGallery';
import { Link } from '@/i18n/navigation';
import { routing } from '@/i18n/routing';
import { ApiError, apiGet } from '@/lib/api';
import { translate, type ProductDetail, type Resource } from '@/lib/types';

type ProductPageProps = PageProps<'/[locale]/products/[slug]'>;

export default function ProductPage({ params }: ProductPageProps) {
  // The slug is only known at request time, so the whole body streams.
  return (
    <section className="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6">
      <Suspense fallback={<ProductDetailSkeleton />}>
        <ProductDetailView params={params} />
      </Suspense>
    </section>
  );
}

async function fetchProduct(slug: string): Promise<ProductDetail> {
  try {
    const response = await apiGet<Resource<ProductDetail>>(
      `/api/v1/products/${encodeURIComponent(slug)}`,
    );
    return response.data;
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) {
      notFound();
    }
    throw error;
  }
}

async function ProductDetailView({
  params,
}: Pick<ProductPageProps, 'params'>) {
  const { locale, slug } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);

  const [product, t, tNav] = await Promise.all([
    fetchProduct(slug),
    getTranslations('product'),
    getTranslations('nav'),
  ]);

  const name = translate(product.name, locale);
  const summary = translate(product.short_description, locale);
  const description = translate(product.description, locale);
  const inStock = product.available_for_sale;

  return (
    <div className="flex flex-col gap-6">
      <nav aria-label={t('breadcrumbLabel')} className="text-sm">
        <ol className="flex flex-wrap items-center gap-2 text-zinc-600 dark:text-zinc-400">
          <li>
            <Link href="/shop" className="hover:text-brand-green">
              {tNav('shop')}
            </Link>
          </li>
          {product.category && (
            <>
              <li aria-hidden="true">/</li>
              <li>
                <Link
                  href={`/categories/${product.category.slug}`}
                  className="hover:text-brand-green"
                >
                  {translate(product.category.name, locale)}
                </Link>
              </li>
            </>
          )}
          <li aria-hidden="true">/</li>
          <li aria-current="page" className="text-foreground">
            {name}
          </li>
        </ol>
      </nav>

      <div className="grid gap-8 md:grid-cols-2">
        <ProductGallery images={product.images} name={name} locale={locale} />

        <div className="flex flex-col gap-4">
          <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
            {name}
          </h1>
          {summary && (
            <p className="text-zinc-700 dark:text-zinc-300">{summary}</p>
          )}

          <div className="flex flex-wrap items-center gap-3">
            <Price baisa={product.price_baisa} locale={locale} className="text-2xl" />
            <span
              data-testid="stock-state"
              className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${
                inStock
                  ? 'bg-brand-green/10 text-brand-green'
                  : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'
              }`}
            >
              {inStock ? t('inStock') : t('outOfStock')}
            </span>
          </div>

          <AddToCartButton
            productId={product.id}
            slug={product.slug}
            name={name}
            nameTranslations={product.name}
            priceBaisa={product.price_baisa}
            variants={product.variants}
            inStock={inStock}
            locale={locale}
          />

          {product.is_rentable && (
            <div className="flex flex-col gap-2 rounded-lg border border-brand-gold/60 p-4">
              <p className="text-sm text-zinc-700 dark:text-zinc-300">
                {t('rentHint')}
              </p>
              <Link
                href={`/rental/${product.slug}`}
                data-testid="rental-cta"
                className="self-start rounded-md border border-brand-gold px-4 py-2 font-medium text-brand-gold transition-colors hover:bg-brand-gold hover:text-white focus-visible:outline-2 focus-visible:outline-brand-gold"
              >
                {t('rentCta')}
              </Link>
            </div>
          )}

          <dl className="text-sm text-zinc-600 dark:text-zinc-400">
            <div className="flex gap-1">
              <dt>{t('sku')}:</dt>
              <dd className="font-mono">{product.sku}</dd>
            </div>
            {product.weight_grams !== null && (
              <div className="flex gap-1">
                <dt>{t('weight')}:</dt>
                <dd>{t('grams', { grams: product.weight_grams })}</dd>
              </div>
            )}
          </dl>

          {description && (
            <div className="flex flex-col gap-2 border-t border-brand-sand pt-4">
              <h2 className="font-semibold">{t('description')}</h2>
              <p className="whitespace-pre-line text-zinc-700 dark:text-zinc-300">
                {description}
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

function ProductDetailSkeleton() {
  return (
    <div aria-hidden="true" className="grid gap-8 md:grid-cols-2">
      <div className="aspect-square animate-pulse rounded-lg bg-brand-sand/60" />
      <div className="flex flex-col gap-4">
        <div className="h-9 w-2/3 animate-pulse rounded bg-brand-sand/60" />
        <div className="h-5 w-full animate-pulse rounded bg-brand-sand/60" />
        <div className="h-8 w-1/3 animate-pulse rounded bg-brand-sand/60" />
        <div className="h-12 w-full animate-pulse rounded bg-brand-sand/60" />
      </div>
    </div>
  );
}
