<template>
    <div :class="wrapperClass">
        <Label
            v-if="label"
            :class="labelClass"
            :style="{ ...labelStyle }"
        >
            {{ label }}
        </Label>

        <div class="relative">
            <span
                class="absolute inset-0"
                :style="{ ...bgStyle }"
            ></span>
            <div
                class="w-full relative z-10"
                :class="inputClass"
            >
                <Select.Native
                    v-bind="$attrs"
                    :options="options"
                    v-model="_modelValue"
                    :iconClass="iconClass"
                    :itemValue="itemValue"
                    class="appearance-none w-full"
                    :style="{ ...inputStyle }"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
    import { Icon, Select } from '@components'
    import Label from './Label.vue'

    defineOptions({
        name: 'Base Select',
        inheritAttrs: false,
    })

    defineProps({
        label: String,
        options: Array,
        wrapperClass: String,
        inputClass: String,
        iconClass: String,
        itemValue: String,
        labelClass: String,
        labelStyle: Object,
        inputStyle: Object,
        bgStyle: Object,
    })

    const _modelValue = defineModel()
</script>
