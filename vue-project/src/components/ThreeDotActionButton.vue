<template>
    <button 
        class="mx-auto group relative"
        title="Take action!"
        v-click-outside="()=>toggleActionMenu = false"
        @click="toggleActionMenu = !toggleActionMenu"
    >
        <Icon
            :class="toggleActionMenu ? 'text-orange-500' : ''"
            name="PhDotsThreeOutline"
            size="16"
        />

        <div 
            v-if="toggleActionMenu"
            class="absolute bg-white bottom-0 -translate-y-1/2 right-0 flex shadow rounded overflow-hidden border [&>*+*]:border-l"
        >
            <template
                v-for="(item, index) in actionButtons"
                :ke="index"
            >
                <Button.Native 
                    v-if="item.method"
                    class="actionBtn !px-1 !py-1"
                    :style="{
                        '--backgroundColor': item.color,
                        '--color': getContrastColor(item.color)
                    }"
                    @onClick="item.method"
                >
                    <Icon 
                        :name="item.iconName" 
                        size="20"
                    />
                </Button.Native>
            </template>
        </div>
    </button>
</template>

<script setup lang="ts">
    import { Button, Icon } from '@components'
    import { computed, ref } from 'vue'
    import { getContrastColor } from '@/helper'

    const props = defineProps<{
        create?: () => void
        view?: () => void
        edit?: () => void
        delete?: () => void
    }>()

    const toggleActionMenu = ref(false)
    const actionButtons = computed(() => {
        return [
            {
                title: 'Create',
                iconName: 'PhPlusSquare',
                color: '#101827',
                method: props.create
            },
            {
                title: 'View',
                iconName: 'PhEye',
                color: '#17a34a',
                method: props.view
            },
            {
                title: 'Edit',
                iconName: 'PhNotePencil',
                color: '#0084c7',
                method: props.edit
            },
            {
                title: 'Delete',
                iconName: 'PhTrashSimple',
                color: '#e23c3c',
                method: props.delete
            },
        ]
    })
</script>


<style scoped>
    .actionBtn:hover {
        background-color: var(--backgroundColor);
        color: var(--color);
    }
</style>