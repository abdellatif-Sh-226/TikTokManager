import { readFileSync, writeFileSync, unlinkSync } from 'fs'
import { marked } from 'marked'
import { execSync } from 'child_process'
import { resolve, dirname } from 'path'
import { fileURLToPath } from 'url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const rootDir = resolve(__dirname, '..')

const mdPath = resolve(rootDir, 'STACKFLOW_BOOK_AR.md')
const htmlPath = resolve(rootDir, 'temp-book.html')
const pdfPath = resolve(rootDir, 'STACKFLOW_BOOK_AR.pdf')
const edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe'

const md = readFileSync(mdPath, 'utf-8')

const html = `<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@300;400;500;700&family=Fira+Code:wght@400;500&display=swap');
  
  * { margin: 0; padding: 0; box-sizing: border-box; }
  
  body {
    font-family: 'Noto Sans Arabic', 'Segoe UI', sans-serif;
    background: #0d0d0d;
    color: #e0e0e0;
    line-height: 1.8;
    padding: 40px 60px;
    direction: rtl;
  }
  
  h1 { 
    color: #fe2c55; 
    font-size: 28px;
    border-bottom: 2px solid #fe2c55;
    padding-bottom: 10px;
    margin: 30px 0 20px;
  }
  h2 { 
    color: #25f4ee; 
    font-size: 22px;
    margin: 25px 0 15px;
    padding-right: 10px;
    border-right: 3px solid #25f4ee;
  }
  h3 { 
    color: #ffffff; 
    font-size: 18px;
    margin: 20px 0 10px;
  }
  p { margin: 10px 0; }
  
  pre {
    background: #1a1a2e;
    border: 1px solid #2e2e4e;
    border-radius: 8px;
    padding: 15px;
    overflow-x: auto;
    margin: 15px 0;
    direction: ltr;
    text-align: left;
  }
  code {
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 13px;
    color: #ffd700;
  }
  pre code { color: #e0e0e0; }
  
  :not(pre) > code {
    background: #1a1a2e;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    direction: ltr;
    text-align: left;
  }
  th, td {
    border: 1px solid #2e2e4e;
    padding: 10px 15px;
  }
  th {
    background: #fe2c55;
    color: white;
    font-weight: 600;
  }
  td { background: #1a1a2e; }
  
  blockquote {
    border-right: 4px solid #fe2c55;
    padding: 10px 20px;
    margin: 15px 0;
    background: #1a1a2e;
    border-radius: 0 8px 8px 0;
  }
  
  hr {
    border: none;
    border-top: 1px solid #2e2e4e;
    margin: 30px 0;
  }
  
  ul, ol { padding-right: 30px; margin: 10px 0; }
  li { margin: 5px 0; }
  
  a { color: #25f4ee; text-decoration: none; }
  a:hover { text-decoration: underline; }
  
  .header {
    text-align: center;
    padding: 40px 0;
    margin-bottom: 30px;
    border-bottom: 2px solid #2e2e4e;
  }
  .header h1 { border: none; font-size: 36px; margin: 0; }
  .header p { color: #888; font-size: 16px; }
  
  strong { color: #ffffff; }
  em { color: #25f4ee; }
  
  @media print {
    body { padding: 20px; }
    pre { break-inside: avoid; }
    h1, h2, h3 { break-after: avoid; }
    table { break-inside: avoid; }
  }
</style>
</head>
<body>
  <div class="header">
    <h1>📘 StackFlow - الكتاب الكامل</h1>
    <p>دليل شامل لفهم مشروع TikTok Manager من الألف إلى الياء</p>
  </div>
  ${marked(md)}
</body>
</html>`

writeFileSync(htmlPath, html, 'utf-8')

try {
  execSync(`"${edgePath}" --headless --print-to-pdf="${pdfPath}" --no-margins "${htmlPath}"`, {
    timeout: 30000,
    stdio: 'pipe',
  })
  console.log('PDF created successfully:', pdfPath)
} catch (err) {
  console.error('Edge PDF error, trying alternative...')
  console.error(err.message)
  process.exit(1)
} finally {
  try { unlinkSync(htmlPath) } catch {}
}
