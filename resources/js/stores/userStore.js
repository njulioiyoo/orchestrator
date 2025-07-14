import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUserStore = defineStore('user', () => {
    // State
    const userTableRefreshTrigger = ref(0)
    const lastUserAction = ref(null)
    const isProcessing = ref(false)

    // Actions
    const triggerUserTableRefresh = () => {
        userTableRefreshTrigger.value++
    }

    const setUserAction = (action, data = null) => {
        lastUserAction.value = {
            type: action, // 'created', 'updated', 'deleted'
            data: data,
            timestamp: Date.now()
        }
    }

    const setProcessing = (processing) => {
        isProcessing.value = processing
    }

    const userCreated = (userData = null) => {
        setUserAction('created', userData)
        triggerUserTableRefresh()
    }

    const userUpdated = (userData = null) => {
        setUserAction('updated', userData)
        triggerUserTableRefresh()
    }

    const userDeleted = (userId = null) => {
        setUserAction('deleted', { id: userId })
        triggerUserTableRefresh()
    }

    const clearLastAction = () => {
        lastUserAction.value = null
    }

    // Getters
    const getRefreshTrigger = () => userTableRefreshTrigger.value
    const getLastAction = () => lastUserAction.value
    const getProcessingState = () => isProcessing.value

    return {
        // State
        userTableRefreshTrigger,
        lastUserAction,
        isProcessing,
        
        // Actions
        triggerUserTableRefresh,
        setUserAction,
        setProcessing,
        userCreated,
        userUpdated,
        userDeleted,
        clearLastAction,
        
        // Getters
        getRefreshTrigger,
        getLastAction,
        getProcessingState
    }
})