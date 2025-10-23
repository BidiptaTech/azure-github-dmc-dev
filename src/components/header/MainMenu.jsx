import { Link, useLocation, useNavigate } from "react-router-dom";
import React, { useState, useEffect } from "react";
import {
  FaChartLine,
  FaCompass,
  FaEnvelopeOpenText,
  FaBoxOpen,
} from "react-icons/fa";
import { useSelector, useDispatch } from "react-redux";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Box,
  IconButton,
} from "@mui/material";
import {
  Close as CloseIcon,
  TravelExplore as TravelIcon,
} from "@mui/icons-material";
import { styled } from "@mui/material/styles";
import DMCSelectionModal from "../common/DMCSelectionModal";
import LocationSearch from "../hero/hero-2/LocationSearch";
import { fetchDMCCount } from "../../slice/dmc/dmcSlice";
import { resetPackages } from "../../slice/tour-packages/prePackagesSlice";
import * as commonActions from "../../slice/common/commonSlice";

// Styled components for the location modal
const StyledDialog = styled(Dialog)(({ theme }) => ({
  '& .MuiDialog-paper': {
    borderRadius: '20px',
    padding: '0px',
    width: '90%',
    maxWidth: '600px',
    maxHeight: '80vh',
    boxShadow: '0 32px 64px 8px rgba(0,0,0,0.18)',
    overflow: 'hidden',
    border: '1px solid rgba(255,255,255,0.1)',
    backdropFilter: 'blur(20px)',
  },
}));

const StyledDialogTitle = styled(DialogTitle)(({ theme }) => ({
  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  color: 'white',
  padding: '24px 32px',
  borderRadius: '20px 20px 0 0',
  position: 'relative',
  minHeight: '80px',
  display: 'flex',
  alignItems: 'center',
  '& .MuiIconButton-root': {
    color: 'white',
    backgroundColor: 'rgba(255,255,255,0.15)',
    padding: '12px',
    borderRadius: '50%',
    backdropFilter: 'blur(10px)',
    border: '1px solid rgba(255,255,255,0.2)',
    transition: 'all 0.3s ease',
    '&:hover': {
      backgroundColor: 'rgba(255,255,255,0.25)',
      transform: 'scale(1.05)',
      boxShadow: '0 4px 20px rgba(0,0,0,0.2)',
    },
  },
}));

const MainMenu = ({ style = "" }) => {
  const { pathname } = useLocation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { userRole } = useSelector((state) => state.auth);
  
  // State for Book Tour flow (single DMC selection)
  const [isBookTourLocationModalOpen, setIsBookTourLocationModalOpen] = useState(false);
  const [isDMCModalOpen, setIsDMCModalOpen] = useState(false);
  const [searchCriteria, setSearchCriteria] = useState(null);

  // State for Packages flow (single DMC selection - same as Book Tour)
  const [isPackagesLocationModalOpen, setIsPackagesLocationModalOpen] = useState(false);
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
          console.log('🌍 Auto-opening Book Tour location modal - no location selected');
          setIsBookTourLocationModalOpen(true);
        } else if (pathname.includes("/dashboard/pre-define-packages")) {
          console.log('🌍 Auto-opening Packages location modal - no location selected');
          setIsPackagesLocationModalOpen(true);
        }
        // Note: Quick Enquiry (home_2) does not auto-open modal - user selects location on the page
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
    
    // Always show location modal
    setIsBookTourLocationModalOpen(true);
  };

  const handleBookTourLocationSelect = (locationData) => {
    console.log('📍 Location selected for Book Tour:', locationData);
    
    // Only proceed if we have both country and city
    if (locationData && locationData.country && locationData.city) {
      setIsBookTourLocationModalOpen(false);
      
      // Set search criteria and open DMC selection modal
      const searchCriteria = { 
        country: { name: locationData.country },
        city: locationData.city 
      };
      setSearchCriteria(searchCriteria);
      setIsDMCModalOpen(true);
    } else {
      console.warn('⚠️ Incomplete location data - need both country and city');
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

  const handleCloseBookTourLocationModal = () => {
    setIsBookTourLocationModalOpen(false);
  };

  const handleCloseDMCModal = () => {
    setIsDMCModalOpen(false);
    setSearchCriteria(null);
  };

  // === Book an Enquiry Handlers (Direct Navigation - No Modal) ===
  const handleBookEnquiryClick = (e) => {
    e.preventDefault();
    
    // Navigate directly to enquiry page
    // User will select location on the Quick Enquiry page itself
    navigate("/dashboard/db-dashboard/home_2");
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
    
    // For Agent users, always show location modal
    setIsPackagesLocationModalOpen(true);
  };

  const handlePackagesLocationSelect = (locationData) => {
    console.log('📍 Location selected for Packages:', locationData);
    
    // Only proceed if we have both country and city
    if (locationData && locationData.country && locationData.city) {
      setIsPackagesLocationModalOpen(false);
      
      // Set search criteria and open DMC selection modal
      const searchCriteria = { 
        country: { name: locationData.country },
        city: locationData.city 
      };
      setPackagesSearchCriteria(searchCriteria);
      setIsPackagesDMCModalOpen(true);
    } else {
      console.warn('⚠️ Incomplete location data - need both country and city');
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

  const handleClosePackagesLocationModal = () => {
    setIsPackagesLocationModalOpen(false);
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

      {/* Book Tour Flow - Location Selection + Single DMC Selection */}
      <StyledDialog
        open={isBookTourLocationModalOpen}
        onClose={handleCloseBookTourLocationModal}
        maxWidth="md"
        fullWidth
      >
        <StyledDialogTitle>
          <Box sx={{ display: 'flex', alignItems: 'center', flex: 1 }}>
            <TravelIcon sx={{ fontSize: 32, mr: 2 }} />
            <Box>
              <Typography variant="h5" component="div" sx={{ fontWeight: 600 }}>
                Select Destination for Book Tour
              </Typography>
              <Typography variant="body2" sx={{ opacity: 0.9, mt: 0.5 }}>
                Choose your travel destination
              </Typography>
            </Box>
          </Box>
          <IconButton
            onClick={handleCloseBookTourLocationModal}
            sx={{ position: 'absolute', right: 16, top: 16 }}
          >
            <CloseIcon />
          </IconButton>
        </StyledDialogTitle>
        <DialogContent sx={{ p: 4, minHeight: '300px' }}>
          <LocationSearch onLocationSelect={handleBookTourLocationSelect} />
        </DialogContent>
      </StyledDialog>

      <DMCSelectionModal
        open={isDMCModalOpen}
        onClose={handleCloseDMCModal}
        onSelect={handleDMCSelect}
        searchCriteria={searchCriteria}
        multiSelect={false}
      />

      {/* Book an Enquiry Flow - Direct Navigation (No Modal) */}
      {/* User will select location directly on the Quick Enquiry page */}

      {/* Packages Flow - Location Selection + Single DMC Selection */}
      <StyledDialog
        open={isPackagesLocationModalOpen}
        onClose={handleClosePackagesLocationModal}
        maxWidth="md"
        fullWidth
      >
        <StyledDialogTitle>
          <Box sx={{ display: 'flex', alignItems: 'center', flex: 1 }}>
            <TravelIcon sx={{ fontSize: 32, mr: 2 }} />
            <Box>
              <Typography variant="h5" component="div" sx={{ fontWeight: 600 }}>
                Select Destination for Packages
              </Typography>
              <Typography variant="body2" sx={{ opacity: 0.9, mt: 0.5 }}>
                Choose your travel destination
              </Typography>
            </Box>
          </Box>
          <IconButton
            onClick={handleClosePackagesLocationModal}
            sx={{ position: 'absolute', right: 16, top: 16 }}
          >
            <CloseIcon />
          </IconButton>
        </StyledDialogTitle>
        <DialogContent sx={{ p: 4, minHeight: '300px' }}>
          <LocationSearch onLocationSelect={handlePackagesLocationSelect} />
        </DialogContent>
      </StyledDialog>

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
