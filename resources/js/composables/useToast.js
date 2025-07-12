/**
 * Toast notification composable using toastr
 * Provides consistent toast notifications across the application
 */

export function useToast() {
    /**
     * Show success toast notification
     * @param {string} message - The message to display
     * @param {string} title - Optional title for the toast
     */
    const success = (message, title = 'Sukses') => {
        if (typeof toastr !== 'undefined') {
            toastr.success(message, title)
        } else {
            console.log(`SUCCESS: ${title} - ${message}`)
        }
    }

    /**
     * Show error toast notification
     * @param {string} message - The message to display
     * @param {string} title - Optional title for the toast
     */
    const error = (message, title = 'Error') => {
        if (typeof toastr !== 'undefined') {
            toastr.error(message, title)
        } else {
            console.error(`ERROR: ${title} - ${message}`)
        }
    }

    /**
     * Show warning toast notification
     * @param {string} message - The message to display
     * @param {string} title - Optional title for the toast
     */
    const warning = (message, title = 'Peringatan') => {
        if (typeof toastr !== 'undefined') {
            toastr.warning(message, title)
        } else {
            console.warn(`WARNING: ${title} - ${message}`)
        }
    }

    /**
     * Show info toast notification
     * @param {string} message - The message to display
     * @param {string} title - Optional title for the toast
     */
    const info = (message, title = 'Info') => {
        if (typeof toastr !== 'undefined') {
            toastr.info(message, title)
        } else {
            console.info(`INFO: ${title} - ${message}`)
        }
    }

    /**
     * Clear all toast notifications
     */
    const clear = () => {
        if (typeof toastr !== 'undefined') {
            toastr.clear()
        }
    }

    /**
     * Show loading toast notification
     * @param {string} message - The loading message
     */
    const loading = (message = 'Memproses...') => {
        if (typeof toastr !== 'undefined') {
            toastr.info(message, 'Loading', {
                timeOut: 0,
                extendedTimeOut: 0,
                tapToDismiss: false,
                closeButton: false
            })
        } else {
            console.log(`LOADING: ${message}`)
        }
    }

    return {
        success,
        error,
        warning,
        info,
        clear,
        loading
    }
}