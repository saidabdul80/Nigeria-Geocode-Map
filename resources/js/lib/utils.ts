/**
 * Enhanced utility functions for Nigeria Geocode Map
 * Provides reusable utilities with proper type safety and modern patterns
 */

import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Combine class names with Tailwind CSS conflict resolution
 */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Format number with Nigerian locale
 */
export function formatNumber(
    value: number, 
    options: Intl.NumberFormatOptions = {}
): string {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
        ...options
    }).format(value);
}

/**
 * Format currency in Nigerian Naira
 */
export function formatCurrency(amount: number): string {
    return formatNumber(amount, {
        style: 'currency',
        currency: 'NGN'
    });
}

/**
 * Format date with Nigerian locale preferences
 */
export function formatDate(
    date: Date | string | number,
    options: Intl.DateTimeFormatOptions = {}
): string {
    const dateObj = typeof date === 'string' || typeof date === 'number' ? new Date(date) : date;
    
    return new Intl.DateTimeFormat('en-NG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        ...options
    }).format(dateObj);
}

/**
 * Format date relative to now (e.g., "2 days ago")
 */
export function formatRelativeDate(date: Date | string | number): string {
    const dateObj = typeof date === 'string' || typeof date === 'number' ? new Date(date) : date;
    const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
    
    const diffInSeconds = (dateObj.getTime() - Date.now()) / 1000;
    
    if (Math.abs(diffInSeconds) < 60) return rtf.format(Math.round(diffInSeconds), 'second');
    if (Math.abs(diffInSeconds) < 3600) return rtf.format(Math.round(diffInSeconds / 60), 'minute');
    if (Math.abs(diffInSeconds) < 86400) return rtf.format(Math.round(diffInSeconds / 3600), 'hour');
    if (Math.abs(diffInSeconds) < 2592000) return rtf.format(Math.round(diffInSeconds / 86400), 'day');
    if (Math.abs(diffInSeconds) < 31536000) return rtf.format(Math.round(diffInSeconds / 2592000), 'month');
    
    return rtf.format(Math.round(diffInSeconds / 31536000), 'year');
}

/**
 * Debounce function execution
 */
export function debounce<T extends (...args: any[]) => any>(
    func: T,
    wait: number
): (...args: Parameters<T>) => void {
    let timeout: ReturnType<typeof setTimeout> | null = null;
    
    return (...args: Parameters<T>) => {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

/**
 * Throttle function execution
 */
export function throttle<T extends (...args: any[]) => any>(
    func: T,
    limit: number
): (...args: Parameters<T>) => void {
    let inThrottle: boolean;
    
    return (...args: Parameters<T>) => {
        if (!inThrottle) {
            func.apply(null, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Deep clone object
 */
export function deepClone<T>(obj: T): T {
    if (obj === null || typeof obj !== 'object') return obj;
    if (obj instanceof Date) return new Date(obj.getTime()) as unknown as T;
    if (obj instanceof Array) return obj.map(item => deepClone(item)) as unknown as T;
    
    const clonedObj = {} as { [key in keyof T]: T[key] };
    for (const key in obj) {
        clonedObj[key] = deepClone(obj[key]);
    }
    
    return clonedObj;
}

/**
 * Check if object is empty
 */
export function isEmpty(obj: any): boolean {
    if (obj == null) return true;
    if (Array.isArray(obj) || typeof obj === 'string') return obj.length === 0;
    if (obj instanceof Date) return false;
    if (typeof obj === 'object') return Object.keys(obj).length === 0;
    return false;
}

/**
 * Generate random ID
 */
export function generateId(prefix = 'id', length = 8): string {
    const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    let result = prefix + '_';
    
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    return result;
}

/**
 * Capitalize first letter of string
 */
export function capitalize(str: string): string {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

/**
 * Convert string to title case
 */
export function titleCase(str: string): string {
    if (!str) return str;
    
    return str
        .split(' ')
        .map(word => capitalize(word))
        .join(' ');
}

/**
 * Slugify string for URLs
 */
export function slugify(str: string): string {
    return str
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

/**
 * Truncate string with ellipsis
 */
export function truncate(str: string, length: number, suffix = '...'): string {
    if (str.length <= length) return str;
    return str.substring(0, length - suffix.length) + suffix;
}

/**
 * Parse coordinates from string
 */
export function parseCoordinates(coordString: string): { lat: number; lng: number } | null {
    if (!coordString) return null;
    
    const parts = coordString.split(',').map(s => s.trim());
    if (parts.length !== 2) return null;
    
    const lat = parseFloat(parts[0]);
    const lng = parseFloat(parts[1]);
    
    if (isNaN(lat) || isNaN(lng)) return null;
    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return null;
    
    return { lat, lng };
}

/**
 * Calculate distance between two coordinates (in kilometers)
 */
export function calculateDistance(
    lat1: number, 
    lng1: number, 
    lat2: number, 
    lng2: number
): number {
    const R = 6371; // Earth's radius in kilometers
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    
    const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    
    return R * c;
}

/**
 * Validate Nigerian phone number
 */
export function validateNigerianPhone(phone: string): boolean {
    // Remove all non-digit characters
    const cleaned = phone.replace(/\D/g, '');
    
    // Check for valid Nigerian phone number patterns
    return /^(234|0)?[789][01]\d{8}$/.test(cleaned);
}

/**
 * Format Nigerian phone number
 */
export function formatNigerianPhone(phone: string): string {
    const cleaned = phone.replace(/\D/g, '');
    
    if (cleaned.startsWith('234')) {
        // International format
        return `+${cleaned.slice(0, 3)} ${cleaned.slice(3, 6)} ${cleaned.slice(6, 10)} ${cleaned.slice(10)}`;
    } else if (cleaned.startsWith('0')) {
        // Local format
        return `${cleaned.slice(0, 4)} ${cleaned.slice(4, 7)} ${cleaned.slice(7)}`;
    }
    
    return phone; // Return original if format not recognized
}

/**
 * Local storage utilities with error handling
 */
export const storage = {
    get<T>(key: string, defaultValue?: T): T | null {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue || null;
        } catch (error) {
            console.warn(`Failed to get item from localStorage: ${key}`, error);
            return defaultValue || null;
        }
    },
    
    set<T>(key: string, value: T): boolean {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            console.warn(`Failed to set item in localStorage: ${key}`, error);
            return false;
        }
    },
    
    remove(key: string): boolean {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.warn(`Failed to remove item from localStorage: ${key}`, error);
            return false;
        }
    },
    
    clear(): boolean {
        try {
            localStorage.clear();
            return true;
        } catch (error) {
            console.warn('Failed to clear localStorage', error);
            return false;
        }
    }
};

/**
 * URL utilities
 */
export const url = {
    /**
     * Build URL with query parameters
     */
    build(base: string, params: Record<string, any> = {}): string {
        const url = new URL(base, window.location.origin);
        
        Object.entries(params).forEach(([key, value]) => {
            if (value != null && value !== '') {
                url.searchParams.append(key, String(value));
            }
        });
        
        return url.toString();
    },
    
    /**
     * Parse query parameters from URL
     */
    parseQuery(search: string = window.location.search): Record<string, string> {
        const params = new URLSearchParams(search);
        const result: Record<string, string> = {};
        
        params.forEach((value, key) => {
            result[key] = value;
        });
        
        return result;
    }
};

/**
 * Array utilities
 */
export const array = {
    /**
     * Remove duplicates from array
     */
    unique<T>(arr: T[]): T[] {
        return [...new Set(arr)];
    },
    
    /**
     * Chunk array into smaller arrays
     */
    chunk<T>(arr: T[], size: number): T[][] {
        const chunks: T[][] = [];
        for (let i = 0; i < arr.length; i += size) {
            chunks.push(arr.slice(i, i + size));
        }
        return chunks;
    },
    
    /**
     * Group array items by key
     */
    groupBy<T>(arr: T[], key: keyof T): Record<string, T[]> {
        return arr.reduce((groups, item) => {
            const group = String(item[key]);
            return {
                ...groups,
                [group]: [...(groups[group] || []), item]
            };
        }, {} as Record<string, T[]>);
    }
};
