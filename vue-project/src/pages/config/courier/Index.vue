<template>
    <div class="relative min-h-[300px]">
        <Loader
            class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
            :active="isLoading"
        />
        <TabHeader
            :activeTab="activeTab"
            :data="courierCompanyNames"
            :hasUnsavedData="hasUnsavedData"
            @onTabChange="changeTab"
        />
        <Card.Native
            v-if="courierCompanyNames.length"
            class="bg-white h-full w-full p-4 rounded-t-none"
        >
            <Form />
        </Card.Native>
    </div>
</template>

<script setup lang="ts">
    import { Card, Loader } from '@components'
    import { inject } from 'vue'
    import Form from './fragments/Form.vue'
    import TabHeader from '@/pages/config/fragments/TabHeader.vue'


    const { 
        isLoading, 
        activeTab, 
        courierCompanyNames, 
        hasUnsavedData,
        changeTab
    } = inject('useCourierConfig', {})
</script>