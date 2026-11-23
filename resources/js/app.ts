import { createInertiaApp, router } from '@inertiajs/vue3'
import { createApp, h, nextTick } from 'vue'

const pages = import.meta.glob('./pages/**/*.vue')

router.on('navigate', () => {
    nextTick(() => {
        const template = window as Window & { HSStaticMethods?: { autoInit: () => void } }
        template.HSStaticMethods?.autoInit()
    })
})

createInertiaApp({
    resolve: async (name) => {
        const page = pages[`./pages/${name}.vue`]
        if (!page) throw new Error(`Inertia page not found: ${name}`)
        return await page() as any
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
