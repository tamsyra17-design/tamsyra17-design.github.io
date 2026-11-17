import type { Locale } from '@/i18n/routing';
import { formatOmr } from '@/lib/money';

interface PriceProps {
  baisa: number;
  locale: Locale;
  className?: string;
}

export default function Price({ baisa, locale, className }: PriceProps) {
  return (
    <span
      data-testid="price"
      dir={locale === 'ar' ? 'rtl' : 'ltr'}
      className={`tabular-nums font-semibold ${className ?? ''}`}
    >
      {formatOmr(baisa, locale)}
    </span>
  );
}
