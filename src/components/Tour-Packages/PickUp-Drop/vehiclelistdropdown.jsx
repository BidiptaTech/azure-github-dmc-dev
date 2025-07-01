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
  Button,
  IconButton,
  Card,
  CardContent
} from '@mui/material';
import { useSelector } from "react-redux";
import LocationOnIcon from '@mui/icons-material/LocationOn';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import EventSeatIcon from '@mui/icons-material/EventSeat';
import DeleteIcon from '@mui/icons-material/Delete';
import FlightLandIcon from '@mui/icons-material/FlightLand';
import AddIcon from '@mui/icons-material/Add';
import VisibilityIcon from '@mui/icons-material/Visibility';
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

const VehicleListDropdown = ({ selectedVehicle, onVehicleChange, entryPorts }) => {
  const vehicles = useSelector((state) => state.pickupDrop.vehicles || []);
  const portZoneType = useSelector((state) => state.pickupDrop.portZoneType);
  const dispatch = useDispatch();
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  console.log("entryPorts", entryPorts);
  // Redux state for locations and times
  const entryPickup = useSelector((state) => state.pickupDrop.entrypickup);
  const entryDropoff = useSelector((state) => state.pickupDrop.entrydropoff);
  const pickupDate = useSelector((state) => state.pickupDrop.pickupdate);
  const entryTime = useSelector((state) => state.pickupDrop.entrytime);
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  
  
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
  
  // Initialize bookings from entryPorts data or default empty booking
  const initializeBookings = () => {
    if (entryPorts && entryPorts.length > 0) {
      // Pre-populate with existing entry port data
      return entryPorts.map((entryPort, index) => {
        const entryData = entryPort.data?.[0];
        if (!entryData) {
          // Fallback to default booking if no data
          return {
            id: `entry-${Date.now()}-${index}`,
            vehicle: null,
            vehicleData: null,
            priceMode: null,
            isComplete: false,
            adults: adultCount || 1,
            children: childCount || 0
          };
        }
        
        // Find the corresponding vehicle from the vehicles list
        const matchingVehicle = vehicles.find(v => v.id === entryData.vehicles_id);
        
        return {
          id: entryData.id,
          vehicle: matchingVehicle || {
            id: entryData.vehicles_id,
            vehicle_name: entryData.vehicles_name,
            vehicle_type: entryData.vehicle_type,
            vehicle_model: entryData.vehicle_model,
            model_year: entryData.model_year,
            seating_capacity: entryData.seating_capacity,
            image: entryData.image,
            city: entryData.city,
            country: entryData.country,
            dmc_id: entryData.dmc_id
          },
          vehicleData: {
            // Map the price mode to expected structure
            private_price: entryData.type === 'private' ? entryData.totalPrice : 0,
            shared_price: entryData.type === 'shared' ? entryData.totalPrice : 0,
            prices: {
              privatePrice: entryData.type === 'private' ? entryData.totalPrice : 0,
              sharablePrice: entryData.type === 'shared' ? entryData.totalPrice : 0
            }
          },
          priceMode: entryData.type === 'shared' ? 'Sharable' : 'Private',
          isComplete: true, // Mark as complete since it's loaded data
          adults: entryData.adults || 1,
          children: entryData.children || 0,
          mode: entryData.Mode || 'dmc',
          dmcId: entryData.dmc_id,
          entrypickup:entryData.entrypickup,
          entrydropoff:entryData.entrydropoff,
          bookingDate:entryData.bookingDate,
          pickupdate:entryData.pickupdate,
          entrytime:entryData.entrytime,
          // Store original loaded data for reference
          originalData: entryData
        };
      });
    }
    
    // Default empty booking when no entryPorts data
    return [{
      id: `entry-${Date.now()}`,
      vehicle: null,
      vehicleData: null,
      priceMode: null,
      isComplete: false,
      adults: adultCount || 1,
      children: childCount || 0
    }];
  };

  // Use a ref to store bookings to prevent them from being lost during re-renders
  const bookingsRef = useRef(initializeBookings());

  // Debug log initial bookings
  useEffect(() => {
    console.log("Entry Vehicle - Initial bookings state:", bookingsRef.current);
    if (entryPorts && entryPorts.length > 0) {
      console.log("Entry Vehicle - Loading with existing entryPorts data");
    } else {
      console.log("Entry Vehicle - Loading with default empty booking");
    }
  }, []);
  
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
  
  // Remove a booking from both local state and Redux state
  const handleRemoveBooking = (indexToRemove) => {
    const bookings = getBookings();
    const bookingToRemove = bookings[indexToRemove];
    
    if (bookingToRemove) {
      console.log("Entry Vehicle - Removing booking:", bookingToRemove);
      
      // Remove from local state
      const updatedBookings = bookings.filter((_, index) => index !== indexToRemove);
      setBookings(updatedBookings);
      
      // Remove from Redux state if it has booking data
      if (bookingToRemove.id || (bookingToRemove.vehicle && bookingToRemove.vehicle.id)) {
        // Clone the existing services array
        const currentServices = [...existingServices];
        
        // Filter out entry_port services that contain this booking
        const filteredServices = currentServices.filter(service => {
          // Check if this is an entry_port service
          if (service.type === "entry_port") {
            // Check if this service contains data that matches our booking
            if (service.data && Array.isArray(service.data)) {
              // Remove this service if any of its data matches our booking
              const hasMatchingData = service.data.some(dataItem => {
                // Match by ID first (most reliable)
                if (bookingToRemove.id && dataItem.id === bookingToRemove.id) {
                  return true;
                }
                
                // Match by vehicle ID as fallback
                if (bookingToRemove.vehicle && 
                    dataItem.vehicles_id === bookingToRemove.vehicle.id) {
                  return true;
                }
                
                return false;
              });
              
              // If this service has matching data, filter it out
              return !hasMatchingData;
            }
          }
          
          // Keep all other services
          return true;
        });
        
        // Only dispatch if there's an actual change
        if (filteredServices.length !== currentServices.length) {
          console.log("Entry Vehicle - Removing booking from Redux:", bookingToRemove);
          console.log("Entry Vehicle - Updated services:", filteredServices);
          dispatch(setAllServices(filteredServices));
        }
      }
    }
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
  
  // Function to dispatch initialized bookings to Redux state
  const dispatchInitializedBookingsToRedux = (bookings) => {
    const completedBookings = bookings.filter(booking => booking.isComplete);
    
    if (completedBookings.length > 0) {
      console.log("Entry Vehicle - Dispatching initialized bookings to Redux:", completedBookings);
      
      // Format bookings for Redux state
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
        
        // Find any existing customer info in current services
        const customerInfoService = existingServices.find(service => service.type === 'CustomerInfo');
        
        // Check if this is a loaded booking or a new booking
        const isLoadedBooking = booking.originalData !== undefined;
        
        // Get location and timing data based on booking type
        const locationData = isLoadedBooking ? {
          entrypickup: booking.originalData.entrypickup,
          entrydropoff: booking.originalData.entrydropoff,
          bookingDate: booking.originalData.pickupdate,
          pickupdate: booking.originalData.pickupdate,
          entrytime: booking.originalData.entrytime
        } : {
          entrypickup: entryPickup,
          entrydropoff: entryDropoff,
          bookingDate: pickupDate,
          pickupdate: pickupDate,
          entrytime: entryTime
        };

        // Create booking data in the same format as currently used
        const bookingData = {
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
          } : {}),
          
          // Core booking details with correct location and timing data
          id: booking.id,
          vehicles_id: vehicle.id,
          image: vehicle.image,
          dmc_id: booking.dmcId,
          vehicles_name: vehicle.vehicle_name,
          Mode: booking.mode,
          type: booking.priceMode === "Sharable" ? "shared" : "private",
          ...locationData, // Use the correct location and timing data
          PickupPlaceid: booking.PickupPlaceid || null,
          DropoffPlaceid: booking.DropoffPlaceid || null,
          adults: bookingAdultCount,
          children: bookingChildCount,
          totalPrice: Math.ceil(price),
          Tax: vehicle.tax_percentage,
          distance: vehicle.distance || vehicleData.$distanceInKM || booking.originalData?.distance || null,
          Night_Start_Time: vehicle.night_start_time || vehicleData.Night_Start_Time || null,
          Night_End_Time: vehicle.night_end_time || vehicleData.Night_End_Time || null,
          city: vehicle.city,
          country: vehicle.country,
          vehicle_type: vehicle.vehicle_type,
          vehicle_model: vehicle.vehicle_model,
          model_year: vehicle.model_year,
          seating_capacity: vehicle.seating_capacity
        };
        
        return {
          type: "entry_port",
          agent_id: agentId,
          tour_id: tourId,
          data: [bookingData]
        };
      });
      
      // Remove any existing Entry Port services and add the new ones
      const filteredServices = existingServices.filter(service => service.type !== "entry_port");
      const finalServices = [...filteredServices, ...bookingsForRedux];
      
      console.log("Entry Vehicle - Dispatching initialized services to Redux:", finalServices);
      dispatch(setAllServices(finalServices));
    }
  };

  // Re-initialize bookings when entryPorts prop changes
  useEffect(() => {
    if (entryPorts && entryPorts.length > 0 && vehicles.length > 0) {
      console.log("Entry Vehicle - Detected entryPorts data, re-initializing bookings:", entryPorts);
      
      // Re-initialize bookings with the latest entryPorts and vehicles data
      const newBookings = entryPorts.map((entryPort, index) => {
        const entryData = entryPort.data?.[0];
        if (!entryData) {
          return {
            id: `entry-${Date.now()}-${index}`,
            vehicle: null,
            vehicleData: null,
            priceMode: null,
            isComplete: false,
            adults: adultCount || 1,
            children: childCount || 0
          };
        }
        
        // Find the corresponding vehicle from the vehicles list
        const matchingVehicle = vehicles.find(v => v.id === entryData.vehicles_id);
        
        return {
          id: entryData.id,
          vehicle: matchingVehicle || {
            id: entryData.vehicles_id,
            vehicle_name: entryData.vehicles_name,
            vehicle_type: entryData.vehicle_type,
            vehicle_model: entryData.vehicle_model,
            model_year: entryData.model_year,
            seating_capacity: entryData.seating_capacity,
            image: entryData.image,
            city: entryData.city,
            country: entryData.country,
            dmc_id: entryData.dmc_id
          },
          vehicleData: {
            private_price: entryData.type === 'private' ? entryData.totalPrice : 0,
            shared_price: entryData.type === 'shared' ? entryData.totalPrice : 0,
            prices: {
              privatePrice: entryData.type === 'private' ? entryData.totalPrice : 0,
              sharablePrice: entryData.type === 'shared' ? entryData.totalPrice : 0
            },
            $distanceInKM: entryData.distance || null
          },
          priceMode: entryData.type === 'shared' ? 'Sharable' : 'Private',
          isComplete: true,
          adults: entryData.adults || 1,
          children: entryData.children || 0,
          mode: entryData.Mode || 'dmc',
          dmcId: entryData.dmc_id,
          originalData: entryData
        };
      });
      
      // Store in local state
      bookingsRef.current = newBookings;
      setBookingsVersion(prev => prev + 1);
      
      console.log("Entry Vehicle - Initialized bookings from entryPorts:", newBookings);
      
      // Also store in Redux state in the same format
      dispatchInitializedBookingsToRedux(newBookings);
    }
  }, [entryPorts, vehicles, adultCount, childCount, existingServices, dispatch, agentId, tourId, entryPickup, entryDropoff, pickupDate, entryTime]);

  // Update current booking when selected vehicle changes from parent (only if no loaded data)
  useEffect(() => {
    if (selectedVehicleObj && !bookingsRef.current[0].vehicle && (!entryPorts || entryPorts.length === 0)) {
      const currentBookings = getBookings();
      const updatedBookings = [...currentBookings];
      updatedBookings[0] = {
        ...updatedBookings[0],
        vehicle: selectedVehicleObj
      };
      setBookings(updatedBookings);
      handleVehicleSelect(selectedVehicleObj, 0);
    }
  }, [selectedVehicleObj, entryPorts]);
  
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
      // For loaded bookings, check if they have originalData (indicating they were loaded from entryPorts)
      const isLoadedBooking = booking.originalData !== undefined;
      
      let isComplete;
      if (isLoadedBooking) {
        // For loaded bookings, check if the booking itself has all required data
        isComplete = 
          booking.vehicle !== null && 
          booking.vehicleData !== null && 
          booking.priceMode !== null && booking.priceMode !== '' &&
          booking.originalData.entrypickup !== null && booking.originalData.entrypickup !== undefined && booking.originalData.entrypickup !== '' &&
          booking.originalData.entrydropoff !== null && booking.originalData.entrydropoff !== undefined && booking.originalData.entrydropoff !== '' &&
          booking.originalData.pickupdate !== null && booking.originalData.pickupdate !== undefined && booking.originalData.pickupdate !== '' &&
          booking.originalData.entrytime !== null && booking.originalData.entrytime !== undefined && booking.originalData.entrytime !== '';
      } else {
        // For new bookings, check Redux state values
        isComplete = 
          booking.vehicle !== null && 
          booking.vehicleData !== null && 
          booking.priceMode !== null && booking.priceMode !== '' &&
          entryPickup !== null && entryPickup !== undefined && entryPickup !== '' &&
          entryDropoff !== null && entryDropoff !== undefined && entryDropoff !== '' &&
          pickupDate !== null && pickupDate !== undefined && pickupDate !== '' &&
          entryTime !== null && entryTime !== undefined && entryTime !== '';
      }
      
      console.log(`Entry Vehicle - Booking ${booking.id} isComplete:`, isComplete, `(loaded: ${isLoadedBooking})`);
      
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
      
      // Dispatch newly completed bookings to Redux
      const completedBookings = updatedBookings.filter(booking => booking.isComplete);
      console.log("Entry Vehicle - Completed bookings:", completedBookings);
      
      // For each newly completed booking, dispatch to Redux individually
      completedBookings.forEach((booking, index) => {
        // Find the actual index of this booking in the bookings array
        const actualIndex = updatedBookings.findIndex(b => b.id === booking.id);
        if (actualIndex !== -1) {
          // Check if this booking was just completed (isComplete changed from false to true)
          const originalBooking = bookings.find(b => b.id === booking.id);
          if (originalBooking && !originalBooking.isComplete && booking.isComplete) {
            console.log("Entry Vehicle - Newly completed booking, dispatching to Redux:", booking.id);
            dispatchBookingToRedux(actualIndex, true); // Force update for newly completed bookings
          }
        }
      });
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
        dispatchBookingToRedux(bookingIndex, true); // Force update since this is a new completion
      }
    }
  };
  
  // Add a function to directly dispatch a specific booking to Redux
  const dispatchBookingToRedux = (bookingIndex, forceUpdate = false) => {
    const bookings = getBookings();
    const booking = bookings[bookingIndex];
    
    if (!booking || !booking.vehicle || !booking.vehicleData) {
      console.error("Cannot dispatch incomplete booking to Redux", booking);
      return;
    }
    
    // Check if this booking is already in Redux state to prevent duplicates
    if (!forceUpdate) {
      const existingBooking = existingServices.find(service => 
        service.type === "entry_port" && 
        service.data && 
        service.data.some(item => item.id === booking.id)
      );
      
      if (existingBooking) {
        console.log("Entry Vehicle - Booking already exists in Redux, skipping dispatch:", booking.id);
        return;
      }
    }
    
    console.log("Entry Vehicle - Directly dispatching booking to Redux:", booking);
    
    const vehicle = booking.vehicle;
    const vehicleData = booking.vehicleData;
    const bookingAdultCount = booking.adults || adultCount;
    const bookingChildCount = booking.children || childCount;
    const totalGuests = bookingAdultCount + bookingChildCount;
    
    // Check if this is a loaded booking or a new booking
    const isLoadedBooking = booking.originalData !== undefined;
    
    // Get location and timing data based on booking type
    const locationData = isLoadedBooking ? {
      entrypickup: booking.originalData.entrypickup,
      entrydropoff: booking.originalData.entrydropoff,
      bookingDate: booking.originalData.pickupdate,
      pickupdate: booking.originalData.pickupdate,
      entrytime: booking.originalData.entrytime
    } : {
      entrypickup: entryPickup,
      entrydropoff: entryDropoff,
      bookingDate: pickupDate,
      pickupdate: pickupDate,
      entrytime: entryTime
    };
    
    // Calculate price based on price mode
    const price = booking.priceMode === "Sharable"
      ? (vehicleData.prices && vehicleData.prices.sharablePrice 
          ? vehicleData.prices.sharablePrice * totalGuests 
          : (vehicleData.shared_price ? parseFloat(vehicleData.shared_price) * totalGuests : 0))
      : (vehicleData.prices && vehicleData.prices.privatePrice 
          ? vehicleData.prices.privatePrice 
          : (vehicleData.private_price ? parseFloat(vehicleData.private_price) : 0));
    
    // Find any existing customer info in current services
    const customerInfoService = existingServices.find(service => service.type === 'CustomerInfo');
    
    // Create booking data matching the exact parameter names from index1.jsx details object
    const bookingData = {
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
      } : {}),
      
      // Core booking details with correct location and timing data
      vehicles_id: vehicle.id,
      image: vehicle.image,
      dmc_id: booking.dmcId,
      vehicles_name: vehicle.vehicle_name,
      Mode: booking.mode,
      type: booking.priceMode === "Sharable" ? "shared" : "private",
      ...locationData, // Use the correct location and timing data
      PickupPlaceid: booking.PickupPlaceid || null,
      DropoffPlaceid: booking.DropoffPlaceid || null,
      adults: bookingAdultCount,
      children: bookingChildCount,
      totalPrice: Math.ceil(price),
      Tax: vehicle.tax_percentage,
      distance: vehicle.distance || vehicleData.$distanceInKM || booking.originalData?.distance || null,
      Night_Start_Time: vehicle.night_start_time || vehicleData.Night_Start_Time || null,
      Night_End_Time: vehicle.night_end_time || vehicleData.Night_End_Time || null,
      city: vehicle.city,
      country: vehicle.country,
      
      // Additional fields for tour package context
      id: booking.id,
      vehicle_type: vehicle.vehicle_type,
      vehicle_model: vehicle.vehicle_model,
      model_year: vehicle.model_year,
      seating_capacity: vehicle.seating_capacity
    };
    
    console.log("Entry Vehicle - Formatted booking data for Redux:", bookingData);
    
    // Clone the existing services array
    const allCurrentServices = [...existingServices];
    
    // Remove any existing Entry Port service with the same booking ID
    const filteredServices = allCurrentServices.filter(service => {
      if (service.type === "entry_port") {
        // If this is an entry_port service, check if it contains our booking ID
        if (service.data && service.data.some(item => item.id === booking.id)) {
          // This service contains our booking ID, so filter it out
          return false;
        }
      }
      // Keep all other services
      return true;
    });
    
    // Create a new Entry Port entry for this vehicle
    const newEntryPortService = {
      type: "entry_port",
      agent_id: agentId,
      tour_id: tourId,
      data: [bookingData]
    };
    
    // Add the new Entry Port service to the filtered services array
    filteredServices.push(newEntryPortService);
    
    console.log("Entry Vehicle - Dispatching updated services to Redux:", filteredServices);
    
    // Dispatch the updated services
    dispatch(setAllServices(filteredServices));
  };
  
  // Modify handleOpenSummaryModal to ensure data is in Redux before showing modal
  const handleOpenSummaryModal = (index) => {
    const bookings = getBookings();
    const booking = bookings[index];
    
    // Only dispatch to Redux if booking is complete and not already in Redux
    if (booking && booking.isComplete) {
      const existingBooking = existingServices.find(service => 
        service.type === "entry_port" && 
        service.data && 
        service.data.some(item => item.id === booking.id)
      );
      
      if (!existingBooking) {
        console.log("Entry Vehicle - Booking not in Redux, dispatching before showing modal");
        dispatchBookingToRedux(index);
      } else {
        console.log("Entry Vehicle - Booking already in Redux, showing modal directly");
      }
    }
    
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
    
    // Check if this is a loaded booking or a new booking
    const isLoadedBooking = booking.originalData !== undefined;
    
    // Get location and timing data based on booking type
    const locationData = isLoadedBooking ? {
      pickupLocation: booking.originalData.entrypickup,
      dropoffLocation: booking.originalData.entrydropoff,
      bookingDate: booking.originalData.pickupdate,
      pickupTime: booking.originalData.entrytime
    } : {
      pickupLocation: entryPickup,
      dropoffLocation: entryDropoff,
      bookingDate: pickupDate,
      pickupTime: entryTime
    };
    
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
      ...locationData,
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
    <Box sx={{ mt: 3 }}>
      {/* Header Card with Gradient Background */}
      
      
      {/* Multiple Booking Cards */}
      <Grid container spacing={3}>
        {bookings.map((booking, index) => {
          const completionStatus = booking.isComplete ? 3 : 
            (booking.vehicle ? 1 : 0) + (booking.adults + booking.children > 0 ? 1 : 0) + 
            (booking.priceMode ? 1 : 0);
          
          return (
            <Grid item xs={12} key={booking.id}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: `2px solid rgba(59, 130, 246, 0.2)`,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: `0 8px 24px rgba(59, 130, 246, 0.15)`,
                    transform: 'translateY(-2px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 2,
                    bgcolor: 'rgba(59, 130, 246, 0.05)',
                    borderBottom: `1px solid rgba(59, 130, 246, 0.1)`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                      <FlightLandIcon sx={{ color: '#3b82f6', fontSize: 24 }} />
                      <Chip 
                        label={`Entry Port #${index + 1}`}
                        sx={{ 
                          bgcolor: '#3b82f6',
                          color: 'white',
                          fontWeight: 600
                        }}
                        size="small"
                      />
                      <Chip 
                        label={`${completionStatus}/3 Complete`}
                        color={completionStatus === 3 ? "success" : "warning"}
                        size="small"
                        variant="outlined"
                      />
                      {booking.isComplete && (
                        <Chip
                          label="Ready for Booking"
                          color="success"
                          size="small"
                          variant="outlined"
                        />
                      )}
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <Tooltip title="Remove this service">
                        <IconButton 
                          size="small" 
                          onClick={(e) => {
                            e.stopPropagation();
                            handleRemoveBooking(index);
                          }}
                          sx={{ 
                            bgcolor: 'rgba(244, 67, 54, 0.1)',
                            '&:hover': { bgcolor: 'rgba(244, 67, 54, 0.2)' }
                          }}
                        >
                          <DeleteIcon sx={{ fontSize: 18, color: '#f44336' }} />
                        </IconButton>
                      </Tooltip>
                      
                      <Button
                        variant="outlined"
                        size="large"
                        onClick={() => handleOpenSummaryModal(index)}
                        disabled={!booking.isComplete}
                        startIcon={<VisibilityIcon />}
                        sx={{
                          borderRadius: 2,
                          px: 4,
                          py: 1,
                          fontSize: '0.875rem',
                          fontWeight: 600,
                          textTransform: 'none',
                          borderColor: '#3b82f6',
                          color: '#3b82f6',
                          '&:hover': {
                            borderColor: '#1e40af',
                            bgcolor: 'rgba(59, 130, 246, 0.05)',
                            transform: 'translateY(-1px)',
                          },
                          '&:disabled': {
                            borderColor: 'rgba(59, 130, 246, 0.3)',
                            color: 'rgba(59, 130, 246, 0.3)',
                          },
                          transition: 'all 0.3s ease',
                        }}
                      >
                        View Summary
                      </Button>
                    </Box>
                  </Box>

                  {/* Content Section */}
                  <Paper 
                    elevation={0} 
                    sx={{ 
                      m: 2,
                      p: 3, 
                      borderRadius: 2,
                      background: 'rgba(255, 255, 255, 0.95)',
                      backdropFilter: 'blur(10px)'
                    }}
                  >
                    <Grid container spacing={2}>
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
                    </Grid>
                  </Paper>
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
              border: `2px dashed rgba(59, 130, 246, 0.4)`,
              bgcolor: 'rgba(59, 130, 246, 0.02)',
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              '&:hover': {
                bgcolor: 'rgba(59, 130, 246, 0.05)',
                borderColor: '#3b82f6',
                transform: 'translateY(-1px)',
              }
            }}
            onClick={handleAddMoreBooking}
          >
            <CardContent sx={{ py: 2 }}>
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                gap: 2
              }}>
           
                <AddIcon sx={{ fontSize: 32, color: '#3b82f6' }} />
                <Typography variant="h6" color="#3b82f6" fontWeight={600}>
                  Add More 
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
      
      {/* Booking Summary Modal */}
      <PortSummaryModal
        open={openSummaryModal}
        onClose={handleCloseSummaryModal}
        bookingData={summaryBookingIndex !== null ? getBookingSummary(summaryBookingIndex) : null}
        portType="Entry Port"
      />
    </Box>
  );
};

export default VehicleListDropdown;




