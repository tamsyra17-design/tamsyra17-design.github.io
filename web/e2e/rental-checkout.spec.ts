import { test, expect } from '@playwright/test';

test('books a rental and shows confirmation', async ({ page }) => {
  await page.goto('http://localhost:3000/en/rental');
  await page.getByTestId('rental-card').first().click();
  await page.getByTestId('rental-from').fill('2026-12-01');
  await page.getByTestId('rental-to').fill('2026-12-03');
  await page.getByLabel('Name').fill('Salim');
  await page.getByLabel('Phone').fill('+96890000000');
  await page.getByTestId('book-rental').click();
  await expect(page.getByTestId('rental-number')).toContainText('RNT-');
});