<template>
    <Base
        inputClass="py-2 px-3 pr-3 rounded-sm block w-full bg-transparent border border-secondary-five"
        :options="options"
        v-model="_modelValue"
        v-bind="$attrs"
    />
</template>

<script setup lang="ts">
    import Base from '../Base.vue'

    defineOptions({
        name: 'PrimarySelect',
        inheritAttrs: false,
    })

    interface Props {
        options: Array<object>
    }
    defineProps<Props>()
    const _modelValue = defineModel()
</script>
