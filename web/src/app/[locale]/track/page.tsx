import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import TrackView from '@/components/rental/TrackView';
import { routing } from '@/i18n/routing';

type TrackPageProps = PageProps<'/[locale]/track'>;

export default async function TrackPage({ params }: TrackPageProps) {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('rental');

  return (
    <section className="mx-auto flex w-full max-w-3xl flex-col gap-6 px-4 py-10 sm:px-6">
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('trackTitle')}
      </h1>
      <TrackView locale={locale} />
    </section>
  );
}
