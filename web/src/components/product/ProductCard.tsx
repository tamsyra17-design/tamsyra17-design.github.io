import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { translate, type Product } from '@/lib/types';
import Price from './Price';
import TiltCard from './TiltCard';

interface ProductCardProps {
  product: Product;
  locale: Locale;
}

export default function ProductCard({ product, locale }: ProductCardProps) {
  const t = useTranslations('catalog');
  const name = translate(product.name, locale);
  const summary = translate(product.short_description, locale);

  return (
    <TiltCard
      data-testid="product-card"
      className="group flex w-full flex-col overflow-hidden rounded-xl border border-brand-border bg-brand-surface shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg"
    >
      <Link
        href={`/products/${product.slug}`}
        className="flex flex-1 flex-col focus-visible:outline-2 focus-visible:outline-brand-gold"
      >
        <div className="relative aspect-[4/5] overflow-hidden bg-brand-sand">
          <div
            aria-hidden="true"
            className="flex h-full w-full items-center justify-center text-6xl font-bold text-brand-green/20"
          >
            {name.charAt(0)}
          </div>
          {product.is_rentable && (
            <span className="absolute top-3 start-3 rounded-full bg-brand-gold px-2.5 py-1 text-xs font-semibold text-white shadow-sm">
              {t('rentable')}
            </span>
          )}
          {product.is_featured && (
            <span className="absolute top-3 end-3 rounded-full bg-brand-green px-2.5 py-1 text-xs font-semibold text-white shadow-sm">
              Featured
            </span>
          )}
        </div>

        <div className="flex flex-1 flex-col gap-2 p-4">
          <h3 className="font-semibold text-foreground line-clamp-1 group-hover:text-brand-green dark:group-hover:text-brand-gold">
            {name}
          </h3>
          {summary && (
            <p className="line-clamp-2 text-sm text-brand-muted">{summary}</p>
          )}
          <div className="mt-auto flex items-center justify-between gap-2 pt-3">
            <Price baisa={product.price_baisa} locale={locale} className="text-lg" />
            {!product.available_for_sale && (
              <span className="text-xs font-medium text-brand-muted">
                {t('outOfStock')}
              </span>
            )}
          </div>
        </div>
      </Link>
    </TiltCard>
  );
}
