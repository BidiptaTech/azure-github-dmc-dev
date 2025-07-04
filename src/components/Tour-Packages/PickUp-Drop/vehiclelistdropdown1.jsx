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
import { useSelector, useDispatch } from "react-redux";
import LocationOnIcon from '@mui/icons-material/LocationOn';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import EventSeatIcon from '@mui/icons-material/EventSeat';
import AirlineSeatReclineNormalIcon from '@mui/icons-material/AirlineSeatReclineNormal';
import DeleteIcon from '@mui/icons-material/Delete';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import AddIcon from '@mui/icons-material/Add';
import VisibilityIcon from '@mui/icons-material/Visibility';
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

const VehicleListDropdown1 = ({ selectedVehicle, onVehicleChange, exitVehicles = [], exitPorts, tourDates = [], date }) => {
  const dispatch = useDispatch();
  
  // Make sure we're only working with exit ports
  const validExitPorts = exitPorts && exitPorts.filter(port => port.type === "exit_port");
  console.log("Exit Vehicle - Filtered exitPorts:", validExitPorts);
  
  // Redux state
  const vehicles = useSelector((state) => state.pickupDrop.vehicles1 || []);
  const portZoneType = useSelector((state) => state.pickupDrop.portZoneType);
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  const exitPickup = useSelector((state) => state.pickupDrop.exitpickup);
  const exitDropoff = useSelector((state) => state.pickupDrop.exitdropoff);
  const pickupDate = useSelector((state) => state.pickupDrop.pickupdate);
  const exitTime = useSelector((state) => state.pickupDrop.exittime);
  const adultCount = useSelector((state) => state.pickupDrop.adultCount);
  const childCount = useSelector((state) => state.pickupDrop.childCount);
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  
  // Get existing services from Redux state
  const existingServices = useSelector((state) => state.tourPackages.AllServices || []);
  console.log("Exit Ports:", validExitPorts);
  
  // Use optional chaining for safe access to nested properties
  const adultsMax = tourDetails?.data?.adult || tourDetails?.adult || 1;
  const childrenMax = tourDetails?.data?.child || tourDetails?.child || 0;

  // Component state
  const [seatingCapacity, setSeatingCapacity] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [selectedBookingIndex, setSelectedBookingIndex] = useState(null);
  
  // Track passenger counts for each booking separately
  const [bookingPassengers, setBookingPassengers] = useState({});

  // Initialize bookings from exitPorts data or default empty booking
  const initializeBookings = () => {
    if (validExitPorts && validExitPorts.length > 0) {
      // Pre-populate with existing exit port data
      return validExitPorts.map((exitPort, index) => {
        const exitData = exitPort.data?.[0];
        if (!exitData) {
          // Fallback to default booking if no data
          return {
            id: `exit-${Date.now()}-${index}`,
            vehicle: null,
            vehicleData: null,
            priceMode: null,
            isComplete: false,
            adults: adultCount || 1,
            children: childCount || 0
          };
        }
        
        // Find the corresponding vehicle from the vehicles list
        const matchingVehicle = vehicles.find(v => v.id === exitData.vehicles_id);
        
        return {
          id: exitData.id,
          vehicle: matchingVehicle || {
            id: exitData.vehicles_id,
            vehicle_name: exitData.vehicles_name,
            vehicle_type: exitData.vehicle_type,
            vehicle_model: exitData.vehicle_model,
            model_year: exitData.model_year,
            seating_capacity: exitData.seating_capacity,
            image: exitData.image,
            city: exitData.city,
            country: exitData.country,
            dmc_id: exitData.dmc_id
          },
          vehicleData: {
            // Map the price mode to expected structure
            private_price: exitData.type === 'private' ? exitData.totalPrice : 0,
            shared_price: exitData.type === 'shared' ? exitData.totalPrice : 0,
            prices: {
              privatePrice: exitData.type === 'private' ? exitData.totalPrice : 0,
              sharablePrice: exitData.type === 'shared' ? exitData.totalPrice : 0
            }
          },
          priceMode: exitData.type === 'shared' ? 'Sharable' : 'Private',
          isComplete: true, // Mark as complete since it's loaded data
          adults: exitData.adults || 1,
          children: exitData.children || 0,
          mode: exitData.Mode || 'dmc',
          dmcId: exitData.dmc_id,
          exitpickup: exitData.exitpickup,
          exitdropoff: exitData.exitdropoff,
          bookingDate: exitData.bookingDate,
          exitpickupdate: exitData.exitpickupdate,
          entrytime: exitData.entrytime,
          // Store original loaded data for reference
          originalData: exitData
        };
      });
    }
    
    // Default empty booking when no exitPorts data
    return [{
      id: `exit-${Date.now()}`,
      vehicle: null,
      vehicleData: null,
      priceMode: null,
      isComplete: false,
      adults: adultCount || 1,
      children: childCount || 0
    }];
  };

  // Log exit vehicles prop to track changes
  useEffect(() => {
    console.log("Exit Vehicles Prop:", exitVehicles);
    console.log("Valid Exit Ports Prop:", validExitPorts);
  }, [exitVehicles, validExitPorts]);
  
  // Use a ref to store bookings to prevent them from being lost during re-renders
  const bookingsRef = useRef(initializeBookings());
  
  // Modal states for summary view
  const [openSummaryModal, setOpenSummaryModal] = useState(false);
  const [summaryBookingIndex, setSummaryBookingIndex] = useState(null);
  
  // Debug log initial bookings
  useEffect(() => {
    console.log("Exit Vehicle - Initial bookings state:", bookingsRef.current);
    if (validExitPorts && validExitPorts.length > 0) {
      console.log("Exit Vehicle - Loading with existing exitPorts data");
    } else {
      console.log("Exit Vehicle - Loading with default empty booking");
    }
  }, [validExitPorts]);
  
  // Automatically store exitPorts data into Redux AllServices state when component receives props
  const hasDispatchedAllExitPortsRef = useRef(false);
  const lastDispatchRef = useRef(null);
  
  // Function to dispatch ALL exit ports from exitPorts to Redux state
  const dispatchAllExitPortsToRedux = useCallback(() => {
    if (!validExitPorts || !Array.isArray(validExitPorts) || validExitPorts.length === 0) {
      console.log('No exitPorts data to dispatch to Redux');
      return;
    }

    // Create a unique key for this dispatch to prevent duplicates
    const dispatchKey = JSON.stringify(validExitPorts.map(service => service.data?.[0]?.id));
    
    if (lastDispatchRef.current === dispatchKey) {
      console.log('Skipping duplicate dispatch for all exit ports');
      return;
    }

    console.log('Dispatching ALL exit ports to Redux:', validExitPorts);
    
    // Clone the existing services array
    const currentServices = [...existingServices];
    
    // Filter out existing exit_port services to avoid duplicates
    const filteredServices = currentServices.filter(service => service.type !== "exit_port");
    
    // Add all exit ports to the filtered services array
    const finalServices = [...filteredServices, ...validExitPorts];
    
    console.log('Exit Vehicle - Dispatching ALL exit ports to Redux:', finalServices);
    dispatch(setAllServices(finalServices));
    
    // Update the last dispatch ref
    lastDispatchRef.current = dispatchKey;
    hasDispatchedAllExitPortsRef.current = true;
  }, [validExitPorts, existingServices, dispatch]);
  
  // Dispatch ALL exit ports to Redux when validExitPorts is available (only once)
  useEffect(() => {
    if (!hasDispatchedAllExitPortsRef.current && validExitPorts && Array.isArray(validExitPorts) && validExitPorts.length > 0) {
      console.log('Dispatching ALL exit ports from exitPorts to Redux on mount');
      dispatchAllExitPortsToRedux();
    }
  }, [validExitPorts, dispatchAllExitPortsToRedux]);
  
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
  
  // Function to dispatch initialized bookings to Redux state
  const dispatchInitializedBookingsToRedux = (bookings) => {
    const completedBookings = bookings.filter(booking => booking.isComplete);
    
    if (completedBookings.length > 0) {
      console.log("Exit Vehicle - Dispatching initialized bookings to Redux:", completedBookings);
      
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
          exitpickup: booking.originalData.exitpickup,
          exitdropoff: booking.originalData.exitdropoff,
          bookingDate: booking.originalData.exitpickupdate,
          exitpickupdate: booking.originalData.exitpickupdate,
          entrytime: booking.originalData.entrytime
        } : {
          exitpickup: exitPickup,
          exitdropoff: exitDropoff,
          bookingDate: pickupDate,
          exitpickupdate: pickupDate,
          entrytime: exitTime
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
          type: "exit_port",
          agent_id: agentId,
          tour_id: tourId,
          data: [bookingData]
        };
      });
      
      // Remove any existing Exit Port services and add the new ones
      const filteredServices = existingServices.filter(service => service.type !== "exit_port");
      const finalServices = [...filteredServices, ...bookingsForRedux];
      
      console.log("Exit Vehicle - Dispatching initialized services to Redux:", finalServices);
      dispatch(setAllServices(finalServices));
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
      
      // Update Redux state for the first booking only
      if (bookingIndex === 0) {
        // Here you would dispatch an action to update Redux state if needed
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
    if (selectedVehicleObj && !bookingsRef.current[0].vehicle && (!validExitPorts || validExitPorts.length === 0)) {
      const currentBookings = getBookings();
      const updatedBookings = [...currentBookings];
      updatedBookings[0] = {
        ...updatedBookings[0],
        vehicle: selectedVehicleObj
      };
      setBookings(updatedBookings);
      handleVehicleSelect(selectedVehicleObj, 0);
    }
  }, [selectedVehicleObj, validExitPorts]);
  
  // Re-initialize bookings when exitPorts prop changes
  useEffect(() => {
    if (validExitPorts && validExitPorts.length > 0 && vehicles.length > 0) {
      console.log("Exit Vehicle - Detected exitPorts data, re-initializing bookings:", validExitPorts);
      
      // Re-initialize bookings with the latest exitPorts and vehicles data
      const newBookings = validExitPorts.map((exitPort, index) => {
        const exitData = exitPort.data?.[0];
        if (!exitData) {
          return {
            id: `exit-${Date.now()}-${index}`,
            vehicle: null,
            vehicleData: null,
            priceMode: null,
            isComplete: false,
            adults: adultCount || 1,
            children: childCount || 0
          };
        }
        
        // Find the corresponding vehicle from the vehicles list
        const matchingVehicle = vehicles.find(v => v.id === exitData.vehicles_id);
        
        return {
          id: exitData.id,
          vehicle: matchingVehicle || {
            id: exitData.vehicles_id,
            vehicle_name: exitData.vehicles_name,
            vehicle_type: exitData.vehicle_type,
            vehicle_model: exitData.vehicle_model,
            model_year: exitData.model_year,
            seating_capacity: exitData.seating_capacity,
            image: exitData.image,
            city: exitData.city,
            country: exitData.country,
            dmc_id: exitData.dmc_id
          },
          vehicleData: {
            private_price: exitData.type === 'private' ? exitData.totalPrice : 0,
            shared_price: exitData.type === 'shared' ? exitData.totalPrice : 0,
            prices: {
              privatePrice: exitData.type === 'private' ? exitData.totalPrice : 0,
              sharablePrice: exitData.type === 'shared' ? exitData.totalPrice : 0
            },
            $distanceInKM: exitData.distance || null
          },
          priceMode: exitData.type === 'shared' ? 'Sharable' : 'Private',
          isComplete: true,
          adults: exitData.adults || 1,
          children: exitData.children || 0,
          mode: exitData.Mode || 'dmc',
          dmcId: exitData.dmc_id,
          originalData: exitData
        };
      });
      
      // Store in local state
      bookingsRef.current = newBookings;
      setBookingsVersion(prev => prev + 1);
      
      console.log("Exit Vehicle - Initialized bookings from exitPorts:", newBookings);
      
      // Also store in Redux state in the same format
      dispatchInitializedBookingsToRedux(newBookings);
    }
  }, [validExitPorts, vehicles, adultCount, childCount, existingServices, dispatch, agentId, tourId, exitPickup, exitDropoff, pickupDate, exitTime]);

  // Update completion status when relevant data changes
  useEffect(() => {
    const bookings = getBookings();
    let needsUpdate = false;
    
    console.log("Exit Vehicle - Checking completion status for bookings:", bookings);
    console.log("Exit Vehicle - Required fields:", {
      exitPickup,
      exitDropoff,
      pickupDate,
      exitTime
    });
    
    const updatedBookings = bookings.map(booking => {
      // Check if all required fields are present
      // For loaded bookings, check if they have originalData (indicating they were loaded from exitPorts)
      const isLoadedBooking = booking.originalData !== undefined;
      
      let isComplete;
      if (isLoadedBooking) {
        // For loaded bookings, check if the booking itself has all required data
        isComplete = 
          booking.vehicle !== null && 
          booking.vehicleData !== null && 
          booking.priceMode !== null && booking.priceMode !== '' &&
          booking.originalData.exitpickup !== null && booking.originalData.exitpickup !== undefined && booking.originalData.exitpickup !== '' &&
          booking.originalData.exitdropoff !== null && booking.originalData.exitdropoff !== undefined && booking.originalData.exitdropoff !== '' &&
          booking.originalData.exitpickupdate !== null && booking.originalData.exitpickupdate !== undefined && booking.originalData.exitpickupdate !== '' &&
          booking.originalData.entrytime !== null && booking.originalData.entrytime !== undefined && booking.originalData.entrytime !== '';
      } else {
        // For new bookings, check Redux state values
        isComplete = 
          booking.vehicle !== null && 
          booking.vehicleData !== null && 
          booking.priceMode !== null && booking.priceMode !== '' &&
          exitPickup !== null && exitPickup !== undefined && exitPickup !== '' &&
          exitDropoff !== null && exitDropoff !== undefined && exitDropoff !== '' &&
          pickupDate !== null && pickupDate !== undefined && pickupDate !== '' &&
          exitTime !== null && exitTime !== undefined && exitTime !== '';
      }
      
      console.log(`Exit Vehicle - Booking ${booking.id} isComplete:`, isComplete, `(loaded: ${isLoadedBooking})`);
      
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
      console.log("Exit Vehicle - Completed bookings:", completedBookings);
      
      // For each newly completed booking, dispatch to Redux individually
      completedBookings.forEach((booking, index) => {
        // Find the actual index of this booking in the bookings array
        const actualIndex = updatedBookings.findIndex(b => b.id === booking.id);
        if (actualIndex !== -1) {
          // Check if this booking was just completed (isComplete changed from false to true)
          const originalBooking = bookings.find(b => b.id === booking.id);
          if (originalBooking && !originalBooking.isComplete && booking.isComplete) {
            console.log("Exit Vehicle - Newly completed booking, dispatching to Redux:", booking.id);
            dispatchBookingToRedux(actualIndex, true); // Force update for newly completed bookings
          }
        }
      });
    }
  }, [exitPickup, exitDropoff, pickupDate, exitTime, adultCount, childCount, existingServices, dispatch]);
  
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
      // Store the booking index along with the vehicle information
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
          exitPickup !== null && exitPickup !== undefined && exitPickup !== '' &&
          exitDropoff !== null && exitDropoff !== undefined && exitDropoff !== '' &&
          pickupDate !== null && pickupDate !== undefined && pickupDate !== '' &&
          exitTime !== null && exitTime !== undefined && exitTime !== '';
          
        // Update completion status
        updatedBookings[bookingIndex].isComplete = isComplete;
        
        // Save updated bookings and trigger re-render
        setBookings(updatedBookings);
        
        // If booking is complete, dispatch to Redux right away
        if (isComplete) {
          // Use setTimeout to ensure state is updated before dispatching
          setTimeout(() => {
            dispatchBookingToRedux(bookingIndex);
          }, 0);
        }
        
        setIsLoading(false);
      })
      .catch((err) => {
        console.error("Error fetching vehicle details (Exit):", err);
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
    const booking = currentBookings[bookingIndex];
    
    // Check if this is a loaded booking or a new booking
    const isLoadedBooking = booking.originalData !== undefined;
    
    let hasAllRequiredFields;
    if (isLoadedBooking) {
      // For loaded bookings, check if the booking itself has all required data
      hasAllRequiredFields = 
        booking.vehicle !== null && 
        booking.vehicleData !== null && 
        value !== null && value !== '' &&
        booking.originalData.exitpickup !== null && booking.originalData.exitpickup !== undefined && booking.originalData.exitpickup !== '' &&
        booking.originalData.exitdropoff !== null && booking.originalData.exitdropoff !== undefined && booking.originalData.exitdropoff !== '' &&
        booking.originalData.exitpickupdate !== null && booking.originalData.exitpickupdate !== undefined && booking.originalData.exitpickupdate !== '' &&
        booking.originalData.entrytime !== null && booking.originalData.entrytime !== undefined && booking.originalData.entrytime !== '';
    } else {
      // For new bookings, check Redux state values
      hasAllRequiredFields = 
        booking.vehicle !== null && 
        booking.vehicleData !== null && 
        value !== null && value !== '' &&
        exitPickup !== null && exitPickup !== undefined && exitPickup !== '' &&
        exitDropoff !== null && exitDropoff !== undefined && exitDropoff !== '' &&
        pickupDate !== null && pickupDate !== undefined && pickupDate !== '' &&
        exitTime !== null && exitTime !== undefined && exitTime !== '';
    }
      
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
        service.type === "exit_port" && 
        service.data && 
        service.data.some(item => item.id === booking.id)
      );
      
      if (existingBooking) {
        console.log("Exit Vehicle - Booking already exists in Redux, skipping dispatch:", booking.id);
        return;
      }
    }
    
    console.log("Exit Vehicle - Directly dispatching booking to Redux:", booking);
    
    const vehicle = booking.vehicle;
    const vehicleData = booking.vehicleData;
    const bookingAdultCount = booking.adults || adultCount;
    const bookingChildCount = booking.children || childCount;
    const totalGuests = bookingAdultCount + bookingChildCount;
    
    // Check if this is a loaded booking or a new booking
    const isLoadedBooking = booking.originalData !== undefined;
    
    // Get location and timing data based on booking type
    const locationData = isLoadedBooking ? {
      exitpickup: booking.originalData.exitpickup,
      exitdropoff: booking.originalData.exitdropoff,
      bookingDate: booking.originalData.exitpickupdate,
      exitpickupdate: booking.originalData.exitpickupdate,
      entrytime: booking.originalData.entrytime
    } : {
      exitpickup: exitPickup,
      exitdropoff: exitDropoff,
      bookingDate: pickupDate,
      exitpickupdate: pickupDate,
      entrytime: exitTime
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
    
    // Create booking data matching the exact parameter names from index2.jsx details object
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
      vehicles_name: vehicle.vehicle_name,
      dmc_id: booking.dmcId,
      Mode: booking.mode,
      type: booking.priceMode === "Sharable" ? "shared" : "private",
      image: vehicle.image,
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
    
    console.log("Exit Vehicle - Formatted booking data for Redux:", bookingData);
    
    // Clone the existing services array
    const allCurrentServices = [...existingServices];
    
    // Remove any existing Exit Port service with the same booking ID
    const filteredServices = allCurrentServices.filter(service => {
      if (service.type === "Exit Port") {
        // If this is an Exit Port service, check if it contains our booking ID
        if (service.data && service.data.some(item => item.id === booking.id)) {
          // This service contains our booking ID, so filter it out
          return false;
        }
      }
      // Keep all other services
      return true;
    });
    
    // Create a new Exit Port entry for this vehicle
    const newExitPortService = {
      type: "exit_port",
      agent_id: agentId,
      tour_id: tourId,
      data: [bookingData]
    };
    
    // Add the new Exit Port service to the filtered services array
    filteredServices.push(newExitPortService);
    
    console.log("Exit Vehicle - Dispatching updated services to Redux:", filteredServices);
    
    // Dispatch the updated services
    dispatch(setAllServices(filteredServices));
  };
  
  // Add a new booking
  const handleAddMoreBooking = () => {
    const bookings = getBookings();
    const newBooking = { 
      id: `exit-${Date.now()}`, // Use timestamp for unique ID with type prefix
      vehicle: null, 
      vehicleData: null, 
      priceMode: null,
      isComplete: false,
      adults: adultCount || 1,
      children: childCount || 0
    };
    setBookings([...bookings, newBooking]);
  };
  
  // Remove a booking
  const handleRemoveBooking = (indexToRemove) => {
    const bookings = getBookings();
    const bookingToRemove = bookings[indexToRemove];
    
    if (bookingToRemove) {
      // Remove from local state
      const updatedBookings = bookings.filter((_, index) => index !== indexToRemove);
      setBookings(updatedBookings);
      
      // Remove from Redux state if it has a vehicleId
      if (bookingToRemove.vehicle && bookingToRemove.vehicle.id) {
        // Clone the existing services array
        const currentServices = [...existingServices];
        
        // Filter out the booking with matching ID or type+vehicleId
        const filteredServices = currentServices.filter(service => {
          // If the booking has an ID, use that for exact matching
          if (bookingToRemove.id && service.id === bookingToRemove.id) {
            return false;
          }
          
          // Otherwise match by type and vehicleId
          if (service.type === "Exit Port" && 
              service.vehicleId === bookingToRemove.vehicle.id) {
            return false;
          }
          
          return true;
        });
        
        // Only dispatch if there's an actual change
        if (filteredServices.length !== currentServices.length) {
          console.log(`Removing Exit Port booking from Redux:`, bookingToRemove);
          dispatch(setAllServices(filteredServices));
        }
      }
    }
  };
  
  // Modify handleOpenSummaryModal to ensure data is in Redux before showing modal
  const handleOpenSummaryModal = (index) => {
    const bookings = getBookings();
    const booking = bookings[index];
    
    // Only dispatch to Redux if booking is complete and not already in Redux
    if (booking && booking.isComplete) {
      const existingBooking = existingServices.find(service => 
        service.type === "exit_port" && 
        service.data && 
        service.data.some(item => item.id === booking.id)
      );
      
      if (!existingBooking) {
        console.log("Exit Vehicle - Booking not in Redux, dispatching before showing modal");
        dispatchBookingToRedux(index);
      } else {
        console.log("Exit Vehicle - Booking already in Redux, showing modal directly");
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
      pickupLocation: booking.originalData.exitpickup,
      dropoffLocation: booking.originalData.exitdropoff,
      bookingDate: booking.originalData.exitpickupdate,
      pickupTime: booking.originalData.entrytime
    } : {
      pickupLocation: exitPickup,
      dropoffLocation: exitDropoff,
      bookingDate: pickupDate,
      pickupTime: exitTime
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

  // Helper to check if a booking is out of current tour dates for the specific dayIndex
  const isBookingOutOfTourDates = (booking) => {
    // Skip validation if tourDates is not provided
    if (!tourDates || tourDates.length === 0) {
      return false;
    }
    
    // Get the booking date from original data or current date
    const bookingDate = booking.originalData?.bookingDate || booking.originalData?.exitpickupdate || (date ? date.format('YYYY-MM-DD') : null);
    
    // Debug logging to check date formats
    console.log('Exit Port date validation debug:', {
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
    
    console.log('Exit Port date validation result:', {
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
      hasDispatchedAllExitPortsRef.current = false;
      lastDispatchRef.current = null;
    };
  }, []);

  return (
    <Box sx={{ mt: 3 }}>
      {/* Header Card with Gradient Background */}
      
      {/* Multiple Booking Cards */}
      <Grid container spacing={3}>
        {bookings.map((booking, index) => {
          const completionStatus = booking.isComplete ? 3 : 
            (booking.vehicle ? 1 : 0) + (booking.adults + booking.children > 0 ? 1 : 0) + 
            (booking.priceMode ? 1 : 0);
          const outOfTourDates = isBookingOutOfTourDates(booking);
          
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
                      <FlightTakeoffIcon sx={{ color: '#3b82f6', fontSize: 24 }} />
                      <Chip 
                        label={`Exit Port #${index + 1}`}
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
                      {bookings.length > 1 && (
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
                      )}
                      
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
                              label={`Select Exit Vehicle ${index > 0 ? '#' + (index + 1) : ''}`}
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
                            <InputLabel id={`price-mode-label-exit-${index}`}>Price Mode</InputLabel>
                            <Select
                              labelId={`price-mode-label-exit-${index}`}
                              id={`price-mode-select-exit-${index}`}
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
        portType="Exit Port"
      />
    </Box>
  );
};

export default VehicleListDropdown1; 