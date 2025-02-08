<template>
    <div 
        class="relative"
        v-click-outside="() => {
            toggleSearch = false
            toggleFilter = false
        }"
    >
        <div class="flex justify-between items-center px-4 relative mb-4">
            <Button.Native
                @click="toggleFilter = true"
            >
                <Icon
                    name="PhFunnelSimple"
                    size="20"
                    weight="bold"
                />
            </Button.Native>
            <Button.Native
                @click="toggleSearch = true"
            >
                <Icon
                    name="PhMagnifyingGlass"
                    size="20"
                    weight="bold"
                />
            </Button.Native>
        </div>
        <div 
            class="absolute -top-6 rounded-t-lg p-4 min-h-[100px] inset-x-0 z-30 bg-white"
            v-if="toggleSearch"
        >
            <MobileProductSearchBox
                v-if="toggleSearch"
                @close="toggleSearch = false"
            />
        </div>

        <MobileProductFilterBox
            v-if="toggleFilter"
            @close="toggleFilter = false"
        />

        <div class="px-4 flex items-center gap-2 mb-3">
            <StatusChangeDropdown />
            <Pagination />
        </div>
    </div>
</template>

<script setup lang="ts">
    import StatusChangeDropdown from '../fragments/StatusChangeDropdown.vue'
    import Pagination from '../fragments/Pagination.vue'
    import MobileProductSearchBox from './MobileProductSearchBox.vue'
    import MobileProductFilterBox from './MobileProductFilterBox.vue'
    import { ref } from 'vue'
    import {
        Icon,
        Button
    } from '@components'

    const toggleFilter = ref(false)
    const toggleSearch = ref(false)
</script>