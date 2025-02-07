<template>
    <div class="relative">
        <Loader
            class="absolute inset-1/2 top-20 -translate-x-1/2 -translate-y-1/2"
            :active="isLoading"
        />
        <TabHeader
            :activeTab="activeTab"
            :data="tabs"
            :hasUnsavedData="hasUnsavedData"
            @onTabChange="tabChange"
        />
        <Card.Native
            class="bg-white min-h-[200px] h-full w-full p-4 rounded-t-none py-10"
        >
            <component
                :is="components[activeTab]"
            />
        </Card.Native>
    </div>
</template>

<script setup lang="ts">
    import { Card, Loader } from '@components'
    import { useCustomStatus } from './useCustomStatus'
    import { provide } from 'vue'
    import TabHeader from '@/pages/config/fragments/TabHeader.vue'

    const _useCustomStatus = useCustomStatus()
    const { 
        isLoading, 
        activeTab, 
        tabs, 
        hasUnsavedData, 
        components,
        tabChange
    } = _useCustomStatus

    provide('useCustomStatus', _useCustomStatus)
</script>