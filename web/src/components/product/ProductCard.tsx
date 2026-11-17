import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { translate, type Product } from '@/lib/types';
import Price from './Price';

interface ProductCardProps {
  product: Product;
  locale: Locale;
}

export default function ProductCard({ product, locale }: ProductCardProps) {
  const t = useTranslations('catalog');
  const name = translate(product.name, locale);
  const summary = translate(product.short_description, locale);

  return (
    <article
      data-testid="product-card"
      className="group flex w-full flex-col overflow-hidden rounded-lg border border-brand-sand bg-white/60 transition-shadow hover:shadow-md dark:bg-white/5"
    >
      <Link
        href={`/products/${product.slug}`}
        className="flex flex-1 flex-col focus-visible:outline-2 focus-visible:outline-brand-gold"
      >
        {/* The list API carries no image yet; show a branded placeholder. */}
        <div
          aria-hidden="true"
          className="relative flex aspect-square items-center justify-center bg-brand-sand text-4xl font-semibold text-brand-green/40"
        >
          {name.charAt(0)}
          {product.is_rentable && (
            <span className="absolute top-2 start-2 rounded-full bg-brand-gold px-2 py-0.5 text-xs font-medium text-white">
              {t('rentable')}
            </span>
          )}
        </div>

        <div className="flex flex-1 flex-col gap-1 p-4">
          <h3 className="font-medium text-foreground group-hover:text-brand-green dark:group-hover:text-brand-gold">
            {name}
          </h3>
          {summary && (
            <p className="line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
              {summary}
            </p>
          )}
          <div className="mt-auto flex items-center justify-between gap-2 pt-2">
            <Price baisa={product.price_baisa} locale={locale} />
            {!product.available_for_sale && (
              <span className="text-xs text-zinc-500">{t('outOfStock')}</span>
            )}
          </div>
        </div>
      </Link>
    </article>
  );
}
