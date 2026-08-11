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
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  console.log("bookingData5678", bookingData);

  // Check if this is originalData format (from restaurantspack) or form format
  const isOriginalDataFormat = bookingData?.originalData && bookingData.originalData.restaurantId;
  
  // Direct mapping based on data format
  const mappedData = !bookingData ? null : isOriginalDataFormat ? {
    // Format 1: Original data from restaurantspack
    restaurantId: bookingData.originalData.restaurantId,
    restaurantName: bookingData.originalData.restaurantName,
    city: bookingData.originalData.city || 'Not specified',
    country: bookingData.originalData.country || 'Not specified',
    image: bookingData.originalData.image || '/placeholder-restaurant.jpg',
    bookingDate: bookingData.originalData.bookingDate,
    visitTime: bookingData.originalData.visitTime,
    mealType: bookingData.originalData.mealType,
    mealSpecificType: bookingData.originalData.mealSpecificType,
    adultCount: bookingData.originalData.adultCount,
    childCount: bookingData.originalData.childCount,
    pax: {
      Adults: bookingData.originalData.adultCount || 0,
      Children: bookingData.originalData.childCount || 0
    },
    totalPrice: bookingData.originalData.totalPrice,
    mealPrice: bookingData.originalData.mealPrice,
    mealDescription: bookingData.originalData.MealDescription || [],
    customerDetails: {
      fullName: bookingData.originalData.fullName || '',
      email: bookingData.originalData.email || '',
      phone: bookingData.originalData.phone || '',
      countryCode: bookingData.originalData.countryCode || '',
      address1: bookingData.originalData.address1 || '',
      address2: bookingData.originalData.address2 || '',
      state: bookingData.originalData.state || '',
      zip: bookingData.originalData.zip || '',
      specialRequests: bookingData.originalData.specialRequests || ''
    },
    bookingType: bookingData.originalData.bookingType || 'enquiry',
    booking_id: bookingData.originalData.booking_id,
    dmc_id: bookingData.originalData.dmc_id,
    priceTypes: bookingData.originalData.priceTypes || ['dmc'],
    transport: bookingData.originalData.transport,
    transportPrice: bookingData.originalData.transportPrice || 0
  } : {
    // Format 2: Form data format
    restaurantId: bookingData.restaurant,
    restaurantName: bookingData.restaurantName || 'Restaurant',
    city: bookingData.city || 'Not specified',
    country: bookingData.country || 'Not specified',
    image: bookingData.image || '/placeholder-restaurant.jpg',
    bookingDate: bookingData.bookingDate,
    visitTime: bookingData.timeSlot,
    mealType: bookingData.mealType,
    mealSpecificType: bookingData.specificMeal?.specificMealType || bookingData.mealType,
    adultCount: bookingData.pax?.Adults || 0,
    childCount: bookingData.pax?.Children || 0,
    pax: bookingData.pax || { Adults: 0, Children: 0 },
    totalPrice: bookingData.specificMeal?.totalPrice || 0,
    mealPrice: bookingData.specificMeal?.totalPrice || 0,
    mealDescription: bookingData.specificMeal?.items || [],
    customerDetails: {
      fullName: '',
      email: '',
      phone: '',
      countryCode: '',
      address1: '',
      address2: '',
      state: '',
      zip: '',
      specialRequests: ''
    },
    bookingType: 'enquiry',
    booking_id: null,
    dmc_id: null,
    priceTypes: ['dmc'],
    transport: null,
    transportPrice: 0
  };

  // Add console log for incoming data
  useEffect(() => {
    if (bookingData) {
      console.log('RestaurantBookingSummaryModal Data:', {
        originalBookingData: bookingData,
        mappedData: mappedData,
        hasOriginalData: !!bookingData?.originalData,
        dataFormat: bookingData?.originalData ? 'originalData' : 'form'
      });
    }
  }, [bookingData, mappedData]);

  // Early return after all hooks to avoid Rules of Hooks violation
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

  // Calculate total price based on mapped data
  const calculateTotalPrice = () => {
    if (!mappedData) return 0;
    console.log('Calculating total price from mapped data:', mappedData);
    
    // Use the mapped total price
    return mappedData.totalPrice || 0;
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

  // Check if the meal type is buffet using mapped data
  const isBuffet = mappedData?.mealSpecificType?.toLowerCase() === 'buffet';

  // Get pax data from mapped data
  const paxData = {
    Adults: mappedData?.pax?.Adults || 0,
    Children: mappedData?.pax?.Children || 0
  };

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
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <Typography variant="subtitle1" color="text.secondary">
                Booking #{bookingIndex + 1}
              </Typography>
              {mappedData?.booking_id && (
                <Chip 
                  label={`ID: ${mappedData.booking_id}`}
                  size="small"
                  sx={{ 
                    bgcolor: '#4caf50',
                    color: 'white',
                    fontSize: '0.7rem',
                    height: '20px'
                  }}
                />
              )}
            </Box>
          </Box>

          <Divider />

          {/* Booking Date */}
          {mappedData?.bookingDate && (
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
                  Booking Date: {mappedData.bookingDate}
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
                image={mappedData?.image || '/placeholder-restaurant.jpg'}
                alt={mappedData?.restaurantName || 'Restaurant'}
              />
              <Box sx={{ display: 'flex', flexDirection: 'column', flex: 1 }}>
                <CardContent>
                  <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                    <Typography variant="h6" gutterBottom>
                      {mappedData?.restaurantName || 'Restaurant'}
                    </Typography>
                    <Chip 
                      icon={<LocalDiningIcon />} 
                      label="Restaurant" 
                      sx={{ 
                        color: '#4caf50', 
                        borderColor: '#4caf50',
                        '& .MuiChip-icon': { color: '#4caf50' }
                      }}
                      variant="outlined"
                    />
                  </Box>
                  

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
                      {mappedData?.mealType}: {mappedData?.visitTime}
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
                      {mappedData?.mealSpecificType || mappedData?.mealType || 'Not selected'}
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
                      {mappedData?.mealDescription?.map((item, index) => (
                        <Box 
                          key={`meal-${item.meal_id || index}-${item.name || item.item_name}`} 
                          sx={{ mb: 1 }}
                        >
                          <Typography variant="body2">
                            {item.name || item.item_name} {item.quantity > 1 ? `(x${item.quantity})` : ''}
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
                  {mappedData?.mealDescription?.map((item, index) => (
                    <Box 
                      key={`price-${item.meal_id || index}-${item.name || item.item_name}`}
                    >
                      {item.adult_price ? (
                        <Box key={`buffet-${item.meal_id || index}`}>
                          <Typography variant="body2" sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                            <span>Adult ({paxData.Adults}x):</span>
                            {PriceHide !== "1" ? (
                              <span>{currencyCode} {Math.ceil((item.adult_price || 0) * exchangeRate * paxData.Adults)}</span>
                            ):(
                              <span>Pricing hidden</span>
                            )}
                          </Typography>
                          {paxData.Children > 0 && (
                            <Typography variant="body2" sx={{ display: 'flex', justifyContent: 'space-between' }}>
                              <span>Child ({paxData.Children}x):</span>
                              {PriceHide !== "1" ? (
                                <span>{currencyCode} {Math.ceil((item.child_price || 0) * exchangeRate * paxData.Children)}</span>
                              ):(
                                <span>Pricing hidden</span>
                              )}
                            </Typography>
                          )}
                        </Box>
                      ) : (
                        <Typography variant="body2" sx={{ display: 'flex', justifyContent: 'space-between' }}>
                          <span>{item.name || item.item_name} ({item.quantity}x):</span>
                          {PriceHide !== "1" ? (
                            <span>{currencyCode} {Math.ceil((item.price || 0) * exchangeRate * item.quantity)}</span>
                          ):(
                            <span>Pricing hidden</span>
                          )}
                        </Typography>
                      )}
                    </Box>
                  )) || (
                    <Typography variant="body2" color="text.secondary">
                      No pricing details available
                    </Typography>
                  )}
                </InfoCard>
              </Grid>
              <Grid item xs={12} md={6}>
                <PriceCard>
                  <Typography variant="subtitle1" gutterBottom sx={{ color: 'inherit' }}>
                    Total Price
                  </Typography>
                  {PriceHide !== "1" ? (
                    formatPrice(totalPrice)
                  ):(
                    <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                      Pricing hidden
                    </Typography>
                  )}
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