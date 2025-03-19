<template>
    <a 
        target="_blank"
        :href="`https://web.whatsapp.com/send/?phone=88${phone_number}&text=Hi!&type=phone_number&app_absent=0`" 
        class="hidden xl:grid items-center size-6 rounded-sm shadow  place-content-center border border-[currentColor] bg-green-500 text-white hover:text-green-500 hover:bg-white active:text-green-500"
    >
        <Icon
            name="PhWhatsappLogo"
            size="20"
            weight="fill"
        />
    </a>
    <a 
        target="_blank"
        :href="`https://api.whatsapp.com/send/?phone=88${phone_number}&text=Hi!&type=phone_number&app_absent=0`" 
        class="grid xl:hidden items-center size-6 rounded-sm shadow place-content-center border border-[currentColor] bg-green-500 text-white hover:text-green-500 hover:bg-white active:text-green-500"
    >
        <Icon
            name="PhWhatsappLogo"
            size="20"
            weight="fill"
        />
    </a>
</template>

<script setup lang="ts">
    import { Icon } from '@components'

    defineProps<{
        phone_number: string
    }>()
</script>