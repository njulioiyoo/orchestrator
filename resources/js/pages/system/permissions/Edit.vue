<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Edit Permission</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <div class="header">
                        <h2>Edit Permission</h2>
                    </div>
                    <div class="body">
                        <form @submit.prevent="handleSubmit">
                            <div class="form-group">
                                <label>Permission Name</label>
                                <input v-model="form.name" type="text" class="form-control" 
                                       placeholder="e.g., create posts" required />
                                <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>
                            </div>

                            <div class="form-group mt-3">
                                <label>Group</label>
                                <input v-model="form.group" type="text" class="form-control" 
                                       placeholder="e.g., Post Management" required />
                                <small v-if="errors.group" class="text-danger">{{ errors.group }}</small>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <i class="fa fa-save"></i> {{ form.processing ? 'Updating...' : 'Update Permission' }}
                                </button>
                                <Link href="/system/permissions" class="btn btn-secondary ml-2">
                                    <i class="fa fa-times"></i> Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    permission: {
        type: Object,
        required: true
    }
})

const page = usePage()
const errors = page.props.errors || {}

const form = useForm({
    name: props.permission.name,
    group: props.permission.group || ''
})

const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Permissions', link: '/system/permissions' },
    { label: 'Edit' },
]

const handleSubmit = () => {
    form.put(`/system/permissions/${props.permission.id}`, {
        preserveScroll: true
    })
}
</script>