import '../css/app.css';
<<<<<<< HEAD
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
=======
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createInertiaApp({
<<<<<<< HEAD
    // Aquí le decimos que busque los archivos .vue dentro de la carpeta resources/js/pages/
=======
>>>>>>> 7d0685b4379ba4764a11a7f976b77bb0be3b5bb1
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
