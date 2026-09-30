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
import MenuIcon from '@mui/icons-material/Menu';
import { setAgentId as setAgentIdEdit } from "@/slice/common/EditSlice";
import { 
  Drawer, 
  IconButton,
  Box,
  Typography,
  Avatar,
  Menu,
  MenuItem,
} from '@mui/material';
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';

import MobileMenu from "../MobileMenu";
import SearchLocationModal from "../../common/SearchLocationModal";
import DMCSelectionModal from "../../common/DMCSelectionModal";

const Header1 = ({ onViewProfile }) => {
  const [navbar, setNavbar] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const userRole = useSelector((state) => state.auth.userRole);
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const dmcLogo = useSelector((state) => state.auth.dmcLogo);
  const agencyLogo = useSelector((state) => state.auth.agencyLogo);
  const username = useSelector((state) => state.auth.Username);
  const [userMenuAnchor, setUserMenuAnchor] = useState(null);
  const initials = (username || "Agent")
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join("") || "A";

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
      <header
        className={`header header-lite ${navbar ? "is-sticky" : ""}`}
        style={{
          padding: 0,
          background: "linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%)",
          height: 88,
        }}
      >
        <style>{`
          .header.header-lite {
            height: 88px !important;
            background: linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.18);
          }
          .header.header-lite .menu__nav > li {
            padding: 0 !important;
          }
          .header.header-lite .menu__nav a {
            padding: 8px 14px !important;
            border-radius: 999px;
            text-decoration: none !important;
            transition: background 0.2s ease, color 0.2s ease;
          }
          .header.header-lite .menu__nav a:hover,
          .header.header-lite .menu__nav a:focus {
            background: rgba(255, 255, 255, 0.14) !important;
            color: #ffffff !important;
            text-decoration: none !important;
            outline: none;
          }
          .header.header-lite .menu__nav li.current > a,
          .header.header-lite .menu__nav li.current > a:hover,
          .header.header-lite .menu__nav li.current > a:focus {
            background: #ffffff !important;
            color: #0f1f4b !important;
            box-shadow: 0 4px 12px rgba(8, 18, 48, 0.22);
          }
          .header.header-lite .header-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 72px;
            padding: 4px 10px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 6px 16px rgba(8, 18, 48, 0.28);
            overflow: hidden;
            line-height: 0;
          }
          .header.header-lite .header-logo img {
            width: auto !important;
            height: 64px !important;
            max-width: 220px;
            object-fit: contain;
          }
        `}</style>
        <div className="header__container" style={{ width: "100%" }}>
          <div style={{ display: "flex", alignItems: "center", width: "100%", maxWidth: 1400, margin: "0 auto", height: 88, padding: "0 28px", gap: 28 }}>
            <div style={{ display: "flex", alignItems: "center", minWidth: 220, flexShrink: 0 }}>
                <Link to="/dashboard/db-dashboard" className="header-logo">
                  <img
                    src={isAuthenticated && userRole == "Agent" ? agencyLogo : dmcLogo}
                    alt="logo icon"
                    style={{
                      height: 64,
                      width: "auto",
                      maxWidth: 220,
                      objectFit: "contain",
                      display: "block",
                    }}
                  />
                </Link>
            </div>

            <div className="header-menu" style={{ flex: 1, display: "flex", justifyContent: "center" }}>
              <div className="header-menu__content">
                <MainMenu style="text-dark-1" onDark />
              </div>
            </div>

            <div style={{ display: "flex", alignItems: "center", justifyContent: "flex-end", minWidth: 150 }}>
                {/* Logout button positioned to the far right */}
                {isAuthenticated && (
                  <>
                    <Box
                      onClick={(event) => setUserMenuAnchor(event.currentTarget)}
                      sx={{
                        display: "flex",
                        alignItems: "center",
                        gap: 1.25,
                        px: 1.25,
                        py: 0.6,
                        borderRadius: "999px",
                        border: "1px solid #e8eef5",
                        cursor: "pointer",
                        bgcolor: "#fff",
                        "&:hover": { bgcolor: "#f8fafc" },
                      }}
                    >
                      <Avatar sx={{ width: 38, height: 38, bgcolor: "#3554d1", fontSize: 13, fontWeight: 700 }}>
                        {initials}
                      </Avatar>
                      <Typography sx={{ fontSize: 14, fontWeight: 600, color: "#0f172a", display: { xs: "none", md: "block" } }}>
                        {username || "Agent"}
                      </Typography>
                      <KeyboardArrowDownIcon sx={{ fontSize: 18, color: "#64748b" }} />
                    </Box>
                    <Menu
                      anchorEl={userMenuAnchor}
                      open={Boolean(userMenuAnchor)}
                      onClose={() => setUserMenuAnchor(null)}
                      anchorOrigin={{ horizontal: "right", vertical: "bottom" }}
                      transformOrigin={{ horizontal: "right", vertical: "top" }}
                      PaperProps={{ sx: { mt: 1, minWidth: 180, borderRadius: "12px" } }}
                    >
                      {onViewProfile && (
                        <MenuItem
                          onClick={() => {
                            setUserMenuAnchor(null);
                            onViewProfile();
                          }}
                        >
                          View profile
                        </MenuItem>
                      )}
                      <MenuItem
                        onClick={() => {
                          setUserMenuAnchor(null);
                          handleLogout();
                        }}
                      >
                        Sign out
                      </MenuItem>
                    </Menu>
                  </>
                )}
                {/* Start mobile menu icon */}
                <div className="d-none xl:d-flex x-gap-20 items-center text-white">
                  <IconButton
                    onClick={handleMobileMenuToggle}
                    sx={{ 
                      color: '#ffffff',
                      '&:hover': {
                        backgroundColor: 'rgba(255, 255, 255, 0.12)',
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
                      },
                    }}
                  >
                    <MobileMenu 
                      onMenuClose={handleMobileMenuClose}
                    />
                  </Drawer>
                </div>
                {/* End mobile menu icon */}
              </div>
          </div>
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
