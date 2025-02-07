<template>
    <component
        :is="to || href ? 'RouterLink' : 'button'"
        v-bind="$attrs"
        :class="
            twMerge(
                localClass,
                wrapperClass,
                loadingStateClass,
                loading ? 'pointer-events-none opacity-70' : '',
                to || href ? 'opacity-50' : ''
            )
        "
        :to="to"
        :href="href"
        @click="localOnClick"
        activeClass="!opacity-100"
    >
        <span v-if="localLabel">{{ localLabel }}</span>
        <slot v-else></slot>

        <slot
            :loading="loading || button.isLoading"
            name="icon"
        >
            <Icon
                :name="icon"
                :source="iconSource"
                :class="iconClass"
            />
        </slot>

        <div
            v-if="(loading || button.isLoading) && !['done', 'processing'].includes(button.isLoading as string)"
            class="text-xl backdrop-blur-xl absolute inset-0 pointer-events-none flex items-center justify-center z-10"
        >
            <Loader
                active
                class="!h-6 !w-6"
                :theme="loaderTheme"
            />
        </div>
    </component>
</template>

<script setup lang="ts">
    import { Icon, Loader } from '@components'
    import { ref, computed } from 'vue'
    import { twMerge } from 'tailwind-merge'
    import { HTMLAttributes } from 'vue'

    defineOptions({
        name: 'NativeButton',
        inheritAttrs: false,
    })

    interface Props {
        to?: string | object | null
        href?: string | object
        icon?: string
        iconClass?: HTMLAttributes['class']
        iconSource?: 'phosphor' | 'custom'
        loading?: boolean
        class?: HTMLAttributes['class']
        loaderTheme?: string | null
    }
    const props = withDefaults(defineProps<Props>(), {
        loading: false,
        loaderTheme: null,
        to: null,
        icon: '',
        iconClass: 'w-4 h-4',
    })

    const emit = defineEmits(['onClick'])

    const localLabel = ref('')
    const localClass: string =
        'flex items-center gap-2 font-semibold relative hover:scale-105 hover:z-30 duration-200 cursor-pointer'
    const wrapperClass = computed<string>(() => props.class || '')

    const button = ref<{
        isLoading: string | boolean
        loading: (config: any) => void
        done: (config: any) => void
    }>({
        isLoading: false,
        loading: (config?: { label?: string }) => {
            if (config?.label) {
                localLabel.value = config?.label
            }
            button.value.isLoading = 'processing'
        },
        done: (config?: { label?: string; delay?: number }) => {
            if (config?.label) {
                localLabel.value = config?.label
            }
            handleDone((config?.delay as number) || 2000)
        },
    })

    const loadingStateClass = computed(() => {
        if (button.value.isLoading == 'processing') {
            return '!bg-yellow-500 text-white'
        }
        if (button.value.isLoading == 'done') {
            return '!bg-green-500 text-white'
        }
        return ''
    })

    const handleDone = (delay: number) => {
        button.value.isLoading = 'done'
        setTimeout(() => {
            button.value.isLoading = false
            localLabel.value = ''
        }, delay)
    }

    const localOnClick = () => {
        if (!button.value.isLoading) {
            emit('onClick', button.value)
        }
    }
</script>
