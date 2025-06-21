import React, { useState, useEffect, useCallback, useMemo } from 'react';
import {
  Typography,
  Container,
  Button,
  Box,
  Grid,
  Paper,
  Stack,
  IconButton,
  Tooltip,
  Alert,
} from '@mui/material';
import DeleteIcon from '@mui/icons-material/Delete';
import { useDispatch, useSelector } from 'react-redux';
import RestaurantListing from './RestaurantListing';
import MealTypeSelect from './MealTypeSelect';
import SpecificMealSelect from './SpecificMealSelect';
import TimeSlotSelect from './TimeSlotSelect';
import PaxSelector from './PaxSelector';
import RestaurantBookingSummaryModal from './RestaurantBookingSummaryModal';
import { setAllServices } from '../../../slice/tour-packages/tourPackageSlice';

export default function RestaurantComponent() {
  const dispatch = useDispatch();
  const [selectedRestaurant, setSelectedRestaurant] = useState(null);
  const restaurants = useSelector((state) => state.restaurants.restaurants);
  const restaurantDetails = useSelector((state) => state.restaurants.restaurantDetails);
  const searchParams = useSelector((state) => state.restaurants.searchParams);
  const status = useSelector((state) => state.restaurants.status);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  
  // State for validation and success messages
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  
  // Initialize form sections with stable default values
  const defaultSection = useMemo(() => ({
    restaurant: '',
    mealType: '',
    specificMeal: '',
    timeSlot: '',
    bookingDate: searchParams?.date || new Date().toISOString().split('T')[0],
    pax: {
      Adults: searchParams?.adults || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams?.adults, searchParams?.children, searchParams?.date]);
  
  // Debug logs
  useEffect(() => {
    if (process.env.NODE_ENV === 'development') {
      console.log('Restaurant Status:', status);
      console.log('Restaurants Data:', restaurants);
      console.log('Restaurant Details:', restaurantDetails);
      console.log('Search Params:', searchParams);
    }
  }, [status, restaurants, restaurantDetails, searchParams]);
  
  const [formSections, setFormSections] = useState([]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  
  // Initialize form with one section when component mounts or searchParams changes
  useEffect(() => {
    if (searchParams && restaurants?.length > 0 && formSections.length === 0) {
      setFormSections([{ ...defaultSection }]);
    }
  }, [searchParams, restaurants, defaultSection]);

  const handleAddMore = useCallback(() => {
    setFormSections(prev => [...prev, { ...defaultSection }]);
  }, [defaultSection]);

  const handleRemoveSection = useCallback((indexToRemove) => {
    setFormSections(prev => prev.filter((_, index) => index !== indexToRemove));
  }, []);

  const handleRestaurantChange = useCallback((restaurantId) => {
    setSelectedRestaurant(restaurantId);
    // Reset form sections when restaurant changes
    setFormSections(prev => prev.map((_, index) => {
      // If it's the first section, set the new restaurant ID but reset other fields
      if (index === 0) {
        return { 
          restaurant: restaurantId,
          mealType: '', // Reset meal type
          specificMeal: '', // Reset specific meal
          timeSlot: '', // Reset time slot
          bookingDate: searchParams?.date || new Date().toISOString().split('T')[0],
          pax: {
            Adults: searchParams?.adults || 1,
            Children: searchParams?.children || 0
          }
        };
      }
      // If there are multiple sections, remove them by returning only the first one
      return null;
    }).filter(Boolean));
  }, [searchParams]);

  // Handle field change with immediate feedback for time slot
  const handleFieldChange = useCallback((sectionIndex, field, value) => {
    setFormSections(prev => {
      // Don't update state if the value hasn't changed
      if (prev[sectionIndex] && prev[sectionIndex][field] === value) {
        return prev;
      }
      
      const newSections = [...prev];
      
      // Handle specific field changes
      if (field === 'mealType') {
        newSections[sectionIndex] = {
          ...newSections[sectionIndex],
          mealType: value,
          specificMeal: '', // Reset specific meal when meal type changes
          timeSlot: '', // Reset time slot when meal type changes
        };
      } else {
        newSections[sectionIndex] = {
          ...newSections[sectionIndex],
          [field]: value
        };
      }
      
      return newSections;
    });
  }, []);

  const handlePaxChange = useCallback((sectionIndex, value) => {
    setFormSections(prev => {
      // Don't update if the values haven't changed
      const currentPax = prev[sectionIndex]?.pax;
      if (currentPax && 
          currentPax.Adults === value.Adults && 
          currentPax.Children === value.Children) {
        return prev;
      }
      
      const newSections = [...prev];
      newSections[sectionIndex] = {
        ...newSections[sectionIndex],
        pax: value
      };
      return newSections;
    });
  }, []);

  const handleOpenModal = useCallback((index) => {
    setSelectedSectionIndex(index);
    setOpenModal(true);
  }, []);

  const handleCloseModal = useCallback(() => {
    setOpenModal(false);
    setSelectedSectionIndex(null);
  }, []);

  // Reset timeSlot and specificMeal when mealType changes or restaurant changes
  useEffect(() => {
    if (formSections.length > 0) {
      setFormSections(prev => prev.map(section => {
        if (section.mealType === '') {
          // If mealType is reset, also reset specificMeal and timeSlot
          return {
            ...section,
            specificMeal: '',
            timeSlot: ''
          };
        }
        return section;
      }));
    }
  }, [formSections.map(section => section.mealType).join(',')]);

  // Validate bookings before submission
  const validateBookings = useCallback((sections = formSections) => {
    // Check if there's at least one booking
    if (sections.length === 0) {
      setValidationError("Please add at least one restaurant booking.");
      return false;
    }
    
    // Validate each booking section
    for (let i = 0; i < sections.length; i++) {
      const section = sections[i];
      
      if (!section.restaurant) {
        setValidationError(`Booking #${i + 1}: Please select a restaurant.`);
        return false;
      }
      
      if (!section.mealType) {
        setValidationError(`Booking #${i + 1}: Please select a meal type.`);
        return false;
      }
      
      if (!section.specificMeal) {
        setValidationError(`Booking #${i + 1}: Please select a specific meal.`);
        return false;
      }
      
      if (!section.timeSlot) {
        setValidationError(`Booking #${i + 1}: Please select a time slot.`);
        return false;
      }
      
      // Check if at least one person is selected
      const totalPax = section.pax.Adults + section.pax.Children;
      if (totalPax <= 0) {
        setValidationError(`Booking #${i + 1}: Please select at least one person.`);
        return false;
      }
    }
    
    setValidationError(null);
    return true;
  }, []);

  // Function to handle booking creation
  const handleBookNow = useCallback((sectionsToBook = formSections, overrideSection = null) => {
    // If we have an override section, apply it to the form sections before validation
    let sectionsToValidate = sectionsToBook;
    
    if (overrideSection && overrideSection.sectionIndex !== undefined) {
      sectionsToValidate = [...sectionsToBook];
      sectionsToValidate[overrideSection.sectionIndex] = {
        ...sectionsToValidate[overrideSection.sectionIndex],
        timeSlot: overrideSection.timeSlot?.timeSlot || overrideSection.timeSlot,
        bookingDate: overrideSection.timeSlot?.bookingDate || sectionsToValidate[overrideSection.sectionIndex].bookingDate
      };
    }
    
    if (!validateBookings(sectionsToValidate)) {
      return;
    }
    
    // If there's only one section, send it directly without the bookings array
    if (sectionsToValidate.length === 1) {
      const section = sectionsToValidate[0];
      const restaurant = restaurants.find(r => r.id === section.restaurant) || {};
      
      const restaurantBookingData = {
        type: 'restaurant',
        id: `restaurant-${Date.now()}-0`,
        restaurantId: section.restaurant,
        restaurantName: restaurant.name || 'Restaurant',
        city: restaurant.city || searchParams?.location?.city || '',
        country: restaurant.country || searchParams?.location?.country || '',
        mealType: section.mealType,
        specificMeal: section.specificMeal,
        timeSlot: section.timeSlot,
        pax: section.pax,
        image: restaurant.image || '/placeholder-restaurant.jpg',
        mode: currentMode,
        cuisine: restaurant.cuisine_type || 'Not specified',
        price: restaurant.price_range || 'Not specified',
        bookingDate: section.bookingDate || searchParams?.date || new Date().toISOString().split('T')[0]
      };
      
      console.log("Restaurant booking data:", restaurantBookingData);
      
      // Dispatch action to store in Redux
      dispatch(setAllServices(restaurantBookingData));
    } else {
      // For multiple bookings, keep the array format for now
      const bookingsData = {
        type: 'restaurant',
        bookings: sectionsToValidate.map((section, index) => {
          const restaurant = restaurants.find(r => r.id === section.restaurant) || {};
          
          return {
            id: `restaurant-${Date.now()}-${index}`,
            restaurantId: section.restaurant,
            restaurantName: restaurant.name || 'Restaurant',
            city: restaurant.city || searchParams?.location?.city || '',
            country: restaurant.country || searchParams?.location?.country || '',
            mealType: section.mealType,
            specificMeal: section.specificMeal,
            timeSlot: section.timeSlot,
            pax: section.pax,
            image: restaurant.image || '/placeholder-restaurant.jpg',
            mode: currentMode,
            cuisine: restaurant.cuisine_type || 'Not specified',
            price: restaurant.price_range || 'Not specified',
            bookingDate: section.bookingDate || searchParams?.date || new Date().toISOString().split('T')[0]
          };
        })
      };
      
      console.log("Restaurant bookings data:", bookingsData);
      
      // Dispatch action to store in Redux
      dispatch(setAllServices(bookingsData));
    }
    
    setBookingSuccess(true);
    
    // Reset form after successful booking
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  }, [formSections, restaurants, validateBookings, dispatch, currentMode, searchParams]);

  // Handle time slot selection to trigger automatic booking
  const handleTimeSlotSelected = useCallback((sectionIndex, timeSlotValue) => {
    // Directly create an override section with the new time slot value
    // This ensures we don't rely on state updates which might not be completed yet
    const overrideSection = {
      sectionIndex,
      timeSlot: timeSlotValue
    };
    
    // Process booking with the override
    handleBookNow(formSections, overrideSection);
  }, [handleBookNow, formSections]);

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

  return (
    <Container maxWidth="xl">
      <Typography variant="h5" gutterBottom sx={{ mb: 4 }}>
        Restaurant Booking
      </Typography>
      
      {validationError && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {validationError}
        </Alert>
      )}
      
      {bookingSuccess && (
        <Alert severity="success" sx={{ mb: 2 }}>
          Restaurant booking information saved successfully!
        </Alert>
      )}
      
      <Stack spacing={4}>
        {formSections.map((section, sectionIndex) => (
          <Paper key={sectionIndex} elevation={2} sx={{ p: 3 }}>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
              <Typography variant="h6">Booking #{sectionIndex + 1}</Typography>
              {sectionIndex > 0 && (
                <IconButton 
                  color="error" 
                  onClick={() => handleRemoveSection(sectionIndex)}
                  size="small"
                >
                  <DeleteIcon />
                </IconButton>
              )}
            </Box>

            <Box sx={{ display: 'flex', gap: 2, flexWrap: 'nowrap' }}>
              <Box sx={{ flex: '0 0 25%' }}>
                <RestaurantListing 
                  restaurants={restaurants} 
                  selectedRestaurant={section.restaurant}
                  onRestaurantChange={(restaurantId) => {
                    handleFieldChange(sectionIndex, 'restaurant', restaurantId);
                  }}
                />
              </Box>

              <Tooltip 
                title={!section.restaurant ? "Please select a restaurant first" : ""}
                placement="top"
                arrow
              >
                <Box sx={{ 
                  flex: '1', 
                  display: 'flex', 
                  gap: 2,
                  opacity: section.restaurant ? 1 : 0.5,
                  pointerEvents: section.restaurant ? 'auto' : 'none',
                  cursor: section.restaurant ? 'auto' : 'not-allowed'
                }}>
                  <Box sx={{ flex: '1' }}>
                    <PaxSelector
                      selectedPax={section.pax}
                      onPaxChange={(value) => handlePaxChange(sectionIndex, value)}
                      initialAdults={searchParams?.adults || 1}
                      initialChildren={searchParams?.children || 0}
                      disabled={!section.restaurant}
                    />
                  </Box>

                  <Box sx={{ flex: '1' }}>
                    <MealTypeSelect
                      value={section.mealType}
                      onChange={(e) => handleFieldChange(sectionIndex, 'mealType', e.target.value)}
                      restaurantDetails={restaurantDetails}
                      disabled={!section.restaurant}
                    />
                  </Box>

                  <Box sx={{ flex: '1' }}>
                    <SpecificMealSelect
                      value={section.specificMeal}
                      onChange={(e) => handleFieldChange(sectionIndex, 'specificMeal', e.target.value)}
                      selectedMealType={section.mealType}
                      restaurantDetails={restaurantDetails}
                      disabled={!section.restaurant || !section.mealType}
                    />
                  </Box>

                  <Box sx={{ flex: '1' }}>
                    <TimeSlotSelect
                      value={section.timeSlot}
                      onChange={(e) => handleFieldChange(sectionIndex, 'timeSlot', e.target.value)}
                      selectedMealType={section.mealType}
                      restaurantDetails={restaurantDetails}
                      disabled={!section.restaurant || !section.mealType || !section.specificMeal}
                      onTimeSlotSelected={(timeSlotValue) => handleTimeSlotSelected(sectionIndex, timeSlotValue)}
                      bookingDate={section.bookingDate}
                      formSection={section}
                    />
                  </Box>
                </Box>
              </Tooltip>
            </Box>

            <Box sx={{ mt: 2, display: 'flex', gap: 2 }}>
              <Tooltip 
                title={
                  !section.restaurant ? "Please select a restaurant first" :
                  !section.mealType ? "Please select a meal type" :
                  !section.specificMeal ? "Please select a specific meal" :
                  !section.timeSlot ? "Please select a time slot" :
                  ""
                }
                placement="top"
                arrow
              >
                <span style={{ width: '100%' }}>
                  <Button
                    variant="outlined"
                    fullWidth
                    size="large"
                    onClick={() => handleOpenModal(sectionIndex)}
                    disabled={!section.restaurant || !section.mealType || !section.specificMeal || !section.timeSlot}
                    sx={{ height: 48 }}
                  >
                    View Summary
                  </Button>
                </span>
              </Tooltip>
            </Box>
          </Paper>
        ))}

        <Box sx={{ mt: 2 }}>
          <Button
            variant="contained"
            fullWidth
            size="large"
            onClick={handleAddMore}
            sx={{ height: 48 }}
          >
            Add More Booking
          </Button>
        </Box>
      </Stack>

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