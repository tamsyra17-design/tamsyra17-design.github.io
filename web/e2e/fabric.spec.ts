import { test, expect } from '@playwright/test';

// The demo seed (commerce Task 28) ships the rental musar as `demo-musar`.
test('rental detail shows the fabric surface with the tier segment count', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', (e) => errors.push(e.message));
  await page.goto('/en/rental/demo-musar');
  const plane = page.getByTestId('fabric-plane');
  await plane.scrollIntoViewIfNeeded();
  await expect(plane).toHaveAttribute('data-segments', /^(24|48|96)$/);
  await page.waitForTimeout(500);
  expect(errors).toEqual([]);
});

test('fabric keeps the booking form usable without WebGL', async ({ page }) => {
  await page.addInitScript(() => {
    const original = HTMLCanvasElement.prototype.getContext;
    HTMLCanvasElement.prototype.getContext = function (
      this: HTMLCanvasElement,
      type: string,
      ...rest: unknown[]
    ) {
      if (type === 'webgl2' || type === 'webgl') return null;
      return (original as (...a: unknown[]) => unknown).call(this, type, ...rest);
    } as typeof HTMLCanvasElement.prototype.getContext;
  });
  await page.goto('/en/rental/demo-musar');
  const plane = page.getByTestId('fabric-plane');
  await expect(plane.locator('img')).toBeVisible();
  await expect(page.locator('canvas')).toHaveCount(0);
  await expect(page.getByTestId('rental-from')).toBeEditable();
});

test('fabric waits for opt-in under reduced motion', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto('/en/rental/demo-musar');
  const plane = page.getByTestId('fabric-plane');
  await plane.scrollIntoViewIfNeeded();
  await expect(plane.locator('img')).toBeVisible();
  await expect(plane.locator('canvas')).toHaveCount(0);
});
