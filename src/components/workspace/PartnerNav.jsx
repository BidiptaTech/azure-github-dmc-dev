import { NavLink, useLocation } from "react-router-dom";
import { appPath } from "@/utils/appPaths";

const NAV_ITEMS = [
  { label: "Overview", to: appPath(), end: true },
  { label: "Plan a trip", to: appPath("plan"), match: ["plan"] },
  { label: "My bookings", to: appPath("bookings"), match: ["bookings"] },
  {
    label: "Quotes & requests",
    to: appPath("quotes"),
    match: ["quotes", "booking-enquiry"],
  },
  { label: "Wallet & payments", to: appPath("wallet"), match: ["wallet"] },
  {
    label: "Packages",
    to: appPath("packages"),
    match: ["packages", "tour-packages-page", "pre-define-packages", "package-details"],
  },
];

function pathIsActive(pathname, item) {
  const path = (pathname || "").replace(/\/$/, "") || appPath();
  if (item.end) {
    return path === appPath();
  }
  if (item.match?.length) {
    const segments = path.split("/").filter(Boolean);
    return item.match.some((segment) => segments.includes(segment));
  }
  return path === item.to || path.startsWith(`${item.to}/`);
}

export default function PartnerNav() {
  const { pathname } = useLocation();

  return (
    <nav className="partner-nav" aria-label="Partner workspace">
      {NAV_ITEMS.map((item) => {
        const active = pathIsActive(pathname, item);
        return (
          <NavLink
            key={item.to}
            to={item.to}
            end={Boolean(item.end)}
            // Clear trip-builder state when opening Plan from nav
            state={item.to === appPath("plan") ? {} : undefined}
            className={`partner-nav__link${active ? " is-active" : ""}`}
          >
            {item.label}
          </NavLink>
        );
      })}
    </nav>
  );
}
