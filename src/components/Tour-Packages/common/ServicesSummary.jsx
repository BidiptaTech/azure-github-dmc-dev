import React from 'react';
import { 
  Box, 
  Typography, 
  Paper, 
  Grid, 
  Card, 
  CardContent, 
  Divider, 
  Chip,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
  Avatar,
  Stack,
  Badge
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
import moment from 'moment';

const ServicesSummary = () => {
  // Get all services from Redux store
  const allServices = useSelector((state) => state.tourPackages.AllServices);
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  const selectedCity = useSelector(state => state.common?.selectedCity?.cityName);
  const selectedCountry = useSelector(state => state.common?.selectedCity?.countryName);

  // Group services by type
  const servicesByType = allServices.reduce((acc, service) => {
    const type = service.type || 'other';
    if (!acc[type]) {
      acc[type] = [];
    }
    acc[type].push(service);
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

  // If no services selected
  if (allServices.length === 0) {
    return (
      <Paper elevation={3} sx={{ p: 4, textAlign: 'center', mt: 4 }}>
        <Typography variant="h6" color="text.secondary">
          No services selected yet
        </Typography>
        <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
          Start adding hotels, attractions, restaurants, and other services to see your trip summary here.
        </Typography>
      </Paper>
    );
  }

  return (
    <Paper elevation={3} sx={{ p: 4, mt: 4 }}>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
        <Typography variant="h5" fontWeight={600}>
          Selected Services Summary
        </Typography>
        <Chip 
          label={`${allServices.length} Service${allServices.length !== 1 ? 's' : ''}`} 
          color="primary" 
          sx={{ ml: 2 }} 
        />
      </Box>

      {/* Trip Overview */}
      <Box sx={{ mb: 4, p: 3, bgcolor: 'rgba(33, 150, 243, 0.05)', borderRadius: 2 }}>
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

      {/* Services by Type */}
      <Grid container spacing={3}>
        {Object.entries(servicesByType).map(([serviceType, services]) => {
          const config = serviceTypeConfig[serviceType] || {
            icon: <DirectionsCarIcon />,
            color: '#666',
            title: serviceType.charAt(0).toUpperCase() + serviceType.slice(1),
            bgColor: 'rgba(102, 102, 102, 0.1)'
          };

          return (
            <Grid item xs={12} key={serviceType}>
              <Card elevation={2} sx={{ borderRadius: 2 }}>
                <CardContent>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                    <Avatar sx={{ bgcolor: config.color, mr: 2 }}>
                      {config.icon}
                    </Avatar>
                    <Typography variant="h6" fontWeight={600}>
                      {config.title}
                    </Typography>
                    <Badge 
                      badgeContent={services.length} 
                      color="primary" 
                      sx={{ ml: 2 }}
                    />
                  </Box>

                  <Grid container spacing={2}>
                    {services.map((service, index) => (
                      <Grid item xs={12} md={6} lg={4} key={index}>
                        <Paper 
                          elevation={1} 
                          sx={{ 
                            p: 2, 
                            bgcolor: config.bgColor,
                            borderLeft: `4px solid ${config.color}`,
                            height: '100%'
                          }}
                        >
                          <Stack spacing={1}>
                            {/* Service Name */}
                            <Typography variant="subtitle1" fontWeight={600} noWrap>
                              {service.hotelDetails?.hotel_name || 
                               service.attractionDetails?.name || 
                               service.restaurantDetails?.name || 
                               service.guideDetails?.name || 
                               service.transportDetails?.vehicle_type ||
                               service.pickupDetails?.type ||
                               'Service'}
                            </Typography>

                            {/* Service Details */}
                            {serviceType === 'hotel' && (
                              <>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <LocationOnIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                  <Typography variant="caption" color="text.secondary" noWrap>
                                    {service.hotelDetails?.location || 'Location not specified'}
                                  </Typography>
                                </Box>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <CalendarTodayIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                  <Typography variant="caption" color="text.secondary">
                                    {formatDateRange(service.bookingDate)} ({calculateNights(service.bookingDate)} nights)
                                  </Typography>
                                </Box>
                                {service.hotelDetails?.rooms && (
                                  <Typography variant="caption" color="text.secondary">
                                    {service.hotelDetails.rooms.length} room{service.hotelDetails.rooms.length !== 1 ? 's' : ''}
                                  </Typography>
                                )}
                              </>
                            )}

                            {serviceType === 'attraction' && (
                              <>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <LocationOnIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                  <Typography variant="caption" color="text.secondary" noWrap>
                                    {service.attractionDetails?.location || 'Location not specified'}
                                  </Typography>
                                </Box>
                                {service.bookingDate && (
                                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                    <CalendarTodayIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                    <Typography variant="caption" color="text.secondary">
                                      {moment(service.bookingDate).format('MMM DD, YYYY')}
                                    </Typography>
                                  </Box>
                                )}
                              </>
                            )}

                            {serviceType === 'restaurant' && (
                              <>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <LocationOnIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                  <Typography variant="caption" color="text.secondary" noWrap>
                                    {service.restaurantDetails?.location || 'Location not specified'}
                                  </Typography>
                                </Box>
                                {service.mealType && (
                                  <Chip 
                                    label={service.mealType} 
                                    size="small" 
                                    sx={{ alignSelf: 'flex-start' }}
                                  />
                                )}
                              </>
                            )}

                            {serviceType === 'guide' && (
                              <>
                                <Typography variant="caption" color="text.secondary">
                                  {service.guideDetails?.specialization || 'General Guide'}
                                </Typography>
                                {service.bookingDate && (
                                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                    <CalendarTodayIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                                    <Typography variant="caption" color="text.secondary">
                                      {moment(service.bookingDate).format('MMM DD, YYYY')}
                                    </Typography>
                                  </Box>
                                )}
                              </>
                            )}

                            {/* Price */}
                            {(service.totalPrice || service.price) && (
                              <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                                <AttachMoneyIcon sx={{ fontSize: 14, mr: 0.5, color: 'success.main' }} />
                                <Typography variant="body2" color="success.main" fontWeight={600}>
                                  ${(service.totalPrice || service.price).toLocaleString()}
                                </Typography>
                              </Box>
                            )}
                          </Stack>
                        </Paper>
                      </Grid>
                    ))}
                  </Grid>
                </CardContent>
              </Card>
            </Grid>
          );
        })}
      </Grid>

      {/* Total Summary */}
      {totalPrice > 0 && (
        <Box sx={{ mt: 4, p: 3, bgcolor: 'success.light', borderRadius: 2 }}>
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
      )}
    </Paper>
  );
};

export default ServicesSummary; 