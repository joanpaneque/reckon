<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import LinkButton from '@/Components/LinkButton.vue';

const form = useForm({
  name: '',
  hour_price: 30.00,
});

const submit = () => {
  form.post(route('work-orders.store'));
};
</script>

<template>
  <AppLayout title="Create work order">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-medium text-gray-900">Create work order</h1>
        <LinkButton :href="route('work-orders.index')" variant="secondary">
          Back to list
        </LinkButton>
      </div>

      <Card>
        <form @submit.prevent="submit" class="space-y-4">
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

          <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
              Create work order
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
