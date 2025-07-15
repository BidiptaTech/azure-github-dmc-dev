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
  const searchDayIndex = useSelector((state) => state.localtour.searchDayIndex);

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
  
  // Simple refs to prevent multiple initialization (following attraction pattern)
  const hasInitializedRef = useRef(false);
  const hasDispatchedAllTransportServicesRef = useRef(false);
  const currentServicesRef = useRef([]);

  // Update the current services ref when allServices changes
  useEffect(() => {
    currentServicesRef.current = allServices;
  }, [allServices]);

  // Function to initialize bookings from props data (similar to attraction component)
  const initializeBookingsFromProps = useCallback(() => {
    const initializedBookings = [];
    const processedIds = new Set();
    
    // Helper function to check if booking belongs to current dayIndex
    const shouldShowBookingForThisDay = (bookingDate) => {
      if (!date || !bookingDate) return false;
      
      try {
        let currentDayDateStr;
        let bookingDateStr;
        
        // Handle the date prop
        if (typeof date === 'object' && date.format) {
          currentDayDateStr = date.format('YYYY-MM-DD');
        } else if (typeof date === 'string') {
          const tempDate = new Date(date);
          if (!isNaN(tempDate.getTime())) {
            currentDayDateStr = tempDate.toISOString().split('T')[0];
          } else {
            currentDayDateStr = date;
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
            bookingDateStr = bookingDate;
          }
        } else {
          return false;
        }
        
        const matchesCurrentDay = currentDayDateStr === bookingDateStr;
        const isOrphanedBooking = tourDates && tourDates.length > 0 && !tourDates.includes(bookingDateStr);
        const showOrphanedInFirstDay = isOrphanedBooking && dayIndex === 0;
        
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
            if (processedIds.has(bookingData.id)) return;
            processedIds.add(bookingData.id);
            
            if (!shouldShowBookingForThisDay(bookingData.bookingDate)) return;
            
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
            if (processedIds.has(bookingData.id)) return;
            processedIds.add(bookingData.id);
            
            if (!shouldShowBookingForThisDay(bookingData.bookingDate)) return;
            
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
            if (processedIds.has(bookingData.id)) return;
            processedIds.add(bookingData.id);
            
            if (!shouldShowBookingForThisDay(bookingData.bookingDate)) return;
            
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
      console.log(`Local Transport - Found ${initializedBookings.length} bookings for dayIndex ${dayIndex}`);
    }
    
    return initializedBookings;
  }, [date, dayIndex, PointToPoint, Hourly, LocalTransports, tourDates]);

  // Function to dispatch ALL transport services from props to Redux state (similar to attraction pattern)
  const dispatchAllTransportServicesToRedux = useCallback(() => {
    const allTransportServices = [...(PointToPoint || []), ...(Hourly || []), ...(LocalTransports || [])];
    
    if (allTransportServices.length === 0) {
      console.log('No transport services to dispatch to Redux');
      return;
    }

    console.log('Local Transport - Dispatching ALL transport services from props to Redux:', allTransportServices);

    // Remove any existing transport services using the ref (similar to attraction pattern)
    const filteredServices = currentServicesRef.current.filter(service => 
      !["travel_point", "travel_hourly", "local_transport"].includes(service.type)
    );

    // Add new transport services, preserving all original properties
    const newTransportServices = allTransportServices.map(transportService => ({
      ...transportService,
      agent_id: agentId || transportService.agent_id,
      tour_id: tourId || transportService.tour_id
    }));

    // Add new services to filtered services
    const finalServices = [...filteredServices, ...newTransportServices];

    console.log('Local Transport - Final services dispatched to Redux:', finalServices);
    dispatch(setAllServices(finalServices));
  }, [PointToPoint, Hourly, LocalTransports, agentId, tourId, dispatch]);

  // Reset refs when dayIndex changes
  useEffect(() => {
    hasInitializedRef.current = false;
    hasDispatchedAllTransportServicesRef.current = false;
    currentServicesRef.current = [];
  }, [dayIndex]);

  // Cleanup effect
  useEffect(() => {
    return () => {
      hasInitializedRef.current = false;
      hasDispatchedAllTransportServicesRef.current = false;
      currentServicesRef.current = [];
    };
  }, []);

  // Initialize bookings when props data is available (similar to attraction pattern)
  useEffect(() => {
    if (!hasInitializedRef.current && 
        ((PointToPoint && PointToPoint.length > 0) || 
         (Hourly && Hourly.length > 0) || 
         (LocalTransports && LocalTransports.length > 0))) {
      
      console.log('Local Transport - Initializing bookings from props');
      const initializedBookings = initializeBookingsFromProps();
      
      if (initializedBookings.length > 0) {
        setAllBookings(initializedBookings);
        setExpandedSections(initializedBookings.map((_, index) => index));
        
        // Update search performed state
        const transportTypesWithData = [...new Set(initializedBookings.map(b => b.transportType))];
        setSearchPerformed(prev => {
          const updated = { ...prev };
          transportTypesWithData.forEach(type => {
            updated[type] = true;
          });
          return updated;
        });
      }
      
      hasInitializedRef.current = true;
    }
  }, [PointToPoint, Hourly, LocalTransports, initializeBookingsFromProps]);

  // Dispatch ALL transport services to Redux when props data is available (similar to attraction pattern)
  useEffect(() => {
    if (!hasDispatchedAllTransportServicesRef.current && 
        ((PointToPoint && PointToPoint.length > 0) || 
         (Hourly && Hourly.length > 0) || 
         (LocalTransports && LocalTransports.length > 0))) {
      
      console.log('Local Transport - Dispatching ALL transport services from props to Redux on mount');
      dispatchAllTransportServicesToRedux();
      hasDispatchedAllTransportServicesRef.current = true;
    }
  }, [PointToPoint, Hourly, LocalTransports, dispatchAllTransportServicesToRedux]);

  // Cache vehicles when available
  useEffect(() => {
    if (hasVehicles && selectedPort) {
      setCachedVehicles(prev => ({
        ...prev,
        [selectedPort]: vehicles
      }));
    }
  }, [vehicles, hasVehicles, selectedPort]);

  // Create new booking when search is performed
  useEffect(() => {
    if (hasVehicles && selectedPort && searchDayIndex === dayIndex) {
      console.log(`Local Transport - Creating new booking for search initiated by this component`);
      
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
        
        setAllBookings(prev => {
          const newBookings = [...prev, newBookingData];
          const newIndex = newBookings.length - 1;
          setExpandedSections(prevExpanded => [...prevExpanded, newIndex]);
          
          // Clear the search day index after creating the booking
          dispatch(clearSearchDayIndex());
          
          return newBookings;
        });
      }
    }
  }, [hasVehicles, selectedPort, searchDayIndex, dayIndex, pickupLocation, dropoffLocation, pickupTime, pickupDate, exitPickupLocation, pickupTime1, exitPickupDate, pickupTimeZone, dispatch, allBookings]);

  // Validation function
  const isBookingValid = useCallback((section) => {
    return section.vehicleId && 
           (section.adults + section.children > 0) && 
           section.priceMode && 
           section.price > 0 &&
           (section.transportType !== "Hourly" || (section.hours && section.hours >= 1));
  }, []);

  // Simple removal function (similar to attraction pattern)
  const handleRemoveBooking = useCallback((indexToRemove) => {
    const bookingToRemove = allBookings[indexToRemove];
    
    if (!bookingToRemove) {
      console.log("Local Transport - No booking found at index:", indexToRemove);
      return;
    }

    console.log("Local Transport - Removing booking:", bookingToRemove);
    
    // Remove from local state
    setAllBookings(prevBookings => prevBookings.filter((_, index) => index !== indexToRemove));
    setExpandedSections(prev => 
      prev.filter(index => index !== indexToRemove)
          .map(index => index > indexToRemove ? index - 1 : index)
    );
    
    // Remove from Redux state if the booking has data
    const hasOriginalId = bookingToRemove?.originalData?.id;
    const hasVehicleId = bookingToRemove?.vehicleId;
    
    if (hasOriginalId || hasVehicleId) {
      // Clone the existing services array using the ref
      const currentServices = [...currentServicesRef.current];
      
      // Filter out transport services that contain this booking
      const filteredServices = currentServices.map(service => {
        // Check if this is a transport service
        if (["travel_point", "travel_hourly", "local_transport"].includes(service.type)) {
          // Check if this service contains data that matches our booking
          if (service.data && Array.isArray(service.data)) {
            // Remove the specific booking
            const filteredData = service.data.filter(dataItem => {
              // Match by original ID first
              if (hasOriginalId && dataItem.id === hasOriginalId) {
                return false;
              }
              
              // Match by vehicle ID and other criteria as fallback
              if (hasVehicleId && 
                  dataItem.vehicles_id === bookingToRemove.vehicleId &&
                  dataItem.type === bookingToRemove.priceMode &&
                  dataItem.adults === bookingToRemove.adults &&
                  dataItem.children === bookingToRemove.children) {
                return false;
              }
              
              return true;
            });
            
            if (filteredData.length === 0) {
              // If no data left, mark for removal
              return null;
            } else {
              // Create a new service with filtered data
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
      if (filteredServices.length !== currentServices.length || 
          JSON.stringify(filteredServices) !== JSON.stringify(currentServices)) {
        console.log("Local Transport - Updating Redux services after removal");
        dispatch(setAllServices(filteredServices));
      }
    }
  }, [allBookings, dispatch]);

  // Handler functions (simplified)
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