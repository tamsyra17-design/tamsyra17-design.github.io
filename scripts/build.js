#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const { minify } = require('html-minifier-terser');
const imagemin = require('imagemin');
const imageminMozjpeg = require('imagemin-mozjpeg');
const imageminPngquant = require('imagemin-pngquant');

const SRC_DIR = path.join(__dirname, '..');
const DIST_DIR = path.join(SRC_DIR, 'dist');

async function build() {
  console.log('🔨 Starting build...');

  // Clean dist directory
  if (fs.existsSync(DIST_DIR)) {
    fs.rmSync(DIST_DIR, { recursive: true });
  }
  fs.mkdirSync(DIST_DIR, { recursive: true });

  try {
    // Copy and minify HTML files
    console.log('📄 Minifying HTML files...');
    await minifyHtml();

    // Copy static assets
    console.log('📁 Copying static files...');
    copyDir(path.join(SRC_DIR, 'assets'), path.join(DIST_DIR, 'assets'));
    fs.copyFileSync(path.join(SRC_DIR, 'CNAME'), path.join(DIST_DIR, 'CNAME'));
    if (fs.existsSync(path.join(SRC_DIR, '.nojekyll'))) {
      fs.copyFileSync(path.join(SRC_DIR, '.nojekyll'), path.join(DIST_DIR, '.nojekyll'));
    }

    // Optimize images
    console.log('🖼️  Optimizing images...');
    await optimizeImages();

    console.log('✅ Build complete! Output in ./dist/');
  } catch (err) {
    console.error('❌ Build failed:', err);
    process.exit(1);
  }
}

async function minifyHtml() {
  const htmlFiles = [
    { src: 'index.html', dist: 'index.html' },
    { src: 'admin.html', dist: 'admin.html' },
    { src: 'hub/index.html', dist: 'hub/index.html' },
  ];

  for (const file of htmlFiles) {
    const srcPath = path.join(SRC_DIR, file.src);
    const distPath = path.join(DIST_DIR, file.dist);

    if (!fs.existsSync(srcPath)) continue;

    const html = fs.readFileSync(srcPath, 'utf8');
    const minified = await minify(html, {
      removeComments: true,
      collapseWhitespace: true,
      removeAttributeQuotes: true,
      minifyCSS: true,
      minifyJS: true,
      removeRedundantAttributes: true,
      removeScriptTypeAttributes: true,
      removeStyleLinkTypeAttributes: true,
      useShortDoctype: true,
    });

    // Ensure directory exists
    fs.mkdirSync(path.dirname(distPath), { recursive: true });
    fs.writeFileSync(distPath, minified);
    console.log(`  ✓ ${file.src}`);
  }
}

async function optimizeImages() {
  const imageSizes = {
    before: 0,
    after: 0,
  };

  const imageFiles = ['*.png', '*.jpg', '*.jpeg', '*.gif'];

  // Copy and optimize root images
  for (const pattern of imageFiles) {
    const files = findFiles(SRC_DIR, pattern);
    for (const file of files) {
      const filename = path.basename(file);
      const relPath = path.relative(SRC_DIR, file);

      // Skip if in node_modules or dist
      if (relPath.includes('node_modules') || relPath.includes('dist')) continue;

      const fileSize = fs.statSync(file).size;
      imageSizes.before += fileSize;

      try {
        await imagemin([file], {
          destination: path.dirname(path.join(DIST_DIR, relPath)),
          plugins: [
            imageminMozjpeg({ quality: 75 }),
            imageminPngquant({
              quality: [0.65, 0.9],
              speed: 4,
            }),
          ],
        });

        const newFile = path.join(DIST_DIR, relPath);
        const newSize = fs.statSync(newFile).size;
        imageSizes.after += newSize;
        console.log(`  ✓ ${relPath} (${formatSize(fileSize)} → ${formatSize(newSize)})`);
      } catch (err) {
        console.warn(`  ⚠ Failed to optimize ${relPath}`);
      }
    }
  }

  if (imageSizes.before > 0) {
    const saved = ((1 - imageSizes.after / imageSizes.before) * 100).toFixed(1);
    console.log(`  Saved: ${formatSize(imageSizes.before - imageSizes.after)} (${saved}%)`);
  }
}

function copyDir(src, dest) {
  if (!fs.existsSync(src)) return;

  fs.mkdirSync(dest, { recursive: true });
  const files = fs.readdirSync(src);

  for (const file of files) {
    const srcPath = path.join(src, file);
    const destPath = path.join(dest, file);

    if (fs.statSync(srcPath).isDirectory()) {
      copyDir(srcPath, destPath);
    } else {
      fs.copyFileSync(srcPath, destPath);
    }
  }
}

function findFiles(dir, pattern) {
  const files = [];
  const regex = new RegExp(pattern.replace('*', '.*'));

  function traverse(currentPath) {
    const items = fs.readdirSync(currentPath);
    for (const item of items) {
      const fullPath = path.join(currentPath, item);
      if (fs.statSync(fullPath).isDirectory()) {
        if (!item.startsWith('.') && item !== 'node_modules' && item !== 'dist') {
          traverse(fullPath);
        }
      } else if (regex.test(item)) {
        files.push(fullPath);
      }
    }
  }

  traverse(dir);
  return files;
}

function formatSize(bytes) {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

build();
