<template>
    <input 
        class="block placeholder:text-inherit placeholder:opacity-40 outline-none"
        v-model="localValue"
        v-bind="$attrs"
    />
</template>

<script setup>
    const localValue = defineModel();
</script>