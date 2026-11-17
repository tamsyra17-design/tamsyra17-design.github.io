// web/e2e/product-detail.spec.ts
import { test, expect } from '@playwright/test';

test('adds a product to the cart', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await page.getByTestId('add-to-cart').click();
  await expect(page.getByTestId('cart-count')).toHaveText('1');
});
