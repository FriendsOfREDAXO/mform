// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/e2e',
    timeout: 60000,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? 'github' : 'list',
    projects: [
        { name: 'chromium', use: { browserName: 'chromium', channel: process.env.MFORM_PW_CHANNEL || undefined } },
    ],
    use: {
        headless: true,
        ignoreHTTPSErrors: true,
        viewport: { width: 1280, height: 900 },
        screenshot: 'only-on-failure',
    },
});
