<template>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ title }}</h1>
            <p v-if="subtitle" class="text-muted mb-0">{{ subtitle }}</p>
        </div>
        <slot name="actions"></slot>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    }
})
</script>