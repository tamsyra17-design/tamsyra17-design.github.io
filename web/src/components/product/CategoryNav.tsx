import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import type { Locale } from '@/i18n/routing';
import { translate, type Category } from '@/lib/types';

interface CategoryNavProps {
  categories: Category[];
  locale: Locale;
  activeSlug?: string;
}

const chip =
  'rounded-full border px-3 py-1 text-sm transition-colors focus-visible:outline-2 focus-visible:outline-brand-gold';
const idle =
  'border-brand-sand text-foreground/80 hover:border-brand-green hover:text-brand-green';
const active = 'border-brand-green bg-brand-green text-white';

export default function CategoryNav({
  categories,
  locale,
  activeSlug,
}: CategoryNavProps) {
  const t = useTranslations('catalog');

  if (categories.length === 0) {
    return null;
  }

  return (
    <nav aria-label={t('categoriesLabel')}>
      <ul className="flex flex-wrap gap-2">
        <li>
          <Link
            href="/shop"
            aria-current={activeSlug ? undefined : 'page'}
            className={`${chip} ${activeSlug ? idle : active}`}
          >
            {t('all')}
          </Link>
        </li>
        {categories.map((category) => (
          <li key={category.id}>
            <Link
              href={`/categories/${category.slug}`}
              aria-current={category.slug === activeSlug ? 'page' : undefined}
              className={`${chip} ${category.slug === activeSlug ? active : idle}`}
            >
              {translate(category.name, locale)}
            </Link>
          </li>
        ))}
      </ul>
    </nav>
  );
}
