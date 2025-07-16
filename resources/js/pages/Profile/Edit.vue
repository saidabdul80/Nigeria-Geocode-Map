<!-- resources/js/Pages/Profile/Edit.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
const props = defineProps({
  user: Object,
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  current_password: '',
  password: '',
  password_confirmation: '',
});



const submit = () => {
  form.put(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset('current_password', 'password', 'password_confirmation'),
  });
};
</script>

<template>
  <AppLayout title="Profile">
    <template #header>
      <h2 class="text-2xl font-bold">Profile</h2>
    </template>

    <div class="max-w-2xl p-4 sm:p-6 lg:p-8">
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
          <InputText
            id="name"
            v-model="form.name"
            class="mt-1 block w-full"
            required
            autocomplete="name"
          />
          <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
            {{ form.errors.name }}
          </p>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <InputText
            id="email"
            v-model="form.email"
            type="email"
            class="mt-1 block w-full"
            required
            autocomplete="email"
          />
          <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
            {{ form.errors.email }}
          </p>
        </div>

        <div>
          <label for="current_password" class="block text-sm font-medium text-gray-700">
            Current Password
          </label>
          <Password
            id="current_password"
            v-model="form.current_password"
            class="mt-1 "
            autocomplete="current-password"
            :feedback="false"
            toggleMask
          />
          <p v-if="form.errors.current_password" class="mt-2 text-sm text-red-600">
            {{ form.errors.current_password }}
          </p>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">
            New Password
          </label>
          <Password
            id="password"
            v-model="form.password"
            class="mt-1 "
            autocomplete="new-password"
            toggleMask
          />
          <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
            {{ form.errors.password }}
          </p>
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
            Confirm Password
          </label>
          <Password
            id="password_confirmation"
            v-model="form.password_confirmation"
            class="mt-1 block"
            autocomplete="new-password"
            toggleMask
          />
        </div>

        <div class="flex items-center gap-4">
          <Button
            type="submit"
            label="Save"
            :disabled="form.processing"
            :loading="form.processing"
          />
          
          <Transition
            enter-active-class="transition ease-in-out"
            enter-from-class="opacity-0"
            leave-active-class="transition ease-in-out"
            leave-to-class="opacity-0"
          >
            <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
          </Transition>

        </div>
        <div>
            <h1><b>Roles</b></h1>
              {{props.user.roles.map(t=>t.name).join(',') }}
        </div>
         <div>
            <h1><b>Active States</b></h1>
              {{props.user.state_permissions.map(t=>t.name).join(',') }}
        </div>
        <div>
            <h1><b>Active Lgas</b></h1>
              {{props.user.lga_permissions.map(t=>t.name).join(',') }}
        </div>
      </form>
    </div>
  </AppLayout>
</template>