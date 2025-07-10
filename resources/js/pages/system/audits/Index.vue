<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Audit Logs</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <div class="header">
                        <h2><strong>Audit Logs</strong></h2>
                    </div>

                    <ReusableDataTable ref="auditDataTable" :data-url="dataUrl" :columns="tableColumns"
                        :auto-refresh="true" :refresh-interval="30000" :show-delete="false"
                        @data-loaded="onDataLoaded" @error="onError" />
                </div>
            </div>
        </div>
        
        <!-- Modal for Audit Details -->
        <div class="modal fade" id="auditDetailModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-history"></i> Audit Log Details
                        </h5>
                        <button type="button" class="close" @click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedAudit">
                            <!-- Basic Information Card -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fa fa-info-circle"></i> Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label"><strong>Event:</strong></label>
                                                <span class="badge badge-info">{{ selectedAudit.event }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label"><strong>Model:</strong></label>
                                                <p class="form-control-static">{{ selectedAudit.auditable_type }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label"><strong>User:</strong></label>
                                                <p class="form-control-static">{{ selectedAudit.user_name }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label"><strong>Date/Time:</strong></label>
                                                <p class="form-control-static">{{ selectedAudit.created_at }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label"><strong>IP Address:</strong></label>
                                                <p class="form-control-static">{{ selectedAudit.ip_address }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label"><strong>Browser:</strong></label>
                                                <p class="form-control-static">{{ selectedAudit.user_agent }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label"><strong>URL:</strong></label>
                                                <div class="input-group">
                                                    <input 
                                                        type="text" 
                                                        class="form-control" 
                                                        :value="selectedAudit.url" 
                                                        readonly
                                                        @click="selectText"
                                                        ref="urlInput"
                                                        title="Click to select, then Ctrl+C to copy">
                                                    <div class="input-group-append">
                                                        <button 
                                                            ref="copyButton"
                                                            class="btn btn-outline-secondary" 
                                                            type="button" 
                                                            @click="copyToClipboard(selectedAudit.url, $event)"
                                                            title="Copy URL to clipboard">
                                                            <i class="fa fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Tip: Click on URL field to select, then press Ctrl+C to copy</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Changes Information -->
                            <div class="row">
                                <!-- Old Values -->
                                <div class="col-md-6" v-if="selectedAudit.old_values && Object.keys(selectedAudit.old_values).length > 0">
                                    <div class="card">
                                        <div class="card-header bg-warning">
                                            <h6 class="mb-0 text-white"><i class="fa fa-arrow-left"></i> Old Values</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Field</th>
                                                            <th>Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(value, key) in selectedAudit.old_values" :key="'old-' + key">
                                                            <td><strong>{{ key }}</strong></td>
                                                            <td class="text-break">{{ value }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- New Values -->
                                <div class="col-md-6" v-if="selectedAudit.new_values && Object.keys(selectedAudit.new_values).length > 0">
                                    <div class="card">
                                        <div class="card-header bg-success">
                                            <h6 class="mb-0 text-white"><i class="fa fa-arrow-right"></i> New Values</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Field</th>
                                                            <th>Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(value, key) in selectedAudit.new_values" :key="'new-' + key">
                                                            <td><strong>{{ key }}</strong></td>
                                                            <td class="text-break">{{ value }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Full width if only one section -->
                                <div class="col-12" v-if="(!selectedAudit.old_values || Object.keys(selectedAudit.old_values).length === 0) && selectedAudit.new_values && Object.keys(selectedAudit.new_values).length > 0">
                                    <div class="card">
                                        <div class="card-header bg-success">
                                            <h6 class="mb-0 text-white"><i class="fa fa-plus"></i> Created Values</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Field</th>
                                                            <th>Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(value, key) in selectedAudit.new_values" :key="'created-' + key">
                                                            <td><strong>{{ key }}</strong></td>
                                                            <td class="text-break">{{ value }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                            <i class="fa fa-times"></i> Close
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
import ReusableDataTable from '@/components/table/ReusableDataTable.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

defineOptions({
    layout: AppLayout
})

const selectedAudit = ref(null)
const auditDataTable = ref(null)

// Breadcrumb configuration
const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Audit Logs' },
]

// DataTable configuration
const dataUrl = '/system/audits/data'

// Table columns configuration
const tableColumns = [
    {
        key: 'DT_RowIndex',
        title: 'No',
        name: 'DT_RowIndex',
        type: 'number',
        orderable: false,
        searchable: false
    },
    {
        key: 'user_name',
        title: 'User',
        name: 'user_name',
        type: 'text'
    },
    {
        key: 'auditable_type_formatted',
        title: 'Model',
        name: 'auditable_type',
        type: 'text'
    },
    {
        key: 'event_formatted',
        title: 'Event',
        name: 'event',
        type: 'text'
    },
    {
        key: 'ip_address',
        title: 'IP Address',
        name: 'ip_address',
        type: 'text'
    },
    {
        key: 'user_agent_formatted',
        title: 'Browser',
        name: 'user_agent',
        type: 'text'
    },
    {
        key: 'created_at_formatted',
        title: 'Date/Time',
        name: 'created_at',
        type: 'datetime'
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

// Event handlers
const onDataLoaded = (data) => {
    console.log('Audit data loaded:', data.length, 'records')
    
    // Bind view button events after data loaded
    setTimeout(() => {
        $('.js-view').off('click').on('click', function() {
            const auditId = $(this).data('id')
            viewAuditDetails(auditId)
        })
    }, 100)
}

const onError = (error) => {
    console.error('Audit DataTable error:', error)
}

onMounted(() => {
    console.log('Audit Logs page mounted')
    
    // Setup modal event listeners  
    setTimeout(() => {
        if (window.$ && window.$.fn.modal) {
            // Clear selectedAudit when modal is hidden
            $('#auditDetailModal').on('hidden.bs.modal', () => {
                selectedAudit.value = null
            })
        }
    }, 1000)
})

async function viewAuditDetails(auditId) {
    try {
        const response = await axios.get(`/system/audits/${auditId}`)
        selectedAudit.value = response.data.audit
        
        // Use jQuery modal instead of Bootstrap 5
        $('#auditDetailModal').modal('show')
    } catch (error) {
        console.error('Error fetching audit details:', error)
        alert('Failed to load audit details')
    }
}

function closeModal() {
    $('#auditDetailModal').modal('hide')
    selectedAudit.value = null
}

async function copyToClipboard(text, event) {
    // Method 1: Try modern Clipboard API first
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text)
            showCopySuccess(event)
            return
        } catch (err) {
            // Fall through to legacy method
        }
    }
    
    // Method 2: Try selecting the existing input field and copy
    const existingInput = document.querySelector('#auditDetailModal input[readonly]')
    if (existingInput && existingInput.value === text) {
        try {
            existingInput.focus()
            existingInput.select()
            existingInput.setSelectionRange(0, existingInput.value.length)
            
            // Give user a moment to see the selection
            setTimeout(() => {
                const successful = document.execCommand('copy')
                if (successful) {
                    showCopySuccess(event)
                    return
                }
                // If that failed, try method 3
                fallbackCopy(text, event)
            }, 100)
            return
        } catch (err) {
            // Fall through to method 3
        }
    }
    
    // Method 3: Create temporary textarea (legacy fallback)
    fallbackCopy(text, event)
}

function fallbackCopy(text, event) {
    const textarea = document.createElement('textarea')
    textarea.value = text
    textarea.style.position = 'fixed'
    textarea.style.left = '0'
    textarea.style.top = '0'
    textarea.style.width = '2em'
    textarea.style.height = '2em'
    textarea.style.padding = '0'
    textarea.style.border = 'none'
    textarea.style.outline = 'none'
    textarea.style.boxShadow = 'none'
    textarea.style.background = 'transparent'
    
    document.body.appendChild(textarea)
    textarea.focus()
    textarea.select()
    
    try {
        const successful = document.execCommand('copy')
        document.body.removeChild(textarea)
        
        if (successful) {
            showCopySuccess(event)
        } else {
            showCopyError()
        }
    } catch (err) {
        document.body.removeChild(textarea)
        showCopyError()
    }
}

function showCopySuccess(event) {
    // Visual feedback on button (no text to clipboard interference)
    const button = event?.target?.closest('button')
    if (button) {
        const originalHtml = button.innerHTML
        button.innerHTML = '<i class="fa fa-check text-success"></i>'
        button.disabled = true
        button.title = 'Copied!'
        
        setTimeout(() => {
            button.innerHTML = originalHtml
            button.disabled = false
            button.title = 'Copy URL to clipboard'
        }, 2000)
    }
    
    // Show toast notification after a delay to avoid clipboard interference
    setTimeout(() => {
        if (window.toastr) {
            toastr.success('URL successfully copied!')
        }
    }, 100)
}

function showCopyError() {
    if (window.toastr) {
        toastr.error('Failed to copy URL')
    } else {
        alert('Failed to copy URL')
    }
}

function selectText(event) {
    event.target.select()
    event.target.setSelectionRange(0, event.target.value.length)
}
</script>

<style scoped>
.form-control-static {
    padding-top: 7px;
    padding-bottom: 7px;
    margin-bottom: 0;
    min-height: 34px;
}

.text-break {
    word-wrap: break-word;
    word-break: break-all;
}

.modal-xl {
    max-width: 90%;
}

.card-header {
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.card-header h6 {
    margin: 0;
    font-weight: 600;
}

.badge {
    font-size: 0.875em;
    padding: 0.375rem 0.75rem;
}

.table-sm th,
.table-sm td {
    padding: 0.5rem;
    vertical-align: top;
}

.input-group .form-control {
    font-family: monospace;
    font-size: 0.875rem;
}

.btn:focus {
    box-shadow: none;
}
</style>