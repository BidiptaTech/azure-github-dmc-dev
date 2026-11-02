/** Partner workspace base path (replaces /app). */
export const APP_BASE = "/app";

export function appPath(suffix = "") {
  if (!suffix || suffix === "/") return APP_BASE;
  const s = suffix.startsWith("/") ? suffix : `/${suffix}`;
  return `${APP_BASE}${s}`;
}

/** Section / home paths where leaving a booking module should clear flow state. */
export const APP_HOME_PATHS = [APP_BASE, `${APP_BASE}/`, appPath("bookings")];

export function isAppHomePath(pathname = "") {
  const p = pathname.replace(/\/$/, "") || APP_BASE;
  return p === APP_BASE || p === appPath("bookings");
}
