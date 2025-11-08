<template>
    <div class="p-4 bg-white border-t border-gray-200 flex gap-2">
        <input 
            v-model="inputValue" 
            @keyup.enter="handleSend"
            type="text" 
            :placeholder="isListening ? 'Listening... Speak in Bangla or English!' : 'Type your message or use voice input...'"
            :class="[
                'flex-1 py-2.5 px-3.5 border rounded-3xl outline-none text-sm transition-all duration-200',
                isListening 
                    ? 'border-red-400 bg-red-50 focus:border-red-500' 
                    : 'border-gray-200 focus:border-indigo-500'
            ]"
        />
        <!-- Voice input microphone button with auto language detection -->
        <button 
            @click="handleVoiceInput"
            :class="[
                'w-10 h-10 rounded-full border-0 cursor-pointer flex items-center justify-center transition-all duration-200 hover:scale-105',
                isListening ? 'animate-pulse bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
            :aria-label="isListening ? 'Stop Voice Input' : 'Start Voice Input (Auto-detect Bangla/English)'"
            :title="isListening ? 'Listening... Click to stop' : 'Click to start voice input - automatically detects Bangla or English'"
        >
            <Icon 
                :name="isListening ? 'PhMicrophoneSlash' : 'PhMicrophone'" 
                :size="20" 
                :class="isListening ? 'animate-bounce' : ''"
            />
        </button>
        <button 
            @click="handleSend" 
            :disabled="!inputValue.trim()"
            class="w-10 h-10 rounded-full text-white border-0 cursor-pointer flex items-center justify-center transition-transform duration-200 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)"
            aria-label="Send Message"
        >
            <Icon name="PhPaperPlane" class="rotate-90" size="20" />
        </button>
    </div>
</template>

<script setup lang="ts">
// TypeScript declarations for SpeechRecognition API
declare global {
    interface Window {
        SpeechRecognition: typeof SpeechRecognition
        webkitSpeechRecognition: typeof SpeechRecognition
    }
}

interface SpeechRecognition extends EventTarget {
    continuous: boolean
    grammars: SpeechGrammarList
    interimResults: boolean
    lang: string
    maxAlternatives: number
    serviceURI: string
    
    start(): void
    stop(): void
    abort(): void
    
    onaudiostart: ((this: SpeechRecognition, ev: Event) => any) | null
    onaudioend: ((this: SpeechRecognition, ev: Event) => any) | null
    onend: ((this: SpeechRecognition, ev: Event) => any) | null
    onerror: ((this: SpeechRecognition, ev: SpeechRecognitionErrorEvent) => any) | null
    onnomatch: ((this: SpeechRecognition, ev: SpeechRecognitionEvent) => any) | null
    onresult: ((this: SpeechRecognition, ev: SpeechRecognitionEvent) => any) | null
    onsoundstart: ((this: SpeechRecognition, ev: Event) => any) | null
    onsoundend: ((this: SpeechRecognition, ev: Event) => any) | null
    onspeechstart: ((this: SpeechRecognition, ev: Event) => any) | null
    onspeechend: ((this: SpeechRecognition, ev: Event) => any) | null
    onstart: ((this: SpeechRecognition, ev: Event) => any) | null
}

interface SpeechRecognitionEvent extends Event {
    resultIndex: number
    results: SpeechRecognitionResultList
}

interface SpeechRecognitionErrorEvent extends Event {
    error: string
    message: string
}

interface SpeechRecognitionResultList {
    length: number
    item(index: number): SpeechRecognitionResult
    [index: number]: SpeechRecognitionResult
}

interface SpeechRecognitionResult {
    length: number
    item(index: number): SpeechRecognitionAlternative
    [index: number]: SpeechRecognitionAlternative
    isFinal: boolean
}

interface SpeechRecognitionAlternative {
    transcript: string
    confidence: number
}

import { computed, ref, onUnmounted } from 'vue'
import { Icon } from '@/components'

const props = defineProps<{
    modelValue: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
    send: []
}>()

const inputValue = computed({
    get: () => props.modelValue,
    set: (value: string) => emit('update:modelValue', value)
})

// Priority languages: Bangla and English
const priorityLanguages = ['bn-BD', 'en-US']

// Language mapping for better recognition with priority for Bangla and English
const getOptimalLanguage = () => {
    const browserLang = navigator.language || navigator.languages?.[0] || 'en-US'
    
    // Map common language codes to more specific speech recognition languages
    const languageMap: Record<string, string> = {
        'bn': 'bn-BD', // Bengali/Bangla (Bangladesh)
        'en': 'en-US', // English (US)
        'es': 'es-ES', 
        'fr': 'fr-FR',
        'de': 'de-DE',
        'it': 'it-IT',
        'pt': 'pt-BR',
        'ja': 'ja-JP',
        'ko': 'ko-KR',
        'zh': 'zh-CN',
        'ar': 'ar-SA',
        'ru': 'ru-RU',
        'hi': 'hi-IN',
        'nl': 'nl-NL',
        'sv': 'sv-SE',
        'da': 'da-DK',
        'no': 'nb-NO',
        'fi': 'fi-FI',
        'pl': 'pl-PL',
        'tr': 'tr-TR',
        'he': 'he-IL'
    }
    
    // Extract language code (e.g., 'en' from 'en-US')
    const langCode = browserLang.split('-')[0]
    
    // Check if browser language is Bangla or English, prioritize them
    if (langCode === 'bn' || browserLang.includes('bn')) {
        return 'bn-BD' // Bengali/Bangla
    }
    if (langCode === 'en' || browserLang.includes('en')) {
        return 'en-US' // English
    }
    
    // Return mapped language or default to English for unsupported languages
    return languageMap[langCode] || 'en-US'
}

// Initialize reactive variables after function definitions
const isListening = ref(false)
const recognition = ref<SpeechRecognition | null>(null)
const currentLanguage = ref(getOptimalLanguage()) // Used for initial detection preference

// Initialize speech recognition with priority for Bangla and English
function initSpeechRecognition(language?: string) {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        console.warn('Speech recognition not supported in this browser')
        return null
    }

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition
    const recognition = new SpeechRecognition()
    
    // Configure recognition settings
    recognition.continuous = false
    recognition.interimResults = false
    recognition.maxAlternatives = 5 // Get more alternatives for better accuracy with multiple languages
    
    // Set language for recognition - use provided language or get optimal one
    recognition.lang = language || getOptimalLanguage()
    
    console.log('Speech recognition initialized with language:', recognition.lang)

    return recognition
}

function handleVoiceInput() {
    if (!recognition.value) {
        recognition.value = initSpeechRecognition(currentLanguage.value)
    }

    if (!recognition.value) {
        alert('Speech recognition is not supported in your browser. Please use Chrome, Edge, or Safari.')
        return
    }

    if (isListening.value) {
        // Stop listening
        recognition.value.stop()
        isListening.value = false
        return
    }

    // Start listening with auto-detection - try optimal language first
    const startingLanguage = getOptimalLanguage()
    console.log('Starting voice recognition with auto-detection, trying:', startingLanguage)
    startVoiceRecognition(startingLanguage)
}

function startVoiceRecognition(language: string, isRetry: boolean = false) {
    if (!recognition.value) {
        recognition.value = initSpeechRecognition(language)
    }

    if (!recognition.value) {
        alert('Speech recognition is not supported in your browser. Please use Chrome, Edge, or Safari.')
        return
    }

    // Update recognition language
    recognition.value.lang = language
    isListening.value = true
    
    recognition.value.onstart = () => {
        console.log('Speech recognition started')
    }

    recognition.value.onresult = (event) => {
        const result = event.results[0]
        
        // Get the best transcript from alternatives
        let bestTranscript = result[0].transcript
        let bestConfidence = result[0].confidence
        
        // Check all alternatives and pick the one with highest confidence
        for (let i = 0; i < result.length; i++) {
            const alternative = result[i]
            if (alternative.confidence > bestConfidence) {
                bestTranscript = alternative.transcript
                bestConfidence = alternative.confidence
            }
        }
        
        console.log('Auto-detected speech:', { 
            transcript: bestTranscript, 
            confidence: bestConfidence, 
            detectedLanguage: language === 'bn-BD' ? 'Bangla (বাংলা)' : 'English',
            language: recognition.value?.lang,
            alternatives: Array.from(result).map(alt => ({ 
                transcript: alt.transcript, 
                confidence: alt.confidence 
            }))
        })
        
        // Clean up the transcript
        const cleanTranscript = bestTranscript.trim()
        let finalTranscript = cleanTranscript
        
        // For English, capitalize first letter. For Bangla, keep as is
        if (language === 'en-US' && cleanTranscript.length > 0) {
            finalTranscript = cleanTranscript.charAt(0).toUpperCase() + cleanTranscript.slice(1)
        }
        
        // Update current language based on successful recognition
        currentLanguage.value = language
        
        // Update input value with recognized text
        inputValue.value = finalTranscript
        isListening.value = false
    }

    recognition.value.onerror = (event) => {
        console.error('Speech recognition error:', event.error, 'Language:', language)
        isListening.value = false
        
        // For 'no-speech' or 'language-not-supported' errors, try the other priority language
        if ((event.error === 'no-speech' || event.error === 'language-not-supported') && !isRetry) {
            const alternativeLanguage = language === 'bn-BD' ? 'en-US' : 'bn-BD'
            console.log(`Auto-detecting: Retrying with ${alternativeLanguage === 'bn-BD' ? 'Bangla' : 'English'}`)
            
            // Reset recognition for new language
            recognition.value = null
            
            // Auto-retry with alternative language
            setTimeout(() => {
                if (!isListening.value) { // Only retry if user hasn't cancelled
                    console.log(`Auto-detection: Switching to ${alternativeLanguage === 'bn-BD' ? 'Bangla' : 'English'} recognition...`)
                    startVoiceRecognition(alternativeLanguage, true)
                }
            }, 300) // Faster retry for better UX
            return
        }
        
        let errorMessage = 'Speech recognition failed. '
        switch (event.error) {
            case 'no-speech':
                errorMessage += 'No speech was detected in both Bangla and English. Please try again.'
                break
            case 'audio-capture':
                errorMessage += 'No microphone was found. Please check your microphone settings.'
                break
            case 'not-allowed':
                errorMessage += 'Microphone access was denied. Please allow microphone access and try again.'
                break
            case 'network':
                errorMessage += 'Network error occurred. Please check your internet connection.'
                break
            case 'language-not-supported':
                errorMessage += 'Language not supported. Trying both Bangla and English.'
                break
            default:
                errorMessage += 'Please try again.'
        }
        alert(errorMessage)
    }

    recognition.value.onend = () => {
        console.log('Speech recognition ended')
        isListening.value = false
    }

    try {
        recognition.value.start()
    } catch (error) {
        console.error('Failed to start speech recognition:', error)
        isListening.value = false
        alert('Failed to start speech recognition. Please try again.')
    }
}

function handleSend() {
    if (inputValue.value.trim()) {
        emit('send')
    }
}

// Auto-detection: No manual language switching needed

// Cleanup speech recognition when component is unmounted
onUnmounted(() => {
    if (recognition.value && isListening.value) {
        recognition.value.stop()
        isListening.value = false
    }
})
</script>

<!-- Styles are now in Tailwind classes directly in the template -->
