import type { QualityTier } from './quality';

export interface FabricUniforms {
  /** Plane subdivisions per side. */
  uSegments: number;
  /** Drape amplitude in scene units; 0 disables the wave entirely. */
  uStrength: number;
}

const BY_TIER: Record<QualityTier, FabricUniforms> = {
  high: { uSegments: 96, uStrength: 0.08 },
  medium: { uSegments: 48, uStrength: 0.05 },
  low: { uSegments: 24, uStrength: 0 },
};

/** Pure: mesh detail and drape strength for a quality tier. */
export function fabricUniforms(tier: QualityTier): FabricUniforms {
  return { ...BY_TIER[tier] };
}
