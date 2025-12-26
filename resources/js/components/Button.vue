<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2',
            variant === 'primary' ? 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' : '',
            variant === 'danger' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : '',
            variant === 'secondary' ? 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500' : '',
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

