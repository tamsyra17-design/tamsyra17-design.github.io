// web/e2e/catalog.spec.ts
import { test, expect } from '@playwright/test';

test('shop lists products and formats OMR', async ({ page }) => {
  await page.goto('http://localhost:3000/ar/shop');
  await expect(page.getByTestId('product-card').first()).toBeVisible();
  await expect(page.getByTestId('price').first()).toContainText('ر.ع.');
});
