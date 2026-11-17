import { test, expect } from '@playwright/test';

test('shows exactly 3 decimals', async ({ page }) => {
  await page.goto('http://localhost:3000/en/shop');
  await expect(page.getByTestId('price').first()).toHaveText(/OMR\s\d+\.\d{3}/);
});