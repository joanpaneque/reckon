<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import LinkButton from '@/Components/LinkButton.vue';

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('friends.store'));
};
</script>

<template>
  <AppLayout title="Add friend">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-medium text-gray-900">Add friend</h1>
        <LinkButton :href="route('friends.index')" variant="secondary">
          Back to list
        </LinkButton>
      </div>

      <Card>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Friend's Email</label>
            <Input
              v-model="form.email"
              :error="form.errors.email"
              type="email"
              placeholder="friend@example.com"
            />
            <p class="mt-1 text-sm text-gray-500">
              Enter the email address of the person you want to add as a friend.
            </p>
          </div>

          <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
              Send friend request
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
