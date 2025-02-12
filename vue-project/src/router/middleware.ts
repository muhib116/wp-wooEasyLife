import { useLicense } from "@/pages/config/license/UseLicense";
import {
  licenseKey,
  isValidLicenseKey
} from '@/service/useServiceProvider';

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

      // Allow navigation to the intended route
      return next();
    } catch (error) {
      console.error("Error in route guard:", error);
      return next({ name: "license" });
    }
  });

  return router;
}