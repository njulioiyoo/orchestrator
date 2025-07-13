<template>
    <div class="container-fluid">
            <div class="block-header">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <h2>System Dashboard</h2>
                        <Breadcrumb :items="breadcrumbItems" />
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="d-flex flex-row-reverse">
                            <div class="page_action">
                                <button 
                                    @click="refreshMetrics" 
                                    type="button" 
                                    class="btn btn-primary"
                                    :disabled="loading"
                                >
                                    <i class="fa fa-refresh" :class="{ 'fa-spin': loading }"></i> 
                                    {{ loading ? 'Refreshing...' : 'Refresh Data' }}
                                </button>
                                <button type="button" class="btn btn-secondary">
                                    <i class="fa fa-download"></i> Export Report
                                </button>
                            </div>
                            <div class="p-2 d-flex" v-if="lastUpdated">
                                <small class="text-muted align-self-center mr-3">
                                    <i class="fa fa-clock"></i>
                                    Last updated: {{ formatTime(lastUpdated) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Metrics Cards -->
            <div class="row clearfix row-deck" v-if="metricsData">
                <!-- User Metrics -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card number-chart">
                        <div class="body">
                            <span class="text-uppercase">Total Users</span>
                            <h4 class="mb-0 mt-2">
                                {{ formatNumber(metricsData.user_metrics.total_users) }}
                                <i class="fa" :class="getGrowthIcon(metricsData.user_metrics.growth_rate)" 
                                   :style="getGrowthColor(metricsData.user_metrics.growth_rate)"></i>
                            </h4>
                            <small class="text-muted">{{ metricsData.user_metrics.active_users }} active users</small>
                        </div>
                        <div class="sparkline-container">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-success" 
                                     :style="`width: ${metricsData.user_metrics.activation_rate}%`"
                                     :title="`${metricsData.user_metrics.activation_rate}% activation rate`">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card number-chart">
                        <div class="body">
                            <span class="text-uppercase">System Health</span>
                            <h4 class="mb-0 mt-2" v-if="healthData">
                                {{ capitalizeFirst(healthData.status) }}
                                <i class="fa fa-heartbeat" 
                                   :class="getHealthTextColor(healthData.status)"></i>
                            </h4>
                            <small class="text-muted">All components monitored</small>
                        </div>
                        <div class="sparkline-container" v-if="healthData">
                            <div class="d-flex justify-content-between">
                                <div v-for="(check, name) in healthData.checks" :key="name" 
                                     class="health-dot" 
                                     :class="getHealthDotClass(check.status)"
                                     :title="`${capitalizeFirst(name)}: ${capitalizeFirst(check.status)}`">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Metrics -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card number-chart">
                        <div class="body">
                            <span class="text-uppercase">Activities (7d)</span>
                            <h4 class="mb-0 mt-2">
                                {{ formatNumber(metricsData.activity_metrics.recent_activities) }}
                                <i class="fa fa-trending-up text-info"></i>
                            </h4>
                            <small class="text-muted">{{ formatNumber(metricsData.activity_metrics.total_activities) }} total activities</small>
                        </div>
                        <div class="sparkline-container">
                            <div class="activity-types">
                                <div v-for="(count, event) in metricsData.activity_metrics.activities_by_type" 
                                     :key="event" 
                                     class="activity-type-bar"
                                     :style="`width: ${getActivityPercentage(count, metricsData.activity_metrics.total_activities)}%`"
                                     :title="`${capitalizeFirst(event)}: ${count}`">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Status -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card">
                        <div class="body d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0">Database Status</h6>
                                <small class="text-success" v-if="healthData && healthData.checks.database.status === 'healthy'">Connected</small>
                                <small class="text-danger" v-else>Disconnected</small>
                            </div>
                            <div class="text-right">
                                <h6 class="mb-0">{{ formatNumber(metricsData.database_metrics.total_records) }}</h6>
                                <small class="text-muted">Records</small>
                            </div>
                        </div>
                        <hr>
                        <div class="body d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0">Database Size</h6>
                                <small class="text-muted">{{ metricsData.database_metrics.database_size }}</small>
                            </div>
                            <div class="text-right">
                                <h6 class="mb-0">{{ Object.keys(metricsData.database_metrics.table_counts).length }}</h6>
                                <small class="text-muted">Tables</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State for Metrics -->
            <div class="row clearfix row-deck" v-else>
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading metrics...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading dashboard data...</p>
                </div>
            </div>

            <!-- Detailed Analytics Section -->
            <div class="row clearfix row-deck" v-if="metricsData">
                <!-- System Health Details -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="header">
                            <h2>System Health Monitor</h2>
                            <ul class="header-dropdown">
                                <li class="dropdown">
                                    <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="javascript:void(0);" @click="refreshMetrics">Refresh</a></li>
                                        <li><a href="javascript:void(0);">View Details</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="body" v-if="healthData">
                            <ul class="list-group">
                                <li v-for="(check, name) in healthData.checks" :key="name" 
                                    class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ capitalizeFirst(name) }}</h6>
                                        <small :class="getHealthTextColor(check.status)">{{ capitalizeFirst(check.status) }}</small>
                                        <div v-if="check.response_time">
                                            <small class="text-muted">Response: {{ check.response_time }}ms</small>
                                        </div>
                                        <div v-if="check.usage_percent">
                                            <small class="text-muted">Usage: {{ check.usage_percent }}%</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge" :class="getHealthBadgeClass(check.status)">
                                            {{ capitalizeFirst(check.status) }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- User Analytics -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="header">
                            <h2>User Analytics</h2>
                        </div>
                        <div class="body">
                            <ul class="list-group">
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Users</h6>
                                        <small class="text-muted">All registered users</small>
                                    </div>
                                    <span class="badge badge-primary badge-pill">{{ formatNumber(metricsData.user_metrics.total_users) }}</span>
                                </li>
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">Active Users</h6>
                                        <small class="text-muted">Verified accounts</small>
                                    </div>
                                    <span class="badge badge-success badge-pill">{{ formatNumber(metricsData.user_metrics.active_users) }}</span>
                                </li>
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">New Users (7d)</h6>
                                        <small class="text-muted">Recent registrations</small>
                                    </div>
                                    <span class="badge badge-info badge-pill">{{ formatNumber(metricsData.user_metrics.recent_users) }}</span>
                                </li>
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">Growth Rate</h6>
                                        <small class="text-muted">Weekly comparison</small>
                                    </div>
                                    <span class="badge badge-pill" :class="getGrowthBadgeClass(metricsData.user_metrics.growth_rate)">
                                        {{ metricsData.user_metrics.growth_rate }}%
                                    </span>
                                </li>
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">Activation Rate</h6>
                                        <small class="text-muted">User engagement</small>
                                    </div>
                                    <div class="w-50">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" 
                                                 :style="`width: ${metricsData.user_metrics.activation_rate}%`">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ metricsData.user_metrics.activation_rate }}%</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- System Configuration -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="header">
                            <h2>System Configuration</h2>
                        </div>
                        <div class="body">
                            <ul class="list-group">
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">Roles</h6>
                                        <small class="text-muted">User roles defined</small>
                                    </div>
                                    <span class="badge badge-warning badge-pill">{{ metricsData.system_metrics.total_roles }}</span>
                                </li>
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">Permissions</h6>
                                        <small class="text-muted">Access controls</small>
                                    </div>
                                    <span class="badge badge-info badge-pill">{{ metricsData.system_metrics.total_permissions }}</span>
                                </li>
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">Menu Items</h6>
                                        <small class="text-muted">Navigation entries</small>
                                    </div>
                                    <span class="badge badge-primary badge-pill">{{ metricsData.system_metrics.total_menus }}</span>
                                </li>
                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                    <div>
                                        <h6 class="mb-0">Active Menus</h6>
                                        <small class="text-muted">Enabled navigation</small>
                                    </div>
                                    <span class="badge badge-success badge-pill">{{ metricsData.system_metrics.active_menus }}</span>
                                </li>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fa fa-code text-primary"></i> PHP</span>
                                        <small class="text-muted">{{ metricsData.system_metrics.php_version }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span><i class="fa fa-cog text-danger"></i> Laravel</span>
                                        <small class="text-muted">{{ metricsData.system_metrics.laravel_version }}</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Analytics Section -->
            <div class="row clearfix row-deck" v-if="metricsData">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="header">
                            <h2>Database Overview</h2>
                        </div>
                        <div class="body">
                            <div class="row mb-3">
                                <div class="col-6 text-center">
                                    <h4 class="text-primary">{{ formatNumber(metricsData.database_metrics.total_records) }}</h4>
                                    <span class="text-muted">Total Records</span>
                                </div>
                                <div class="col-6 text-center">
                                    <h4 class="text-success">{{ metricsData.database_metrics.database_size }}</h4>
                                    <span class="text-muted">Database Size</span>
                                </div>
                            </div>
                            <hr>
                            <h6>Records by Table</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr v-for="(count, table) in metricsData.database_metrics.table_counts" :key="table">
                                            <td>{{ capitalizeFirst(table) }}</td>
                                            <td class="text-right">{{ formatNumber(count) }}</td>
                                            <td width="30%">
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar bg-info" 
                                                         :style="`width: ${getTablePercentage(count, metricsData.database_metrics.total_records)}%`">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="header">
                            <h2>Recent Activities</h2>
                        </div>
                        <div class="body">
                            <div class="row mb-3">
                                <div class="col-6 text-center">
                                    <h4 class="text-info">{{ formatNumber(metricsData.activity_metrics.recent_activities) }}</h4>
                                    <span class="text-muted">Last 7 Days</span>
                                </div>
                                <div class="col-6 text-center">
                                    <h4 class="text-primary">{{ formatNumber(metricsData.activity_metrics.total_activities) }}</h4>
                                    <span class="text-muted">Total Activities</span>
                                </div>
                            </div>
                            <hr>
                            <h6>Activity Types</h6>
                            <div class="activity-chart">
                                <div v-for="(count, event) in metricsData.activity_metrics.activities_by_type" :key="event" 
                                     class="activity-item-row">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-capitalize">{{ event }}</span>
                                        <span class="badge badge-secondary">{{ count }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" 
                                             :style="`width: ${getActivityPercentage(count, metricsData.activity_metrics.total_activities)}%`">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics Widget -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="header">
                            <h2>Performance Metrics</h2>
                        </div>
                        <div class="body">
                            <div class="d-flex justify-content-between mb-3" v-if="healthData">
                                <div class="text-center">
                                    <h5 class="mb-1" :class="getHealthTextColor(healthData.checks.database.status)">
                                        {{ healthData.checks.database.response_time || 'N/A' }}
                                    </h5>
                                    <small class="text-muted">DB Response (ms)</small>
                                </div>
                                <div class="text-center">
                                    <h5 class="mb-1" :class="getHealthTextColor(healthData.checks.memory.status)">
                                        {{ healthData.checks.memory.usage_percent || 'N/A' }}%
                                    </h5>
                                    <small class="text-muted">Memory Usage</small>
                                </div>
                                <div class="text-center">
                                    <h5 class="mb-1" :class="getHealthTextColor(healthData.checks.storage.status)">
                                        {{ healthData.checks.storage.usage_percent || 'N/A' }}%
                                    </h5>
                                    <small class="text-muted">Storage Usage</small>
                                </div>
                            </div>
                            <hr>
                            <h6>User Engagement</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>User Activation</span>
                                    <span>{{ metricsData.user_metrics.activation_rate }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" 
                                         :style="`width: ${metricsData.user_metrics.activation_rate}%`">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Active vs Total</span>
                                    <span>{{ Math.round((metricsData.user_metrics.active_users / metricsData.user_metrics.total_users) * 100) }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" 
                                         :style="`width: ${Math.round((metricsData.user_metrics.active_users / metricsData.user_metrics.total_users) * 100)}%`">
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Growth: <span :class="getGrowthClass(metricsData.user_metrics.growth_rate)" class="font-weight-bold">{{ metricsData.user_metrics.growth_rate }}%</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Status -->
            <div class="row clearfix row-deck" v-if="metricsData && healthData">
                <div class="col-lg-8 col-md-8 col-sm-12">
                    <div class="card">
                        <div class="header">
                            <h2>System Status Overview</h2>
                        </div>
                        <div class="body">
                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <h6>Component Health</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr v-for="(check, name) in healthData.checks" :key="name">
                                                    <td>
                                                        <i :class="getHealthIcon(name)" class="mr-2"></i>
                                                        {{ capitalizeFirst(name) }}
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-sm" :class="getHealthBadgeClass(check.status)">
                                                            {{ capitalizeFirst(check.status) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <small class="text-muted">
                                                            <span v-if="check.response_time">{{ check.response_time }}ms</span>
                                                            <span v-else-if="check.usage_percent">{{ check.usage_percent }}%</span>
                                                            <span v-else>OK</span>
                                                        </small>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <h6>User Activity Summary</h6>
                                    <div class="user-roles-breakdown">
                                        <div v-for="(count, role) in metricsData.user_metrics.users_by_role" :key="role" 
                                             class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-capitalize">{{ role }}</span>
                                            <span class="badge badge-primary badge-pill">{{ count }}</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <h5 class="text-primary">{{ metricsData.user_metrics.recent_users }}</h5>
                                        <small class="text-muted">New users this week</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card">
                        <div class="header">
                            <h2>Quick Actions</h2>
                        </div>
                        <div class="body">
                            <div class="btn-group-vertical w-100" role="group">
                                <button @click="refreshMetrics" class="btn btn-outline-primary mb-2" :disabled="loading">
                                    <i class="fa fa-refresh mr-2" :class="{ 'fa-spin': loading }"></i>
                                    Refresh Dashboard
                                </button>
                                <button class="btn btn-outline-success mb-2">
                                    <i class="fa fa-users mr-2"></i>
                                    Manage Users
                                </button>
                                <button class="btn btn-outline-info mb-2">
                                    <i class="fa fa-cogs mr-2"></i>
                                    System Settings
                                </button>
                                <button class="btn btn-outline-warning mb-2">
                                    <i class="fa fa-shield-alt mr-2"></i>
                                    Security Center
                                </button>
                                <button class="btn btn-outline-secondary">
                                    <i class="fa fa-chart-bar mr-2"></i>
                                    View Reports
                                </button>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">
                                    System uptime: <br>
                                    <strong class="text-success">{{ metricsData.system_metrics.uptime || '99.9%' }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>
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
const lastUpdated = ref(null)

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
        
        lastUpdated.value = new Date()
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

// Helper functions
const formatTime = (date) => {
    if (!date) return ''
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }).format(date)
}

const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M'
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K'
    }
    return num.toString()
}

const getHealthBadgeClass = (status) => {
    const classes = {
        healthy: 'badge-success',
        warning: 'badge-warning', 
        critical: 'badge-danger'
    }
    return classes[status] || 'badge-secondary'
}

const getHealthCardClass = (status) => {
    const classes = {
        healthy: 'status-healthy',
        warning: 'status-warning',
        critical: 'status-critical',
        unhealthy: 'status-critical'
    }
    return classes[status] || 'status-unknown'
}

const getHealthIndicatorClass = (status) => {
    const classes = {
        healthy: 'indicator-healthy',
        warning: 'indicator-warning',
        critical: 'indicator-critical',
        unhealthy: 'indicator-critical'
    }
    return classes[status] || 'indicator-unknown'
}

const getProgressBarClass = (percentage) => {
    if (percentage >= 90) return 'progress-danger'
    if (percentage >= 70) return 'progress-warning'
    return 'progress-success'
}

const getGrowthClass = (rate) => {
    if (rate > 0) return 'growth-positive'
    if (rate < 0) return 'growth-negative'
    return 'growth-neutral'
}

const getGrowthIcon = (rate) => {
    if (rate > 0) return 'fa-arrow-up'
    if (rate < 0) return 'fa-arrow-down'
    return 'fa-minus'
}

const getActivityPercentage = (count, total) => {
    return total > 0 ? Math.round((count / total) * 100) : 0
}

const getTablePercentage = (count, total) => {
    return total > 0 ? Math.round((count / total) * 100) : 0
}

const getHealthTextColor = (status) => {
    const classes = {
        healthy: 'text-success',
        warning: 'text-warning',
        critical: 'text-danger',
        unhealthy: 'text-danger'
    }
    return classes[status] || 'text-muted'
}

const getHealthDotClass = (status) => {
    const classes = {
        healthy: 'bg-success',
        warning: 'bg-warning',
        critical: 'bg-danger',
        unhealthy: 'bg-danger'
    }
    return `health-dot ${classes[status] || 'bg-secondary'}`
}

const getGrowthColor = (rate) => {
    if (rate > 0) return 'color: #28a745'
    if (rate < 0) return 'color: #dc3545'
    return 'color: #6c757d'
}

const getGrowthBadgeClass = (rate) => {
    if (rate > 0) return 'badge-success'
    if (rate < 0) return 'badge-danger'
    return 'badge-secondary'
}

onMounted(() => {
    fetchMetrics()
    
    // Auto-refresh every 5 minutes
    setInterval(fetchMetrics, 300000)
})
</script>

<style scoped>
/* Dashboard Custom Styles */
.sparkline-container {
    padding: 10px 0;
}

.health-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin: 0 2px;
    transition: transform 0.2s ease;
}

.health-dot:hover {
    transform: scale(1.2);
}

.activity-types {
    display: flex;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    background: #e9ecef;
}

.activity-type-bar {
    height: 100%;
    background: linear-gradient(90deg, #007bff, #0056b3);
    transition: all 0.3s ease;
}

.activity-type-bar:not(:last-child) {
    border-right: 1px solid rgba(255,255,255,0.3);
}

.activity-item-row {
    margin-bottom: 15px;
}

.activity-item-row:last-child {
    margin-bottom: 0;
}

/* Card hover effects */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 25px rgba(0,0,0,0.1);
}

/* Number chart specific styles */
.number-chart .body {
    position: relative;
    padding-bottom: 40px;
}

.number-chart .sparkline-container {
    position: absolute;
    bottom: 15px;
    left: 15px;
    right: 15px;
}

/* Progress animations */
.progress-bar {
    transition: width 0.6s ease;
}

/* Badge enhancements */
.badge {
    font-size: 0.75em;
    font-weight: 500;
}

.badge-pill {
    padding-right: 0.6em;
    padding-left: 0.6em;
    border-radius: 10rem;
}

/* Table enhancements */
.table-sm td {
    padding: 0.5rem;
    vertical-align: middle;
}

/* Loading spinner */
.spinner-border {
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-flex.flex-row-reverse {
        flex-direction: column !important;
    }
    
    .page_action {
        margin-top: 15px;
    }
    
    .page_action .btn {
        display: block;
        width: 100%;
        margin-bottom: 10px;
    }
}

/* Activity chart improvements */
.activity-chart .progress {
    margin-bottom: 10px;
}

/* System health improvements */
.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

/* Header dropdown improvements */
.header-dropdown {
    list-style: none;
    margin: 0;
    padding: 0;
}

.header-dropdown .dropdown-toggle::after {
    display: none;
}

/* Custom animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 40px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.card {
    animation: fadeInUp 0.5s ease-out;
}

/* Improved spacing */
.row-deck {
    margin-bottom: 30px;
}

.row-deck:last-child {
    margin-bottom: 0;
}

/* Additional enhancements */
.user-roles-breakdown {
    max-height: 200px;
    overflow-y: auto;
}

.btn-group-vertical .btn {
    border-radius: 4px !important;
    text-align: left;
}

.performance-metric {
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 10px;
}

/* System status table */
.table-sm {
    font-size: 0.875rem;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

/* Quick actions styling */
.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover,
.btn-outline-secondary:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* Compact card padding */
.card .body {
    padding: 1rem;
}

.card .header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

/* Performance metrics improvements */
.performance-metrics-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

@media (max-width: 768px) {
    .performance-metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>
