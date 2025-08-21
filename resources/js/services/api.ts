/**
 * Reusable API service layer for Nigeria Geocode Map
 * Provides centralized HTTP client with error handling, type safety, and caching
 */

import axios, { type AxiosInstance, type AxiosRequestConfig, type AxiosResponse } from 'axios';
import type { ApiResponse, PaginatedResponse, Lga, Ward, State, Record as RecordType } from '@/types/entities';

/**
 * Configuration options for the API service
 */
export interface ApiServiceConfig {
    baseURL?: string;
    timeout?: number;
    headers?: { [key: string]: string };
    enableCaching?: boolean;
    cacheTimeout?: number;
}

/**
 * Generic API error class for better error handling
 */
export class ApiError extends Error {
    public status: number;
    public data?: any;

    constructor(message: string, status: number, data?: any) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.data = data;
    }
}

/**
 * Simple in-memory cache for API responses
 */
class ApiCache {
    private cache = new Map<string, { data: any; timestamp: number }>();
    private timeout: number;

    constructor(timeout = 5 * 60 * 1000) { // 5 minutes default
        this.timeout = timeout;
    }

    get<T>(key: string): T | null {
        const cached = this.cache.get(key);
        if (!cached) return null;

        if (Date.now() - cached.timestamp > this.timeout) {
            this.cache.delete(key);
            return null;
        }

        return cached.data;
    }

    set<T>(key: string, data: T): void {
        this.cache.set(key, { data, timestamp: Date.now() });
    }

    clear(): void {
        this.cache.clear();
    }
}

/**
 * Main API service class providing reusable HTTP methods
 */
export class ApiService {
    private client: AxiosInstance;
    private cache: ApiCache;

    constructor(config: ApiServiceConfig = {}) {
        this.client = axios.create({
            baseURL: config.baseURL || '',
            timeout: config.timeout || 10000,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...config.headers,
            },
        });

        this.cache = new ApiCache(config.cacheTimeout);
        this.setupInterceptors();
    }

    private setupInterceptors(): void {
        // Request interceptor for common headers
        this.client.interceptors.request.use(
            (config) => {
                // Add CSRF token if available
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) {
                    config.headers['X-CSRF-TOKEN'] = token;
                }
                return config;
            },
            (error) => Promise.reject(error)
        );

        // Response interceptor for error handling
        this.client.interceptors.response.use(
            (response: AxiosResponse) => response,
            (error) => {
                const message = error.response?.data?.message || error.message || 'An error occurred';
                const status = error.response?.status || 500;
                const data = error.response?.data;
                
                throw new ApiError(message, status, data);
            }
        );
    }

    /**
     * Generic GET request with caching support
     */
    async get<T>(url: string, config?: AxiosRequestConfig & { cache?: boolean }): Promise<T> {
        const cacheKey = `${url}${JSON.stringify(config?.params || {})}`;
        
        if (config?.cache !== false) {
            const cached = this.cache.get<T>(cacheKey);
            if (cached) return cached;
        }

        const response = await this.client.get<T>(url, config);
        
        if (config?.cache !== false) {
            this.cache.set(cacheKey, response.data);
        }
        
        return response.data;
    }

    /**
     * Generic POST request
     */
    async post<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
        const response = await this.client.post<T>(url, data, config);
        return response.data;
    }

    /**
     * Generic PUT request
     */
    async put<T>(url: string, data?: any, config?: AxiosRequestConfig): Promise<T> {
        const response = await this.client.put<T>(url, data, config);
        return response.data;
    }

    /**
     * Generic DELETE request
     */
    async delete<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
        const response = await this.client.delete<T>(url, config);
        return response.data;
    }

    /**
     * Clear cache
     */
    clearCache(): void {
        this.cache.clear();
    }
}

/**
 * Location API service for Nigeria geocoding operations
 */
export class LocationApiService extends ApiService {
    
    /**
     * Get all states
     */
    async getStates(): Promise<State[]> {
        return this.get<State[]>('/api/states', { cache: true });
    }

    /**
     * Get LGAs for a specific state
     */
    async getLgasByState(stateId: number): Promise<Lga[]> {
        return this.get<Lga[]>(`/api/states/${stateId}/lgas`, { cache: true });
    }

    /**
     * Get wards for a specific LGA
     */
    async getWardsByLga(lgaId: number): Promise<Ward[]> {
        return this.get<Ward[]>(`/api/lgas/${lgaId}/wards`, { cache: true });
    }

    /**
     * Search locations by name
     */
    async searchLocations(query: string, type?: 'state' | 'lga' | 'ward'): Promise<{
        states: State[];
        lgas: Lga[];
        wards: Ward[];
    }> {
        return this.get('/api/locations/search', {
            params: { query, type },
            cache: false
        });
    }
}

/**
 * Records API service
 */
export class RecordsApiService extends ApiService {
    
    /**
     * Get paginated records
     */
    async getRecords(page = 1, perPage = 15, filters: { [key: string]: any } = {}): Promise<PaginatedResponse<RecordType>> {
        return this.get('/api/records', {
            params: { page, per_page: perPage, ...filters },
            cache: false
        });
    }

    /**
     * Create a new record
     */
    async createRecord(data: RecordType): Promise<ApiResponse<RecordType>> {
        return this.post('/api/records', data);
    }

    /**
     * Update an existing record
     */
    async updateRecord(id: number, data: Partial<RecordType>): Promise<ApiResponse<RecordType>> {
        return this.put(`/api/records/${id}`, data);
    }

    /**
     * Delete a record
     */
    async deleteRecord(id: number): Promise<ApiResponse> {
        return this.delete(`/api/records/${id}`);
    }
}

// Export pre-configured instances for easy use
export const locationApi = new LocationApiService();
export const recordsApi = new RecordsApiService();
export const api = new ApiService();