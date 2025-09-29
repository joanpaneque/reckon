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
        'block w-full px-3 py-2 border text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 resize-none',
        error ? 'border-red-300 focus:border-red-500' : 'border-gray-300 focus:border-gray-500'
      ]"
    ></textarea>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>
