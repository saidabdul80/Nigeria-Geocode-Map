<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

const toast = useToast();
const confirm = useConfirm();

const props = defineProps({
  outlooks: Object,
  states: Array,
  years: Array
});

const form = useForm({
  state_id: null,
  lga_id: null,
  outlook: null,
  project_year: new Date().getFullYear()
});

const showModal = ref(false);
const editingId = ref(null);
const availableLgas = ref([]);

// Computed property for filtered records
const filteredOutlooks = computed(() => {
  return props.outlooks.data.map(outlook => ({
    ...outlook,
    state_name: outlook.state.name,
    lga_name: outlook.lga.name
  }));
});

// Watch for state changes to update LGAs
watch(() => form.state_id, (newStateId) => {
  if (newStateId) {
    const state = props.states.find(s => s.id === newStateId);
    availableLgas.value = state?.lgas || [];
    form.lga_id = null;
  } else {
    availableLgas.value = [];
    form.lga_id = null;
  }
}, { immediate: true });

const openCreateModal = () => {
  form.reset();
  editingId.value = null;
  showModal.value = true;
};

const openEditModal = (outlook) => {
  form.state_id = outlook.state_id;
  form.lga_id = outlook.lga_id;
  form.outlook = outlook.outlook;
  form.project_year = outlook.project_year;
  editingId.value = outlook.id;
  showModal.value = true;
};

const submitForm = () => {
  if (editingId.value) {
    form.put(route('project-outlooks.update', editingId.value), {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Outlook updated successfully',
          life: 3000
        });
      },
      onError: (errors) => {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: errors.message || 'Failed to update outlook',
          life: 3000
        });
      }
    });
  } else {
    form.post(route('project-outlooks.store'), {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Outlook created successfully',
          life: 3000
        });
      },
      onError: (errors) => {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: errors.message || 'Failed to create outlook',
          life: 3000
        });
      }
    });
  }
};

const confirmDelete = (id) => {
  confirm.require({
    message: 'Are you sure you want to delete this outlook?',
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    accept: () => {
      router.delete(route('project-outlooks.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
          toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Outlook deleted successfully',
            life: 3000
          });
        }
      });
    }
  });
};
</script>

<template>
  <AppLayout title="Project Outlooks">
    <Toast />
    <ConfirmDialog />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Project Outlooks by LGA</h2>
        <Button 
          icon="pi pi-plus" 
          label="Add New Outlook" 
          @click="openCreateModal"
          severity="success"
          raised
        />
      </div>
    </template>

    <div class="card">
        <div class="flex justify-end mb-4">
                <Button 
                    icon="pi pi-plus" 
                    label="Add New Outlook" 
                    @click="openCreateModal"
                    severity="success"
                    raised
                    />
        </div>
      <DataTable 
        :value="filteredOutlooks" 
        paginator 
        :rows="outlooks.per_page"
        :totalRecords="outlooks.total"
        :rowsPerPageOptions="[5,10,25,50]"
        tableStyle="min-width: 50rem"
      >
        <Column field="state_name" header="State" sortable></Column>
        <Column field="lga_name" header="LGA" sortable></Column>
        <Column field="outlook" header="Outlook" sortable>
          <template #body="{ data }">
            <span :class="{
              'text-green-500': data.outlook >= 70,
              'text-amber-500': data.outlook >= 45 && data.outlook < 70,
              'text-red-500': data.outlook < 45
            }">
              {{ data.outlook }}
            </span>
          </template>
        </Column>
        <Column field="project_year" header="Year" sortable></Column>
        <Column header="Actions" style="width: 10rem">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button 
                icon="pi pi-pencil" 
                severity="secondary" 
                @click="openEditModal(data)"
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
      </DataTable>
    </div>

    <Dialog 
      v-model:visible="showModal" 
      modal 
      :header="editingId ? 'Edit Outlook' : 'Create New Outlook'"
      :style="{ width: '50vw' }"
      :breakpoints="{ '960px': '75vw', '641px': '90vw' }"
    >
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
          <label for="state" class="font-medium">State</label>
          <Dropdown 
            id="state"
            v-model="form.state_id" 
            :options="states" 
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
          <label for="outlook" class="font-medium">Outlook</label>
          <InputText 
            id="outlook"
            v-model="form.outlook" 
            type="number"
            min="0"
            max="100"
            placeholder="Enter outlook percentage"
            class="w-full"
            :class="{ 'p-invalid': form.errors.outlook }"
          />
          <small v-if="form.errors.outlook" class="p-error">{{ form.errors.outlook }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="project_year" class="font-medium">Project Year</label>
          <Dropdown 
            id="project_year"
            v-model="form.project_year" 
            :options="years" 
            placeholder="Select year"
            class="w-full"
            :class="{ 'p-invalid': form.errors.project_year }"
          />
          <small v-if="form.errors.project_year" class="p-error">{{ form.errors.project_year }}</small>
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