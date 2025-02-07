<template>
    <label 
        class="inline-flex items-center cursor-pointer select-none"
    >
        <input 
            type="checkbox"
            class="sr-only peer"
            :checked="+modelValue"
            @change="event => handleChange(event.target.checked)"
        >
        <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
    </label>
</template>

<script setup lang="ts">
    const modelValue = defineModel()

    const emit = defineEmits(['onInput'])
    const handleChange = (value: boolean) => {
        modelValue.value = +value
        emit('onInput', value)
    }
</script>