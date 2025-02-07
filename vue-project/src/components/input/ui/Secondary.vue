<template>
    <Base
        v-model="localValue"
        wrapperClass="block relative"
        labelClass="text-primary-one block mb-3 text-lg font-normal"
        :labelStyle="labelStyle"
        inputClass="text-primary-one text-lg font-light border border-secondary-five px-6 py-3 block w-full rounded-lg h-[50px] disabled:bg-gray-100 disabled:opacity-60"
        :inputStyle="inputStyle"
        :bgStyle="bgStyle"
        iconClass="absolute top-1/2 right-2 transform -translate-y-1/2"
        :iconSource="iconSource"
        :iconName="iconName"
        :label="label"
        @onIconClick="$emit('onIconClick')"
    />
</template>

<script setup>
    import Base from '../Base.vue'

    const props = defineProps({
        iconSource: String,
        labelStyle: Object,
        inputStyle: Object,
        bgStyle: Object,
        iconName: String,
        class: String,
        label: String,
    })
    const localValue  = defineModel();
</script>