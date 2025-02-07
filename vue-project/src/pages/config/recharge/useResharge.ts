import { validateBDPhoneNumber } from "@/helper"
import { smsRecharge } from "@/remoteApi"
import { inject, ref } from "vue"

export const useRecharge = () => {
    const {
        loadUserData
    } = inject('useServiceProvider')

    const alertMessage = ref({
        title: '',
        type: '',
        wait: 5000
    })
    const minRechargeAmount = 50
    const data = [
        {
            logo: 'https://wpsalehub.com/wp-content/uploads/2025/01/image-14.webp',
            paymentPartner: 'bKash',
            bg: '#e4136b22',
            fee: 1.85, //in percent
            note: 'bKash "Send Money" fee will be added with net price.',
            instructions: ` 
                <p>01. Go to your bKash app or Dial *247#</p> 
                <p>02. Choose “Send Money”</p> 
                <p>03. Enter below bKash Account Number</p> 
                <p>04. Enter total amount</p> 
                <p>06. Now enter your bKash Account PIN to confirm the transaction</p> 
                <p>07. Copy Transaction ID from payment confirmation message and paste that Transaction ID below</p> 
            `,
            accountType: 'Personal',
            account: '01770-989591'
        },
        {
            logo: 'https://wpsalehub.com/wp-content/uploads/2025/01/rocket.webp',
            paymentPartner: 'Rocket',
            bg: '#8e349322',
            fee: 1.8, //in percent
            note: 'Rocket "Send Money" fee will be added with net price.',
            instructions: `
                <p>01. Go to your Rocket app or Dial *322#</p>
                <p>02. Choose “Send Money”</p>
                <p>03. Enter below Rocket Account Number</p>
                <p>04. Enter <b>total amount</b></p>
                <p>06. Now enter your Rocket Account PIN to confirm the transaction</p>
                <p>07. Copy Transaction ID from payment confirmation message and paste that Transaction ID below</p>
            `,
            accountType: 'Personal',
            account: '01770-989591-9'
        }
    ]

    const form = ref<{
        rechargeableAmount: number | null
        transactionCharge: number | 0
        payableAmount: number
        transactionId: string
        accountNumber: string
        transaction_method: string
    }>({
        payableAmount: 0,
        transactionCharge: 0,
        rechargeableAmount: null,
        transactionId: '',
        accountNumber: '',
        transaction_method: ''
    })

    const getPayableAmount = (fee: number) => {
        let amount = 0
        if(form.value.rechargeableAmount){
            form.value.transactionCharge =  (form.value.rechargeableAmount * (fee / 100))
            amount = form.value.rechargeableAmount + form.value.transactionCharge
        }

        amount = Math.round(amount)
        form.value.payableAmount = amount
        return amount
    }

    const rechargeBalance = async (btn) => {
        const payload = {
            account_number: form.value.accountNumber,
            transaction_method: form.value.transaction_method,
            total_amount: form.value.payableAmount,
            total_charge: form.value.transactionCharge,
            transaction_id: form.value.transactionId,
        }

        if(
            !payload.account_number.trim() 
            || !payload.transaction_method.trim()
            || !payload.total_amount
            || !payload.transaction_id.trim()
        ) {
            alertMessage.value = {
                ...alertMessage.value,
                type: 'danger',
                title: `Please ensure all fields marked with * are filled in.`,
            }
            return
        }

        if(payload.transaction_method == 'bKash' && !validateBDPhoneNumber(payload.account_number)) {
            alertMessage.value = {
                ...alertMessage.value,
                type: 'danger',
                title: `The bKash number you provided (${payload.account_number} is invalid.`,
            }
            return
        }
        if(payload.transaction_method == 'Rocket' && !validateBDPhoneNumber(payload.account_number.slice(0, -1))) {
            alertMessage.value = {
                ...alertMessage.value,
                type: 'danger',
                title: `The Rocket number you provided (${payload.account_number}) is invalid.`,
            }
            return
        }
        if(payload.total_amount < minRechargeAmount) {
            alertMessage.value = {
                ...alertMessage.value,
                type: 'danger',
                title: `Minimum recharge amount ${minRechargeAmount}tk`,
            }

            return
        }

        try {
            btn.isLoading = true
            const response = await smsRecharge(payload)
            /**
             * {
                "status": true,
                "message": "Success",
                "data": {
                    "user_id": 2,
                    "created_by": 2,
                    "total_amount": "51.00",
                    "transaction_charge": "0.93",
                    "transaction_method": "bKash",
                    "transaction_id": "Ea sit recusandae P",
                    "account_number": "01770989591",
                    "domain": "localhost",
                    "status": "pending",
                    "updated_at": "2025-02-06T12:04:07.000000Z",
                    "created_at": "2025-02-06T12:04:07.000000Z",
                    "id": 1
                }
            }
             */
            
            if(response.status) {
                alertMessage.value = {
                    type: 'success',
                    title: 'Your SMS recharge request has been successfully submitted. <br> Our admin will verify your transaction within <strong>24 hours</strong>.',
                    wait: 10000
                }
            } else {
                alertMessage.value = {
                    type: 'warning',
                    title: response.message,
                    wait: 5000
                }
            }
            
        } catch (err) {
            console.error(err)
            alertMessage.value = {
                ...alertMessage.value,
                type: 'danger',
                title: err.response.data.message,
            }
        } finally {
            btn.isLoading = false
        }
    }

    return {
        data,
        form,
        getPayableAmount,
        rechargeBalance,
        minRechargeAmount,
        alertMessage
    }
}