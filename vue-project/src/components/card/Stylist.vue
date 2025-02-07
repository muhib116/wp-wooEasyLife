<template>
    <Card class="!p-0">
        <div
            v-bind="$attrs"
            class="bg-blue-500 h-full rounded-lg p-5 text-white relative overflow-hidden"
        >
            
            <!-- background decoration -->
            <Icon
                :name="iconName"
                :size="(iconSize * 4)"
                weight="fill"
                class="absolute top-0 right-0 translate-x-1/2 mr-4 opacity-10"
                :class="iconClass"
            />
            <span class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 size-20 bg-slate-200/10 rounded-full"></span>
            <!-- /background decoration -->

            <slot>
                <div class="flex items-start gap-3">
                    <span class="aspect-square p-2 bg-black/10 inline-block rounded-full flex-shrink-0 flex-grow-0">
                        <Icon
                            :name="iconName"
                            :size="iconSize"
                            :weight="iconWeight"
                            :class="iconClass"
                        />
                    </span>
                    <div class="flex-1 w-[calc(100%-25%)]">
                        <h3
                            class="font-black text-xl leading-6 truncate" 
                            :title="title"
                            v-html="title"
                        />
                        <h3 
                            class="font-extralight text-sm capitalize mt-1"
                            v-html="subtitle"
                        />
                    </div>
                </div>
                <footer class="min-h-4" :class="$slots.footer ? 'mt-2' : ''">
                    <slot name="footer"></slot>
                </footer>
            </slot>
        </div>
    </Card>
</template>

<script setup lang="ts">
    import Card from './Native.vue'
    import { Icon } from '@components'

    defineOptions({
        inheritAttrs: false
    })

    interface Props {
        iconName?: string
        iconClass?: string
        iconWeight?: string
        iconSize?: number
        title?: string
        subtitle?: string
        footerContent?: any
    }

    withDefaults(defineProps<Props>(), {
        title: 'Title',
        subtitle: 'Subtitle',
        iconName: 'PhPresentationChart',
        iconClass: '',
        iconWeight: 'light',
        iconSize: 25
    })
</script>