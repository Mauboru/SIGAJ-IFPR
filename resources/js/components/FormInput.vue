<template>
    <div>
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder"
            :required="required"
            :class="[
                'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2 transition-colors',
                error ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400' : ''
            ]"
        />
        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    id: String,
    label: String,
    type: { type: String, default: 'text' },
    modelValue: [String, Number],
    placeholder: String,
    required: Boolean,
    error: String
});

defineEmits(['update:modelValue']);
</script>

