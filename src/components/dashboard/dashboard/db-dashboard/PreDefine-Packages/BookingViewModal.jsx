import React, { useState } from 'react';
import { useSelector } from 'react-redux';
import {
  Box,
  Typography,
  Chip,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  useMediaQuery,
  useTheme,
  Slide,
  Grid,
  Paper,
  Avatar,
  Divider,
  List,
  ListItem,
  ListItemAvatar,
  ListItemText,
  Alert,
  Card,
  CardContent,
  CardMedia
} from '@mui/material';
import {
  Close,
  CalendarToday,
  PeopleAlt,
  LocationOn,
  AttachMoney,
  Person,
  Hotel,
  Attractions,
  Restaurant,
  EmojiPeople,
  Phone,
  Email,
  HotelOutlined,
  RestaurantOutlined,
  DirectionsCar,
  Museum,
  BeachAccess,
  Hiking,
  Flight,
  NightsStay,
  LocalActivity,
  Business
} from '@mui/icons-material';
import { StatusChip, PaymentStatusChip } from './StatusChips';
import PDFGenerator, { PDFPrintButton, usePDFGenerator } from './PDFGenerator';
import { calculateTotalWithTaxes } from './utils';

// Slide transition for modal
const Transition = React.forwardRef(function Transition(props, ref) {
  return <Slide direction="up" ref={ref} {...props} />;
});

// Helper function to parse JSON safely
const parseJsonSafely = (jsonString) => {
  if (!jsonString) return null;
  if (typeof jsonString === 'object') return jsonString;
  
  try {
    return JSON.parse(jsonString);
  } catch (error) {
    console.error("Error parsing JSON:", error);
    return null;
  }
};

// Service Icons mapping based on service type
const getServiceIcon = (serviceType) => {
  switch (serviceType?.toLowerCase()) {
    case 'hotel':
    case 'accommodation':
      return <HotelOutlined />;
    case 'restaurant':
    case 'dining':
      return <RestaurantOutlined />;
    case 'attraction':
    case 'sightseeing':
      return <Museum />;
    case 'transportation':
    case 'transfer':
      return <DirectionsCar />;
    case 'beach':
      return <BeachAccess />;
    case 'hike':
    case 'hiking':
      return <Hiking />;
    case 'flight':
    case 'airport':
      return <Flight />;
    case 'activity':
      return <LocalActivity />;
    default:
      return <LocalActivity />;
  }
};

// Helper function to get color based on service type
const getServiceColor = (serviceType) => {
  switch (serviceType?.toLowerCase()) {
    case 'hotel':
    case 'accommodation':
      return 'primary.main';
    case 'restaurant':
    case 'dining':
      return 'warning.main';
    case 'attraction':
    case 'sightseeing':
      return 'success.main';
    case 'transportation':
    case 'transfer':
      return 'info.main';
    case 'beach':
      return 'info.light';
    case 'hike':
    case 'hiking':
      return 'success.dark';
    case 'flight':
    case 'airport':
      return 'secondary.main';
    default:
      return 'primary.light';
  }
};

// Helper function to extract relevant information from objects
const safeRender = (content, type = 'default') => {
  if (content === null || content === undefined) {
    return '';
  }

  // Handle simple string/number values
  if (typeof content !== 'object') {
    return content;
  }

  // Handle different types of objects based on their expected structure
  switch (type) {
    case 'name':
      // Extract name from objects like {"id":"...", "name":"Taj Hotel Kolkata", ...}
      return content.name || content.title || 'Unnamed';

    case 'location':
      // Handle location objects
      if (content.address) return content.address;
      if (content.city) return content.city;
      if (content.name) return content.name; // Some location objects might just have a name
      return 'No location specified';

    case 'time':
      // Handle time/duration objects
      if (content.duration) return content.duration;
      if (content.time) return content.time;
      return 'Full day';

    case 'guide':
      // Handle guide objects
      if (content.name) {
        let result = content.name;
        if (content.languages && Array.isArray(content.languages)) {
          const langs = content.languages.map(l => 
            typeof l === 'object' && l.language ? l.language : l
          );
          if (langs.length > 0) {
            result += ` (${langs.join(', ')})`;
          }
        }
        return result;
      }
      return 'Guide information unavailable';

    default:
      // For description and other text, try to extract meaningful content
      if (content.description) return content.description;
      if (content.details) return content.details;
      if (content.text) return content.text;
      if (content.name) return content.name;
      
      // Avoid showing complex nested objects as strings
      if (Object.keys(content).length > 0) {
        const simpleProps = Object.entries(content)
          .filter(([key, val]) => typeof val !== 'object' || val === null)
          .map(([key, val]) => `${key}: ${val}`)
          .join(', ');
          
        return simpleProps || 'Additional information available';
      }
      
      return '';
  }
};

// Enhanced Service Card Component - For debugging data issues
const ServiceCard = ({ service, fallback = false }) => {
  // Only log in development environment 
  // if (process.env.NODE_ENV === 'development') {
  //   console.log('Service data:', service);
  // }
  
  // Extract basic information that should be available
  let serviceType = '';
  let serviceName = '';
  let serviceTime = '';
  let serviceLocation = '';
  let serviceImage = '';
  let hasEntryTransport = false;
  let hasExitTransport = false;
  let hasAttractionTransfer = false;
  let attractionTransferType = '';
  
  // Handle cases where service might be an object with nested data
  if (typeof service === 'object' && service !== null) {
    // Try to extract type information
    if (service.service_type) {
      // Handle the specific format shown in the example
      serviceType = service.service_type;
      serviceName = service.service_name || 'Unnamed Service';
      
      // Handle details object
      if (service.details && typeof service.details === 'object') {
        // Extract more information from details
        if (!serviceName && service.details.name) {
          serviceName = service.details.name;
        }
        
        // Get image from details
        if (service.details.image) {
          serviceImage = service.details.image;
        } else if (service.details.images && Array.isArray(service.details.images) && service.details.images.length > 0) {
          serviceImage = service.details.images[0];
        }
        
        // Extract location from details if available
        serviceLocation = service.details.location || service.details.address || service.details.city || '';
      }
      
      // Check for transport inclusion flags
      hasEntryTransport = service.entry_port === 1 || service.entry_port === true;
      hasExitTransport = service.exit_port === 1 || service.exit_port === true;
      
      // Check for attraction transfer
      if (serviceType.toLowerCase() === 'attraction' && service.attraction_with_transfer) {
        const transferValue = parseInt(service.attraction_with_transfer);
        if (transferValue === 1) {
          hasAttractionTransfer = true;
          attractionTransferType = 'unidirectional';
        } else if (transferValue === 2) {
          hasAttractionTransfer = true;
          attractionTransferType = 'bidirectional';
        }
      }
    } 
    else if (service.type) {
      serviceType = typeof service.type === 'string' ? service.type.toLowerCase() : 'unknown';
      
      // Extract name from various possible properties
      serviceName = service.name || service.title || service.hotel_name || 
                    service.attraction_name || service.restaurant_name || 'Unknown Service';
      
      // Extract time/duration
      serviceTime = service.time || service.duration || service.period || 'Full day';
      
      // Extract location information
      serviceLocation = service.location || service.address || service.city || '';
      
      if (typeof serviceLocation === 'object' && serviceLocation !== null) {
        serviceLocation = serviceLocation.address || serviceLocation.name || serviceLocation.city || '';
      }
      
      // Extract image
      serviceImage = service.image || service.main_image || '';
      
      // Try to find transport flags in alternate locations
      hasEntryTransport = service.entry_transport || service.arrival_transfer || service.includes_transfer;
      hasExitTransport = service.exit_transport || service.departure_transfer;
      
      // Check for attraction transfer in standard format
      if (serviceType.toLowerCase() === 'attraction' && service.attraction_with_transfer) {
        const transferValue = parseInt(service.attraction_with_transfer);
        if (transferValue === 1) {
          hasAttractionTransfer = true;
          attractionTransferType = 'unidirectional';
        } else if (transferValue === 2) {
          hasAttractionTransfer = true;
          attractionTransferType = 'bidirectional';
        }
      }
    }
    // If no type, try to infer from other properties
    else if (service.hotel_id || service.hotel_name) {
      serviceType = 'hotel';
      serviceName = service.hotel_name || 'Hotel';
    } else if (service.attraction_id || service.attraction_name) {
      serviceType = 'attraction';
      serviceName = service.attraction_name || 'Attraction';
      
      // Check for attraction transfer
      if (service.attraction_with_transfer) {
        const transferValue = parseInt(service.attraction_with_transfer);
        if (transferValue === 1) {
          hasAttractionTransfer = true;
          attractionTransferType = 'unidirectional';
        } else if (transferValue === 2) {
          hasAttractionTransfer = true;
          attractionTransferType = 'bidirectional';
        }
      }
    } else if (service.restaurant_id || service.restaurant_name) {
      serviceType = 'restaurant';
      serviceName = service.restaurant_name || 'Restaurant';
    } else {
      // Try to extract any name-like property
      for (const key of Object.keys(service)) {
        if (key.includes('name') || key.includes('title')) {
          serviceName = service[key];
          break;
        }
      }
      
      if (!serviceName) {
        serviceName = 'Unknown Service';
      }
    }
  } else {
    // Handle primitive values or null/undefined
    serviceName = String(service || 'Unknown Service');
  }

  const serviceIcon = getServiceIcon(serviceType);
  const serviceColor = getServiceColor(serviceType);

  return (
    <Card elevation={1} sx={{ mb: 0.5, borderRadius: '8px', overflow: 'hidden' }}>
      <CardContent sx={{ p: 1, pb: '8px !important' }}>
        <Grid container spacing={1} alignItems="center">
          {serviceImage ? (
            <Grid item xs={2}>
              <Avatar 
                src={serviceImage} 
                variant="rounded"
                sx={{ width: 40, height: 40 }}
                alt={serviceName}
              />
            </Grid>
          ) : (
            <Grid item xs={1}>
              <Avatar sx={{ bgcolor: serviceColor, width: 28, height: 28 }}>
                {serviceIcon}
              </Avatar>
            </Grid>
          )}
          <Grid item xs={serviceImage ? 10 : 11}>
            <Typography variant="body2" fontWeight="bold" sx={{ lineHeight: 1.2 }}>
              {serviceName}
            </Typography>
            {serviceTime && (
              <Typography variant="caption" color="text.secondary">
                {serviceTime}
              </Typography>
            )}
          </Grid>
        </Grid>
        
        <Box sx={{ ml: 4, mt: 0.5 }}>
          {/* Transport status indicators */}
          {(hasEntryTransport || hasExitTransport || hasAttractionTransfer) && (
            <Box sx={{ display: 'flex', gap: 0.5, flexWrap: 'wrap', mb: 0.5 }}>
              {hasEntryTransport && (
                <Chip 
                  size="small" 
                  color="info" 
                  icon={<DirectionsCar fontSize="small" />}
                  label="Arrival Transport"
                  variant="outlined" 
                  sx={{ height: 20, fontSize: '0.65rem' }} 
                />
              )}
              {hasExitTransport && (
                <Chip 
                  size="small" 
                  color="info" 
                  icon={<DirectionsCar fontSize="small" />}
                  label="Departure Transport" 
                  variant="outlined" 
                  sx={{ height: 20, fontSize: '0.65rem' }} 
                />
              )}
              {hasAttractionTransfer && attractionTransferType === 'unidirectional' && (
                <Chip 
                  size="small" 
                  color="success" 
                  icon={<DirectionsCar fontSize="small" />}
                  label="One-Way Transfer" 
                  variant="outlined" 
                  sx={{ height: 20, fontSize: '0.65rem' }} 
                />
              )}
              {hasAttractionTransfer && attractionTransferType === 'bidirectional' && (
                <Chip 
                  size="small" 
                  color="success" 
                  icon={<DirectionsCar fontSize="small" />}
                  label="Round-Trip Transfer" 
                  variant="outlined" 
                  sx={{ height: 20, fontSize: '0.65rem' }} 
                />
              )}
            </Box>
          )}
          
          {serviceLocation && (
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <LocationOn fontSize="small" color="error" sx={{ mr: 0.5, fontSize: '0.8rem' }} />
              <Typography variant="caption">{serviceLocation}</Typography>
            </Box>
          )}
        </Box>
        
        {/* Display raw data only if fallback mode is explicitly enabled AND we're in development */}
        {fallback && process.env.NODE_ENV === 'development' && (
          <Box sx={{ ml: 4, mt: 0.5, bgcolor: 'grey.50', p: 0.5, borderRadius: '4px' }}>
            <Typography variant="caption" sx={{ whiteSpace: 'pre-wrap', fontSize: '0.7rem' }}>
              {JSON.stringify(service, null, 2)}
            </Typography>
          </Box>
        )}
      </CardContent>
    </Card>
  );
};

// Section component (non-collapsible)
const Section = ({ title, icon, color, children }) => {
  return (
    <Paper 
      elevation={2} 
      sx={{ 
        p: 0, 
        borderRadius: '8px',
        overflow: 'hidden',
        mb: 2
      }}
    >
      <Box 
        sx={{ 
          p: 1, 
          bgcolor: color || 'primary.main', 
          color: 'white',
          display: 'flex',
          alignItems: 'center'
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          {icon}
          <Typography variant="subtitle1" sx={{ ml: 1 }}>{title}</Typography>
        </Box>
      </Box>
      
      {children}
    </Paper>
  );
};

// Day Card Component
const DayCard = ({ day, index, totalDays }) => {
  // Check if day.services exists and has items
  const hasServices = day.services && Array.isArray(day.services) && day.services.length > 0;
  
  // Debugging the day structure - only in development
  // if (process.env.NODE_ENV === 'development') {
  //   console.log(`Day ${day.day || index + 1} data:`, day);
  // }
  
  // Extract transport information from day data
  const hasArrivalPickup = day.arrival_pickup === 1 || day.arrival_pickup === true;
  const hasDepartureService = day.departure_service === 1 || day.departure_service === true;
  
  // Group services by type with better type detection for the new format
  const hotelServices = hasServices ? day.services.filter(s => 
    (s?.type && typeof s.type === 'string' && (s.type.toLowerCase() === 'hotel' || s.type.toLowerCase() === 'accommodation')) ||
    (s?.service_type && typeof s.service_type === 'string' && (s.service_type.toLowerCase() === 'hotel' || s.service_type.toLowerCase() === 'accommodation')) ||
    s?.hotel_id || s?.hotel_name
  ) : [];
  
  const attractionServices = hasServices ? day.services.filter(s => 
    (s?.type && typeof s.type === 'string' && (s.type.toLowerCase() === 'attraction' || s.type.toLowerCase() === 'sightseeing')) ||
    (s?.service_type && typeof s.service_type === 'string' && (s.service_type.toLowerCase() === 'attraction' || s.service_type.toLowerCase() === 'sightseeing')) ||
    s?.attraction_id || s?.attraction_name
  ) : [];
  
  const restaurantServices = hasServices ? day.services.filter(s => 
    (s?.type && typeof s.type === 'string' && (s.type.toLowerCase() === 'restaurant' || s.type.toLowerCase() === 'dining')) ||
    (s?.service_type && typeof s.service_type === 'string' && (s.service_type.toLowerCase() === 'restaurant' || s.service_type.toLowerCase() === 'dining')) ||
    s?.restaurant_id || s?.restaurant_name
  ) : [];
  
  const transferServices = hasServices ? day.services.filter(s => 
    (s?.type && typeof s.type === 'string' && (s.type.toLowerCase() === 'transportation' || s.type.toLowerCase() === 'transfer')) ||
    (s?.service_type && typeof s.service_type === 'string' && (s.service_type.toLowerCase() === 'transportation' || s.service_type.toLowerCase() === 'transfer'))
  ) : [];
  
  const otherServices = hasServices ? day.services.filter(s => 
    !hotelServices.includes(s) && 
    !attractionServices.includes(s) && 
    !restaurantServices.includes(s) && 
    !transferServices.includes(s)
  ) : [];

  return (
    <Card 
      elevation={1} 
      sx={{ 
        mb: 1, 
        borderRadius: '8px',
        overflow: 'hidden'
      }}
    >
      <Box 
        sx={{ 
          p: 1, 
          bgcolor: 'grey.100', 
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <CalendarToday fontSize="small" sx={{ mr: 1, color: 'text.secondary' }} />
          <Typography variant="subtitle2" fontWeight="bold">
            Day {day.day}: {day.date}
          </Typography>
        </Box>
        
        {/* Transport Status Indicators */}
        {(hasArrivalPickup || hasDepartureService) && (
          <Box sx={{ display: 'flex', gap: 0.5, flexWrap: 'wrap' }}>
            {hasArrivalPickup && (
              <Chip 
                size="small" 
                color="success" 
                icon={<DirectionsCar fontSize="small" />}
                label="Arrival Pickup"
                variant="outlined" 
                sx={{ height: 20, fontSize: '0.65rem' }} 
              />
            )}
            {hasDepartureService && (
              <Chip 
                size="small" 
                color="info" 
                icon={<DirectionsCar fontSize="small" />}
                label="Departure Service"
                variant="outlined" 
                sx={{ height: 20, fontSize: '0.65rem' }} 
              />
            )}
          </Box>
        )}
      </Box>
      
      <CardContent sx={{ p: 1 }}>
        {!hasServices ? (
          <Alert severity="info" sx={{ py: 0.5 }}>No scheduled activities for this day</Alert>
        ) : (
          <Box sx={{ mt: 1 }}>
            {/* Hotels Section */}
            {hotelServices.length > 0 && (
              <Box sx={{ mb: 1.5 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5, pl: 1 }}>
                  <Hotel fontSize="small" sx={{ color: 'primary.main', mr: 0.5 }} />
                  <Typography variant="body2" fontWeight="bold">Accommodation</Typography>
                </Box>
                {hotelServices.map((service, idx) => (
                  <ServiceCard key={`hotel-${idx}`} service={service} />
                ))}
              </Box>
            )}
            
            {/* Attractions Section */}
            {attractionServices.length > 0 && (
              <Box sx={{ mb: 1.5 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5, pl: 1 }}>
                  <Attractions fontSize="small" sx={{ color: 'success.main', mr: 0.5 }} />
                  <Typography variant="body2" fontWeight="bold">Attractions</Typography>
                </Box>
                {attractionServices.map((service, idx) => (
                  <ServiceCard key={`attr-${idx}`} service={service} />
                ))}
              </Box>
            )}
            
            {/* Restaurants Section */}
            {restaurantServices.length > 0 && (
              <Box sx={{ mb: 1.5 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5, pl: 1 }}>
                  <Restaurant fontSize="small" sx={{ color: 'warning.main', mr: 0.5 }} />
                  <Typography variant="body2" fontWeight="bold">Dining</Typography>
                </Box>
                {restaurantServices.map((service, idx) => (
                  <ServiceCard key={`rest-${idx}`} service={service} />
                ))}
              </Box>
            )}
            
            {/* Transfers Section */}
            {transferServices.length > 0 && (
              <Box sx={{ mb: 1.5 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5, pl: 1 }}>
                  <DirectionsCar fontSize="small" sx={{ color: 'info.main', mr: 0.5 }} />
                  <Typography variant="body2" fontWeight="bold">Transportation</Typography>
                </Box>
                {transferServices.map((service, idx) => (
                  <ServiceCard key={`trans-${idx}`} service={service} />
                ))}
              </Box>
            )}
            
            {/* Other Activities Section */}
            {otherServices.length > 0 && (
              <Box sx={{ mb: 1.5 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5, pl: 1 }}>
                  <LocalActivity fontSize="small" sx={{ color: 'secondary.main', mr: 0.5 }} />
                  <Typography variant="body2" fontWeight="bold">Other Services</Typography>
                </Box>
                {otherServices.map((service, idx) => (
                  <ServiceCard key={`other-${idx}`} service={service} fallback={false} />
                ))}
              </Box>
            )}
            
            {/* Fallback for cases where no services match the categories above but we still have services */}
            {hotelServices.length === 0 && 
              attractionServices.length === 0 && 
              restaurantServices.length === 0 && 
              transferServices.length === 0 && 
              otherServices.length === 0 && 
              hasServices && (
                <Box sx={{ mb: 1.5 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5, pl: 1 }}>
                    <LocalActivity fontSize="small" sx={{ color: 'secondary.main', mr: 0.5 }} />
                    <Typography variant="body2" fontWeight="bold">Services</Typography>
                  </Box>
                  {day.services.map((service, idx) => (
                    <ServiceCard key={`fallback-${idx}`} service={service} fallback={false} />
                  ))}
                </Box>
            )}
          </Box>
        )}
      </CardContent>
    </Card>
  );
};

const BookingViewModal = ({ open, onClose, bookingData }) => {
  const theme = useTheme();
  const fullScreen = useMediaQuery(theme.breakpoints.down('md'));
  const { generatePDF } = usePDFGenerator();
  
  // Get agent info from Redux store
  const agencyLogo = useSelector((state) => state.auth.agencyLogo);
  const agentCompanyName = useSelector((state) => state.auth.agentCompanyName);
  
  if (!bookingData) {
    return null;
  }

  const handleClose = () => {
    onClose();
  };
  
  
  // Parse booking_details and travel_dates if present
  const bookingDetails = parseJsonSafely(bookingData.booking_details);
  const travelDates = parseJsonSafely(bookingData.travel_dates);
  
  const itinerary = bookingDetails?.itinerary || [];
  
  // Log the itinerary data for debugging
  // if (process.env.NODE_ENV === 'development') {
  //   console.log('Itinerary data:', itinerary);
  // }
  
  // const itinerary = bookingDetails?.itinerary || [];
  
  // // Log the itinerary data for debugging
  // if (process.env.NODE_ENV === 'development') {
  //   console.log('Itinerary data:', itinerary);
  // }
  
  return (
    <PDFGenerator bookingData={bookingData}>
      <Dialog
        open={open}
        onClose={handleClose}
        fullScreen={fullScreen}
        maxWidth="md"
        fullWidth
        TransitionComponent={Transition}
        PaperProps={{
          sx: {
            borderRadius: '12px',
            '@media print': {
              borderRadius: '0px',
              boxShadow: 'none',
              margin: '0',
              maxWidth: '100%',
              width: '100%'
            }
          }
        }}
      >
        {/* Dialog header */}
        <DialogTitle sx={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: 'center',
          borderBottom: '1px solid',
          borderColor: 'divider',
          p: 1.5,
          '@media print': {
            display: 'none'
          }
        }}>
          <Typography variant="h6" fontWeight={600}>
            Booking #{bookingData.bookingId || bookingData.booking_id}
          </Typography>
        </DialogTitle>
        
        {/* Dialog content */}
        <DialogContent sx={{ p: 2 }}>
          {/* Agent Branding Header */}
          {(agentCompanyName || agencyLogo) && (
            <Paper 
              elevation={3} 
              sx={{ 
                borderRadius: '12px', 
                mb: 3,
                overflow: 'hidden',
                background: 'linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)',
                border: '1px solid #e0e0e0',
                boxShadow: '0 4px 12px rgba(0,0,0,0.08)'
              }}
            >
                <Box sx={{ 
                 p: 3,
                 display: 'flex',
                 flexDirection: 'row',
                 alignItems: 'center',
                 justifyContent: 'center',
                 gap: 3,
                 position: 'relative',
                 '&::before': {
                   content: '""',
                   position: 'absolute',
                   top: 0,
                   left: 0,
                   right: 0,
                   height: '4px',
                   background: 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)',
                 }
               }}>
                 {agencyLogo && (
                   <Box 
                     component="img"
                     src={agencyLogo} 
                     alt={agentCompanyName || 'Agent Logo'}
                     sx={{ 
                       width: 120, 
                       height: 90,
                       objectFit: 'contain',
                       filter: 'drop-shadow(0 2px 4px rgba(0,0,0,0.1))',
                       transition: 'transform 0.2s ease',
                       '&:hover': {
                         transform: 'scale(1.05)'
                       }
                     }}
                   />
                 )}
                 <Box sx={{ textAlign: agencyLogo ? 'left' : 'center' }}>
                   <Typography 
                     variant="h5" 
                     fontWeight={700} 
                     sx={{ 
                       color: '#1a237e', 
                       mb: 0.5,
                       letterSpacing: '-0.5px',
                       lineHeight: 1.2
                     }}
                   >
                     {agentCompanyName || 'Travel Agency'}
                   </Typography>
                   {/* <Box sx={{ 
                     display: 'flex', 
                     alignItems: 'center', 
                     justifyContent: agencyLogo ? 'flex-start' : 'center',
                     gap: 1 
                   }}>
                     <Box sx={{ 
                       width: '30px', 
                       height: '2px', 
                       background: 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)',
                       borderRadius: '2px'
                     }} />
                     <Typography 
                       variant="body2" 
                       sx={{ 
                         color: '#5e6e82', 
                         textTransform: 'uppercase', 
                         letterSpacing: '1.5px',
                         fontSize: '0.75rem',
                         fontWeight: 600
                       }}
                     >
                       Travel Agency
                     </Typography>
                     <Box sx={{ 
                       width: '30px', 
                       height: '2px', 
                       background: 'linear-gradient(90deg, #764ba2 0%, #667eea 100%)',
                       borderRadius: '2px'
                     }} />
                   </Box> */}
                 </Box>
               </Box>
            </Paper>
          )}
          
          {/* Redesigned Customer and booking summary */}
          <Paper 
            elevation={1} 
            sx={{ 
              borderRadius: '8px', 
              mb: 2,
              overflow: 'hidden'
            }}
          >
            <Box sx={{ 
              bgcolor: theme.palette.primary.main, 
              color: 'white',
              py: 0.75,
              px: 2,
              borderTopLeftRadius: '8px',
              borderTopRightRadius: '8px',
            }}>
              <Typography variant="subtitle1" fontWeight="600">Booking Summary</Typography>
            </Box>
            
            <Grid container sx={{ p: 2 }} spacing={2}>
              <Grid item xs={12} md={6} sx={{ borderRight: { md: '1px solid', xs: 'none' }, borderColor: 'divider' }}>
                {/* Left Column - Customer Details */}
                <Grid container spacing={1}>
                  {/* Customer */}
                  <Grid item xs={12}>
                    <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                      <Person sx={{ color: 'primary.main', mt: 0.25, mr: 1.5, fontSize: '1.3rem' }} />
                      <Box>
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block' }}>Customer</Typography>
                        <Typography variant="body1" fontWeight={500}>{bookingData.customerName}</Typography>
                      </Box>
                    </Box>
                  </Grid>
                  
                  {/* Destination */}
                  <Grid item xs={12} sx={{ mt: 1 }}>
                    <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                      <LocationOn sx={{ color: 'error.main', mt: 0.25, mr: 1.5, fontSize: '1.3rem' }} />
                      <Box>
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block' }}>Destination</Typography>
                        <Typography variant="body1" fontWeight={500}>{bookingData.destination}</Typography>
                      </Box>
                    </Box>
                  </Grid>
                  
                  {/* Payment Amount */}
                  <Grid item xs={12} sx={{ mt: 1 }}>
                    <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                      <AttachMoney sx={{ color: 'success.main', mt: 0.25, mr: 1.5, fontSize: '1.3rem' }} />
                      <Box>
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block' }}>Payment Amount</Typography>
                        
                        {/* Base Amount */}
                        <Typography variant="body2" fontWeight={400} color="text.secondary" sx={{ mb: 0.5 }}>
                          SGD {bookingData.payment || bookingData.total_amount}
                        </Typography>
                        
                        {/* Total with Taxes - Green highlighted */}
                        {bookingData.taxes && (Array.isArray(bookingData.taxes) ? bookingData.taxes.length > 0 : bookingData.taxes) && (
                          <Box sx={{ 
                            display: 'inline-flex', 
                            alignItems: 'baseline',
                            padding: '4px 8px',
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            borderRadius: '6px',
                            mt: 0.5
                          }}>
                            <Typography variant="body1" sx={{ 
                              fontWeight: 600, 
                              color: 'success.main', 
                              mr: 0.5
                            }}>
                              SGD {calculateTotalWithTaxes(bookingData)}
                            </Typography>
                            <Typography variant="caption" sx={{ 
                              color: 'success.main',
                              fontStyle: 'italic'
                            }}>
                              (incl. tax)
                            </Typography>
                          </Box>
                        )}
                        
                        {/* If no taxes, show single amount */}
                        {(!bookingData.taxes || (Array.isArray(bookingData.taxes) && bookingData.taxes.length === 0)) && (
                          <Box sx={{ 
                            display: 'inline-flex', 
                            alignItems: 'baseline',
                            padding: '4px 8px',
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            borderRadius: '6px',
                            mt: 0.5
                          }}>
                            <Typography variant="body1" sx={{ 
                              fontWeight: 600, 
                              color: 'success.main'
                            }}>
                              SGD {bookingData.payment || bookingData.total_amount}
                            </Typography>
                          </Box>
                        )}
                      </Box>
                    </Box>
                  </Grid>
                  
                  {/* Number of Guests */}
                  <Grid item xs={12} sx={{ mt: 1 }}>
                    <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                      <PeopleAlt sx={{ color: 'info.main', mt: 0.25, mr: 1.5, fontSize: '1.3rem' }} />
                      <Box>
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block' }}>Number of Guests</Typography>
                        <Typography variant="body1" fontWeight={500}>
                          {/* Use the detailed guest info if available */}
                          {bookingDetails ? (
                            <>
                              {(bookingDetails.adult_count || 0) + (bookingDetails.child_count || 0)} person(s)
                              {(bookingDetails.adult_count > 0 || bookingDetails.child_count > 0 || 
                                bookingDetails.male_count > 0 || bookingDetails.female_count > 0) && (
                                <Typography variant="caption" display="block" color="text.secondary" sx={{ mt: 0.5 }}>
                                  {bookingDetails.adult_count > 0 && `${bookingDetails.adult_count} adults`}
                                  {bookingDetails.child_count > 0 && (bookingDetails.adult_count > 0 ? ', ' : '') + `${bookingDetails.child_count} children`}
                                  {(bookingDetails.male_count > 0 || bookingDetails.female_count > 0) && ' • '}
                                  {bookingDetails.male_count > 0 && `${bookingDetails.male_count} male`}
                                  {bookingDetails.female_count > 0 && (bookingDetails.male_count > 0 ? ', ' : '') + `${bookingDetails.female_count} female`}
                                </Typography>
                              )}
                            </>
                          ) : (
                            `${bookingData.pax} person(s)`
                          )}
                        </Typography>
                      </Box>
                    </Box>
                  </Grid>
                  
                  
                </Grid>
              </Grid>
              
              <Grid item xs={12} md={6}>
                {/* Right Column - Booking Details */}
                <Grid container spacing={1}>
                  {/* Dates Row */}
                  <Grid item xs={6}>
                    <Box sx={{ 
                      p: 1.5, 
                      bgcolor: 'grey.50', 
                      borderRadius: '6px',
                      height: '100%',
                      display: 'flex',
                      flexDirection: 'column'
                    }}>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <CalendarToday color="primary" fontSize="small" sx={{ mr: 1 }} />
                        <Typography variant="body2" color="text.secondary">Start Date</Typography>
                      </Box>
                      <Typography variant="body1" fontWeight={500} sx={{ mt: 0.5 }}>{bookingData.startDate}</Typography>
                    </Box>
                  </Grid>
                  
                  <Grid item xs={6}>
                    <Box sx={{ 
                      p: 1.5, 
                      bgcolor: 'grey.50', 
                      borderRadius: '6px',
                      height: '100%',
                      display: 'flex',
                      flexDirection: 'column'
                    }}>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <CalendarToday color="error" fontSize="small" sx={{ mr: 1 }} />
                        <Typography variant="body2" color="text.secondary">End Date</Typography>
                      </Box>
                      <Typography variant="body1" fontWeight={500} sx={{ mt: 0.5 }}>{bookingData.endDate}</Typography>
                    </Box>
                  </Grid>
                  
                  {/* Status Row */}
                  <Grid item xs={6} sx={{ mt: 1 }}>
                    <Box sx={{ 
                      p: 1.5, 
                      bgcolor: 'grey.50', 
                      borderRadius: '6px',
                      height: '100%'
                    }}>
                      <Typography variant="body2" color="text.secondary" gutterBottom>Booking Status</Typography>
                      <StatusChip status={bookingData.status} />
                    </Box>
                  </Grid>
                  
                  {/* <Grid item xs={6} sx={{ mt: 1 }}>
                    <Box sx={{ 
                      p: 1.5, 
                      bgcolor: 'grey.50', 
                      borderRadius: '6px',
                      height: '100%'
                    }}>
                      <Typography variant="body2" color="text.secondary" gutterBottom>Payment Status</Typography>
                      <PaymentStatusChip status={bookingData.paymentStatus} />
                    </Box>
                  </Grid> */}
                </Grid>
              </Grid>
            </Grid>
          </Paper>

        {/* Itinerary Section - All days displayed with collapse/expand */}
        {itinerary.length > 0 ? (
          <Box sx={{ mb: 2 }}>
            <Typography variant="subtitle1" fontWeight="bold" sx={{ mb: 1, display: 'flex', alignItems: 'center' }}>
              <CalendarToday sx={{ mr: 1, fontSize: '1.2rem' }} />
              Trip Itinerary ({itinerary.length} days)
            </Typography>
            
            {itinerary.map((day, index) => (
              <DayCard 
                key={index} 
                day={day} 
                index={index} 
                totalDays={itinerary.length} 
              />
            ))}
          </Box>
        ) : (
          <Alert severity="info" sx={{ my: 2 }}>No itinerary information available for this booking.</Alert>
        )}
        
        {/* Additional Details Cards - Always Open */}
        {bookingData.hotels && bookingData.hotels.length > 0 && (
          <Section 
            title="Accommodation" 
            icon={<Hotel />} 
            color="primary.main"
          >
            <List sx={{ p: 0 }}>
              {bookingData.hotels.map((hotel, index) => (
                <React.Fragment key={index}>
                  <ListItem sx={{ p: 1 }}>
                    <ListItemAvatar>
                      <Avatar 
                        src={hotel.main_image} 
                        alt={hotel.name} 
                        variant="rounded"
                        sx={{ width: 50, height: 50 }}
                      />
                    </ListItemAvatar>
                    <ListItemText 
                      primary={
                        <Typography variant="subtitle2" fontWeight="bold">
                          {hotel.name}
                        </Typography>
                      } 
                      secondary={
                        <Box>
                          <Box sx={{ display: 'flex', alignItems: 'center' }}>
                            <LocationOn fontSize="small" sx={{ mr: 0.5, color: 'error.light', fontSize: '0.8rem' }} />
                            <Typography variant="caption">{hotel.address}</Typography>
                          </Box>
                          <Box sx={{ display: 'flex', alignItems: 'center', mt: 0.5 }}>
                            <NightsStay fontSize="small" sx={{ mr: 0.5, color: 'primary.light', fontSize: '0.8rem' }} />
                            <Typography variant="caption">
                              {travelDates ? `${travelDates.check_in} to ${travelDates.check_out}` : 'Duration of stay'}
                            </Typography>
                          </Box>
                        </Box>
                      }
                      sx={{ ml: 1 }}
                    />
                  </ListItem>
                  {index < bookingData.hotels.length - 1 && <Divider />}
                </React.Fragment>
              ))}
            </List>
          </Section>
        )}
        
        {bookingData.attractions && bookingData.attractions.length > 0 && (
          <Section 
            title="Attractions" 
            icon={<Attractions />} 
            color="success.main"
          >
            <Grid container spacing={1} sx={{ p: 1 }}>
              {bookingData.attractions.map((attraction, index) => (
                <Grid item xs={6} sm={4} md={3} key={index}>
                  <Card elevation={1} sx={{ borderRadius: '8px', height: '100%', display: 'flex', flexDirection: 'column' }}>
                    <CardMedia
                      component="img"
                      height="80"
                      image={attraction.master_image}
                      alt={attraction.name}
                    />
                    <CardContent sx={{ p: 1, pt: 0.5, pb: '8px !important', flexGrow: 1 }}>
                      <Typography variant="body2" fontWeight="bold" noWrap>
                        {attraction.name}
                      </Typography>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <LocationOn fontSize="small" sx={{ mr: 0.5, color: 'error.light', fontSize: '0.7rem' }} />
                        <Typography variant="caption" noWrap>
                          {attraction.location}
                        </Typography>
                      </Box>
                    </CardContent>
                  </Card>
                </Grid>
              ))}
            </Grid>
          </Section>
        )}
        
        {bookingData.guides && bookingData.guides.length > 0 && (
          <Section 
            title="Tour Guides" 
            icon={<EmojiPeople />} 
            color="info.main"
          >
            <List sx={{ p: 0 }}>
              {bookingData.guides.map((guide, index) => (
                <ListItem key={index} sx={{ p: 1 }}>
                  <ListItemAvatar>
                    <Avatar 
                      src={guide.image} 
                      alt={guide.name} 
                      sx={{ width: 40, height: 40 }}
                    />
                  </ListItemAvatar>
                  <ListItemText 
                    primary={
                      <Typography variant="subtitle2" fontWeight="bold">
                        {guide.name}
                      </Typography>
                    }
                    secondary={
                      <Box>
                        <Grid container spacing={1}>
                          <Grid item xs={12} sm={6}>
                            <Box sx={{ display: 'flex', alignItems: 'center' }}>
                              <Phone fontSize="small" sx={{ mr: 0.5, color: 'primary.light', fontSize: '0.8rem' }} />
                              <Typography variant="caption">{guide.contact_no}</Typography>
                            </Box>
                          </Grid>
                          <Grid item xs={12} sm={6}>
                            <Box sx={{ display: 'flex', alignItems: 'center' }}>
                              <Email fontSize="small" sx={{ mr: 0.5, color: 'primary.light', fontSize: '0.8rem' }} />
                              <Typography variant="caption">{guide.email}</Typography>
                            </Box>
                          </Grid>
                        </Grid>
                        
                        {/* Show languages if available */}
                        {guide.languages && guide.languages.length > 0 && (
                          <Box sx={{ mt: 0.5 }}>
                            <Typography variant="caption" color="text.secondary">Languages:</Typography>
                            <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mt: 0.5 }}>
                              {guide.languages.map((lang, i) => (
                                <Chip key={i} label={typeof lang === 'object' ? lang.language : lang} 
                                  size="small" variant="outlined" 
                                  sx={{ height: 20, fontSize: '0.7rem' }} 
                                />
                              ))}
                            </Box>
                          </Box>
                        )}
                      </Box>
                    }
                  />
                </ListItem>
              ))}
            </List>
          </Section>
        )}
        
        {bookingData.restaurants && bookingData.restaurants.length > 0 && (
          <Section 
            title="Dining" 
            icon={<Restaurant />} 
            color="warning.main"
          >
            <Grid container spacing={1} sx={{ p: 1 }}>
              {bookingData.restaurants.map((restaurant, index) => (
                <Grid item xs={12} sm={6} md={4} key={index}>
                  <Card elevation={1} sx={{ borderRadius: '8px', overflow: 'hidden', display: 'flex', height: '100%' }}>
                    <CardMedia
                      component="img"
                      sx={{ width: 70 }}
                      image={restaurant.master_image}
                      alt={restaurant.name}
                    />
                    <CardContent sx={{ p: 1, pt: 0.5, pb: '8px !important', flexGrow: 1 }}>
                      <Typography variant="body2" fontWeight="bold">
                        {restaurant.name}
                      </Typography>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <LocationOn fontSize="small" sx={{ mr: 0.5, color: 'error.light', fontSize: '0.7rem' }} />
                        <Typography variant="caption">
                          {restaurant.city}
                        </Typography>
                      </Box>
                    </CardContent>
                  </Card>
                </Grid>
              ))}
            </Grid>
          </Section>
        )}
        
        {/* Page footer */}
        <Box sx={{ mt: 2, textAlign: 'center', color: 'text.secondary' }}>
          <Divider sx={{ mb: 1 }} />
          <Typography variant="caption" display="block" sx={{ mt: 0.5 }}>
            Booking Reference: #{bookingData.bookingId || bookingData.booking_id} • Generated on {new Date().toLocaleDateString('en-GB', {
              weekday: 'short',
              day: 'numeric',
              month: 'short',
              year: '2-digit',
            })}
          </Typography>
        </Box>
      </DialogContent>
      
      {/* Dialog actions */}
      <DialogActions sx={{ 
        p: 1.5, 
        borderTop: '1px solid', 
        borderColor: 'divider',
        '@media print': {
          display: 'none'
        }
      }}>
        <PDFPrintButton 
          variant="contained"
          color="primary"
          size="small"
          sx={{ mr: 1 }}
        >
          Download PDF
        </PDFPrintButton>
        <Button 
          onClick={handleClose} 
          variant="outlined"
          color="primary"
          size="small"
        >
          Close
        </Button>
      </DialogActions>
      </Dialog>
    </PDFGenerator>
  );
};

export default BookingViewModal; 