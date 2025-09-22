import { Link, useLocation, useNavigate } from "react-router-dom";
import React, { useState, useEffect } from "react";
import {
  FaChartLine,
  FaCompass,
  FaEnvelopeOpenText,
  FaBoxOpen,
} from "react-icons/fa";
import { useSelector, useDispatch } from "react-redux";
import DMCSelectionModal from "../common/DMCSelectionModal";
import SearchLocationModal from "../common/SearchLocationModal";
import { fetchDMCCount } from "../../slice/dmc/dmcSlice";
import { resetPackages } from "../../slice/tour-packages/prePackagesSlice";
import * as commonActions from "../../slice/common/commonSlice";

const MainMenu = ({ style = "" }) => {
  const { pathname } = useLocation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { userRole } = useSelector((state) => state.auth);
  
  // State for Book Tour flow (single DMC selection)
  const [isSearchModalOpen, setIsSearchModalOpen] = useState(false);
  const [isDMCModalOpen, setIsDMCModalOpen] = useState(false);
  const [searchCriteria, setSearchCriteria] = useState(null);
  
  // State for Book an Enquiry flow (multiple DMC selection)
  const [isEnquirySearchModalOpen, setIsEnquirySearchModalOpen] = useState(false);
  const [isEnquiryDMCModalOpen, setIsEnquiryDMCModalOpen] = useState(false);
  const [enquirySearchCriteria, setEnquirySearchCriteria] = useState(null);

  // State for Packages flow (single DMC selection - same as Book Tour)
  const [isPackagesSearchModalOpen, setIsPackagesSearchModalOpen] = useState(false);
  const [isPackagesDMCModalOpen, setIsPackagesDMCModalOpen] = useState(false);
  const [packagesSearchCriteria, setPackagesSearchCriteria] = useState(null);

  // Get DMC count and selected DMC from Redux state
  const dmcCount = useSelector((state) => state.dmc.dmcCount);
  const dmcCountLoading = useSelector((state) => state.dmc.dmcCountLoading);
  const selectedDmcId = useSelector((state) => state.dmc.dmcId);
  const selectedDmcData = useSelector((state) => state.dmc.selectedDmcData);
  const selectedCountries = useSelector((state) => state.dmc.selectedCountries);

  // Automatically fetch DMC count when component mounts
  useEffect(() => {
    dispatch(fetchDMCCount());
  }, [dispatch]);

  // Auto-open search modal if no country/DMC is selected
  useEffect(() => {
    // Check if we have DMC data or selected countries
    const hasDmcData = selectedDmcData && selectedDmcData.location && selectedDmcData.location !== 'Auto-selected';
    const hasSelectedCountries = selectedCountries && selectedCountries.length > 0;
    
    // If no DMC data and no selected countries, auto-open the appropriate search modal
    if (!hasDmcData && !hasSelectedCountries && !dmcCountLoading) {
      // Only auto-open for Agent users, and only if we're on a relevant page
      if (userRole === "Agent") {
        if (pathname.includes("/dashboard/db-dashboard/home_1")) {
          console.log('🌍 Auto-opening Book Tour search modal - no country selected');
          setIsSearchModalOpen(true);
        } else if (pathname.includes("/dashboard/db-dashboard/home_2")) {
          console.log('🌍 Auto-opening Enquiry search modal - no country selected');
          setIsEnquirySearchModalOpen(true);
        } else if (pathname.includes("/dashboard/pre-define-packages")) {
          console.log('🌍 Auto-opening Packages search modal - no country selected');
          setIsPackagesSearchModalOpen(true);
        }
      }
    }
  }, [selectedDmcData, selectedCountries, dmcCountLoading, userRole, pathname]);

  const isManagerOrSalesHead =
    userRole === "Sales Head(DMC)" ||
    userRole === "Sales Manager (DMC)" ||
    userRole === "Assistant Manager (DMC)" ||
    userRole === "Operational Head(DMC)" ||
    userRole === "DMC Operational Manager" ||
    userRole === "DMC Assistant Operational Manager";
  // Determine packages path based on user role
  const packagesPath = userRole === "Agent" 
    ? "/dashboard/pre-define-packages" 
    : "/dashboard/db-dashboard/tour-packages";

  // Common style for menu items
  const menuItemStyle = {
    marginRight: "40px",
  };

  // === Book Tour Handlers (Single DMC Selection) ===
  const handleBookTourClick = (e) => {
    e.preventDefault();
    
    // Wait for DMC count to load
    if (dmcCountLoading) {
      return;
    }
    
    // Always show search modal
    setIsSearchModalOpen(true);
  };

  const handleSearchSubmit = (searchData) => {
    // Check if this is a single DMC auto-selection (skipDMCModal: true)
    if (searchData.skipDMCModal && searchData.selectedDMC) {
      // Single DMC auto-selected - navigate directly to booking page
      navigate("/dashboard/db-dashboard/home_1", { 
        state: { 
          selectedDMC: searchData.selectedDMC,
          searchCriteria: { country: searchData.country }
        } 
      });
    } else {
      // Multiple DMCs - proceed with normal DMC selection modal
      setSearchCriteria({ country: searchData });
      setIsSearchModalOpen(false);
      setIsDMCModalOpen(true);
    }
  };

  const handleDMCSelect = (selectedDMC) => {
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

  // === Book an Enquiry Handlers (Multiple DMC Selection) ===
  const handleBookEnquiryClick = (e) => {
    e.preventDefault();
    if (dmcCountLoading) {  
      return;
    }

    // Always show search modal
    setIsEnquirySearchModalOpen(true);
  };

  const handleEnquirySearchSubmit = (searchData) => {
    // Check if this is a single DMC auto-selection (skipDMCModal: true)
    if (searchData.skipDMCModal && searchData.selectedDMC) {
      // Single DMC auto-selected - navigate directly to enquiry page
      navigate("/dashboard/db-dashboard/home_2", { 
        state: { 
          selectedDMCs: [searchData.selectedDMC],
          searchCriteria: { country: searchData.country }
        } 
      });
    } else {
      // Multiple DMCs - proceed with normal DMC selection modal
      setEnquirySearchCriteria({ country: searchData });
      setIsEnquirySearchModalOpen(false);
      setIsEnquiryDMCModalOpen(true);
    }
  };

  const handleEnquiryDMCSelect = (selectedDMCs) => {
    // Navigate to the enquiry page with the selected DMCs and search criteria
    navigate("/dashboard/db-dashboard/home_2", { 
      state: { 
        selectedDMCs,
        searchCriteria: enquirySearchCriteria 
      } 
    });
  };

  const handleCloseEnquirySearchModal = () => {
    setIsEnquirySearchModalOpen(false);
  };

  const handleCloseEnquiryDMCModal = () => {
    setIsEnquiryDMCModalOpen(false);
    setEnquirySearchCriteria(null);
  };

  // === Packages Handlers (Single DMC Selection - Same as Book Tour) ===
  const handlePackagesClick = (e) => {
    e.preventDefault();
    
    // Clear packages listing when Packages button is clicked
    dispatch(resetPackages());
    
    // Reset guest search to default values immediately
    const defaultGuestCounts = {
      Adults: 1,
      Children: 0,
      Infants: 0,
      maleCount: 0,
      femaleCount: 0,
      ages: []
    };
    dispatch(commonActions.setGuestCounts(defaultGuestCounts));
    
    // Wait for DMC count to load
    if (dmcCountLoading) {
      return;
    }
    
    // For non-Agent users, skip DMC selection and go directly to packages
    if (userRole !== "Agent") {
      navigate(packagesPath, { 
        state: { 
          selectedDMC: null,
          searchCriteria: null 
        } 
      });
      return;
    }
    
    // For Agent users, always show search modal
    setIsPackagesSearchModalOpen(true);
  };

  const handlePackagesSearchSubmit = (searchData) => {
    // Check if this is a single DMC auto-selection (skipDMCModal: true)
    if (searchData.skipDMCModal && searchData.selectedDMC) {
      // Single DMC auto-selected - navigate directly to packages page
      navigate(packagesPath, { 
        state: { 
          selectedDMC: searchData.selectedDMC,
          searchCriteria: { country: searchData.country }
        } 
      });
    } else {
      // Multiple DMCs - proceed with normal DMC selection modal
      setPackagesSearchCriteria({ country: searchData });
      setIsPackagesSearchModalOpen(false);
      setIsPackagesDMCModalOpen(true);
    }
  };

  const handlePackagesDMCSelect = (selectedDMC) => {
    // Clear packages listing when navigating to packages
    dispatch(resetPackages());
    
    // Navigate to the packages page with the selected DMC and search criteria
    navigate(packagesPath, { 
      state: { 
        selectedDMC,
        searchCriteria: packagesSearchCriteria 
      } 
    });
  };

  const handleClosePackagesSearchModal = () => {
    setIsPackagesSearchModalOpen(false);
  };

  const handleClosePackagesDMCModal = () => {
    setIsPackagesDMCModalOpen(false);
    setPackagesSearchCriteria(null);
  };

  return (
    <>
    <nav className="menu js-navList  lg:d-block">
      <ul className={`menu__nav ${style} -is-active`} style={{ display: "flex" }}>
        <li
          className={`menu-item ${
            pathname === "/dashboard/db-dashboard" ? "current" : ""
          }`}
          style={menuItemStyle}
        >
                     <Link
             to="/dashboard/db-dashboard"
             className="d-flex items-center px-20 py-15 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
             style={{ flexDirection: "column", alignItems: "flex-start" }}
            
           >
             
              
                 
            <div style={{ display: "flex", alignItems: "center" }}>
                <FaChartLine 
                  className="text-28 text-green-1" 
                  style={{ marginRight: "12px" }} 
                  title="Manage Your Experience"
                />
                <span
                  className={`fw-700 text-18 ${
                    pathname === "/dashboard/db-dashboard" ? "text-green-1" : ""
                  }`}
                  title="Manage Your Experience"
                >
                  Dashboard
                </span>
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
               className="d-flex items-center px-20 py-15 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
               style={{ flexDirection: "column", alignItems: "flex-start" }}
              
             >
                  <div style={{ display: "flex", alignItems: "center" }}>
                  <FaCompass 
                    className="text-28 text-green-1" 
                    style={{ marginRight: "12px" }} 
                    title="Discover Your Dream Destinations"
                  />
                  <span
                    className={`fw-700 text-18 ${
                      pathname === "/dashboard/db-dashboard/home_1"
                        ? "text-green-1"
                        : ""
                    }`}
                    title="Discover Your Dream Destinations"
                  >
                    Book Tour
                  </span> 
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
                             <a
                 href="#"
                 onClick={handleBookEnquiryClick}
               className="d-flex items-center px-20 py-15 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
               style={{ flexDirection: "column", alignItems: "flex-start" }}
             >
                               <div style={{ display: "flex", alignItems: "center" }}>
                  <FaEnvelopeOpenText 
                    className="text-28 text-green-1" 
                    style={{ marginRight: "12px" }} 
                    title="Reach Out for Custom Requests"
                  />
                  <span
                    className={`fw-700 text-18 ${
                      pathname === "/dashboard/db-dashboard/home_2"
                        ? "text-green-1"
                        : ""
                    }`}
                    title="Reach Out for Custom Requests"
                  >
                    Quick Enquiry
                  </span>
                </div>
               </a>
          </li>
        )}

        {!(userRole === "Operational Head(DMC)" || userRole === "DMC Operational Manager" || userRole === "DMC Assistant Operational Manager") && (
          <li
            className={`menu-item ${
              pathname === packagesPath ? "current" : ""
            }`}
          >
                       <a
               href="#"
               onClick={handlePackagesClick}
               className="d-flex items-center px-20 py-15 text-decoration-none hover:bg-green-1/5 rounded-4 transition-all"
               style={{ flexDirection: "column", alignItems: "flex-start" }}
             >
                             <div style={{ display: "flex", alignItems: "center" }}>
                  <FaBoxOpen 
                    className="text-28 text-green-1" 
                    style={{ marginRight: "12px" }} 
                    title="Custom Travel Bundles"
                  />
                  <span
                    className={`fw-700 text-18 ${
                      pathname === packagesPath ? "text-green-1" : ""
                    }`}
                    title="Custom Travel Bundles"
                  >
                    Packages
                  </span>
                </div>
             </a>
          </li>
        )}
      </ul>
    </nav>

      {/* Book Tour Flow - Single DMC Selection */}
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
        multiSelect={false}
      />

      {/* Book an Enquiry Flow - Multiple DMC Selection */}
      <SearchLocationModal
        open={isEnquirySearchModalOpen}
        onClose={handleCloseEnquirySearchModal}
        onSearch={handleEnquirySearchSubmit}
      />

      <DMCSelectionModal
        open={isEnquiryDMCModalOpen}
        onClose={handleCloseEnquiryDMCModal}
        onSelect={handleEnquiryDMCSelect}
        searchCriteria={enquirySearchCriteria}
        multiSelect={true}
      />

      {/* Packages Flow - Single DMC Selection (Same as Book Tour) */}
      <SearchLocationModal
        open={isPackagesSearchModalOpen}
        onClose={handleClosePackagesSearchModal}
        onSearch={handlePackagesSearchSubmit}
      />

      <DMCSelectionModal
        open={isPackagesDMCModalOpen}
        onClose={handleClosePackagesDMCModal}
        onSelect={handlePackagesDMCSelect}
        searchCriteria={packagesSearchCriteria}
        multiSelect={false}
      />
    </>
  );
};


export default MainMenu;
