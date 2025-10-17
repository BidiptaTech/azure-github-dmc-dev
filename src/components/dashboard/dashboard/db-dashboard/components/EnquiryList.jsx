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
  width: { xs: "95%", sm: "90%", md: "80%" },
  maxWidth: { xs: 400, sm: 600, md: 1000 },
  maxHeight: { xs: "85vh", sm: "90vh" },
  bgcolor: "background.paper",
  boxShadow: 24,
  borderRadius: { xs: 1.5, sm: 2 },
  p: { xs: 2, sm: 3, md: 4 },
  overflow: "auto",
};

const EnquiryList = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { enquiries, status, error, stats } = useSelector(
    (state) => state.bookingEnquiry
  );
  const { userRole } = useSelector((state) => state.auth);
  const selectedAgent = useSelector((state) => state.editing.agentId);
  // console.log(enquiries, "enq");

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
      // Use agent ID from Redux if available
      if (selectedAgent) {
        await dispatch(fetchEnquiries({ 
          agentId: selectedAgent,
          reset: true, 
          start: 0, 
          limit: 30 
        })).unwrap();
      } else {
        await dispatch(fetchEnquiries({ 
          reset: true, 
          start: 0, 
          limit: 30 
        })).unwrap();
      }
    } catch (error) {
      console.error('Failed to refresh enquiries:', error);
    } finally {
      setIsRefreshing(false);
    }
  };
useEffect(() => {
  handleRefresh();
}, []);
  // Initial data fetch
  // useEffect(() => {
  //   if (status === "idle") {
  //     dispatch(fetchEnquiries({ reset: true, start: 0, limit: 30 }));
  //   }
  // }, [status, dispatch]);

  // Add a separate effect to refresh enquiries periodically and on component mount
  // useEffect(() => {
  //   // Fetch enquiries on component mount
  //   if (selectedAgent) {
  //     dispatch(fetchEnquiries({ agentId: selectedAgent, reset: true, start: 0, limit: 30 }));
  //   } else {
  //     dispatch(fetchEnquiries({ reset: true, start: 0, limit: 30 }));
  //   }
    
  //   // // Set up periodic refresh every 30 seconds
  //   // const interval = setInterval(() => {
  //   //   if (selectedAgent) {
  //   //     dispatch(fetchEnquiries({ agentId: selectedAgent, reset: false, start: 0, limit: 30 }));
  //   //   } else {
  //   //     dispatch(fetchEnquiries({ reset: false, start: 0, limit: 30 }));
  //   //   }
  //   // }, 30000); // 30 seconds
    
  //   // // Cleanup interval on component unmount
  //   // return () => clearInterval(interval);
  // }, [dispatch, selectedAgent]);

  // Effect to detect new enquiries
  // useEffect(() => {
  //   if (enquiries.length > 0 && lastEnquiryCount > 0 && enquiries.length > lastEnquiryCount) {
  //     setShowNewEnquiryAlert(true);
  //     // Auto-hide the alert after 5 seconds
  //     setTimeout(() => setShowNewEnquiryAlert(false), 5000);
  //   }
  //   setLastEnquiryCount(enquiries.length);
  // }, [enquiries.length, lastEnquiryCount]);



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
    // Filter by booking ID (display_id) or multi_enq_id
    if (filters.searchId) {
      const searchTerm = filters.searchId.toLowerCase();
      const displayId = enquiry.display_id?.toLowerCase() || '';
      const multiEnqId = enquiry.multi_enq_id?.toLowerCase() || '';
      
      // Check if search term matches either display_id or multi_enq_id
      if (!displayId.includes(searchTerm) && !multiEnqId.includes(searchTerm)) {
        return false;
      }
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

    if (orderBy === "guest") {
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

  const totalPages = Math.ceil(sortedEnquiries.length / rowsPerPage);
  
  // Auto-fetch more data when reaching the last page
  useEffect(() => {
    if (page > 0 && sortedEnquiries.length > 0) {
      const currentPageEnd = (page + 1) * rowsPerPage;
      const dataAvailable = sortedEnquiries.length;
      
      // If we're on the last page or near the end of available data, fetch more
      if (page + 1 === totalPages || currentPageEnd >= dataAvailable - rowsPerPage) {
        const nextStart = Math.ceil(dataAvailable / 30) * 30; // Align to the next 30-item chunk
        
        // console.log('Auto-fetching more data in EnquiryList:', {
        //   currentPage: page + 1,
        //   totalPages,
        //   dataAvailable,
        //   nextStart
        // });
        
        if (selectedAgent) {
          dispatch(fetchEnquiries({ 
            agentId: selectedAgent,
            start: nextStart, 
            limit: 30, 
            reset: false 
          }));
        } else {
          dispatch(fetchEnquiries({ start: nextStart, limit: 30, reset: false }));
        }
      }
    }
  }, [page, sortedEnquiries.length, rowsPerPage, dispatch, totalPages, selectedAgent]);

  const paginatedEnquiries = sortedEnquiries.slice(
    page * rowsPerPage,
    page * rowsPerPage + rowsPerPage
  );

  // Helper to format date
  const formatDate = (dateString) => {
    return dayjs(dateString).format("MMM DD, YYYY HH:mm");
  };
  const formatDate1 = (dateString) => {
    return dayjs(dateString).format("DD-MM-YYYY");
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
    <Box sx={{ 
      width: "100%",
      // Global scrollbar styling for consistency
      '& *::-webkit-scrollbar': {
        width: '8px',
        height: '8px',
      },
      '& *::-webkit-scrollbar-track': {
        background: alpha('#667eea', 0.1),
        borderRadius: '10px',
      },
      '& *::-webkit-scrollbar-thumb': {
        background: 'linear-gradient(45deg, #667eea, #764ba2)',
        borderRadius: '10px',
        '&:hover': {
          background: 'linear-gradient(45deg, #764ba2, #667eea)',
        },
      },
    }}>
      {/* Compact Enhanced Stats Cards */}
      <Grid container spacing={{ xs: 1, sm: 2 }} sx={{ mb: { xs: 2, sm: 3, md: 4 } }}>
        <Grid item xs={6} sm={4} md={1.5}>
          <Card sx={{ 
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            height: { xs: 100, sm: 110, md: 120 },
            transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            cursor: 'pointer',
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflow: 'hidden',
            position: 'relative',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
              opacity: 0,
              transition: 'opacity 0.3s ease',
            },
            '&:hover': {
              transform: 'translateY(-6px) scale(1.02)',
              boxShadow: '0 15px 30px rgba(102, 126, 234, 0.4)',
              '&::before': {
                opacity: 1,
              }
            }
          }}>
            <CardContent sx={{ 
              textAlign: "center", 
              py: { xs: 1, sm: 1.25, md: 1.5 }, 
              px: { xs: 0.5, sm: 0.75, md: 1 }, 
              position: 'relative', 
              zIndex: 1 
            }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: { xs: 0.5, sm: 0.75, md: 1 } }}>
                <Avatar sx={{ 
                  bgcolor: 'rgba(255,255,255,0.2)', 
                  width: { xs: 28, sm: 32, md: 35 }, 
                  height: { xs: 28, sm: 32, md: 35 },
                  backdropFilter: 'blur(10px)',
                  border: '2px solid rgba(255,255,255,0.3)'
                }}>
                  <EventIcon sx={{ color: 'white', fontSize: { xs: 16, sm: 18, md: 20 } }} />
                </Avatar>
              </Box>
              <Typography
                variant="h5"
                sx={{ 
                  fontWeight: 800, 
                  color: "white", 
                  mb: { xs: 0.25, sm: 0.5 }, 
                  textShadow: '0 2px 4px rgba(0,0,0,0.3)', 
                  lineHeight: 1,
                  fontSize: { xs: '1.1rem', sm: '1.25rem', md: '1.5rem' }
                }}
              >
                {stats.total}
              </Typography>
              <Typography variant="caption" sx={{ 
                color: 'rgba(255,255,255,0.9)', 
                fontWeight: 600, 
                fontSize: { xs: '0.6rem', sm: '0.65rem', md: '0.7rem' }, 
                lineHeight: 1.2 
              }}>
                📊 Total Enquiries
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={6} sm={4} md={1.5}>
          <Card sx={{ 
            background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
            height: { xs: 100, sm: 110, md: 120 },
            transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            cursor: 'pointer',
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflow: 'hidden',
            position: 'relative',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
              opacity: 0,
              transition: 'opacity 0.3s ease',
            },
            '&:hover': {
              transform: 'translateY(-6px) scale(1.02)',
              boxShadow: '0 15px 30px rgba(255, 107, 107, 0.4)',
              '&::before': {
                opacity: 1,
              }
            }
          }}>
            <CardContent sx={{ textAlign: "center", py: 1.5, px: 1, position: 'relative', zIndex: 1 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ 
                  bgcolor: 'rgba(255,255,255,0.2)', 
                  width: 35, 
                  height: 35,
                  backdropFilter: 'blur(10px)',
                  border: '2px solid rgba(255,255,255,0.3)'
                }}>
                  <HotelIcon sx={{ color: 'white', fontSize: 20 }} />
                </Avatar>
              </Box>
              <Typography
                variant="h5"
                sx={{ fontWeight: 800, color: "white", mb: 0.5, textShadow: '0 2px 4px rgba(0,0,0,0.3)', lineHeight: 1 }}
              >
                {stats.withHotel}
              </Typography>
              <Typography variant="caption" sx={{ color: 'rgba(255,255,255,0.9)', fontWeight: 600, fontSize: '0.7rem', lineHeight: 1.2 }}>
                🏨 With Hotel
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={6} sm={4} md={1.5}>
          <Card sx={{ 
            background: 'linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%)',
            height: { xs: 100, sm: 110, md: 120 },
            transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            cursor: 'pointer',
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflow: 'hidden',
            position: 'relative',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
              opacity: 0,
              transition: 'opacity 0.3s ease',
            },
            '&:hover': {
              transform: 'translateY(-6px) scale(1.02)',
              boxShadow: '0 15px 30px rgba(253, 203, 110, 0.4)',
              '&::before': {
                opacity: 1,
              }
            }
          }}>
            <CardContent sx={{ textAlign: "center", py: 1.5, px: 1, position: 'relative', zIndex: 1 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ 
                  bgcolor: 'rgba(255,255,255,0.2)', 
                  width: 35, 
                  height: 35,
                  backdropFilter: 'blur(10px)',
                  border: '2px solid rgba(255,255,255,0.3)'
                }}>
                  <LocalTaxiIcon sx={{ color: '#d63031', fontSize: 20 }} />
                </Avatar>
              </Box>
              <Typography
                variant="h5"
                sx={{ fontWeight: 800, color: "#d63031", mb: 0.5, textShadow: '0 2px 4px rgba(0,0,0,0.1)', lineHeight: 1 }}
              >
                {stats.withPickup}
              </Typography>
              <Typography variant="caption" sx={{ color: '#d63031', fontWeight: 600, fontSize: '0.7rem', lineHeight: 1.2 }}>
                🚕 With Pickup
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={6} sm={4} md={1.5}>
          <Card sx={{ 
            background: 'linear-gradient(135deg, #74b9ff 0%, #0984e3 100%)',
            height: { xs: 100, sm: 110, md: 120 },
            transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            cursor: 'pointer',
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflow: 'hidden',
            position: 'relative',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
              opacity: 0,
              transition: 'opacity 0.3s ease',
            },
            '&:hover': {
              transform: 'translateY(-6px) scale(1.02)',
              boxShadow: '0 15px 30px rgba(116, 185, 255, 0.4)',
              '&::before': {
                opacity: 1,
              }
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 1.5, px: 1, position: 'relative', zIndex: 1 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ 
                  bgcolor: 'rgba(255,255,255,0.2)', 
                  width: 35, 
                  height: 35,
                  backdropFilter: 'blur(10px)',
                  border: '2px solid rgba(255,255,255,0.3)'
                }}>
                  <DirectionsBoatIcon sx={{ color: 'white', fontSize: 20 }} />
                </Avatar>
              </Box>
              <Typography variant="h5" sx={{ fontWeight: 800, color: 'white', mb: 0.5, textShadow: '0 2px 4px rgba(0,0,0,0.3)', lineHeight: 1 }}>
                {stats.withPort}
              </Typography>
              <Typography variant="caption" sx={{ color: 'rgba(255,255,255,0.9)', fontWeight: 600, fontSize: '0.7rem', lineHeight: 1.2 }}>
                ⛵ With Port
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={6} sm={4} md={1.5}>
          <Card sx={{ 
            background: 'linear-gradient(135deg, #fd79a8 0%, #e84393 100%)',
            height: { xs: 100, sm: 110, md: 120 },
            transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            cursor: 'pointer',
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflow: 'hidden',
            position: 'relative',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
              opacity: 0,
              transition: 'opacity 0.3s ease',
            },
            '&:hover': {
              transform: 'translateY(-6px) scale(1.02)',
              boxShadow: '0 15px 30px rgba(253, 121, 168, 0.4)',
              '&::before': {
                opacity: 1,
              }
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 1.5, px: 1, position: 'relative', zIndex: 1 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ 
                  bgcolor: 'rgba(255,255,255,0.2)', 
                  width: 35, 
                  height: 35,
                  backdropFilter: 'blur(10px)',
                  border: '2px solid rgba(255,255,255,0.3)'
                }}>
                  <AttractionsIcon sx={{ color: 'white', fontSize: 20 }} />
                </Avatar>
              </Box>
              <Typography variant="h5" sx={{ fontWeight: 800, color: 'white', mb: 0.5, textShadow: '0 2px 4px rgba(0,0,0,0.3)', lineHeight: 1 }}>
                {stats.withAttractions}
              </Typography>
              <Typography variant="caption" sx={{ color: 'rgba(255,255,255,0.9)', fontWeight: 600, fontSize: '0.7rem', lineHeight: 1.2 }}>
                🎡 Attractions
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={6} sm={4} md={1.5}>
          <Card sx={{ 
            background: 'linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%)',
            height: { xs: 100, sm: 110, md: 120 },
            transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            cursor: 'pointer',
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflow: 'hidden',
            position: 'relative',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
              opacity: 0,
              transition: 'opacity 0.3s ease',
            },
            '&:hover': {
              transform: 'translateY(-6px) scale(1.02)',
              boxShadow: '0 15px 30px rgba(162, 155, 254, 0.4)',
              '&::before': {
                opacity: 1,
              }
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 1.5, px: 1, position: 'relative', zIndex: 1 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ 
                  bgcolor: 'rgba(255,255,255,0.2)', 
                  width: 35, 
                  height: 35,
                  backdropFilter: 'blur(10px)',
                  border: '2px solid rgba(255,255,255,0.3)'
                }}>
                  <ParkIcon sx={{ color: 'white', fontSize: 20 }} />
                </Avatar>
              </Box>
              <Typography variant="h5" sx={{ fontWeight: 800, color: 'white', mb: 0.5, textShadow: '0 2px 4px rgba(0,0,0,0.3)', lineHeight: 1 }}>
                {stats.withPackagedAttractions}
              </Typography>
              <Typography variant="caption" sx={{ color: 'rgba(255,255,255,0.9)', fontWeight: 600, fontSize: '0.7rem', lineHeight: 1.2 }}>
                🎢 Packages
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={6} sm={4} md={1.5}>
          <Card sx={{ 
            background: 'linear-gradient(135deg, #55a3ff 0%, #003d82 100%)',
            height: { xs: 100, sm: 110, md: 120 },
            transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            cursor: 'pointer',
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflow: 'hidden',
            position: 'relative',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
              opacity: 0,
              transition: 'opacity 0.3s ease',
            },
            '&:hover': {
              transform: 'translateY(-6px) scale(1.02)',
              boxShadow: '0 15px 30px rgba(85, 163, 255, 0.4)',
              '&::before': {
                opacity: 1,
              }
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 1.5, px: 1, position: 'relative', zIndex: 1 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ 
                  bgcolor: 'rgba(255,255,255,0.2)', 
                  width: 35, 
                  height: 35,
                  backdropFilter: 'blur(10px)',
                  border: '2px solid rgba(255,255,255,0.3)'
                }}>
                  <RestaurantIcon sx={{ color: 'white', fontSize: 20 }} />
                </Avatar>
              </Box>
              <Typography variant="h5" sx={{ fontWeight: 800, color: 'white', mb: 0.5, textShadow: '0 2px 4px rgba(0,0,0,0.3)', lineHeight: 1 }}>
                {stats.withRestaurants}
              </Typography>
              <Typography variant="caption" sx={{ color: 'rgba(255,255,255,0.9)', fontWeight: 600, fontSize: '0.7rem', lineHeight: 1.2 }}>
                🍽️ Restaurants
              </Typography>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={6} sm={4} md={1.5}>
          <Card sx={{ 
            background: 'linear-gradient(135deg, #00b894 0%, #00cec9 100%)',
            height: { xs: 100, sm: 110, md: 120 },
            transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            cursor: 'pointer',
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflow: 'hidden',
            position: 'relative',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              background: 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
              opacity: 0,
              transition: 'opacity 0.3s ease',
            },
            '&:hover': {
              transform: 'translateY(-6px) scale(1.02)',
              boxShadow: '0 15px 30px rgba(0, 184, 148, 0.4)',
              '&::before': {
                opacity: 1,
              }
            }
          }}>
            <CardContent sx={{ textAlign: 'center', py: 1.5, px: 1, position: 'relative', zIndex: 1 }}>
              <Box sx={{ display: 'flex', justifyContent: 'center', mb: 1 }}>
                <Avatar sx={{ 
                  bgcolor: 'rgba(255,255,255,0.2)', 
                  width: 35, 
                  height: 35,
                  backdropFilter: 'blur(10px)',
                  border: '2px solid rgba(255,255,255,0.3)'
                }}>
                  <PersonIcon sx={{ color: 'white', fontSize: 20 }} />
                </Avatar>
              </Box>
              <Typography
                variant="h5"
                sx={{ fontWeight: 800, color: "white", mb: 0.5, textShadow: '0 2px 4px rgba(0,0,0,0.3)', lineHeight: 1 }}
              >
                {stats.withGuides}
              </Typography>
              <Typography variant="caption" sx={{ color: 'rgba(255,255,255,0.9)', fontWeight: 600, fontSize: '0.7rem', lineHeight: 1.2 }}>
                👨‍🏫 With Guides
              </Typography>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Advanced Modern Filters Section */}
      <Paper 
        elevation={0}
        sx={{ 
          p: { xs: 2, sm: 3, md: 4 }, 
          mb: { xs: 2, sm: 3, md: 4 }, 
          borderRadius: { xs: 2, sm: 3, md: 4 }, 
          background: 'linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%)',
          backdropFilter: 'blur(20px)',
          border: '1px solid rgba(255,255,255,0.3)',
          boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
          position: 'relative',
          overflow: 'hidden',
          '&::before': {
            content: '""',
            position: 'absolute',
            top: 0,
            left: 0,
            right: 0,
            height: '4px',
            background: 'linear-gradient(90deg, #667eea 0%, #764ba2 25%, #fd79a8 50%, #fdcb6e 75%, #74b9ff 100%)',
          }
        }}
      >
        <Box sx={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: { xs: 'flex-start', sm: 'center' }, 
          mb: { xs: 2, sm: 3 },
          flexDirection: { xs: 'column', sm: 'row' },
          gap: { xs: 2, sm: 0 }
        }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: { xs: 1.5, sm: 2 } }}>
            <Box sx={{
              background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
              borderRadius: '50%',
              p: { xs: 0.75, sm: 1 },
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              boxShadow: '0 4px 15px rgba(102, 126, 234, 0.3)'
            }}>
              <EventIcon sx={{ color: 'white', fontSize: { xs: 20, sm: 24 } }} />
            </Box>
            <Box>
              <Typography variant="h5" sx={{ 
                fontWeight: 800, 
                background: 'linear-gradient(45deg, #667eea, #764ba2)',
                WebkitBackgroundClip: 'text',
                WebkitTextFillColor: 'transparent',
                mb: 0.5,
                fontSize: { xs: '1.1rem', sm: '1.25rem', md: '1.5rem' }
              }}>
                🔍 Advanced Search & Filter
              </Typography>
              <Typography variant="body2" sx={{ 
                color: '#64748b', 
                fontWeight: 500,
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}>
                Find and filter enquiries with powerful search options
              </Typography>
            </Box>
          </Box>
          <Button
            variant="contained"
            startIcon={<RefreshIcon />}
            onClick={handleRefresh}
            disabled={isRefreshing || status === 'loading'}
            sx={{
              background: 'linear-gradient(45deg, #667eea, #764ba2)',
              borderRadius: { xs: 2, sm: 2.5, md: 3 },
              textTransform: 'none',
              fontWeight: 600,
              px: { xs: 2, sm: 2.5, md: 3 },
              py: { xs: 1, sm: 1.25, md: 1.5 },
              boxShadow: '0 4px 15px rgba(102, 126, 234, 0.4)',
              transition: 'all 0.3s ease',
              '&:hover': {
                background: 'linear-gradient(45deg, #764ba2, #667eea)',
                transform: 'translateY(-2px)',
                boxShadow: '0 6px 20px rgba(102, 126, 234, 0.6)',
              },
              '&:disabled': {
                background: 'linear-gradient(45deg, #e2e8f0, #cbd5e0)',
                color: '#a0aec0'
              },
              fontSize: { xs: '0.75rem', sm: '0.875rem', md: '1rem' },
              width: { xs: '100%', sm: 'auto' }
            }}
          >
            {isRefreshing ? '🔄 Refreshing...' : '⚡ Refresh Data'}
          </Button>
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
              placeholder="Enter country name..."
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
              placeholder="Enter city name..."
            />
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <FormControl fullWidth size="medium">
              <InputLabel sx={{ 
                fontWeight: 600,
                '&.Mui-focused': {
                  color: '#74b9ff',
                }
              }}>📅 Date Range</InputLabel>
              <Select
                name="dateRange"
                value={filters.dateRange}
                label="📅 Date Range"
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
                    borderColor: '#74b9ff',
                    borderWidth: 2,
                  },
                  '&.Mui-focused': {
                    background: 'rgba(255,255,255,1)',
                    boxShadow: '0 4px 20px rgba(116, 185, 255, 0.2)',
                  },
                  '&.Mui-focused .MuiOutlinedInput-notchedOutline': {
                    borderColor: '#74b9ff',
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
                          background: 'linear-gradient(45deg, #74b9ff, #0984e3)',
                          color: 'white',
                        }
                      }
                    }
                  }
                }}
              >
                <MenuItem value="all">🌐 All Dates</MenuItem>
                <MenuItem value="upcoming">⏭️ Upcoming Tours</MenuItem>
                <MenuItem value="past">📅 Past Tours</MenuItem>
              </Select>
            </FormControl>
          </Grid>
        </Grid>
      </Paper>

      <Paper sx={{ 
        width: "100%", 
        overflow: "hidden", 
        borderRadius: { xs: 2, sm: 2.5, md: 3 }, 
        mb: { xs: 1, sm: 2 },
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        border: `1px solid ${alpha('#667eea', 0.1)}`,
        position: 'relative',
        maxWidth: '100%', // Prevent overflow
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
              borderRadius: { xs: 2, sm: 2.5, md: 3 },
            }}
          >
            <CircularProgress size={24} />
          </Box>
        )}
        <TableContainer 
          sx={{ 
            maxHeight: { xs: 400, sm: 500, md: 600 },
            borderRadius: { xs: 2, sm: 2.5, md: 3 },
            overflowX: { xs: 'auto', sm: 'auto', md: 'hidden' }, // Enable horizontal scroll on mobile/tablet
            overflowY: 'auto',   // Keep vertical scrollbar
            width: '100%',
            '&::-webkit-scrollbar': {
              width: '8px',
              height: { xs: '8px', sm: '8px', md: '0px' }, // Show horizontal scrollbar on mobile/tablet
            },
            '&::-webkit-scrollbar-track': {
              background: alpha('#667eea', 0.1),
              borderRadius: '10px',
            },
            '&::-webkit-scrollbar-thumb': {
              background: 'linear-gradient(45deg, #667eea, #764ba2)',
              borderRadius: '10px',
              '&:hover': {
                background: 'linear-gradient(45deg, #764ba2, #667eea)',
              },
            },
          }}
        >
          <Table stickyHeader aria-label="enquiries table" sx={{ 
            tableLayout: 'fixed',
            width: '100%',
            minWidth: { xs: '800px', sm: '900px', md: 'unset' }, // Set minimum width for mobile/tablet scrolling
          }}>
            <TableHead>
              <TableRow sx={{ 
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
               
              }}>
                <SortableTableCell id="display_id" label="Enquiry ID" sx={{ width: { xs: '12%', sm: '10%', md: '8%' } }} />
                <SortableTableCell id="location" label="📍Location" sx={{ width: { xs: '18%', sm: '16%', md: '15%' } }} />
                <SortableTableCell id="dates" label="📅Tour Date" sx={{ width: { xs: '20%', sm: '19%', md: '18%' } }} />
                <SortableTableCell id="guest" label="👥Guest" sx={{ width: { xs: '14%', sm: '13%', md: '12%' } }} />
                <SortableTableCell id="price" label="💰Price" sx={{ width: { xs: '12%', sm: '11%', md: '10%' } }} />
                <SortableTableCell id="services" label="🏨Services" sx={{ width: { xs: '30%', sm: '28%', md: '25%' } }} />
                <SortableTableCell id="created_at" label="⏰Created" sx={{ width: { xs: '14%', sm: '13%', md: '12%' } }} />
                {(userRole === "Sales Head(DMC)" ||
                  userRole === "Sales Manager (DMC)" ||
                  userRole === "Assistant Manager (DMC)") && (
                  <SortableTableCell
                    id="actions"
                    label="⚡ Actions"
                    disableSort={true}
                    sx={{ width: { xs: '12%', sm: '11%', md: '10%' } }}
                  />
                )}
              </TableRow>
            </TableHead>
            <TableBody>
              {paginatedEnquiries.length > 0 ? (
                paginatedEnquiries.map((enquiry, index) => (
                  <TableRow 
                    hover 
                    key={enquiry.id}
                    sx={{
                      background: index % 2 === 0 
                        ? 'linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.9) 100%)'
                        : 'linear-gradient(135deg, rgba(248,250,252,0.7) 0%, rgba(241,245,249,0.7) 100%)',
                      transition: 'all 0.3s ease',
                      '&:hover': {
                        background: 'linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(147, 51, 234, 0.08) 100%)',
                        transform: 'scale(1.01)',
                        boxShadow: '0 4px 20px rgba(59, 130, 246, 0.15)',
                        '& .MuiTableCell-root': {
                          borderColor: alpha('#3b82f6', 0.2),
                        }
                      },
                      '& .MuiTableCell-root': {
                        borderBottom: `1px solid ${alpha('#e2e8f0', 0.8)}`,
                        padding: '16px 8px',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        whiteSpace: 'nowrap',
                      }
                    }}
                  >
                    <TableCell sx={{ width: { xs: '12%', sm: '10%', md: '8%' }, whiteSpace: 'normal' }}>
                      <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                        <Typography
                          variant="body2"
                          sx={{ 
                            fontWeight: 700, 
                            background: 'linear-gradient(45deg, #1976d2, #42a5f5)',
                            WebkitBackgroundClip: 'text',
                            WebkitTextFillColor: 'transparent',
                            fontSize: '0.9rem'
                          }}
                        >
                          {enquiry.display_id}
                        </Typography>
                        {enquiry.multi_enq_id && ( 
                          <Chip
                            label={enquiry.multi_enq_id}
                            size="small"
                            sx={{
                              background: 'linear-gradient(45deg, #667eea, #764ba2)',
                              color: 'white',
                              fontWeight: 600,
                              fontSize: '0.7rem',
                              height: '20px',
                              '& .MuiChip-label': {
                                padding: '0 6px',
                              }
                            }}
                          />
                        )}
                      </Box>
                    </TableCell>
                    <TableCell sx={{ width: { xs: '18%', sm: '16%', md: '15%' }, whiteSpace: 'normal' }}>
                      <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                        <Box sx={{
                          background: 'linear-gradient(45deg, #ff6b6b, #ee5a24)',
                          borderRadius: '50%',
                          p: 0.5,
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center'
                        }}>
                          <LocationIcon
                            fontSize="small"
                            sx={{ color: 'white' }}
                          />
                        </Box>
                        <Box>
                          <Typography variant="body2" sx={{ fontWeight: 600, color: '#2d3748' }}>
                            {enquiry.city}
                          </Typography>
                          <Typography variant="caption" sx={{ color: '#718096', fontWeight: 500 }}>
                            {enquiry.country}
                          </Typography>
                        </Box>
                      </Box>
                    </TableCell>
                    <TableCell sx={{ width: { xs: '20%', sm: '19%', md: '18%' }, whiteSpace: 'normal' }}>
                      <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                        <Box sx={{
                          background: 'linear-gradient(45deg, #4ecdc4, #44a08d)',
                          borderRadius: '50%',
                          p: 0.5,
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center'
                        }}>
                          <EventIcon
                            fontSize="small"
                            sx={{ color: 'white' }}
                          />
                        </Box>
                        <Box>
                          <Typography variant="body2" sx={{ fontWeight: 600, color: '#2d3748' }}>
                            {formatDate1(enquiry.check_in_time)}
                          </Typography>
                          <Typography variant="caption" sx={{ color: '#718096', fontWeight: 500 }}>
                            to {formatDate1(enquiry.check_out_time)}
                          </Typography>
                        </Box>
                      </Box>
                    </TableCell>
                    <TableCell sx={{ width: { xs: '14%', sm: '13%', md: '12%' }, whiteSpace: 'normal' }}>
                      <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                        <Box sx={{
                          background: 'linear-gradient(45deg, #a8edea, #fed6e3)',
                          borderRadius: '50%',
                          p: 0.5,
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center'
                        }}>
                          <PeopleIcon
                            fontSize="small"
                            sx={{ color: '#4a5568' }}
                          />
                        </Box>
                        <Box>
                          <Typography variant="body2" sx={{ fontWeight: 600, color: '#2d3748' }}>
                            {enquiry.adult + enquiry.child + enquiry.infant} Total
                          </Typography>
                          <Typography variant="caption" sx={{ color: '#718096', fontWeight: 500 }}>
                            {enquiry.adult}A
                            {enquiry.child > 0 ? `, ${enquiry.child}C` : ""}
                            {enquiry.infant > 0 ? `, ${enquiry.infant}I` : ""}
                          </Typography>
                        </Box>
                      </Box>
                    </TableCell>
                    <TableCell sx={{ width: { xs: '12%', sm: '11%', md: '10%' } }}>
                      <Box sx={{ 
                        display: "flex", 
                        alignItems: "center",
                        background: 'linear-gradient(45deg, rgba(46, 125, 50, 0.1), rgba(102, 187, 106, 0.1))',
                        borderRadius: 2,
                        p: 1,
                        border: '1px solid rgba(46, 125, 50, 0.2)'
                      }}>
                        <Typography 
                          variant="body2" 
                          sx={{ 
                            fontWeight: 700, 
                            background: 'linear-gradient(45deg, #2e7d32, #66bb6a)',
                            WebkitBackgroundClip: 'text',
                            WebkitTextFillColor: 'transparent',
                            fontSize: '0.9rem'
                          }}
                        >
                          {enquiry.approx_price ? `SGD ${parseFloat(enquiry.approx_price).toLocaleString()}` : 'N/A'}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell sx={{ width: { xs: '30%', sm: '28%', md: '25%' }, whiteSpace: 'normal' }}>
                      <Box sx={{ display: "flex", gap: 1, flexWrap: "wrap" }}>
                        <Tooltip
                          title={enquiry.hotel ? "🏨 View Hotel Details" : "🏨 Hotel Not Selected"}
                          placement="top"
                          arrow
                        >
                          <span>
                            <IconButton
                              size="small"
                              disabled={!enquiry.hotel}
                              onClick={() => enquiry.hotel && handleServiceClick(enquiry, "hotel")}
                              sx={{
                                background: enquiry.hotel
                                  ? 'linear-gradient(45deg, #667eea, #764ba2)'
                                  : 'linear-gradient(45deg, #e2e8f0, #cbd5e0)',
                                color: enquiry.hotel ? 'white' : '#a0aec0',
                                width: 32,
                                height: 32,
                                transition: 'all 0.3s ease',
                                boxShadow: enquiry.hotel ? '0 4px 15px rgba(102, 126, 234, 0.4)' : 'none',
                                "&:hover": {
                                  background: enquiry.hotel
                                    ? 'linear-gradient(45deg, #5a67d8, #667eea)'
                                    : 'linear-gradient(45deg, #cbd5e0, #a0aec0)',
                                  transform: enquiry.hotel ? 'scale(1.1) rotate(5deg)' : 'none',
                                  boxShadow: enquiry.hotel ? '0 6px 20px rgba(102, 126, 234, 0.6)' : 'none',
                                },
                              }}
                            >
                              <HotelIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.local_transfer ? "🚕 View Pickup Details" : "🚕 Pickup Not Selected"} placement="top" arrow>
                          <span>
                            <IconButton 
                              size="small" 
                              disabled={!enquiry.local_transfer}
                              onClick={() => enquiry.local_transfer && handleServiceClick(enquiry, 'local_transfer')}
                              sx={{ 
                                background: enquiry.local_transfer
                                  ? 'linear-gradient(45deg, #ffeaa7, #fdcb6e)'
                                  : 'linear-gradient(45deg, #e2e8f0, #cbd5e0)',
                                color: enquiry.local_transfer ? '#d63031' : '#a0aec0',
                                width: 32,
                                height: 32,
                                transition: 'all 0.3s ease',
                                boxShadow: enquiry.local_transfer ? '0 4px 15px rgba(253, 203, 110, 0.4)' : 'none',
                                '&:hover': {
                                  background: enquiry.local_transfer
                                    ? 'linear-gradient(45deg, #fdcb6e, #e17055)'
                                    : 'linear-gradient(45deg, #cbd5e0, #a0aec0)',
                                  transform: enquiry.local_transfer ? 'scale(1.1) rotate(-5deg)' : 'none',
                                  boxShadow: enquiry.local_transfer ? '0 6px 20px rgba(253, 203, 110, 0.6)' : 'none',
                                }
                              }}
                            >
                              <LocalTaxiIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.port ? "⛵ View Port Details" : "⛵ Port Not Selected"} placement="top" arrow>
                          <span>
                            <IconButton 
                              size="small" 
                              disabled={!enquiry.port}
                              onClick={() => enquiry.port && handleServiceClick(enquiry, 'port')}
                              sx={{ 
                                background: enquiry.port
                                  ? 'linear-gradient(45deg, #74b9ff, #0984e3)'
                                  : 'linear-gradient(45deg, #e2e8f0, #cbd5e0)',
                                color: enquiry.port ? 'white' : '#a0aec0',
                                width: 32,
                                height: 32,
                                transition: 'all 0.3s ease',
                                boxShadow: enquiry.port ? '0 4px 15px rgba(116, 185, 255, 0.4)' : 'none',
                                '&:hover': {
                                  background: enquiry.port
                                    ? 'linear-gradient(45deg, #0984e3, #2d3436)'
                                    : 'linear-gradient(45deg, #cbd5e0, #a0aec0)',
                                  transform: enquiry.port ? 'scale(1.1) rotate(5deg)' : 'none',
                                  boxShadow: enquiry.port ? '0 6px 20px rgba(116, 185, 255, 0.6)' : 'none',
                                }
                              }}
                            >
                              <DirectionsBoatIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.attraction ? "🎡 View Attraction Details" : "🎡 Attractions Not Selected"} placement="top" arrow>
                          <span>
                            <IconButton 
                              size="small" 
                              disabled={!enquiry.attraction}
                              onClick={() => enquiry.attraction && handleServiceClick(enquiry, "attraction")}
                              sx={{
                                background: enquiry.attraction
                                  ? 'linear-gradient(45deg, #fd79a8, #e84393)'
                                  : 'linear-gradient(45deg, #e2e8f0, #cbd5e0)',
                                color: enquiry.attraction ? 'white' : '#a0aec0',
                                width: 32,
                                height: 32,
                                transition: 'all 0.3s ease',
                                boxShadow: enquiry.attraction ? '0 4px 15px rgba(253, 121, 168, 0.4)' : 'none',
                                "&:hover": {
                                  background: enquiry.attraction
                                    ? 'linear-gradient(45deg, #e84393, #d63031)'
                                    : 'linear-gradient(45deg, #cbd5e0, #a0aec0)',
                                  transform: enquiry.attraction ? 'scale(1.1) rotate(-5deg)' : 'none',
                                  boxShadow: enquiry.attraction ? '0 6px 20px rgba(253, 121, 168, 0.6)' : 'none',
                                },
                              }}
                            >
                              <AttractionsIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.packaged_attractions ? "🎢 View Packaged Attractions Details" : "🎢 Packaged Attractions Not Selected"} placement="top" arrow>
                          <span>
                            <IconButton 
                              size="small" 
                              disabled={!enquiry.packaged_attractions}
                              onClick={() => enquiry.packaged_attractions && handleServiceClick(enquiry, "packaged_attractions")}
                              sx={{
                                background: enquiry.packaged_attractions
                                  ? 'linear-gradient(45deg, #a29bfe, #6c5ce7)'
                                  : 'linear-gradient(45deg, #e2e8f0, #cbd5e0)',
                                color: enquiry.packaged_attractions ? 'white' : '#a0aec0',
                                width: 32,
                                height: 32,
                                transition: 'all 0.3s ease',
                                boxShadow: enquiry.packaged_attractions ? '0 4px 15px rgba(162, 155, 254, 0.4)' : 'none',
                                "&:hover": {
                                  background: enquiry.packaged_attractions
                                    ? 'linear-gradient(45deg, #6c5ce7, #5f3dc4)'
                                    : 'linear-gradient(45deg, #cbd5e0, #a0aec0)',
                                  transform: enquiry.packaged_attractions ? 'scale(1.1) rotate(5deg)' : 'none',
                                  boxShadow: enquiry.packaged_attractions ? '0 6px 20px rgba(162, 155, 254, 0.6)' : 'none',
                                },
                              }}
                            >
                              <ParkIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.restaurant ? "🍽️ View Restaurant Details" : "🍽️ Restaurants Not Selected"} placement="top" arrow>
                          <span>
                            <IconButton
                              size="small"
                              disabled={!enquiry.restaurant}
                              onClick={() => enquiry.restaurant && handleServiceClick(enquiry, "restaurant")}
                              sx={{
                                background: enquiry.restaurant
                                  ? 'linear-gradient(45deg, #fd79a8, #fdcb6e)'
                                  : 'linear-gradient(45deg, #e2e8f0, #cbd5e0)',
                                color: enquiry.restaurant ? 'white' : '#a0aec0',
                                width: 32,
                                height: 32,
                                transition: 'all 0.3s ease',
                                boxShadow: enquiry.restaurant ? '0 4px 15px rgba(253, 121, 168, 0.4)' : 'none',
                                "&:hover": {
                                  background: enquiry.restaurant
                                    ? 'linear-gradient(45deg, #e17055, #d63031)'
                                    : 'linear-gradient(45deg, #cbd5e0, #a0aec0)',
                                  transform: enquiry.restaurant ? 'scale(1.1) rotate(-5deg)' : 'none',
                                  boxShadow: enquiry.restaurant ? '0 6px 20px rgba(253, 121, 168, 0.6)' : 'none',
                                },
                              }}
                            >
                              <RestaurantIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                        <Tooltip title={enquiry.guide ? "👨‍🏫 View Guide Details" : "👨‍🏫 Guide Not Selected"} placement="top" arrow>
                          <span>
                            <IconButton
                              size="small"
                              disabled={!enquiry.guide}
                              onClick={() => enquiry.guide && handleServiceClick(enquiry, "guide")}
                              sx={{
                                background: enquiry.guide
                                  ? 'linear-gradient(45deg, #00b894, #00cec9)'
                                  : 'linear-gradient(45deg, #e2e8f0, #cbd5e0)',
                                color: enquiry.guide ? 'white' : '#a0aec0',
                                width: 32,
                                height: 32,
                                transition: 'all 0.3s ease',
                                boxShadow: enquiry.guide ? '0 4px 15px rgba(0, 184, 148, 0.4)' : 'none',
                                "&:hover": {
                                  background: enquiry.guide
                                    ? 'linear-gradient(45deg, #00cec9, #55a3ff)'
                                    : 'linear-gradient(45deg, #cbd5e0, #a0aec0)',
                                  transform: enquiry.guide ? 'scale(1.1) rotate(5deg)' : 'none',
                                  boxShadow: enquiry.guide ? '0 6px 20px rgba(0, 184, 148, 0.6)' : 'none',
                                },
                              }}
                            >
                              <PersonIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                      </Box>
                    </TableCell>
                    <TableCell sx={{ width: { xs: '14%', sm: '13%', md: '12%' }, whiteSpace: 'normal' }}>
                      <Box sx={{ display: "flex", alignItems: "flex-start", gap: 0.8 }}>
                        <Box sx={{
                          background: 'linear-gradient(45deg, #636e72, #2d3436)',
                          borderRadius: '50%',
                          p: 0.5,
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          flexShrink: 0,
                          mt: 0.3
                        }}>
                          <AccessTimeIcon
                            fontSize="small"
                            sx={{ color: 'white' }}
                          />
                        </Box>
                        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.2, minWidth: 0 }}>
                          <Typography variant="body2" sx={{ fontWeight: 600, color: '#2d3748', fontSize: '0.75rem', lineHeight: 1.2 }}>
                            {dayjs(enquiry.created_at).format("DD-MM-YYYY")}
                          </Typography>
                          <Typography variant="caption" sx={{ color: '#718096', fontWeight: 500, fontSize: '0.65rem', lineHeight: 1.2 }}>
                            {dayjs(enquiry.created_at).format("HH:mm")}
                          </Typography>
                        </Box>
                      </Box>
                    </TableCell>
                    {(userRole === "Sales Head(DMC)" ||
                      userRole === "Sales Manager (DMC)" ||
                      userRole === "Assistant Manager (DMC)") && (
                      <TableCell sx={{ width: { xs: '12%', sm: '11%', md: '10%' } }}>
                        <Button
                          size="small"
                          variant="contained"
                          onClick={() => handleConvert(enquiry)}
                          sx={{
                            background: 'linear-gradient(45deg, #667eea, #764ba2)',
                            color: 'white',
                            fontWeight: 700,
                            fontSize: '0.75rem',
                            borderRadius: { xs: 2, sm: 2.5, md: 3 },
                            textTransform: 'none',
                            boxShadow: '0 4px 15px rgba(102, 126, 234, 0.4)',
                            transition: 'all 0.3s ease',
                            '&:hover': {
                              background: 'linear-gradient(45deg, #764ba2, #667eea)',
                              transform: 'translateY(-2px)',
                              boxShadow: '0 6px 20px rgba(102, 126, 234, 0.6)',
                            }
                          }}
                        >
                          ⚡ Create Tour
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
        <Box
          sx={{
            display: "flex",
            flexDirection: "column",
            justifyContent: "center",
            alignItems: "center",
            padding: { xs: "15px 0", sm: "20px 0" },
            width: "100%",
          }}
        >
          {/* Custom Pagination */}
          <div className="row y-gap-10 justify-center items-center">
            <div className="col-auto">
              <div className="row x-gap-20 y-gap-20 items-center justify-center">
                {/* Previous Page */}
                <div className="col-auto">
                  <button
                    className="button -blue-1 size-40 rounded-full border-light"
                    onClick={() => setPage(Math.max(0, page - 1))}
                    disabled={page === 0}
                    style={{ opacity: page === 0 ? 0.5 : 1 }}
                  >
                    <i className="icon-arrow-left text-14"></i>
                  </button>
                </div>

                {/* Page Numbers */}
                {(() => {
                  const pages = [];
                  let startPage = Math.max(1, page + 1 - 2);
                  let endPage = Math.min(totalPages, startPage + 4);

                  // Ensure we always show exactly 5 pages if possible
                  if (endPage - startPage < 4 && totalPages > 5) {
                    startPage = Math.max(1, endPage - 4);
                  }

                  // Add first page and ellipsis if needed
                  if (startPage > 1) {
                    pages.push(
                      <div key={1} className="col-auto">
                        <button
                          className="size-40 flex-center rounded-full cursor-pointer"
                          onClick={() => setPage(0)}
                        >
                          1
                        </button>
                      </div>
                    );
                    if (startPage > 2) {
                      pages.push(
                        <div key="ellipsis1" className="col-auto">
                          <span className="size-40 flex-center rounded-full text-light-1">...</span>
                        </div>
                      );
                    }
                  }

                  // Add middle pages
                  for (let i = startPage; i <= endPage; i++) {
                    pages.push(
                      <div key={i} className="col-auto">
                        <button
                          className={`size-40 flex-center rounded-full cursor-pointer ${
                            page + 1 === i ? "bg-dark-1 text-white" : ""
                          }`}
                          onClick={() => setPage(i - 1)}
                        >
                          {i}
                        </button>
                      </div>
                    );
                  }

                  // Add last page and ellipsis if needed
                  if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                      pages.push(
                        <div key="ellipsis2" className="col-auto">
                          <span className="size-40 flex-center rounded-full text-light-1">...</span>
                        </div>
                      );
                    }
                    pages.push(
                      <div key={totalPages} className="col-auto">
                        <button
                          className="size-40 flex-center rounded-full cursor-pointer"
                          onClick={() => setPage(totalPages - 1)}
                        >
                          {totalPages}
                        </button>
                      </div>
                    );
                  }

                  return pages;
                })()}

                {/* Next Page */}
                <div className="col-auto">
                  <button
                    className="button -blue-1 size-40 rounded-full border-light"
                    onClick={() => setPage(Math.min(totalPages - 1, page + 1))}
                    disabled={page >= totalPages - 1}
                    style={{ opacity: page >= totalPages - 1 ? 0.5 : 1 }}
                  >
                    <i className="icon-arrow-right text-14"></i>
                  </button>
                </div>
              </div>
            </div>

            {/* Page Info */}
            <div className="col-auto">
              <div className="text-14 text-light-1">
                Page {page + 1} of {totalPages} • Showing {Math.min(rowsPerPage, paginatedEnquiries.length)} of {sortedEnquiries.length} enquiries
              </div>
            </div>
          </div>
        </Box>
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
              alignItems: { xs: "flex-start", sm: "center" },
              mb: { xs: 1.5, sm: 2 },
              flexDirection: { xs: "column", sm: "row" },
              gap: { xs: 1, sm: 0 }
            }}
          >
            <Typography 
              variant="h6" 
              component="h2" 
              fontWeight={600}
              sx={{
                fontSize: { xs: '1rem', sm: '1.25rem', md: '1.5rem' },
                lineHeight: { xs: 1.2, sm: 1.3, md: 1.4 }
              }}
            >
              {selectedService === 'hotel' && 'Hotel Details'}
              {selectedService === 'attraction' && 'Attraction Details'}
              {selectedService === 'restaurant' && 'Restaurant Details'}
              {selectedService === 'guide' && 'Guide Details'}
              {selectedService === 'local_transfer' && 'Local Transfer Details'}
              {selectedService === 'port' && 'Port Details'}
            </Typography>
            <IconButton 
              onClick={() => setModalOpen(false)}
              sx={{
                alignSelf: { xs: "flex-end", sm: "center" },
                p: { xs: 0.5, sm: 1 },
                '& .MuiSvgIcon-root': {
                  fontSize: { xs: '1.2rem', sm: '1.5rem' }
                }
              }}
            >
              <CloseIcon />
            </IconButton>
          </Box>

          {/* Modal Content */}
          <Box sx={{
            maxHeight: { xs: '60vh', sm: '70vh' },
            overflowY: 'auto',
            '&::-webkit-scrollbar': {
              width: '6px',
            },
            '&::-webkit-scrollbar-track': {
              background: alpha('#667eea', 0.1),
              borderRadius: '10px',
            },
            '&::-webkit-scrollbar-thumb': {
              background: 'linear-gradient(45deg, #667eea, #764ba2)',
              borderRadius: '10px',
              '&:hover': {
                background: 'linear-gradient(45deg, #764ba2, #667eea)',
              },
            },
          }}>
            {/* Tabs for Overview and Details */}
            <Tabs
              value={tabValue}
              onChange={handleTabChange}
              sx={{ 
                borderBottom: 1, 
                borderColor: "divider", 
                mb: { xs: 1.5, sm: 2 },
                '& .MuiTab-root': {
                  fontSize: { xs: '0.875rem', sm: '1rem' },
                  minHeight: { xs: 40, sm: 48 },
                  padding: { xs: '8px 12px', sm: '12px 16px' }
                },
                '& .MuiTabs-indicator': {
                  height: { xs: 2, sm: 3 }
                }
              }}
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