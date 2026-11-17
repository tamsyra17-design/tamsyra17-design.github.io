// web/e2e/cart.spec.ts
import { test, expect } from '@playwright/test';

test('updates quantity and shows total in OMR', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await page.getByTestId('add-to-cart').click();
  await page.goto('http://localhost:3000/en/cart');
  await page.getByTestId('qty-plus').click();
  await expect(page.getByTestId('cart-subtotal')).toContainText('OMR');
});
