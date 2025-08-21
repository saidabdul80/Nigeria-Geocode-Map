/**
 * Error handling utilities and composables
 * Provides standardized error handling across the application
 */

import { ref } from 'vue';

/**
 * Error severity levels
 */
export type ErrorSeverity = 'error' | 'warn' | 'info' | 'success';

/**
 * Structured error interface
 */
export interface AppError {
    id: string;
    message: string;
    severity: ErrorSeverity;
    code?: string | number;
    details?: any;
    timestamp: Date;
    dismissed?: boolean;
}

/**
 * Toast notification interface (compatible with PrimeVue)
 */
export interface ToastMessage {
    severity: 'success' | 'info' | 'warn' | 'error';
    summary: string;
    detail?: string;
    life?: number;
    closable?: boolean;
}

/**
 * Error handling composable
 */
export function useErrorHandler() {
    const errors = ref<AppError[]>([]);
    const isHandling = ref(false);

    /**
     * Generate unique error ID
     */
    const generateErrorId = (): string => {
        return `error_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    };

    /**
     * Create structured error from various input types
     */
    const createError = (
        error: unknown,
        severity: ErrorSeverity = 'error',
        context?: string
    ): AppError => {
        let message = 'An unknown error occurred';
        let code: string | number | undefined;
        let details: any;

        if (error && typeof error === 'object' && 'message' in error) {
            const err = error as any;
            message = err.message || message;
            if ('status' in err) code = err.status;
            if ('data' in err) details = err.data;
        } else if (error instanceof Error) {
            message = error.message;
            code = error.name;
        } else if (typeof error === 'string') {
            message = error;
        }

        if (context) {
            message = `${context}: ${message}`;
        }

        return {
            id: generateErrorId(),
            message,
            severity,
            code,
            details,
            timestamp: new Date(),
            dismissed: false
        };
    };

    /**
     * Handle and log error
     */
    const handleError = (
        error: unknown,
        severity: ErrorSeverity = 'error',
        context?: string
    ): AppError => {
        isHandling.value = true;
        
        const appError = createError(error, severity, context);
        
        // Add to errors array
        errors.value.unshift(appError);
        
        // Keep only last 50 errors
        if (errors.value.length > 50) {
            errors.value = errors.value.slice(0, 50);
        }

        // Log to console
        console[severity === 'error' ? 'error' : severity === 'warn' ? 'warn' : 'log'](
            `[${severity.toUpperCase()}] ${appError.message}`,
            appError.details
        );

        isHandling.value = false;
        return appError;
    };

    /**
     * Convert error to toast message
     */
    const errorToToast = (error: AppError): ToastMessage => {
        return {
            severity: error.severity === 'error' ? 'error' : 
                     error.severity === 'warn' ? 'warn' : 
                     error.severity === 'success' ? 'success' : 'info',
            summary: error.severity === 'error' ? 'Error' :
                    error.severity === 'warn' ? 'Warning' :
                    error.severity === 'success' ? 'Success' : 'Information',
            detail: error.message,
            life: error.severity === 'error' ? 5000 : 3000,
            closable: true
        };
    };

    /**
     * Clear specific error
     */
    const clearError = (errorId: string): void => {
        const index = errors.value.findIndex(err => err.id === errorId);
        if (index >= 0) {
            errors.value.splice(index, 1);
        }
    };

    /**
     * Clear all errors
     */
    const clearAllErrors = (): void => {
        errors.value = [];
    };

    /**
     * Dismiss error (mark as dismissed but keep in history)
     */
    const dismissError = (errorId: string): void => {
        const error = errors.value.find(err => err.id === errorId);
        if (error) {
            error.dismissed = true;
        }
    };

    /**
     * Get undismissed errors
     */
    const activeErrors = (): AppError[] => {
        return errors.value.filter(err => !err.dismissed);
    };

    return {
        // State
        errors,
        isHandling,

        // Methods
        handleError,
        createError,
        errorToToast,
        clearError,
        clearAllErrors,
        dismissError,
        activeErrors
    };
}

/**
 * Toast notification composable (wrapper for toast libraries)
 */
export function useToast() {
    // This will work with PrimeVue's useToast or can be adapted for other libraries
    let toastService: any = null;

    // Try to get PrimeVue toast service if available
    try {
        // This would normally be imported from PrimeVue
        // import { useToast } from 'primevue/usetoast';
        // toastService = useToast();
    } catch (error) {
        // Fallback implementation
    }

    /**
     * Show toast notification
     */
    const show = (message: ToastMessage): void => {
        if (toastService && toastService.add) {
            toastService.add(message);
        } else {
            // Fallback: log to console
            console.log(`[TOAST ${message.severity.toUpperCase()}] ${message.summary}: ${message.detail}`);
        }
    };

    /**
     * Show success toast
     */
    const success = (summary: string, detail?: string): void => {
        show({
            severity: 'success',
            summary,
            detail,
            life: 3000
        });
    };

    /**
     * Show error toast
     */
    const error = (summary: string, detail?: string): void => {
        show({
            severity: 'error',
            summary,
            detail,
            life: 5000
        });
    };

    /**
     * Show warning toast
     */
    const warn = (summary: string, detail?: string): void => {
        show({
            severity: 'warn',
            summary,
            detail,
            life: 4000
        });
    };

    /**
     * Show info toast
     */
    const info = (summary: string, detail?: string): void => {
        show({
            severity: 'info',
            summary,
            detail,
            life: 3000
        });
    };

    /**
     * Show error from AppError
     */
    const showError = (appError: AppError): void => {
        const toast = useErrorHandler().errorToToast(appError);
        show(toast);
    };

    return {
        show,
        success,
        error,
        warn,
        info,
        showError
    };
}

/**
 * Global error boundary for async operations
 */
export async function withErrorHandling<T>(
    operation: () => Promise<T>,
    context?: string,
    options: {
        showToast?: boolean;
        severity?: ErrorSeverity;
        fallback?: T;
    } = {}
): Promise<T | null> {
    const { handleError } = useErrorHandler();
    const toast = useToast();

    try {
        return await operation();
    } catch (error) {
        const appError = handleError(error, options.severity, context);
        
        if (options.showToast) {
            toast.showError(appError);
        }

        return options.fallback || null;
    }
}

/**
 * Retry mechanism with exponential backoff
 */
export async function withRetry<T>(
    operation: () => Promise<T>,
    maxRetries = 3,
    baseDelay = 1000
): Promise<T> {
    let lastError: any;
    
    for (let attempt = 0; attempt <= maxRetries; attempt++) {
        try {
            return await operation();
        } catch (error) {
            lastError = error;
            
            if (attempt === maxRetries) {
                break;
            }
            
            // Exponential backoff with jitter
            const delay = baseDelay * Math.pow(2, attempt) + Math.random() * 100;
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
    
    throw lastError;
}