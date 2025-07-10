<template>
    <div>

        <Head>
            <!-- CSS Lokal -->
            <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css" />
            <link rel="stylesheet" href="/assets/vendor/font-awesome/css/font-awesome.min.css" />
            <link rel="stylesheet" href="/assets/vendor/toastr/toastr.min.css" />
            <link rel="stylesheet" href="/assets/css/main.css" />

            <!-- DataTables CDN CSS -->
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
        </Head>

        <!-- Loader -->
        <div v-if="showLoader" class="page-loader-wrapper">
            <div class="loader">
                <div class="m-t-30">
                    <img src="../../js/images/logo.svg" width="60" height="60" alt="Iconic" />
                </div>
                <p>Please wait...</p>
            </div>
        </div>

        <!-- Main content -->
        <div v-else>
            <Navbar />
            <Sidebar />
            <Rightbar />

            <div id="main-content">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import Rightbar from '@/components/Rightbar.vue'

const showLoader = ref(true)

// Function to load external JS
const loadScript = (src) => {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script')
        script.src = src
        script.async = false
        script.onload = resolve
        script.onerror = reject
        document.body.appendChild(script)
    })
}

onMounted(async () => {
    setTimeout(async () => {
        try {
            showLoader.value = false

            // Load internal scripts first
            await loadScript('/assets/bundles/libscripts.bundle.js')
            await loadScript('/assets/bundles/vendorscripts.bundle.js')
            
            // ✅ Now jQuery should be available from bundles
            console.log('jQuery version after bundles:', window.jQuery?.fn?.jquery)
            
            // ✅ Make sure jQuery is globally available
            window.$ = window.jQuery = window.jQuery || $
            
            // ✅ Load DataTables after all jQuery setup is complete
            await loadScript('https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js')
            
            // Force DataTable registration if it didn't work
            if (!window.jQuery.fn.DataTable) {
                console.warn('DataTable not registered, trying manual approach')
                // Try different CDN
                await loadScript('https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js')
            }
            
            console.log('DataTable loaded:', !!(window.jQuery && window.jQuery.fn.DataTable))
            console.log('DataTable function type:', typeof window.jQuery?.fn?.DataTable)
            console.log('jQuery.fn keys containing DataTable:', Object.keys(window.jQuery.fn).filter(key => key.toLowerCase().includes('data')))
            
            // Ensure DataTable is fully registered
            await new Promise(resolve => setTimeout(resolve, 300))
            await loadScript('/assets/vendor/toastr/toastr.js')
            await loadScript('/assets/bundles/c3.bundle.js')
            await loadScript('/assets/bundles/mainscripts.bundle.js')
            
            // Define Iconic stub to prevent errors
            window.Iconic = window.Iconic || {
                colors: {
                    "theme-cyan1": "#17a2b8",
                    "theme-cyan2": "#6fd8e7",
                    "theme-red": "#dc3545",
                    "theme-green": "#28a745"
                }
            }
            
            // Load index.js after defining Iconic
            try {
                await loadScript('/assets/index.js')
            } catch (error) {
                console.warn('Failed to load index.js:', error)
            }

            // ✅ Trigger event after everything is loaded
            console.log('Triggering jquery-datatables-loaded event')
            window.dispatchEvent(new Event('jquery-datatables-loaded'))
            
        } catch (error) {
            console.error('Error loading scripts:', error)
        }
    }, 1000) // after 1s
})
</script>
