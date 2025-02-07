import { ref } from "vue";
import axios from "axios";
import {
    getAnalyzeAddressPrompt
} from './prompt'

export const useOpenAi = () => {
  const API_KEY = import.meta.env.VITE_OPENAI_API_KEY; // Use environment variable
  const BASE_URL = "https://api.openai.com/v1/chat/completions";

  const analyzeAddress = async (address: string) => {
    try {
      const prompt = getAnalyzeAddressPrompt(address);

      const response = await axios.post(
        BASE_URL,
        {
          model: "gpt-4-turbo",
          messages: [{ role: "system", content: prompt }],
          max_tokens: 100, // Optimized token usage
          temperature: 0.3, // Less randomness for structured responses
        },
        {
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${API_KEY}`,
          },
        }
      );

      const result = response.data.choices[0].message.content;

      try {
        return JSON.parse(result); // Ensure valid JSON output
      } catch (error) {
        return { error: "Invalid JSON response from OpenAI" };
      }
    } catch (error) {
      console.error("OpenAI API Error:", error);
      return { error: "Failed to process the address" };
    }
  };

  return { analyzeAddress };
};
