<script setup>
defineProps({
  modelValue: [String, Number],
  placeholder: String,
  error: String,
  rows: {
    type: Number,
    default: 3,
  },
});

defineEmits(['update:modelValue']);
</script>

<template>
  <div>
    <textarea
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :rows="rows"
      :class="[
        'input-modern block w-full resize-none',
        error ? 'border-red-600 focus:border-red-600 focus:ring-red-600' : ''
      ]"
    ></textarea>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>
