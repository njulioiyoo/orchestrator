<template>
    <div class="container-fluid">
        <Head :title="`Tenant: ${tenant.name}`" />
        
        <div class="block-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>Tenant: {{ tenant.name }}</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        
        <div class="row clearfix">
            <!-- Tenant Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="header d-flex justify-content-between align-items-center">
                        <h2>Tenant Information</h2>
                        <Link :href="`/system/tenants/${tenant.encrypted_id}/edit`" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </Link>
                    </div>
                    
                    <div class="body">
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
                
                <!-- API Credentials -->
                <div class="card mt-4">
                    <div class="header d-flex justify-content-between align-items-center">
                        <h2>API Credentials</h2>
                        <button @click="showCreateCredentialModal = true" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Generate New
                        </button>
                    </div>
                    
                    <div class="body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-gear me-2" :class="tenant.tenant_type === 'api_only' ? 'text-warning' : 'text-success'"></i>
                                    <span>Tenant Type</span>
                                    <span class="badge ms-auto" :class="tenant.tenant_type === 'api_only' ? 'bg-warning' : 'bg-success'">
                                        {{ tenant.tenant_type === 'api_only' ? 'API Only' : 'Regular' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-key me-2" :class="tenant.allow_web_login ? 'text-success' : 'text-muted'"></i>
                                    <span>Web Login</span>
                                    <span class="badge ms-auto" :class="tenant.allow_web_login ? 'bg-success' : 'bg-secondary'">
                                        {{ tenant.allow_web_login ? 'Allowed' : 'Blocked' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="apiCredentials.length === 0" class="text-center text-muted py-4">
                            <i class="bi bi-key display-4"></i>
                            <p class="mt-2 mb-0">No API credentials found</p>
                            <button @click="showCreateCredentialModal = true" class="btn btn-success btn-sm mt-2">
                                <i class="bi bi-plus-circle me-1"></i>Generate First Credential
                            </button>
                        </div>
                        
                        <div v-else class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>API Key</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="credential in apiCredentials" :key="credential.id">
                                        <td>
                                            <code class="text-primary">{{ credential.api_key }}</code>
                                        </td>
                                        <td>
                                            <span class="badge" :class="credential.is_active ? 'bg-success' : 'bg-secondary'">
                                                {{ credential.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(credential.created_at) }}</td>
                                        <td>
                                            <button @click="viewCredential(credential)" class="btn btn-info btn-sm me-1">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button @click="regenerateSecret(credential)" class="btn btn-warning btn-sm me-1">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                            <button @click="deleteCredential(credential)" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Configuration -->
                <div class="card mt-4">
                    <div class="header">
                        <h2>Configuration</h2>
                    </div>
                    
                    <div class="body">
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
                    <div class="header">
                        <h2>Users ({{ tenant.users.length }})</h2>
                    </div>
                    
                    <div class="body">
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
                    <div class="header">
                        <h2>Quick Actions</h2>
                    </div>
                    
                    <div class="body">
                        <div class="d-grid gap-2">
                            <Link :href="`/system/tenants/${tenant.encrypted_id}/edit`" class="btn btn-primary btn-sm">
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
        
        <!-- Create API Credential Modal -->
        <div v-if="showCreateCredentialModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Generate API Credentials</h5>
                        <button type="button" class="btn-close" @click="showCreateCredentialModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="createCredential">
                            <div class="mb-3">
                                <label class="form-label">Rate Limits (per minute)</label>
                                <input v-model="credentialForm.requests_per_minute" type="number" class="form-control" min="1" max="1000" value="100">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Rate Limits (per hour)</label>
                                <input v-model="credentialForm.requests_per_hour" type="number" class="form-control" min="1" max="10000" value="1000">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Allowed Domains</label>
                                <textarea v-model="credentialForm.allowed_domains_text" class="form-control" rows="3" placeholder="One domain per line (leave empty for all domains)"></textarea>
                                <div class="form-text">Leave empty to allow all domains, or enter one domain per line</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Expires At</label>
                                <input v-model="credentialForm.expires_at" type="datetime-local" class="form-control">
                                <div class="form-text">Leave empty for no expiration</div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input v-model="credentialForm.make_api_only" class="form-check-input" type="checkbox" id="makeApiOnly">
                                    <label class="form-check-label" for="makeApiOnly">
                                        <strong>Make this tenant API-only</strong>
                                    </label>
                                    <div class="form-text text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        This will disable web login for this tenant. Users will only be able to access via API.
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showCreateCredentialModal = false">Cancel</button>
                        <button type="button" class="btn btn-success" @click="createCredential" :disabled="creating">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ creating ? 'Generating...' : 'Generate Credentials' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View Credential Modal -->
        <div v-if="showViewCredentialModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">API Credential Details</h5>
                        <button type="button" class="btn-close" @click="showViewCredentialModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedCredential">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">API Key</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" :value="selectedCredential.api_key" readonly>
                                        <button @click="copyToClipboard(selectedCredential.api_key)" class="btn btn-outline-secondary">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge" :class="selectedCredential.is_active ? 'bg-success' : 'bg-secondary'">
                                            {{ selectedCredential.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Rate Limits</label>
                                    <p class="form-control-plaintext">
                                        {{ selectedCredential.rate_limits?.requests_per_minute || 'No limit' }} requests/minute<br>
                                        {{ selectedCredential.rate_limits?.requests_per_hour || 'No limit' }} requests/hour
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Allowed Domains</label>
                                    <p class="form-control-plaintext">
                                        <span v-if="selectedCredential.allowed_domains && selectedCredential.allowed_domains.length > 0">
                                            <span v-for="domain in selectedCredential.allowed_domains" :key="domain" class="badge bg-info me-1">
                                                {{ domain }}
                                            </span>
                                        </span>
                                        <span v-else class="text-muted">All domains</span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Created</label>
                                    <p class="form-control-plaintext">{{ formatDate(selectedCredential.created_at) }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Expires</label>
                                    <p class="form-control-plaintext">
                                        {{ selectedCredential.expires_at ? formatDate(selectedCredential.expires_at) : 'Never' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showViewCredentialModal = false">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import { Head, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { computed, ref, reactive, onMounted } from 'vue'
import axios from 'axios'

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    tenant: Object
})

// Reactive data
const apiCredentials = ref([])
const showCreateCredentialModal = ref(false)
const showViewCredentialModal = ref(false)
const selectedCredential = ref(null)
const creating = ref(false)

const credentialForm = reactive({
    requests_per_minute: 100,
    requests_per_hour: 1000,
    allowed_domains_text: '',
    expires_at: '',
    make_api_only: false
})

const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Tenants', link: '/system/tenants' },
    { label: 'View' },
]

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
        router.delete(`/system/tenants/${props.tenant.encrypted_id}`)
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

// API Credential functions
async function fetchApiCredentials() {
    try {
        const response = await axios.get(`/system/tenants/${props.tenant.encrypted_id}/api-credentials`)
        apiCredentials.value = response.data.data.credentials
    } catch (error) {
        console.error('Error fetching API credentials:', error)
    }
}

async function createCredential() {
    creating.value = true
    try {
        const allowedDomains = credentialForm.allowed_domains_text
            .split('\n')
            .map(domain => domain.trim())
            .filter(domain => domain)
        
        const data = {
            rate_limits: {
                requests_per_minute: credentialForm.requests_per_minute,
                requests_per_hour: credentialForm.requests_per_hour
            },
            allowed_domains: allowedDomains.length > 0 ? allowedDomains : ['*'],
            expires_at: credentialForm.expires_at || null,
            make_api_only: credentialForm.make_api_only
        }
        
        const response = await axios.post(`/system/tenants/${props.tenant.encrypted_id}/api-credentials`, data)
        
        // Show success message with credentials
        alert(`API Credentials Generated Successfully!

API Key: ${response.data.data.api_key}
API Secret: ${response.data.data.api_secret}
Tenant ID: ${response.data.data.tenant_id}

Please save these credentials securely. The API Secret will not be shown again.`)
        
        // Refresh the list
        await fetchApiCredentials()
        
        // Close modal and reset form
        showCreateCredentialModal.value = false
        resetCredentialForm()
        
        // Refresh page to update tenant type if changed
        if (credentialForm.make_api_only) {
            router.reload()
        }
        
    } catch (error) {
        console.error('Error creating credential:', error)
        alert('Error creating API credentials. Please try again.')
    } finally {
        creating.value = false
    }
}

function resetCredentialForm() {
    credentialForm.requests_per_minute = 100
    credentialForm.requests_per_hour = 1000
    credentialForm.allowed_domains_text = ''
    credentialForm.expires_at = ''
    credentialForm.make_api_only = false
}

function viewCredential(credential) {
    selectedCredential.value = credential
    showViewCredentialModal.value = true
}

async function regenerateSecret(credential) {
    if (!confirm('Are you sure you want to regenerate the API secret? This will invalidate the current secret.')) {
        return
    }
    
    try {
        const response = await axios.post(`/system/tenants/${props.tenant.encrypted_id}/api-credentials/${credential.id}/regenerate-secret`)
        
        alert(`API Secret Regenerated Successfully!

API Key: ${response.data.data.api_key}
New API Secret: ${response.data.data.api_secret}
Tenant ID: ${response.data.data.tenant_id}

Please update your application with the new secret.`)
        
        await fetchApiCredentials()
    } catch (error) {
        console.error('Error regenerating secret:', error)
        alert('Error regenerating API secret. Please try again.')
    }
}

async function deleteCredential(credential) {
    if (!confirm('Are you sure you want to delete this API credential? This action cannot be undone.')) {
        return
    }
    
    try {
        await axios.delete(`/system/tenants/${props.tenant.encrypted_id}/api-credentials/${credential.id}`)
        alert('API credential deleted successfully.')
        await fetchApiCredentials()
    } catch (error) {
        console.error('Error deleting credential:', error)
        alert('Error deleting API credential. Please try again.')
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!')
    })
}

// Load API credentials on component mount
onMounted(() => {
    fetchApiCredentials()
})
</script>