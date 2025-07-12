<template>
    <div class="container-fluid dashboard-container">
        <!-- Header Section -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-6 col-sm-12">
                    <div class="header-content">
                        <h1 class="dashboard-title">
                            <i class="fa fa-dashboard text-primary"></i>
                            System Dashboard
                        </h1>
                        <p class="dashboard-subtitle">Real-time monitoring & performance insights</p>
                        <Breadcrumb :items="breadcrumbItems" />
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="header-controls">
                        <div class="last-updated" v-if="lastUpdated">
                            <small class="text-muted">
                                <i class="fa fa-clock"></i>
                                Last updated: {{ formatTime(lastUpdated) }}
                            </small>
                        </div>
                        <button 
                            @click="refreshMetrics" 
                            class="btn btn-gradient-primary refresh-btn"
                            :disabled="loading"
                        >
                            <i class="fa fa-refresh" :class="{ 'fa-spin': loading }"></i>
                            {{ loading ? 'Refreshing...' : 'Refresh Data' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health Status -->
        <div class="health-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fa fa-heartbeat text-danger"></i>
                    System Health Monitor
                </h3>
                <div class="section-badge" v-if="healthData">
                    <span class="badge" :class="getHealthBadgeClass(healthData.status)">
                        {{ capitalizeFirst(healthData.status) }}
                    </span>
                </div>
            </div>
            
            <div class="health-grid" v-if="healthData">
                <!-- Overall Status Card -->
                <div class="health-card overall-status" :class="getHealthCardClass(healthData.status)">
                    <div class="card-header">
                        <div class="status-icon">
                            <i class="fa fa-heartbeat"></i>
                        </div>
                        <div class="status-pulse" :class="getHealthCardClass(healthData.status)"></div>
                    </div>
                    <div class="card-content">
                        <h4>System Status</h4>
                        <div class="status-text">{{ capitalizeFirst(healthData.status) }}</div>
                        <div class="status-timestamp">{{ formatTime(healthData.timestamp) }}</div>
                    </div>
                </div>

                <!-- Individual Health Checks -->
                <div 
                    v-for="(check, name) in healthData.checks" 
                    :key="name"
                    class="health-card" 
                    :class="getHealthCardClass(check.status)"
                >
                    <div class="card-header">
                        <div class="status-icon">
                            <i :class="getHealthIcon(name)"></i>
                        </div>
                        <div class="health-indicator" :class="getHealthIndicatorClass(check.status)"></div>
                    </div>
                    <div class="card-content">
                        <h5>{{ capitalizeFirst(name) }}</h5>
                        <div class="status-text">{{ capitalizeFirst(check.status) }}</div>
                        <div class="status-details">
                            <small>{{ check.message }}</small>
                            <!-- Additional details for specific checks -->
                            <div v-if="check.response_time" class="metric-detail">
                                <i class="fa fa-tachometer-alt"></i>
                                {{ check.response_time }}ms
                            </div>
                            <div v-if="check.usage_percent" class="metric-detail">
                                <i class="fa fa-chart-pie"></i>
                                {{ check.usage_percent }}% used
                                <div class="progress-mini">
                                    <div class="progress-bar" :style="`width: ${check.usage_percent}%`" :class="getProgressBarClass(check.usage_percent)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="health-loading">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Loading health data...</p>
                </div>
            </div>
        </div>

        <!-- Performance Metrics Section -->
        <div class="metrics-section" v-if="metricsData">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fa fa-chart-area text-primary"></i>
                    Performance Analytics
                </h3>
            </div>

            <div class="metrics-grid">
                <!-- User Metrics Card -->
                <div class="metric-card user-metrics">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <h4>User Analytics</h4>
                    </div>
                    <div class="metric-content">
                        <div class="metric-stats">
                            <div class="stat-item primary">
                                <div class="stat-number">{{ formatNumber(metricsData.user_metrics.total_users) }}</div>
                                <div class="stat-label">Total Users</div>
                                <div class="stat-icon"><i class="fa fa-users"></i></div>
                            </div>
                            <div class="stat-item success">
                                <div class="stat-number">{{ formatNumber(metricsData.user_metrics.active_users) }}</div>
                                <div class="stat-label">Active Users</div>
                                <div class="stat-icon"><i class="fa fa-user-check"></i></div>
                            </div>
                            <div class="stat-item info">
                                <div class="stat-number">{{ formatNumber(metricsData.user_metrics.recent_users) }}</div>
                                <div class="stat-label">New (7d)</div>
                                <div class="stat-icon"><i class="fa fa-user-plus"></i></div>
                            </div>
                            <div class="stat-item" :class="getGrowthClass(metricsData.user_metrics.growth_rate)">
                                <div class="stat-number">{{ metricsData.user_metrics.growth_rate }}%</div>
                                <div class="stat-label">Growth Rate</div>
                                <div class="stat-icon">
                                    <i class="fa" :class="getGrowthIcon(metricsData.user_metrics.growth_rate)"></i>
                                </div>
                            </div>
                        </div>
                        <!-- User activation rate -->
                        <div class="progress-section">
                            <div class="progress-label">
                                <span>Activation Rate</span>
                                <span class="progress-value">{{ metricsData.user_metrics.activation_rate }}%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" :style="`width: ${metricsData.user_metrics.activation_rate}%`"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Metrics Card -->
                <div class="metric-card system-metrics">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="fa fa-cogs"></i>
                        </div>
                        <h4>System Overview</h4>
                    </div>
                    <div class="metric-content">
                        <div class="system-stats">
                            <div class="system-item">
                                <div class="system-icon"><i class="fa fa-shield-alt"></i></div>
                                <div class="system-details">
                                    <div class="system-number">{{ metricsData.system_metrics.total_roles }}</div>
                                    <div class="system-label">Roles</div>
                                </div>
                            </div>
                            <div class="system-item">
                                <div class="system-icon"><i class="fa fa-key"></i></div>
                                <div class="system-details">
                                    <div class="system-number">{{ metricsData.system_metrics.total_permissions }}</div>
                                    <div class="system-label">Permissions</div>
                                </div>
                            </div>
                            <div class="system-item">
                                <div class="system-icon"><i class="fa fa-bars"></i></div>
                                <div class="system-details">
                                    <div class="system-number">{{ metricsData.system_metrics.total_menus }}</div>
                                    <div class="system-label">Menus</div>
                                </div>
                            </div>
                            <div class="system-item">
                                <div class="system-icon"><i class="fa fa-check-circle"></i></div>
                                <div class="system-details">
                                    <div class="system-number">{{ metricsData.system_metrics.active_menus }}</div>
                                    <div class="system-label">Active</div>
                                </div>
                            </div>
                        </div>
                        <div class="system-info">
                            <div class="info-item">
                                <i class="fa fa-code"></i>
                                <span>PHP {{ metricsData.system_metrics.php_version }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fa fa-laravel"></i>
                                <span>Laravel {{ metricsData.system_metrics.laravel_version }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Metrics Card -->
                <div class="metric-card activity-metrics">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="fa fa-activity"></i>
                        </div>
                        <h4>Activity Monitor</h4>
                    </div>
                    <div class="metric-content">
                        <div class="activity-summary">
                            <div class="activity-total">
                                <div class="total-number">{{ formatNumber(metricsData.activity_metrics.total_activities) }}</div>
                                <div class="total-label">Total Activities</div>
                            </div>
                            <div class="activity-recent">
                                <div class="recent-number">{{ formatNumber(metricsData.activity_metrics.recent_activities) }}</div>
                                <div class="recent-label">Last 7 Days</div>
                            </div>
                        </div>
                        <div class="activity-breakdown">
                            <h6>Activity Types</h6>
                            <div class="activity-list">
                                <div v-for="(count, event) in metricsData.activity_metrics.activities_by_type" :key="event" class="activity-item">
                                    <span class="activity-name">{{ capitalizeFirst(event) }}</span>
                                    <span class="activity-count">{{ count }}</span>
                                    <div class="activity-bar">
                                        <div class="activity-progress" :style="`width: ${getActivityPercentage(count, metricsData.activity_metrics.total_activities)}%`"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Metrics Card -->
                <div class="metric-card database-metrics">
                    <div class="metric-header">
                        <div class="metric-icon">
                            <i class="fa fa-database"></i>
                        </div>
                        <h4>Database Status</h4>
                    </div>
                    <div class="metric-content">
                        <div class="database-summary">
                            <div class="db-total">
                                <div class="db-number">{{ formatNumber(metricsData.database_metrics.total_records) }}</div>
                                <div class="db-label">Total Records</div>
                            </div>
                            <div class="db-size">
                                <div class="size-number">{{ metricsData.database_metrics.database_size }}</div>
                                <div class="size-label">Database Size</div>
                            </div>
                        </div>
                        <div class="table-breakdown">
                            <h6>Records by Table</h6>
                            <div class="table-list">
                                <div v-for="(count, table) in metricsData.database_metrics.table_counts" :key="table" class="table-item">
                                    <div class="table-info">
                                        <span class="table-name">{{ capitalizeFirst(table) }}</span>
                                        <span class="table-count">{{ formatNumber(count) }}</span>
                                    </div>
                                    <div class="table-progress">
                                        <div class="table-bar" :style="`width: ${getTablePercentage(count, metricsData.database_metrics.total_records)}%`"></div>
                                    </div>
                                </div>
                            </div>
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

onMounted(() => {
    fetchMetrics()
    
    // Auto-refresh every 5 minutes
    setInterval(fetchMetrics, 300000)
})
</script>

<style scoped>
/* Dashboard Container */
.dashboard-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding: 20px 0;
}

/* Header Styles */
.dashboard-header {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.2);
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.dashboard-subtitle {
    color: #7f8c8d;
    font-size: 1.1rem;
    margin-bottom: 15px;
}

.header-controls {
    text-align: right;
}

.last-updated {
    margin-bottom: 15px;
}

.refresh-btn {
    background: linear-gradient(45deg, #3498db, #2980b9);
    border: none;
    padding: 12px 25px;
    border-radius: 50px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.refresh-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
}

.refresh-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Section Headers */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #2c3e50;
}

.section-badge .badge {
    padding: 8px 15px;
    font-size: 0.9rem;
    border-radius: 20px;
}

/* Health Section */
.health-section {
    margin-bottom: 40px;
}

.health-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.health-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.health-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.health-card.overall-status {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.health-card.status-healthy {
    border-left: 5px solid #27ae60;
}

.health-card.status-warning {
    border-left: 5px solid #f39c12;
}

.health-card.status-critical {
    border-left: 5px solid #e74c3c;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.status-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
    font-size: 1.5rem;
}

.overall-status .status-icon {
    background: rgba(255,255,255,0.2);
    color: white;
}

.status-pulse {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.status-pulse.status-healthy {
    background: #27ae60;
}

.status-pulse.status-warning {
    background: #f39c12;
}

.status-pulse.status-critical {
    background: #e74c3c;
}

@keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.1); }
    100% { opacity: 1; transform: scale(1); }
}

.health-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.indicator-healthy { background: #27ae60; }
.indicator-warning { background: #f39c12; }
.indicator-critical { background: #e74c3c; }

.card-content h4, .card-content h5 {
    margin-bottom: 10px;
    font-weight: 600;
}

.status-text {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.status-timestamp, .status-details {
    font-size: 0.9rem;
    opacity: 0.8;
}

.metric-detail {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 8px;
}

.progress-mini {
    width: 100%;
    height: 4px;
    background: rgba(0,0,0,0.1);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 5px;
}

.progress-mini .progress-bar {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease;
}

.progress-success { background: #27ae60; }
.progress-warning { background: #f39c12; }
.progress-danger { background: #e74c3c; }

/* Health Loading */
.health-loading {
    text-align: center;
    padding: 50px;
}

.loading-spinner .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Metrics Section */
.metrics-section {
    margin-bottom: 40px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.metric-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2980b9);
}

.metric-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ecf0f1;
}

.metric-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(45deg, #3498db, #2980b9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.metric-header h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
}

/* User Metrics */
.metric-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.stat-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-2px);
}

.stat-item.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.stat-item.success { background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); color: white; }
.stat-item.info { background: linear-gradient(135deg, #3498db 0%, #85c1e9 100%); color: white; }
.stat-item.growth-positive { background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); color: white; }
.stat-item.growth-negative { background: linear-gradient(135deg, #e74c3c 0%, #ffb3ba 100%); color: white; }
.stat-item.growth-neutral { background: linear-gradient(135deg, #95a5a6 0%, #d5dbdb 100%); color: white; }

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.stat-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0.3;
    font-size: 1.2rem;
}

.progress-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-weight: 600;
}

.progress-bar-container {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar-container .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2980b9);
    border-radius: 4px;
    transition: width 0.3s ease;
}

/* System Metrics */
.system-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.system-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.system-item:hover {
    background: #e9ecef;
}

.system-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.system-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

.system-label {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.system-info {
    display: flex;
    gap: 20px;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #7f8c8d;
    font-size: 0.9rem;
}

/* Activity Metrics */
.activity-summary {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}

.activity-total, .activity-recent {
    flex: 1;
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.total-number, .recent-number {
    font-size: 2rem;
    font-weight: 700;
    color: #3498db;
}

.total-label, .recent-label {
    font-size: 0.9rem;
    color: #7f8c8d;
    margin-top: 5px;
}

.activity-breakdown h6 {
    margin-bottom: 15px;
    color: #2c3e50;
}

.activity-list {
    space-y: 10px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
}

.activity-name {
    flex: 1;
    font-size: 0.9rem;
}

.activity-count {
    font-weight: 600;
    color: #3498db;
    min-width: 40px;
    text-align: right;
}

.activity-bar {
    flex: 1;
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.activity-progress {
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2980b9);
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Database Metrics */
.database-summary {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}

.db-total, .db-size {
    flex: 1;
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.db-number, .size-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #27ae60;
}

.db-label, .size-label {
    font-size: 0.9rem;
    color: #7f8c8d;
    margin-top: 5px;
}

.table-breakdown h6 {
    margin-bottom: 15px;
    color: #2c3e50;
}

.table-item {
    margin-bottom: 12px;
}

.table-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.table-name {
    font-size: 0.9rem;
    color: #2c3e50;
}

.table-count {
    font-weight: 600;
    color: #27ae60;
}

.table-progress {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
}

.table-bar {
    height: 100%;
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    border-radius: 2px;
    transition: width 0.3s ease;
}

/* Badge Styles */
.badge-success { background: #27ae60; }
.badge-warning { background: #f39c12; }
.badge-danger { background: #e74c3c; }
.badge-secondary { background: #95a5a6; }

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-title {
        font-size: 2rem;
    }
    
    .header-controls {
        text-align: left;
        margin-top: 20px;
    }
    
    .health-grid {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .metric-stats {
        grid-template-columns: 1fr;
    }
    
    .system-stats {
        grid-template-columns: 1fr;
    }
    
    .activity-summary, .database-summary {
        flex-direction: column;
    }
}

/* Loading animations */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter, .fade-leave-to {
    opacity: 0;
}
</style>
