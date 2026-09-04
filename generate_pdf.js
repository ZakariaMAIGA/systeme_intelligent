import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

async function generatePDF() {
  console.log("Launching Edge with puppeteer-core...");
  const edgePath = "C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe";
  const htmlPath = path.resolve("dossier_print.html");
  const pdfPath = path.resolve("DOSSIER_PRESENTATION_CLIENT.pdf");

  const htmlContent = fs.readFileSync(htmlPath, 'utf8');

  const browser = await puppeteer.launch({
    executablePath: edgePath,
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
  });

  const page = await browser.newPage();
  await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

  console.log("Rendering PDF...");
  await page.pdf({
    path: pdfPath,
    format: 'A4',
    printBackground: true,
    displayHeaderFooter: false,
    margin: {
      top: '0mm',
      right: '0mm',
      bottom: '0mm',
      left: '0mm'
    }
  });

  await browser.close();
  console.log("✅ PDF successfully generated:", pdfPath);
}

generatePDF().catch(err => {
  console.error("Error generating PDF:", err);
  process.exit(1);
});
