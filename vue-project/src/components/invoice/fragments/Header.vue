<template>
    <div class="grid grid-cols-2 items-center">
        <div v-if="configData.invoice_logo">
            <img 
                :src="configData.invoice_logo" 
                alt="company-logo" 
                height="100" 
                width="100"
            >
        </div>
    
        <div class="text-right">
            <p>
                {{ configData.invoice_company_name }}
            </p>
            <p class="text-gray-500 text-sm">
                {{ configData.invoice_email }}
            </p>
            <p class="text-gray-500 text-sm mt-1">
                {{ configData.invoice_phone }}
            </p>
        </div>
    </div>
</template>

<script lang="ts" setup>
    import { inject } from 'vue'

    const {configData} = inject('configData')
</script>