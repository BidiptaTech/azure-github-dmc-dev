import React, { useState, useEffect } from 'react';
import { Paper, Typography, Box, Button, Grid, Divider } from '@mui/material';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import PersonIcon from '@mui/icons-material/Person';
import { useSelector, useDispatch } from 'react-redux';
import UserInfo from './UserInfo';
import { resetBookingStatus } from '../../../slice/tour-packages/prePackagesSlice';

  const PackagePricing = ({
  packageData,
  selectedHotels = [],
  selectedAttractions = [],
  selectedGuides = [],
  bookedAttractions = {},
  selectedHotelId, 
  selectedGuideId,
  guidesByDay = {},
  itineraryDates = [],
}) => {
  // console.log('packageData', packageData);
  // console.log('packageData', packageData);
  // State for the UserInfo modal
  const [isUserInfoModalOpen, setIsUserInfoModalOpen] = useState(false);
  const [bookingData, setBookingData] = useState(null);
  const [bookingComplete, setBookingComplete] = useState(false);

  // Try to parse itinerary if it exists, otherwise use empty object
  const originalItinerary = packageData.itinerary ? 
    (typeof packageData.itinerary === 'string' ? JSON.parse(packageData.itinerary) : packageData.itinerary) : 
    {};

  const dispatch = useDispatch();


  // Get search params and booking status from Redux store
  const { searchParams, bookingSuccess, bookingData: bookedData } = useSelector(state => state.prePackages);
  
  // Get userRole and agentId from auth slice
  const { userRole, agentId } = useSelector(state => state.auth);

  // Handle successful booking - removed notification handling since it's now handled at the page level
  useEffect(() => {
    if (bookingSuccess && bookedData) {
      setBookingComplete(true);
    }
  }, [bookingSuccess, bookedData]);


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





  // Helper functions to check transfer availability
  const hasEntryPortTransfer = () => {
    // Check if entry port transfer is available from package data
    return packageData?.entry_port_transfer === 1 ||
      packageData?.entry_port_transfer === true ||
      packageData?.entry_port === 1 ||
      packageData?.entry_port === true ||
      packageData?.has_entry_port_transfer === true ||
      packageData?.arrival_pickup === 1 || 
      packageData?.arrival_pickup === true;
  };


  const hasExitPortTransfer = () => {
    // Check if exit port transfer is available from package data
    return packageData?.exit_port_transfer === 1 ||
      packageData?.exit_port_transfer === true ||
      packageData?.exit_port === 1 ||
      packageData?.exit_port === true ||
      packageData?.has_exit_port_transfer === true ||
      packageData?.departure_service === 1 ||
      packageData?.departure_service === true;
  };


  // Helper to check if an attraction has transfer available and what type
  const getAttractionTransferType = (attractionId) => {
    // First, check attraction directly in selectedAttractions
   
  
    
    // First, check attraction directly in selectedAttractions
    const attraction = selectedAttractions.find(a => 
      (a.id === attractionId || a._id === attractionId || a.attraction_id === attractionId)
    );
    
    if (attraction) {
      // Check for transfer_type in the attraction
      if (attraction.transfer_type === 'both_way' || attraction.transfer_type === 2) return 'bidirectional';
      if (attraction.transfer_type === 'one_way' || attraction.transfer_type === 1) return 'unidirectional';
      
      // Check for transfer_available in the attraction
      if (attraction.transfer_available === 1 || attraction.transfer_available === true) 
        return 'unidirectional';
      
      // Check with_transfer property
      if (attraction.with_transfer === 2) return 'bidirectional';
      if (attraction.with_transfer === 1 || attraction.with_transfer === true) return 'unidirectional';
    }
    
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
    if (packageData?.attraction_with_transfer === 1 || packageData?.attraction_with_transfer === true) {
      return 'unidirectional';
    }


    return null; // No transfer available
  };

 
 

  // Handle booking button click
  const handleBookPackage = () => {
    // For Hotels - Use ALL hotels assigned to respective days instead of just one
    let hotelsToUse = [];
    if (selectedHotels && selectedHotels.length > 0) {
      hotelsToUse = selectedHotels.map(hotel => ({
        ...hotel,
        type: 'hotel',
        entry_port: hasEntryPortTransfer() ? 1 : null,
        exit_port: hasExitPortTransfer() ? 1 : null
      }));
    } else if (packageData.selected_hotels && packageData.selected_hotels.length > 0) {
      // No modal selection, use hotels from package data
      hotelsToUse = packageData.selected_hotels.map(hotel => ({
        ...hotel,
        type: 'hotel',
        entry_port: hasEntryPortTransfer() ? 1 : null,
        exit_port: hasExitPortTransfer() ? 1 : null
      }));
    }

    // For Guides - Include ALL guides instead of just one
    let guidesToUse = [];
    if (selectedGuides && selectedGuides.length > 0) {
      // Use all selected guides
      guidesToUse = selectedGuides.map(guide => ({
        ...guide,
        type: 'guide'
      }));
    } else if ((packageData.selected_guides && packageData.selected_guides.length > 0) ||
      (packageData.selected_guide && packageData.selected_guide.length > 0)) {
      // No modal selection, use guides from package data
      guidesToUse = packageData.selected_guides || packageData.selected_guide;
      guidesToUse = guidesToUse.map(guide => ({
        ...guide,
        type: 'guide'
      }));
    }


    // Create enhanced itinerary with services for each day
    const enhancedItinerary = itineraryDates.map(dayInfo => {
      console.log('dayInfo', dayInfo);
      console.log('dayInfo', dayInfo);
      // Start with basic day info
      const enhancedDay = {
       
       
        ...dayInfo,
        services: []
      };

      // For first day, add arrival_pickup flag
      if (dayInfo.day === 1) {
        enhancedDay.arrival_pickup = hasEntryPortTransfer() ? 1 : 0;
      }
      
      // For last day, add departure_service flag
      if (dayInfo.day === itineraryDates.length) {
        enhancedDay.departure_service = hasExitPortTransfer() ? 1 : 0;
      }

      // Add hotels if selected - but only those assigned to this specific day
      if (hotelsToUse.length > 0) {
        // Filter hotels for this day (day is 1-indexed)
        const hotelsForThisDay = hotelsToUse.filter(hotel => 
          hotel.days && Array.isArray(hotel.days) && hotel.days.includes(dayInfo.day)
        );

        if (hotelsForThisDay.length > 0) {
          hotelsForThisDay.forEach(hotel => {
            const hotelService = {
              service_type: 'hotel',
              service_id: hotel.id || hotel._id,
              service_name: hotel.name,
              details: hotel
            };

            // Add entry_port for first day if available
            if (dayInfo.day === 1 && hasEntryPortTransfer()) {
              hotelService.entry_port = 1;
            } else {
              hotelService.entry_port = 0;
            }

            // Add exit_port for last day if available
            if (dayInfo.day === itineraryDates.length && hasExitPortTransfer()) {
              hotelService.exit_port = 1;
            } else {
              hotelService.exit_port = 0;
            }

            enhancedDay.services.push(hotelService);
          });
        }
      }


      // Add attraction if booked for this day
      if (selectedAttractions && selectedAttractions.length > 0) {
        selectedAttractions.forEach(attraction => {
          const attractionId = attraction.id || attraction._id || attraction.attraction_id;
         
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
              attractionService.attraction_with_transfer = 0;
            }


            enhancedDay.services.push(attractionService);
          }
        });
      }

     


             // Add guides if selected - use day-wise guide mapping
       const dayGuide = guidesByDay[dayInfo.day - 1]; // guidesByDay uses 0-based index
       if (dayGuide) {
         enhancedDay.services.push({
           service_type: 'guide',
           service_id: dayGuide.id || dayGuide._id || dayGuide.guide_id,
           service_name: dayGuide.name,
           guide_id: dayGuide.id || dayGuide._id || dayGuide.guide_id,
           day: dayInfo.day,
           assigned_day: dayInfo.day,
           languages: dayGuide.languages || [dayGuide.language] || ['English'],
           contact_no: dayGuide.contact_no,
           experience: dayGuide.experience,
           details: dayGuide
         });
       }

      return enhancedDay;
    });

    // Determine the correct agent_id to use
    // If user is an Agent, use their own agentId; otherwise use the one from searchParams
    const effectiveAgentId = userRole === 'Agent' ? agentId : searchParams?.agent_id || null;

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
        hotels: hotelsToUse.map(hotel => ({
          ...hotel,
          type: 'hotel',
          entry_port: hasEntryPortTransfer() ? 1 : 0,
          exit_port: hasExitPortTransfer() ? 1 : 0
        })),
        attractions: selectedAttractions.length > 0 ?
          selectedAttractions.filter(attraction => {
            const attractionId = attraction.id || attraction._id || attraction.attraction_id;
            return bookedAttractions[attractionId] !== undefined;
          }).map(attraction => {
            const attractionId = attraction.id || attraction._id || attraction.attraction_id;
            const transferType = getAttractionTransferType(attractionId);

            return {
              ...attraction,
              type: 'attraction',
              attraction_with_transfer: transferType === 'bidirectional' ? 2 :
                transferType === 'unidirectional' ? 1 : 0
            };
          }) : [],
        /* restaurants: restaurantToUse ? [{ ...restaurantToUse, type: 'restaurant' }] : [], */
        guides: Object.keys(guidesByDay).map(dayIndex => {
          const guide = guidesByDay[dayIndex];
          return {
            ...guide,
            type: 'guide',
            day: parseInt(dayIndex) + 1, // Convert 0-based index to 1-based day
            assigned_day: parseInt(dayIndex) + 1,
            guide_id: guide.id || guide._id || guide.guide_id,
            name: guide.name,
            languages: guide.languages || [guide.language] || ['English'],
            contact_no: guide.contact_no,
            experience: guide.experience
          };
        })
      },
      booking_details: {
        adult_count: adultCount,
        child_count: childCount,
        male_count: maleCount,
        female_count: femaleCount,
        total_price: totalPrice,
        travel_dates: searchParams?.check_in && searchParams?.check_out ? {
          check_in: searchParams.check_in,
          check_out: searchParams.check_out
        } : null,
        package_date: searchParams?.date || packageData.date,
        date: searchParams?.date || packageData.date,
        itinerary: enhancedItinerary.map(day => {
          const originalDay = originalItinerary?.itinerary?.find(d => d.day === day.day);
        
          return {
            ...day,
            arrival_pickup: originalDay?.arrival_pickup ?? null,
            departure_service: originalDay?.departure_service ?? null,
            services: day.services.map(service => {
              if (service.service_type === 'hotel') {
                if (day.day === 1 && hasEntryPortTransfer()) {
                  service.entry_port = 1;
                }
        
                if (day.day === itineraryDates.length && hasExitPortTransfer()) {
                  service.exit_port = 1;
                }
              }
        
              if (service.service_type === 'attraction') {
                const attractionId = service.service_id;
                const transferType = getAttractionTransferType(attractionId);
        
                if (transferType === 'bidirectional') {
                  service.attraction_with_transfer = 2;
                } else if (transferType === 'unidirectional') {
                  service.attraction_with_transfer = 1;
                } else {
                  service.attraction_with_transfer = 0;
                }
              }
        
              return service;
            })
          };
        }),
        entry_port_transfer: hasEntryPortTransfer() ? 1 : 0,
        exit_port_transfer: hasExitPortTransfer() ? 1 : 0,
        has_arrival_pickup: hasEntryPortTransfer() ? 1 : 0,
        has_departure_service: hasExitPortTransfer() ? 1 : 0,
        // Use the determined agent_id
        agent_id: effectiveAgentId
      }
    };

    // Debug: Log the booking data to see what guides are being sent
    console.log('Booking Data - Guides by Day:', guidesByDay);
    console.log('Booking Data - Selected Guides:', bookingData.selected.guides);
    console.log('Booking Data - Enhanced Itinerary:', bookingData.booking_details.itinerary);
    console.log('Booking Data - Using Agent ID:', effectiveAgentId);

    // Save booking data to state and open the user info modal
    setBookingData(bookingData);
    setIsUserInfoModalOpen(true);
  };


  // Handle final form submission with user info
  const handleFormSubmit = (finalData) => {
    // Final data with user info is already logged in UserInfo component
    // You could also handle API calls or other actions here
    // console.log('Form submitted successfully');
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
              <Box component="span" fontSize="0.7rem" mr={0.3}>
                SGD
              </Box>
              {adultPrice.toFixed(2)} each
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
              <Box component="span" fontSize="0.7rem" mr={0.3}>
                SGD
              </Box>
              {childPrice.toFixed(2)} each
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
            <Box component="span" fontSize="0.7rem" mr={0.3}>
              SGD
            </Box>
            {totalPrice.toFixed(2)}
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



    </Paper>
  );
};


export default PackagePricing; 