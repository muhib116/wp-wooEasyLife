<template>
    <div :class="class">
        <h3
            v-if="label"
            :class="labelClass"
        >
            {{ label }}
        </h3>
        <div class="relative">
            <input
                :type="inputType"
                :placeholder="placeholder"
                v-model="localValue"
                v-bind="$attrs"
                class="border border-secondary-five bg-transparent rounded py-2 px-[14px] text-sm font-medium w-full"
                :class="[inputClass, !isEmpty(icon) && 'pr-10'].join(' ')"
            />
            <button
                type="button"
                @click="$emit('onIconClick')"
                class="absolute right-3 top-1/2 -translate-y-1/2 hover:opacity-70"
            >
                <component
                    :is="icon"
                    class="w-5 h-5"
                    :class="iconClass"
                />
            </button>
        </div>
    </div>
</template>
<script>
    export default {
        name: 'Input',
        inheritAttrs: false,
    }
</script>
<script setup>
    import { computed, ref } from 'vue'
    import { isEmpty } from 'lodash'

    const props = defineProps({
        inputType: {
            type: String,
            default: 'text',
        },
        placeholder: String,
        label: String,
        modelValue: String,
        class: String,
        inputClass: String,
        labelClass: {
            type: String,
            default: 'fs-13 font-medium text-[#616161] mb-1',
        },
        iconClass: String,
        icon: Object,
    })

    const emit = defineEmits(['update:modelValue', 'input'])
    const localValue = computed({
        get() {
            return props.modelValue
        },
        set(value) {
            emit('update:modelValue', value)
            emit('input', value)
        },
    })
</script>
