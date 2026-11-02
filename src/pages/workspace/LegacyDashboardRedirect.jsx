import { Navigate, useLocation } from "react-router-dom";
import { APP_BASE } from "@/utils/appPaths";

/** Map /dashboard/db-dashboard[/...] → /app[/...] */
export default function LegacyDashboardRedirect() {
  const location = useLocation();
  const rest = location.pathname.replace(/^\/dashboard\/db-dashboard/, "") || "";
  const target = `${APP_BASE}${rest}` || APP_BASE;
  return <Navigate to={`${target}${location.search || ""}`} replace state={location.state} />;
}
