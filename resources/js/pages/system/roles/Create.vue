<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>{{ isEdit ? 'Edit Role' : 'Create Role' }}</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-8">
                <div class="card">
                    <div class="header">
                        <h2>{{ isEdit ? 'Edit Role' : 'New Role' }}</h2>
                    </div>
                    <div class="body">
                        <form @submit.prevent="handleSubmit">
                            <div class="form-group">
                                <label>Role Name</label>
                                <input v-model="form.name" type="text" class="form-control" />
                                <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>
                            </div>

                            <div class="form-group mt-4">
                                <label>Permissions</label>
                                <div v-for="(groupPermissions, group) in permissions" :key="group" class="mb-3">
                                    <h6 class="text-primary">{{ group }}</h6>
                                    <div class="row">
                                        <div v-for="permission in groupPermissions" :key="permission.id"
                                            class="col-md-3">
                                            <div class="form-check">
                                                <input :id="'permission-' + permission.id" v-model="form.permissions"
                                                    :value="permission.name" type="checkbox" class="form-check-input" />
                                                <label :for="'permission-' + permission.id" class="form-check-label">
                                                    {{ permission.name }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <i class="fa fa-save"></i> {{ form.processing ? 'Saving...' : 'Save' }}
                            </button>
                            <Link href="/system/roles" class="btn btn-secondary ml-2">Cancel</Link>
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
import { useToast } from '@/composables/useToast.js'

defineOptions({ layout: AppLayout })

const props = defineProps({
    role: {
        type: Object,
        default: null,
    },
    permissions: {
        type: Object,
        required: true
    },
    rolePermissions: {
        type: Array,
        default: () => []
    }
})

const isEdit = !!props.role
const toast = useToast()

const form = useForm({
    name: props.role?.name ?? '',
    permissions: props.rolePermissions ?? []
})

const errors = usePage().props.errors || {}

const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Roles', link: '/system/roles' },
    { label: isEdit ? 'Edit' : 'Create' },
]

const handleSubmit = () => {
    const url = isEdit ? `/system/roles/${props.role.encrypted_id}` : '/system/roles'
    const method = isEdit ? form.put.bind(form) : form.post.bind(form)

    method(url, {
        preserveScroll: true,
        onBefore: () => {
            toast.loading(isEdit ? 'Memperbarui role...' : 'Membuat role...')
        },
        onSuccess: () => {
            toast.clear()
            if (isEdit) {
                toast.success('Role berhasil diperbarui!')
            } else {
                toast.success('Role berhasil dibuat!')
                form.reset()
            }
        },
        onError: (errors) => {
            toast.clear()
            toast.error(isEdit ? 'Gagal memperbarui role. Silakan periksa form dan coba lagi.' : 'Gagal membuat role. Silakan periksa form dan coba lagi.')
            console.error('Role form submission failed:', errors)
        }
    })
}
</script>