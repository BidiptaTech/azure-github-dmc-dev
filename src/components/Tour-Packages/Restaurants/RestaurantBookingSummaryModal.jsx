import React, { useEffect } from 'react';
import {
  Typography,
  Box,
  Modal,
  Paper,
  Stack,
  Divider,
  IconButton,
  Card,
  CardContent,
  CardMedia,
  Grid,
  Button,
  styled,
  Chip
} from '@mui/material';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import FastfoodIcon from '@mui/icons-material/Fastfood';
import CloseIcon from '@mui/icons-material/Close';
import GroupIcon from '@mui/icons-material/Group';
import LocalDiningIcon from '@mui/icons-material/LocalDining';
import ScheduleIcon from '@mui/icons-material/Schedule';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import { useSelector } from 'react-redux';

// Styled components
const StyledModal = styled(Modal)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
}));

const ModalContent = styled(Paper)(({ theme }) => ({
  position: 'relative',
  width: '90%',
  maxWidth: 800,
  maxHeight: '90vh',
  overflow: 'auto',
  padding: theme.spacing(3),
  backgroundColor: theme.palette.background.paper,
  borderRadius: theme.spacing(2),
  boxShadow: theme.shadows[5],
}));

const SummarySection = styled(Box)(({ theme }) => ({
  marginBottom: theme.spacing(3),
}));

const DetailRow = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  gap: theme.spacing(1),
  marginBottom: theme.spacing(1),
}));

const InfoCard = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(2),
  height: '100%',
  display: 'flex',
  flexDirection: 'column',
  gap: theme.spacing(1)
}));

const PriceCard = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(2),
  backgroundColor: '#4caf50',
  color: 'white',
  height: '100%',
  display: 'flex',
  flexDirection: 'column',
  gap: theme.spacing(1)
}));

const RestaurantBookingSummaryModal = ({ 
  open, 
  onClose, 
  bookingData,
  bookingIndex,
  restaurantDetails,
  bookingDate
}) => {
  const currencyCode = useSelector((state) => state.auth.currencyCode) || "SGD";
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate) || 1;
  const restaurants = useSelector((state) => state.restaurants.restaurants);

  // Get restaurant details - handle both current restaurantDetails and restaurantspack data
  const getRestaurantDetails = () => {
    if (restaurantDetails) {
      return restaurantDetails;
    } else if (bookingData?.restaurant && restaurants) {
      // Find restaurant details from the restaurants list
      const foundRestaurant = restaurants.find(r => r.id === bookingData.restaurant);
      return foundRestaurant;
    } else if (bookingData?.originalData) {
      // Create restaurant details from original data
      return {
        name: bookingData.originalData.restaurantName,
        city: bookingData.originalData.city || 'Not specified',
        country: bookingData.originalData.country || 'Not specified',
        cuisine: 'Not specified',
        master_image: '/placeholder-restaurant.jpg'
      };
    }
    return null;
  };

  const effectiveRestaurantDetails = getRestaurantDetails();

  // Add console log for incoming data
  useEffect(() => {
    console.log('RestaurantBookingSummaryModal Data:', {
      bookingData,
      restaurantDetails,
      effectiveRestaurantDetails,
      hasOriginalData: !!bookingData?.originalData
    });
  }, [bookingData, restaurantDetails]);

  if (!bookingData) return null;

  // Helper function to safely render potentially complex data
  const renderValue = (value) => {
    if (value === null || value === undefined) return 'Not specified';
    if (typeof value === 'object') {
      // If it's a meal selection object, handle it appropriately
      if (value.items && value.specificMealType) {
        console.log('Rendering meal selection:', value);
        return value.items.map(item => item.name).join(', ');
      }
      return 'Complex data';
    }
    return String(value);
  };

  // Helper function to format time slots
  const formatTimeSlot = (openTime, closeTime) => {
    if (!openTime || !closeTime) return 'Not specified';
    return `${openTime} - ${closeTime}`;
  };

  // Get meal time slots based on meal type
  const getMealTimeSlot = (mealType) => {
    if (!effectiveRestaurantDetails) return 'Not specified';
    
    switch (mealType?.toLowerCase()) {
      case 'breakfast':
        return formatTimeSlot(effectiveRestaurantDetails.opening_time_bf, effectiveRestaurantDetails.closing_time_bf);
      case 'lunch':
        return formatTimeSlot(effectiveRestaurantDetails.opening_time_lunch, effectiveRestaurantDetails.closing_time_lunch);
      case 'dinner':
        return formatTimeSlot(effectiveRestaurantDetails.opening_time_dinner, effectiveRestaurantDetails.closing_time_dinner);
      default:
        return 'Not specified';
    }
  };

  // Calculate total price based on meal selection
  const calculateTotalPrice = () => {
    if (!bookingData.specificMeal) return 0;

    console.log('Calculating total price from:', bookingData.specificMeal);
    
    // If specificMeal contains the meal selection object
    if (typeof bookingData.specificMeal === 'object' && bookingData.specificMeal.totalPrice) {
      return bookingData.specificMeal.totalPrice;
    }

    return 0;
  };

  const totalPrice = calculateTotalPrice();
  console.log('Final calculated price:', totalPrice);

  // Format price with currency
  const formatPrice = (price) => {
    if (!price) return '0.00';
    
    const mainPrice = Math.ceil(price * exchangeRate);
    const usdPrice = Math.ceil(price * usdExchangeRate);
    const sgdPrice = Math.ceil(price);

    return (
      <Stack spacing={0.5}>
        <Typography variant="h5" sx={{ fontWeight: 600, color: 'inherit' }}>
          {currencyCode} {mainPrice.toLocaleString()}
        </Typography>
        {currencyCode !== 'USD' && (
          <Typography variant="body2" sx={{ color: 'inherit', opacity: 0.9 }}>
            USD {usdPrice.toLocaleString()}
          </Typography>
        )}
        {currencyCode !== 'SGD' && (
          <Typography variant="body2" sx={{ color: 'inherit', opacity: 0.9 }}>
            SGD {sgdPrice.toLocaleString()}
          </Typography>
        )}
      </Stack>
    );
  };

  // Check if the meal type is buffet
  const isBuffet = bookingData.specificMeal?.specificMealType?.toLowerCase() === 'buffet';

  // Get pax data - handle both form data and restaurantspack data
  const getPaxData = () => {
    if (bookingData.pax) {
      // Form data format
      return {
        Adults: bookingData.pax.Adults || 0,
        Children: bookingData.pax.Children || 0
      };
    } else if (bookingData.originalData) {
      // Restaurantspack data format
      return {
        Adults: bookingData.originalData.adultCount || 0,
        Children: bookingData.originalData.childCount || 0
      };
    }
    return { Adults: 0, Children: 0 };
  };

  const paxData = getPaxData();

  return (
    <StyledModal
      open={open}
      onClose={onClose}
      aria-labelledby="restaurant-booking-summary-modal"
    >
      <ModalContent>
        <IconButton
          onClick={onClose}
          sx={{
            position: 'absolute',
            right: 8,
            top: 8,
            color: 'grey.500',
          }}
        >
          <CloseIcon />
        </IconButton>

        <Stack spacing={3}>
          {/* Header */}
          <Box>
            <Typography variant="h5" component="h2" gutterBottom sx={{ fontWeight: 600 }}>
              Restaurant Booking Summary
            </Typography>
            <Typography variant="subtitle1" color="text.secondary">
              Booking #{bookingIndex + 1}
            </Typography>
          </Box>

          <Divider />

          {/* Booking Date */}
          {bookingDate && (
            <SummarySection>
              <Box
                sx={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  p: 2,
                  backgroundColor: '#4caf50',
                  color: 'white',
                  borderRadius: 2,
                  gap: 1
                }}
              >
                <CalendarTodayIcon sx={{ color: 'white' }} />
                <Typography variant="h6" sx={{ fontWeight: 600, color: 'white' }}>
                  Booking Date: {bookingDate}
                </Typography>
              </Box>
            </SummarySection>
          )}

          {/* Restaurant Details */}
          <SummarySection>
            <Card sx={{ display: 'flex', mb: 2 }}>
              <CardMedia
                component="img"
                sx={{ width: 200, height: 200, objectFit: 'cover' }}
                image={effectiveRestaurantDetails?.master_image || effectiveRestaurantDetails?.additional_images?.[0] || '/placeholder-restaurant.jpg'}
                alt={effectiveRestaurantDetails?.name || effectiveRestaurantDetails?.restaurant_name || 'Restaurant'}
              />
              <Box sx={{ display: 'flex', flexDirection: 'column', flex: 1 }}>
                <CardContent>
                  <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                    <Typography variant="h6" gutterBottom>
                      {effectiveRestaurantDetails?.name || effectiveRestaurantDetails?.restaurant_name || 'Restaurant'}
                    </Typography>
                    <Chip 
                      icon={<LocalDiningIcon />} 
                      label={effectiveRestaurantDetails?.cuisine || effectiveRestaurantDetails?.cuisine_type || 'Cuisine not specified'} 
                      sx={{ 
                        color: '#4caf50', 
                        borderColor: '#4caf50',
                        '& .MuiChip-icon': { color: '#4caf50' }
                      }}
                      variant="outlined"
                    />
                  </Box>
                  
                  <DetailRow>
                    <LocationOnIcon sx={{ color: '#4caf50' }} />
                    <Typography>
                      {effectiveRestaurantDetails?.city || 'Not specified'}, {effectiveRestaurantDetails?.country || 'Not specified'}
                    </Typography>
                  </DetailRow>

                  {/* Selected Time Info */}
                  <Box 
                    sx={{ 
                      mt: 2,
                      p: 1.5,
                      borderRadius: 1,
                      bgcolor: '#4caf50',
                      color: 'white',
                    }}
                  >
                    <Typography variant="subtitle2" sx={{ fontWeight: 600, mb: 0.5 }}>
                      <AccessTimeIcon sx={{ mr: 1, verticalAlign: 'middle', color: 'white' }} />
                      Selected Time
                    </Typography>
                    <Typography sx={{ fontSize: '1rem', ml: 3,color: 'white', }}>
                      {bookingData.mealType}: {bookingData.timeSlot || getMealTimeSlot(bookingData.mealType)}
                    </Typography>
                  </Box>
                </CardContent>
              </Box>
            </Card>
          </SummarySection>

          {/* Meal Details */}
          <SummarySection>
            <Typography variant="h6" gutterBottom>
              <RestaurantIcon sx={{ mr: 1, verticalAlign: 'middle', color: '#4caf50' }} />
              Meal Information
            </Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={isBuffet ? 12 : 6}>
                <InfoCard>
                  <Typography variant="subtitle1" sx={{ color: '#4caf50' }} gutterBottom>
                    Meal Type
                  </Typography>
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <FastfoodIcon sx={{ color: '#4caf50' }} />
                    <Typography variant="body1">
                      {bookingData.specificMeal?.specificMealType || bookingData.mealType || 'Not selected'}
                    </Typography>
                  </Box>
                </InfoCard>
              </Grid>
              {!isBuffet && (
                <Grid item xs={12} md={6}>
                  <InfoCard>
                    <Typography variant="subtitle1" sx={{ color: '#4caf50' }} gutterBottom>
                      Selected Items
                    </Typography>
                    <Box>
                      {bookingData.specificMeal?.items?.map((item) => (
                        <Box 
                          key={`meal-${item.meal_id}-${item.name}`} 
                          sx={{ mb: 1 }}
                        >
                          <Typography variant="body2">
                            {item.name} {item.quantity > 1 ? `(x${item.quantity})` : ''}
                          </Typography>
                        </Box>
                      )) || 'No items selected'}
                    </Box>
                  </InfoCard>
                </Grid>
              )}
            </Grid>
          </SummarySection>

          {/* Guest Details */}
          <SummarySection>
            <Typography variant="h6" gutterBottom>
              <GroupIcon sx={{ mr: 1, verticalAlign: 'middle', color: '#4caf50' }} />
              Guest Details
            </Typography>
            <InfoCard>
              <Box sx={{ display: 'flex', gap: 3 }}>
                <Box>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <PersonIcon sx={{ mr: 1, color: '#4caf50' }} />
                    <Typography>
                      Adults: {paxData.Adults}
                    </Typography>
                  </Box>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <ChildCareIcon sx={{ mr: 1, color: '#4caf50' }} />
                    <Typography>
                      Children: {paxData.Children}
                    </Typography>
                  </Box>
                </Box>
              </Box>
            </InfoCard>
          </SummarySection>

          {/* Price Details */}
          <SummarySection>
            <Typography variant="h6" gutterBottom>
              <AttachMoneyIcon sx={{ mr: 1, verticalAlign: 'middle', color: '#4caf50' }} />
              Price Details
            </Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={6}>
                <InfoCard>
                  <Typography variant="subtitle1" sx={{ color: '#4caf50' }} gutterBottom>
                    Price Breakdown
                  </Typography>
                  {bookingData.specificMeal?.items?.map((item) => (
                    <Box 
                      key={`price-${item.meal_id}-${item.name}`}
                    >
                      {item.adult_price ? (
                        <Box key={`buffet-${item.meal_id}`}>
                          <Typography variant="body2" sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                            <span>Adult ({paxData.Adults}x):</span>
                            <span>{currencyCode} {Math.ceil((item.adult_price || 0) * exchangeRate * paxData.Adults)}</span>
                          </Typography>
                          {paxData.Children > 0 && (
                            <Typography variant="body2" sx={{ display: 'flex', justifyContent: 'space-between' }}>
                              <span>Child ({paxData.Children}x):</span>
                              <span>{currencyCode} {Math.ceil((item.child_price || 0) * exchangeRate * paxData.Children)}</span>
                            </Typography>
                          )}
                        </Box>
                      ) : (
                        <Typography variant="body2" sx={{ display: 'flex', justifyContent: 'space-between' }}>
                          <span>{item.name} ({item.quantity}x):</span>
                          <span>{currencyCode} {Math.ceil(item.price * exchangeRate * item.quantity)}</span>
                        </Typography>
                      )}
                    </Box>
                  ))}
                </InfoCard>
              </Grid>
              <Grid item xs={12} md={6}>
                <PriceCard>
                  <Typography variant="subtitle1" gutterBottom sx={{ color: 'inherit' }}>
                    Total Price
                  </Typography>
                  {formatPrice(totalPrice)}
                  {/* {restaurantDetails?.tax_percentage && (
                    <Typography variant="caption" sx={{ color: 'inherit', opacity: 0.8 }}>
                      *Prices are subject to {restaurantDetails.tax_percentage}% tax
                    </Typography>
                  )} */}
                </PriceCard>
              </Grid>
            </Grid>
          </SummarySection>

          {/* Action Buttons */}
          <Box sx={{ display: 'flex', gap: 2, justifyContent: 'flex-end', mt: 2 }}>
            <Button 
              variant="outlined" 
              onClick={onClose}
              sx={{ 
                borderColor: '#4caf50', 
                color: '#4caf50',
                '&:hover': {
                  borderColor: '#388e3c',
                  backgroundColor: 'rgba(76, 175, 80, 0.04)'
                }
              }}
            >
              Close
            </Button>
          </Box>
        </Stack>
      </ModalContent>
    </StyledModal>
  );
};

export default RestaurantBookingSummaryModal; 