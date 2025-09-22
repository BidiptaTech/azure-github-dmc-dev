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
import { setAgentId as setAgentIdEdit } from "@/slice/common/EditSlice";
import {
  FaChartLine,
  FaCompass,
  FaEnvelopeOpenText,
  FaBoxOpen,
} from "react-icons/fa";
import DMCSelectionModal from "../common/DMCSelectionModal";
import SearchLocationModal from "../common/SearchLocationModal";
import { fetchDMCCount } from "../../slice/dmc/dmcSlice";
import { resetPackages } from "../../slice/tour-packages/prePackagesSlice";
import * as commonActions from "../../slice/common/commonSlice";

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

  // Check if the user is a manager or sales head
  const isManagerOrSalesHead = userRole === "Sales Head(DMC)" || userRole === "Sales Manager (DMC)"|| userRole === "Assistant Manager (DMC)";

  // Determine packages path based on user role
  const packagesPath = userRole === "Agent" 
    ? "/dashboard/pre-define-packages" 
    : "/dashboard/db-dashboard/tour-packages";

  // Automatically fetch DMC count when component mounts
  useEffect(() => {
    dispatch(fetchDMCCount());
  }, [dispatch]);

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

  const handleLogout = async () => {
    dispatch(clearSelectedDmc()); // Clear DMC selection on logout
    dispatch(setAgentIdEdit(null));
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
            style={{ 
              margin: "8px 15px",
              display: "flex",
              alignItems: "center",
              padding: "15px 20px",
              minHeight: "50px",
              borderRadius: "8px"
            }}
          >
            <FaChartLine style={{ marginRight: "12px", color: "#10b981" }} />
            Dashboard
          </MenuItem>
          
          {/* Only show Book Tour for non-manager roles */}
          {!isManagerOrSalesHead && (
            <MenuItem
              onClick={handleBookTourClick}
              className={
                pathname === "/dashboard/db-dashboard/home_1"
                  ? "menu-active-link"
                  : ""
              }
              style={{ 
                margin: "8px 15px",
                display: "flex",
                alignItems: "center",
                padding: "15px 20px",
                minHeight: "50px",
                borderRadius: "8px"
              }}
            >
              <FaCompass style={{ marginRight: "12px", color: "#10b981" }} />
              Book Tour
            </MenuItem>
          )}
          
          {/* Only show Book An Enquiry for non-manager roles */}
          {!isManagerOrSalesHead && (
            <MenuItem
              onClick={handleBookEnquiryClick}
              className={
                pathname === "/dashboard/db-dashboard/home_2"
                  ? "menu-active-link"
                  : ""
              }
              style={{ 
                margin: "8px 15px",
                display: "flex",
                alignItems: "center",
                padding: "15px 20px",
                minHeight: "50px",
                borderRadius: "8px"
              }}
            >
              <FaEnvelopeOpenText style={{ marginRight: "12px", color: "#10b981" }} />
              Quick Enquiry
            </MenuItem>
          )}

          {/* Show Packages for non-operational roles */}
          {!(userRole === "Operational Head(DMC)" || userRole === "DMC Operational Manager" || userRole === "DMC Assistant Operational Manager") && (
            <MenuItem
              onClick={handlePackagesClick}
              className={
                pathname === packagesPath ? "menu-active-link" : ""
              }
              style={{ 
                margin: "8px 15px",
                display: "flex",
                alignItems: "center",
                padding: "15px 20px",
                minHeight: "50px",
                borderRadius: "8px"
              }}
            >
              <FaBoxOpen style={{ marginRight: "12px", color: "#10b981" }} />
              Packages
            </MenuItem>
          )}
          
          {/* <MenuItem
            onClick={() => navigate("/dashboard/db-dashboard/create-package")}
            className={
              pathname === "/dashboard/db-dashboard/create-package"
                ? "menu-active-link"
                : ""
            }
            style={{ 
              margin: "8px 15px",
              display: "flex",
              alignItems: "center",
              padding: "15px 20px",
              minHeight: "50px",
              borderRadius: "8px"
            }}
          >
            <FaBoxOpen style={{ marginRight: "12px", color: "#10b981" }} />
            Create Package
          </MenuItem> */}

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

export default MobileMenu;
