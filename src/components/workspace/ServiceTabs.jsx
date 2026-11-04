import { useLocation, useNavigate } from "react-router-dom";
import { useSelector } from "react-redux";
import { appPath } from "@/utils/appPaths";

const TABS = [
  { label: "Hotels", match: /view-hotel-search|hotel-details|hotel-checkout/i, path: (id) => appPath(`view-hotel-search/${id || 0}`) },
  { label: "Entry / Exit Ports", match: /pickupdrop/i, path: () => appPath("pickupdrop") },
  { label: "Attraction & Experiences", match: /attractions|tour-single|attraction-/i, path: () => appPath("attractions") },
  { label: "Guides", match: /tourguide|activity-single/i, path: () => appPath("tourguide") },
  { label: "Restaurants & Dinnings", match: /restaurants/i, path: () => appPath("restaurants") },
  { label: "Local Transfer", match: /localtransfer/i, path: () => appPath("localtransfer") },
];

export default function ServiceTabs() {
  const navigate = useNavigate();
  const { pathname } = useLocation();
  const tourId = useSelector((state) => state.hotels?.id || state.steps?.tourId || 0);

  return (
    <div className="pw-service-tabs" role="tablist" aria-label="Trip services">
      {TABS.map((tab) => {
        const active = tab.match.test(pathname);
        return (
          <button
            key={tab.label}
            type="button"
            role="tab"
            aria-selected={active}
            className={`pw-service-tabs__btn${active ? " is-active" : ""}`}
            onClick={() => navigate(tab.path(tourId))}
          >
            {tab.label}
          </button>
        );
      })}
    </div>
  );
}
