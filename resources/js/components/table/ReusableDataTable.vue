<template>
    <div class="body">
        <div class="table-responsive">
            <table :ref="tableRef" :id="tableId" class="table center-aligned-table">
                <thead>
                    <tr>
                        <th v-for="column in columns" :key="column.key">
                            {{ column.title }}
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import dayjs from 'dayjs'
import axios from 'axios'
import Swal from 'sweetalert2'

// Props
const props = defineProps({
    // URL untuk mengambil data
    dataUrl: {
        type: String,
        required: true
    },
    // Konfigurasi kolom
    columns: {
        type: Array,
        required: true,
        // Format: [{ key: 'id', title: 'No', name: 'id', orderable: true, searchable: true }]
    },
    // ID unik untuk table
    tableId: {
        type: String,
        default: () => `datatable-${Math.random().toString(36).substr(2, 9)}`
    },
    // URL untuk delete (opsional)
    deleteUrl: {
        type: String,
        default: null
    },
    // Opsi tambahan untuk DataTable
    options: {
        type: Object,
        default: () => ({})
    },
    // Flag untuk auto-refresh
    autoRefresh: {
        type: Boolean,
        default: true
    },
    // Interval auto-refresh dalam milidetik
    refreshInterval: {
        type: Number,
        default: 30000
    }
})

// Emits
const emit = defineEmits(['dataLoaded', 'deleteSuccess', 'error'])

// Refs
const tableRef = ref(`table-${props.tableId}`)
const dataTableInstance = ref(null)

// State management
let isSetupInProgress = false
let isSetupComplete = false
let setupAttempts = 0
const maxSetupAttempts = 10
let pollingInterval = null

// Fungsi untuk format data berdasarkan tipe kolom
const formatCellData = (data, column) => {
    if (!data) return ''

    switch (column.type) {
        case 'date':
            return dayjs(data).format(column.format || 'DD MMM YYYY HH:mm:ss')
        case 'datetime':
            return dayjs(data).format(column.format || 'DD MMM YYYY HH:mm:ss')
        case 'currency':
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: column.currency || 'IDR'
            }).format(data)
        case 'number':
            return new Intl.NumberFormat('id-ID').format(data)
        case 'custom':
            return column.render ? column.render(data) : data
        default:
            return data
    }
}

// Fungsi untuk membuat konfigurasi kolom DataTable
const buildDataTableColumns = () => {
    return props.columns.map(column => {
        const dtColumn = {
            data: column.key,
            name: column.name || column.key,
            orderable: column.orderable !== false,
            searchable: column.searchable !== false,
        }

        // Jika ada custom render atau tipe khusus
        if (column.type && column.type !== 'text') {
            dtColumn.render = (data) => formatCellData(data, column)
        } else if (column.render) {
            dtColumn.render = column.render
        }

        return dtColumn
    })
}

// Fungsi untuk inisialisasi DataTable
const initDataTables = () => {
    if (!window.jQuery || !window.jQuery.fn.DataTable) {
        return false
    }

    try {
        // Hancurkan instance lama jika ada
        if (dataTableInstance.value) {
            try {
                dataTableInstance.value.destroy()
            } catch (e) {
                // Error destroying existing DataTable
            }
            dataTableInstance.value = null
        }

        const $table = window.jQuery(`#${props.tableId}`)

        // Konfigurasi default
        const defaultConfig = {
            processing: true,
            serverSide: true,
            ajax: {
                url: props.dataUrl,
                data: function (d) {
                    d.timestamp = new Date().getTime()
                    return d
                },
                error: function (xhr, error, thrown) {
                    emit('error', { xhr, error, thrown })
                }
            },
            columns: buildDataTableColumns(),
            drawCallback: function () {
                bindDeleteHandler()
                emit('dataLoaded', this.api().data().toArray())
            },
            initComplete: function () {
                // DataTable initialization complete
            }
        }

        // Merge dengan opsi kustom
        const config = { ...defaultConfig, ...props.options }

        dataTableInstance.value = $table.DataTable(config)

        return true
    } catch (error) {
        emit('error', error)
        return false
    }
}

// Fungsi untuk reload DataTable
const reloadDataTable = () => {
    try {
        if (dataTableInstance.value) {
            dataTableInstance.value.ajax.reload(null, false)
        } else {
            setupDataTable()
        }
    } catch (error) {
        setTimeout(() => setupDataTable(), 1000)
    }
}

// Fungsi untuk mengikat event delete
const bindDeleteHandler = () => {
    if (!props.deleteUrl) return

    try {
        if (!window.jQuery) {
            return
        }

        window.jQuery(document).off('click', `#${props.tableId} .js-delete`)

        window.jQuery(document).on('click', `#${props.tableId} .js-delete`, function () {
            const id = window.jQuery(this).data('id')
            const name = window.jQuery(this).data('name') || 'item'

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete "${name}". This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteEndpoint = props.deleteUrl.replace(':id', id)

                    axios.delete(deleteEndpoint)
                        .then(response => {
                            Swal.fire(
                                'Deleted!',
                                response.data.message || 'Item has been deleted.',
                                'success'
                            )
                            reloadDataTable()
                            emit('deleteSuccess', { id, response: response.data })
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                error.response?.data?.message || 'Failed to delete the item.',
                                'error'
                            )
                            emit('error', error)
                        })
                }
            })
        })

    } catch (error) {
        // Error binding delete handlers
    }
}

// Fungsi untuk setup DataTable
const setupDataTable = async () => {
    if (isSetupInProgress) {
        return
    }

    if (isSetupComplete && dataTableInstance.value) {
        reloadDataTable()
        return
    }

    isSetupInProgress = true

    // Tunggu NextTick untuk memastikan DOM sudah ready
    await nextTick()

    const checkDependencies = () => {
        if (typeof window.jQuery === 'undefined') {
            loadJQuery()
            return false
        }

        if (typeof window.jQuery.fn.DataTable === 'undefined') {
            loadDataTablesScript()
            return false
        }

        return true
    }

    const loadJQuery = () => {
        const jQueryScript = document.createElement('script')
        jQueryScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js'
        jQueryScript.onload = () => {
            loadDataTablesScript()
        }
        jQueryScript.onerror = () => {
            handleSetupError()
        }
        document.head.appendChild(jQueryScript)
    }

    const loadDataTablesScript = () => {
        const dataTableScript = document.createElement('script')
        dataTableScript.src = 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js'
        dataTableScript.onload = () => {
            // Load CSS
            if (!document.querySelector('link[href*="datatables"]')) {
                const dataTablesCss = document.createElement('link')
                dataTablesCss.rel = 'stylesheet'
                dataTablesCss.href = 'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css'
                document.head.appendChild(dataTablesCss)
            }

            setTimeout(() => {
                isSetupInProgress = false
                setupDataTable()
            }, 500)
        }
        dataTableScript.onerror = () => {
            handleSetupError()
        }
        document.head.appendChild(dataTableScript)
    }

    const handleSetupError = () => {
        isSetupInProgress = false
        if (setupAttempts < maxSetupAttempts) {
            setupAttempts++
            setTimeout(setupDataTable, 1000)
        } else {
            emit('error', new Error('Failed to load DataTable dependencies'))
        }
    }

    if (checkDependencies()) {
        const initResult = initDataTables()

        if (initResult) {
            isSetupComplete = true
            isSetupInProgress = false

            // Setup auto-refresh jika diaktifkan
            if (props.autoRefresh && !pollingInterval) {
                setupAutoRefresh()
            }
        } else {
            handleSetupError()
        }
    }
}

// Setup auto-refresh
const setupAutoRefresh = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval)
    }

    pollingInterval = setInterval(() => {
        if (dataTableInstance.value && document.visibilityState === 'visible') {
            reloadDataTable()
        }
    }, props.refreshInterval)
}

// Watch untuk perubahan props
watch(() => props.dataUrl, () => {
    if (dataTableInstance.value) {
        setupDataTable()
    }
})

watch(() => props.columns, () => {
    if (dataTableInstance.value) {
        setupDataTable()
    }
}, { deep: true })

// Expose methods untuk parent component
defineExpose({
    reloadDataTable,
    getDataTableInstance: () => dataTableInstance.value,
    setupDataTable
})

// Lifecycle hooks
onMounted(() => {
    isSetupInProgress = false
    isSetupComplete = false
    setupAttempts = 0

    setTimeout(() => {
        setupDataTable()
    }, 100)
})

onBeforeUnmount(() => {
    // Clear interval
    if (pollingInterval) {
        clearInterval(pollingInterval)
    }

    // Destroy DataTable
    if (dataTableInstance.value) {
        try {
            dataTableInstance.value.destroy()
        } catch (e) {
            // Error destroying DataTable
        }
        dataTableInstance.value = null
    }

    // Clean up event handlers
    try {
        if (window.jQuery) {
            window.jQuery(document).off('click', `#${props.tableId} .js-delete`)
        }
    } catch (e) {
        // Error cleaning up jQuery handlers
    }
})
</script>