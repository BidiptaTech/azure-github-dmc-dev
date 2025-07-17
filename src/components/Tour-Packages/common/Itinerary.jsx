import React, { useMemo, useState , useEffect} from 'react';
import { 
  Box, 
  Typography, 
  Paper, 
  Grid, 
  Card, 
  CardContent, 
  CardMedia, 
  Divider, 
  Stack,
  Chip,
  Button,
  Stepper,
  Step,
  StepLabel,
  StepContent,
  IconButton,
  TextField,
  Checkbox,
  FormControlLabel,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Tabs,
  Tab,
  CircularProgress,
  Snackbar,
  Alert
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import moment from 'moment';
import HotelIcon from '@mui/icons-material/Hotel';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import AttractionsIcon from '@mui/icons-material/Attractions';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import PersonIcon from '@mui/icons-material/Person';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import EventIcon from '@mui/icons-material/Event';
import AirportShuttleIcon from '@mui/icons-material/AirportShuttle';
import CommentIcon from '@mui/icons-material/Comment';
import RoomIcon from '@mui/icons-material/Room';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';
import DeleteIcon from '@mui/icons-material/Delete';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import WarningIcon from '@mui/icons-material/Warning';
import LocalActivityIcon from '@mui/icons-material/LocalActivity';
import FlightIcon from '@mui/icons-material/Flight';
import { BookPackageEnquiry, UpdateCustomBooking } from '../../../slice/tour-packages/tourPackageSlice';
import { useNavigate } from 'react-router-dom';

// Import all service components
import HotelComponent from '../../Tour-Packages/hotel';
import AttractionComponent from '../../Tour-Packages/attraction';
import RestaurantComponent from '../../Tour-Packages/Restaurants';
import GuideComponent from '../../Tour-Packages/Guide';
import TransportComponent from '../../Tour-Packages/Local-Transport';
import PickupDropComponent from '../../Tour-Packages/PickUp-Drop';
import SimpleCustomerInfo from './SimpleCustomerInfo';
import ServicesSummaryModal from './ServicesSummaryModal';

export default function Itinerary({ onBookingSuccess }) {
  const navigate = useNavigate();
  // Get data from Redux store
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  console.log("searchCriteria", searchCriteria);
  const { hotels } = useSelector((state) => state.hotels);
  const selectedCity = useSelector(state => state.common?.selectedCity?.cityName);
  const selectedCountry = useSelector(state => state.common?.selectedCity?.countryName);
  const enquiryDetail = useSelector(state => state.tourPackages.initialPackages?.EnquiryDetails);
  console.log("enquiryDetaills", enquiryDetail);
  // Get all services from Redux store for validation
  const allServices = useSelector((state) => state.tourPackages.AllServices);
  console.log("All Services for validation:", allServices);
  
  const [portType,setPortType] = useState("Entry Port");
  const [portType1,setPortType1] = useState("Exit Port");
  const dispatch = useDispatch();
  const packageData = useSelector((state) => state.tourPackages.packageData);
  console.log("packageData", packageData);

  // Function to automatically update service dates when tour dates change
  const updateServiceDatesForNewTourDates = useMemo(() => {
    return (newStartDate, newEndDate) => {
      if (!allServices || allServices.length === 0) {
        console.log("No services to update dates for");
        return;
      }

      console.log("Updating service dates for new tour dates:", {
        newStart: newStartDate.format('YYYY-MM-DD'),
        newEnd: newEndDate.format('YYYY-MM-DD'),
        totalServices: allServices.length
      });

      const updatedServices = allServices.map((service) => {
        if (!service.data || !Array.isArray(service.data)) {
          return service;
        }

        const updatedData = service.data.map((item) => {
          let updatedItem = { ...item };
          let hasDateUpdate = false;

          // Handle different booking date formats
          if (item.bookingDate) {
            if (Array.isArray(item.bookingDate)) {
              // Hotel format: [check-in, check-out]
              const originalCheckIn = moment(item.bookingDate[0]);
              const originalCheckOut = moment(item.bookingDate[1]);
              
              // Update hotel dates to fit within new tour dates
              const newCheckIn = moment.max(originalCheckIn, newStartDate);
              const newCheckOut = moment.min(originalCheckOut, newEndDate);
              
              // Ensure minimum 1 night stay for hotels
              if (newCheckOut.isSameOrBefore(newCheckIn)) {
                newCheckOut.add(1, 'day');
              }
              
              // If still outside tour range, adjust to fit
              if (newCheckIn.isBefore(newStartDate)) {
                newCheckIn.set({ 
                  year: newStartDate.year(),
                  month: newStartDate.month(),
                  date: newStartDate.date()
                });
              }
              
              if (newCheckOut.isAfter(newEndDate)) {
                newCheckOut.set({
                  year: newEndDate.year(),
                  month: newEndDate.month(),
                  date: newEndDate.date()
                });
              }

              updatedItem.bookingDate = [
                newCheckIn.format('YYYY-MM-DD'),
                newCheckOut.format('YYYY-MM-DD')
              ];
              hasDateUpdate = true;

              console.log(`Updated hotel ${item.id} dates:`, {
                original: [originalCheckIn.format('YYYY-MM-DD'), originalCheckOut.format('YYYY-MM-DD')],
                updated: updatedItem.bookingDate
              });

            } else {
              // Other services format: single date string
              const originalDate = moment(item.bookingDate);
              let newDate = moment(originalDate);

              // If date is outside tour range, move it to the closest valid date
              if (originalDate.isBefore(newStartDate)) {
                newDate = moment(newStartDate);
                hasDateUpdate = true;
              } else if (originalDate.isAfter(newEndDate)) {
                newDate = moment(newEndDate);
                hasDateUpdate = true;
              }

              updatedItem.bookingDate = newDate.format('YYYY-MM-DD');

              if (hasDateUpdate) {
                console.log(`Updated ${service.type} service ${item.id} date:`, {
                  original: originalDate.format('YYYY-MM-DD'),
                  updated: updatedItem.bookingDate
                });
              }
            }
          }

          // Update dayIndex for services that have it (attractions, guides, etc.)
          if (item.dayIndex !== undefined && hasDateUpdate) {
            const serviceDate = Array.isArray(updatedItem.bookingDate) 
              ? moment(updatedItem.bookingDate[0])
              : moment(updatedItem.bookingDate);
            
            const newDayIndex = serviceDate.diff(newStartDate, 'days');
            updatedItem.dayIndex = Math.max(0, newDayIndex);
            
            console.log(`Updated ${service.type} service ${item.id} dayIndex:`, {
              originalDayIndex: item.dayIndex,
              newDayIndex: updatedItem.dayIndex
            });
          }

          return updatedItem;
        });

        return {
          ...service,
          data: updatedData
        };
      });

      // Update Redux store with corrected service dates
      dispatch({ 
        type: 'tourPackages/setAllServices', 
        payload: updatedServices 
      });

      console.log("Service dates updated successfully");
    };
  }, [allServices, dispatch]);

  // Effect to automatically update service dates when tour dates change
  useEffect(() => {
    if (!searchCriteria?.checkIn || !searchCriteria?.checkOut || !allServices || allServices.length === 0) {
      return;
    }

    // Parse tour date range
    let tourStartDate, tourEndDate;
    
    if (searchCriteria.checkIn.includes('-') && searchCriteria.checkIn.match(/^\d{4}-\d{2}-\d{2}$/)) {
      // YYYY-MM-DD format
      tourStartDate = moment(searchCriteria.checkIn, 'YYYY-MM-DD');
      tourEndDate = moment(searchCriteria.checkOut, 'YYYY-MM-DD');
    } else {
      // DD/MM/YYYY format
      tourStartDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
      tourEndDate = moment(searchCriteria.checkOut, 'DD/MM/YYYY');
    }

    // Check if any services have dates outside the tour range
    const hasInvalidDates = allServices.some(service => {
      if (!service.data || !Array.isArray(service.data)) return false;
      
      return service.data.some(item => {
        if (!item.bookingDate) return false;
        
        if (Array.isArray(item.bookingDate)) {
          // Hotel format
          const checkIn = moment(item.bookingDate[0]);
          const checkOut = moment(item.bookingDate[1]);
          return checkIn.isBefore(tourStartDate) || checkOut.isAfter(tourEndDate);
        } else {
          // Other services format
          const bookingDate = moment(item.bookingDate);
          return bookingDate.isBefore(tourStartDate) || bookingDate.isAfter(tourEndDate);
        }
      });
    });

    // Only update if there are invalid dates
    if (hasInvalidDates) {
      console.log("Found services with dates outside tour range, auto-updating...");
      updateServiceDatesForNewTourDates(tourStartDate, tourEndDate);
    }
  }, [searchCriteria?.checkIn, searchCriteria?.checkOut, updateServiceDatesForNewTourDates]);

  // Validation function for service booking dates
  const validateServiceDates = useMemo(() => {
    if (!searchCriteria?.checkIn || !searchCriteria?.checkOut || !allServices?.length) {
      return { isValid: true, invalidServices: [], message: '' };
    }

    // Parse tour date range
    let tourStartDate, tourEndDate;
    
    if (searchCriteria.checkIn.includes('-') && searchCriteria.checkIn.match(/^\d{4}-\d{2}-\d{2}$/)) {
      // YYYY-MM-DD format
      tourStartDate = moment(searchCriteria.checkIn, 'YYYY-MM-DD');
      tourEndDate = moment(searchCriteria.checkOut, 'YYYY-MM-DD');
    } else {
      // DD/MM/YYYY format
      tourStartDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
      tourEndDate = moment(searchCriteria.checkOut, 'DD/MM/YYYY');
    }

    console.log("Tour date range:", {
      start: tourStartDate.format('YYYY-MM-DD'),
      end: tourEndDate.format('YYYY-MM-DD')
    });

    const invalidServices = [];

    allServices.forEach((service, serviceIndex) => {
      if (service.data && Array.isArray(service.data)) {
        service.data.forEach((item, itemIndex) => {
          if (item.bookingDate) {
            // Handle different booking date formats
            if (Array.isArray(item.bookingDate)) {
              // Hotel format: [check-in, check-out]
              const checkIn = moment(item.bookingDate[0]);
              const checkOut = moment(item.bookingDate[1]);
              
              console.log(`Hotel service ${item.id}:`, {
                checkIn: checkIn.format('YYYY-MM-DD'),
                checkOut: checkOut.format('YYYY-MM-DD'),
                tourStart: tourStartDate.format('YYYY-MM-DD'),
                tourEnd: tourEndDate.format('YYYY-MM-DD')
              });

              if (checkIn.isBefore(tourStartDate) || checkOut.isAfter(tourEndDate)) {
                invalidServices.push({
                  type: service.type,
                  id: item.id,
                  name: item.hotelDetails?.hotel_name || item.name || `${service.type} service`,
                  bookingDate: `${checkIn.format('MMM DD, YYYY')} - ${checkOut.format('MMM DD, YYYY')}`,
                  reason: checkIn.isBefore(tourStartDate) 
                    ? 'Check-in date is before tour start'
                    : 'Check-out date is after tour end'
                });
              }
            } else {
              // Other services format: single date string
              const bookingDate = moment(item.bookingDate);
              
              console.log(`${service.type} service ${item.id}:`, {
                bookingDate: bookingDate.format('YYYY-MM-DD'),
                tourStart: tourStartDate.format('YYYY-MM-DD'),
                tourEnd: tourEndDate.format('YYYY-MM-DD')
              });

              if (bookingDate.isBefore(tourStartDate) || bookingDate.isAfter(tourEndDate)) {
                invalidServices.push({
                  type: service.type,
                  id: item.id,
                  name: item.AttractionName || item.RestaurantName || item.GuideName || item.name || `${service.type} service`,
                  bookingDate: bookingDate.format('MMM DD, YYYY'),
                  reason: bookingDate.isBefore(tourStartDate)
                    ? 'Service date is before tour start'
                    : 'Service date is after tour end'
                });
              }
            }
          }
        });
      }
    });

    const isValid = invalidServices.length === 0;
    const message = isValid 
      ? '' 
      : `${invalidServices.length} service${invalidServices.length > 1 ? 's have' : ' has'} booking dates outside the tour date range`;

    console.log("Validation result:", { isValid, invalidServices, message });

    return { isValid, invalidServices, message };
  }, [allServices, searchCriteria]);

  // Categorize package data by service type
  const categorizedServices = useMemo(() => {
    if (!packageData?.tour?.booking) {
      return {
        hotels: [],
        entryPorts: [],
        exitPorts: [],
        attractions: [],
        restaurants: [],
        guides: [],
        travelPoints: [],
        travelHourly: [],
        localTransports: []
      };
    }

    const services = {
      hotels: [],
      entryPorts: [],
      exitPorts: [],
      attractions: [],
      restaurants: [],
      guides: [],
      travelPoints: [],
      travelHourly: [],
      localTransports: []
    };

    packageData.tour.booking.forEach(booking => {
      const serviceType = booking.type?.toLowerCase();
      
      switch (serviceType) {
        case 'hotel':
          services.hotels.push(booking);
          break;
        case 'entry_port':
          services.entryPorts.push(booking);
          break;
        case 'exit_port':
          services.exitPorts.push(booking);
          break;
        case 'attraction':
          services.attractions.push(booking);
          break;
        case 'attraction_package':
          services.attractions.push(booking);
          break;
        case 'restaurant':
          services.restaurants.push(booking);
          break;
        case 'guide':
          services.guides.push(booking);
          break;
        case 'travel_point':
          services.travelPoints.push(booking);
          break;
        case 'travel_hourly':
          services.travelHourly.push(booking);
          break;
        case 'local_transport':
          services.localTransports.push(booking);
          break;
        default:
          console.warn(`Unknown service type: ${booking.type}`);
      }
    });

    return services;
  }, [packageData]);

  // Log categorized services for debugging
  useEffect(() => {
    if (packageData?.tour?.booking) {
      console.log("Categorized Services:", categorizedServices);
      console.log("Hotels:", categorizedServices.hotels);
      console.log("Entry Ports:", categorizedServices.entryPorts);
      console.log("Exit Ports:", categorizedServices.exitPorts);
      console.log("Attractions:", categorizedServices.attractions);
      console.log("Restaurants:", categorizedServices.restaurants);
      console.log("Guides:", categorizedServices.guides);
      console.log("Travel Points:", categorizedServices.travelPoints);
      console.log("Travel Hourly:", categorizedServices.travelHourly);
      console.log("Local Transports:", categorizedServices.localTransports);
    }
  }, [categorizedServices, packageData]);
  // State for active service tab
  const [activeServiceTab, setActiveServiceTab] = useState(0);
  
  // State for summary modal
  const [summaryModalOpen, setSummaryModalOpen] = useState(false);
  
  // State for snackbar
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState('');
  const [snackbarSeverity, setSnackbarSeverity] = useState('success');
  const { loading, error, packageEnquiryId, customerInfoValid } = useSelector((state) => state.tourPackages);
  
  // Debug logging for button state
  const isButtonDisabled = loading || !validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid;
  console.log("Button disabled state:", {
    loading,
    hasServices: allServices && allServices.length > 0,
    isValidationPassing: validateServiceDates.isValid,
    customerInfoValid,
    finalDisabledState: isButtonDisabled
  });
  
  // Generate dates array from the selected date range
  const dates = useMemo(() => {
    if (!searchCriteria?.checkIn || !searchCriteria?.checkOut) return [];
    
    // Detect date format and parse accordingly
    const checkInDate = searchCriteria.checkIn;
    const checkOutDate = searchCriteria.checkOut;
    
    let startDate, endDate;
    
    // Check if dates are in YYYY-MM-DD format (from loaded package) or DD/MM/YYYY format (from search form)
    if (checkInDate.includes('-') && checkInDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
      // YYYY-MM-DD format (from loaded package data)
      startDate = moment(checkInDate, 'YYYY-MM-DD');
      endDate = moment(checkOutDate, 'YYYY-MM-DD');
    } else {
      // DD/MM/YYYY format (from search form)
      startDate = moment(checkInDate, 'DD/MM/YYYY');
      endDate = moment(checkOutDate, 'DD/MM/YYYY');
    }
    
    const dayDiff = endDate.diff(startDate, 'days');

    // Generate an array of all dates in the range
    const datesArray = [];
    for (let i = 0; i <= dayDiff; i++) {
      datesArray.push(moment(startDate).add(i, 'days'));
    }
    return datesArray;
  }, [searchCriteria]);
  
  // Mock hotel data for the top section
  const mockHotel = {
    name: "Hotel Boss",
    location: "Little India • 3 Star",
    image: "https://source.unsplash.com/random/600x400/?hotel"
  };

  // If no dates available, show a message
  if (dates.length === 0) {
    return (
      <Paper sx={{ p: 4, textAlign: 'center' }}>
        <Typography variant="h5">
          Please select travel dates to view your itinerary
        </Typography>
      </Paper>
    );
  }

  // Function to handle booking
  const handleBookPackage = () => {
    dispatch(BookPackageEnquiry())
      .unwrap()
      .then((result) => {
        setSnackbarMessage('Package booked successfully!');
        setSnackbarSeverity('success');
        setOpenSnackbar(true);
        
        // Call the callback to reset current step in parent component
        if (onBookingSuccess) {
          onBookingSuccess();
        }
      })
      .catch((error) => {
        setSnackbarMessage(error?.message || 'Failed to book package. Please try again.');
        setSnackbarSeverity('error');
        setOpenSnackbar(true);
      });
  };
  const handleUpdatePackage = () => {
    dispatch(UpdateCustomBooking())
      .unwrap()
      .then((result) => {
        setSnackbarMessage('Package Upadated successfully!');
        setSnackbarSeverity('success');
        setOpenSnackbar(true);
        navigate('/dashboard/db-dashboard');
        // Call the callback to reset current step in parent component
        if (onBookingSuccess) {
          onBookingSuccess();
        }
      })
      .catch((error) => {
        setSnackbarMessage(error?.message || 'Failed to book package. Please try again.');
        setSnackbarSeverity('error');
        setOpenSnackbar(true);
      });
  };
  
  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  return (
    <Box>
      <Grid container spacing={3}>
        {/* Main content area - Grid 7 */}
        <Grid item xs={12} md={enquiryDetail ? 8 : 12}>
          {/* Top Level Hotel Section */}
          <Paper elevation={3} sx={{ p: 3, mb: 4, borderRadius: 2 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
              <Box 
                sx={{ 
                  bgcolor: 'primary.main', 
                  color: 'white', 
                  p: 0.8, 
                  borderRadius: '50%', 
                  mr: 1,
                  display: 'flex'
                }}
              >
                <HotelIcon />
              </Box>
              <Typography variant="h5">Hotels</Typography>
            </Box>
            
            {/* <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
              Please add hotels details (if included in package) with services provided for each hotels and the selling cost price.
            </Typography>
            
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
              <Typography variant="body2" color="text.secondary" sx={{ mr: 1 }}>
                Tip: To speed up the process of adding multiple hotels, use
              </Typography>
              <Button size="small" variant="text" color="primary">Next Night</Button>
              <Typography variant="body2" color="text.secondary" sx={{ mx: 1 }}>or</Typography>
              <Button size="small" variant="text" color="primary">Duplicate</Button>
              <Typography variant="body2" color="text.secondary">actions.</Typography>
            </Box> */}
            
            {/* Hotel Component */}
            <HotelComponent hotels={categorizedServices.hotels} />

          </Paper>

          
          
          {/* Transports and Activities Section */}
          <Paper elevation={3} sx={{ p: 3, mb: 4, borderRadius: 2 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
              <Box 
                sx={{ 
                  bgcolor: 'primary.main', 
                  color: 'white', 
                  p: 0.8, 
                  borderRadius: '50%', 
                  mr: 1,
                  display: 'flex'
                }}
              >
                <DirectionsCarIcon />
              </Box>
              <Typography variant="h5">Transports and Other Services</Typography>
            </Box>
            
            
            {/* Day by Day Transportation and Activities */}
            {dates.map((date, index) => (
              <Box key={index} sx={{ mb: 4 }}>
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
                  <Typography variant="h6">
                    Day {index + 1}, {date.format('Do MMMM')}, {date.format('dddd')}
                  </Typography>
                </Box>

                {/* First Day - Place Port Component at the beginning */}
                {index === 0 && (
                  <Box sx={{ mb: 2 }}>
                    <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #1976d2' }}>
                      {/* <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Arrival</Typography> */}
                      <PickupDropComponent 
                        portType={portType} 
                        setPortType={() => setPortType("Entry Port")} 
                        date={date}
                        dayIndex={index}
                        entryPorts={categorizedServices.entryPorts}
                        tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
                      />
                    </Paper>
                  </Box>
                )}

                {/* Attraction Component */}
                <Box sx={{ mb: 2 }}>
                  <Paper elevation={1} sx={{ p: 0, borderLeft: '4px solid #f44336' }}>
                    
                    <AttractionComponent 
                      date={date}
                      dayIndex={index}
                      attractionspack={categorizedServices.attractions}
                      tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
                    />
                  </Paper>
                </Box>

                {/* Guide Component */}
                <Box sx={{ mb: 2 }}>
                  <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #2196f3' }}>
                    {/* <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Guide</Typography> */}
                    <GuideComponent 
                      date={date}
                      dayIndex={index}
                      guidespack={categorizedServices.guides}
                      tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
                    />
                  </Paper>
                </Box>

                {/* Restaurant Component */}
                <Box sx={{ mb: 2 }}>
                  <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #ff9800' }}>
                    <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Restaurant</Typography>
                    <RestaurantComponent 
                      date={date}
                      dayIndex={index}
                      restaurantspack={categorizedServices.restaurants}
                      tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
                    />
                  </Paper>
                </Box>

                {/* Transport Component */}
                <Box sx={{ mb: 2 }}>
                  <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #2196f3' }}>
                    <TransportComponent 
                      dayIndex={index}
                      date={date}
                      PointToPoint={categorizedServices.travelPoints}
                      Hourly={categorizedServices.travelHourly}
                      LocalTransports={categorizedServices.localTransports}
                      tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
                    />
                  </Paper>
                </Box>

                {/* Last Day - Place Port Component at the end */}
                {index === dates.length - 1 && (
  <Box sx={{ mb: 2 }}>
    <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #1976d2' }}>
      {/* <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Departure</Typography> */}
      <PickupDropComponent 
        portType1={portType1} 
        setPortType1={() => setPortType1("Exit Port")} 
        date={date}
        dayIndex={index}
        exitPorts={categorizedServices.exitPorts}
        tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
      />
    </Paper>
  </Box>
)}
                
                {index < dates.length - 1 && <Divider sx={{ my: 3 }} />}
              </Box>
            ))}
          </Paper>
          
          {/* Customer Information Section - Added just before Trip Summary */}
          <SimpleCustomerInfo />
          
          {/* Summary section */}
          <Paper elevation={3} sx={{ mt: 4, p: 3 }}>
            <Typography variant="h6" gutterBottom>Trip Summary</Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={8}>
                <Typography variant="body1">
                  {dates.length} day{dates.length !== 1 ? 's' : ''} in {selectedCity}, {selectedCountry}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {searchCriteria.guests?.adults || 1} Adult{parseInt(searchCriteria.guests?.adults) !== 1 ? 's' : ''} •
                  {searchCriteria.guests?.children > 0 ? ` ${searchCriteria.guests.children} Child${parseInt(searchCriteria.guests.children) !== 1 ? 'ren' : ''} •` : ''}
                  {dates.length - 1} Night{dates.length - 1 !== 1 ? 's' : ''}
                </Typography>
              </Grid>
              <Grid item xs={12} md={4} sx={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: 2 }}>
                <Button 
                  variant="outlined" 
                  color="primary" 
                  size="large"
                  onClick={() => setSummaryModalOpen(true)}
                >
                  View Summary
                </Button>
                {/* <Button variant="contained" color="primary" size="large">
                  Save Itinerary
                </Button> */}
              </Grid>
            </Grid>
          </Paper>
          
          {/* Book Package Button Section - Improved with Validation */}
          <Paper 
            elevation={3} 
            sx={{ 
              mt: 4, 
              p: 4, 
              display: 'flex', 
              flexDirection: 'column', 
              alignItems: 'center', 
              borderRadius: 2,
              background: 'linear-gradient(to right, rgba(255,255,255,0.95), rgba(245,245,255,0.95))',
              boxShadow: '0 8px 32px rgba(0,0,0,0.1)'
            }}
          >
            <Typography variant="h5" gutterBottom sx={{ mb: 2, fontWeight: 600 }}>
              Ready to book this amazing package?
            </Typography>

            {/* Show warning if no services are added */}
            {(!allServices || allServices.length === 0) && (
              <Alert 
                severity="info" 
                icon={<WarningIcon />}
                sx={{ 
                  mb: 3, 
                  width: '100%', 
                  maxWidth: 600,
                  borderRadius: 2
                }}
              >
                <Typography variant="body2" fontWeight={600} gutterBottom>
                  No services have been added to your package yet
                </Typography>
                <Typography variant="body2">
                  Please add hotels, attractions, restaurants, guides, or transportation services to your itinerary before booking.
                </Typography>
              </Alert>
            )}

            {/* Show validation warning if there are date issues */}
            {allServices && allServices.length > 0 && !validateServiceDates.isValid && (
              <Alert 
                severity="warning" 
                icon={<WarningIcon />}
                sx={{ 
                  mb: 3, 
                  width: '100%', 
                  maxWidth: 600,
                  borderRadius: 2
                }}
              >
                <Typography variant="body2" fontWeight={600} gutterBottom>
                  {validateServiceDates.message}
                </Typography>
                <Typography variant="body2" sx={{ mb: 1 }}>
                  The following services need to be adjusted to fit within your tour dates 
                  ({moment(searchCriteria.checkIn, searchCriteria.checkIn.includes('-') ? 'YYYY-MM-DD' : 'DD/MM/YYYY').format('MMM DD, YYYY')} - {moment(searchCriteria.checkOut, searchCriteria.checkOut.includes('-') ? 'YYYY-MM-DD' : 'DD/MM/YYYY').format('MMM DD, YYYY')}):
                </Typography>
                <Box component="ul" sx={{ pl: 2, mt: 1 }}>
                  {validateServiceDates.invalidServices.map((service, index) => (
                    <li key={index}>
                      <Typography variant="caption" display="block">
                        <strong>{service.name}</strong> ({service.type}) - {service.bookingDate}
                        <br />
                        <em>{service.reason}</em>
                      </Typography>
                    </li>
                  ))}
                </Box>
              </Alert>
            )}

            {/* Show customer info warning if not valid */}
            {allServices && allServices.length > 0 && validateServiceDates.isValid && !customerInfoValid && (
              <Alert 
                severity="warning" 
                icon={<WarningIcon />}
                sx={{ 
                  mb: 3, 
                  width: '100%', 
                  maxWidth: 600,
                  borderRadius: 2
                }}
              >
                <Typography variant="body2" fontWeight={600} gutterBottom>
                  Customer information required
                </Typography>
                <Typography variant="body2">
                  Please complete all mandatory fields in the Customer Information section above to enable booking.
                </Typography>
              </Alert>
            )}

            <Button 
              variant="contained" 
              color="primary" 
              size="large" 
              disabled={isButtonDisabled}
              startIcon={loading ? null : <ShoppingCartIcon />}
              onClick={packageData?.tour?.booking?.length > 0 ? handleUpdatePackage : handleBookPackage}
              sx={{
                py: 1.5,
                px: 4,
                fontSize: '1.1rem',
                fontWeight: 600,
                borderRadius: '50px',
                background: (!validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid)
                  ? 'linear-gradient(45deg, #9e9e9e 30%, #bdbdbd 90%)'
                  : 'linear-gradient(45deg, #3554D1 30%, #5672E9 90%)',
                boxShadow: (!validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid)
                  ? '0 4px 8px rgba(158, 158, 158, 0.3)'
                  : '0 10px 20px rgba(53, 84, 209, 0.3)',
                transition: 'all 0.3s ease',
                textTransform: 'none',
                '&:hover': {
                  transform: (!validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid) ? 'none' : 'translateY(-3px)',
                  boxShadow: (!validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid)
                    ? '0 4px 8px rgba(158, 158, 158, 0.3)'
                    : '0 15px 30px rgba(53, 84, 209, 0.4)',
                },
                '&:disabled': {
                  color: 'white',
                  cursor: 'not-allowed'
                },
                minWidth: 200,
              }}
            >
              {loading ? (
                <CircularProgress size={24} color="inherit" />
              ) : !allServices || allServices.length === 0 ? (
                'No Services Added'
              ) : !validateServiceDates.isValid ? (
                'Please Fix Service Dates'
              ) : !customerInfoValid ? (
                'Complete Customer Info'
              ) : (
                packageData?.tour?.tour_id > 0 ? 'Update Package Now' : 'Book Package Now'
              )}
            </Button>

            {validateServiceDates.isValid && packageEnquiryId && (
              <Typography variant="body1" sx={{ mt: 2, color: 'success.main', fontWeight: 500 }}>
                Booking ID: {packageEnquiryId}
              </Typography>
            )}

            {((!allServices || allServices.length === 0) || !validateServiceDates.isValid || !customerInfoValid) && (
              <Typography variant="body2" sx={{ mt: 2, color: 'text.secondary', textAlign: 'center', maxWidth: 400 }}>
                {(!allServices || allServices.length === 0) 
                  ? 'Add some services to your itinerary to enable booking.'
                  : !validateServiceDates.isValid
                  ? 'Please ensure all service booking dates fall within your tour period before proceeding with the booking.'
                  : !customerInfoValid
                  ? 'Complete all mandatory customer information fields to enable booking.'
                  : ''
                }
              </Typography>
            )}
          </Paper>
          
          {/* Snackbar for notifications */}
          <Snackbar 
            open={openSnackbar} 
            autoHideDuration={6000} 
            onClose={handleCloseSnackbar}
            anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
          >
            <Alert 
              onClose={handleCloseSnackbar} 
              severity={snackbarSeverity} 
              sx={{ 
                width: '100%',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                borderRadius: 2
              }}
            >
              {snackbarMessage}
            </Alert>
          </Snackbar>
          
          {/* Services Summary Modal */}
          <ServicesSummaryModal 
            open={summaryModalOpen} 
            onClose={() => setSummaryModalOpen(false)} 
          />
        </Grid>
        
        {/* Enquiry Details Sidebar - Grid 4 (only shown when enquiryDetail exists) */}
        {enquiryDetail && (
          <Grid item xs={12} md={5} lg={4}>
            <Paper 
              elevation={3} 
              sx={{ 
                p: 3, 
                borderRadius: 2,
                position: 'sticky',
                top: '20px',
                maxHeight: 'calc(100vh - 40px)',
                overflowY: 'auto',
                bgcolor: 'rgba(255, 255, 255, 0.98)',
                borderLeft: '4px solid #3554D1'
              }}
            >
              <Typography 
                variant="h5" 
                sx={{ 
                  mb: 3, 
                  pb: 1.5, 
                  borderBottom: '2px solid #3554D1',
                  fontWeight: 600,
                  color: '#3554D1' 
                }}
              >
                Enquiry for Services
              </Typography>
              
              {/* Hotels */}
              {enquiryDetail.hotel_on && (
                <Box sx={{ mb: 3 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                    <HotelIcon sx={{ mr: 1, color: '#3554D1' }} />
                    <Typography variant="h6">Hotels</Typography>
                  </Box>
                  <Grid container spacing={2}>
                    {enquiryDetail.hotel?.map((hotel, index) => (
                      <Grid item xs={12} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 2, overflow: 'hidden' }}>
                          <Box sx={{ width: 100, height: 80 }}>
                            <img 
                              src={hotel.main_image} 
                              alt={hotel.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1.5, flexGrow: 1 }}>
                            <Typography variant="subtitle1" fontWeight={500}>{hotel.name}</Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Attractions */}
              {enquiryDetail.attraction_on && (
                <Box sx={{ mb: 3 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                    <AttractionsIcon sx={{ mr: 1, color: '#f44336' }} />
                    <Typography variant="h6">Attractions</Typography>
                  </Box>
                  <Grid container spacing={2}>
                    {enquiryDetail.attraction?.map((attraction, index) => (
                      <Grid item xs={12} sm={6} md={12} lg={6} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 2, overflow: 'hidden' }}>
                          <Box sx={{ width: 80, height: 80 }}>
                            <img 
                              src={attraction.master_image} 
                              alt={attraction.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1.5, flexGrow: 1 }}>
                            <Typography variant="subtitle2" fontWeight={500}>{attraction.name}</Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Packaged Attractions */}
              {enquiryDetail.packaged_attraction_on && (
                <Box sx={{ mb: 3 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                    <LocalActivityIcon sx={{ mr: 1, color: '#9c27b0' }} />
                    <Typography variant="h6">Packaged Attractions</Typography>
                  </Box>
                  <Grid container spacing={2}>
                    {enquiryDetail.packaged_attractions?.map((pkg, index) => {
                      // Parse the image JSON if it's a string
                      let imageUrl = null;
                      if (pkg.image) {
                        try {
                          const parsedImages = JSON.parse(pkg.image);
                          imageUrl = parsedImages[0]?.replace(/\\/g, '');
                        } catch (e) {
                          imageUrl = null;
                        }
                      }
                      
                      return (
                        <Grid item xs={12} key={index}>
                          <Card sx={{ display: 'flex', borderRadius: 2, overflow: 'hidden' }}>
                            <Box sx={{ width: 80, height: 80, bgcolor: imageUrl ? 'transparent' : '#f0f0f0', display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                              {imageUrl ? (
                                <img 
                                  src={imageUrl} 
                                  alt={pkg.name}
                                  style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                  onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                                />
                              ) : (
                                <LocalActivityIcon sx={{ color: '#9c27b0', fontSize: 40 }} />
                              )}
                            </Box>
                            <Box sx={{ p: 1.5, flexGrow: 1 }}>
                              <Typography variant="subtitle2" fontWeight={500}>{pkg.name}</Typography>
                            </Box>
                          </Card>
                        </Grid>
                      );
                    })}
                  </Grid>
                </Box>
              )}
              
              {/* Restaurants */}
              {enquiryDetail.restaurant_on && (
                <Box sx={{ mb: 3 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                    <RestaurantIcon sx={{ mr: 1, color: '#ff9800' }} />
                    <Typography variant="h6">Restaurants</Typography>
                  </Box>
                  <Grid container spacing={2}>
                    {enquiryDetail.restaurant?.map((restaurant, index) => (
                      <Grid item xs={12} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 2, overflow: 'hidden' }}>
                          <Box sx={{ width: 80, height: 80 }}>
                            <img 
                              src={restaurant.master_image} 
                              alt={restaurant.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1.5, flexGrow: 1 }}>
                            <Typography variant="subtitle2" fontWeight={500}>{restaurant.name}</Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Guides */}
              {enquiryDetail.guide_on && (
                <Box sx={{ mb: 3 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                    <PersonIcon sx={{ mr: 1, color: '#2196f3' }} />
                    <Typography variant="h6">Guides</Typography>
                  </Box>
                  <Grid container spacing={2}>
                    {enquiryDetail.guide?.map((guide, index) => (
                      <Grid item xs={6} md={12} lg={6} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 2, overflow: 'hidden' }}>
                          <Box sx={{ width: 60, height: 60 }}>
                            <img 
                              src={guide.image} 
                              alt={guide.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1.5, flexGrow: 1 }}>
                            <Typography variant="subtitle2" fontWeight={500}>{guide.name}</Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Drivers/Vehicles */}
              {enquiryDetail.driver?.length > 0 && (
                <Box sx={{ mb: 3 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                    <DirectionsCarIcon sx={{ mr: 1, color: '#4caf50' }} />
                    <Typography variant="h6">Vehicles</Typography>
                  </Box>
                  <Grid container spacing={2}>
                    {enquiryDetail.driver?.map((vehicle, index) => (
                      <Grid item xs={12} sm={6} md={12} lg={6} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 2, overflow: 'hidden' }}>
                          <Box sx={{ width: 80, height: 80 }}>
                            <img 
                              src={vehicle.image} 
                              alt={vehicle.vehicle_name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1.5, flexGrow: 1 }}>
                            <Typography variant="subtitle2" fontWeight={500}>{vehicle.vehicle_name}</Typography>
                            <Typography variant="caption" color="text.secondary">
                              {vehicle.vehicle_type} • {vehicle.vehicle_model}
                            </Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Ports */}
              {(enquiryDetail.entry_port_on || enquiryDetail.exit_port_on) && enquiryDetail.ports?.length > 0 && (
                <Box sx={{ mb: 3 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                    <FlightIcon sx={{ mr: 1, color: '#795548' }} />
                    <Typography variant="h6">Ports</Typography>
                  </Box>
                  {enquiryDetail.ports?.map((port, index) => (
                    <Card key={index} sx={{ mb: 2, borderRadius: 2, overflow: 'hidden' }}>
                      <CardContent sx={{ p: 2 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <Chip 
                            label={port.type === 'entry' ? 'Arrival' : 'Departure'} 
                            color={port.type === 'entry' ? 'primary' : 'secondary'}
                            size="small"
                            sx={{ mr: 1 }}
                          />
                        </Box>
                        <Typography variant="body2" sx={{ mb: 0.5 }}>
                          <strong>Port:</strong> {port.port_address}
                        </Typography>
                        <Typography variant="body2">
                          <strong>Drop-off:</strong> {port.dropoff_name}
                        </Typography>
                      </CardContent>
                    </Card>
                  ))}
                </Box>
              )}
              
              {/* Service Status Summary */}
              <Box sx={{ mt: 4, pt: 2, borderTop: '1px solid #e0e0e0' }}>
                <Typography variant="h6" sx={{ mb: 2 }}>Service Status</Typography>
                <Grid container spacing={1}>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<HotelIcon />} 
                      label="Hotels" 
                      color={enquiryDetail.hotel_on ? "success" : "default"}
                      variant={enquiryDetail.hotel_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<AttractionsIcon />} 
                      label="Attractions" 
                      color={enquiryDetail.attraction_on ? "success" : "default"}
                      variant={enquiryDetail.attraction_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<RestaurantIcon />} 
                      label="Restaurants" 
                      color={enquiryDetail.restaurant_on ? "success" : "default"}
                      variant={enquiryDetail.restaurant_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<PersonIcon />} 
                      label="Guides" 
                      color={enquiryDetail.guide_on ? "success" : "default"}
                      variant={enquiryDetail.guide_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<DirectionsCarIcon />} 
                      label="Transport" 
                      color={enquiryDetail.pickup_on ? "success" : "default"}
                      variant={enquiryDetail.pickup_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<LocalActivityIcon />} 
                      label="Packages" 
                      color={enquiryDetail.packaged_attraction_on ? "success" : "default"}
                      variant={enquiryDetail.packaged_attraction_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<FlightIcon />} 
                      label="Entry Port" 
                      color={enquiryDetail.entry_port_on ? "success" : "default"}
                      variant={enquiryDetail.entry_port_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<FlightIcon />} 
                      label="Exit Port" 
                      color={enquiryDetail.exit_port_on ? "success" : "default"}
                      variant={enquiryDetail.exit_port_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start' }}
                    />
                  </Grid>
                </Grid>
              </Box>
            </Paper>
          </Grid>
        )}
      </Grid>
    </Box>
  );
} 