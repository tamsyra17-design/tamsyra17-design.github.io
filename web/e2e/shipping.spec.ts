import { test, expect } from '@playwright/test';

test('shows shipping when governorate is selected', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await page.getByTestId('add-to-cart').click();
  await page.goto('http://localhost:3000/en/checkout');
  await page.getByLabel('Governorate').selectOption('Muscat');
  await expect(page.getByTestId('shipping-total')).toContainText('OMR');
});