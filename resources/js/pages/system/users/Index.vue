<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Users</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <PageHeaderWithCreateButton title="Users" buttonLink="/system/users/create" />

                    <ReusableDataTable ref="usersDataTable" :data-url="dataUrl" :columns="tableColumns"
                        :delete-url="deleteUrl" :auto-refresh="true" :refresh-interval="30000"
                        @data-loaded="onDataLoaded" @delete-success="onDeleteSuccess" @error="onError" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import PageHeaderWithCreateButton from '@/components/page-parts/PageHeaderWithCreateButton.vue'
import ReusableDataTable from '@/components/table/ReusableDataTable.vue'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout
})

const page = usePage()
const usersDataTable = ref(null)

// Breadcrumb configuration
const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Users' },
]

// DataTable configuration
const dataUrl = '/system/users/data'
const deleteUrl = '/system/users/:id'

// Konfigurasi kolom yang dinamis
const tableColumns = [
    {
        key: 'id',
        title: 'No',
        name: 'id',
        type: 'number'
    },
    {
        key: 'name',
        title: 'Name',
        name: 'name',
        type: 'text'
    },
    {
        key: 'email',
        title: 'Email',
        name: 'email',
        type: 'text'
    },
    {
        key: 'created_at',
        title: 'Created At',
        name: 'created_at',
        type: 'datetime',
        format: 'DD MMM YYYY HH:mm:ss'
    },
    {
        key: 'updated_at',
        title: 'Updated At',
        name: 'updated_at',
        type: 'datetime',
        format: 'DD MMM YYYY HH:mm:ss'
    },
    {
        key: 'action',
        title: 'Action',
        name: 'action',
        orderable: false,
        searchable: false,
        type: 'custom'
    }
]

// Event handlers
const onDataLoaded = (data) => {
    console.log('Data loaded:', data.length, 'records')
}

const onDeleteSuccess = ({ id, response }) => {
    console.log('User deleted successfully:', id)
}

const onError = (error) => {
    console.error('DataTable error:', error)
}

// Function untuk reload data dari luar komponen
const reloadTable = () => {
    if (usersDataTable.value) {
        usersDataTable.value.reloadDataTable()
    }
}

// Cek localStorage untuk user baru dibuat
const checkAndHandleNewUserCreated = () => {
    try {
        const newUserCreated = localStorage.getItem('newUserCreated')

        if (newUserCreated === 'true') {
            console.log('New user created flag detected, reloading table')
            localStorage.removeItem('newUserCreated')
            localStorage.removeItem('newUserTimestamp')

            // Reload table
            setTimeout(() => {
                reloadTable()
            }, 500)
        }
    } catch (error) {
        console.error('Error checking localStorage:', error)
    }
}

// Event handlers
const userCreatedHandler = () => {
    console.log('Event user-created terdeteksi')
    reloadTable()
}

const inertiaNavigationHandler = (event) => {
    const detail = event?.detail || {}
    const url = detail?.page?.url || window.location.pathname

    if (url && url === '/system/users') {
        setTimeout(checkAndHandleNewUserCreated, 100)
    }
}

const inertiaFinishHandler = (event) => {
    const detail = event?.detail || {}
    const url = detail?.page?.url || window.location.pathname

    if (url && url === '/system/users') {
        checkAndHandleNewUserCreated()
    }
}

// Lifecycle hooks
onMounted(() => {
    console.log('Users page mounted')

    // Setup event listeners
    window.addEventListener('user-created', userCreatedHandler)
    window.addEventListener('inertia:navigate', inertiaNavigationHandler)
    window.addEventListener('inertia:finish', inertiaFinishHandler)

    // Tab visibility change handler
    const visibilityChangeHandler = () => {
        if (document.visibilityState === 'visible') {
            console.log('Tab menjadi aktif, checking for updates')
            checkAndHandleNewUserCreated()
        }
    }
    document.addEventListener('visibilitychange', visibilityChangeHandler)

    // Check for flash messages or localStorage flags
    if (page.props.flash && page.props.flash.success) {
        console.log('Flash message detected:', page.props.flash.success)
        setTimeout(() => {
            reloadTable()
        }, 1000)
    } else {
        // Check localStorage on mount
        checkAndHandleNewUserCreated()
    }

    // Cleanup pada unmount
    onBeforeUnmount(() => {
        console.log('Users page unmounting, cleaning up event listeners')
        window.removeEventListener('user-created', userCreatedHandler)
        window.removeEventListener('inertia:navigate', inertiaNavigationHandler)
        window.removeEventListener('inertia:finish', inertiaFinishHandler)
        document.removeEventListener('visibilitychange', visibilityChangeHandler)
    })
})

// Expose functions untuk debugging atau penggunaan dari luar
defineExpose({
    reloadTable,
    getDataTableInstance: () => usersDataTable.value?.getDataTableInstance()
})
</script>