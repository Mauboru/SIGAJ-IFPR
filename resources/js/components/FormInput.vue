<template>
    <div>
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700">
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
                'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2',
                error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300'
            ]"
        />
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
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

