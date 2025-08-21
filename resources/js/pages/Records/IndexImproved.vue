<!-- 
  Example usage of the new reusable composables and utilities
  This demonstrates how other developers can use the improved components
-->
<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Records Management</h1>
      <button
        @click="showModal = true"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        :disabled="locationSelector.isLoading"
      >
        Add Record
      </button>
    </div>

    <!-- Error Display -->
    <div v-if="errorHandler.activeErrors().length > 0" class="space-y-2">
      <div
        v-for="error in errorHandler.activeErrors()"
        :key="error.id"
        :class="cn(
          'p-4 rounded-lg border',
          error.severity === 'error' && 'bg-red-50 border-red-200 text-red-700',
          error.severity === 'warn' && 'bg-yellow-50 border-yellow-200 text-yellow-700',
          error.severity === 'info' && 'bg-blue-50 border-blue-200 text-blue-700'
        )"
      >
        <div class="flex justify-between items-start">
          <p>{{ error.message }}</p>
          <button
            @click="errorHandler.dismissError(error.id)"
            class="text-sm hover:font-medium"
          >
            Dismiss
          </button>
        </div>
      </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="p-6">
        <div class="mb-4 flex flex-col sm:flex-row gap-4">
          <!-- Location Filter -->
          <div class="flex-1">
            <label class="block text-sm font-medium mb-2">Filter by State</label>
            <select
              v-model="selectedFilterState"
              @change="handleFilterChange"
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
            >
              <option value="">All States</option>
              <option
                v-for="state in locationSelector.states.value"
                :key="state.id"
                :value="state.id"
              >
                {{ state.name }}
              </option>
            </select>
          </div>

          <!-- Search -->
          <div class="flex-1">
            <label class="block text-sm font-medium mb-2">Search</label>
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Search records..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2"
            />
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600">Loading records...</p>
        </div>

        <!-- Records -->
        <div v-else class="space-y-4">
          <div
            v-for="record in paginatedRecords"
            :key="record.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
          >
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-medium">{{ getLocationString(record) }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                  Created {{ formatRelativeDate(record.created_at) }}
                </p>
                <div v-if="record.data" class="mt-2">
                  <div
                    v-for="(value, key) in record.data"
                    :key="key"
                    class="inline-block bg-gray-100 rounded px-2 py-1 text-xs mr-2 mb-1"
                  >
                    {{ titleCase(key) }}: {{ value }}
                  </div>
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="editRecord(record)"
                  class="text-blue-600 hover:text-blue-800 text-sm"
                >
                  Edit
                </button>
                <button
                  @click="deleteRecord(record.id)"
                  class="text-red-600 hover:text-red-800 text-sm"
                  :disabled="form.isSubmitting"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="paginatedRecords.length === 0" class="text-center py-8">
            <p class="text-gray-500">No records found</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-6 flex justify-center space-x-2">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage <= 1"
            class="px-3 py-1 border rounded disabled:opacity-50"
          >
            Previous
          </button>
          <span class="px-3 py-1">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage >= totalPages"
            class="px-3 py-1 border rounded disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Modal for Add/Edit Record -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold">
            {{ editingRecord ? 'Edit Record' : 'Add New Record' }}
          </h2>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
            ×
          </button>
        </div>

        <form @submit.prevent="form.submit" class="space-y-4">
          <!-- Location Selection -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">State</label>
              <select
                :value="locationSelector.selectedStateId.value"
                @change="locationSelector.setState(Number($event.target.value) || null)"
                :class="cn(
                  'w-full border rounded-lg px-3 py-2',
                  form.errors.state_id ? 'border-red-500' : 'border-gray-300'
                )"
              >
                <option value="">Select State</option>
                <option
                  v-for="state in locationSelector.states.value"
                  :key="state.id"
                  :value="state.id"
                >
                  {{ state.name }}
                </option>
              </select>
              <p v-if="form.errors.state_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.state_id }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">LGA</label>
              <select
                :value="locationSelector.selectedLgaId.value"
                @change="locationSelector.setLga(Number($event.target.value) || null)"
                :disabled="!locationSelector.selectedStateId.value"
                :class="cn(
                  'w-full border rounded-lg px-3 py-2',
                  form.errors.lga_id ? 'border-red-500' : 'border-gray-300'
                )"
              >
                <option value="">Select LGA</option>
                <option
                  v-for="lga in locationSelector.lgas.value"
                  :key="lga.id"
                  :value="lga.id"
                >
                  {{ lga.name }}
                </option>
              </select>
              <p v-if="form.errors.lga_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.lga_id }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Ward</label>
              <select
                :value="locationSelector.selectedWardId.value"
                @change="locationSelector.setWard(Number($event.target.value) || null)"
                :disabled="!locationSelector.selectedLgaId.value"
                :class="cn(
                  'w-full border rounded-lg px-3 py-2',
                  form.errors.ward_id ? 'border-red-500' : 'border-gray-300'
                )"
              >
                <option value="">Select Ward</option>
                <option
                  v-for="ward in locationSelector.wards.value"
                  :key="ward.id"
                  :value="ward.id"
                >
                  {{ ward.name }}
                </option>
              </select>
              <p v-if="form.errors.ward_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.ward_id }}
              </p>
            </div>
          </div>

          <!-- Data Fields -->
          <div class="space-y-4">
            <h3 class="font-medium">Record Data</h3>
            <div
              v-for="(field, index) in dataFields"
              :key="index"
              class="flex items-center space-x-2"
            >
              <input
                v-model="field.key"
                placeholder="Field name"
                class="flex-1 border border-gray-300 rounded px-3 py-2"
              />
              <input
                v-model="field.value"
                placeholder="Field value"
                class="flex-1 border border-gray-300 rounded px-3 py-2"
              />
              <button
                type="button"
                @click="removeDataField(index)"
                class="text-red-600 hover:text-red-800"
              >
                Remove
              </button>
            </div>
            <button
              type="button"
              @click="addDataField"
              class="text-blue-600 hover:text-blue-800 text-sm"
            >
              + Add Field
            </button>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end space-x-4 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.isSubmitting || !form.isValid"
              :class="cn(
                'px-4 py-2 rounded-lg text-white',
                form.isSubmitting || !form.isValid
                  ? 'bg-gray-400 cursor-not-allowed'
                  : 'bg-blue-600 hover:bg-blue-700'
              )"
            >
              {{ form.isSubmitting ? 'Saving...' : 'Save Record' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useForm, validationRules } from '@/composables/useForm';
import { useLocationSelector } from '@/composables/useLocationSelector';
import { useErrorHandler, useToast, withErrorHandling } from '@/composables/useError';
import { recordsApi } from '@/services/api';
import { cn, formatRelativeDate, titleCase, debounce } from '@/lib/utils';
import type { Record, RecordFormData, State, PaginatedResponse } from '@/types';

interface Props {
  records: PaginatedResponse<Record>;
  states: State[];
}

const props = defineProps<Props>();

// Composables
const locationSelector = useLocationSelector();
const errorHandler = useErrorHandler();
const toast = useToast();

// State
const showModal = ref(false);
const editingRecord = ref<Record | null>(null);
const isLoading = ref(false);
const searchQuery = ref('');
const selectedFilterState = ref<number | null>(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Data fields for form
const dataFields = ref<Array<{ key: string; value: string }>>([
  { key: '', value: '' }
]);

// Form configuration
const form = useForm<RecordFormData>({
  initialData: {
    state_id: null,
    lga_id: null,
    ward_id: null,
    record: {}
  },
  validationRules: {
    state_id: [validationRules.required('Please select a state')],
    lga_id: [validationRules.required('Please select an LGA')],
    ward_id: [validationRules.required('Please select a ward')]
  },
  onSubmit: async (data) => {
    // Convert data fields to record object
    const recordData: Record<string, string> = {};
    dataFields.value.forEach(field => {
      if (field.key && field.value) {
        recordData[field.key] = field.value;
      }
    });

    const submitData = {
      ...data,
      ...locationSelector.getSelection(),
      data: recordData
    };

    await withErrorHandling(async () => {
      if (editingRecord.value) {
        await recordsApi.updateRecord(editingRecord.value.id, submitData);
        toast.success('Success', 'Record updated successfully');
      } else {
        await recordsApi.createRecord(submitData as Record);
        toast.success('Success', 'Record created successfully');
      }
      
      closeModal();
      refreshRecords();
    }, 'Save record', { showToast: true });
  }
});

// Computed
const filteredRecords = computed(() => {
  let filtered = props.records.data;

  if (selectedFilterState.value) {
    filtered = filtered.filter(record => record.state_id === selectedFilterState.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(record => {
      const locationString = getLocationString(record).toLowerCase();
      return locationString.includes(query);
    });
  }

  return filtered;
});

const totalPages = computed(() => Math.ceil(filteredRecords.value.length / itemsPerPage.value));
const paginatedRecords = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredRecords.value.slice(start, end);
});

// Methods
const getLocationString = (record: Record): string => {
  const state = props.states.find(s => s.id === record.state_id);
  const lga = state?.lgas?.find(l => l.id === record.lga_id);
  // For ward, you'd need to fetch or include ward data in the record
  return `${state?.name || 'Unknown'}, ${lga?.name || 'Unknown'}`;
};

const editRecord = (record: Record) => {
  editingRecord.value = record;
  
  // Set location selector
  locationSelector.setSelection({
    stateId: record.state_id,
    lgaId: record.lga_id,
    wardId: record.ward_id
  });
  
  // Set data fields
  if (record.data) {
    dataFields.value = Object.entries(record.data).map(([key, value]) => ({
      key,
      value: String(value)
    }));
  }
  
  showModal.value = true;
};

const deleteRecord = async (id: number) => {
  if (!confirm('Are you sure you want to delete this record?')) {
    return;
  }

  await withErrorHandling(async () => {
    await recordsApi.deleteRecord(id);
    toast.success('Success', 'Record deleted successfully');
    refreshRecords();
  }, 'Delete record', { showToast: true });
};

const closeModal = () => {
  showModal.value = false;
  editingRecord.value = null;
  form.reset();
  dataFields.value = [{ key: '', value: '' }];
  locationSelector.reset();
};

const addDataField = () => {
  dataFields.value.push({ key: '', value: '' });
};

const removeDataField = (index: number) => {
  dataFields.value.splice(index, 1);
  if (dataFields.value.length === 0) {
    dataFields.value.push({ key: '', value: '' });
  }
};

const refreshRecords = () => {
  router.reload({ only: ['records'] });
};

const goToPage = (page: number) => {
  currentPage.value = page;
};

const handleFilterChange = () => {
  currentPage.value = 1; // Reset to first page when filter changes
};

// Debounced search
const debouncedSearch = debounce(() => {
  currentPage.value = 1; // Reset to first page when search changes
}, 300);

// Lifecycle
onMounted(async () => {
  // Load states for location selector
  locationSelector.states.value = props.states;
});
</script>