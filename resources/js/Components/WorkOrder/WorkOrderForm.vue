<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import AutocompleteInput from '@/Components/AutocompleteInput.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  workOrder: {
    type: Object,
    default: null,
  },
  submitRoute: {
    type: String,
    required: true,
  },

  method: {
    type: String,
    default: 'post',
    validator: (value) => ['post', 'patch', 'put'].includes(value),
  },
  submitLabel: {
    type: String,
    default: 'Submit',
  },
});

// Hardcoded friends list
const friendsList = usePage().props.friends;

const searchTerm = ref('');

const form = useForm({
  name: props.workOrder?.name || '',
  hour_price: props.workOrder?.hour_price || 30.00,
  shared_with: props.workOrder?.shared_with.map(friend => {
    return {
      ...friend,
      permission: friend.pivot.permission,
    };
  }) || [],
});

const filterFriends = (friend, search) => {
  const searchLower = search.toLowerCase();
  return (
    friend.email.toLowerCase().includes(searchLower) ||
    friend.name.toLowerCase().includes(searchLower)
  );
};

const displayFriend = (friend) => {
  return `${friend.email} (${friend.name})`;
};

const addFriend = (friend) => {
  // Check if friend is already added
  const alreadyAdded = form.shared_with.some(
    (f) => f.email === friend.email
  );

  if (!alreadyAdded) {
    form.shared_with.push({
      id: friend.id,
      email: friend.email,
      name: friend.name,
      permission: 'view',
    });
  }

  searchTerm.value = '';
};

const removeFriend = (index) => {
  form.shared_with.splice(index, 1);
};

const submit = () => {
  if (props.method === 'post') {
    form.post(props.submitRoute);
  } else if (props.method === 'patch') {
    form.patch(props.submitRoute);
  } else if (props.method === 'put') {
    form.put(props.submitRoute);
  }
};
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
      <Input v-model="form.name" :error="form.errors.name" />
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Hour Price (€)</label>
      <Input
        v-model="form.hour_price"
        :error="form.errors.hour_price"
        type="number"
        step="0.01"
        min="0"
      />
    </div>

    <!-- Share with friends section -->
    <div class="border-t pt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Share with friends</h3>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Add friend
          </label>
          <AutocompleteInput
            v-model="searchTerm"
            :options="friendsList"
            :filter-function="filterFriends"
            :display-format="displayFriend"
            :max-results="3"
            :show-on-focus="true"
            placeholder="Search by email or name..."
            @select="addFriend"
          />
          <p v-if="form.errors.shared_with" class="mt-1 text-sm text-red-600">
            {{ form.errors.shared_with }}
          </p>
        </div>

        <!-- List of shared friends -->
        <div v-if="form.shared_with.length > 0" class="space-y-2">
          <div
            v-for="(friend, index) in form.shared_with"
            :key="friend.email"
            class="flex items-center gap-3 p-3 border border-gray-200 hover:border-gray-300 transition-colors"
          >
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ friend.name }}</p>
              <p class="text-xs text-gray-500">{{ friend.email }}</p>
            </div>

            <select
              v-model="friend.permission"
              class="py-1.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="view">View</option>
              <option value="edit">Edit</option>
            </select>

            <button
              type="button"
              @click="removeFriend(index)"
              class="px-3 py-1.5 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 transition-colors"
            >
              Remove
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-end pt-4 border-t">
      <Button type="submit" :disabled="form.processing">
        {{ submitLabel }}
      </Button>
    </div>
  </form>
</template>
