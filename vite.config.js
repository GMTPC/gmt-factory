import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import { resolve, dirname } from 'path';
import { readdirSync, existsSync, statSync, cpSync } from 'fs';
import { fileURLToPath } from 'url';
import { templateAssets } from './vite.template-assets.js';

const __dirname = dirname(fileURLToPath(import.meta.url));

// Auto-detect libraries from node_modules based on common patterns
function autoDetectLibs() {
  const libs = [];
  const nodeModules = resolve(__dirname, 'node_modules');
  
  if (!existsSync(nodeModules)) return libs;
  
  const packages = readdirSync(nodeModules).filter(name => !name.startsWith('.'));
  
  packages.forEach(pkg => {
    const pkgPath = resolve(nodeModules, pkg);
    const distPath = resolve(pkgPath, 'dist');
    
    if (!statSync(pkgPath).isDirectory()) return;
    
    if (existsSync(distPath) && statSync(distPath).isDirectory()) {
      const files = readdirSync(distPath).filter(f => !f.endsWith('.map'));
      if (files.length > 0) {
        libs.push({
          src: `node_modules/${pkg}/dist`,
          dest: `libs/${pkg}`,
          rename: { stripBase: true }
        });
      }
    }
  });
  
  const scopedPackages = packages.filter(name => name.startsWith('@'));
  scopedPackages.forEach(scope => {
    const scopePath = resolve(nodeModules, scope);
    if (!statSync(scopePath).isDirectory()) return;
    
    const scopedLibs = readdirSync(scopePath).filter(name => !name.startsWith('.'));
    scopedLibs.forEach(pkg => {
      const pkgPath = resolve(scopePath, pkg);
      const distPath = resolve(pkgPath, 'dist');
      const srcPath = resolve(pkgPath, 'src');
      const cssPath = resolve(pkgPath, 'css');
      const fontPath = resolve(pkgPath, 'font');
      
      if (!statSync(pkgPath).isDirectory()) return;
      
      if (existsSync(distPath) && statSync(distPath).isDirectory()) {
        const files = readdirSync(distPath).filter(f => !f.endsWith('.map'));
        if (files.length > 0) {
          libs.push({
            src: `node_modules/${scope}/${pkg}/dist`,
            dest: `libs/${scope}/${pkg}`,
            rename: { stripBase: true }
          });
        }
      }
      else if (existsSync(srcPath) && statSync(srcPath).isDirectory()) {
        const subdirs = readdirSync(srcPath).filter(d => statSync(resolve(srcPath, d)).isDirectory());
        subdirs.forEach(subdir => {
          const files = readdirSync(resolve(srcPath, subdir)).filter(f => 
            f.endsWith('.css') || f.endsWith('.woff') || f.endsWith('.woff2') || f.endsWith('.ttf')
          );
          if (files.length > 0) {
            libs.push({
              src: `node_modules/${scope}/${pkg}/src/${subdir}`,
              dest: `libs/${scope}/${pkg}/${subdir}`,
              rename: { stripBase: true }
            });
          }
        });
      }
      else if (existsSync(cssPath) && statSync(cssPath).isDirectory()) {
        libs.push({
          src: `node_modules/${scope}/${pkg}/css`,
          dest: `libs/${scope}/${pkg}/css`,
          rename: { stripBase: true }
        });
      }
      else if (existsSync(fontPath) && statSync(fontPath).isDirectory()) {
        libs.push({
          src: `node_modules/${scope}/${pkg}/font`,
          dest: `libs/${scope}/${pkg}/font`,
          rename: { stripBase: true }
        });
      }
    });
  });
  
  return libs;
}

// Custom plugin to natively copy auto-detected libraries, bypassing Rollup tracking for speed
function autoCopyLibsPlugin() {
    let copied = false;
    const doCopy = () => {
        if (copied) return;
        copied = true;
        const libs = autoDetectLibs();
        libs.forEach(lib => {
            const srcPath = resolve(__dirname, lib.src);
            const destPath = resolve(__dirname, 'public/build', lib.dest);
            if (existsSync(srcPath)) {
                cpSync(srcPath, destPath, { recursive: true, force: true });
            }
        });
        console.log(`\n📦 Auto-copied ${libs.length} auto-detected libraries!\n`);
    };

    return {
        name: 'auto-copy-libs',
        configureServer(server) {
            server.httpServer?.once('listening', () => {
                doCopy();
            });
            // Fallback for some setups
            setTimeout(doCopy, 1000);
        },
        closeBundle() {
            doCopy();
        }
    }
}

export default defineConfig(({ command }) => ({
    plugins: [
        inertia({ ssr: false }),
        vue(),
        templateAssets(__dirname),
        laravel({
            input: [
                'resources/css/style.css',
                'resources/js/apex-chart-data.js',
                'resources/js/calendar-data.js',
                'resources/js/charjs-chart-data.js',
                'resources/js/chat.js',
                'resources/js/clipboard.js',
                'resources/js/coming-soon.js',
                'resources/js/datatable.js',
                'resources/js/dragula.js',
                'resources/js/echart-chart-data.js',
                'resources/js/form-quill.js',
                'resources/js/lightbox.js',
                'resources/js/range-slider.js',
                'resources/js/script.js',
                'resources/js/theme-script.js',
                'resources/js/app.ts',
            ],
            hotFile: resolve(__dirname, 'public/hot'),
            refresh: true,
        }),
        tailwindcss(),
        ...(command === 'build' ? [
            autoCopyLibsPlugin(),
            viteStaticCopy({
            targets: [
                { src: 'node_modules/lucide-static/font', dest: 'libs/lucide-static/font', rename: { stripBase: true } },
                { src: 'node_modules/@fortawesome/fontawesome-free/webfonts', dest: 'libs/@fortawesome/fontawesome-free/webfonts', rename: { stripBase: true } },
                { src: "node_modules/echarts-countries-js/echarts-countries-js", dest: "libs/echarts-countries-js/echarts-countries-js", rename: { stripBase: true }},
                { src: "node_modules/@fullcalendar/core",dest: "libs/@fullcalendar/core", rename: { stripBase: true }},
                { src: "node_modules/@fullcalendar/daygrid",dest: "libs/@fullcalendar/daygrid", rename: { stripBase: true }},
                { src: "node_modules/@fullcalendar/interaction",dest: "libs/@fullcalendar/interaction", rename: { stripBase: true }},
                { src: "node_modules/@fullcalendar/timegrid",dest: "libs/@fullcalendar/timegrid", rename: { stripBase: true }},
                { src: "node_modules/choices.js/public/assets/styles", dest: "libs/choices.js/public/assets/styles", rename: { stripBase: true } },
                { src: "node_modules/choices.js/public/assets/scripts", dest: "libs/choices.js/public/assets/scripts", rename: { stripBase: true } },
                { src: "node_modules/prismjs/themes", dest: "libs/prismjs/themes", rename: { stripBase: true } },
                { src: "node_modules/wnumb/wNumb.min.js", dest: "libs/wnumb", rename: { stripBase: true } },
                { src: 'node_modules/glightbox/dist/css', dest: 'libs/glightbox/css', rename: { stripBase: true } },
                { src: 'node_modules/glightbox/dist/js', dest: 'libs/glightbox/js', rename: { stripBase: true } },
                { src: 'node_modules/datatables.net/js', dest: 'libs/datatables.net/js', rename: { stripBase: true } },
                { src: 'node_modules/datatables.net-dt/css', dest: 'libs/datatables.net-dt/css', rename: { stripBase: true } },
                { src: "node_modules/@tabler/icons-webfont/dist", dest: "libs/@tabler/icons-webfont", rename: { stripBase: true } },                
                // Copy resources images to public/build/img
                { src: 'resources/img/**/*', dest: 'img', rename: { stripBase: 2 } },
            ],
            }),
        ] : []),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: 'http://localhost:5173',
        cors: {
            origin: 'http://localhost:8088',
        },
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
        },
        watch: {
            interval: 1000,
            binaryInterval: 2000,
            ignored: [
                /[/\\]public[/\\]build(?:[/\\]|$)/,
                /[/\\]storage(?:[/\\]|$)/,
                /[/\\]vendor(?:[/\\]|$)/,
            ],
        },
    },
    build: {
        outDir: 'public/build',
        rolldownOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                        return 'css/[name]-[hash][extname]';
                    }
                    if (assetInfo.name && assetInfo.name.match(/\.(png|jpe?g|gif|svg)$/)) {
                        return 'img/[name][extname]';
                    }
                    return '[name][extname]';
                },
                entryFileNames: 'js/[name]-[hash].js',
                chunkFileNames: 'js/[name]-[hash].js',
            },
        },
    },
}));
