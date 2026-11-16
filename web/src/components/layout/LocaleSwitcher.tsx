'use client';

import { useLocale, useTranslations } from 'next-intl';
import { Link, usePathname } from '@/i18n/navigation';
import { routing, type Locale } from '@/i18n/routing';

/**
 * Switches to the other supported locale while keeping the current pathname.
 * next-intl's `usePathname` strips the locale prefix, so the same page is
 * rendered under the new locale.
 */
export default function LocaleSwitcher() {
  const t = useTranslations('localeSwitcher');
  const currentLocale = useLocale();
  const pathname = usePathname();

  const targetLocale: Locale =
    routing.locales.find((locale) => locale !== currentLocale) ??
    routing.defaultLocale;

  const label =
    targetLocale === 'ar' ? t('switchToArabic') : t('switchToEnglish');

  return (
    <nav aria-label={t('label')} className="flex items-center">
      <Link
        href={pathname}
        locale={targetLocale}
        lang={targetLocale}
        dir={targetLocale === 'ar' ? 'rtl' : 'ltr'}
        className={`rounded-full border border-brand-sand px-3 py-1.5 text-sm font-medium text-brand-green transition-colors hover:border-brand-gold hover:text-brand-gold dark:text-brand-sand ${
          targetLocale === 'ar' ? 'font-arabic' : 'font-sans'
        }`}
      >
        {label}
      </Link>
    </nav>
  );
}
