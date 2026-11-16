import { test, expect } from '@playwright/test';

test('header and locale switch work', async ({ page }) => {
  await page.goto('http://localhost:3000/ar');
  await expect(page.getByRole('link', { name: 'المتجر' })).toBeVisible();
  await page.getByRole('link', { name: 'English' }).click();
  await expect(page).toHaveURL(/\/en/);
  await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
});

test('shared header and footer are rendered in both locales', async ({
  page,
}) => {
  await page.goto('/ar');
  await expect(page.getByRole('banner')).toBeVisible();
  const headerNav = page.getByRole('navigation', { name: 'التنقل الرئيسي' });
  await expect(headerNav).toBeVisible();
  await expect(headerNav.getByRole('link', { name: 'الرئيسية' })).toBeVisible();
  await expect(headerNav.getByRole('link', { name: 'المتجر' })).toBeVisible();
  await expect(headerNav.getByRole('link', { name: 'التأجير' })).toBeVisible();
  await expect(headerNav.getByRole('link', { name: 'السلة' })).toBeVisible();
  await expect(headerNav.getByRole('link', { name: 'حسابي' })).toBeVisible();
  await expect(page.getByRole('contentinfo')).toBeVisible();

  await page.goto('/en');
  await expect(page.getByRole('heading', { name: 'Omani Heritage' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'العربية' })).toBeVisible();
  await expect(
    page.getByRole('navigation', { name: 'Main navigation' }).getByRole('link', { name: 'Shop' }),
  ).toBeVisible();
});
