// web/src/stores/cart.test.ts
import { describe, it, expect, beforeEach } from 'vitest';
import { useCartStore } from './cart';

beforeEach(() => useCartStore.setState({ items: [] }));

it('adds items and sums subtotal', () => {
  const { addItem } = useCartStore.getState();
  addItem({ productId: 1, variantId: 1, name: 'خنجر', priceBaisa: 12500, qty: 2 });
  expect(useCartStore.getState().subtotalBaisa()).toBe(25000);
});

describe('cart store', () => {
  it('merges duplicate lines by productId + variantId', () => {
    const { addItem } = useCartStore.getState();
    addItem({ productId: 1, variantId: 1, name: 'خنجر', priceBaisa: 12500, qty: 1 });
    addItem({ productId: 1, variantId: 1, name: 'خنجر', priceBaisa: 12500, qty: 2 });
    addItem({ productId: 1, variantId: 2, name: 'خنجر', priceBaisa: 15000, qty: 1 });

    const { items, subtotalBaisa, count } = useCartStore.getState();
    expect(items).toHaveLength(2);
    expect(items[0].qty).toBe(3);
    expect(count()).toBe(4);
    expect(subtotalBaisa()).toBe(52500);
  });

  it('removes a line and clears the cart', () => {
    const { addItem, removeItem, clear } = useCartStore.getState();
    addItem({ productId: 1, variantId: null, name: 'a', priceBaisa: 1000, qty: 1 });
    addItem({ productId: 2, variantId: null, name: 'b', priceBaisa: 2000, qty: 1 });

    removeItem(1, null);
    expect(useCartStore.getState().items.map((i) => i.productId)).toEqual([2]);

    clear();
    expect(useCartStore.getState().items).toEqual([]);
  });

  it('persists items to localStorage under omani-cart', () => {
    useCartStore
      .getState()
      .addItem({ productId: 3, variantId: null, name: 'c', priceBaisa: 500, qty: 2 });

    const stored = JSON.parse(localStorage.getItem('omani-cart') ?? '{}');
    expect(stored.state.items).toEqual([
      expect.objectContaining({ productId: 3, qty: 2, priceBaisa: 500 }),
    ]);
  });
});
