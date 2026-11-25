#!/usr/bin/env node
const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');

const PORT = process.env.PORT || 8080;
const SRC_DIR = path.join(__dirname, '..');

const mimeTypes = {
  '.html': 'text/html',
  '.css': 'text/css',
  '.js': 'application/javascript',
  '.json': 'application/json',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.gif': 'image/gif',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.ttf': 'font/ttf',
  '.eot': 'application/vnd.ms-fontobject',
};

const server = http.createServer((req, res) => {
  let pathname = url.parse(req.url).pathname;

  // Serve index.html for root
  if (pathname === '/') {
    pathname = '/index.html';
  }

  // Security: prevent directory traversal
  const safePath = path.normalize(path.join(SRC_DIR, pathname)).replace(/\0/g, '');
  if (!safePath.startsWith(SRC_DIR)) {
    res.writeHead(403, { 'Content-Type': 'text/plain' });
    res.end('Forbidden');
    return;
  }

  fs.readFile(safePath, (err, data) => {
    if (err) {
      // If file not found and it's not .html, try .html
      if (err.code === 'ENOENT' && !pathname.endsWith('.html')) {
        fs.readFile(safePath + '.html', (err2, data2) => {
          if (err2) {
            res.writeHead(404, { 'Content-Type': 'text/plain' });
            res.end('Not Found: ' + pathname);
          } else {
            serveFile(res, data2, '.html');
          }
        });
      } else {
        res.writeHead(404, { 'Content-Type': 'text/plain' });
        res.end('Not Found: ' + pathname);
      }
    } else {
      const ext = path.extname(pathname);
      serveFile(res, data, ext);
    }
  });
});

function serveFile(res, data, ext) {
  const contentType = mimeTypes[ext] || 'application/octet-stream';
  res.writeHead(200, {
    'Content-Type': contentType,
    'Cache-Control': 'no-cache',
    'Access-Control-Allow-Origin': '*',
  });
  res.end(data);
}

server.listen(PORT, '0.0.0.0', () => {
  console.log(`\n🚀 Development server running at http://localhost:${PORT}`);
  console.log(`📁 Serving files from: ${SRC_DIR}`);
  console.log(`\n Press Ctrl+C to stop\n`);
});
