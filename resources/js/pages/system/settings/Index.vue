<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>System Settings</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>System Settings Configuration</h2>
                    </div>
                    <div class="body">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active show" data-toggle="tab" href="#General-tab">
                                    <i class="fa fa-cog"></i> General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#Email-tab">
                                    <i class="fa fa-envelope"></i> Email
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#Appearance-tab">
                                    <i class="fa fa-paint-brush"></i> Appearance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#System-tab">
                                    <i class="fa fa-server"></i> System
                                </a>
                            </li>
                        </ul>

                        <form @submit.prevent="updateSettings">
                            <div class="tab-content">
                                <!-- General Settings Tab -->
                                <div class="tab-pane show active" id="General-tab">
                                    <h6>General Settings</h6>
                                    <div class="row" v-if="settingsGrouped.general">
                                        <div v-for="setting in settingsGrouped.general" :key="setting.key" class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label :for="setting.key" class="form-label">{{ setting.name }}</label>
                                                <input 
                                                    v-if="setting.type === 'text'"
                                                    v-model="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    type="text"
                                                    class="form-control"
                                                    :placeholder="setting.description"
                                                />
                                                <textarea 
                                                    v-else-if="setting.type === 'textarea'"
                                                    v-model="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    class="form-control"
                                                    rows="3"
                                                    :placeholder="setting.description"
                                                ></textarea>
                                                <select 
                                                    v-else-if="setting.type === 'select'"
                                                    v-model="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    class="form-control"
                                                >
                                                    <option value="">Select an option</option>
                                                    <option v-for="(label, value) in setting.options" :key="value" :value="value">
                                                        {{ label }}
                                                    </option>
                                                </select>
                                                <small v-if="setting.description" class="text-muted">{{ setting.description }}</small>
                                                <div v-if="form.errors[setting.key]" class="text-danger mt-1">
                                                    {{ form.errors[setting.key] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Settings Tab -->
                                <div class="tab-pane" id="Email-tab">
                                    <h6>Email Configuration</h6>
                                    <div class="row" v-if="settingsGrouped.email">
                                        <div v-for="setting in settingsGrouped.email" :key="setting.key" class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label :for="setting.key" class="form-label">{{ setting.name }}</label>
                                                <input 
                                                    v-if="setting.type === 'text'"
                                                    v-model="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    type="text"
                                                    class="form-control"
                                                    :placeholder="setting.description"
                                                />
                                                <input 
                                                    v-else-if="setting.type === 'number'"
                                                    v-model.number="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    type="number"
                                                    class="form-control"
                                                    :placeholder="setting.description"
                                                />
                                                <input 
                                                    v-else-if="setting.type === 'email'"
                                                    v-model="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    type="email"
                                                    class="form-control"
                                                    :placeholder="setting.description"
                                                />
                                                <select 
                                                    v-else-if="setting.type === 'select'"
                                                    v-model="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    class="form-control"
                                                >
                                                    <option value="">Select an option</option>
                                                    <option v-for="(label, value) in setting.options" :key="value" :value="value">
                                                        {{ label }}
                                                    </option>
                                                </select>
                                                <small v-if="setting.description" class="text-muted">{{ setting.description }}</small>
                                                <div v-if="form.errors[setting.key]" class="text-danger mt-1">
                                                    {{ form.errors[setting.key] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Appearance Settings Tab -->
                                <div class="tab-pane" id="Appearance-tab">
                                    <h6>Appearance Settings</h6>
                                    <div class="row" v-if="settingsGrouped.appearance">
                                        <div v-for="setting in settingsGrouped.appearance" :key="setting.key" class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label :for="setting.key" class="form-label">{{ setting.name }}</label>
                                                <input 
                                                    v-if="setting.type === 'text'"
                                                    v-model="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    type="text"
                                                    class="form-control"
                                                    :placeholder="setting.description"
                                                />
                                                <div v-else-if="setting.type === 'file'" class="input-group">
                                                    <input 
                                                        type="file"
                                                        :id="setting.key"
                                                        class="form-control"
                                                        @change="handleFileUpload($event, setting.key)"
                                                    />
                                                    <span class="input-group-text">
                                                        <i class="fa fa-upload"></i>
                                                    </span>
                                                </div>
                                                <small v-if="setting.description" class="text-muted">{{ setting.description }}</small>
                                                <div v-if="form.errors[setting.key]" class="text-danger mt-1">
                                                    {{ form.errors[setting.key] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- System Settings Tab -->
                                <div class="tab-pane" id="System-tab">
                                    <h6>System Configuration</h6>
                                    <div class="row" v-if="settingsGrouped.system">
                                        <div v-for="setting in settingsGrouped.system" :key="setting.key" class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label :for="setting.key" class="form-label">{{ setting.name }}</label>
                                                <div v-if="setting.type === 'boolean'" class="form-check form-switch">
                                                    <input 
                                                        v-model="form.settings[setting.key]"
                                                        type="checkbox"
                                                        :id="setting.key"
                                                        class="form-check-input"
                                                        :true-value="1"
                                                        :false-value="0"
                                                    />
                                                    <label :for="setting.key" class="form-check-label">
                                                        {{ form.settings[setting.key] == 1 ? 'Enabled' : 'Disabled' }}
                                                    </label>
                                                </div>
                                                <input 
                                                    v-else-if="setting.type === 'number'"
                                                    v-model.number="form.settings[setting.key]"
                                                    :id="setting.key"
                                                    type="number"
                                                    class="form-control"
                                                    :placeholder="setting.description"
                                                />
                                                <small v-if="setting.description" class="text-muted">{{ setting.description }}</small>
                                                <div v-if="form.errors[setting.key]" class="text-danger mt-1">
                                                    {{ form.errors[setting.key] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="form-group text-right">
                                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                            <i class="fa fa-save"></i>
                                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                                        </button>
                                    </div>
                                </div>
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
import { ref, reactive, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    settingsGrouped: {
        type: Object,
        required: true
    }
})

// Breadcrumb configuration
const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Settings' },
]

// Initialize form with all settings
const initializeFormData = () => {
    const formData = {}
    Object.values(props.settingsGrouped).forEach(group => {
        group.forEach(setting => {
            // Convert values based on type
            let value = setting.value
            if (setting.type === 'boolean') {
                value = value === '1' || value === true || value === 'true' ? 1 : 0
            } else if (setting.type === 'number') {
                value = parseFloat(value) || 0
            }
            formData[setting.key] = value
        })
    })
    return formData
}

const form = useForm({
    settings: initializeFormData()
})

const updateSettings = () => {
    form.put(route('system.settings.update'), {
        onSuccess: () => {
            // Success notification will be handled by the backend
            console.log('Settings updated successfully')
        },
        onError: (errors) => {
            console.error('Error updating settings:', errors)
            // Errors will be shown in the form fields
        }
    })
}

const handleFileUpload = (event, settingKey) => {
    const file = event.target.files[0]
    if (file) {
        // This is a placeholder - in real implementation, you'd upload the file
        // and set the file path/URL as the setting value
        form.settings[settingKey] = file.name
    }
}
</script>

<style scoped>
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.tab-content {
    padding-top: 20px;
}

.text-danger {
    font-size: 0.875rem;
}

.form-check-label {
    margin-left: 0.5rem;
}
</style>