<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';

const form = useForm({
  name: '',
  email: '',
  email_confirmation: '',
  password: '',
});

const emailError = ref('');

const emailsMatch = computed(() => {
  if (form.email_confirmation === '') return true;
  return form.email === form.email_confirmation;
});

const submit = () => {
  // Validar que los emails coincidan
  if (!emailsMatch.value) {
    emailError.value = 'The email addresses do not match';
    return;
  }

  emailError.value = '';
  form.post(route('register'));
};
</script>

<template>
  <div class="min-h-screen bg-dark-primary flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-h2 font-bold text-text-primary tracking-wide-modern">Create account</h1>
        <p class="mt-2 text-sm text-text-secondary">Sign up to get started</p>
      </div>

      <Card>
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="name" class="block text-sm font-medium text-text-secondary mb-1">
              Your name
            </label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="Alex Thompson"
              :error="form.errors.name"
              required
            />
          </div>

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
            <label for="email_confirmation" class="block text-sm font-medium text-text-secondary mb-1">
              Confirm email
            </label>
            <Input
              id="email_confirmation"
              v-model="form.email_confirmation"
              type="email"
              placeholder="your@email.com"
              :error="emailError || (!emailsMatch ? 'The email addresses do not match' : '')"
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
              placeholder="Minimum 8 characters"
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
            {{ form.processing ? 'Creating account...' : 'Create account' }}
          </Button>

          <div class="text-center text-sm text-text-secondary">
            Already have an account?
            <a :href="route('login')" class="text-accent font-medium hover:underline">
              Sign in
            </a>
          </div>
        </form>
      </Card>
    </div>
  </div>
</template>
