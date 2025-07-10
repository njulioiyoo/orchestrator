import { usePage } from '@inertiajs/vue3'

// Shared function for permission checking
const checkPermission = (el, binding) => {
    const page = usePage()
    const user = page.props.auth?.user
    
    if (!user) {
        el.remove()
        return
    }
    
    const permissions = user.permissions || []
    const roles = user.roles || []
    
    let hasAccess = false
    
    if (binding.arg === 'role') {
        // v-permission:role="'admin'"
        if (typeof binding.value === 'string') {
            hasAccess = roles.includes(binding.value)
        } else if (Array.isArray(binding.value)) {
            hasAccess = binding.value.some(role => roles.includes(role))
        }
    } else {
        // v-permission="'create users'" or v-permission="['create users', 'edit users']"
        if (typeof binding.value === 'string') {
            hasAccess = permissions.includes(binding.value)
        } else if (Array.isArray(binding.value)) {
            if (binding.modifiers.all) {
                // v-permission.all="['create users', 'edit users']"
                hasAccess = binding.value.every(permission => permissions.includes(permission))
            } else {
                // v-permission="['create users', 'edit users']"
                hasAccess = binding.value.some(permission => permissions.includes(permission))
            }
        }
    }
    
    if (!hasAccess) {
        el.remove()
    }
}

export default {
    mounted(el, binding) {
        checkPermission(el, binding)
    },
    
    updated(el, binding) {
        // Re-check on update
        checkPermission(el, binding)
    }
}