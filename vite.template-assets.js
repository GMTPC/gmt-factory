import { copyFile, mkdir, readdir, stat, utimes } from 'node:fs/promises';
import { dirname, extname, join, relative, resolve } from 'node:path';

// Blade uses /build URLs in both dev and production. Prepare only its runtime
// assets in dev; copying every package's dist folder makes Windows mounts slow.
const targets = [
    ['resources/img', 'img'],
    ['node_modules/@phosphor-icons/web/src/duotone', 'libs/@phosphor-icons/web/duotone'],
    ['node_modules/@phosphor-icons/web/src/regular', 'libs/@phosphor-icons/web/regular'],
    ['node_modules/@phosphor-icons/web/src/fill', 'libs/@phosphor-icons/web/fill'],
    ['node_modules/lucide-static/font', 'libs/lucide-static/font'],
    ['node_modules/@fortawesome/fontawesome-free/css/fontawesome.min.css', 'libs/@fortawesome/fontawesome-free/css/fontawesome.min.css'],
    ['node_modules/@fortawesome/fontawesome-free/css/all.min.css', 'libs/@fortawesome/fontawesome-free/css/all.min.css'],
    ['node_modules/@fortawesome/fontawesome-free/webfonts', 'libs/@fortawesome/fontawesome-free/webfonts'],
    ['node_modules/simplebar/dist/simplebar.min.css', 'libs/simplebar/simplebar.min.css'],
    ['node_modules/simplebar/dist/simplebar.min.js', 'libs/simplebar/simplebar.min.js'],
    ['node_modules/preline/dist/preline.js', 'libs/preline/preline.js'],
    ['node_modules/prismjs/themes', 'libs/prismjs/themes'],
    ['node_modules/flatpickr/dist/flatpickr.min.js', 'libs/flatpickr/flatpickr.min.js'],
    ['node_modules/flatpickr/dist/flatpickr.min.css', 'libs/flatpickr/flatpickr.min.css'],
    ['node_modules/flatpickr/dist/flatpickr.css', 'libs/flatpickr/flatpickr.css'],
    ['node_modules/choices.js/public/assets/styles/choices.min.css', 'libs/choices.js/public/assets/styles/choices.min.css'],
    ['node_modules/choices.js/public/assets/scripts/choices.min.js', 'libs/choices.js/public/assets/scripts/choices.min.js'],
    ['node_modules/nouislider/dist/nouislider.min.js', 'libs/nouislider/nouislider.min.js'],
    ['node_modules/nouislider/dist/nouislider.min.css', 'libs/nouislider/nouislider.min.css'],
    ['node_modules/wnumb/wNumb.min.js', 'libs/wnumb/wNumb.min.js'],
    ['node_modules/dragula/dist/dragula.min.js', 'libs/dragula/dragula.min.js'],
    ['node_modules/dragula/dist/dragula.min.css', 'libs/dragula/dragula.min.css'],
    ['node_modules/glightbox/dist/js/glightbox.min.js', 'libs/glightbox/js/glightbox.min.js'],
    ['node_modules/glightbox/dist/css/glightbox.min.css', 'libs/glightbox/css/glightbox.min.css'],
    ['node_modules/glightbox/dist/css/glightbox.css', 'libs/glightbox/css/glightbox.css'],
    ['node_modules/quill/dist/quill.js', 'libs/quill/quill.js'],
    ['node_modules/quill/dist/quill.core.css', 'libs/quill/quill.core.css'],
    ['node_modules/quill/dist/quill.snow.css', 'libs/quill/quill.snow.css'],
    ['node_modules/quill/dist/quill.bubble.css', 'libs/quill/quill.bubble.css'],
    ['node_modules/apexcharts/dist/apexcharts.min.js', 'libs/apexcharts/apexcharts.min.js'],
    ['node_modules/echarts/dist/echarts.min.js', 'libs/echarts/echarts.min.js'],
    ['node_modules/echarts-countries-js/echarts-countries-js/world.js', 'libs/echarts-countries-js/echarts-countries-js/world.js'],
    ['node_modules/@fullcalendar/core/index.global.min.js', 'libs/@fullcalendar/core/index.global.min.js'],
    ['node_modules/@fullcalendar/daygrid/index.global.min.js', 'libs/@fullcalendar/daygrid/index.global.min.js'],
    ['node_modules/@fullcalendar/interaction/index.global.min.js', 'libs/@fullcalendar/interaction/index.global.min.js'],
    ['node_modules/chart.js/dist/chart.umd.min.js', 'libs/chart.js/chart.umd.min.js'],
    ['node_modules/jquery/dist/jquery.min.js', 'libs/jquery/jquery.min.js'],
    ['node_modules/datatables.net/js/dataTables.min.js', 'libs/datatables.net/js/dataTables.min.js'],
    ['node_modules/datatables.net-dt/css/dataTables.dataTables.min.css', 'libs/datatables.net-dt/css/dataTables.dataTables.min.css'],
    ['node_modules/clipboard/dist/clipboard.min.js', 'libs/clipboard/clipboard.min.js'],
    ['node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css', 'libs/@tabler/icons-webfont/tabler-icons.min.css'],
    ['node_modules/@tabler/icons-webfont/dist/fonts', 'libs/@tabler/icons-webfont/fonts'],
];

const runtimeExtensions = new Set(['.js', '.css', '.woff', '.woff2', '.ttf', '.eot', '.svg', '.png', '.jpg', '.jpeg', '.gif', '.webp', '.ico', '.avif']);

async function copyChanged(source, destination) {
    const sourceStat = await stat(source).catch((error) => {
        if (error.code === 'ENOENT') return null;
        throw error;
    });
    if (!sourceStat) return;

    if (sourceStat.isDirectory()) {
        await Promise.all((await readdir(source)).map((entry) =>
            copyChanged(join(source, entry), join(destination, entry))));
        return;
    }

    if (!runtimeExtensions.has(extname(source))) return;
    const destinationStat = await stat(destination).catch((error) => {
        if (error.code === 'ENOENT') return null;
        throw error;
    });
    if (destinationStat?.size === sourceStat.size && Math.abs(destinationStat.mtimeMs - sourceStat.mtimeMs) < 1) return;

    await mkdir(dirname(destination), { recursive: true });
    await copyFile(source, destination);
    await utimes(destination, sourceStat.atime, sourceStat.mtime);
}

export function templateAssets(root) {
    return {
        name: 'template-assets-dev',
        apply: 'serve',
        async configureServer(server) {
            const startedAt = Date.now();
            server.config.logger.info('Preparing template assets...');
            // Await preparation before accepting requests, so first load has no 404s.
            await Promise.all(targets.map(async ([source, destination]) => {
                await copyChanged(resolve(root, source), resolve(root, 'public/build', destination));
            }));
            server.config.logger.info(`Template assets ready in ${Date.now() - startedAt} ms`);

            const images = resolve(root, 'resources/img');
            const updateImage = async (file) => {
                const path = relative(images, file);
                if (path.startsWith('..') || path === '') return;
                try {
                    await copyChanged(file, resolve(root, 'public/build/img', path));
                    server.ws.send({ type: 'full-reload' });
                } catch (error) {
                    server.config.logger.error(`Unable to update template image: ${error.message}`);
                }
            };
            server.watcher.on('add', updateImage).on('change', updateImage);
        },
    };
}
