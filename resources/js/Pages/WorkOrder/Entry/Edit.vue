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
  workOrderEntry: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  name: props.workOrderEntry.name,
  description: props.workOrderEntry.description,
  started_at: props.workOrderEntry.started_at,
  ended_at: props.workOrderEntry.ended_at,
});

const submit = () => {
  form.patch(route('work-orders.entries.update', [props.workOrder.id, props.workOrderEntry.id]));
};
</script>

<template>
  <AppLayout title="Edit Entry">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-h2 font-bold text-text-primary tracking-wide-modern">Edit Entry</h1>
          <p class="text-sm text-text-secondary mt-1">{{ workOrder.name }}</p>
        </div>
        <LinkButton prefetch :href="route('work-orders.show', workOrder.id)" variant="secondary">
          Back to work order
        </LinkButton>
      </div>

      <Card>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-text-secondary mb-1">Name</label>
            <Input v-model="form.name" :error="form.errors.name" />
          </div>

          <div>
            <label class="block text-sm font-medium text-text-secondary mb-1">Description</label>
            <Textarea v-model="form.description" :error="form.errors.description" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-text-secondary mb-1">Started At</label>
              <Input v-model="form.started_at" type="datetime-local" :error="form.errors.started_at" />
            </div>

            <div>
              <label class="block text-sm font-medium text-text-secondary mb-1">Ended At</label>
              <Input v-model="form.ended_at" type="datetime-local" :error="form.errors.ended_at" />
            </div>
          </div>

          <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
              Update Entry
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
