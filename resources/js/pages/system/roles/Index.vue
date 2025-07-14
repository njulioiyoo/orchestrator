<!-- Roles Index Page -->
<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Roles</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <PageHeaderWithCreateButton title="Roles" buttonLink="/system/roles/create" />
                    <ReusableDataTable ref="rolesDataTable" :data-url="dataUrl" :columns="tableColumns"
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
import { ref } from 'vue'

defineOptions({
    layout: AppLayout
})

const rolesDataTable = ref(null)

const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Roles' },
]

const dataUrl = '/system/roles/data'
const deleteUrl = '/system/roles/:id'

const tableColumns = [
    {
        key: 'id',
        title: 'No',
        name: 'id',
        type: 'number'
    },
    {
        key: 'name',
        title: 'Role Name',
        name: 'name',
        type: 'text'
    },
    {
        key: 'users_count',
        title: 'Users Count',
        name: 'users_count',
        type: 'number',
        orderable: false
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

const onDataLoaded = (data) => {
    // Roles data loaded
}

const onDeleteSuccess = ({ id, response }) => {
    // Role deleted successfully
}

const onError = (error) => {
    // DataTable error
}
</script>