import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';

export default function Footer() {
  const t = useTranslations('footer');
  const tSite = useTranslations('site');
  const tNav = useTranslations('nav');

  return (
    <footer className="bg-brand-green text-brand-sand">
      <div className="mx-auto flex w-full max-w-6xl flex-col gap-2 px-4 py-10 sm:px-6">
        <p className="text-lg font-semibold tracking-tight text-white">
          {tSite('name')}
        </p>
        <p className="max-w-sm text-sm text-brand-sand/80">{t('tagline')}</p>
        <Link
          href="/track"
          className="w-fit text-sm text-white underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-brand-gold"
        >
          {tNav('track')}
        </Link>
      </div>

      <div className="border-t border-white/15">
        <p className="mx-auto w-full max-w-6xl px-4 py-4 text-sm text-brand-sand/70 sm:px-6">
          {t('copyright')}
        </p>
      </div>
    </footer>
  );
}
