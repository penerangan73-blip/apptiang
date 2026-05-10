const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
    const page = await browser.newPage();
    await page.setViewport({ width: 375, height: 812, isMobile: true });
    await page.goto('http://localhost:8000', { waitUntil: 'networkidle2' });
    await page.screenshot({ path: '/home/dzaka/.gemini/antigravity/brain/571b9af4-aa9a-4e8a-aaea-3763e74fdb45/mobile_view.png' });
    await browser.close();
})();
