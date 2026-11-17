import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import RentalDetail from '@/components/rental/RentalDetail';
import { routing } from '@/i18n/routing';

type RentalPageProps = PageProps<'/[locale]/rental/[slug]'>;

export default async function RentalDetailPage({ params }: RentalPageProps) {
  const { locale, slug } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('rental');

  return (
    <section className="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6">
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('detailTitle')}
      </h1>
      <RentalDetail slug={slug} locale={locale} />
    </section>
  );
}
