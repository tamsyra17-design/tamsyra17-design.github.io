import { hasLocale } from 'next-intl';
import { getTranslations, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import RentalConfirmation from '@/components/rental/RentalConfirmation';
import { routing } from '@/i18n/routing';

type RentalPageProps = PageProps<'/[locale]/rentals/[number]'>;

export default function RentalConfirmationPage({ params }: RentalPageProps) {
  // The rental number is only known at request time, so the body streams.
  return (
    <section className="mx-auto flex w-full max-w-3xl flex-col gap-6 px-4 py-10 sm:px-6">
      <Suspense fallback={null}>
        <RentalConfirmationView params={params} />
      </Suspense>
    </section>
  );
}

async function RentalConfirmationView({ params }: Pick<RentalPageProps, 'params'>) {
  const { locale, number } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const t = await getTranslations('rental');

  return (
    <>
      <h1 className="text-3xl font-bold tracking-tight text-brand-green dark:text-brand-sand">
        {t('confirmationTitle')}
      </h1>
      <RentalConfirmation number={decodeURIComponent(number)} locale={locale} />
    </>
  );
}
