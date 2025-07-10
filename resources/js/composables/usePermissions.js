import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermissions() {
    const page = usePage()
    
    const user = computed(() => page.props.auth.user)
    const permissions = computed(() => user.value?.permissions || [])
    const roles = computed(() => user.value?.roles || [])
    
    const hasPermission = (permission) => {
        if (!user.value) return false
        return permissions.value.includes(permission)
    }
    
    const hasAnyPermission = (permissionList) => {
        if (!user.value) return false
        return permissionList.some(permission => permissions.value.includes(permission))
    }
    
    const hasAllPermissions = (permissionList) => {
        if (!user.value) return false
        return permissionList.every(permission => permissions.value.includes(permission))
    }
    
    const hasRole = (role) => {
        if (!user.value) return false
        return roles.value.includes(role)
    }
    
    const hasAnyRole = (roleList) => {
        if (!user.value) return false
        return roleList.some(role => roles.value.includes(role))
    }
    
    const canViewUsers = computed(() => hasPermission('view users'))
    const canCreateUsers = computed(() => hasPermission('create users'))
    const canEditUsers = computed(() => hasPermission('edit users'))
    const canDeleteUsers = computed(() => hasPermission('delete users'))
    
    const canViewRoles = computed(() => hasPermission('view roles'))
    const canCreateRoles = computed(() => hasPermission('create roles'))
    const canEditRoles = computed(() => hasPermission('edit roles'))
    const canDeleteRoles = computed(() => hasPermission('delete roles'))
    
    const canViewPermissions = computed(() => hasPermission('view permissions'))
    const canCreatePermissions = computed(() => hasPermission('create permissions'))
    const canEditPermissions = computed(() => hasPermission('edit permissions'))
    const canDeletePermissions = computed(() => hasPermission('delete permissions'))
    
    return {
        user,
        permissions,
        roles,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        hasRole,
        hasAnyRole,
        canViewUsers,
        canCreateUsers,
        canEditUsers,
        canDeleteUsers,
        canViewRoles,
        canCreateRoles,
        canEditRoles,
        canDeleteRoles,
        canViewPermissions,
        canCreatePermissions,
        canEditPermissions,
        canDeletePermissions
    }
}