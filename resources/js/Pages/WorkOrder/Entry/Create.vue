<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Textarea from '@/Components/Textarea.vue';
import LinkButton from '@/Components/LinkButton.vue';

const props = defineProps({
  workOrder: {
    type: Object,
    required: true,
  },
});
import { ref } from 'vue';

const startNow = ref(true);

const form = useForm({
  name: '',
  description: '',
  started_at: '',
  ended_at: '',
});

const submit = () => {
  if (startNow.value) {
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 19);
    form.started_at = localDateTime;
    form.ended_at = '';
  }
  form.post(route('work-orders.entries.store', props.workOrder.id));
};
</script>

<template>
  <AppLayout title="Create entry">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-medium text-gray-900">Create entry</h1>
          <p class="text-sm text-gray-500 mt-1">{{ workOrder.name }}</p>
        </div>
        <LinkButton :href="route('work-orders.show', workOrder.id)" variant="secondary">
          Back to work order
        </LinkButton>
      </div>

      <Card>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <Input v-model="form.name" :error="form.errors.name" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
            <Textarea v-model="form.description" :error="form.errors.description" />
          </div>

          <div>
            <label class="flex items-center">
              <input v-model="startNow" type="checkbox" class="mr-2" />
              <span class="text-sm font-medium text-gray-700">Start entry now</span>
            </label>
          </div>

          <div v-if="!startNow" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Started At</label>
              <Input v-model="form.started_at" type="datetime-local" :error="form.errors.started_at" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ended At</label>
              <Input v-model="form.ended_at" type="datetime-local" :error="form.errors.ended_at" />
            </div>
          </div>

          <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
              {{ startNow ? 'Start entry' : 'create Entry' }}
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
