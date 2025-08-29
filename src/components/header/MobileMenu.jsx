import { Link } from "react-router-dom";
import { useLocation } from "react-router-dom";
import { Sidebar, Menu, MenuItem, SubMenu } from "react-pro-sidebar";
import {
  homeItems,
  blogItems,
  pageItems,
  dashboardItems,
  categorieMobileItems,
  categorieMegaMenuItems,
} from "../../data/mainMenuData";
import { isActiveLink } from "../../utils/linkActiveChecker";
import Social from "../common/social/Social";
import ContactInfo from "./ContactInfo";
import { useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import { logoutUser } from "@/slice/common/authSlices";
import { clearSelectedDmc } from "@/slice/dmc/dmcSlice";
import LogoutIcon from '@mui/icons-material/Logout';

const MobileMenu = () => {
  const { pathname } = useLocation();
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const { userRole } = useSelector((state) => state.auth);
  const dispatch = useDispatch();
  const [isActiveParent, setIsActiveParent] = useState(false);
  const [isActiveNestedParentTwo, setisActiveNestedParentTwo] = useState(false);
  const [isActiveNestedParent, setisActiveNestedParent] = useState(false);
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);
  const navigate = useNavigate();

  // Check if the user is a manager or sales head
  const isManagerOrSalesHead = userRole === "Sales Head(DMC)" || userRole === "Sales Manager (DMC)"|| userRole === "Assistant Manager (DMC)";

  const handleLogout = async () => {
    dispatch(clearSelectedDmc()); // Clear DMC selection on logout
    const result = await dispatch(logoutUser());
    if (logoutUser.fulfilled.match(result)) {
      navigate("/login");
    }
  };

  useEffect(() => {
    categorieMegaMenuItems.map((megaMenu) => {
      megaMenu?.menuCol?.map((megaCol) => {
        megaCol?.menuItems?.map((item) => {
          item?.menuList?.map((list) => {
            if (list.routePath?.split("/")[1] == pathname.split("/")[1]) {
              setIsActiveParent(true);
              setisActiveNestedParentTwo(item?.title);
              setisActiveNestedParent(megaMenu?.id);
            }
          });
        });
      });
    });
  }, []);

  return (
    <>
      <div className="pro-header d-flex align-items-center justify-between border-bottom-light">
        <Link to="/">
          <img
            src={dmcLogo}
            style={{ width: "70px", height: "50px", borderRadius: "10px" }}
            alt="brand"
          />
        </Link>
        {/* End logo */}

        <div
          className="fix-icon"
          data-bs-dismiss="offcanvas"
          aria-label="Close"
        >
          <i className="icon icon-close"></i>
        </div>
        {/* icon close */}
      </div>
      {/* End pro-header */}

      <Sidebar width="400" backgroundColor="#fff">
        <Menu style={{ padding: "10px 0" }}>
          <MenuItem
            onClick={() => navigate("/dashboard/db-dashboard")}
            className={
              pathname === "/dashboard/db-dashboard" ? "menu-active-link" : ""
            }
            style={{ margin: "5px 0" }}
          >
            Dashboard
          </MenuItem>
          
          {/* Only show Book Tour for non-manager roles */}
          {!isManagerOrSalesHead && (
            <MenuItem
              onClick={() => navigate("/dashboard/db-dashboard/home_1")}
              className={
                pathname === "/dashboard/db-dashboard/home_1"
                  ? "menu-active-link"
                  : ""
              }
              style={{ margin: "5px 0" }}
            >
              Book Tour
            </MenuItem>
          )}
          
          {/* Only show Book An Enquiry for non-manager roles */}
          {!isManagerOrSalesHead && (
            <MenuItem
              onClick={() => navigate("/dashboard/db-dashboard/home_2")}
              className={
                pathname === "/dashboard/db-dashboard/home_2"
                  ? "menu-active-link"
                  : ""
              }
              style={{ margin: "5px 0" }}
            >
              Quick Enquiry
            </MenuItem>
          )}
          
          <MenuItem
            onClick={() => navigate("/dashboard/db-dashboard/create-package")}
            className={
              pathname === "/dashboard/db-dashboard/create-package"
                ? "menu-active-link"
                : ""
            }
            style={{ margin: "5px 0" }}
          >
            Create Package
          </MenuItem>

          {isAuthenticated && (
            <MenuItem
              onClick={handleLogout}
              style={{ 
                margin: "15px 0 5px 0",
                display: "flex", 
                alignItems: "center", 
                padding: "10px 15px",
                background: "#ff4747",
                color: "white",
                borderRadius: "4px",
                fontWeight: "500"
              }}
            >
              <LogoutIcon style={{ fontSize: "20px", marginRight: "8px" }} /> Logout
            </MenuItem>
          )}
        </Menu>
      </Sidebar>

      <div className="mobile-footer px-20 py-5 border-top-light"></div>

      <div className="pro-footer">
        <ContactInfo />
        <div className="mt-10">
          <h5 className="text-16 fw-500 mb-10">Follow us on social media</h5>
          <div className="d-flex x-gap-20 items-center">
            <Social />
          </div>
        </div>
      </div>
      {/* End pro-footer */}
    </>
  );
};

export default MobileMenu;
