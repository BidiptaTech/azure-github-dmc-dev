import React, { useState } from 'react';
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
  Tabs,
  Tab,
  Avatar,
  Divider,
  List,
  ListItem,
  ListItemAvatar,
  ListItemText,
  Alert
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
  Email
} from '@mui/icons-material';
import { StatusChip, PaymentStatusChip } from './StatusChips';

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

// TabPanel component for tabs
function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`booking-tabpanel-${index}`}
      aria-labelledby={`booking-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          {children}
        </Box>
      )}
    </div>
  );
}

function a11yProps(index) {
  return {
    id: `booking-tab-${index}`,
    'aria-controls': `booking-tabpanel-${index}`,
  };
}

const BookingViewModal = ({ open, onClose, bookingData }) => {
  const theme = useTheme();
  const fullScreen = useMediaQuery(theme.breakpoints.down('md'));
  const [tabValue, setTabValue] = useState(0);
  
  if (!bookingData) {
    return null;
  }

  const handleClose = () => {
    onClose();
  };
  
  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };

  // Check if we have API rich data
  const hasAPIData = bookingData.hotels || bookingData.attractions || bookingData.guides || bookingData.restaurants;

  // Parse booking_details and travel_dates if present
  const bookingDetails = parseJsonSafely(bookingData.booking_details);
  const travelDates = parseJsonSafely(bookingData.travel_dates);
  
  return (
    <Dialog
      open={open}
      onClose={handleClose}
      fullScreen={fullScreen}
      maxWidth="lg"
      fullWidth={hasAPIData}
      TransitionComponent={Transition}
      PaperProps={{
        sx: {
          borderRadius: '12px',
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
        <Typography variant="h6" fontWeight={600}>
          Booking Details: #{bookingData.bookingId || bookingData.booking_id}
        </Typography>
        <Button 
          variant="contained" 
          color="error" 
          size="small" 
          startIcon={<Close />}
          onClick={handleClose}
        >
          Close
        </Button>
      </DialogTitle>
      
      {/* Dialog content */}
      <DialogContent sx={{ p: 3 }}>
        <Paper elevation={1} sx={{ p: 3, borderRadius: '10px', mb: 3 }}>
          <Grid container spacing={3}>
            {/* Customer Name */}
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <Person sx={{ color: 'primary.main', mr: 1.5 }} />
                <Box>
                  <Typography variant="body2" color="text.secondary">Customer Name</Typography>
                  <Typography variant="h6" fontWeight={500}>{bookingData.customerName}</Typography>
                </Box>
              </Box>
            </Grid>
            
            {/* Destination */}
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <LocationOn sx={{ color: 'error.main', mr: 1.5 }} />
                <Box>
                  <Typography variant="body2" color="text.secondary">Destination</Typography>
                  <Typography variant="h6" fontWeight={500}>{bookingData.destination}</Typography>
                </Box>
              </Box>
            </Grid>
            
            {/* Start Date */}
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <CalendarToday sx={{ color: 'primary.main', mr: 1.5 }} />
                <Box>
                  <Typography variant="body2" color="text.secondary">Start Date</Typography>
                  <Typography variant="body1">{bookingData.startDate}</Typography>
                </Box>
              </Box>
            </Grid>
            
            {/* End Date */}
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <CalendarToday sx={{ color: 'error.main', mr: 1.5 }} />
                <Box>
                  <Typography variant="body2" color="text.secondary">End Date</Typography>
                  <Typography variant="body1">{bookingData.endDate}</Typography>
                </Box>
              </Box>
            </Grid>
            
            {/* Number of Guests */}
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <PeopleAlt sx={{ color: 'info.main', mr: 1.5 }} />
                <Box>
                  <Typography variant="body2" color="text.secondary">Number of Guests</Typography>
                  <Typography variant="body1">
                    {/* Use the detailed guest info if available */}
                    {bookingDetails ? (
                      <>
                        {(bookingDetails.adult_count || 0) + (bookingDetails.child_count || 0)} person(s)
                        {bookingDetails.male_count > 0 || bookingDetails.female_count > 0 ? (
                          <Typography variant="caption" display="block" color="text.secondary">
                            {bookingDetails.male_count || 0} male, {bookingDetails.female_count || 0} female
                          </Typography>
                        ) : null}
                        {bookingDetails.adult_count > 0 || bookingDetails.child_count > 0 ? (
                          <Typography variant="caption" display="block" color="text.secondary">
                            {bookingDetails.adult_count || 0} adults, {bookingDetails.child_count || 0} children
                          </Typography>
                        ) : null}
                      </>
                    ) : (
                      `${bookingData.pax} person(s)`
                    )}
                  </Typography>
                </Box>
              </Box>
            </Grid>
            
            {/* Payment Amount */}
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <AttachMoney sx={{ color: 'success.main', mr: 1.5 }} />
                <Box>
                  <Typography variant="body2" color="text.secondary">Payment Amount</Typography>
                  <Typography variant="body1" fontWeight={500}>
                    {bookingDetails?.currency || 'USD'} {bookingDetails?.total_price || bookingData.payment}
                  </Typography>
                </Box>
              </Box>
            </Grid>
            
            {/* Status */}
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>Booking Status</Typography>
                <StatusChip status={bookingData.status} />
              </Box>
            </Grid>
            
            {/* Payment Status */}
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>Payment Status</Typography>
                <PaymentStatusChip status={bookingData.paymentStatus} />
              </Box>
            </Grid>
          </Grid>
        </Paper>

        {/* Additional Details Sections (only show if API data is present) */}
        {hasAPIData && (
          <Box sx={{ width: '100%' }}>
            <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
              <Tabs value={tabValue} onChange={handleTabChange} aria-label="booking details tabs">
                {bookingData.hotels && bookingData.hotels.length > 0 && (
                  <Tab icon={<Hotel />} label="Hotels" iconPosition="start" {...a11yProps(0)} />
                )}
                {bookingData.attractions && bookingData.attractions.length > 0 && (
                  <Tab icon={<Attractions />} label="Attractions" iconPosition="start" {...a11yProps(1)} />
                )}
                {bookingData.restaurants && bookingData.restaurants.length > 0 && (
                  <Tab icon={<Restaurant />} label="Restaurants" iconPosition="start" {...a11yProps(2)} />
                )}
                {bookingData.guides && bookingData.guides.length > 0 && (
                  <Tab icon={<EmojiPeople />} label="Guides" iconPosition="start" {...a11yProps(3)} />
                )}
              </Tabs>
            </Box>
            
            {/* Hotels Tab */}
            {bookingData.hotels && bookingData.hotels.length > 0 && (
              <TabPanel value={tabValue} index={0}>
                <Typography variant="h6" gutterBottom>Accommodation</Typography>
                <Grid container spacing={3}>
                  {bookingData.hotels.map((hotel, index) => (
                    <Grid item xs={12} key={index}>
                      <Paper elevation={2} sx={{ p: 2, borderRadius: '10px' }}>
                        <Grid container spacing={2}>
                          <Grid item xs={12} sm={4} md={3}>
                            <img 
                              src={hotel.main_image}
                              alt={hotel.name}
                              style={{ 
                                width: '100%', 
                                height: '160px', 
                                objectFit: 'cover', 
                                borderRadius: '8px'
                              }}
                            />
                          </Grid>
                          <Grid item xs={12} sm={8} md={9}>
                            <Typography variant="h6">{hotel.name}</Typography>
                            <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                              <LocationOn fontSize="small" sx={{ mr: 0.5, color: 'error.main' }} />
                              <Typography variant="body2">{hotel.address}</Typography>
                            </Box>
                            <Divider sx={{ my: 1.5 }} />
                            <Grid container spacing={2}>
                              <Grid item xs={12} sm={6}>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <Phone fontSize="small" sx={{ mr: 0.5, color: 'primary.main' }} />
                                  <Typography variant="body2">{hotel.phone}</Typography>
                                </Box>
                              </Grid>
                              <Grid item xs={12} sm={6}>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <Email fontSize="small" sx={{ mr: 0.5, color: 'primary.main' }} />
                                  <Typography variant="body2">{hotel.email}</Typography>
                                </Box>
                              </Grid>
                            </Grid>
                          </Grid>
                        </Grid>
                      </Paper>
                    </Grid>
                  ))}
                </Grid>
              </TabPanel>
            )}
            
            {/* Attractions Tab */}
            {bookingData.attractions && bookingData.attractions.length > 0 && (
              <TabPanel value={tabValue} index={bookingData.hotels && bookingData.hotels.length > 0 ? 1 : 0}>
                <Typography variant="h6" gutterBottom>Places To Visit</Typography>
                <Grid container spacing={3}>
                  {bookingData.attractions.map((attraction, index) => (
                    <Grid item xs={12} sm={6} md={4} key={index}>
                      <Paper elevation={2} sx={{ borderRadius: '10px', overflow: 'hidden', height: '100%', display: 'flex', flexDirection: 'column' }}>
                        <Box sx={{ position: 'relative' }}>
                          <img 
                            src={attraction.master_image}
                            alt={attraction.name}
                            style={{ 
                              width: '100%', 
                              height: '180px', 
                              objectFit: 'cover'
                            }}
                          />
                          <Chip 
                            label="Included" 
                            color="success" 
                            size="small" 
                            sx={{ 
                              position: 'absolute', 
                              bottom: 10, 
                              right: 10,
                              backgroundColor: 'rgba(76, 175, 80, 0.9)'
                            }}
                          />
                        </Box>
                        <Box sx={{ p: 2, flex: 1, display: 'flex', flexDirection: 'column' }}>
                          <Typography variant="h6" sx={{ mb: 1 }}>{attraction.name}</Typography>
                          <Box sx={{ display: 'flex', alignItems: 'center', mt: 'auto' }}>
                            <LocationOn fontSize="small" sx={{ mr: 0.5, color: 'error.main' }} />
                            <Typography variant="body2">{attraction.location}</Typography>
                          </Box>
                        </Box>
                      </Paper>
                    </Grid>
                  ))}
                </Grid>
              </TabPanel>
            )}
            
            {/* Restaurants Tab */}
            {bookingData.restaurants && bookingData.restaurants.length > 0 && (
              <TabPanel value={tabValue} index={
                (bookingData.hotels && bookingData.hotels.length > 0 ? 1 : 0) + 
                (bookingData.attractions && bookingData.attractions.length > 0 ? 1 : 0)
              }>
                <Typography variant="h6" gutterBottom>Dining</Typography>
                <Grid container spacing={3}>
                  {bookingData.restaurants.map((restaurant, index) => (
                    <Grid item xs={12} sm={6} key={index}>
                      <Paper elevation={2} sx={{ p: 2, borderRadius: '10px' }}>
                        <Grid container spacing={2}>
                          <Grid item xs={4}>
                            <img 
                              src={restaurant.master_image}
                              alt={restaurant.name}
                              style={{ 
                                width: '100%', 
                                height: '100px', 
                                objectFit: 'cover', 
                                borderRadius: '8px'
                              }}
                            />
                          </Grid>
                          <Grid item xs={8}>
                            <Typography variant="h6">{restaurant.name}</Typography>
                            <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                              <LocationOn fontSize="small" sx={{ mr: 0.5, color: 'error.main' }} />
                              <Typography variant="body2">{restaurant.city}</Typography>
                            </Box>
                            <Chip 
                              label="Included" 
                              color="success" 
                              size="small" 
                              variant="outlined"
                              sx={{ mt: 1 }}
                            />
                          </Grid>
                        </Grid>
                      </Paper>
                    </Grid>
                  ))}
                </Grid>
              </TabPanel>
            )}
            
            {/* Guides Tab */}
            {bookingData.guides && bookingData.guides.length > 0 && (
              <TabPanel value={tabValue} index={
                (bookingData.hotels && bookingData.hotels.length > 0 ? 1 : 0) + 
                (bookingData.attractions && bookingData.attractions.length > 0 ? 1 : 0) + 
                (bookingData.restaurants && bookingData.restaurants.length > 0 ? 1 : 0)
              }>
                <Typography variant="h6" gutterBottom>Tour Guides</Typography>
                <Grid container spacing={3}>
                  {bookingData.guides.map((guide, index) => (
                    <Grid item xs={12} sm={6} key={index}>
                      <Paper elevation={2} sx={{ p: 2, borderRadius: '10px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                          <Avatar 
                            src={guide.image} 
                            alt={guide.name} 
                            sx={{ width: 64, height: 64, mr: 2 }}
                          />
                          <Box>
                            <Typography variant="h6">{guide.name}</Typography>
                            <Typography variant="body2" color="text.secondary">Tour Guide</Typography>
                          </Box>
                        </Box>
                        <Divider sx={{ my: 1.5 }} />
                        <Grid container spacing={1}>
                          <Grid item xs={12} sm={6}>
                            <Box sx={{ display: 'flex', alignItems: 'center' }}>
                              <Phone fontSize="small" sx={{ mr: 0.5, color: 'primary.main' }} />
                              <Typography variant="body2">{guide.contact_no}</Typography>
                            </Box>
                          </Grid>
                          <Grid item xs={12} sm={6}>
                            <Box sx={{ display: 'flex', alignItems: 'center' }}>
                              <Email fontSize="small" sx={{ mr: 0.5, color: 'primary.main' }} />
                              <Typography variant="body2">{guide.email}</Typography>
                            </Box>
                          </Grid>
                        </Grid>
                        {guide.languages && guide.languages.length > 0 && (
                          <Box sx={{ mt: 2 }}>
                            <Typography variant="body2" color="text.secondary" gutterBottom>Languages:</Typography>
                            <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
                              {guide.languages.map((lang, i) => (
                                <Chip key={i} label={lang} size="small" variant="outlined" />
                              ))}
                            </Box>
                          </Box>
                        )}
                      </Paper>
                    </Grid>
                  ))}
                </Grid>
              </TabPanel>
            )}
          </Box>
        )}
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

export default BookingViewModal; 