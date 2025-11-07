import { checkFraudCustomer } from "@/remoteApi";
import { normalizePhoneNumber } from "@/helper";
import { ref } from "vue";

export const useFraudChecker = () => {
    const data = ref();

    const handleFraudCheck = async (phone: string, btn) => {
        if (!phone || normalizePhoneNumber(phone).length !== 11) {
            alert("Please enter a valid phone number !");
            return;
        }
        try {
            btn.isLoading = true;
            const payload = {
                data: [
                    {
                        id: 1,
                        phone: phone,
                    },
                ],
            };

            const { data: _data } = await checkFraudCustomer(payload);
            if (_data?.length) {
                data.value = _data[0];
            }
            return data.value
        } finally {
            btn.isLoading = false;
        }
    }

    return {
        handleFraudCheck,
        data
    }
}