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
                <component
                    :is="isValidRoute ? 'router-link' : 'div'"
                    :to="isValidRoute ? to : null" 
                    class="flex items-start gap-3"
                    :class="{ 
                        'cursor-pointer hover:opacity-80 transition-opacity': isValidRoute || hasInvalidRoute,
                        'hover:text-white': to
                    }"
                    @click="handleClick"
                >
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
                </component>
                <footer class="min-h-4" :class="$slots.footer ? 'mt-2' : ''">
                    <slot name="footer"></slot>
                </footer>
            </slot>
        </div>
    </Card>
</template>

<script setup lang="ts">
    import Card from './Native.vue'
    import { Icon } from '@/components'
    import { computed } from 'vue'
    import { useRouter } from 'vue-router'

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
        to?: {
            name?: string
            params?: Record<string, any>
            query?: Record<string, any>
        }
    }

    const props = withDefaults(defineProps<Props>(), {
        title: 'Title',
        subtitle: 'Subtitle',
        iconName: 'PhPresentationChart',
        iconClass: '',
        iconWeight: 'light',
        iconSize: 25,
        to: undefined
    })

    const router = useRouter()

    const isValidRoute = computed(() => {
        if (!props.to?.name) return false
        
        try {
            // Try to resolve the route to check if it exists
            router.resolve(props.to)
            return true
        } catch (error) {
            console.warn('Invalid route:', props.to, error)
            return false
        }
    })

    const hasInvalidRoute = computed(() => {
        return props.to?.name && !isValidRoute.value
    })

    const handleClick = () => {
        if (hasInvalidRoute.value) {
            console.error('Attempted to navigate to invalid route:', props.to)
            // You could emit an event or show a notification here
            // For now, just log the error
        }
    }
</script>