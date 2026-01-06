<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors',
            variant === 'primary' ? 'bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:ring-indigo-500' : '',
            variant === 'danger' ? 'bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 focus:ring-red-500' : '',
            variant === 'secondary' ? 'bg-gray-600 hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600 focus:ring-gray-500' : '',
            (disabled || loading) ? 'opacity-50 cursor-not-allowed' : ''
        ]"
        @click="$emit('click')"
    >
        <span v-if="loading" class="mr-2">Carregando...</span>
        <slot></slot>
    </button>
</template>

<script setup>
defineProps({
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary' },
    disabled: Boolean,
    loading: Boolean
});

defineEmits(['click']);
</script>


