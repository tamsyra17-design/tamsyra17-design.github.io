import { test, expect } from '@playwright/test';

test('rental page shows quote after selecting dates', async ({ page }) => {
  await page.goto('http://localhost:3000/en/rental');
  await page.getByTestId('rental-card').first().click();
  await page.getByTestId('rental-from').fill('2026-12-01');
  await page.getByTestId('rental-to').fill('2026-12-03');
  await expect(page.getByTestId('rental-total')).toContainText('OMR');
});