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
import PersonOutlineIcon from '@mui/icons-material/PersonOutline';
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

  // Group services by type for hotel section and customer info
  const hotelServices = allServices.filter(service => service.type === 'hotel');
  const customerInfo = allServices.find(service => service.type === 'CustomerInfo');
  const otherServices = allServices.filter(service => service.type !== 'hotel' && service.type !== 'CustomerInfo');

  // Group other services by date/day
  const servicesByDate = otherServices.reduce((acc, service) => {
    // For services with specific booking dates, use that date
    let serviceDate = null;
    if (service.bookingDate) {
      if (Array.isArray(service.bookingDate)) {
        serviceDate = moment(service.bookingDate[0]);
      } else {
        serviceDate = moment(service.bookingDate);
      }
    }
    
    // Find the day index for this service
    let dayIndex = 0;
    if (serviceDate) {
      const startDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
      dayIndex = serviceDate.diff(startDate, 'days');
      if (dayIndex < 0) dayIndex = 0;
      if (dayIndex >= dates.length) dayIndex = dates.length - 1;
    }
    
    if (!acc[dayIndex]) {
      acc[dayIndex] = [];
    }
    acc[dayIndex].push(service);
    return acc;
  }, {});

  // Service type configurations
  const serviceTypeConfig = {
    hotel: {
      icon: <HotelIcon />,
      color: '#1976d2',
      title: 'Hotels',
      bgColor: 'rgba(25, 118, 210, 0.1)'
    },
    attraction: {
      icon: <AttractionsIcon />,
      color: '#f44336',
      title: 'Attractions',
      bgColor: 'rgba(244, 67, 54, 0.1)'
    },
    restaurant: {
      icon: <RestaurantIcon />,
      color: '#ff9800',
      title: 'Restaurants',
      bgColor: 'rgba(255, 152, 0, 0.1)'
    },
    guide: {
      icon: <PersonIcon />,
      color: '#4caf50',
      title: 'Guides',
      bgColor: 'rgba(76, 175, 80, 0.1)'
    },
    transport: {
      icon: <DirectionsCarIcon />,
      color: '#2196f3',
      title: 'Transportation',
      bgColor: 'rgba(33, 150, 243, 0.1)'
    },
    pickup_drop: {
      icon: <AirportShuttleIcon />,
      color: '#9c27b0',
      title: 'Pickup & Drop',
      bgColor: 'rgba(156, 39, 176, 0.1)'
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
    console.log('Customer Info:', customerInfo);
    console.log('Other Services:', otherServices);
  }, [allServices, hotelServices, customerInfo, otherServices]);

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

                    <Grid container spacing={1.5}>
                      {hotelServices.map((service, index) => (
                        <Grid item xs={12} sm={6} key={index}>
                          <Box 
                            sx={{ 
                              p: 2, 
                              bgcolor: 'rgba(25, 118, 210, 0.1)',
                              borderLeft: '3px solid #1976d2',
                              borderRadius: 1
                            }}
                          >
                            <Stack spacing={0.5}>
                              <Typography variant="subtitle2" fontWeight={600}>
                                {service.hotelDetails?.hotel_name || 'Hotel'}
                              </Typography>
                              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                <LocationOnIcon sx={{ fontSize: 12, mr: 0.5, color: 'text.secondary' }} />
                                <Typography variant="caption" color="text.secondary" noWrap>
                                  {service.hotelDetails?.location || 'Location not specified'}
                                </Typography>
                              </Box>
                              <Typography variant="caption" color="text.secondary">
                                {formatDateRange(service.bookingDate)} ({calculateNights(service.bookingDate)} nights)
                              </Typography>
                              {service.hotelDetails?.rooms && (
                                <Typography variant="caption" color="text.secondary">
                                  {service.hotelDetails.rooms.length} room{service.hotelDetails.rooms.length !== 1 ? 's' : ''}
                                </Typography>
                              )}
                              {(service.totalPrice || service.price) && (
                                <Box sx={{ display: 'flex', alignItems: 'center', mt: 0.5 }}>
                                  <AttachMoneyIcon sx={{ fontSize: 12, mr: 0.5, color: 'success.main' }} />
                                  <Typography variant="caption" color="success.main" fontWeight={600}>
                                    ${(service.totalPrice || service.price).toLocaleString()}
                                  </Typography>
                                </Box>
                              )}
                            </Stack>
                          </Box>
                        </Grid>
                      ))}
                    </Grid>
                  </CardContent>
                </Card>
              </Box>
            )}

            {/* Day by Day Itinerary */}
            {dates.length > 0 && (
              <Box>
                <Typography variant="h6" fontWeight={600} sx={{ mb: 2 }}>
                  Day-wise Itinerary
                </Typography>
                
                {dates.map((date, dayIndex) => {
                  const dayServices = servicesByDate[dayIndex] || [];
                  
                  return (
                    <Box key={dayIndex} sx={{ mb: 3 }}>
                      {/* Day Header */}
                      <Box 
                        sx={{ 
                          bgcolor: 'primary.main', 
                          color: 'white', 
                          p: 1.5, 
                          borderRadius: 1,
                          mb: 2
                        }}
                      >
                        <Typography variant="subtitle1" fontWeight={600}>
                          Day {dayIndex + 1}, {date.format('Do MMMM')}, {date.format('dddd')}
                        </Typography>
                      </Box>

                      {/* Services for this day */}
                      {dayServices.length > 0 ? (
                        <Grid container spacing={1.5}>
                          {dayServices.map((service, serviceIndex) => {
                            const config = serviceTypeConfig[service.type] || {
                              icon: <DirectionsCarIcon />,
                              color: '#666',
                              title: service.type?.charAt(0).toUpperCase() + service.type?.slice(1) || 'Service',
                              bgColor: 'rgba(102, 102, 102, 0.1)'
                            };

                            return (
                              <Grid item xs={12} sm={6} md={4} key={serviceIndex}>
                                <Box 
                                  sx={{ 
                                    p: 2, 
                                    bgcolor: config.bgColor,
                                    borderLeft: `3px solid ${config.color}`,
                                    borderRadius: 1,
                                    height: '100%'
                                  }}
                                >
                                  <Stack spacing={0.5}>
                                    {/* Service Type Header */}
                                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
                                      <Avatar sx={{ bgcolor: config.color, mr: 1, width: 20, height: 20 }}>
                                        {React.cloneElement(config.icon, { sx: { fontSize: 12 } })}
                                      </Avatar>
                                      <Typography variant="caption" color="text.secondary" fontWeight={600}>
                                        {config.title}
                                      </Typography>
                                    </Box>

                                    {/* Service Name */}
                                    <Typography variant="subtitle2" fontWeight={600} noWrap>
                                      {service.hotelDetails?.hotel_name || 
                                       service.attractionDetails?.name || 
                                       service.restaurantDetails?.name || 
                                       service.guideDetails?.name || 
                                       service.transportDetails?.vehicle_type ||
                                       service.pickupDetails?.type ||
                                       'Service'}
                                    </Typography>

                                    {/* Service Details */}
                                    {service.type === 'attraction' && (
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <LocationOnIcon sx={{ fontSize: 12, mr: 0.5, color: 'text.secondary' }} />
                                        <Typography variant="caption" color="text.secondary" noWrap>
                                          {service.attractionDetails?.location || 'Location not specified'}
                                        </Typography>
                                      </Box>
                                    )}

                                    {service.type === 'restaurant' && (
                                      <>
                                        <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                          <LocationOnIcon sx={{ fontSize: 12, mr: 0.5, color: 'text.secondary' }} />
                                          <Typography variant="caption" color="text.secondary" noWrap>
                                            {service.restaurantDetails?.location || 'Location not specified'}
                                          </Typography>
                                        </Box>
                                        {service.mealType && (
                                          <Chip 
                                            label={service.mealType} 
                                            size="small" 
                                            sx={{ alignSelf: 'flex-start', height: 18, fontSize: '0.6rem' }}
                                          />
                                        )}
                                      </>
                                    )}

                                    {service.type === 'guide' && (
                                      <Typography variant="caption" color="text.secondary">
                                        {service.guideDetails?.specialization || 'General Guide'}
                                      </Typography>
                                    )}

                                    {service.type === 'transport' && (
                                      <Typography variant="caption" color="text.secondary">
                                        {service.transportDetails?.description || 'Transportation service'}
                                      </Typography>
                                    )}

                                    {service.type === 'pickup_drop' && (
                                      <Typography variant="caption" color="text.secondary">
                                        {service.pickupDetails?.description || 'Pickup/Drop service'}
                                      </Typography>
                                    )}

                                    {/* Price */}
                                    {(service.totalPrice || service.price) && (
                                      <Box sx={{ display: 'flex', alignItems: 'center', mt: 0.5 }}>
                                        <AttachMoneyIcon sx={{ fontSize: 12, mr: 0.5, color: 'success.main' }} />
                                        <Typography variant="caption" color="success.main" fontWeight={600}>
                                          ${(service.totalPrice || service.price).toLocaleString()}
                                        </Typography>
                                      </Box>
                                    )}
                                  </Stack>
                                </Box>
                              </Grid>
                            );
                          })}
                        </Grid>
                      ) : (
                        <Box sx={{ p: 2, textAlign: 'center', bgcolor: 'grey.50', borderRadius: 1 }}>
                          <Typography variant="body2" color="text.secondary">
                            No services planned for this day
                          </Typography>
                        </Box>
                      )}
                    </Box>
                  );
                })}
              </Box>
            )}

            {/* Customer Information Section */}
            <Box sx={{ mt: 4 }}>
              <Card elevation={1} sx={{ borderRadius: 2 }}>
                <CardContent>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                    <Avatar sx={{ bgcolor: '#673ab7', mr: 2, width: 32, height: 32 }}>
                      <PersonOutlineIcon sx={{ fontSize: 18 }} />
                    </Avatar>
                    <Typography variant="h6" fontWeight={600}>
                      Customer Information
                    </Typography>
                  </Box>

                  {customerInfo ? (
                    <Grid container spacing={2}>
                      {/* Personal Information */}
                      <Grid item xs={12} md={6}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600} display="block" gutterBottom>
                            Full Name
                          </Typography>
                          <Typography variant="body1" fontWeight={600}>
                            {customerInfo.fullName || 'Not provided'}
                          </Typography>
                        </Box>
                      </Grid>

                      <Grid item xs={12} md={6}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600} display="block" gutterBottom>
                            Email
                          </Typography>
                          <Typography variant="body2">
                            {customerInfo.email || 'Not provided'}
                          </Typography>
                        </Box>
                      </Grid>

                      <Grid item xs={12} md={6}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600} display="block" gutterBottom>
                            Phone Number
                          </Typography>
                          <Typography variant="body2">
                            {customerInfo.countryCode} {customerInfo.phone || 'Not provided'}
                          </Typography>
                        </Box>
                      </Grid>

                      {/* Address Information */}
                      <Grid item xs={12} md={6}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600} display="block" gutterBottom>
                            State/Region
                          </Typography>
                          <Typography variant="body2">
                            {customerInfo.state || 'Not provided'}
                          </Typography>
                        </Box>
                      </Grid>

                      <Grid item xs={12}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600} display="block" gutterBottom>
                            Address
                          </Typography>
                          <Typography variant="body2">
                            {customerInfo.address1 || 'Not provided'}
                            {customerInfo.address2 && (
                              <>
                                <br />
                                {customerInfo.address2}
                              </>
                            )}
                            {customerInfo.zip && (
                              <>
                                <br />
                                ZIP: {customerInfo.zip}
                              </>
                            )}
                          </Typography>
                        </Box>
                      </Grid>

                      {/* Special Requests */}
                      {customerInfo.specialRequests && (
                        <Grid item xs={12}>
                          <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                            <Typography variant="caption" color="text.secondary" fontWeight={600} display="block" gutterBottom>
                              Special Requests
                            </Typography>
                            <Typography variant="body2">
                              {customerInfo.specialRequests}
                            </Typography>
                          </Box>
                        </Grid>
                      )}

                      {/* Trip Details */}
                      <Grid item xs={12}>
                        <Divider sx={{ my: 2 }} />
                        <Typography variant="subtitle2" fontWeight={600} gutterBottom>
                          Trip Details
                        </Typography>
                      </Grid>

                      <Grid item xs={12} sm={6} md={3}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600}>
                            Adults
                          </Typography>
                          <Typography variant="h6" color="primary.main">
                            {searchCriteria.guests?.adults || 1}
                          </Typography>
                        </Box>
                      </Grid>
                      
                      <Grid item xs={12} sm={6} md={3}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600}>
                            Children
                          </Typography>
                          <Typography variant="h6" color="primary.main">
                            {searchCriteria.guests?.children || 0}
                          </Typography>
                        </Box>
                      </Grid>
                      
                      <Grid item xs={12} sm={6} md={3}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600}>
                            Infants
                          </Typography>
                          <Typography variant="h6" color="primary.main">
                            {searchCriteria.guests?.infants || 0}
                          </Typography>
                        </Box>
                      </Grid>
                      
                      <Grid item xs={12} sm={6} md={3}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600}>
                            Total Guests
                          </Typography>
                          <Typography variant="h6" color="primary.main">
                            {(searchCriteria.guests?.adults || 1) + 
                             (searchCriteria.guests?.children || 0) + 
                             (searchCriteria.guests?.infants || 0)}
                          </Typography>
                        </Box>
                      </Grid>

                      {/* Travel Dates */}
                      <Grid item xs={12} sm={6}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600} display="block" gutterBottom>
                            Check-in Date
                          </Typography>
                          <Box sx={{ display: 'flex', alignItems: 'center' }}>
                            <CalendarTodayIcon sx={{ fontSize: 16, mr: 1, color: 'primary.main' }} />
                            <Typography variant="body2" fontWeight={600}>
                              {searchCriteria?.checkIn || 'Not specified'}
                            </Typography>
                          </Box>
                        </Box>
                      </Grid>

                      <Grid item xs={12} sm={6}>
                        <Box sx={{ p: 2, bgcolor: 'rgba(103, 58, 183, 0.1)', borderRadius: 1 }}>
                          <Typography variant="caption" color="text.secondary" fontWeight={600} display="block" gutterBottom>
                            Check-out Date
                          </Typography>
                          <Box sx={{ display: 'flex', alignItems: 'center' }}>
                            <CalendarTodayIcon sx={{ fontSize: 16, mr: 1, color: 'primary.main' }} />
                            <Typography variant="body2" fontWeight={600}>
                              {searchCriteria?.checkOut || 'Not specified'}
                            </Typography>
                          </Box>
                        </Box>
                      </Grid>
                    </Grid>
                  ) : (
                    <Box sx={{ textAlign: 'center', py: 4 }}>
                      <Typography variant="body2" color="text.secondary">
                        Customer information not yet provided
                      </Typography>
                      <Typography variant="caption" color="text.secondary">
                        Please fill out the customer information form to see details here
                      </Typography>
                    </Box>
                  )}
                </CardContent>
              </Card>
            </Box>

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