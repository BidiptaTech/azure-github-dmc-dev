import React, { useState, useEffect, useCallback, useMemo } from 'react';
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

export default function RestaurantComponent() {
  const theme = useTheme();
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
  const [expandedSections, setExpandedSections] = useState([0]);
  
  // Initialize form sections with stable default values
  const defaultSection = useMemo(() => ({
    ...initialFormState,
    bookingDate: searchParams?.date || new Date().toISOString().split('T')[0],
    pax: {
      Adults: searchParams?.adults || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams?.adults, searchParams?.children, searchParams?.date]);
  
  const [formSections, setFormSections] = useState([{ ...defaultSection }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  
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
    setFormSections([...formSections, { ...defaultSection }]);
    setExpandedSections([...expandedSections, newIndex]);
  };

  const handleRemoveSection = (indexToRemove) => {
    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    setExpandedSections(expandedSections.filter(index => index !== indexToRemove).map(index => index > indexToRemove ? index - 1 : index));
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
        timeSlot: ''
      };
    } else if (field === 'pax') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        pax: value
      };
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value
      };
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

  // Calculate completion status for each section
  const getSectionCompletion = (section) => {
    let completed = 0;
    if (section.restaurant) completed++;
    if (section.mealType) completed++;
    if (section.specificMeal) completed++;
    if (section.timeSlot) completed++;
    return completed;
  };

  // Validate bookings before submission
  const validateBookings = () => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one restaurant booking.");
      return false;
    }
    
    for (let i = 0; i < formSections.length; i++) {
      const section = formSections[i];
      
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
      
      const totalPax = section.pax.Adults + section.pax.Children;
      if (totalPax <= 0) {
        setValidationError(`Booking #${i + 1}: Please select at least one person.`);
        return false;
      }
    }
    
    setValidationError(null);
    return true;
  };

  // Function to handle booking creation
  const handleBookNow = () => {
    if (!validateBookings()) {
      return;
    }

    if (formSections.length === 1) {
      const section = formSections[0];
      const restaurant = restaurants.find(r => r.id === section.restaurant) || {};
      
      const restaurantBookingData = {
        type: 'restaurant',
        id: `restaurant-${Date.now()}-0`,
        restaurantId: section.restaurant,
        restaurantName: restaurant.restaurant_name || 'Restaurant',
        city: restaurant.city || searchParams?.location?.city || '',
        country: restaurant.country || searchParams?.location?.country || '',
        mealType: section.mealType,
        specificMeal: section.specificMeal,
        timeSlot: section.timeSlot,
        pax: section.pax,
        image: restaurant.image || '/placeholder-restaurant.jpg',
        mode: currentMode,
        cuisine: restaurant.cuisine_type || 'Not specified',
        bookingDate: section.bookingDate || searchParams?.date || new Date().toISOString().split('T')[0]
      };
      
      dispatch(setAllServices(restaurantBookingData));
    } else {
      const bookingsData = {
        type: 'restaurant',
        bookings: formSections.map((section, index) => {
          const restaurant = restaurants.find(r => r.id === section.restaurant) || {};
          
          return {
            id: `restaurant-${Date.now()}-${index}`,
            restaurantId: section.restaurant,
            restaurantName: restaurant.restaurant_name || 'Restaurant',
            city: restaurant.city || searchParams?.location?.city || '',
            country: restaurant.country || searchParams?.location?.country || '',
            mealType: section.mealType,
            specificMeal: section.specificMeal,
            timeSlot: section.timeSlot,
            pax: section.pax,
            image: restaurant.image || '/placeholder-restaurant.jpg',
            mode: currentMode,
            cuisine: restaurant.cuisine_type || 'Not specified',
            bookingDate: section.bookingDate || searchParams?.date || new Date().toISOString().split('T')[0]
          };
        })
      };
      
      dispatch(setAllServices(bookingsData));
    }
    
    setBookingSuccess(true);
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
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

  const getSelectedRestaurant = (restaurantId) => {
    return restaurants.find(r => r.id === restaurantId) || null;
  };

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
              Restaurant booking information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Grid container spacing={2}>
        {formSections.map((section, sectionIndex) => {
          const selectedRestaurantDetails = getSelectedRestaurant(section.restaurant);
          const completionStatus = getSectionCompletion(section);
          const isExpanded = expandedSections.includes(sectionIndex);
          
          return (
            <Grid item xs={12} key={sectionIndex}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: `2px solid ${alpha('#4caf50', 0.2)}`,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: `0 8px 24px ${alpha('#4caf50', 0.15)}`,
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