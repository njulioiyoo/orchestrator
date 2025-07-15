<!-- Tenants Index Page -->
<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Tenants</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <PageHeaderWithCreateButton title="Tenants" buttonLink="/system/tenants/create" />
                    <ReusableDataTable ref="tenantsDataTable" :data-url="dataUrl" :columns="tableColumns"
                        :delete-url="deleteUrl" :auto-refresh="true" :refresh-interval="30000"
                        @data-loaded="onDataLoaded" @delete-success="onDeleteSuccess" @error="onError" />
                </div>
            </div>
        </div>
        
        <!-- Tenant Detail Modal -->
        <div class="modal fade" id="tenantDetailModal" ref="modalRef" tabindex="-1" aria-labelledby="tenantDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tenantDetailModalLabel">
                            Tenant Details: {{ selectedTenant?.name }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="isLoading" class="text-center py-4">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading tenant details...</p>
                        </div>
                        
                        <div v-else-if="selectedTenant" class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Name</label>
                                <p class="mb-0">{{ selectedTenant.name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Slug</label>
                                <p class="mb-0">
                                    <span class="badge bg-secondary">{{ selectedTenant.slug }}</span>
                                </p>
                            </div>
                        </div>
                        
                        <div v-if="selectedTenant" class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Domain</label>
                                <p class="mb-0">{{ selectedTenant.domain || 'Not set' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Subdomain</label>
                                <p class="mb-0">{{ selectedTenant.subdomain || 'Not set' }}</p>
                            </div>
                        </div>
                        
                        <div v-if="selectedTenant" class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="mb-0">
                                    <span class="badge" :class="selectedTenant.is_active ? 'bg-success' : 'bg-danger'">
                                        {{ selectedTenant.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Expires At</label>
                                <p class="mb-0" :class="isExpired(selectedTenant.expires_at) ? 'text-danger' : ''">
                                    {{ selectedTenant.expires_at ? formatDate(selectedTenant.expires_at) : 'Never' }}
                                    <span v-if="isExpired(selectedTenant.expires_at)" class="badge bg-danger ms-2">Expired</span>
                                </p>
                            </div>
                        </div>
                        
                        <div v-if="selectedTenant" class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created</label>
                                <p class="mb-0">{{ formatDate(selectedTenant.created_at) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Last Updated</label>
                                <p class="mb-0">{{ formatDate(selectedTenant.updated_at) }}</p>
                            </div>
                        </div>

                        <!-- Users -->
                        <div v-if="selectedTenant" class="mt-4">
                            <label class="form-label fw-bold">Users ({{ selectedTenant.users?.length || 0 }})</label>
                            <div v-if="selectedTenant.users?.length === 0" class="text-muted">
                                No users found
                            </div>
                            <div v-else class="list-group">
                                <div v-for="user in selectedTenant.users" :key="user.id" class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ user.name }}</h6>
                                            <small class="text-muted">{{ user.email }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
                        <button 
                            v-if="selectedTenant" 
                            type="button" 
                            class="btn btn-primary" 
                            @click="editTenant"
                        >
                            <i class="fa fa-edit me-1"></i>Edit
                        </button>
                    </div>
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
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

defineOptions({
    layout: AppLayout
})

const tenantsDataTable = ref(null)
const selectedTenant = ref(null)
const modalRef = ref(null)
const isLoading = ref(false)
const currentEncryptedId = ref(null)
let modalInstance = null

const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Tenants' },
]

const dataUrl = '/system/tenants/data'
const deleteUrl = '/system/tenants/:id'

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
        key: 'slug',
        title: 'Slug',
        name: 'slug',
        type: 'text'
    },
    {
        key: 'domain',
        title: 'Domain',
        name: 'domain',
        type: 'text'
    },
    {
        key: 'subdomain',
        title: 'Subdomain',
        name: 'subdomain',
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
        key: 'is_active',
        title: 'Status',
        name: 'is_active',
        type: 'boolean'
    },
    {
        key: 'expires_at',
        title: 'Expires At',
        name: 'expires_at',
        type: 'datetime',
        format: 'DD MMM YYYY'
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
    // Tenants data loaded
}

const onDeleteSuccess = ({ id, response }) => {
    // Tenant deleted successfully
}

const onError = (error) => {
    // DataTable error
}

// Modal functions
const viewTenant = async (encryptedId) => {
    try {
        // Store the encrypted ID
        currentEncryptedId.value = encryptedId
        
        // Show modal first
        if (!modalInstance && modalRef.value) {
            modalInstance = new window.bootstrap.Modal(modalRef.value)
        }
        if (modalInstance) {
            modalInstance.show()
        }
        
        // Set loading state
        isLoading.value = true
        selectedTenant.value = null
        
        // Fetch data
        const response = await axios.get(`/system/tenants/${encryptedId}/detail`)
        selectedTenant.value = response.data.tenant
        
    } catch (error) {
        console.error('Error fetching tenant details:', error)
        alert('Error loading tenant details')
        closeModal()
    } finally {
        isLoading.value = false
    }
}

const closeModal = () => {
    if (modalInstance) {
        modalInstance.hide()
    }
    selectedTenant.value = null
    currentEncryptedId.value = null
    isLoading.value = false
}

const editTenant = () => {
    const encryptedId = selectedTenant.value?.encrypted_id || currentEncryptedId.value
    
    if (encryptedId) {
        closeModal()
        router.visit(`/system/tenants/${encryptedId}/edit`)
    }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}

const isExpired = (dateString) => {
    if (!dateString) return false
    return new Date(dateString) < new Date()
}

// Setup event listeners for view buttons
onMounted(() => {
    // Listen for view button clicks
    document.addEventListener('click', (e) => {
        if (e.target.closest('.js-view')) {
            e.preventDefault()
            const button = e.target.closest('.js-view')
            const encryptedId = button.dataset.id
            if (encryptedId) {
                viewTenant(encryptedId)
            }
        }
    })
})
</script>