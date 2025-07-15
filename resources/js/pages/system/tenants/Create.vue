<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Create Tenant</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <div class="header">
                        <h2>Tenant Information</h2>
                    </div>
                    <div class="body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input 
                                id="name"
                                v-model="form.name" 
                                type="text" 
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.name }"
                                placeholder="Enter tenant name"
                                required
                            />
                            <div v-if="form.errors.name" class="invalid-feedback">
                                {{ form.errors.name }}
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input 
                                id="slug"
                                v-model="form.slug" 
                                type="text" 
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.slug }"
                                placeholder="Auto-generated from name"
                            />
                            <div v-if="form.errors.slug" class="invalid-feedback">
                                {{ form.errors.slug }}
                            </div>
                            <small class="form-text text-muted">Leave empty to auto-generate from name</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="domain" class="form-label">Domain</label>
                            <input 
                                id="domain"
                                v-model="form.domain" 
                                type="text" 
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.domain }"
                                placeholder="example.com"
                            />
                            <div v-if="form.errors.domain" class="invalid-feedback">
                                {{ form.errors.domain }}
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="subdomain" class="form-label">Subdomain</label>
                            <input 
                                id="subdomain"
                                v-model="form.subdomain" 
                                type="text" 
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.subdomain }"
                                placeholder="tenant"
                            />
                            <div v-if="form.errors.subdomain" class="invalid-feedback">
                                {{ form.errors.subdomain }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expires_at" class="form-label">Expires At</label>
                            <input 
                                id="expires_at"
                                v-model="form.expires_at" 
                                type="datetime-local" 
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.expires_at }"
                            />
                            <div v-if="form.errors.expires_at" class="invalid-feedback">
                                {{ form.errors.expires_at }}
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input 
                                    id="is_active"
                                    v-model="form.is_active" 
                                    type="checkbox" 
                                    class="form-check-input"
                                    :class="{ 'is-invalid': form.errors.is_active }"
                                />
                                <label for="is_active" class="form-check-label">
                                    Active
                                </label>
                                <div v-if="form.errors.is_active" class="invalid-feedback">
                                    {{ form.errors.is_active }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Configuration</label>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input 
                                        id="config_allow_registration"
                                        v-model="form.config.allow_registration" 
                                        type="checkbox" 
                                        class="form-check-input"
                                    />
                                    <label for="config_allow_registration" class="form-check-label">
                                        Allow Registration
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input 
                                        id="config_email_verification"
                                        v-model="form.config.email_verification" 
                                        type="checkbox" 
                                        class="form-check-input"
                                    />
                                    <label for="config_email_verification" class="form-check-label">
                                        Email Verification
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input 
                                        id="config_two_factor"
                                        v-model="form.config.two_factor_auth" 
                                        type="checkbox" 
                                        class="form-check-input"
                                    />
                                    <label for="config_two_factor" class="form-check-label">
                                        Two-Factor Auth
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <Link href="/system/tenants" class="btn btn-secondary">
                            Cancel
                        </Link>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                            Create Tenant
                        </button>
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
import { Link, useForm } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout
})

const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Tenants', link: '/system/tenants' },
    { label: 'Create' },
]

const form = useForm({
    name: '',
    slug: '',
    domain: '',
    subdomain: '',
    expires_at: '',
    is_active: true,
    config: {
        allow_registration: false,
        email_verification: false,
        two_factor_auth: false
    }
})

function submit() {
    form.post('/system/tenants')
}
</script>