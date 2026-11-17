// Browser-Test fuer den Bild-Picker (assets/js/img-picker.js, MForm::addImgPickerField).
// Braucht kein REDAXO: Skript und CSS werden in eine leere Seite mit einem nachgebauten Feld geladen.
//   npx playwright test tests/e2e/img-picker.spec.js

const path = require('path');
const { test, expect } = require('@playwright/test');

const ASSETS = path.join(__dirname, '..', '..', 'assets');

const svg = (fill) => 'data:image/svg+xml;base64,' + Buffer.from(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 80"><rect width="120" height="80" fill="' + fill + '"/></svg>',
).toString('base64');
const META = JSON.stringify({
    text: { img: svg('#9fc3e3'), description: 'Überschrift und Fließtext' },
    image: { img: svg('#5b8fbf') },
    video: { icon: 'fa fa-film', description: 'YouTube oder MP4' },
});

const field = (id) => `
    <div class="form-group mform-img-picker-group" data-item="${id}">
        <label class="control-label" for="sel-${id}">Inhaltstyp</label>
        <select id="sel-${id}" class="form-control mform-img-picker" data-mform-img-picker='${META}'>
            <option value="text">Text</option>
            <option value="image">Bild + Text</option>
            <option value="video">Video</option>
        </select>
    </div>`;

async function setup(page) {
    await page.setContent(`<!doctype html><html><head><meta charset="utf-8"></head><body>
        <button id="outside" type="button">anderes Feld</button><div id="form">${field('a')}</div></body></html>`);
    await page.addStyleTag({ path: path.join(ASSETS, 'css', 'mform-tokens.css') });
    await page.addStyleTag({ path: path.join(ASSETS, 'css', 'img-picker.css') });
    await page.addScriptTag({ path: path.join(ASSETS, 'js', 'img-picker.js') });
    await page.evaluate(() => {
        window.changes = [];
        document.getElementById('sel-a').addEventListener('change', (e) => window.changes.push(e.target.value));
    });
}

test.describe('Bild-Picker', () => {
    test.beforeEach(async ({ page }) => {
        await setup(page);
    });

    test('ersetzt das Select durch ein kompaktes Feld mit Vorschau', async ({ page }) => {
        const toggle = page.locator('[data-item="a"] .mform-img-picker__toggle');
        await expect(toggle).toBeVisible();
        await expect(toggle).toHaveText(/Text/);
        await expect(toggle.locator('img')).toHaveCount(1);
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(page.locator('[data-item="a"] .mform-img-picker__panel')).toBeHidden();
        // Label zeigt jetzt auf den Button
        await expect(page.locator('[data-item="a"] label')).toHaveAttribute('for', await toggle.getAttribute('id'));
    });

    test('Auswahl per Maus setzt den Wert des Selects und feuert change', async ({ page }) => {
        await page.click('[data-item="a"] .mform-img-picker__toggle');
        const panel = page.locator('[data-item="a"] .mform-img-picker__panel');
        await expect(panel).toBeVisible();
        await expect(panel.locator('[role="option"]')).toHaveCount(3);
        await panel.locator('[role="option"]', { hasText: 'Video' }).click();

        await expect(panel).toBeHidden();
        await expect(page.locator('#sel-a')).toHaveValue('video');
        await expect(page.locator('[data-item="a"] .mform-img-picker__toggle')).toHaveText(/Video/);
        expect(await page.evaluate(() => window.changes)).toEqual(['video']);
    });

    test('Tastatur: Pfeile, Enter waehlt, Esc schliesst ohne Aenderung', async ({ page }) => {
        const toggle = page.locator('[data-item="a"] .mform-img-picker__toggle');
        await toggle.focus();
        await page.keyboard.press('ArrowDown');
        await page.keyboard.press('End');
        await page.keyboard.press('Enter');
        await expect(page.locator('#sel-a')).toHaveValue('video');
        await expect(toggle).toBeFocused();

        await page.keyboard.press('ArrowDown');
        await page.keyboard.press('Home');
        await page.keyboard.press('Escape');
        await expect(page.locator('[data-item="a"] .mform-img-picker__panel')).toBeHidden();
        await expect(page.locator('#sel-a')).toHaveValue('video');
        await expect(toggle).toBeFocused();
    });

    test('schliesst bei Klick daneben und wenn der Fokus den Picker verlaesst', async ({ page }) => {
        const panel = page.locator('[data-item="a"] .mform-img-picker__panel');
        await page.click('[data-item="a"] .mform-img-picker__toggle');
        await page.click('#outside');
        await expect(panel).toBeHidden();

        await page.click('[data-item="a"] .mform-img-picker__toggle');
        await expect(panel).toBeVisible();
        await page.focus('#outside');
        await expect(panel).toBeHidden();
    });

    test('Wert von aussen geaendert wird angezeigt', async ({ page }) => {
        await page.evaluate(() => {
            const select = document.getElementById('sel-a');
            select.value = 'image';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        await expect(page.locator('[data-item="a"] .mform-img-picker__toggle')).toHaveText(/Bild \+ Text/);
    });

    test('geklontes Repeater-Item wird neu aufgebaut und arbeitet unabhaengig', async ({ page }) => {
        await page.evaluate(() => {
            const original = document.querySelector('[data-item="a"]');
            const clone = original.cloneNode(true);
            clone.setAttribute('data-item', 'b');
            clone.querySelector('select').id = 'sel-b';
            document.getElementById('form').appendChild(clone);
            window.MFormImgPicker.init(clone);
        });
        const b = page.locator('[data-item="b"]');
        await expect(b.locator('.mform-img-picker__toggle')).toHaveCount(1);
        await expect(b.locator('.mform-img-picker__panel')).toHaveCount(1);

        await b.locator('.mform-img-picker__toggle').click();
        await b.locator('[role="option"]', { hasText: 'Bild + Text' }).click();
        await expect(page.locator('#sel-b')).toHaveValue('image');
        await expect(page.locator('#sel-a')).toHaveValue('text');
        await expect(page.locator('[data-item="a"] .mform-img-picker__toggle')).toHaveText(/Text/);
    });
});
