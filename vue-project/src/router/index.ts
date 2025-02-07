import {
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from "vue-router";
import HomeView from "@/pages/dashboard/Index.vue";
import RestrictionAlert from "@/pages/RestrictionAlert.vue";
import OrdersView from "@/pages/orders/Index.vue";
import MissingOrderView from "@/pages/missingOrder/Index.vue";
import FraudCheckerView from "@/pages/fraudChecker/Index.vue";
import ConfigView from "@/pages/config/Index.vue";
import SMSConfigView from "@/pages/config/smsConfig/Index.vue";
import LicenseView from "@/pages/config/license/Index.vue";
import SendSmsView from "@/pages/config/sendSms/Index.vue";
import RechargeView from "@/pages/config/recharge/Index.vue";
import IntegrationView from "@/pages/config/integration/Index.vue";
import CourierView from "@/pages/config/courier/Index.vue";
import CustomStatusView from "@/pages/config/customStatus/Index.vue";
import CustomBlackList from "@/pages/config/customBlackList/Index.vue";
import MarketingTools from "@/pages/config/marketing-tools/Index.vue";
import middleware from "./middleware";
import OpenAiView from '@/openAi/Index.vue'
import NotFound from '@/pages/404NotFound/Index.vue'

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: "/open-ai",
      name: "openAi",
      component: OpenAiView,
    },
    {
      path: "/",
      name: "dashboard",
      component: HomeView,
    },
    {
      path: "/orders",
      name: "orders",
      component: OrdersView,
    },
    {
      path: "/missing-orders",
      name: "missingOrders",
      component: MissingOrderView,
    },
    {
      path: "/restriction-alert",
      name: "RestrictionAlert",
      component: RestrictionAlert,
    },
    {
      path: "/fraud-check",
      name: "fraudCheck",
      component: FraudCheckerView,
    },
    {
      path: "/config",
      name: "config",
      component: ConfigView,
      children: [
        {
          path: "license",
          name: "license",
          component: LicenseView,
        },
        {
          path: "sms-config",
          name: "smsConfig",
          component: SMSConfigView,
        },
        {
          path: "send-sms",
          name: "sendSms",
          component: SendSmsView,
        },
        {
          path: "sms-recharge",
          name: "smsRecharge",
          component: RechargeView,
        },
        {
          path: "integration",
          name: "integration",
          component: IntegrationView,
        },
        {
          path: "courier",
          name: "courier",
          component: CourierView,
        },
        {
          path: "custom-status",
          name: "customStatus",
          component: CustomStatusView,
        },
        {
          path: "custom-black-list",
          name: "blackList",
          component: CustomBlackList,
        },
        {
          path: "marketing-tools",
          name: "marketingTools",
          component: MarketingTools,
        },
      ],
    },


    // Catch-all 404 route (Fallback)
    { path: "/:pathMatch(.*)*", name: "NotFound", component: NotFound },
  ],
});

export default middleware(router);
