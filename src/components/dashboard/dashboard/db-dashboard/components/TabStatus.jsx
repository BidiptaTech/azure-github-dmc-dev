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
  Chip,
  InputAdornment,
  Tooltip,
  Stack,
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
  CalendarMonth,
  FilterList,
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
  const hasActiveFilters = Object.values(filters).some(value => value !== '');

  const filterFieldSx = {
    '& .MuiOutlinedInput-root': {
      borderRadius: 1.5,
      backgroundColor: '#fff',
      fontSize: '0.875rem',
      '& fieldset': {
        borderColor: 'rgba(19, 53, 123, 0.12)',
      },
      '&:hover fieldset': {
        borderColor: 'rgba(67, 97, 238, 0.45)',
      },
      '&.Mui-focused fieldset': {
        borderColor: '#4361ee',
        borderWidth: 1.5,
      },
    },
    '& .MuiInputLabel-root': {
      fontSize: '0.875rem',
      '&.Mui-focused': { color: '#4361ee' },
    },
  };

  const FILTER_LABELS = {
    searchId: 'Booking ID',
    customerName: 'Customer',
    country: 'Country',
    checkInDate: 'Check-in',
    checkOutDate: 'Check-out',
    status: 'Status',
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

      {/* Compact filter toolbar */}
      <Box
        sx={{
          mb: 2.5,
          borderRadius: 2,
          border: '1px solid rgba(19, 53, 123, 0.1)',
          backgroundColor: '#f8fafc',
          overflow: 'hidden',
        }}
      >
        <Box
          sx={{
            px: { xs: 1.5, sm: 2 },
            py: 1.25,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            gap: 1,
            borderBottom: hasActiveFilters ? '1px solid rgba(19, 53, 123, 0.08)' : 'none',
          }}
        >
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.75 }}>
            <FilterList sx={{ color: '#4361ee', fontSize: '1.1rem' }} />
            <Box sx={{ fontWeight: 600, fontSize: '0.85rem', color: '#13357b', letterSpacing: 0.2 }}>
              Filters
            </Box>
            {hasActiveFilters && (
              <Chip
                size="small"
                label={`${Object.values(filters).filter(Boolean).length} active`}
                sx={{
                  height: 22,
                  fontSize: '0.7rem',
                  fontWeight: 600,
                  backgroundColor: 'rgba(67, 97, 238, 0.12)',
                  color: '#4361ee',
                }}
              />
            )}
          </Box>
          {hasActiveFilters && (
            <Tooltip title="Clear all filters">
              <IconButton
                size="small"
                onClick={handleClearFilters}
                sx={{
                  color: '#64748b',
                  '&:hover': { color: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.08)' },
                }}
              >
                <Clear fontSize="small" />
              </IconButton>
            </Tooltip>
          )}
        </Box>

        <Stack
          direction="row"
          flexWrap="wrap"
          useFlexGap
          spacing={1.25}
          sx={{ px: { xs: 1.5, sm: 2 }, py: 1.5 }}
        >
          <TextField
            name="searchId"
            placeholder="Booking / enquiry ID"
            variant="outlined"
            size="small"
            value={filters.searchId}
            onChange={handleFilterChange}
            sx={{ ...filterFieldSx, flex: { xs: '1 1 100%', sm: '1 1 180px' }, minWidth: { sm: 180 } }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Search sx={{ fontSize: '1.1rem', color: '#94a3b8' }} />
                </InputAdornment>
              ),
            }}
          />
          <TextField
            name="customerName"
            placeholder="Customer"
            variant="outlined"
            size="small"
            value={filters.customerName}
            onChange={handleFilterChange}
            sx={{ ...filterFieldSx, flex: '1 1 140px', minWidth: 140 }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <PersonOutline sx={{ fontSize: '1.1rem', color: '#94a3b8' }} />
                </InputAdornment>
              ),
            }}
          />
          <TextField
            name="country"
            placeholder="Country"
            variant="outlined"
            size="small"
            value={filters.country}
            onChange={handleFilterChange}
            sx={{ ...filterFieldSx, flex: '1 1 130px', minWidth: 130 }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Public sx={{ fontSize: '1.1rem', color: '#94a3b8' }} />
                </InputAdornment>
              ),
            }}
          />
          <TextField
            name="checkInDate"
            type="date"
            size="small"
            variant="outlined"
            value={filters.checkInDate}
            onChange={handleFilterChange}
            sx={{ ...filterFieldSx, flex: '1 1 145px', minWidth: 145 }}
            InputLabelProps={{ shrink: true }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Tooltip title="Check-in">
                    <CalendarMonth sx={{ fontSize: '1.05rem', color: '#94a3b8' }} />
                  </Tooltip>
                </InputAdornment>
              ),
            }}
          />
          <TextField
            name="checkOutDate"
            type="date"
            size="small"
            variant="outlined"
            value={filters.checkOutDate}
            onChange={handleFilterChange}
            sx={{ ...filterFieldSx, flex: '1 1 145px', minWidth: 145 }}
            InputLabelProps={{ shrink: true }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Tooltip title="Check-out">
                    <CalendarMonth sx={{ fontSize: '1.05rem', color: '#94a3b8' }} />
                  </Tooltip>
                </InputAdornment>
              ),
            }}
          />
          <FormControl size="small" sx={{ ...filterFieldSx, flex: '1 1 140px', minWidth: 140 }}>
            <InputLabel>Status</InputLabel>
            <Select
              name="status"
              value={filters.status}
              label="Status"
              onChange={handleFilterChange}
              MenuProps={{
                PaperProps: {
                  sx: {
                    borderRadius: 1.5,
                    mt: 0.5,
                    maxHeight: 320,
                    boxShadow: '0 8px 24px rgba(19, 53, 123, 0.12)',
                    '& .MuiMenuItem-root': {
                      fontSize: '0.85rem',
                      py: 1,
                      '&:hover': { backgroundColor: 'rgba(67, 97, 238, 0.08)' },
                      '&.Mui-selected': {
                        backgroundColor: 'rgba(67, 97, 238, 0.12)',
                        '&:hover': { backgroundColor: 'rgba(67, 97, 238, 0.16)' },
                      },
                    },
                  },
                },
              }}
            >
              <MenuItem value="">All statuses</MenuItem>
              <MenuItem value="Confirmed">Confirmed</MenuItem>
              <MenuItem value="Definite">Definite</MenuItem>
              <MenuItem value="Actual">Actual</MenuItem>
              <MenuItem value="Pending">Pending</MenuItem>
              <MenuItem value="Tentative">Tentative</MenuItem>
              <MenuItem value="New Enquiry">New Enquiry</MenuItem>
              <MenuItem value="Prospect">Prospect</MenuItem>
              <MenuItem value="Closed">Closed</MenuItem>
              <MenuItem value="Auto Cancel">Auto Cancel</MenuItem>
              <MenuItem value="Refunded">Refunded</MenuItem>
              <MenuItem value="On Hold">On Hold</MenuItem>
              <MenuItem value="Refund - Pending">Refund - Pending</MenuItem>
              <MenuItem value="Cancel">Cancel</MenuItem>
            </Select>
          </FormControl>
        </Stack>

        {hasActiveFilters && (
          <Box
            sx={{
              px: { xs: 1.5, sm: 2 },
              pb: 1.5,
              display: 'flex',
              flexWrap: 'wrap',
              gap: 0.75,
            }}
          >
            {Object.entries(filters).map(([key, value]) =>
              value ? (
                <Chip
                  key={key}
                  size="small"
                  label={`${FILTER_LABELS[key]}: ${value}`}
                  onDelete={() =>
                    setFilters((prev) => ({
                      ...prev,
                      [key]: '',
                    }))
                  }
                  sx={{
                    height: 24,
                    fontSize: '0.72rem',
                    fontWeight: 500,
                    backgroundColor: '#fff',
                    border: '1px solid rgba(67, 97, 238, 0.25)',
                    color: '#13357b',
                    '& .MuiChip-deleteIcon': {
                      fontSize: '0.95rem',
                      color: '#94a3b8',
                      '&:hover': { color: '#ef4444' },
                    },
                  }}
                />
              ) : null
            )}
          </Box>
        )}
      </Box>

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
