import React, { useState, useEffect } from 'react';
import { Paper, Typography, Box, Button, Grid, Divider, Snackbar, Alert } from '@mui/material';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import PersonIcon from '@mui/icons-material/Person';
import { useSelector, useDispatch } from 'react-redux';
import UserInfo from './UserInfo';
import { resetBookingStatus } from '../../../slice/tour-packages/prePackagesSlice';

const PackagePricing = ({ 
  packageData, 
  selectedHotels = [], 
  selectedAttractions = [], 
  selectedRestaurants = [], 
  selectedGuides = [] 
}) => {
  // State for the UserInfo modal
  const [isUserInfoModalOpen, setIsUserInfoModalOpen] = useState(false);
  const [bookingData, setBookingData] = useState(null);
  const [bookingComplete, setBookingComplete] = useState(false);
  const [notification, setNotification] = useState({
    open: false,
    message: '',
    severity: 'success'
  });
  
  const dispatch = useDispatch();
  
  // Get search params and booking status from Redux store
  const { searchParams, bookingSuccess, bookingData: bookedData } = useSelector(state => state.prePackages);
  
  // Handle successful booking
  useEffect(() => {
    if (bookingSuccess && bookedData) {
      setBookingComplete(true);
      setNotification({
        open: true,
        message: 'Your booking has been successfully submitted!',
        severity: 'success'
      });
      
      // Reset booking status after showing success
      setTimeout(() => {
        dispatch(resetBookingStatus());
      }, 1000);
    }
  }, [bookingSuccess, bookedData, dispatch]);
  
  // Calculate total price based on number of adults and children
  const adultPrice = parseFloat(packageData.price_adult) || 0;
  const childPrice = parseFloat(packageData.price_child) || 0;
  
  // Use search params for passenger counts if available, otherwise fallback to packageData
  const adultCount = searchParams?.adults ? parseInt(searchParams.adults) : 1;
  const childCount = searchParams?.children ? parseInt(searchParams.children) : 0;
  const maleCount = searchParams?.male_count ? parseInt(searchParams.male_count) : 0;
  const femaleCount = searchParams?.female_count ? parseInt(searchParams.female_count) : 0;
  
  const totalAdultPrice = adultPrice * adultCount;
  const totalChildPrice = childPrice * childCount;
  const totalPrice = totalAdultPrice + totalChildPrice;
  
  // Check if child price is available
  const hasChildPrice = packageData.price_child && parseFloat(packageData.price_child) > 0;
  
  // Handle booking button click
  const handleBookPackage = () => {
    // Ensure we use ONLY ONE item from each category 
    // If user selected items via modal, use the first selected item
    // Otherwise use the first item from the package data
    
    // For Hotels
    let hotelToUse = null;
    if (selectedHotels.length > 0) {
      // User made a selection via modal, use the first selected hotel
      hotelToUse = selectedHotels[0]; 
    } else if (packageData.selected_hotels && packageData.selected_hotels.length > 0) {
      // No modal selection, use the first hotel from package data
      hotelToUse = packageData.selected_hotels[0];
    }
    
    // For Attractions
    let attractionToUse = null;
    if (selectedAttractions.length > 0) {
      // User made a selection via modal, use the first selected attraction
      attractionToUse = selectedAttractions[0];
    } else if (packageData.selected_attractions && packageData.selected_attractions.length > 0) {
      // No modal selection, use the first attraction from package data
      attractionToUse = packageData.selected_attractions[0];
    }
    
    // For Restaurants
    let restaurantToUse = null;
    if (selectedRestaurants.length > 0) {
      // User made a selection via modal, use the first selected restaurant
      restaurantToUse = selectedRestaurants[0];
    } else if (packageData.selected_restaurants && packageData.selected_restaurants.length > 0) {
      // No modal selection, use the first restaurant from package data
      restaurantToUse = packageData.selected_restaurants[0];
    }
    
    // For Guides
    let guideToUse = null;
    if (selectedGuides.length > 0) {
      // User made a selection via modal, use the first selected guide
      guideToUse = selectedGuides[0];
    } else if (packageData.selected_guides && packageData.selected_guides.length > 0) {
      // No modal selection, use the first guide from package data (selected_guides)
      guideToUse = packageData.selected_guides[0];
    } else if (packageData.selected_guide && packageData.selected_guide.length > 0) {
      // No modal selection, use the first guide from package data (selected_guide)
      guideToUse = packageData.selected_guide[0];
    }
    
    // Create a structured data object with all selections and their types
    const bookingData = {
      package: {
        // Exclude the package's selected_hotels/attractions/etc as we want to use only what the user selected
        ...packageData,
        selected_hotels: undefined,
        selected_attractions: undefined,
        selected_restaurants: undefined,
        selected_guides: undefined,
        selected_guide: undefined,
        type: 'package'
      },
      selected: {
        hotels: hotelToUse ? [{ ...hotelToUse, type: 'hotel' }] : [],
        attractions: attractionToUse ? [{ ...attractionToUse, type: 'attraction' }] : [],
        restaurants: restaurantToUse ? [{ ...restaurantToUse, type: 'restaurant' }] : [],
        guides: guideToUse ? [{ ...guideToUse, type: 'guide' }] : []
      },
      booking_details: {
        adult_count: adultCount,
        child_count: childCount,
        male_count: maleCount,
        female_count: femaleCount,
        total_price: totalPrice,
        currency: 'USD',
        travel_dates: searchParams?.check_in && searchParams?.check_out ? {
          check_in: searchParams.check_in,
          check_out: searchParams.check_out
        } : null
      }
    };
    
    // Save booking data to state and open the user info modal
    setBookingData(bookingData);
    setIsUserInfoModalOpen(true);
  };
  
  // Handle final form submission with user info
  const handleFormSubmit = (finalData) => {
    // Final data with user info is already logged in UserInfo component
    // You could also handle API calls or other actions here
    console.log('Form submitted successfully');
  };
  
  // Handle notification close
  const handleCloseNotification = () => {
    setNotification(prev => ({...prev, open: false}));
  };
  
  return (
    <>
      <Paper elevation={2} sx={{ borderRadius: '12px', overflow: 'hidden', height: '100%' }}>
        <Box sx={{ bgcolor: 'primary.main', color: 'primary.contrastText', px: 3, py: 1.5, display: 'flex', alignItems: 'center' }}>
          <AttachMoneyIcon sx={{ mr: 1 }} />
          <Typography variant="h6" fontWeight="bold">Price Summary</Typography>
        </Box>
        
        <Box sx={{ p: 3 }}>
          {/* Adult Price Section */}
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            py: 1,
            borderBottom: '1px dashed #e0e0e0'
          }}>
            <Typography variant="body1" fontWeight="medium">
              Adult Price
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <Typography variant="caption" sx={{ mr: 0.5 }}>SGD</Typography>
              <Typography variant="h6" color="primary" fontWeight="bold">
                {packageData.price_adult}
              </Typography>
            </Box>
          </Box>
          
          {/* Adult Count and Total */}
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            py: 1,
            borderBottom: '1px dashed #e0e0e0'
          }}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <PersonIcon fontSize="small" sx={{ mr: 0.5 }} />
              <Typography variant="body2">
                {adultCount} {adultCount > 1 ? 'Adults' : 'Adult'} 
                {(maleCount > 0 || femaleCount > 0) && (
                  <Typography component="span" variant="caption" sx={{ ml: 0.5 }}>
                    ({maleCount} male, {femaleCount} female)
                  </Typography>
                )}
              </Typography>
            </Box>
            <Typography variant="body1" fontWeight="medium">
              <Typography variant="caption" sx={{ mr: 0.5 }}>SGD {totalAdultPrice.toFixed(2)}</Typography> 
            </Typography>
          </Box>
          
          {/* Child Price Section - Only show if child price exists */}
          {hasChildPrice && childCount > 0 && (
            <>
              <Box sx={{ 
                display: 'flex', 
                justifyContent: 'space-between', 
                alignItems: 'center',
                py: 1,
                borderBottom: '1px dashed #e0e0e0'
              }}>
                <Typography variant="body1" fontWeight="medium">
                  Child Price
                </Typography>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <AttachMoneyIcon color="primary" />
                  <Typography variant="h6" fontWeight="bold">
                    {packageData.price_child}
                  </Typography>
                </Box>
              </Box>
              
              {/* Child Count and Total - Only show if there are children */}
              <Box sx={{ 
                display: 'flex', 
                justifyContent: 'space-between', 
                alignItems: 'center',
                py: 1,
                borderBottom: '1px dashed #e0e0e0'
              }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <PersonIcon fontSize="small" sx={{ mr: 0.5 }} />
                  <Typography variant="body2">
                    {childCount} {childCount > 1 ? 'Children' : 'Child'}
                  </Typography>
                </Box>
                <Typography variant="body1" fontWeight="medium">
                  <Typography variant="caption" sx={{ mr: 0.5 }}>SGD</Typography> {totalChildPrice.toFixed(2)}
                </Typography>
              </Box>
            </>
          )}
          
          {/* Duration */}
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            py: 1,
            borderBottom: '1px dashed #e0e0e0'
          }}>
            <Typography variant="body1" fontWeight="medium">
              Duration
            </Typography>
            <Typography variant="body1" fontWeight="bold">
              {packageData.duration_days} Days
            </Typography>
          </Box>
          
          
          
          {/* Total Price */}
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            py: 1.5,
            mt: 1,
            bgcolor: 'primary.light',
            px: 2,
            borderRadius: '8px'
          }}>
            <Typography variant="h6" fontWeight="bold">
              Total Price
            </Typography>
            <Typography variant="h6" color="primary.dark" fontWeight="bold">
              <Typography variant="caption" component="span" sx={{ mr: 0.5 }}>SGD</Typography> {totalPrice.toFixed(2)}
            </Typography>
          </Box>
          
          <Button 
            variant="contained" 
            color="primary" 
            fullWidth 
            size="large"
            sx={{ py: 1.5, mt: 2 }}
            onClick={handleBookPackage}
            disabled={bookingComplete}
          >
            {bookingComplete ? 'Booking Complete' : 'Book This Package'}
          </Button>
          
          {bookingComplete && (
            <Alert severity="success" sx={{ mt: 2 }}>
              Your booking has been confirmed successfully.
            </Alert>
          )}
          
          <Typography variant="body2" color="text.secondary" align="center" sx={{ mt: 1 }}>
            * Prices are per person
          </Typography>
        </Box>
      </Paper>
      
      {/* User Info Modal */}
      {bookingData && (
        <UserInfo 
          open={isUserInfoModalOpen} 
          onClose={() => setIsUserInfoModalOpen(false)}
          onSubmit={handleFormSubmit}
          bookingData={bookingData}
        />
      )}
      
      {/* Success Notification */}
      <Snackbar
        open={notification.open}
        autoHideDuration={6000}
        onClose={handleCloseNotification}
        anchorOrigin={{ vertical: 'top', horizontal: 'right' }}
      >
        <Alert 
          onClose={handleCloseNotification} 
          severity={notification.severity} 
          elevation={6} 
          variant="filled"
        >
          {notification.message}
        </Alert>
      </Snackbar>
    </>
  );
};

export default PackagePricing; 