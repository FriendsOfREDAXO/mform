// Playwright-Smoke-Test fuer MForm: Renderer-Paritaet, Demo-Seiten, Form Builder und Migrationsseite.
//
// Laeuft gegen eine beliebige REDAXO-Installation mit installiertem MForm:
//   MFORM_BASE_URL=http://127.0.0.1:8000/redaxo/index.php MFORM_USER=admin MFORM_PASSWORD=adminpassword npx playwright test
// Standardwerte passen zur CI-Installation aus .github/workflows/static-analysis.yml.

const { test, expect } = require('@playwright/test');

const BASE = process.env.MFORM_BASE_URL || 'http://127.0.0.1:8000/redaxo/index.php';
const USER = process.env.MFORM_USER || 'admin';
const PASSWORD = process.env.MFORM_PASSWORD || 'adminpassword';

const PHP_ERROR = /Fatal error|Warning:|Notice:|Deprecated:|\[translate:[^\]]+\]/;

async function login(page) {
    await page.goto(BASE);
    await page.fill('#rex-id-login-user', USER);
    await page.fill('#rex-id-login-password', PASSWORD);
    await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
    await expect(page.locator('#rex-id-login-user')).toHaveCount(0);
}

async function expectNoPhpErrors(page) {
    const text = await page.evaluate(() => document.body.innerText);
    expect(text).not.toMatch(PHP_ERROR);
}

test.describe('MForm Smoke', () => {
    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Renderer-Paritaet: alle Checks in beiden Pfaden OK', async ({ page }) => {
        await page.goto(BASE + '?page=mform/demo/demo_renderer_parity');
        await expectNoPhpErrors(page);
        await expect(page.locator('table .label-success').first()).toBeVisible();
        await expect(page.locator('table .label-danger')).toHaveCount(0);
        // Flex-Repeater-Template wurde tatsaechlich gerendert (Feld-Marker im HTML-Dump).
        await expect(page.locator('pre').last()).toContainText('data-mfr-field');
    });

    test('Form Builder laedt Palette und Vorschau', async ({ page }) => {
        await page.goto(BASE + '?page=mform/formbuilder');
        await expectNoPhpErrors(page);
        await expect(page.locator('#mform-fb')).toBeVisible();
        await expect(page.locator('[data-fb-palette] li.mform-fb__pal-item').first()).toBeVisible();
    });

    test('Migrationsseite zeigt Inventar', async ({ page }) => {
        await page.goto(BASE + '?page=mform/migration');
        await expectNoPhpErrors(page);
        await expect(page.locator('#mform-migration-step1')).toBeAttached();
        await expect(page.locator('section.rex-page-section, .rex-page-section')).not.toHaveCount(0);
    });

    test('Demo-Seiten ohne PHP-Fehler', async ({ page }) => {
        for (const sub of ['demo_base', 'demo_wrapper', 'demo_extended', 'demo_repeater', 'demo_expert', 'demo_layout_preview']) {
            await page.goto(BASE + '?page=mform/demo/' + sub);
            await expectNoPhpErrors(page);
            await expect(page.locator('.rex-page-section, section').first()).toBeAttached();
            expect(await page.locator('pre.rex-code, .mform, .mfr-container').count(), sub).toBeGreaterThan(0);
        }
    });
});
