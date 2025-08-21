/**
 * Improved appearance/theme management composable
 * Provides type-safe theme switching with better error handling and modularity
 */

import { onMounted, onUnmounted, ref, computed, readonly } from 'vue';

export type Appearance = 'light' | 'dark' | 'system';

/**
 * Configuration options for the appearance composable
 */
export interface AppearanceConfig {
    storageKey?: string;
    cookieName?: string;
    cookieDays?: number;
    defaultTheme?: Appearance;
    autoInit?: boolean;
}

/**
 * Theme management utilities
 */
class ThemeManager {
    private mediaQueryList: MediaQueryList | null = null;
    private listeners: Set<(appearance: Appearance) => void> = new Set();

    constructor(private config: AppearanceConfig) {
        this.setupMediaQuery();
    }

    private setupMediaQuery(): void {
        if (typeof window === 'undefined') return;

        this.mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
    }

    /**
     * Apply theme to the document
     */
    applyTheme(value: Appearance): void {
        if (typeof window === 'undefined' || !document?.documentElement) {
            return;
        }

        try {
            if (value === 'system') {
                const systemTheme = this.getSystemTheme();
                document.documentElement.classList.toggle('dark', systemTheme === 'dark');
            } else {
                document.documentElement.classList.toggle('dark', value === 'dark');
            }
        } catch (error) {
            console.warn('Failed to apply theme:', error);
        }
    }

    /**
     * Get the current system theme preference
     */
    getSystemTheme(): 'light' | 'dark' {
        if (!this.mediaQueryList) return 'light';
        return this.mediaQueryList.matches ? 'dark' : 'light';
    }

    /**
     * Get stored appearance preference
     */
    getStoredAppearance(): Appearance | null {
        if (typeof localStorage === 'undefined') return null;

        try {
            const stored = localStorage.getItem(this.config.storageKey || 'appearance');
            return stored as Appearance | null;
        } catch (error) {
            console.warn('Failed to read appearance from localStorage:', error);
            return null;
        }
    }

    /**
     * Store appearance preference
     */
    storeAppearance(value: Appearance): void {
        // Store in localStorage
        if (typeof localStorage !== 'undefined') {
            try {
                localStorage.setItem(this.config.storageKey || 'appearance', value);
            } catch (error) {
                console.warn('Failed to save appearance to localStorage:', error);
            }
        }

        // Store in cookie for SSR
        this.setCookie(value);
    }

    /**
     * Set cookie for SSR support
     */
    private setCookie(value: string): void {
        if (typeof document === 'undefined') return;

        try {
            const days = this.config.cookieDays || 365;
            const maxAge = days * 24 * 60 * 60;
            const name = this.config.cookieName || 'appearance';

            document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
        } catch (error) {
            console.warn('Failed to set appearance cookie:', error);
        }
    }

    /**
     * Subscribe to system theme changes
     */
    subscribeToSystemChanges(callback: (appearance: Appearance) => void): () => void {
        if (!this.mediaQueryList) {
            return () => {};
        }

        const handler = () => {
            const storedAppearance = this.getStoredAppearance();
            if (storedAppearance === 'system' || !storedAppearance) {
                callback('system');
            }
        };

        this.mediaQueryList.addEventListener('change', handler);
        
        return () => {
            this.mediaQueryList?.removeEventListener('change', handler);
        };
    }
}

// Global theme manager instance
let themeManager: ThemeManager | null = null;

/**
 * Initialize theme management (call once in app setup)
 */
export function initializeTheme(config: AppearanceConfig = {}): void {
    if (typeof window === 'undefined') return;

    themeManager = new ThemeManager({
        storageKey: 'appearance',
        cookieName: 'appearance',
        cookieDays: 365,
        defaultTheme: 'system',
        autoInit: true,
        ...config
    });

    // Apply initial theme
    const savedAppearance = themeManager.getStoredAppearance();
    const initialTheme = savedAppearance || config.defaultTheme || 'system';
    themeManager.applyTheme(initialTheme);
}

/**
 * Standalone theme update function for use outside composables
 */
export function updateTheme(value: Appearance): void {
    if (!themeManager) {
        console.warn('Theme manager not initialized. Call initializeTheme() first.');
        return;
    }
    themeManager.applyTheme(value);
}

/**
 * Main appearance composable
 */
export function useAppearance(config: AppearanceConfig = {}) {
    // Initialize theme manager if not already done
    if (!themeManager && typeof window !== 'undefined') {
        initializeTheme(config);
    }

    const appearance = ref<Appearance>(config.defaultTheme || 'system');
    const isSystemDark = ref(false);

    // Computed for the actual resolved theme
    const resolvedTheme = computed<'light' | 'dark'>(() => {
        if (appearance.value === 'system') {
            return isSystemDark.value ? 'dark' : 'light';
        }
        return appearance.value as 'light' | 'dark';
    });

    const isDark = computed(() => resolvedTheme.value === 'dark');

    let unsubscribe: (() => void) | null = null;

    /**
     * Update appearance preference
     */
    const updateAppearance = (value: Appearance): void => {
        if (!themeManager) {
            console.warn('Theme manager not initialized');
            return;
        }

        appearance.value = value;
        themeManager.storeAppearance(value);
        themeManager.applyTheme(value);
    };

    /**
     * Toggle between light and dark (ignores system)
     */
    const toggleAppearance = (): void => {
        const currentResolved = resolvedTheme.value;
        updateAppearance(currentResolved === 'dark' ? 'light' : 'dark');
    };

    /**
     * Reset to system preference
     */
    const resetToSystem = (): void => {
        updateAppearance('system');
    };

    onMounted(() => {
        if (!themeManager) return;

        // Load saved preference
        const savedAppearance = themeManager.getStoredAppearance();
        if (savedAppearance) {
            appearance.value = savedAppearance;
        }

        // Update system theme state
        isSystemDark.value = themeManager.getSystemTheme() === 'dark';

        // Subscribe to system theme changes
        unsubscribe = themeManager.subscribeToSystemChanges((newAppearance) => {
            isSystemDark.value = themeManager!.getSystemTheme() === 'dark';
            if (appearance.value === 'system') {
                themeManager!.applyTheme('system');
            }
        });
    });

    onUnmounted(() => {
        unsubscribe?.();
    });

    return {
        // Reactive state (readonly to prevent external mutation)
        appearance: readonly(appearance),
        resolvedTheme: readonly(resolvedTheme),
        isDark: readonly(isDark),
        isSystemDark: readonly(isSystemDark),

        // Actions
        updateAppearance,
        toggleAppearance,
        resetToSystem,

        // Utilities
        availableThemes: ['light', 'dark', 'system'] as const
    };
}
