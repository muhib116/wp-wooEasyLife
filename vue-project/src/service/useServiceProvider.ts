import { getLicenseStatus } from "@/api"
import { getUser } from "@/remoteApi"
import { ref } from "vue"

export const userData = ref()
export const licenseKey = ref(localStorage.getItem('license_key'))
export const isValidLicenseKey = ref(true)
export const licenseStatus = ref('valid')
export const licenseAlertMessage = ref<{
    type: "danger" | "warning" | "info" | "success",
    message: string
}>()

export const setUserData = (data) => {
    userData.value = data
}

export const userDataLoading = ref(false)
export const loadUserData = async () => {
    try {
        userDataLoading.value = true
        const data = await getUser() //this function calling to check authentication, read inside the code
        setUserData(data)
    } catch(err) {
        console.error(err)
    } finally {
        userDataLoading.value = false
    }
}

export const getNoticeOfBalanceOver = (balance: number) : {
    type: string,
    message: string
} => {
    let balanceNotice = {
        type: '',
        message: ''
    }

    if (balance <= 0) {
        balanceNotice = {
            type: 'danger',
            message: `
                <h1 class="font-medium text-lg text-red-600">⚠ Your Balance is Depleted!</h1>
                <p class="mt-2">You can no longer process new orders because your balance has run out.</p>
                <p class="mt-2 font-semibold">Recharge now to continue enjoying seamless order processing with WooEasyLife.</p>
                <p class="mt-2">Don’t miss out on new orders—stay ahead by keeping your balance topped up!</p>
            `
        }
    } else if (balance > 0 && balance <= 5) {
        balanceNotice = {
            type: 'warning',
            message: `
                <h1 class="font-medium text-lg text-yellow-600">⚠ Low Balance Alert!</h1>
                <p class="mt-2">You're running low on balance! Only <strong>${balance}</strong> left.</p>
                <p class="mt-2">Once your balance is exhausted, you won't be able to process new orders.</p>
                <p class="mt-2 font-semibold">Recharge now to avoid interruptions and keep your orders flowing!</p>
            `
        }
    } else if (balance > 5 && balance <= 25) {
        balanceNotice = {
            type: 'info',
            message: `
                <h1 class="font-medium text-lg text-blue-600">🔔 Balance Running Low!</h1>
                <p class="mt-2">You still have <strong>${balance}</strong> in your account, but it's getting low.</p>
                <p class="mt-2">Consider recharging soon to ensure uninterrupted order processing.</p>
                <p class="mt-2 font-semibold">Stay ahead and top up before your balance runs out!</p>
            `
        }
    }

    return balanceNotice;
}

export const router = ref()
export const route = ref()
export const redirectToLicensePage = () => {
    if(route.value.name == 'license') return
    router.value && router.value?.push({
        name: 'license'
    })
}

export const useServiceProvider = () => 
{
    return {
        route,
        router,
        userData,
        licenseKey,
        userDataLoading,
        isValidLicenseKey,
        licenseAlertMessage,
        setUserData,
        loadUserData,
        redirectToLicensePage,
        getNoticeOfBalanceOver,
        licenseStatus
    }
}