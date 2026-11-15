import { test, expect } from '@playwright/test';

test('Arabic home is RTL', async ({ page }) => {
  await page.goto('http://localhost:3000/ar');
  await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
  await expect(page.getByRole('heading', { name: 'تراث عماني' })).toBeVisible();
});

test('English home is LTR', async ({ page }) => {
  await page.goto('http://localhost:3000/en');
  await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
  await expect(page.getByRole('heading', { name: 'Omani Heritage' })).toBeVisible();
});
