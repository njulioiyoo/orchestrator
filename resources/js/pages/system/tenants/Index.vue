<template>
    <div>
        <Head title="Tenant Management" />
        
        <PageHeader title="Tenant Management" subtitle="Manage multi-tenant clients and configurations" />
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tenants</h5>
                <Link :href="route('system.tenants.create')" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Tenant
                </Link>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Domain</th>
                                <th>Subdomain</th>
                                <th>Users</th>
                                <th>Status</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tenant in tenants.data" :key="tenant.id">
                                <td>
                                    <strong>{{ tenant.name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ tenant.slug }}</span>
                                </td>
                                <td>{{ tenant.domain || '-' }}</td>
                                <td>{{ tenant.subdomain || '-' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ tenant.users_count || 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge" :class="tenant.is_active ? 'bg-success' : 'bg-danger'">
                                        {{ tenant.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="tenant.expires_at" :class="isExpired(tenant.expires_at) ? 'text-danger' : 'text-muted'">
                                        {{ formatDate(tenant.expires_at) }}
                                    </span>
                                    <span v-else class="text-muted">Never</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <Link :href="route('system.tenants.show', tenant.id)" 
                                              class="btn btn-sm btn-outline-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </Link>
                                        <Link :href="route('system.tenants.edit', tenant.id)" 
                                              class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button @click="deleteTenant(tenant)" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Delete"
                                                :disabled="tenant.users_count > 0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="tenants.links" class="d-flex justify-content-center mt-4">
                    <nav>
                        <ul class="pagination">
                            <li v-for="link in tenants.links" :key="link.label" 
                                class="page-item" :class="{ active: link.active, disabled: !link.url }">
                                <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label"></Link>
                                <span v-else class="page-link" v-html="link.label"></span>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import PageHeader from '@/components/PageHeader.vue'

defineProps({
    tenants: Object
})

function deleteTenant(tenant) {
    if (tenant.users_count > 0) {
        alert('Cannot delete tenant with existing users.')
        return
    }
    
    if (confirm(`Are you sure you want to delete tenant "${tenant.name}"?`)) {
        router.delete(route('system.tenants.destroy', tenant.id))
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString()
}

function isExpired(dateString) {
    return new Date(dateString) < new Date()
}
</script>