<template>
    <div class="print:hidden">
        <Heading
            title="Recent Orders"
            class="mb-4 px-6"
        />
        <TableFilter />
        <TableHeaderAction />
    
        <Pagination class=" mt-2 mb-4 md:mb-2 px-4">
            <template #leftSide>
                <StatusChangeDropdown />
                <SearchBox />
            </template>
        </Pagination>
    </div>
</template>

<script setup lang="ts">
    import { Heading } from '@components'
    import TableFilter from '../fragments/TableFilter.vue'
    import StatusChangeDropdown from '../fragments/StatusChangeDropdown.vue'
    import TableHeaderAction from '../TableHeaderAction.vue'
    import Pagination from '../fragments/Pagination.vue'
    import SearchBox from '../fragments/SearchBox.vue'
</script>