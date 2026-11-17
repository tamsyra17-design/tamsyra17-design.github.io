'use client';

import { useEffect, useSyncExternalStore } from 'react';
import { useCartStore } from './cart';

const subscribe = (onChange: () => void) =>
  useCartStore.persist.onFinishHydration(onChange);
const getSnapshot = () => useCartStore.persist.hasHydrated();
const getServerSnapshot = () => false;

/**
 * Rehydrates the persisted cart once on the client and reports whether it
 * has finished. Server render and the first client render both see `false`,
 * so cart-dependent UI never causes a hydration mismatch.
 */
export function useCartHydrated(): boolean {
  useEffect(() => {
    if (!useCartStore.persist.hasHydrated()) {
      void useCartStore.persist.rehydrate();
    }
  }, []);

  return useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot);
}
