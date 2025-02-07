<link rel="stylesheet" href="<?php echo plugin_dir_url(__DIR__) . 'checkoutPage/style.css'; ?>">

<div id="woo_easy_modal_box">
    <div
        class="woo_easy_life_modal_wrapper lg" 
        :class="modalToggle ? 'active' : ''"
        id="woo_easy_modal"
    >
        <div class="modal_container" style="color: #555 !important;">
            <div class="modal_header" style="display: block;">
                <h4 
                    style="
                        font-size: 20px;
                        text-align: center;
                        font-weight: bold;
                        margin-bottom: 0px;
                    "
                >
                    Verify your phone number
                </h4>
            </div>
            <div class="modal_body text-center">
                <h4 
                    style="
                        font-weight: 500;
                        text-align: center;
                        margin-bottom: 0px;
                        font-size: 16px;
                    "
                >
                    An OTP has been sent to 
    
                    <span style="
                        font-weight: bold;
                        text-align: center;
                        margin-bottom: 6px;
                        font-size: 16px;
                    ">
                        {{ billingPhone }}.
                    </span>
                </h4>
    
                <div style="display: grid; justify-content: center; text-align: center;">
                    <span style="font-weight: bold; font-size: 18px; margin-bottom:6px;">
                        Enter OTP Code
                    </span>
                    <div
                        :class="[
                            !otpIsNotValid && isOTPValidated ? 'wooEasyOTPValid' : '',
                            !otpIsNotValid && isOTPValidating ? 'wooEasyOTPValidating' : '',
                            otpIsNotValid ? 'wooEasyOTPError' : '',
                        ]"
                        style="position: relative;"
                    >
                        <svg v-if="!otpIsNotValid && isOTPValidating" class="spin" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M232,128a104,104,0,0,1-208,0c0-41,23.81-78.36,60.66-95.27a8,8,0,0,1,6.68,14.54C60.15,61.59,40,93.27,40,128a88,88,0,0,0,176,0c0-34.73-20.15-66.41-51.34-80.73a8,8,0,0,1,6.68-14.54C208.19,49.64,232,87,232,128Z"></path></svg>
                        <svg v-if="!otpIsNotValid && isOTPValidated" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                        <input
                            style="
                                padding: 6px 6px;
                                font-size: 20px;
                                text-align: center;
                                border: 1px solid #ff7a00;
                                border-radius: 2px;
                                background: #3331;
                                box-shadow: 2px 2px 2px #4444 inset;
                                font-weight: bold;
                                letter-spacing: 20px;
                            "
                            focus
                            placeholder=""
                            v-model="otpCode"
                            @input="validateOTP"
                        />
                        <p
                            v-if="otpIsNotValid"
                            style="
                                position: absolute;
                                font-size: 12px;
                                color: red;
                                white-space: nowrap;
                            "
                        >
                            Your OTP is not valid, try again with valid otp.
                        </p>
                    </div>
                </div>
    
                <br />
                <p style="text-align: center; font-weight: bold;margin-bottom: 0px !important;">
                    Didn’t receive the code?
                </p>
                <p style="text-align: center;color: #888;">
                    You can resend it {{ getCountDownData }}
                </p>
    
                <div    
                    style="
                        display: flex;
                        place-items: center;
                        justify-content: center;
                        gap: 20px;
                        margin-top: 10px;
                    "
                >
                    <button
                        v-if="remainingTime <= 0"
                        style="
                            background-color: #2196F3;
                            color: #fff;
                            padding: 8px 15px;
                            border-radius: 4px;
                            box-shadow: 0 2px 10px #0003;
                            cursor: pointer;
                            border: none;
                        "
                        @click="resendOTP"
                    >Resend OTP</button>
                </div>
            </div>
    
            <div class="modal_footer">
                Secure your order
                <button 
                    class="close btn"
                    @click="modalToggle = false"
                >Close</button>
            </div>
        </div>
    </div>
</div>


<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<script>
    const { createApp, ref, onMounted, computed, watchEffect } = Vue
    createApp({
        setup() {
            const resendOtpCountDownTimeInSecond = 25
            const remainingTime = ref(resendOtpCountDownTimeInSecond)
            const minutes = ref(Math.floor(remainingTime.value / 60))
            const seconds = ref(remainingTime.value % 60)
            const displayCount = ref('00:00')
            const billingPhone = ref('')
            const otpSendStatus = ref(false)
            const otpCode = ref('')
            const isOTPValidating = ref(false)
            const isOTPValidated = ref(false)
            const modalToggle = ref(false)
            const otpIsNotValid = ref(false)
            const apiBaseUrl = 'http://localhost:8080/wordpress/wp-json/wooeasylife/v1'

            const woo_easy_life_startCountdown = () => {
                isOTPValidated.value = false
                remainingTime.value = resendOtpCountDownTimeInSecond

                const interval = setInterval(() => {
                    minutes.value = Math.floor(remainingTime.value / 60);
                    seconds.value = remainingTime.value % 60;

                    displayCount.value = `in ${minutes.value}:${seconds.value < 10 ? '0' : ''}${seconds.value}`;

                    if (remainingTime.value <= 0) {
                        clearInterval(interval);
                    }

                    remainingTime.value--;
                }, 1000);
            }

            const getCountDownData = computed(() => {
                return remainingTime.value > 0 ? `in ${displayCount.value}` : 'now'
            })

            const validateBDPhoneNumber = (phoneNumber) => {
                // Remove spaces and non-numeric characters except for "+"
                phoneNumber = phoneNumber.replace(/[^\d+]/g, '');

                // Define valid patterns
                const patterns = [
                    /^\+8801[3-9]\d{8}$/,  // +880 format
                    /^8801[3-9]\d{8}$/,    // 880 format
                    /^01[3-9]\d{8}$/       // 01 format
                ];

                // Check if the phone number matches any of the valid patterns
                for (const pattern of patterns) {
                    if (pattern.test(phoneNumber)) {
                        return true;
                    }
                }

                // Return an error message for invalid phone numbers
                return false;
            }

            const scrollToBillingPhoneField = () => {
                const billingPhoneField = document.getElementById('billing_phone_field');
                if (billingPhoneField) {
                    billingPhoneField.scrollIntoView({
                        behavior: 'smooth', // Smooth scrolling animation
                        block: 'start'      // Align to the top of the element
                    });
                }
            }

            const sendOTP = async () => {
                if(otpSendStatus.value) return
                const { data } = await axios.post(`${apiBaseUrl}/otp/send`, {
                    phone_number: billingPhone.value
                })

                if(data.status == "success"){
                    otpSendStatus.value = true
                }
            }

            const resendOTP = async () => {
                isOTPValidating.value = false
                woo_easy_life_startCountdown()
                otpCode.value = ''
                await axios.post(`${apiBaseUrl}/otp/resend`, {
                    phone_number: billingPhone.value
                })
            }

            let timeoutId = null
            const validateOTP = async () => {
                clearTimeout(timeoutId)
                timeoutId = setTimeout(async () => {
                    if(otpCode.value.length == 4){
                        isOTPValidating.value = true

                        
                        try {
                            const { data } = await axios.post(`${apiBaseUrl}/otp/validate`, {
                                phone_number: billingPhone.value,
                                otp: otpCode.value
                            })
                            if(data.status = 'success'){
                                isOTPValidated.value = true
                                isOTPValidating.value = false
                                otpIsNotValid.value = false
    
                                const place_order_btn = document.querySelector('form[name="checkout"] #place_order')
                                if(place_order_btn){
                                    place_order_btn.click()
                                }
                            }
                        } catch(err) {
                            otpIsNotValid.value = true
                            otpCode.value = ''
                        }
                    }else {
                        isOTPValidating.value = false
                    }
                }, 700)
            }

            
            setTimeout(() => {
                const billing_phoneInput = document.querySelector('#billing_phone')
                const wooEasyLifeOtpModalOpener = document.getElementById('wooEasyLifeOtpModalOpener')
                const woocommerceCheckoutForm = document.querySelector("form[name=checkout].checkout.woocommerce-checkout")

                if(billing_phoneInput) {
                    window.onclick = (e) => {
                        if(e.target.getAttribute('id') != 'wooEasyLifeOtpModalOpener') return
                        billingPhone.value = billing_phoneInput.value;
                        otpCode.value = ''

                        if(validateBDPhoneNumber(billingPhone.value)){
                            modalToggle.value = true

                            sendOTP()
                            
                            if(!otpSendStatus.value){
                                woo_easy_life_startCountdown()
                            }
                            billing_phoneInput.classList.remove('woo_easy_life_input_error')
                            return
                        }

                        billing_phoneInput.classList.add('woo_easy_life_input_error')
                        scrollToBillingPhoneField()
                        alert('Please enter a valid phone number.')
                    };
                }
                if (woocommerceCheckoutForm) {
                    woocommerceCheckoutForm.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault() // Prevent the form submission
                            console.log('Enter key press prevented')
                        }
                    })
                } else {
                    console.error('Checkout form not found'); // Log an error if the form is not found
                }
            }, 2000)


            return {
                resendOtpCountDownTimeInSecond,
                remainingTime,
                minutes,
                seconds,
                displayCount,
                resendOTP,
                woo_easy_life_startCountdown,
                getCountDownData,
                billingPhone,
                modalToggle,
                validateOTP,
                otpCode,
                isOTPValidated,
                isOTPValidating,
                otpIsNotValid
            }
        }
    }).mount('#woo_easy_modal_box')
</script>