import { Link, useLocation } from "react-router-dom";
import {
  FaChartLine,
  FaCompass,
  FaEnvelopeOpenText,
  FaBoxOpen,
} from "react-icons/fa";
import { useSelector } from "react-redux";

const MainMenu = ({ style = "" }) => {
  const { pathname } = useLocation();
  const { userRole } = useSelector((state) => state.auth);

  const isManagerOrSalesHead =
    userRole === "Sales Head(DMC)" ||
    userRole === "Sales Manager (DMC)" ||
    userRole === "Assistant Manager (DMC)";
    
  // Determine packages path based on user role
  const packagesPath = userRole === "Agent" 
    ? "/dashboard/pre-define-packages" 
    : "/dashboard/db-dashboard/tour-packages";

  // Common style for menu items
  const menuItemStyle = {
    marginRight: "25px",
  };

  return (
    <nav className="menu js-navList">
      <ul className={`menu__nav ${style} -is-active`} style={{ display: "flex" }}>
        <li
          className={`menu-item ${
            pathname === "/dashboard/db-dashboard" ? "current" : ""
          }`}
          style={menuItemStyle}
        >
          <Link
            to="/dashboard/db-dashboard"
            className="d-flex items-center px-15 py-10 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
          >
            <div style={{ minWidth: "40px", display: "flex", justifyContent: "center" }}>
              <FaChartLine className="text-22 text-green-1" />
            </div>
            <span
              className={`fw-600 text-15 ${
                pathname === "/dashboard/db-dashboard" ? "text-green-1" : ""
              }`}
            >
              Dashboard
              <span className="text-12 text-light-1 fw-400 ml-8 block"> &nbsp;
                Manage Your Experience
              </span>
            </span>
          </Link>
        </li>

        {!isManagerOrSalesHead && (
          <li
            className={`menu-item ${
              pathname === "/dashboard/db-dashboard/home_1" ? "current" : ""
            }`}
            style={menuItemStyle}
          >
            <Link
              to="/dashboard/db-dashboard/home_1"
              className="d-flex items-center px-15 py-10 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
            >
              <div style={{ minWidth: "40px", display: "flex", justifyContent: "center" }}>
                <FaCompass className="text-22 text-green-1" />
              </div>
              <span
                className={`fw-600 text-15 ${
                  pathname === "/dashboard/db-dashboard/home_1"
                    ? "text-green-1"
                    : ""
                }`}
              >
                Book Tour
                <span className="text-12 text-light-1 fw-400 ml-8 block">
                  Discover Your Dream Destinations
                </span>
              </span>
            </Link>
          </li>
        )}

        {!isManagerOrSalesHead && (
          <li
            className={`menu-item ${
              pathname === "/dashboard/db-dashboard/home_2" ? "current" : ""
            }`}
            style={menuItemStyle}
          >
            <Link
              to="/dashboard/db-dashboard/home_2"
              className="d-flex items-center px-15 py-10 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
            >
              <div style={{ minWidth: "40px", display: "flex", justifyContent: "center" }}>
                <FaEnvelopeOpenText className="text-22 text-green-1" />
              </div>
              <span
                className={`fw-600 text-15 ${
                  pathname === "/dashboard/db-dashboard/home_2"
                    ? "text-green-1"
                    : ""
                }`}
              >
                Book An Enquiry
                <span className="text-12 text-light-1 fw-400 ml-8 block">
                  Reach Out for Custom Requests
                </span>
              </span>
            </Link>
          </li>
        )}

        <li
          className={`menu-item ${
            pathname === packagesPath ? "current" : ""
          }`}
        >
          <Link
            to={packagesPath}
            className="d-flex items-center px-15 py-10 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
          >
            <div style={{ minWidth: "40px", display: "flex", justifyContent: "center" }}>
              <FaBoxOpen className="text-22 text-green-1" />
            </div>
            <span
              className={`fw-600 text-15 ${
                pathname === packagesPath ? "text-green-1" : ""
              }`}
            >
              Packages
              <span className="text-12 text-light-1 fw-400 ml-8 block">
              Custom Travel Bundles
              </span>
            </span>
          </Link>
        </li>
      </ul>
    </nav>
  );
};

export default MainMenu;
