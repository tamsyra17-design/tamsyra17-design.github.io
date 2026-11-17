// web/e2e/checkout.spec.ts
import { test, expect } from '@playwright/test';

test('guest can place a COD order and see confirmation', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await page.getByTestId('product-card').first().click();
  await page.getByTestId('add-to-cart').click();
  await page.goto('http://localhost:3000/en/checkout');
  await page.getByLabel('Name').fill('Salim');
  await page.getByLabel('Phone').fill('+96890000000');
  await page.getByLabel('Governorate').selectOption('Muscat');
  await page.getByLabel('Wilayat').fill('Muscat');
  await page.getByLabel('Address').fill('Al Khuwair');
  await page.getByTestId('place-order').click();
  await expect(page.getByTestId('order-number')).toContainText('ORD-');
});
