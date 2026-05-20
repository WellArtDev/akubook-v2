import { test, expect } from '@playwright/test';

test('health endpoint returns ok status', async ({ request }) => {
    const response = await request.get('/healthz');
    expect(response.status()).toBe(200);

    const payload = await response.json();
    expect(payload.status).toBe('ok');
    expect(payload.app).toBe('ok');
    expect(payload.database).toBe('ok');
});

const routeGroups = [
    {
        group: 'Core',
        routes: [
            { path: '/dashboard', title: /Dashboard/ },
            { path: '/role-dashboard', title: /Role Dashboard/ },
        ],
    },
    {
        group: 'Governance',
        routes: [
            { path: '/governance-dashboard-v2', title: /Governance Dashboard v2/ },
            { path: '/sensitive-alerts', title: /Sensitive Alerts/ },
            { path: '/compliance-export-packs', title: /Compliance Export Packs/ },
        ],
    },
    {
        group: 'Master Data',
        routes: [
            { path: '/customers', title: /Customers/ },
            { path: '/suppliers', title: /Suppliers/ },
            { path: '/employees', title: /Employees/ },
        ],
    },
    {
        group: 'Sales',
        routes: [
            { path: '/sales-orders', title: /Sales Orders/ },
            { path: '/customer-payments', title: /Customer Payments/ },
            { path: '/customer-statements', title: /Customer Statement/ },
            { path: '/sales-reports', title: /Sales Reports/ },
            { path: '/sales-dashboard', title: /Sales Dashboard/ },
        ],
    },
    {
        group: 'Purchase',
        routes: [
            { path: '/purchase-orders', title: /Purchase Orders/ },
            { path: '/supplier-payments', title: /Supplier Payments/ },
            { path: '/supplier-statements', title: /Supplier Statement/ },
            { path: '/purchase-reports', title: /Purchase Reports/ },
            { path: '/purchase-dashboard', title: /Purchase Dashboard/ },
        ],
    },
    {
        group: 'Finance Reports',
        routes: [
            { path: '/reports/trial-balance', title: /Trial Balance/ },
            { path: '/reports/general-ledger', title: /General Ledger/ },
            { path: '/financial-reports', title: /Financial Reports/ },
        ],
    },
];

const routes = routeGroups.flatMap(({ group, routes }) => routes.map((route) => ({ ...route, group })));

async function login(page) {
    await page.goto('/login');

    if (/dashboard/.test(page.url())) {
        return;
    }

    const emailInput = page.locator('input[name="email"], input[type="email"]');
    const passwordInput = page.locator('input[name="password"], input[type="password"]');

    if ((await emailInput.count()) === 0) {
        await page.goto('/dashboard');
        if (/dashboard/.test(page.url())) {
            return;
        }
        await page.goto('/login');
    }

    await expect(emailInput.first()).toBeVisible();
    await expect(passwordInput.first()).toBeVisible();

    await emailInput.first().fill(process.env.E2E_EMAIL || 'admin@akubook.com');
    await passwordInput.first().fill(process.env.E2E_PASSWORD || 'password');
    await page.getByRole('button', { name: /Masuk|Log in|Login/i }).click();
    await expect(page).toHaveURL(/dashboard/);
}

test('critical authenticated routes render without console errors', async ({ page }) => {
    const errors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') {
            errors.push(`${page.url()} :: ${msg.text()}`);
        }
    });

    await login(page);

    for (const route of routes) {
        await test.step(`${route.group}: ${route.path}`, async () => {
            const response = await page.goto(route.path);
            expect(response?.status(), `${route.path} should not return HTTP error`).toBeLessThan(400);
            await expect(page).toHaveURL(new RegExp(route.path.replace('/', '\\/')));
            await expect(page).toHaveTitle(route.title);
            await expect(page.locator('body')).toBeVisible();
        });
    }

    expect(errors).toEqual([]);
});
