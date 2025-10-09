import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import {
  Typography,
  Container,
  Button,
  Box,
  Grid,
  Card,
  CardContent,
  Stack,
  IconButton,
  Tooltip,
  Alert,
  Chip,
  Collapse,
  Fade,
  Zoom,
  Slide,
  useTheme,
  alpha,
  Paper,
} from '@mui/material';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import PeopleIcon from '@mui/icons-material/People';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import RestaurantMenuIcon from '@mui/icons-material/RestaurantMenu';
import DinnerDiningIcon from '@mui/icons-material/DinnerDining';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import RadioButtonUncheckedIcon from '@mui/icons-material/RadioButtonUnchecked';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import ExpandLessIcon from '@mui/icons-material/ExpandLess';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import VisibilityIcon from '@mui/icons-material/Visibility';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import { useDispatch, useSelector } from 'react-redux';
import RestaurantListing from './RestaurantListing';
import MealTypeSelect from './MealTypeSelect';
import SpecificMealSelect from './SpecificMealSelect';
import TimeSlotSelect from './TimeSlotSelect';
import PaxSelector from './PaxSelector';
import RestaurantBookingSummaryModal from './RestaurantBookingSummaryModal';
import { setAllServices } from '../../../slice/tour-packages/tourPackageSlice';
import { fetchRestaurants } from '../../../slice/restaurant/RestaurantsSlice';
import PortCity from './PortCity';
import { shallowEqual } from 'react-redux';
const initialFormState = {
  restaurant: '',
  mealType: '',
  specificMeal: '',
  timeSlot: '',
  bookingDate: new Date().toISOString().split('T')[0],
  pax: {
    Adults: 1,
    Children: 0
  }
};

export default function RestaurantComponent({ date, dayIndex, restaurantspack, tourDates = [] }) {
  const theme = useTheme();
  const dispatch = useDispatch();
  const [selectedRestaurant, setSelectedRestaurant] = useState(null);
  const restaurants = useSelector((state) => state.restaurants.restaurants);
  const restaurantDetails = useSelector((state) => state.restaurants.restaurantDetails);
  const searchParams = useSelector((state) => state.restaurants.searchParams, shallowEqual);
  const status = useSelector((state) => state.restaurants.status);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  const country = useSelector((state) => state.tourPackages.searchCriteria.country);
  const tour = useSelector((state) => state.hotels.tourdetails, shallowEqual);
  console.log('Restaurant update', restaurantspack);
  console.log("tour", tour);
  
  // Get existing services from Redux state
  const existingServices = useSelector((state) => state.tourPackages.AllServices || []);

  // Helper function to convert any date format to YYYY-MM-DD string
  const formatDateToString = (dateInput) => {
    if (!dateInput) {
      return new Date().toISOString().split('T')[0];
    }
    
    // If it's a Moment object
    if (dateInput._isAMomentObject) {
      return dateInput.format('YYYY-MM-DD');
    }
    
    // If it's already a string in YYYY-MM-DD format
    if (typeof dateInput === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(dateInput)) {
      return dateInput;
    }
    
    // If it's a Date object or other format
    try {
      return new Date(dateInput).toISOString().split('T')[0];
    } catch (error) {
      console.error('Error formatting date:', error);
      return new Date().toISOString().split('T')[0];
    }
  };

  // Use the passed date as the booking date for this specific day
  const bookingDate = formatDateToString(date);
  
  // State for validation and success messages
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [expandedSections, setExpandedSections] = useState([0]);
  
  // City selection state
  const [selectedCity, setSelectedCity] = useState(null);
  const [cityError, setCityError] = useState(false);
  const [isCityEnabled, setIsCityEnabled] = useState(true);
  const [isRestaurantListingEnabled, setIsRestaurantListingEnabled] = useState(false);
  console.log("selectedCity", selectedCity);
  
  // Use a ref to track restaurant bookings to prevent them from being lost during re-renders
  const restaurantBookingsRef = useRef([]);
  // State to trigger re-renders when bookings change
  const [bookingsVersion, setBookingsVersion] = useState(0);
  // Track which sections have already been saved to Redux
  const [savedSectionIds, setSavedSectionIds] = useState([]);
  
  // Add refs for handling restaurantspack data (following attraction pattern)
  const hasInitializedRef = useRef(false);
  const lastDispatchRef = useRef(null);
  const hasDispatchedAllRestaurantsRef = useRef(false);
  const currentServicesRef = useRef([]);
  const isInitializingRef = useRef(false);
  const hasDataConflictsRef = useRef(false);
  
  // Initialize form sections with stable default values
  const defaultSection = useMemo(() => ({
    ...initialFormState,
    bookingDate: bookingDate, // Use the date from the specific itinerary day
    pax: {
      Adults: searchParams?.adults || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams?.adults, searchParams?.children, bookingDate]);
  console.log("defaultSection", defaultSection);
  
  const [formSections, setFormSections] = useState([{ ...defaultSection }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);

  console.log("selectedSectionIndex13", selectedSectionIndex);
  console.log("formSections13", formSections);
  // Update the current services ref when existingServices changes
  useEffect(() => {
    currentServicesRef.current = existingServices;
  }, [existingServices]);

  // Reset restaurant listing state when city changes or component mounts
  useEffect(() => {
    if (!selectedCity) {
      setIsRestaurantListingEnabled(false);
    }
  }, [selectedCity]);

  // Debug effect to track isRestaurantListingEnabled changes
  useEffect(() => {
    console.log("isRestaurantListingEnabled changed to:", isRestaurantListingEnabled);
  }, [isRestaurantListingEnabled]);

  // Function to initialize form sections from restaurantspack data (following attraction pattern)
  const initializeFormSectionsFromRestaurantPack = useCallback(() => {
    if (!restaurantspack || !Array.isArray(restaurantspack) || restaurantspack.length === 0) {
      console.log('No restaurantspack data to initialize from');
      isInitializingRef.current = false; // Ensure flag is reset even when no data
      return;
    }

    console.log('Initializing form sections from restaurantspack:', restaurantspack);
    
    // Check for data conflicts that could cause infinite loops (for logging only)
    const hasDataConflicts = restaurantspack.some(restaurantService => {
      const restaurantData = restaurantService.data?.[0];
      if (!restaurantData) return false;
      
      // Check if adult/child counts from restaurantspack don't match search form data
      const searchAdults = tour?.adult || 0;
      const searchChildren = tour?.child || 0;
      const restaurantAdults = Number(restaurantData.adultCount) || 0;
      const restaurantChildren = Number(restaurantData.childCount) || 0;
      
      const hasMismatch = (searchAdults !== restaurantAdults) || (searchChildren !== restaurantChildren);
      
      if (hasMismatch) {
        console.warn('Restaurant data mismatch detected but proceeding with initialization:', {
          searchForm: { adults: searchAdults, children: searchChildren },
          restaurantData: { adults: restaurantAdults, children: restaurantChildren },
          restaurantId: restaurantData.RestaurantId
        });
      }
      
      return hasMismatch;
    });
    
    // Store the conflict status for use in auto-dispatch logic
    hasDataConflictsRef.current = hasDataConflicts;
    if (hasDataConflicts) {
      console.log('Restaurant data conflicts detected - will proceed with initialization but skip auto-dispatch');
    }
    
    // Set initialization flag to prevent handleInputChange from triggering during initialization
    isInitializingRef.current = true;

    // Filter restaurants - show all for first dayIndex, match by bookingDate for other days
    const dayRestaurants = restaurantspack.filter(restaurantService => {
      const restaurantData = restaurantService.data?.[0];
      
      if (!restaurantData) return false;

      // For first dayIndex (dayIndex === 0), show restaurants that either match current bookingDate OR don't match any tour dates
      if (dayIndex === 0) {
       
        
        // Show if it matches current bookingDate OR doesn't match any tour dates
        const matchesCurrentDate = restaurantData.bookingDate === bookingDate;
        const notInTourDates = !tourDates.includes(restaurantData.bookingDate);
        const shouldShow = matchesCurrentDate || notInTourDates;
        
        
        return shouldShow;
      }

      // For other dayIndexes, match by bookingDate
      return restaurantData.bookingDate === bookingDate;
    });

    if (dayRestaurants.length === 0) {
      isInitializingRef.current = false; // Ensure flag is reset when no restaurants found
      console.log(`No restaurants found for dayIndex ${dayIndex}${dayIndex === 0 ? ' (showing all dates)' : ` (bookingDate: ${bookingDate})`}`);
      return;
    }

    // Convert restaurant data to form sections for current day
    const newFormSections = dayRestaurants.map((restaurantService, index) => {
      const restaurantData = restaurantService.data[0];
      
      // Create proper specificMeal object for modal compatibility
      const specificMealObject = {
        specificMealType: restaurantData.mealSpecificType,
        totalPrice: restaurantData.totalPrice || 0,
        items: restaurantData.MealDescription || []
      };
      
      return {
        restaurant: restaurantData.restaurantId,
        restaurantName: restaurantData.restaurantName || '',
        city: restaurantData.city || '',
        country: restaurantData.country || '',
        image: restaurantData.image || '',
        mealType: restaurantData.mealType,
        specificMeal: specificMealObject, // Use object format for modal compatibility
        timeSlot: restaurantData.visitTime || '',
        pax: {
          Adults: restaurantData.adultCount || 0,
          Children: restaurantData.childCount || 0
        },
        bookingDate: restaurantData.bookingDate || bookingDate,
        // Store the original data for reference, including booking_id
        originalData: {
          ...restaurantData,
          booking_id: restaurantService.booking_id // Preserve booking_id from service level
        }
        
      };
    });

    console.log('Initialized form sections for current day:', newFormSections);
    setFormSections(newFormSections);
    setExpandedSections(newFormSections.map((_, index) => index));
    
    // Reset initialization flag after form sections are set
    setTimeout(() => {
      isInitializingRef.current = false;
      console.log('Restaurant initialization completed, handleInputChange is now enabled');
    }, 100);
  }, [ dayIndex, bookingDate, tour]); // Added tourDates to dependencies

  // Function to dispatch ALL restaurants from restaurantspack to Redux state (following attraction pattern)
  const dispatchAllRestaurantsToRedux = useCallback(() => {
    if (!restaurantspack || !Array.isArray(restaurantspack) || restaurantspack.length === 0) {
      console.log('No restaurantspack data to dispatch to Redux');
      return;
    }

    // Create a unique key for this dispatch to prevent duplicates
    const dispatchKey = JSON.stringify(restaurantspack.map(service => service.data?.[0]?.restaurantId));
    
    if (lastDispatchRef.current === dispatchKey) {
      console.log('Skipping duplicate dispatch for all restaurants');
      return;
    }

    // Check if there are already restaurant services for the current day in Redux
    const existingRestaurantServices = currentServicesRef.current.filter(service => 
      service.type === "restaurant" && 
      service.data && 
      Array.isArray(service.data) &&
      service.data.some(booking => booking.bookingDate === bookingDate)
    );

    if (existingRestaurantServices.length > 0) {
      console.log('Restaurant services already exist for this day, skipping dispatch to prevent duplicates');
      return;
    }

    console.log('Dispatching ALL restaurants from restaurantspack to Redux:', restaurantspack);

    // Remove any existing restaurant services using the ref
    const filteredServices = currentServicesRef.current.filter(service => service.type !== "restaurant");

    // Create new restaurant service entries for ALL restaurants, preserving booking_id
    const newRestaurantServices = restaurantspack.map(restaurantService => {
      const restaurantData = restaurantService.data[0];
      
      if (!restaurantData) {
        console.log('No restaurant data found in service:', restaurantService);
        return null;
      }

      console.log('Processing restaurant for Redux:', restaurantData);
      
      // Create service object with booking_id preserved
      const serviceObject = {
        type: "restaurant",
        agent_id: agentId,
        tour_id: tourId,
        bookingType: "enquiry",
        data: [restaurantData]
      };

      // Add booking_id if it exists in the original service
      if (restaurantService.booking_id) {
        serviceObject.booking_id = restaurantService.booking_id;
      }

      return serviceObject;
    }).filter(Boolean); // Remove null entries

    if (newRestaurantServices.length === 0) {
      console.log('No valid restaurants to dispatch to Redux');
      return;
    }

    // Add new services to filtered services
    const finalServices = [...filteredServices, ...newRestaurantServices];

    console.log('Dispatching ALL restaurant services to Redux:', finalServices);
    dispatch(setAllServices(finalServices));
    
    // Update the last dispatch ref
    lastDispatchRef.current = dispatchKey;
  }, [restaurantspack, agentId, tourId, dispatch]);

  // Reset refs when dayIndex changes (following attraction pattern)
  useEffect(() => {
    hasInitializedRef.current = false;
    lastDispatchRef.current = null;
    hasDispatchedAllRestaurantsRef.current = false;
    currentServicesRef.current = [];
    isInitializingRef.current = false;
    hasDataConflictsRef.current = false;
  }, [dayIndex]);

  // Cleanup effect (following attraction pattern)
  useEffect(() => {
    return () => {
      hasInitializedRef.current = false;
      lastDispatchRef.current = null;
      hasDispatchedAllRestaurantsRef.current = false;
      currentServicesRef.current = [];
      isInitializingRef.current = false;
      hasDataConflictsRef.current = false;
        };
  }, []);

  // Initialize form sections when restaurantspack changes (following attraction pattern)
  useEffect(() => {
    if (!hasInitializedRef.current && restaurantspack && Array.isArray(restaurantspack) && restaurantspack.length > 0) {
      console.log('Initializing form sections from restaurantspack');
      initializeFormSectionsFromRestaurantPack();
      hasInitializedRef.current = true;
    }
  }, [restaurantspack, initializeFormSectionsFromRestaurantPack]);

  // Dispatch ALL restaurants to Redux when restaurantspack is available (only once) (following attraction pattern)
  useEffect(() => {
    if (!hasDispatchedAllRestaurantsRef.current && restaurantspack && Array.isArray(restaurantspack) && restaurantspack.length > 0) {
      console.log('Dispatching ALL restaurants from restaurantspack to Redux on mount');
      dispatchAllRestaurantsToRedux();
      hasDispatchedAllRestaurantsRef.current = true;
    }
  }, [restaurantspack, dispatchAllRestaurantsToRedux]);
  
  // Log props received from parent component (enhanced logging following attraction pattern)
  useEffect(() => {
    console.log('RestaurantComponent - Received props:', { date, dayIndex, bookingDate, restaurantspack });
    console.log('RestaurantComponent - Date type check:', { 
      dateType: typeof date, 
      isMoment: date?._isAMomentObject,
      formattedBookingDate: bookingDate,
      bookingDateType: typeof bookingDate
    });
    console.log('RestaurantComponent - Form sections count:', formSections.length);
    console.log('RestaurantComponent - Has initialized:', hasInitializedRef.current);
    console.log('RestaurantComponent - Form sections with booking dates:', formSections.map(section => ({
      bookingDate: section.bookingDate,
      dayIndex: dayIndex,
      hasOriginalData: !!section.originalData
    })));
  }, [date, dayIndex, bookingDate, formSections, restaurantspack]);
  
  // Getter and setter for bookings
  const getRestaurantBookings = () => restaurantBookingsRef.current;
  
  const setRestaurantBookings = (newBookings) => {
    // Check if the bookings array has actually changed before updating
    const currentBookings = restaurantBookingsRef.current;
    if (JSON.stringify(currentBookings) !== JSON.stringify(newBookings)) {
      restaurantBookingsRef.current = newBookings;
      setBookingsVersion(prev => prev + 1); // Trigger re-render
    }
  };
  
  // Check if existing restaurant bookings exist in Redux and load them
  useEffect(() => {
    // Find existing restaurant bookings
    if (existingServices && existingServices.length > 0) {
      const existingRestaurantServices = existingServices.filter(service => 
        service.type === "restaurant" && service.data && Array.isArray(service.data)
      );
      
      // Flatten all restaurant data into one array
      const allRestaurants = existingRestaurantServices.flatMap(service => service.data);
      
      // Set to the ref
      if (allRestaurants && allRestaurants.length > 0) {
        restaurantBookingsRef.current = allRestaurants;
        setBookingsVersion(prev => prev + 1);
      }
    }
  }, [existingServices]);
  
  // Initialize form with one section when component mounts or searchParams changes
  useEffect(() => {
    if (searchParams && restaurants?.length > 0 && formSections.length === 0) {
      setFormSections([{ ...defaultSection }]);
    }
  }, [searchParams, restaurants, defaultSection]);

  // Initialize expanded sections
  useEffect(() => {
    if (formSections.length > 0 && expandedSections.length === 0) {
      setExpandedSections([0]);
    }
  }, [formSections.length, expandedSections.length]);

  const handleAddMore = () => {
    const newIndex = formSections.length;
    const newSection = { 
      ...defaultSection,
      // Ensure new sections don't have originalData to avoid conflicts (following attraction pattern)
      originalData: null
    };
    setFormSections([...formSections, newSection]);
    setExpandedSections([...expandedSections, newIndex]);
  };

  const handleRemoveSection = (indexToRemove) => {
    const sectionToRemove = formSections[indexToRemove];
    
    if (!sectionToRemove) {
      console.log("Restaurant - No section found at index:", indexToRemove);
      return;
    }

    console.log("Restaurant - Removing section:", sectionToRemove);
    
    // Remove from local state
    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    
    // Update expanded sections
    setExpandedSections(expandedSections.filter(index => index !== indexToRemove).map(index => index > indexToRemove ? index - 1 : index));
    
    // Remove from Redux state if the section has restaurant data (either has an original ID or restaurant selection)
    const hasOriginalId = sectionToRemove?.originalData?.restaurantId;
    const hasRestaurantId = sectionToRemove?.restaurant;
    console.log("Restaurant - Has original ID:", hasOriginalId);
    console.log("Restaurant - Section to remove:", sectionToRemove);
    
    if (hasOriginalId || hasRestaurantId) {
      // Clone the existing services array
      const currentServices = [...existingServices];
      console.log("Restaurant - Current services before removal:", currentServices);
      
      // Filter out the specific restaurant service
      const filteredServices = currentServices.filter(service => {
        // Check if this is a restaurant service
        if (service.type === "restaurant") {
          // For existing services with booking_id, match by booking_id
          if (sectionToRemove.originalData?.booking_id && service.booking_id) {
            const shouldRemove = service.booking_id === sectionToRemove.originalData.booking_id;
            console.log(`Restaurant - Checking booking_id match: ${service.booking_id} === ${sectionToRemove.originalData.booking_id} = ${shouldRemove}`);
            return !shouldRemove;
          }
          
          // For new services without booking_id, match by restaurant data
          if (service.data && Array.isArray(service.data)) {
            const hasMatchingData = service.data.some(dataItem => {
              // Match by restaurantId and bookingDate
              if (sectionToRemove.restaurant && dataItem.restaurantId) {
                const matchesRestaurant = dataItem.restaurantId === sectionToRemove.restaurant;
                const matchesDate = dataItem.bookingDate === sectionToRemove.bookingDate;
                console.log(`Restaurant - Checking data match: restaurantId ${dataItem.restaurantId} === ${sectionToRemove.restaurant} && bookingDate ${dataItem.bookingDate} === ${sectionToRemove.bookingDate} = ${matchesRestaurant && matchesDate}`);
                return matchesRestaurant && matchesDate;
              }
              return false;
            });
            
            if (hasMatchingData) {
              console.log("Restaurant - Found matching data, removing service");
              return false; // Remove this service
            }
          }
        }
        
        // Keep all other services
        return true;
      });
      
      console.log("Restaurant - Filtered services after removal:", filteredServices);
      
      // Only dispatch if there's an actual change
      if (filteredServices.length !== currentServices.length) {
        console.log("Restaurant - Removing restaurant service from Redux");
        console.log(`Restaurant - Services count: ${currentServices.length} -> ${filteredServices.length}`);
        dispatch(setAllServices(filteredServices));
      } else {
        console.log("Restaurant - No matching service found to remove");
      }
    }
    
    // Remove section signature from saved IDs
    if (sectionToRemove) {
      const sectionSignature = `${sectionToRemove.restaurant}-${sectionToRemove.mealType}-${sectionToRemove.specificMeal}-${sectionToRemove.timeSlot}-${dayIndex}`;
      setSavedSectionIds(prev => prev.filter(signature => signature !== sectionSignature));
    }
  };

  const toggleSectionExpand = (index) => {
    if (expandedSections.includes(index)) {
      setExpandedSections(expandedSections.filter(i => i !== index));
    } else {
      setExpandedSections([...expandedSections, index]);
    }
  };

  const handleInputChange = (sectionIndex, field, value) => {
    console.log('handleInputChange called:', { sectionIndex, field, value, currentFormSections: formSections, isInitializing: isInitializingRef.current });
    
    // Skip updates during initialization to prevent overwriting initialized data
    if (isInitializingRef.current) {
      console.log('Skipping handleInputChange during initialization');
      return;
    }
    
    const newFormSections = [...formSections];
    
    // Generate old signature before changes
    const oldSectionSignature = formSections[sectionIndex] ? 
      `${formSections[sectionIndex].restaurant}-${formSections[sectionIndex].mealType}-${formSections[sectionIndex].specificMeal}-${formSections[sectionIndex].timeSlot}-${dayIndex}` : '';
    
    if (field === 'restaurant') {
      // Find the selected restaurant details
      const selectedRestaurantDetails = restaurants.find(r => r.id === value);
      
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        restaurant: value,
        restaurantName: selectedRestaurantDetails?.name || selectedRestaurantDetails?.restaurant_name || '',
        city: selectedRestaurantDetails?.city || '',
        country: selectedRestaurantDetails?.country || '',
        image: selectedRestaurantDetails?.master_image || selectedRestaurantDetails?.additional_images?.[0] || '',
        // Reset dependent fields when restaurant changes
        mealType: '',
        specificMeal: '',
        timeSlot: ''
      };
      setSelectedRestaurant(value);
      
      // Remove old signature since restaurant changed
      if (oldSectionSignature) {
        setSavedSectionIds(prev => 
          prev.filter(signature => signature !== oldSectionSignature)
        );
        console.log(`Restaurant booking section ${sectionIndex + 1} restaurant changed, will be re-evaluated for saving`);
      }
    } else if (field === 'mealType') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        mealType: value,
        specificMeal: '',
        timeSlot: '',
        bookingDate: bookingDate // Preserve booking date
      };
      
      // Check completion and manage saved signatures
      const updatedSection = newFormSections[sectionIndex];
      const isComplete = 
        updatedSection.restaurant && 
        updatedSection.mealType && 
        updatedSection.specificMeal && 
        updatedSection.timeSlot && 
        (updatedSection.pax.Adults + updatedSection.pax.Children > 0);
      
      const newSectionSignature = 
        `${updatedSection.restaurant}-${updatedSection.mealType}-${updatedSection.specificMeal}-${updatedSection.timeSlot}-${dayIndex}`;
      
      // If the data changed, remove the old signature from saved list
      if (oldSectionSignature !== newSectionSignature) {
        setSavedSectionIds(prev => 
          prev.filter(signature => signature !== oldSectionSignature)
        );
        console.log(`Restaurant booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
      }
    } else if (field === 'specificMeal' && value === '') {
      // Clear timeSlot when specificMeal is cleared (e.g., on cancel)
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        specificMeal: '',
        timeSlot: '',
        bookingDate: bookingDate // Preserve booking date
      };
      
      // Check completion and manage saved signatures
      const updatedSection = newFormSections[sectionIndex];
      const isComplete = 
        updatedSection.restaurant && 
        updatedSection.mealType && 
        updatedSection.specificMeal && 
        updatedSection.timeSlot && 
        (updatedSection.pax.Adults + updatedSection.pax.Children > 0);
      
      const newSectionSignature = 
        `${updatedSection.restaurant}-${updatedSection.mealType}-${updatedSection.specificMeal}-${updatedSection.timeSlot}-${dayIndex}`;
      
      // If the data changed, remove the old signature from saved list
      if (oldSectionSignature !== newSectionSignature) {
        setSavedSectionIds(prev => 
          prev.filter(signature => signature !== oldSectionSignature)
        );
        console.log(`Restaurant booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
      }
    } else if (field === 'pax') {
      const currentPax = newFormSections[sectionIndex].pax;
      if (
        currentPax.Adults !== value.Adults ||
        currentPax.Children !== value.Children
      ) {
        newFormSections[sectionIndex] = {
          ...newFormSections[sectionIndex],
          pax: {
            Adults: value.Adults || 0,
            Children: value.Children || 0
          }
        };
        setFormSections(newFormSections);
        
        // Check completion and manage saved signatures
        const updatedSection = newFormSections[sectionIndex];
        const isComplete = 
          updatedSection.restaurant && 
          updatedSection.mealType && 
          updatedSection.specificMeal && 
          updatedSection.timeSlot && 
          (updatedSection.pax.Adults + updatedSection.pax.Children > 0);
        
        const newSectionSignature = 
          `${updatedSection.restaurant}-${updatedSection.mealType}-${updatedSection.specificMeal}-${updatedSection.timeSlot}-${dayIndex}`;
        
        // If the data changed, remove the old signature from saved list
        if (oldSectionSignature !== newSectionSignature) {
          setSavedSectionIds(prev => 
            prev.filter(signature => signature !== oldSectionSignature)
          );
          console.log(`Restaurant booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
        }
        
        // Dispatch update to Redux if section is complete
        if (isComplete) {
          console.log(`Restaurant booking section ${sectionIndex + 1} is now complete`);
          dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
        }
      }
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        bookingDate: bookingDate // Preserve booking date
      };
      
      // Check completion and manage saved signatures
      const updatedSection = newFormSections[sectionIndex];
      const isComplete = 
        updatedSection.restaurant && 
        updatedSection.mealType && 
        updatedSection.specificMeal && 
        updatedSection.timeSlot && 
        (updatedSection.pax.Adults + updatedSection.pax.Children > 0);
      
      const newSectionSignature = 
        `${updatedSection.restaurant}-${updatedSection.mealType}-${updatedSection.specificMeal}-${updatedSection.timeSlot}-${dayIndex}`;
      
      // If the data changed, remove the old signature from saved list
      if (oldSectionSignature !== newSectionSignature) {
        setSavedSectionIds(prev => 
          prev.filter(signature => signature !== oldSectionSignature)
        );
        console.log(`Restaurant booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
      }
      
      // Dispatch update to Redux if section is complete
      if (isComplete) {
        console.log(`Restaurant booking section ${sectionIndex + 1} is now complete`);
        dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
      }
    }
    
    console.log('Updated form sections:', newFormSections);
    setFormSections(newFormSections);
  };

  // Alias for backward compatibility with existing component calls
  const handleFieldChange = handleInputChange;
  const handlePaxChange = (sectionIndex, value) => handleInputChange(sectionIndex, 'pax', value);

  const handleOpenModal = useCallback((index) => {
    setSelectedSectionIndex(index);
    setOpenModal(true);
  }, []);

  const handleCloseModal = useCallback(() => {
    setOpenModal(false);
    setSelectedSectionIndex(null);
  }, []);

  // Handle city selection
  const handleCitySelect = (city) => {
    console.log("City selected:", city);
    console.log("Current isRestaurantListingEnabled:", isRestaurantListingEnabled);
    setSelectedCity(city);
    
    if (city) {
      setCityError(false);
      // Disable restaurant listing until API call is successful
      console.log("Disabling restaurant listing - waiting for API response");
      setIsRestaurantListingEnabled(false);
      
      // Dispatch fetchRestaurants API call
      console.log("Dispatching fetchRestaurants with params:", {
        city: `${city.name}, (${country})`,
        date: bookingDate,
        adults: tour.adult,
        children: tour.child,
        tour_id: tour.tour_id,
        fromMainSearch: false
      });
      
      dispatch(fetchRestaurants({ 
        city: `${city.name}, (${country})`, 
        date: bookingDate, 
        adults: tour.adult,
        children: tour.child,
        tour_id: tour.tour_id,
        fromMainSearch: false
      }))
        .then((result) => {
          console.log("fetchRestaurants API result:", result);
          if (result.error) {
            console.error("fetchRestaurants API Error:", result.error);
            console.log("API failed - keeping restaurant listing disabled");
            setIsRestaurantListingEnabled(false);
          } else {
            console.log("fetchRestaurants API Success - enabling restaurant listing");
            console.log("API succeeded - enabling restaurant listing");
            setIsRestaurantListingEnabled(true);
          }
        })
        .catch((error) => {
          console.error("Error dispatching fetchRestaurants:", error);
          console.log("API dispatch failed - keeping restaurant listing disabled");
          setIsRestaurantListingEnabled(false);
        });
    } else {
      // If no city selected, disable restaurant listing
      console.log("No city selected - disabling restaurant listing");
      setIsRestaurantListingEnabled(false);
    }
  };

  // Calculate completion status for each section
  const getSectionCompletion = (section) => {
    let completed = 0;
    if (section.restaurant) completed++;
    if (section.mealType) completed++;
    if (section.specificMeal) completed++;
    if (section.timeSlot) completed++;
    return completed;
  };

  // Get booking summary for a specific section
  const getBookingSummary = useCallback((booking) => {
    // If we have original data, use it directly
    if (booking.originalData) {
      console.log('Using original data for restaurant booking summary:', booking.originalData);
      return {
        restaurant: booking.originalData,
        restaurantName: booking.originalData.restaurantName,
        city: booking.originalData.city || searchParams?.location?.city || '',
        country: booking.originalData.country || searchParams?.location?.country || '',
        mealType: booking.mealType,
        specificMeal: booking.specificMeal,
        timeSlot: booking.timeSlot,
        pax: booking.pax,
        mode: currentMode,
        image: booking.originalData.image || '/placeholder-restaurant.jpg',
        cuisine: booking.originalData.cuisine_type || 'Not specified',
        bookingDate: booking.bookingDate,
        booking_id: booking.originalData.booking_id // Preserve booking_id
      };
    }

    // Fallback to finding data from Redux state (for new bookings)
    const selectedRestaurantDetails = restaurants.find(r => r.id === booking.restaurant) || {};
    
    return {
      restaurant: selectedRestaurantDetails,
      restaurantName: selectedRestaurantDetails.restaurant_name || 'Restaurant',
      city: selectedRestaurantDetails.city || searchParams?.location?.city || '',
      country: selectedRestaurantDetails.country || searchParams?.location?.country || '',
      mealType: booking.mealType,
      specificMeal: booking.specificMeal,
      timeSlot: booking.timeSlot,
      pax: booking.pax,
      mode: currentMode,
      image: selectedRestaurantDetails.image || '/placeholder-restaurant.jpg',
      cuisine: selectedRestaurantDetails.cuisine_type || 'Not specified',
      bookingDate: booking.bookingDate
    };
  }, [restaurants, searchParams, currentMode]);

  // Function to dispatch individual booking updates to Redux
  const dispatchBookingUpdateToRedux = useCallback((sectionIndex, updatedSection) => {
    if (!updatedSection.restaurant || !updatedSection.mealType || !updatedSection.specificMeal || !updatedSection.timeSlot) {
      console.log('Incomplete section, skipping Redux dispatch');
      return;
    }

    // If we have original data, use it directly
    if (updatedSection.originalData) {
      console.log('Using original data for individual booking update:', updatedSection.originalData);
      
      // Get customer details from original data if available
      const customerDetails = {
        fullName: updatedSection.originalData.fullName || "",
        email: updatedSection.originalData.email || "",
        phone: updatedSection.originalData.phone || "",
        countryCode: updatedSection.originalData.countryCode || "",
        address1: updatedSection.originalData.address1 || "",
        address2: updatedSection.originalData.address2 || "",
        state: updatedSection.originalData.state || "",
        zip: updatedSection.originalData.zip || "",
        specialRequests: updatedSection.originalData.specialRequests || "",
      };
      
      const bookingData = {
        // Include customer details
        ...customerDetails,
        
        // Core booking details
        bookingDate: updatedSection.originalData.bookingDate,
        visitTime: updatedSection.originalData.visitTime,
        adultCount: updatedSection.originalData.adultCount,
        childCount: updatedSection.originalData.childCount,
        restaurantId: updatedSection.originalData.restaurantId,
        restaurantName: updatedSection.originalData.restaurantName,
        mealType: updatedSection.originalData.mealType,
        mealSpecificType: updatedSection.originalData.mealSpecificType,
        MealDescription: updatedSection.originalData.MealDescription,
        totalPrice: updatedSection.originalData.totalPrice,
        mealPrice: updatedSection.originalData.mealPrice,
        transport: updatedSection.originalData.transport,
        transportPrice: updatedSection.originalData.transportPrice,
        priceTypes: updatedSection.originalData.priceTypes,
        dmc_id: updatedSection.originalData.dmc_id,
        bookingType: updatedSection.originalData.bookingType || "enquiry",
        booking_id: updatedSection.originalData.booking_id // Preserve booking_id
      };

      // Clone existing services
      const currentServices = [...existingServices];
      
      // Find and update existing restaurant service for this bookingDate
      let found = false;
      const updatedServices = currentServices.map(service => {
        if (service.type === "restaurant" && service.data && Array.isArray(service.data)) {
          const updatedData = service.data.map(item => {
            if (item.bookingDate === bookingDate && item.restaurantId === bookingData.restaurantId) {
              found = true;
              return bookingData;
            }
            return item;
          });
          
          if (found) {
            return { ...service, data: updatedData };
          }
        }
        return service;
      });

      // If not found, add new service entry
      if (!found) {
        const newRestaurantService = {
          type: "restaurant",
          agent_id: agentId,
          tour_id: tourId,
          data: [bookingData],
          bookingType: "enquiry"
        };
        
        // Add booking_id if available from original data
        if (updatedSection.originalData?.booking_id) {
          newRestaurantService.booking_id = updatedSection.originalData.booking_id;
        }
        
        updatedServices.push(newRestaurantService);
      }

      console.log("Restaurant - Dispatching individual booking update to Redux (original data):", bookingData);
      dispatch(setAllServices(updatedServices));
      return;
    }

    // For new bookings, calculate everything
    const summaryData = getBookingSummary(updatedSection);
    const restaurant = restaurants.find(r => r.id === updatedSection.restaurant) || {};
    
    // Get pricing data from the meal selection
    const adultCount = updatedSection.pax?.Adults || 0;
    const childCount = updatedSection.pax?.Children || 0;
    
    // Extract price information from the specificMeal selection
    let totalPrice = 0;
    let mealPrice = 0;
    let mealDescriptionArray = [];
    
    // If specificMeal contains pricing data from SpecificMealSelect
    if (updatedSection.specificMeal && typeof updatedSection.specificMeal === 'object' && updatedSection.specificMeal.totalPrice) {
      totalPrice = updatedSection.specificMeal.totalPrice;
      mealPrice = updatedSection.specificMeal.totalPrice;
      
      // Create MealDescription array from the selected items
      if (updatedSection.specificMeal.items && Array.isArray(updatedSection.specificMeal.items)) {
        mealDescriptionArray = updatedSection.specificMeal.items.map(item => ({
          item_name: item.name || "Meal Item",
          name: item.name || "Meal Item", 
          price: item.price || 0,
          meal_id: item.meal_id || restaurant.id || 0,
          category: updatedSection.mealType || "Meal",
          item_type: "Standard",
          quantity: item.quantity || 1
        }));
      }
    } else {
      // Fallback pricing if no specific meal data
      const basePrice = 50;
      totalPrice = (adultCount + childCount) * basePrice;
      mealPrice = totalPrice;
      mealDescriptionArray = [{
        item_name: updatedSection.specificMeal || "Meal",
        name: updatedSection.specificMeal || "Meal",
        price: basePrice,
        meal_id: restaurant.id || 0,
        category: updatedSection.mealType || "Meal",
        item_type: "Standard",
        quantity: adultCount + childCount
      }];
    }

    const bookingData = {
      // Customer information fields (will be populated when available)
      fullName: "",
      email: "",
      phone: "",
      countryCode: "",
      address1: "",
      address2: "",
      state: "",
      zip: "",
      specialRequests: "",
      
      // Core booking details
      bookingDate: updatedSection.bookingDate,
      visitTime: updatedSection.timeSlot,
      adultCount: adultCount,
      childCount: childCount,
      restaurantId: updatedSection.restaurant,
      restaurantName: restaurant.restaurant_name || 'Restaurant',
      mealType: updatedSection.mealType,
      mealSpecificType: typeof updatedSection.specificMeal === 'object' ? updatedSection.specificMeal.specificMealType : updatedSection.specificMeal,
      MealDescription: mealDescriptionArray,
      totalPrice: totalPrice,
      mealPrice: mealPrice,
      transport: null,
      transportPrice: 0,
      priceTypes: ["dmc"],
      dmc_id: restaurant.dmc_id || null,
      bookingType: "enquiry"
    };

    // Clone existing services
    const currentServices = [...existingServices];
    
    // Find and update existing restaurant service for this bookingDate
    let found = false;
    const updatedServices = currentServices.map(service => {
      if (service.type === "restaurant" && service.data && Array.isArray(service.data)) {
        const updatedData = service.data.map(item => {
          if (item.bookingDate === bookingDate && item.restaurantId === bookingData.restaurantId) {
            found = true;
            return bookingData;
          }
          return item;
        });
        
        if (found) {
          return { ...service, data: updatedData };
        }
      }
      return service;
    });

    // If not found, add new service entry
    if (!found) {
      const newRestaurantService = {
        type: "restaurant",
        agent_id: agentId,
        tour_id: tourId,
        data: [bookingData],
        bookingType: "enquiry"
      };
      
      // Add booking_id if available from original data
      if (updatedSection.originalData?.booking_id) {
        newRestaurantService.booking_id = updatedSection.originalData.booking_id;
      }
      
      updatedServices.push(newRestaurantService);
    }

    console.log("Restaurant - Dispatching individual booking update to Redux:", bookingData);
    dispatch(setAllServices(updatedServices));
    setBookingSuccess(true);
    
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  }, [restaurants, restaurantDetails, currentMode, agentId, tourId, dayIndex, existingServices, dispatch, getBookingSummary, bookingDate]);

  // Validate bookings before submission
  const validateBookings = useCallback(() => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one restaurant enquiry.");
      return false;
    }
    
    // Only validate complete sections
    const completeSections = formSections.filter(section => 
      section.restaurant && 
      section.mealType && 
      section.specificMeal && 
      section.timeSlot && 
      (section.pax.Adults + section.pax.Children > 0)
    );
    
    if (completeSections.length === 0) {
      // Don't show error when auto-validating
      return false;
    }
    
    setValidationError(null);
    return true;
  }, [formSections, setValidationError]);

  // Function to handle booking creation
  const handleBookNow = useCallback(() => {
    if (!validateBookings()) {
      return;
    }
    
    // Filter out incomplete sections
    const completeSections = formSections.filter(section => 
      section.restaurant && 
      section.mealType && 
      section.specificMeal && 
      section.timeSlot && 
      (section.pax.Adults + section.pax.Children > 0)
    );
    
    if (completeSections.length === 0) {
      return; // No complete sections to save
    }
    
    // Clone the existing services array, but only remove restaurant services for the current bookingDate
    // Preserve restaurant services for other dates
    const servicesWithoutRestaurants = existingServices.filter(service => {
      if (service.type !== "restaurant") {
        return true; // Keep non-restaurant services
      }
      
      // For restaurant services, check if they belong to the current bookingDate
      // If the service has data and any booking has the same bookingDate, remove it
      if (service.data && Array.isArray(service.data)) {
        const hasCurrentDayBooking = service.data.some(booking => 
          booking.bookingDate === bookingDate
        );
        return !hasCurrentDayBooking; // Keep if it doesn't have current day booking
      }
      
      return true; // Keep if no data or invalid structure
    });
    
    // Create new restaurant services for the current complete sections
    const restaurantServices = completeSections.map((section, index) => {
      const summaryData = getBookingSummary(section);
      const restaurant = restaurants.find(r => r.id === section.restaurant) || {};
      
      // Create unique booking ID - use formSections index to maintain identity
      const sectionIndex = formSections.indexOf(section);
      const bookingId = `restaurant-${Date.now()}-${sectionIndex}`;
      
      // Get pricing data from the meal selection
      const adultCount = section.pax?.Adults || 0;
      const childCount = section.pax?.Children || 0;
      
      // Extract price information from the specificMeal selection
      let totalPrice = 0;
      let mealPrice = 0;
      let mealDescriptionArray = [];
      
      // If specificMeal contains pricing data from SpecificMealSelect
      if (section.specificMeal && typeof section.specificMeal === 'object' && section.specificMeal.totalPrice) {
        totalPrice = section.specificMeal.totalPrice;
        mealPrice = section.specificMeal.totalPrice;
        
        // Create MealDescription array from the selected items
        if (section.specificMeal.items && Array.isArray(section.specificMeal.items)) {
          mealDescriptionArray = section.specificMeal.items.map(item => ({
            item_name: item.name || "Meal Item",
            name: item.name || "Meal Item", 
            price: item.price || 0,
            meal_id: item.meal_id || restaurant.id || 0,
            category: section.mealType || "Meal",
            item_type: "Standard",
            quantity: item.quantity || 1
          }));
        }
      } else {
        // Fallback pricing if no specific meal data
        const basePrice = 50;
        totalPrice = (adultCount + childCount) * basePrice;
        mealPrice = totalPrice;
        mealDescriptionArray = [{
          item_name: section.specificMeal || "Meal",
          name: section.specificMeal || "Meal",
          price: basePrice,
          meal_id: restaurant.id || 0,
          category: section.mealType || "Meal",
          item_type: "Standard",
          quantity: adultCount + childCount
        }];
      }
      
      // Create the restaurant booking data matching the exact JSON format you specified
      const bookingData = {
        // Customer information fields (will be populated when available)
        fullName: "",
        email: "",
        phone: "",
        countryCode: "",
        address1: "",
        address2: "",
        state: "",
        zip: "",
        specialRequests: "",
        
        // Core booking details
        bookingDate: section.bookingDate, // Use the date from the specific itinerary day
        visitTime: section.timeSlot,
        adultCount: adultCount,
        childCount: childCount,
        restaurantId: section.restaurant,
        restaurantName: restaurant.restaurant_name || 'Restaurant',
        mealType: section.mealType,
        mealSpecificType: typeof section.specificMeal === 'object' ? section.specificMeal.specificMealType : section.specificMeal,
        MealDescription: mealDescriptionArray,
        totalPrice: totalPrice,
        mealPrice: mealPrice,
        transport: null,
        transportPrice: 0,
        priceTypes: ["dmc"],
        dmc_id: restaurant.dmc_id || null,
        bookingType: "enquiry"
      };
      
      // Add booking_id if available from original data
      if (section.originalData?.booking_id) {
        bookingData.booking_id = section.originalData.booking_id;
      }
      
      console.log(`Restaurant enquiry data for section ${index}:`, bookingData);
      console.log(`Restaurant enquiry pricing check for section ${index}:`, {
        adultCount,
        childCount,
        totalPrice,
        mealPrice,
        mealType: section.mealType,
        specificMeal: section.specificMeal,
        specificMealType: typeof section.specificMeal === 'object' ? section.specificMeal.specificMealType : section.specificMeal,
        mealDescriptionArray
      });
      console.log(`Restaurant enquiry date check for section ${index}:`, {
        sectionBookingDate: section.bookingDate,
        formattedBookingDate: bookingDate,
        dayIndex: dayIndex,
        finalBookingDate: bookingData.bookingDate
      });
      
      // Create a new restaurant service entry matching the current working format
      const serviceObject = {
        agent_id: agentId,
        bookingType: "enquiry",
        tour_id: tourId,
        type: "restaurant",
        data: [bookingData]
      };
      
      // Add booking_id if available from original data
      if (section.originalData?.booking_id) {
        serviceObject.booking_id = section.originalData.booking_id;
      }
      
      return serviceObject;
    });
    
    // Combine non-restaurant services with new restaurant services
    const updatedServices = [...servicesWithoutRestaurants, ...restaurantServices];
    
    console.log("Restaurant - Dispatching updated enquiry services to Redux:", updatedServices);
    console.log("Restaurant - Services filtering check:", {
      totalExistingServices: existingServices.length,
      restaurantServicesRemoved: existingServices.filter(s => s.type === "restaurant").length - servicesWithoutRestaurants.filter(s => s.type === "restaurant").length,
      currentBookingDate: bookingDate,
      dayIndex: dayIndex,
      newRestaurantServices: restaurantServices.length,
      finalTotalServices: updatedServices.length,
      existingRestaurantServicesForCurrentDate: existingServices.filter(s => 
        s.type === "restaurant" && 
        s.data && 
        Array.isArray(s.data) && 
        s.data.some(booking => booking.bookingDate === bookingDate)
      ).length
    });
    
    // Dispatch the updated services
    dispatch(setAllServices(updatedServices));
    
    setBookingSuccess(true);
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  }, [formSections, existingServices, validateBookings, dispatch, getBookingSummary, restaurants, searchParams, currentMode]);

  // Ref to track if we're already processing to prevent infinite loops
  const isProcessingRef = useRef(false);
  const lastFormSectionsRef = useRef([]);
  
  // Effect to automatically dispatch completed restaurant bookings to Redux
  useEffect(() => {
    // Skip if no form sections, during loading, or already processing
    if (formSections.length === 0 || status === 'loading' || isProcessingRef.current) return;
    
    // Skip auto-dispatch if we have restaurantspack data (to prevent duplicates)
    // Only auto-dispatch when there's no restaurantspack data (new bookings)
    if (restaurantspack && Array.isArray(restaurantspack) && restaurantspack.length > 0) {
      console.log('Restaurant - Skipping auto-dispatch because restaurantspack data exists');
      return;
    }
    
    // Check for data conflicts that could cause infinite loops using the ref
    if (hasDataConflictsRef.current) {
      console.log('Restaurant - Data conflicts detected, skipping auto-dispatch to prevent infinite loops');
      return;
    }
    
    // Check if form sections have actually changed to prevent unnecessary re-runs
    const currentFormSectionsString = JSON.stringify(formSections.map(s => ({
      restaurant: s.restaurant,
      mealType: s.mealType,
      specificMeal: s.specificMeal,
      timeSlot: s.timeSlot,
      pax: s.pax
    })));
    const lastFormSectionsString = JSON.stringify(lastFormSectionsRef.current);
    
    if (currentFormSectionsString === lastFormSectionsString) {
      console.log('Restaurant - Form sections unchanged, skipping auto-dispatch');
      return;
    }
    
    // Update the ref with current form sections
    lastFormSectionsRef.current = formSections.map(s => ({
      restaurant: s.restaurant,
      mealType: s.mealType,
      specificMeal: s.specificMeal,
      timeSlot: s.timeSlot,
      pax: s.pax
    }));
    
    // Find sections that are complete but not yet saved
    const newCompleteSections = formSections.filter((section) => {
      // Check if all required fields are filled
      const isComplete = (
        section.restaurant && 
        section.mealType && 
        section.specificMeal && 
        section.timeSlot && 
        (section.pax.Adults + section.pax.Children > 0)
      );
      
      // Generate a unique ID for this section based on its contents and dayIndex
      const sectionSignature = `${section.restaurant}-${section.mealType}-${section.specificMeal}-${section.timeSlot}-${dayIndex}`;
      
      // Check if this section has already been saved
      const isSaved = savedSectionIds.includes(sectionSignature);
      
      // Return true if this section is complete and not yet saved
      return isComplete && !isSaved;
    });
    
    // If we found new complete sections, update Redux
    if (newCompleteSections.length > 0) {
      // Set processing flag to prevent multiple simultaneous executions
      isProcessingRef.current = true;
      
      // Get signatures for the new sections
      const newSectionSignatures = newCompleteSections.map(section => 
        `${section.restaurant}-${section.mealType}-${section.specificMeal}-${section.timeSlot}-${dayIndex}`
      );
      
      console.log('Restaurant - Auto dispatch triggered for enquiries:', {
        newCompleteSections: newCompleteSections.length,
        dayIndex: dayIndex,
        newSectionSignatures: newSectionSignatures,
        currentSavedIds: savedSectionIds
      });
      
      // Wait a bit to avoid too many Redux updates
      const timeoutId = setTimeout(() => {
        try {
          // Call handleBookNow
          handleBookNow();
          
          // Mark these sections as saved
          setSavedSectionIds(prev => [...prev, ...newSectionSignatures]);
        } catch (error) {
          console.error('Error in auto dispatch:', error);
        } finally {
          // Reset processing flag after a delay to allow for state updates
          setTimeout(() => {
            isProcessingRef.current = false;
          }, 1000);
        }
      }, 500);
      
      return () => {
        clearTimeout(timeoutId);
        isProcessingRef.current = false;
      };
    }
  }, [formSections, status, savedSectionIds, restaurantspack, dayIndex, tour]); // Added tour to dependencies

  // Helper to check if a booking is out of current tour dates for the specific dayIndex
  const isBookingOutOfTourDates = (booking) => {
    // Only validate if this booking belongs to the current dayIndex
    const bookingDayIndex = booking.originalData?.dayIndex || dayIndex;
    
    // If the booking doesn't belong to this dayIndex, don't validate
    if (bookingDayIndex !== dayIndex) {
      return false;
    }
    
    const bookingDate = booking.originalData?.bookingDate || booking.bookingDate;
    
    // Debug logging to check date formats
    console.log('Restaurant date validation debug:', {
      bookingId: booking.originalData?.id || 'new-booking',
      bookingDate: bookingDate,
      tourDates: tourDates,
      dayIndex: dayIndex,
      bookingDayIndex: bookingDayIndex
    });
    
    // Handle edge cases
    if (!bookingDate || !tourDates || tourDates.length === 0) {
      console.log('Missing bookingDate or tourDates, skipping validation');
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
    
    console.log('Restaurant date validation result:', {
      normalizedBookingDate: normalizedBookingDate,
      isDateValid: isDateValid,
      willShowError: !isDateValid
    });
    
    return !isDateValid;
  };

  const getSelectedRestaurant = (restaurantId) => {
    return restaurants.find(r => r.id === restaurantId) || null;
  };

  // if (status === 'failed') {
  //   return (
  //     <Container>
  //       <Typography variant="h6" sx={{ textAlign: 'center', my: 4, color: 'error.main' }}>
  //         Failed to load restaurants. Please try again.
  //       </Typography>
  //     </Container>
  //   );
  // }

  // if (!restaurants || restaurants.length === 0) {
  //   return (
  //     <Container>
  //       <Typography variant="h6" sx={{ textAlign: 'center', my: 4 }}>
  //         Please search for restaurants first
  //       </Typography>
  //     </Container>
  //   );
  // }

  const totalBookings = formSections.length;

  return (
    <Container maxWidth="xl" sx={{ mt: 3, mb: 4 }}>
      <Card
        elevation={3}
        sx={{
          mb: 1.5,
          borderRadius: 2,
          background: 'linear-gradient(135deg, #4caf50 0%, #388e3c 100%)',
          color: 'white',
          boxShadow: '0 4px 16px rgba(76, 175, 80, 0.3)',
        }}
      >
        <CardContent sx={{ 
          py: { xs: 1, sm: 0.8, md: 0.5 },
          px: { xs: 1.5, sm: 2, md: 2 },
          height: { xs: 'auto', sm: '52px' },
          minHeight: { xs: '60px', sm: '52px' }
        }}>
          <Box 
            display="flex" 
            alignItems="center" 
            justifyContent="space-between"
            flexDirection={{ xs: 'column', sm: 'row' }}
            gap={{ xs: 1, sm: 0 }}
          >
            <Box 
              display="flex" 
              alignItems="center"
              flexDirection={{ xs: 'column', sm: 'row' }}
              textAlign={{ xs: 'center', sm: 'left' }}
              gap={{ xs: 1, sm: 0 }}
            >
              <RestaurantIcon sx={{ 
                mr: { xs: 0, sm: 1.5 }, 
                mb: { xs: 0.5, sm: 0 },
                fontSize: { xs: 32, sm: 28 }, 
                color: '#FFD700' 
              }} />
              <Box>
                <Typography 
                  variant="h6" 
                  fontWeight="600" 
                  sx={{ 
                    color: 'white', 
                    fontSize: { xs: '0.85rem', sm: '0.9rem', md: '0.9rem' },
                    lineHeight: 1.2
                  }}
                >
                  Book Restaurant Services
                </Typography>
                <Typography 
                  variant="body2" 
                  sx={{ 
                    color: 'rgba(255, 255, 255, 0.8)', 
                    fontSize: { xs: '0.65rem', sm: '0.7rem', md: '0.7rem' },
                    lineHeight: 1.3,
                    display: { xs: 'none', sm: 'block' }
                  }}
                >
                  Select restaurants and configure your dining experience
                </Typography>
              </Box>
            </Box>
            <Chip 
              label={`${totalBookings} Booking${totalBookings !== 1 ? 's' : ''}`}
              sx={{ 
                bgcolor: 'rgba(255, 255, 255, 0.2)',
                color: 'white',
                fontWeight: 600,
                border: '1px solid rgba(255, 255, 255, 0.3)',
                fontSize: { xs: '0.7rem', sm: '0.75rem' },
                height: { xs: '28px', sm: '20px' },
                minWidth: { xs: '80px', sm: 'auto' },
                mt: { xs: 0.5, sm: 0 }
              }}
            />
          </Box>
        </CardContent>
      </Card>
      
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
              Restaurant enquiry information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Grid container spacing={1.5}>
        {formSections.map((section, sectionIndex) => {
          const selectedRestaurantDetails = getSelectedRestaurant(section.restaurant);
          const completionStatus = getSectionCompletion(section);
          const isExpanded = expandedSections.includes(sectionIndex);
          const outOfTourDates = isBookingOutOfTourDates(section);
          console.log("sectionIndex1451", section);
          return (
            <Grid item xs={12} key={sectionIndex}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 2,
                  border: outOfTourDates ? '1px solid #e53935' : `1px solid ${alpha('#4caf50', 0.2)}`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: outOfTourDates
                      ? `0 4px 12px ${alpha('#e53935', 0.15)}`
                      : `0 4px 12px ${alpha('#4caf50', 0.15)}`,
                    transform: 'translateY(-1px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 1.5,
                    bgcolor: alpha('#4caf50', 0.05),
                    borderBottom: `1px solid ${alpha('#4caf50', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                      <Chip 
                        label={`Booking ${sectionIndex + 1}`}
                        sx={{ 
                          bgcolor: '#4caf50',
                          color: 'white',
                          fontWeight: 600,
                          fontSize: '0.7rem',
                          height: '20px'
                        }}
                        size="small"
                      />
                      <Chip 
                        label={`${completionStatus}/4 Complete`}
                        color={completionStatus === 4 ? "success" : "warning"}
                        size="small"
                        variant="outlined"
                        sx={{ fontSize: '0.7rem', height: '20px' }}
                      />
                      {selectedRestaurantDetails && (
                        <Chip 
                          icon={<LocationOnIcon sx={{ fontSize: 14 }} />}
                          label={selectedRestaurantDetails.city}
                          size="small"
                          variant="outlined"
                          sx={{ 
                            borderColor: '#4caf50',
                            color: '#4caf50',
                            fontSize: '0.7rem',
                            height: '20px'
                          }}
                        />
                      )}
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <Tooltip title={isExpanded ? "Collapse" : "Expand"}>
                        <IconButton 
                          size="small" 
                          onClick={() => toggleSectionExpand(sectionIndex)}
                          sx={{ 
                            bgcolor: alpha('#4caf50', 0.1),
                            '&:hover': { bgcolor: alpha('#4caf50', 0.2) }
                          }}
                        >
                          <i className={`icon-chevron-${isExpanded ? 'up' : 'down'}`} />
                        </IconButton>
                      </Tooltip>
                      
                      {section.restaurant && (
                        <Button
                          variant="outlined"
                          size="medium"
                          onClick={() => handleOpenModal(sectionIndex)}
                          disabled={!section.restaurant}
                          startIcon={<VisibilityIcon />}
                          sx={{
                            borderRadius: 1.5,
                            px: 3,
                            py: 0.8,
                            fontSize: '0.8rem',
                            fontWeight: 600,
                            textTransform: 'none',
                            borderColor: '#4caf50',
                            color: '#4caf50',
                            '&:hover': {
                              borderColor: '#388e3c',
                              bgcolor: alpha('#4caf50', 0.05),
                              transform: 'translateY(-1px)',
                            },
                            transition: 'all 0.3s ease',
                          }}
                        >
                          View Summary
                        </Button>
                      )}
                                  
                      <Tooltip title="Remove Booking">
                        <IconButton 
                          size="small"
                          color="error" 
                          onClick={() => handleRemoveSection(sectionIndex)}
                          sx={{ 
                            bgcolor: alpha(theme.palette.error.main, 0.1),
                            '&:hover': { bgcolor: alpha(theme.palette.error.main, 0.2) }
                          }}
                        >
                          <DeleteIcon sx={{ fontSize: 16 }} />
                        </IconButton>
                      </Tooltip>
                    </Box>
                  </Box>

                  {/* Summary when collapsed */}
                  {!isExpanded && selectedRestaurantDetails && (
                    <Box sx={{ p: 1.5 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                        <Box 
                          component="img"
                          src={selectedRestaurantDetails.image || '/placeholder-restaurant.jpg'}
                          alt={selectedRestaurantDetails.restaurant_name}
                          sx={{ 
                            width: 50, 
                            height: 50, 
                            borderRadius: 1.5,
                            objectFit: 'cover',
                            border: `1px solid ${alpha('#4caf50', 0.2)}`
                          }}
                        />
                        <Box sx={{ flex: 1 }}>
                          <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 0.5, fontSize: '0.9rem' }}>
                            {selectedRestaurantDetails.restaurant_name}
                          </Typography>
                          <Box sx={{ display: 'flex', gap: 1.5, flexWrap: 'wrap' }}>
                            {section.pax.Adults + section.pax.Children > 0 && (
                              <Chip 
                                icon={<PeopleIcon sx={{ fontSize: 14 }} />}
                                label={`${section.pax.Adults + section.pax.Children} Pax`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#4caf50',
                                  color: '#4caf50',
                                  fontSize: '0.7rem',
                                  height: '20px'
                                }}
                              />
                            )}
                            {section.mealType && (
                              <Chip 
                                icon={<RestaurantMenuIcon sx={{ fontSize: 14 }} />}
                                label={section.mealType}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#4caf50',
                                  color: '#4caf50',
                                  fontSize: '0.7rem',
                                  height: '20px'
                                }}
                              />
                            )}
                            {section.timeSlot && (
                              <Chip 
                                icon={<AccessTimeIcon sx={{ fontSize: 14 }} />}
                                label={section.timeSlot}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#4caf50',
                                  color: '#4caf50',
                                  fontSize: '0.7rem',
                                  height: '20px'
                                }}
                              />
                            )}
                          </Box>
                        </Box>
                      </Box>
                    </Box>
                  )}

                  {/* Expanded Content */}
                  <Collapse in={isExpanded} timeout={300}>
                    <Paper 
                      elevation={0} 
                      sx={{ 
                        m: 1.5,
                        p: 0, 
                        borderRadius: 1.5,
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdropFilter: 'blur(10px)'
                      }}
                    >
                      <Grid container spacing={1.5} alignItems="flex-end">
                        {/* City Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <LocationOnIcon sx={{ mr: 0.8, color: '#1976d2', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!isCityEnabled ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                City
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '36px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                              <PortCity
                                onLocationSelect={handleCitySelect}
                                hasError={cityError}
                                setError={setCityError}
                                disabled={!isCityEnabled}
                              />
                            </Box>
                          </Box>
                        </Grid>
                        
                        {/* Restaurant Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <RestaurantIcon sx={{ mr: 0.8, color: '#4caf50', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600" 
                                color={!isRestaurantListingEnabled ? "text.disabled" : "text.primary"} 
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Restaurant 
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <RestaurantListing 
                                restaurants={restaurants} 
                                selectedRestaurant={section.restaurant}
                                selectedRestaurantName={section.restaurantName}
                                onRestaurantChange={(restaurantId) => handleFieldChange(sectionIndex, 'restaurant', restaurantId)}
                                disabled={!isRestaurantListingEnabled}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Guests Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <PeopleIcon sx={{ mr: 0.8, color: '#2e7d32', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.restaurant ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Guests
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <PaxSelector
                                selectedPax={section.pax}
                                onPaxChange={(value) => handlePaxChange(sectionIndex, value)}
                                initialAdults={section.pax?.Adults || searchParams?.adults || 1}
                                initialChildren={section.pax?.Children || searchParams?.children || 0}
                                disabled={!section.restaurant}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Meal Type Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <RestaurantMenuIcon sx={{ mr: 0.8, color: '#ff9800', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.restaurant ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Meal Type
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <MealTypeSelect
                                value={section.mealType}
                                onChange={(e) => handleFieldChange(sectionIndex, 'mealType', e.target.value)}
                                restaurantDetails={restaurantDetails}
                                disabled={!section.restaurant}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Specific Meal Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <DinnerDiningIcon sx={{ mr: 0.8, color: '#9c27b0', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.restaurant || !section.mealType ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Dish
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <SpecificMealSelect
                                value={section.specificMeal}
                                onChange={(e) => handleFieldChange(sectionIndex, 'specificMeal', e.target.value)}
                                selectedMealType={section.mealType}
                                restaurantDetails={restaurantDetails}
                                disabled={!section.restaurant || !section.mealType}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Time Slot Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                              <AccessTimeIcon sx={{ mr: 0.8, color: '#e91e63', fontSize: 18 }} />
                              <Typography 
                                variant="body2" 
                                fontWeight="600"
                                color={!section.restaurant || !section.mealType || !section.specificMeal ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Time Slot
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <TimeSlotSelect
                                value={section.timeSlot}
                                onChange={(e) => handleFieldChange(sectionIndex, 'timeSlot', e.target.value)}
                                selectedMealType={section.mealType}
                                restaurantDetails={selectedRestaurantDetails}
                                disabled={!section.restaurant || !section.mealType || !section.specificMeal}
                                bookingDate={section.bookingDate}
                                formSection={section}
                              />
                            </Box>
                          </Box>
                        </Grid>
                      </Grid>
                    </Paper>
                  </Collapse>

                  {/* Red alert if out of tour dates */}
                  {outOfTourDates && (
                    <Box sx={{ px: 1.5, pt: 0.5 }}>
                      <Alert severity="error" sx={{ borderRadius: 1.5, mb: 0.5 }}>
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
              borderRadius: 2,
              border: `1px dashed ${alpha('#4caf50', 0.4)}`,
              bgcolor: alpha('#4caf50', 0.02),
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              '&:hover': {
                bgcolor: alpha('#4caf50', 0.05),
                borderColor: '#4caf50',
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
                gap: 1.5
              }}>
                <AddIcon sx={{ fontSize: 28, color: '#4caf50' }} />
                <Typography variant="subtitle1" color="#4caf50" fontWeight={600} sx={{ fontSize: '0.9rem' }}>
                  Add More
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <RestaurantBookingSummaryModal
        open={openModal}
        onClose={handleCloseModal}
        bookingData={selectedSectionIndex !== null ? formSections[selectedSectionIndex] : null}
        bookingIndex={selectedSectionIndex}
        restaurantDetails={restaurantDetails}
      />
    </Container>
  );
} 