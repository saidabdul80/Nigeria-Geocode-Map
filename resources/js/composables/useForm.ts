/**
 * Simplified form composable with validation and error handling
 * Provides type-safe form management for Vue components
 */

import { ref, reactive, computed } from 'vue';

export interface ValidationRule<T = any> {
    validate: (value: T) => boolean | string;
    message?: string;
}

export interface FormConfig<T extends { [key: string]: any }> {
    initialData: T;
    validationRules?: { [K in keyof T]?: ValidationRule[] };
    onSubmit?: (data: T) => Promise<void> | void;
}

/**
 * Common validation rules that can be reused across forms
 */
export const validationRules = {
    required: (message = 'This field is required'): ValidationRule => ({
        validate: (value) => {
            if (value === null || value === undefined) return false;
            if (typeof value === 'string') return value.trim().length > 0;
            if (Array.isArray(value)) return value.length > 0;
            return true;
        },
        message
    }),

    email: (message = 'Please enter a valid email address'): ValidationRule<string> => ({
        validate: (value) => {
            if (!value) return true; // Let required rule handle empty values
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(value);
        },
        message
    }),

    minLength: (length: number, message?: string): ValidationRule<string> => ({
        validate: (value) => {
            if (!value) return true; // Let required rule handle empty values
            return value.length >= length;
        },
        message: message || `Must be at least ${length} characters`
    }),

    maxLength: (length: number, message?: string): ValidationRule<string> => ({
        validate: (value) => {
            if (!value) return true;
            return value.length <= length;
        },
        message: message || `Must be no more than ${length} characters`
    }),

    numeric: (message = 'Must be a valid number'): ValidationRule<string | number> => ({
        validate: (value) => {
            if (!value) return true;
            return !isNaN(Number(value));
        },
        message
    }),

    positive: (message = 'Must be a positive number'): ValidationRule<string | number> => ({
        validate: (value) => {
            if (!value) return true;
            const num = Number(value);
            return !isNaN(num) && num > 0;
        },
        message
    })
};

/**
 * Form composable for managing form state, validation, and submission
 */
export function useForm<T extends { [key: string]: any }>(config: FormConfig<T>) {
    const formData = reactive({ ...config.initialData }) as T;
    const errors = reactive<{ [key: string]: string }>({});
    const touched = reactive<{ [key: string]: boolean }>({});
    
    const isSubmitting = ref(false);
    const isValid = computed(() => Object.keys(errors).length === 0);

    /**
     * Validate a single field
     */
    const validateField = (field: keyof T): boolean => {
        const rules = config.validationRules?.[field];
        if (!rules) return true;

        const value = formData[field];
        
        for (const rule of rules) {
            const result = rule.validate(value);
            if (result === false) {
                errors[field as string] = rule.message || 'Invalid value';
                return false;
            }
            if (typeof result === 'string') {
                errors[field as string] = result;
                return false;
            }
        }

        delete errors[field as string];
        return true;
    };

    /**
     * Validate all fields
     */
    const validateAll = (): boolean => {
        let isFormValid = true;
        
        if (config.validationRules) {
            for (const field in config.validationRules) {
                if (!validateField(field)) {
                    isFormValid = false;
                }
            }
        }
        
        return isFormValid;
    };

    /**
     * Set field value and validate
     */
    const setField = (field: keyof T, value: any): void => {
        formData[field] = value;
        touched[field as string] = true;
        
        if (config.validationRules?.[field]) {
            validateField(field);
        }
    };

    /**
     * Handle form submission
     */
    const submit = async (): Promise<void> => {
        if (isSubmitting.value) return;

        // Mark all fields as touched
        for (const field in formData) {
            touched[field] = true;
        }

        if (!validateAll()) {
            return;
        }

        isSubmitting.value = true;
        
        try {
            await config.onSubmit?.(formData);
        } catch (error) {
            handleSubmissionError(error);
        } finally {
            isSubmitting.value = false;
        }
    };

    /**
     * Handle API errors and set field errors
     */
    const handleSubmissionError = (error: unknown): void => {
        if (error && typeof error === 'object' && 'data' in error) {
            const apiError = error as any;
            if (apiError.data?.errors) {
                // Handle Laravel validation errors
                for (const [field, messages] of Object.entries(apiError.data.errors)) {
                    if (field in formData && Array.isArray(messages)) {
                        errors[field] = messages[0] as string;
                    }
                }
            }
        }
    };

    /**
     * Reset form to initial state
     */
    const reset = (): void => {
        Object.assign(formData, config.initialData);
        Object.keys(errors).forEach(key => delete errors[key]);
        Object.keys(touched).forEach(key => delete touched[key]);
        isSubmitting.value = false;
    };

    /**
     * Clear all errors
     */
    const clearErrors = (): void => {
        Object.keys(errors).forEach(key => delete errors[key]);
    };

    /**
     * Set specific error for a field
     */
    const setError = (field: keyof T, message: string): void => {
        errors[field as string] = message;
    };

    return {
        // Data
        data: formData,
        errors,
        touched,
        isSubmitting,
        isValid,

        // Methods
        setField,
        submit,
        reset,
        clearErrors,
        setError,
        validateField,
        validateAll
    };
}