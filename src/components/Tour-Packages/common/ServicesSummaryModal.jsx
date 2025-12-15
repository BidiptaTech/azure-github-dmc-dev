import React from 'react';
import { 
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Box, 
  Typography, 
  Grid, 
  Card, 
  CardContent, 
  Chip,
  Avatar,
  Stack,
  Badge,
  Button,
  IconButton,
  Divider
} from '@mui/material';
import { useSelector } from 'react-redux';
import HotelIcon from '@mui/icons-material/Hotel';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import AttractionsIcon from '@mui/icons-material/Attractions';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import PersonIcon from '@mui/icons-material/Person';
import AirportShuttleIcon from '@mui/icons-material/AirportShuttle';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import GroupIcon from '@mui/icons-material/Group';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import CloseIcon from '@mui/icons-material/Close';
import PrintIcon from '@mui/icons-material/Print';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import SummarizeIcon from '@mui/icons-material/Summarize';
import moment from 'moment';

const ServicesSummaryModal = ({ open, onClose }) => {
  // Get all services from Redux store
  const allServices = useSelector((state) => state.tourPackages.AllServices);
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  const selectedCity = useSelector(state => state.common?.selectedCity?.cityName);
  const selectedCountry = useSelector(state => state.common?.selectedCity?.countryName);
const PriceHide = useSelector(state => state.auth.PriceHide);
  // Generate dates array from the selected date range
  const dates = React.useMemo(() => {
    if (!searchCriteria?.checkIn || !searchCriteria?.checkOut) return [];
    
    const startDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
    const endDate = moment(searchCriteria.checkOut, 'DD/MM/YYYY');
    const dayDiff = endDate.diff(startDate, 'days');

    // Generate an array of all dates in the range
    const datesArray = [];
    for (let i = 0; i <= dayDiff; i++) {
      datesArray.push(moment(startDate).add(i, 'days'));
    }
    return datesArray;
  }, [searchCriteria]);

  // Group services by type for hotel section
  const hotelServices = allServices.filter(service => service.type === 'hotel');
  const otherServices = allServices.filter(service => service.type !== 'hotel' && service.type !== 'CustomerInfo');

  // Create comprehensive itinerary by grouping all services by date
  const itineraryByDate = React.useMemo(() => {
    const dateMap = new Map();
    
    // First, collect all services by date
    allServices.forEach(service => {
      let serviceDates = [];
      
      // Extract dates based on service type and structure
      if ((service.type === 'Hotel' || service.type === 'hotel') && service.data?.[0]?.bookingDate) {
        const bookingDate = service.data[0].bookingDate;
        if (Array.isArray(bookingDate)) {
          // For hotels, only add check-in and check-out dates
          const startDate = moment(bookingDate[0]).format('YYYY-MM-DD');
          const endDate = moment(bookingDate[1]).format('YYYY-MM-DD');
          
          // Add check-in date
          serviceDates.push(startDate);
          
          // Add check-out date (only if it's different from check-in)
          if (startDate !== endDate) {
            serviceDates.push(endDate);
          }
        } else {
          serviceDates.push(moment(bookingDate).format('YYYY-MM-DD'));
        }
      } else if (service.data?.[0]?.bookingDate) {
        // Handle single booking date
        const bookingDate = service.data[0].bookingDate;
        if (Array.isArray(bookingDate)) {
          serviceDates.push(moment(bookingDate[0]).format('YYYY-MM-DD'));
        } else {
          serviceDates.push(moment(bookingDate).format('YYYY-MM-DD'));
        }
      } else if (service.data?.[0]?.pickupdate) {
        // Handle pickup date
        serviceDates.push(moment(service.data[0].pickupdate).format('YYYY-MM-DD'));
      } else if (service.data?.[0]?.exitpickupdate) {
        // Handle exit pickup date for exit_port services
        serviceDates.push(moment(service.data[0].exitpickupdate).format('YYYY-MM-DD'));
      }
      
      // Add service to each relevant date
      serviceDates.forEach(dateStr => {
        if (!dateMap.has(dateStr)) {
          dateMap.set(dateStr, []);
        }
        dateMap.get(dateStr).push(service);
      });
    });
    
    // Generate complete trip duration from search criteria
    const completeItinerary = [];
    
    if (searchCriteria?.checkIn && searchCriteria?.checkOut) {
      const startDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
      const endDate = moment(searchCriteria.checkOut, 'DD/MM/YYYY');
      const totalDays = endDate.diff(startDate, 'days') + 1;
      
      // Generate all days from start to end
      for (let i = 0; i < totalDays; i++) {
        const currentDate = moment(startDate).add(i, 'days');
        const dateStr = currentDate.format('YYYY-MM-DD');
        
        // Get services for this date (if any)
        const dayServices = dateMap.get(dateStr) || [];
        
          // Sort services by time within each day
        const sortedServices = dayServices.sort((a, b) => {
          const getServiceTime = (service) => {
            const data = service.data?.[0];
            let timeValue = '00:00';
            
            if (data?.visitTime) timeValue = data.visitTime;
            else if (data?.entrytime) timeValue = data.entrytime;
            else if (data?.Night_Start_Time) timeValue = data.Night_Start_Time;
            else if (data?.entrypickup) timeValue = data.entrypickup;
            
            // Convert to string and handle different time formats
            const timeStr = String(timeValue);
            
            // Handle numeric time (like 9 for 9 AM)
            if (/^\d+$/.test(timeStr)) {
              const hour = parseInt(timeStr);
              return `${hour.toString().padStart(2, '0')}:00`;
            }
            
            // Handle time with AM/PM
            if (timeStr.includes('AM') || timeStr.includes('PM')) {
              return timeStr;
            }
            
            // Handle 24-hour format (HH:MM:SS)
            if (/^\d{2}:\d{2}:\d{2}$/.test(timeStr)) {
              return timeStr.substring(0, 5); // Return HH:MM
            }
            
            // Handle HH:MM format
            if (/^\d{1,2}:\d{2}$/.test(timeStr)) {
              return timeStr;
            }
            
            return timeStr;
          };
          
          const timeA = getServiceTime(a);
          const timeB = getServiceTime(b);
          
          // Convert times to 24-hour format for proper comparison
          const convertTo24Hour = (timeStr) => {
            if (!timeStr) return '00:00';
            
            const str = String(timeStr).toLowerCase();
            
            // Handle AM/PM format
            if (str.includes('am') || str.includes('pm')) {
              const [time, period] = str.split(/\s*(am|pm)\s*/);
              const [hours, minutes = '00'] = time.split(':');
              let hour24 = parseInt(hours);
              
              if (period === 'pm' && hour24 !== 12) hour24 += 12;
              if (period === 'am' && hour24 === 12) hour24 = 0;
              
              return `${hour24.toString().padStart(2, '0')}:${minutes.padStart(2, '0')}`;
            }
            
            return str;
          };
          
          const time24A = convertTo24Hour(timeA);
          const time24B = convertTo24Hour(timeB);
          
          return time24A.localeCompare(time24B);
        });
        
        // Push hotel checkout entries to the end of the day
        const isHotelCheckout = (service) => {
          if (!(service.type === 'Hotel' || service.type === 'hotel')) return false;
          const hotelData = service.data?.[0];
          const bookingDate = hotelData?.bookingDate;
          if (Array.isArray(bookingDate)) {
            const checkOutDate = moment(bookingDate[1]).format('YYYY-MM-DD');
            return currentDate.format('YYYY-MM-DD') === checkOutDate;
          }
          return false;
        };

        const nonCheckout = sortedServices.filter(svc => !isHotelCheckout(svc));
        const checkoutLast = sortedServices.filter(svc => isHotelCheckout(svc));
        const finalServices = [...nonCheckout, ...checkoutLast];
        
        completeItinerary.push({
          date: currentDate,
          services: finalServices
        });
      }
    } else {
      // Fallback to original logic if no search criteria
      completeItinerary.push(...Array.from(dateMap.entries())
        .sort(([dateA], [dateB]) => moment(dateA).diff(moment(dateB)))
        .map(([date, services]) => ({
          date: moment(date),
          services: services
        })));
    }
    
    return completeItinerary;
  }, [allServices, searchCriteria]);

  // Service type configurations
  const serviceTypeConfig = {
    Hotel: {
      icon: <HotelIcon />,
      color: '#1976d2',
      title: 'Hotel',
      bgColor: 'rgba(25, 118, 210, 0.1)'
    },
    hotel: {
      icon: <HotelIcon />,
      color: '#1976d2',
      title: 'Hotel',
      bgColor: 'rgba(25, 118, 210, 0.1)'
    },
    attraction: {
      icon: <AttractionsIcon />,
      color: '#f44336',
      title: 'Attraction',
      bgColor: 'rgba(244, 67, 54, 0.1)'
    },
    attraction_package: {
      icon: <AttractionsIcon />,
      color: '#e91e63',
      title: 'Attraction Package',
      bgColor: 'rgba(233, 30, 99, 0.1)'
    },
    restaurant: {
      icon: <RestaurantIcon />,
      color: '#ff9800',
      title: 'Restaurant',
      bgColor: 'rgba(255, 152, 0, 0.1)'
    },
    guide: {
      icon: <PersonIcon />,
      color: '#4caf50',
      title: 'Guide',
      bgColor: 'rgba(76, 175, 80, 0.1)'
    },
    travel_point: {
      icon: <DirectionsCarIcon />,
      color: '#2196f3',
      title: 'Transport',
      bgColor: 'rgba(33, 150, 243, 0.1)'
    },
    travel_hourly: {
      icon: <DirectionsCarIcon />,
      color: '#ff5722',
      title: 'Hourly Transport',
      bgColor: 'rgba(255, 87, 34, 0.1)'
    },
    local_transfer: {
      icon: <DirectionsCarIcon />,
      color: '#2196f3',
      title: 'Local Transfer',
      bgColor: 'rgba(33, 150, 243, 0.1)'
    },
    entry_port: {
      icon: <AirportShuttleIcon />,
      color: '#9c27b0',
      title: 'Entry Port',
      bgColor: 'rgba(156, 39, 176, 0.1)'
    },
    exit_port: {
      icon: <AirportShuttleIcon />,
      color: '#795548',
      title: 'Exit Port',
      bgColor: 'rgba(121, 85, 72, 0.1)'
    }
  };

  // Calculate total price
  const totalPrice = allServices.reduce((total, service) => {
    return total + (service.totalPrice || service.price || 0);
  }, 0);

  // Helper: robust price extractor for any service
  const extractServicePrice = (service) => {
    if (!service) return 0;
    // Direct total if present
    if (typeof service.totalPrice === 'number') return service.totalPrice;

    const data0 = service.data?.[0] || {};
    // Common fields
    if (typeof data0.totalPrice === 'number') return data0.totalPrice;
    if (typeof data0.price === 'number') return data0.price;

    // Restaurant variants
    if (service.type === 'restaurant') {
      const spec = data0.mealSpecificType;
      if (typeof spec === 'object' && typeof spec?.totalPrice === 'number') return spec.totalPrice;
      if (typeof data0.mealPrice === 'number') return data0.mealPrice;
    }

    // Attraction variants
    if (service.type === 'attraction' || service.type === 'attraction_package') {
      if (typeof data0.totalPrice === 'number') return data0.totalPrice;
      if (typeof data0.ticket_details?.adult_price === 'number') {
        const adults = Number(data0.adultCount || 0);
        const children = Number(data0.childCount || 0);
        const seniors = Number(data0.seniorCount || 0);
        const ap = data0.ticket_details.adult_price || 0;
        const cp = data0.ticket_details.child_price || 0;
        const sp = data0.ticket_details.senior_price || 0;
        return adults * ap + children * cp + seniors * sp;
      }
    }

    // Hotel variants: sum beds/rooms if needed
    if (service.type === 'Hotel' || service.type === 'hotel') {
      if (typeof data0.totalPrice === 'number') return data0.totalPrice;
      const rooms = Array.isArray(data0.rooms) ? data0.rooms : [];
      if (rooms.length) {
        const roomsTotal = rooms.reduce((sum, room) => {
          const beds = Array.isArray(room?.beds) ? room.beds : [];
          const bedTotal = beds.reduce((bSum, bed) => {
            const base = Number(bed.price || 0);
            const mealAdd = bed.selectedMeals
              ? Object.values(bed.selectedMeals).reduce((ms, m) => ms + Number(m?.price || 0), 0)
              : 0;
            return bSum + base + mealAdd;
          }, 0);
          return sum + bedTotal;
        }, 0);
        if (roomsTotal > 0) return roomsTotal;
      }
    }

    // Transport
    if (
      service.type === 'travel_point' ||
      service.type === 'local_transfer' ||
      service.type === 'travel_hourly' ||
      service.type === 'entry_port' ||
      service.type === 'exit_port'
    ) {
      if (typeof data0.totalPrice === 'number') return data0.totalPrice;
      if (typeof data0.price === 'number') return data0.price;
    }

    return 0;
  };

  const servicesWithPrices = React.useMemo(() => {
    return allServices.map((svc) => ({
      service: svc,
      price: extractServicePrice(svc)
    }));
  }, [allServices]);

  const computedGrandTotal = React.useMemo(() => {
    return servicesWithPrices
      .filter((item) => Number(item.price) > 0)
      .reduce((sum, item) => sum + (Number(item.price) || 0), 0);
  }, [servicesWithPrices]);

  // Function to get current destination based on hotels for a specific date
  const getCurrentDestination = (date) => {
    const dateStr = date.format('YYYY-MM-DD');
    
    // Find hotels that are active on this date
    const activeHotels = allServices.filter(service => {
      if ((service.type === 'Hotel' || service.type === 'hotel') && service.data?.[0]?.bookingDate) {
        const bookingDate = service.data[0].bookingDate;
        if (Array.isArray(bookingDate)) {
          const startDate = moment(bookingDate[0]).format('YYYY-MM-DD');
          const endDate = moment(bookingDate[1]).format('YYYY-MM-DD');
          return dateStr >= startDate && dateStr <= endDate;
        }
      }
      return false;
    });

    if (activeHotels.length > 0) {
      const hotelData = activeHotels[0].data?.[0];
      return hotelData?.hotelDetails?.location || hotelData?.location || selectedCity;
    }
    
    return selectedCity;
  };

  // Format date range for hotels
  const formatDateRange = (bookingDate) => {
    if (Array.isArray(bookingDate) && bookingDate.length === 2) {
      const startDate = moment(bookingDate[0]);
      const endDate = moment(bookingDate[1]);
      return `${startDate.format('MMM DD')} - ${endDate.format('MMM DD, YYYY')}`;
    }
    return 'Date not specified';
  };

  // Calculate nights for hotel bookings
  const calculateNights = (bookingDate) => {
    if (Array.isArray(bookingDate) && bookingDate.length === 2) {
      const startDate = moment(bookingDate[0]);
      const endDate = moment(bookingDate[1]);
      return endDate.diff(startDate, 'days');
    }
    return 0;
  };

  const handlePrint = () => {
    window.print();
  };

  // Function to capitalize first letter of each word
  const capitalizeWords = (str) => {
    if (!str) return '';
    return str.split(' ').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
    ).join(' ');
  };

  // Debug: Log services to console (commented out for production)
  // React.useEffect(() => {
  //   console.log('All Services:', allServices);
  //   console.log('Hotel Services:', hotelServices);
  //   console.log('Other Services:', otherServices);
  // }, [allServices, hotelServices, otherServices]);

  return (
    <Dialog 
      open={open} 
      onClose={onClose}
      maxWidth="lg"
      fullWidth
      PaperProps={{
        sx: { 
          borderRadius: 2,
          maxHeight: '95vh',
          bgcolor: 'white'
        }
      }}
    >
      <DialogTitle sx={{ p: 0 }}>
        <Box sx={{ 
          display: 'flex', 
          alignItems: 'center', 
          justifyContent: 'space-between',
          bgcolor: 'white',
          p: 2,
          borderBottom: '1px solid #e0e0e0'
        }}>
          <Typography variant="h6" fontWeight={600} color="text.primary">
            Trip Summary
              </Typography>
            <IconButton 
              onClick={onClose}
              sx={{ 
              color: 'text.secondary',
                '&:hover': { 
                bgcolor: 'rgba(0,0,0,0.04)'
              }
              }}
            >
              <CloseIcon />
            </IconButton>
        </Box>
      </DialogTitle>

      <DialogContent sx={{ p: 0, bgcolor: 'white' }}>
        {allServices.length === 0 ? (
          <Box sx={{ 
            textAlign: 'center', 
            py: 6,
            px: 3
          }}>
            <Typography variant="h6" color="text.secondary" gutterBottom>
                No Services Selected Yet
              </Typography>
            <Typography variant="body2" color="text.secondary">
              Start building your perfect trip by adding services.
              </Typography>
            </Box>
        ) : (
          <Grid container sx={{ height: '100%' }}>
            {/* Left Side - Itinerary */}
            <Grid item xs={12} md={8} sx={{ borderRight: '1px solid #e0e0e0' }}>
              <Box sx={{ 
                bgcolor: 'white',
                height: '100vh',
                maxHeight: '80vh',
                overflow: 'auto'
              }}>
                
                {/* Top Route Bar */}
                {/* <Box sx={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  bgcolor: '#f5f5f5',
                  p: 2,
                  borderBottom: '1px solid #e0e0e0'
                }}>
                  <DirectionsCarIcon sx={{ fontSize: 16, mr: 1, color: '#666' }} />
                  <Typography variant="body2" color="text.secondary" fontWeight={500}>
                    {selectedCity}
                  </Typography>
                </Box> */}

                {/* Main Itinerary Section */}
                <Box sx={{ p: 3 }}>
                  {/* Trip Title - Beige Section */}
                  <Box sx={{ 
                    bgcolor: '#f5e8d7', 
                    p: 2, 
                    borderRadius: 1, 
                    mb: 3,
                    border: '1px solid #e0c4a0'
                  }}>
                    <Typography variant="h6" fontWeight={600} color="text.primary">
                      {selectedCountry} - {(() => {
                        if (searchCriteria?.checkIn && searchCriteria?.checkOut) {
                          const startDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
                          const endDate = moment(searchCriteria.checkOut, 'DD/MM/YYYY');
                          const nights = endDate.diff(startDate, 'days');
                          return `${nights} Nights Stay`;
                        }
                        return '2 Nights Stay';
                      })()}
              </Typography>
            </Box>

            {/* Hotels Section - Show at top like main itinerary */}
            {/* {hotelServices.length > 0 && (
              <Box sx={{ mb: 3 }}>
                <Card elevation={1} sx={{ borderRadius: 2 }}>
                  <CardContent>
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                      <Avatar sx={{ bgcolor: '#1976d2', mr: 2, width: 32, height: 32 }}>
                        <HotelIcon sx={{ fontSize: 18 }} />
                      </Avatar>
                      <Typography variant="h6" fontWeight={600}>
                        Hotels
                      </Typography>
                      <Badge 
                        badgeContent={hotelServices.length} 
                        color="primary" 
                        sx={{ ml: 2 }}
                      />
                    </Box>

                    <Grid container spacing={2}>
                      {hotelServices.map((service, index) => {
                        const hotelData = service.data?.[0]; // Get the first hotel data object
                        const hotelDetails = hotelData?.hotelDetails;
                        const bookingDate = hotelData?.bookingDate;
                        
                        return (
                          <Grid item xs={12} key={index}>
                            <Card variant="outlined" sx={{ mb: 1 }}>
                              <CardContent sx={{ p: 1.5, '&:last-child': { pb: 1.5 } }}>
                                                              <Grid container spacing={2}>
                        
                                <Grid item xs={12} sm={3} md={2}>
                                  <Box sx={{ position: 'relative', height: 120, maxHeight: 120 }}>
                                    {hotelDetails?.image ? (
                                      <Box component="img" 
                                        src={hotelDetails.image} 
                                        alt={hotelDetails.hotel_name}
                                        sx={{
                                          width: '100%',
                                          height: '100%',
                                          objectFit: 'cover',
                                          borderRadius: 1
                                        }}
                                      />
                                    ) : (
                                      <Box 
                                        sx={{
                                          width: '100%', 
                                          height: '100%', 
                                          bgcolor: 'rgba(25, 118, 210, 0.1)',
                                          display: 'flex',
                                          alignItems: 'center',
                                          justifyContent: 'center',
                                          borderRadius: 1
                                        }}
                                      >
                                        <HotelIcon sx={{ fontSize: 32, color: '#1976d2' }} />
                                      </Box>
                                    )}
                                  </Box>
                                </Grid>
                                  
                                                                 
                                <Grid item xs={12} sm={9} md={10}>
                                  <Stack spacing={0.5}>
                                 
                                    <Box>
                                      <Typography variant="h6" fontWeight={600} sx={{ mb: 0.5 }}>
                                        {hotelDetails?.hotel_name || 'Hotel'}
                                      </Typography>
                                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
                                        <LocationOnIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.8rem' }}>
                                          {hotelDetails?.location || 'Location not specified'}
                                        </Typography>
                                      </Box>
                                    </Box>
                                
                                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1.5 }}>
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <CalendarTodayIcon sx={{ fontSize: 14, mr: 0.5, color: 'primary.main' }} />
                                        <Typography variant="body2" sx={{ fontSize: '0.8rem' }}>
                                          {formatDateRange(bookingDate)} ({calculateNights(bookingDate)} nights)
                                        </Typography>
                                      </Box>
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <AccessTimeIcon sx={{ fontSize: 14, mr: 0.5, color: 'primary.main' }} />
                                        <Typography variant="body2" sx={{ fontSize: '0.8rem' }}>
                                          Check-in: {hotelDetails?.checkInTime ? hotelDetails.checkInTime.substring(0, 5) : 'N/A'} | 
                                          Check-out: {hotelDetails?.checkOutTime ? hotelDetails.checkOutTime.substring(0, 5) : 'N/A'}
                                        </Typography>
                                      </Box>
                                    </Box>
                                      
                                   
                                      {hotelData?.rooms && hotelData.rooms.map((room, roomIndex) => (
                                        <Box key={roomIndex} sx={{ bgcolor: 'rgba(25, 118, 210, 0.05)', p: 1.5, borderRadius: 1.5, mt: 1, border: '1px solid rgba(25, 118, 210, 0.2)' }}>
                                          <Typography variant="subtitle2" fontWeight={600} gutterBottom sx={{ color: '#1976d2', mb: 1, fontSize: '0.9rem' }}>
                                            🏨 Room {roomIndex + 1}: {room.room_type || 'Standard'} Room
                                          </Typography>
                                          
                                     
                                          {room.beds && room.beds.map((bed, bedIndex) => (
                                            <Box key={bedIndex} sx={{ bgcolor: 'white', p: 1.5, borderRadius: 1, mb: 1.5, boxShadow: '0 1px 2px rgba(0,0,0,0.08)' }}>
                                              <Grid container spacing={1.5}>
                                             
                                                <Grid item xs={12} md={6}>
                                                  <Stack spacing={0.5}>
                                                    <Typography variant="body2" fontWeight={600} sx={{ color: '#333', display: 'flex', alignItems: 'center', fontSize: '0.85rem' }}>
                                                      🛏️ {bed.bed_type || 'Standard Bed'}
                                                      {bed.baby_cot === 1 && (
                                                        <Chip label="Baby Cot" size="small" color="secondary" sx={{ ml: 1, fontSize: '0.7rem', height: 20 }} />
                                                      )}
                                                    </Typography>
                                                    
                                                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                                        <GroupIcon sx={{ fontSize: 14, mr: 0.5, color: 'primary.main' }} />
                                                        <Typography variant="body2" fontWeight={500} sx={{ fontSize: '0.8rem' }}>
                                                          {bed.head_count || 0} Guest{(bed.head_count || 0) !== 1 ? 's' : ''}
                                                        </Typography>
                                                      </Box>
                                                      
                                                    
                                                      
                                                      {bed.price > 0 && (
                                                        <Chip 
                                                          label={`$${bed.price}`} 
                                                          size="small" 
                                                          color="primary" 
                                                          variant="outlined"
                                                          sx={{ fontSize: '0.7rem', height: 20 }}
                                                        />
                                                      )}
                                                    </Box>
                                                  </Stack>
                                                </Grid>
                                                
                                               
                                                <Grid item xs={12} md={6}>
                                                  <Typography variant="body2" fontWeight={600} gutterBottom sx={{ color: '#666', fontSize: '0.8rem' }}>
                                                    🍽️ Meal Plans:
                                                  </Typography>
                                                  
                                                  {bed.selectedMeals ? (
                                                    <Stack spacing={0.3}>
                                                      {Object.entries(bed.selectedMeals).map(([mealKey, meal], mealIndex) => (
                                                        <Box key={mealKey} sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                                          <Typography variant="body2" sx={{ fontSize: '0.75rem' }}>
                                                            Guest {mealIndex + 1}: {meal.type || 'Room Only'}
                                                          </Typography>
                                                          {meal.price > 0 && (
                                                            <Typography variant="body2" color="success.main" fontWeight={500} sx={{ fontSize: '0.75rem' }}>
                                                              +${meal.price}
                                                            </Typography>
                                                          )}
                                                        </Box>
                                                      ))}
                                                    </Stack>
                                                  ) : (
                                                    <Typography variant="body2" color="text.secondary" sx={{ fontStyle: 'italic', fontSize: '0.75rem' }}>
                                                      {bed.mealTypes?.map((meal, idx) => `Guest ${idx + 1}: ${meal}`).join(', ') || 'Room Only'}
                                                    </Typography>
                                                  )}
                                                </Grid>
                                              </Grid>
                                             
                                              {(bed.price > 0 || (bed.selectedMeals && Object.values(bed.selectedMeals).some(meal => meal.price > 0))) && (
                                                <Divider sx={{ my: 0.5 }} />
                                              )}
                                              {(bed.price > 0 || (bed.selectedMeals && Object.values(bed.selectedMeals).some(meal => meal.price > 0))) && (
                                                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                                  <Typography variant="body2" fontWeight={600} sx={{ fontSize: '0.8rem' }}>
                                                    Bed Total:
                                                  </Typography>
                                                  <Typography variant="body2" color="primary.main" fontWeight={600} sx={{ fontSize: '0.85rem' }}>
                                                    ${(
                                                      (bed.price || 0) + 
                                                      (bed.selectedMeals ? Object.values(bed.selectedMeals).reduce((sum, meal) => sum + (meal.price || 0), 0) : 0)
                                                    ).toLocaleString()}
                                                  </Typography>
                                                </Box>
                                              )}
                                            </Box>
                                          ))}
                                          
                                         
                                          <Divider sx={{ my: 0.8 }} />
                                          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', bgcolor: 'rgba(25, 118, 210, 0.1)', p: 1, borderRadius: 1 }}>
                                            <Typography variant="body2" fontWeight={600} sx={{ color: '#1976d2', fontSize: '0.85rem' }}>
                                              Room {roomIndex + 1} Total:
                                            </Typography>
                                            <Typography variant="subtitle1" color="primary.main" fontWeight={700} sx={{ fontSize: '0.95rem' }}>
                                              ${(() => {
                                                const roomTotal = room.beds?.reduce((roomSum, bed) => {
                                                  const bedPrice = bed.price || 0;
                                                  const mealPrice = bed.selectedMeals ? 
                                                    Object.values(bed.selectedMeals).reduce((mealSum, meal) => mealSum + (meal.price || 0), 0) : 0;
                                                  return roomSum + bedPrice + mealPrice;
                                                }, 0) || 0;
                                                return roomTotal.toLocaleString();
                                              })()}
                                            </Typography>
                                          </Box>
                                        </Box>
                                      ))}
                                      
                               
                                      <Box sx={{ mt: 2, p: 1.5, bgcolor: 'linear-gradient(135deg, #1976d2 0%, #42a5f5 100%)', background: 'linear-gradient(135deg, #1976d2 0%, #42a5f5 100%)', borderRadius: 2, color: 'white' }}>
                                        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                          <Box>
                                            <Typography variant="subtitle1" fontWeight={600} sx={{ fontSize: '1rem' }}>
                                              🏨 Hotel Grand Total
                                            </Typography>
                                            <Typography variant="body2" sx={{ opacity: 0.9, color: 'white', fontSize: '0.8rem' }}>
                                              {hotelData?.rooms?.length || 0} Room{(hotelData?.rooms?.length || 0) !== 1 ? 's' : ''} • {calculateNights(bookingDate)} Night{calculateNights(bookingDate) !== 1 ? 's' : ''}
                                            </Typography>
                                          </Box>
                                          <Typography variant="h6" fontWeight={700} sx={{ fontSize: '1.1rem' }}>
                                            ${(() => {
                                              // Calculate total from all rooms, beds, and meals
                                              const calculatedTotal = hotelData?.rooms?.reduce((hotelSum, room) => {
                                                return hotelSum + (room.beds?.reduce((roomSum, bed) => {
                                                  const bedPrice = bed.price || 0;
                                                  const mealPrice = bed.selectedMeals ? 
                                                    Object.values(bed.selectedMeals).reduce((mealSum, meal) => mealSum + (meal.price || 0), 0) : 0;
                                                  return roomSum + bedPrice + mealPrice;
                                                }, 0) || 0);
                                              }, 0) || 0;
                                              
                                              // Use calculated total if available, otherwise fall back to provided totals
                                              const finalTotal = calculatedTotal > 0 ? calculatedTotal : (hotelData?.totalPrice || service.totalPrice || service.price || 0);
                                              return finalTotal.toLocaleString();
                                            })()}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Stack>
                                  </Grid>
                                </Grid>
                              </CardContent>
                            </Card>
                          </Grid>
                        );
                      })}
                    </Grid>
                  </CardContent>
                </Card>
              </Box>
            )} */}

                  {/* Day-wise Itinerary */}
                  {itineraryByDate.length > 0 && (
              <Box>
                {itineraryByDate.map((dayData, dayIndex) => {
                  const { date, services } = dayData;
                  
                  return (
                          <Box key={dayIndex} sx={{ 
                            borderBottom: dayIndex < itineraryByDate.length - 1 ? '1px solid #e0e0e0' : 'none',
                            pb: 3,
                            mb: 3
                          }}>
                            {/* Day Row */}
                            <Box sx={{ 
                              display: 'flex', 
                              alignItems: 'flex-start'
                            }}>
                              {/* Left Column - Day Info */}
                              <Box sx={{ 
                                minWidth: 100,
                                mr: 3,
                                borderRight: '1px solid #e0e0e0',
                                pr: 2
                              }}>
                                <Typography variant="subtitle1" fontWeight={600} color="text.primary" sx={{ mb: 0.5 }}>
                                  Day {dayIndex + 1}
                        </Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.85rem' }}>
                                  {date.format('MMM DD, ddd')}
                        </Typography>
                      </Box>

                              {/* Right Column - Activities */}
                              <Box sx={{ flex: 1 }}>
                        {services.map((service, serviceIndex) => {
                          const config = serviceTypeConfig[service.type] || {
                            icon: <DirectionsCarIcon />,
                            color: '#666',
                            title: service.type?.charAt(0).toUpperCase() + service.type?.slice(1) || 'Service',
                            bgColor: 'rgba(102, 102, 102, 0.1)'
                          };

                          const serviceData = service.data?.[0];
                          
                          // Get service details based on type
                          const getServiceDetails = () => {
                            switch (service.type) {
                              case 'Hotel':
                              case 'hotel':
                                return {
                                  name: service.data?.[0]?.hotelDetails?.hotel_name || 'Hotel',
                                  location: service.data?.[0]?.hotelDetails?.location,
                                  time: `Check-in: ${service.data?.[0]?.hotelDetails?.checkInTime?.substring(0, 5) || 'N/A'} | Check-out: ${service.data?.[0]?.hotelDetails?.checkOutTime?.substring(0, 5) || 'N/A'}`,
                                  extra: `Room Type: ${service.data?.[0]?.rooms?.[0]?.room_type || 'Standard'}`,
                                  price: service.totalPrice || service.data?.[0]?.totalPrice
                                };
                              case 'attraction':
                                return {
                                  name: serviceData?.AttractionName || 'Attraction',
                                  location: serviceData?.location,
                                  time: serviceData?.visitTime || 'Time not specified',
                                  extra: `Adults: ${serviceData?.adultCount || 0}, Children: ${serviceData?.childCount || 0}, Seniors: ${serviceData?.seniorCount || 0}`,
                                  price: serviceData?.totalPrice
                                };
                              case 'attraction_package':
                                return {
                                  name: capitalizeWords(serviceData?.ticketName || serviceData?.package_details?.package_name || 'Attraction Package'),
                                  location: serviceData?.location,
                                  time: serviceData?.visitTime || 'Time not specified',
                                  extra: `${serviceData?.AttractionName || ''} | Adults: ${serviceData?.adultCount || 0}, Children: ${serviceData?.childCount || 0}, Seniors: ${serviceData?.seniorCount || 0}`,
                                  price: serviceData?.totalPrice,
                                  description: serviceData?.package_details?.package_description || serviceData?.ticket_details?.description,
                                  packageDetails: serviceData?.package_details,
                                  ticketDetails: serviceData?.ticket_details
                                };
                              case 'restaurant':
                                {
                                  const mealType = serviceData?.mealType
                                    || (typeof serviceData?.mealSpecificType === 'string' ? serviceData?.mealSpecificType : serviceData?.mealSpecificType?.mealType)
                                    || serviceData?.mealSpecificType?.meal_type
                                    || 'Meal';
                                  const mealCategory = Array.isArray(serviceData?.MealDescription) && serviceData.MealDescription[0]?.category
                                    ? serviceData.MealDescription[0].category
                                    : undefined;
                                return {
                                  name: serviceData?.restaurantName || 'Restaurant',
                                  location: serviceData?.city,
                                  time: serviceData?.visitTime || 'Time not specified',
                                    extra: mealCategory ? `${mealType}: ${mealCategory}` : `${mealType}`,
                                    price: serviceData?.totalPrice || (typeof serviceData?.mealSpecificType === 'object' ? serviceData?.mealSpecificType?.totalPrice : undefined)
                                };
                                }
                              case 'guide':
                                return {
                                  name: serviceData?.guide_name || 'Guide',
                                  location: serviceData?.city,
                                  time: `${serviceData?.entrypickup || 'N/A'} | ${serviceData?.hours || 0} hours`,
                                  extra: `Experience: ${serviceData?.experience || 0} years | Languages: ${serviceData?.languages?.map(l => l.language).join(', ') || 'Not specified'}`,
                                  price: serviceData?.totalPrice
                                };
                              case 'travel_point':
                                return {
                                  name: `${serviceData?.vehicles_name || 'Vehicle'} - ${serviceData?.type || 'Transfer'}`,
                                  location: `${serviceData?.entrypickup} → ${serviceData?.entrydropoff}`,
                                  time: `${serviceData?.entrytime || 'N/A'} | Duration: ${serviceData?.selectedHours || 0} hours`,
                                  extra: `Distance: ${serviceData?.distance || 0} km | Adults: ${serviceData?.adults || 0}`,
                                  price: serviceData?.totalPrice
                                };
                              case 'travel_hourly':
                                return {
                                  name: `${serviceData?.vehicles_name || 'Vehicle'} - Hourly Transport`,
                                  //location: `${serviceData?.entrypickup} → ${serviceData?.entrydropoff}`|| 'Pickup location not specified',
                                  time: `${serviceData?.entrytime || 'N/A'} | Duration: ${serviceData?.selectedHours || 0} hours`,
                                  extra: `${serviceData?.type || 'Private'} Vehicle | Adults: ${serviceData?.adults || 0}`,
                                  price: serviceData?.totalPrice
                                };
                              case 'local_transport':
                                return {
                                  name: `${serviceData?.vehicles_name || 'Vehicle'} - Local Transfer`,
                                  location: `${serviceData?.entrypickup} → ${serviceData?.entrydropoff}`,
                                  time: `${serviceData?.entrytime || 'N/A'}`,
                                  extra: `${serviceData?.type || 'Private'} Vehicle | Adults: ${serviceData?.adults || 0}`,
                                  price: serviceData?.totalPrice
                                };
                              case 'entry_port':
                                return {
                                  name: `${serviceData?.vehicles_name || 'Vehicle'} - Entry Port Transfer`,
                                  location: `${serviceData?.entrypickup} → ${serviceData?.entrydropoff}`,
                                  time: `${serviceData?.entrytime || 'N/A'} | ${serviceData?.Night_Start_Time?.substring(0, 5) || ''} - ${serviceData?.Night_End_Time?.substring(0, 5) || ''}`,
                                  extra: `${serviceData?.vehicle_type || ''} | Distance: ${serviceData?.distance || 0} km`,
                                  price: serviceData?.totalPrice
                                };
                              case 'exit_port':
                                return {
                                  name: `${serviceData?.vehicles_name || 'Vehicle'} - Exit Port Transfer`,
                                  location: `${serviceData?.exitpickup} → ${serviceData?.exitdropoff}`,
                                  time: `${serviceData?.entrytime || 'N/A'} | ${serviceData?.Night_Start_Time?.substring(0, 5) || ''} - ${serviceData?.Night_End_Time?.substring(0, 5) || ''}`,
                                  extra: `${serviceData?.vehicle_type || serviceData?.type} | Distance: ${serviceData?.distance || 0} km | Adults: ${serviceData?.adults || 0}`,
                                  price: serviceData?.totalPrice
                                };
                              default:
                                return {
                                  name: 'Service',
                                  location: 'Location not specified',
                                  time: 'Time not specified',
                                  extra: '',
                                  price: 0
                                };
                            }
                          };

                          const details = getServiceDetails();

                          return (
                            <React.Fragment key={serviceIndex}>
                              {/* Service Card */}
                              <Box sx={{ 
                                display: 'flex',
                                alignItems: 'center',
                                p: 2,
                                mb: serviceIndex < services.length - 1 ? 1.5 : 0,
                                bgcolor: 'rgba(0, 0, 0, 0.02)',
                                borderRadius: 1,
                                border: '1px solid rgba(0, 0, 0, 0.08)',
                                transition: 'all 0.2s ease',
                                '&:hover': {
                                  bgcolor: 'rgba(0, 0, 0, 0.04)',
                                  borderColor: 'rgba(0, 0, 0, 0.12)'
                                }
                              }}>
                                {/* Service Icon */}
                                <Box sx={{ 
                                  mr: 2,
                                  display: 'flex',
                                  alignItems: 'center',
                                  justifyContent: 'center',
                                  minWidth: 40,
                                  height: 40,
                                  bgcolor: config.bgColor || 'rgba(102, 102, 102, 0.1)',
                                  borderRadius: '50%',
                                  border: `2px solid ${config.color || '#666'}`
                                }}>
                                  {React.cloneElement(config.icon, { 
                                    sx: { 
                                      fontSize: 20, 
                                      color: config.color || '#666' 
                                    } 
                                  })}
                                </Box>
                                
                                {/* Service Details */}
                                <Box sx={{ flex: 1, minWidth: 0 }}>
                                  <Typography variant="subtitle2" fontWeight={600} color="text.primary" sx={{ 
                                    mb: 0.5,
                                    fontSize: '0.95rem',
                                    lineHeight: 1.3
                                  }}>
                                    {(() => {
                                      switch (service.type) {
                                        case 'Hotel':
                                        case 'hotel':
                                          // Determine if this is check-in or check-out
                                          const hotelData = service.data?.[0];
                                          const bookingDate = hotelData?.bookingDate;
                                          const currentDate = dayData.date.format('YYYY-MM-DD');
                                          
                                          if (Array.isArray(bookingDate)) {
                                            const checkInDate = moment(bookingDate[0]).format('YYYY-MM-DD');
                                            const checkOutDate = moment(bookingDate[1]).format('YYYY-MM-DD');
                                            
                                            if (currentDate === checkInDate) {
                                              return `Check in to ${details.name}`;
                                            } else if (currentDate === checkOutDate) {
                                              return `Checkout from ${details.name}`;
                                            }
                                          }
                                          return `Check in to ${details.name}`;
                                        case 'attraction':
                                          return `${details.name}`;
                                        case 'restaurant':
                                          const currentDestination = getCurrentDestination(dayData.date);
                                          const dataObj = service.data?.[0] || {};
                                          const mealTypeBase = dataObj.mealType 
                                            || (Array.isArray(dataObj.MealDescription) && dataObj.MealDescription[0]?.category)
                                            || dataObj.foodType 
                                            || dataObj.food_type 
                                            || 'Meal';
                                          const mealSpecific = typeof dataObj.mealSpecificType === 'string'
                                            ? dataObj.mealSpecificType
                                            : (dataObj.mealSpecificType?.mealType || dataObj.mealSpecificType?.meal_type);
                                          const mealLabel = mealSpecific && mealSpecific.toLowerCase() !== (mealTypeBase || '').toLowerCase()
                                            ? `${mealTypeBase} (${mealSpecific})`
                                            : `${mealTypeBase}`;
                                          const rName = dataObj.restaurantName || 'Restaurant';
                                          return `Day Meals: ${mealLabel}: Included at ${rName}`;
                                        case 'travel_point':
                                        case 'local_transfer':
                                          return details.name;
                                        default:
                                          return details.name;
                                      }
                                    })()}
                                  </Typography>
                                  
                                  {/* Service Time and Additional Info */}
                                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, flexWrap: 'wrap' }}>
                                    {details.time && details.time !== 'Time not specified' && (
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <AccessTimeIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                        <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.75rem' }}>
                                          {details.time}
                                        </Typography>
                                      </Box>
                                    )}
                                    
                                    {details.location && (
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <LocationOnIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                        <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.75rem' }}>
                                          {details.location}
                                        </Typography>
                                      </Box>
                                    )}
                                    
                                    {details.extra && (
                                      <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.75rem' }}>
                                        {details.extra}
                                      </Typography>
                                    )}
                                  </Box>
                                </Box>
                                
                                {/* Service Price */}
                                {details.price && details.price > 0 && (
                                  <Box sx={{ ml: 2, textAlign: 'right' }}>
                                    {PriceHide === "0" && (  
                                    <Typography variant="subtitle2" fontWeight={600} color="primary.main" sx={{ fontSize: '0.9rem' }}>
                                      SGD {Number(details.price).toLocaleString()}
                                    </Typography>
                                  ) }
                                  </Box>
                                )}
                              </Box>
                              
                              {/* Horizontal Divider - Show between services, not after the last one */}
                              {serviceIndex < services.length - 1 && (
                                <Divider sx={{ 
                                  mx: 2, 
                                  my: 1,
                                  borderColor: 'rgba(0, 0, 0, 0.08)'
                                }} />
                              )}
                            </React.Fragment>
                          );
                        })}
                            </Box>
                          </Box>
                        </Box>
                      );
                    })}
                  </Box>
                )}
                
                  {itineraryByDate.length === 0 && (
                    <Box sx={{ textAlign: 'center', py: 6, px: 3 }}>
                      <Typography variant="h6" color="text.secondary" gutterBottom>
                        No itinerary available
                                              </Typography>
                      <Typography variant="body2" color="text.secondary">
                        Add services with booking dates to see your day-wise itinerary here.
                                                    </Typography>
                                                    </Box>
                  )}
                                              </Box>
                                              
                {/* Bottom Route Bar */}
                <Box sx={{ 
                                                                  display: 'flex',
                                                                  alignItems: 'center',
                  bgcolor: '#f5f5f5',
                  p: 2,
                  borderTop: '1px solid #e0e0e0'
                }}>
                  <DirectionsCarIcon sx={{ fontSize: 16, mr: 1, color: '#666' }} />
                  <Typography variant="body2" color="text.secondary" fontWeight={500}>
                    {selectedCity} 
                                                              </Typography>
                                                            </Box>
                                                </Box>
            </Grid>

            {/* Right Side - Pricing */}
            <Grid item xs={12} md={4}>
              <Box sx={{ 
                p: 3,
                position: 'sticky', 
                top: 0, 
                bgcolor: '#fafafa', 
                height: '100vh',
                maxHeight: '100vh',
                overflow: 'hidden',
                borderLeft: '1px solid #e0e0e0',
                zIndex: 1
              }}>
                <Typography variant="h6" fontWeight={700} sx={{ mb: 2 }}>
                  Price Summary
                </Typography>

                <Box sx={{ maxHeight: '60vh', overflow: 'auto', pr: 1 }}>
                  {servicesWithPrices.filter(({ price }) => Number(price) > 0).map(({ service, price }, idx) => {
                    const data0 = service.data?.[0] || {};
                    // Friendly name per service type
                    let label = service.type;
                    if (service.type === 'hotel' || service.type === 'Hotel') {
                      label = data0?.hotelDetails?.hotel_name || 'Hotel';
                    } else if (service.type === 'attraction') {
                      label = data0?.AttractionName || data0?.ticketName || 'Attraction';
                    } else if (service.type === 'attraction_package') {
                      label = data0?.package_details?.package_name || data0?.ticketName || 'Attraction Package';
                    } else if (service.type === 'restaurant') {
                      label = data0?.restaurantName || 'Restaurant';
                    } else if (service.type === 'travel_point' || service.type === 'local_transfer' || service.type === 'travel_hourly') {
                      label = `${data0?.vehicles_name || 'Transport'}${data0?.type ? ' - ' + data0.type : ''}`;
                    } else if (service.type === 'entry_port') {
                      label = 'Entry Port Transfer';
                    } else if (service.type === 'exit_port') {
                      label = 'Exit Port Transfer';
                    } else if (service.type) {
                      label = service.type.toString().replace(/_/g, ' ');
                    }

                    const iconConfig = serviceTypeConfig[service.type];
 
                    return (
                      <Box key={idx} sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', py: 1.2, borderBottom: '1px dashed #e5e5e5' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', pr: 2, minWidth: 0 }}>
                          <Box sx={{ mr: 1 }}>
                            {iconConfig ? React.cloneElement(iconConfig.icon, { sx: { fontSize: 16, color: '#666' } }) : <DirectionsCarIcon sx={{ fontSize: 16, color: '#666' }} />}
                          </Box>
                          <Typography variant="body2" color="text.secondary" noWrap>
                            {label}
                          </Typography>
                        </Box>
                        {PriceHide === "0" && (
                         <Typography variant="body2" fontWeight={600}>
                           {Number(price || 0).toLocaleString()} SGD
                                              </Typography>
                                              )}
                                            </Box>
                          );
                        })}
                    </Box>

                <Divider sx={{ my: 2 }} />

                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <Typography variant="subtitle1" fontWeight={700}>
                    Grand Total
                      </Typography>
                      {PriceHide === "0" && (
                   <Typography variant="h6" fontWeight={800} color="primary.main">
                     SGD {Number(computedGrandTotal || 0).toLocaleString()}
                      </Typography>
                      )}
                </Box>
              </Box>
                    </Grid>
         
                    </Grid>
        )}
      </DialogContent>

    </Dialog>
  );
};

export default ServicesSummaryModal;