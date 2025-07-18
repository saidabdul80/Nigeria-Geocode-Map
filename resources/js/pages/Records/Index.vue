<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

// PrimeVue Components
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Tag from 'primevue/tag'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import Dropdown from 'primevue/dropdown'
import axios from 'axios'

const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  records: {
    type: Object,
    required: true,
    default: () => ({ data: [] })
  },
  states: {
    type: Array,
    required: true,
    default: () => []
  },
  options: {
    type: Array,
    default: () => ['change', 'wind', 'rain']
  }
})

// Form handling - explicitly define structure
const form = useForm({
  state_id: null,
  lga_id: null,
  ward_id: null,
  record: []
})

// UI State
const showModal = ref(false)
const editingId = ref(null)
const availableLgas = ref([])
const currentDataKey = ref('change')
const currentDataValue = ref('')
const currentDataOutlook = ref('')

// Computed - Safely access data
const filteredRecords = computed(() => {
  return props.records.data.map(record => ({
    ...record,
    state_name: props.states.find(s => s.id === record.state_id)?.name || 'Unknown',
    lga_name: availableLgas.value.find(l => l.id === record.lga_id)?.name || 'Unknown',
    // Safely handle data access
    dataArray: record.data ? Object.entries(record.data).map(([key, value]) => ({ key, value })) : []
  }))
})

// Add availableWards ref
const availableWards = ref([]);


watch(() => form.lga_id, (newLgaId) => {
  if (newLgaId) {
    axios.get(`/lgas/${newLgaId}/wards`)
      .then(response => {
        availableWards.value = response.data;
      });
  } else {
    availableWards.value = [];
  }
}, { immediate: true });

// Watch for state changes - fixed implementation
watch(() => form.state_id, async (newStateId) => {
  if (newStateId) {
    try {
      const state = props.states.find(s => s.id === newStateId)
      availableLgas.value = state?.lgas || []
      form.lga_id = null
    } catch (error) {
      console.error('Error fetching LGAs:', error)
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to load LGAs',
        life: 3000
      })
    }
  } else {
    availableLgas.value = []
    form.lga_id = null
  }
}, { immediate: true })

// Methods
const addDataField = () => {
  if (currentDataKey.value && currentDataValue.value) {
    form.record.push({
      key: currentDataKey.value,
      value: currentDataValue.value,
      outlook: currentDataOutlook.value
    })
    currentDataKey.value = ''
    currentDataValue.value = ''
    currentDataOutlook.value = ''
  }
}

const filteredStates = computed(() => {
  return props.states;
});

const removeDataField = (index) => {
  form.record.splice(index, 1)
}

const editRecord = (record) => {
  editingId.value = record.id
  form.state_id = record.state_id
  form.lga_id = record.lga_id
  // Safely handle data access
  form.record = record.data;
  showModal.value = true
}

const submitForm = () => {
//   const formattedData = form.record.reduce((acc, item) => {
//     acc[item.key] = {
//       key: item.key,
//       value: item.value,
//       outlook: item.outlook
//     };
//     return acc;
//   }, {});

  const payload = {
    state_id: form.state_id,
    lga_id: form.lga_id,
    data:form.record
  }

  if (editingId.value) {
    form.put(route('records.update', editingId.value), payload, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false
        form.reset()
        editingId.value = null
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Record updated successfully',
          life: 3000
        })
      },
      onError: (errors) => {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: errors.message || 'Failed to update record',
          life: 3000
        })
      }
    })
  } else {
    form.post(route('records.store'), payload, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false
        form.reset()
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Record created successfully',
          life: 3000
        })
      },
      onError: (errors) => {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: errors.message || 'Failed to create record',
          life: 3000
        })
      }
    })
  }
}

const confirmDelete = (id) => {
  confirm.require({
    message: 'Are you sure you want to delete this record?',
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    accept: () => deleteRecord(id)
  })
}

const deleteRecord = (id) => {
  router.delete(route('records.destroy', id)), {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Record deleted successfully',
        life: 3000
      })
    },
    onError: () => {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to delete record',
        life: 3000
      })
    }
  }
}

// Pagination handling
const onPage = (event) => {
  router.get(route('records.index')), {
    page: event.page + 1, // PrimeVue starts at 0, Laravel at 1
    per_page: event.rows
  }, {
    preserveState: true,
    replace: true
  }
}
</script>

<template>
  <AppLayout title="Weather Records">
    <Toast />
    <ConfirmDialog />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Weather Records</h2>
        <Button 
          icon="pi pi-plus" 
          label="Add New Record" 
          @click="showModal = true; editingId = null;"
          severity="success"
          raised
        />
      </div>
    </template>

    <div class="card">
      <div class="flex justify-end mb-4">
        <Button 
          icon="pi pi-plus" 
          label="Add New Record" 
          @click="showModal = true; editingId = null; "
          severity="success"
          raised
        />
      </div>

      <DataTable 
        :value="filteredRecords" 
        paginator 
        :rows="records.per_page"
        :totalRecords="records.total"
        :rowsPerPageOptions="[5,10,25,50]"
        tableStyle="min-width: 50rem"
        @page="onPage"
        :first="(records.current_page - 1) * records.per_page"
        removableSort
      >
        <Column field="state_name" header="State" sortable></Column>
        <Column field="lga.name" header="LGA" sortable></Column>
        <Column header="Data">
          <template #body="{ data }">
            <div class="flex flex-wrap gap-2">
              <Tag 
                v-for="(value, key) in data.data" 
                :key="key" 
                :value="`${value.key}:${value.value}`"
                severity="info"
              />
            </div>
          </template>
        </Column>
        <Column header="Actions" style="width: 10rem">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button 
                icon="pi pi-pencil" 
                severity="secondary" 
                @click="editRecord(data)"
                outlined
              />
              <Button 
                icon="pi pi-trash" 
                severity="danger" 
                @click="confirmDelete(data.id)"
                outlined
              />
            </div>
          </template>
        </Column>
        <template #paginatorstart>
          <span class="text-sm">
            Showing {{ records.from }} to {{ records.to }} of {{ records.total }} records
          </span>
        </template>
      </DataTable>
    </div>

    <Dialog 
      v-model:visible="showModal" 
      modal 
      :header="editingId ? 'Edit Record' : 'Create New Record'"
      :style="{ width: '50vw' }"
      :breakpoints="{ '960px': '75vw', '641px': '90vw' }"
    >
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
          <label for="state" class="font-medium">State</label>
          <Dropdown 
            id="state"
            v-model="form.state_id" 
            :options="filteredStates" 
            optionLabel="name" 
            optionValue="id"
            placeholder="Select a State" 
            class="w-full"
            :class="{ 'p-invalid': form.errors.state_id }"
          />
          <small v-if="form.errors.state_id" class="p-error">{{ form.errors.state_id }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="lga" class="font-medium">LGA</label>
          <Dropdown 
            id="lga"
            v-model="form.lga_id" 
            :options="availableLgas" 
            optionLabel="name" 
            optionValue="id"
            placeholder="Select an LGA" 
            class="w-full"
            :disabled="!form.state_id"
            :class="{ 'p-invalid': form.errors.lga_id }"
          />
          <small v-if="form.errors.lga_id" class="p-error">{{ form.errors.lga_id }}</small>
        </div>
        <div class="flex flex-col gap-2">
            <label for="ward" class="font-medium">Ward</label>
            <Dropdown 
                id="ward"
                v-model="form.ward_id" 
                :options="availableWards" 
                optionLabel="name" 
                optionValue="id"
                placeholder="Select a Ward" 
                class="w-full"
                :disabled="!form.lga_id"
            />
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium">Report Data</label>
          <small v-if="form.errors.data" class="p-error">{{ form.errors.data }}</small>
          <div class="flex flex-col gap-3">
            <div 
              v-for="(item, index) in form.record" 
              :key="index" 
              class="p-3 border-round surface-card flex justify-content-between align-items-center"
            >
              <div>
                <span class="font-medium">{{ item.key }}:</span>
                <span class="ml-2">{{ item.outlook }} :</span>
                <span class="ml-2">{{ item.value }}</span>
              </div>
              <Button 
                icon="pi pi-times" 
                severity="danger" 
                text 
                rounded 
                @click="removeDataField(index)"
              />
            </div>

            <div class="grid grid-cols-7 gap-2">
              <Dropdown 
                v-model="currentDataKey" 
                :options="options" 
                placeholder="Select data type"
                class="flex-1 col-span-2"
              />
               <!-- <InputText 
                v-model="currentDataOutlook" 
                placeholder="Outlook" 
                class="flex-1 col-span-2"
              /> -->
              <InputText 
                v-model="currentDataValue" 
                placeholder="Value" 
                class="flex-1 col-span-2"
              />
              <Button 
                icon="pi pi-plus" 
                @click="addDataField" 
                :disabled="!currentDataKey || (!currentDataValue)"
                severity="secondary"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <Button 
            label="Cancel" 
            severity="secondary" 
            @click="showModal = false"
            outlined
          />
          <Button 
            :label="editingId ? 'Update' : 'Save'" 
            @click="submitForm" 
            :disabled="form.processing"
            :loading="form.processing"
          />
        </div>
      </div>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
:deep(.p-datatable) {
  font-size: 0.875rem;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
  background-color: var(--surface-100);
}

:deep(.p-datatable .p-datatable-tbody > tr) {
  background-color: var(--surface-card);
}
</style>