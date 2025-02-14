import { ref } from "vue";

export const useVoiceToText = () => {
    const isRecognizing = ref(false);
    let recognition = null;
    let silenceTimer = null;

    const startSpeechRecognition = (cb) => {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            alert("Your browser does not support Speech Recognition.");
            return;
        }

        if (isRecognizing.value) {
            stopSpeechRecognition();
            return;
        }

        navigator.mediaDevices.getUserMedia({ audio: true }).then(() => {
            recognition = new SpeechRecognition();
            recognition.lang = "bn-BD"; // Set to Bangla
            recognition.continuous = false; // Disable continuous mode for mobile
            recognition.interimResults = true;

            recognition.start();
            isRecognizing.value = true;
            console.log("🎤 Listening...");

            resetSilenceTimer();

            recognition.onresult = async (event) => {
                resetSilenceTimer();
                let finalTranscript = "";
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    let spokenText = event.results[i][0].transcript;
                    spokenText = convertBanglaToEnglishNumbers(spokenText);
                    finalTranscript += spokenText + " ";
                }
                cb(finalTranscript);
                console.log("📝 Recognized Text:", finalTranscript);
            };

            function resetSilenceTimer() {
                if (silenceTimer) clearTimeout(silenceTimer);
                silenceTimer = setTimeout(() => {
                    console.log("⏳ Silence detected, stopping...");
                    stopSpeechRecognition();
                }, 4000); // Increased to 4 seconds
            }

            recognition.onerror = (event) => {
                console.error("❌ Speech recognition error:", event.error);
                stopSpeechRecognition();
            };

            recognition.onend = () => {
                isRecognizing.value = false;
                console.log("🛑 Stopped.");
                if (isRecognizing.value) recognition.start(); // Restart on mobile
            };
        }).catch((err) => {
            console.error("Microphone access denied:", err);
        });
    };

    const stopSpeechRecognition = () => {
        if (recognition) {
            recognition.stop();
            isRecognizing.value = false;
            console.log("🛑 Manually stopped.");
        }
        if (silenceTimer) clearTimeout(silenceTimer);
    };

    function convertBanglaToEnglishNumbers(banglaNumber) {
        const banglaToEnglishMap = {
            "০": "0", "১": "1", "২": "2", "৩": "3", "৪": "4",
            "৫": "5", "৬": "6", "৭": "7", "৮": "8", "৯": "9"
        };
        return banglaNumber.replace(/[০-৯]/g, (digit) => banglaToEnglishMap[digit]);
    }

    return {
        isRecognizing,
        startSpeechRecognition,
        stopSpeechRecognition,
    };
};
