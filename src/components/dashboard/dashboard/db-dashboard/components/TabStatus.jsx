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
  TextField,
  FormControl,
  InputLabel,
  Select,
  Button,
  InputAdornment,
  Typography,
} from "@mui/material";
import {
  DonutLarge,
  Upcoming as UpcomingIcon,
  History,
  Menu as MenuIcon,
  Search,
  Clear,
  PersonOutline,
  Public,
  Event,
  EventAvailable,
  FilterAlt,
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
  const [tabValue, setTabValue] = useState(1);
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
  const hasActiveFilters = Object.values(filters).some((value) => value !== '');
  const activeFilterCount = Object.values(filters).filter(Boolean).length;

  const commonFieldStyles = {
    minWidth: { xs: '100%', md: 180, lg: 200 },
    '& .MuiOutlinedInput-root': {
      borderRadius: 2.5,
      backgroundColor: 'rgba(255,255,255,0.9)',
      transition: 'all 0.2s ease',
      '&:hover': {
        boxShadow: '0 6px 18px rgba(19, 53, 123, 0.12)',
      },
      '&.Mui-focused': {
        boxShadow: '0 10px 24px rgba(19, 53, 123, 0.18)',
      },
      '& fieldset': {
        borderColor: 'rgba(19, 53, 123, 0.2)',
      },
      '&:hover fieldset': {
        borderColor: '#13357b',
      },
      '&.Mui-focused fieldset': {
        borderColor: '#13357b',
      },
    },
    '& .MuiInputLabel-root': {
      fontWeight: 600,
      color: '#4b5563',
      '&.Mui-focused': {
        color: '#13357b',
      },
    },
  };

  const inputIcon = (icon) => (
    <InputAdornment position="start">
      {icon}
    </InputAdornment>
  );

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
      <Card
        sx={{
          mb: 3,
          p: { xs: 2.5, md: 3 },
          borderRadius: 3,
          background: 'linear-gradient(135deg, rgba(19,53,123,0.08), rgba(67,97,238,0.12))',
          boxShadow: '0 20px 45px -24px rgba(19,53,123,0.45)',
          backdropFilter: 'blur(14px)'
        }}
      >
        <Box
          sx={{
            mb: { xs: 2, md: 2.5 },
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            flexWrap: 'wrap',
            gap: 1.5
          }}
        >
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <Search sx={{ color: '#13357b', fontSize: '1.2rem' }} />
            <Box sx={{ fontWeight: 700, fontSize: '1.05rem', color: '#13357b' }}>
              Search & Filter
            </Box>
          </Box>
          {hasActiveFilters && (
            <Button
              onClick={handleClearFilters}
              startIcon={<Clear />}
              sx={{
                color: '#f44336',
                fontWeight: 600,
                '&:hover': {
                  backgroundColor: 'rgba(244, 67, 54, 0.12)'
                }
              }}
            >
              Clear Filters
            </Button>
          )}
        </Box>

        <Box
          sx={{
            display: 'grid',
            gridTemplateColumns: {
              xs: '1fr',
              sm: 'repeat(2, minmax(0, 1fr))',
              md: 'repeat(5, minmax(160px, 1fr))'
            },
            gap: { xs: 2, sm: 2.5 }
          }}
        >
          <TextField
            name="searchId"
            label="🔍 Booking ID"
            variant="outlined"
            fullWidth
            value={filters.searchId}
            onChange={handleFilterChange}
            size="small"
            placeholder="Enter booking or enquiry ID"
            sx={{
              '& .MuiOutlinedInput-root': {
                borderRadius: 2,
                backgroundColor: 'rgba(255,255,255,0.92)',
                boxShadow: '0 12px 30px -18px rgba(19,53,123,0.6)',
                transition: 'all 0.25s ease',
                '& fieldset': {
                  borderColor: 'rgba(19,53,123,0.18)'
                },
                '&:hover': {
                  backgroundColor: '#fff',
                  transform: 'translateY(-2px)',
                  boxShadow: '0 20px 38px -16px rgba(19,53,123,0.35)'
                },
                '&:hover fieldset': {
                  borderColor: '#4361ee'
                },
                '&.Mui-focused': {
                  backgroundColor: '#fff',
                  boxShadow: '0 22px 44px -20px rgba(67,97,238,0.5)'
                },
                '&.Mui-focused fieldset': {
                  borderColor: '#4361ee',
                  borderWidth: 2
                }
              },
              '& .MuiInputLabel-root': {
                fontWeight: 600
              }
            }}
          />
          <TextField
            name="customerName"
            label="👤 Customer"
            variant="outlined"
            fullWidth
            value={filters.customerName}
            onChange={handleFilterChange}
            size="small"
            placeholder="Search customer name"
            sx={{
              '& .MuiOutlinedInput-root': {
                borderRadius: 2,
                backgroundColor: 'rgba(255,255,255,0.92)',
                boxShadow: '0 12px 30px -18px rgba(19,53,123,0.6)',
                transition: 'all 0.25s ease',
                '& fieldset': {
                  borderColor: 'rgba(19,53,123,0.18)'
                },
                '&:hover': {
                  backgroundColor: '#fff',
                  transform: 'translateY(-2px)',
                  boxShadow: '0 20px 38px -16px rgba(173,83,137,0.35)'
                },
                '&:hover fieldset': {
                  borderColor: '#fd79a8'
                },
                '&.Mui-focused': {
                  backgroundColor: '#fff',
                  boxShadow: '0 22px 44px -20px rgba(253,121,168,0.5)'
                },
                '&.Mui-focused fieldset': {
                  borderColor: '#fd79a8',
                  borderWidth: 2
                }
              },
              '& .MuiInputLabel-root': {
                fontWeight: 600
              }
            }}
          />
          <TextField
            name="country"
            label="🌍 Country"
            variant="outlined"
            fullWidth
            value={filters.country}
            onChange={handleFilterChange}
            size="small"
            placeholder="Filter by country"
            sx={{
              '& .MuiOutlinedInput-root': {
                borderRadius: 2,
                backgroundColor: 'rgba(255,255,255,0.92)',
                boxShadow: '0 12px 30px -18px rgba(19,53,123,0.6)',
                transition: 'all 0.25s ease',
                '& fieldset': {
                  borderColor: 'rgba(19,53,123,0.18)'
                },
                '&:hover': {
                  backgroundColor: '#fff',
                  transform: 'translateY(-2px)',
                  boxShadow: '0 20px 38px -16px rgba(253,203,110,0.35)'
                },
                '&:hover fieldset': {
                  borderColor: '#fdcb6e'
                },
                '&.Mui-focused': {
                  backgroundColor: '#fff',
                  boxShadow: '0 22px 44px -20px rgba(253,203,110,0.5)'
                },
                '&.Mui-focused fieldset': {
                  borderColor: '#fdcb6e',
                  borderWidth: 2
                }
              },
              '& .MuiInputLabel-root': {
                fontWeight: 600
              }
            }}
          />
          <TextField
            name="checkInDate"
            label="📅 Check-in"
            type="date"
            variant="outlined"
            fullWidth
            value={filters.checkInDate}
            onChange={handleFilterChange}
            size="small"
            InputLabelProps={{ shrink: true }}
            sx={{
              '& .MuiOutlinedInput-root': {
                borderRadius: 2,
                backgroundColor: 'rgba(255,255,255,0.92)',
                boxShadow: '0 12px 30px -18px rgba(19,53,123,0.6)',
                transition: 'all 0.25s ease',
                '& fieldset': {
                  borderColor: 'rgba(19,53,123,0.18)'
                },
                '&:hover': {
                  backgroundColor: '#fff',
                  transform: 'translateY(-2px)',
                  boxShadow: '0 20px 38px -16px rgba(76,175,80,0.35)'
                },
                '&:hover fieldset': {
                  borderColor: '#4caf50'
                },
                '&.Mui-focused': {
                  backgroundColor: '#fff',
                  boxShadow: '0 22px 44px -20px rgba(76,175,80,0.5)'
                },
                '&.Mui-focused fieldset': {
                  borderColor: '#4caf50',
                  borderWidth: 2
                }
              },
              '& .MuiInputLabel-root': {
                fontWeight: 600
              }
            }}
          />
          <TextField
            name="checkOutDate"
            label="📅 Check-out"
            type="date"
            variant="outlined"
            fullWidth
            value={filters.checkOutDate}
            onChange={handleFilterChange}
            size="small"
            InputLabelProps={{ shrink: true }}
            sx={{
              '& .MuiOutlinedInput-root': {
                borderRadius: 2,
                backgroundColor: 'rgba(255,255,255,0.92)',
                boxShadow: '0 12px 30px -18px rgba(19,53,123,0.6)',
                transition: 'all 0.25s ease',
                '& fieldset': {
                  borderColor: 'rgba(19,53,123,0.18)'
                },
                '&:hover': {
                  backgroundColor: '#fff',
                  transform: 'translateY(-2px)',
                  boxShadow: '0 20px 38px -16px rgba(255,152,0,0.35)'
                },
                '&:hover fieldset': {
                  borderColor: '#ff9800'
                },
                '&.Mui-focused': {
                  backgroundColor: '#fff',
                  boxShadow: '0 22px 44px -20px rgba(255,152,0,0.5)'
                },
                '&.Mui-focused fieldset': {
                  borderColor: '#ff9800',
                  borderWidth: 2
                }
              },
              '& .MuiInputLabel-root': {
                fontWeight: 600
              }
            }}
          />
        </Box>

        <Box
          sx={{
            mt: { xs: 2.5, md: 3 },
            display: 'flex',
            justifyContent: { xs: 'flex-start', md: 'flex-end' },
            alignItems: 'center'
          }}
        >
          <FormControl fullWidth size="small" sx={{ maxWidth: 280 }}>
            <InputLabel
              sx={{
                fontWeight: 600,
                '&.Mui-focused': {
                  color: '#9c27b0'
                }
              }}
            >
              📊 Status
            </InputLabel>
            <Select
              name="status"
              value={filters.status}
              label="📊 Status"
              onChange={handleFilterChange}
              sx={{
                borderRadius: 2,
                backgroundColor: 'rgba(255,255,255,0.94)',
                boxShadow: '0 14px 36px -20px rgba(19,53,123,0.55)',
                transition: 'all 0.25s ease',
                '& .MuiOutlinedInput-notchedOutline': {
                  borderColor: 'rgba(19,53,123,0.18)'
                },
                '&:hover': {
                  backgroundColor: '#fff',
                  transform: 'translateY(-1px)',
                  boxShadow: '0 20px 40px -18px rgba(156,39,176,0.38)'
                },
                '&:hover .MuiOutlinedInput-notchedOutline': {
                  borderColor: '#9c27b0'
                },
                '&.Mui-focused': {
                  backgroundColor: '#fff',
                  boxShadow: '0 24px 46px -18px rgba(156,39,176,0.45)'
                },
                '&.Mui-focused .MuiOutlinedInput-notchedOutline': {
                  borderColor: '#9c27b0',
                  borderWidth: 2
                }
              }}
              MenuProps={{
                PaperProps: {
                  sx: {
                    borderRadius: 2,
                    mt: 1,
                    boxShadow: '0 18px 34px rgba(0,0,0,0.12)',
                    border: '1px solid rgba(19,53,123,0.08)',
                    '& .MuiMenuItem-root': {
                      borderRadius: 1.5,
                      mx: 0.5,
                      my: 0.5,
                      '&:hover': {
                        background: '#13357b',
                        color: '#fff'
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
        </Box>
      </Card>

      {/* Active Filters Display */}
      {/* {hasActiveFilters && (
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
      )} */}

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
