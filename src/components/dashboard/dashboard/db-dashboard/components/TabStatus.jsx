import * as React from "react";
import { useState } from "react";
import { useSelector } from "react-redux";
import {
  Box,
  Card,
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
  Business,
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
    country: 'Company',
    checkInDate: 'Check-in',
    checkOutDate: 'Check-out',
    status: 'Status',
  };

  // Tab configuration
  const tabCounts = [
    upcomingTours.length,
    pendingTours.length,
    lists.length,
  ];

  const tabs = [
    { icon: <DonutLarge sx={{ fontSize: 18 }} />, label: 'Ongoing', count: tabCounts[0] },
    { icon: <UpcomingIcon sx={{ fontSize: 18 }} />, label: 'Upcoming', count: tabCounts[1] },
    { icon: <History sx={{ fontSize: 18 }} />, label: 'Past', count: tabCounts[2] },
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
          mb: 1,
          borderRadius: '14px',
          border: '1px solid #e8eef8',
          backgroundColor: '#fff',
          overflow: 'hidden',
        }}
      >
        <Box
          sx={{
            px: { xs: 1.25, sm: 1.5 },
            py: 0.75,
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
          <Box
            component="button"
            type="button"
            onClick={handleClearFilters}
            sx={{
              border: '1px solid #e2e8f0',
              background: '#fff',
              color: '#64748b',
              borderRadius: '999px',
              px: 1.25,
              py: 0.4,
              fontSize: '0.8rem',
              fontWeight: 600,
              cursor: 'pointer',
              display: 'inline-flex',
              alignItems: 'center',
              gap: 0.5,
              '&:hover': { borderColor: '#cbd5e1', color: '#0f172a' },
            }}
          >
            <Clear sx={{ fontSize: 16 }} />
            Clear Filters
          </Box>
        </Box>

        <Stack
          direction="row"
          flexWrap="wrap"
          useFlexGap
          spacing={1}
          sx={{ px: { xs: 1.25, sm: 1.5 }, pb: 1.25, pt: 0.5 }}
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
            placeholder="Company"
            variant="outlined"
            size="small"
            value={filters.country}
            onChange={handleFilterChange}
            sx={{ ...filterFieldSx, flex: '1 1 150px', minWidth: 150 }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Business sx={{ fontSize: '1.1rem', color: '#94a3b8' }} />
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
        <Box
          sx={{
            mb: 1,
            display: 'grid',
            gridTemplateColumns: 'repeat(3, 1fr)',
            borderRadius: '14px',
            border: '1px solid #e8eef8',
            backgroundColor: '#fff',
            overflow: 'hidden',
          }}
        >
          {tabs.map((tab, index) => {
            const selected = tabValue === index;
            return (
              <Box
                key={tab.label}
                component="button"
                type="button"
                onClick={(event) => handleTabChange(event, index)}
                sx={{
                  border: 'none',
                  borderBottom: selected ? '2px solid #3554d1' : '2px solid transparent',
                  background: selected ? '#f3f7ff' : '#fff',
                  color: selected ? '#3554d1' : '#64748b',
                  py: 0.85,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: 0.75,
                  cursor: 'pointer',
                  fontWeight: 700,
                  fontSize: '0.95rem',
                  fontFamily: 'inherit',
                }}
              >
                {tab.icon}
                {tab.label}
                <Box
                  component="span"
                  sx={{
                    minWidth: 28,
                    px: 0.75,
                    py: 0.1,
                    borderRadius: '999px',
                    bgcolor: selected ? '#e7efff' : '#f1f5f9',
                    color: selected ? '#3554d1' : '#64748b',
                    fontSize: '0.8rem',
                    fontWeight: 700,
                  }}
                >
                  {tab.count}
                </Box>
              </Box>
            );
          })}
        </Box>
      )}

      {/* Mobile Dropdown Menu */}
      {isMobile && <MobileDropdownMenu />}

      <TabPanel value={tabValue} index={0}>
        <Card sx={{ mb: 1.5, p: 1.25, boxShadow: 'none', border: '1px solid #e8eef8', borderRadius: '14px' }}>
          <Upcoming filters={filters} />
        </Card>
      </TabPanel>

      <TabPanel value={tabValue} index={1}>
        <Card sx={{ mb: 1.5, p: 1.25, boxShadow: 'none', border: '1px solid #e8eef8', borderRadius: '14px' }}>
          <Pending filters={filters} />
        </Card>
      </TabPanel>

      <TabPanel value={tabValue} index={2}>
        <Card sx={{ mb: 1.5, p: 1.25, boxShadow: 'none', border: '1px solid #e8eef8', borderRadius: '14px' }}>
          <Deleted filters={filters} />
        </Card>
      </TabPanel>
    </Box>
  );
}
