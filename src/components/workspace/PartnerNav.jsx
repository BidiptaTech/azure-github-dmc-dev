import { NavLink, useLocation } from "react-router-dom";
import { appPath } from "@/utils/appPaths";

const NAV_ITEMS = [
  { label: "Dashboard", to: appPath(), end: true },
  { label: "My bookings", to: appPath("bookings"), match: ["bookings"] },
  {
    label: "Quotes & requests",
    to: appPath("quotes"),
    match: ["quotes"],
  },
  { label: "Wallet & payments", to: appPath("wallet"), match: ["wallet"] },
  {
    label: "Packages",
    to: appPath("packages"),
    match: ["packages"],
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
            className={`partner-nav__link${active ? " is-active" : ""}`}
          >
            {item.label}
          </NavLink>
        );
      })}
    </nav>
  );
}
