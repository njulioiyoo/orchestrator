<script setup>
import { useForm, usePage, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useUserStore } from '@/stores/userStore.js'

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    userRoles: {
        type: Array,
        default: () => [],
    },
    submitUrl: String,
    isEdit: {
        type: Boolean,
        default: false,
    },
})

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    role: props.userRoles?.[0] ?? '', // Single role instead of array
})

// Form initialized with user role pre-selected

// Modal state
const showModal = ref(false)

const errors = usePage().props.errors || {}

// Use Pinia store for state management
const userStore = useUserStore()

const handleSubmit = () => {
    const method = props.isEdit ? form.put.bind(form) : form.post.bind(form)
    
    userStore.setProcessing(true)

    method(props.submitUrl, {
        preserveScroll: false,
        onSuccess: () => {
            userStore.setProcessing(false)
            
            if (props.isEdit) {
                // Trigger user updated action
                userStore.userUpdated({
                    id: props.user?.id,
                    name: form.name,
                    email: form.email,
                    role: form.role
                })
            } else {
                // Reset form for new user creation
                form.reset()
                // Trigger user created action
                userStore.userCreated({
                    name: form.name,
                    email: form.email,
                    role: form.role
                })
            }
            
            // Navigate back to index
            router.visit('/system/users')
        },
        onError: () => {
            userStore.setProcessing(false)
            console.warn('Form submission failed.')
        }
    })
}

</script>

<template>
    <form @submit.prevent="handleSubmit">
        <div class="form-group">
            <label>Name</label>
            <input v-model="form.name" type="text" class="form-control" />
            <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" type="email" class="form-control" />
            <small v-if="errors.email" class="text-danger">{{ errors.email }}</small>
        </div>

        <div class="form-group mt-3">
            <label>{{ isEdit ? 'New Password (optional)' : 'Password' }}</label>
            <input v-model="form.password" type="password" class="form-control" />
            <small v-if="isEdit" class="text-muted">Leave blank if not changing password</small>
            <small v-if="errors.password" class="text-danger">{{ errors.password }}</small>
        </div>

        <div class="form-group mt-3">
            <label>Role</label>
            <select v-model="form.role" class="form-control">
                <option value="">Select Role</option>
                <option v-for="role in props.roles" :key="role.id" :value="role.name">
                    {{ role.name }}
                </option>
            </select>
            <small v-if="errors.role" class="text-danger">{{ errors.role }}</small>
        </div>

        <button type="submit" class="btn btn-primary mt-3" :disabled="form.processing">
            <i class="fa fa-save"></i> {{ form.processing ? 'Saving...' : 'Save' }}
        </button>
        <Link href="/system/users" class="btn btn-secondary mt-3 ml-2">Cancel</Link>
    </form>
</template>
