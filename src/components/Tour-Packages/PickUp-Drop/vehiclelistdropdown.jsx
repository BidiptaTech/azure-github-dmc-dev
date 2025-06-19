import React, { useEffect, useState, useRef } from 'react';
import { 
  Grid, 
  Autocomplete, 
  TextField, 
  Tooltip, 
  Box,
  Typography,
  Stack,
  Chip,
  Paper,
  styled,
  FormControl,
  FormLabel,
  Select,
  MenuItem,
  InputLabel,
  Button
} from '@mui/material';
import { useSelector } from "react-redux";
import LocationOnIcon from '@mui/icons-material/LocationOn';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import EventSeatIcon from '@mui/icons-material/EventSeat';
import { useDispatch } from 'react-redux';

import { fetchVehicleDetails } from '../../../slice/port/pickupDropSlice';
import { setAllServices } from '../../../slice/tour-packages/tourPackageSlice';
import Passenger from './Passenger';
import PortSummaryModal from './PortSummaryModal';

// Custom styled tooltip
const CustomTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    backgroundColor: 'white',
    color: 'rgba(0, 0, 0, 0.87)',
    maxWidth: 400,
    border: '1px solid #dadde9',
    borderRadius: '12px',
    padding: 0,
    boxShadow: theme.shadows[3]
  },
}));

// Tooltip content component
const TooltipContent = ({ vehicle }) => {
  return (
    <Box>
      {/* Header Image Section */}
      <Box sx={{ position: 'relative', width: '100%', height: 200 }}>
        <Box
          component="img"
          src={vehicle.image}
          alt={vehicle.vehicle_name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '12px',
            borderTopRightRadius: '12px',
          }}
        />
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 2 }}>
        {/* Title and Location */}
        <Typography variant="h6" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1.1rem' }}>
          {vehicle.vehicle_name}
        </Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
          <LocationOnIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 0.5 }} />
          <Typography variant="body2" color="text.secondary">
            {vehicle.city}, {vehicle.country}
          </Typography>
        </Box>

        {/* Vehicle Details */}
        <Box sx={{ mb: 2 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
            Vehicle Details
          </Typography>
          <Stack spacing={1.5}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 1 }} />
              <Typography variant="body2">
                <strong>Type:</strong> {vehicle.vehicle_type}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 1 }} />
              <Typography variant="body2">
                <strong>Model:</strong> {vehicle.vehicle_model}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 1 }} />
              <Typography variant="body2">
                <strong>Year:</strong> {vehicle.model_year}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <EventSeatIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 1 }} />
              <Typography variant="body2">
                <strong>Seating Capacity:</strong> {vehicle.seating_capacity}
              </Typography>
            </Box>
          </Stack>
        </Box>

        {/* Pricing Section */}
        <Box sx={{ mt: 2 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
            Pricing Details
          </Typography>
          <Grid container spacing={2}>
            {/* DMC Prices */}
            {(vehicle.dmc_private_price > 0 || vehicle.dmc_sharable_price > 0) && (
              <Grid item xs={6}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1.5,
                    bgcolor: 'rgba(25, 118, 210, 0.02)',
                    borderColor: 'rgba(25, 118, 210, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: 'primary.main', fontWeight: 500 }}>
                    DMC Prices
                  </Typography>
                  <Stack spacing={0.5}>
                    {vehicle.dmc_private_price > 0 && (
                      <Typography variant="body2">Private: ${vehicle.dmc_private_price}</Typography>
                    )}
                    {vehicle.dmc_sharable_price > 0 && (
                      <Typography variant="body2">Sharable: ${vehicle.dmc_sharable_price}</Typography>
                    )}
                  </Stack>
                </Paper>
              </Grid>
            )}

            {/* Travclicks Prices */}
            {(vehicle.trav_private_price > 0 || vehicle.trav_sharable_price > 0) && (
              <Grid item xs={6}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1.5,
                    bgcolor: 'rgba(76, 175, 80, 0.02)',
                    borderColor: 'rgba(76, 175, 80, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: '#2e7d32', fontWeight: 500 }}>
                    Travclicks Prices
                  </Typography>
                  <Stack spacing={0.5}>
                    {vehicle.trav_private_price > 0 && (
                      <Typography variant="body2">Private: ${vehicle.trav_private_price}</Typography>
                    )}
                    {vehicle.trav_sharable_price > 0 && (
                      <Typography variant="body2">Sharable: ${vehicle.trav_sharable_price}</Typography>
                    )}
                  </Stack>
                </Paper>
              </Grid>
            )}
          </Grid>

          {/* Tax Information */}
          {vehicle.tax_percentage && (
            <Typography 
              variant="caption" 
              sx={{ 
                display: 'block',
                mt: 1,
                color: 'text.secondary',
                fontStyle: 'italic'
              }}
            >
              *Prices are subject to {vehicle.tax_percentage}% tax
            </Typography>
          )}
        </Box>
      </Box>
    </Box>
  );
};

// Updated Mode component with dropdown instead of radio buttons
const Mode = ({ pricemode, setpricemode, vehicles }) => {
  // Return null if no vehicle data is available
  if (!vehicles) return null;

  console.log("vehicles in Mode component:", vehicles);
  
  // Safely check which price modes are available in both possible data structures
  const hasPrivatePrice = 
    (vehicles.prices && vehicles.prices.privatePrice > 0) || 
    (vehicles.private_price && parseFloat(vehicles.private_price) > 0);
    
  const hasSharablePrice = 
    (vehicles.prices && vehicles.prices.sharablePrice > 0) || 
    (vehicles.shared_price && parseFloat(vehicles.shared_price) > 0);
  
  console.log("hasPrivatePrice", hasPrivatePrice);
  console.log("hasSharablePrice", hasSharablePrice);

  // If no pricing options available, return null
  if (!hasPrivatePrice && !hasSharablePrice) return null;
  
  // Set default mode on first render
  useEffect(() => {
    // If no mode is selected yet, set a default
    if (!pricemode) {
      if (hasPrivatePrice) {
        setpricemode("Private");
      } else if (hasSharablePrice) {
        setpricemode("Sharable");
      }
    }
  }, [vehicles, pricemode, setpricemode, hasPrivatePrice, hasSharablePrice]);
  
  return (
    <Grid item xs={12} sm={6} md={3}>
      <FormControl fullWidth>
        <InputLabel id="price-mode-label">Price Mode</InputLabel>
        <Select
          labelId="price-mode-label"
          id="price-mode-select"
          value={pricemode}
          label="Price Mode"
          onChange={(e) => setpricemode(e.target.value)}
        >
          {hasPrivatePrice && (
            <MenuItem value="Private">Private</MenuItem>
          )}
          {hasSharablePrice && (
            <MenuItem value="Sharable">Sharable</MenuItem>
          )}
        </Select>
      </FormControl>
    </Grid>
  );
};

const VehicleListDropdown = ({ selectedVehicle, onVehicleChange }) => {
  const vehicles = useSelector((state) => state.pickupDrop.vehicles || []);
  const portZoneType = useSelector((state) => state.pickupDrop.portZoneType);
  const dispatch = useDispatch();
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  
  // Redux state for locations and times
  const entryPickup = useSelector((state) => state.pickupDrop.entrypickup);
  const entryDropoff = useSelector((state) => state.pickupDrop.entrydropoff);
  const pickupDate = useSelector((state) => state.pickupDrop.pickupdate);
  const entryTime = useSelector((state) => state.pickupDrop.entrytime);
  
  // Get existing services from Redux state
  const existingServices = useSelector((state) => state.tourPackages.AllServices || []);
  
  // Use optional chaining for safe access to nested properties
  const adultsMax = tourDetails?.data?.adult ?? 1;
  const childrenMax = tourDetails?.data?.child ?? 0;

  // Component state
  const [seatingCapacity, setSeatingCapacity] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const adultCount = useSelector((state) => state.pickupDrop.adultCount);
  const childCount = useSelector((state) => state.pickupDrop.childCount);
  
  // Modal states
  const [openSummaryModal, setOpenSummaryModal] = useState(false);
  const [summaryBookingIndex, setSummaryBookingIndex] = useState(null);
  
  // Use a ref to store bookings to prevent them from being lost during re-renders
  const bookingsRef = useRef([
    { 
      id: `entry-${Date.now()}`, // Use unique ID with timestamp and type prefix
      vehicle: null, 
      vehicleData: null, 
      priceMode: null,
      isComplete: false,
      adults: adultCount || 1,
      children: childCount || 0
    }
  ]);
  
  // State to trigger re-renders when bookings change
  const [bookingsVersion, setBookingsVersion] = useState(0);
  
  // Getter for bookings that reads from the ref
  const getBookings = () => bookingsRef.current;
  
  // Setter for bookings that updates the ref and triggers a re-render
  const setBookings = (newBookings) => {
    // Check if the bookings array has actually changed before updating
    const currentBookings = bookingsRef.current;
    if (JSON.stringify(currentBookings) !== JSON.stringify(newBookings)) {
      bookingsRef.current = newBookings;
      setBookingsVersion(prev => prev + 1); // Trigger re-render
    }
  };
  
  // Handle passenger count changes for a specific booking
  const handleBookingPassengerChange = (bookingIndex, type, count) => {
    const bookings = getBookings();
    const updatedBookings = [...bookings];
    
    if (updatedBookings[bookingIndex]) {
      updatedBookings[bookingIndex] = {
        ...updatedBookings[bookingIndex],
        [type]: count
      };
      setBookings(updatedBookings);
      
      // Also update Redux state for the first booking only
      if (bookingIndex === 0) {
        // Here you would dispatch an action to update Redux state
        // For example: dispatch(updatePassengerCount({type, count}))
      }
    }
  };
  
  // Wrapper for the Passenger component that connects it to a specific booking
  const handleAdultChange = (bookingIndex, count) => {
    handleBookingPassengerChange(bookingIndex, 'adults', count);
  };
  
  const handleChildChange = (bookingIndex, count) => {
    handleBookingPassengerChange(bookingIndex, 'children', count);
  };
  
  // Add a new booking
  const handleAddMoreBooking = () => {
    const bookings = getBookings();
    const newBooking = { 
      id: `entry-${Date.now()}`, // Use timestamp for unique ID with type prefix
      vehicle: null, 
      vehicleData: null, 
      priceMode: null,
      isComplete: false,
      adults: adultCount || 1,
      children: childCount || 0
    };
    setBookings([...bookings, newBooking]);
  };
  
  // Filter vehicles that have at least one pricing mode
  const filteredVehicles = vehicles.filter(vehicle => {
    const hasDmcPrice = vehicle.dmc_private_price > 0 || vehicle.dmc_sharable_price > 0;
    const hasTravclicksPrice = vehicle.trav_private_price > 0 || vehicle.trav_sharable_price > 0;
    return hasDmcPrice || hasTravclicksPrice;
  });
  
  // Find selected vehicle object for the primary booking
  const selectedVehicleObj = selectedVehicle ? 
    filteredVehicles.find(v => v.id === selectedVehicle) : null;
  
  // Update current booking when selected vehicle changes from parent
  useEffect(() => {
    if (selectedVehicleObj && !bookingsRef.current[0].vehicle) {
      const currentBookings = getBookings();
      const updatedBookings = [...currentBookings];
      updatedBookings[0] = {
        ...updatedBookings[0],
        vehicle: selectedVehicleObj
      };
      setBookings(updatedBookings);
      handleVehicleSelect(selectedVehicleObj, 0);
    }
  }, [selectedVehicleObj]);
  
  // Update completion status when relevant data changes
  useEffect(() => {
    const bookings = getBookings();
    let needsUpdate = false;
    
    console.log("Entry Vehicle - Checking completion status for bookings:", bookings);
    console.log("Entry Vehicle - Required fields:", {
      entryPickup,
      entryDropoff,
      pickupDate,
      entryTime
    });
    
    const updatedBookings = bookings.map(booking => {
      // Check if all required fields are present
      const isComplete = 
        booking.vehicle !== null && 
        booking.vehicleData !== null && 
        booking.priceMode !== null && booking.priceMode !== '' &&
        entryPickup !== null && entryPickup !== undefined && entryPickup !== '' &&
        entryDropoff !== null && entryDropoff !== undefined && entryDropoff !== '' &&
        pickupDate !== null && pickupDate !== undefined && pickupDate !== '' &&
        entryTime !== null && entryTime !== undefined && entryTime !== '';
      
      console.log(`Entry Vehicle - Booking ${booking.id} isComplete:`, isComplete);
      
      if (isComplete !== booking.isComplete) {
        needsUpdate = true;
        return { ...booking, isComplete };
      }
      return booking;
    });
    
    if (needsUpdate) {
      // Update bookings ref directly without using setBookings
      bookingsRef.current = updatedBookings;
      
      // Trigger re-render
      setBookingsVersion(prev => prev + 1);
      
      // Dispatch completed bookings to Redux
      const completedBookings = updatedBookings.filter(booking => booking.isComplete);
      console.log("Entry Vehicle - Completed bookings:", completedBookings);
      
      if (completedBookings.length > 0) {
        // Format the bookings for setAllServices
        const bookingsForRedux = completedBookings.map(booking => {
          const vehicle = booking.vehicle;
          const vehicleData = booking.vehicleData;
          const bookingAdultCount = booking.adults || adultCount;
          const bookingChildCount = booking.children || childCount;
          const totalGuests = bookingAdultCount + bookingChildCount;
          
          // Calculate price based on price mode
          const price = booking.priceMode === "Sharable"
            ? (vehicleData.prices && vehicleData.prices.sharablePrice 
                ? vehicleData.prices.sharablePrice * totalGuests 
                : (vehicleData.shared_price ? parseFloat(vehicleData.shared_price) * totalGuests : 0))
            : (vehicleData.prices && vehicleData.prices.privatePrice 
                ? vehicleData.prices.privatePrice 
                : (vehicleData.private_price ? parseFloat(vehicleData.private_price) : 0));
          
          return {
            id: booking.id, // Include the unique ID
            type: "Entry Port",
            vehicleName: vehicle.vehicle_name,
            vehicleType: vehicle.vehicle_type,
            vehicleModel: vehicle.vehicle_model,
            modelYear: vehicle.model_year,
            seatingCapacity: vehicle.seating_capacity,
            vehicleImage: vehicle.image,
            city: vehicle.city,
            country: vehicle.country,
            pickupLocation: entryPickup,
            dropoffLocation: entryDropoff,
            pickupDate: pickupDate,
            pickupTime: entryTime,
            adults: bookingAdultCount,
            children: bookingChildCount,
            price: price,
            taxPercentage: vehicle.tax_percentage,
            priceMode: booking.priceMode,
            mode: booking.mode,
            dmcId: booking.dmcId,
            vehicleId: vehicle.id
          };
        });
        
        console.log("Entry Vehicle - Formatted bookings for Redux:", bookingsForRedux);
        
        // Create a map of existing services by ID for faster lookup
        const existingServicesMap = {};
        existingServices.forEach(service => {
          if (service.id) {
            existingServicesMap[service.id] = service;
          }
        });
        
        // First, filter out any existing Entry Port bookings
        const nonEntryPortServices = existingServices.filter(service => service.type !== "Entry Port");
        
        // Then, filter out any Exit Port services that have the same IDs as our bookings
        const finalServices = [...nonEntryPortServices];
        
        // Add the new Entry Port bookings
        bookingsForRedux.forEach(booking => {
          // Only add if it doesn't already exist
          if (!existingServicesMap[booking.id]) {
            finalServices.push(booking);
          }
        });
        
        console.log("Entry Vehicle - Dispatching finalServices to Redux:", finalServices);
        // Dispatch to Redux with the properly filtered services
        dispatch(setAllServices(finalServices));
      }
    }
  }, [entryPickup, entryDropoff, pickupDate, entryTime, adultCount, childCount, existingServices, dispatch]);
  
  // Handle vehicle selection
  const handleVehicleSelect = (vehicle, bookingIndex) => {
    if (!vehicle) return;
    
    const hasDmcPrice = vehicle.dmc_private_price > 0 || vehicle.dmc_sharable_price > 0;
    const hasTravclicksPrice = vehicle.trav_private_price > 0 || vehicle.trav_sharable_price > 0;
    
    const mode = (hasDmcPrice && !hasTravclicksPrice) ? "dmc" : "travclicks";
    const dmcId = (hasDmcPrice && !hasTravclicksPrice) ? vehicle.dmc_id : vehicle.travclicks_dmc_id;
    
    // Always call the parent's onVehicleChange with the latest selection
    // regardless of booking index
    if (onVehicleChange) {
      onVehicleChange(vehicle.id, mode, dmcId, vehicle.city, vehicle.country, bookingIndex);
    }
    
    // Update the local bookings state with the selected vehicle
    const bookings = getBookings();
    const updatedBookings = [...bookings];
    updatedBookings[bookingIndex] = {
      ...updatedBookings[bookingIndex],
      vehicle: vehicle,
      mode: mode,
      dmcId: dmcId
    };
    setBookings(updatedBookings);
    
    // Fetch vehicle details
    setIsLoading(true);
    setError(null);
    
    dispatch(fetchVehicleDetails({ city: vehicle.city, country: vehicle.country, type: portZoneType }))
      .unwrap()
      .then((data) => {
        setSeatingCapacity(data.seating_capacity || 0);
        
        // Update the booking with the fetched data
        const currentBookings = getBookings();
        const updatedBookings = [...currentBookings];
        updatedBookings[bookingIndex] = {
          ...updatedBookings[bookingIndex],
          vehicleData: data
        };
        
        // Check if all conditions for isComplete are met
        const booking = updatedBookings[bookingIndex];
        const isComplete = 
          booking.vehicle !== null && 
          data !== null && 
          booking.priceMode !== null && booking.priceMode !== '' &&
          entryPickup !== null && entryPickup !== undefined && entryPickup !== '' &&
          entryDropoff !== null && entryDropoff !== undefined && entryDropoff !== '' &&
          pickupDate !== null && pickupDate !== undefined && pickupDate !== '' &&
          entryTime !== null && entryTime !== undefined && entryTime !== '';
          
        // Update completion status
        updatedBookings[bookingIndex].isComplete = isComplete;
        
        // Save updated bookings and trigger re-render
        setBookings(updatedBookings);
        setIsLoading(false);
      })
      .catch((err) => {
        console.error("Error fetching vehicle details:", err);
        setError(err.message || "Failed to load vehicle details");
        setIsLoading(false);
      });
  };
  
  // Handle price mode selection - directly update Redux after price mode selection
  const handlePriceModeSelect = (value, bookingIndex) => {
    if (!value) return;
    
    const bookings = getBookings();
    const updatedBookings = [...bookings];
    updatedBookings[bookingIndex] = {
      ...updatedBookings[bookingIndex],
      priceMode: value
    };
    setBookings(updatedBookings);
    
    // Force check completion status after price mode change
    const currentBookings = getBookings();
    const hasAllRequiredFields = 
      currentBookings[bookingIndex].vehicle !== null && 
      currentBookings[bookingIndex].vehicleData !== null && 
      value !== null && value !== '' &&
      entryPickup !== null && entryPickup !== undefined && entryPickup !== '' &&
      entryDropoff !== null && entryDropoff !== undefined && entryDropoff !== '' &&
      pickupDate !== null && pickupDate !== undefined && pickupDate !== '' &&
      entryTime !== null && entryTime !== undefined && entryTime !== '';
      
    if (hasAllRequiredFields !== currentBookings[bookingIndex].isComplete) {
      updatedBookings[bookingIndex] = {
        ...updatedBookings[bookingIndex],
        isComplete: hasAllRequiredFields
      };
      bookingsRef.current = updatedBookings;
      setBookingsVersion(prev => prev + 1);
      
      // Directly dispatch to Redux after price mode is selected and all fields are filled
      if (hasAllRequiredFields) {
        dispatchBookingToRedux(bookingIndex);
      }
    }
  };
  
  // Add a function to directly dispatch a specific booking to Redux
  const dispatchBookingToRedux = (bookingIndex) => {
    const bookings = getBookings();
    const booking = bookings[bookingIndex];
    
    if (!booking || !booking.vehicle || !booking.vehicleData) {
      console.error("Cannot dispatch incomplete booking to Redux", booking);
      return;
    }
    
    console.log("Entry Vehicle - Directly dispatching booking to Redux:", booking);
    
    const vehicle = booking.vehicle;
    const vehicleData = booking.vehicleData;
    const bookingAdultCount = booking.adults || adultCount;
    const bookingChildCount = booking.children || childCount;
    const totalGuests = bookingAdultCount + bookingChildCount;
    
    // Calculate price based on price mode
    const price = booking.priceMode === "Sharable"
      ? (vehicleData.prices && vehicleData.prices.sharablePrice 
          ? vehicleData.prices.sharablePrice * totalGuests 
          : (vehicleData.shared_price ? parseFloat(vehicleData.shared_price) * totalGuests : 0))
      : (vehicleData.prices && vehicleData.prices.privatePrice 
          ? vehicleData.prices.privatePrice 
          : (vehicleData.private_price ? parseFloat(vehicleData.private_price) : 0));
    
    const bookingForRedux = {
      id: booking.id,
      type: "Entry Port",
      vehicleName: vehicle.vehicle_name,
      vehicleType: vehicle.vehicle_type,
      vehicleModel: vehicle.vehicle_model,
      modelYear: vehicle.model_year,
      seatingCapacity: vehicle.seating_capacity,
      vehicleImage: vehicle.image,
      city: vehicle.city,
      country: vehicle.country,
      pickupLocation: entryPickup,
      dropoffLocation: entryDropoff,
      pickupDate: pickupDate,
      pickupTime: entryTime,
      adults: bookingAdultCount,
      children: bookingChildCount,
      price: price,
      taxPercentage: vehicle.tax_percentage,
      priceMode: booking.priceMode,
      mode: booking.mode,
      dmcId: booking.dmcId,
      vehicleId: vehicle.id
    };
    
    console.log("Entry Vehicle - Formatted booking for Redux:", bookingForRedux);
    
    // Clone the existing services array
    const allCurrentServices = [...existingServices];
    
    // First, filter out any existing Entry Port bookings with this ID
    const filteredServices = allCurrentServices.filter(
      service => !(service.type === "Entry Port" && service.id === booking.id)
    );
    
    // Add the new booking
    filteredServices.push(bookingForRedux);
    
    console.log("Entry Vehicle - Dispatching updated services to Redux:", filteredServices);
    
    // Dispatch the updated services
    dispatch(setAllServices(filteredServices));
  };
  
  // Modify handleOpenSummaryModal to ensure data is in Redux before showing modal
  const handleOpenSummaryModal = (index) => {
    // Make sure the data is in Redux before showing the modal
    dispatchBookingToRedux(index);
    
    setSummaryBookingIndex(index);
    setOpenSummaryModal(true);
  };
  
  // Close the summary modal
  const handleCloseSummaryModal = () => {
    setOpenSummaryModal(false);
    setSummaryBookingIndex(null);
  };
  
  // Generate booking summary data for the modal
  const getBookingSummary = (bookingIndex) => {
    const bookings = getBookings();
    const booking = bookings[bookingIndex];
    if (!booking || !booking.vehicle || !booking.vehicleData || !booking.isComplete) return null;
    
    const vehicle = booking.vehicle;
    const vehicleData = booking.vehicleData;
    const bookingAdultCount = booking.adults || adultCount;
    const bookingChildCount = booking.children || childCount;
    const totalGuests = bookingAdultCount + bookingChildCount;
    
    // Calculate price based on price mode
    const price = booking.priceMode === "Sharable"
      ? (vehicleData.prices && vehicleData.prices.sharablePrice 
          ? vehicleData.prices.sharablePrice * totalGuests 
          : (vehicleData.shared_price ? parseFloat(vehicleData.shared_price) * totalGuests : 0))
      : (vehicleData.prices && vehicleData.prices.privatePrice 
          ? vehicleData.prices.privatePrice 
          : (vehicleData.private_price ? parseFloat(vehicleData.private_price) : 0));
    
    return {
      vehicleName: vehicle.vehicle_name,
      vehicleType: vehicle.vehicle_type,
      vehicleModel: vehicle.vehicle_model,
      modelYear: vehicle.model_year,
      seatingCapacity: vehicle.seating_capacity,
      vehicleImage: vehicle.image,
      city: vehicle.city,
      country: vehicle.country,
      pickupLocation: entryPickup,
      dropoffLocation: entryDropoff,
      pickupDate: pickupDate,
      pickupTime: entryTime,
      adults: bookingAdultCount,
      children: bookingChildCount,
      price: price,
      taxPercentage: vehicle.tax_percentage,
      priceMode: booking.priceMode,
      mode: booking.mode
    };
  };

  // Get current bookings from ref
  const bookings = getBookings();

  return (
    <>
      <Grid container spacing={2} sx={{ mt: 3 }}>
        <Grid item xs={12}>
          <Typography variant="h6" gutterBottom>
            Select Entry Vehicle
          </Typography>
        </Grid>
        
        {bookings.map((booking, index) => (
          <Grid container spacing={2} key={booking.id} sx={{ 
            mt: index > 0 ? 3 : 0, 
            mb: 3,
            p: 2,
            mx: 0.1,
            borderRadius: 1,
            bgcolor: 'background.paper',
            boxShadow: 1
          }}>
            {index > 0 && (
              <Grid item xs={12}>
                <Typography variant="subtitle1" gutterBottom fontWeight="500">
                  Additional Entry Vehicle #{index + 1}
                </Typography>
              </Grid>
            )}
            
            <Grid item xs={12} sm={6} md={3}>
              <Autocomplete
                value={booking.vehicle}
                onChange={(event, newValue) => handleVehicleSelect(newValue, index)}
                options={filteredVehicles}
                getOptionLabel={(option) => option?.vehicle_name || ''}
                isOptionEqualToValue={(option, value) => {
                  if (!option || !value) return false;
                  return option.id === value.id;
                }}
                noOptionsText="No vehicles with valid pricing available"
                renderOption={(props, option) => {
                  // Extract key from props to handle it separately
                  const { key, ...otherProps } = props;
                  
                  return (
                    <CustomTooltip
                      key={option.id} // Use option.id as key for the tooltip
                      title={<TooltipContent vehicle={option} />}
                      placement="right"
                      arrow
                    >
                      <Box component="li" key={key} {...otherProps}>
                        {option.vehicle_name}
                        <Box sx={{ ml: 'auto', display: 'flex', gap: 0.5 }}>
                          {(option.dmc_private_price > 0 || option.dmc_sharable_price > 0) && (
                            <Chip 
                              key={`dmc-${option.id}`}
                              size="small" 
                              label="DMC"
                              sx={{ 
                                height: 20,
                                fontSize: '0.7rem',
                                bgcolor: 'rgba(25, 118, 210, 0.08)',
                                color: 'primary.main'
                              }}
                            />
                          )}
                          {(option.trav_private_price > 0 || option.trav_sharable_price > 0) && (
                            <Chip 
                              key={`travclicks-${option.id}`}
                              size="small" 
                              label="Travclicks"
                              sx={{ 
                                height: 20,
                                fontSize: '0.7rem',
                                bgcolor: 'rgba(76, 175, 80, 0.08)',
                                color: '#2e7d32'
                              }}
                            />
                          )}
                        </Box>
                      </Box>
                    </CustomTooltip>
                  );
                }}
                renderInput={(params) => (
                  <TextField
                    {...params}
                    label={`Select Entry Vehicle ${index > 0 ? '#' + (index + 1) : ''}`}
                    fullWidth
                  />
                )}
              />
            </Grid>
            
            {/* Use existing Passenger component with custom props for this booking */}
            <Passenger 
              adultsMax={adultsMax} 
              childrenMax={childrenMax} 
              seatingCapacity={seatingCapacity}
              initialAdults={booking.adults || 1}
              initialChildren={booking.children || 0}
              onAdultChange={(count) => handleAdultChange(index, count)}
              onChildChange={(count) => handleChildChange(index, count)}
            />
            
            {booking.vehicleData && (
              <Grid item xs={12} sm={6} md={3}>
                <FormControl fullWidth>
                  <InputLabel id={`price-mode-label-entry-${index}`}>Price Mode</InputLabel>
                  <Select
                    labelId={`price-mode-label-entry-${index}`}
                    id={`price-mode-select-entry-${index}`}
                    value={booking.priceMode || ''}
                    label="Price Mode"
                    onChange={(e) => handlePriceModeSelect(e.target.value, index)}
                  >
                    {((booking.vehicleData.prices && booking.vehicleData.prices.privatePrice > 0) || 
                      (booking.vehicleData.private_price && parseFloat(booking.vehicleData.private_price) > 0)) && (
                      <MenuItem value="Private">Private</MenuItem>
                    )}
                    {((booking.vehicleData.prices && booking.vehicleData.prices.sharablePrice > 0) || 
                      (booking.vehicleData.shared_price && parseFloat(booking.vehicleData.shared_price) > 0)) && (
                      <MenuItem value="Sharable">Sharable</MenuItem>
                    )}
                  </Select>
                </FormControl>
              </Grid>
            )}
            
            <Grid item xs={12}>
              <Box sx={{ display: 'flex', gap: 2, mt: 2 }}>
                <Button
                  variant="contained"
                  color="primary"
                  disabled={!booking.isComplete}
                  onClick={() => handleOpenSummaryModal(index)}
                  fullWidth
                >
                  View Entry Summary
                </Button>
              </Box>
            </Grid>
          </Grid>
        ))}
        
        <Grid item xs={12}>
          <Button
            variant="outlined"
            color="primary"
            onClick={handleAddMoreBooking}
            fullWidth
          >
            Add More Booking
          </Button>
        </Grid>
      </Grid>
      
      {/* Booking Summary Modal */}
      <PortSummaryModal
        open={openSummaryModal}
        onClose={handleCloseSummaryModal}
        bookingData={summaryBookingIndex !== null ? getBookingSummary(summaryBookingIndex) : null}
        portType="Entry Port"
      />
    </>
  );
};

export default VehicleListDropdown;




