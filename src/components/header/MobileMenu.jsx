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
import Social from "../common/social/Social";

const MobileMenu = ({ onMenuClose }) => {
  const { pathname } = useLocation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  const { userRole } = useSelector((state) => state.auth);
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);

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
    
    // Close mobile menu and navigate directly (no modal)
    closeMobileMenu();
    navigate("/dashboard/db-dashboard/home_1");
  };

  const handleBookEnquiryClick = () => {
    console.log('📱 Quick Enquiry clicked');
    
    // Close mobile menu and navigate directly (no modal)
    closeMobileMenu();
    navigate("/dashboard/db-dashboard/home_2");
  };

  const handlePackagesClick = () => {
    console.log('📱 Packages clicked');
    
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
    
    // Close mobile menu and navigate directly (no modal)
    closeMobileMenu();
    navigate(packagesPath);
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
        
        {/* <Box sx={{ mt: 2, textAlign: 'center' }}>
          <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
            Follow us on social media
          </Typography>
            <Social />
        </Box> */}
      </Box>
    </Box>
  );
};

export default MobileMenu;
