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
  selectedGuides = [],
  bookedAttractions = {},
  selectedHotelId,
  selectedGuideId,
  itineraryDates = [],
  entryPortTransfer = 0,
  exitPortTransfer = 0,
  attractionWithTransfer = {}
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
  
  // Helper function to get selected entity by ID
  const getSelectedEntityById = (entities, entityId) => {
    if (!entityId || !entities || !entities.length) return null;
    return entities.find(entity => (entity.id === entityId || entity._id === entityId));
  };

  // Helper functions to check transfer availability
  const hasEntryPortTransfer = () => {
    return packageData?.entry_port_transfer === 1 || 
           packageData?.entry_port_transfer === true ||
           packageData?.entry_port === 1 || 
           packageData?.entry_port === true || 
           packageData?.has_entry_port_transfer === true;
  };
  
  const hasExitPortTransfer = () => {
    return packageData?.exit_port_transfer === 1 || 
           packageData?.exit_port_transfer === true ||
           packageData?.exit_port === 1 || 
           packageData?.exit_port === true || 
           packageData?.has_exit_port_transfer === true;
  };
  
  // Helper to check if an attraction has transfer available and what type
  const getAttractionTransferType = (attractionId) => {
    // Check if attraction transfer is available in package data
    if (packageData?.attractions_with_transfer) {
      const transferValue = packageData.attractions_with_transfer[attractionId];
      if (transferValue === 2) return 'bidirectional';
      if (transferValue === 1 || transferValue === true) return 'unidirectional';
    }
    
    // Check if package data has general attraction_with_transfer flag
    if (packageData?.attraction_with_transfer === 2) {
      return 'bidirectional';
    }
    if (packageData?.attraction_with_transfer === 1 || 
        packageData?.attraction_with_transfer === true) {
      return 'unidirectional';
    }
    
    // Default to checking the attraction object itself
    const attraction = selectedAttractions.find(a => (a.id === attractionId || a._id === attractionId));
    if (attraction?.with_transfer === 2) return 'bidirectional';
    if (attraction?.with_transfer === 1 || 
        attraction?.with_transfer === true ||
        attraction?.transfer_available === true) return 'unidirectional';
    
    return null; // No transfer available
  };

  const hasAttractionTransfer = (attractionId) => {
    return getAttractionTransferType(attractionId) !== null;
  };

  // Handle booking button click
  const handleBookPackage = () => {
    // Ensure we use ONLY ONE item from each category 
    // If user selected items via modal, use the first selected item
    // Otherwise use the first item from the package data
    
    // For Hotels
    let hotelToUse = null;
    if (selectedHotelId) {
      // User selected a specific hotel via UI
      hotelToUse = getSelectedEntityById(selectedHotels, selectedHotelId);
    } else if (selectedHotels.length > 0) {
      // User made a selection via modal, use the first selected hotel
      hotelToUse = selectedHotels[0]; 
    } else if (packageData.selected_hotels && packageData.selected_hotels.length > 0) {
      // No modal selection, use the first hotel from package data
      hotelToUse = packageData.selected_hotels[0];
    }
    
    // For Guides - define outside the loop so it's accessible later
    let guideToUse = null;
    if (selectedGuideId) {
      // User selected a specific guide via UI
      guideToUse = getSelectedEntityById(selectedGuides, selectedGuideId);
    } else if (selectedGuides.length > 0) {
      // User made a selection via modal, use the first selected guide
      guideToUse = selectedGuides[0];
    } else if ((packageData.selected_guides && packageData.selected_guides.length > 0) || 
               (packageData.selected_guide && packageData.selected_guide.length > 0)) {
      // No modal selection, use the first guide from package data
      guideToUse = packageData.selected_guides?.[0] || packageData.selected_guide?.[0];
    }
    
    // Create enhanced itinerary with services for each day
    const enhancedItinerary = itineraryDates.map(dayInfo => {
      // Start with basic day info
      const enhancedDay = {
        ...dayInfo,
        services: []
      };
      
      // Add hotel if selected
      if (hotelToUse) {
        const hotelService = {
          service_type: 'hotel',
          service_id: hotelToUse.id || hotelToUse._id,
          service_name: hotelToUse.name,
          details: hotelToUse
        };
        
        // Add entry_port for first day if available
        if (dayInfo.day === 1 && hasEntryPortTransfer()) {
          hotelService.entry_port = 1;
        } else {
          hotelService.entry_port = null;
        }
        
        // Add exit_port for last day if available
        if (dayInfo.day === itineraryDates.length && hasExitPortTransfer()) {
          hotelService.exit_port = 1;
        } else {
          hotelService.exit_port = null;
        }
        
        enhancedDay.services.push(hotelService);
      }
      
      // Add attraction if booked for this day
      if (selectedAttractions && selectedAttractions.length > 0) {
        selectedAttractions.forEach(attraction => {
          const attractionId = attraction.id || attraction._id;
          const bookedDayIndex = bookedAttractions[attractionId];
          
          // Only add if booked for this specific day
          if (bookedDayIndex !== undefined && bookedDayIndex === dayInfo.day - 1) {
            const attractionService = {
              service_type: 'attraction',
              service_id: attractionId,
              service_name: attraction.name || attraction.title,
              details: attraction
            };
            
            // Add attraction_with_transfer if available for this attraction
            const transferType = getAttractionTransferType(attractionId);
            if (transferType === 'bidirectional') {
              attractionService.attraction_with_transfer = 2;
            } else if (transferType === 'unidirectional') {
              attractionService.attraction_with_transfer = 1;
            } else {
              attractionService.attraction_with_transfer = null;
            }
            
            enhancedDay.services.push(attractionService);
          }
        });
      }
      
      // Add guide if selected - guide is already defined above
      if (guideToUse) {
        enhancedDay.services.push({
          service_type: 'guide',
          service_id: guideToUse.id || guideToUse._id,
          service_name: guideToUse.name,
          details: guideToUse
        });
      }
      
      // For restaurants (commented out as requested)
      /* let restaurantToUse = null;
      if (selectedRestaurants.length > 0) {
        // User made a selection via modal, use the first selected restaurant
        restaurantToUse = selectedRestaurants[0];
      } else if (packageData.selected_restaurants && packageData.selected_restaurants.length > 0) {
        // No modal selection, use the first restaurant from package data
        restaurantToUse = packageData.selected_restaurants[0];
      }
      
      if (restaurantToUse) {
        enhancedDay.services.push({
          service_type: 'restaurant',
          service_id: restaurantToUse.id || restaurantToUse._id,
          service_name: restaurantToUse.name,
          details: restaurantToUse
        });
      } */
      
      return enhancedDay;
    });
    
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
        hotels: hotelToUse ? [{ 
          ...hotelToUse, 
          type: 'hotel',
          entry_port: hasEntryPortTransfer() ? 1 : null,
          exit_port: hasExitPortTransfer() ? 1 : null
        }] : [],
        attractions: selectedAttractions.length > 0 ? 
          selectedAttractions.filter(attraction => 
            bookedAttractions[attraction.id || attraction._id] !== undefined
          ).map(attraction => {
            const attractionId = attraction.id || attraction._id;
            const transferType = getAttractionTransferType(attractionId);
            
            return {
              ...attraction, 
              type: 'attraction',
              attraction_with_transfer: transferType === 'bidirectional' ? 2 : 
                                        transferType === 'unidirectional' ? 1 : null
            };
          }) : [],
        /* restaurants: restaurantToUse ? [{ ...restaurantToUse, type: 'restaurant' }] : [], */
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
        } : null,
        itinerary: enhancedItinerary,
        entry_port_transfer: hasEntryPortTransfer() ? 1 : null,
        exit_port_transfer: hasExitPortTransfer() ? 1 : null
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
    <Paper
      elevation={2}
      sx={{
        borderRadius: '16px',
        overflow: 'hidden',
      }}
    >
      {/* Header */}
      <Box
        sx={{
          bgcolor: 'primary.main',
          color: 'white',
          px: 3,
          py: 2,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
        }}
      >
        <Typography variant="h6" fontWeight="bold">
          Price Summary
        </Typography>
        <AttachMoneyIcon fontSize="medium" />
      </Box>
      
      {/* Content */}
      <Box sx={{ p: 3 }}>
        {/* Adult Price */}
        <Box sx={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: 'center',
          py: 1,
          borderBottom: '1px dashed #e0e0e0'
        }}>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <PersonIcon sx={{ color: 'primary.main', mr: 1, fontSize: 20 }} />
            <Typography variant="body1">
              Adult{adultCount > 1 ? 's' : ''} ({adultCount})
            </Typography>
          </Box>
          <Typography variant="body1" fontWeight="bold">
            ${adultPrice.toFixed(2)} each
          </Typography>
        </Box>
        
        {/* Child Price (if available) */}
        {hasChildPrice && childCount > 0 && (
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            py: 1,
            borderBottom: '1px dashed #e0e0e0'
          }}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <PersonIcon sx={{ color: 'primary.main', mr: 1, fontSize: 16 }} />
              <Typography variant="body1">
                Child{childCount > 1 ? 'ren' : ''} ({childCount})
              </Typography>
            </Box>
            <Typography variant="body1" fontWeight="bold">
              ${childPrice.toFixed(2)} each
            </Typography>
          </Box>
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
        
        {/* Travel Dates */}
        {searchParams?.check_in && searchParams?.check_out && (
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            py: 1,
            borderBottom: '1px dashed #e0e0e0'
          }}>
            <Typography variant="body1" fontWeight="medium">
              Travel Dates
            </Typography>
            <Typography variant="body2" fontWeight="medium">
              {new Date(searchParams.check_in).toLocaleDateString()} - {new Date(searchParams.check_out).toLocaleDateString()}
            </Typography>
          </Box>
        )}
        
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
            ${totalPrice.toFixed(2)}
          </Typography>
        </Box>
        
        <Button 
          variant="contained" 
          color="primary" 
          fullWidth 
          size="large"
          sx={{ py: 1.5, mt: 2 }}
          onClick={handleBookPackage}
        >
          Book This Package
        </Button>
        <Typography variant="body2" color="text.secondary" align="center" sx={{ mt: 1 }}>
          * Prices are per person
        </Typography>
      </Box>
      
      {/* User Info Modal */}
      {isUserInfoModalOpen && (
        <UserInfo
          open={isUserInfoModalOpen}
          onClose={() => setIsUserInfoModalOpen(false)}
          bookingData={bookingData}
          onSubmit={handleFormSubmit}
        />
      )}
      
      {/* Notifications */}
      <Snackbar 
        open={notification.open} 
        autoHideDuration={6000} 
        onClose={handleCloseNotification}
      >
        <Alert 
          onClose={handleCloseNotification} 
          severity={notification.severity} 
          sx={{ width: '100%' }}
        >
          {notification.message}
        </Alert>
      </Snackbar>
    </Paper>
  );
};

export default PackagePricing; 