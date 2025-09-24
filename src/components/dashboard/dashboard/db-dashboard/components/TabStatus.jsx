import * as React from "react";
import { useState } from "react";
import { useSelector } from "react-redux";
import {
  Box,
  Card,
  Tabs,
  Tab,
  useMediaQuery,
  useTheme,
  Menu,
  MenuItem,
  IconButton,
} from "@mui/material";
import {
  DonutLarge,
  Upcoming as UpcomingIcon,
  History,
  Menu as MenuIcon,
} from "@mui/icons-material";
import Pending from "./Pending";
import Upcoming from "./Upcoming";
import Completed from "./Completed";
import Deleted from "./Deleted";

// TabPanel component for accessibility
function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && <Box sx={{ p: 0 }}>{children}</Box>}
    </div>
  );
}

function a11yProps(index) {
  return {
    id: `simple-tab-${index}`,
    'aria-controls': `simple-tabpanel-${index}`,
  };
}

export default function TabStatus() {
  const [tabValue, setTabValue] = useState(0);
  const [anchorEl, setAnchorEl] = useState(null);
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));

  // Get actual data from Redux store
  const { upcomingTours = [] } = useSelector((state) => state.lists);
  const { pendingTours = [] } = useSelector((state) => state.lists);
  const { lists = [] } = useSelector((state) => state.lists);

  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
    if (isMobile) {
      setAnchorEl(null); // Close dropdown after selection on mobile
    }
  };

  const handleMenuClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
  };

  // Tab configuration
  const tabs = [
    { icon: <DonutLarge />, label: 'Ongoing' },
    { icon: <UpcomingIcon />, label: 'Upcoming' },
    { icon: <History />, label: 'Past' }
  ];

  // Mobile Dropdown Menu Component
  const MobileDropdownMenu = () => (
    <Menu
      anchorEl={anchorEl}
      open={Boolean(anchorEl)}
      onClose={handleMenuClose}
      sx={{
        '& .MuiPaper-root': {
          mt: 1,
          borderRadius: 2,
          boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
          minWidth: 200,
        },
      }}
    >
      {tabs.map((tab, index) => (
        <MenuItem
          key={index}
          selected={tabValue === index}
          onClick={(e) => handleTabChange(e, index)}
          sx={{
            py: 1.5,
            px: 2,
            '&.Mui-selected': {
              backgroundColor: 'rgba(67, 97, 238, 0.1)',
              '&:hover': {
                backgroundColor: 'rgba(67, 97, 238, 0.15)',
              },
            },
            '&:hover': {
              backgroundColor: 'rgba(67, 97, 238, 0.05)',
            },
          }}
        >
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
            <Box sx={{ color: tabValue === index ? '#4361ee' : 'inherit' }}>
              {tab.icon}
            </Box>
            <Box
              sx={{
                fontWeight: tabValue === index ? 700 : 600,
                color: tabValue === index ? '#4361ee' : 'inherit',
              }}
            >
              {tab.label}
            </Box>
          </Box>
        </MenuItem>
      ))}
    </Menu>
  );

  return (
    <Box sx={{ width: "100%" }}>
      {/* Mobile Header with Menu Button */}
      {isMobile && (
        <Card sx={{ mb: 3, p: 2, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
            <IconButton
              onClick={handleMenuClick}
              sx={{
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                '&:hover': {
                  backgroundColor: 'rgba(67, 97, 238, 0.2)',
                },
              }}
            >
              <MenuIcon sx={{ color: '#4361ee' }} />
            </IconButton>
            <Box>
              <Box sx={{ fontWeight: 600, fontSize: '1.1rem', color: '#4361ee' }}>
                {tabs[tabValue]?.label}
              </Box>
            </Box>
          </Box>
        </Card>
      )}

      {/* Desktop Tabs */}
      {!isMobile && (
        <Card sx={{ mb: 3, overflow: 'hidden' }}>
          <Tabs
            value={tabValue}
            onChange={handleTabChange}
            aria-label="status tabs"
            indicatorColor="primary"
            textColor="primary"
            variant="fullWidth"
            sx={{
              background: 'linear-gradient(to right, #f5f7fa, #f9fcff)',
              '& .MuiTab-root': {
                fontWeight: 600,
                py: 2.5,
                textTransform: 'none',
                fontSize: '0.95rem',
                minHeight: '64px',
                transition: 'all 0.2s',
                '&:hover': {
                  backgroundColor: 'rgba(67, 97, 238, 0.04)',
                },
                '&.Mui-selected': {
                  color: '#4361ee',
                  fontWeight: 700,
                }
              }
            }}
          >
            {tabs.map((tab, index) => (
              <Tab
                key={index}
                icon={tab.icon}
                label={tab.label}
                iconPosition="start"
                {...a11yProps(index)}
              />
            ))}
          </Tabs>
        </Card>
      )}

      {/* Mobile Dropdown Menu */}
      {isMobile && <MobileDropdownMenu />}

      <TabPanel value={tabValue} index={0}>
        <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
          <Upcoming />
        </Card>
      </TabPanel>

      <TabPanel value={tabValue} index={1}>
        <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
          <Pending />
        </Card>
      </TabPanel>

      <TabPanel value={tabValue} index={2}>
        <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
          <Deleted />
        </Card>
      </TabPanel>
    </Box>
  );
}
