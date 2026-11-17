import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import type { Category } from '@/lib/types';

interface CategoryStripProps {
  categories: Category[];
  locale: Locale;
}

export default function CategoryStrip({ categories, locale }: CategoryStripProps) {
  const t = useTranslations('catalog');

  return (
    <nav data-testid="category-strip" aria-label={t('categoriesLabel')} className="flex gap-3 overflow-x-auto pb-2">
      {categories.map((category) => (
        <Link
          key={category.id}
          href={`/categories/${category.slug}`}
          className="whitespace-nowrap rounded-full border border-brand-sand px-4 py-1.5 text-sm hover:bg-brand-sand/40"
        >
          {category.name[locale] ?? category.name.en}
        </Link>
      ))}
    </nav>
  );
}
