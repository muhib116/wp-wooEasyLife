(()=>{
  const payload = {
    billing_first_name: "",
    billing_last_name: "",
    billing_phone: "",
    billing_email: "",
  }
  
  const handleEventListener = (objectKey, inputField) => {
    if (inputField) {
      // set initial value
      payload[objectKey] = inputField.value;
  
      inputField.addEventListener("input", (e) => {
        payload[objectKey] = e.target.value;
        updateAbandonedData(payload);
      });
  
      updateAbandonedData(payload);
    }
  }
  
  setTimeout(() => {
    const checkoutForm = document.querySelector("form[name=checkout]");
    if (checkoutForm) {
      const billing_first_name = checkoutForm.querySelector(
        "input[name=billing_first_name]"
      );
      const billing_last_name = checkoutForm.querySelector(
        "input[name=billing_last_name]"
      );
      const billing_phone = checkoutForm.querySelector(
        "input[name=billing_phone]"
      );
      const billing_email = checkoutForm.querySelector(
        "input[name=billing_email]"
      );
  
      handleEventListener("billing_first_name", billing_first_name);
      handleEventListener("billing_last_name", billing_last_name);
      handleEventListener("billing_phone", billing_phone);
      handleEventListener("billing_email", billing_email);
    }
  }, 2000)
  
  let timeoutId = null;
  const updateAbandonedData = async (payload) => {
    if (payload.billing_email == "" && payload.billing_phone == "") return;
  
    timeoutId && clearTimeout(timeoutId);
    timeoutId = setTimeout(async () => {
      try {
        const response = await fetch(woo_easy_life_ajax_obj.ajax_url, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded", // Required for admin-ajax.php
          },
          body: new URLSearchParams({
            action: "update_abandoned_data", // Action name registered in PHP
            billing_first_name: payload.billing_first_name,
            billing_last_name: payload.billing_last_name,
            billing_phone: payload.billing_phone,
            billing_email: payload.billing_email,
          }),
        });
  
        // Parse JSON response
        const result = await response.json();
  
        // Check if the request was successful
        if (result.success) {
          // console.log("Session ID:", result?.data?.session_id);
        } else {
          // console.error("Error:", result?.data);
        }
      } catch (error) {
        console.error("Fetch error:", error);
      }
    }, 1000);
  }
})()

