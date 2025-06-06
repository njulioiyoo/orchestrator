<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Analytical</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
defineOptions({
    layout: AppLayout
})

import { ref, onMounted } from 'vue'

const breadcrumbItems = ref([
    { label: '', link: 'index.html', icon: 'fa fa-dashboard' },
    { label: 'Dashboard' },
    { label: 'Analytical' },
])
</script>
