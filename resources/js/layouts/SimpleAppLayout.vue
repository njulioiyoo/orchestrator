<template>
    <div>
        <Head>
            <!-- Essential CSS only -->
            <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css" />
            <link rel="stylesheet" href="/assets/vendor/font-awesome/css/font-awesome.min.css" />
            <link rel="stylesheet" href="/assets/css/main.css" />
        </Head>

        <!-- Simple loader -->
        <div v-if="showLoader" class="page-loader-wrapper">
            <div class="loader">
                <div class="m-t-30">
                    <img src="/assets/images/logo.svg" width="60" height="60" alt="Logo" />
                </div>
                <p>Loading...</p>
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
import { ref, onMounted, nextTick } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import Rightbar from '@/components/Rightbar.vue'
import GlobalSearchModal from '@/components/GlobalSearchModal.vue'

const showLoader = ref(true)
const showSearchModal = ref(false)
const searchQuery = ref('')
const { props } = usePage()

// Simple script loader function
const loadScript = (src) => {
    return new Promise((resolve, reject) => {
        // Check if already loaded
        if (document.querySelector(`script[src="${src}"]`)) {
            resolve()
            return
        }
        
        const script = document.createElement('script')
        script.src = src
        script.async = false
        script.onload = resolve
        script.onerror = reject
        document.head.appendChild(script)
    })
}

onMounted(async () => {
    // Simple delay to show loading
    setTimeout(async () => {
        try {
            showLoader.value = false
            
            // Load only essential scripts
            await loadScript('/assets/bundles/libscripts.bundle.js')
            await loadScript('/assets/bundles/vendorscripts.bundle.js')
            
            // Wait for jQuery to be available
            await new Promise(resolve => {
                const checkJQuery = () => {
                    if (window.jQuery) {
                        window.$ = window.jQuery
                        resolve()
                    } else {
                        setTimeout(checkJQuery, 100)
                    }
                }
                checkJQuery()
            })
            
            // Load mainscripts last
            await loadScript('/assets/bundles/mainscripts.bundle.js')
            
            console.log('Essential scripts loaded successfully')
            
        } catch (error) {
            console.warn('Script loading error:', error)
            showLoader.value = false // Still show content even if scripts fail
        }
    }, 800)
})

// Global search functionality
const handleGlobalSearch = (query) => {
    searchQuery.value = query
    showSearchModal.value = true
}

const closeSearchModal = () => {
    showSearchModal.value = false
    searchQuery.value = ''
}
</script>

<style scoped>
.page-loader-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loader {
    text-align: center;
}

.loader p {
    margin-top: 10px;
    color: #666;
}
</style>