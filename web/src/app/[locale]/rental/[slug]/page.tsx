import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import RentalDetail from '@/components/rental/RentalDetail';
import { routing } from '@/i18n/routing';

type RentalPageProps = PageProps<'/[locale]/rental/[slug]'>;

export default function RentalDetailPage({ params }: RentalPageProps) {
  // The slug is only known at request time, so the body streams behind
  // Suspense (awaiting it outside a boundary fails prerendering).
  return (
    <section className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6">
      <Suspense fallback={<div aria-hidden="true" className="h-9 w-1/2 animate-pulse rounded bg-brand-sand/60" />}>
        <RentalDetailView params={params} />
      </Suspense>
    </section>
  );
}

async function RentalDetailView({ params }: Pick<RentalPageProps, 'params'>) {
  const { locale, slug } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('rental');

  return (
    <>
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('detailTitle')}
      </h1>
      <RentalDetail slug={slug} locale={locale} />
    </>
  );
}
