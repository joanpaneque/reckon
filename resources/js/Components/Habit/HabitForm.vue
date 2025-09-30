<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import AutocompleteInput from '@/Components/AutocompleteInput.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  habit: {
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

// Friends list from page props
const page = usePage();
const friendsList = computed(() => page.props.friends || []);

const searchTerm = ref('');

// Preset pastel colors for calendar - Professional palette from design resources
const presetColors = [
  '#cdb4db', '#ffc8dd', '#ffafcc', '#bde0fe', '#a2d2ff', '#fdffb6',
  '#caffbf', '#9bf6ff', '#a0c4ff', '#ffc6ff', '#a7bed3', '#c6e2e9',
  '#f1ffc4', '#ffcaaf', '#dab894', '#70d6ff', '#ff70a6', '#ff9770',
  '#ffd670', '#e9ff70', '#ff6961', '#77dd77', '#fdfd96', '#84b6f4',
];

const form = useForm({
  name: props.habit?.name || '',
  description: props.habit?.description || '',
  start_date: props.habit?.start_date || '',
  end_date: props.habit?.end_date || '',
  frequency: props.habit?.frequency || 'everyday',
  color: props.habit?.color || '#93C5FD',
  shared_with: props.habit?.shared_with || [],
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
    });
  }

  searchTerm.value = '';
};

const removeFriend = (index) => {
  form.shared_with.splice(index, 1);
};

const setToday = () => {
  const today = new Date();
  const formattedDate = today.toISOString().split('T')[0];
  form.start_date = formattedDate;
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
      <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
      <textarea
        v-model="form.description"
        class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        :class="{ 'border-red-500': form.errors.description }"
        rows="3"
        placeholder="Optional description..."
      />
      <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
        {{ form.errors.description }}
      </p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
      <div class="flex gap-2">
        <Input
          v-model="form.start_date"
          :error="form.errors.start_date"
          type="date"
          class="flex-1"
        />
        <button
          type="button"
          @click="setToday"
          class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
        >
          Now
        </button>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
      <Input
        v-model="form.end_date"
        :error="form.errors.end_date"
        type="date"
      />
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
      <select
        v-model="form.frequency"
        class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        :class="{ 'border-red-500': form.errors.frequency }"
      >
        <option value="everyday">Everyday</option>
        <option value="weekdays">Weekdays</option>
        <option value="weekends">Weekends</option>
      </select>
      <p v-if="form.errors.frequency" class="mt-1 text-sm text-red-600">
        {{ form.errors.frequency }}
      </p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
      
      <!-- Preset Colors Grid -->
      <div style="display: grid; grid-template-columns: repeat(6, 2rem); gap: 0.25rem; margin-bottom: 0.75rem;">
        <button
          v-for="color in presetColors"
          :key="color"
          type="button"
          @click="form.color = color"
          :style="{ backgroundColor: color }"
          :class="[
            'w-8 h-8 rounded border-2 transition-all hover:scale-110',
            form.color === color ? 'border-gray-900 ring-2 ring-gray-900 ring-offset-2' : 'border-gray-300'
          ]"
          :title="color"
        />
      </div>

      <!-- Color Picker -->
      <div class="flex items-center gap-3">
        <div class="flex-1">
          <input
            type="color"
            v-model="form.color"
            class="w-full h-10 border border-gray-300 rounded cursor-pointer"
          />
        </div>
        <input
          type="text"
          v-model="form.color"
          placeholder="#93C5FD"
          maxlength="7"
          class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm uppercase w-28"
          :class="{ 'border-red-500': form.errors.color }"
        />
        <div
          :style="{ backgroundColor: form.color }"
          class="w-10 h-10 border-2 border-gray-300 rounded"
        />
      </div>
      <p v-if="form.errors.color" class="mt-1 text-sm text-red-600">
        {{ form.errors.color }}
      </p>
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
