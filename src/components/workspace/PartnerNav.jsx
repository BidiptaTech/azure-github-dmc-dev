import { NavLink } from "react-router-dom";
import { appPath } from "@/utils/appPaths";

const NAV_ITEMS = [
  { label: "Overview", to: appPath() },
  { label: "Plan a trip", to: appPath("plan") },
  { label: "My bookings", to: appPath("bookings") },
  { label: "Quotes & requests", to: appPath("quotes") },
  { label: "Wallet & payments", to: appPath("wallet") },
  { label: "Packages", to: appPath("packages") },
];

export default function PartnerNav() {
  return (
    <nav className="partner-nav" aria-label="Partner workspace">
      {NAV_ITEMS.map((item) => (
        <NavLink
          key={item.to}
          to={item.to}
          end={item.to === appPath()}
          // Clear trip-builder state when opening Plan from nav
          state={item.to === appPath("plan") ? {} : undefined}
          className={({ isActive }) =>
            `partner-nav__link${isActive ? " is-active" : ""}`
          }
        >
          {item.label}
        </NavLink>
      ))}
    </nav>
  );
}
