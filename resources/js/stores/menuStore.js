import { defineStore } from 'pinia'
import axios from 'axios'

export const useMenuStore = defineStore('menu', {
    state: () => ({
        menus: [],
        isLoaded: false,
        isLoading: false,
        hasError: false
    }),

    getters: {
        rootMenus: (state) => {
                return state.menus.filter(menu => !menu.parent_id)
        },
        
        getMenuById: (state) => {
            return (id) => state.menus.find(menu => menu.id === id)
        },

        hasMenus: (state) => {
            return state.menus.length > 0
        },

        shouldShowFallback: (state) => {
            return state.isLoaded && (state.hasError || state.menus.length === 0)
        }
    },

    actions: {
        async fetchMenus() {
            if (this.isLoading) return

            this.isLoading = true
            this.hasError = false

            try {
                const response = await axios.get('/api/menus', { timeout: 10000 })
                this.menus = response.data || []
                this.isLoaded = true
            } catch (error) {
                console.error('Error fetching menus:', error)
                this.hasError = true
                this.isLoaded = true
            } finally {
                this.isLoading = false
            }
        },

        async initializeMenus() {
            await this.fetchMenus()
        },

        setTestMenus() {
            const testMenus = [
                {
                    id: 999,
                    name: 'test-menu',
                    label: 'Test Menu',
                    icon: 'fa-test',
                    url: '/test',
                    parent_id: null,
                    sort_order: 1,
                    is_active: true,
                    children: []
                }
            ]
            this.menus = testMenus
            this.isLoaded = true
            this.hasError = false
            console.log('🧪 Test menus set:', this.menus)
        },

        clearMenus() {
            this.menus = []
            this.isLoaded = false
            this.hasError = false
        },

        addMenu(menu) {
            this.menus.push(menu)
        },

        updateMenu(updatedMenu) {
            const index = this.menus.findIndex(menu => menu.id === updatedMenu.id)
            if (index !== -1) {
                this.menus[index] = updatedMenu
            }
        },

        removeMenu(menuId) {
            this.menus = this.menus.filter(menu => menu.id !== menuId)
        },

        setMenus(menus) {
            this.menus = menus || []
            this.isLoaded = true
            this.hasError = false
        }
    }
})