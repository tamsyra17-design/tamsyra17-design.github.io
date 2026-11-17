import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';

export default function Footer() {
  const t = useTranslations('footer');
  const tSite = useTranslations('site');

  return (
    <footer className="mt-auto border-t border-brand-border bg-brand-sand/30 dark:bg-brand-sand/10">
      <div className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
          <div className="flex flex-col gap-3">
            <Link href="/" className="text-lg font-bold text-brand-green dark:text-brand-sand">
              {tSite('name')}
            </Link>
            <p className="text-sm text-brand-muted">{t('tagline')}</p>
          </div>
          <div>
            <h3 className="mb-3 text-sm font-semibold uppercase tracking-wider text-brand-muted">
              Shop
            </h3>
            <ul className="flex flex-col gap-2 text-sm">
              <li><Link href="/shop" className="hover:text-brand-green dark:hover:text-brand-gold">Shop</Link></li>
              <li><Link href="/rental" className="hover:text-brand-green dark:hover:text-brand-gold">Rental</Link></li>
            </ul>
          </div>
          <div>
            <h3 className="mb-3 text-sm font-semibold uppercase tracking-wider text-brand-muted">
              Account
            </h3>
            <ul className="flex flex-col gap-2 text-sm">
              <li><Link href="/cart" className="hover:text-brand-green dark:hover:text-brand-gold">Cart</Link></li>
              <li><Link href="/account" className="hover:text-brand-green dark:hover:text-brand-gold">Account</Link></li>
              <li><Link href="/track" className="hover:text-brand-green dark:hover:text-brand-gold">Track order</Link></li>
            </ul>
          </div>
          <div>
            <h3 className="mb-3 text-sm font-semibold uppercase tracking-wider text-brand-muted">
              Contact
            </h3>
            <p className="text-sm text-brand-muted">Muscat, Oman</p>
          </div>
        </div>

        <div className="flex flex-col items-center justify-between gap-4 border-t border-brand-border pt-6 sm:flex-row">
          <p className="text-xs text-brand-muted">{t('copyright')}</p>
          <p className="text-xs text-brand-muted">OMR · Oman only</p>
        </div>
      </div>
    </footer>
  );
}
