import React, { useEffect, useState, useRef, useCallback } from 'react';
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
  CardContent,
  Alert
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
          value={pricemode || ''}
          label="Price Mode"
          onChange={(e) => setpricemode(e.target.value)}
        >
          {[
            hasPrivatePrice ? (
              <MenuItem key="private" value="Private">Private</MenuItem>
            ) : null
          ].filter(Boolean)}
          {[
            hasSharablePrice ? (
              <MenuItem key="sharable" value="Sharable">Sharable</MenuItem>
            ) : null
          ].filter(Boolean)}
        </Select>
      </FormControl>
    </Grid>
  );
};

const VehicleListDropdown = ({ selectedVehicle, onVehicleChange, entryPorts, tourDates = [], date }) => {
  const vehicles = useSelector((state) => state.pickupDrop.vehicles || []);
  const portZoneType = useSelector((state) => state.pickupDrop.portZoneType);
  const dispatch = useDispatch();
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  
  // Make sure we're only working with entry ports
  const validEntryPorts = entryPorts && entryPorts.filter(port => port.type === "entry_port");
  console.log("Entry Vehicle - Filtered entryPorts:", validEntryPorts);
  
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
  const adultsMax = tourDetails?.data?.adult || tourDetails?.adult || 1;
  const childrenMax = tourDetails?.data?.child || tourDetails?.child || 0;

  // Component state
  const [seatingCapacity, setSeatingCapacity] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const adultCount = useSelector((state) => state.pickupDrop.adultCount);
  const childCount = useSelector((state) => state.pickupDrop.childCount);
  
  // Modal states
  const [openSummaryModal, setOpenSummaryModal] = useState(false);
  const [summaryBookingIndex, setSummaryBookingIndex] = useState(null);
  
  // Track deleted booking IDs to prevent re-initialization
  const deletedBookingIdsRef = useRef(new Set());
  
  // Initialize bookings from entryPorts data or default empty booking
  const initializeBookings = () => {
    if (validEntryPorts && validEntryPorts.length > 0) {
      // Pre-populate with existing entry port data, but exclude deleted bookings
      return validEntryPorts
        .filter(entryPort => {
          const entryData = entryPort.data?.[0];
          if (!entryData) return false;
          
          // Check if this booking was deleted
          const bookingId = entryData.id;
          const bookingIdFromService = entryPort.booking_id;
          
          // Skip if this booking was deleted
          if (deletedBookingIdsRef.current.has(bookingId) || 
              deletedBookingIdsRef.current.has(bookingIdFromService)) {
            console.log("Entry Vehicle - Skipping deleted booking during initialization:", bookingId);
            return false;
          }
          
          return true;
        })
        .map((entryPort, index) => {
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
          
          // Normalize data types to handle inconsistencies
          const normalizedEntryData = {
            ...entryData,
            // Convert string numbers to actual numbers
            adults: entryData.adults ? Number(entryData.adults) : 1,
            children: entryData.children ? Number(entryData.children) : 0,
            dmc_id: entryData.dmc_id ? String(entryData.dmc_id) : '',
            vehicles_id: entryData.vehicles_id ? String(entryData.vehicles_id) : '',
            totalPrice: entryData.totalPrice ? Number(entryData.totalPrice) : 0,
            seating_capacity: entryData.seatingCapacity ? Number(entryData.seatingCapacity) : 1,
            distance: entryData.distance ? Number(entryData.distance) : 0,
            // Normalize type for case insensitivity
            type: entryData.type ? entryData.type.toLowerCase() : 'private'
          };
          
          // Find the corresponding vehicle from the vehicles list
          const matchingVehicle = vehicles.find(v => String(v.id) === String(normalizedEntryData.vehicles_id));
          
          return {
            id: normalizedEntryData.id || `entry-${Date.now()}-${index}`,
            vehicle: matchingVehicle || {
              id: normalizedEntryData.vehicles_id,
              vehicle_name: normalizedEntryData.vehicles_name || 'Unknown Vehicle',
              vehicle_type: normalizedEntryData.vehicle_type || '',
              vehicle_model: normalizedEntryData.vehicle_model || '',
              model_year: normalizedEntryData.model_year || '',
              seating_capacity: Number(normalizedEntryData.seating_capacity) || 1,
              image: normalizedEntryData.image || '',
              city: normalizedEntryData.city || '',
              country: normalizedEntryData.country || '',
              dmc_id: normalizedEntryData.dmc_id || ''
            },
            vehicleData: {
              // Map the price mode to expected structure
              private_price: normalizedEntryData.type === 'private' ? normalizedEntryData.totalPrice : 0,
              shared_price: normalizedEntryData.type === 'shared' || normalizedEntryData.type === "sharable" ? normalizedEntryData.totalPrice : 0,
              prices: {
                privatePrice: normalizedEntryData.type === 'private' ? normalizedEntryData.totalPrice : 0,
                sharablePrice: normalizedEntryData.type === 'shared' || normalizedEntryData.type === "sharable" ? normalizedEntryData.totalPrice : 0
              }
            },
            priceMode: normalizedEntryData.type === 'shared' || normalizedEntryData.type === "sharable" ? 'Sharable' : 'Private',
            isComplete: true, // Mark as complete since it's loaded data
            adults: normalizedEntryData.adults || 1,
            children: normalizedEntryData.children || 0,
            mode: normalizedEntryData.Mode || 'dmc',
            dmcId: normalizedEntryData.dmc_id || '',
            entrypickup: normalizedEntryData.entrypickup || '',
            entrydropoff: normalizedEntryData.entrydropoff || '',
            bookingDate: normalizedEntryData.bookingDate || '',
            pickupdate: normalizedEntryData.pickupdate || '',
            entrytime: normalizedEntryData.entrytime || '',
            // Store original loaded data for reference, including booking_id
            originalData: {
              ...normalizedEntryData,
              booking_id: entryPort.booking_id // Preserve booking_id from service level
            }
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

  // Debug log initial bookings and reset deleted bookings when entryPorts change significantly
  useEffect(() => {
    console.log("Entry Vehicle - Initial bookings state:", bookingsRef.current);
    if (validEntryPorts && validEntryPorts.length > 0) {
      console.log("Entry Vehicle - Loading with existing entryPorts data");
      
      // Reset deleted bookings when entryPorts change significantly (new tour)
      // This prevents deleted bookings from persisting across different tours
      const currentEntryPortIds = validEntryPorts.map(port => port.booking_id).filter(Boolean);
      const hasSignificantChange = currentEntryPortIds.length > 0 && 
        !currentEntryPortIds.some(id => deletedBookingIdsRef.current.has(id));
      
      if (hasSignificantChange) {
        console.log("Entry Vehicle - Detected significant entryPorts change, clearing deleted bookings set");
        deletedBookingIdsRef.current.clear();
      }
    } else {
      console.log("Entry Vehicle - Loading with default empty booking");
    }
  }, [validEntryPorts]);
  
  // Automatically store entryPorts data into Redux AllServices state when component receives props
  const hasDispatchedAllEntryPortsRef = useRef(false);
  const lastDispatchRef = useRef(null);
  const hasInitializedRef = useRef(false);
  
  // State to trigger re-renders when bookings change
  const [bookingsVersion, setBookingsVersion] = useState(0);
  
  // Getter for bookings that reads from the ref
  const getBookings = () => bookingsRef.current;
  
  // Setter for bookings that updates the ref and triggers a re-render
  const setBookings = (newBookingsOrUpdater) => {
    let newBookings;
    
    // Handle functional updates
    if (typeof newBookingsOrUpdater === 'function') {
      newBookings = newBookingsOrUpdater(bookingsRef.current);
    } else {
      newBookings = newBookingsOrUpdater;
    }
    
    // Check if the bookings array has actually changed before updating
    const currentBookings = bookingsRef.current;
    if (JSON.stringify(currentBookings) !== JSON.stringify(newBookings)) {
      bookingsRef.current = newBookings;
      setBookingsVersion(prev => prev + 1); // Trigger re-render
    }
  };
  
  // Handle passenger count changes for a specific booking
  const handleBookingPassengerChange = (bookingIndex, type, count) => {
    setBookings(prevBookings => {
      const updatedBookings = [...prevBookings];
      
      if (updatedBookings[bookingIndex]) {
        updatedBookings[bookingIndex] = {
          ...updatedBookings[bookingIndex],
          [type]: Number(count) // Ensure count is stored as a number
        };
      }
      
      return updatedBookings;
    });
    
    // Also update Redux state for the first booking only
    if (bookingIndex === 0) {
      // Here you would dispatch an action to update Redux state
      // For example: dispatch(updatePassengerCount({type, count}))
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
    
    if (!bookingToRemove) {
      console.warn("Entry Vehicle - No booking found at index:", indexToRemove);
      return;
    }
    
    console.log("Entry Vehicle - Removing booking:", bookingToRemove);
    
    // Add the booking ID to the deleted set to prevent re-initialization
    if (bookingToRemove.id) {
      deletedBookingIdsRef.current.add(bookingToRemove.id);
    }
    if (bookingToRemove.originalData?.booking_id) {
      deletedBookingIdsRef.current.add(bookingToRemove.originalData.booking_id);
    }
    
    console.log("Entry Vehicle - Added to deleted set:", deletedBookingIdsRef.current);
    
    // Remove from local state
    const updatedBookings = bookings.filter((_, index) => index !== indexToRemove);
    setBookings(updatedBookings);
    
    // Remove from Redux state if it has booking data (either has an ID or a valid vehicle)
    const hasBookingId = bookingToRemove.id;
    const hasVehicleId = bookingToRemove.vehicle && bookingToRemove.vehicle.id;
    
    if (hasBookingId || hasVehicleId) {
        // Clone the existing services array
        const currentServices = [...existingServices];
        
        // Filter out entry_port services that contain this booking
        const filteredServices = currentServices.map(service => {
          // Check if this is an entry_port service
          if (service.type === "entry_port") {
            // Check if this service contains data that matches our booking
            if (service.data && Array.isArray(service.data)) {
              // Remove the specific booking with matching ID and booking_id (if available)
              const filteredData = service.data.filter(dataItem => {
                // Match by booking_id first (most reliable)
                if (bookingToRemove.originalData?.booking_id && dataItem.booking_id) {
                  return !(dataItem.id === bookingToRemove.id && 
                          dataItem.booking_id === bookingToRemove.originalData.booking_id);
                }
                
                // Match by ID as fallback
                if (bookingToRemove.id && dataItem.id === bookingToRemove.id) {
                  return false;
                }
                
                // Match by vehicle ID as final fallback
                if (bookingToRemove.vehicle && 
                    bookingToRemove.vehicle.id &&
                    dataItem.vehicles_id === bookingToRemove.vehicle.id) {
                  return false;
                }
                
                return true;
              });
              
              if (filteredData.length === 0) {
                // If no data left, mark for removal
                return null;
              } else {
                // Create a new service with filtered data (immutable update)
                return {
                  ...service,
                  data: filteredData
                };
              }
            }
          }
          
          // Keep all other services as-is
          return service;
        }).filter(service => service !== null); // Remove services marked as null
        
        // Only dispatch if there's an actual change
        if (filteredServices.length !== currentServices.length) {
          console.log("Entry Vehicle - Removing booking from Redux:", bookingToRemove);
          console.log("Entry Vehicle - Updated services:", filteredServices);
          dispatch(setAllServices(filteredServices));
        }
      }
  };
  
  // Filter vehicles that have at least one pricing mode
  const filteredVehicles = Array.isArray(vehicles) ? vehicles.filter(vehicle => {
    const hasDmcPrice = vehicle.dmc_private_price > 0 || vehicle.dmc_sharable_price > 0;
    const hasTravclicksPrice = vehicle.trav_private_price > 0 || vehicle.trav_sharable_price > 0;
    return hasDmcPrice || hasTravclicksPrice;
  }) : [];
  
  // Find selected vehicle object for the primary booking
  const selectedVehicleObj = selectedVehicle ? 
    filteredVehicles.find(v => v.id === selectedVehicle) : null;
  
  // Function to dispatch initialized bookings to Redux state
  const dispatchInitializedBookingsToRedux = (bookings) => {
    // Skip if we've already dispatched the original entry ports
    if (hasInitializedRef.current) {
      console.log("Entry Vehicle - Skipping dispatchInitializedBookingsToRedux because original data was already dispatched");
      return;
    }
    
    const completedBookings = bookings.filter(booking => booking.isComplete);
    
    if (completedBookings.length > 0) {
      console.log("Entry Vehicle - Dispatching initialized bookings to Redux:", completedBookings);
      
      // Format bookings for Redux state
      const bookingsForRedux = completedBookings.map(booking => {
        const vehicleObj = booking.vehicle || {};
        const vehicleDataObj = booking.vehicleData || {};
        const bookingAdultCount = booking.adults ? Number(booking.adults) : (adultCount ? Number(adultCount) : 1);
        const bookingChildCount = booking.children ? Number(booking.children) : (childCount ? Number(childCount) : 0);
        const totalGuests = bookingAdultCount + bookingChildCount;
        
        // Calculate price based on price mode - with error handling
        let price = 0;
        try {
          if (booking.priceMode === "Sharable") {
            if (vehicleDataObj.prices && vehicleDataObj.prices.sharablePrice) {
              price = Number(vehicleDataObj.prices.sharablePrice) * totalGuests;
            } else if (vehicleDataObj.shared_price) {
              price = parseFloat(vehicleDataObj.shared_price) * totalGuests;
            }
          } else {
            if (vehicleDataObj.prices && vehicleDataObj.prices.privatePrice) {
              price = Number(vehicleDataObj.prices.privatePrice);
            } else if (vehicleDataObj.private_price) {
              price = parseFloat(vehicleDataObj.private_price);
            }
          }
        } catch (e) {
          console.error("Error calculating price:", e);
          price = 0;
        }
        
        // Find any existing customer info in current services
        const customerInfoService = existingServices.find(service => service.type === 'CustomerInfo');
        
        // Check if this is a loaded booking or a new booking
        const isLoadedBooking = booking.originalData !== undefined;
        
        // Get location and timing data based on booking type
        const locationData = isLoadedBooking ? {
          entrypickup: booking.originalData.entrypickup || '',
          entrydropoff: booking.originalData.entrydropoff || '',
          bookingDate: booking.originalData.pickupdate || '',
          pickupdate: booking.originalData.pickupdate || '',
          entrytime: booking.originalData.entrytime || ''
        } : {
          entrypickup: entryPickup || '',
          entrydropoff: entryDropoff || '',
          bookingDate: pickupDate || '',
          pickupdate: pickupDate || '',
          entrytime: entryTime || ''
        };

        // Create booking data in the same format as currently used
        const bookingData = {
          // Customer information fields (will be populated when available or default to empty strings)
          fullName: customerInfoService?.fullName || "",
          email: customerInfoService?.email || "",
          phone: customerInfoService?.phone || "",
          countryCode: customerInfoService?.countryCode || "",
          address1: customerInfoService?.address1 || "",
          address2: customerInfoService?.address2 || "",
          state: customerInfoService?.state || "",
          zip: customerInfoService?.zip || "",
          specialRequests: customerInfoService?.specialRequests || "",
            
          // Core booking details with correct location and timing data
          id: booking.id || `entry-${Date.now()}`,
          vehicles_id: vehicleObj.id || '',
          image: vehicleObj.image || '',
          dmc_id: booking.dmcId || '',
          vehicles_name: vehicleObj.vehicle_name || 'Unknown Vehicle',
          Mode: booking.mode || 'dmc',
          type: booking.priceMode === "Sharable" ? "Shared" : "Private",
          ...locationData, // Use the correct location and timing data
          PickupPlaceid: booking.PickupPlaceid || null,
          DropoffPlaceid: booking.DropoffPlaceid || null,
          adults: bookingAdultCount,
          children: bookingChildCount,
          totalPrice: Math.ceil(price),
          Tax: vehicleObj.tax_percentage || 0,
          distance: vehicleObj.distance || vehicleDataObj.$distanceInKM || booking.originalData?.distance || 0,
          Night_Start_Time: vehicleObj.night_start_time || vehicleDataObj.Night_Start_Time || null,
          Night_End_Time: vehicleObj.night_end_time || vehicleDataObj.Night_End_Time || null,
          city: vehicleObj.city || '',
          country: vehicleObj.country || '',
          vehicle_type: vehicleObj.vehicle_type || '',
          vehicle_model: vehicleObj.vehicle_model || '',
          model_year: vehicleObj.model_year || '',
          seating_capacity: vehicleObj.seating_capacity || 1
        };
        
        // Add booking_id if available from original data
        if (booking.originalData?.booking_id) {
          bookingData.booking_id = booking.originalData.booking_id;
        }
        
        const serviceObject = {
          type: "entry_port",
          agent_id: agentId || '',
          tour_id: tourId || '',
          booking_id: booking.originalData?.booking_id, // Preserve booking_id from original data
          data: [bookingData],
          bookingType: "enquiry"
        };
        
        console.log(`Entry Vehicle - Service data with booking_id: ${booking.originalData?.booking_id}`, serviceObject);
        
        return serviceObject;
      });
      
      // Remove any existing Entry Port services and add the new ones
      const filteredServices = existingServices.filter(service => service.type !== "entry_port");
      const finalServices = [...filteredServices, ...bookingsForRedux];
      
      console.log("Entry Vehicle - Dispatching initialized services to Redux:", finalServices);
      dispatch(setAllServices(finalServices));
    }
  };

  // Re-initialize bookings when entryPorts prop changes - simplified with stable dependencies
  
  useEffect(() => {
    if (validEntryPorts && validEntryPorts.length > 0 && Array.isArray(vehicles) && vehicles.length > 0 && !hasInitializedRef.current) {
      console.log("Entry Vehicle - Detected entryPorts data, re-initializing bookings:", validEntryPorts);
      
      // Re-initialize bookings with the latest entryPorts and vehicles data, but exclude deleted bookings
      const newBookings = validEntryPorts
        .filter(entryPort => {
          const entryData = entryPort.data?.[0];
          if (!entryData) return false;
          
          // Check if this booking was deleted
          const bookingId = entryData.id;
          const bookingIdFromService = entryPort.booking_id;
          
          // Skip if this booking was deleted
          if (deletedBookingIdsRef.current.has(bookingId) || 
              deletedBookingIdsRef.current.has(bookingIdFromService)) {
            console.log("Entry Vehicle - Skipping deleted booking during re-initialization:", bookingId);
            return false;
          }
          
          return true;
        })
        .map((entryPort, index) => {
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
          
          // Normalize data types to handle inconsistencies
          const normalizedEntryData = {
            ...entryData,
            // Convert string numbers to actual numbers
            adults: entryData.adults ? Number(entryData.adults) : 1,
            children: entryData.children ? Number(entryData.children) : 0,
            dmc_id: entryData.dmc_id ? String(entryData.dmc_id) : '',
            vehicles_id: entryData.vehicles_id ? String(entryData.vehicles_id) : '',
            totalPrice: entryData.totalPrice ? Number(entryData.totalPrice) : 0,
            distance: entryData.distance ? Number(entryData.distance) : 0,
            // Normalize type for case insensitivity
            type: entryData.type ? entryData.type.toLowerCase() : 'private'
          };
          
          // Find the corresponding vehicle from the vehicles list
          const matchingVehicle = vehicles.find(v => String(v.id) === String(normalizedEntryData.vehicles_id));
          
          const booking = {
            id: normalizedEntryData.id || `entry-${Date.now()}-${index}`,
            vehicle: matchingVehicle || {
              id: normalizedEntryData.vehicles_id || '',
              vehicle_name: normalizedEntryData.vehicles_name || 'Unknown Vehicle',
              vehicle_type: normalizedEntryData.vehicle_type || '',
              vehicle_model: normalizedEntryData.vehicle_model || '',
              model_year: normalizedEntryData.model_year || '',
              seating_capacity: normalizedEntryData.seating_capacity || 1,
              image: normalizedEntryData.image || '',
              city: normalizedEntryData.city || '',
              country: normalizedEntryData.country || '',
              dmc_id: normalizedEntryData.dmc_id || ''
            },
            vehicleData: {
              private_price: normalizedEntryData.type === 'private' ? normalizedEntryData.totalPrice : 0,
              shared_price: normalizedEntryData.type === 'shared' ? normalizedEntryData.totalPrice : 0,
              prices: {
                privatePrice: normalizedEntryData.type === 'private' ? normalizedEntryData.totalPrice : 0,
                sharablePrice: normalizedEntryData.type === 'shared' ? normalizedEntryData.totalPrice : 0
              },
              $distanceInKM: normalizedEntryData.distance || null
            },
            priceMode: normalizedEntryData.type === 'shared' ? 'Sharable' : 'Private',
            isComplete: true,
            adults: normalizedEntryData.adults || 1,
            children: normalizedEntryData.children || 0,
            mode: normalizedEntryData.Mode || 'dmc',
            dmcId: normalizedEntryData.dmc_id || '',
            originalData: {
              ...normalizedEntryData,
              booking_id: entryPort.booking_id // Preserve booking_id from service level
            }
          };
          
          console.log(`Entry Vehicle - Initialized booking with booking_id: ${entryPort.booking_id}`, {
            bookingId: booking.id,
            serviceBookingId: entryPort.booking_id,
            entryData: normalizedEntryData
          });
          
          return booking;
        });
      
      // Store in local state
      bookingsRef.current = newBookings;
      setBookingsVersion(prev => prev + 1);
      hasInitializedRef.current = true;
      
      console.log("Entry Vehicle - Initialized bookings from entryPorts:", newBookings);
      
      // Also store in Redux state in the same format
      dispatchInitializedBookingsToRedux(newBookings);
    }
  }, [validEntryPorts, vehicles]);

  // Update current booking when selected vehicle changes from parent (only if no loaded data)
  useEffect(() => {
    if (selectedVehicleObj && 
        bookingsRef.current && 
        bookingsRef.current.length > 0 && 
        bookingsRef.current[0] && 
        !bookingsRef.current[0].vehicle && 
        (!validEntryPorts || validEntryPorts.length === 0)) {
      const currentBookings = getBookings();
      const updatedBookings = [...currentBookings];
      updatedBookings[0] = {
        ...updatedBookings[0],
        vehicle: selectedVehicleObj
      };
      setBookings(updatedBookings);
      handleVehicleSelect(selectedVehicleObj, 0);
    }
  }, [selectedVehicleObj, validEntryPorts]);
  
  // Update completion status when relevant data changes - simplified
  const completionCheckValues = React.useMemo(() => ({
    entryPickup,
    entryDropoff,
    pickupDate,
    entryTime
  }), [entryPickup, entryDropoff, pickupDate, entryTime]);

  const checkBookingCompletion = React.useCallback((booking) => {
    const isLoadedBooking = booking.originalData !== undefined;
    
    let isComplete;
    if (isLoadedBooking) {
      isComplete = 
        booking.vehicle !== null && 
        booking.vehicleData !== null && 
        booking.priceMode !== null && booking.priceMode !== '' &&
        booking.originalData.entrypickup !== null && booking.originalData.entrypickup !== undefined && booking.originalData.entrypickup !== '' &&
        booking.originalData.entrydropoff !== null && booking.originalData.entrydropoff !== undefined && booking.originalData.entrydropoff !== '' &&
        booking.originalData.pickupdate !== null && booking.originalData.pickupdate !== undefined && booking.originalData.pickupdate !== '' &&
        booking.originalData.entrytime !== null && booking.originalData.entrytime !== undefined && booking.originalData.entrytime !== '';
    } else {
      isComplete = 
        booking.vehicle !== null && 
        booking.vehicleData !== null && 
        booking.priceMode !== null && booking.priceMode !== '' &&
        completionCheckValues.entryPickup !== null && completionCheckValues.entryPickup !== undefined && completionCheckValues.entryPickup !== '' &&
        completionCheckValues.entryDropoff !== null && completionCheckValues.entryDropoff !== undefined && completionCheckValues.entryDropoff !== '' &&
        completionCheckValues.pickupDate !== null && completionCheckValues.pickupDate !== undefined && completionCheckValues.pickupDate !== '' &&
        completionCheckValues.entryTime !== null && completionCheckValues.entryTime !== undefined && completionCheckValues.entryTime !== '';
    }
    
    return isComplete;
  }, [completionCheckValues]);

  // Completion status checking with manual dispatch tracking
  const prevBookingsStringifiedRef = useRef('');
  const bookingsStringified = JSON.stringify(getBookings().map(b => ({
    id: b.id,
    hasVehicle: !!b.vehicle,
    hasVehicleData: !!b.vehicleData,
    priceMode: b.priceMode,
    isComplete: b.isComplete
  })));

  useEffect(() => {
    // Only proceed if bookings actually changed
    if (prevBookingsStringifiedRef.current === bookingsStringified) {
      return;
    }
    
    const bookings = getBookings();
    let needsUpdate = false;
    
    const updatedBookings = bookings.map(booking => {
      const isComplete = checkBookingCompletion(booking);
      
      if (isComplete !== booking.isComplete) {
        console.log(`Entry Vehicle - Booking ${booking.id} completion status changed to:`, isComplete);
        needsUpdate = true;
        return { ...booking, isComplete };
      }
      return booking;
    });
    
    if (needsUpdate) {
      prevBookingsStringifiedRef.current = JSON.stringify(updatedBookings.map(b => ({
        id: b.id,
        hasVehicle: !!b.vehicle,
        hasVehicleData: !!b.vehicleData,
        priceMode: b.priceMode,
        isComplete: b.isComplete
      })));
      bookingsRef.current = updatedBookings;
      setBookingsVersion(prev => prev + 1);
    } else {
      // Update the ref even if no updates needed to prevent future unnecessary checks
      prevBookingsStringifiedRef.current = bookingsStringified;
    }
  }, [bookingsStringified, checkBookingCompletion]);

  // Add a function to directly dispatch a specific booking to Redux
  const dispatchBookingToRedux = React.useCallback((bookingIndex, forceUpdate = false) => {
    // Skip if we've already dispatched the original entry ports and not forcing update
    if (hasDispatchedAllEntryPortsRef.current && !forceUpdate) {
      console.log("Entry Vehicle - Skipping dispatchBookingToRedux because original data was already dispatched");
      return;
    }
    
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
    
    const vehicle = booking.vehicle || {};
    const vehicleData = booking.vehicleData || {};
    const bookingAdultCount = booking.adults ? Number(booking.adults) : (adultCount ? Number(adultCount) : 1);
    const bookingChildCount = booking.children ? Number(booking.children) : (childCount ? Number(childCount) : 0);
    const totalGuests = bookingAdultCount + bookingChildCount;
    
    // Check if this is a loaded booking or a new booking
    const isLoadedBooking = booking.originalData !== undefined;
    
    // Get location and timing data based on booking type
    const locationData = isLoadedBooking ? {
      entrypickup: booking.originalData.entrypickup || '',
      entrydropoff: booking.originalData.entrydropoff || '',
      bookingDate: booking.originalData.pickupdate || '',
      pickupdate: booking.originalData.pickupdate || '',
      entrytime: booking.originalData.entrytime || ''
    } : {
      entrypickup: entryPickup || '',
      entrydropoff: entryDropoff || '',
      bookingDate: pickupDate || '',
      pickupdate: pickupDate || '',
      entrytime: entryTime || ''
    };
    
    // Calculate price based on price mode
    let price = 0;
    try {
      if (booking.priceMode === "Sharable") {
        if (vehicleData.prices && vehicleData.prices.sharablePrice) {
          price = Number(vehicleData.prices.sharablePrice) * totalGuests;
        } else if (vehicleData.shared_price) {
          price = parseFloat(vehicleData.shared_price) * totalGuests;
        }
      } else {
        if (vehicleData.prices && vehicleData.prices.privatePrice) {
          price = Number(vehicleData.prices.privatePrice);
        } else if (vehicleData.private_price) {
          price = parseFloat(vehicleData.private_price);
        }
      }
    } catch (e) {
      console.error("Error calculating price:", e);
      price = 0;
    }
    
    // Find any existing customer info in current services
    const customerInfoService = existingServices.find(service => service.type === 'CustomerInfo');
    
    // Create booking data matching the exact parameter names from index1.jsx details object
    const bookingData = {
      // Customer information fields (will be populated when available or default to empty strings)
      fullName: customerInfoService?.fullName || "",
      email: customerInfoService?.email || "",
      phone: customerInfoService?.phone || "",
      countryCode: customerInfoService?.countryCode || "",
      address1: customerInfoService?.address1 || "",
      address2: customerInfoService?.address2 || "",
      state: customerInfoService?.state || "",
      zip: customerInfoService?.zip || "",
      specialRequests: customerInfoService?.specialRequests || "",
      
      // Core booking details with correct location and timing data
      vehicles_id: vehicle.id || '',
      image: vehicle.image || '',
      dmc_id: booking.dmcId || '',
      vehicles_name: vehicle.vehicle_name || 'Unknown Vehicle',
      Mode: booking.mode || 'dmc',
      type: booking.priceMode === "Sharable" ? "Shared" : "Private",
      ...locationData, // Use the correct location and timing data
      PickupPlaceid: booking.PickupPlaceid || null,
      DropoffPlaceid: booking.DropoffPlaceid || null,
      adults: bookingAdultCount,
      children: bookingChildCount,
      totalPrice: Math.ceil(price),
      Tax: vehicle.tax_percentage || 0,
      distance: vehicle.distance || vehicleData.$distanceInKM || booking.originalData?.distance || 0,
      Night_Start_Time: vehicle.night_start_time || vehicleData.Night_Start_Time || null,
      Night_End_Time: vehicle.night_end_time || vehicleData.Night_End_Time || null,
      city: vehicle.city || '',
      country: vehicle.country || '',
      
      // Additional fields for tour package context
      id: booking.id || `entry-${Date.now()}`,
      vehicle_type: vehicle.vehicle_type || '',
      vehicle_model: vehicle.vehicle_model || '',
      model_year: vehicle.model_year || '',
      seating_capacity: vehicle.seating_capacity || 1
    };
    
    // Add booking_id if available from original data
    if (booking.originalData?.booking_id) {
      bookingData.booking_id = booking.originalData.booking_id;
    }
    
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
      agent_id: agentId || '',
      tour_id: tourId || '',
      booking_id: booking.originalData?.booking_id, // Preserve booking_id from original data
      data: [bookingData],
      bookingType: "enquiry"
    };
    
    console.log(`Entry Vehicle - Direct dispatch with booking_id: ${booking.originalData?.booking_id}`, newEntryPortService);
    
    // Add the new Entry Port service to the filtered services array
    filteredServices.push(newEntryPortService);
    
    console.log("Entry Vehicle - Dispatching updated services to Redux:", filteredServices);
    
    // Dispatch the updated services
    dispatch(setAllServices(filteredServices));
  }, [existingServices, dispatch, adultCount, childCount, entryPickup, entryDropoff, pickupDate, entryTime, agentId, tourId]);

  // Auto-dispatch newly completed bookings to Redux
  useEffect(() => {
    const bookings = getBookings();
    
    bookings.forEach((booking, index) => {
      if (booking.isComplete) {
        // Check if this booking is already in Redux
        const existingBooking = existingServices.find(service => 
          service.type === "entry_port" && 
          service.data && 
          service.data.some(item => item.id === booking.id)
        );
        
        if (!existingBooking) {
          console.log("Entry Vehicle - Auto-dispatching newly completed booking to Redux:", booking.id);
          dispatchBookingToRedux(index);
        }
      }
    });
  }, [bookingsVersion, existingServices, dispatchBookingToRedux]); // Watch for booking completion changes
  
  // Handle vehicle selection
  const handleVehicleSelect = (vehicleItem, bookingIndex) => {
    if (!vehicleItem) return;
    
    const hasDmcPrice = vehicleItem.dmc_private_price > 0 || vehicleItem.dmc_sharable_price > 0;
    const hasTravclicksPrice = vehicleItem.trav_private_price > 0 || vehicleItem.trav_sharable_price > 0;
    
    const mode = (hasDmcPrice && !hasTravclicksPrice) ? "dmc" : "travclicks";
    const dmcId = (hasDmcPrice && !hasTravclicksPrice) ? vehicleItem.dmc_id : vehicleItem.travclicks_dmc_id;
    
    // Always call the parent's onVehicleChange with the latest selection
    // regardless of booking index
    if (onVehicleChange) {
      onVehicleChange(vehicleItem.id, mode, dmcId, vehicleItem.city, vehicleItem.country, bookingIndex);
    }
    
    // Update the local bookings state with the selected vehicle
    setBookings(prevBookings => {
      const updatedBookings = [...prevBookings];
      updatedBookings[bookingIndex] = {
        ...updatedBookings[bookingIndex],
        vehicle: vehicleItem,
        mode: mode,
        dmcId: dmcId
      };
      return updatedBookings;
    });
    
    // Fetch vehicle details
    setIsLoading(true);
    setError(null);
    
    dispatch(fetchVehicleDetails({ city: vehicleItem.city, country: vehicleItem.country, type: portZoneType }))
      .unwrap()
      .then((data) => {
        setSeatingCapacity(data.seating_capacity || 0);
        
        // Update the booking with the fetched data
        setBookings(prevBookings => {
          const updatedBookings = [...prevBookings];
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
          
          return updatedBookings;
        });
        setIsLoading(false);
      })
      .catch((err) => {
        console.error("Error fetching vehicle details:", err);
        setError(err.message || "Failed to load vehicle details");
        setIsLoading(false);
      });
  };
  
  // Handle price mode selection - use functional update to ensure state is updated properly
  const handlePriceModeSelect = React.useCallback((value, bookingIndex) => {
    if (!value) return;
    
    setBookings(prevBookings => {
      if (!prevBookings || !prevBookings[bookingIndex]) return prevBookings;
      
      const updatedBookings = [...prevBookings];
      updatedBookings[bookingIndex] = {
        ...updatedBookings[bookingIndex],
        priceMode: value
      };
      return updatedBookings;
    });
  }, []);
  
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
    
    const summary = {
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
    
    // Include booking_id if available from original data
    if (booking.originalData?.booking_id) {
      summary.booking_id = booking.originalData.booking_id;
    }
    
    return summary;
  };

  // Helper to check if a booking is out of current tour dates for the specific dayIndex
  const isBookingOutOfTourDates = (booking) => {
    // Skip validation if tourDates is not provided
    if (!tourDates || tourDates.length === 0) {
      return false;
    }
    
    // Get the booking date from original data or current date
    const bookingDate = booking.originalData?.bookingDate || booking.originalData?.pickupdate || (date ? date.format('YYYY-MM-DD') : null);
    
    // Debug logging to check date formats
    console.log('Entry Port date validation debug:', {
      bookingId: booking.id || 'new-booking',
      bookingDate: bookingDate,
      tourDates: tourDates
    });
    
    // Handle edge cases
    if (!bookingDate) {
      console.log('Missing bookingDate, skipping validation');
      return false;
    }
    
    // Normalize booking date to YYYY-MM-DD format
    let normalizedBookingDate;
    try {
      if (typeof bookingDate === 'string') {
        // If it's already in YYYY-MM-DD format
        if (/^\d{4}-\d{2}-\d{2}$/.test(bookingDate)) {
          normalizedBookingDate = bookingDate;
        } else {
          // Convert from other formats to YYYY-MM-DD
          normalizedBookingDate = new Date(bookingDate).toISOString().split('T')[0];
        }
      } else {
        // If it's a Date object
        normalizedBookingDate = new Date(bookingDate).toISOString().split('T')[0];
      }
    } catch (error) {
      console.error('Error normalizing booking date:', error);
      return false;
    }
    
    // Check if the normalized booking date exists in tourDates
    const isDateValid = tourDates.includes(normalizedBookingDate);
    
    console.log('Entry Port date validation result:', {
      normalizedBookingDate: normalizedBookingDate,
      isDateValid: isDateValid,
      willShowError: !isDateValid
    });
    
    return !isDateValid;
  };
  
  // Get current bookings from ref
  const bookings = getBookings();

  // Cleanup effect
  useEffect(() => {
    return () => {
      hasDispatchedAllEntryPortsRef.current = false;
      lastDispatchRef.current = null;
      hasInitializedRef.current = false;
      // Clear deleted bookings set on unmount
      deletedBookingIdsRef.current.clear();
    };
  }, []);

  return (
    <Box sx={{ mt: 3 }}>
      {/* Header Card with Gradient Background */}
      
      
      {/* Multiple Booking Cards */}
      <Grid container spacing={3}>
        {bookings.map((booking, index) => {
          const completionStatus = booking.isComplete ? 3 : 
            (booking.vehicle ? 1 : 0) + (Number(booking.adults) + Number(booking.children) > 0 ? 1 : 0) + 
            (booking.priceMode ? 1 : 0);
          const outOfTourDates = isBookingOutOfTourDates(booking);
          console.log("booking22", booking);
          return (
            <Grid item xs={12} key={booking.id}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: outOfTourDates ? '2px solid #e53935' : `2px solid rgba(59, 130, 246, 0.2)`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: outOfTourDates
                      ? `0 8px 24px rgba(229,57,53,0.15)`
                      : `0 8px 24px rgba(59, 130, 246, 0.15)`,
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
                        adultsMax={adultsMax || 1} 
                        childrenMax={childrenMax || 0} 
                        seatingCapacity={Number(booking.vehicle?.seating_capacity) || Number(seatingCapacity) || 1}
                        initialAdults={Number(booking.adults) || 1}
                        initialChildren={Number(booking.children) || 0}
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
                              {[
                                (booking.vehicleData.prices && booking.vehicleData.prices.privatePrice > 0) || 
                                (booking.vehicleData.private_price && parseFloat(booking.vehicleData.private_price) > 0) ? (
                                <MenuItem key="private" value="Private">Private</MenuItem>
                              ) : null
                              ].filter(Boolean)}
                              {[
                                (booking.vehicleData.prices && booking.vehicleData.prices.sharablePrice > 0) || 
                                (booking.vehicleData.shared_price && parseFloat(booking.vehicleData.shared_price) > 0) ? (
                                <MenuItem key="sharable" value="Sharable">Sharable</MenuItem>
                              ) : null
                              ].filter(Boolean)}
                            </Select>
                          </FormControl>
                        </Grid>
                      )}
                    </Grid>
                  </Paper>
                  
                  {/* Red alert if out of tour dates */}
                  {outOfTourDates && (
                    <Box sx={{ px: 2, pt: 1 }}>
                      <Alert severity="error" sx={{ borderRadius: 2, mb: 1 }}>
                        The booking is out of currently updated tour dates
                      </Alert>
                    </Box>
                  )}
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



