<template>
    <!-- Top navbar div start -->
    <nav class="navbar navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-brand">
                <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-bars"></i></button>
                <button type="button" class="btn-toggle-fullwidth"><i class="fa fa-bars"></i></button>
            </div>

            <div class="navbar-right">
                <form id="navbar-search" class="navbar-form search-form" @submit.prevent="handleSearch">
                    <input 
                        v-model="searchQuery"
                        class="form-control" 
                        placeholder="Search here..." 
                        type="text"
                        @keyup.enter="handleSearch"
                        @input="handleSearchInput"
                    >
                    <button type="button" class="btn btn-default" @click="handleSearch">
                        <i class="icon-magnifier"></i>
                    </button>
                </form>

                <div id="navbar-menu">
                    <ul class="nav navbar-nav">
                        <li class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle icon-menu" data-toggle="dropdown">
                                <i class="fa fa-bell"></i>
                                <span class="notification-dot"></span>
                            </a>
                            <ul class="dropdown-menu notifications">
                                <li class="header"><strong>You have 4 new Notifications</strong></li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="media">
                                            <div class="media-left">
                                                <i class="icon-info text-warning"></i>
                                            </div>
                                            <div class="media-body">
                                                <p class="text">Campaign <strong>Holiday Sale</strong> is nearly reach
                                                    budget limit.</p>
                                                <span class="timestamp">10:00 AM Today</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="media">
                                            <div class="media-left">
                                                <i class="icon-like text-success"></i>
                                            </div>
                                            <div class="media-body">
                                                <p class="text">Your New Campaign <strong>Holiday Sale</strong> is
                                                    approved.</p>
                                                <span class="timestamp">11:30 AM Today</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="media">
                                            <div class="media-left">
                                                <i class="icon-pie-chart text-info"></i>
                                            </div>
                                            <div class="media-body">
                                                <p class="text">Website visits from Twitter is 27% higher than last
                                                    week.</p>
                                                <span class="timestamp">04:00 PM Today</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="media">
                                            <div class="media-left">
                                                <i class="icon-info text-danger"></i>
                                            </div>
                                            <div class="media-body">
                                                <p class="text">Error on website analytics configurations</p>
                                                <span class="timestamp">Yesterday</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="footer"><a href="javascript:void(0);" class="more">See all notifications</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="icon-menu" @click="logout"><i
                                    class="fa fa-power-off"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const searchQuery = ref('')

const emit = defineEmits(['search'])

const logout = () => {
    router.post('/logout', {}, {
        onFinish: () => {
            // Force complete page reload after logout to clear all state
            setTimeout(() => {
                window.location.href = '/login'
            }, 100)
        }
    })
}

const handleSearch = () => {
    if (searchQuery.value.trim().length >= 2) {
        emit('search', searchQuery.value.trim())
    }
}

const handleSearchInput = () => {
    // Debounced search - trigger search after user stops typing for 500ms
    clearTimeout(window.searchTimeout)
    window.searchTimeout = setTimeout(() => {
        if (searchQuery.value.trim().length >= 2) {
            emit('search', searchQuery.value.trim())
        }
    }, 500)
}
</script>