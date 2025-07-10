import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import axios from 'axios'
import permissionDirective from './directives/permission.js'

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Function to update CSRF token
const updateCsrfToken = () => {
    const token = document.head.querySelector('meta[name="csrf-token"]')
    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
    } else {
        console.warn('CSRF token not found in <meta name="csrf-token">')
    }
}

// Set initial CSRF token
updateCsrfToken()

// Update CSRF token on page navigation
document.addEventListener('inertia:navigate', () => {
    updateCsrfToken()
})

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./pages/**/*.vue', { eager: true })
        return pages[`./pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia()
        
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .directive('permission', permissionDirective)
            .mount(el)
    },
})
