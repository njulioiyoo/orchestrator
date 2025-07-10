<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Tambah Menu Baru</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Form Menu Baru</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name (ID) <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="name"
                                            v-model="form.name"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.name }"
                                            placeholder="e.g., master-data"
                                            required
                                        />
                                        <div class="invalid-feedback" v-if="errors.name">
                                            {{ errors.name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="label">Label <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="label"
                                            v-model="form.label"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.label }"
                                            placeholder="e.g., Master Data"
                                            required
                                        />
                                        <div class="invalid-feedback" v-if="errors.label">
                                            {{ errors.label }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="icon">Icon (FontAwesome class)</label>
                                        <input
                                            type="text"
                                            id="icon"
                                            v-model="form.icon"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.icon }"
                                            placeholder="e.g., fa fa-cogs"
                                        />
                                        <div class="invalid-feedback" v-if="errors.icon">
                                            {{ errors.icon }}
                                        </div>
                                        <small class="form-text text-muted">
                                            Preview: <i :class="form.icon" v-if="form.icon"></i>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="parent_id">Parent Menu</label>
                                        <select
                                            id="parent_id"
                                            v-model="form.parent_id"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.parent_id }"
                                        >
                                            <option value="">-- Select Parent Menu --</option>
                                            <option v-for="parent in parentMenus" :key="parent.id" :value="parent.id">
                                                {{ parent.label }}
                                            </option>
                                        </select>
                                        <div class="invalid-feedback" v-if="errors.parent_id">
                                            {{ errors.parent_id }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="url">URL</label>
                                        <input
                                            type="text"
                                            id="url"
                                            v-model="form.url"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.url }"
                                            placeholder="e.g., /master-data"
                                        />
                                        <div class="invalid-feedback" v-if="errors.url">
                                            {{ errors.url }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="route">Route Name</label>
                                        <input
                                            type="text"
                                            id="route"
                                            v-model="form.route"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.route }"
                                            placeholder="e.g., master.index"
                                        />
                                        <div class="invalid-feedback" v-if="errors.route">
                                            {{ errors.route }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sort_order">Sort Order</label>
                                        <input
                                            type="number"
                                            id="sort_order"
                                            v-model="form.sort_order"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.sort_order }"
                                            min="0"
                                            placeholder="0"
                                        />
                                        <div class="invalid-feedback" v-if="errors.sort_order">
                                            {{ errors.sort_order }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="is_active">Status</label>
                                        <select
                                            id="is_active"
                                            v-model="form.is_active"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.is_active }"
                                        >
                                            <option :value="true">Active</option>
                                            <option :value="false">Inactive</option>
                                        </select>
                                        <div class="invalid-feedback" v-if="errors.is_active">
                                            {{ errors.is_active }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Permissions</label>
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                id="permission_admin"
                                                v-model="adminPermission"
                                                class="form-check-input"
                                            />
                                            <label class="form-check-label" for="permission_admin">
                                                Requires Admin Role
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Check this if the menu should only be visible to Admin users.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" :disabled="processing">
                                    <i class="fa fa-save"></i> Simpan
                                </button>
                                <a href="/system/menus" class="btn btn-secondary ml-2">
                                    <i class="fa fa-times"></i> Batal
                                </a>
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
import { router } from '@inertiajs/vue3'
import { useMenuStore } from '@/stores/menuStore.js'
import { ref, watch } from 'vue'

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    parentMenus: Array,
    errors: Object
})

const menuStore = useMenuStore()

const form = ref({
    name: '',
    label: '',
    icon: '',
    url: '',
    route: '',
    parent_id: '',
    sort_order: 0,
    is_active: true,
    permissions: []
})

const adminPermission = ref(false)
const processing = ref(false)

const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Menu Management', link: '/system/menus' },
    { label: 'Create' }
]

watch(adminPermission, (newValue) => {
    if (newValue) {
        form.value.permissions = [{ type: 'role', name: 'Admin' }]
    } else {
        form.value.permissions = []
    }
})

const submit = () => {
    processing.value = true
    
    router.post('/system/menus', form.value, {
        onSuccess: (page) => {
            // Refresh menu store after successful creation
            menuStore.fetchMenus()
        },
        onFinish: () => {
            processing.value = false
        }
    })
}
</script>