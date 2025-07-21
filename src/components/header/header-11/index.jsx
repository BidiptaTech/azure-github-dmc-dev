import { Link, useNavigate } from "react-router-dom";
import axios from "axios";
import { useEffect, useState } from "react";
import MainMenu from "../MainMenu";
import CurrenctyMegaMenu from "../CurrenctyMegaMenu";
import LanguageMegaMenu from "../LanguageMegaMenu";
import Cookies from "js-cookie";
import { useDispatch, useSelector } from "react-redux";
import { logout, logoutUser } from "@/slice/common/authSlices";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";
import LogoutIcon from '@mui/icons-material/Logout';

import MobileMenu from "../MobileMenu";

const Header1 = () => {
  const [navbar, setNavbar] = useState(false);
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const userRole = useSelector((state) => state.auth.userRole);
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);

  const handleLogout = () => {
    dispatch(logoutUser());
  };

  const changeBackground = () => {
    if (window.scrollY >= 10) {
      setNavbar(true);
    } else {
      setNavbar(false);
    }
  };

  useEffect(() => {
    window.addEventListener("scroll", changeBackground);
    return () => {
      window.removeEventListener("scroll", changeBackground);
    };
  }, []);

  return (
    <>
      <header className={`header bg-dark-3 ${navbar ? "is-sticky" : ""}`} style={{ padding: "5px 0" }}>
        <div className="header__container px-30 sm:px-20">
          <div className="row justify-between items-center" style={{ position: "relative" }}>
            <div className="col-auto" style={{ width: "85%", marginRight: "auto" }}>
              <div className="d-flex items-center">
                <Link to="/dashboard/db-dashboard" className="header-logo mr-25">
                  <div
                    style={{
                      display: "inline-block",
                      borderRadius: "12px",
                      padding: "4px",
                    }}
                  >
                    <img
                      src={dmcLogo}
                      alt="logo icon"
                      style={{
                        width: "75px",
                        height: "42px",
                        borderRadius: "10px",
                      }}
                    />
                  </div>
                </Link>
                {/* End logo */}

                <div className="header-menu" style={{ marginTop: "5px" }}>
                  <div className="header-menu__content">
                    <MainMenu style="text-white" />
                  </div>
                </div>
                {/* End header-menu */}
              </div>
              {/* End d-flex */}
            </div>
            {/* End col */}

            <div className="col-auto" style={{ position: "absolute", right: "10px", top: "50%", transform: "translateY(-50%)" }}>
              <div className="d-flex items-center">
                {/* Logout button positioned to the far right */}
                {isAuthenticated && userRole !== "Agent" && (
                  <div className="d-flex items-center mr-15">
                    <Link
                      onClick={handleLogout}
                      className="button px-15 fw-500 text-14 border-white -outline-white h-40 text-white hover:bg-white hover:text-dark-1 flex items-center gap-2"
                      style={{
                        borderRadius: "6px",
                      }}
                    >
                      <LogoutIcon className="text-18" />
                      <span>Logout</span>
                    </Link>
                  </div>
                )}

                {/* Start mobile menu icon */}
                <div className="d-none xl:d-flex x-gap-20 items-center text-white">
                  <div>
                    <button
                      className="d-flex items-center icon-menu text-inherit text-20"
                      data-bs-toggle="offcanvas"
                      aria-controls="mobile-sidebar_menu"
                      data-bs-target="#mobile-sidebar_menu"
                    />

                    <div
                      className="offcanvas offcanvas-start mobile_menu-contnet"
                      tabIndex="-1"
                      id="mobile-sidebar_menu"
                      aria-labelledby="offcanvasMenuLabel"
                      data-bs-scroll="true"
                    >
                      <MobileMenu />
                      {/* End MobileMenu */}
                    </div>
                  </div>
                </div>
                {/* End mobile menu icon */}
              </div>
            </div>
            {/* End col-auto */}
          </div>
          {/* End .row */}
        </div>
        {/* End header_container */}
      </header>
    </>
  );
};

export default Header1;
