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
import { setSelectedVehicle, resetVehicles1, clearSearchDayIndex } from '@/slice/localtour/Localslice';
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

const LocalTransportComponent = React.memo(function LocalTransportComponent({ dayIndex = 0, date , PointToPoint, Hourly, LocalTransports, tourDates = [] }) {
  const theme = useTheme();
  const dispatch = useDispatch();
  // Redux selectors
  const Location = useSelector((state) => state.bookings?.searchLocation || {});
  const vehicles = useSelector(state => state.localtour.vehicles || []);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const allServices = useSelector((state) => state.tourPackages.AllServices || []);
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  const searchDayIndex = useSelector((state) => state.localtour.searchDayIndex);


  
  // Location data from Redux
  const pickupLocation = useSelector(state => state.localtour.entrypickup || '');
  const dropoffLocation = useSelector(state => state.localtour.entrydropoff || '');
  const exitPickupLocation = useSelector(state => state.localtour.exitpickup || '');
  const pickupTime = useSelector(state => state.localtour.entrytime || '');
  const pickupTime1 = useSelector(state => state.localtour.entrytime1 || '');
  const pickupTimeZone = useSelector(state => state.localtour.entrytime || '');
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

  // Flag to prevent re-initialization after first setup
  const [isInitialSetupComplete, setIsInitialSetupComplete] = useState(false);

  // Refs for tracking and preventing loops
  const prevBookingsRef = useRef([]);
  const prevServicesRef = useRef([]);
  const prevVehicles = useRef([]);
  const isDispatching = useRef(false);
  const lastDispatchTime = useRef(0);
  const dispatchTimeoutRef = useRef(null);

  // Track which bookings have already been saved to Redux
  const [savedBookingIds, setSavedBookingIds] = useState([]);

  // Flag to track if initialization has been done for this specific component instance
  const [hasInitializedBookings, setHasInitializedBookings] = useState(false);
  const [hasDispatchedToRedux, setHasDispatchedToRedux] = useState(false);
  const [lastInitializationKey, setLastInitializationKey] = useState('');
  
  // Use ref to track if initialization has already been attempted for this component instance
  const initializationAttempted = useRef(false);
  
  // Create a unique key for this component instance to prevent double initialization
  const initializationKey = useMemo(() => {
    // Count total bookings across all transport types for more accurate key
    const pointToPointCount = PointToPoint?.reduce((sum, service) => 
      sum + (service.data?.length || 0), 0) || 0;
    const hourlyCount = Hourly?.reduce((sum, service) => 
      sum + (service.data?.length || 0), 0) || 0;
    const localTransportsCount = LocalTransports?.reduce((sum, service) => 
      sum + (service.data?.length || 0), 0) || 0;
    
    return `${dayIndex}-${date}-${pointToPointCount}-${hourlyCount}-${localTransportsCount}`;
  }, [dayIndex, date, PointToPoint, Hourly, LocalTransports]);
  
  // Initialize bookings from props data when available - only run once and prevent re-initialization
  useEffect(() => {
    // Hard stop - if initial setup is already complete, never initialize again
    if (isInitialSetupComplete) {
      console.log(`Local Transport - Skipping initialization for dayIndex ${dayIndex} (setup already complete)`);
      return;
    }
    
    // Skip if we've already initialized for this specific instance
    if (hasInitializedBookings && lastInitializationKey === initializationKey) {
      console.log(`Local Transport - Skipping initialization for dayIndex ${dayIndex} (already initialized for key: ${initializationKey})`);
      return;
    }
    
    if ((PointToPoint && PointToPoint.length > 0) || 
        (Hourly && Hourly.length > 0) || 
        (LocalTransports && LocalTransports.length > 0)) {
      
      console.log(`Local Transport - Initializing bookings for dayIndex ${dayIndex} with key: ${initializationKey}`);
      console.log(`Local Transport - Data summary:`, {
        PointToPoint: PointToPoint?.map(s => ({ type: s.type, dataCount: s.data?.length || 0 })) || [],
        Hourly: Hourly?.map(s => ({ type: s.type, dataCount: s.data?.length || 0 })) || [],
        LocalTransports: LocalTransports?.map(s => ({ type: s.type, dataCount: s.data?.length || 0 })) || []
      });
      
      // Mark initialization as done for this key
      setHasInitializedBookings(true);
      setLastInitializationKey(initializationKey);

      // Get all bookings from props
      const initializedBookings = initializeBookingsFromProps();

      if (initializedBookings.length > 0) {
        console.log(`Local Transport - Setting ${initializedBookings.length} bookings in local state for dayIndex ${dayIndex}`);
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
      } else {
        console.log(`Local Transport - No bookings found after filtering for dayIndex ${dayIndex}`);
      }
    } else {
      console.log(`Local Transport - No data available for initialization on dayIndex ${dayIndex}`);
    }
  }, [
    // Only depend on dayIndex to prevent re-initialization when props change
    dayIndex,
    isInitialSetupComplete,
    hasInitializedBookings,
    lastInitializationKey,
    initializationKey
    // Remove props from dependencies to prevent re-initialization
  ]);

  // Function to initialize bookings from props data
  const initializeBookingsFromProps = useCallback(() => {
    const initializedBookings = [];
    const processedIds = new Set(); // Track processed booking IDs to avoid duplicates
    
    console.log(`Local Transport - Initializing bookings for dayIndex ${dayIndex} with date:`, date);
    console.log('Local Transport - Available data:', {
      PointToPoint: PointToPoint?.length || 0,
      Hourly: Hourly?.length || 0,
      LocalTransports: LocalTransports?.length || 0
    });
    console.log('Local Transport - Tour dates available:', tourDates);
    console.log('Local Transport - Current dayIndex:', dayIndex);

    // Helper function to check if booking belongs to current dayIndex
    // Improved to handle both JSON formats and be more flexible
    const shouldShowBookingForThisDay = (bookingDate, componentDayIndex) => {
      // If componentDayIndex is provided and matches current dayIndex, show the booking
      if (componentDayIndex !== undefined && componentDayIndex === dayIndex) {
        console.log(`Local Transport - Showing booking by componentDayIndex: ${componentDayIndex} === ${dayIndex}`);
        return true;
      }

      // If no date provided, show in dayIndex 0 to avoid losing data
      if (!date || !bookingDate) {
        console.log(`Local Transport - No date info, showing in dayIndex 0: dayIndex=${dayIndex}`);
        return dayIndex === 0;
      }

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
        // If there's an error in date comparison, show in dayIndex 0 to avoid losing data
        return dayIndex === 0;
      }
    };

    // Helper function to distribute bookings without componentDayIndex across dayIndexes
    const shouldShowBookingByDistribution = (bookingData, transportType, index) => {
      // Always use date-based distribution for better accuracy
      // If we have tourDates, try to match the booking date with the date for this dayIndex
      if (tourDates && tourDates.length > dayIndex && bookingData.bookingDate) {
        try {
          // Get the date for this dayIndex from tourDates
          const currentDayDate = tourDates[dayIndex];
          
          // Normalize booking date
          let bookingDateStr;
          if (typeof bookingData.bookingDate === 'string') {
            const tempDate = new Date(bookingData.bookingDate);
            if (!isNaN(tempDate.getTime())) {
              bookingDateStr = tempDate.toISOString().split('T')[0];
            } else {
              bookingDateStr = bookingData.bookingDate;
            }
          }
          
          // If the booking date matches the date for this dayIndex, show it
          if (currentDayDate === bookingDateStr) {
            console.log(`Local Transport - Showing ${transportType} booking in dayIndex ${dayIndex} by date match:`, {
              bookingId: bookingData.id,
              bookingDate: bookingDateStr,
              dayIndexDate: currentDayDate
            });
            return true;
          }
        } catch (error) {
          console.error('Error in date-based distribution:', error);
        }
      }
      
      // If no tourDates or date doesn't match, only show in dayIndex 0 as a last resort
      // But this should be avoided in most cases
      if (dayIndex === 0 && (!tourDates || tourDates.length === 0)) {
        console.log(`Local Transport - Showing ${transportType} booking in dayIndex 0 (no tourDates fallback):`, {
          id: bookingData.id,
          bookingDate: bookingData.bookingDate,
          reason: 'no tourDates available, using dayIndex 0 fallback'
        });
        return true;
      }
      
      // For all other cases, don't show the booking
      return false;
    };

    // Process PointToPoint data
    if (PointToPoint && Array.isArray(PointToPoint)) {
      // Track bookings by date for better distribution
      const bookingsByDate = new Map();
      
      // First pass: collect all bookings by date
      PointToPoint.forEach(pointToPointService => {
        if (pointToPointService.data && Array.isArray(pointToPointService.data)) {
          pointToPointService.data.forEach(bookingData => {
            // Create a copy of bookingData to avoid modifying the original object
            const bookingDataCopy = { ...bookingData };
            if (!bookingDataCopy.id) {
              // Generate a stable ID if none exists
              bookingDataCopy.id = `generated-point-${pointToPointService.booking_id}-${bookingDataCopy.vehicles_id}-${Date.now()}`;
            }
            
            // Skip if already processed
            if (processedIds.has(bookingDataCopy.id)) {
              return;
            }
            
            // Normalize booking date
            let bookingDateStr = bookingDataCopy.bookingDate || '';
            try {
              if (typeof bookingDateStr === 'string') {
                const tempDate = new Date(bookingDateStr);
                if (!isNaN(tempDate.getTime())) {
                  bookingDateStr = tempDate.toISOString().split('T')[0];
                }
              }
            } catch (e) {
              console.error('Error normalizing date:', e);
            }
            
            // Group by date
            if (!bookingsByDate.has(bookingDateStr)) {
              bookingsByDate.set(bookingDateStr, []);
            }
            bookingsByDate.get(bookingDateStr).push({
              bookingData: bookingDataCopy,
              serviceType: pointToPointService.type,
              booking_id: pointToPointService.booking_id
            });
          });
        }
      });
      
      console.log(`Local Transport - Found PointToPoint bookings for ${bookingsByDate.size} different dates`);
      
      // Second pass: process bookings based on dayIndex and date
      bookingsByDate.forEach((bookingsForDate, dateStr) => {
        // Find if this date corresponds to a specific dayIndex in tourDates
        let matchingDayIndex = -1;
        if (tourDates && tourDates.length > 0) {
          matchingDayIndex = tourDates.findIndex(date => date === dateStr);
        }
        
        console.log(`Local Transport - Processing PointToPoint bookings for date ${dateStr}:`, {
          dayIndex: dayIndex,
          matchingDayIndex: matchingDayIndex,
          bookingsCount: bookingsForDate.length,
          tourDates: tourDates
        });
        
        bookingsForDate.forEach(({ bookingData, serviceType, booking_id }) => {
          // Check for duplicate IDs
          if (processedIds.has(bookingData.id)) {
            console.log(`Local Transport - Skipping duplicate PointToPoint booking ID: ${bookingData.id}`);
            return;
          }
          
          processedIds.add(bookingData.id);
          
          // Check if booking belongs to current dayIndex (improved logic)
          const componentDayIndex = bookingData.componentDayIndex;
          
          // Special case: if we found a matching dayIndex for this date and it's the current dayIndex
          const isMatchingDayByDate = matchingDayIndex === dayIndex && matchingDayIndex !== -1;
          
          // Only show this booking if:
          // 1. It has a componentDayIndex that matches current dayIndex, OR
          // 2. Its date matches the date for current dayIndex, OR
          // 3. It passes the distribution logic (which now uses strict date matching)
          const shouldShow = shouldShowBookingForThisDay(bookingData.bookingDate, componentDayIndex) || 
                            isMatchingDayByDate || 
                            shouldShowBookingByDistribution(bookingData, "Point To Point", 0);
          
          if (!shouldShow) {
            console.log(`Local Transport - Skipping PointToPoint booking for dayIndex ${dayIndex}:`, {
              id: bookingData.id,
              bookingDate: bookingData.bookingDate,
              componentDayIndex: componentDayIndex,
              matchingDayIndex,
              reason: 'does not match current dayIndex by any criteria'
            });
            return;
          }
          
          // If we get here, we should show this booking for the current dayIndex
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
            originalData: {
              ...bookingData,
              booking_id: booking_id // Ensure booking_id is included
            }
          };
          initializedBookings.push(booking);
          console.log(`Local Transport - Added PointToPoint booking:`, {
            id: booking.id,
            date: booking.bookingDate,
            dayIndex: dayIndex,
            reason: isMatchingDayByDate ? 'date matches dayIndex' : 
                    componentDayIndex !== undefined ? 'componentDayIndex match' : 
                    'distribution fallback'
          });
        });
      });
    }

    // Process Hourly data
    if (Hourly && Array.isArray(Hourly)) {
      // Track bookings by date for better distribution
      const bookingsByDate = new Map();
      
      // First pass: collect all bookings by date
      Hourly.forEach(hourlyService => {
        if (hourlyService.data && Array.isArray(hourlyService.data)) {
          hourlyService.data.forEach(bookingData => {
            // Create a copy of bookingData to avoid modifying the original object
            const bookingDataCopy = { ...bookingData };
            if (!bookingDataCopy.id) {
              // Generate a stable ID if none exists
              bookingDataCopy.id = `generated-hourly-${hourlyService.booking_id}-${bookingDataCopy.vehicles_id}-${Date.now()}`;
            }
            
            // Skip if already processed
            if (processedIds.has(bookingDataCopy.id)) {
              return;
            }
            
            // Normalize booking date
            let bookingDateStr = bookingDataCopy.bookingDate || '';
            try {
              if (typeof bookingDateStr === 'string') {
                const tempDate = new Date(bookingDateStr);
                if (!isNaN(tempDate.getTime())) {
                  bookingDateStr = tempDate.toISOString().split('T')[0];
                }
              }
            } catch (e) {
              console.error('Error normalizing date:', e);
            }
            
            // Group by date
            if (!bookingsByDate.has(bookingDateStr)) {
              bookingsByDate.set(bookingDateStr, []);
            }
            bookingsByDate.get(bookingDateStr).push({
              bookingData: bookingDataCopy,
              serviceType: hourlyService.type,
              booking_id: hourlyService.booking_id
            });
          });
        }
      });
      
      console.log(`Local Transport - Found Hourly bookings for ${bookingsByDate.size} different dates`);
      
      // Second pass: process bookings based on dayIndex and date
      bookingsByDate.forEach((bookingsForDate, dateStr) => {
        // Find if this date corresponds to a specific dayIndex in tourDates
        let matchingDayIndex = -1;
        if (tourDates && tourDates.length > 0) {
          matchingDayIndex = tourDates.findIndex(date => date === dateStr);
        }
        
        console.log(`Local Transport - Processing Hourly bookings for date ${dateStr}:`, {
          dayIndex: dayIndex,
          matchingDayIndex: matchingDayIndex,
          bookingsCount: bookingsForDate.length,
          tourDates: tourDates
        });
        
        bookingsForDate.forEach(({ bookingData, serviceType, booking_id }) => {
          // Check for duplicate IDs
          if (processedIds.has(bookingData.id)) {
            console.log(`Local Transport - Skipping duplicate Hourly booking ID: ${bookingData.id}`);
            return;
          }
          
          processedIds.add(bookingData.id);
          
          // Check if booking belongs to current dayIndex (improved logic)
          const componentDayIndex = bookingData.componentDayIndex;
          
          // Special case: if we found a matching dayIndex for this date and it's the current dayIndex
          const isMatchingDayByDate = matchingDayIndex === dayIndex && matchingDayIndex !== -1;
          
          // Only show this booking if:
          // 1. It has a componentDayIndex that matches current dayIndex, OR
          // 2. Its date matches the date for current dayIndex, OR
          // 3. It passes the distribution logic (which now uses strict date matching)
          const shouldShow = shouldShowBookingForThisDay(bookingData.bookingDate, componentDayIndex) || 
                            isMatchingDayByDate || 
                            shouldShowBookingByDistribution(bookingData, "Hourly", 1);
          
          if (!shouldShow) {
            console.log(`Local Transport - Skipping Hourly booking for dayIndex ${dayIndex}:`, {
              id: bookingData.id,
              bookingDate: bookingData.bookingDate,
              componentDayIndex: componentDayIndex,
              matchingDayIndex,
              reason: 'does not match current dayIndex by any criteria'
            });
            return;
          }
          
          // If we get here, we should show this booking for the current dayIndex
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
            originalData: {
              ...bookingData,
              booking_id: booking_id // Ensure booking_id is included
            }
          };
          initializedBookings.push(booking);
          console.log(`Local Transport - Added Hourly booking:`, {
            id: booking.id,
            date: booking.bookingDate,
            dayIndex: dayIndex,
            reason: isMatchingDayByDate ? 'date matches dayIndex' : 
                    componentDayIndex !== undefined ? 'componentDayIndex match' : 
                    'distribution fallback'
          });
        });
      });
    }

    // Process LocalTransports data
    if (LocalTransports && Array.isArray(LocalTransports)) {
      // Track bookings by date for better distribution
      const bookingsByDate = new Map();
      
      // First pass: collect all bookings by date
      LocalTransports.forEach(localService => {
        if (localService.data && Array.isArray(localService.data)) {
          localService.data.forEach(bookingData => {
            // Create a copy of bookingData to avoid modifying the original object
            const bookingDataCopy = { ...bookingData };
            if (!bookingDataCopy.id) {
              // Generate a stable ID if none exists
              bookingDataCopy.id = `generated-local-${localService.booking_id}-${bookingDataCopy.vehicles_id}-${Date.now()}`;
            }
            
            // Skip if already processed
            if (processedIds.has(bookingDataCopy.id)) {
              return;
            }
            
            // Normalize booking date
            let bookingDateStr = bookingDataCopy.bookingDate || '';
            try {
              if (typeof bookingDateStr === 'string') {
                const tempDate = new Date(bookingDateStr);
                if (!isNaN(tempDate.getTime())) {
                  bookingDateStr = tempDate.toISOString().split('T')[0];
                }
              }
            } catch (e) {
              console.error('Error normalizing date:', e);
            }
            
            // Group by date
            if (!bookingsByDate.has(bookingDateStr)) {
              bookingsByDate.set(bookingDateStr, []);
            }
            bookingsByDate.get(bookingDateStr).push({
              bookingData: bookingDataCopy,
              serviceType: localService.type,
              booking_id: localService.booking_id
            });
          });
        }
      });
      
      console.log(`Local Transport - Found LocalTransfer bookings for ${bookingsByDate.size} different dates`);
      
      // Second pass: process bookings based on dayIndex and date
      bookingsByDate.forEach((bookingsForDate, dateStr) => {
        // Find if this date corresponds to a specific dayIndex in tourDates
        let matchingDayIndex = -1;
        if (tourDates && tourDates.length > 0) {
          matchingDayIndex = tourDates.findIndex(date => date === dateStr);
        }
        
        console.log(`Local Transport - Processing LocalTransfer bookings for date ${dateStr}:`, {
          dayIndex: dayIndex,
          matchingDayIndex: matchingDayIndex,
          bookingsCount: bookingsForDate.length,
          tourDates: tourDates
        });
        
        bookingsForDate.forEach(({ bookingData, serviceType, booking_id }) => {
          // Check for duplicate IDs
          if (processedIds.has(bookingData.id)) {
            console.log(`Local Transport - Skipping duplicate LocalTransfer booking ID: ${bookingData.id}`);
            return;
          }
          
          processedIds.add(bookingData.id);
          
          // Check if booking belongs to current dayIndex (improved logic)
          const componentDayIndex = bookingData.componentDayIndex;
          
          // Special case: if we found a matching dayIndex for this date and it's the current dayIndex
          const isMatchingDayByDate = matchingDayIndex === dayIndex && matchingDayIndex !== -1;
          
          // Only show this booking if:
          // 1. It has a componentDayIndex that matches current dayIndex, OR
          // 2. Its date matches the date for current dayIndex, OR
          // 3. It passes the distribution logic (which now uses strict date matching)
          const shouldShow = shouldShowBookingForThisDay(bookingData.bookingDate, componentDayIndex) || 
                            isMatchingDayByDate || 
                            shouldShowBookingByDistribution(bookingData, "Local Transfer", 2);
          
          if (!shouldShow) {
            console.log(`Local Transport - Skipping LocalTransfer booking for dayIndex ${dayIndex}:`, {
              id: bookingData.id,
              bookingDate: bookingData.bookingDate,
              componentDayIndex: componentDayIndex,
              matchingDayIndex,
              reason: 'does not match current dayIndex by any criteria'
            });
            return;
          }
          
          // If we get here, we should show this booking for the current dayIndex
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
            originalData: {
              ...bookingData,
              booking_id: booking_id // Ensure booking_id is included
            }
          };
          initializedBookings.push(booking);
          console.log(`Local Transport - Added LocalTransfer booking:`, {
            id: booking.id,
            date: booking.bookingDate,
            dayIndex: dayIndex,
            reason: isMatchingDayByDate ? 'date matches dayIndex' : 
                    componentDayIndex !== undefined ? 'componentDayIndex match' : 
                    'distribution fallback'
          });
        });
      });
    }

    if (initializedBookings.length > 0) {
      console.log(`Local Transport - Found ${initializedBookings.length} bookings for dayIndex ${dayIndex}:`, 
        initializedBookings.map(b => ({ id: b.id, transportType: b.transportType, bookingDate: b.bookingDate }))
      );
    } else {
      console.log(`Local Transport - No bookings found for dayIndex ${dayIndex}`);
    }

    return initializedBookings;
  }, [date, dayIndex, PointToPoint, Hourly, LocalTransports, tourDates]);

  // Function to dispatch initialized bookings to Redux - only handles bookings for this specific dayIndex
  const dispatchInitializedBookingsToRedux = useCallback((bookings) => {
    // Prevent dispatching if initial setup is already complete
    if (isInitialSetupComplete) {
      console.log(`Local Transport - Skipping dispatch to Redux for dayIndex ${dayIndex} (setup already complete)`);
      return;
    }
  
    const completedBookings = bookings.filter(booking => booking.isComplete);
  
    if (completedBookings.length > 0) {
      console.log(`Local Transport - Dispatching ${completedBookings.length} initialized bookings to Redux for dayIndex ${dayIndex}`);
  
      const currentServices = [...allServices];
      let hasUpdates = false;
      
      // Group bookings by type to handle them more efficiently
      const bookingsByType = {
        "Point To Point": [],
        "Hourly": [],
        "Local Transfer": []
      };
      
      completedBookings.forEach(booking => {
        if (booking.transportType) {
          bookingsByType[booking.transportType].push(booking);
        }
      });
      
      // Log the distribution of bookings by type
      console.log(`Local Transport - Bookings by type:`, {
        "Point To Point": bookingsByType["Point To Point"].length,
        "Hourly": bookingsByType["Hourly"].length,
        "Local Transfer": bookingsByType["Local Transfer"].length
      });
  
      // Process each booking
      completedBookings.forEach(booking => {
        if (booking.originalData) {
          // Get the booking_id from the originalData if available
          const booking_id = booking.originalData.booking_id;
          
          // If we have a booking_id, try to find the service directly
          if (booking_id) {
            const existingServiceIndex = currentServices.findIndex(service => 
              service.booking_id === booking_id
            );
            
            if (existingServiceIndex !== -1) {
              // Service exists, check if booking data exists
              const existingService = currentServices[existingServiceIndex];
              const originalBookingExists = existingService.data?.some(item =>
                item.id === booking.originalData.id
              );
              
              if (!originalBookingExists) {
                // Add missing booking to existing service
                const updatedService = {
                  ...existingService,
                  data: [...(existingService.data || []), booking.originalData]
                };
                
                console.log(`Local Transport - Updating existing service in Redux with booking_id ${booking_id}:`, {
                  type: updatedService.type,
                  booking_id: updatedService.booking_id,
                  oldDataCount: existingService.data?.length || 0,
                  newDataCount: updatedService.data.length,
                  bookingId: booking.originalData.id
                });
                
                currentServices[existingServiceIndex] = updatedService;
                hasUpdates = true;
              } else {
                console.log(`Local Transport - Service already exists with this booking:`, {
                  type: existingService.type,
                  booking_id: existingService.booking_id,
                  bookingId: booking.originalData.id
                });
              }
              
              return; // Skip the rest of the processing for this booking
            }
          }
          
          // If we get here, either we don't have a booking_id or the service wasn't found
          // Try to find the service by matching booking data
          const allAvailableServices = [PointToPoint, Hourly, LocalTransports].flat().filter(Boolean);
          
          const originalService = allAvailableServices.find(service => {
            // Check if any booking in the service matches our booking ID
            return service.data?.some(item => item.id === booking.originalData.id);
          });
  
          if (originalService) {
            const existingServiceIndex = currentServices.findIndex(service =>
              service.booking_id === originalService.booking_id
            );
  
            if (existingServiceIndex === -1) {
              // Add full service since it doesn't exist yet
              const newService = {
                agent_id: agentId || originalService.agent_id,
                bookingType: "enquiry",
                booking_id: originalService.booking_id,
                data: originalService.data,
                tour_id: tourId || originalService.tour_id,
                type: originalService.type
              };
              
              console.log(`Local Transport - Adding new service to Redux:`, {
                type: newService.type,
                booking_id: newService.booking_id,
                dataCount: newService.data?.length || 0
              });
              
              currentServices.push(newService);
              hasUpdates = true;
            } else {
              // Check if the specific booking is missing in an existing service
              const existingService = currentServices[existingServiceIndex];
              const originalBookingExists = existingService.data?.some(item =>
                item.id === booking.originalData.id
              );
  
              if (!originalBookingExists) {
                // Add missing booking to existing service
                const updatedService = {
                  ...existingService,
                  data: [...(existingService.data || []), booking.originalData]
                };
                
                console.log(`Local Transport - Updating existing service in Redux:`, {
                  type: updatedService.type,
                  booking_id: updatedService.booking_id,
                  oldDataCount: existingService.data?.length || 0,
                  newDataCount: updatedService.data.length
                });
                
                currentServices[existingServiceIndex] = updatedService;
                hasUpdates = true;
              } else {
                console.log(`Local Transport - Service already exists with this booking:`, {
                  type: existingService.type,
                  booking_id: existingService.booking_id,
                  bookingId: booking.originalData.id
                });
              }
            }
          } else {
            // If we can't find the original service, create a new one based on the booking type
            console.warn(`Local Transport - Could not find original service for booking, creating new:`, {
              bookingId: booking.originalData.id,
              transportType: booking.transportType
            });
            
            // Determine service type based on booking transport type
            let serviceType;
            if (booking.transportType === "Point To Point") {
              serviceType = "travel_point";
            } else if (booking.transportType === "Hourly") {
              serviceType = "travel_hourly";
            } else if (booking.transportType === "Local Transfer") {
              serviceType = "local_transport";
            } else {
              console.error(`Unknown transport type: ${booking.transportType}`);
              return;
            }
            
            // Create a new service for this booking
            const newService = {
              agent_id: agentId,
              bookingType: "enquiry",
              booking_id: booking.originalData.booking_id || `generated-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
              data: [booking.originalData],
              tour_id: tourId,
              type: serviceType
            };
            
            console.log(`Local Transport - Creating new service for orphaned booking:`, {
              type: newService.type,
              booking_id: newService.booking_id,
              bookingId: booking.originalData.id
            });
            
            currentServices.push(newService);
            hasUpdates = true;
          }
        }
      });
  
      if (hasUpdates) {
        console.log(`Local Transport - Dispatching updated services to Redux for dayIndex ${dayIndex}`);
        console.log(`Local Transport - Services being dispatched:`, 
          currentServices.filter(s => ["travel_point", "travel_hourly", "local_transport"].includes(s.type))
            .map(s => ({ type: s.type, booking_id: s.booking_id, dataCount: s.data?.length || 0 }))
        );
        dispatch(setAllServices(currentServices));
      } else {
        console.log(`Local Transport - No updates needed for dayIndex ${dayIndex}, services already in Redux`);
      }
    } else {
      console.log(`Local Transport - No completed bookings to dispatch for dayIndex ${dayIndex}`);
    }
  }, [allServices, dispatch, agentId, tourId, PointToPoint, Hourly, LocalTransports, dayIndex, isInitialSetupComplete]);
  

  // Validation function
  const isBookingValid = useCallback((section) => {
    // Basic validation that applies to all transport types
    const baseValid = section.vehicleId && 
                     (section.adults + section.children > 0) && 
                     section.priceMode && 
                     section.price > 0;
    
    // Transport type specific validation
    if (section.transportType === "Hourly") {
      return baseValid && section.hours && section.hours >= 1;
    }
    
    // For Point To Point and Local Transfer
    return baseValid;
  }, []);

  // Improved dispatch function with better debouncing and consolidation
  const dispatchValidBookingsToRedux = useCallback(() => {
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
      
      // Track booking indices that will have new services
      const bookingIndicesWithNewServices = new Set();
      
      // Collect all valid bookings that haven't been saved yet
      // If setup is complete, only process bookings that don't have originalData (new user-created bookings)
      allBookings.forEach((booking, index) => {
        const isNewBooking = !booking.originalData;
        const shouldProcessBooking = isInitialSetupComplete ? isNewBooking : true;
        
        if (isBookingValid(booking) && booking.transportType && shouldProcessBooking) {
          // Create a unique signature for this booking
          let bookingSignature = `${booking.vehicleId}-${booking.priceMode}-${booking.price}-${booking.adults}-${booking.children}-${booking.pickupLocation || ''}-${booking.dropoffLocation || ''}`;
          
          // Add transport-specific fields to signature
          if (booking.transportType === "Hourly") {
            bookingSignature += `-${booking.hours || 1}`;
          } else if (booking.transportType === "Local Transfer") {
            bookingSignature += `-${booking.to_zone_id || '1'}-${booking.from_zone_id || '1'}`;
          }
          
          // Only include if not already saved
          if (!savedBookingIds.includes(bookingSignature)) {
            validBookings[booking.transportType].push({...booking, index, signature: bookingSignature});
            bookingIndicesWithNewServices.add(index);
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
        let updatedServices = [...allServices];
        const newSavedSignatures = [];
        
        // Remove old services for booking indices that will have new services
        // This prevents duplicates when editing existing bookings
        if (bookingIndicesWithNewServices.size > 0) {
          console.log(`Local Transport - Removing old services for booking indices: ${Array.from(bookingIndicesWithNewServices).join(', ')}`);
          updatedServices = updatedServices.filter(service => {
            // Check if this service was created by this component for the booking indices we're updating
            if (service.type && ["travel_point", "travel_hourly", "local_transport"].includes(service.type)) {
              // Check if service has data with our component's dayIndex marker
              const hasMatchingDayIndex = service.data && service.data.some(item => 
                item.componentDayIndex === dayIndex
              );
              
              if (hasMatchingDayIndex) {
                // Check if this service corresponds to a booking index we're updating
                const serviceBookingIndex = service.localBookingIndex;
                if (serviceBookingIndex !== undefined && bookingIndicesWithNewServices.has(serviceBookingIndex)) {
                  console.log(`Local Transport - Removing old service for booking index ${serviceBookingIndex}`);
                  return false; // Remove this service
                }
              }
            }
            return true; // Keep this service
          });
        }
        
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
              totalPrice: Math.ceil(booking.price || 0),
              componentDayIndex: dayIndex // Add marker to identify which component created this
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
                // Ensure zone IDs are set
                const zoneId = booking.to_zone_id || booking.zoneId || '1';
                const fromZoneId = booking.from_zone_id || '1';
                
                bookingData = {
                  ...bookingData,
                  entrypickup: booking.pickupLocation || '',
                  entrydropoff: booking.dropoffLocation || '',
                  PickupPlaceid: booking.PickupPlaceid || null,
                  DropoffPlaceid: booking.DropoffPlaceid || null,
                  to_zone_id: zoneId,
                  from_zone_id: fromZoneId,
                  zone_id: zoneId,
                  exitpickupdate: booking.bookingDate,
                  totalPrice: Math.ceil(booking.price || 0)
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
            
            return { bookingData, bookingIndex: booking.index };
          });
          
          // Create new services for each booking
          bookingsData.forEach(({ bookingData, bookingIndex }) => {
            // Get the original booking to check if it has a booking_id
            const originalBooking = allBookings[bookingIndex];
            
            // Create base service structure
            const newService = {
              agent_id: agentId,
              bookingType: "enquiry",
              type: serviceType,
              tour_id: tourId,
              localBookingIndex: bookingIndex, // Track which local booking this corresponds to
              data: [bookingData] // Each service contains only one booking
            };
            
            // Only include booking_id if this is from existing data (originalData) and has a booking_id
            if (originalBooking.originalData && originalBooking.originalData.booking_id) {
              newService.booking_id = originalBooking.originalData.booking_id;
              console.log(`Local Transport - Adding booking_id ${originalBooking.originalData.booking_id} for existing booking`);
            } else {
              console.log(`Local Transport - No booking_id added for new booking at index ${bookingIndex}`);
            }
            
            updatedServices.push(newService);
            console.log(`Local Transport - Created new ${transportType} service for booking index ${bookingIndex} with booking ID: ${bookingData.id}`);
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
  }, [allBookings, allServices, dispatch, agentId, tourId, isBookingValid, savedBookingIds, dayIndex, isInitialSetupComplete]);

  // Reset vehicles and saved booking IDs when component unmounts or dayIndex changes
  useEffect(() => {
    return () => {
      dispatch(resetVehicles1());
      dispatch(clearSearchDayIndex()); // Clear search day index on unmount
      if (dispatchTimeoutRef.current) {
        clearTimeout(dispatchTimeoutRef.current);
      }
      // Clear saved booking IDs on unmount to prevent stale state
      setSavedBookingIds([]);
      // Reset initialization flag on unmount
      initializationAttempted.current = false;
    };
  }, [dispatch]);

  // Clear saved booking IDs when dayIndex changes
  useEffect(() => {
    setSavedBookingIds([]);
    setIsInitialSetupComplete(false); // Reset setup flag when dayIndex changes
    initializationAttempted.current = false; // Reset initialization flag when dayIndex changes
    // Clear search day index when dayIndex changes to prevent cross-day interference
    dispatch(clearSearchDayIndex());
    console.log(`Local Transport - Cleared saved booking IDs and reset flags for new dayIndex: ${dayIndex}`);
  }, [dayIndex, dispatch]);

  // Dispatch initialized bookings to Redux - only run once and prevent re-dispatching
  useEffect(() => {
    // Hard stop - if initial setup is already complete, never dispatch again
    if (isInitialSetupComplete) {
      console.log(`Local Transport - Skipping dispatch to Redux for dayIndex ${dayIndex} (setup already complete)`);
      return;
    }

     // Skip if we've already initialized for this specific instance
     if (hasInitializedBookings && lastInitializationKey === initializationKey) {
      console.log(`Local Transport - Skipping initialization for dayIndex ${dayIndex} (already initialized for key: ${initializationKey})`);
      return;
    }
    
    // Hard stop - if we've already dispatched for this component instance
    if (hasDispatchedToRedux) {
      console.log(`Local Transport - Skipping dispatch to Redux for dayIndex ${dayIndex} (already dispatched)`);
      return;
    }
    
    if (hasInitializedBookings) {
      console.log(`Local Transport - Dispatching initialized bookings to Redux for dayIndex ${dayIndex}`);
      dispatchInitializedBookingsToRedux(allBookings);
      setHasDispatchedToRedux(true);
      setIsInitialSetupComplete(true); // Mark setup as complete after dispatching
    }
  }, [
    // Only run when component mounts or dayIndex changes
    dayIndex,
    hasInitializedBookings,
    hasDispatchedToRedux,
    isInitialSetupComplete,
    dispatchInitializedBookingsToRedux,
    allBookings,
    lastInitializationKey,
    initializationKey
  ]);

  // Separate effect to dispatch original bookings to Redux - only once
  useEffect(() => {
    // Skip if initial setup is already complete
    if (isInitialSetupComplete) {
      return;
    }
    
    if (hasInitializedBookings && !hasDispatchedToRedux && allBookings.length > 0) {
      // Only dispatch bookings that have originalData (came from props)
      const originalBookings = allBookings.filter(booking => booking.originalData);

      if (originalBookings.length > 0) {
        console.log(`Dispatching ${originalBookings.length} original bookings to Redux for dayIndex ${dayIndex}`);
        dispatchInitializedBookingsToRedux(originalBookings);
        setHasDispatchedToRedux(true);
        // Mark initial setup as complete after dispatching to Redux
        setIsInitialSetupComplete(true);
        console.log(`Local Transport - Initial setup completed for dayIndex ${dayIndex}`);
      }
    }
  }, [hasInitializedBookings, hasDispatchedToRedux, allBookings, dispatchInitializedBookingsToRedux, dayIndex, isInitialSetupComplete]);
  

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
    if (hasVehicles && selectedPort && searchDayIndex === dayIndex) {
      console.log(`Local Transport - Day ${dayIndex}: Creating booking for search initiated by this component`);

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
            
            // Clear the search day index after creating the booking
            dispatch(clearSearchDayIndex());
            
            return newBookings;
          });
        }, 50);
      }
    } else if (hasVehicles && selectedPort) {
      // Log when we have vehicles but this component shouldn't create the booking
      console.log(`Local Transport - Day ${dayIndex}: Skipping booking creation (searchDayIndex: ${searchDayIndex}, this dayIndex: ${dayIndex})`);
    }
  }, [
    hasVehicles, 
    selectedPort, 
    searchDayIndex, 
    dayIndex, 
    pickupLocation, 
    dropoffLocation, 
    pickupTime, 
    pickupDate, 
    exitPickupLocation, 
    pickupTime1, 
    exitPickupDate, 
    pickupTimeZone,
    dispatch,
    allBookings
  ]);

  // Monitor Redux state changes for debugging
  useEffect(() => {
    console.log(`Local Transport - Day ${dayIndex}: Redux state updated:`, {
      allServicesCount: allServices.length,
      transportServices: allServices.filter(s => ["travel_point", "travel_hourly", "local_transport"].includes(s.type))
        .map(s => ({ type: s.type, booking_id: s.booking_id, dataCount: s.data?.length || 0 })),
      vehiclesCount: vehicles.length,
      selectedPort: selectedPort
    });
  }, [allServices, vehicles, selectedPort, dayIndex]);

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
  const handleVehicleChange = useCallback((sectionIndex, vehicleId, mode, dmcId, city, country, toZoneId, fromZoneId) => {
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

      // For Local Transfer, ensure zone IDs are set
      const finalToZoneId = transportType === "Local Transfer" ? (toZoneId || '1') : '';
      const finalFromZoneId = transportType === "Local Transfer" ? (fromZoneId || '1') : '';

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
        bookingDate: updatedBookingDate,
        // Add zone information for Local Transfer
        to_zone_id: finalToZoneId,
        from_zone_id: finalFromZoneId,
        zoneId: finalToZoneId // For compatibility with existing code
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
      console.log(`Local Transport - Removing booking:`, bookingToRemove);
      
      // Generate the signature for this booking to remove it from saved IDs
      let bookingSignature = `${bookingToRemove.vehicleId}-${bookingToRemove.priceMode}-${bookingToRemove.price}-${bookingToRemove.adults}-${bookingToRemove.children}-${bookingToRemove.pickupLocation || ''}-${bookingToRemove.dropoffLocation || ''}`;
      
      // Add transport-specific fields to signature
      if (bookingToRemove.transportType === "Hourly") {
        bookingSignature += `-${bookingToRemove.hours || 1}`;
      } else if (bookingToRemove.transportType === "Local Transfer") {
        bookingSignature += `-${bookingToRemove.to_zone_id || '1'}-${bookingToRemove.from_zone_id || '1'}`;
      }
      
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
      
      // Remove service from Redux using the new tracking system
      if (bookingToRemove.vehicleId || bookingToRemove.originalData) {
        const currentServices = [...allServices];
        
        const filteredServices = currentServices.filter(service => {
          // For services created by this component, use the localBookingIndex
          if (service.localBookingIndex !== undefined && service.data && service.data.some(item => item.componentDayIndex === dayIndex)) {
            // Remove service if it corresponds to the booking we want to remove
            if (service.localBookingIndex === indexToRemove) {
              console.log(`Local Transport - Removing service for booking index ${indexToRemove} from Redux`);
              return false;
            }
          }
          
          // For legacy services (originalData), check by booking ID
          if (service.data && Array.isArray(service.data) && bookingToRemove.originalData) {
            const containsBooking = service.data.some(item => item.id === bookingToRemove.originalData.id);
            if (containsBooking) {
              console.log(`Local Transport - Removing legacy service with booking ID ${bookingToRemove.originalData.id} from Redux`);
              return false;
            }
          }
          
          // Keep other services
          return true;
        });
        
        if (filteredServices.length !== currentServices.length) {
          console.log(`Local Transport - Updating Redux services after removal`);
          dispatch(setAllServices(filteredServices));
          prevServicesRef.current = filteredServices;
        }
      }
    }
  }, [allBookings, allServices, dispatch, dayIndex]);

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
      <Container maxWidth="xl" sx={{ py: 0.5, position: 'relative' }}>
        {/* Header Card with Gradient Background */}
        <Card 
          elevation={3}
          sx={{
            borderRadius: 2,
            background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
            color: 'white',
            mb: 0.1,
            mx: 'auto',
          }}
        >
          <CardContent sx={{height: '52px', py: 0.1}}>
            <Box display="flex" alignItems="center" justifyContent="space-between">
              <Box display="flex" alignItems="center">
                <DirectionsCarIcon sx={{ mr: 1.5, fontSize: "1.1rem", color: '#FFD700' }} />
                <Box>
                  <Typography variant="h6" fontWeight="600" sx={{ color: 'white', fontSize: '0.9rem' }}>
                    Book Transport Services
                  </Typography>
                  <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)', fontSize: '0.7rem' }}>
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
                  border: '1px solid rgba(255, 255, 255, 0.3)',
                  fontSize: '0.75rem',
                  height: '20px'
                }}
              />
            </Box>
          </CardContent>
        </Card>

        {/* Search Card */}
        <Card 
          elevation={2}
          sx={{ 
            borderRadius: 2,
            border: `2px solid ${alpha('#ff6b6b', 0.2)}`,
            mb: 2,
            transition: 'all 0.3s ease',
            '&:hover': {
              boxShadow: `0 6px 20px ${alpha('#ff6b6b', 0.15)}`,
              transform: 'translateY(-1px)',
            }
          }}
        >
          <CardContent sx={{ p: 0.5 }}>
            <SearchLocationTransport Location={Location} dayIndex={dayIndex} date={date}/>
          </CardContent>
        </Card>
      </Container>
    );
  }

  return (
    <Container maxWidth="xl" sx={{ py: 0.5, position: 'relative' }}>
      {/* Header Card with Gradient Background */}
      <Card 
        elevation={3}
        sx={{
          borderRadius: 2,
          background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
          color: 'white',
          mb: 0.5,
          mx: 'auto',
        }}
      >
        <CardContent sx={{ py: 0.5}}>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <DirectionsCarIcon sx={{ mr: 1.5, fontSize: '1.1rem', color: '#FFD700' }} />
              <Box>
                <Typography variant="h6" fontWeight="600" sx={{ color: 'white', fontSize: '0.9rem' }}>
                  Book Transport Services
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)', fontSize: '0.7rem' }}>
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
                border: '1px solid rgba(255, 255, 255, 0.3)',
                fontSize: '0.75rem',
                height: '24px'
              }}
            />
          </Box>
        </CardContent>
      </Card>

      {/* Search Section */}
      <Box sx={{ mb: 2 }}>
        <SearchLocationTransport Location={Location} dayIndex={dayIndex} date={date} />
      </Box>

      {/* Alerts */}
      <Fade in={validationError} timeout={300}>
        <Box>
          {validationError && (
            <Alert severity="error" sx={{ mb: 1.5, borderRadius: 1.5 }}>
              {validationError}
            </Alert>
          )}
        </Box>
      </Fade>

      <Fade in={bookingSuccess} timeout={300}>
        <Box>
          {bookingSuccess && (
            <Alert severity="success" sx={{ mb: 1.5, borderRadius: 1.5 }}>
              Transport booking information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>

      {/* Multiple Booking Cards */}
      <Grid container spacing={1}>
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
                  borderRadius: 2,
                  border: outOfTourDates ? '2px solid #e53935' : `2px solid ${alpha('#ff6b6b', 0.2)}`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: outOfTourDates
                      ? `0 6px 20px ${alpha('#e53935', 0.15)}`
                      : `0 6px 20px ${alpha('#ff6b6b', 0.15)}`,
                    transform: 'translateY(-1px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 1.5,
                    bgcolor: alpha('#ff6b6b', 0.05),
                    borderBottom: `1px solid ${alpha('#ff6b6b', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                      <Chip 
                        label={`Transport #${bookingIndex + 1}`}
                        sx={{ 
                          bgcolor: '#ff6b6b',
                          color: 'white',
                          fontWeight: 600,
                          fontSize: '0.75rem',
                          height: '24px'
                        }}
                        size="small"
                      />
                      <Chip 
                        label={`${completionStatus}/4 Complete`}
                        color={completionStatus === 4 ? "success" : "warning"}
                        size="small"
                        variant="outlined"
                        sx={{ fontSize: '0.75rem', height: '24px' }}
                      />
                      <Chip 
                        label={booking.transportType || "Unknown"}
                        color={booking.transportType === "Hourly" ? "secondary" : "primary"}
                        size="small"
                        variant="outlined"
                        sx={{ fontSize: '0.75rem', height: '24px' }}
                      />
                      {(Number(booking.price) > 0 || Number(booking.totalPrice) > 0) && (
                        <Chip
                          label={`$${(
                            !isNaN(Number(booking.price)) && Number(booking.price) > 0
                              ? Number(booking.price)
                              : Number(booking.totalPrice)
                          ).toFixed(2)}`}
                          color="success"
                          size="small"
                          variant="outlined"
                          sx={{ fontSize: '0.75rem', height: '24px' }}
                        />
                      )}
                    </Box>

                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.8 }}>
                      <Tooltip title={isExpanded ? "Collapse" : "Expand"}>
                        <IconButton 
                          size="small" 
                          onClick={() => toggleExpand(bookingIndex)}
                          sx={{ 
                            bgcolor: alpha('#ff6b6b', 0.1),
                            '&:hover': { bgcolor: alpha('#ff6b6b', 0.2) },
                            width: 32,
                            height: 32
                          }}
                        >
                          {isExpanded ? <ExpandLessIcon /> : <ExpandMoreIcon />}
                        </IconButton>
                      </Tooltip>


                        <Tooltip title="Remove this transport service">
                          <IconButton 
                            size="small" 
                            onClick={() => handleRemoveBooking(bookingIndex)}
                            sx={{ 
                              bgcolor: alpha('#f44336', 0.1),
                              '&:hover': { bgcolor: alpha('#f44336', 0.2) },
                              width: 32,
                              height: 32
                            }}
                          >
                            <DeleteIcon sx={{ fontSize: 16, color: '#f44336' }} />
                          </IconButton>
                        </Tooltip>


                      <Button
                        variant="outlined"
                        size="medium"
                        onClick={() => {
                          // Force check if booking is complete
                          if (isBookingValid(booking)) {
                            console.log(`Local Transport - Opening modal for booking ${bookingIndex}`);
                            handleOpenModal(bookingIndex);
                          } else {
                            console.log(`Local Transport - Cannot open modal, booking ${bookingIndex} is invalid:`, booking);
                            // Show validation error
                            setValidationError(validateBooking(booking, bookingIndex));
                            // Clear error after 3 seconds
                            setTimeout(() => setValidationError(null), 3000);
                          }
                        }}
                        disabled={!isBookingValid(booking)}
                        startIcon={<VisibilityIcon />}
                        sx={{
                          borderRadius: 1.5,
                          px: 2.5,
                          py: 0.8,
                          fontSize: '0.8rem',
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
                        m: 0,
                        p: 0, 
                        borderRadius: 1,
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdropFilter: 'blur(10px)'
                      }}
                    >
                      {/* Vehicle Dropdown with Complete Functionality */}
                      <Box sx={{ p: 0.1 }}>
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
                            onVehicleChange={(vehicleId, mode, dmcId, city, country, toZoneId, fromZoneId) => 
                              handleVehicleChange(bookingIndex, vehicleId, mode, dmcId, city, country, toZoneId, fromZoneId)}
                            onPaxChange={(adults, children) => 
                              handlePaxChange(bookingIndex, adults, children)}
                            onPriceModeChange={(priceMode) => 
                              handlePriceModeChange(bookingIndex, priceMode)}
                            onPriceChange={(price) =>
                              handlePriceChange(bookingIndex, price)}
                            onBookingComplete={(isComplete) => {
                              // Only update if the completion status has changed
                              const currentComplete = allBookings[bookingIndex]?.isComplete;
                              if (currentComplete !== isComplete) {
                                console.log(`Local Transfer - Booking ${bookingIndex} completion changed: ${currentComplete} -> ${isComplete}`);
                                
                                // Update booking completion status
                                setAllBookings(prev => {
                                  const updatedBookings = [...prev];
                                  if (updatedBookings[bookingIndex]) {
                                    updatedBookings[bookingIndex] = {
                                      ...updatedBookings[bookingIndex],
                                      isComplete: isComplete
                                    };
                                  }
                                  return updatedBookings;
                                });
                                
                                // Trigger Redux dispatch for complete bookings only once
                                if (isComplete) {
                                  console.log(`Local Transfer - Dispatching booking ${bookingIndex} to Redux`);
                                  
                                  // Use setTimeout to avoid immediate state updates
                                  const timeoutId = setTimeout(() => {
                                    dispatchValidBookingsToRedux();
                                  }, 300);
                                  
                                  // Clean up timeout if component unmounts
                                  return () => clearTimeout(timeoutId);
                                }
                              }
                            }}
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
                    <Box sx={{ px: 1.5, pt: 0.8 }}>
                      <Alert severity="error" sx={{ borderRadius: 1.5, mb: 0.8 }}>
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
              borderRadius: 2,
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
            <CardContent sx={{ py: 1.5 }}>
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                gap: 1.5
              }}>
                <AddIcon sx={{ fontSize: 28, color: '#ff6b6b' }} />
                <Typography variant="h6" color="#ff6b6b" fontWeight={600} sx={{ fontSize: '1.1rem' }}>
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
}, (prevProps, nextProps) => {
  // Custom comparison function for React.memo to prevent unnecessary re-renders
  return (
    prevProps.dayIndex === nextProps.dayIndex &&
    prevProps.date === nextProps.date &&
    prevProps.PointToPoint === nextProps.PointToPoint &&
    prevProps.Hourly === nextProps.Hourly &&
    prevProps.LocalTransports === nextProps.LocalTransports &&
    JSON.stringify(prevProps.tourDates) === JSON.stringify(nextProps.tourDates)
  );
});

LocalTransportComponent.displayName = 'LocalTransportComponent';

export default LocalTransportComponent;