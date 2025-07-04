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
  const searchParams = useSelector((state) => state.restaurants.searchParams);
  const status = useSelector((state) => state.restaurants.status);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  console.log('Restaurant update', restaurantspack);
  
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
  
  // Initialize form sections with stable default values
  const defaultSection = useMemo(() => ({
    ...initialFormState,
    bookingDate: bookingDate, // Use the date from the specific itinerary day
    pax: {
      Adults: searchParams?.adults || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams?.adults, searchParams?.children, bookingDate]);
  
  const [formSections, setFormSections] = useState([{ ...defaultSection }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);

  // Update the current services ref when existingServices changes
  useEffect(() => {
    currentServicesRef.current = existingServices;
  }, [existingServices]);

  // Function to initialize form sections from restaurantspack data (following attraction pattern)
  const initializeFormSectionsFromRestaurantPack = useCallback(() => {
    if (!restaurantspack || !Array.isArray(restaurantspack) || restaurantspack.length === 0) {
      console.log('No restaurantspack data to initialize from');
      return;
    }

    console.log('Initializing form sections from restaurantspack:', restaurantspack);

    // Filter restaurants that match the current dayIndex for form sections
    const dayRestaurants = restaurantspack.filter(restaurantService => {
      const restaurantData = restaurantService.data?.[0];
      // Match by bookingDate since restaurantspack uses bookingDate
      return restaurantData && restaurantData.bookingDate === bookingDate;
    });

    if (dayRestaurants.length === 0) {
      console.log(`No restaurants found for bookingDate ${bookingDate}`);
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
        mealType: restaurantData.mealType,
        specificMeal: specificMealObject, // Use object format for modal compatibility
        timeSlot: restaurantData.visitTime || '',
        pax: {
          Adults: restaurantData.adultCount || 0,
          Children: restaurantData.childCount || 0
        },
        bookingDate: restaurantData.bookingDate || bookingDate,
        // Store the original data for reference
        originalData: restaurantData
      };
    });

    console.log('Initialized form sections for current day:', newFormSections);
    setFormSections(newFormSections);
    setExpandedSections(newFormSections.map((_, index) => index));
  }, [restaurantspack, dayIndex, bookingDate]);

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

    console.log('Dispatching ALL restaurants from restaurantspack to Redux:', restaurantspack);

    // Process ALL restaurants from restaurantspack, not just current day
    const allRestaurantsForRedux = restaurantspack.map(restaurantService => {
      const restaurantData = restaurantService.data[0];
      
      if (!restaurantData) {
        console.log('No restaurant data found in service:', restaurantService);
        return null;
      }

      console.log('Processing restaurant for Redux:', restaurantData);
      
      return restaurantData; // Use the restaurant data as-is since it already matches the format
    }).filter(Boolean); // Remove null entries

    if (allRestaurantsForRedux.length === 0) {
      console.log('No valid restaurants to dispatch to Redux');
      return;
    }

    // Remove any existing restaurant services using the ref
    const filteredServices = currentServicesRef.current.filter(service => service.type !== "restaurant");

    // Create new restaurant service entries for ALL restaurants
    const newRestaurantServices = allRestaurantsForRedux.map(restaurantData => ({
      type: "restaurant",
      agent_id: agentId,
      tour_id: tourId,
      bookingType: "enquiry",
      data: [restaurantData]
    }));

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
  }, [dayIndex]);

  // Cleanup effect (following attraction pattern)
  useEffect(() => {
    return () => {
      hasInitializedRef.current = false;
      lastDispatchRef.current = null;
      hasDispatchedAllRestaurantsRef.current = false;
      currentServicesRef.current = [];
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
    // Get the section being removed
    const sectionToRemove = formSections[indexToRemove];
    
    // Remove from local state
    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    
    // Update expanded sections
    setExpandedSections(expandedSections.filter(index => index !== indexToRemove).map(index => index > indexToRemove ? index - 1 : index));
    
    // Remove from Redux state if the section has an original restaurantId (following attraction pattern)
    if (sectionToRemove?.originalData?.restaurantId) {
      const currentServices = [...existingServices];
      const updatedServices = currentServices.filter(service => {
        if (service.type === "restaurant" && service.data && Array.isArray(service.data)) {
          return !service.data.some(data => data.restaurantId === sectionToRemove.originalData.restaurantId);
        }
        return true;
      });
      
      console.log('Removing restaurant from Redux state:', sectionToRemove.originalData.restaurantId);
      dispatch(setAllServices(updatedServices));
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
    console.log('handleInputChange called:', { sectionIndex, field, value });
    const newFormSections = [...formSections];
    
    if (field === 'restaurant') {
      newFormSections[sectionIndex] = {
        ...defaultSection,
        restaurant: value,
        bookingDate: newFormSections[sectionIndex].bookingDate,
        pax: newFormSections[sectionIndex].pax
      };
      setSelectedRestaurant(value);
    } else if (field === 'mealType') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        mealType: value,
        specificMeal: '',
        timeSlot: '',
        bookingDate: bookingDate // Preserve booking date
      };
    } else if (field === 'pax') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        pax: value,
        bookingDate: bookingDate // Preserve booking date
      };
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        bookingDate: bookingDate // Preserve booking date
      };
    }
    
    console.log('Updated form sections:', newFormSections);
    setFormSections(newFormSections);
    
    // Check if the current section is now complete
    const updatedSection = newFormSections[sectionIndex];
    const isComplete = 
      updatedSection.restaurant && 
      updatedSection.mealType && 
      updatedSection.specificMeal && 
      updatedSection.timeSlot && 
      (updatedSection.pax.Adults + updatedSection.pax.Children > 0);
    
    // Generate signature for this section
    const oldSectionSignature = formSections[sectionIndex] ? 
      `${formSections[sectionIndex].restaurant}-${formSections[sectionIndex].mealType}-${formSections[sectionIndex].specificMeal}-${formSections[sectionIndex].timeSlot}-${dayIndex}` : '';
    
    const newSectionSignature = 
      `${updatedSection.restaurant}-${updatedSection.mealType}-${updatedSection.specificMeal}-${updatedSection.timeSlot}-${dayIndex}`;
    
    // If the data changed, remove the old signature from saved list
    if (oldSectionSignature !== newSectionSignature) {
      setSavedSectionIds(prev => 
        prev.filter(signature => signature !== oldSectionSignature)
      );
      
      console.log(`Restaurant enquiry section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
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
    
    // Clone the existing services array, but only remove restaurant services for the current dayIndex
    // Preserve restaurant services for other dates
    const servicesWithoutRestaurants = existingServices.filter(service => {
      if (service.type !== "restaurant") {
        return true; // Keep non-restaurant services
      }
      
      // For restaurant services, check if they belong to the current dayIndex
      // If the service has data and any booking has the same dayIndex, remove it
      if (service.data && Array.isArray(service.data)) {
        const hasCurrentDayBooking = service.data.some(booking => 
          booking.dayIndex === dayIndex
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
      return {
        agent_id: agentId,
        bookingType: "enquiry",
        tour_id: tourId,
        type: "restaurant",
        data: [bookingData]
      };
    });
    
    // Combine non-restaurant services with new restaurant services
    const updatedServices = [...servicesWithoutRestaurants, ...restaurantServices];
    
    console.log("Restaurant - Dispatching updated enquiry services to Redux:", updatedServices);
    console.log("Restaurant - Services filtering check:", {
      totalExistingServices: existingServices.length,
      restaurantServicesRemoved: existingServices.filter(s => s.type === "restaurant").length - servicesWithoutRestaurants.filter(s => s.type === "restaurant").length,
      currentDayIndex: dayIndex,
      newRestaurantServices: restaurantServices.length,
      finalTotalServices: updatedServices.length
    });
    
    // Dispatch the updated services
    dispatch(setAllServices(updatedServices));
    
    setBookingSuccess(true);
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  }, [formSections, existingServices, validateBookings, dispatch, getBookingSummary, restaurants, searchParams, currentMode]);

  // Effect to automatically dispatch completed restaurant bookings to Redux
  useEffect(() => {
    // Skip if no form sections or during loading
    if (formSections.length === 0 || status === 'loading') return;
    
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
        // Call handleBookNow
        handleBookNow();
        
        // Mark these sections as saved
        setSavedSectionIds(prev => [...prev, ...newSectionSignatures]);
      }, 500);
      
      return () => clearTimeout(timeoutId);
    }
  }, [formSections, handleBookNow, status, savedSectionIds]);

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

  if (status === 'failed') {
    return (
      <Container>
        <Typography variant="h6" sx={{ textAlign: 'center', my: 4, color: 'error.main' }}>
          Failed to load restaurants. Please try again.
        </Typography>
      </Container>
    );
  }

  if (!restaurants || restaurants.length === 0) {
    return (
      <Container>
        <Typography variant="h6" sx={{ textAlign: 'center', my: 4 }}>
          Please search for restaurants first
        </Typography>
      </Container>
    );
  }

  const totalBookings = formSections.length;

  return (
    <Container maxWidth="xl" sx={{ mt: 4, mb: 6 }}>
      <Card
        elevation={4}
        sx={{
          mb: 3,
          borderRadius: 3,
          background: 'linear-gradient(135deg, #4caf50 0%, #388e3c 100%)',
          color: 'white',
          boxShadow: '0 8px 32px rgba(76, 175, 80, 0.3)',
        }}
      >
        <CardContent sx={{ py: 1}}>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <RestaurantIcon sx={{ mr: 2, fontSize: 32, color: '#FFD700' }} />
              <Box>
                <Typography variant="h5" fontWeight="600" sx={{ color: 'white' }}>
                  Book Restaurant Services
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)' }}>
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
                border: '1px solid rgba(255, 255, 255, 0.3)'
              }}
            />
          </Box>
        </CardContent>
      </Card>
      
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
              Restaurant enquiry information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Grid container spacing={2}>
        {formSections.map((section, sectionIndex) => {
          const selectedRestaurantDetails = getSelectedRestaurant(section.restaurant);
          const completionStatus = getSectionCompletion(section);
          const isExpanded = expandedSections.includes(sectionIndex);
          const outOfTourDates = isBookingOutOfTourDates(section);
          
          return (
            <Grid item xs={12} key={sectionIndex}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: outOfTourDates ? '2px solid #e53935' : `2px solid ${alpha('#4caf50', 0.2)}`,
                  background: outOfTourDates ? 'rgba(229,57,53,0.08)' : undefined,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: outOfTourDates
                      ? `0 8px 24px ${alpha('#e53935', 0.15)}`
                      : `0 8px 24px ${alpha('#4caf50', 0.15)}`,
                    transform: 'translateY(-2px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 2,
                    bgcolor: alpha('#4caf50', 0.05),
                    borderBottom: `1px solid ${alpha('#4caf50', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                      <Chip 
                        label={`Booking ${sectionIndex + 1}`}
                        sx={{ 
                          bgcolor: '#4caf50',
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
                      {selectedRestaurantDetails && (
                        <Chip 
                          icon={<LocationOnIcon sx={{ fontSize: 16 }} />}
                          label={selectedRestaurantDetails.city}
                          size="small"
                          variant="outlined"
                          sx={{ 
                            borderColor: '#4caf50',
                            color: '#4caf50'
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
                          size="large"
                          onClick={() => handleOpenModal(sectionIndex)}
                          disabled={!section.restaurant}
                          startIcon={<VisibilityIcon />}
                          sx={{
                            borderRadius: 2,
                            px: 4,
                            py: 1,
                            fontSize: '0.875rem',
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
                                  
                      {sectionIndex > 0 && (
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
                            <DeleteIcon sx={{ fontSize: 18 }} />
                          </IconButton>
                        </Tooltip>
                      )}
                    </Box>
                  </Box>

                  {/* Summary when collapsed */}
                  {!isExpanded && selectedRestaurantDetails && (
                    <Box sx={{ p: 2 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                        <Box 
                          component="img"
                          src={selectedRestaurantDetails.image || '/placeholder-restaurant.jpg'}
                          alt={selectedRestaurantDetails.restaurant_name}
                          sx={{ 
                            width: 60, 
                            height: 60, 
                            borderRadius: 2,
                            objectFit: 'cover',
                            border: `2px solid ${alpha('#4caf50', 0.2)}`
                          }}
                        />
                        <Box sx={{ flex: 1 }}>
                          <Typography variant="h6" fontWeight={600} sx={{ mb: 0.5 }}>
                            {selectedRestaurantDetails.restaurant_name}
                          </Typography>
                          <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap' }}>
                            {section.pax.Adults + section.pax.Children > 0 && (
                              <Chip 
                                icon={<PeopleIcon sx={{ fontSize: 16 }} />}
                                label={`${section.pax.Adults + section.pax.Children} Pax`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#4caf50',
                                  color: '#4caf50'
                                }}
                              />
                            )}
                            {section.mealType && (
                              <Chip 
                                icon={<RestaurantMenuIcon sx={{ fontSize: 16 }} />}
                                label={section.mealType}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#4caf50',
                                  color: '#4caf50'
                                }}
                              />
                            )}
                            {section.timeSlot && (
                              <Chip 
                                icon={<AccessTimeIcon sx={{ fontSize: 16 }} />}
                                label={section.timeSlot}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#4caf50',
                                  color: '#4caf50'
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
                        m: 2,
                        p: 0, 
                        borderRadius: 2,
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdropFilter: 'blur(10px)'
                      }}
                    >
                      <Grid container spacing={2} alignItems="flex-end">
                        {/* Restaurant Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <RestaurantIcon sx={{ mr: 1, color: '#4caf50', fontSize: 20 }} />
                              <Typography variant="subtitle2" fontWeight="600" color="text.primary">
                                Select Restaurant
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <RestaurantListing 
                                restaurants={restaurants} 
                                selectedRestaurant={section.restaurant}
                                onRestaurantChange={(restaurantId) => handleFieldChange(sectionIndex, 'restaurant', restaurantId)}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Guests Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <PeopleIcon sx={{ mr: 1, color: '#2e7d32', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.restaurant ? "text.disabled" : "text.primary"}
                              >
                                Select Guests
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <PaxSelector
                                selectedPax={section.pax}
                                onPaxChange={(value) => handlePaxChange(sectionIndex, value)}
                                initialAdults={searchParams?.adults || 1}
                                initialChildren={searchParams?.children || 0}
                                disabled={!section.restaurant}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Meal Type Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <RestaurantMenuIcon sx={{ mr: 1, color: '#ff9800', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.restaurant ? "text.disabled" : "text.primary"}
                              >
                                Meal Type
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
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
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <DinnerDiningIcon sx={{ mr: 1, color: '#9c27b0', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.restaurant || !section.mealType ? "text.disabled" : "text.primary"}
                              >
                                Select Dish
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
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
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <AccessTimeIcon sx={{ mr: 1, color: '#e91e63', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.restaurant || !section.mealType || !section.specificMeal ? "text.disabled" : "text.primary"}
                              >
                                Time Slot
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <TimeSlotSelect
                                value={section.timeSlot}
                                onChange={(e) => handleFieldChange(sectionIndex, 'timeSlot', e.target.value)}
                                selectedMealType={section.mealType}
                                restaurantDetails={restaurantDetails}
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
              border: `2px dashed ${alpha('#4caf50', 0.4)}`,
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
                gap: 2
              }}>
                <AddIcon sx={{ fontSize: 32, color: '#4caf50' }} />
                <Typography variant="h6" color="#4caf50" fontWeight={600}>
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