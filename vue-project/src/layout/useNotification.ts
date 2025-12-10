import { checkHasNewOrder } from "@/api";
import { showNotification, detectInternetState } from "@/helper";
import { inject, onBeforeUnmount, onMounted, ref } from "vue";
import { useRoute } from "vue-router"

export const useNotification = () => {
  const route = useRoute()
  const { getOrders, loadOrderStatusList } = inject("useOrders", {});
  const hasNewOrder = ref(false);

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
        })

        if (route.name == 'orders') {
          await loadOrderStatusList();
          // await getOrders(false);
        }
      }
    } catch (error) {
      console.error("Error checking new order status:", error)
    }
  }

  return {
    hasNewOrder,
    checkNewOrderStatus
  };
};
