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
import { clearSelectedDmc } from "@/slice/dmc/dmcSlice";
import LogoutIcon from '@mui/icons-material/Logout';
import MenuIcon from '@mui/icons-material/Menu';
import { setAgentId as setAgentIdEdit } from "@/slice/common/EditSlice";
import { 
  Drawer, 
  List, 
  ListItem, 
  ListItemButton, 
  ListItemIcon, 
  ListItemText, 
  IconButton,
  Box,
  Typography,
  Divider
} from '@mui/material';

import MobileMenu from "../MobileMenu";
import SearchLocationModal from "../../common/SearchLocationModal";
import DMCSelectionModal from "../../common/DMCSelectionModal";

const Header1 = () => {
  const [navbar, setNavbar] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const userRole = useSelector((state) => state.auth.userRole);
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const dmcLogo = useSelector((state) => state.auth.dmcLogo);
  const agencyLogo = useSelector((state) => state.auth.agencyLogo);

  // Modal states for mobile menu
  const [isSearchModalOpen, setIsSearchModalOpen] = useState(false);
  const [isDMCModalOpen, setIsDMCModalOpen] = useState(false);
  const [searchCriteria, setSearchCriteria] = useState(null);
  const [isEnquirySearchModalOpen, setIsEnquirySearchModalOpen] = useState(false);
  const [isEnquiryDMCModalOpen, setIsEnquiryDMCModalOpen] = useState(false);
  const [enquirySearchCriteria, setEnquirySearchCriteria] = useState(null);
  const [isPackagesSearchModalOpen, setIsPackagesSearchModalOpen] = useState(false);
  const [isPackagesDMCModalOpen, setIsPackagesDMCModalOpen] = useState(false);
  const [packagesSearchCriteria, setPackagesSearchCriteria] = useState(null);

  const handleLogout = () => {
    dispatch(setAgentIdEdit(null));
    dispatch(logoutUser());
  
    dispatch(clearSelectedDmc()); // Clear DMC selection on logout
  };

  // Handle mobile menu toggle - Simple Material-UI approach
  const handleMobileMenuToggle = () => {
    console.log('🍔 Mobile menu button clicked');
    setMobileMenuOpen(!mobileMenuOpen);
  };

  const handleMobileMenuClose = () => {
    console.log('📱 Closing mobile menu');
    setMobileMenuOpen(false);
  };

  // Modal handlers
  const handleSearchSubmit = (searchData) => {
    if (searchData.skipDMCModal && searchData.selectedDMC) {
      navigate("/dashboard/db-dashboard/home_1", { 
        state: { 
          selectedDMC: searchData.selectedDMC,
          searchCriteria: { country: searchData.country }
        } 
      });
    } else {
      setSearchCriteria({ country: searchData });
      setIsSearchModalOpen(false);
      setIsDMCModalOpen(true);
    }
  };

  const handleDMCSelect = (selectedDMC) => {
    navigate("/dashboard/db-dashboard/home_1", { 
      state: { selectedDMC, searchCriteria } 
    });
  };

  const handleEnquirySearchSubmit = (searchData) => {
    if (searchData.skipDMCModal && searchData.selectedDMC) {
      navigate("/dashboard/db-dashboard/home_2", { 
        state: { 
          selectedDMCs: [searchData.selectedDMC],
          searchCriteria: { country: searchData.country }
        } 
      });
    } else {
      setEnquirySearchCriteria({ country: searchData });
      setIsEnquirySearchModalOpen(false);
      setIsEnquiryDMCModalOpen(true);
    }
  };

  const handleEnquiryDMCSelect = (selectedDMCs) => {
    navigate("/dashboard/db-dashboard/home_2", { 
      state: { selectedDMCs, searchCriteria: enquirySearchCriteria } 
    });
  };

  const handlePackagesSearchSubmit = (searchData) => {
    const packagesPath = userRole === "Agent" 
      ? "/dashboard/pre-define-packages" 
      : "/dashboard/db-dashboard/tour-packages";
      
    if (searchData.skipDMCModal && searchData.selectedDMC) {
      navigate(packagesPath, { 
        state: { 
          selectedDMC: searchData.selectedDMC,
          searchCriteria: { country: searchData.country }
        } 
      });
    } else {
      setPackagesSearchCriteria({ country: searchData });
      setIsPackagesSearchModalOpen(false);
      setIsPackagesDMCModalOpen(true);
    }
  };

  const handlePackagesDMCSelect = (selectedDMC) => {
    const packagesPath = userRole === "Agent" 
      ? "/dashboard/pre-define-packages" 
      : "/dashboard/db-dashboard/tour-packages";
      
    dispatch(resetPackages());
    navigate(packagesPath, { 
      state: { selectedDMC, searchCriteria: packagesSearchCriteria } 
    });
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
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      borderRadius: "8px",
                      background: "rgba(255, 255, 255, 0.1)",
                      backdropFilter: "blur(10px)",
                      border: "1px solid rgba(255, 255, 255, 0.2)",
                      transition: "all 0.3s ease",
                      height: "50px",
                      width: "100%",
                      minHeight: "45px",
                      minWidth: "140px",
                    }}
                    onMouseEnter={(e) => {
                      e.currentTarget.style.background = "rgba(255, 255, 255, 0.15)";
                      e.currentTarget.style.border = "1px solid rgba(255, 255, 255, 0.3)";
                      e.currentTarget.style.transform = "translateY(-1px)";
                    }}
                    onMouseLeave={(e) => {
                      e.currentTarget.style.background = "rgba(255, 255, 255, 0.1)";
                      e.currentTarget.style.border = "1px solid rgba(255, 255, 255, 0.2)";
                      e.currentTarget.style.transform = "translateY(0)";
                    }}
                  >
                    <img
                      src={isAuthenticated && userRole == "Agent" ? agencyLogo : dmcLogo}  
                      alt="logo icon"
                      style={{
                        width: "100%",
                        height: "50px",
                        objectFit: "cover",
                        transition: "all 0.3s ease",
                        display: "block",
                        filter: "brightness(1.05) contrast(1.05)",
                        borderRadius: "6px",
                      }}
                      onLoad={(e) => {
                        e.target.style.opacity = "0";
                        setTimeout(() => {
                          e.target.style.transition = "opacity 0.4s ease";
                          e.target.style.opacity = "1";
                        }, 50);
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
                  <IconButton
                    onClick={handleMobileMenuToggle}
                    sx={{ 
                      color: 'white',
                      '&:hover': {
                        backgroundColor: 'rgba(255, 255, 255, 0.1)',
                      }
                    }}
                  >
                    <MenuIcon />
                  </IconButton>

                  {/* Material-UI Drawer */}
                  <Drawer
                    anchor="left"
                    open={mobileMenuOpen}
                    onClose={handleMobileMenuClose}
                    sx={{
                      '& .MuiDrawer-paper': {
                        width: 320,
                        backgroundColor: '#fff',
                        boxShadow: '0 8px 32px rgba(0, 0, 0, 0.12)',
                        zIndex: 1200, // Lower than modal z-index
                      },
                      zIndex: 1200, // Ensure drawer doesn't interfere with modals
                    }}
                  >
                    <MobileMenu 
                      onMenuClose={handleMobileMenuClose}
                      onOpenSearchModal={() => setIsSearchModalOpen(true)}
                      onOpenEnquirySearchModal={() => setIsEnquirySearchModalOpen(true)}
                      onOpenPackagesSearchModal={() => setIsPackagesSearchModalOpen(true)}
                    />
                  </Drawer>
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

      {/* Modals - Outside the drawer to avoid z-index issues */}
      <SearchLocationModal
        open={isSearchModalOpen}
        onClose={() => setIsSearchModalOpen(false)}
        onSearch={handleSearchSubmit}
      />

      <DMCSelectionModal
        open={isDMCModalOpen}
        onClose={() => {
          setIsDMCModalOpen(false);
          setSearchCriteria(null);
        }}
        onSelect={handleDMCSelect}
        searchCriteria={searchCriteria}
        multiSelect={false}
      />

      <SearchLocationModal
        open={isEnquirySearchModalOpen}
        onClose={() => setIsEnquirySearchModalOpen(false)}
        onSearch={handleEnquirySearchSubmit}
      />

      <DMCSelectionModal
        open={isEnquiryDMCModalOpen}
        onClose={() => {
          setIsEnquiryDMCModalOpen(false);
          setEnquirySearchCriteria(null);
        }}
        onSelect={handleEnquiryDMCSelect}
        searchCriteria={enquirySearchCriteria}
        multiSelect={true}
      />

      <SearchLocationModal
        open={isPackagesSearchModalOpen}
        onClose={() => setIsPackagesSearchModalOpen(false)}
        onSearch={handlePackagesSearchSubmit}
      />

      <DMCSelectionModal
        open={isPackagesDMCModalOpen}
        onClose={() => {
          setIsPackagesDMCModalOpen(false);
          setPackagesSearchCriteria(null);
        }}
        onSelect={handlePackagesDMCSelect}
        searchCriteria={packagesSearchCriteria}
        multiSelect={false}
      />
    </>
  );
};

export default Header1;
