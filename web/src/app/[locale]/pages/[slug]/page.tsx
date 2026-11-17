import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { routing } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import { translate, type Translations } from '@/lib/types';

interface CmsPage {
  slug: string;
  title: Translations;
  body: Translations;
}

type CmsPageProps = PageProps<'/[locale]/pages/[slug]'>;

export default async function CmsPage({ params }: CmsPageProps) {
  const { locale, slug } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);

  let page: CmsPage;
  try {
    const res = await apiGet<{ data: CmsPage }>(`/api/v1/pages/${slug}`);
    page = res.data;
  } catch {
    notFound();
  }

  if (!page) notFound();

  return (
    <section className="mx-auto flex w-full max-w-3xl flex-col gap-6 px-4 py-10 sm:px-6">
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {translate(page.title, locale)}
      </h1>
      <div
        className="prose prose-zinc dark:prose-invert"
        dangerouslySetInnerHTML={{ __html: translate(page.body, locale) }}
      />
    </section>
  );
}
