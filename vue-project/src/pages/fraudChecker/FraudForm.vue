<template>
    <div class="bg-red-100 flex rounded-l shadow mb-4 text-lg">
        <Input.Native 
            name="phone"
            class="flex-1 w-full px-5"
            placeholder="01xxxxxxxx"
            v-model="modelValue"
        />
        <Button.Primary
            @onClick="(btn) => $emit('onSubmit', btn)"
            class="bg-orange-500 !text-sm !font-medium"
            :class="normalizePhoneNumber(modelValue).length == 11 ? '' : 'opacity-80 pointer-events-none'"
        >
            Check Now
        </Button.Primary>
    </div>
</template>

<script setup lang="ts">
    import { Input, Button } from '@components'
    import { normalizePhoneNumber } from '@/helper'

    const modelValue = defineModel()
</script>