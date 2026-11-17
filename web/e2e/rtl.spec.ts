import { test, expect } from '@playwright/test';

test('keeps OMR and phone readable in RTL', async ({ page }) => {
  await page.goto('http://localhost:3000/ar/shop');
  await page.getByTestId('product-card').first().click();
  await page.getByTestId('add-to-cart').click();
  await page.goto('http://localhost:3000/ar/cart');
  await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
  await expect(page.getByTestId('cart-subtotal')).toContainText('ر.ع.');
});