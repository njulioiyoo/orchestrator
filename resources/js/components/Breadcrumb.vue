<template>
    <ul class="breadcrumb">
        <li v-for="(item, index) in items" :key="index" class="breadcrumb-item"
            :class="{ active: index === items.length - 1 }">
            <template v-if="item.link && index !== items.length - 1">
                <a :href="item.link">
                    <i v-if="item.icon" :class="item.icon"></i>
                    {{ item.label }}
                </a>
            </template>
            <template v-else>
                <i v-if="item.icon" :class="item.icon"></i>
                {{ item.label }}
            </template>
        </li>
    </ul>
</template>

<script setup>
defineProps({
    items: {
        type: Array,
        required: true
    }
})
</script>
