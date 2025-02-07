<template>
    <Card.Native>
        <div v-if="showCustomDateInput" class="w-full text-sm flex items-end mb-2 gap-3">
            <div class="flex-1 grid grid-cols-2 gap-3">
                <div class="grid">
                    Start date
                    <label class="font-light border px-3 py-1 rounded-sm">
                        <input
                            class="outline-none bg-transparent w-full !border-none focus:outline-none"
                            type="date"
                            v-model="customDates.start_date"
                        />
                    </label>
                </div>
    
                <div class="grid">
                    End date
                    <label class="font-light border px-3 py-1 rounded-sm">
                        <input
                            class="outline-none bg-transparent w-full !border-none focus:outline-none"
                            type="date"
                            v-model="customDates.end_date"
                        />
                    </label>
                </div>
            </div>

            <Button.Primary
                class="ml-auto w-[118px] text-center justify-center !py-[6px]"
                @click="() => {
                    $emit('dateChange', customDates, selectedStatusOption)
                }"
            >
                Apply Now
            </Button.Primary>
        </div>

        <div class="flex justify-between items-end mb-4">
            <Heading
                :title="title"
                :subtitle="subtitle"
            />

            <div class="flex gap-2 relative">
                <slot name="before-filter"></slot>
                <Loader
                    :active="showStatusFilter && isLoading"
                    class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
                />
                <label
                    v-if="showStatusFilter" 
                    class="font-light border px-2 py-1 rounded-sm"
                >
                    <select 
                        class="outline-none bg-transparent w-full !border-none focus:outline-none"
                        v-model="selectedStatusOption"
                        @change="handleLoadData()"
                    >
                        <option>Select status</option>
                        <option
                            v-for="(option, index) in orderStatuses"
                            :key="index"
                            :value="option.slug"
                        >
                            {{ option.title }}
                        </option>
                    </select>
                </label>

                <label
                    v-if="showDateFilter"
                    class="font-light border px-2 py-1 rounded-sm"
                >
                    <select 
                        class="outline-none bg-transparent w-full !border-none focus:outline-none"
                        v-model="selectedFilterOption"
                        @change="handleLoadData()"
                    >
                        <option
                            v-for="(option, index) in filterOptions"
                            :key="index"
                            :value="option.id"
                        >
                            {{ option.title }}
                        </option>
                    </select>
                </label>
                <slot name="after-filter"></slot>
            </div>
        </div>
        <slot></slot>
    </Card.Native>
</template>

<script setup lang="ts">
    import { Heading, Card, Button, Loader } from '@components'
    import { useDashboard } from '../useDashboard'
    import { onMounted, ref } from 'vue'

    const props = withDefaults(defineProps<{
        title?: string,
        subtitle?: string,
        showStatusFilter?: boolean
        showDateFilter?: boolean
    }>(), {
        showDateFilter: true
    })

    const emit = defineEmits(['dateChange'])

    const showCustomDateInput = ref(false)
    const {
        isLoading,
        orderStatuses,
        filterOptions,
        selectedFilterOption,
        selectedStatusOption,
        customDates,
        getDateRangeFormatted
    } = useDashboard(props)

    const handleLoadData = () => {
        if(selectedFilterOption.value == 'custom'){
            showCustomDateInput.value = true
            return
        }

        showCustomDateInput.value = false
        getDateRangeFormatted(selectedFilterOption.value)
        emit('dateChange', customDates.value, selectedStatusOption.value)
    }

    onMounted(() => {
        getDateRangeFormatted(selectedFilterOption.value)
        emit('dateChange', customDates.value, selectedStatusOption.value)
    })
</script>