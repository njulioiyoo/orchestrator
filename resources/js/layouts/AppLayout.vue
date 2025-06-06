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
        showLoader.value = false

        // ✅ Load jQuery and DataTables from CDN
        await loadScript('https://code.jquery.com/jquery-3.6.0.min.js')
        await loadScript('https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js')

        // Trigger event: scripts loaded
        window.dispatchEvent(new Event('jquery-datatables-loaded'))

        // Load other internal scripts
        await loadScript('/assets/bundles/libscripts.bundle.js')
        await loadScript('/assets/bundles/vendorscripts.bundle.js')
        await loadScript('/assets/vendor/toastr/toastr.js')
        await loadScript('/assets/bundles/c3.bundle.js')
        await loadScript('/assets/bundles/mainscripts.bundle.js')
        await loadScript('/assets/index.js')

        // ✅ Make jQuery globally available
        window.$ = window.jQuery = window.jQuery || jQuery
    }, 1000) // after 1s
})
</script>
