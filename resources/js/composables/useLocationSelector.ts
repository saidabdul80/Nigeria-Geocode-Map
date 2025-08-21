/**
 * Location management composable
 * Provides reusable location selection logic with cascading dropdowns
 */

import { ref, computed, watch, type Ref } from 'vue';
import { locationApi } from '@/services/api';
import type { State, Lga, Ward } from '@/types/entities';

export interface LocationSelection {
    stateId: number | null;
    lgaId: number | null;
    wardId: number | null;
}

/**
 * Composable for managing location selection with cascading dropdowns
 */
export function useLocationSelector(initialSelection: Partial<LocationSelection> = {}) {
    // State management
    const states = ref<State[]>([]);
    const lgas = ref<Lga[]>([]);
    const wards = ref<Ward[]>([]);
    
    const selectedStateId = ref<number | null>(initialSelection.stateId || null);
    const selectedLgaId = ref<number | null>(initialSelection.lgaId || null);
    const selectedWardId = ref<number | null>(initialSelection.wardId || null);
    
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Computed properties for easy access
    const selectedState = computed(() => 
        states.value.find(state => state.id === selectedStateId.value) || null
    );
    
    const selectedLga = computed(() => 
        lgas.value.find(lga => lga.id === selectedLgaId.value) || null
    );
    
    const selectedWard = computed(() => 
        wards.value.find(ward => ward.id === selectedWardId.value) || null
    );

    const hasValidSelection = computed(() => 
        selectedStateId.value !== null && selectedLgaId.value !== null && selectedWardId.value !== null
    );

    /**
     * Load all states
     */
    const loadStates = async (): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            states.value = await locationApi.getStates();
        } catch (err) {
            error.value = 'Failed to load states';
            console.error('Error loading states:', err);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Load LGAs for selected state
     */
    const loadLgas = async (stateId: number): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            lgas.value = await locationApi.getLgasByState(stateId);
        } catch (err) {
            error.value = 'Failed to load LGAs';
            console.error('Error loading LGAs:', err);
            lgas.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Load wards for selected LGA
     */
    const loadWards = async (lgaId: number): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = null;
            wards.value = await locationApi.getWardsByLga(lgaId);
        } catch (err) {
            error.value = 'Failed to load wards';
            console.error('Error loading wards:', err);
            wards.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Set state and reset dependent selections
     */
    const setState = async (stateId: number | null): Promise<void> => {
        selectedStateId.value = stateId;
        selectedLgaId.value = null;
        selectedWardId.value = null;
        lgas.value = [];
        wards.value = [];

        if (stateId) {
            await loadLgas(stateId);
        }
    };

    /**
     * Set LGA and reset ward selection
     */
    const setLga = async (lgaId: number | null): Promise<void> => {
        selectedLgaId.value = lgaId;
        selectedWardId.value = null;
        wards.value = [];

        if (lgaId) {
            await loadWards(lgaId);
        }
    };

    /**
     * Set ward
     */
    const setWard = (wardId: number | null): void => {
        selectedWardId.value = wardId;
    };

    /**
     * Reset all selections
     */
    const reset = (): void => {
        selectedStateId.value = null;
        selectedLgaId.value = null;
        selectedWardId.value = null;
        lgas.value = [];
        wards.value = [];
        error.value = null;
    };

    /**
     * Get current selection as object
     */
    const getSelection = (): LocationSelection => ({
        stateId: selectedStateId.value,
        lgaId: selectedLgaId.value,
        wardId: selectedWardId.value
    });

    /**
     * Set complete selection (useful for editing)
     */
    const setSelection = async (selection: Partial<LocationSelection>): Promise<void> => {
        if (selection.stateId) {
            selectedStateId.value = selection.stateId;
            await loadLgas(selection.stateId);
            
            if (selection.lgaId) {
                selectedLgaId.value = selection.lgaId;
                await loadWards(selection.lgaId);
                
                if (selection.wardId) {
                    selectedWardId.value = selection.wardId;
                }
            }
        }
    };

    // Watch for state changes to auto-load LGAs
    watch(selectedStateId, async (newStateId, oldStateId) => {
        if (newStateId && newStateId !== oldStateId) {
            await loadLgas(newStateId);
        }
    });

    // Watch for LGA changes to auto-load wards
    watch(selectedLgaId, async (newLgaId, oldLgaId) => {
        if (newLgaId && newLgaId !== oldLgaId) {
            await loadWards(newLgaId);
        }
    });

    return {
        // State
        states,
        lgas,
        wards,
        selectedStateId,
        selectedLgaId,
        selectedWardId,
        selectedState,
        selectedLga,
        selectedWard,
        isLoading,
        error,
        hasValidSelection,

        // Methods
        loadStates,
        setState,
        setLga,
        setWard,
        reset,
        getSelection,
        setSelection
    };
}