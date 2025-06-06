<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import UserForm from './partials/UserForm.vue'

defineOptions({ layout: AppLayout })

const breadcrumbItems = [
    { label: 'System', link: '#' },
    { label: 'Users', link: '/system/users' },
    { label: 'Create' },
]
</script>

<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>Create User</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-6">
                <div class="card">
                    <div class="header">
                        <h2>New User</h2>
                    </div>
                    <div class="body">
                        <!-- Submit URL harus POST ke /system/users -->
                        <UserForm :submit-url="'/system/users'" :is-edit="false" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
