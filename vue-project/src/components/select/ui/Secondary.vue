<template>
    <Base
        labelClass="text-primary-one block mb-3 text-lg"
        inputClass="h-[50px] !text-lg font-light border border-secondary-five px-6 py-3 block w-full rounded-lg disabled:bg-gray-100 disabled:opacity-60"
        :options="options"
        v-model="_modelValue"
        v-bind="$attrs"
    />
</template>

<script setup lang="ts">
    import Base from '../Base.vue'

    defineOptions({
        name: 'Secondary Select',
        inheritAttrs: false,
    })

    interface Props {
        options: Array<object>
    }
    
    defineProps<Props>()
    const _modelValue = defineModel()
</script>
