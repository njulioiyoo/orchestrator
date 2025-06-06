<template>
    <div class="header d-flex justify-content-between align-items-center">
        <h2>{{ title }}</h2>
        <Link :href="buttonLink" class="btn btn-default">
        <i :class="buttonIcon"></i> <span>{{ buttonText }}</span>
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    title: String,
    buttonLink: String,
    buttonText: {
        type: String,
        default: 'Create'
    },
    buttonIcon: {
        type: String,
        default: 'fa fa-plus-square'
    }
})
</script>
