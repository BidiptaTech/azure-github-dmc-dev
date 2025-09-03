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
import CloseIcon from '@mui/icons-material/Close';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
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
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState('');
  const [snackbarSeverity, setSnackbarSeverity] = useState('success');
  const [portType,setPortType] = useState("Entry Port");
  const [portType1,setPortType1] = useState("Exit Port");
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const dispatch = useDispatch();
  const packageData = useSelector((state) => state.tourPackages.packageData);
  const errorMessage = useSelector((state) => {
    try {
      return state.pickupDrop?.error || state.localtour?.error;
    } catch (error) {
      console.warn('Error accessing pickupDrop or localtour state:', error);
      return null;
    }
  });
  console.log("packageData", packageData);
  console.log("errorMessage", errorMessage);

  // Effect to show snackbar when errorMessage has a string value
  useEffect(() => {
    console.log('useEffect triggered with errorMessage:', errorMessage);
    
    
    try {
      if (errorMessage && 
          typeof errorMessage === 'string' && 
          errorMessage.trim() !== '') {
        console.log('Setting snackbar with message:', errorMessage);
        setSnackbarMessage(errorMessage);
        setSnackbarSeverity('error');
        setOpenSnackbar(true);
        console.log('Snackbar state should be set to open');
      } else {
        console.log('errorMessage conditions not met:', {
          hasErrorMessage: !!errorMessage,
          isString: typeof errorMessage === 'string',
          notEmpty: errorMessage?.trim() !== ''
        });
      }
    } catch (error) {
      console.error('Error in errorMessage useEffect:', error);
      console.error('errorMessage value:', errorMessage);
      console.error('errorMessage type:', typeof errorMessage);
    }
  }, [errorMessage]);

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

  // Helper function to check if sidebar should be shown
  const shouldShowSidebar = () => {
    return (enquiryDetail?.hotel_on === true || 
           enquiryDetail?.attraction_on === true || 
           enquiryDetail?.restaurant_on === true || 
           enquiryDetail?.guide_on === true || 
           enquiryDetail?.localtransfer_on === true || 
           enquiryDetail?.ports_on === true || 
           enquiryDetail?.pickup_on === true || 
           enquiryDetail?.entry_port_on === true || 
           enquiryDetail?.exit_port_on === true || 
           enquiryDetail?.packaged_attraction_on === true) && sidebarOpen;
  };

  // Function to handle booking
  const handleBookPackage = () => {
    dispatch(BookPackageEnquiry())
      .unwrap()
      .then((result) => {
        setSnackbarMessage('Package booked successfully!');
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
        <Grid item xs={12} md={shouldShowSidebar() ? 8 : 12} sx={{
          marginRight: shouldShowSidebar() ? '25%' : 0,
          transition: 'margin-right 0.3s ease',
          width: shouldShowSidebar() ? '75%' : '100%',
        }}>
          {/* Top Level Hotel Section */}
          <Paper elevation={2} sx={{ p: 2, mb: 2, borderRadius: 1.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
              <Box 
                sx={{ 
                  bgcolor: 'primary.main', 
                  color: 'white', 
                  p: 0.5, 
                  borderRadius: '50%', 
                  mr: 0.8,
                  display: 'flex'
                }}
              >
                <HotelIcon sx={{ fontSize: '1rem' }} />
              </Box>
              <Typography variant="h6" sx={{ fontSize: '1.1rem' }}>Hotels</Typography>
            </Box>
            
            {/* Hotel Component */}
            <HotelComponent hotels={categorizedServices.hotels} />

          </Paper>

          
          
          {/* Transports and Activities Section */}
          <Paper elevation={2} sx={{ p: 2, mb: 2, borderRadius: 1.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
              <Box 
                sx={{ 
                  bgcolor: 'primary.main', 
                  color: 'white', 
                  p: 0.5, 
                  borderRadius: '50%', 
                  mr: 0.8,
                  display: 'flex'
                }}
              >
                <DirectionsCarIcon sx={{ fontSize: '1rem' }} />
              </Box>
              <Typography variant="h6" sx={{ fontSize: '1.1rem' }}>Transports and Other Services</Typography>
            </Box>
            
            
            {/* Day by Day Transportation and Activities */}
            {dates.map((date, index) => (
              <Box key={index} id={`day-${index + 1}`} sx={{ mb: 2 }}>
                {/* Day Plan Navigation for this specific day */}
                <Paper elevation={1} sx={{ p: 1.5, mb: 1.5, borderRadius: 1.5, bgcolor: '#fafbfc' }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <Box 
                      sx={{ 
                        bgcolor: '#e3f2fd', 
                        color: '#1976d2', 
                        p: 0.4, 
                        borderRadius: '50%', 
                        mr: 1,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                      }}
                    >
                      <CalendarTodayIcon sx={{ fontSize: '0.8rem' }} />
                    </Box>
                    <Typography variant="subtitle2" sx={{ color: '#37474f', fontSize: '0.8rem', fontWeight: 500 }}>Day {index + 1} Navigation</Typography>
                  </Box>
                  
                  {/* Horizontal Day Navigation */}
                  <Box sx={{ 
                    display: 'flex', 
                    gap: 1, 
                    flexWrap: 'wrap', 
                    justifyContent: 'center',
                    mb: 0.5
                  }}>
                    {dates.map((navDate, navIndex) => (
                      <Box
                        key={navIndex}
                        onClick={() => {
                          // Scroll to the corresponding day section
                          const dayElement = document.getElementById(`day-${navIndex + 1}`);
                          if (dayElement) {
                            dayElement.scrollIntoView({ 
                              behavior: 'smooth', 
                              block: 'start' 
                            });
                          }
                        }}
                        sx={{
                          minWidth: '60px',
                          p: 1,
                          textAlign: 'center',
                          bgcolor: navIndex === index ? '#e8f5e8' : '#f8f9fa',
                          color: navIndex === index ? '#2e7d32' : '#5f6368',
                          border: '1px solid',
                          borderColor: navIndex === index ? '#4caf50' : '#e0e0e0',
                          borderRadius: 1,
                          cursor: 'pointer',
                          transition: 'all 0.3s ease',
                          boxShadow: navIndex === index ? '0 1px 4px rgba(76, 175, 80, 0.2)' : '0 1px 2px rgba(0,0,0,0.1)',
                          '&:hover': {
                            bgcolor: navIndex === index ? '#d4edda' : '#e3f2fd',
                            borderColor: navIndex === index ? '#45a049' : '#2196f3',
                            transform: 'translateY(-1px)',
                            boxShadow: navIndex === index ? '0 2px 8px rgba(76, 175, 80, 0.3)' : '0 2px 8px rgba(33, 150, 243, 0.2)'
                          }
                        }}
                      >
                        <Typography 
                          variant="caption" 
                          sx={{ 
                            fontWeight: 600, 
                            display: 'block',
                            mb: 0.3, 
                            fontSize: '0.65rem',
                            lineHeight: 1.1,
                            color: 'inherit'
                          }}
                        >
                          Day {navIndex + 1}
                        </Typography>
                        <Typography 
                          variant="caption" 
                          sx={{ 
                            display: 'block',
                            mb: 0.2, 
                            fontSize: '0.6rem',
                            lineHeight: 1,
                            opacity: 0.9,
                            color: 'inherit'
                          }}
                        >
                          {navDate.format('MMM DD')}
                        </Typography>
                        <Typography 
                          variant="caption" 
                          sx={{ 
                            display: 'block',
                            fontSize: '0.55rem',
                            lineHeight: 1,
                            opacity: 0.8,
                            color: 'inherit'
                          }}
                        >
                          {navDate.format('ddd')}
                        </Typography>
                      </Box>
                    ))}
                  </Box>
                </Paper>

                {/* Day Header */}
                <Box 
                  sx={{ 
                    bgcolor: 'primary.main', 
                    color: 'white', 
                    p: 1, 
                    borderRadius: 0.8,
                    mb: 1
                  }}
                >
                  <Typography variant="subtitle1" sx={{ fontSize: '0.9rem' }}>
                    Day {index + 1}, {date.format('Do MMMM')}, {date.format('dddd')}
                  </Typography>
                </Box>

                {/* First Day - Place Port Component at the beginning */}
                {index === 0 && (
                  <Box sx={{ mb: 1 }}>
                    <Paper elevation={1} sx={{ p: 1.5, borderLeft: '3px solid #1976d2' }}>
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
                <Box sx={{ mb: 1 }}>
                  <Paper elevation={1} sx={{ p: 0, borderLeft: '3px solid #f44336' }}>
                    
                    <AttractionComponent 
                      date={date}
                      dayIndex={index}
                      attractionspack={categorizedServices.attractions}
                      tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
                    />
                  </Paper>
                </Box>

                {/* Guide Component */}
                <Box sx={{ mb: 1 }}>
                  <Paper elevation={1} sx={{ p: 1.5, borderLeft: '3px solid #2196f3' }}>
                    <GuideComponent 
                      date={date}
                      dayIndex={index}
                      guidespack={categorizedServices.guides}
                      tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
                    />
                  </Paper>
                </Box>

                {/* Restaurant Component */}
                <Box sx={{ mb: 1 }}>
                  <Paper elevation={1} sx={{ p: 1.5, borderLeft: '3px solid #ff9800' }}>
                    <Typography variant="subtitle2" fontWeight={500} sx={{ mb: 0.5, fontSize: '0.8rem' }}>Restaurant</Typography>
                    <RestaurantComponent 
                      date={date}
                      dayIndex={index}
                      restaurantspack={categorizedServices.restaurants}
                      tourDates={dates.map(d => d.format('YYYY-MM-DD'))}
                    />
                  </Paper>
                </Box>

                {/* Transport Component */}
                <Box sx={{ mb: 1 }}>
                  <Paper elevation={1} sx={{ p: 1.5, borderLeft: '3px solid #2196f3' }}>
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
                  <Box sx={{ mb: 1 }}>
                    <Paper elevation={1} sx={{ p: 1.5, borderLeft: '3px solid #1976d2' }}>
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
                
                {index < dates.length - 1 && <Divider sx={{ my: 1.5 }} />}
              </Box>
            ))}
          </Paper>
          
          {/* Customer Information Section - Added just before Trip Summary */}
          <SimpleCustomerInfo />
          
          {/* Summary section */}
          <Paper elevation={2} sx={{ mt: 2, p: 2 }}>
            <Typography variant="subtitle1" gutterBottom sx={{ fontSize: '0.9rem' }}>Trip Summary</Typography>
            <Grid container spacing={1.5}>
              <Grid item xs={12} md={8}>
                <Typography variant="body2" sx={{ fontSize: '0.8rem' }}>
                  {dates.length} day{dates.length !== 1 ? 's' : ''} in {selectedCity}, {selectedCountry}
                </Typography>
                <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                  {searchCriteria.guests?.adults || 1} Adult{parseInt(searchCriteria.guests?.adults) !== 1 ? 's' : ''} •
                  {searchCriteria.guests?.children > 0 ? ` ${searchCriteria.guests.children} Child${parseInt(searchCriteria.guests.children) !== 1 ? 'ren' : ''} •` : ''}
                  {dates.length - 1} Night{dates.length - 1 !== 1 ? 's' : ''}
                </Typography>
              </Grid>
              <Grid item xs={12} md={4} sx={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: 1.5 }}>
                <Button 
                  variant="outlined" 
                  color="primary" 
                  size="small"
                  onClick={() => setSummaryModalOpen(true)}
                  sx={{ fontSize: '0.75rem' }}
                >
                  View Summary
                </Button>
              </Grid>
            </Grid>
          </Paper>
          
          {/* Book Package Button Section - nazwane with Validation */}
          <Paper 
            elevation={2} 
            sx={{ 
              mt: 2, 
              p: 2.5, 
              display: 'flex', 
              flexDirection: 'column', 
              alignItems: 'center', 
              borderRadius: 1.5,
              background: 'linear-gradient(to right, rgba(255,255,255,0.95), rgba(245,245,255,0.95))',
              boxShadow: '0 4px 16px rgba(0,0,0,0.1)'
            }}
          >
            <Typography variant="h6" gutterBottom sx={{ mb: 1.5, fontWeight: 600, fontSize: '1rem' }}>
              Ready to book this amazing package?
            </Typography>

            {/* Show warning if no services are added */}
            {(!allServices || allServices.length === 0) && (
              <Alert 
                severity="info" 
                icon={<WarningIcon />}
                sx={{ 
                  mb: 2, 
                  width: '100%', 
                  maxWidth: 500,
                  borderRadius: 1.5
                }}
              >
                <Typography variant="body2" fontWeight={600} gutterBottom sx={{ fontSize: '0.8rem' }}>
                  No services have been added to your package yet
                </Typography>
                <Typography variant="body2" sx={{ fontSize: '0.75rem' }}>
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
                  mb: 2, 
                  width: '100%', 
                  maxWidth: 500,
                  borderRadius: 1.5
                }}
              >
                <Typography variant="body2" fontWeight={600} gutterBottom sx={{ fontSize: '0.8rem' }}>
                  {validateServiceDates.message}
                </Typography>
                <Typography variant="body2" sx={{ mb: 0.5, fontSize: '0.75rem' }}>
                  The following services need to be adjusted to fit within your tour dates 
                  ({moment(searchCriteria.checkIn, searchCriteria.checkIn.includes('-') ? 'YYYY-MM-DD' : 'DD/MM/YYYY').format('MMM DD, YYYY')} - {moment(searchCriteria.checkOut, searchCriteria.checkOut.includes('-') ? 'YYYY-MM-DD' : 'DD/MM/YYYY').format('MMM DD, YYYY')}):
                </Typography>
                <Box component="ul" sx={{ pl: 1.5, mt: 0.5 }}>
                  {validateServiceDates.invalidServices.map((service, index) => (
                    <li key={index}>
                      <Typography variant="caption" display="block" sx={{ fontSize: '0.7rem' }}>
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
                  mb: 2, 
                  width: '100%', 
                  maxWidth: 500,
                  borderRadius: 1.5
                }}
              >
                <Typography variant="body2" fontWeight={600} gutterBottom sx={{ fontSize: '0.8rem' }}>
                  Customer information required
                </Typography>
                <Typography variant="body2" sx={{ fontSize: '0.75rem' }}>
                  Please complete all mandatory fields in the Customer Information section above to enable booking.
                </Typography>
              </Alert>
            )}

            <Button 
              variant="contained" 
              color="primary" 
              size="medium" 
              disabled={isButtonDisabled}
              startIcon={loading ? null : <ShoppingCartIcon />}
              onClick={packageData?.tour?.tour_id > 0 ? handleUpdatePackage : handleBookPackage}
              sx={{
                py: 1,
                px: 3,
                fontSize: '0.9rem',
                fontWeight: 600,
                borderRadius: '25px',
                background: (!validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid)
                  ? 'linear-gradient(45deg, #9e9e9e 30%, #bdbdbd 90%)'
                  : 'linear-gradient(45deg, #3554D1 30%, #5672E9 90%)',
                boxShadow: (!validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid)
                  ? '0 2px 4px rgba(158, 158, 158, 0.3)'
                  : '0 6px 12px rgba(53, 84, 209, 0.3)',
                transition: 'all 0.3s ease',
                textTransform: 'none',
                '&:hover': {
                  transform: (!validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid) ? 'none' : 'translateY(-2px)',
                  boxShadow: (!validateServiceDates.isValid || !allServices || allServices.length === 0 || !customerInfoValid)
                    ? '0 2px 4px rgba(158, 158, 158, 0.3)'
                    : '0 8px 16px rgba(53, 84, 209, 0.4)',
                },
                '&:disabled': {
                  color: 'white',
                  cursor: 'not-allowed'
                },
                minWidth: 180,
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
              <Typography variant="body2" sx={{ mt: 1.5, color: 'success.main', fontWeight: 500, fontSize: '0.8rem' }}>
                Booking ID: {packageEnquiryId}
              </Typography>
            )}

            {((!allServices || allServices.length === 0) || !validateServiceDates.isValid || !customerInfoValid) && (
              <Typography variant="caption" sx={{ mt: 1.5, color: 'text.secondary', textAlign: 'center', maxWidth: 350, fontSize: '0.7rem' }}>
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
                boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
                borderRadius: 1.5
              }}
            >
              {snackbarMessage}
            </Alert>
          </Snackbar>
          
          {/* Debug info for snackbar */}
          {console.log('Snackbar Debug Info:', {
            openSnackbar,
            snackbarMessage,
            snackbarSeverity
          })}
          
          {/* Test button for snackbar - remove after debugging */}
          <Button 
            variant="outlined" 
            onClick={() => {
              console.log('Test button clicked');
              setSnackbarMessage('Test message from button');
              setSnackbarSeverity('error');
              setOpenSnackbar(true);
            }}
            sx={{ mt: 2 }}
          >
            Test Snackbar
          </Button>
          
          {/* Services Summary Modal */}
          <ServicesSummaryModal 
            open={summaryModalOpen} 
            onClose={() => setSummaryModalOpen(false)} 
          />
        </Grid>
        
        {/* Floating button to reopen sidebar */}
        {!sidebarOpen && (enquiryDetail?.hotel_on === true || 
           enquiryDetail?.attraction_on === true || 
           enquiryDetail?.restaurant_on === true || 
           enquiryDetail?.guide_on === true || 
           enquiryDetail?.localtransfer_on === true || 
           enquiryDetail?.ports_on === true || 
           enquiryDetail?.pickup_on === true || 
           enquiryDetail?.entry_port_on === true || 
           enquiryDetail?.exit_port_on === true || 
           enquiryDetail?.packaged_attraction_on === true) && (
          <Box sx={{
            position: 'fixed',
            right: 15,
            top: 500,
            zIndex: 1001
          }}>
            <Button
              variant="contained"
              onClick={() => setSidebarOpen(true)}
              sx={{
                bgcolor: '#3554D1',
                color: 'white',
                borderRadius: '50%',
                minWidth: 'auto',
                width: 48,
                height: 48,
                boxShadow: '0 2px 8px rgba(53, 84, 209, 0.3)',
                '&:hover': {
                  bgcolor: '#5672E9',
                  boxShadow: '0 4px 12px rgba(53, 84, 209, 0.4)',
                }
              }}
            >
              <CommentIcon sx={{ fontSize: '1.2rem' }} />
            </Button>
          </Box>
        )}

        {/* Enquiry Details Sidebar - Grid 4 (only shown when enquiryDetail exists) */}
        {shouldShowSidebar() && (
          <Grid item xs={12} md={5} lg={4} sx={{ 
            position: 'fixed', 
            right: 0,
            top: 90, // Start where itinerary content begins instead of top
            height: 'calc(100vh - 100px)', // Adjust height accordingly
            width: '70%',
            overflowY: 'auto',
            zIndex: 1000,
            bgcolor: 'white',
            boxShadow: '1px 0 6px rgba(0,0,0,0.1)',
            // Custom scrollbar styling for sidebar
            '&::-webkit-scrollbar': {
              width: '6px',
            },
            '&::-webkit-scrollbar-track': {
              background: '#f1f1f1',
              borderRadius: '3px',
            },
            '&::-webkit-scrollbar-thumb': {
              background: '#3554D1',
              borderRadius: '3px',
              '&:hover': {
                background: '#5672E9',
              },
            },
          }}>
            <Paper 
              elevation={2} 
              sx={{ 
                p: 2, 
                borderRadius: 0,
                height: '100%', // Changed from 100vh to 100%
                // Removed overflowY: 'auto' to eliminate duplicate scrollbar
                bgcolor: 'white',
                borderRight: '3px solid #3554D1',
                display: 'flex',
                flexDirection: 'column'
              }}
            >
              <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2, pb: 1, borderBottom: '2px solid #3554D1' }}>
                <Typography 
                  variant="h6" 
                  sx={{ 
                    fontWeight: 600,
                    color: '#3554D1',
                    fontSize: '1rem'
                  }}
                >
                  Enquiry for Services
                </Typography>
                <IconButton 
                  onClick={() => setSidebarOpen(false)}
                  sx={{ 
                    color: '#3554D1',
                    '&:hover': { bgcolor: 'rgba(53, 84, 209, 0.1)' }
                  }}
                >
                  <CloseIcon sx={{ fontSize: '1.2rem' }} />
                </IconButton>
              </Box>
              
              {/* Hotels */}
              {enquiryDetail.hotel_on && (
                <Box sx={{ mb: 2 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <HotelIcon sx={{ mr: 0.8, color: '#3554D1', fontSize: '1.2rem' }} />
                    <Typography variant="subtitle1" sx={{ fontSize: '0.9rem' }}>Hotels</Typography>
                  </Box>
                  <Grid container spacing={1.5}>
                    {enquiryDetail.hotel?.map((hotel, index) => (
                      <Grid item xs={12} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 1.5, overflow: 'hidden' }}>
                          <Box sx={{ width: 80, height: 60 }}>
                            <img 
                              src={hotel.main_image} 
                              alt={hotel.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1, flexGrow: 1 }}>
                            <Typography variant="subtitle2" fontWeight={500} sx={{ fontSize: '0.8rem' }}>{hotel.name}</Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Attractions */}
              {enquiryDetail.attraction_on && (
                <Box sx={{ mb: 2 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <AttractionsIcon sx={{ mr: 0.8, color: '#f44336', fontSize: '1.2rem' }} />
                    <Typography variant="subtitle1" sx={{ fontSize: '0.9rem' }}>Attractions</Typography>
                  </Box>
                  <Grid container spacing={1.5}>
                    {enquiryDetail.attraction?.map((attraction, index) => (
                      <Grid item xs={12} sm={6} md={12} lg={6} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 1.5, overflow: 'hidden' }}>
                          <Box sx={{ width: 60, height: 60 }}>
                            <img 
                              src={attraction.master_image} 
                              alt={attraction.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1, flexGrow: 1 }}>
                            <Typography variant="caption" fontWeight={500} sx={{ fontSize: '0.75rem' }}>{attraction.name}</Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Packaged Attractions */}
              {enquiryDetail.packaged_attraction_on && (
                <Box sx={{ mb: 2 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <LocalActivityIcon sx={{ mr: 0.8, color: '#9c27b0', fontSize: '1.2rem' }} />
                    <Typography variant="subtitle1" sx={{ fontSize: '0.9rem' }}>Packaged Attractions</Typography>
                  </Box>
                  <Grid container spacing={1.5}>
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
                          <Card sx={{ display: 'flex', borderRadius: 1.5, overflow: 'hidden' }}>
                            <Box sx={{ width: 60, height: 60, bgcolor: imageUrl ? 'transparent' : '#f0f0f0', display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                              {imageUrl ? (
                                <img 
                                  src={imageUrl} 
                                  alt={pkg.name}
                                  style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                  onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                                />
                              ) : (
                                <LocalActivityIcon sx={{ color: '#9c27b0', fontSize: 30 }} />
                              )}
                            </Box>
                            <Box sx={{ p: 1, flexGrow: 1 }}>
                              <Typography variant="caption" fontWeight={500} sx={{ fontSize: '0.75rem' }}>{pkg.name}</Typography>
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
                <Box sx={{ mb: 2 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <RestaurantIcon sx={{ mr: 0.8, color: '#ff9800', fontSize: '1.2rem' }} />
                    <Typography variant="subtitle1" sx={{ fontSize: '0.9rem' }}>Restaurants</Typography>
                  </Box>
                  <Grid container spacing={1.5}>
                    {enquiryDetail.restaurant?.map((restaurant, index) => (
                      <Grid item xs={12} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 1.5, overflow: 'hidden' }}>
                          <Box sx={{ width: 60, height: 60 }}>
                            <img 
                              src={restaurant.master_image} 
                              alt={restaurant.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1, flexGrow: 1 }}>
                            <Typography variant="caption" fontWeight={500} sx={{ fontSize: '0.75rem' }}>{restaurant.name}</Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Guides */}
              {enquiryDetail.guide_on && (
                <Box sx={{ mb: 2 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <PersonIcon sx={{ mr: 0.8, color: '#2196f3', fontSize: '1.2rem' }} />
                    <Typography variant="subtitle1" sx={{ fontSize: '0.9rem' }}>Guides</Typography>
                  </Box>
                  <Grid container spacing={1.5}>
                    {enquiryDetail.guide?.map((guide, index) => (
                      <Grid item xs={6} md={12} lg={6} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 1.5, overflow: 'hidden' }}>
                          <Box sx={{ width: 50, height: 50 }}>
                            <img 
                              src={guide.image} 
                              alt={guide.name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1, flexGrow: 1 }}>
                            <Typography variant="caption" fontWeight={500} sx={{ fontSize: '0.75rem' }}>{guide.name}</Typography>
                          </Box>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </Box>
              )}
              
              {/* Drivers/Vehicles */}
              {enquiryDetail.driver?.length > 0 && (
                <Box sx={{ mb: 2 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <DirectionsCarIcon sx={{ mr: 0.8, color: '#4caf50', fontSize: '1.2rem' }} />
                    <Typography variant="subtitle1" sx={{ fontSize: '0.9rem' }}>Vehicles</Typography>
                  </Box>
                  <Grid container spacing={1.5}>
                    {enquiryDetail.driver?.map((vehicle, index) => (
                      <Grid item xs={12} sm={6} md={12} lg={6} key={index}>
                        <Card sx={{ display: 'flex', borderRadius: 1.5, overflow: 'hidden' }}>
                          <Box sx={{ width: 60, height: 60 }}>
                            <img 
                              src={vehicle.image} 
                              alt={vehicle.vehicle_name}
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              onError={(e) => { e.target.src = '/img/general/placeholder.svg' }}
                            />
                          </Box>
                          <Box sx={{ p: 1, flexGrow: 1 }}>
                            <Typography variant="caption" fontWeight={500} sx={{ fontSize: '0.75rem' }}>{vehicle.vehicle_name}</Typography>
                            <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
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
                <Box sx={{ mb: 2 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <FlightIcon sx={{ mr: 0.8, color: '#795548', fontSize: '1.2rem' }} />
                    <Typography variant="subtitle1" sx={{ fontSize: '0.9rem' }}>Ports</Typography>
                  </Box>
                  {enquiryDetail.ports?.map((port, index) => (
                    <Card key={index} sx={{ mb: 1.5, borderRadius: 1.5, overflow: 'hidden' }}>
                      <CardContent sx={{ p: 1.5 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
                          <Chip 
                            label={port.type === 'entry' ? 'Arrival' : 'Departure'} 
                            color={port.type === 'entry' ? 'primary' : 'secondary'}
                            size="small"
                            sx={{ mr: 0.8, fontSize: '0.65rem' }}
                          />
                        </Box>
                        <Typography variant="caption" sx={{ mb: 0.3, fontSize: '0.75rem' }}>
                          <strong>Port:</strong> {port.port_address}
                        </Typography>
                        <Typography variant="caption" sx={{ fontSize: '0.75rem' }}>
                          <strong>Drop-off:</strong> {port.dropoff_name}
                        </Typography>
                      </CardContent>
                    </Card>
                  ))}
                </Box>
              )}
              
              {/* Service Status Summary */}
              <Box sx={{ mt: 3, pt: 1.5, borderTop: '1px solid #e0e0e0' }}>
                <Typography variant="subtitle1" sx={{ mb: 1.5, fontSize: '0.9rem' }}>Service Status</Typography>
                <Grid container spacing={0.8}>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<HotelIcon sx={{ fontSize: '1rem' }} />} 
                      label="Hotels" 
                      color={enquiryDetail.hotel_on ? "success" : "default"}
                      variant={enquiryDetail.hotel_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.7rem', height: '24px' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<AttractionsIcon sx={{ fontSize: '1rem' }} />} 
                      label="Attractions" 
                      color={enquiryDetail.attraction_on ? "success" : "default"}
                      variant={enquiryDetail.attraction_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.7rem', height: '24px' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<RestaurantIcon sx={{ fontSize: '1rem' }} />} 
                      label="Restaurants" 
                      color={enquiryDetail.restaurant_on ? "success" : "default"}
                      variant={enquiryDetail.restaurant_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.7rem', height: '24px' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<PersonIcon sx={{ fontSize: '1rem' }} />} 
                      label="Guides" 
                      color={enquiryDetail.guide_on ? "success" : "default"}
                      variant={enquiryDetail.guide_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.7rem', height: '24px' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<DirectionsCarIcon sx={{ fontSize: '1rem' }} />} 
                      label="Transport" 
                      color={enquiryDetail.pickup_on ? "success" : "default"}
                      variant={enquiryDetail.pickup_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.7rem', height: '24px' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<LocalActivityIcon sx={{ fontSize: '1rem' }} />} 
                      label="Packages" 
                      color={enquiryDetail.packaged_attraction_on ? "success" : "default"}
                      variant={enquiryDetail.packaged_attraction_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.7rem', height: '24px' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<FlightIcon sx={{ fontSize: '1rem' }} />} 
                      label="Entry Port" 
                      color={enquiryDetail.entry_port_on ? "success" : "default"}
                      variant={enquiryDetail.entry_port_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.7rem', height: '24px' }}
                    />
                  </Grid>
                  <Grid item xs={6}>
                    <Chip 
                      icon={<FlightIcon sx={{ fontSize: '1rem' }} />} 
                      label="Exit Port" 
                      color={enquiryDetail.exit_port_on ? "success" : "default"}
                      variant={enquiryDetail.exit_port_on ? "filled" : "outlined"}
                      sx={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.7rem', height: '24px' }}
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