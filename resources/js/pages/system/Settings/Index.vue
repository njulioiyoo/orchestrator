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
            <div class="col-md-12">
                <form @submit.prevent="updateSettings">
                    <div class="row">
                        <div v-for="(settings, groupName) in settingsGrouped" :key="groupName" class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">{{ formatGroupName(groupName) }}</h4>
                                </div>
                                <div class="card-body">
                                    <div v-for="setting in settings" :key="setting.key" class="form-group mb-3">
                                        <label :for="setting.key" class="form-label">
                                            {{ setting.name }}
                                            <i v-if="setting.description" 
                                               class="fa fa-question-circle ms-1" 
                                               :title="setting.description"
                                               style="cursor: help;">
                                            </i>
                                        </label>

                                        <!-- Text Input -->
                                        <input 
                                            v-if="setting.type === 'text' || setting.type === 'email' || setting.type === 'url'"
                                            v-model="form.settings[setting.key]"
                                            :type="setting.type === 'email' ? 'email' : 'text'"
                                            :id="setting.key"
                                            class="form-control"
                                            :placeholder="setting.description"
                                        />

                                        <!-- Number Input -->
                                        <input 
                                            v-else-if="setting.type === 'number'"
                                            v-model.number="form.settings[setting.key]"
                                            type="number"
                                            :id="setting.key"
                                            class="form-control"
                                            :placeholder="setting.description"
                                        />

                                        <!-- Textarea -->
                                        <textarea 
                                            v-else-if="setting.type === 'textarea'"
                                            v-model="form.settings[setting.key]"
                                            :id="setting.key"
                                            class="form-control"
                                            rows="3"
                                            :placeholder="setting.description"
                                        ></textarea>

                                        <!-- Boolean (Switch) -->
                                        <div v-else-if="setting.type === 'boolean'" class="form-check form-switch">
                                            <input 
                                                v-model="form.settings[setting.key]"
                                                type="checkbox"
                                                :id="setting.key"
                                                class="form-check-input"
                                                :true-value="1"
                                                :false-value="0"
                                            />
                                            <label :for="setting.key" class="form-check-label">
                                                {{ form.settings[setting.key] ? 'Enabled' : 'Disabled' }}
                                            </label>
                                        </div>

                                        <!-- Select -->
                                        <select 
                                            v-else-if="setting.type === 'select'"
                                            v-model="form.settings[setting.key]"
                                            :id="setting.key"
                                            class="form-select"
                                        >
                                            <option value="">Select an option</option>
                                            <option v-for="(label, value) in setting.options" :key="value" :value="value">
                                                {{ label }}
                                            </option>
                                        </select>

                                        <!-- File Upload -->
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

                                        <!-- Error display for this field -->
                                        <div v-if="form.errors[setting.key]" class="text-danger mt-1">
                                            {{ form.errors[setting.key] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-end">
                                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                        <i class="fa fa-save me-1"></i>
                                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
            console.log('Settings updated successfully')
        },
        onError: (errors) => {
            console.error('Error updating settings:', errors)
        }
    })
}

const formatGroupName = (groupName) => {
    return groupName.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
}

const handleFileUpload = (event, settingKey) => {
    const file = event.target.files[0]
    if (file) {
        form.settings[settingKey] = file.name
    }
}
</script>

<style scoped>
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.text-danger {
    font-size: 0.875rem;
}
</style>