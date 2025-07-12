<template>
    <div class="modal fade" id="globalSearchModal" tabindex="-1" role="dialog" aria-labelledby="globalSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="globalSearchModalLabel">
                        <i class="fa fa-search"></i> Search Results
                        <span v-if="searchQuery" class="text-muted">for "{{ searchQuery }}"</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Loading State -->
                    <div v-if="loading" class="text-center py-4">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Searching...</p>
                    </div>

                    <!-- No Results -->
                    <div v-else-if="!loading && results.length === 0 && searchQuery" class="text-center py-4">
                        <i class="fa fa-search fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">No results found for "{{ searchQuery }}"</p>
                        <small class="text-muted">Try different keywords or check your spelling</small>
                    </div>

                    <!-- Search Results -->
                    <div v-else-if="results.length > 0">
                        <div v-for="category in groupedResults" :key="category.type" class="mb-4">
                            <h6 class="text-primary border-bottom pb-2">
                                <i :class="category.icon"></i>
                                {{ category.label }} ({{ category.items.length }})
                            </h6>
                            
                            <div class="list-group list-group-flush">
                                <a 
                                    v-for="item in category.items" 
                                    :key="item.id"
                                    :href="item.url"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    @click="closeModal"
                                >
                                    <div>
                                        <div class="fw-bold">{{ item.title }}</div>
                                        <small class="text-muted" v-if="item.description">{{ item.description }}</small>
                                    </div>
                                    <div>
                                        <span v-if="item.badge" class="badge" :class="item.badge.class">
                                            {{ item.badge.text }}
                                        </span>
                                        <i class="fa fa-chevron-right text-muted ml-2"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Initial State -->
                    <div v-else class="text-center py-4">
                        <i class="fa fa-search fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">Start typing to search across all modules</p>
                        <small class="text-muted">Search for users, roles, permissions, menus, and more...</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <small class="text-muted">
                            <kbd>ESC</kbd> to close
                        </small>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useToast } from '@/composables/useToast.js'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    query: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['close'])

const toast = useToast()
const loading = ref(false)
const results = ref([])
const searchQuery = ref('')

// Group results by type
const groupedResults = computed(() => {
    const groups = {}
    
    results.value.forEach(item => {
        if (!groups[item.type]) {
            groups[item.type] = {
                type: item.type,
                label: getTypeLabel(item.type),
                icon: getTypeIcon(item.type),
                items: []
            }
        }
        groups[item.type].items.push(item)
    })
    
    return Object.values(groups)
})

// Get human readable label for type
const getTypeLabel = (type) => {
    const labels = {
        'users': 'Users',
        'roles': 'Roles', 
        'permissions': 'Permissions',
        'menus': 'Menus',
        'settings': 'Settings'
    }
    return labels[type] || type
}

// Get icon for type
const getTypeIcon = (type) => {
    const icons = {
        'users': 'fa fa-users',
        'roles': 'fa fa-user-circle',
        'permissions': 'fa fa-shield',
        'menus': 'fa fa-bars',
        'settings': 'fa fa-cogs'
    }
    return icons[type] || 'fa fa-file'
}

// Watch for query changes and perform search
watch(() => props.query, (newQuery) => {
    searchQuery.value = newQuery
    if (newQuery && newQuery.length >= 2) {
        performSearch(newQuery)
    } else {
        results.value = []
    }
}, { immediate: true })

// Perform search via API
const performSearch = async (query) => {
    if (!query || query.length < 2) return
    
    loading.value = true
    
    try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        
        if (!response.ok) {
            throw new Error('Search request failed')
        }
        
        const data = await response.json()
        results.value = data.results || []
        
    } catch (error) {
        console.error('Search error:', error)
        toast.error('Gagal melakukan pencarian. Silakan coba lagi.')
        results.value = []
    } finally {
        loading.value = false
    }
}

// Close modal
const closeModal = () => {
    emit('close')
    // Reset state
    searchQuery.value = ''
    results.value = []
    loading.value = false
}

// Handle ESC key
const handleKeydown = (event) => {
    if (event.key === 'Escape' && props.show) {
        closeModal()
    }
}

// Add global event listener for ESC key
if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleKeydown)
}
</script>

<style scoped>
.list-group-item:hover {
    background-color: #f8f9fa;
}

.modal-lg {
    max-width: 600px;
}

.fw-bold {
    font-weight: 600;
}

kbd {
    background-color: #e9ecef;
    border: 1px solid #adb5bd;
    border-radius: 3px;
    color: #495057;
    font-size: 0.75rem;
    padding: 2px 4px;
}
</style>