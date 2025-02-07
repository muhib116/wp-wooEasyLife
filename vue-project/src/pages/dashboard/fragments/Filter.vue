<template>
    <Card.Native>
        <div class="grid grid-cols-3  gap-4">
            <div class="grid">
                Filter by
                <label class="font-light border px-3 py-2 rounded">
                    <select 
                        class="outline-none bg-transparent w-full !border-none focus:outline-none"
                        v-model="selectedFilterOption"
                        @change="getData"
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
            </div>

            <div v-if="selectedFilterOption == 'custom'" class="grid">
                Start date
                <label class="font-light border px-3 py-2 rounded">
                    <input
                        class="outline-none bg-transparent w-full !border-none focus:outline-none"
                        type="date"
                        v-model="customDates.start_date"
                    />
                </label>
            </div>

            <div v-if="selectedFilterOption == 'custom'" class="grid">
                End date
                <label class="font-light border px-3 py-2 rounded">
                    <input
                        class="outline-none bg-transparent w-full !border-none focus:outline-none"
                        type="date"
                        v-model="customDates.end_date"
                    />
                </label>
            </div>
        </div>

        <Button.Primary 
            v-if="selectedFilterOption == 'custom'" 
            class="mt-4 ml-auto"
            @onClick="getDataByCustomFilter"
        >
            Filter
        </Button.Primary>
    </Card.Native>
</template>

<script setup lang="ts">
    import { Button, Card } from '@/components'
    import { inject } from 'vue'

    const {
        filterOptions,
        selectedFilterOption,
        getData,
        customDates,
        getDataByCustomFilter
    } = inject('useDashboard')
</script>