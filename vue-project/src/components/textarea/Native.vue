<template>
    <div :class="wrapperClass">
        <Label
            v-if="label"
            :for="uid"
            :class="labelClass"
            :style="{ ...labelStyle }"
            class="flex justify-between w-full"
        >
            <span>{{ label }}</span>
        </Label>

        <div class="relative">
            <span
                class="absolute inset-0"
                :style="{ ...bgStyle }"
            ></span>
            <div class="flex z-10 items-center gap-2 w-full relative">
                <Button.Native
                    class="absolute z-10 right-[6px] bottom-[6px] size-[25px] grid place-content-center rounded-full opacity-50 hover:opacity-100"
                    @click="handleVoiceToText"
                    :loading="isRecognizing"
                >
                    <Icon
                        name="PhMicrophone"
                        size="20"
                    />
                </Button.Native>

                <textarea
                    v-model="localValue"
                    v-bind="$attrs"
                    :id="uid"
                    class="relative placeholder:text-inherit placeholder:opacity-60 !border !border-secondary-five rounded-sm px-4 py-2 block w-full disabled:bg-gray-100 disabled:opacity-60 focus:ring-0 focus:outline-none"
                    :style="inputStyle"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
    import Label from './Label.vue'
    import { provide, getCurrentInstance, computed, useAttrs } from 'vue'
    import { Icon, Button } from '@components'
    import { useVoiceToText } from '@/service/useVoiceToText.ts'

    const instance = getCurrentInstance()
    const attrs = useAttrs()
    const {
        isRecognizing,
        startSpeechRecognition
    } = useVoiceToText()
    
    defineOptions({
        name: 'BaseInput',
        inheritAttrs: false,
    })

    const props = defineProps({
        wrapperClass: String,
        labelClass: String,
        labelStyle: Object,
        inputStyle: Object,
        bgStyle: Object,
        inputClass: String,
        label: String
    })
    const uid = computed(() => attrs?.id || `component_id_${instance.uid}`)

    provide('props', props) // ignore

    const localValue = defineModel()
    const handleVoiceToText = () => {
        startSpeechRecognition((text) => {
            localValue.value = text
        })
    }
</script>