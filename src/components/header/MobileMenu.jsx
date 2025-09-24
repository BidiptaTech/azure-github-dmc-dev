import { Link, useLocation, useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import { logoutUser } from "@/slice/common/authSlices";
import { clearSelectedDmc } from "@/slice/dmc/dmcSlice";
import { setAgentId as setAgentIdEdit } from "@/slice/common/EditSlice";
import { resetPackages } from "../../slice/tour-packages/prePackagesSlice";
import { fetchDMCCount } from "../../slice/dmc/dmcSlice";
import * as commonActions from "../../slice/common/commonSlice";
import {
  Box,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Typography,
  Divider,
  Avatar,
  IconButton,
  Paper,
} from '@mui/material';
import {
  Dashboard as DashboardIcon,
  Explore as ExploreIcon,
  Email as EmailIcon,
  Inventory as InventoryIcon,
  Logout as LogoutIcon,
  Close as CloseIcon,
} from '@mui/icons-material';
// Modal components are now imported and used by the parent header component
import Social from "../common/social/Social";

const MobileMenu = ({ onMenuClose, onOpenSearchModal, onOpenEnquirySearchModal, onOpenPackagesSearchModal }) => {
  const { pathname } = useLocation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const { userRole } = useSelector((state) => state.auth);
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);

  // Modal states are now managed by the parent header component

  // Get DMC count and selected DMC from Redux state
  const dmcCount = useSelector((state) => state.dmc.dmcCount);
  const dmcCountLoading = useSelector((state) => state.dmc.dmcCountLoading);
  const selectedDmcId = useSelector((state) => state.dmc.dmcId);
  const selectedDmcData = useSelector((state) => state.dmc.selectedDmcData);
  const selectedCountries = useSelector((state) => state.dmc.selectedCountries);

  // Determine packages path based on user role
  const packagesPath = userRole === "Agent" 
    ? "/dashboard/pre-define-packages" 
    : "/dashboard/db-dashboard/tour-packages";

  // Automatically fetch DMC count when component mounts
  useEffect(() => {
    dispatch(fetchDMCCount());
  }, [dispatch]);

  // Modal state tracking is now handled by the parent header component

  // Auto-open functionality is now handled by the parent header component

  // Simple function to close mobile menu
  const closeMobileMenu = () => {
    console.log('🚪 Closing mobile menu');
    if (onMenuClose) {
      onMenuClose();
    }
  };

  // === Menu Handlers ===
  const handleDashboardClick = () => {
    console.log('📱 Dashboard clicked');
    closeMobileMenu();
    navigate("/dashboard/db-dashboard");
  };

  const handleBookTourClick = () => {
    console.log('📱 Book Tour clicked');
    if (dmcCountLoading) return;
    
    // Close mobile menu first, then open modal with a small delay
    closeMobileMenu();
    setTimeout(() => {
      console.log('📱 Opening search modal via parent');
      onOpenSearchModal();
      console.log('✅ Book Tour search modal opened');
    }, 150);
  };

  // Modal handlers are now managed by the parent header component

  const handleBookEnquiryClick = () => {
    console.log('📱 Quick Enquiry clicked');
    if (dmcCountLoading) return;
    
    // Close mobile menu first, then open modal with a small delay
    closeMobileMenu();
    setTimeout(() => {
      console.log('📱 Opening enquiry search modal via parent');
      onOpenEnquirySearchModal();
      console.log('✅ Quick Enquiry search modal opened');
    }, 150);
  };

  const handlePackagesClick = () => {
    console.log('📱 Packages clicked');
    dispatch(resetPackages());
    dispatch(commonActions.setGuestCounts({
      Adults: 1, Children: 0, Infants: 0,
      maleCount: 0, femaleCount: 0, ages: []
    }));
    
    if (dmcCountLoading) return;
    
    if (userRole !== "Agent") {
      closeMobileMenu();
      navigate(packagesPath, { 
        state: { selectedDMC: null, searchCriteria: null } 
      });
      return;
    }
    
    // Close mobile menu first, then open modal with a small delay
    closeMobileMenu();
    setTimeout(() => {
      console.log('📱 Opening packages search modal via parent');
      onOpenPackagesSearchModal();
      console.log('✅ Packages search modal opened');
    }, 150);
  };

  const handleLogout = async () => {
    console.log('📱 Logout clicked');
    closeMobileMenu();
    dispatch(clearSelectedDmc());
    dispatch(setAgentIdEdit(null));
    const result = await dispatch(logoutUser());
    if (logoutUser.fulfilled.match(result)) {
      navigate("/login");
    }
  };

  // All modal handlers are now managed by the parent header component

  // Check if the user is a manager or sales head
  const isManagerOrSalesHead = userRole === "Sales Head(DMC)" || userRole === "Sales Manager (DMC)" || userRole === "Assistant Manager (DMC)";

  // Menu items configuration
  const menuItems = [
    {
      id: 'dashboard',
      label: 'Dashboard',
      icon: <DashboardIcon />,
      onClick: handleDashboardClick,
      active: pathname === "/dashboard/db-dashboard",
      show: true
    },
    {
      id: 'book-tour',
      label: 'Book Tour',
      icon: <ExploreIcon />,
      onClick: handleBookTourClick,
      active: pathname === "/dashboard/db-dashboard/home_1",
      show: !isManagerOrSalesHead
    },
    {
      id: 'quick-enquiry',
      label: 'Quick Enquiry',
      icon: <EmailIcon />,
      onClick: handleBookEnquiryClick,
      active: pathname === "/dashboard/db-dashboard/home_2",
      show: !isManagerOrSalesHead
    },
    {
      id: 'packages',
      label: 'Packages',
      icon: <InventoryIcon />,
      onClick: handlePackagesClick,
      active: pathname === packagesPath,
      show: !(userRole === "Operational Head(DMC)" || userRole === "DMC Operational Manager" || userRole === "DMC Assistant Operational Manager")
    }
  ];

  return (
    <Box sx={{ width: 320, height: '100%', display: 'flex', flexDirection: 'column' }}>
      {/* Header */}
      <Box sx={{ 
        display: 'flex', 
        alignItems: 'center', 
        justifyContent: 'space-between',
        p: 2,
        borderBottom: '1px solid #e0e0e0'
      }}>
        <Link to="/" style={{ textDecoration: 'none' }}>
          <Avatar
            src={dmcLogo}
            sx={{ width: 50, height: 50, borderRadius: 2 }}
            variant="rounded"
          />
        </Link>
        <IconButton onClick={closeMobileMenu} size="small">
          <CloseIcon />
        </IconButton>
      </Box>

      {/* Menu Items */}
      <List sx={{ flex: 1, pt: 1 }}>
        {menuItems.filter(item => item.show).map((item) => (
          <ListItem key={item.id} disablePadding sx={{ px: 1 }}>
            <ListItemButton
              onClick={item.onClick}
              selected={item.active}
              sx={{
                borderRadius: 2,
                mx: 1,
                my: 0.5,
                '&.Mui-selected': {
                  backgroundColor: '#e3f2fd',
                  color: '#1976d2',
                  '& .MuiListItemIcon-root': {
                    color: '#1976d2',
                  },
                },
                '&:hover': {
                  backgroundColor: '#f5f5f5',
                },
              }}
            >
              <ListItemIcon sx={{ color: item.active ? '#1976d2' : '#666' }}>
                {item.icon}
              </ListItemIcon>
              <ListItemText 
                primary={item.label}
                primaryTypographyProps={{
                  fontWeight: item.active ? 600 : 400,
                  fontSize: '0.95rem'
                }}
              />
            </ListItemButton>
          </ListItem>
        ))}
      </List>

      {/* Footer */}
      <Box sx={{ borderTop: '1px solid #e0e0e0', p: 2 }}>
          {isAuthenticated && (
          <>
            <Divider sx={{ mb: 2 }} />
            <ListItem disablePadding>
              <ListItemButton
              onClick={handleLogout}
                sx={{
                  borderRadius: 2,
                  backgroundColor: '#ff4747',
                  color: 'white',
                  '&:hover': {
                    backgroundColor: '#d32f2f',
                  },
                }}
              >
                <ListItemIcon sx={{ color: 'white' }}>
                  <LogoutIcon />
                </ListItemIcon>
                <ListItemText 
                  primary="Logout"
                  primaryTypographyProps={{
                    fontWeight: 500
                  }}
                />
              </ListItemButton>
            </ListItem>
          </>
        )}
        
        <Box sx={{ mt: 2, textAlign: 'center' }}>
          <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
            Follow us on social media
          </Typography>
            <Social />
        </Box>
      </Box>

      {/* Modals are now rendered by the parent header component */}
    </Box>
  );
};

export default MobileMenu;
