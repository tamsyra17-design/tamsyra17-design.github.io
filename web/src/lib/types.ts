import type { Locale } from '@/i18n/routing';

/** A bilingual value as emitted by the API: always an {ar, en} pair. */
export type Translations = Record<Locale, string | null>;

export interface Category {
  id: number;
  slug: string;
  name: Translations;
  parent_id: number | null;
  is_rental_only: boolean;
}

/** List shape from `GET /api/v1/products` (ProductResource). */
export interface Product {
  id: number;
  slug: string;
  sku: string;
  name: Translations;
  short_description: Translations;
  price_baisa: number;
  category_id: number | null;
  is_rentable: boolean;
  is_featured: boolean;
  available_for_sale: boolean;
  available_for_rental: boolean;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

export interface Paginated<T> {
  data: T[];
  meta: PaginationMeta;
}

export interface Collection<T> {
  data: T[];
}

/** Pick the value for `locale`, falling back to the other language. */
export function translate(value: Translations, locale: Locale): string {
  return value[locale] ?? value[locale === 'ar' ? 'en' : 'ar'] ?? '';
}
