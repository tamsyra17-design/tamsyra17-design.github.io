import { hasLocale } from 'next-intl';
import { setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import { routing } from '@/i18n/routing';
import { apiGet } from '@/lib/api';
import { translate, type Translations } from '@/lib/types';

interface CmsPage {
  slug: string;
  title: Translations;
  body: Translations;
}

type CmsPageProps = PageProps<'/[locale]/pages/[slug]'>;

export default function CmsPage({ params }: CmsPageProps) {
  // The slug is only known at request time, so the body streams.
  return (
    <section className="mx-auto flex w-full max-w-3xl flex-col gap-6 px-4 py-10 sm:px-6">
      <Suspense fallback={null}>
        <CmsPageView params={params} />
      </Suspense>
    </section>
  );
}

async function CmsPageView({ params }: Pick<CmsPageProps, 'params'>) {
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
    <>
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {translate(page.title, locale)}
      </h1>
      <div
        className="prose prose-zinc dark:prose-invert"
        dangerouslySetInnerHTML={{ __html: translate(page.body, locale) }}
      />
    </>
  );
}
