import { useLicense } from "@/pages/config/license/UseLicense";
import {
  userData,
  licenseKey,
  isValidLicenseKey
} from '@/service/useServiceProvider';

const premiumRoutesName = [
  'missingOrders',
  'fraudCheck',
];

export default function (router) {
  const { loadLicenseKey } = useLicense(false);

  router.beforeEach(async (to, from, next) => {
    try {
      // Allow access to the license page without validation
      if (to.name === "license") {
        return next();
      }

      // Ensure the license key is loaded before proceeding
      if (!licenseKey.value) {
        await loadLicenseKey();
      }

      // Wait for reactivity to update
      await new Promise(resolve => setTimeout(resolve, 100));

      // Redirect to license page if the license is invalid or missing
      if (!isValidLicenseKey.value) {
        return next({ name: "license" });
      }

      // Restrict premium routes if balance is depleted
      if (premiumRoutesName.includes(to.name) && (userData.value?.remaining_order ?? 0) <= 0) {
        return next({ name: "RestrictionAlert" });
      }

      // Allow navigation to the intended route
      return next();
    } catch (error) {
      console.error("Error in route guard:", error);
      return next({ name: "license" });
    }
  });

  return router;
}