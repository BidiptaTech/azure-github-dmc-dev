import React, { useState, useEffect } from "react";
import {
  Box,
  Typography,
  Paper,
  TableContainer,
  Table,
  TableHead,
  TableBody,
  TableRow,
  TableCell,
  TablePagination,
  Chip,
  IconButton,
  CircularProgress,
  TextField,
  MenuItem,
  FormControl,
  InputLabel,
  Select,
  Grid,
  Card,
  CardContent,
  Tooltip,
  TableSortLabel,
  Button,
  Modal,
  Divider,
  Avatar,
  List,
  ListItem,
  ListItemAvatar,
  ListItemText,
  Tabs,
  Tab,
  Badge,
  Alert,
  Snackbar,
} from "@mui/material";
import { alpha } from "@mui/material/styles";
import {
  Visibility as VisibilityIcon,
  Event as EventIcon,
  LocationOn as LocationIcon,
  People as PeopleIcon,
  Hotel as HotelIcon,
  LocalTaxi as LocalTaxiIcon,
  Attractions as AttractionsIcon,
  Park as ParkIcon,
  Restaurant as RestaurantIcon,
  Person as PersonIcon,
  Close as CloseIcon,
  AccessTime as AccessTimeIcon,
  Phone as PhoneIcon,
  Email as EmailIcon,
  DirectionsBoat as DirectionsBoatIcon,
  Refresh as RefreshIcon,
} from '@mui/icons-material';

import dayjs from 'dayjs';
import { useSelector, useDispatch } from 'react-redux';
import { fetchEnquiries } from '@/slice/enquiries/enquiryListSlice';
import { convertEnquiresToTourId, resetConvertState } from '@/slice/enquiries/enquiryToTourSlice';
import { unwrapResult } from '@reduxjs/toolkit';
import { fetchLists } from '@/slice/common/TourlistSlice';
import { useNavigate } from 'react-router-dom';
// Import the render service components
import {
  RenderHotelDetails,
  RenderAttractionDetails,
  RenderRestaurantDetails,
  RenderGuideDetails,
  RenderLocalTransferDetails,
  RenderPortDetails,
  RenderPackagedAttractionsDetails
} from './renderServices';
import { clearPackages, setPackageData } from "@/slice/tour-packages/tourPackageSlice";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";

// Modal style
const modalStyle = {
  position: "absolute",
  top: "50%",
  left: "50%",
  transform: "translate(-50%, -50%)",
  width: "80%",
  maxWidth: 1000,
  maxHeight: "90vh",
  bgcolor: "background.paper",
  boxShadow: 24,
  borderRadius: 2,
  p: 4,
  overflow: "auto",
};

const EnquiryList = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { enquiries, status, error, stats } = useSelector(
    (state) => state.bookingEnquiry
  );
  const { userRole } = useSelector((state) => state.auth);
  console.log(enquiries, "enq");

  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [filters, setFilters] = useState({
    country: "",
    city: "",
    dateRange: "all",
    searchId: "", // Add search by booking ID
  });
  const [order, setOrder] = useState("desc");
  const [orderBy, setOrderBy] = useState("created_at");

  // State for modal
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedService, setSelectedService] = useState(null);
  const [selectedEnquiry, setSelectedEnquiry] = useState(null);
  const [tabValue, setTabValue] = useState(0);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [lastEnquiryCount, setLastEnquiryCount] = useState(0);
  const [showNewEnquiryAlert, setShowNewEnquiryAlert] = useState(false);

  // Handle manual refresh
  const handleRefresh = async () => {
    setIsRefreshing(true);
    try {
      await dispatch(fetchEnquiries()).unwrap();
    } catch (error) {
      console.error('Failed to refresh enquiries:', error);
    } finally {
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    if (status === "idle") {
      dispatch(fetchEnquiries());
    }
  }, [status, dispatch]);

  // Add a separate effect to refresh enquiries periodically and on component mount
  useEffect(() => {
    // Fetch enquiries on component mount
    dispatch(fetchEnquiries());
    
    // // Set up periodic refresh every 30 seconds
    // const interval = setInterval(() => {
    //   dispatch(fetchEnquiries());
    // }, 30000); // 30 seconds
    
    // // Cleanup interval on component unmount
    // return () => clearInterval(interval);
  }, [dispatch]);

  // Effect to detect new enquiries
  useEffect(() => {
    if (enquiries.length > 0 && lastEnquiryCount > 0 && enquiries.length > lastEnquiryCount) {
      setShowNewEnquiryAlert(true);
      // Auto-hide the alert after 5 seconds
      setTimeout(() => setShowNewEnquiryAlert(false), 5000);
    }
    setLastEnquiryCount(enquiries.length);
  }, [enquiries.length, lastEnquiryCount]);

  const handleChangePage = (event, newPage) => {
    setPage(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(parseInt(event.target.value, 10));
    setPage(0);
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters((prev) => ({
      ...prev,
      [name]: value,
    }));
    setPage(0);
  };

  const handleRequestSort = (property) => {
    const isAsc = orderBy === property && order === "asc";
    setOrder(isAsc ? "desc" : "asc");
    setOrderBy(property);
  };

  // Handler for opening the service details modal
  const handleServiceClick = (enquiry, serviceType) => {
   
    setSelectedEnquiry(enquiry);
    setSelectedService(serviceType);
    setModalOpen(true);
    setTabValue(0);
  };

  // Handler for changing tabs in the modal
  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };

  // Custom filtering logic
  const filteredEnquiries = enquiries.filter((enquiry) => {
    // Filter by booking ID (display_id)
    if (
      filters.searchId &&
      !enquiry.display_id.toLowerCase().includes(filters.searchId.toLowerCase())
    ) {
      return false;
    }

    // Filter by country
    if (
      filters.country &&
      !enquiry.country.toLowerCase().includes(filters.country.toLowerCase())
    ) {
      return false;
    }

    // Filter by city
    if (
      filters.city &&
      !enquiry.city.toLowerCase().includes(filters.city.toLowerCase())
    ) {
      return false;
    }

    // Filter by date range
    if (filters.dateRange !== "all") {
      const today = dayjs();
      const checkInDate = dayjs(enquiry.check_in_time);

      if (filters.dateRange === "upcoming" && checkInDate.isBefore(today)) {
        return false;
      } else if (filters.dateRange === "past" && checkInDate.isAfter(today)) {
        return false;
      }
    }

    return true;
  });

  function descendingComparator(a, b, orderBy) {
    // Special handling for nested properties
    if (orderBy === "location") {
      if (b.city.toLowerCase() < a.city.toLowerCase()) {
        return -1;
      }
      if (b.city.toLowerCase() > a.city.toLowerCase()) {
        return 1;
      }
      return 0;
    }

    if (orderBy === "dates") {
      if (dayjs(b.check_in_time).valueOf() < dayjs(a.check_in_time).valueOf()) {
        return -1;
      }
      if (dayjs(b.check_in_time).valueOf() > dayjs(a.check_in_time).valueOf()) {
        return 1;
      }
      return 0;
    }

    if (orderBy === "travelers") {
      const totalA = a.adult + a.child + a.infant;
      const totalB = b.adult + b.child + b.infant;
      if (totalB < totalA) {
        return -1;
      }
      if (totalB > totalA) {
        return 1;
      }
      return 0;
    }

    if (orderBy === "services") {
      const servicesA =
        (a.hotel ? 1 : 0) +
        (a.pickup ? 1 : 0) +
        (a.attraction ? 1 : 0) +
        (a.restaurant ? 1 : 0) +
        (a.guide ? 1 : 0);
      const servicesB =
        (b.hotel ? 1 : 0) +
        (b.pickup ? 1 : 0) +
        (b.attraction ? 1 : 0) +
        (b.restaurant ? 1 : 0) +
        (b.guide ? 1 : 0);
      if (servicesB < servicesA) {
        return -1;
      }
      if (servicesB > servicesA) {
        return 1;
      }
      return 0;
    }

    if (orderBy === "price") {
      const priceA = parseFloat(a.approx_price) || 0;
      const priceB = parseFloat(b.approx_price) || 0;
      if (priceB < priceA) {
        return -1;
      }
      if (priceB > priceA) {
        return 1;
      }
      return 0;
    }

    // Default handling for direct properties
    if (b[orderBy] < a[orderBy]) {
      return -1;
    }
    if (b[orderBy] > a[orderBy]) {
      return 1;
    }
    return 0;
  }

  function getComparator(order, orderBy) {
    return order === "desc"
      ? (a, b) => descendingComparator(a, b, orderBy)
      : (a, b) => -descendingComparator(a, b, orderBy);
  }

  // Apply sorting
  const sortedEnquiries = [...filteredEnquiries].sort(
    getComparator(order, orderBy)
  );

  const paginatedEnquiries = sortedEnquiries.slice(
    page * rowsPerPage,
    page * rowsPerPage + rowsPerPage
  );

  // Helper to format date
  const formatDate = (dateString) => {
    return dayjs(dateString).format("MMM DD, YYYY HH:mm");
  };

 

  // Custom TableCell header with sorting
  const SortableTableCell = ({ id, label, disableSort = false }) => (
    <TableCell
      sx={{ fontWeight: "bold", bgcolor: "#f5f5f5" }}
      sortDirection={orderBy === id ? order : false}
    >
      {disableSort ? (
        label
      ) : (
        <TableSortLabel
          active={orderBy === id}
          direction={orderBy === id ? order : "asc"}
          onClick={() => handleRequestSort(id)}
        >
          {label}
        </TableSortLabel>
      )}
    </TableCell>
  );

  // Convert to tour
  const handleConvert = async (enquiry) => {
    const agentId = enquiry.agent_id;
    const enquiryId = enquiry.enquiry_id;
    dispatch(setPackageData(null));
        dispatch(clearPackages());
        dispatch(resetConvertState());

    try {
      // Wait for conversion to complete
      await dispatch(
        convertEnquiresToTourId({ agentId, enquiryID: enquiryId })
      ).unwrap()
      .then((response) => {
        dispatch(resetPackages());
        navigate("/dashboard/tour-packages");
        console.log("Full Response Data:", response);
      })
      .catch((error) => {
        console.error("Error fetching booking:", error);
      });

      // Then refresh the enquiry list
      // dispatch(fetchEnquiries());
      // dispatch(fetchLists(agentId));
    } catch (error) {
      console.error("Conversion failed:", error);
      // Optionally show a toast or error message here
    }
  };

  if (status === 'loading' && enquiries.length === 0) {
    return (
      <Box
        sx={{
          p: 4,
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          bgcolor: alpha('#1976d2', 0.04),
          borderRadius: 2,
        }}
      >
        <CircularProgress size={24} sx={{ mr: 1 }} />
        <Typography variant="body1" color="primary">
          Loading enquiries...
        </Typography>
      </Box>
    );
  }

  if (status === "failed") {
    return (
      <Box
        sx={{
          p: 4,
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          bgcolor: alpha("#d32f2f", 0.04),
          borderRadius: 2,
        }}
      >
        <Typography variant="body1" color="error">
          Error: {error}
        </Typography>
      </Box>
    );
  }

  return (
    <Box sx={{ width: "100%" }}>
      {/* Enhanced Stats Cards */}
      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={2}>
          <Card sx={{ 
            bgcolor: alpha("#1976d2", 0.04), 
            height: "100%",
            transition: 'all 0.3s ease',
            cursor: 'pointer',
            '&:hover': {
              transform: 'translateY(-4px)',
              boxShadow: 3,
              bgcolor: alpha("#1976d2", 0.08),
            }
          }}>
            <CardContent sx={{ textAlign: "center", py: 3 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ bgcolor: '#1976d2', width: 40, height: 40 }}>
                  <EventIcon />
                </Avatar>
              </Box>
              <Typography
                variant="h4"
                sx={{ fontWeight: 700, color: "#1976d2", mb: 1 }}
              >
                {stats.total}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ fontWeight: 500 }}>
                Total Enquiries
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={2}>
          <Card sx={{ 
            bgcolor: alpha("#1976d2", 0.04), 
            height: "100%",
            transition: 'all 0.3s ease',
            cursor: 'pointer',
            '&:hover': {
              transform: 'translateY(-4px)',
              boxShadow: 3,
              bgcolor: alpha("#1976d2", 0.08),
            }
          }}>
            <CardContent sx={{ textAlign: "center", py: 3 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ bgcolor: '#1976d2', width: 40, height: 40 }}>
                  <HotelIcon />
                </Avatar>
              </Box>
              <Typography
                variant="h4"
                sx={{ fontWeight: 700, color: "#1976d2", mb: 1 }}
              >
                {stats.withHotel}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ fontWeight: 500 }}>
                With Hotel
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={2}>
          <Card sx={{ 
            bgcolor: alpha("#1976d2", 0.04), 
            height: "100%",
            transition: 'all 0.3s ease',
            cursor: 'pointer',
            '&:hover': {
              transform: 'translateY(-4px)',
              boxShadow: 3,
              bgcolor: alpha("#1976d2", 0.08),
            }
          }}>
            <CardContent sx={{ textAlign: "center", py: 3 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ bgcolor: '#1976d2', width: 40, height: 40 }}>
                  <LocalTaxiIcon />
                </Avatar>
              </Box>
              <Typography
                variant="h4"
                sx={{ fontWeight: 700, color: "#1976d2", mb: 1 }}
              >
                {stats.withPickup}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ fontWeight: 500 }}>
                With Pickup
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={2}>
          <Card sx={{ 
            bgcolor: alpha('#1976d2', 0.04), 
            height: '100%',
            transition: 'all 0.3s ease',
            cursor: 'pointer',
            '&:hover': {
              transform: 'translateY(-4px)',
              boxShadow: 3,
              bgcolor: alpha('#1976d2', 0.08),
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 3 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ bgcolor: '#1976d2', width: 40, height: 40 }}>
                  <DirectionsBoatIcon />
                </Avatar>
              </Box>
              <Typography variant="h4" sx={{ fontWeight: 700, color: '#1976d2', mb: 1 }}>
                {stats.withPort}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ fontWeight: 500 }}>
                With Port
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={2}>
          <Card sx={{ 
            bgcolor: alpha('#1976d2', 0.04), 
            height: '100%',
            transition: 'all 0.3s ease',
            cursor: 'pointer',
            '&:hover': {
              transform: 'translateY(-4px)',
              boxShadow: 3,
              bgcolor: alpha('#1976d2', 0.08),
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 3 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ bgcolor: '#1976d2', width: 40, height: 40 }}>
                  <AttractionsIcon />
                </Avatar>
              </Box>
              <Typography variant="h4" sx={{ fontWeight: 700, color: '#1976d2', mb: 1 }}>
                {stats.withAttractions}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ fontWeight: 500 }}>
                With Attractions
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={2}>
          <Card sx={{ 
            bgcolor: alpha('#1976d2', 0.04), 
            height: '100%',
            transition: 'all 0.3s ease',
            cursor: 'pointer',
            '&:hover': {
              transform: 'translateY(-4px)',
              boxShadow: 3,
              bgcolor: alpha('#1976d2', 0.08),
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 3 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ bgcolor: '#1976d2', width: 40, height: 40 }}>
                <ParkIcon />

                </Avatar>
              </Box>
              <Typography variant="h4" sx={{ fontWeight: 700, color: '#1976d2', mb: 1 }}>
                {stats.withPackagedAttractions}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ fontWeight: 500 }}>
                With Packaged Attractions
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={2}>
          <Card sx={{ 
            bgcolor: alpha('#1976d2', 0.04), 
            height: '100%',
            transition: 'all 0.3s ease',
            cursor: 'pointer',
            '&:hover': {
              transform: 'translateY(-4px)',
              boxShadow: 3,
              bgcolor: alpha('#1976d2', 0.08),
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 3 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ bgcolor: '#1976d2', width: 40, height: 40 }}>
                  <RestaurantIcon />
                </Avatar>
              </Box>
              <Typography variant="h4" sx={{ fontWeight: 700, color: '#1976d2', mb: 1 }}>
                {stats.withRestaurants}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ fontWeight: 500 }}>
                With Restaurants
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={2}>
          <Card sx={{ 
            bgcolor: alpha("#1976d2", 0.04), 
            height: "100%",
            transition: 'all 0.3s ease',
            cursor: 'pointer',
            '&:hover': {
              transform: 'translateY(-4px)',
              boxShadow: 3,
              bgcolor: alpha("#1976d2", 0.08),
            }
          }}>
            <CardContent sx={{ textAlign: "center", py: 3 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ bgcolor: '#1976d2', width: 40, height: 40 }}>
                  <PersonIcon />
                </Avatar>
              </Box>
              <Typography
                variant="h4"
                sx={{ fontWeight: 700, color: "#1976d2", mb: 1 }}
              >
                {stats.withGuides}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ fontWeight: 500 }}>
                With Guides
              </Typography>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Enhanced Filters Section */}
      <Paper 
        sx={{ 
          p: 3, 
          mb: 3, 
          borderRadius: 2, 
          bgcolor: alpha('#1976d2', 0.02),
          border: `1px solid ${alpha('#1976d2', 0.1)}`
        }}
      >
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
          <Typography variant="h6" sx={{ fontWeight: 600, color: '#1976d2' }}>
            Search & Filter Enquiries
          </Typography>
          <Button
            variant="outlined"
            color="primary"
            startIcon={<RefreshIcon />}
            onClick={handleRefresh}
            disabled={isRefreshing || status === 'loading'}
            sx={{
              borderRadius: 2,
              textTransform: 'none',
              '&:hover': {
                backgroundColor: alpha('#1976d2', 0.08),
              }
            }}
          >
            {isRefreshing ? 'Refreshing...' : 'Refresh'}
          </Button>
        </Box>
        <Grid container spacing={3}>
          <Grid item xs={12} sm={6} md={3}>
            <TextField
              name="searchId"
              label="🔍 Search by Booking ID"
              variant="outlined"
              fullWidth
              value={filters.searchId}
              onChange={handleFilterChange}
              size="small"
              sx={{
                '& .MuiOutlinedInput-root': {
                  '&:hover fieldset': {
                    borderColor: '#1976d2',
                  },
                },
              }}
              placeholder="Enter booking ID..."
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
              size="small"
              sx={{
                '& .MuiOutlinedInput-root': {
                  '&:hover fieldset': {
                    borderColor: '#1976d2',
                  },
                },
              }}
            />
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <TextField
              name="city"
              label="🏙️ Filter by City"
              variant="outlined"
              fullWidth
              value={filters.city}
              onChange={handleFilterChange}
              size="small"
              sx={{
                '& .MuiOutlinedInput-root': {
                  '&:hover fieldset': {
                    borderColor: '#1976d2',
                  },
                },
              }}
            />
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <FormControl fullWidth size="small">
              <InputLabel>📅 Date Range</InputLabel>
              <Select
                name="dateRange"
                value={filters.dateRange}
                label="📅 Date Range"
                onChange={handleFilterChange}
                sx={{
                  '&:hover .MuiOutlinedInput-notchedOutline': {
                    borderColor: '#1976d2',
                  },
                }}
              >
                <MenuItem value="all">All Dates</MenuItem>
                <MenuItem value="upcoming">Upcoming</MenuItem>
                <MenuItem value="past">Past</MenuItem>
              </Select>
            </FormControl>
          </Grid>
        </Grid>
        
        {/* Search Results Info */}
        {(filters.searchId || filters.country || filters.city || filters.dateRange !== 'all') && (
          <Box sx={{ mt: 2, p: 2, bgcolor: alpha('#1976d2', 0.05), borderRadius: 1 }}>
            <Typography variant="body2" color="primary">
              📊 Showing {filteredEnquiries.length} of {enquiries.length} enquiries
              {filters.searchId && ` • Searching for: "${filters.searchId}"`}
              {filters.country && ` • Country: ${filters.country}`}
              {filters.city && ` • City: ${filters.city}`}
              {filters.dateRange !== 'all' && ` • Date: ${filters.dateRange}`}
            </Typography>
          </Box>
        )}
      </Paper>

      <Paper sx={{ 
        width: "100%", 
        overflow: "hidden", 
        borderRadius: 3, 
        mb: 2,
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        border: `1px solid ${alpha('#1976d2', 0.1)}`,
        position: 'relative'
      }}>
        {/* Loading overlay for refresh */}
        {status === 'loading' && enquiries.length > 0 && (
          <Box
            sx={{
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              bgcolor: 'rgba(255, 255, 255, 0.7)',
              display: 'flex',
              justifyContent: 'center',
              alignItems: 'center',
              zIndex: 1,
              borderRadius: 3,
            }}
          >
            <CircularProgress size={24} />
          </Box>
        )}
        <TableContainer sx={{ maxHeight: 600 }}>
          <Table stickyHeader aria-label="enquiries table">
            <TableHead>
              <TableRow>
                <SortableTableCell id="display_id" label="ID" />
                <SortableTableCell id="location" label="Location" />
                <SortableTableCell id="dates" label="Dates" />
                <SortableTableCell id="travelers" label="Travelers" />
                <SortableTableCell id="price" label="Approx Price" />
                <SortableTableCell id="services" label="Services" />
                <SortableTableCell id="created_at" label="Created" />
                {(userRole === "Sales Head(DMC)" ||
                  userRole === "Sales Manager (DMC)" ||
                  userRole === "Assistant Manager (DMC)") && (
                  <SortableTableCell
                    id="actions"
                    label="Actions"
                    disableSort={true}
                  />
                )}
              </TableRow>
            </TableHead>
            <TableBody>
              {paginatedEnquiries.length > 0 ? (
                paginatedEnquiries.map((enquiry) => (
                  <TableRow hover key={enquiry.id} >
                    <TableCell>
                      <Typography
                        variant="body2"
                        sx={{ fontWeight: 700, color: "#1976d2" }}
                      >
                        {enquiry.display_id}
                     {enquiry.multi_enq_id && ( 
                      <Chip
                        label={enquiry.multi_enq_id}
                        color="primary"
                        variant="filled"
                      
                        size="small"
                      
                     />
                     )}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Box sx={{ display: "flex", alignItems: "center" }}>
                        <LocationIcon
                          fontSize="small"
                          color="action"
                          sx={{ mr: 1 }}
                        />
                        <Box>
                          <Typography variant="body2">
                            {enquiry.city}
                          </Typography>
                          <Typography variant="caption" color="textSecondary">
                            {enquiry.country}
                          </Typography>
                        </Box>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Box sx={{ display: "flex", alignItems: "center" }}>
                        <EventIcon
                          fontSize="small"
                          color="action"
                          sx={{ mr: 1 }}
                        />
                        <Box>
                          <Typography variant="body2">
                            {formatDate(enquiry.check_in_time)}
                          </Typography>
                          <Typography variant="caption" color="textSecondary">
                            to {formatDate(enquiry.check_out_time)}
                          </Typography>
                        </Box>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Box sx={{ display: "flex", alignItems: "center" }}>
                        <PeopleIcon
                          fontSize="small"
                          color="action"
                          sx={{ mr: 1 }}
                        />
                        <Box>
                          <Typography variant="body2">
                            {enquiry.adult + enquiry.child + enquiry.infant}{" "}
                            Total
                          </Typography>
                          <Typography variant="caption" color="textSecondary">
                            {enquiry.adult} Adult
                            {enquiry.adult !== 1 ? "s" : ""},
                            {enquiry.child > 0
                              ? ` ${enquiry.child} Child${
                                  enquiry.child !== 1 ? "ren" : ""
                                }`
                              : ""}
                            {enquiry.infant > 0
                              ? `, ${enquiry.infant} Infant${
                                  enquiry.infant !== 1 ? "s" : ""
                                }`
                              : ""}
                          </Typography>
                        </Box>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Box sx={{ display: "flex", alignItems: "center" }}>
                        <Typography variant="body2" sx={{ fontWeight: 600, color: '#2e7d32' }}>
                          {enquiry.approx_price ? `$${parseFloat(enquiry.approx_price).toLocaleString()}` : 'N/A'}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Box sx={{ display: "flex", gap: 1, flexWrap: "wrap" }}>
                        <Tooltip
                          title={
                            enquiry.hotel
                              ? "View Hotel Details"
                              : "Hotel Not Selected"
                          }
                        >
                          <span>
                            <IconButton
                              size="small"
                              color={enquiry.hotel ? "primary" : "default"}
                              disabled={!enquiry.hotel}
                              onClick={() =>
                                enquiry.hotel &&
                                handleServiceClick(enquiry, "hotel")
                              }
                              sx={{
                                backgroundColor: enquiry.hotel
                                  ? alpha("#1976d2", 0.1)
                                  : "transparent",
                                "&:hover": {
                                  backgroundColor: enquiry.hotel
                                    ? alpha("#1976d2", 0.2)
                                    : "transparent",
                                },
                              }}
                            >
                              <HotelIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.local_transfer ? "View Pickup Details" : "Pickup Not Selected"}>
                          <span>
                            <IconButton 
                              size="small" 
                              color={enquiry.local_transfer ? "primary" : "default"}
                              disabled={!enquiry.local_transfer}
                              onClick={() => enquiry.local_transfer && handleServiceClick(enquiry, 'local_transfer')}
                              sx={{ 
                                backgroundColor: enquiry.local_transfer ? alpha('#1976d2', 0.1) : 'transparent',
                                '&:hover': {
                                  backgroundColor: enquiry.local_transfer ? alpha('#1976d2', 0.2) : 'transparent',
                                }
                              }}
                            >
                              <LocalTaxiIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.port ? "View Port Details" : "Port Not Selected"}>
                          <span>
                            <IconButton 
                              size="small" 
                              color={enquiry.port ? "primary" : "default"}
                              disabled={!enquiry.port}
                              onClick={() => enquiry.port && handleServiceClick(enquiry, 'port')}
                              sx={{ 
                                backgroundColor: enquiry.port ? alpha('#1976d2', 0.1) : 'transparent',
                                '&:hover': {
                                  backgroundColor: enquiry.port ? alpha('#1976d2', 0.2) : 'transparent',
                                }
                              }}
                            >
                              <DirectionsBoatIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.attraction ? "View Attraction Details" : "Attractions Not Selected"}>
                          <span>
                            <IconButton 
                              size="small" 
                              color={enquiry.attraction ? "primary" : "default"}
                              disabled={!enquiry.attraction}
                              onClick={() =>
                                enquiry.attraction &&
                                handleServiceClick(enquiry, "attraction")
                              }
                              sx={{
                                backgroundColor: enquiry.attraction
                                  ? alpha("#1976d2", 0.1)
                                  : "transparent",
                                "&:hover": {
                                  backgroundColor: enquiry.attraction
                                    ? alpha("#1976d2", 0.2)
                                    : "transparent",
                                },
                              }}
                            >
                              <AttractionsIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.packaged_attractions ? "View Packaged Attractions Details" : "Packaged Attractions Not Selected"}>
                          <span>
                            <IconButton 
                              size="small" 
                              color={enquiry.packaged_attractions ? "primary" : "default"}
                              disabled={!enquiry.packaged_attractions}
                              onClick={() =>
                                enquiry.packaged_attractions &&
                                handleServiceClick(enquiry, "packaged_attractions")
                              }
                              sx={{
                                backgroundColor: enquiry.packaged_attractions
                                  ? alpha("#1976d2", 0.1)
                                  : "transparent",
                                "&:hover": {
                                  backgroundColor: enquiry.packaged_attractions
                                    ? alpha("#1976d2", 0.2)
                                    : "transparent",
                                },
                              }}
                            >
                              <ParkIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip
                          title={
                            enquiry.restaurant
                              ? "View Restaurant Details"
                              : "Restaurants Not Selected"
                          }
                        >
                          <span>
                            <IconButton
                              size="small"
                              color={enquiry.restaurant ? "primary" : "default"}
                              disabled={!enquiry.restaurant}
                              onClick={() =>
                                enquiry.restaurant &&
                                handleServiceClick(enquiry, "restaurant")
                              }
                              sx={{
                                backgroundColor: enquiry.restaurant
                                  ? alpha("#1976d2", 0.1)
                                  : "transparent",
                                "&:hover": {
                                  backgroundColor: enquiry.restaurant
                                    ? alpha("#1976d2", 0.2)
                                    : "transparent",
                                },
                              }}
                            >
                              <RestaurantIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip
                          title={
                            enquiry.guide
                              ? "View Guide Details"
                              : "Guide Not Selected"
                          }
                        >
                          <span>
                            <IconButton
                              size="small"
                              color={enquiry.guide ? "primary" : "default"}
                              disabled={!enquiry.guide}
                              onClick={() =>
                                enquiry.guide &&
                                handleServiceClick(enquiry, "guide")
                              }
                              sx={{
                                backgroundColor: enquiry.guide
                                  ? alpha("#1976d2", 0.1)
                                  : "transparent",
                                "&:hover": {
                                  backgroundColor: enquiry.guide
                                    ? alpha("#1976d2", 0.2)
                                    : "transparent",
                                },
                              }}
                            >
                              <PersonIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2">
                        {formatDate(enquiry.created_at)}
                      </Typography>
                    </TableCell>
                    {(userRole === "Sales Head(DMC)" ||
                      userRole === "Sales Manager (DMC)" ||
                      userRole === "Assistant Manager (DMC)") && (
                      <TableCell>
                        <Button
                          size="medium"
                          variant="contained"
                          color="primary"
                          onClick={() => handleConvert(enquiry)}
                        >
                          Create Tour
                        </Button>
                      </TableCell>
                    )}
                  </TableRow>
                ))
              ) : (
                <TableRow>
                  <TableCell colSpan={7} align="center" sx={{ py: 3 }}>
                    <Typography variant="body1" color="textSecondary">
                      No enquiries found
                    </Typography>
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </TableContainer>
        <TablePagination
          rowsPerPageOptions={[10, 25, 50, 100]}
          component="div"
          count={filteredEnquiries.length}
          rowsPerPage={rowsPerPage}
          page={page}
          onPageChange={handleChangePage}
          onRowsPerPageChange={handleChangeRowsPerPage}
        />
      </Paper>

      {/* Service Details Modal */}
      <Modal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        aria-labelledby="service-details-modal"
      >
        <Box sx={modalStyle}>
          {/* Modal Header */}
          <Box
            sx={{
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
              mb: 2,
            }}
          >
            <Typography variant="h6" component="h2" fontWeight={600}>
              {selectedService === 'hotel' && 'Hotel Details'}
              {selectedService === 'attraction' && 'Attraction Details'}
              {selectedService === 'restaurant' && 'Restaurant Details'}
              {selectedService === 'guide' && 'Guide Details'}
              {selectedService === 'local_transfer' && 'Local Transfer Details'}
              {selectedService === 'port' && 'Port Details'}
            </Typography>
            <IconButton onClick={() => setModalOpen(false)}>
              <CloseIcon />
            </IconButton>
          </Box>

          {/* Modal Content */}
          <Box>
            {/* Tabs for Overview and Details */}
            <Tabs
              value={tabValue}
              onChange={handleTabChange}
              sx={{ borderBottom: 1, borderColor: "divider", mb: 2 }}
            >
              <Tab label="Overview" />
              <Tab label="Details" />
            </Tabs>

            {/* Conditional Content Based on Selected Service */}
            {selectedEnquiry &&
              selectedService === "hotel" &&
              <RenderHotelDetails details={selectedEnquiry.hotel_details} tabValue={tabValue} />}

            {selectedEnquiry &&
              selectedService === "attraction" &&
              <RenderAttractionDetails details={selectedEnquiry.attraction_details} tabValue={tabValue} />}

            {selectedEnquiry &&
              selectedService === "packaged_attractions" &&
              <RenderPackagedAttractionsDetails details={selectedEnquiry.packaged_attraction_details} tabValue={tabValue} />}

            {selectedEnquiry &&
              selectedService === "restaurant" &&
              <RenderRestaurantDetails details={selectedEnquiry.restaurant_details} tabValue={tabValue} />}

            {selectedEnquiry &&
              selectedService === "guide" &&
              <RenderGuideDetails details={selectedEnquiry.guide_details} tabValue={tabValue} />}
              
            {selectedEnquiry && selectedService === 'local_transfer' && 
              <RenderLocalTransferDetails details={selectedEnquiry.local_transfer_details} tabValue={tabValue} />}
            
            {selectedEnquiry && selectedService === 'port' && 
              <RenderPortDetails enquiry={selectedEnquiry} tabValue={tabValue} />}
          </Box>
        </Box>
      </Modal>

      {/* New Enquiry Alert */}
      <Snackbar
        open={showNewEnquiryAlert}
        autoHideDuration={5000}
        onClose={() => setShowNewEnquiryAlert(false)}
        anchorOrigin={{ vertical: 'top', horizontal: 'right' }}
      >
        <Alert 
          onClose={() => setShowNewEnquiryAlert(false)} 
          severity="success" 
          sx={{ width: '100%' }}
        >
          🎉 New enquiry received! The list has been updated.
        </Alert>
      </Snackbar>
    </Box>
  );
};

export default EnquiryList; 