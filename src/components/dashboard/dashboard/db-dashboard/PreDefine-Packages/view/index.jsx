import React, { useState, useEffect, useRef } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { fetchPackageBookingLists } from '../../../../../../slice/tour-packages/prePackagesSlice';
import {
  Box,
  Grid,
  Card,
  Typography,
  Divider,
  Chip,
  Button,
  Paper,
  Tabs,
  Tab,
  Avatar,
  List,
  IconButton,
  Tooltip,
  CircularProgress,
  Alert,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  useMediaQuery,
  useTheme,
  Slide
} from '@mui/material';
import {
  Close,
  CalendarToday,
  PeopleAlt,
  LocationOn,
  AttachMoney,
  Info,
  Attractions,
  Hotel,
  Restaurant,
  Person,
  CheckCircle,
  Download,
  Print
} from '@mui/icons-material';
import { StatusChip, PaymentStatusChip } from '../StatusChips';

// Slide transition for modal
const Transition = React.forwardRef(function Transition(props, ref) {
  return <Slide direction="up" ref={ref} {...props} />;
});

// Helper function to format date strings
const formatDate = (dateString) => {
  if (!dateString) return 'Not specified';
  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
      return 'Not specified';
    }
    
    return date.toLocaleDateString('en-US', { 
      day: 'numeric', 
      month: 'short', 
      year: 'numeric' 
    });
  } catch (error) {
    console.error("Error formatting date:", error, dateString);
    return 'Not specified';
  }
};

// Helper function to parse JSON strings safely
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

// ItineraryDay component
const ItineraryDay = ({ day, date, activities }) => {
  return (
    <Paper elevation={1} sx={{ p: 2, mb: 2, borderRadius: '10px' }}>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <Chip 
            label={`Day ${day}`} 
            color="primary" 
            size="small" 
            sx={{ mr: 1 }}
          />
          <Typography variant="subtitle1" fontWeight={600}>
            {formatDate(date)}
          </Typography>
        </Box>
      </Box>
      
      <Divider sx={{ my: 1 }} />
      
      <Box>
        {activities ? (
          <Typography variant="body2">
            {activities}
          </Typography>
        ) : (
          <Typography variant="body2" color="text.secondary" sx={{ fontStyle: 'italic' }}>
            No detailed itinerary available for this day.
          </Typography>
        )}
      </Box>
    </Paper>
  );
};

// Main component
const PackageBookingViewModal = ({ open, onClose, bookingId }) => {
  const theme = useTheme();
  const fullScreen = useMediaQuery(theme.breakpoints.down('md'));
  const dispatch = useDispatch();
  const [tabValue, setTabValue] = useState(0);
  const [booking, setBooking] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // References for scrolling to different itinerary days
  const contentRef = useRef(null);
  const dayRefs = useRef([]);
  const [activeDay, setActiveDay] = useState(0);
  
  // Get booking data from Redux store
  const { bookingLists, bookingListsLoading, bookingListsError } = useSelector((state) => state.prePackages);
  
  // Generate itinerary days between check-in and check-out
  const generateItineraryDays = (checkIn, checkOut) => {
    if (!checkIn || !checkOut) return [];
    
    const startDate = new Date(checkIn);
    const endDate = new Date(checkOut);
    const days = [];
    
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) return [];
    
    // Calculate the number of days between dates
    const diffInTime = endDate.getTime() - startDate.getTime();
    const diffInDays = Math.ceil(diffInTime / (1000 * 3600 * 24)) + 1; // +1 to include both start and end dates
    
    for (let i = 0; i < diffInDays; i++) {
      const currentDate = new Date(startDate);
      currentDate.setDate(startDate.getDate() + i);
      
      days.push({
        day: i + 1,
        date: currentDate,
        activities: i === 0 
          ? "Arrival & Check-in" 
          : i === diffInDays - 1 
            ? "Check-out & Departure" 
            : `Day ${i + 1} activities and sightseeing`
      });
    }
    
    return days;
  };
  
  // Fetch booking data when component mounts or booking ID changes
  useEffect(() => {
    if (open && bookingId) {
      setIsLoading(true);
      setError(null);
      dispatch(fetchPackageBookingLists());
    }
  }, [dispatch, open, bookingId]);
  
  // Find and set the specific booking when data is available
  useEffect(() => {
    if (!open || !bookingId) return;
    
    if (bookingListsLoading) {
      setIsLoading(true);
      return;
    }
    
    setIsLoading(false);
    
    if (bookingListsError) {
      setError(bookingListsError);
      return;
    }
    
    if (bookingLists) {
      // Find the specific booking
      const foundBooking = Array.isArray(bookingLists) 
        ? bookingLists.find(b => b.booking_id.toString() === bookingId.toString())
        : bookingLists.booking_lists && Array.isArray(bookingLists.booking_lists)
          ? bookingLists.booking_lists.find(b => b.booking_id.toString() === bookingId.toString())
          : bookingLists.data && Array.isArray(bookingLists.data)
            ? bookingLists.data.find(b => b.booking_id.toString() === bookingId.toString())
            : null;
      
      if (foundBooking) {
        // Parse nested JSON strings in the booking
        const processedBooking = {
          ...foundBooking,
          bookingDetails: parseJsonSafely(foundBooking.booking_details),
          travelDates: parseJsonSafely(foundBooking.travel_dates),
          package: parseJsonSafely(foundBooking.package),
          userInfo: parseJsonSafely(foundBooking.user_info)
        };
        
        setBooking(processedBooking);
        
        // Initialize refs for each day for scrolling
        if (processedBooking.travelDates) {
          const days = generateItineraryDays(
            processedBooking.travelDates.check_in,
            processedBooking.travelDates.check_out
          );
          
          dayRefs.current = days.map(() => React.createRef());
        }
      } else {
        setError(`Booking with ID ${bookingId} not found`);
      }
    }
  }, [bookingLists, bookingListsLoading, bookingListsError, bookingId, open]);
  
  // Handle tab change
  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };
  
  // Reset state when modal closes
  const handleClose = () => {
    setTabValue(0);
    onClose();
  };
  
  // Render content based on loading/error state
  const renderContent = () => {
    if (isLoading) {
      return (
        <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '300px' }}>
          <CircularProgress />
        </Box>
      );
    }
    
    if (error) {
      return (
        <Alert severity="error" sx={{ m: 3 }}>
          {error}
        </Alert>
      );
    }
    
    if (!booking) {
      return (
        <Alert severity="warning" sx={{ m: 3 }}>
          Booking information is not available.
        </Alert>
      );
    }
    
    // Get travel dates
    const travelDates = booking.travelDates || 
                      (booking.bookingDetails && booking.bookingDetails.travel_dates) || 
                      {};
    
    // Generate itinerary days
    const itineraryDays = generateItineraryDays(travelDates.check_in, travelDates.check_out);
    
    // Customer info
    const customerName = 
      (booking.userInfo && booking.userInfo.fullName) || 
      booking.customer_name || 
      'Customer';
      
    // Destination info
    const destination = 
      (booking.package && booking.package.destination) || 
      booking.destination || 
      (booking.hotels && booking.hotels[0] && booking.hotels[0].city) ||
      (booking.attractions && booking.attractions[0] && booking.attractions[0].location) ||
      'Destination not specified';
      
    // Calculate total people count
    const adultCount = booking.bookingDetails?.adult_count || 0;
    const childCount = booking.bookingDetails?.child_count || 0;
    const totalPax = adultCount + childCount || 0;
    
    // Payment details
    const totalPrice = booking.bookingDetails?.total_price || 0;
    const currency = booking.bookingDetails?.currency || 'USD';
    
    return (
      <>
        {/* Status cards row */}
        <Grid container spacing={3} sx={{ mb: 3 }}>
          <Grid item xs={12} lg={8}>
            <Card sx={{ p: 2, borderRadius: '12px' }}>
              <Box sx={{ display: 'flex', flexWrap: 'wrap', justifyContent: 'space-between', alignItems: 'center' }}>
                {/* Customer info */}
                <Box sx={{ display: 'flex', alignItems: 'center', mb: { xs: 2, md: 0 } }}>
                  <Avatar sx={{ mr: 2, backgroundColor: '#e3f2fd' }}>
                    <Person sx={{ color: '#1976d2' }} />
                  </Avatar>
                  <Box>
                    <Typography variant="subtitle2" color="text.secondary">Customer</Typography>
                    <Typography variant="h6" fontWeight={600}>{customerName}</Typography>
                  </Box>
                </Box>
                
                {/* Destination */}
                <Box sx={{ display: 'flex', alignItems: 'center', mb: { xs: 2, md: 0 } }}>
                  <Avatar sx={{ mr: 2, backgroundColor: '#fff3e0' }}>
                    <LocationOn sx={{ color: '#ff9800' }} />
                  </Avatar>
                  <Box>
                    <Typography variant="subtitle2" color="text.secondary">Destination</Typography>
                    <Typography variant="h6" fontWeight={600}>{destination}</Typography>
                  </Box>
                </Box>
                
                {/* Status */}
                <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', mb: { xs: 2, md: 0 } }}>
                  <Typography variant="subtitle2" color="text.secondary" sx={{ mb: 1 }}>Status</Typography>
                  <StatusChip status={booking.status} />
                </Box>
                
                {/* Payment Status */}
                <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                  <Typography variant="subtitle2" color="text.secondary" sx={{ mb: 1 }}>Payment</Typography>
                  <PaymentStatusChip status="Paid" />
                </Box>
              </Box>
            </Card>
          </Grid>
          
          {/* Trip highlights */}
          <Grid item xs={12} lg={4}>
            <Card sx={{ p: 2, borderRadius: '12px', height: '100%' }}>
              <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 1.5 }}>
                Trip Highlights
              </Typography>
              
              <Grid container spacing={2}>
                {/* Duration */}
                <Grid item xs={6}>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <CalendarToday sx={{ fontSize: 18, color: 'primary.main', mr: 1 }} />
                    <Box>
                      <Typography variant="body2" color="text.secondary">Duration</Typography>
                      <Typography variant="body1" fontWeight={500}>
                        {itineraryDays.length} days
                      </Typography>
                    </Box>
                  </Box>
                </Grid>
                
                {/* Total guests */}
                <Grid item xs={6}>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <PeopleAlt sx={{ fontSize: 18, color: 'primary.main', mr: 1 }} />
                    <Box>
                      <Typography variant="body2" color="text.secondary">Guests</Typography>
                      <Typography variant="body1" fontWeight={500}>
                        {totalPax} ({adultCount} adult{adultCount !== 1 ? 's' : ''}{childCount > 0 ? `, ${childCount} child${childCount !== 1 ? 'ren' : ''}` : ''})
                      </Typography>
                    </Box>
                  </Box>
                </Grid>
                
                {/* Trip cost */}
                <Grid item xs={12}>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <AttachMoney sx={{ fontSize: 18, color: 'primary.main', mr: 1 }} />
                    <Box>
                      <Typography variant="body2" color="text.secondary">Total Cost</Typography>
                      <Typography variant="body1" fontWeight={600} color="success.main">
                        {currency} {totalPrice.toLocaleString()}
                      </Typography>
                    </Box>
                  </Box>
                </Grid>
              </Grid>
            </Card>
          </Grid>
        </Grid>
        
        {/* Main content tabs */}
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Card sx={{ borderRadius: '12px', overflow: 'hidden' }}>
              <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
                <Tabs 
                  value={tabValue} 
                  onChange={handleTabChange}
                  variant="scrollable"
                  scrollButtons="auto"
                  sx={{ px: 2, pt: 1 }}
                >
                  <Tab label="Itinerary" icon={<CalendarToday />} iconPosition="start" />
                  <Tab label="Accommodations" icon={<Hotel />} iconPosition="start" />
                  <Tab label="Attractions" icon={<Attractions />} iconPosition="start" />
                  <Tab label="Dining" icon={<Restaurant />} iconPosition="start" />
                  <Tab label="Service Providers" icon={<Person />} iconPosition="start" />
                </Tabs>
              </Box>
              
              {/* Tab content */}
              <Box sx={{ p: 3 }}>
                {/* Itinerary Tab */}
                {tabValue === 0 && (
                  <Box>
                    <Typography variant="h6" fontWeight={600} sx={{ mb: 2 }}>
                      Trip Itinerary
                    </Typography>
                    
                    <Box sx={{ mb: 3 }}>
                      <Chip 
                        icon={<CheckCircle />}
                        label={`${formatDate(travelDates.check_in)} - ${formatDate(travelDates.check_out)}`}
                        color="primary"
                        sx={{ mr: 1, mb: 1 }}
                      />
                      <Chip 
                        icon={<Info />}
                        label={`${itineraryDays.length} day itinerary`}
                        variant="outlined"
                        sx={{ mr: 1, mb: 1 }}
                      />
                    </Box>
                    
                    <Box ref={contentRef} sx={{ maxHeight: '400px', overflow: 'auto', pr: 2 }}>
                      {itineraryDays.map((day, index) => (
                        <ItineraryDay
                          key={index}
                          day={day.day}
                          date={day.date}
                          activities={day.activities}
                        />
                      ))}
                    </Box>
                  </Box>
                )}
                
                {/* Accommodations Tab */}
                {tabValue === 1 && (
                  <Box>
                    <Typography variant="h6" fontWeight={600} sx={{ mb: 2 }}>
                      Accommodations
                    </Typography>
                    
                    {booking.hotels && booking.hotels.length > 0 ? (
                      <List sx={{ width: '100%', bgcolor: 'background.paper', maxHeight: '400px', overflow: 'auto' }}>
                        {booking.hotels.map((hotel, index) => (
                          <Paper key={index} elevation={1} sx={{ mb: 2, p: 2, borderRadius: '10px' }}>
                            <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                              <Avatar 
                                variant="rounded"
                                src={hotel.main_image}
                                alt={hotel.name}
                                sx={{ width: 80, height: 80, mr: 2 }}
                              />
                              <Box sx={{ flex: 1 }}>
                                <Typography variant="subtitle1" fontWeight={600}>
                                  {hotel.name}
                                </Typography>
                                <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                                  <LocationOn sx={{ fontSize: 18, color: 'error.main', mr: 0.5 }} />
                                  <Typography variant="body2" color="text.secondary">
                                    {hotel.address}
                                  </Typography>
                                </Box>
                                <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1 }}>
                                  <Chip label="Included" size="small" color="success" variant="outlined" />
                                  <Chip label={`Check-in: ${formatDate(travelDates.check_in)}`} size="small" />
                                  <Chip label={`Check-out: ${formatDate(travelDates.check_out)}`} size="small" />
                                </Box>
                              </Box>
                            </Box>
                          </Paper>
                        ))}
                      </List>
                    ) : (
                      <Alert severity="info">
                        No accommodation information available for this booking.
                      </Alert>
                    )}
                  </Box>
                )}
                
                {/* Attractions Tab */}
                {tabValue === 2 && (
                  <Box>
                    <Typography variant="h6" fontWeight={600} sx={{ mb: 2 }}>
                      Attractions
                    </Typography>
                    
                    {booking.attractions && booking.attractions.length > 0 ? (
                      <Box sx={{ maxHeight: '400px', overflow: 'auto' }}>
                        <Grid container spacing={2}>
                          {booking.attractions.map((attraction, index) => (
                            <Grid item xs={12} md={6} key={index}>
                              <Paper elevation={1} sx={{ p: 2, borderRadius: '10px', height: '100%' }}>
                                <Box sx={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
                                  <Box sx={{ position: 'relative', mb: 2 }}>
                                    <img 
                                      src={attraction.master_image} 
                                      alt={attraction.name}
                                      style={{ 
                                        width: '100%', 
                                        height: '160px', 
                                        objectFit: 'cover', 
                                        borderRadius: '8px' 
                                      }}
                                    />
                                    <Chip 
                                      label="Included" 
                                      color="success" 
                                      size="small"
                                      sx={{ 
                                        position: 'absolute', 
                                        top: 10, 
                                        right: 10,
                                        backgroundColor: 'rgba(76, 175, 80, 0.9)'
                                      }}
                                    />
                                  </Box>
                                  
                                  <Typography variant="h6" fontWeight={600}>
                                    {attraction.name}
                                  </Typography>
                                  
                                  <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                                    <LocationOn sx={{ fontSize: 18, color: 'error.main', mr: 0.5 }} />
                                    <Typography variant="body2" color="text.secondary">
                                      {attraction.location}
                                    </Typography>
                                  </Box>
                                </Box>
                              </Paper>
                            </Grid>
                          ))}
                        </Grid>
                      </Box>
                    ) : (
                      <Alert severity="info">
                        No attraction information available for this booking.
                      </Alert>
                    )}
                  </Box>
                )}
                
                {/* Dining Tab */}
                {tabValue === 3 && (
                  <Box>
                    <Typography variant="h6" fontWeight={600} sx={{ mb: 2 }}>
                      Dining
                    </Typography>
                    
                    {booking.restaurants && booking.restaurants.length > 0 ? (
                      <Box sx={{ maxHeight: '400px', overflow: 'auto' }}>
                        <Grid container spacing={2}>
                          {booking.restaurants.map((restaurant, index) => (
                            <Grid item xs={12} md={6} key={index}>
                              <Paper elevation={1} sx={{ p: 2, borderRadius: '10px' }}>
                                <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                                  <Avatar 
                                    variant="rounded"
                                    src={restaurant.master_image}
                                    alt={restaurant.name}
                                    sx={{ width: 60, height: 60, mr: 2 }}
                                  />
                                  <Box sx={{ flex: 1 }}>
                                    <Typography variant="subtitle1" fontWeight={600}>
                                      {restaurant.name}
                                    </Typography>
                                    <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                                      <LocationOn sx={{ fontSize: 18, color: 'error.main', mr: 0.5 }} />
                                      <Typography variant="body2" color="text.secondary">
                                        {restaurant.city}
                                      </Typography>
                                    </Box>
                                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1 }}>
                                      <Chip label="Included" size="small" color="success" variant="outlined" />
                                    </Box>
                                  </Box>
                                </Box>
                              </Paper>
                            </Grid>
                          ))}
                        </Grid>
                      </Box>
                    ) : (
                      <Alert severity="info">
                        No dining information available for this booking.
                      </Alert>
                    )}
                  </Box>
                )}
                
                {/* Service Providers Tab */}
                {tabValue === 4 && (
                  <Box>
                    <Typography variant="h6" fontWeight={600} sx={{ mb: 2 }}>
                      Tour Guides & Service Providers
                    </Typography>
                    
                    {booking.guides && booking.guides.length > 0 ? (
                      <List sx={{ width: '100%', bgcolor: 'background.paper', maxHeight: '400px', overflow: 'auto' }}>
                        {booking.guides.map((guide, index) => (
                          <Paper key={index} elevation={1} sx={{ mb: 2, p: 2, borderRadius: '10px' }}>
                            <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                              <Avatar 
                                src={guide.image}
                                alt={guide.name}
                                sx={{ width: 70, height: 70, mr: 2 }}
                              />
                              <Box sx={{ flex: 1 }}>
                                <Typography variant="subtitle1" fontWeight={600}>
                                  {guide.name}
                                </Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ mt: 0.5 }}>
                                  Tour Guide
                                </Typography>
                                <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                                  <Typography variant="body2" color="text.secondary" sx={{ mr: 1 }}>
                                    Contact: {guide.contact_no}
                                  </Typography>
                                  <Typography variant="body2" color="text.secondary">
                                    Email: {guide.email}
                                  </Typography>
                                </Box>
                                <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1 }}>
                                  {guide.languages && guide.languages.map((lang, i) => (
                                    <Chip key={i} label={lang} size="small" />
                                  ))}
                                </Box>
                              </Box>
                            </Box>
                          </Paper>
                        ))}
                      </List>
                    ) : (
                      <Alert severity="info">
                        No guide information available for this booking.
                      </Alert>
                    )}
                  </Box>
                )}
              </Box>
            </Card>
          </Grid>
        </Grid>
      </>
    );
  };
  
  return (
    <Dialog
      open={open}
      onClose={handleClose}
      fullScreen={fullScreen}
      maxWidth="lg"
      fullWidth
      TransitionComponent={Transition}
      PaperProps={{
        sx: {
          borderRadius: '12px',
          maxHeight: '90vh'
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
        p: 2
      }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <Typography variant="h6" fontWeight={600}>
            Booking Details: #{bookingId}
          </Typography>
        </Box>
        
        <Box sx={{ display: 'flex', gap: 1 }}>
          <Tooltip title="Download Itinerary">
            <Button 
              startIcon={<Download />} 
              variant="outlined" 
              color="primary"
              size="small"
            >
              Download
            </Button>
          </Tooltip>
          
          <Tooltip title="Print">
            <Button 
              startIcon={<Print />} 
              variant="outlined" 
              color="secondary"
              size="small"
            >
              Print
            </Button>
          </Tooltip>
          
          <IconButton onClick={handleClose} size="small">
            <Close />
          </IconButton>
        </Box>
      </DialogTitle>
      
      {/* Dialog content */}
      <DialogContent dividers sx={{ p: 3 }}>
        {renderContent()}
      </DialogContent>
      
      {/* Dialog actions */}
      <DialogActions sx={{ p: 2, borderTop: '1px solid', borderColor: 'divider' }}>
        <Button 
          onClick={handleClose} 
          variant="outlined"
          color="primary"
        >
          Close
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default PackageBookingViewModal;
