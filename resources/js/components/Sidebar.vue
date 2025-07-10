<template>
    <!-- main left menu -->
    <div id="left-sidebar" class="sidebar">
        <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-arrow-left"></i></button>
        <div class="sidebar-scroll">
            <div class="user-account">
                <img src="../../js/images/user.png" class="rounded-circle user-photo" alt="User Profile Picture">
                <div class="dropdown">
                    <span>Welcome,</span>
                    <a href="javascript:void(0);" class="dropdown-toggle user-name"
                        data-toggle="dropdown"><strong>{{ user?.name || 'User' }}</strong></a>
                    <ul class="dropdown-menu dropdown-menu-right account">
                        <li><a href="#" @click="logout"><i class="icon-power"></i>Logout</a></li>
                    </ul>
                </div>
                <hr>
            </div>
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#menu">Menu</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#Chat"><i class="icon-book-open"></i></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#setting"><i class="icon-settings"></i></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#question"><i class="icon-question"></i></a></li>                
            </ul>
                
            <!-- Tab panes -->
            <div class="tab-content padding-0">
                <div class="tab-pane active" id="menu">
                    <nav id="left-sidebar-nav" class="sidebar-nav">
                        <ul id="main-menu" class="metismenu li_animation_delay">
                            <!-- Static Dashboard Menu -->
                            <li :class="{ 'active': isRouteActive('/dashboard') }">
                                <a href="/dashboard"><i class="fa fa-dashboard"></i><span>Dashboard</span></a>
                            </li>
                            
                            <!-- Dynamic Menu with Children -->
                            <template v-if="menuStore.hasMenus">
                                <li v-for="menu in menuStore.rootMenus" :key="menu.id" 
                                    v-show="hasPermission(menu)"
                                    :class="{ 'active': isMenuExpanded(menu) }">
                                    
                                    <!-- Parent Menu -->
                                    <a v-if="menu.children && menu.children.length > 0" 
                                       href="#" 
                                       class="has-arrow"
                                       :aria-expanded="isMenuExpanded(menu).toString()">
                                        <i :class="menu.icon || 'fa fa-folder'"></i>
                                        <span>{{ menu.label }}</span>
                                    </a>
                                    
                                    <!-- Single Menu (no children) -->
                                    <a v-else 
                                       :href="menu.url || '#'">
                                        <i :class="menu.icon || 'fa fa-circle'"></i>
                                        <span>{{ menu.label }}</span>
                                    </a>
                                    
                                    <!-- Children Menu -->
                                    <ul v-if="menu.children && menu.children.length > 0" 
                                        :aria-expanded="isMenuExpanded(menu).toString()"
                                        :class="{ 'collapse': !isMenuExpanded(menu), 'collapse in': isMenuExpanded(menu) }"
                                        :style="isMenuExpanded(menu) ? '' : 'display: none;'">
                                        <li v-for="child in menu.children" :key="child.id"
                                            v-show="hasPermission(child)"
                                            :class="{ 'active': isMenuExpanded(child) }">
                                            
                                            <!-- Child with sub-children -->
                                            <a v-if="child.children && child.children.length > 0"
                                               href="#"
                                               class="has-arrow"
                                               :aria-expanded="isMenuExpanded(child).toString()">
                                                <i :class="child.icon || 'fa fa-folder-o'"></i>
                                                <span>{{ child.label }}</span>
                                            </a>
                                            
                                            <!-- Simple child -->
                                            <a v-else 
                                               :href="child.url || '#'">
                                                <i :class="child.icon || 'fa fa-circle-o'"></i>
                                                <span>{{ child.label }}</span>
                                            </a>
                                            
                                            <!-- Sub-children -->
                                            <ul v-if="child.children && child.children.length > 0"
                                                :aria-expanded="isMenuExpanded(child).toString()"
                                                :class="{ 'collapse': !isMenuExpanded(child), 'collapse in': isMenuExpanded(child) }"
                                                :style="isMenuExpanded(child) ? '' : 'display: none;'">
                                                <li v-for="subChild in child.children" :key="subChild.id"
                                                    v-show="hasPermission(subChild)"
                                                    :class="{ 'active': isMenuActive(subChild) }">
                                                    <a :href="subChild.url || '#'">
                                                        <i :class="subChild.icon || 'fa fa-dot-circle-o'"></i>
                                                        <span>{{ subChild.label }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </template>
                            
                            <!-- Loading state -->
                            <template v-else-if="menuStore.isLoading">
                                <li>
                                    <a href="#"><i class="fa fa-spinner fa-spin"></i><span>Loading menus...</span></a>
                                </li>
                            </template>
                            
                            <!-- Fallback Static Menu (if dynamic menus fail to load or are empty) -->
                            <template v-if="menuStore.shouldShowFallback">
                                <li v-permission:role="'Admin'" :class="{ 'active': isMasterActive, 'mm-active': isMasterActive }">
                                    <a href="#Master" class="has-arrow" :class="{ 'mm-collapsed': !isMasterActive }"><i class="fa fa-cogs"></i><span>Master</span></a>
                                    <ul :class="{ 'mm-show': isMasterActive }">
                                        <li :class="{ 'active': isSystemActive, 'mm-active': isSystemActive }">
                                            <a href="#System" class="has-arrow" :class="{ 'mm-collapsed': !isSystemActive }"><i class="fa fa-database"></i><span>System</span></a>
                                            <ul :class="{ 'mm-show': isSystemActive }">
                                                <li :class="{ 'active': isUsersActive }">
                                                    <a href="/system/users"><i class="fa fa-users"></i><span>Users</span></a>
                                                </li>
                                                <li :class="{ 'active': isRolesActive }">
                                                    <a href="/system/roles"><i class="fa fa-user-circle"></i><span>Roles</span></a>
                                                </li>
                                                <li :class="{ 'active': isPermissionsActive }">
                                                    <a href="/system/permissions"><i class="fa fa-shield"></i><span>Permissions</span></a>
                                                </li>
                                                <li :class="{ 'active': isAuditsActive }">
                                                    <a href="/system/audits"><i class="fa fa-history"></i><span>Audit Logs</span></a>
                                                </li>
                                                <li :class="{ 'active': isMenusActive }">
                                                    <a href="/system/menus"><i class="fa fa-bars"></i><span>Menus</span></a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </template>
                        </ul>
                    </nav>
                </div>
                
                <div class="tab-pane" id="Chat">
                    <form>
                        <div class="input-group m-b-20">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon-magnifier"></i></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Search...">
                        </div>
                    </form>
                    <ul class="right_chat list-unstyled li_animation_delay">
                        <li>
                            <a href="javascript:void(0);" class="media">
                                <img class="media-object" src="../../js/images/user.png" alt="">
                                <div class="media-body">
                                    <span class="name d-flex justify-content-between">Admin User <i class="fa fa-heart-o font-12"></i></span>
                                    <span class="message">admin@admin.com</span>
                                </div>
                            </a>                            
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="media">
                                <img class="media-object" src="../../js/images/user.png" alt="">
                                <div class="media-body">
                                    <span class="name d-flex justify-content-between">Support Team <i class="fa fa-heart-o font-12"></i></span>
                                    <span class="message">support@company.com</span>
                                </div>
                            </a>                            
                        </li>
                    </ul>
                </div>
                
                <div class="tab-pane" id="setting">
                    <h6>Choose Theme</h6>
                    <ul class="choose-skin list-unstyled">
                        <li data-theme="purple"><div class="purple"></div></li>
                        <li data-theme="blue"><div class="blue"></div></li>
                        <li data-theme="cyan" class="active"><div class="cyan"></div></li>
                        <li data-theme="green"><div class="green"></div></li>
                        <li data-theme="orange"><div class="orange"></div></li>
                        <li data-theme="blush"><div class="blush"></div></li>
                        <li data-theme="red"><div class="red"></div></li>
                    </ul>

                    <hr>
                    <h6>General Settings</h6>
                    <ul class="setting-list list-unstyled">
                        <li>
                            <label class="fancy-checkbox">
                                <input type="checkbox" name="checkbox" checked>
                                <span>Allowed Notifications</span>
                            </label>                      
                        </li>
                        <li>
                            <label class="fancy-checkbox">
                                <input type="checkbox" name="checkbox">
                                <span>Offline Mode</span>
                            </label>
                        </li>
                        <li>
                            <label class="fancy-checkbox">
                                <input type="checkbox" name="checkbox">
                                <span>Location Permission</span>
                            </label>
                        </li>
                    </ul>
                </div>
                
                <div class="tab-pane" id="question">
                    <form>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon-magnifier"></i></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Search...">
                        </div>
                    </form>
                    <ul class="list-unstyled question">
                        <li class="menu-heading">HOW-TO</li>
                        <li><a href="javascript:void(0);">How to Create Menu</a></li>
                        <li><a href="javascript:void(0);">Manage User Roles</a></li>
                        <li><a href="javascript:void(0);">System Analytics</a></li>
                        <li class="menu-heading">ACCOUNT</li>
                        <li><a href="javascript:void(0);">Create New Account</a></li>
                        <li><a href="javascript:void(0);">Change Password</a></li>
                        <li><a href="javascript:void(0);">Privacy &amp; Policy</a></li>
                        <li class="menu-heading">SYSTEM</li>
                        <li><a href="javascript:void(0);">Audit Logs</a></li>
                        <li><a href="javascript:void(0);">Backup &amp; Restore</a></li>                        
                        <li class="menu-button mt-3">
                            <a href="#" class="btn btn-primary btn-block">Documentation</a>
                        </li>
                    </ul>
                </div>    
            </div>
        </div>
    </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { usePermissions } from '@/composables/usePermissions.js'
import { useMenuStore } from '@/stores/menuStore.js'
import { computed, onMounted, watch, nextTick } from 'vue'

const { user } = usePermissions()
const page = usePage()
const menuStore = useMenuStore()

// Get current route to determine active menu
const currentRoute = computed(() => page.url)

// Debug computed for menu store state
const storeState = computed(() => {
    return {
        hasMenus: menuStore.hasMenus,
        menusCount: menuStore.menus.length,
        isLoaded: menuStore.isLoaded,
        isLoading: menuStore.isLoading,
        hasError: menuStore.hasError,
        shouldShowFallback: menuStore.shouldShowFallback,
        rootMenusCount: menuStore.rootMenus.length
    }
})


// Check if route is active
const isRouteActive = (route) => {
    if (route === '/dashboard') {
        return currentRoute.value === '/dashboard'
    }
    return currentRoute.value.startsWith(route)
}

// Check if menu has active children (recursive)
const hasActiveChildren = (menu) => {
    if (!menu.children || menu.children.length === 0) return false
    
    return menu.children.some(child => {
        // Check if child itself is active
        if (isMenuActive(child)) return true
        
        // Recursively check if this child has active children
        return hasActiveChildren(child)
    })
}

// Check if menu should be expanded
const isMenuExpanded = (menu) => {
    if (menu.url && isRouteActive(menu.url)) return true
    if (menu.route && isRouteActive(menu.route)) return true
    
    // For Master menu, expand if we're in any system/* path
    if (menu.name === 'master' && currentRoute.value.startsWith('/system/')) return true
    
    return hasActiveChildren(menu)
}

// Check if menu item is active
const isMenuActive = (menu) => {
    if (menu.url && isRouteActive(menu.url)) return true
    if (menu.route && isRouteActive(menu.route)) return true
    
    const currentUrl = currentRoute.value
    const urlParts = currentUrl.split('/').filter(part => part)
    
    // For System menu, active if we're in any system/* path and it has active children
    if (menu.name === 'system' && currentUrl.startsWith('/system/')) {
        return true
    }
    
    // For grandchildren (users, roles, permissions, etc.), check exact URL match
    if (menu.name && urlParts.length >= 2 && urlParts[0] === 'system' && urlParts[1] === menu.name) {
        return true
    }
    
    return false
}

// Check if user has permission to see menu
const hasPermission = (menu) => {
    if (!menu.permissions || menu.permissions.length === 0) {
        return true
    }
    
    // Check if user has any of the required permissions
    return menu.permissions.some(permission => {
        if (permission.type === 'role') {
            return user.value && user.value.roles && 
                   user.value.roles.some(role => {
                       // Handle both string and object role formats
                       const roleName = typeof role === 'string' ? role : role.name
                       return roleName === permission.name
                   })
        }
        
        if (permission.type === 'permission') {
            return user.value && user.value.permissions && 
                   user.value.permissions.some(perm => {
                       // Handle both string and object permission formats
                       const permName = typeof perm === 'string' ? perm : perm.name
                       return permName === permission.name
                   })
        }
        return false
    })
}

// Computed properties for fallback menu
const isSystemActive = computed(() => {
    return currentRoute.value.startsWith('/system/')
})

const isMasterActive = computed(() => {
    return isSystemActive.value
})

const isUsersActive = computed(() => {
    return currentRoute.value.startsWith('/system/users')
})

const isRolesActive = computed(() => {
    return currentRoute.value.startsWith('/system/roles')
})

const isPermissionsActive = computed(() => {
    return currentRoute.value.startsWith('/system/permissions')
})

const isAuditsActive = computed(() => {
    return currentRoute.value.startsWith('/system/audits')
})

const isMenusActive = computed(() => {
    return currentRoute.value.startsWith('/system/menus')
})

onMounted(async () => {
    await menuStore.initializeMenus()
})

const logout = () => {
    router.post('/logout')
}
</script>