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
        console.warn('jQuery atau DataTable plugin belum tersedia.')
        return false
    }

    try {
        // Hancurkan instance lama jika ada
        if (dataTableInstance.value) {
            try {
                dataTableInstance.value.destroy()
            } catch (e) {
                console.error('Error destroying existing DataTable:', e)
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
                    console.error('DataTables AJAX error:', error, thrown)
                    emit('error', { xhr, error, thrown })
                }
            },
            columns: buildDataTableColumns(),
            drawCallback: function () {
                bindDeleteHandler()
                emit('dataLoaded', this.api().data().toArray())
            },
            initComplete: function () {
                console.log('DataTable initialization complete')
            }
        }

        // Merge dengan opsi kustom
        const config = { ...defaultConfig, ...props.options }

        dataTableInstance.value = $table.DataTable(config)

        console.log('DataTable berhasil diinisialisasi')
        return true
    } catch (error) {
        console.error('Error saat inisialisasi DataTable:', error)
        emit('error', error)
        return false
    }
}

// Fungsi untuk reload DataTable
const reloadDataTable = () => {
    try {
        if (dataTableInstance.value) {
            console.log('Reloading DataTable data')
            dataTableInstance.value.ajax.reload(null, false)
        } else {
            console.warn('DataTable instance tidak tersedia, mencoba inisialisasi')
            setupDataTable()
        }
    } catch (error) {
        console.error('Error reloading DataTable:', error)
        setTimeout(() => setupDataTable(), 1000)
    }
}

// Fungsi untuk mengikat event delete
const bindDeleteHandler = () => {
    if (!props.deleteUrl) return

    try {
        if (!window.jQuery) {
            console.warn('jQuery tidak tersedia saat binding delete handler')
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
                            console.error('Delete error:', error)
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

        console.log('Delete handlers bound successfully')
    } catch (error) {
        console.error('Error binding delete handlers:', error)
    }
}

// Fungsi untuk setup DataTable
const setupDataTable = async () => {
    if (isSetupInProgress) {
        console.log('Setup DataTable sedang berlangsung')
        return
    }

    if (isSetupComplete && dataTableInstance.value) {
        console.log('DataTable sudah ada, melakukan reload data')
        reloadDataTable()
        return
    }

    isSetupInProgress = true

    // Tunggu NextTick untuk memastikan DOM sudah ready
    await nextTick()

    const checkDependencies = () => {
        if (typeof window.jQuery === 'undefined') {
            console.log('jQuery belum tersedia, memuat jQuery...')
            loadJQuery()
            return false
        }

        if (typeof window.jQuery.fn.DataTable === 'undefined') {
            console.log('DataTables belum tersedia, memuat DataTables...')
            loadDataTablesScript()
            return false
        }

        return true
    }

    const loadJQuery = () => {
        const jQueryScript = document.createElement('script')
        jQueryScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js'
        jQueryScript.onload = () => {
            console.log('jQuery berhasil dimuat')
            loadDataTablesScript()
        }
        jQueryScript.onerror = () => {
            console.error('Gagal memuat jQuery!')
            handleSetupError()
        }
        document.head.appendChild(jQueryScript)
    }

    const loadDataTablesScript = () => {
        const dataTableScript = document.createElement('script')
        dataTableScript.src = 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js'
        dataTableScript.onload = () => {
            console.log('DataTables berhasil dimuat!')

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
            console.error('Gagal memuat DataTables!')
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
            console.error(`Berhenti mencoba setelah ${maxSetupAttempts} percobaan`)
            emit('error', new Error('Failed to load DataTable dependencies'))
        }
    }

    if (checkDependencies()) {
        const initResult = initDataTables()

        if (initResult) {
            console.log('DataTable berhasil diinisialisasi')
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
            console.log('Auto-refresh datatable')
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
    console.log('ReusableDataTable mounted')
    isSetupInProgress = false
    isSetupComplete = false
    setupAttempts = 0

    setTimeout(() => {
        setupDataTable()
    }, 100)
})

onBeforeUnmount(() => {
    console.log('ReusableDataTable unmounting')

    // Clear interval
    if (pollingInterval) {
        clearInterval(pollingInterval)
    }

    // Destroy DataTable
    if (dataTableInstance.value) {
        try {
            dataTableInstance.value.destroy()
        } catch (e) {
            console.error('Error destroying DataTable:', e)
        }
        dataTableInstance.value = null
    }

    // Clean up event handlers
    try {
        if (window.jQuery) {
            window.jQuery(document).off('click', `#${props.tableId} .js-delete`)
        }
    } catch (e) {
        console.error('Error cleaning up jQuery handlers:', e)
    }
})
</script>