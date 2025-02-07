<template>
    <div 
        class="bg-black/30 p-6 fixed inset-0 z-50 flex items-center justify-center"
        v-if="modelValue"
        @click.self="$emit('close')"
    >
        <Card.Native v-bind="$attrs" class="min-w-[300px] bg-white relative">
            
            <slot v-if="!hideHeader" name="header">
                <div class="flex justify-between border-b pb-2">
                    <Heading 
                        :title="title"
                    />
                    <button
                        class="absolute right-4 top-4 p-1 bg-black/5 rounded"
                        @click="$emit('close')"
                        title="Click to close popup"
                    >
                        <Icon 
                            name="PhX" 
                            size="16" 
                            weight="bold"
                            class="hover:text-red-500 duration-75"
                        />
                    </button>
                </div>
            </slot>

            <div 
                class="pb-5 max-h-[calc(100svh-190px)] overflow-auto"
                :class="[
                    !hideHeader ? 'pt-3' : '',
                    !hideFooter ? 'pb-3' : '',
                ]"
            >
                <slot></slot>
            </div>

            <slot v-if="!hideFooter" name="footer">
                <footer class="flex justify-end gap-5 items-center border-t pt-3">
                    <Button.Outline
                        v-if="cancelText"
                        class="bg-red-500 text-white border-0"
                        
                        @click="$emit('close')"
                    >
                        {{ cancelText }}
                    </Button.Outline>
                    <Button.Primary
                        v-if="confirmText"
                        @click="$emit('confirm')"
                    >
                        {{ confirmText }}
                    </Button.Primary>
                </footer>
            </slot>
        </Card.Native>
    </div>
</template>

<script setup lang="ts">
    import { ref } from 'vue'
    import { Card, Heading, Icon, Button } from '@components'


    defineOptions({
        inheritAttrs: false
    })

    interface Props {
        title?: string,
        cancelText?: string,
        confirmText?: string,
        hideHeader?: boolean,
        hideFooter?: boolean,
    }
    withDefaults(defineProps<Props>(), {
        title: "Title",
        hideHeader: false,
        hideFooter: false,
    })
    

    const modelValue = defineModel()
</script>