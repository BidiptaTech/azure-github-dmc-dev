import { useEffect, useState } from "react";
import { Outlet, useLocation, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { Menu, MenuItem } from "@mui/material";
import PartnerNav from "@/components/workspace/PartnerNav";
import AgentProfileModal from "@/components/header/AgentProfileModal";
import { logoutUser } from "@/slice/common/authSlices";
import { clearSelectedDmc } from "@/slice/dmc/dmcSlice";
import { setAgentId as setAgentIdEdit } from "@/slice/common/EditSlice";
import { clearBookingFlow } from "@/utils/clearBookingFlow";
import { APP_BASE, appPath } from "@/utils/appPaths";

const BOOKING_FLOW_SEGMENTS = [
  "view-hotel-search",
  "hotel-details",
  "hotel-checkout",
  "thank-you",
  "attractions",
  "restaurants",
  "pickupdrop",
  "localtransfer",
  "tourguide",
  "tour-single",
  "restaurants-details",
  "activity-single",
  "restaurants-checkout",
  "attraction-checkout",
  "restaurants-thank-you",
  "attraction-thank-you",
  "CheckOut",
  "ThankYou",
  "updatebooking",
  "cart",
  "cart-checkout",
  "tour-packages-page",
  "home_1",
  "home_2",
  "pre-define-packages",
  "booking-enquiry",
  "package-details",
  "plan",
];

export default function PartnerLayout() {
  const location = useLocation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const username = useSelector((state) => state.auth.Username);
  const agencyLogo = useSelector((state) => state.auth.agencyLogo);
  const dmcLogo = useSelector((state) => state.auth.dmcLogo);
  const userRole = useSelector((state) => state.auth.userRole);
  const [userMenuAnchor, setUserMenuAnchor] = useState(null);
  const [profileOpen, setProfileOpen] = useState(false);

  const initials =
    (username || "Agent")
      .split(" ")
      .filter(Boolean)
      .slice(0, 2)
      .map((part) => part[0]?.toUpperCase())
      .join("") || "A";

  const logoSrc =
    userRole === "Agent" && agencyLogo
      ? agencyLogo
      : dmcLogo || "/Images/logo.png";

  useEffect(() => {
    const path = location.pathname || "";
    if (!path.startsWith(APP_BASE)) return;

    const stillInBookingFlow = BOOKING_FLOW_SEGMENTS.some((segment) =>
      path.includes(segment)
    );
    if (stillInBookingFlow) return;

    clearBookingFlow(dispatch);
  }, [location.pathname, dispatch]);

  const handleLogout = () => {
    setUserMenuAnchor(null);
    dispatch(setAgentIdEdit(null));
    dispatch(logoutUser());
    dispatch(clearSelectedDmc());
    navigate("/login");
  };

  return (
    <div className="partner-shell">
      <header className="partner-topbar">
        <button
          type="button"
          className="partner-brand"
          onClick={() => navigate(appPath())}
          aria-label="Travclicks home"
        >
          {logoSrc ? (
            <img
              className="partner-brand__logo"
              src={logoSrc}
              alt="Travclicks"
              onError={(e) => {
                e.currentTarget.onerror = null;
                e.currentTarget.src = "/Images/logo.png";
              }}
            />
          ) : (
            <span className="partner-brand__text">
              <span className="partner-brand__name">Travclicks</span>
              <span className="partner-brand__tag">
                Travel Technology Transformed
              </span>
            </span>
          )}
        </button>

        <PartnerNav />

        <div className="partner-topbar__end">
          <button
            type="button"
            className="partner-avatar-btn"
            onClick={(e) => setUserMenuAnchor(e.currentTarget)}
            aria-label="Account menu"
          >
            {initials}
          </button>
          <Menu
            anchorEl={userMenuAnchor}
            open={Boolean(userMenuAnchor)}
            onClose={() => setUserMenuAnchor(null)}
          >
            <MenuItem
              onClick={() => {
                setUserMenuAnchor(null);
                setProfileOpen(true);
              }}
            >
              Profile
            </MenuItem>
            <MenuItem onClick={() => navigate(appPath("cart"))}>Cart</MenuItem>
            <MenuItem onClick={handleLogout}>Log out</MenuItem>
          </Menu>
        </div>
      </header>

      <main
        className={`partner-main${
          /view-hotel-search|hotel-details|pickupdrop|attractions|tourguide|restaurants|localtransfer/i.test(
            location.pathname
          )
            ? " partner-main--wide"
            : ""
        }`}
      >
        <Outlet />
      </main>

      <AgentProfileModal
        open={profileOpen}
        onClose={() => setProfileOpen(false)}
      />
    </div>
  );
}
