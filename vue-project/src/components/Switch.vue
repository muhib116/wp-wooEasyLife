<template>
    <label 
        class="inline-flex items-center cursor-pointer select-none"
        :class="{
            'opacity-90 pointer-events-none': $attrs.disabled || button.isLoading
        }"
    >
        <input 
            v-bind="$attrs"
            type="checkbox"
            class="sr-only peer"
            :checked="+modelValue"
            @change="event => handleChange(event.target.checked)"
        >
        <div 
            class="
                grid peer place-content-center relative w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-green-500 peer-disabled:cursor-not-allowed peer-disabled:opacity-50 transition-colors
                peer-checked:[&>span]:translate-x-full rtl:peer-checked:[&>span]:-translate-x-full 
                peer-checked:[&>span]:text-green-500
            "
        >
            <span class="grid place-content-center peer-focus:outline-none peer-checked:border-white absolute top-[2px] start-[2px] bg-white border-gray-300 border rounded-full h-5 w-5 transition-all">
                <Loader
                    v-if="button.isLoading"
                    size="18"
                    color="inherit"
                />
                <Icon
                    v-else-if="+modelValue"
                    name="PhCheck"
                    size="16"
                    weight="bold"
                />
                <Icon
                    v-else
                    name="PhX"
                    size="14"
                    class="opacity-10"
                />
            </span>
        </div>
    </label>
</template>

<script setup lang="ts">
    import Loader from './loader/Index.vue'
    import Icon from './Icon.vue'
    import { ref } from 'vue'

    const modelValue = defineModel()
    defineOptions({
        inheritAttrs: false
    })

    const button = ref<{
        isLoading: string | boolean
    }>({
        isLoading: false
    })

    const emit = defineEmits(['onInput'])
    const handleChange = (value: boolean) => {
        modelValue.value = +value
        emit('onInput', button.value, value)
    }
</script>