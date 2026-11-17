import type { Locale } from '@/i18n/routing';

const BAISA_PER_RIAL = 1000;

const formatters: Partial<Record<Locale, Intl.NumberFormat>> = {};

function formatter(locale: Locale): Intl.NumberFormat {
  formatters[locale] ??= new Intl.NumberFormat(
    locale === 'ar' ? 'ar-OM' : 'en-OM',
    {
      style: 'currency',
      currency: 'OMR',
      minimumFractionDigits: 3,
      maximumFractionDigits: 3,
    },
  );

  return formatters[locale];
}

/** ICU bidi marks and the no-break space around the currency symbol. */
const NOISE = /[‎‏؜ \s]/g;

/**
 * Format an integer baisa amount as Omani rials with 3 decimals:
 * `"OMR 12.500"` (en) or `"ر.ع. ١٢٫٥٠٠"` (ar).
 *
 * The currency symbol always leads the (locale-specific) digits so the
 * string is identical regardless of ICU's per-locale symbol placement.
 */
export function formatOmr(baisa: number, locale: Locale = 'en'): string {
  const parts = formatter(locale).formatToParts(baisa / BAISA_PER_RIAL);
  const currency = parts.find((part) => part.type === 'currency')?.value ?? '';
  const amount = parts
    .filter((part) => part.type !== 'currency')
    .map((part) => part.value)
    .join('')
    .replace(NOISE, '');

  return `${currency.replace(NOISE, '')} ${amount}`;
}
