<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import UserForm from './partials/UserForm.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    user: Object,
})

const breadcrumbItems = [
    { label: 'System', link: '#' },
    { label: 'Users', link: '/system/users' },
    { label: 'Edit' },
]
</script>

<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>Edit User</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-6">
                <div class="card">
                    <div class="header">
                        <h2>Edit User</h2>
                    </div>
                    <div class="body">
                        <!-- Submit URL PUT ke /system/users/{id} -->
                        <UserForm :user="user" :submit-url="`/system/users/${user.id}`" :is-edit="true" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
