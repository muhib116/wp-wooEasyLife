import { ref } from "vue";

export const useVoiceToText = () => {
    const isRecognizing = ref(false);
    let recognition = null;
    let silenceTimer = null; // Timer to track silence

    const startSpeechRecognition = (cb) => {
        // Check browser support
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            alert("Your browser does not support Speech Recognition.");
            return;
        }

        // If already recognizing, stop it
        if (isRecognizing.value) {
            stopSpeechRecognition();
            return;
        }

        // Reset text before starting
        cb("");

        // Initialize Speech Recognition
        recognition = new SpeechRecognition();
        recognition.lang = "bn-BD"; // Default to Bangla
        recognition.continuous = true; // Keep listening
        recognition.interimResults = true; // Enable real-time transcription

        // Start recognition
        recognition.start();
        isRecognizing.value = true;
        console.log("🎤 Listening for Bangla & English speech...");

        // Reset silence timer
        resetSilenceTimer();

        // Capture speech results with delay between words
        recognition.onresult = async (event) => {
            resetSilenceTimer(); // Reset timer whenever speech is detected

            let finalTranscript = "";
            for (let i = event.resultIndex; i < event.results.length; i++) {
                let spokenText = event.results[i][0].transcript;

                // Convert phone numbers or numbers to English
                spokenText = convertBanglaToEnglishNumbers(spokenText);

                // Simulate a small delay between words
                finalTranscript += spokenText + " ";
                await delay(400); // Increase this value for a longer pause
            }

            // Update the text
            cb(finalTranscript);
            console.log("📝 Recognized Text:", finalTranscript);
        };

        // Stop listening if no speech detected for 2s
        function resetSilenceTimer() {
            if (silenceTimer) clearTimeout(silenceTimer);
            silenceTimer = setTimeout(() => {
                console.log("⏳ Silence detected, stopping recognition...");
                stopSpeechRecognition();
            }, 2000); // 2 seconds of silence
        }

        // Handle errors
        recognition.onerror = (event) => {
            console.error("❌ Speech recognition error:", event.error);
            stopSpeechRecognition();
        };

        // Stop when speech ends
        recognition.onend = () => {
            isRecognizing.value = false;
            console.log("🛑 Speech recognition stopped.");
        };
    };

    // Function to stop recognition manually
    const stopSpeechRecognition = () => {
        if (recognition) {
            recognition.stop();
            isRecognizing.value = false;
            console.log("🛑 Speech recognition manually stopped.");
        }
        if (silenceTimer) clearTimeout(silenceTimer);
    };

    // Function to replace spoken numbers with English numerals
    function convertBanglaToEnglishNumbers(banglaNumber) {
        // Mapping Bangla digits to English digits
        const banglaToEnglishMap = {
            "০": "0", "১": "1", "২": "2", "৩": "3", "৪": "4",
            "৫": "5", "৬": "6", "৭": "7", "৮": "8", "৯": "9"
        };

        // Replace each Bangla digit with the corresponding English digit
        return banglaNumber.replace(/[০-৯]/g, (digit) => banglaToEnglishMap[digit]);
    }

    // Function to create a delay between words
    function delay(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    return {
        isRecognizing,
        startSpeechRecognition,
        stopSpeechRecognition,
    };
};
