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

export default function LocalTransportComponent({ dayIndex = 0, date , PointToPoint, Hourly, LocalTransports, tourDates = [] }) {
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
  
  // Refs for tracking and preventing loops
  // Refs for tracking and preventing loops
  const prevBookingsRef = useRef([]);
  const prevServicesRef = useRef([]);
  const prevVehicles = useRef([]);
  const isDispatching = useRef(false);
  const lastDispatchTime = useRef(0);
  const dispatchTimeoutRef = useRef(null);

  // Track which bookings have already been saved to Redux
  const [savedBookingIds, setSavedBookingIds] = useState([]);

  // Function to initialize bookings from props data
  const initializeBookingsFromProps = useCallback(() => {
    const initializedBookings = [];
    const processedIds = new Set(); // Track processed booking IDs to avoid duplicates
    
    // console.log(`Initializing bookings for dayIndex ${dayIndex} with date:`, date);
    
    // Helper function to check if booking belongs to current dayIndex
    // Similar to attraction component's dayIndex filtering
    const shouldShowBookingForThisDay = (bookingDate) => {
      if (!date || !bookingDate) return false;
      
      try {
        // The 'date' prop from Itinerary is the specific date for this dayIndex
        // Compare booking date with current day's date
        let currentDayDateStr;
        let bookingDateStr;
        
        // Handle the date prop (could be moment object or string)
        if (typeof date === 'object' && date.format) {
          // If it's a moment object
          currentDayDateStr = date.format('YYYY-MM-DD');
        } else if (typeof date === 'string') {
          // If it's already a string, try to normalize it
          const tempDate = new Date(date);
          if (!isNaN(tempDate.getTime())) {
            currentDayDateStr = tempDate.toISOString().split('T')[0];
          } else {
            currentDayDateStr = date; // Assume it's already in YYYY-MM-DD format
          }
        } else {
          return false;
        }
        
        // Handle booking date
        if (typeof bookingDate === 'string') {
          const tempDate = new Date(bookingDate);
          if (!isNaN(tempDate.getTime())) {
            bookingDateStr = tempDate.toISOString().split('T')[0];
          } else {
            bookingDateStr = bookingDate; // Assume it's already in YYYY-MM-DD format
          }
        } else {
          return false;
        }
        
                 // Show booking if it matches current day
         const matchesCurrentDay = currentDayDateStr === bookingDateStr;
         
         // For orphaned bookings (dates not in tour dates), show them only in dayIndex 0 to avoid duplication
         const isOrphanedBooking = tourDates && tourDates.length > 0 && !tourDates.includes(bookingDateStr);
         const showOrphanedInFirstDay = isOrphanedBooking && dayIndex === 0;
         
         // For debugging
         if (matchesCurrentDay || showOrphanedInFirstDay) {
           console.log(`Local Transport - Showing booking for dayIndex ${dayIndex}:`, {
             bookingDate: bookingDateStr,
             currentDayDate: currentDayDateStr,
             matchesCurrentDay,
             isOrphanedBooking,
             showOrphanedInFirstDay,
             dayIndex,
             reason: matchesCurrentDay ? 'matches current day' : 'orphaned booking (shown in dayIndex 0 only)'
           });
         }
         
         return matchesCurrentDay || showOrphanedInFirstDay;
      } catch (error) {
        console.error('Error comparing dates for dayIndex filtering:', error);
        return false;
      }
    };

    // Process PointToPoint data
    if (PointToPoint && Array.isArray(PointToPoint)) {
      PointToPoint.forEach(pointToPointService => {
        if (pointToPointService.data && Array.isArray(pointToPointService.data)) {
          pointToPointService.data.forEach(bookingData => {
            // Check for duplicate IDs
            if (processedIds.has(bookingData.id)) {
              return;
            }
            processedIds.add(bookingData.id);
            
            // Check if booking belongs to current dayIndex (similar to attraction component)
            if (!shouldShowBookingForThisDay(bookingData.bookingDate)) {
              return;
            }
            
            const booking = {
              id: bookingData.id,
              vehicle: null, 
              vehicleId: bookingData.vehicles_id,
              mode: bookingData.Mode,
              dmcId: bookingData.dmc_id,
              city: bookingData.city,
              country: bookingData.country,
              adults: bookingData.adults,
              children: bookingData.children,
              priceMode: bookingData.type,
              hours: 1,
              transportType: "Point To Point",
              vehicleName: bookingData.vehicles_name,
              vehicleImage: bookingData.image,
              vehicleModel: '',
              vehicleType: '',
              zoneId: '',
              pickupLocation: bookingData.entrypickup,
              dropoffLocation: bookingData.entrydropoff,
              pickupTime: bookingData.entrytime,
              bookingDate: bookingData.bookingDate,
              price: bookingData.totalPrice,
              isComplete: true,
              originalData: bookingData
            };
            initializedBookings.push(booking);
          });
        }
      });
    }

    // Process Hourly data
    if (Hourly && Array.isArray(Hourly)) {
      Hourly.forEach(hourlyService => {
        if (hourlyService.data && Array.isArray(hourlyService.data)) {
          hourlyService.data.forEach(bookingData => {
            // Check for duplicate IDs
            if (processedIds.has(bookingData.id)) {
              return;
            }
            processedIds.add(bookingData.id);
            
            // Check if booking belongs to current dayIndex (similar to attraction component)
            if (!shouldShowBookingForThisDay(bookingData.bookingDate)) {
              return;
            }
            
            const booking = {
              id: bookingData.id,
              vehicle: null, 
              vehicleId: bookingData.vehicles_id,
              mode: bookingData.Mode,
              dmcId: bookingData.dmc_id,
              city: bookingData.city,
              country: bookingData.country,
              adults: bookingData.adults,
              children: bookingData.children,
              priceMode: bookingData.type,
              hours: bookingData.selectedHours || 1,
              transportType: "Hourly",
              vehicleName: bookingData.vehicles_name,
              vehicleImage: bookingData.image,
              vehicleModel: '',
              vehicleType: '',
              zoneId: '',
              pickupLocation: bookingData.entrypickup,
              dropoffLocation: '',
              pickupTime: bookingData.entrytime,
              bookingDate: bookingData.bookingDate,
              price: bookingData.totalPrice,
              isComplete: true,
              originalData: bookingData
            };
            initializedBookings.push(booking);
          });
        }
      });
    }

    // Process LocalTransports data
    if (LocalTransports && Array.isArray(LocalTransports)) {
      LocalTransports.forEach(localService => {
        if (localService.data && Array.isArray(localService.data)) {
          localService.data.forEach(bookingData => {
            // Check for duplicate IDs
            if (processedIds.has(bookingData.id)) {
              return;
            }
            processedIds.add(bookingData.id);
            
            // Check if booking belongs to current dayIndex (similar to attraction component)
            if (!shouldShowBookingForThisDay(bookingData.bookingDate)) {
              return;
            }
            
            const booking = {
              id: bookingData.id,
              vehicle: null, 
              vehicleId: bookingData.vehicles_id,
              mode: bookingData.Mode,
              dmcId: bookingData.dmc_id,
              city: bookingData.city,
              country: bookingData.country,
              adults: bookingData.adults,
              children: bookingData.children,
              priceMode: bookingData.type,
              hours: 1,
              transportType: "Local Transfer",
              vehicleName: bookingData.vehicles_name,
              vehicleImage: bookingData.image,
              vehicleModel: '',
              vehicleType: '',
              zoneId: bookingData.to_zone_id || '',
              pickupLocation: bookingData.entrypickup,
              dropoffLocation: bookingData.entrydropoff,
              pickupTime: bookingData.entrytime,
              bookingDate: bookingData.bookingDate,
              price: bookingData.totalPrice,
              isComplete: true,
              originalData: bookingData
            };
            initializedBookings.push(booking);
          });
        }
      });
    }
    
    if (initializedBookings.length > 0) {
      console.log(`Found ${initializedBookings.length} bookings for dayIndex ${dayIndex}`);
    }
    
    return initializedBookings;
  }, [date, dayIndex, PointToPoint, Hourly, LocalTransports, tourDates]);

  // Function to dispatch initialized bookings to Redux - only handles bookings for this specific dayIndex
  const dispatchInitializedBookingsToRedux = useCallback((bookings) => {
    const completedBookings = bookings.filter(booking => booking.isComplete);
    
    if (completedBookings.length > 0) {
      console.log(`Local Transport - Dispatching ${completedBookings.length} initialized bookings to Redux for dayIndex ${dayIndex}`);
      
      // Instead of replacing all services, we'll just ensure our original services are in Redux
      // This prevents conflicts between different component instances
      const currentServices = [...allServices];
      let hasUpdates = false;
      
      // Process each original booking to restore its service entry
      completedBookings.forEach(booking => {
        if (booking.originalData) {
          // Find the original service this booking belongs to
          const originalService = [PointToPoint, Hourly, LocalTransports]
            .flat()
            .find(service => 
              service.data && service.data.some(item => item.id === booking.originalData.id)
            );
          
          if (originalService) {
            // Check if this service is already in Redux
            const existingServiceIndex = currentServices.findIndex(service => 
              service.id === originalService.id
            );
            
            if (existingServiceIndex === -1) {
              // Service not in Redux, add it
              console.log(`Adding missing service to Redux: ${originalService.type} (ID: ${originalService.id})`);
              currentServices.push({
                ...originalService,
                agent_id: agentId || originalService.agent_id,
                tour_id: tourId || originalService.tour_id
              });
              hasUpdates = true;
            } else {
              // Service exists, ensure it has the correct data
              const existingService = currentServices[existingServiceIndex];
              const originalBookingExists = existingService.data?.some(item => 
                item.id === booking.originalData.id
              );
              
              if (!originalBookingExists) {
                // Add missing booking to existing service
                console.log(`Adding missing booking to existing service: ${booking.originalData.id}`);
                currentServices[existingServiceIndex] = {
                  ...existingService,
                  data: [...(existingService.data || []), booking.originalData]
                };
                hasUpdates = true;
              }
            }
          }
        }
      });
      
      // Only dispatch if we actually made changes
      if (hasUpdates) {
        console.log(`Local Transport - Dispatching updated services to Redux for dayIndex ${dayIndex}`);
        dispatch(setAllServices(currentServices));
      } else {
        console.log(`Local Transport - No updates needed for dayIndex ${dayIndex}, services already in Redux`);
      }
    }
  }, [allServices, dispatch, agentId, tourId, PointToPoint, Hourly, LocalTransports, dayIndex]);

  // Validation function
  const isBookingValid = useCallback((section) => {
    return section.vehicleId && 
           (section.adults + section.children > 0) && 
           section.priceMode && 
           section.price > 0 &&
           section.price > 0 &&
           (section.transportType !== "Hourly" || (section.hours && section.hours >= 1));
  }, []);

  // Improved dispatch function with better debouncing and consolidation
  const dispatchValidBookingsToRedux = useCallback(() => {


  // Handler functions
    // Clear any pending dispatch
    if (dispatchTimeoutRef.current) {
      clearTimeout(dispatchTimeoutRef.current);
    }

    // Debounce the dispatch to prevent rapid calls
    dispatchTimeoutRef.current = setTimeout(() => {
      const now = Date.now();
      
      // Prevent dispatching too frequently
      if (now - lastDispatchTime.current < 1000) {
        console.log("Local Transport - Dispatch frequency limit reached, skipping...");
        return;
      }

      if (isDispatching.current) {
        console.log("Local Transport - Dispatch already in progress, skipping...");
        return;
      }

      // Group bookings by type for batch processing - only process new bookings, not original data
      const validBookings = {
        "Point To Point": [],
        "Hourly": [],
        "Local Transfer": []
      };
      
      // Collect all valid bookings that haven't been saved yet
      allBookings.forEach((booking, index) => {
        if (isBookingValid(booking) && booking.transportType && !booking.originalData) {
          // Create a unique signature for this booking
          const bookingSignature = `${booking.vehicleId}-${booking.priceMode}-${booking.price}-${booking.adults}-${booking.children}-${booking.pickupLocation || ''}-${booking.dropoffLocation || ''}-${booking.hours || 1}`;
          
          // Only include if not already saved
          if (!savedBookingIds.includes(bookingSignature)) {
            validBookings[booking.transportType].push({...booking, index, signature: bookingSignature});
          }
        }
      });
      
      const totalValidBookings = Object.values(validBookings).reduce((sum, bookings) => sum + bookings.length, 0);
      
      if (totalValidBookings === 0) {
        console.log("Local Transport - No new valid bookings to dispatch");
        return;
      }
      
      console.log(`Local Transport - Dispatching ${totalValidBookings} new valid bookings to Redux`);
      isDispatching.current = true;
      lastDispatchTime.current = now;
      
      try {
        // Start with a copy of current services
        const updatedServices = [...allServices];
        const newSavedSignatures = [];
        
        // Process each transport type separately but consolidate the dispatch
        Object.entries(validBookings).forEach(([transportType, bookings]) => {
          if (bookings.length === 0) return;
          
          let serviceType;
          if (transportType === "Point To Point") {
            serviceType = "travel_point";
          } else if (transportType === "Hourly") {
            serviceType = "travel_hourly";
          } else if (transportType === "Local Transfer") {
            serviceType = "local_transport";
          } else {
            return;
          }
          
          const customerInfoService = allServices.find(service => service.type === 'CustomerInfo');
          const bookingsData = bookings.map(booking => {
            // Base booking data structure
            const bookingId = booking.id || `${booking.transportType.toLowerCase().replace(/\s+/g, '-')}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
            
            let bookingData = {
              id: bookingId,
              Mode: booking.mode,
              dmc_id: booking.dmcId,
              fullName: customerInfoService?.fullName || '',
              email: customerInfoService?.email || '',
              phone: customerInfoService?.phone || '',
              country: booking.country || '',
              countryCode: customerInfoService?.countryCode || '',
              state: customerInfoService?.state || '',
              city: booking.city || '',
              zip: customerInfoService?.zip || '',
              address1: customerInfoService?.address1 || '',
              address2: customerInfoService?.address2 || '',
              bookingDate: booking.bookingDate,
              pickupdate: booking.bookingDate,
              entrytime: booking.pickupTime,
              vehicles_id: booking.vehicleId,
              vehicles_name: booking.vehicleName,
              type: booking.priceMode,
              adults: booking.adults,
              children: booking.children,
              specialRequests: customerInfoService?.specialRequests || '',
              image: booking.vehicleImage || '',
              totalPrice: Math.ceil(booking.price || 0)
            };
            
            // Add transport type specific parameters
            if (transportType === "Point To Point") {
              bookingData = {
                ...bookingData,
                entrypickup: booking.pickupLocation || '',
                entrydropoff: booking.dropoffLocation || '',
                PickupPlaceid: booking.PickupPlaceid || null,
                DropoffPlaceid: booking.DropoffPlaceid || null,
                distance: booking.distance || 0,
                Tax: booking.Tax || 0,
                Night_Start_Time: booking.Night_Start_Time || null,
                Night_End_Time: booking.Night_End_Time || null
              };
            } else if (transportType === "Hourly") {
              bookingData = {
                ...bookingData,
                entrypickup: booking.pickupLocation || '',
                PickupPlaceid: booking.PickupPlaceid || null,
                exitpickupdate: booking.bookingDate,
                selectedHours: booking.hours || 1,
                Tax: booking.Tax || 0,
                Night_Start_Time: booking.Night_Start_Time || null,
                Night_End_Time: booking.Night_End_Time || null
              };
            } else if (transportType === "Local Transfer") {
              bookingData = {
                ...bookingData,
                entrypickup: booking.pickupLocation || '',
                entrydropoff: booking.dropoffLocation || '',
                PickupPlaceid: booking.PickupPlaceid || null,
                DropoffPlaceid: booking.DropoffPlaceid || null,
                to_zone_id: booking.to_zone_id || booking.zoneId || '',
                from_zone_id: booking.from_zone_id || ''
              };
            }
            
            // Update local state with the generated ID if needed
            if (!booking.id) {
              setAllBookings(prevBookings => {
                const updatedBookings = [...prevBookings];
                if (updatedBookings[booking.index]) {
                  updatedBookings[booking.index] = {
                    ...updatedBookings[booking.index],
                    id: bookingId
                  };
                }
                return updatedBookings;
              });
            }
            
            // Track this booking as processed
            newSavedSignatures.push(booking.signature);
            
            return bookingData;
          });
          
          // Always create a new service for each new booking - don't merge with existing services
          // Each booking gets its own separate service object
          bookingsData.forEach(bookingData => {
            const newService = {
              booking_id: Math.floor(Math.random() * 10000), // Generate a unique booking_id
              agent_id: agentId,
              type: serviceType,
              tour_id: tourId,
              data: [bookingData] // Each service contains only one booking
            };
            updatedServices.push(newService);
            console.log(`Local Transport - Created new ${transportType} service with booking ID: ${bookingData.id}`);
          });
          
          console.log(`Local Transport - Processed ${bookings.length} ${transportType} bookings for Redux`);
        });
        
        // Single consolidated dispatch for all transport types
        if (newSavedSignatures.length > 0) {
          console.log(`Local Transport - Single dispatch for ${newSavedSignatures.length} new bookings to Redux`);
          
          // Log the service format being created for debugging
          const newServices = updatedServices.filter(service => 
            ["travel_point", "travel_hourly", "local_transport"].includes(service.type)
          );
          console.log('Local Transport - Service format being dispatched:', JSON.stringify(newServices, null, 2));
          
          dispatch(setAllServices(updatedServices));
          
          // Update saved booking signatures
          setSavedBookingIds(prev => [...prev, ...newSavedSignatures]);
        }
        
      } catch (error) {
        console.error("Local Transport - Error dispatching bookings to Redux:", error);
      } finally {
        setTimeout(() => {
          isDispatching.current = false;
        }, 500);
      }
    }, 300);
  }, [allBookings, allServices, dispatch, agentId, tourId, isBookingValid, savedBookingIds]);

  // Reset vehicles and saved booking IDs when component unmounts or dayIndex changes
  useEffect(() => {
    return () => {
      dispatch(resetVehicles1());
      if (dispatchTimeoutRef.current) {
        clearTimeout(dispatchTimeoutRef.current);
      }
      // Clear saved booking IDs on unmount to prevent stale state
      setSavedBookingIds([]);
    };
  }, [dispatch]);

  // Clear saved booking IDs when dayIndex changes
  useEffect(() => {
    setSavedBookingIds([]);
    console.log(`Local Transport - Cleared saved booking IDs for new dayIndex: ${dayIndex}`);
  }, [dayIndex]);

  // Flag to track if initialization has been done for this specific component instance
  const [hasInitializedBookings, setHasInitializedBookings] = useState(false);
  const [hasDispatchedToRedux, setHasDispatchedToRedux] = useState(false);
  const [lastInitializationKey, setLastInitializationKey] = useState('');
  
  // Create a unique key for this component instance to prevent double initialization
  const initializationKey = useMemo(() => {
    return `${dayIndex}-${date}-${PointToPoint?.length || 0}-${Hourly?.length || 0}-${LocalTransports?.length || 0}`;
  }, [dayIndex, date, PointToPoint?.length, Hourly?.length, LocalTransports?.length]);
  
  // Initialize bookings from props data when available - only run once per unique key
  useEffect(() => {
    // Skip if we've already initialized for this specific instance
    if (hasInitializedBookings && lastInitializationKey === initializationKey) return;
    
    if ((PointToPoint && PointToPoint.length > 0) || 
        (Hourly && Hourly.length > 0) || 
        (LocalTransports && LocalTransports.length > 0)) {
      
      console.log(`Local Transport - Initializing bookings for dayIndex ${dayIndex}`);
      
      // Mark initialization as done for this key
      setHasInitializedBookings(true);
      setLastInitializationKey(initializationKey);
      
      // Get all bookings from props
      const initializedBookings = initializeBookingsFromProps();
      
      if (initializedBookings.length > 0) {
        // Set the bookings in local state - replace any existing bookings for this component
        setAllBookings(initializedBookings);
        setExpandedSections(initializedBookings.map((_, index) => index));
        
        // Update search performed state for the transport types that have data
        const transportTypesWithData = [...new Set(initializedBookings.map(b => b.transportType))];
        setSearchPerformed(prev => {
          const updated = { ...prev };
          transportTypesWithData.forEach(type => {
            updated[type] = true;
          });
          return updated;
        });
      }
    }
  }, [initializationKey, hasInitializedBookings, lastInitializationKey, initializeBookingsFromProps]);

  // Separate effect to dispatch original bookings to Redux - only once
  // Separate effect to dispatch original bookings to Redux - only once
  useEffect(() => {
    if (hasInitializedBookings && !hasDispatchedToRedux && allBookings.length > 0) {
      // Only dispatch bookings that have originalData (came from props)
      const originalBookings = allBookings.filter(booking => booking.originalData);
      
      if (originalBookings.length > 0) {
        console.log(`Dispatching ${originalBookings.length} original bookings to Redux for dayIndex ${dayIndex}`);
        dispatchInitializedBookingsToRedux(originalBookings);
        setHasDispatchedToRedux(true);
      }
    }
  }, [hasInitializedBookings, hasDispatchedToRedux, allBookings, dispatchInitializedBookingsToRedux, dayIndex]);
  

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
        
        // Use setTimeout to prevent immediate state updates that could cause loops
        setTimeout(() => {
          setAllBookings(prev => {
            const newBookings = [...prev, newBookingData];
            const newIndex = newBookings.length - 1;
            setExpandedSections(prevExpanded => [...prevExpanded, newIndex]);
            return newBookings;
          });
        }, 50);
      }
    }
  }, [hasVehicles, selectedPort]); // Reduced dependencies to prevent loops

  // Improved booking validation and auto-dispatch trigger
  useEffect(() => {
    // Skip if no bookings or during loading
    if (allBookings.length === 0) return;
    
    // Find bookings that are complete but not yet saved
    const newCompleteBookings = allBookings.filter((booking, index) => {
      // Check if all required fields are filled and it's not from original data
      const isComplete = isBookingValid(booking) && !booking.originalData;
      
      // Generate a unique signature for this booking
      const bookingSignature = `${booking.vehicleId}-${booking.priceMode}-${booking.price}-${booking.adults}-${booking.children}-${booking.pickupLocation || ''}-${booking.dropoffLocation || ''}-${booking.hours || 1}`;
      
      // Check if this booking has already been saved
      const isSaved = savedBookingIds.includes(bookingSignature);
      
      // Return true if this booking is complete and not yet saved
      return isComplete && !isSaved;
    });
    
    // If we found new complete bookings, trigger dispatch
    if (newCompleteBookings.length > 0) {
      console.log(`Local Transport - Auto dispatch triggered for ${newCompleteBookings.length} complete bookings on dayIndex ${dayIndex}`);
      
      // Wait a bit to avoid too many Redux updates
      const timeoutId = setTimeout(() => {
        dispatchValidBookingsToRedux();
      }, 500);
      
      return () => clearTimeout(timeoutId);
    }
  }, [allBookings, isBookingValid, savedBookingIds, dispatchValidBookingsToRedux, dayIndex]);

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
        newBookings[sectionIndex] = {
          ...newBookings[sectionIndex],
          price
        };
      }
      
      return newBookings;
    });
  }, []);

  const handleHourlyPriceChange = useCallback((sectionIndex, totalHourlyPrice) => {
    setAllBookings(prevBookings => {
      const newBookings = [...prevBookings];
      
      if (newBookings[sectionIndex].price !== totalHourlyPrice) {
        newBookings[sectionIndex] = {
          ...newBookings[sectionIndex],
          price: totalHourlyPrice
        };
      }
      
      return newBookings;
    });
  }, []);

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
      // Generate the signature for this booking to remove it from saved IDs
      const bookingSignature = `${bookingToRemove.vehicleId}-${bookingToRemove.priceMode}-${bookingToRemove.price}-${bookingToRemove.adults}-${bookingToRemove.children}-${bookingToRemove.pickupLocation || ''}-${bookingToRemove.dropoffLocation || ''}-${bookingToRemove.hours || 1}`;
      
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
      
      // Remove the booking signature from saved IDs
      setSavedBookingIds(prev => prev.filter(signature => signature !== bookingSignature));
      console.log(`Local Transport - Removed booking signature from saved IDs: ${bookingSignature.substring(0, 50)}...`);
      
      setExpandedSections(prev => 
        prev.filter(index => index !== indexToRemove)
            .map(index => index > indexToRemove ? index - 1 : index)
      );
      
      if (bookingToRemove.vehicleId) {
        const currentServices = [...allServices];
        
        const filteredServices = currentServices.filter(service => {
          // Remove service if it contains the booking we want to remove
          if (service.data && Array.isArray(service.data)) {
            // Check if this service contains the booking to remove
            const containsBooking = service.data.some(item => item.id === bookingToRemove.id);
            return !containsBooking;
          }
          
          // Keep other services that don't match our booking
          return true;
        });
        
        if (filteredServices.length !== currentServices.length) {
          console.log(`Local Transport - Removing ${bookingToRemove.transportType} booking from Redux`);
          dispatch(setAllServices(filteredServices));
          prevServicesRef.current = filteredServices;
        }
      }
    }
  }, [allBookings, allServices, dispatch]);

  const handleOpenModal = useCallback((index) => {
    setSelectedSectionIndex(index);
    setOpenModal(true);
  }, []);
  

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
    
    // If booking has a vehicle from props data, create a vehicle object for it
    if (booking.originalData && booking.vehicleId && booking.vehicleName) {
      const mockVehicle = {
        id: booking.vehicleId,
        vehicle_name: booking.vehicleName,
        image: booking.vehicleImage,
        city: booking.city,
        country: booking.country,
        dmc_id: booking.dmcId,
        vehicle_type: booking.vehicleType || 'Unknown',
        vehicle_model: booking.vehicleModel || 'Unknown',
        model_year: 'Unknown',
        seating_capacity: 0,
        dmc_private_price: 0,
        dmc_sharable_price: 0,
        trav_private_price: 0,
        trav_sharable_price: 0,
        tax_percentage: 0
      };
      
      // Return the mock vehicle along with any cached vehicles
      const existingVehicles = cachedVehicles[bookingType] || vehicles || [];
      const vehicleExists = existingVehicles.some(v => v.id === booking.vehicleId);
      
      if (!vehicleExists) {
        return [mockVehicle, ...existingVehicles];
      }
    }
    
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

  // Helper to check if a booking is out of current tour dates
  const isBookingOutOfTourDates = (booking) => {
    const bookingDate = booking.originalData?.bookingDate || booking.bookingDate;
    
    // Debug logging to check date formats
    console.log('Local Transport - Date validation debug:', {
      bookingId: booking.originalData?.id || 'new-booking',
      bookingDate: bookingDate,
      tourDates: tourDates,
      dayIndex: dayIndex,
      transportType: booking.transportType
    });
    
    // Handle edge cases
    if (!bookingDate || !tourDates || tourDates.length === 0) {
      console.log('Local Transport - Missing bookingDate or tourDates, skipping validation');
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
      console.error('Local Transport - Error normalizing booking date:', error);
      return false;
    }
    
    // Check if the normalized booking date exists in tourDates
    const isDateValid = tourDates.includes(normalizedBookingDate);
    
    console.log('Local Transport - Date validation result:', {
      normalizedBookingDate: normalizedBookingDate,
      isDateValid: isDateValid,
      willShowError: !isDateValid,
      tourDatesCount: tourDates.length
    });
    
    return !isDateValid;
  };

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
            <SearchLocationTransport Location={Location} dayIndex={dayIndex} date={date}/>
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
        <SearchLocationTransport Location={Location} dayIndex={dayIndex} date={date} />
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
          const outOfTourDates = isBookingOutOfTourDates(booking);
          
          return (
            <Grid item xs={12} key={`booking-${bookingIndex}`}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: outOfTourDates ? '2px solid #e53935' : `2px solid ${alpha('#ff6b6b', 0.2)}`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  border: outOfTourDates ? '2px solid #e53935' : `2px solid ${alpha('#ff6b6b', 0.2)}`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: outOfTourDates
                      ? `0 8px 24px ${alpha('#e53935', 0.15)}`
                      : `0 8px 24px ${alpha('#ff6b6b', 0.15)}`,
                    boxShadow: outOfTourDates
                      ? `0 8px 24px ${alpha('#e53935', 0.15)}`
                      : `0 8px 24px ${alpha('#ff6b6b', 0.15)}`,
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
                        {booking.transportType === "Point To Point" && (searchPerformed["Point To Point"] || booking.originalData) && (
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
                            isNewBooking={!booking.vehicleId && !booking.originalData}
                            cachedVehicles={getVehiclesForBooking(booking)}
                            cachedVehicleName={booking.vehicleName}
                            preloadedBooking={booking.originalData ? booking : null}
                          />
                        )}
                        
                        {booking.transportType === "Hourly" && (searchPerformed["Hourly"] || booking.originalData) && (
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
                            isNewBooking={!booking.vehicleId && !booking.originalData}
                            cachedVehicles={getVehiclesForBooking(booking)}
                            cachedVehicleName={booking.vehicleName}
                            preloadedBooking={booking.originalData ? booking : null}
                          />
                        )}
                        
                        {booking.transportType === "Local Transfer" && (searchPerformed["Local Transfer"] || booking.originalData) && (
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
                            isNewBooking={!booking.vehicleId && !booking.originalData}
                            cachedVehicles={getVehiclesForBooking(booking)}
                            cachedVehicleName={booking.vehicleName}
                            preloadedBooking={booking.originalData ? booking : null}
                          />
                        )}
                      </Box>
                    </Paper>
                  </Collapse>
                  
                  {/* Red alert if out of tour dates */}
                  {outOfTourDates && (
                    <Box sx={{ px: 2, pt: 1 }}>
                      <Alert severity="error" sx={{ borderRadius: 2, mb: 1 }}>
                        The {booking.transportType} booking is out of currently updated tour dates
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