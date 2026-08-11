import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import LoadingOverlay from './Components/LoadingOverlay.vue';

import '../css/app.css';
import './bootstrap';

const appName = window.document.documentElement.dataset.page || 'E-Archive STTNI';

createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h('div', [h(App, props), h(LoadingOverlay)]),
        })
            .use(plugin)
            .use(ZiggyVue, {
                ...props.initialPage.props.ziggy,
                url: window.location.origin,
                location: new URL(window.location.href),
            })
            .mount(el);
    },
});
