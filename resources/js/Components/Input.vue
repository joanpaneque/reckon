<script setup>
defineProps({
  modelValue: [String, Number],
  type: {
    type: String,
    default: 'text',
  },
  placeholder: String,
  error: String,
});

defineEmits(['update:modelValue']);
</script>

<template>
  <div>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :type="type"
      :placeholder="placeholder"
      :class="[
        'block w-full px-3 py-2 border text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500',
        error ? 'border-red-300 focus:border-red-500' : 'border-gray-300 focus:border-gray-500'
      ]"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>
