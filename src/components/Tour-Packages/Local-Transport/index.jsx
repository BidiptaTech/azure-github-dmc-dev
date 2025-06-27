import React, { useState, useEffect, useRef, useCallback, useMemo } from 'react';
import { 
  Typography, 
  Card, 
  CardContent, 
  Grid, 
  Box, 
  Button, 
  Container,
  Paper,
  Stack,
  Alert,
  Tooltip,
  Chip,
  IconButton,
  Fade,
  Collapse,
  useTheme,
  alpha,
  Popover,
  Divider,
} from '@mui/material';
import VisibilityIcon from '@mui/icons-material/Visibility';
import PeopleIcon from '@mui/icons-material/People';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import ExpandLessIcon from '@mui/icons-material/ExpandLess';
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import RemoveCircleOutlineIcon from '@mui/icons-material/RemoveCircleOutline';
import AirlineSeatReclineNormalIcon from '@mui/icons-material/AirlineSeatReclineNormal';
import SearchLocationTransport from './SearchLocationTransport';
import { useSelector, useDispatch } from 'react-redux';
import { setSelectedVehicle, resetVehicles1 } from '@/slice/localtour/Localslice';
import { setAllServices } from '@/slice/tour-packages/tourPackageSlice';
import VehicleListDropdown from './vehiclelistdropdown';
import VehicleListDropdown1 from './vehiclelistdropdown1';
import VehicleListDropdownZone from './vehiclelistdropdownZone';
import TransportSummaryModal from './TransportSummaryModal';
import Passenger from './Passenger';
import DeleteIcon from '@mui/icons-material/Delete';
import LocalOfferIcon from '@mui/icons-material/LocalOffer';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CircularProgress from '@mui/material/CircularProgress';
import AddIcon from '@mui/icons-material/Add';
import BusinessCenterIcon from '@mui/icons-material/BusinessCenter';

const initialFormState = {
  vehicle: null,
  vehicleId: null,
  mode: '',
  dmcId: '',
  city: '',
  country: '',
  adults: 1,
  children: 0,
  priceMode: '',
  hours: 1,
  transportType: '',
  vehicleName: '',
  vehicleImage: '',
  vehicleModel: '',
  vehicleType: '',
  zoneId: '',
  pickupLocation: '',
  dropoffLocation: '',
  pickupTime: '',
  bookingDate: '',
  price: 0,
};

export default function LocalTransportComponent({ dayIndex = 0 }) {
  const theme = useTheme();
  const dispatch = useDispatch();
  
  // Redux selectors
  const Location = useSelector((state) => state.bookings?.searchLocation || {});
  const vehicles = useSelector(state => state.localtour.vehicles || []);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const allServices = useSelector((state) => state.tourPackages.AllServices || []);
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  
  // Location data from Redux
  const pickupLocation = useSelector(state => state.localtour.entrypickup || '');
  const dropoffLocation = useSelector(state => state.localtour.entrydropoff || '');
  const exitPickupLocation = useSelector(state => state.localtour.exitpickup || '');
  const pickupTime = useSelector(state => state.localtour.entrytime || '');
  const pickupTime1 = useSelector(state => state.localtour.entrytime1 || '');
  const pickupTimeZone = useSelector(state => state.localtour.entrytimezone || '');
  const pickupDate = useSelector(state => state.localtour.pickdate || '');
  const exitPickupDate = useSelector(state => state.localtour.exitpickupdate || '');
  
  // Component state
  const hasVehicles = vehicles && vehicles.length > 0;
  const [allBookings, setAllBookings] = useState([]);
  const [searchPerformed, setSearchPerformed] = useState({
    "Point To Point": false,
    "Hourly": false,
    "Local Transfer": false
  });
  const [cachedVehicles, setCachedVehicles] = useState({
    "Point To Point": [],
    "Hourly": [],
    "Local Transfer": []
  });
  const [openModal, setOpenModal] = useState(false);
  const [validationError, setValidationError] = useState(null);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  
  
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [expandedSections, setExpandedSections] = useState([]);
  const [bookingsVersion, setBookingsVersion] = useState(0);
  
  // Refs for tracking
  const prevBookingsRef = useRef([]);
  const prevServicesRef = useRef([]);
  const prevVehicles = useRef([]);
  const isDispatching = useRef(false);

  // Validation function
  const isBookingValid = useCallback((section) => {
    return section.vehicleId && 
           (section.adults + section.children > 0) && 
           section.priceMode && 
           (section.transportType !== "Hourly" || (section.hours && section.hours >= 1));
  }, []);

  // Redux dispatch function
  const dispatchBookingToRedux = useCallback((bookingIndex) => {
    if (isDispatching.current) return;
    
    const booking = allBookings[bookingIndex];
    
    if (!booking || !booking.vehicleId || !booking.priceMode) {
      console.error("Cannot dispatch incomplete booking to Redux", booking);
      return;
    }
    
    const bookingId = booking.id || `${booking.transportType.toLowerCase().replace(/\s+/g, '-')}-${Date.now()}`;
    const customerInfoService = allServices.find(service => service.type === 'CustomerInfo');
    
    const bookingData = {
      id: bookingId,
      vehicle_id: booking.vehicleId,
      vehicle_name: booking.vehicleName,
      vehicle_type: booking.vehicleType,
      vehicle_model: booking.vehicleModel,
      vehicle_image: booking.vehicleImage,
      city: booking.city,
      country: booking.country,
      pickup_location: booking.pickupLocation,
      dropoff_location: booking.dropoffLocation,
      booking_date: booking.bookingDate,
      pickup_time: booking.pickupTime,
      adults: booking.adults,
      children: booking.children,
      price: booking.price,
      transport_type: booking.priceMode === "Sharable" ? "shared" : "private",
      mode: booking.mode,
      dmc_id: booking.dmcId,
      ...(customerInfoService ? { 
        fullName: customerInfoService.fullName, 
        email: customerInfoService.email,
        phone: customerInfoService.phone,
        address1: customerInfoService.address1,
        address2: customerInfoService.address2,
        state: customerInfoService.state,
        zip: customerInfoService.zip,
        specialRequests: customerInfoService.specialRequests,
        countryCode: customerInfoService.countryCode
      } : {})
    };
    
    if (booking.transportType === "Hourly") {
      bookingData.hours = booking.hours;
    }
    
    if (booking.transportType === "Local Transfer" && booking.zoneId) {
      bookingData.zone_id = booking.zoneId;
    }
    
    const currentServices = [...allServices];
    const existingServiceIndex = currentServices.findIndex(service => {
      if (service.type === booking.transportType) {
        return service.data && service.data.some(item => item.id === bookingId || 
          (item.vehicle_id === booking.vehicleId));
      }
      return false;
    });
    
    if (existingServiceIndex >= 0) {
      const existingService = currentServices[existingServiceIndex];
      const existingBookingItem = existingService.data && existingService.data.find(item => 
        item.id === bookingId || (item.vehicle_id === booking.vehicleId)
      );
      
      if (existingBookingItem && 
          existingBookingItem.price === bookingData.price && 
          existingBookingItem.transport_type === bookingData.transport_type &&
          existingBookingItem.adults === bookingData.adults &&
          existingBookingItem.children === bookingData.children) {
        
        if (!booking.id) {
          setAllBookings(prevBookings => {
            const updatedBookings = [...prevBookings];
            updatedBookings[bookingIndex] = {
              ...updatedBookings[bookingIndex],
              id: bookingId
            };
            return updatedBookings;
          });
        }
        
        return {
          type: booking.transportType,
          agent_id: agentId,
          tour_id: tourId,
          data: [existingBookingItem]
        };
      }
    }
    
    const filteredServices = currentServices.filter(service => {
      if (service.type === booking.transportType) {
        if (service.data && service.data.some(item => 
          item.id === bookingId || (item.vehicle_id === booking.vehicleId))) {
          return false;
        }
      }
      return true;
    });
    
    const newServiceEntry = {
      type: booking.transportType,
      agent_id: agentId,
      tour_id: tourId,
      data: [bookingData]
    };
    
    filteredServices.push(newServiceEntry);
    
    const hasChanged = JSON.stringify(filteredServices) !== JSON.stringify(prevServicesRef.current);
    
    if (hasChanged) {
      console.log(`${booking.transportType} - Dispatching booking to Redux:`, booking);
      
      isDispatching.current = true;
      dispatch(setAllServices(filteredServices));
      prevServicesRef.current = filteredServices;
      
      setTimeout(() => {
        isDispatching.current = false;
      }, 50);
    }
    
    if (!booking.id) {
      setAllBookings(prevBookings => {
        const updatedBookings = [...prevBookings];
        updatedBookings[bookingIndex] = {
          ...updatedBookings[bookingIndex],
          id: bookingId
        };
        return updatedBookings;
      });
    }
    
    // Make sure the returned service entry includes agent_id and tour_id
    return {
      ...newServiceEntry,
      agent_id: agentId,
      tour_id: tourId
    };
  }, [allBookings, allServices, dispatch, agentId, tourId]);

  // Add function to dispatch all valid bookings to Redux 
  const dispatchAllValidBookings = useCallback(() => {
    allBookings.forEach((booking, index) => {
      if (isBookingValid(booking)) {
        dispatchBookingToRedux(index);
      }
    });
  }, [allBookings, isBookingValid, dispatchBookingToRedux]);

  // Reset vehicles when component unmounts
  useEffect(() => {
    return () => {
      dispatch(resetVehicles1());
    };
  }, [dispatch]);

  // Cache vehicles from Redux when they change
  useEffect(() => {
    if (hasVehicles && vehicles !== prevVehicles.current) {
      prevVehicles.current = vehicles;
      
      setCachedVehicles(prev => ({
        ...prev,
        [selectedPort]: vehicles
      }));
    }
  }, [vehicles, hasVehicles, selectedPort]);

  // Monitor for vehicle search results - now creates new booking entries
  useEffect(() => {
    if (hasVehicles && selectedPort) {
      setSearchPerformed(prev => ({
        ...prev,
        [selectedPort]: true
      }));
      
      const hasBookingOfType = allBookings.some(booking => booking.transportType === selectedPort);
      
      if (!hasBookingOfType) {
        let newBookingData = { ...initialFormState, transportType: selectedPort };
        
        if (selectedPort === "Point To Point") {
          newBookingData.pickupLocation = pickupLocation;
          newBookingData.dropoffLocation = dropoffLocation;
          newBookingData.pickupTime = pickupTime;
          newBookingData.bookingDate = pickupDate;
        } else if (selectedPort === "Hourly") {
          newBookingData.pickupLocation = exitPickupLocation;
          newBookingData.pickupTime = pickupTime1;
          newBookingData.bookingDate = exitPickupDate;
        } else if (selectedPort === "Local Transfer") {
          newBookingData.pickupLocation = pickupLocation;
          newBookingData.dropoffLocation = dropoffLocation;
          newBookingData.pickupTime = pickupTimeZone;
          newBookingData.bookingDate = pickupDate;
        }
        
        setBookingsVersion(prev => prev + 1);
        setAllBookings(prev => {
          const newBookings = [...prev, newBookingData];
          const newIndex = newBookings.length - 1;
          setExpandedSections(prevExpanded => [...prevExpanded, newIndex]);
          return newBookings;
        });
      }
    }
  }, [hasVehicles, selectedPort, pickupLocation, dropoffLocation, pickupTime, pickupDate, exitPickupLocation, pickupTime1, exitPickupDate, pickupTimeZone]);

  // Log bookings only when they actually change significantly
  useEffect(() => {
    if (allBookings.length !== prevBookingsRef.current.length || 
        allBookings.some((booking, i) => 
          !prevBookingsRef.current[i] || 
          booking.price !== prevBookingsRef.current[i].price ||
          booking.vehicleId !== prevBookingsRef.current[i].vehicleId
        )) {
      console.log("allBookings", allBookings);
      prevBookingsRef.current = [...allBookings];
    }
  }, [allBookings]);

  // Effect to handle dispatching after price mode changes
  useEffect(() => {
    if (bookingsVersion > 0) {
      allBookings.forEach((booking, index) => {
        if (isBookingValid(booking)) {
          dispatchBookingToRedux(index);
        }
      });
    }
  }, [bookingsVersion, allBookings, isBookingValid, dispatchBookingToRedux]);

  // Handler functions
  const handleVehicleChange = useCallback((sectionIndex, vehicleId, mode, dmcId, city, country) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      
      let vehicleDetails = null;
      
      if (vehicleId) {
        const vehicleType = newBookings[sectionIndex].transportType;
        const relevantVehicles = cachedVehicles[vehicleType] || vehicles;
        vehicleDetails = relevantVehicles.find(v => v.id === vehicleId);
      }
      
      const transportType = newBookings[sectionIndex].transportType;
      
      let updatedPickupLocation, updatedDropoffLocation, updatedPickupTime, updatedBookingDate;
      
      if (transportType === "Point To Point") {
        updatedPickupLocation = pickupLocation;
        updatedDropoffLocation = dropoffLocation;
        updatedPickupTime = pickupTime;
        updatedBookingDate = pickupDate;
      } else if (transportType === "Hourly") {
        updatedPickupLocation = exitPickupLocation;
        updatedDropoffLocation = '';
        updatedPickupTime = pickupTime1;
        updatedBookingDate = exitPickupDate;
      } else if (transportType === "Local Transfer") {
        updatedPickupLocation = pickupLocation;
        updatedDropoffLocation = dropoffLocation;
        updatedPickupTime = pickupTimeZone;
        updatedBookingDate = pickupDate;
      }
      
      newBookings[sectionIndex] = {
        ...newBookings[sectionIndex],
        vehicleId,
        mode,
        dmcId,
        city: city || (vehicleDetails?.city || ''),
        country: country || (vehicleDetails?.country || ''),
        vehicleName: vehicleDetails?.vehicle_name || '',
        vehicleImage: vehicleDetails?.image || '',
        vehicleType: vehicleDetails?.vehicle_type || '',
        vehicleModel: vehicleDetails?.vehicle_model || '',
        pickupLocation: updatedPickupLocation,
        dropoffLocation: updatedDropoffLocation,
        pickupTime: updatedPickupTime,
        bookingDate: updatedBookingDate
      };
      
      return newBookings;
    });
    
    dispatch(setSelectedVehicle({
      id: vehicleId, 
      mode: mode, 
      dmcId: dmcId, 
      city, 
      country,
    }));
  }, [cachedVehicles, vehicles, pickupLocation, dropoffLocation, pickupTime, pickupDate, exitPickupLocation, pickupTime1, exitPickupDate, pickupTimeZone, dispatch]);

  const handlePaxChange = useCallback((sectionIndex, adults, children) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      newBookings[sectionIndex] = {
        ...newBookings[sectionIndex],
        adults,
        children
      };
      return newBookings;
    });
  }, []);

  const handlePriceModeChange = useCallback((sectionIndex, priceMode) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      newBookings[sectionIndex] = {
        ...newBookings[sectionIndex],
        priceMode
      };
      
      const updatedBooking = newBookings[sectionIndex];
      const isComplete = updatedBooking.vehicleId && 
                         (updatedBooking.adults + updatedBooking.children > 0) && 
                         priceMode && 
                         (updatedBooking.transportType !== "Hourly" || (updatedBooking.hours && updatedBooking.hours >= 1));
      
      if (isComplete) {
        setBookingsVersion(prev => prev + 1);
      }
      
      return newBookings;
    });
  }, []);

  const handleHourChange = useCallback((sectionIndex, hours) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      newBookings[sectionIndex] = {
        ...newBookings[sectionIndex],
        hours
      };
      return newBookings;
    });
  }, []);

  const handlePriceChange = useCallback((sectionIndex, price) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      
      if (newBookings[sectionIndex].price !== price) {
        console.log(`Updated price for booking #${sectionIndex + 1} to ${price}`);
        
        newBookings[sectionIndex] = {
          ...newBookings[sectionIndex],
          price
        };
        
        const booking = newBookings[sectionIndex];
        if (isBookingValid(booking) && price > 0) {
          setBookingsVersion(prev => prev + 1);
        }
      }
      
      return newBookings;
    });
  }, [isBookingValid]);

  const handleHourlyPriceChange = useCallback((sectionIndex, totalHourlyPrice) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      
      if (newBookings[sectionIndex].price !== totalHourlyPrice) {
        console.log(`Updated hourly price for booking #${sectionIndex + 1} to ${totalHourlyPrice}`);
        
        newBookings[sectionIndex] = {
          ...newBookings[sectionIndex],
          price: totalHourlyPrice
        };
        
        const booking = newBookings[sectionIndex];
        if (isBookingValid(booking) && totalHourlyPrice > 0) {
          setBookingsVersion(prev => prev + 1);
        }
      }
      
      return newBookings;
    });
  }, [isBookingValid]);

  const handleAddMore = useCallback(() => {
    let newBookingData = { ...initialFormState, transportType: selectedPort };
    
    if (selectedPort === "Point To Point") {
      newBookingData.pickupLocation = pickupLocation;
      newBookingData.dropoffLocation = dropoffLocation;
      newBookingData.pickupTime = pickupTime;
      newBookingData.bookingDate = pickupDate;
    } else if (selectedPort === "Hourly") {
      newBookingData.pickupLocation = exitPickupLocation;
      newBookingData.pickupTime = pickupTime1;
      newBookingData.bookingDate = exitPickupDate;
    } else if (selectedPort === "Local Transfer") {
      newBookingData.pickupLocation = pickupLocation;
      newBookingData.dropoffLocation = dropoffLocation;
      newBookingData.pickupTime = pickupTimeZone;
      newBookingData.bookingDate = pickupDate;
    }
    
    setAllBookings(prev => {
      const newBookings = [...prev, newBookingData];
      const newIndex = newBookings.length - 1;
      setExpandedSections(prevExpanded => [...prevExpanded, newIndex]);
      return newBookings;
    });
  }, [selectedPort, pickupLocation, dropoffLocation, pickupTime, pickupDate, exitPickupLocation, pickupTime1, exitPickupDate, pickupTimeZone]);

  const handleRemoveBooking = useCallback((indexToRemove) => {
    const bookingToRemove = allBookings[indexToRemove];
    
    if (bookingToRemove) {
      setAllBookings(prevBookings => {
        const updatedBookings = prevBookings.filter((_, index) => index !== indexToRemove);
        
        if (!updatedBookings.some(booking => booking.transportType === bookingToRemove.transportType)) {
          setSearchPerformed(prev => ({
            ...prev,
            [bookingToRemove.transportType]: false
          }));
        }
        
        return updatedBookings;
      });
      
      setExpandedSections(prev => 
        prev.filter(index => index !== indexToRemove)
            .map(index => index > indexToRemove ? index - 1 : index)
      );
      
      if (bookingToRemove.vehicleId) {
        const currentServices = [...allServices];
        
        const filteredServices = currentServices.filter(service => {
          if (bookingToRemove.id && service.id === bookingToRemove.id) {
            return false;
          }
          
          if (service.type === bookingToRemove.transportType && 
              service.vehicleId === bookingToRemove.vehicleId) {
            return false;
          }
          
          return true;
        });
        
        if (filteredServices.length !== currentServices.length) {
          console.log(`Removing ${bookingToRemove.transportType} booking from Redux:`, bookingToRemove);
          dispatch(setAllServices(filteredServices));
          prevServicesRef.current = filteredServices;
        }
      }
    }
  }, [allBookings, allServices, dispatch]);

  const handleOpenModal = useCallback((index) => {
    dispatchBookingToRedux(index);
    setSelectedSectionIndex(index);
    setOpenModal(true);
  }, [dispatchBookingToRedux]);

  const handleCloseModal = useCallback(() => {
    setOpenModal(false);
    setSelectedSectionIndex(null);
  }, []);

  const toggleExpand = (bookingIndex) => {
    setExpandedSections(prev =>
      prev.includes(bookingIndex) ? prev.filter(i => i !== bookingIndex) : [...prev, bookingIndex]
    );
  };

  const getVehiclesForBooking = useCallback((booking) => {
    const bookingType = booking.transportType;
    return cachedVehicles[bookingType] || vehicles;
  }, [cachedVehicles, vehicles]);

  const validateBooking = (section, index) => {
    if (!section.vehicleId) {
      return `Booking #${index + 1}: Please select a vehicle.`;
    }
    
    if (section.adults + section.children <= 0) {
      return `Booking #${index + 1}: Please select at least one passenger.`;
    }
    
    if (!section.priceMode) {
      return `Booking #${index + 1}: Please select a price mode.`;
    }
    
    if (section.transportType === "Hourly" && (!section.hours || section.hours < 1)) {
      return `Booking #${index + 1}: Please select valid hours.`;
    }
    
    return null;
  };

  const anySearchPerformed = Object.values(searchPerformed).some(value => value);

  // Show search form if no search performed yet
  if (!anySearchPerformed) {
    return (
      <Container maxWidth="xl" sx={{ py: 2, position: 'relative' }}>
        {/* Header Card with Gradient Background */}
        <Card 
          elevation={3}
          sx={{
            borderRadius: 3,
            background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
            color: 'white',
            mb: 3,
            mx: 'auto',
          }}
        >
          <CardContent sx={{ py: 1}}>
            <Box display="flex" alignItems="center" justifyContent="space-between">
              <Box display="flex" alignItems="center">
                <DirectionsCarIcon sx={{ mr: 2, fontSize: 32, color: '#FFD700' }} />
                <Box>
                  <Typography variant="h5" fontWeight="600" sx={{ color: 'white' }}>
                    Book Transport Services
                  </Typography>
                  <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)' }}>
                    Select professional transport and configure your tour package
                  </Typography>
                </Box>
              </Box>
              <Chip 
                label="Search Required"
                sx={{ 
                  bgcolor: 'rgba(255, 255, 255, 0.2)',
                  color: 'white',
                  fontWeight: 600,
                  border: '1px solid rgba(255, 255, 255, 0.3)'
                }}
              />
            </Box>
          </CardContent>
        </Card>

        {/* Search Card */}
        <Card 
          elevation={2}
          sx={{ 
            borderRadius: 3,
            border: `2px solid ${alpha('#ff6b6b', 0.2)}`,
            mb: 3,
            transition: 'all 0.3s ease',
            '&:hover': {
              boxShadow: `0 8px 24px ${alpha('#ff6b6b', 0.15)}`,
              transform: 'translateY(-2px)',
            }
          }}
        >
          <CardContent sx={{ p: 2 }}>
            <SearchLocationTransport Location={Location} dayIndex={dayIndex} />
          </CardContent>
        </Card>
      </Container>
    );
  }

  return (
    <Container maxWidth="xl" sx={{ py: 2, position: 'relative' }}>
      {/* Header Card with Gradient Background */}
      <Card 
        elevation={3}
        sx={{
          borderRadius: 3,
          background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
          color: 'white',
          mb: 3,
          mx: 'auto',
        }}
      >
        <CardContent sx={{ py: 1}}>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <DirectionsCarIcon sx={{ mr: 2, fontSize: 32, color: '#FFD700' }} />
              <Box>
                <Typography variant="h5" fontWeight="600" sx={{ color: 'white' }}>
                  Book Transport Services
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)' }}>
                  Select professional transport and configure your tour package
                </Typography>
              </Box>
            </Box>
            <Chip 
              label={`${allBookings.length} Service${allBookings.length !== 1 ? 's' : ''}`}
              sx={{ 
                bgcolor: 'rgba(255, 255, 255, 0.2)',
                color: 'white',
                fontWeight: 600,
                border: '1px solid rgba(255, 255, 255, 0.3)'
              }}
            />
          </Box>
        </CardContent>
      </Card>
      
      {/* Search Section */}
      <Box sx={{ mb: 3 }}>
        <SearchLocationTransport Location={Location} dayIndex={dayIndex} />
      </Box>
      
      {/* Alerts */}
      <Fade in={validationError} timeout={300}>
        <Box>
          {validationError && (
            <Alert severity="error" sx={{ mb: 2, borderRadius: 2 }}>
              {validationError}
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Fade in={bookingSuccess} timeout={300}>
        <Box>
          {bookingSuccess && (
            <Alert severity="success" sx={{ mb: 2, borderRadius: 2 }}>
              Transport booking information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      {/* Multiple Booking Cards */}
      <Grid container spacing={3}>
        {allBookings.map((booking, bookingIndex) => {
          const isExpanded = expandedSections.includes(bookingIndex);
          const completionStatus = isBookingValid(booking) ? 4 : 
            (booking.vehicleId ? 1 : 0) + (booking.adults + booking.children > 0 ? 1 : 0) + 
            (booking.priceMode ? 1 : 0) + (booking.transportType !== "Hourly" || booking.hours >= 1 ? 1 : 0);
          
          return (
            <Grid item xs={12} key={`booking-${bookingIndex}`}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: `2px solid ${alpha('#ff6b6b', 0.2)}`,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: `0 8px 24px ${alpha('#ff6b6b', 0.15)}`,
                    transform: 'translateY(-2px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 2,
                    bgcolor: alpha('#ff6b6b', 0.05),
                    borderBottom: `1px solid ${alpha('#ff6b6b', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                      <Chip 
                        label={`Transport #${bookingIndex + 1}`}
                        sx={{ 
                          bgcolor: '#ff6b6b',
                          color: 'white',
                          fontWeight: 600
                        }}
                        size="small"
                      />
                      <Chip 
                        label={`${completionStatus}/4 Complete`}
                        color={completionStatus === 4 ? "success" : "warning"}
                        size="small"
                        variant="outlined"
                      />
                      <Chip 
                        label={booking.transportType || "Unknown"}
                        color={booking.transportType === "Hourly" ? "secondary" : "primary"}
                        size="small"
                        variant="outlined"
                      />
                      {booking.price > 0 && (
                        <Chip
                          label={`$${booking.price.toFixed(2)}`}
                          color="success"
                          size="small"
                          variant="outlined"
                        />
                      )}
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <Tooltip title={isExpanded ? "Collapse" : "Expand"}>
                        <IconButton 
                          size="small" 
                          onClick={() => toggleExpand(bookingIndex)}
                          sx={{ 
                            bgcolor: alpha('#ff6b6b', 0.1),
                            '&:hover': { bgcolor: alpha('#ff6b6b', 0.2) }
                          }}
                        >
                          {isExpanded ? <ExpandLessIcon /> : <ExpandMoreIcon />}
                        </IconButton>
                      </Tooltip>
                      
                      {allBookings.length > 1 && (
                        <Tooltip title="Remove this transport service">
                          <IconButton 
                            size="small" 
                            onClick={() => handleRemoveBooking(bookingIndex)}
                            sx={{ 
                              bgcolor: alpha('#f44336', 0.1),
                              '&:hover': { bgcolor: alpha('#f44336', 0.2) }
                            }}
                          >
                            <DeleteIcon sx={{ fontSize: 18, color: '#f44336' }} />
                          </IconButton>
                        </Tooltip>
                      )}
                      
                      <Button
                        variant="outlined"
                        size="large"
                        onClick={() => handleOpenModal(bookingIndex)}
                        disabled={!isBookingValid(booking)}
                        startIcon={<VisibilityIcon />}
                        sx={{
                          borderRadius: 2,
                          px: 4,
                          py: 1,
                          fontSize: '0.875rem',
                          fontWeight: 600,
                          textTransform: 'none',
                          borderColor: '#ff6b6b',
                          color: '#ff6b6b',
                          '&:hover': {
                            borderColor: '#ee5a24',
                            bgcolor: alpha('#ff6b6b', 0.05),
                            transform: 'translateY(-1px)',
                          },
                          '&:disabled': {
                            borderColor: alpha('#ff6b6b', 0.3),
                            color: alpha('#ff6b6b', 0.3),
                          },
                          transition: 'all 0.3s ease',
                        }}
                      >
                        View Summary
                      </Button>
                    </Box>
                  </Box>

                  {/* Expanded Content */}
                  <Collapse in={isExpanded} timeout={300}>
                    <Paper 
                      elevation={0} 
                      sx={{ 
                        m: 2,
                        p: 0, 
                        borderRadius: 2,
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdropFilter: 'blur(10px)'
                      }}
                    >
                      {/* Vehicle Dropdown with Complete Functionality */}
                      <Box sx={{ p: 3 }}>
                        {booking.transportType === "Point To Point" && searchPerformed["Point To Point"] && (
                          <VehicleListDropdown
                            key={`point-to-point-${bookingIndex}`}
                            selectedVehicle={booking.vehicleId || null}
                            onVehicleChange={(vehicleId, mode, dmcId, city, country) => 
                              handleVehicleChange(bookingIndex, vehicleId, mode, dmcId, city, country)}
                            onPaxChange={(adults, children) => 
                              handlePaxChange(bookingIndex, adults, children)}
                            onPriceModeChange={(priceMode) => 
                              handlePriceModeChange(bookingIndex, priceMode)}
                            onPriceChange={(price) =>
                              handlePriceChange(bookingIndex, price)}
                            sectionIndex={bookingIndex}
                            isNewBooking={!booking.vehicleId}
                            cachedVehicles={getVehiclesForBooking(booking)}
                            cachedVehicleName={booking.vehicleName}
                          />
                        )}
                        
                        {booking.transportType === "Hourly" && searchPerformed["Hourly"] && (
                          <VehicleListDropdown1
                            key={`hourly-${bookingIndex}`}
                            selectedVehicle={booking.vehicleId || null}
                            onVehicleChange={(vehicleId, mode, dmcId, city, country) => 
                              handleVehicleChange(bookingIndex, vehicleId, mode, dmcId, city, country)}
                            onPaxChange={(adults, children) => 
                              handlePaxChange(bookingIndex, adults, children)}
                            onPriceModeChange={(priceMode) => 
                              handlePriceModeChange(bookingIndex, priceMode)}
                            onHourChange={(hours) => 
                              handleHourChange(bookingIndex, hours)}
                            onHourlyPriceChange={(totalHourlyPrice) =>
                              handleHourlyPriceChange(bookingIndex, totalHourlyPrice)}
                            sectionIndex={bookingIndex}
                            isNewBooking={!booking.vehicleId}
                            cachedVehicles={getVehiclesForBooking(booking)}
                            cachedVehicleName={booking.vehicleName}
                          />
                        )}
                        
                        {booking.transportType === "Local Transfer" && searchPerformed["Local Transfer"] && (
                          <VehicleListDropdownZone
                            key={`local-transfer-${bookingIndex}`}
                            selectedVehicle={booking.vehicleId || null}
                            onVehicleChange={(vehicleId, mode, dmcId, city, country) => 
                              handleVehicleChange(bookingIndex, vehicleId, mode, dmcId, city, country)}
                            onPaxChange={(adults, children) => 
                              handlePaxChange(bookingIndex, adults, children)}
                            onPriceModeChange={(priceMode) => 
                              handlePriceModeChange(bookingIndex, priceMode)}
                            onPriceChange={(price) =>
                              handlePriceChange(bookingIndex, price)}
                            sectionIndex={bookingIndex}
                            isNewBooking={!booking.vehicleId}
                            cachedVehicles={getVehiclesForBooking(booking)}
                            cachedVehicleName={booking.vehicleName}
                          />
                        )}
                      </Box>
                    </Paper>
                  </Collapse>
                </CardContent>
              </Card>
            </Grid>
          );
        })}

        {/* Add More Card */}
        <Grid item xs={12}>
          <Card 
            sx={{ 
              borderRadius: 3,
              border: `2px dashed ${alpha('#ff6b6b', 0.4)}`,
              bgcolor: alpha('#ff6b6b', 0.02),
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              '&:hover': {
                bgcolor: alpha('#ff6b6b', 0.05),
                borderColor: '#ff6b6b',
                transform: 'translateY(-1px)',
              }
            }}
            onClick={handleAddMore}
          >
            <CardContent sx={{ py: 2 }}>
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                gap: 2
              }}>
                <AddIcon sx={{ fontSize: 32, color: '#ff6b6b' }} />
                <Typography variant="h6" color="#ff6b6b" fontWeight={600}>
                  Add More Transport Service
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Transport Summary Modal */}
      {openModal && selectedSectionIndex !== null && (
        <TransportSummaryModal
          open={openModal}
          onClose={handleCloseModal}
          bookingData={allBookings[selectedSectionIndex]}
          bookingIndex={selectedSectionIndex}
          bookingType={allBookings[selectedSectionIndex]?.transportType}
          onConfirm={() => {
            setBookingSuccess(true);
            setOpenModal(false);
            setTimeout(() => setBookingSuccess(false), 3000);
          }}
        />
      )}
    </Container>
  );
} 