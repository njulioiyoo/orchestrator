<template>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Access Denied</h4>
                    </div>
                    <div class="card-body text-center">
                        <i class="fa fa-lock fa-5x text-danger mb-4"></i>
                        <h3 class="text-danger">Permission Denied</h3>
                        <p class="text-muted">
                            You don't have permission to access this resource.
                        </p>
                        <p class="text-muted" v-if="requiredPermission">
                            Required permission: <strong>{{ requiredPermission }}</strong>
                        </p>
                        <div class="mt-4">
                            <button @click="goBack" class="btn btn-primary">
                                <i class="fa fa-arrow-left"></i> Go Back
                            </button>
                            <a href="/dashboard" class="btn btn-secondary ml-2">
                                <i class="fa fa-home"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
    requiredPermission: {
        type: String,
        default: null
    }
})

const goBack = () => {
    window.history.back()
}
</script>