import { useTranslations } from 'next-intl';

export default function Footer() {
  const t = useTranslations('footer');
  const tSite = useTranslations('site');

  return (
    <footer className="bg-brand-green text-brand-sand">
      <div className="mx-auto flex w-full max-w-6xl flex-col gap-2 px-4 py-10 sm:px-6">
        <p className="text-lg font-semibold tracking-tight text-white">
          {tSite('name')}
        </p>
        <p className="max-w-sm text-sm text-brand-sand/80">{t('tagline')}</p>
      </div>

      <div className="border-t border-white/15">
        <p className="mx-auto w-full max-w-6xl px-4 py-4 text-sm text-brand-sand/70 sm:px-6">
          {t('copyright')}
        </p>
      </div>
    </footer>
  );
}
