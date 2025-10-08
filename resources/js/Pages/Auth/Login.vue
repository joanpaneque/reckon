<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';

const form = useForm({
  email: '',
  password: '',
});

const submit = () => {
  form.post(route('login'));
};
</script>

<template>
  <div class="min-h-screen bg-dark-primary flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-h2 font-bold text-text-primary tracking-wide-modern">Sign In</h1>
        <p class="mt-2 text-sm text-text-secondary">Access your account</p>
      </div>

      <Card>
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-text-secondary mb-1">
              Email
            </label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="your@email.com"
              :error="form.errors.email"
              required
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-text-secondary mb-1">
              Password
            </label>
            <Input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="Your password"
              :error="form.errors.password"
              required
            />
          </div>

          <Button
            type="submit"
            variant="primary"
            :class="['w-full', form.processing && 'opacity-50 cursor-not-allowed']"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Signing in...' : 'Sign In' }}
          </Button>

          <div class="text-center text-sm text-text-secondary">
            Don't have an account?
            <a :href="route('register')" class="text-accent font-medium hover:underline">
              Create one
            </a>
          </div>
        </form>
      </Card>
    </div>
  </div>
</template>
