export const baseUrl =
  location.hostname === "localhost"
    ? import.meta.env.DEV
      ? "http://localhost:8080/wordpress"
      : window?.wooEasyLife?.site_url || location.origin
    : location.origin;

export const localApiBaseURL = `${baseUrl}/wp-json/wooeasylife/v1`;
