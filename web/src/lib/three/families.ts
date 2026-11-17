export type SilhouetteFamily = 'metal' | 'woven' | 'layered';

const METAL = ['khanjar', 'saif', 'jewellery', 'ring', 'dagger', 'sword'];
const WOVEN = ['musar', 'shawl', 'asa', 'scarf', 'turban', 'fabric'];

export function familyFor(slug: string): SilhouetteFamily {
  const lower = slug.toLowerCase();
  if (METAL.some((keyword) => lower.includes(keyword))) return 'metal';
  if (WOVEN.some((keyword) => lower.includes(keyword))) return 'woven';
  return 'layered';
}
