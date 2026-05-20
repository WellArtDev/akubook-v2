import { test, expect } from '@playwright/test';

const routes = [
    '/dashboard',
    '/role-dashboard',
    '/governance-dashboard-v2',
    '/suppliers',
];

test('critical authenticated routes render without console errors', async ({ page }) => {
    const errors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') {
            errors.push(msg.text());
        }
    });

    await page.goto('/login');
    await page.getByLabel('Email').fill(process.env.E2E_EMAIL || 'admin@akubook.com');
    await page.getByLabel('Password').fill(process.env.E2E_PASSWORD || 'password');
    await page.getByRole('button', { name: 'Masuk' }).click();

    await expect(page).toHaveURL(/dashboard/);

    for (const route of routes) {
        await page.goto(route);
        await expect(page).toHaveURL(new RegExp(route.replace('/', '\\/')));
        await expect(page.locator('body')).toBeVisible();
    }

    expect(errors).toEqual([]);
});
