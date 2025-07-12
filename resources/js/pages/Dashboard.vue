<template>
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Dashboard</h2>
                    <Breadcrumb :items="breadcrumbItems" />
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 text-right">
                    <button 
                        @click="refreshMetrics" 
                        class="btn btn-primary btn-sm"
                        :disabled="loading"
                    >
                        <i class="fa fa-refresh" :class="{ 'fa-spin': loading }"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- System Health Status -->
        <div class="row clearfix mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="header">
                        <h2>System Health Status</h2>
                    </div>
                    <div class="body">
                        <div class="row" v-if="healthData">
                            <div class="col-lg-3 col-md-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-heartbeat" 
                                           :class="{
                                               'text-success': healthData.status === 'healthy',
                                               'text-warning': healthData.status === 'warning',
                                               'text-danger': healthData.status === 'critical'
                                           }"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Overall Status</div>
                                        <div class="number" 
                                             :class="{
                                                 'text-success': healthData.status === 'healthy',
                                                 'text-warning': healthData.status === 'warning',
                                                 'text-danger': healthData.status === 'critical'
                                             }">
                                            {{ capitalizeFirst(healthData.status) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6" v-for="(check, name) in healthData.checks" :key="name">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i :class="getHealthIcon(name)" 
                                           :class="{
                                               'text-success': check.status === 'healthy',
                                               'text-warning': check.status === 'warning',
                                               'text-danger': check.status === 'unhealthy'
                                           }"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">{{ capitalizeFirst(name) }}</div>
                                        <div class="number"
                                             :class="{
                                                 'text-success': check.status === 'healthy',
                                                 'text-warning': check.status === 'warning',
                                                 'text-danger': check.status === 'unhealthy'
                                             }">
                                            {{ capitalizeFirst(check.status) }}
                                        </div>
                                        <small class="text-muted">{{ check.message }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row clearfix mb-4" v-if="metricsData">
            <!-- User Metrics -->
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="header">
                        <h2>User Metrics</h2>
                    </div>
                    <div class="body">
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-users text-primary"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Total Users</div>
                                        <div class="number">{{ metricsData.user_metrics.total_users }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-user-check text-success"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Active Users</div>
                                        <div class="number">{{ metricsData.user_metrics.active_users }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-user-plus text-info"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">New Users (7d)</div>
                                        <div class="number">{{ metricsData.user_metrics.recent_users }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-chart-line" 
                                           :class="{
                                               'text-success': metricsData.user_metrics.growth_rate > 0,
                                               'text-danger': metricsData.user_metrics.growth_rate < 0,
                                               'text-muted': metricsData.user_metrics.growth_rate === 0
                                           }"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Growth Rate</div>
                                        <div class="number">{{ metricsData.user_metrics.growth_rate }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Metrics -->
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="header">
                        <h2>System Metrics</h2>
                    </div>
                    <div class="body">
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-shield-alt text-warning"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Roles</div>
                                        <div class="number">{{ metricsData.system_metrics.total_roles }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-key text-info"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Permissions</div>
                                        <div class="number">{{ metricsData.system_metrics.total_permissions }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-bars text-primary"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Total Menus</div>
                                        <div class="number">{{ metricsData.system_metrics.total_menus }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-check text-success"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Active Menus</div>
                                        <div class="number">{{ metricsData.system_metrics.active_menus }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                PHP: {{ metricsData.system_metrics.php_version }} | 
                                Laravel: {{ metricsData.system_metrics.laravel_version }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Metrics -->
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="header">
                        <h2>Activity Metrics</h2>
                    </div>
                    <div class="body">
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-history text-info"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Total Activities</div>
                                        <div class="number">{{ metricsData.activity_metrics.total_activities }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-clock text-primary"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Recent (7d)</div>
                                        <div class="number">{{ metricsData.activity_metrics.recent_activities }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Activities by Type</h6>
                            <div v-for="(count, event) in metricsData.activity_metrics.activities_by_type" :key="event" class="d-flex justify-content-between">
                                <span>{{ capitalizeFirst(event) }}:</span>
                                <span class="badge badge-secondary">{{ count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Metrics -->
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="header">
                        <h2>Database Metrics</h2>
                    </div>
                    <div class="body">
                        <div class="row">
                            <div class="col-12">
                                <div class="info-box-4 hover-zoom-effect">
                                    <div class="icon">
                                        <i class="fa fa-database text-success"></i>
                                    </div>
                                    <div class="content">
                                        <div class="text">Total Records</div>
                                        <div class="number">{{ metricsData.database_metrics.total_records }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Records by Table</h6>
                            <div v-for="(count, table) in metricsData.database_metrics.table_counts" :key="table" class="d-flex justify-content-between">
                                <span>{{ capitalizeFirst(table) }}:</span>
                                <span class="badge badge-info">{{ count }}</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                Database Size: {{ metricsData.database_metrics.database_size }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-else class="row clearfix">
            <div class="col-12 text-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading metrics...</span>
                </div>
                <p class="mt-2">Loading performance metrics...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import { useToast } from '@/Composables/useToast'
defineOptions({
    layout: AppLayout
})

import { ref, onMounted } from 'vue'
import axios from 'axios'

const { success, error } = useToast()

const breadcrumbItems = ref([
    { label: '', link: 'dashboard', icon: 'fa fa-dashboard' },
    { label: 'Dashboard' },
])

const loading = ref(false)
const metricsData = ref(null)
const healthData = ref(null)

const fetchMetrics = async () => {
    loading.value = true
    try {
        const [metricsResponse, healthResponse] = await Promise.all([
            axios.get('/api/metrics/performance'),
            axios.get('/api/metrics/health')
        ])
        
        if (metricsResponse.data.success) {
            metricsData.value = metricsResponse.data.data
        }
        
        if (healthResponse.data.success) {
            healthData.value = healthResponse.data.data
        }
    } catch (err) {
        console.error('Error fetching metrics:', err)
        error('Failed to load dashboard metrics')
    } finally {
        loading.value = false
    }
}

const refreshMetrics = async () => {
    await fetchMetrics()
    success('Metrics refreshed successfully')
}

const capitalizeFirst = (str) => {
    if (!str) return ''
    return str.charAt(0).toUpperCase() + str.slice(1)
}

const getHealthIcon = (checkName) => {
    const icons = {
        database: 'fa fa-database',
        cache: 'fa fa-memory',
        storage: 'fa fa-hdd',
        memory: 'fa fa-microchip'
    }
    return icons[checkName] || 'fa fa-cog'
}

onMounted(() => {
    fetchMetrics()
    
    // Auto-refresh every 5 minutes
    setInterval(fetchMetrics, 300000)
})
</script>
