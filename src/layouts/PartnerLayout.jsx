import { useEffect, useState } from "react";
import { Outlet, useLocation, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { Divider, Menu, MenuItem } from "@mui/material";
import KeyboardArrowDownIcon from "@mui/icons-material/KeyboardArrowDown";
import PersonOutlineIcon from "@mui/icons-material/PersonOutline";
import ShoppingCartOutlinedIcon from "@mui/icons-material/ShoppingCartOutlined";
import LogoutOutlinedIcon from "@mui/icons-material/LogoutOutlined";
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
  const email = useSelector((state) => state.auth.Email);
  const agencyLogo = useSelector((state) => state.auth.agencyLogo);
  const dmcLogo = useSelector((state) => state.auth.dmcLogo);
  const userRole = useSelector((state) => state.auth.userRole);
  const agentCompanyName = useSelector((state) => state.auth.agentCompanyName);
  const dmcCompanyName = useSelector((state) => state.auth.dmcCompanyName);
  const [userMenuAnchor, setUserMenuAnchor] = useState(null);
  const [profileOpen, setProfileOpen] = useState(false);

  const displayName = username || "Agent";
  const companyLabel =
    userRole === "Agent"
      ? agentCompanyName
      : dmcCompanyName || agentCompanyName;
  const roleLabel = userRole || "Agent";

  const initials =
    displayName
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

        <button
          type="button"
          className="partner-account-btn"
          onClick={(e) => setUserMenuAnchor(e.currentTarget)}
          aria-label="Account menu"
          aria-haspopup="menu"
          aria-expanded={Boolean(userMenuAnchor)}
        >
          <span className="partner-avatar-btn" aria-hidden="true">
            {initials}
          </span>
          <span className="partner-account-btn__meta">
            <span className="partner-account-btn__name">{displayName}</span>
            {roleLabel ? (
              <span className="partner-account-btn__role">{roleLabel}</span>
            ) : null}
          </span>
          <KeyboardArrowDownIcon className="partner-account-btn__chevron" />
        </button>
        <Menu
          anchorEl={userMenuAnchor}
          open={Boolean(userMenuAnchor)}
          onClose={() => setUserMenuAnchor(null)}
          anchorOrigin={{ horizontal: "right", vertical: "bottom" }}
          transformOrigin={{ horizontal: "right", vertical: "top" }}
          PaperProps={{
            className: "partner-account-menu",
            elevation: 0,
          }}
        >
          <div className="partner-account-menu__header">
            <span className="partner-avatar-btn" aria-hidden="true">
              {initials}
            </span>
            <div className="partner-account-menu__identity">
              <strong>{displayName}</strong>
              {email ? <span>{email}</span> : null}
              <span className="partner-account-menu__role">
                {[roleLabel, companyLabel].filter(Boolean).join(" · ")}
              </span>
            </div>
          </div>
          <Divider className="partner-account-menu__divider" />
          <MenuItem
            className="partner-account-menu__item"
            onClick={() => {
              setUserMenuAnchor(null);
              setProfileOpen(true);
            }}
          >
            <PersonOutlineIcon fontSize="small" />
            View profile
          </MenuItem>
          <MenuItem
            className="partner-account-menu__item"
            onClick={() => {
              setUserMenuAnchor(null);
              navigate(appPath("cart"));
            }}
          >
            <ShoppingCartOutlinedIcon fontSize="small" />
            Cart
          </MenuItem>
          <Divider className="partner-account-menu__divider" />
          <MenuItem
            className="partner-account-menu__item partner-account-menu__item--danger"
            onClick={handleLogout}
          >
            <LogoutOutlinedIcon fontSize="small" />
            Sign out
          </MenuItem>
        </Menu>
      </header>

      <main
        className={`partner-main${
          /view-hotel-search|hotel-details|pickupdrop|attractions|tourguide|restaurants|localtransfer|booking-enquiry|tour-packages-page|pre-define-packages|packages/i.test(
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
