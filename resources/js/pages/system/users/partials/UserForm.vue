<script setup>
import { useForm, usePage, Link } from '@inertiajs/vue3'

const props = defineProps({
    user: {
        type: Object,
        default: null,
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
})

const errors = usePage().props.errors || {}

const notifyNewUserCreated = () => {
    try {
        localStorage.setItem('newUserCreated', 'true')
        localStorage.setItem('newUserTimestamp', Date.now().toString())
        window.dispatchEvent(new CustomEvent('user-created'))
    } catch (error) {
        console.error('Error in notifyNewUserCreated:', error)
    }
}

const handleSubmit = () => {
    const method = props.isEdit ? form.put.bind(form) : form.post.bind(form)

    method(props.submitUrl, {
        preserveScroll: true,
        onSuccess: () => {
            if (!props.isEdit) {
                notifyNewUserCreated()
                form.reset()
            }
        },
        onError: () => {
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

        <button type="submit" class="btn btn-primary mt-3" :disabled="form.processing">
            <i class="fa fa-save"></i> {{ form.processing ? 'Saving...' : 'Save' }}
        </button>
        <Link href="/system/users" class="btn btn-secondary mt-3 ml-2">Cancel</Link>
    </form>
</template>
