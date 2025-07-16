<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import axios from 'axios';

const toast = useToast();
const confirm = useConfirm();

const props = defineProps({
  users: Object,
  roles: Array,
  states: Array,
});

const form = useForm({
  id: null,
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: [],
  state_permissions: [],
  lga_permissions: [],
    ward_permissions: []
});

const showModal = ref(false);
const editingId = ref(null);
const availableLgas = ref([]);
const availableWards = ref([]);
const loadingWards = ref(false);
const wardSearchQuery = ref('');

const openCreateModal = () => {
  form.reset();
  editingId.value = null;
  showModal.value = true;
};

const openEditModal = (user) => {
  form.id = user.id;
  form.name = user.name;
  form.email = user.email;
  form.roles = user.roles.map(r => r.id);
  form.state_permissions = user.state_permissions.map(s => s.id);
  form.lga_permissions = user.lga_permissions.map(l => l.id);
  form.ward_permissions = user.ward_permissions.map(w => w.id);
  editingId.value = user.id;
  showModal.value = true;
  if (user.lga_permissions.length > 0) {
    availableWards.value = user.ward_permissions;
  }
};

const submitForm = () => {
  if (editingId.value) {
    form.put(route('users.update', editingId.value), {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'User updated successfully',
          life: 3000,
        });
      },
      onError: (errors) => {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: errors.message || 'Failed to update user',
          life: 3000,
        });
      },
    });
  } else {
    form.post(route('users.store'), {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'User created successfully',
          life: 3000,
        });
      },
      onError: (errors) => {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: errors.message || 'Failed to create user',
          life: 3000,
        });
      },
    });
  }
};

const confirmDelete = (id) => {
  confirm.require({
    message: 'Are you sure you want to delete this user?',
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    accept: () => deleteUser(id),
  });
};

const loadWards = async (lgaIds, search = '') => {
  if (!lgaIds || lgaIds.length === 0) {
    availableWards.value = [];
    return;
  }

  loadingWards.value = true;
  try {
    const response = await axios.get('/wards', {
      params: { 
        lga_ids: lgaIds.join(','),
        search: search 
      }
    });
    availableWards.value = response.data;
  } catch (error) {
    console.error('Error loading wards:', error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load wards',
      life: 3000
    });
  } finally {
    loadingWards.value = false;
  }
}


const deleteUser = (id) => {
  router.delete(route('users.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'User deleted successfully',
        life: 3000,
      });
    },
    onError: () => {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to delete user',
        life: 3000,
      });
    },
  });
};

watch(()=>form.state_permissions, (stateIds)=>{
 if (stateIds && stateIds.length > 0) {
    availableLgas.value = props.states
      .filter(state => stateIds.includes(state.id))
      .flatMap(state => state.lgas);
  } else {
    availableLgas.value = [];
    form.lga_permissions = [];
  }
}, { immediate: true })

watch(() => form.lga_permissions, (lgaIds) => {
  loadWards(lgaIds, wardSearchQuery.value);
  form.ward_permissions = [];
});
</script>

<template>
  <AppLayout title="User Management">
    <Toast />
    <ConfirmDialog />

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">User Management</h2>
        </div>
   
    </template>

    <div class="card">
         <div class="flex justify-end mb-4">
 <Button
      icon="pi pi-plus"
      label="Add New User"
      @click="openCreateModal"
      severity="success"
      raised
    />
         </div>
      <DataTable
        :value="users.data"
        paginator
        :rows="users.per_page"
        :totalRecords="users.total"
        :rowsPerPageOptions="[5,10,25,50]"
        tableStyle="min-width: 50rem"
      >
        <Column field="name" header="Name" sortable></Column>
        <Column field="email" header="Email" sortable></Column>
        <Column header="Roles">
          <template #body="{ data }">
            <div class="flex flex-wrap gap-2">
              <span
                v-for="role in data.roles"
                :key="role.id"
                class="p-tag p-tag-rounded"
                :class="{
                  'bg-primary-500': role.name === 'admin',
                  'bg-green-500': role.name === 'editor',
                  'bg-blue-500': role.name === 'state_editor',
                  'bg-purple-500': role.name === 'lga_editor',
                }"
              >
                {{ role.name }}
              </span>
            </div>
          </template>
        </Column>
        <Column header="State Access">
          <template #body="{ data }">
            <div v-if="data.state_permissions.length > 0" class="flex flex-wrap gap-2">
              <span
                v-for="state in data.state_permissions"
                :key="state.id"
                class="p-tag p-tag-rounded bg-blue-100 text-blue-800"
              >
                {{ state.name }}
              </span>
            </div>
            <span v-else class="text-gray-500">All states</span>
          </template>
        </Column>
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
      :header="editingId ? 'Edit User' : 'Create New User'"
      :style="{ width: '50vw' }"
      :breakpoints="{ '960px': '75vw', '641px': '90vw' }"
    >
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
          <label for="name" class="font-medium">Name</label>
          <InputText
            id="name"
            v-model="form.name"
            placeholder="Full Name"
            class="w-full"
            :class="{ 'p-invalid': form.errors.name }"
          />
          <small v-if="form.errors.name" class="p-error">{{ form.errors.name }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="email" class="font-medium">Email</label>
          <InputText
            id="email"
            v-model="form.email"
            placeholder="Email Address"
            class="w-full"
            :class="{ 'p-invalid': form.errors.email }"
          />
          <small v-if="form.errors.email" class="p-error">{{ form.errors.email }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="password" class="font-medium">
            {{ editingId ? 'New Password (leave blank to keep current)' : 'Password' }}
          </label>
          <Password
            id="password"
            v-model="form.password"
            placeholder="Password"
            class="w-full"
            :class="{ 'p-invalid': form.errors.password }"
            toggleMask
            :feedback="false"
          />
          <small v-if="form.errors.password" class="p-error">{{ form.errors.password }}</small>
        </div>

        <div class="flex flex-col gap-2" v-if="form.password">
          <label for="password_confirmation" class="font-medium">Confirm Password</label>
          <Password
            id="password_confirmation"
            v-model="form.password_confirmation"
            placeholder="Confirm Password"
            class="w-full"
            toggleMask
            :feedback="false"
          />
        </div>

        <div class="flex flex-col gap-2">
          <label for="roles" class="font-medium">Roles</label>
          <MultiSelect
            id="roles"
            v-model="form.roles"
            :options="roles"
            optionLabel="name"
            optionValue="id"
            placeholder="Select Roles"
            class="w-full"
            :class="{ 'p-invalid': form.errors.roles }"
          />
          <small v-if="form.errors.roles" class="p-error">{{ form.errors.roles }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="state_permissions" class="font-medium">State Permissions</label>
          <MultiSelect
            id="state_permissions"
            v-model="form.state_permissions"
            :options="states"
            optionLabel="name"
            optionValue="id"
            placeholder="Select States (leave empty for all states)"
            class="w-full"
            @change="onStateChange"
          />
        </div>

        <div class="flex flex-col gap-2">
          <label for="lga_permissions" class="font-medium">LGA Permissions</label>
          <MultiSelect
            id="lga_permissions"
            v-model="form.lga_permissions"
            :options="availableLgas"
            optionLabel="name"
            optionValue="id"
            placeholder="Select LGAs (leave empty for all LGAs)"
            class="w-full"
            :disabled="availableLgas.length === 0"
          />
        </div>


      <div class="flex flex-col gap-2">
        <label for="ward_permissions" class="font-medium">
          Ward Permissions
          <span v-if="availableWards.length" class="text-sm text-gray-500">
            (Showing {{ availableWards.length }} wards)
          </span>
        </label>
        <MultiSelect
          v-model="form.ward_permissions"
          :options="availableWards"
          optionLabel="name"
          optionValue="id"
          placeholder="Select Wards"
          class="w-full"
          :disabled="!form.lga_permissions?.length"
          :loading="loadingWards"
          :filter="false"
        >
          <template #option="slotProps">
            <div>{{ slotProps.option.name }}</div>
            <div class="text-xs text-gray-500">
              {{ slotProps.option.lga?.name || 'Unknown LGA' }}
            </div>
          </template>
        </MultiSelect>
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