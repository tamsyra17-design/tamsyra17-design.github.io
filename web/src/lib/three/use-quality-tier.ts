'use client';

import { useEffect, useState } from 'react';
import { chooseTier, readTier, writeTier, type QualityTier } from './quality';

/**
 * The session's quality tier: read from sessionStorage, or chosen once from
 * the device and persisted, so every scene on every route agrees. Renders
 * `medium` until mounted (the server cannot know the device).
 */
export function useQualityTier(): QualityTier {
  const [tier, setTier] = useState<QualityTier>('medium');

  useEffect(() => {
    const chosen =
      readTier() ??
      chooseTier({
        deviceMemory: (navigator as { deviceMemory?: number }).deviceMemory,
        hardwareConcurrency: navigator.hardwareConcurrency,
        dpr: window.devicePixelRatio,
      });
    writeTier(chosen);
    // eslint-disable-next-line react-hooks/set-state-in-effect -- device probe runs client-side only
    setTier(chosen);
  }, []);

  return tier;
}
