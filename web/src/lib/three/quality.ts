export type QualityTier = 'low' | 'medium' | 'high';

export interface TierInput {
  deviceMemory?: number;
  hardwareConcurrency?: number;
  dpr: number;
}

const STORAGE_KEY = 'os:quality-tier';

export function chooseTier(input: TierInput): QualityTier {
  const memory = input.deviceMemory ?? 4;
  const cores = input.hardwareConcurrency ?? 4;

  if (memory < 4 || cores < 4) return 'low';
  if (memory >= 8 && cores >= 8) return 'high';
  return 'medium';
}

export function readTier(): QualityTier | null {
  if (typeof window === 'undefined') return null;
  const value = window.sessionStorage.getItem(STORAGE_KEY);
  if (value === 'low' || value === 'medium' || value === 'high') return value;
  return null;
}

export function writeTier(tier: QualityTier): void {
  if (typeof window === 'undefined') return;
  window.sessionStorage.setItem(STORAGE_KEY, tier);
}