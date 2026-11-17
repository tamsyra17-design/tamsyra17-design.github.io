import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';
import type { Translations } from '@/lib/types';

/** One cart line. Money is always integer baisa (1 OMR = 1000 baisa). */
export interface CartItem {
  productId: number;
  /** `null` when the product has no variants. */
  variantId: number | null;
  name: string;
  priceBaisa: number;
  qty: number;
  slug?: string;
  /** Both-language names so the cart follows the active locale. */
  names?: Translations;
}

/** Upper bound for a single line's quantity. */
export const MAX_QTY = 99;

interface CartState {
  items: CartItem[];
  /** Add a line, merging into an existing one with the same product+variant. */
  addItem: (item: CartItem) => void;
  removeItem: (productId: number, variantId: number | null) => void;
  /** Set a line's quantity, clamped to 1..MAX_QTY. */
  setQty: (productId: number, variantId: number | null, qty: number) => void;
  clear: () => void;
  subtotalBaisa: () => number;
  count: () => number;
}

const sameLine = (
  item: CartItem,
  productId: number,
  variantId: number | null,
): boolean => item.productId === productId && item.variantId === variantId;

/** Clamp to a positive whole number so money math stays in integers. */
const toQty = (qty: number): number =>
  Math.min(MAX_QTY, Math.max(1, Math.trunc(qty) || 1));

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      items: [],

      addItem: (item) => {
        const qty = toQty(item.qty);
        const variantId = item.variantId ?? null;

        set((state) => {
          const existing = state.items.find((line) =>
            sameLine(line, item.productId, variantId),
          );

          if (!existing) {
            return {
              items: [
                ...state.items,
                { ...item, variantId, qty, priceBaisa: Math.round(item.priceBaisa) },
              ],
            };
          }

          return {
            items: state.items.map((line) =>
              line === existing ? { ...line, qty: toQty(line.qty + qty) } : line,
            ),
          };
        });
      },

      removeItem: (productId, variantId) =>
        set((state) => ({
          items: state.items.filter(
            (line) => !sameLine(line, productId, variantId ?? null),
          ),
        })),

      setQty: (productId, variantId, qty) =>
        set((state) => ({
          items: state.items.map((line) =>
            sameLine(line, productId, variantId ?? null)
              ? { ...line, qty: toQty(qty) }
              : line,
          ),
        })),

      clear: () => set({ items: [] }),

      subtotalBaisa: () =>
        get().items.reduce((sum, line) => sum + line.priceBaisa * line.qty, 0),

      count: () => get().items.reduce((sum, line) => sum + line.qty, 0),
    }),
    {
      name: 'omani-cart',
      storage: createJSONStorage(() => localStorage),
      partialize: (state) => ({ items: state.items }),
      // Rehydrated on mount by <CartHydrator /> so server and first client
      // render agree (empty cart) and React does not report a mismatch.
      skipHydration: true,
    },
  ),
);
