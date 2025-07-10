<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Menu Management</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="card">
                    <PageHeaderWithCreateButton 
                        title="Menu Management" 
                        buttonLink="/system/menus/create" />
                    <div class="card-body">
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Label</th>
                                        <th>Icon</th>
                                        <th>URL</th>
                                        <th>Parent</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="menu in sortedMenus" :key="menu.id">
                                        <tr>
                                            <td>{{ menu.id }}</td>
                                            <td>{{ menu.name }}</td>
                                            <td>{{ menu.label }}</td>
                                            <td>
                                                <i :class="menu.icon" v-if="menu.icon"></i>
                                                {{ menu.icon }}
                                            </td>
                                            <td>{{ menu.url || menu.route }}</td>
                                            <td>{{ menu.parent?.label || '-' }}</td>
                                            <td>{{ menu.sort_order }}</td>
                                            <td>
                                                <span class="badge" :class="menu.is_active ? 'badge-success' : 'badge-secondary'">
                                                    {{ menu.is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a :href="`/system/menus/${menu.id}/edit`" class="btn btn-sm btn-warning">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button @click="deleteMenu(menu)" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Child menus -->
                                        <template v-for="child in menu.children" :key="`child-${child.id}`">
                                            <tr class="child-row">
                                                <td>{{ child.id }}</td>
                                                <td style="padding-left: 30px;">└─ {{ child.name }}</td>
                                                <td>{{ child.label }}</td>
                                                <td>
                                                    <i :class="child.icon" v-if="child.icon"></i>
                                                    {{ child.icon }}
                                                </td>
                                                <td>{{ child.url || child.route }}</td>
                                                <td>{{ menu.label }}</td>
                                                <td>{{ child.sort_order }}</td>
                                                <td>
                                                    <span class="badge" :class="child.is_active ? 'badge-success' : 'badge-secondary'">
                                                        {{ child.is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a :href="`/system/menus/${child.id}/edit`" class="btn btn-sm btn-warning">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <button @click="deleteMenu(child)" class="btn btn-sm btn-danger">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Grandchild menus (level 3) -->
                                            <tr v-for="grandchild in child.children" :key="`grandchild-${grandchild.id}`" 
                                                class="grandchild-row">
                                                <td>{{ grandchild.id }}</td>
                                                <td style="padding-left: 60px;">└── {{ grandchild.name }}</td>
                                                <td>{{ grandchild.label }}</td>
                                                <td>
                                                    <i :class="grandchild.icon" v-if="grandchild.icon"></i>
                                                    {{ grandchild.icon }}
                                                </td>
                                                <td>{{ grandchild.url || grandchild.route }}</td>
                                                <td>{{ child.label }}</td>
                                                <td>{{ grandchild.sort_order }}</td>
                                                <td>
                                                    <span class="badge" :class="grandchild.is_active ? 'badge-success' : 'badge-secondary'">
                                                        {{ grandchild.is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a :href="`/system/menus/${grandchild.id}/edit`" class="btn btn-sm btn-warning">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <button @click="deleteMenu(grandchild)" class="btn btn-sm btn-danger">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import PageHeaderWithCreateButton from '@/components/page-parts/PageHeaderWithCreateButton.vue'
import { router } from '@inertiajs/vue3'
import { useMenuStore } from '@/stores/menuStore.js'
import { ref, onMounted } from 'vue'

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    menus: Array
})

const menuStore = useMenuStore()
const sortedMenus = ref([])

const breadcrumbItems = [
    { label: '', link: '#', icon: 'fa fa-dashboard' },
    { label: 'System' },
    { label: 'Menu Management' }
]

// Initialize sorted menus
onMounted(() => {
    console.log('Menu Index mounted, props.menus:', props.menus)
    sortedMenus.value = [...props.menus]
    // Also update the store with fresh data from props
    if (props.menus && props.menus.length >= 0) {
        console.log('Setting menus in store from props:', props.menus)
        menuStore.setMenus(props.menus)
    }
})


const deleteMenu = (menu) => {
    if (confirm(`Are you sure you want to delete the menu "${menu.label}"?`)) {
        router.delete(`/system/menus/${menu.id}`, {
            onSuccess: () => {
                // Remove from store
                menuStore.removeMenu(menu.id)
                // Remove from local array
                sortedMenus.value = sortedMenus.value.filter(m => m.id !== menu.id)
                // Success message will be handled by flash message
            }
        })
    }
}

</script>

<style scoped>
.child-row {
    background-color: #f8f9fa;
}

.child-row td {
    border-left: 3px solid #007bff;
}

.grandchild-row {
    background-color: #e9ecef;
}

.grandchild-row td {
    border-left: 5px solid #28a745;
}

</style>