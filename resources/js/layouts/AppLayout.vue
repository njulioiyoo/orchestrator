<template>
    <div>

        <Head>
            <!-- CSS Lokal -->
            <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css" />
            <link rel="stylesheet" href="/assets/vendor/font-awesome/css/font-awesome.min.css" />
            <link rel="stylesheet" href="/assets/vendor/toastr/toastr.min.css" />
            <link rel="stylesheet" href="assets/vendor/charts-c3/plugin.css" />
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
            <Navbar @search="handleGlobalSearch" />
            <Sidebar />
            <Rightbar />

            <div id="main-content">
                <slot />
            </div>

            <!-- Global Search Modal -->
            <GlobalSearchModal 
                :show="showSearchModal"
                :query="searchQuery"
                @close="closeSearchModal"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import Rightbar from '@/components/Rightbar.vue'
import GlobalSearchModal from '@/components/GlobalSearchModal.vue'

const showLoader = ref(true)
const showSearchModal = ref(false)
const searchQuery = ref('')
const { props } = usePage()

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

            // Override any existing toastr welcome messages
            if (typeof toastr !== 'undefined') {
                // Clear any existing toastr messages first
                toastr.clear();
                
                // Clear any persistent toast containers that might have been created by template
                setTimeout(() => {
                    const toastContainers = document.querySelectorAll('#toast-container, .toast-container');
                    toastContainers.forEach(container => {
                        const welcomeToasts = container.querySelectorAll('.toast:has(.toast-message)');
                        welcomeToasts.forEach(toast => {
                            const message = toast.querySelector('.toast-message');
                            if (message && message.textContent.includes('Hello, welcome to Iconic')) {
                                toast.remove();
                            }
                        });
                    });
                }, 100);

                // Check for login success message from Inertia props
                const loginSuccess = props.flash?.login_success;
                if (loginSuccess) {
                    toastr.info(loginSuccess.message);
                }
            }

            window.dispatchEvent(new Event('jquery-datatables-loaded'))

        } catch (error) {
            console.error('Error loading scripts:', error)
        }
    }, 1000) // after 1s
})

// Global search functionality
const handleGlobalSearch = (query) => {
    searchQuery.value = query
    showSearchModal.value = true
    
    // Show modal using Bootstrap if available
    setTimeout(() => {
        if (typeof window.$ !== 'undefined') {
            window.$('#globalSearchModal').modal('show')
        }
    }, 100)
}

const closeSearchModal = () => {
    showSearchModal.value = false
    searchQuery.value = ''
    
    // Hide modal using Bootstrap if available
    if (typeof window.$ !== 'undefined') {
        window.$('#globalSearchModal').modal('hide')
    }
}

// Additional cleanup for persistent welcome toasts
onMounted(() => {
    // Run cleanup multiple times to catch any delayed toast creation
    const cleanupIntervals = [500, 2000, 5000];
    
    cleanupIntervals.forEach(delay => {
        setTimeout(() => {
            const toastContainers = document.querySelectorAll('#toast-container, .toast-container');
            toastContainers.forEach(container => {
                const toasts = container.querySelectorAll('.toast');
                toasts.forEach(toast => {
                    const message = toast.querySelector('.toast-message');
                    if (message && message.textContent.includes('Hello, welcome to Iconic')) {
                        toast.remove();
                        console.log('Removed persistent welcome toast');
                    }
                });
            });
        }, delay);
    });
})
</script>
