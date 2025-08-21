# Nigeria Geocode Map - Reusable Components Documentation

This documentation explains how to use the improved, reusable components and utilities in the Nigeria Geocode Map application.

## Table of Contents

1. [API Services](#api-services)
2. [Composables](#composables)
3. [Utilities](#utilities)
4. [PHP Services](#php-services)
5. [Usage Examples](#usage-examples)

## API Services

### ApiService Class

The base API service provides centralized HTTP client with error handling, type safety, and caching.

```typescript
import { ApiService, locationApi, recordsApi } from '@/services/api';

// Using pre-configured instances
const states = await locationApi.getStates();
const records = await recordsApi.getRecords(1, 15);

// Creating custom service
const customApi = new ApiService({
  baseURL: '/api/v2',
  timeout: 15000,
  enableCaching: true
});
```

### LocationApiService

Specialized service for Nigeria location data:

```typescript
import { locationApi } from '@/services/api';

// Get all states
const states = await locationApi.getStates();

// Get LGAs for a state
const lgas = await locationApi.getLgasByState(1);

// Get wards for an LGA
const wards = await locationApi.getWardsByLga(1);

// Search locations
const results = await locationApi.searchLocations('Lagos');
```

### RecordsApiService

Service for managing records:

```typescript
import { recordsApi } from '@/services/api';

// Get paginated records
const records = await recordsApi.getRecords(1, 15, { state_id: 1 });

// Create record
const newRecord = await recordsApi.createRecord(recordData);

// Update record
const updated = await recordsApi.updateRecord(1, { data: updatedData });

// Delete record
await recordsApi.deleteRecord(1);
```

## Composables

### useForm

Type-safe form management with validation:

```vue
<script setup lang="ts">
import { useForm, validationRules } from '@/composables/useForm';

const form = useForm({
  initialData: {
    name: '',
    email: '',
    age: 0
  },
  validationRules: {
    name: [validationRules.required()],
    email: [validationRules.required(), validationRules.email()],
    age: [validationRules.required(), validationRules.positive()]
  },
  onSubmit: async (data) => {
    // Handle form submission
    await api.post('/submit', data);
  }
});
</script>

<template>
  <form @submit.prevent="form.submit">
    <input 
      :value="form.data.name" 
      @input="form.setField('name', $event.target.value)"
      :class="{ 'error': form.errors.name }"
    />
    <p v-if="form.errors.name">{{ form.errors.name }}</p>
    
    <button 
      type="submit" 
      :disabled="form.isSubmitting || !form.isValid"
    >
      Submit
    </button>
  </form>
</template>
```

### useLocationSelector

Cascading location selection with automatic data loading:

```vue
<script setup lang="ts">
import { useLocationSelector } from '@/composables/useLocationSelector';

const locationSelector = useLocationSelector();

// Load states on mount
onMounted(() => {
  locationSelector.loadStates();
});
</script>

<template>
  <select 
    :value="locationSelector.selectedStateId.value"
    @change="locationSelector.setState($event.target.value)"
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

  <select 
    :value="locationSelector.selectedLgaId.value"
    @change="locationSelector.setLga($event.target.value)"
    :disabled="!locationSelector.selectedStateId.value"
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
</template>
```

### useAppearance

Improved theme management with better type safety:

```vue
<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';

const appearance = useAppearance();
</script>

<template>
  <div>
    <p>Current theme: {{ appearance.resolvedTheme.value }}</p>
    <p>System prefers dark: {{ appearance.isSystemDark.value }}</p>
    
    <button @click="appearance.toggleAppearance()">
      Toggle Theme
    </button>
    
    <select 
      :value="appearance.appearance.value"
      @change="appearance.updateAppearance($event.target.value)"
    >
      <option 
        v-for="theme in appearance.availableThemes" 
        :key="theme" 
        :value="theme"
      >
        {{ theme }}
      </option>
    </select>
  </div>
</template>
```

### useErrorHandler

Comprehensive error handling and display:

```vue
<script setup lang="ts">
import { useErrorHandler, useToast, withErrorHandling } from '@/composables/useError';

const errorHandler = useErrorHandler();
const toast = useToast();

const handleAsyncOperation = async () => {
  await withErrorHandling(async () => {
    // Your async operation
    await api.post('/data');
  }, 'Save data', { showToast: true });
};
</script>

<template>
  <!-- Error Display -->
  <div v-for="error in errorHandler.activeErrors()" :key="error.id">
    <div :class="`alert alert-${error.severity}`">
      {{ error.message }}
      <button @click="errorHandler.dismissError(error.id)">×</button>
    </div>
  </div>
  
  <button @click="handleAsyncOperation">
    Save Data
  </button>
</template>
```

## Utilities

### Enhanced utils.ts

Comprehensive utility functions:

```typescript
import { 
  cn, formatNumber, formatCurrency, formatDate, formatRelativeDate,
  debounce, throttle, deepClone, isEmpty, generateId,
  capitalize, titleCase, slugify, truncate,
  parseCoordinates, calculateDistance,
  validateNigerianPhone, formatNigerianPhone,
  storage, url, array
} from '@/lib/utils';

// Class name combination
const classes = cn('btn', 'btn-primary', { 'btn-disabled': disabled });

// Number formatting
const price = formatCurrency(1500); // ₦1,500.00
const date = formatRelativeDate(new Date()); // "2 hours ago"

// Debounced search
const debouncedSearch = debounce((query: string) => {
  // Search logic
}, 300);

// Phone validation
const isValid = validateNigerianPhone('08123456789'); // true
const formatted = formatNigerianPhone('08123456789'); // "0812 345 6789"

// Storage utilities
storage.set('user', { name: 'John' });
const user = storage.get('user');

// URL utilities
const apiUrl = url.build('/api/search', { q: 'lagos', limit: 10 });
const params = url.parseQuery('?page=1&limit=10'); // { page: '1', limit: '10' }

// Array utilities
const unique = array.unique([1, 2, 2, 3]); // [1, 2, 3]
const chunks = array.chunk([1, 2, 3, 4, 5], 2); // [[1, 2], [3, 4], [5]]
const grouped = array.groupBy(users, 'role'); // { admin: [...], user: [...] }
```

## PHP Services

### Permission Services

Reusable permission management:

```php
<?php

use App\Services\{
    PermissionRegistry,
    SimplePermissionService,
    StatePermissionService,
    ResourcePermissionService
};

// In GateRegistrar::register()
$registry = PermissionRegistry::create();

// Simple permissions
$registry->add(SimplePermissionService::create('manage_users'));

// Hierarchical permissions
$registry->add(new StatePermissionService());

// Resource-based permissions with ownership
$registry->add(ResourcePermissionService::create(
    'edit_posts',
    Post::class,
    'user_id' // owner field
));

$registry->register();
```

### Seeder Services

Flexible data seeding:

```php
<?php

use App\Services\SeederServiceFactory;
use App\Models\State;

// From CSV file
SeederServiceFactory::fromCsv(State::class, 'data/states.csv')
    ->setColumnMapping([
        'state_name' => 'name',
        'state_code' => 'code'
    ])
    ->setChunkSize(500)
    ->seed();

// From JSON file
SeederServiceFactory::fromJson(Lga::class, 'data/lgas.json')->seed();

// From array (backward compatibility)
SeederServiceFactory::fromArray(Ward::class, $wardData)->seed();
```

## Usage Examples

### Complete Form Component

```vue
<template>
  <form @submit.prevent="form.submit" class="space-y-4">
    <!-- Location Selector -->
    <LocationSelector 
      v-model="locationSelection"
      :error="form.errors.location"
    />
    
    <!-- Dynamic Fields -->
    <DynamicFields 
      v-model="form.data.customFields"
      :validation-rules="fieldRules"
    />
    
    <!-- Submit Button -->
    <SubmitButton 
      :loading="form.isSubmitting"
      :disabled="!form.isValid"
    >
      Save Record
    </SubmitButton>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@/composables/useForm';
import { useLocationSelector } from '@/composables/useLocationSelector';

const locationSelector = useLocationSelector();
const form = useForm({
  initialData: { /* ... */ },
  validationRules: { /* ... */ },
  onSubmit: async (data) => { /* ... */ }
});
</script>
```

### API Integration

```typescript
// Custom API service
class ProjectApiService extends ApiService {
  async getProjects(): Promise<Project[]> {
    return this.get('/api/projects', { cache: true });
  }
  
  async createProject(data: CreateProjectData): Promise<Project> {
    return this.post('/api/projects', data);
  }
}

const projectApi = new ProjectApiService();
```

## Best Practices

1. **Type Safety**: Always use TypeScript interfaces for data structures
2. **Error Handling**: Use `withErrorHandling` for async operations
3. **Caching**: Enable API caching for static data (states, LGAs)
4. **Validation**: Use predefined validation rules from `useForm`
5. **Composable Reuse**: Extract common logic into composables
6. **Consistent UI**: Use utility classes with `cn()` function

## Migration Guide

To migrate existing components to use these reusable utilities:

1. Replace direct axios calls with API services
2. Replace manual form handling with `useForm`
3. Replace hardcoded location logic with `useLocationSelector`
4. Add proper error handling with `useErrorHandler`
5. Use utility functions instead of custom implementations

This documentation provides a foundation for building maintainable, reusable components that other developers can easily understand and extend.