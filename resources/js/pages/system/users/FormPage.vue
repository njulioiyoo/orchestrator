<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import UserForm from './partials/UserForm.vue'
import { usePage } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })

const props = defineProps({
    user: Object,
})

const page = usePage()
const isEdit = !!props.user
const roles = page.props.roles
const userRoles = page.props.userRoles

// Props correctly passed to UserForm component

const title = isEdit ? 'Edit User' : 'Create User'
const breadcrumbItems = [
    { label: 'System', link: '#' },
    { label: 'Users', link: '/system/users' },
    { label: isEdit ? 'Edit' : 'Create' },
]

const submitUrl = isEdit ? `/system/users/${props.user.encrypted_id}` : '/system/users'
</script>

<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>{{ title }}</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-6">
                <div class="card">
                    <div class="header">
                        <h2>{{ title }}</h2>
                    </div>
                    <div class="body">
                        <UserForm :user="user" :submit-url="submitUrl" :is-edit="isEdit" :roles="roles" :user-roles="userRoles" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
