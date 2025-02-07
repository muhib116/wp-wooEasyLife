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
            class="min-h-[200px] h-full w-full p-4 rounded-t-none py-10"
        >
            <component
                :is="components[activeTab]"
            />
        </Card.Native>
    </div>
</template>

<script setup lang="ts">
    import { Card, Loader } from '@components'
    import { useSms } from './useSms'
    import { provide } from 'vue'
    import TabHeader from '@/pages/config/fragments/TabHeader.vue'

    const _useSms = useSms()
    const {
        isLoading,
        components,
        activeTab,
        tabs,
        hasUnsavedData,
        tabChange
    } = _useSms

    provide('useSms', _useSms)
</script>