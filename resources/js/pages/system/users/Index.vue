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
                    <PageHeaderWithCreateButton 
                        title="Users" 
                        :buttonLink="canCreateUsers ? '/system/users/create' : null"
                        v-permission="'create users'" />

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
import { ref, watch, onMounted } from 'vue'
import { usePermissions } from '@/composables/usePermissions.js'
import { useUserStore } from '@/stores/userStore.js'

defineOptions({
    layout: AppLayout
})

const usersDataTable = ref(null)
const { canCreateUsers, canEditUsers, canDeleteUsers } = usePermissions()
const userStore = useUserStore()

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

// Function to reload DataTable
const reloadTable = () => {
    if (usersDataTable.value) {
        usersDataTable.value.reloadDataTable()
    }
}

// Simple event handlers for DataTable
const onDataLoaded = (data) => {
    // Data loaded
}

const onDeleteSuccess = ({ id, response }) => {
    // Trigger user deleted in store
    userStore.userDeleted(id)
}

const onError = (error) => {
    // DataTable error
}

// Watch for store changes to refresh DataTable
watch(
    () => userStore.userTableRefreshTrigger,
    (newValue, oldValue) => {
        if (newValue !== oldValue && newValue > 0) {
            setTimeout(() => {
                reloadTable()
            }, 100) // Small delay to ensure any navigation is complete
        }
    }
)

// Lifecycle
onMounted(() => {
    // Check if there's a recent action when component mounts
    const lastAction = userStore.getLastAction()
    if (lastAction && lastAction.timestamp > Date.now() - 5000) { // Within last 5 seconds
        setTimeout(() => {
            reloadTable()
        }, 500)
    }
})
</script>