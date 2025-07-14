<template>
    <div>
        <Head :title="`Tenant: ${tenant.name}`" />
        
        <PageHeader 
            :title="`Tenant: ${tenant.name}`" 
            subtitle="View tenant details and manage configuration" 
        />
        
        <div class="row">
            <!-- Tenant Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Tenant Information</h5>
                        <Link :href="route('system.tenants.edit', tenant.id)" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </Link>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Name</label>
                                <p class="mb-0">{{ tenant.name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Slug</label>
                                <p class="mb-0">
                                    <span class="badge bg-secondary">{{ tenant.slug }}</span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Domain</label>
                                <p class="mb-0">{{ tenant.domain || 'Not set' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Subdomain</label>
                                <p class="mb-0">{{ tenant.subdomain || 'Not set' }}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="mb-0">
                                    <span class="badge" :class="tenant.is_active ? 'bg-success' : 'bg-danger'">
                                        {{ tenant.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Expires At</label>
                                <p class="mb-0" :class="isExpired ? 'text-danger' : ''">
                                    {{ tenant.expires_at ? formatDate(tenant.expires_at) : 'Never' }}
                                    <span v-if="isExpired" class="badge bg-danger ms-2">Expired</span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created</label>
                                <p class="mb-0">{{ formatDate(tenant.created_at) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Last Updated</label>
                                <p class="mb-0">{{ formatDate(tenant.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Configuration -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Configuration</h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-plus me-2" :class="tenant.config?.allow_registration ? 'text-success' : 'text-muted'"></i>
                                    <span>Allow Registration</span>
                                    <span class="badge ms-auto" :class="tenant.config?.allow_registration ? 'bg-success' : 'bg-secondary'">
                                        {{ tenant.config?.allow_registration ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-envelope-check me-2" :class="tenant.config?.email_verification ? 'text-success' : 'text-muted'"></i>
                                    <span>Email Verification</span>
                                    <span class="badge ms-auto" :class="tenant.config?.email_verification ? 'bg-success' : 'bg-secondary'">
                                        {{ tenant.config?.email_verification ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-shield-check me-2" :class="tenant.config?.two_factor_auth ? 'text-success' : 'text-muted'"></i>
                                    <span>Two-Factor Auth</span>
                                    <span class="badge ms-auto" :class="tenant.config?.two_factor_auth ? 'bg-success' : 'bg-secondary'">
                                        {{ tenant.config?.two_factor_auth ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Users ({{ tenant.users.length }})</h5>
                    </div>
                    
                    <div class="card-body">
                        <div v-if="tenant.users.length === 0" class="text-center text-muted py-4">
                            <i class="bi bi-people display-4"></i>
                            <p class="mt-2 mb-0">No users found</p>
                        </div>
                        
                        <div v-else class="list-group list-group-flush">
                            <div v-for="user in tenant.users" :key="user.id" class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ user.name }}</h6>
                                        <small class="text-muted">{{ user.email }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <Link :href="route('system.tenants.edit', tenant.id)" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil me-2"></i>Edit Tenant
                            </Link>
                            <button @click="copyTenantInfo" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-clipboard me-2"></i>Copy Tenant Info
                            </button>
                            <button 
                                @click="deleteTenant" 
                                class="btn btn-outline-danger btn-sm"
                                :disabled="tenant.users.length > 0"
                            >
                                <i class="bi bi-trash me-2"></i>Delete Tenant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import PageHeader from '@/components/PageHeader.vue'

const props = defineProps({
    tenant: Object
})

const isExpired = computed(() => {
    if (!props.tenant.expires_at) return false
    return new Date(props.tenant.expires_at) < new Date()
})

function formatDate(dateString) {
    return new Date(dateString).toLocaleString()
}

function deleteTenant() {
    if (props.tenant.users.length > 0) {
        alert('Cannot delete tenant with existing users.')
        return
    }
    
    if (confirm(`Are you sure you want to delete tenant "${props.tenant.name}"?`)) {
        router.delete(route('system.tenants.destroy', props.tenant.id))
    }
}

function copyTenantInfo() {
    const info = `Tenant: ${props.tenant.name}
Slug: ${props.tenant.slug}
Domain: ${props.tenant.domain || 'Not set'}
Subdomain: ${props.tenant.subdomain || 'Not set'}
Status: ${props.tenant.is_active ? 'Active' : 'Inactive'}
Users: ${props.tenant.users.length}`
    
    navigator.clipboard.writeText(info).then(() => {
        alert('Tenant information copied to clipboard!')
    })
}
</script>