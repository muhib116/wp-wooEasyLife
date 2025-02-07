<template>
    <select
        class="max-w-full bg-transparent outline-none"
        :class="inputClass"
        v-model="_modelValue"
        @change="onChange"
    >
        <option :value="null">{{ defaultOption }}</option>
        <option
            v-for="(item, index) in options"
            :key="index"
            :value="_getValue(item, index)"
        >
            {{ item[itemValue] }}
        </option>
    </select>
</template>

<script setup lang="ts">
    interface Props {
        options: Array<object>
        modelValue: any
        inputClass?: string
        defaultOption?: string
        itemValue?: string
        itemKey?: string
        returnType?: 'id' | 'index' | 'object'
        wrapperClass?: string
        iconClass?: string
    }
    const props = withDefaults(
        defineProps<Props>(),
        {
            itemValue: 'title',
            itemKey: 'id',
            defaultOption: 'Select',
        }
    )
    
    defineOptions({
        name: 'Native Select',
    })
    const emit = defineEmits(['onChange'])
    const _modelValue = defineModel()
    const _getValue = (option: object, index: number) => {
        if (props.returnType === 'index') {
            return index
        }
        if (props.returnType === 'object') {
            return option
        }
        return option[props.itemKey]
    }
    const onChange = () => {
        emit('onChange', _modelValue.value)
    }
</script>
