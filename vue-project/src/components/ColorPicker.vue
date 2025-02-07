<template>
    <div>
        {{ label }}
        <div class="flex flex-wrap gap-4">
            <span
                v-for="(color, index) in colors"
                :key="index"
                :style="{backgroundColor: color}"
                class="block size-8 rounded-full border-2 cursor-pointer flex-shrink-0 flex-grow-0"
                :class="color == modelValue ? 'border-orange-400 scale ring-2' : 'border-white shadow'"
                @click="() => {
                    modelValue = color
                    $emit('onChange', color)
                }"
            ></span>
    
            <label 
                class="block size-8 rounded-full border-2 border-white shadow relative cursor-pointer flex-shrink-0 flex-grow-0"
                :style="{backgroundColor: modelValue, color: getContrastColor(modelValue)}"
            >
                <Input.Native
                    class="absolute inset-1/2 rounded-full opacity-0 -translate-x-1/2 -translate-y-1/2"
                    label="Color"
                    type="color"
                    v-model="modelValue"
                    @input="$emit('onChange', modelValue)"
                />
                <Icon
                    class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
                    name="PhPlus"
                    size="16"
                    weight="bold"
                />
            </label>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Input, Icon } from '@components'
    import { getContrastColor } from '@/helper'
    import { computed } from 'vue'

    const props = withDefaults(defineProps<{
        colors?: [],
        label?: string,
        selectedColor: string,
        modelValue: String
    }>(), {
        colors: [
            '#f1c40f',
            '#f39c12',
            '#27ae60',
            '#e74c3c',
            '#8e44ad',
            '#2ecc71',
            '#3498db',
            '#2c3e50'
        ],
        label: 'Select Color'
    })

    const emit = defineEmits(['update:modelValue'])
    const modelValue = computed({
        get(){
            return props.modelValue
        },
        set(newValue) {
            emit('update:modelValue', newValue)
        }
    })
</script>