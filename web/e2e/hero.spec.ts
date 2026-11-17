import { test, expect } from '@playwright/test';

test('hero pins on desktop and parallaxes on mobile', async ({ page }) => {
  await page.setViewportSize({ width: 1280, height: 800 });
  await page.goto('/en');
  await expect(page.getByTestId('home-hero')).toHaveAttribute('data-pinned', 'true');

  await page.setViewportSize({ width: 390, height: 844 });
  await page.reload();
  await expect(page.getByTestId('home-hero')).toHaveAttribute('data-pinned', 'false');
});

test('reduced motion shows the poster', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto('/en');
  await expect(page.getByTestId('home-hero').locator('img')).toBeVisible();
});