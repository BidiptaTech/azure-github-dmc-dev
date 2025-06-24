import React, { useState, useEffect, useRef, useCallback, useMemo } from 'react';
import { 
  Typography, 
  Card, 
  CardContent, 
  CardMedia, 
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
} from '@mui/material';
import DeleteIcon from '@mui/icons-material/Delete';
import AlternateFilterSearchBox from './TransportSearchLocation';
import SearchLocationTransport from './SearchLocationTransport';
import { useSelector, useDispatch } from 'react-redux';
import { setSelectedVehicle, resetVehicles1 } from '@/slice/localtour/Localslice';
import { setAllServices } from '@/slice/tour-packages/tourPackageSlice';
import VehicleListDropdown from './vehiclelistdropdown';
import VehicleListDropdown1 from './vehiclelistdropdown1';
import VehicleListDropdownZone from './vehiclelistdropdownZone';
import TransportSummaryModal from './TransportSummaryModal';

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
  hours: 1, // Only relevant for hourly bookings
  transportType: '', // To track if this is a Point-to-Point, Hourly, or Local Transfer booking
  vehicleName: '', // Store the vehicle name to persist display
  vehicleImage: '', // Store the vehicle image to persist display
  vehicleModel: '', // Store additional vehicle details
  vehicleType: '', // Store additional vehicle details
  zoneId: '', // For Local Transfer zone ID
  pickupLocation: '', // Store pickup location
  dropoffLocation: '', // Store dropoff location
  pickupTime: '', // Store pickup time
  bookingDate: '', // Store pickup date
  price: 0, // Store the calculated price for the booking
};

export default function LocalTransportComponent({ dayIndex = 0 }) {
  const Location = useSelector((state) => {
    return state.bookings?.searchLocation || {};
  });
  const vehicles = useSelector(state => state.localtour.vehicles || []);
  const selectedVehicleId = useSelector(state => state.localtour.selectedVehicle?.id);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const hasVehicles = vehicles && vehicles.length > 0;
  const dispatch = useDispatch();
  
  // Add the allServices selector from Redux state - memoize to prevent unnecessary rerenders
  const allServices = useSelector((state) => state.tourPackages.AllServices || []);
  
  // Get location data from redux store
  const pickupLocation = useSelector(state => state.localtour.entrypickup || '');
  const dropoffLocation = useSelector(state => state.localtour.entrydropoff || '');
  const exitPickupLocation = useSelector(state => state.localtour.exitpickup || '');
  const pickupTime = useSelector(state => state.localtour.entrytime || '');
  const pickupTime1 = useSelector(state => state.localtour.entrytime1 || '');
  const pickupTimeZone = useSelector(state => state.localtour.entrytimezone || '');
  
  // Make sure to get the date values directly from Redux
  const pickupDate = useSelector(state => state.localtour.pickdate || '');
  const exitPickupDate = useSelector(state => state.localtour.exitpickupdate || '');
  
  // Single array of all bookings regardless of type
  const [allBookings, setAllBookings] = useState([]);
  
  // Use a ref to track previous booking state to avoid excessive logging
  const prevBookingsRef = useRef([]);
  
  // Add a ref to track the previous Redux state to prevent unnecessary dispatches
  const prevServicesRef = useRef([]);
  
  // Add a version tracking mechanism to prevent infinite loops
  const [bookingsVersion, setBookingsVersion] = useState(0);
  
  // Cache to store vehicle data for each type
  const [cachedVehicles, setCachedVehicles] = useState({
    "Point To Point": [],
    "Hourly": [],
    "Local Transfer": []
  });
  
  // Track search status for each transport type
  const [searchPerformed, setSearchPerformed] = useState({
    "Point To Point": false,
    "Hourly": false,
    "Local Transfer": false
  });
  
  // For tracking the current booking being viewed
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [openModal, setOpenModal] = useState(false);
  const [validationError, setValidationError] = useState(null);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  
  // Keep track of previous vehicles to avoid unnecessary rerenders
  const prevVehicles = useRef([]);

  // Function to check if a booking is valid
  const isBookingValid = useCallback((section) => {
    return section.vehicleId && 
           (section.adults + section.children > 0) && 
           section.priceMode && 
           (section.transportType !== "Hourly" || (section.hours && section.hours >= 1));
  }, []);
  
  // Track if we're currently dispatching to prevent recursive updates
  const isDispatching = useRef(false);

  // Function to dispatch booking data to Redux store
  const dispatchBookingToRedux = useCallback((bookingIndex) => {
    // Prevent recursive dispatches
    if (isDispatching.current) return;
    
    const booking = allBookings[bookingIndex];
    
    if (!booking || !booking.vehicleId || !booking.priceMode) {
      console.error("Cannot dispatch incomplete booking to Redux", booking);
      return;
    }
    
    // Generate a unique ID for this booking if it doesn't have one
    const bookingId = booking.id || `${booking.transportType.toLowerCase().replace(/\s+/g, '-')}-${Date.now()}`;
    
    // Find any existing customer info in current services
    const customerInfoService = allServices.find(service => service.type === 'CustomerInfo');
    
    // Prepare booking data in the new format
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
      // If we have customer info, spread it into the booking data
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
    
    // Add hours only for hourly bookings
    if (booking.transportType === "Hourly") {
      bookingData.hours = booking.hours;
    }
    
    // Add zoneId only for Local Transfer bookings if it exists
    if (booking.transportType === "Local Transfer" && booking.zoneId) {
      bookingData.zone_id = booking.zoneId;
    }
    
    // Clone the existing services array
    const currentServices = [...allServices];
    
    // Check if this booking already exists in the services (with the new structure)
    const existingServiceIndex = currentServices.findIndex(service => {
      if (service.type === booking.transportType) {
        // Check if this service has data array with our booking ID
        return service.data && service.data.some(item => item.id === bookingId || 
          (item.vehicle_id === booking.vehicleId));
      }
      return false;
    });
    
    // If the booking already exists with the same data, don't dispatch again
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
        
        // Update local state to include the ID if needed
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
        
        // Return the existing service structure
        return {
          type: booking.transportType,
          data: [existingBookingItem]
        };
      }
    }
    
    // Filter out any existing services with the same transport type that contain our vehicle ID
    const filteredServices = currentServices.filter(service => {
      if (service.type === booking.transportType) {
        // If this is the same type service, check if it contains our vehicle ID
        if (service.data && service.data.some(item => 
          item.id === bookingId || (item.vehicle_id === booking.vehicleId))) {
          // This service contains our booking ID or vehicle, so filter it out
          return false;
        }
      }
      // Keep all other services
      return true;
    });
    
    // Create a new service entry for this transport type
    const newServiceEntry = {
      type: booking.transportType,
      data: [bookingData]
    };
    
    // Add the new service entry
    filteredServices.push(newServiceEntry);
    
    // Check if the services array has actually changed
    const hasChanged = JSON.stringify(filteredServices) !== JSON.stringify(prevServicesRef.current);
    
    if (hasChanged) {
      console.log(`${booking.transportType} - Dispatching booking to Redux:`, booking);
      console.log(`${booking.transportType} - Formatted booking data for Redux:`, bookingData);
      console.log(`${booking.transportType} - Dispatching updated services to Redux:`, filteredServices);
      
      // Set the dispatching flag to prevent recursive updates
      isDispatching.current = true;
      
      // Dispatch to Redux
      dispatch(setAllServices(filteredServices));
      
      // Update our reference to the current services
      prevServicesRef.current = filteredServices;
      
      // Reset the dispatching flag after a short delay
      setTimeout(() => {
        isDispatching.current = false;
      }, 50);
    }
    
    // Update local state to include the ID
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
    
    // Return the service entry in the new format
    return newServiceEntry;
  }, [allBookings, allServices, dispatch]);

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

  // Keep track of changes to date values
  useEffect(() => {
    console.log("Date values changed in Redux:", { 
      pickupDate,
      exitPickupDate
    });
  }, [pickupDate, exitPickupDate]);

  // Log bookings only when they actually change significantly
  useEffect(() => {
    // Only log if the length changes or if there are meaningful updates
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
  
  // Show bookings only after search has been performed
  useEffect(() => {
    if (hasVehicles && selectedPort) {
      setSearchPerformed(prev => ({
        ...prev,
        [selectedPort]: true
      }));
      
      // Add a new booking of the current type if it's the first search for this type
      const hasBookingOfType = allBookings.some(booking => booking.transportType === selectedPort);
      
      if (!hasBookingOfType) {
        // Initialize new booking with the location data based on transport type
        let newBookingData = { ...initialFormState, transportType: selectedPort };
        
        // Add location data based on transport type
        if (selectedPort === "Point To Point") {
          newBookingData.pickupLocation = pickupLocation;
          newBookingData.dropoffLocation = dropoffLocation;
          newBookingData.pickupTime = pickupTime;
          newBookingData.bookingDate = pickupDate;
          console.log("Creating Point to Point booking with date:", pickupDate);
        } else if (selectedPort === "Hourly") {
          newBookingData.pickupLocation = exitPickupLocation;
          newBookingData.pickupTime = pickupTime1;
          newBookingData.bookingDate = exitPickupDate;
          console.log("Creating Hourly booking with date:", exitPickupDate);
        } else if (selectedPort === "Local Transfer") {
          newBookingData.pickupLocation = pickupLocation;
          newBookingData.dropoffLocation = dropoffLocation;
          newBookingData.pickupTime = pickupTimeZone;
          newBookingData.bookingDate = pickupDate;
          console.log("Creating Local Transfer booking with date:", pickupDate);
        }
        
        // Increment the version to track that we're adding a new booking
        setBookingsVersion(prev => prev + 1);
        setAllBookings(prev => [...prev, newBookingData]);
      }
    }
  }, [hasVehicles, selectedPort, pickupLocation, dropoffLocation, pickupTime, pickupDate, exitPickupLocation, pickupTime1, exitPickupDate, pickupTimeZone]);
  // Removed allBookings from dependency array to prevent infinite loop

  const handleVehicleChange = useCallback((sectionIndex, vehicleId, mode, dmcId, city, country) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      
      // Find the vehicle from appropriate vehicle list with complete details
      let vehicleDetails = null;
      
      if (vehicleId) {
        const vehicleType = newBookings[sectionIndex].transportType;
        const relevantVehicles = cachedVehicles[vehicleType] || vehicles;
        vehicleDetails = relevantVehicles.find(v => v.id === vehicleId);
      }
      
      // Get the current transport type to determine which location data to use
      const transportType = newBookings[sectionIndex].transportType;
      
      // Store appropriate location data based on transport type
      let updatedPickupLocation, updatedDropoffLocation, updatedPickupTime, updatedBookingDate;
      
      if (transportType === "Point To Point") {
        updatedPickupLocation = pickupLocation;
        updatedDropoffLocation = dropoffLocation;
        updatedPickupTime = pickupTime;
        updatedBookingDate = pickupDate;
      } else if (transportType === "Hourly") {
        updatedPickupLocation = exitPickupLocation;
        updatedDropoffLocation = ''; // No dropoff for hourly
        updatedPickupTime = pickupTime1;
        updatedBookingDate = exitPickupDate;
      } else if (transportType === "Local Transfer") {
        updatedPickupLocation = pickupLocation;
        updatedDropoffLocation = dropoffLocation;
        updatedPickupTime = pickupTimeZone;
        updatedBookingDate = pickupDate;
      }
      
      // Update booking with vehicle details including all properties needed for display
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
    
    // Also update Redux store for the current vehicle selection
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
      
      // Check if booking is now complete
      const updatedBooking = newBookings[sectionIndex];
      const isComplete = updatedBooking.vehicleId && 
                         (updatedBooking.adults + updatedBooking.children > 0) && 
                         priceMode && 
                         (updatedBooking.transportType !== "Hourly" || (updatedBooking.hours && updatedBooking.hours >= 1));
      
      if (isComplete) {
        // We'll dispatch after state is updated using the version tracking
        setBookingsVersion(prev => prev + 1);
      }
      
      return newBookings;
    });
  }, []);
  
  // Use an effect to handle dispatching after price mode changes
  useEffect(() => {
    if (bookingsVersion > 0) {
      // Find complete bookings that need to be dispatched
      allBookings.forEach((booking, index) => {
        if (isBookingValid(booking)) {
          dispatchBookingToRedux(index);
        }
      });
    }
  }, [bookingsVersion, allBookings, isBookingValid, dispatchBookingToRedux]);
  
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
  
  // Handler for price updates - for Point To Point and Local Transfer
  const handlePriceChange = useCallback((sectionIndex, price) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      
      // Only update and log if the price actually changed
      if (newBookings[sectionIndex].price !== price) {
        console.log(`Updated price for booking #${sectionIndex + 1} to ${price}`);
        
        newBookings[sectionIndex] = {
          ...newBookings[sectionIndex],
          price
        };
        
        // If booking is complete, we'll dispatch after state update
        const booking = newBookings[sectionIndex];
        if (isBookingValid(booking) && price > 0) {
          // Use version tracking instead of setTimeout
          setBookingsVersion(prev => prev + 1);
        }
      }
      
      return newBookings;
    });
  }, [isBookingValid]);
  
  // Handler for hourly price updates - for Hourly bookings
  const handleHourlyPriceChange = useCallback((sectionIndex, totalHourlyPrice) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      
      // Only update and log if the price actually changed
      if (newBookings[sectionIndex].price !== totalHourlyPrice) {
        console.log(`Updated hourly price for booking #${sectionIndex + 1} to ${totalHourlyPrice}`);
        
        newBookings[sectionIndex] = {
          ...newBookings[sectionIndex],
          price: totalHourlyPrice
        };
        
        // If booking is complete, we'll dispatch after state update
        const booking = newBookings[sectionIndex];
        if (isBookingValid(booking) && totalHourlyPrice > 0) {
          // Use version tracking instead of setTimeout
          setBookingsVersion(prev => prev + 1);
        }
      }
      
      return newBookings;
    });
  }, [isBookingValid]);

  const handleAddMore = useCallback(() => {
    // Add a new booking of the current port type with location data
    let newBookingData = { ...initialFormState, transportType: selectedPort };
    
    // Add location data based on transport type
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
    
    setAllBookings(prev => [...prev, newBookingData]);
  }, [selectedPort, pickupLocation, dropoffLocation, pickupTime, pickupDate, exitPickupLocation, pickupTime1, exitPickupDate, pickupTimeZone]);

  const handleRemoveSection = useCallback((indexToRemove) => {
    // Get the booking that's being removed to find it in Redux
    const bookingToRemove = allBookings[indexToRemove];
    
    if (bookingToRemove) {
      // First, remove from local state
      setAllBookings(prevBookings => {
        const updatedBookings = prevBookings.filter((_, index) => index !== indexToRemove);
        
        // If this was the last booking of its type, update the search performed state
        if (!updatedBookings.some(booking => booking.transportType === bookingToRemove.transportType)) {
          setSearchPerformed(prev => ({
            ...prev,
            [bookingToRemove.transportType]: false
          }));
        }
        
        return updatedBookings;
      });
      
      // Then, remove from Redux state
      if (bookingToRemove.vehicleId) {
        // Clone the existing services array
        const currentServices = [...allServices];
        
        // Filter out the booking with matching type and vehicleId or matching ID
        const filteredServices = currentServices.filter(service => {
          // If the booking has an ID, use that for exact matching
          if (bookingToRemove.id && service.id === bookingToRemove.id) {
            return false;
          }
          
          // Otherwise match by type and vehicleId
          if (service.type === bookingToRemove.transportType && 
              service.vehicleId === bookingToRemove.vehicleId) {
            return false;
          }
          
          return true;
        });
        
        // Only dispatch if there's an actual change
        if (filteredServices.length !== currentServices.length) {
          console.log(`Removing ${bookingToRemove.transportType} booking from Redux:`, bookingToRemove);
          dispatch(setAllServices(filteredServices));
          
          // Update our reference to the current services
          prevServicesRef.current = filteredServices;
        }
      }
    }
  }, [allBookings, allServices, dispatch]);
  
  const handleOpenModal = useCallback((index) => {
    // Make sure the data is in Redux before showing the modal
    dispatchBookingToRedux(index);
    
    setSelectedSectionIndex(index);
    setOpenModal(true);
  }, [dispatchBookingToRedux]);

  const handleCloseModal = useCallback(() => {
    setOpenModal(false);
    setSelectedSectionIndex(null);
  }, []);
  
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
    
    // Zone ID is no longer required for Local Transfer
    
    return null;
  };

  // Check if any search has been performed for any transport type
  const anySearchPerformed = Object.values(searchPerformed).some(value => value);

  // Function to get the appropriate vehicles list for a booking
  const getVehiclesForBooking = useCallback((booking) => {
    const bookingType = booking.transportType;
    return cachedVehicles[bookingType] || vehicles;
  }, [cachedVehicles, vehicles]);

  return (
    <Container>
      <Typography variant="h5" gutterBottom>Local Transport Options</Typography>
      <SearchLocationTransport Location={Location} dayIndex={dayIndex} />
      
      {validationError && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {validationError}
        </Alert>
      )}
      
      {bookingSuccess && (
        <Alert severity="success" sx={{ mb: 2 }}>
          Booking information saved successfully to the tour package data!
        </Alert>
      )}
      
      {/* Show bookings section after at least one search has been performed */}
      {anySearchPerformed && (
        <Stack spacing={4} mt={4}>
          {allBookings.map((booking, sectionIndex) => (
                          <Paper 
              key={sectionIndex} 
              elevation={2} 
              sx={{ p: 3, position: 'relative', zIndex: 0 }}
              onClick={(e) => {
                // Don't process click events on the paper that might interfere with child components
                if (e.target === e.currentTarget) {
                  e.stopPropagation();
                }
              }}
            >
              <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                  <Typography variant="h6">Booking #{sectionIndex + 1}</Typography>
                  <Chip 
                    label={booking.transportType} 
                    color={booking.transportType === "Hourly" ? "secondary" : "primary"}
                    sx={{ fontWeight: 500 }}
                  />
                  {booking.price > 0 && (
                    <Chip
                      label={`Price: $${booking.price.toFixed(2)}`}
                      color="success"
                      sx={{ fontWeight: 500 }}
                    />
                  )}
                </Box>
                <IconButton
                  color="error"
                  onClick={(e) => {
                    e.stopPropagation();
                    handleRemoveSection(sectionIndex);
                  }}
                  size="small"
                  aria-label="delete booking"
                  sx={{ 
                    borderRadius: '4px', 
                    '&:hover': { 
                      bgcolor: 'rgba(211, 47, 47, 0.04)' 
                    } 
                  }}
                >
                  <DeleteIcon />
                </IconButton>
              </Box>
              
              {/* Render the appropriate component based on the booking's transport type */}
              {booking.transportType === "Point To Point" && searchPerformed["Point To Point"] && (
                <VehicleListDropdown
                  key={`point-to-point-${sectionIndex}`}
                  selectedVehicle={booking.vehicleId || null}
                  onVehicleChange={(vehicleId, mode, dmcId, city, country) => 
                    handleVehicleChange(sectionIndex, vehicleId, mode, dmcId, city, country)}
                  onPaxChange={(adults, children) => 
                    handlePaxChange(sectionIndex, adults, children)}
                  onPriceModeChange={(priceMode) => 
                    handlePriceModeChange(sectionIndex, priceMode)}
                  onPriceChange={(price) =>
                    handlePriceChange(sectionIndex, price)}
                  sectionIndex={sectionIndex}
                  isNewBooking={!booking.vehicleId}
                  cachedVehicles={getVehiclesForBooking(booking)}
                  cachedVehicleName={booking.vehicleName}
                />
              )}
              
              {booking.transportType === "Hourly" && searchPerformed["Hourly"] && (
                <VehicleListDropdown1
                  key={`hourly-${sectionIndex}`}
                  selectedVehicle={booking.vehicleId || null}
                  onVehicleChange={(vehicleId, mode, dmcId, city, country) => 
                    handleVehicleChange(sectionIndex, vehicleId, mode, dmcId, city, country)}
                  onPaxChange={(adults, children) => 
                    handlePaxChange(sectionIndex, adults, children)}
                  onPriceModeChange={(priceMode) => 
                    handlePriceModeChange(sectionIndex, priceMode)}
                  onHourChange={(hours) => 
                    handleHourChange(sectionIndex, hours)}
                  onHourlyPriceChange={(totalHourlyPrice) =>
                    handleHourlyPriceChange(sectionIndex, totalHourlyPrice)}
                  sectionIndex={sectionIndex}
                  isNewBooking={!booking.vehicleId}
                  cachedVehicles={getVehiclesForBooking(booking)}
                  cachedVehicleName={booking.vehicleName}
                />
              )}
              
              {booking.transportType === "Local Transfer" && searchPerformed["Local Transfer"] && (
                <VehicleListDropdownZone
                  key={`local-transfer-${sectionIndex}`}
                  selectedVehicle={booking.vehicleId || null}
                  onVehicleChange={(vehicleId, mode, dmcId, city, country) => {
                    handleVehicleChange(sectionIndex, vehicleId, mode, dmcId, city, country);
                  }}
                  onPaxChange={(adults, children) => 
                    handlePaxChange(sectionIndex, adults, children)}
                  onPriceModeChange={(priceMode) => 
                    handlePriceModeChange(sectionIndex, priceMode)}
                  onPriceChange={(price) =>
                    handlePriceChange(sectionIndex, price)}
                  sectionIndex={sectionIndex}
                  isNewBooking={!booking.vehicleId}
                  cachedVehicles={vehicles}
                  cachedVehicleName={booking.vehicleName}
                />
              )}
              
              <Box sx={{ mt: 2, display: 'flex', gap: 2 }}>
                <Tooltip 
                  title={!isBookingValid(booking) ? "Please complete all required fields" : ""}
                  placement="top"
                  arrow
                >
                  <span style={{ width: '100%' }}>
                    <Button
                      variant="outlined"
                      fullWidth
                      size="large"
                      onClick={() => handleOpenModal(sectionIndex)}
                      disabled={!isBookingValid(booking)}
                      sx={{ height: 48 }}
                    >
                      View Summary
                    </Button>
                  </span>
                </Tooltip>
              </Box>
            </Paper>
          ))}

          {searchPerformed[selectedPort] && (
            <Box sx={{ mt: 2 }}>
              <Button
                variant="contained"
                fullWidth
                size="large"
                onClick={handleAddMore}
                sx={{ height: 48 }}
              >
                Add More Booking
              </Button>
            </Box>
          )}
        </Stack>
      )}

      {/* Summary Modal */}
      <TransportSummaryModal
        open={openModal}
        onClose={handleCloseModal}
        bookingData={selectedSectionIndex !== null ? allBookings[selectedSectionIndex] : null}
        bookingIndex={selectedSectionIndex}
        bookingType={selectedSectionIndex !== null ? allBookings[selectedSectionIndex]?.transportType : selectedPort}
      />
    </Container>
  );
} 