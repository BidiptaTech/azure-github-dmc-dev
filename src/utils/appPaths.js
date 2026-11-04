/** Partner workspace base path (replaces /app). */
export const APP_BASE = "/app";

export function appPath(suffix = "") {
  if (!suffix || suffix === "/") return APP_BASE;
  const s = suffix.startsWith("/") ? suffix : `/${suffix}`;
  return `${APP_BASE}${s}`;
}

/** New enquiry lives under Quotes & requests. */
export const QUOTES_ENQUIRY_PATH = appPath("quotes/booking-enquiry");

/** Package flows live under Packages. */
export const PACKAGES_SEARCH_PATH = appPath("packages/pre-define-packages");
export const PACKAGES_BUILDER_PATH = appPath("packages/tour-packages-page");
export function packageDetailsPath(id) {
  return appPath(`packages/package-details/${id}`);
}

/** Section / home paths where leaving a booking module should clear flow state. */
export const APP_HOME_PATHS = [APP_BASE, `${APP_BASE}/`, appPath("bookings")];

export function isAppHomePath(pathname = "") {
  const p = pathname.replace(/\/$/, "") || APP_BASE;
  return p === APP_BASE || p === appPath("bookings");
}
