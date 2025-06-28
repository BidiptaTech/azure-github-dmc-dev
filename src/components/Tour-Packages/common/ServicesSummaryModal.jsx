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
import moment from 'moment';

const ServicesSummaryModal = ({ open, onClose }) => {
  // Get all services from Redux store
  const allServices = useSelector((state) => state.tourPackages.AllServices);
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  const selectedCity = useSelector(state => state.common?.selectedCity?.cityName);
  const selectedCountry = useSelector(state => state.common?.selectedCity?.countryName);

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
    
    allServices.forEach(service => {
      let serviceDates = [];
      
      // Extract dates based on service type and structure
      if (service.type === 'Hotel' && service.data?.[0]?.bookingDate) {
        const bookingDate = service.data[0].bookingDate;
        if (Array.isArray(bookingDate)) {
          // For hotels, create entries for each day of stay
          const startDate = moment(bookingDate[0]);
          const endDate = moment(bookingDate[1]);
          const daysDiff = endDate.diff(startDate, 'days');
          
          for (let i = 0; i < daysDiff; i++) {
            serviceDates.push(moment(startDate).add(i, 'days').format('YYYY-MM-DD'));
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
    
    // Convert to array and sort by date
    return Array.from(dateMap.entries())
      .sort(([dateA], [dateB]) => moment(dateA).diff(moment(dateB)))
      .map(([date, services]) => ({
        date: moment(date),
        services: services.sort((a, b) => {
          // Sort services by time within each day
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
        })
      }));
  }, [allServices]);

  // Service type configurations
  const serviceTypeConfig = {
    Hotel: {
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

  // Debug: Log services to console
  React.useEffect(() => {
    console.log('All Services:', allServices);
    console.log('Hotel Services:', hotelServices);
    console.log('Other Services:', otherServices);
  }, [allServices, hotelServices, otherServices]);

  return (
    <Dialog 
      open={open} 
      onClose={onClose}
      maxWidth="lg"
      fullWidth
      PaperProps={{
        sx: { 
          borderRadius: 3,
          maxHeight: '90vh'
        }
      }}
    >
      <DialogTitle sx={{ pb: 1 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <Typography variant="h5" fontWeight={600}>
              Trip Services Summary
            </Typography>
            {allServices.length > 0 && (
              <Chip 
                label={`${allServices.length} Service${allServices.length !== 1 ? 's' : ''}`} 
                color="primary" 
                sx={{ ml: 2 }} 
              />
            )}
          </Box>
          <Box>
            {allServices.length > 0 && (
              <IconButton onClick={handlePrint} sx={{ mr: 1 }}>
                <PrintIcon />
              </IconButton>
            )}
            <IconButton onClick={onClose}>
              <CloseIcon />
            </IconButton>
          </Box>
        </Box>
      </DialogTitle>

      <DialogContent sx={{ pt: 2 }}>
        {allServices.length === 0 ? (
          <Box sx={{ textAlign: 'center', py: 6 }}>
            <Typography variant="h6" color="text.secondary" gutterBottom>
              No services selected yet
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Start adding hotels, attractions, restaurants, and other services to see your trip summary here.
            </Typography>
          </Box>
        ) : (
          <>
            {/* Trip Overview */}
            <Box sx={{ mb: 3, p: 3, bgcolor: 'rgba(33, 150, 243, 0.05)', borderRadius: 2 }}>
              <Grid container spacing={3}>
                <Grid item xs={12} md={8}>
                  <Typography variant="h6" gutterBottom>
                    {selectedCity}, {selectedCountry}
                  </Typography>
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, flexWrap: 'wrap' }}>
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                      <CalendarTodayIcon sx={{ fontSize: 16, mr: 0.5, color: 'text.secondary' }} />
                      <Typography variant="body2" color="text.secondary">
                        {searchCriteria?.checkIn} - {searchCriteria?.checkOut}
                      </Typography>
                    </Box>
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                      <GroupIcon sx={{ fontSize: 16, mr: 0.5, color: 'text.secondary' }} />
                      <Typography variant="body2" color="text.secondary">
                        {searchCriteria.guests?.adults || 1} Adult{parseInt(searchCriteria.guests?.adults) !== 1 ? 's' : ''}
                        {searchCriteria.guests?.children > 0 ? ` • ${searchCriteria.guests.children} Child${parseInt(searchCriteria.guests.children) !== 1 ? 'ren' : ''}` : ''}
                      </Typography>
                    </Box>
                  </Box>
                </Grid>
                <Grid item xs={12} md={4} sx={{ textAlign: { xs: 'left', md: 'right' } }}>
                  {totalPrice > 0 && (
                    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: { xs: 'flex-start', md: 'flex-end' } }}>
                      <AttachMoneyIcon sx={{ color: 'success.main', mr: 0.5 }} />
                      <Typography variant="h6" color="success.main" fontWeight={600}>
                        ${totalPrice.toLocaleString()}
                      </Typography>
                    </Box>
                  )}
                </Grid>
              </Grid>
            </Box>

            {/* Hotels Section - Show at top like main itinerary */}
            {hotelServices.length > 0 && (
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
                      {hotelServices.map((service, index) => (
                        <Grid item xs={12} key={index}>
                          <Card variant="outlined" sx={{ mb: 1 }}>
                            <CardContent sx={{ p: 2, '&:last-child': { pb: 2 } }}>
                              <Grid container spacing={2}>
                                {/* Hotel Image and Main Info */}
                                <Grid item xs={12} sm={4} md={3}>
                                  <Box sx={{ position: 'relative', height: '100%', minHeight: 140 }}>
                                    {service.hotelDetails?.image ? (
                                      <Box component="img" 
                                        src={service.hotelDetails?.image} 
                                        alt={service.hotelDetails?.hotel_name}
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
                                        <HotelIcon sx={{ fontSize: 40, color: '#1976d2' }} />
                                      </Box>
                                    )}
                                  </Box>
                                </Grid>
                                
                                {/* Hotel Details */}
                                <Grid item xs={12} sm={8} md={9}>
                                  <Stack spacing={1}>
                                    {/* Hotel Name and Location */}
                                    <Box>
                                      <Typography variant="h6" fontWeight={600}>
                                        {service.hotelDetails?.hotel_name || 'Hotel'}
                                      </Typography>
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <LocationOnIcon sx={{ fontSize: 16, mr: 0.5, color: 'text.secondary' }} />
                                        <Typography variant="body2" color="text.secondary">
                                          {service.hotelDetails?.location || 'Location not specified'}
                                        </Typography>
                                      </Box>
                                    </Box>
                                    
                                    {/* Date and Check-in/out Times */}
                                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 2 }}>
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <CalendarTodayIcon sx={{ fontSize: 16, mr: 0.5, color: 'primary.main' }} />
                                        <Typography variant="body2">
                                          {formatDateRange(service.bookingDate)} ({calculateNights(service.bookingDate)} nights)
                                        </Typography>
                                      </Box>
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <AccessTimeIcon sx={{ fontSize: 16, mr: 0.5, color: 'primary.main' }} />
                                        <Typography variant="body2">
                                          Check-in: {service.hotelDetails?.checkInTime ? service.hotelDetails.checkInTime.substring(0, 5) : 'N/A'} | 
                                          Check-out: {service.hotelDetails?.checkOutTime ? service.hotelDetails.checkOutTime.substring(0, 5) : 'N/A'}
                                        </Typography>
                                      </Box>
                                    </Box>
                                    
                                    {/* Room Details */}
                                    {service.hotelDetails?.rooms && service.hotelDetails.rooms.map((room, roomIndex) => (
                                      <Box key={roomIndex} sx={{ bgcolor: 'rgba(25, 118, 210, 0.05)', p: 1.5, borderRadius: 1, mt: 1 }}>
                                        <Typography variant="subtitle2" fontWeight={600} gutterBottom>
                                          {room.room_type || 'Standard'} Room
                                        </Typography>
                                        
                                        {/* Bed Information */}
                                        <Box sx={{ mb: 1 }}>
                                          <Typography variant="body2" color="text.secondary">
                                            <strong>Beds:</strong> {room.beds?.map(bed => `${bed.bed_type} (Max: ${bed.max_occupancy})`).join(', ')}
                                          </Typography>
                                        </Box>
                                        
                                        {/* Meal Information */}
                                        <Box sx={{ mb: 1 }}>
                                          <Typography variant="body2" color="text.secondary">
                                            <strong>Meal Plan:</strong> {room.selectedMeals?.meal_1?.type || 'Room Only'}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    ))}
                                    
                                    {/* Price */}
                                    {(service.totalPrice !== undefined || service.price !== undefined) && (
                                      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end', mt: 1 }}>
                                        <Typography variant="subtitle1" color="primary" fontWeight={600}>
                                          Total: ${(service.totalPrice || service.price || 0).toLocaleString()}
                                        </Typography>
                                      </Box>
                                    )}
                                  </Stack>
                                </Grid>
                              </Grid>
                            </CardContent>
                          </Card>
                        </Grid>
                      ))}
                    </Grid>
                  </CardContent>
                </Card>
              </Box>
            )}

            {/* Professional Day-wise Itinerary */}
            {itineraryByDate.length > 0 ? (
              <Box>
                <Typography variant="h6" fontWeight={600} sx={{ mb: 2 }}>
                  Trip Itinerary
                </Typography>
                
                {itineraryByDate.map((dayData, dayIndex) => {
                  const { date, services } = dayData;
                  
                  return (
                    <Box key={dayIndex} sx={{ mb: 4 }}>
                      {/* Day Header */}
                      <Box 
                        sx={{ 
                          bgcolor: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 
                          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                          color: 'white', 
                          p: 2, 
                          borderRadius: 2,
                          mb: 2,
                          boxShadow: '0 4px 8px rgba(0,0,0,0.1)'
                        }}
                      >
                        <Typography variant="h6" fontWeight={600}>
                          Day {dayIndex + 1} - {date.format('dddd, Do MMMM YYYY')}
                        </Typography>
                        <Typography variant="body2" sx={{ opacity: 0.9 }}>
                          {services.length} {services.length === 1 ? 'activity' : 'activities'} planned
                        </Typography>
                      </Box>

                      {/* Services for this day */}
                      <Stack spacing={2}>
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
                              case 'restaurant':
                                return {
                                  name: serviceData?.restaurantName || 'Restaurant',
                                  location: serviceData?.city,
                                  time: serviceData?.visitTime || 'Time not specified',
                                  extra: `${serviceData?.mealType || 'Meal'} | ${serviceData?.cuisine || 'Various'} cuisine`,
                                  price: serviceData?.totalPrice || serviceData?.mealSpecificType?.totalPrice
                                };
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
                                  time: serviceData?.entrytime || 'Time not specified',
                                  extra: `Distance: ${serviceData?.distance || 0} km | Adults: ${serviceData?.adults || 0}`,
                                  price: serviceData?.totalPrice
                                };
                              case 'travel_hourly':
                                return {
                                  name: `${serviceData?.vehicles_name || 'Vehicle'} - Hourly Transport`,
                                  location: serviceData?.entrypickup || 'Pickup location not specified',
                                  time: `${serviceData?.entrytime || 'N/A'} | Duration: ${serviceData?.selectedHours || 0} hours`,
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
                            <Card 
                              key={serviceIndex} 
                              elevation={2}
                              sx={{ 
                                borderRadius: 2,
                                overflow: 'hidden',
                                '&:hover': {
                                  boxShadow: '0 8px 25px rgba(0,0,0,0.15)',
                                  transform: 'translateY(-2px)',
                                  transition: 'all 0.3s ease'
                                }
                              }}
                            >
                              <CardContent sx={{ p: 0 }}>
                                <Box sx={{ display: 'flex', height: '100%' }}>
                                  {/* Service Type Indicator */}
                                  <Box 
                                    sx={{ 
                                      width: 8,
                                      bgcolor: config.color,
                                      flexShrink: 0
                                    }}
                                  />
                                  
                                  {/* Content */}
                                  <Box sx={{ flex: 1, p: 3 }}>
                                    <Grid container spacing={2} alignItems="center">
                                      {/* Service Icon and Type */}
                                      <Grid item xs={12} sm={2} md={1}>
                                        <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                                          <Avatar sx={{ bgcolor: config.color, mb: 1, width: 48, height: 48 }}>
                                            {React.cloneElement(config.icon, { sx: { fontSize: 24 } })}
                                          </Avatar>
                                          <Typography variant="caption" color="text.secondary" fontWeight={600} textAlign="center">
                                            {config.title}
                                          </Typography>
                                        </Box>
                                      </Grid>
                                      
                                      {/* Service Details */}
                                      <Grid item xs={12} sm={10} md={8}>
                                        <Stack spacing={1}>
                                          <Typography variant="h6" fontWeight={600} color="text.primary">
                                            {details.name}
                                          </Typography>
                                          
                                          {details.location && (
                                            <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                              <LocationOnIcon sx={{ fontSize: 16, mr: 1, color: 'text.secondary' }} />
                                              <Typography variant="body2" color="text.secondary">
                                                {details.location}
                                              </Typography>
                                            </Box>
                                          )}
                                          
                                          <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                            <AccessTimeIcon sx={{ fontSize: 16, mr: 1, color: 'primary.main' }} />
                                            <Typography variant="body2" color="text.primary">
                                              {details.time}
                                            </Typography>
                                          </Box>
                                          
                                          {details.extra && (
                                            <Typography variant="body2" color="text.secondary" sx={{ fontStyle: 'italic' }}>
                                              {details.extra}
                                            </Typography>
                                          )}
                                        </Stack>
                                      </Grid>
                                      
                                      {/* Price */}
                                      <Grid item xs={12} sm={12} md={3}>
                                        <Box sx={{ textAlign: { xs: 'left', md: 'right' } }}>
                                          {details.price > 0 && (
                                            <Box sx={{ display: 'inline-flex', alignItems: 'center', bgcolor: 'success.light', px: 2, py: 1, borderRadius: 1 }}>
                                              <AttachMoneyIcon sx={{ fontSize: 18, mr: 0.5, color: 'success.contrastText' }} />
                                              <Typography variant="subtitle1" color="success.contrastText" fontWeight={600}>
                                                ${details.price.toLocaleString()}
                                              </Typography>
                                            </Box>
                                          )}
                                        </Box>
                                      </Grid>
                                    </Grid>
                                  </Box>
                                </Box>
                              </CardContent>
                            </Card>
                          );
                        })}
                      </Stack>
                    </Box>
                  );
                })}
              </Box>
            ) : (
              <Box sx={{ textAlign: 'center', py: 6 }}>
                <Typography variant="h6" color="text.secondary" gutterBottom>
                  No itinerary available
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Add services with booking dates to see your day-wise itinerary here.
                </Typography>
              </Box>
            )}



            {/* Total Summary */}
            {totalPrice > 0 && (
              <>
                <Divider sx={{ my: 3 }} />
                <Box sx={{ p: 3, bgcolor: 'success.light', borderRadius: 2 }}>
                  <Grid container alignItems="center">
                    <Grid item xs={12} md={8}>
                      <Typography variant="h6" color="success.contrastText">
                        Total Estimated Cost
                      </Typography>
                      <Typography variant="body2" color="success.contrastText" sx={{ opacity: 0.8 }}>
                        Based on selected services and configurations
                      </Typography>
                    </Grid>
                    <Grid item xs={12} md={4} sx={{ textAlign: { xs: 'left', md: 'right' } }}>
                      <Typography variant="h4" color="success.contrastText" fontWeight={700}>
                        ${totalPrice.toLocaleString()}
                      </Typography>
                    </Grid>
                  </Grid>
                </Box>
              </>
            )}
          </>
        )}
      </DialogContent>

      <DialogActions sx={{ px: 3, pb: 3 }}>
        <Button onClick={onClose} variant="outlined" color="primary">
          Close
        </Button>
        {allServices.length > 0 && (
          <Button onClick={handlePrint} variant="contained" color="primary" startIcon={<PrintIcon />}>
            Print Summary
          </Button>
        )}
      </DialogActions>
    </Dialog>
  );
};

export default ServicesSummaryModal;