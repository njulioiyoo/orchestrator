<template>
    <!-- main left menu -->
    <div id="left-sidebar" class="sidebar">
        <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-arrow-left"></i></button>
        <div class="sidebar-scroll">
            <div class="user-account">
                <img src="../../js/images/user.png" class="rounded-circle user-photo" alt="User Profile Picture">
                <div class="dropdown">
                    <span>Welcome,</span>
                    <a href="javascript:void(0);" class="dropdown-toggle user-name" data-toggle="dropdown"><strong>{{
                        user?.name || 'User' }}</strong></a>
                    <ul class="dropdown-menu dropdown-menu-right account">
                        <li><a href="#" @click="logout"><i class="icon-power"></i>Logout</a></li>
                    </ul>
                </div>
                <hr>
            </div>
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#menu">Menu</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#Chat"><i
                            class="icon-book-open"></i></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#setting"><i
                            class="icon-settings"></i></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#question"><i
                            class="icon-question"></i></a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content padding-0">
                <div class="tab-pane active" id="menu">
                    <nav id="left-sidebar-nav" class="sidebar-nav">
                        <ul id="main-menu" class="metismenu li_animation_delay">
                            <!-- Dynamic Menu from Database -->
                            <template v-if="menuStore.hasMenus">
                                <li v-for="menu in menuStore.rootMenus" :key="menu.id" v-show="hasPermission(menu)"
                                    :class="{ 'active': isMenuOrChildrenActive(menu) }">

                                    <!-- Parent Menu with children - BIARKAN MetisMenu handle, tanpa custom click -->
                                    <a v-if="menu.children && menu.children.length > 0" href="javascript:void(0);"
                                        class="has-arrow">
                                        <i :class="menu.icon || 'fa fa-folder'"></i>
                                        <span>{{ menu.label }}</span>
                                    </a>

                                    <!-- Single Menu (no children) -->
                                    <a v-else :href="menu.url || '#'">
                                        <i :class="menu.icon || 'fa fa-circle'"></i>
                                        <span>{{ menu.label }}</span>
                                    </a>

                                    <!-- Children Menu - TAMPIL NORMAL, BIARKAN MetisMenu handle show/hide -->
                                    <ul v-if="menu.children && menu.children.length > 0">
                                        <li v-for="child in menu.children" :key="child.id" v-show="hasPermission(child)"
                                            :class="{ 'active': isMenuOrChildrenActive(child) }">

                                            <!-- Child with sub-children -->
                                            <a v-if="child.children && child.children.length > 0"
                                                href="javascript:void(0);" class="has-arrow">
                                                <i :class="child.icon || 'fa fa-folder-o'"></i>
                                                <span>{{ child.label }}</span>
                                            </a>

                                            <!-- Simple child -->
                                            <a v-else :href="child.url || '#'">
                                                <i :class="child.icon || 'fa fa-circle-o'"></i>
                                                <span>{{ child.label }}</span>
                                            </a>

                                            <!-- Sub-children - TAMPIL NORMAL -->
                                            <ul v-if="child.children && child.children.length > 0">
                                                <li v-for="subChild in child.children" :key="subChild.id"
                                                    v-show="hasPermission(subChild)"
                                                    :class="{ 'active': isCurrentRoute(subChild.url) }">
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
                                    <span class="name d-flex justify-content-between">Admin User <i
                                            class="fa fa-heart-o font-12"></i></span>
                                    <span class="message">admin@admin.com</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="media">
                                <img class="media-object" src="../../js/images/user.png" alt="">
                                <div class="media-body">
                                    <span class="name d-flex justify-content-between">Support Team <i
                                            class="fa fa-heart-o font-12"></i></span>
                                    <span class="message">support@company.com</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-pane" id="setting">
                    <h6>Choose Theme</h6>
                    <ul class="choose-skin list-unstyled">
                        <li data-theme="purple">
                            <div class="purple"></div>
                        </li>
                        <li data-theme="blue">
                            <div class="blue"></div>
                        </li>
                        <li data-theme="cyan" class="active">
                            <div class="cyan"></div>
                        </li>
                        <li data-theme="green">
                            <div class="green"></div>
                        </li>
                        <li data-theme="orange">
                            <div class="orange"></div>
                        </li>
                        <li data-theme="blush">
                            <div class="blush"></div>
                        </li>
                        <li data-theme="red">
                            <div class="red"></div>
                        </li>
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
import { computed, onMounted, nextTick, ref, watch } from 'vue'

const { user } = usePermissions()
const page = usePage()
const menuStore = useMenuStore()

// REACTIVE STATE untuk tracking expanded menu
const expandedMenuLabel = ref(null)
const forceRerender = ref(0) // Untuk trigger reactivity

// Get current route to determine active menu
const currentRoute = computed(() => page.url)

// Check if current route matches given URL
const isCurrentRoute = (url) => {
    if (!url) return false
    return currentRoute.value === url
}

// Function untuk update expanded state secara real-time
const updateExpandedState = () => {
    const expandedMenu = document.querySelector('#main-menu > li[aria-expanded="true"]')
    if (expandedMenu) {
        const span = expandedMenu.querySelector('span')
        expandedMenuLabel.value = span ? span.textContent.trim() : null
    } else {
        expandedMenuLabel.value = null
    }

    // Force Vue reactivity
    forceRerender.value++
}

// REACTIVE LOGIC: Berdasarkan state yang selalu update
const isMenuOrChildrenActive = (menu) => {
    // Trigger reactivity (tidak terlihat di UI tapi penting untuk Vue)
    forceRerender.value // Access reactive value

    // PRIORITAS 1: Jika ada menu yang expanded, hanya menu itu yang active
    if (expandedMenuLabel.value) {
        return menu.label?.trim() === expandedMenuLabel.value
    }

    // PRIORITAS 2: Tidak ada yang expanded, gunakan URL-based logic
    if (menu.url && isCurrentRoute(menu.url)) return true

    if (menu.children && menu.children.length > 0) {
        return menu.children.some(child => {
            if (child.url && isCurrentRoute(child.url)) return true
            if (child.children && child.children.length > 0) {
                return child.children.some(subChild => subChild.url && isCurrentRoute(subChild.url))
            }
            return false
        })
    }

    return false
}

// Setup DOM observer untuk real-time updates
const setupDOMObserver = () => {
    // Initial check
    updateExpandedState()

    // MutationObserver untuk detect perubahan aria-expanded
    const observer = new MutationObserver((mutations) => {
        let shouldUpdate = false

        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' &&
                (mutation.attributeName === 'aria-expanded' ||
                    mutation.attributeName === 'class')) {
                shouldUpdate = true
            }
        })

        if (shouldUpdate) {
            // Delay sedikit untuk memastikan DOM sudah terupdate
            setTimeout(updateExpandedState, 10)
        }
    })

    const targetNode = document.getElementById('main-menu')
    if (targetNode) {
        observer.observe(targetNode, {
            attributes: true,
            attributeFilter: ['aria-expanded', 'class'],
            subtree: true,
            childList: true
        })
    }

    return observer
}

// Watch for route changes to reset expanded state if needed
watch(currentRoute, () => {
    // Ketika route berubah, reset expanded state
    setTimeout(updateExpandedState, 100)
})

// Check if user has permission to see menu
const hasPermission = (menu) => {
    if (!menu.permissions || menu.permissions.length === 0) {
        return true
    }

    return menu.permissions.some(permission => {
        if (permission.type === 'role') {
            return user.value && user.value.roles &&
                user.value.roles.some(role => {
                    const roleName = typeof role === 'string' ? role : role.name
                    return roleName === permission.name
                })
        }

        if (permission.type === 'permission') {
            return user.value && user.value.permissions &&
                user.value.permissions.some(perm => {
                    const permName = typeof perm === 'string' ? perm : perm.name
                    return permName === permission.name
                })
        }
        return false
    })
}

onMounted(async () => {
    await menuStore.initializeMenus()

    nextTick(() => {
        if (window.$ && window.$.fn.metisMenu) {
            window.$('#main-menu').metisMenu()

            // Setup DOM observer setelah MetisMenu initialized
            setTimeout(() => {
                setupDOMObserver()
            }, 100)
        }
    })
})

const logout = () => {
    router.post('/logout')
}
</script>