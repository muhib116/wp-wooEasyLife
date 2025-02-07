import { checkHasNewOrder } from "@/api";
import { showNotification, detectInternetState } from "@/helper";
import { inject, onMounted, ref } from "vue";
import { useRoute } from "vue-router"

export const useNotification = () => {
  const route = useRoute()
  const { getOrders, loadOrderStatusList } = inject("useOrders", {});
  const hasNewOrder = ref(false);

  let timeoutId;
  const notificationSound = new Audio(
    import.meta.env.DEV
      ? "/notification-sound.wav"
      : window?.wooEasyLife?.dist_url + "/notification-sound.wav"
  );
  
  const checkNewOrderStatus = async () => {
    try {
      const { data } = await checkHasNewOrder();
      if (data?.has_new_orders) {
        notificationSound.play();
        hasNewOrder.value = true;
        showNotification({
          type: 'success',
          message: 'New Order Received 🎉'
        }, 3000, false, 'top-right')

        if(route.name == 'orders'){
          await loadOrderStatusList();
          await getOrders(false);
        }
  
        timeoutId = setTimeout(() => {
          hasNewOrder.value = false;
          scheduleNextCheck(8000);
        }, 10000);
      } else {
        scheduleNextCheck(10000);
      }
    } catch (error) {
      console.error("Error checking new order status:", error);
      scheduleNextCheck(10000);
    }
  };
  
  const scheduleNextCheck = (delay) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(checkNewOrderStatus, delay);
  };
  
  // Start tracking orders
  onMounted(() => {
    detectInternetState((data: {type: "success" | "info" | "warning" | "danger", message: string}) => {
      showNotification(data)
    })
    checkNewOrderStatus()
  });

  return {
    hasNewOrder,
  };
};
