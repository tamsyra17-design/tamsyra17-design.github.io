'use client';

import { useTranslations } from 'next-intl';
import { useState } from 'react';
import type { Locale } from '@/i18n/routing';
import { storageUrl } from '@/lib/api';
import { translate, type ProductImage } from '@/lib/types';

interface ProductGalleryProps {
  images: ProductImage[];
  name: string;
  locale: Locale;
}

/** Images live on the API's public storage disk. */
const imageUrl = storageUrl;

export default function ProductGallery({
  images,
  name,
  locale,
}: ProductGalleryProps) {
  const t = useTranslations('product');
  const ordered = [...images].sort(
    (a, b) => Number(b.is_primary) - Number(a.is_primary),
  );
  const [activeId, setActiveId] = useState(ordered[0]?.id);
  const active = ordered.find((image) => image.id === activeId) ?? ordered[0];

  if (!active) {
    return (
      <div
        data-testid="product-gallery"
        aria-hidden="true"
        className="flex aspect-square items-center justify-center rounded-lg bg-brand-sand text-7xl font-semibold text-brand-green/40"
      >
        {name.charAt(0)}
      </div>
    );
  }

  const altFor = (image: ProductImage) => translate(image.alt, locale) || name;

  return (
    <div
      data-testid="product-gallery"
      role="group"
      aria-label={t('galleryLabel')}
      className="flex flex-col gap-3"
    >
      {/* eslint-disable-next-line @next/next/no-img-element -- API-hosted images, origin is deploy-specific */}
      <img
        src={imageUrl(active.path)}
        alt={altFor(active)}
        className="aspect-square w-full rounded-lg bg-brand-sand object-cover"
      />

      {ordered.length > 1 && (
        <ul className="flex flex-wrap gap-2">
          {ordered.map((image, index) => (
            <li key={image.id}>
              <button
                type="button"
                onClick={() => setActiveId(image.id)}
                aria-pressed={image.id === active.id}
                aria-label={t('showImage', { index: index + 1 })}
                className={`block size-16 overflow-hidden rounded-md border-2 focus-visible:outline-2 focus-visible:outline-brand-gold ${
                  image.id === active.id
                    ? 'border-brand-green'
                    : 'border-transparent'
                }`}
              >
                {/* eslint-disable-next-line @next/next/no-img-element -- see above */}
                <img
                  src={imageUrl(image.path)}
                  alt=""
                  className="size-full object-cover"
                />
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
