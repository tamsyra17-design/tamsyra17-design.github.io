import type { MetadataRoute } from 'next';
import { routing } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import type { Collection, Product, Category } from '@/lib/types';

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000';

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const entries: MetadataRoute.Sitemap = [];

  for (const locale of routing.locales) {
    entries.push({
      url: `${SITE_URL}/${locale}`,
      lastModified: new Date(),
      changeFrequency: 'weekly',
      priority: 1,
    });
  }

  try {
    const [products, categories] = await Promise.all([
      apiGet<Collection<Product>>('/api/v1/products', { per_page: 1000 }),
      apiGet<Collection<Category>>('/api/v1/categories'),
    ]);

    for (const locale of routing.locales) {
      for (const product of products.data) {
        entries.push({
          url: `${SITE_URL}/${locale}/products/${product.slug}`,
          lastModified: new Date(),
          changeFrequency: 'weekly',
          priority: 0.8,
        });
      }
      for (const category of categories.data) {
        entries.push({
          url: `${SITE_URL}/${locale}/categories/${category.slug}`,
          lastModified: new Date(),
          changeFrequency: 'weekly',
          priority: 0.7,
        });
      }
    }
  } catch {
    // Sitemap generation must never break the build if the API is down.
  }

  return entries;
}
