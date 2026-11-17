import { test, expect } from '@playwright/test';

test('product page has localized metadata and hreflang', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await expect(page.locator('link[rel="alternate"][hreflang="ar"]')).toHaveCount(1);
  await expect(page.locator('link[rel="alternate"][hreflang="en"]')).toHaveCount(1);
});