<template>
    <div v-if="data?.length" class="flex justify-center">
        <button 
            v-for="(item, index) in data"
            :key="index"
            class="capitalize px-5 py-2 font-bold"
            :class="[
                item.slug == activeTab ? 'bg-white rounded-t-lg text-orange-500' : 'bg-gray-100 text-gray-400',
                hasUnsavedData ? 'opacity-40' : ''
            ]"
            @click="$emit('onTabChange', item.slug)"
            :title="item.title"
        >
            <img
                v-if="courierConfigs[item.slug]?.logo"
                class="w-[80px]"
                :src="courierConfigs[item.slug].logo"
            />
            <span v-else>{{ item.title }}</span>
        </button>
    </div>
</template>

<script setup lang="ts">
import { inject } from 'vue'

    withDefaults(
        defineProps<{
            data: {title: string, slug: string}[],
            hasUnsavedData: boolean
            activeTab: string
        }>(),
        {
            hasUnsavedData: false
        }
    )

    const { courierConfigs } = inject('useCourierConfig')
</script>