import { Link, useLocation, useNavigate } from "react-router-dom";
import React, { useState } from "react";
import {
  FaChartLine,
  FaCompass,
  FaEnvelopeOpenText,
  FaBoxOpen,
} from "react-icons/fa";
import { useSelector } from "react-redux";
import DMCSelectionModal from "../common/DMCSelectionModal";
import SearchLocationModal from "../common/SearchLocationModal";

const MainMenu = ({ style = "" }) => {
  const { pathname } = useLocation();
  const navigate = useNavigate();
  const { userRole } = useSelector((state) => state.auth);
  const [isSearchModalOpen, setIsSearchModalOpen] = useState(false);
  const [isDMCModalOpen, setIsDMCModalOpen] = useState(false);
  const [searchCriteria, setSearchCriteria] = useState(null);

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

  const handleBookTourClick = (e) => {
    e.preventDefault();
    setIsSearchModalOpen(true);
  };

  const handleSearchSubmit = (searchData) => {
    setSearchCriteria(searchData);
    setIsSearchModalOpen(false);
    setIsDMCModalOpen(true);
  };

  const handleDMCSelect = (selectedDMC) => {
    console.log('Selected DMC:', selectedDMC);
    console.log('Search Criteria:', searchCriteria);
    // Navigate to the tour booking page with the selected DMC and search criteria
    navigate("/dashboard/db-dashboard/home_1", { 
      state: { 
        selectedDMC,
        searchCriteria 
      } 
    });
  };

  const handleCloseSearchModal = () => {
    setIsSearchModalOpen(false);
  };

  const handleCloseDMCModal = () => {
    setIsDMCModalOpen(false);
    setSearchCriteria(null);
  };

  return (
    <>
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
              style={{ flexDirection: "column", alignItems: "flex-start" }}
             
            >
              
               
                  
              <div style={{ display: "flex", alignItems: "center" }}>
                <FaChartLine className="text-22 text-green-1" style={{ marginRight: "8px" }} />
                <span
                  className={`fw-600 text-15 ${
                    pathname === "/dashboard/db-dashboard" ? "text-green-1" : ""
                  }`}
                >
                  Dashboard
                </span>
              </div>
              <div className="text-12 text-light-1 fw-400 mt-4" style={{ marginLeft: "30px" }}>
                Manage Your Experience
              </div>
             
            </Link>
          </li>

          {!isManagerOrSalesHead && (
            <li
              className={`menu-item ${
                pathname === "/dashboard/db-dashboard/home_1" ? "current" : ""
              }`}
              style={menuItemStyle}
            >
              <a
                href="#"
                onClick={handleBookTourClick}
                className="d-flex items-center px-15 py-10 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
                style={{ flexDirection: "column", alignItems: "flex-start" }}
               
              >
                <div style={{ display: "flex", alignItems: "center" }}>
                  <FaCompass className="text-22 text-green-1" style={{ marginRight: "8px" }} />
                  <span
                    className={`fw-600 text-15 ${
                      pathname === "/dashboard/db-dashboard/home_1"
                        ? "text-green-1"
                        : ""
                    }`}
                  >
                    Book Tour
                  </span> 
                </div>
                <div className="text-12 text-light-1 fw-400 mt-4" style={{ marginLeft: "30px" }}>
                  Discover Your Dream Destinations
                </div>
              </a>
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
                style={{ flexDirection: "column", alignItems: "flex-start" }}
              >
                <div style={{ display: "flex", alignItems: "center" }}>
                  <FaEnvelopeOpenText className="text-22 text-green-1" style={{ marginRight: "8px" }} />
                  <span
                    className={`fw-600 text-15 ${
                      pathname === "/dashboard/db-dashboard/home_2"
                        ? "text-green-1"
                        : ""
                    }`}
                  >
                    Book An Enquiry
                  </span>
                </div>
                <div className="text-12 text-light-1 fw-400 mt-4" style={{ marginLeft: "30px" }}>
                  Reach Out for Custom Requests
                </div>
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
              style={{ flexDirection: "column", alignItems: "flex-start" }}
            >
              <div style={{ display: "flex", alignItems: "center" }}>
                <FaBoxOpen className="text-22 text-green-1" style={{ marginRight: "8px" }} />
                <span
                  className={`fw-600 text-15 ${
                    pathname === packagesPath ? "text-green-1" : ""
                  }`}
                >
                  Packages
                </span>
              </div>
              <div className="text-12 text-light-1 fw-400 mt-4" style={{ marginLeft: "30px" }}>
                Custom Travel Bundles
              </div>
            </Link>
          </li>
        </ul>
      </nav>

      <SearchLocationModal
        open={isSearchModalOpen}
        onClose={handleCloseSearchModal}
        onSearch={handleSearchSubmit}
      />

      <DMCSelectionModal
        open={isDMCModalOpen}
        onClose={handleCloseDMCModal}
        onSelect={handleDMCSelect}
        searchCriteria={searchCriteria}
      />
    </>
  );
};


export default MainMenu;
