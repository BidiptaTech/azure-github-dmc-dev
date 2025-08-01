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

  // Automatically fetch DMC count when component mounts
  useEffect(() => {
    console.log('🔄 MainMenu: Automatically fetching DMC count...');
    dispatch(fetchDMCCount());
  }, [dispatch]);

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

  // === Book Tour Handlers (Single DMC Selection) ===
  const handleBookTourClick = (e) => {
    e.preventDefault();
    
    console.log('🎯 Book Tour clicked - DMC Count:', dmcCount);
    console.log('🎯 DMC Count Loading:', dmcCountLoading);
    
    // Wait for DMC count to load
    if (dmcCountLoading) {
      console.log('⏳ DMC count still loading - please wait...');
      return;
    }
    
    // Check if DMC count is 1 - if so, skip modal and go directly to booking
    if (dmcCount && dmcCount.dmc_count === 1) {
      console.log('✅ Only 1 DMC available - skipping modal, going directly to booking');
      console.log('✅ Using auto-stored DMC ID:', selectedDmcId);
      console.log('✅ Using auto-stored DMC Data:', selectedDmcData);
      
      // Navigate directly to the booking page without showing modal
      navigate("/dashboard/db-dashboard/home_1", { 
        state: { 
          selectedDMC: selectedDmcData || { dmcId: selectedDmcId, name: `DMC ${selectedDmcId}` },
          searchCriteria: null   
        } 
      });
    } else {
      console.log('📋 Multiple DMCs available - opening search modal');
      setIsSearchModalOpen(true);
    }
  };

  const handleSearchSubmit = (searchData) => {
    setSearchCriteria(searchData);
    setIsSearchModalOpen(false);
    setIsDMCModalOpen(true);
  };

  const handleDMCSelect = (selectedDMC) => {
    console.log('Selected DMC (Book Tour):', selectedDMC);
    console.log('Search Criteria (Book Tour):', searchCriteria);
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
    console.log('🎯 Book Enquiry clicked - DMC Count:', dmcCount);
    console.log('🎯 DMC Count Loading:', dmcCountLoading);
    console.log('🎯 User Role:', userRole);
    if (dmcCountLoading) {  
      console.log('⏳ DMC count still loading - please wait...');
      return;
    }

      if(dmcCount && dmcCount.dmc_count === 1){ 
      console.log('✅ Only 1 DMC available - skipping modal, going directly to enquiry');
      console.log('✅ Using auto-stored DMC ID:', selectedDmcId);
      console.log('✅ Using auto-stored DMC Data:', selectedDmcData);
      navigate("/dashboard/db-dashboard/home_2", { 
        state: { 
          selectedDMCs: [selectedDmcData] ||{ dmcId: selectedDmcId, name: `DMC ${selectedDmcId}` },
          searchCriteria: null 
        } 
      });
    }else{
      setIsEnquirySearchModalOpen(true);
    
    }
  };

  const handleEnquirySearchSubmit = (searchData) => {
    setEnquirySearchCriteria(searchData);
    setIsEnquirySearchModalOpen(false);
    setIsEnquiryDMCModalOpen(true);
  };

  const handleEnquiryDMCSelect = (selectedDMCs) => {
    console.log('Selected DMCs (Book Enquiry):', selectedDMCs);
    console.log('Search Criteria (Book Enquiry):', enquirySearchCriteria);
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
    
    console.log('🎯 Packages clicked - DMC Count:', dmcCount);
    console.log('🎯 DMC Count Loading:', dmcCountLoading);
    console.log('🎯 User Role:', userRole);
    
    // Wait for DMC count to load
    if (dmcCountLoading) {
      console.log('⏳ DMC count still loading - please wait...');
      return;
    }
    
    // For non-Agent users, skip DMC selection and go directly to packages
    if (userRole !== "Agent") {
      console.log('✅ Non-Agent user - skipping DMC selection, going directly to packages');
      navigate(packagesPath, { 
        state: { 
          selectedDMC: null,
          searchCriteria: null 
        } 
      });
      return;
    }
    
    // For Agent users, check DMC count
    if (dmcCount && dmcCount.dmc_count === 1) {
      console.log('✅ Only 1 DMC available - skipping modal, going directly to packages');
      console.log('✅ Using auto-stored DMC ID:', selectedDmcId);
      console.log('✅ Using auto-stored DMC Data:', selectedDmcData);
      
      // Navigate directly to the packages page without showing modal
      navigate(packagesPath, { 
        state: { 
          selectedDMC: selectedDmcData || { dmcId: selectedDmcId, name: `DMC ${selectedDmcId}` },
          searchCriteria: null 
        } 
      });
    } else {
      console.log('📋 Multiple DMCs available - opening search modal for packages');
      setIsPackagesSearchModalOpen(true);
    }
  };

  const handlePackagesSearchSubmit = (searchData) => {
    setPackagesSearchCriteria(searchData);
    setIsPackagesSearchModalOpen(false);
    setIsPackagesDMCModalOpen(true);
  };

  const handlePackagesDMCSelect = (selectedDMC) => {
    console.log('Selected DMC (Packages):', selectedDMC);
    console.log('Search Criteria (Packages):', packagesSearchCriteria);
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
              <a
                href="#"
                onClick={handleBookEnquiryClick}
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
              </a>
          </li>
        )}

        <li
          className={`menu-item ${
            pathname === packagesPath ? "current" : ""
          }`}
        >
          <a
            href="#"
            onClick={handlePackagesClick}
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
          </a>
        </li>
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
