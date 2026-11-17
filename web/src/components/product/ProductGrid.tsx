import { useTranslations } from 'next-intl';
import type { Locale } from '@/i18n/routing';
import type { Product } from '@/lib/types';
import ProductCard from './ProductCard';

interface ProductGridProps {
  products: Product[];
  locale: Locale;
}

export default function ProductGrid({ products, locale }: ProductGridProps) {
  const t = useTranslations('catalog');

  if (products.length === 0) {
    return (
      <p className="py-16 text-center text-zinc-600 dark:text-zinc-400">
        {t('empty')}
      </p>
    );
  }

  return (
    <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      {products.map((product) => (
        <li key={product.id} className="flex">
          <ProductCard product={product} locale={locale} />
        </li>
      ))}
    </ul>
  );
}

export function ProductGridSkeleton() {
  return (
    <ul
      aria-hidden="true"
      className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
    >
      {Array.from({ length: 8 }, (_, i) => (
        <li
          key={i}
          className="aspect-[3/4] animate-pulse rounded-lg bg-brand-sand/60"
        />
      ))}
    </ul>
  );
}
