<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Permissions</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <PageHeaderWithCreateButton 
                        title="Permissions" 
                        buttonLink="/system/permissions/create" />

                    <ReusableDataTable ref="permissionsDataTable" :data-url="dataUrl" :columns="tableColumns"
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
// Removed unused import

defineOptions({
    layout: AppLayout
})

const page = usePage()
const permissionsDataTable = ref(null)
// Removed unused permission composable

// Breadcrumb configuration
const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Permissions' },
]

// DataTable configuration
const dataUrl = '/system/permissions/data'
const deleteUrl = '/system/permissions/:id'

// Table columns configuration
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
        key: 'group',
        title: 'Group',
        name: 'group',
        type: 'text'
    },
    {
        key: 'guard_name',
        title: 'Guard',
        name: 'guard_name',
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
    // Data loaded
}

const onDeleteSuccess = ({ id, response }) => {
    // Permission deleted successfully
}

const onError = (error) => {
    // DataTable error
}

// Function to reload data
const reloadTable = () => {
    if (permissionsDataTable.value) {
        permissionsDataTable.value.reloadDataTable()
    }
}

// Check and handle new permission created
const checkAndHandleNewPermissionCreated = () => {
    try {
        const newPermissionCreated = localStorage.getItem('newPermissionCreated')

        if (newPermissionCreated === 'true') {
            localStorage.removeItem('newPermissionCreated')
            localStorage.removeItem('newPermissionTimestamp')

            setTimeout(() => {
                reloadTable()
            }, 500)
        }
    } catch (error) {
        // Error checking localStorage
    }
}

// Lifecycle hooks
onMounted(() => {
    // Check for flash messages
    if (page.props.flash && page.props.flash.success) {
        setTimeout(() => {
            reloadTable()
        }, 1000)
    } else {
        checkAndHandleNewPermissionCreated()
    }
})

// Expose functions
defineExpose({
    reloadTable,
    getDataTableInstance: () => permissionsDataTable.value?.getDataTableInstance()
})
</script>