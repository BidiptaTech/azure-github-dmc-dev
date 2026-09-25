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
  const tourStatus = useSelector((state) => state.tourPackages.tourStatus);
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

  // Refs for packageData hydrate + AllServices upsert (Attraction/Guide pattern)
  const hasInitializedRef = useRef(false);
  const lastDispatchRef = useRef(null);
  const hasDispatchedAllRestaurantsRef = useRef(false);
  const currentServicesRef = useRef([]);
  const isInitializingRef = useRef(false);
  const hasDataConflictsRef = useRef(false);
  const isProcessingRef = useRef(false);
  const lastFormSectionsRef = useRef([]);
  const getBookingSummaryRef = useRef(() => null);
  console.log("hasDataConflictsRefre", hasDataConflictsRef);

  // Initialize form sections with stable default values
  const defaultSection = useMemo(() => ({
    ...initialFormState,
    bookingDate: bookingDate,
    localId: `rest-new-${dayIndex}-0`,
    pax: {
      Adults: searchParams?.adults || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams?.adults, searchParams?.children, bookingDate, dayIndex]);
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

  // Hydrate form sections from restaurantspack for this day only
  const initializeFormSectionsFromRestaurantPack = useCallback(() => {
    if (!restaurantspack || !Array.isArray(restaurantspack) || restaurantspack.length === 0) {
      console.log('No restaurantspack data to initialize from');
      isInitializingRef.current = false;
      return;
    }

    console.log('Initializing form sections from restaurantspack:', restaurantspack);

    const hasDataConflicts = restaurantspack.some(restaurantService => {
      const restaurantData = restaurantService.data?.[0];
      if (!restaurantData) return false;

      const searchAdults = searchParams?.adults || 0;
      const searchChildren = searchParams?.children || 0;
      const restaurantAdults = Number(restaurantData.adultCount) || 0;
      const restaurantChildren = Number(restaurantData.childCount) || 0;
      const hasMismatch = (searchAdults !== restaurantAdults) || (searchChildren !== restaurantChildren);

      if (hasMismatch) {
        console.warn('Restaurant data mismatch detected but proceeding with initialization:', {
          searchForm: { adults: searchAdults, children: searchChildren },
          restaurantData: { adults: restaurantAdults, children: restaurantChildren },
          restaurantId: restaurantData.RestaurantId || restaurantData.restaurantId
        });
      }
      return hasMismatch;
    });

    hasDataConflictsRef.current = hasDataConflicts;
    isInitializingRef.current = true;

    // Filter restaurants - dayIndex===0 special case, else bookingDate match
    const dayRestaurants = restaurantspack.filter(restaurantService => {
      const restaurantData = restaurantService.data?.[0];
      if (!restaurantData) return false;

      if (dayIndex === 0) {
        const matchesCurrentDate = restaurantData.bookingDate === bookingDate;
        const notInTourDates = !tourDates.includes(restaurantData.bookingDate);
        return matchesCurrentDate || notInTourDates;
      }

      return restaurantData.bookingDate === bookingDate;
    });

    if (dayRestaurants.length === 0) {
      isInitializingRef.current = false;
      console.log(`No restaurants found for dayIndex ${dayIndex}${dayIndex === 0 ? ' (showing all dates)' : ` (bookingDate: ${bookingDate})`}`);
      return;
    }

    const newFormSections = dayRestaurants.map((restaurantService, index) => {
      const restaurantData = restaurantService.data[0];

      const resolvedDayIndex = typeof restaurantData.dayIndex === 'number'
        ? restaurantData.dayIndex
        : (Array.isArray(tourDates) && restaurantData.bookingDate
          ? Math.max(0, tourDates.indexOf(restaurantData.bookingDate))
          : dayIndex);
      const safeDayIndex = resolvedDayIndex === -1 ? dayIndex : resolvedDayIndex;

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
        specificMeal: specificMealObject,
        timeSlot: restaurantData.visitTime || '',
        pax: {
          Adults: restaurantData.adultCount || 0,
          Children: restaurantData.childCount || 0
        },
        bookingDate: restaurantData.bookingDate || bookingDate,
        localId: restaurantService.booking_id
          ? `bk-${restaurantService.booking_id}`
          : `rest-${safeDayIndex}-${restaurantData.restaurantId}-${restaurantData.visitTime || index}`,
        originalData: {
          ...restaurantData,
          booking_id: restaurantService.booking_id,
          dayIndex: safeDayIndex
        }
      };
    });

    console.log('Initialized form sections for current day:', newFormSections);
    setFormSections(newFormSections);
    setExpandedSections(newFormSections.map((_, index) => index));

    setTimeout(() => {
      isInitializingRef.current = false;
      console.log('Restaurant initialization completed, handleInputChange is now enabled');
    }, 100);
  }, [dayIndex, bookingDate, searchParams, restaurantspack, tourDates]);

  // Seed ONLY this day's package restaurants into AllServices (never wipe other days)
  const seedDayRestaurantsToRedux = useCallback((daySections) => {
    if (!daySections || daySections.length === 0) return;

    const currentServices = currentServicesRef.current || [];

    const servicesWithoutThisDay = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'restaurant') return true;
      if (!service.data || !Array.isArray(service.data)) return true;
      return !service.data.some((item) => {
        if (typeof item.dayIndex === 'number') return item.dayIndex === dayIndex;
        return item.bookingDate === bookingDate;
      });
    });

    const newRestaurantServices = daySections.map((section) => {
      const restaurantData = section.originalData || {};
      const resolvedDayIndex = typeof restaurantData.dayIndex === 'number' ? restaurantData.dayIndex : dayIndex;

      const processedRestaurantData = {
        ...restaurantData,
        id: section.localId,
        restaurantId: restaurantData.restaurantId,
        restaurantName: restaurantData.restaurantName,
        mealType: restaurantData.mealType || section.mealType,
        mealSpecificType: restaurantData.mealSpecificType ||
          (typeof section.specificMeal === 'object' ? section.specificMeal?.specificMealType : section.specificMeal),
        MealDescription: restaurantData.MealDescription ||
          (typeof section.specificMeal === 'object' ? section.specificMeal?.items : []) || [],
        visitTime: restaurantData.visitTime || section.timeSlot || '',
        adultCount: Number(section.pax?.Adults ?? restaurantData.adultCount) || 0,
        childCount: Number(section.pax?.Children ?? restaurantData.childCount) || 0,
        totalPrice: Number(restaurantData.totalPrice) || 0,
        mealPrice: Number(restaurantData.mealPrice ?? restaurantData.totalPrice) || 0,
        bookingDate: section.bookingDate || restaurantData.bookingDate || bookingDate,
        dayIndex: resolvedDayIndex,
        city: restaurantData.city || section.city || '',
        country: restaurantData.country || section.country || '',
        image: restaurantData.image || section.image || '',
        dmc_id: restaurantData.dmc_id,
        booking_id: restaurantData.booking_id,
        bookingType: restaurantData.bookingType || 'enquiry'
      };

      const serviceObject = {
        type: 'restaurant',
        agent_id: agentId,
        tour_id: tourId,
        data: [processedRestaurantData],
        bookingType: 'enquiry'
      };

      if (restaurantData.booking_id) {
        serviceObject.booking_id = restaurantData.booking_id;
      }

      return serviceObject;
    });

    dispatch(setAllServices([...servicesWithoutThisDay, ...newRestaurantServices]));
    hasDispatchedAllRestaurantsRef.current = true;
  }, [agentId, tourId, dispatch, dayIndex, bookingDate]);

  // Reset init flags when dayIndex changes (do NOT clear currentServicesRef)
  useEffect(() => {
    hasInitializedRef.current = false;
    lastDispatchRef.current = null;
    hasDispatchedAllRestaurantsRef.current = false;
    isInitializingRef.current = false;
    hasDataConflictsRef.current = false;
  }, [dayIndex]);

  // Cleanup effect
  useEffect(() => {
    return () => {
      hasInitializedRef.current = false;
      lastDispatchRef.current = null;
      hasDispatchedAllRestaurantsRef.current = false;
      isInitializingRef.current = false;
      hasDataConflictsRef.current = false;
    };
  }, []);

  // Initialize form sections once from restaurantspack
  useEffect(() => {
    if (hasInitializedRef.current) return;
    if (!restaurantspack || !Array.isArray(restaurantspack) || restaurantspack.length === 0) return;

    initializeFormSectionsFromRestaurantPack();
    hasInitializedRef.current = true;
  }, [restaurantspack, initializeFormSectionsFromRestaurantPack]);

  // After form hydrate from pack, seed this day's restaurants into AllServices once
  useEffect(() => {
    if (!hasInitializedRef.current || hasDispatchedAllRestaurantsRef.current) return;
    if (!restaurantspack || restaurantspack.length === 0) return;
    if (!formSections.some((s) => s.originalData)) return;

    seedDayRestaurantsToRedux(formSections.filter((s) => s.originalData));
  }, [formSections, restaurantspack, seedDayRestaurantsToRedux]);

  // Log props received from parent component
  useEffect(() => {
    console.log('RestaurantComponent - Received props:', { date, dayIndex, bookingDate, restaurantspack });
    console.log('RestaurantComponent - Form sections count:', formSections.length);
    console.log('RestaurantComponent - Has initialized:', hasInitializedRef.current);
  }, [date, dayIndex, bookingDate, formSections, restaurantspack]);

  // Check if existing restaurant bookings exist in Redux and load them
  useEffect(() => {
    if (existingServices && existingServices.length > 0) {
      const existingRestaurantServices = existingServices.filter(service =>
        service.type === "restaurant" && service.data && Array.isArray(service.data)
      );
      const allRestaurants = existingRestaurantServices.flatMap(service => service.data);
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
      localId: `rest-new-${dayIndex}-${Date.now()}-${newIndex}`,
      originalData: null
    };
    setFormSections([...formSections, newSection]);
    setExpandedSections([...expandedSections, newIndex]);
  };

  const handleRemoveSection = (indexToRemove) => {
    const sectionToRemove = formSections[indexToRemove];
    if (!sectionToRemove) return;

    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    setExpandedSections(
      expandedSections
        .filter((index) => index !== indexToRemove)
        .map((index) => (index > indexToRemove ? index - 1 : index))
    );

    const sectionKey = sectionToRemove.localId
      || (sectionToRemove.originalData?.booking_id ? `bk-${sectionToRemove.originalData.booking_id}` : null);
    const hasIdentity = sectionKey || sectionToRemove.restaurant || sectionToRemove.originalData?.booking_id;
    if (!hasIdentity) return;

    const currentServices = [...(currentServicesRef.current || [])];
    const filteredServices = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'restaurant') return true;

      if (sectionToRemove.originalData?.booking_id && service.booking_id === sectionToRemove.originalData.booking_id) {
        return false;
      }

      if (service.data && Array.isArray(service.data)) {
        const matches = service.data.some((item) =>
          (sectionKey && item.id === sectionKey) ||
          (sectionToRemove.restaurant &&
            (item.dayIndex === dayIndex || item.bookingDate === (sectionToRemove.bookingDate || bookingDate)) &&
            String(item.restaurantId) === String(sectionToRemove.restaurant) &&
            String(item.visitTime || '') === String(sectionToRemove.timeSlot || '') &&
            String(item.mealType || '') === String(sectionToRemove.mealType || ''))
        );
        if (matches) return false;
      }
      return true;
    });

    if (filteredServices.length !== currentServices.length) {
      dispatch(setAllServices(filteredServices));
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
    if (isInitializingRef.current) return;

    // Lock restaurant identity for packageData bookings
    if (field === 'restaurant' && formSections[sectionIndex]?.originalData) {
      return;
    }

    const newFormSections = [...formSections];

    if (field === 'restaurant') {
      const selectedRestaurantDetails = restaurants.find(r => r.id === value);
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        restaurant: value,
        restaurantName: selectedRestaurantDetails?.name || selectedRestaurantDetails?.restaurant_name || '',
        city: selectedRestaurantDetails?.city || '',
        country: selectedRestaurantDetails?.country || '',
        image: selectedRestaurantDetails?.master_image || selectedRestaurantDetails?.additional_images?.[0] || '',
        mealType: '',
        specificMeal: '',
        timeSlot: ''
      };
      setSelectedRestaurant(value);
      setFormSections(newFormSections);
      return;
    }

    if (field === 'mealType') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        mealType: value,
        specificMeal: '',
        timeSlot: '',
        bookingDate: bookingDate
      };
      setFormSections(newFormSections);
      return;
    }

    if (field === 'specificMeal' && value === '') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        specificMeal: '',
        timeSlot: '',
        bookingDate: bookingDate
      };
      setFormSections(newFormSections);
      return;
    }

    if (field === 'pax') {
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

        const updatedSection = newFormSections[sectionIndex];
        const isComplete =
          updatedSection.restaurant &&
          updatedSection.mealType &&
          updatedSection.specificMeal &&
          updatedSection.timeSlot &&
          (updatedSection.pax.Adults + updatedSection.pax.Children > 0);

        if (isComplete) {
          dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
        }
      }
      return;
    }

    newFormSections[sectionIndex] = {
      ...newFormSections[sectionIndex],
      [field]: value,
      bookingDate: bookingDate
    };
    setFormSections(newFormSections);

    const updatedSection = newFormSections[sectionIndex];
    const isComplete =
      updatedSection.restaurant &&
      updatedSection.mealType &&
      updatedSection.specificMeal &&
      updatedSection.timeSlot &&
      (updatedSection.pax.Adults + updatedSection.pax.Children > 0);

    if (isComplete) {
      dispatchBookingUpdateToRedux(sectionIndex, updatedSection);
    }
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
      
      const resolvedCountry =
        (typeof country === "string" && country) ||
        country?.name ||
        country?.label ||
        tour?.destination ||
        tour?.country ||
        city?.country ||
        "";

      dispatch(fetchRestaurants({ 
        city: city.address || city.name,
        country: resolvedCountry,
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
    // Pack / edit hydrate: prefer live form values, fall back to originalData
    if (booking.originalData) {
      return {
        restaurant: booking.originalData,
        restaurantName: booking.restaurantName || booking.originalData.restaurantName,
        city: booking.city || booking.originalData.city || searchParams?.location?.city || '',
        country: booking.country || booking.originalData.country || searchParams?.location?.country || '',
        mealType: booking.mealType || booking.originalData.mealType,
        specificMeal: booking.specificMeal || {
          specificMealType: booking.originalData.mealSpecificType,
          totalPrice: booking.originalData.totalPrice || 0,
          items: booking.originalData.MealDescription || []
        },
        timeSlot: booking.timeSlot || booking.originalData.visitTime || '',
        pax: booking.pax || {
          Adults: Number(booking.originalData.adultCount) || 0,
          Children: Number(booking.originalData.childCount) || 0
        },
        mode: currentMode,
        image: booking.image || booking.originalData.image || '/placeholder-restaurant.jpg',
        cuisine: booking.originalData.cuisine_type || 'Not specified',
        bookingDate: booking.bookingDate || booking.originalData.bookingDate,
        booking_id: booking.originalData.booking_id
      };
    }

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

  getBookingSummaryRef.current = getBookingSummary;

  // Upsert one complete section into AllServices (always write live form values)
  const dispatchBookingUpdateToRedux = useCallback((sectionIndex, updatedSection) => {
    if (!updatedSection.restaurant || !updatedSection.mealType || !updatedSection.specificMeal || !updatedSection.timeSlot) {
      return;
    }
    if ((updatedSection.pax?.Adults || 0) + (updatedSection.pax?.Children || 0) <= 0) {
      return;
    }

    const currentServices = [...(currentServicesRef.current || [])];
    const sectionKey = updatedSection.localId
      || (updatedSection.originalData?.booking_id ? `bk-${updatedSection.originalData.booking_id}` : null)
      || `rest-${dayIndex}-${updatedSection.restaurant}-${updatedSection.mealType}-${updatedSection.timeSlot}`;

    const adultCount = updatedSection.pax?.Adults || 0;
    const childCount = updatedSection.pax?.Children || 0;

    let mealSpecificType = typeof updatedSection.specificMeal === 'object'
      ? updatedSection.specificMeal.specificMealType
      : updatedSection.specificMeal;
    let totalPrice = 0;
    let mealPrice = 0;
    let mealDescriptionArray = [];

    if (updatedSection.specificMeal && typeof updatedSection.specificMeal === 'object') {
      totalPrice = Number(updatedSection.specificMeal.totalPrice) || 0;
      mealPrice = totalPrice;
      if (Array.isArray(updatedSection.specificMeal.items)) {
        mealDescriptionArray = updatedSection.specificMeal.items.map(item => ({
          item_name: item.name || item.item_name || "Meal Item",
          name: item.name || item.item_name || "Meal Item",
          price: item.price || 0,
          meal_id: item.meal_id || updatedSection.restaurant || 0,
          category: updatedSection.mealType || "Meal",
          item_type: "Standard",
          quantity: item.quantity || 1
        }));
      }
    }

    let bookingData;

    if (updatedSection.originalData) {
      // Lock restaurant identity from originalData; apply live form for editable fields
      if (!totalPrice && updatedSection.originalData.totalPrice) {
        totalPrice = Number(updatedSection.originalData.totalPrice) || 0;
        mealPrice = Number(updatedSection.originalData.mealPrice ?? updatedSection.originalData.totalPrice) || 0;
      }
      if (mealDescriptionArray.length === 0) {
        mealDescriptionArray = updatedSection.originalData.MealDescription || [];
      }
      if (!mealSpecificType) {
        mealSpecificType = updatedSection.originalData.mealSpecificType;
      }

      bookingData = {
        ...updatedSection.originalData,
        fullName: updatedSection.originalData.fullName || "",
        email: updatedSection.originalData.email || "",
        phone: updatedSection.originalData.phone || "",
        countryCode: updatedSection.originalData.countryCode || "",
        address1: updatedSection.originalData.address1 || "",
        address2: updatedSection.originalData.address2 || "",
        state: updatedSection.originalData.state || "",
        zip: updatedSection.originalData.zip || "",
        specialRequests: updatedSection.originalData.specialRequests || "",
        id: sectionKey,
        restaurantId: updatedSection.originalData.restaurantId,
        restaurantName: updatedSection.originalData.restaurantName,
        city: updatedSection.originalData.city,
        country: updatedSection.originalData.country,
        image: updatedSection.originalData.image,
        dmc_id: updatedSection.originalData.dmc_id,
        visitTime: updatedSection.timeSlot,
        mealType: updatedSection.mealType,
        mealSpecificType,
        MealDescription: mealDescriptionArray,
        adultCount,
        childCount,
        totalPrice: totalPrice || Number(updatedSection.originalData.totalPrice) || 0,
        mealPrice: mealPrice || Number(updatedSection.originalData.mealPrice ?? updatedSection.originalData.totalPrice) || 0,
        bookingDate: updatedSection.bookingDate || bookingDate,
        dayIndex: dayIndex,
        bookingType: updatedSection.originalData.bookingType || "enquiry",
        booking_id: updatedSection.originalData.booking_id,
        transport: updatedSection.originalData.transport,
        transportPrice: updatedSection.originalData.transportPrice,
        priceTypes: updatedSection.originalData.priceTypes
      };
    } else {
      const summaryData = getBookingSummaryRef.current(updatedSection) || {};
      const restaurant = restaurants.find(r => r.id === updatedSection.restaurant) || {};

      if (!totalPrice) {
        const basePrice = 50;
        totalPrice = (adultCount + childCount) * basePrice;
        mealPrice = totalPrice;
        mealDescriptionArray = [{
          item_name: mealSpecificType || "Meal",
          name: mealSpecificType || "Meal",
          price: basePrice,
          meal_id: restaurant.id || 0,
          category: updatedSection.mealType || "Meal",
          item_type: "Standard",
          quantity: adultCount + childCount
        }];
      }

      bookingData = {
        fullName: "",
        email: "",
        phone: "",
        countryCode: "",
        address1: "",
        address2: "",
        state: "",
        zip: "",
        specialRequests: "",
        id: sectionKey,
        bookingDate: updatedSection.bookingDate || bookingDate,
        visitTime: updatedSection.timeSlot,
        adultCount,
        childCount,
        restaurantId: updatedSection.restaurant,
        restaurantName: restaurant.restaurant_name || summaryData.restaurantName || 'Restaurant',
        mealType: updatedSection.mealType,
        mealSpecificType,
        MealDescription: mealDescriptionArray,
        totalPrice,
        mealPrice,
        transport: null,
        transportPrice: 0,
        priceTypes: ["dmc"],
        dmc_id: restaurant.dmc_id || null,
        city: restaurant.city || summaryData.city || '',
        country: restaurant.country || summaryData.country || '',
        image: restaurant.image || summaryData.image || '',
        dayIndex: dayIndex,
        bookingType: "enquiry"
      };
    }

    const filteredServices = currentServices.filter((service) => {
      const type = String(service.type || '').toLowerCase();
      if (type !== 'restaurant') return true;

      if (updatedSection.originalData?.booking_id && service.booking_id === updatedSection.originalData.booking_id) {
        return false;
      }

      if (service.data && Array.isArray(service.data)) {
        const matches = service.data.some((item) =>
          item.id === sectionKey ||
          ((item.dayIndex === dayIndex || item.bookingDate === (bookingData.bookingDate || bookingDate)) &&
            String(item.restaurantId) === String(bookingData.restaurantId) &&
            String(item.visitTime || '') === String(bookingData.visitTime || '') &&
            String(item.mealType || '') === String(bookingData.mealType || ''))
        );
        if (matches) return false;
      }
      return true;
    });

    const newRestaurantService = {
      type: 'restaurant',
      agent_id: agentId,
      tour_id: tourId,
      data: [bookingData],
      bookingType: 'enquiry'
    };
    if (updatedSection.originalData?.booking_id) {
      newRestaurantService.booking_id = updatedSection.originalData.booking_id;
    }

    dispatch(setAllServices([...filteredServices, newRestaurantService]));
  }, [restaurants, agentId, tourId, dayIndex, bookingDate, dispatch]);

  // Validate bookings before submission
  const validateBookings = useCallback(() => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one restaurant enquiry.");
      return false;
    }

    const completeSections = formSections.filter(section =>
      section.restaurant &&
      section.mealType &&
      section.specificMeal &&
      section.timeSlot &&
      (section.pax.Adults + section.pax.Children > 0)
    );

    if (completeSections.length === 0) {
      return false;
    }

    setValidationError(null);
    return true;
  }, [formSections, setValidationError]);

  // Sync complete sections to AllServices on form changes (create + edit)
  useEffect(() => {
    if (formSections.length === 0 || isProcessingRef.current || isInitializingRef.current) return;

    const currentFormSectionsString = JSON.stringify(formSections.map(s => ({
      restaurant: s.restaurant,
      mealType: s.mealType,
      specificMeal: s.specificMeal,
      timeSlot: s.timeSlot,
      pax: s.pax,
      localId: s.localId
    })));
    const lastFormSectionsString = JSON.stringify(lastFormSectionsRef.current);

    if (currentFormSectionsString === lastFormSectionsString) {
      return;
    }

    lastFormSectionsRef.current = formSections.map(s => ({
      restaurant: s.restaurant,
      mealType: s.mealType,
      specificMeal: s.specificMeal,
      timeSlot: s.timeSlot,
      pax: s.pax,
      localId: s.localId
    }));

    formSections.forEach((section, index) => {
      const isComplete = (
        section.restaurant &&
        section.mealType &&
        section.specificMeal &&
        section.timeSlot &&
        ((section.pax?.Adults || 0) + (section.pax?.Children || 0) > 0)
      );
      if (!isComplete) return;
      if (!section.originalData && (!restaurants || restaurants.length === 0)) return;

      dispatchBookingUpdateToRedux(index, section);
    });
  }, [formSections, restaurants, dayIndex, dispatchBookingUpdateToRedux]);

  // Helper to check if a booking is out of current tour dates for the specific dayIndex
  const isBookingOutOfTourDates = (booking) => {
    const bookingDayIndex = booking.originalData?.dayIndex || dayIndex;

    if (bookingDayIndex !== dayIndex) {
      return false;
    }

    const bookingDateValue = booking.originalData?.bookingDate || booking.bookingDate;

    if (!bookingDateValue || !tourDates || tourDates.length === 0) {
      return false;
    }

    let normalizedBookingDate;
    try {
      if (typeof bookingDateValue === 'string') {
        if (/^\d{4}-\d{2}-\d{2}$/.test(bookingDateValue)) {
          normalizedBookingDate = bookingDateValue;
        } else {
          normalizedBookingDate = new Date(bookingDateValue).toISOString().split('T')[0];
        }
      } else {
        normalizedBookingDate = new Date(bookingDateValue).toISOString().split('T')[0];
      }
    } catch (error) {
      console.error('Error normalizing booking date:', error);
      return false;
    }

    return !tourDates.includes(normalizedBookingDate);
  };

  const getSelectedRestaurant = (restaurantId, section) => {
    if (section?.originalData) {
      return {
        id: section.originalData.restaurantId || restaurantId,
        restaurant_name: section.originalData.restaurantName || section.restaurantName,
        city: section.originalData.city || section.city,
        country: section.originalData.country || section.country,
        image: section.originalData.image || section.image || '/placeholder-restaurant.jpg'
      };
    }
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
          const selectedRestaurantDetails = getSelectedRestaurant(section.restaurant, section);
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

                      {(tourStatus !== "Confirmed" && tourStatus !== "Definite" && tourStatus !== "Actual") && (
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
                      )}
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
                                color={section.originalData || !isCityEnabled ? "text.disabled" : "text.primary"}
                                sx={{ fontSize: '0.8rem' }}
                              >
                                City
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '36px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                              {section.originalData ? (
                                <Typography variant="body2" color="text.secondary" sx={{ px: 1 }}>
                                  {section.originalData.city || section.city || 'Package restaurant'}
                                </Typography>
                              ) : (
                                <PortCity
                                  onLocationSelect={handleCitySelect}
                                  hasError={cityError}
                                  setError={setCityError}
                                  disabled={!isCityEnabled}
                                />
                              )}
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
                                color={section.originalData || !isRestaurantListingEnabled ? "text.disabled" : "text.primary"} 
                                sx={{ fontSize: '0.8rem' }}
                              >
                                Select Restaurant 
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '42px', display: 'flex', alignItems: 'center' }}>
                              <RestaurantListing 
                                restaurants={restaurants} 
                                selectedRestaurant={section.restaurant}
                                selectedRestaurantName={section.originalData?.restaurantName || section.restaurantName}
                                onRestaurantChange={(restaurantId) => handleFieldChange(sectionIndex, 'restaurant', restaurantId)}
                                disabled={!!section.originalData || !isRestaurantListingEnabled}
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