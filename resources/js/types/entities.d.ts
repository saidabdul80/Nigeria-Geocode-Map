/**
 * Core entity types for Nigeria Geocode Map
 * These types provide structure and type safety for the application
 */

export interface State {
    id: number;
    name: string;
    country_id: number;
    status: number;
    created_at: string | null;
    updated_at: string | null;
    lgas?: Lga[];
}

export interface Lga {
    id: number;
    state_id: number;
    name: string;
    longitude: string;
    latitude: string;
    created_at: string | null;
    updated_at: string | null;
    wards?: Ward[];
}

export interface Ward {
    id: number;
    lga_id: number;
    name: string;
    longitude?: string;
    latitude?: string;
    created_at: string | null;
    updated_at: string | null;
}

export interface Record {
    id: number;
    state_id: number;
    lga_id: number;
    ward_id: number;
    data: RecordData;
    created_at: string;
    updated_at: string;
}

export interface RecordData {
    [key: string]: string | number;
}

export interface ProjectOutlook {
    id: number;
    title: string;
    description: string;
    created_at: string;
    updated_at: string;
}

// Form interfaces for better type safety
export interface RecordFormData {
    state_id: number | null;
    lga_id: number | null;
    ward_id: number | null;
    record: RecordData;
}

export interface LocationSelectOptions {
    states: State[];
    lgas: Lga[];
    wards: Ward[];
}

// API Response types
export interface ApiResponse<T = any> {
    data: T;
    message?: string;
    status: 'success' | 'error';
}

export interface PaginatedResponse<T = any> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
}