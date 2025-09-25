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
  Grid,
  TextField,
  FormControl,
  InputLabel,
  Select,
  Button,
  Chip,
} from "@mui/material";
import {
  DonutLarge,
  Upcoming as UpcomingIcon,
  History,
  Menu as MenuIcon,
  Search,
  Clear,
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

  // Search filters state
  const [filters, setFilters] = useState({
    searchId: '',
    customerName: '',
    country: '',
    checkInDate: '',
    checkOutDate: '',
    status: ''
  });

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

  // Handle filter changes
  const handleFilterChange = (event) => {
    const { name, value } = event.target;
    setFilters(prev => ({
      ...prev,
      [name]: value
    }));
  };

  // Clear all filters
  const handleClearFilters = () => {
    setFilters({
      searchId: '',
      customerName: '',
      country: '',
      checkInDate: '',
      checkOutDate: '',
      status: ''
    });
  };

  // Check if any filters are active
  const hasActiveFilters = Object.values(filters).some(value => value !== '');

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

      {/* Search Filters Section */}
      <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
        <Box sx={{ mb: 2, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <Search sx={{ color: '#4361ee', fontSize: '1.2rem' }} />
            <Box sx={{ fontWeight: 600, fontSize: '1.1rem', color: '#4361ee' }}>
              Search & Filter
            </Box>
          </Box>
          {hasActiveFilters && (
            <Button
              onClick={handleClearFilters}
              startIcon={<Clear />}
              sx={{
                color: '#f44336',
                '&:hover': {
                  backgroundColor: 'rgba(244, 67, 54, 0.1)',
                },
              }}
            >
              Clear Filters
            </Button>
          )}
        </Box>

        <Grid container spacing={{ xs: 2, sm: 3, md: 4 }}>
          <Grid item xs={12} sm={6} md={3}>
            <TextField
              name="searchId"
              label="🔍 Search by Booking ID"
              variant="outlined"
              fullWidth
              value={filters.searchId}
              onChange={handleFilterChange}
              size="medium"
              sx={{
                '& .MuiOutlinedInput-root': {
                  borderRadius: { xs: 2, sm: 2.5, md: 3 },
                  background: 'rgba(255,255,255,0.8)',
                  backdropFilter: 'blur(10px)',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    background: 'rgba(255,255,255,0.95)',
                    transform: 'translateY(-2px)',
                    boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
                  },
                  '&:hover fieldset': {
                    borderColor: '#667eea',
                    borderWidth: 2,
                  },
                  '&.Mui-focused': {
                    background: 'rgba(255,255,255,1)',
                    boxShadow: '0 4px 20px rgba(102, 126, 234, 0.2)',
                  },
                  '&.Mui-focused fieldset': {
                    borderColor: '#667eea',
                    borderWidth: 2,
                  }
                },
                '& .MuiInputLabel-root': {
                  fontWeight: 600,
                  '&.Mui-focused': {
                    color: '#667eea',
                  }
                }
              }}
              placeholder="Enter booking ID or multi-enquiry ID..."
            />
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <TextField
              name="customerName"
              label="👤 Customer Name"
              variant="outlined"
              fullWidth
              value={filters.customerName}
              onChange={handleFilterChange}
              size="medium"
              sx={{
                '& .MuiOutlinedInput-root': {
                  borderRadius: { xs: 2, sm: 2.5, md: 3 },
                  background: 'rgba(255,255,255,0.8)',
                  backdropFilter: 'blur(10px)',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    background: 'rgba(255,255,255,0.95)',
                    transform: 'translateY(-2px)',
                    boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
                  },
                  '&:hover fieldset': {
                    borderColor: '#fd79a8',
                    borderWidth: 2,
                  },
                  '&.Mui-focused': {
                    background: 'rgba(255,255,255,1)',
                    boxShadow: '0 4px 20px rgba(253, 121, 168, 0.2)',
                  },
                  '&.Mui-focused fieldset': {
                    borderColor: '#fd79a8',
                    borderWidth: 2,
                  }
                },
                '& .MuiInputLabel-root': {
                  fontWeight: 600,
                  '&.Mui-focused': {
                    color: '#fd79a8',
                  }
                }
              }}
              placeholder="Enter customer name..."
            />
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <TextField
              name="country"
              label="🌍 Filter by Country"
              variant="outlined"
              fullWidth
              value={filters.country}
              onChange={handleFilterChange}
              size="medium"
              sx={{
                '& .MuiOutlinedInput-root': {
                  borderRadius: { xs: 2, sm: 2.5, md: 3 },
                  background: 'rgba(255,255,255,0.8)',
                  backdropFilter: 'blur(10px)',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    background: 'rgba(255,255,255,0.95)',
                    transform: 'translateY(-2px)',
                    boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
                  },
                  '&:hover fieldset': {
                    borderColor: '#fdcb6e',
                    borderWidth: 2,
                  },
                  '&.Mui-focused': {
                    background: 'rgba(255,255,255,1)',
                    boxShadow: '0 4px 20px rgba(253, 203, 110, 0.2)',
                  },
                  '&.Mui-focused fieldset': {
                    borderColor: '#fdcb6e',
                    borderWidth: 2,
                  }
                },
                '& .MuiInputLabel-root': {
                  fontWeight: 600,
                  '&.Mui-focused': {
                    color: '#fdcb6e',
                  }
                }
              }}
              placeholder="Enter country name..."
            />
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <TextField
              name="checkInDate"
              label="📅 Check-in Date"
              type="date"
              variant="outlined"
              fullWidth
              value={filters.checkInDate}
              onChange={handleFilterChange}
              size="medium"
              InputLabelProps={{ shrink: true }}
              sx={{
                '& .MuiOutlinedInput-root': {
                  borderRadius: { xs: 2, sm: 2.5, md: 3 },
                  background: 'rgba(255,255,255,0.8)',
                  backdropFilter: 'blur(10px)',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    background: 'rgba(255,255,255,0.95)',
                    transform: 'translateY(-2px)',
                    boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
                  },
                  '&:hover fieldset': {
                    borderColor: '#4caf50',
                    borderWidth: 2,
                  },
                  '&.Mui-focused': {
                    background: 'rgba(255,255,255,1)',
                    boxShadow: '0 4px 20px rgba(76, 175, 80, 0.2)',
                  },
                  '&.Mui-focused fieldset': {
                    borderColor: '#4caf50',
                    borderWidth: 2,
                  }
                },
                '& .MuiInputLabel-root': {
                  fontWeight: 600,
                  '&.Mui-focused': {
                    color: '#4caf50',
                  }
                }
              }}
            />
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <TextField
              name="checkOutDate"
              label="📅 Check-out Date"
              type="date"
              variant="outlined"
              fullWidth
              value={filters.checkOutDate}
              onChange={handleFilterChange}
              size="medium"
              InputLabelProps={{ shrink: true }}
              sx={{
                '& .MuiOutlinedInput-root': {
                  borderRadius: { xs: 2, sm: 2.5, md: 3 },
                  background: 'rgba(255,255,255,0.8)',
                  backdropFilter: 'blur(10px)',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    background: 'rgba(255,255,255,0.95)',
                    transform: 'translateY(-2px)',
                    boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
                  },
                  '&:hover fieldset': {
                    borderColor: '#ff9800',
                    borderWidth: 2,
                  },
                  '&.Mui-focused': {
                    background: 'rgba(255,255,255,1)',
                    boxShadow: '0 4px 20px rgba(255, 152, 0, 0.2)',
                  },
                  '&.Mui-focused fieldset': {
                    borderColor: '#ff9800',
                    borderWidth: 2,
                  }
                },
                '& .MuiInputLabel-root': {
                  fontWeight: 600,
                  '&.Mui-focused': {
                    color: '#ff9800',
                  }
                }
              }}
            />
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <FormControl fullWidth size="medium">
              <InputLabel sx={{ 
                fontWeight: 600,
                '&.Mui-focused': {
                  color: '#9c27b0',
                }
              }}>📊 Status</InputLabel>
              <Select
                name="status"
                value={filters.status}
                label="📊 Status"
                onChange={handleFilterChange}
                sx={{
                  borderRadius: { xs: 2, sm: 2.5, md: 3 },
                  background: 'rgba(255,255,255,0.8)',
                  backdropFilter: 'blur(10px)',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    background: 'rgba(255,255,255,0.95)',
                    transform: 'translateY(-2px)',
                    boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
                  },
                  '&:hover .MuiOutlinedInput-notchedOutline': {
                    borderColor: '#9c27b0',
                    borderWidth: 2,
                  },
                  '&.Mui-focused': {
                    background: 'rgba(255,255,255,1)',
                    boxShadow: '0 4px 20px rgba(156, 39, 176, 0.2)',
                  },
                  '&.Mui-focused .MuiOutlinedInput-notchedOutline': {
                    borderColor: '#9c27b0',
                    borderWidth: 2,
                  }
                }}
                MenuProps={{
                  PaperProps: {
                    sx: {
                      borderRadius: { xs: 2, sm: 2.5, md: 3 },
                      mt: 1,
                      boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
                      border: '1px solid rgba(255,255,255,0.3)',
                      '& .MuiMenuItem-root': {
                        borderRadius: 2,
                        mx: 1,
                        my: 0.5,
                        '&:hover': {
                          background: 'linear-gradient(45deg, #9c27b0, #673ab7)',
                          color: 'white',
                        }
                      }
                    }
                  }
                }}
              >
                <MenuItem value="">🌐 All Status</MenuItem>
                <MenuItem value="Confirmed">✅ Confirmed</MenuItem>
                <MenuItem value="Definite">🎯 Definite</MenuItem>
                <MenuItem value="Actual">✅ Actual</MenuItem>
                <MenuItem value="Pending">⏳ Pending</MenuItem>
                <MenuItem value="Tentative">🤔 Tentative</MenuItem>
                <MenuItem value="New Enquiry">🆕 New Enquiry</MenuItem>
                <MenuItem value="Prospect">👀 Prospect</MenuItem>
                <MenuItem value="Closed">🔒 Closed</MenuItem>
                <MenuItem value="Auto Cancel">🔄 Auto Cancel</MenuItem>
                <MenuItem value="Refunded">💰 Refunded</MenuItem>
                <MenuItem value="On Hold">⏸️ On Hold</MenuItem>
                <MenuItem value="Refund - Pending">⏳ Refund - Pending</MenuItem>
                <MenuItem value="Cancel">❌ Cancel</MenuItem>
              </Select>
            </FormControl>
          </Grid>
        </Grid>

        {/* Active Filters Display */}
        {hasActiveFilters && (
          <Box sx={{ mt: 2, display: 'flex', flexWrap: 'wrap', gap: 1 }}>
            {Object.entries(filters).map(([key, value]) => {
              if (value) {
                return (
                  <Chip
                    key={key}
                    label={`${key}: ${value}`}
                    onDelete={() => {
                      setFilters(prev => ({
                        ...prev,
                        [key]: ''
                      }));
                    }}
                    sx={{
                      backgroundColor: 'rgba(67, 97, 238, 0.1)',
                      color: '#4361ee',
                      '& .MuiChip-deleteIcon': {
                        color: '#4361ee',
                      }
                    }}
                  />
                );
              }
              return null;
            })}
          </Box>
        )}
      </Card>

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
          <Upcoming filters={filters} />
        </Card>
      </TabPanel>

      <TabPanel value={tabValue} index={1}>
        <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
          <Pending filters={filters} />
        </Card>
      </TabPanel>

      <TabPanel value={tabValue} index={2}>
        <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
          <Deleted filters={filters} />
        </Card>
      </TabPanel>
    </Box>
  );
}
