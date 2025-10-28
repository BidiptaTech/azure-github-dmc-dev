import React, { useState, useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  Box,
  Container,
  Typography,
  Paper,
  Grid,
  Button,
  Divider,
  Chip,
  Card,
  CardContent,
  Avatar,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  Accordion,
  AccordionSummary,
  AccordionDetails,
  Alert,
  Backdrop,
  CircularProgress,
  Snackbar,
  TextField
} from "@mui/material";
import { styled } from "@mui/material/styles";
import {
  CalendarMonth as CalendarIcon,
  LocationOn as LocationIcon,
  People as PeopleIcon,
  Hotel as HotelIcon,
  Flight as FlightIcon,
  ConfirmationNumber as TicketIcon,
  Place as PlaceIcon,
  Person as PersonIcon,
  Restaurant as RestaurantIcon,
  CheckCircle as CheckCircleIcon,
  Security as SecurityIcon,
  Support as SupportIcon,
  ExpandMore as ExpandMoreIcon,
  ArrowBack as ArrowBackIcon,
  Send as SendIcon,
  Error as ErrorIcon,
  AttachMoney as AttachMoneyIcon,
  DirectionsCar as CarIcon,
  LocalTaxi as TaxiIcon,
  DirectionsBus as BusIcon,
  Explore as ExploreIcon,
  Map as MapIcon,
  Tour as TourIcon
} from "@mui/icons-material";
import { 
  updateServiceDetails, 
  updateCalculatedPrice, 
  clearServiceDetails, 
  clearSpecificService,
  fetchBookingid
} from "@/slice/common/EnquirySlice";
import { fetchEnquiryList } from "@/slice/common/enquiryListSlice";
import { 
  updateSearchState,
  settourdetails,
  setId 
} from "@/slice/hotel/hotelSlice";
import { setBookingType } from "@/slice/common/commonSlice";
import axios from "axios";
import Cookies from "js-cookie";  
import { BASE_URL } from '@/services/api';


// Styled components
const SectionPaper = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(3),
  marginBottom: theme.spacing(3),
  borderRadius: theme.spacing(2),
  boxShadow: "0 4px 20px rgba(0, 0, 0, 0.08)",
  transition: "all 0.3s ease",
  overflow: "hidden",
  "&:hover": {
    boxShadow: "0 8px 30px rgba(0, 0, 0, 0.12)",
    transform: "translateY(-3px)"
  },
  // Mobile and tablet responsive styling
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(1.5),
    marginBottom: theme.spacing(1.5),
    borderRadius: theme.spacing(1.5),
  },
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(1),
    marginBottom: theme.spacing(1),
    borderRadius: theme.spacing(1),
  }
}));

const SectionHeader = styled(Box)(({ theme }) => ({
  display: "flex",
  alignItems: "center",
  marginBottom: theme.spacing(2),
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    marginBottom: theme.spacing(1),
    flexDirection: 'column',
    alignItems: 'flex-start',
    gap: theme.spacing(0.5)
  }
}));

const SectionIcon = styled(Avatar)(({ theme, bgcolor }) => ({
  backgroundColor: bgcolor || theme.palette.primary.main,
  color: theme.palette.common.white,
  marginRight: theme.spacing(2),
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    marginRight: 0,
    marginBottom: theme.spacing(1),
    width: 32,
    height: 32
  }
}));

const DetailItem = styled(Box)(({ theme }) => ({
  display: "flex",
  justifyContent: "space-between",
  alignItems: "center",
  padding: theme.spacing(1.5),
  borderRadius: theme.spacing(1),
  marginBottom: theme.spacing(1),
  backgroundColor: theme.palette.background.paper,
  border: `1px solid ${theme.palette.divider}`,
  transition: "all 0.2s ease",
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
    transform: "translateX(5px)"
  },
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(0.75),
    marginBottom: theme.spacing(0.25),
    flexDirection: 'column',
    alignItems: 'flex-start',
    gap: theme.spacing(0.25),
    "&:hover": {
      transform: "translateX(2px)"
    }
  }
}));

const ServiceCard = styled(Card)(({ theme, servicecolor }) => ({
  marginBottom: theme.spacing(2),
  transition: "all 0.3s ease",
  borderLeft: `4px solid ${servicecolor || theme.palette.primary.main}`,
  "&:hover": {
    boxShadow: "0 6px 15px rgba(0, 0, 0, 0.1)",
    transform: "translateY(-3px)"
  },
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    marginBottom: theme.spacing(1),
    borderLeft: `3px solid ${servicecolor || theme.palette.primary.main}`,
    "&:hover": {
      transform: "translateY(-2px)"
    }
  }
}));

const ActionButton = styled(Button)(({ theme }) => ({
  padding: theme.spacing(1.5, 3),
  borderRadius: theme.spacing(3),
  boxShadow: "0 4px 14px rgba(0, 0, 0, 0.1)",
  transition: "all 0.3s ease",
  "&:hover": {
    transform: "translateY(-3px)",
    boxShadow: "0 6px 20px rgba(0, 0, 0, 0.15)"
  },
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(0.75, 1.5),
    borderRadius: theme.spacing(2),
    fontSize: '0.875rem',
    "&:hover": {
      transform: "translateY(-2px)"
    }
  }
}));

// Service colors (matching the ones from BookingEnquiries)
const serviceColors = {
  hotel: '#1976d2',
  entryExitPort: '#2e7d32',
  attraction: '#d32f2f',
  localTour: '#7b1fa2',
  tourGuide: '#ed6c02',
  restaurant: '#0288d1'
};

// Helper function to format service name
const formatServiceName = (key) => {
  return key
    .replace(/([A-Z])/g, " $1")
    .replace(/^./, (str) => str.toUpperCase());
};

// Helper function to get icon for service
const getServiceIcon = (service) => {
  switch(service) {
    case "hotel": return <HotelIcon />;
    case "entryExitPort": return <CarIcon />;
    case "attraction": return <ExploreIcon />;
    case "localTour": return <TourIcon />;
    case "tourGuide": return <PersonIcon />;
    case "restaurant": return <RestaurantIcon />;
    case "packagedAttractions": return <MapIcon />;
    default: return <CheckCircleIcon />;
  }
};

const ConfirmDetails = ({ bookingOptions, onBack, onComplete, resetBookingOptions }) => {
  // Scroll to top when component mounts
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);
  const dispatch = useDispatch();
  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState(null);
  const [submitSuccess, setSubmitSuccess] = useState(false);
  const [localEnquiryId, setLocalEnquiryId] = useState(null);
  const [countryValue, setCountryValue] = useState("");
  const [cityValue, setCityValue] = useState("");
  const [calculatedPrice, setCalculatedPrice] = useState(0);
  
  const bookingDetails = useSelector((state) => state.enquiry);
  const serviceDetails = useSelector((state) => state.enquiry.serviceDetails || {});
  const selectedServices = useSelector((state) => state.enquiry.selectedServices || Object.keys(bookingOptions || {}).filter(key => bookingOptions[key]));
  
  // Update how we get the ID - prioritizing multiEnqId for multi-DMC enquiries, then enquiryId over tourId
  const enquiryId = useSelector((state) => {
    // Try several places in state where the enquiry ID might be stored
    // Prefer multi_enq_id for multi-DMC enquiries
    return state.enquiry.multiEnqId ||
           state.enquiry.enquiryId || 
           state.enquiry.id || 
           (state.enquiry.bookings && state.enquiry.bookings.length > 0 ? 
             (state.enquiry.bookings[0].multiEnqId || state.enquiry.bookings[0].enquiryId) : null) ||
           state.enquiry.tourId;
  });
  
  const isMultiEnquiry = useSelector((state) => !!state.enquiry.multiEnqId);
  const enquiryStatus = useSelector((state) => state.enquiry.status);
  
  // Log all state values on component mount
  useEffect(() => {
    console.log("Component mounted with state:", {
      enquiryId,
      isMultiEnquiry,
      selectedServices,
      serviceDetails,
      enquiryStatus
    });
    
 
    
    // If we found an enquiryId, store it locally
    if (enquiryId) {
      setLocalEnquiryId(enquiryId);
      console.log("Enquiry ID found and stored locally:", enquiryId);
    } else {
      console.warn("No enquiryId found in Redux state");
    }
  }, []);
  
  // Watch for changes in enquiryId
  useEffect(() => {
    console.log("Enquiry ID changed:", enquiryId);
    if (enquiryId) {
      setLocalEnquiryId(enquiryId);
    }
  }, [enquiryId]);

  // Watch for changes in serviceDetails for debugging
  useEffect(() => {
    console.log("Service Details changed:", serviceDetails);
    console.log("Selected Services:", selectedServices);
  }, [serviceDetails, selectedServices]);

  // Calculate and update price when service details change
  useEffect(() => {
    if (selectedServices && serviceDetails) {
      const price = calculateApproximatePrice();
      setCalculatedPrice(price);
    }
  }, [selectedServices, serviceDetails, bookingDetails?.guestCounts, bookingDetails?.checkinDate, bookingDetails?.checkoutDate]);

  // Calculate and store price when component mounts
  useEffect(() => {
    if (selectedServices && serviceDetails) {
      const price = calculateApproximatePrice();
      setCalculatedPrice(price);
    }
  }, [selectedServices, serviceDetails, bookingDetails]);
  
  // Function to calculate approximate pricing using ACTUAL API prices
  const calculateApproximatePrice = () => {
    let totalPrice = 0;
    
    // Get guest count from booking details for per-person calculations
    const guestCounts = bookingDetails?.guestCounts || {};
    const totalPersons = (guestCounts.Adults || 1) + (guestCounts.Children || 0) + (guestCounts.Infants || 0);
    
    // Calculate days between check-in and check-out
    const checkinDate = bookingDetails?.checkinDate ? new Date(bookingDetails.checkinDate) : new Date();
    const checkoutDate = bookingDetails?.checkoutDate ? new Date(bookingDetails.checkoutDate) : new Date(Date.now() + 24 * 60 * 60 * 1000);
    const totalDays = Math.max(1, Math.ceil((checkoutDate - checkinDate) / (24 * 60 * 60 * 1000)));

    console.log("Pricing calculation details:", { totalPersons, totalDays, serviceDetails });

    // Calculate hotel pricing using ACTUAL hotel prices
    if (selectedServices.includes("hotel") && serviceDetails.hotel) {
      const hotelDetails = serviceDetails["undefined"] || serviceDetails.hotel;
      const selectedHotels = hotelDetails.preferredHotels || [];
      
      let hotelPrice = 0;
      selectedHotels.forEach(hotel => {
        // Use actual single_base_price from API, fallback to default if 0 or missing
        const actualPrice = parseFloat(hotel.single_base_price) || 120; // 120 as fallback
        hotelPrice += actualPrice * totalDays;
        console.log(`Hotel "${hotel.name}": $${actualPrice}/night × ${totalDays} days = $${actualPrice * totalDays}`);
      });
      
      totalPrice += hotelPrice;
      console.log(`Total hotel pricing: $${hotelPrice}`);
    }

    // Calculate port/transfer pricing using ACTUAL vehicle prices
    if (selectedServices.includes("entryExitPort") && serviceDetails.entryExitPort) {
      const entryExitDetails = serviceDetails.entryExitPort;
      let transferCount = 0;
      if (entryExitDetails.showEntryPort !== false) transferCount++; // Entry transfer
      if (entryExitDetails.showExitPort === true) transferCount++; // Exit transfer
      
      const cars = entryExitDetails.preferredCars || [];
      let transferPrice = 0;
      
      if (cars.length > 0) {
        cars.forEach(car => {
          // Use actual base_price from API, fallback to default if missing
          const actualPrice = parseFloat(car.base_price) || 45; // 45 as fallback
          transferPrice += actualPrice * transferCount;
          console.log(`Vehicle "${car.vehicle_name}": $${actualPrice} × ${transferCount} transfers = $${actualPrice * transferCount}`);
        });
      } else {
        // If no specific cars selected, use default price
        transferPrice = transferCount * 45; // Default port transfer price
      }
      
      totalPrice += transferPrice;
      console.log(`Total transfer pricing: $${transferPrice}`);
    }

    // Calculate attraction pricing using ACTUAL attraction prices
    if (selectedServices.includes("attraction") && serviceDetails.attraction) {
      const attractions = serviceDetails.attraction.selectedAttractions || [];
      
      let attractionPrice = 0;
      attractions.forEach(attraction => {
        // Use actual base_price from API, fallback to default if 0 or missing
        const actualPrice = parseFloat(attraction.base_price) || 25; // 25 as fallback
        attractionPrice += actualPrice * totalPersons;
        console.log(`Attraction "${attraction.name}": $${actualPrice}/person × ${totalPersons} persons = $${actualPrice * totalPersons}`);
      });
      
      totalPrice += attractionPrice;
      console.log(`Total attraction pricing: $${attractionPrice}`);
    }

    // Calculate local tour pricing using ACTUAL vehicle prices
    if (selectedServices.includes("localTour") && serviceDetails.localTour) {
      const localTourCars = serviceDetails.localTour.preferredCars || [];
      
      let localTourPrice = 0;
      if (localTourCars.length > 0) {
        localTourCars.forEach(car => {
          // Use actual base_price from API, fallback to default if missing
          const actualPrice = parseFloat(car.base_price) || 85; // 85 as fallback
          localTourPrice += actualPrice * totalDays;
          console.log(`Local tour vehicle "${car.vehicle_name}": $${actualPrice}/day × ${totalDays} days = $${actualPrice * totalDays}`);
        });
      } else {
        // If no specific cars selected, use default price
        localTourPrice = 85 * totalDays; // Default local tour price
      }
      
      totalPrice += localTourPrice;
      console.log(`Total local tour pricing: $${localTourPrice}`);
    }

    // Calculate tour guide pricing using ACTUAL guide prices
    if (selectedServices.includes("tourGuide") && serviceDetails.tourGuide) {
      const guides = serviceDetails.tourGuide.preferredGuides || [];
      
      let guidePrice = 0;
      guides.forEach(guide => {
        // Use actual base_price from API, fallback to default if missing
        const actualPrice = parseFloat(guide.base_price) || 150; // 150 as fallback
        guidePrice += actualPrice * totalDays;
        console.log(`Guide "${guide.name}": $${actualPrice}/day × ${totalDays} days = $${actualPrice * totalDays}`);
      });
      
      totalPrice += guidePrice;
      console.log(`Total guide pricing: $${guidePrice}`);
    }

    // Calculate restaurant pricing using ACTUAL restaurant prices
    if (selectedServices.includes("restaurant") && serviceDetails.restaurant) {
      const restaurants = serviceDetails.restaurant.selectedRestaurants || [];
      
      let restaurantPrice = 0;
      restaurants.forEach(restaurant => {
        // Use actual base-price from API, fallback to default if missing
        const actualPrice = parseFloat(restaurant['base-price']) || 35; // 35 as fallback
        restaurantPrice += actualPrice * totalPersons;
        console.log(`Restaurant "${restaurant.name}": $${actualPrice}/person × ${totalPersons} persons = $${actualPrice * totalPersons}`);
      });
      
      totalPrice += restaurantPrice;
      console.log(`Total restaurant pricing: $${restaurantPrice}`);
    }

    console.log(`Total approximate price calculated: $${totalPrice}`);
    const roundedPrice = Math.round(totalPrice);
    
    // Dispatch the calculated price to Redux so EnquirySlice can use it
    dispatch(updateCalculatedPrice(roundedPrice));
    
    return roundedPrice; // Round to nearest dollar
  };

  // console.log("Selected Services:", selectedServices);
  // console.log("Service Details from Redux:", serviceDetails);
  // console.log("Enquiry ID from Redux:", enquiryId);
  // console.log("Local Enquiry ID:", localEnquiryId);
  
  // Format date to be more readable
  const formatDate = (dateString) => {
    if (!dateString) return "Not specified";
    
    // If dateString is in format DD/MM/YYYY
    const parts = dateString.split('/');
    if (parts.length === 3) {
      return `${parts[0]} ${getMonthName(parts[1])} ${parts[2]}`;
    }
    
    return dateString;
  };
  
  const getMonthName = (month) => {
    const months = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];
    return months[parseInt(month) - 1] || month;
  };
  
  // Helper function to safely render data that might be undefined
  const renderSafely = (data, fallback = "Not specified") => {
    if (data === null || data === undefined || (typeof data === 'string' && data.trim() === '')) {
      return fallback;
    }
    return data;
  };
  
  // Add new useEffect for detailed debugging
  // useEffect(() => {
  //   console.log("==== SERVICE DETAILS DEBUG ====");
  //   console.log("Selected Services:", selectedServices);
  //   console.log("Full serviceDetails:", serviceDetails);
  //   if (selectedServices.includes("hotel")) {
  //     console.log("Hotel Details:", serviceDetails.hotel);
  //     console.log("Hotel preferredHotels:", serviceDetails.hotel?.preferredHotels);
  //   }
  // }, [serviceDetails, selectedServices]);
  
  // Add state for hotel data
  const [hotelData, setHotelData] = useState({
    starCategory: null,
    compareHotels: "no",
    preferredHotels: [],
    remarks: ""
  });

  // Add useEffect to get hotel data from state once on mount
  useEffect(() => {
    console.log("Setting up hotel data");     
    
    // Get hotel data from bookingDetails if available
    if (bookingDetails?.serviceDetails) {
      // Check for data in both the hotel key and potentially under the undefined key
      let hotelDetails = bookingDetails.serviceDetails.hotel || {};
      let undefinedDetails = bookingDetails.serviceDetails["undefined"] || {};
      
      // If there's data under the undefined key, use that instead as it seems to contain the actual values
      // console.log("Found hotel details in state:", hotelDetails);
      // console.log("Found undefined details in state:", undefinedDetails);
      
      // Merge both sources, with undefined key taking precedence
      setHotelData({
        starCategory: undefinedDetails.starCategory || hotelDetails.starCategory || null,
        compareHotels: undefinedDetails.compareHotels || hotelDetails.compareHotels || "no",
        preferredHotels: Array.isArray(undefinedDetails.preferredHotels) ? 
          undefinedDetails.preferredHotels : 
          (Array.isArray(hotelDetails.preferredHotels) ? hotelDetails.preferredHotels : []),
        remarks: undefinedDetails.remarks || hotelDetails.remarks || ""
      });
      
      console.log("Initialized hotel data with remarks:", undefinedDetails.remarks || hotelDetails.remarks || "");
    } else {
      console.log("No hotel details found in state");
    }
  }, []);
  
  // Initialize country and city values from Redux state
  useEffect(() => {
    if (bookingDetails.searchLocation) {
      setCountryValue(bookingDetails.searchLocation.country || "");
      setCityValue(bookingDetails.searchLocation.city || "");
    }
  }, [bookingDetails.searchLocation]);

  // Handle country and city change
  const handleCountryChange = (e) => {
    setCountryValue(e.target.value);
    if (bookingDetails.searchLocation) {
      dispatch(updateServiceDetails({
        service: 'searchLocation',
        data: { 
          ...bookingDetails.searchLocation,
          country: e.target.value 
        }
      }));
    }
  };

  const handleCityChange = (e) => {
    setCityValue(e.target.value);
    if (bookingDetails.searchLocation) {
      dispatch(updateServiceDetails({
        service: 'searchLocation',
        data: { 
          ...bookingDetails.searchLocation,
          city: e.target.value 
        }
      }));
    }
  };
  
  // Update the renderServiceDetails function for "hotel" case
  const renderServiceDetails = (service) => {
    // Define details variable outside the if statement so it's available for all services
    let details = serviceDetails[service] || {};
    
    // For hotel service, check both the normal key and the undefined key
    if (service === "hotel") {
      // Check if there's data in the undefined key
      if (serviceDetails["undefined"] && Object.keys(serviceDetails["undefined"]).length > 0) {
        console.log("Using hotel data from 'undefined' key for display");
        details = serviceDetails["undefined"];
      }
      
      // Use merged data to ensure we have all properties
      const hotelDetails = {
        ...details,
        ...hotelData
      };
      
      // console.log("Hotel Details Extended Debug:");
      // console.log("- Star Category:", hotelDetails.starCategory);
      // console.log("- Compare Hotels:", hotelDetails.compareHotels);
      // console.log("- Preferred Hotels Array:", hotelDetails.preferredHotels);
      // console.log("- Array Length:", hotelDetails.preferredHotels?.length);
      // console.log("- Is Array?", Array.isArray(hotelDetails.preferredHotels));
      // console.log("- Remarks:", hotelDetails.remarks);
      
        return (
          <>
            <DetailItem>
              <Typography variant="body2" fontWeight={500}>Star Category:</Typography>
            <Typography variant="body2">{renderSafely(hotelDetails.starCategory, "Not specified")} {hotelDetails.starCategory ? "Stars" : ""}</Typography>
            </DetailItem>
            
            <DetailItem>
              <Typography variant="body2" fontWeight={500}>Compare Hotels:</Typography>
              <Chip 
                size="small" 
              label={hotelDetails.compareHotels === "yes" ? "Yes" : "No"} 
              color={hotelDetails.compareHotels === "yes" ? "primary" : "default"}
              />
            </DetailItem>
            
          {hotelDetails.preferredHotels && hotelDetails.preferredHotels.length > 0 ? (
              <Box sx={{ mt: 1 }}>
                <Typography variant="body2" fontWeight={500} sx={{ mb: 1 }}>Preferred Hotels:</Typography>
                <List dense disablePadding>
                {hotelDetails.preferredHotels.map((hotel, index) => (
                    <ListItem key={index} dense disableGutters>
                      <ListItemIcon sx={{ minWidth: 30 }}>
                        <HotelIcon fontSize="small" color="primary" />
                      </ListItemIcon>
                      <ListItemText primary={typeof hotel === 'object' ? hotel.name || 'Unnamed Hotel' : hotel} />
                    </ListItem>
                  ))}
                </List>
              </Box>
            ) : (
              <DetailItem>
                <Typography variant="body2" fontWeight={500}>Preferred Hotels:</Typography>
                <Typography variant="body2">None selected</Typography>
              </DetailItem>
            )}
            
          {/* Add a manual button to update hotel data */}
          {/* {process.env.NODE_ENV !== 'production' && (
            <Box sx={{ mt: 2, mb: 2 }}>
              <Button 
                size="small" 
                variant="outlined" 
                color="info"
                onClick={() => {
                  console.log("Manual update of hotel data");
                  // Refresh from serviceDetails if available
                  if (serviceDetails.hotel) {
                    setHotelData({
                      ...hotelData,
                      ...serviceDetails.hotel
                    });
                  }
                }}
              >
                Debug: Refresh Hotel Data
              </Button>
            </Box>
          )} */}
          
          {hotelDetails.remarks ? (
              <Box sx={{ mt: 2 }}>
                <Typography variant="body2" fontWeight={500}>Remarks:</Typography>
                <Typography variant="body2" sx={{ fontStyle: 'italic', mt: 1 }}>
                "{hotelDetails.remarks}"
                </Typography>
              </Box>
            ) : (
              <DetailItem>
                <Typography variant="body2" fontWeight={500}>Remarks:</Typography>
                <Typography variant="body2">None provided</Typography>
              </DetailItem>
            )}
          </>
        );
    }
    
    // console.log(`Rendering details for ${service}:`, details);
    
    switch(service) {
      case "hotel":
        // This won't be reached as we're handling this above
        return null;
      case "entryExitPort":
        return (
          <>
            <Box sx={{ mb: 3 }}>
              <Typography variant="subtitle1" fontWeight={600} color="primary.main" sx={{ mb: 1 }}>
                Entry Port
              </Typography>
              <Divider sx={{ mb: 2 }} />
              
              {details.showEntryPort !== false && (
                <>
                  {/* Entry Port Details */}
                  {details.portAddress && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Port/Airport Pickup:</Typography>
                      <Typography variant="body2">
                        {typeof details.portAddress === 'string' 
                          ? details.portAddress 
                          : details.portAddress.port_name || details.portAddress.name || "Selected Port"}
                      </Typography>
                    </DetailItem>
                  )}
                  
                  <DetailItem>
                    <Typography variant="body2" fontWeight={500}>Drop-off Type:</Typography>
                    <Chip 
                      size="small" 
                      label={details.entryDropoffLocationType || "Hotel"} 
                      color="primary"
                    />
                  </DetailItem>
                  
                  {/* Different types of drop-off locations */}
                  {details.hotelDropOff && details.entryDropoffLocationType === "hotel" && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Hotel Drop-off:</Typography>
                      <Typography variant="body2">
                        {typeof details.hotelDropOff === 'string' 
                          ? details.hotelDropOff 
                          : details.hotelDropOff.name || details.hotelDropOff.hotel_name || "Selected Hotel"}
                      </Typography>
                    </DetailItem>
                  )}
                  
                  {/* Add separate handling for attraction drop-off */}
                  {details.attractionDropOff && details.entryDropoffLocationType === "attraction" && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Attraction Drop-off:</Typography>
                      <Typography variant="body2">
                        {typeof details.attractionDropOff === 'string' 
                          ? details.attractionDropOff 
                          : details.attractionDropOff.name || details.attractionDropOff.attraction_name || "Selected Attraction"}
                      </Typography>
                    </DetailItem>
                  )}
                  
                  {/* Add separate handling for restaurant drop-off */}
                  {details.restaurantDropOff && details.entryDropoffLocationType === "restaurant" && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Restaurant Drop-off:</Typography>
                      <Typography variant="body2">
                        {typeof details.restaurantDropOff === 'string' 
                          ? details.restaurantDropOff 
                          : details.restaurantDropOff.name || details.restaurantDropOff.restaurant_name || "Selected Restaurant"}
                      </Typography>
                    </DetailItem>
                  )}
                  
                  {details.destination && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Drop-off Location:</Typography>
                      <Typography variant="body2">
                        {typeof details.destination === 'string' 
                          ? details.destination 
                          : details.destination.name || "Selected Destination"}
                      </Typography>
                    </DetailItem>
                  )}
                </>
              )}
            </Box>
            
            <Box sx={{ mb: 3 }}>
              <Typography variant="subtitle1" fontWeight={600} color="primary.main" sx={{ mb: 1 }}>
                Exit Port
              </Typography>
              <Divider sx={{ mb: 2 }} />
              
              {details.showExitPort ? (
                <>
                  {/* Exit Port Details */}
                  {details.exitPickupLocationType && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Pickup Type:</Typography>
                      <Chip 
                        size="small" 
                        label={details.exitPickupLocationType || "Hotel"} 
                        color="primary"
                      />
                    </DetailItem>
                  )}
                  
                  {/* Exit Port Pickup Location */}
                  {details.exitPickupLocationType === "hotel" && details.exitPickupLocation && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Hotel Pickup:</Typography>
                      <Typography variant="body2">
                        {typeof details.exitPickupLocation === 'string' 
                          ? details.exitPickupLocation 
                          : details.exitPickupLocation.name || "Selected Hotel"}
                      </Typography>
                    </DetailItem>
                  )}
                  
                  {details.exitPickupLocationType === "attraction" && (details.exitAttractionPickup || details.exitPickupLocation) && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Attraction Pickup:</Typography>
                      <Typography variant="body2">
                        {typeof details.exitAttractionPickup === 'object' 
                          ? details.exitAttractionPickup?.name || details.exitAttractionPickup?.attraction_name || "Selected Attraction"
                          : typeof details.exitPickupLocation === 'object'
                            ? details.exitPickupLocation.name || details.exitPickupLocation.attraction_name || "Selected Attraction"
                            : "Selected Attraction"}
                      </Typography>
                    </DetailItem>
                  )}
                  
                  {details.exitPickupLocationType === "restaurant" && (details.exitRestaurantPickup || details.exitPickupLocation) && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Restaurant Pickup:</Typography>
                      <Typography variant="body2">
                        {typeof details.exitRestaurantPickup === 'object' 
                          ? details.exitRestaurantPickup?.name || details.exitRestaurantPickup?.restaurant_name || "Selected Restaurant"
                          : typeof details.exitPickupLocation === 'object'
                            ? details.exitPickupLocation.name || details.exitPickupLocation.restaurant_name || "Selected Restaurant"
                            : "Selected Restaurant"}
                      </Typography>
                    </DetailItem>
                  )}
                  
                  {/* If no specific exitPickupLocation but we have destination data */}
                  {!details.exitPickupLocation && details.destination && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Pickup Location:</Typography>
                      <Typography variant="body2">
                        {typeof details.destination === 'string' 
                          ? details.destination 
                          : details.destination.name || "Selected Destination"}
                      </Typography>
                    </DetailItem>
                  )}
                  
                  {details.exitPortAddress && (
                    <DetailItem>
                      <Typography variant="body2" fontWeight={500}>Port/Airport Drop-off:</Typography>
                      <Typography variant="body2">
                        {typeof details.exitPortAddress === 'string' 
                          ? details.exitPortAddress 
                          : details.exitPortAddress.port_name || details.exitPortAddress.name || "Selected Port"}
                      </Typography>
                    </DetailItem>
                  )}
                </>
              ) : (
                <Typography variant="body2" color="text.secondary" sx={{ fontStyle: 'italic' }}>
                  Exit port service not selected
                </Typography>
              )}
            </Box>
            
            <Box>
              <Typography variant="subtitle1" fontWeight={600} color="primary.main" sx={{ mb: 1 }}>
                Car Details
              </Typography>
              <Divider sx={{ mb: 2 }} />
              
              <DetailItem>
                <Typography variant="body2" fontWeight={500}>Car Type:</Typography>
                <Chip 
                  size="small" 
                  label={details.carType || "Sharable"} 
                  color={details.carType === "private" ? "primary" : "default"}
                />
              </DetailItem>
              
              {details.preferredCars && details.preferredCars.length > 0 && (
                <Box sx={{ mt: 1 }}>
                  <Typography variant="body2" fontWeight={500} sx={{ mb: 1 }}>Preferred Cars:</Typography>
                  <List dense disablePadding>
                    {details.preferredCars.map((car, index) => (
                      <ListItem key={index} dense disableGutters>
                                              <ListItemIcon sx={{ minWidth: 30 }}>
                        <CarIcon fontSize="small" color="primary" />
                      </ListItemIcon>
                        <ListItemText primary={
                          typeof car === 'string' 
                            ? car 
                            : car.name || car.vehicle_name || car.id || 'Vehicle'
                        } />
                      </ListItem>
                    ))}
                  </List>
                </Box>
              )}
              
              {details.remarks && (
                <Box sx={{ mt: 2 }}>
                  <Typography variant="body2" fontWeight={500}>Remarks:</Typography>
                  <Typography variant="body2" sx={{ fontStyle: 'italic', mt: 1 }}>
                    "{details.remarks}"
                  </Typography>
                </Box>
              )}
            </Box>
          </>
        );
        
      case "attraction":
        return (
          <>
            {details.selectedAttractions && (
              <Box sx={{ mt: 1 }}>
                <Typography variant="body2" fontWeight={500} sx={{ mb: 1 }}>Selected Attractions:</Typography>
                <List dense disablePadding>
                  {Array.isArray(details.selectedAttractions) ? 
                    details.selectedAttractions.map((attraction, index) => (
                    <ListItem key={index} dense disableGutters>
                      <ListItemIcon sx={{ minWidth: 30 }}>
                        <ExploreIcon fontSize="small" color="error" />
                      </ListItemIcon>
                      <ListItemText primary={attraction.name || attraction} />
                    </ListItem>
                    )) : 
                    // Handle when it's an object
                    <ListItem dense disableGutters>
                      <ListItemIcon sx={{ minWidth: 30 }}>
                        <ExploreIcon fontSize="small" color="error" />
                      </ListItemIcon>
                      <ListItemText primary={details.selectedAttractions.name || "Selected Attraction"} />
                    </ListItem>
                  }
                </List>
              </Box>
            )}
            
            <DetailItem>
              <Typography variant="body2" fontWeight={500}>Need Transport:</Typography>
              <Chip 
                size="small" 
                label={details.needTransport ? "Yes" : "No"} 
                color={details.needTransport ? "primary" : "default"}
              />
            </DetailItem>
            
            {details.needTransport && (
              <>
                {/* <DetailItem>
                  <Typography variant="body2" fontWeight={500}>Destination Type:</Typography>
                  <Chip 
                    size="small" 
                    label={details.destinationType || "Hotel"} 
                    color="primary"
                  />
                </DetailItem> */}
                
                {details.destination && (
                  <DetailItem>
                    <Typography variant="body2" fontWeight={500}>Selected Destination:</Typography>
                    <Typography variant="body2">
                      {typeof details.destination === 'string' 
                        ? details.destination 
                        : details.destination.name || details.destination.hotel_name || details.destination.port_name || "Selected Destination"}
                    </Typography>
                  </DetailItem>
                )}
                
                <DetailItem>
                  <Typography variant="body2" fontWeight={500}>Car Type:</Typography>
                  <Chip 
                    size="small" 
                    label={details.carType || "Sharable"} 
                    color={details.carType === "private" ? "primary" : "default"}
                  />
                </DetailItem>
              </>
            )}
            
            {details.remarks && (
              <Box sx={{ mt: 2 }}>
                <Typography variant="body2" fontWeight={500}>Remarks:</Typography>
                <Typography variant="body2" sx={{ fontStyle: 'italic', mt: 1 }}>
                  "{details.remarks}"
                </Typography>
              </Box>
            )}
          </>
        );
        
      case "localTour":
        return (
          <>
            {details.preferredCars && details.preferredCars.length > 0 && (
              <Box sx={{ mt: 1 }}>
                <Typography variant="body2" fontWeight={500} sx={{ mb: 1 }}>Preferred Cars:</Typography>
                <List dense disablePadding>
                  {details.preferredCars.map((car, index) => (
                    <ListItem key={index} dense disableGutters>
                      <ListItemIcon sx={{ minWidth: 30 }}>
                        <TourIcon fontSize="small" color="secondary" />
                      </ListItemIcon>
                      <ListItemText primary={
                        typeof car === 'string' 
                          ? car 
                          : car.name || car.vehicle_name || car.id || 'Vehicle'
                      } />
                    </ListItem>
                  ))}
                </List>
              </Box>
            )}
            
            {details.remarks && (
              <Box sx={{ mt: 2 }}>
                <Typography variant="body2" fontWeight={500}>Remarks:</Typography>
                <Typography variant="body2" sx={{ fontStyle: 'italic', mt: 1 }}>
                  "{details.remarks}"
                </Typography>
              </Box>
            )}
          </>
        );
        
      case "tourGuide":
        return (
          <>
            {details.preferredGuides && details.preferredGuides.length > 0 && (
              <Box sx={{ mt: 1 }}>
                <Typography variant="body2" fontWeight={500} sx={{ mb: 1 }}>Preferred Guides:</Typography>
                <List dense disablePadding>
                  {details.preferredGuides.map((guide, index) => {
                    // Handle the case where guide is an object with a language property
                    // which indicates it's a complex guide object
                    let guideName = "Guide";
                    let guideLanguages = [];
                    let guideLocation = "";
                    let guideExperience = "";
                    
                    if (typeof guide === 'string') {
                      guideName = guide;
                    } else if (guide && typeof guide === 'object') {
                      // Use name if available, otherwise try various fallbacks
                      guideName = guide.name || `Guide ${guide.id || guide.guide_id || index + 1}`;
                      
                      // Extract additional details if available
                      if (guide.languages && Array.isArray(guide.languages)) {
                        guideLanguages = guide.languages.map(lang => 
                          `${lang.language} (${lang.proficiency})`
                        );
                      }
                      
                      // Location information
                      if (guide.city && guide.country) {
                        guideLocation = `${guide.city}, ${guide.country}`;
                      } else if (guide.city) {
                        guideLocation = guide.city;
                      } else if (guide.country) {
                        guideLocation = guide.country;
                      }
                      
                      // Experience information
                      if (guide.experience_years) {
                        guideExperience = `${guide.experience_years} years`;
                      }
                    }
                    
                    return (
                      <ListItem key={index} dense disableGutters sx={{ flexDirection: 'column', alignItems: 'flex-start', mb: 1 }}>
                        <Box sx={{ display: 'flex', width: '100%' }}>
                          <ListItemIcon sx={{ minWidth: 30 }}>
                            <PersonIcon fontSize="small" color="warning" />
                          </ListItemIcon>
                          <ListItemText 
                            primary={guideName} 
                            primaryTypographyProps={{ fontWeight: 500 }}
                          />
                        </Box>
                        
                        {/* Show additional details if available */}
                        {(guideLanguages.length > 0 || guideLocation || guideExperience) && (
                          <Box sx={{ pl: 3.8, mt: 0.5 }}>
                            {guideLanguages.length > 0 && (
                              <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.8rem' }}>
                                Languages: {guideLanguages.join(', ')}
                              </Typography>
                            )}
                            
                            {guideLocation && (
                              <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.8rem' }}>
                                Location: {guideLocation}
                              </Typography>
                            )}
                            
                            {guideExperience && (
                              <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.8rem' }}>
                                Experience: {guideExperience}
                              </Typography>
                            )}
                          </Box>
                        )}
                      </ListItem>
                    );
                  })}
                </List>
              </Box>
            )}
            
            {details.specialRequirements && (
              <Box sx={{ mt: 2 }}>
                <Typography variant="body2" fontWeight={500}>Special Requirements:</Typography>
                <Typography variant="body2" sx={{ fontStyle: 'italic', mt: 1 }}>
                  "{details.specialRequirements}"
                </Typography>
              </Box>
            )}
          </>
        );
        
      case "restaurant":
        return (
          <>
            {details.selectedRestaurants && (
              <Box sx={{ mt: 1 }}>
                <Typography variant="body2" fontWeight={500} sx={{ mb: 1 }}>Selected Restaurants:</Typography>
                <List dense disablePadding>
                  {Array.isArray(details.selectedRestaurants) ? 
                    details.selectedRestaurants.map((restaurant, index) => (
                    <ListItem key={index} dense disableGutters>
                      <ListItemIcon sx={{ minWidth: 30 }}>
                        <RestaurantIcon fontSize="small" sx={{ color: serviceColors.restaurant }} />
                      </ListItemIcon>
                      <ListItemText primary={restaurant.name || restaurant} />
                    </ListItem>
                    )) : 
                    // Handle when it's an object
                    <ListItem dense disableGutters>
                      <ListItemIcon sx={{ minWidth: 30 }}>
                        <RestaurantIcon fontSize="small" sx={{ color: serviceColors.restaurant }} />
                      </ListItemIcon>
                      <ListItemText primary={details.selectedRestaurants.name || details.selectedRestaurants.restaurant_name || "Selected Restaurant"} />
                    </ListItem>
                  }
                </List>
              </Box>
            )}
            
            <DetailItem>
              <Typography variant="body2" fontWeight={500}>Need Transport:</Typography>
              <Chip 
                size="small" 
                label={details.needTransport ? "Yes" : "No"} 
                color={details.needTransport ? "primary" : "default"}
              />
            </DetailItem>
            
            {details.needTransport && (
              <>
                {/* <DetailItem>
                  <Typography variant="body2" fontWeight={500}>Destination Type:</Typography>
                  <Chip 
                    size="small" 
                    label={details.destinationType || "Hotel"} 
                    color="primary"
                  />
                </DetailItem>
                
                {details.destination && (
                  <DetailItem>
                    <Typography variant="body2" fontWeight={500}>Selected Destination:</Typography>
                    <Typography variant="body2">
                      {typeof details.destination === 'string' 
                        ? details.destination 
                        : details.destination.name || details.destination.hotel_name || details.destination.port_name || "Selected Destination"}
                    </Typography>
                  </DetailItem>
                )} */}
                
                <DetailItem>
                  <Typography variant="body2" fontWeight={500}>Car Type:</Typography>
                  <Chip 
                    size="small" 
                    label={details.carType || "Sharable"} 
                    color={details.carType === "private" ? "primary" : "default"}
                  />
                </DetailItem>
              </>
            )}
            
            {details.remarks && (
              <Box sx={{ mt: 2 }}>
                <Typography variant="body2" fontWeight={500}>Remarks:</Typography>
                <Typography variant="body2" sx={{ fontStyle: 'italic', mt: 1 }}>
                  "{details.remarks}"
                </Typography>
              </Box>
            )}
          </>
        );
        
      case "packagedAttractions":
        return (
          <>
            {details.selectedPackagedAttractions && (
              <Box sx={{ mt: 1 }}>
                <Typography variant="body2" fontWeight={500} sx={{ mb: 1 }}>Selected Packages:</Typography>
                <List dense disablePadding>
                  {Array.isArray(details.selectedPackagedAttractions) && details.selectedPackagedAttractions.length > 0 ?
                    details.selectedPackagedAttractions.map((pkg, idx) => (
                      <ListItem key={idx} dense disableGutters alignItems="flex-start">
                        <ListItemIcon sx={{ minWidth: 30 }}>
                          <MapIcon fontSize="small" color="primary" />
                        </ListItemIcon>
                        <ListItemText
                          primary={pkg.name || pkg}
                          secondary={pkg.attractions && pkg.attractions.length > 0 ? (
                            <>
                              <Typography variant="caption" color="text.secondary">Includes:</Typography>
                              <ul style={{ margin: 0, paddingLeft: 16 }}>
                                {pkg.attractions.map((att) => (
                                  <li key={att.attraction_id} style={{ fontSize: 12 }}>{att.name}</li>
                                ))}
                              </ul>
                            </>
                          ) : null}
                        />
                      </ListItem>
                    )) : (
                      <ListItem dense disableGutters>
                        <ListItemIcon sx={{ minWidth: 30 }}>
                          <MapIcon fontSize="small" color="primary" />
                        </ListItemIcon>
                        <ListItemText primary={details.selectedPackagedAttractions?.name || "Selected Package"} />
                      </ListItem>
                    )}
                </List>
              </Box>
            )}
            {details.remarks && (
              <Box sx={{ mt: 2 }}>
                <Typography variant="body2" fontWeight={500}>Remarks:</Typography>
                <Typography variant="body2" sx={{ fontStyle: 'italic', mt: 1 }}>
                  "{details.remarks}"
                </Typography>
              </Box>
            )}
          </>
        );
      default:
        return (
          <Alert severity="info" sx={{ mt: 2 }}>
            No additional details available for this service
          </Alert>
        );
    }
  };

  const handleSubmit = async () => {
    console.log("Creating enquiry:", {
      isMultiEnquiry,
      selectedServices,
      serviceDetails
    });
    
    // Build the payload for create-enquiry API
    const buildEnquiryPayload = () => {
      const payload = {
        approx_price: calculatedPrice || 0,
        // Hotel service
        hotel: selectedServices.includes("hotel"),
        hotel_ids: [],
        hotel_categories: [],
        hotel_compare: "no",
        hotel_remarks: "",
        // Port service
        port: selectedServices.includes("entryExitPort"),
        entry_port: false,
        entry_port_id: null,
        entry_port_address: "",
        entry_dropoff_type: "hotel",
        entry_dropoff_location_id: null,
        exit_port: false,
        exit_port_id: null,
        exit_port_address: "",
        exit_pickup_type: "hotel",
        exit_pickup_location_id: null,
        port_ids: [],
        port_transport_type: "sharable",
        port_remarks: "",
        // Attraction service
        attraction: selectedServices.includes("attraction"),
        attraction_ids: [],
        attraction_transport: false,
        attraction_transport_type: "sharable",
        attraction_remarks: "",
        // Packaged attractions service
        packaged_attractions: selectedServices.includes("packagedAttractions"),
        packaged_attraction_ids: [],
        packaged_attractions_remarks: "",
        // Local transfer service
        local_transfer: selectedServices.includes("localTour"),
        local_transport_vehicle_ids: [],
        local_transfer_remarks: "",
        // Guide service
        guide: selectedServices.includes("tourGuide"),
        guide_ids: [],
        guide_remarks: "",
        // Restaurant service
        restaurant: selectedServices.includes("restaurant"),
        restaurant_ids: [],
        restaurant_transport: false,
        restaurant_transport_type: "sharable",
        restaurant_remarks: "",
      };

      // Populate hotel data
      if (selectedServices.includes("hotel") && serviceDetails.hotel) {
        const hotelDetails = serviceDetails.hotel || serviceDetails["undefined"] || {};
        payload.hotel_compare = hotelDetails.compareHotels || "no";
        payload.hotel_remarks = hotelDetails.remarks || "";
        
        if (hotelDetails.starCategory) {
          payload.hotel_categories = [hotelDetails.starCategory];
        }
        
        if (hotelDetails.preferredHotels && Array.isArray(hotelDetails.preferredHotels)) {
          payload.hotel_ids = hotelDetails.preferredHotels.map(hotel => {
            // Extract hotel_unique_id from hotel object
            if (typeof hotel === 'string') {
              return hotel;
            }
            return hotel.hotel_unique_id || hotel.id || hotel.hotel_id || hotel;
          });
        }
      }

      // Populate entry/exit port data
      if (selectedServices.includes("entryExitPort") && serviceDetails.entryExitPort) {
        const portDetails = serviceDetails.entryExitPort;
        
        // Entry port
        payload.entry_port = portDetails.showEntryPort !== false;
        if (payload.entry_port) {
          if (portDetails.portAddress) {
            payload.entry_port_id = portDetails.portAddress.port_id || portDetails.portAddress.id || null;
            payload.entry_port_address = portDetails.portAddress.port_name || portDetails.portAddress.name || portDetails.portAddress;
          }
          payload.entry_dropoff_type = portDetails.entryDropoffLocationType || "hotel";
          
          if (portDetails.hotelDropOff && payload.entry_dropoff_type === "hotel") {
            payload.entry_dropoff_location_id = portDetails.hotelDropOff.hotel_id || portDetails.hotelDropOff.id || null;
          } else if (portDetails.attractionDropOff && payload.entry_dropoff_type === "attraction") {
            payload.entry_dropoff_location_id = portDetails.attractionDropOff.attraction_id || portDetails.attractionDropOff.id || null;
          } else if (portDetails.restaurantDropOff && payload.entry_dropoff_type === "restaurant") {
            payload.entry_dropoff_location_id = portDetails.restaurantDropOff.restaurant_id || portDetails.restaurantDropOff.id || null;
          }
        }
        
        // Exit port
        payload.exit_port = portDetails.showExitPort === true;
        if (payload.exit_port) {
          if (portDetails.exitPortAddress) {
            payload.exit_port_id = portDetails.exitPortAddress.port_id || portDetails.exitPortAddress.id || null;
            payload.exit_port_address = portDetails.exitPortAddress.port_name || portDetails.exitPortAddress.name || portDetails.exitPortAddress;
          }
          payload.exit_pickup_type = portDetails.exitPickupLocationType || "hotel";
          
          if (portDetails.exitPickupLocation && payload.exit_pickup_type === "hotel") {
            payload.exit_pickup_location_id = portDetails.exitPickupLocation.hotel_id || portDetails.exitPickupLocation.id || null;
          } else if (portDetails.exitAttractionPickup && payload.exit_pickup_type === "attraction") {
            payload.exit_pickup_location_id = portDetails.exitAttractionPickup.attraction_id || portDetails.exitAttractionPickup.id || null;
          } else if (portDetails.exitRestaurantPickup && payload.exit_pickup_type === "restaurant") {
            payload.exit_pickup_location_id = portDetails.exitRestaurantPickup.restaurant_id || portDetails.exitRestaurantPickup.id || null;
          }
        }
        
        payload.port_transport_type = portDetails.carType || "sharable";
        payload.port_remarks = portDetails.remarks || "";
        
        if (portDetails.preferredCars && Array.isArray(portDetails.preferredCars)) {
          payload.port_ids = portDetails.preferredCars.map(car => 
            car.id || car.vehicle_id || car
          );
        }
      }

      // Populate attraction data
      if (selectedServices.includes("attraction") && serviceDetails.attraction) {
        const attractionDetails = serviceDetails.attraction;
        payload.attraction_transport = attractionDetails.needTransport || false;
        payload.attraction_transport_type = attractionDetails.carType || "sharable";
        payload.attraction_remarks = attractionDetails.remarks || "";
        
        if (attractionDetails.selectedAttractions && Array.isArray(attractionDetails.selectedAttractions)) {
          payload.attraction_ids = attractionDetails.selectedAttractions.map(attraction => 
            attraction.id || attraction.attraction_id || attraction
          );
        }
      }

      // Populate packaged attractions data
      if (selectedServices.includes("packagedAttractions") && serviceDetails.packagedAttractions) {
        const packagedDetails = serviceDetails.packagedAttractions;
        payload.packaged_attractions_remarks = packagedDetails.remarks || "";
        
        if (packagedDetails.selectedPackagedAttractions && Array.isArray(packagedDetails.selectedPackagedAttractions)) {
          payload.packaged_attraction_ids = packagedDetails.selectedPackagedAttractions.map(pkg => 
            pkg.id || pkg.package_id || pkg
          );
        }
      }

      // Populate local tour data
      if (selectedServices.includes("localTour") && serviceDetails.localTour) {
        const localTourDetails = serviceDetails.localTour;
        payload.local_transfer_remarks = localTourDetails.remarks || "";
        
        if (localTourDetails.preferredCars && Array.isArray(localTourDetails.preferredCars)) {
          payload.local_transport_vehicle_ids = localTourDetails.preferredCars.map(car => 
            car.id || car.vehicle_id || car
          );
        }
      }

      // Populate tour guide data
      if (selectedServices.includes("tourGuide") && serviceDetails.tourGuide) {
        const guideDetails = serviceDetails.tourGuide;
        payload.guide_remarks = guideDetails.specialRequirements || "";
        
        if (guideDetails.preferredGuides && Array.isArray(guideDetails.preferredGuides)) {
          payload.guide_ids = guideDetails.preferredGuides.map(guide => 
            guide.id || guide.guide_id || guide
          );
        }
      }

      // Populate restaurant data
      if (selectedServices.includes("restaurant") && serviceDetails.restaurant) {
        const restaurantDetails = serviceDetails.restaurant;
        payload.restaurant_transport = restaurantDetails.needTransport || false;
        payload.restaurant_transport_type = restaurantDetails.carType || "sharable";
        payload.restaurant_remarks = restaurantDetails.remarks || "";
        
        if (restaurantDetails.selectedRestaurants && Array.isArray(restaurantDetails.selectedRestaurants)) {
          payload.restaurant_ids = restaurantDetails.selectedRestaurants.map(restaurant => 
            restaurant.id || restaurant.restaurant_id || restaurant
          );
        }
      }

      console.log("Built enquiry payload:", payload);
      return payload;
    };
    
    // Update hotel remarks in serviceDetails before submission if needed
    if (selectedServices.includes("hotel")) {
      // Make sure the remarks from hotelData are explicitly set in serviceDetails.hotel
      const updatedHotelRemarks = hotelData.remarks || 
                               serviceDetails["undefined"]?.remarks || 
                               serviceDetails.hotel?.remarks || 
                               "";
      const updatedHotelCompare = hotelData.compareHotels || 
                               serviceDetails["undefined"]?.compareHotels || 
                               serviceDetails.hotel?.compareHotels || 
                               "no";
      
      console.log("Ensuring hotel remarks are set before submission:", updatedHotelRemarks);
      
      // Update Redux state with merged hotel data
      dispatch(updateServiceDetails({
        service: 'hotel',
        data: { 
          ...serviceDetails.hotel,
          ...hotelData,
          remarks: updatedHotelRemarks
        }
      }));
    }
    
    // Fix attraction data if it's not an array
    if (selectedServices.includes("attraction") && serviceDetails.attraction) {
      const attractionData = serviceDetails.attraction;
      
      // Ensure selectedAttractions is always an array
      if (attractionData.selectedAttractions && !Array.isArray(attractionData.selectedAttractions)) {
        console.log("Converting selectedAttractions to array:", attractionData.selectedAttractions);
        
        dispatch(updateServiceDetails({
          service: 'attraction',
          data: {
            ...attractionData,
            selectedAttractions: [attractionData.selectedAttractions]
          }
        }));
      }
    }
    
    setSubmitting(true);
    setSubmitError(null);
    
    try {
      console.log("Creating enquiry with fetchBookingid (create-enquiry API)...");
      console.log("Service details:", serviceDetails);
      
      // Build the payload for the create-enquiry API
      const enquiryPayload = buildEnquiryPayload();
      console.log("Sending enquiry payload to create-enquiry API:", enquiryPayload);
      
      // Call fetchBookingid which calls the create-enquiry API with our payload
      const bookingIdResult = await dispatch(fetchBookingid(enquiryPayload));
      
      if (fetchBookingid.fulfilled.match(bookingIdResult)) {
        const bookingData = bookingIdResult.payload;
        const id = bookingData?.multi_enq_id;
        const country = bookingData?.country || bookingData?.data?.country || bookingDetails?.searchLocation?.country;
        const city = bookingData?.city || bookingData?.data?.city || bookingDetails?.searchLocation?.city;

        console.log("Enquiry created successfully:", { id, country, city, bookingData });

        if (id) {
          // Update state with API response
          dispatch(updateSearchState({ 
            location: country,
            cityName: city,
            countryName: country
          }));
          
          dispatch(settourdetails(bookingData)); // Set full enquiry details
          dispatch(setId(id)); // Set the ID
          dispatch(setBookingType("enquiry")); // Set booking type to enquiry
          
          // Fetch the enquiry list data for hotels and other services
          if (country && city) {
            const fetchParams = {
              country: country,
              city: city
            };
            
            // Validate fetchParams before making the API call
            if (typeof fetchParams.country === 'string' && typeof fetchParams.city === 'string') {
              console.log("Calling fetchEnquiryList with params:", fetchParams);
              dispatch(fetchEnquiryList(fetchParams));
            } else {
              console.warn("Invalid fetchParams format:", fetchParams);
            }
          } else {
            console.warn("Missing country or city for fetchEnquiryList:", { country, city });
          }
          
          // Mark as successful
        setSubmitSuccess(true);
        
        // Clear Redux service details after successful submission
        dispatch(clearServiceDetails());
          console.log("Cleared service details from Redux after successful enquiry creation");
        
        // Reset booking options
        if (resetBookingOptions && typeof resetBookingOptions === 'function') {
          resetBookingOptions();
        }
        
        // Store successful submission state in localStorage for the thank you page
        localStorage.setItem('enquirySubmitted', 'true');
          localStorage.setItem('enquiryData', JSON.stringify(bookingData));
        
        // Use the onComplete callback if available
        if (onComplete && typeof onComplete === 'function') {
          setTimeout(() => {
            onComplete(); // This will show the modal
          }, 1000); // Show success message for 1 second before showing modal
        } else {
          // Legacy fallback for direct navigation
          setTimeout(() => {
            window.location.href = '/thank-you';
            
            // After showing thank you page, redirect to agent dashboard
            setTimeout(() => {
              window.location.href = '/dashboard/db-dashboard';
            }, 5000); // Redirect after 5 seconds 
          }, 2000);
        }
      } else {
          console.warn("No multi_enq_id found in fetchBookingid response:", bookingData);
          throw new Error("Invalid response: No enquiry ID found");
        }
      } else {
        // Handle error from fetchBookingid
        const errorMessage = bookingIdResult.error?.message || "Failed to create enquiry";
        console.error("Enquiry creation failed:", errorMessage);
        console.error("Error details:", bookingIdResult.error);
        setSubmitError(errorMessage);
      }
    } catch (error) {
      console.error("Error in submit handler:", error);
      console.error("Error stack:", error.stack);
      setSubmitError(error.message || "An unexpected error occurred");
    } finally {
      setSubmitting(false);
    }
  };

  // Handle closing the success snackbar
  const handleCloseSnackbar = () => {
    setSubmitSuccess(false);
  };

  // Update the submitDirectly function to use the new attraction and restaurant specific fields when handling drop-off and pickup locations.


  return (
    <Container maxWidth="lg" sx={{ 
      py: { xs: 1, sm: 1.5, md: 4 },
      px: { xs: 0.5, sm: 1, md: 3 }
    }}>
      <Typography 
        variant="h4" 
        component="h1" 
        align="center" 
        fontWeight={600} 
        gutterBottom
        sx={{
          fontSize: { xs: '1.5rem', sm: '2rem', md: '2.125rem' },
          color: { xs: '#ff6b6b', sm: '#ff6b6b', md: 'text.primary' },
          background: { xs: 'linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%)', sm: 'linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%)', md: 'none' },
          WebkitBackgroundClip: { xs: 'text', sm: 'text', md: 'unset' },
          WebkitTextFillColor: { xs: 'transparent', sm: 'transparent', md: 'unset' },
          backgroundClip: { xs: 'text', sm: 'text', md: 'unset' },
          textShadow: { xs: '0 2px 4px rgba(255, 107, 107, 0.3)', sm: '0 2px 4px rgba(255, 107, 107, 0.3)', md: 'none' }
        }}
      >
        Review Your Booking
      </Typography>
      <Typography 
        variant="body1" 
        align="center" 
        color="text.secondary" 
        sx={{ 
          mb: { xs: 1, sm: 1.5, md: 4 },
          fontSize: { xs: '0.875rem', sm: '1rem', md: '1rem' },
          color: { xs: '#666', sm: '#666', md: 'text.secondary' }
        }}
      >
        Please review all your selected services and confirm your booking details
      </Typography>
      
     
      
      {/* Trip details section */}
      <SectionPaper>
        <SectionHeader>
          <SectionIcon>
            <CalendarIcon />
          </SectionIcon>
          <Typography variant="h6" component="h2" fontWeight={600}>
            Trip Details
          </Typography>
        </SectionHeader>
        
        <Divider sx={{ mb: 3 }} />
        
        <Grid container spacing={{ xs: 1, sm: 1.5, md: 3 }}>
          <Grid item xs={12} md={6}>
              <Box display="flex" alignItems="center" sx={{ mb: 1, width: '100%' }}>
              </Box>
              <Grid container spacing={{ xs: 0.5, sm: 1, md: 2 }}>
                <Grid item xs={12} sm={6}>
                  <TextField
                    fullWidth
                    label="Country"
                    value={countryValue}
                    readOnly
                    size="small"
                    variant="outlined"
                    InputProps={{
                      sx: { 
                        borderRadius: 2,
                        fontSize: { xs: '0.875rem', sm: '1rem' }
                      }
                    }}
                  />
                </Grid>
                <Grid item xs={12} sm={6}>
                  <TextField
                    fullWidth
                    label="City"
                    value={cityValue}
                    readOnly
                    size="small"
                    variant="outlined"
                    InputProps={{
                      sx: { 
                        borderRadius: 2,
                        fontSize: { xs: '0.875rem', sm: '1rem' }
                      }
                    }}
                  />
                </Grid>
              </Grid>
          </Grid>
          
          <Grid item xs={12} sm={6} md={3}>
            <DetailItem>
              <Box display="flex" alignItems="center">
                <CalendarIcon sx={{ mr: 1, color: 'primary.main' }} />
                <Typography variant="body2" fontWeight={500}>Check-in</Typography>
              </Box>
              <Typography variant="body2">
                {formatDate(bookingDetails.checkIn)}
              </Typography>
            </DetailItem>
          </Grid>
          
          <Grid item xs={12} sm={6} md={3}>
            <DetailItem>
              <Box display="flex" alignItems="center">
                <CalendarIcon sx={{ mr: 1, color: 'primary.main' }} />
                <Typography variant="body2" fontWeight={500}>Check-out</Typography>
              </Box>
              <Typography variant="body2">
                {formatDate(bookingDetails.checkOut)}
              </Typography>
            </DetailItem>
          </Grid>
          
          <Grid item xs={12} sm={6} md={3}>
            <DetailItem>
              <Box display="flex" alignItems="center">
                <PeopleIcon sx={{ mr: 1, color: 'primary.main' }} />
                <Typography variant="body2" fontWeight={500}>Guests</Typography>
              </Box>
              <Typography variant="body2">
                {bookingDetails.guests
                  ? `${bookingDetails.guests.adults || 0} Adults, ${
                      bookingDetails.guests.children || 0
                    } Children`
                  : "Not specified"}
              </Typography>
            </DetailItem>
          </Grid>
        </Grid>
      </SectionPaper>
            
      {/* Selected services section */}
      <SectionPaper>
        <SectionHeader>
          <SectionIcon bgcolor="#2e7d32">
            <CheckCircleIcon />
          </SectionIcon>
          <Typography variant="h6" component="h2" fontWeight={600}>
            Selected Services
          </Typography>
        </SectionHeader>
        
        <Divider sx={{ mb: 3 }} />
        
        {selectedServices && selectedServices.length > 0 ? (
          <Grid container spacing={3}>
            {selectedServices.map((service) => (
              <Grid item xs={12} md={6} key={service}>
                <ServiceCard servicecolor={serviceColors[service]}>
                  <CardContent>
                    <Box display="flex" alignItems="center" mb={2}>
                      <Avatar sx={{ bgcolor: serviceColors[service], mr: 2 }}>
                        {getServiceIcon(service)}
                      </Avatar>
                      <Typography variant="h6" fontWeight={600}>
                        {formatServiceName(service)}
                      </Typography>
                    </Box>
                    
                    <Divider sx={{ mb: 2 }} />
                    
                    <Accordion 
                      elevation={0} 
                      sx={{ 
                        '&:before': { display: 'none' },
                        bgcolor: 'transparent'
                      }}
                      defaultExpanded={true}
                    >
                      <AccordionSummary 
                        expandIcon={<ExpandMoreIcon />}
                        sx={{ p: 0, minHeight: 'auto' }}
                      >
                        <Typography color="primary" fontWeight={500}>
                          Service Details
                        </Typography>
                      </AccordionSummary>
                      <AccordionDetails sx={{ pt: 2, pb: 0 }}>
                        {renderServiceDetails(service)}
                      </AccordionDetails>
                    </Accordion>
                  </CardContent>
                </ServiceCard>
              </Grid>
            ))}
          </Grid>
        ) : (
          <Box py={3} textAlign="center" bgcolor="background.paper" borderRadius={1}>
            <Alert severity="warning" icon={<ErrorIcon />}>
              No services selected. Please go back and select at least one service.
            </Alert>
          </Box>
        )}
      </SectionPaper>
      
      {/* Trust badges section */}
      <Grid container spacing={2} sx={{ mb: 5 }}>
        <Grid item xs={12} md={6}>
          <Paper 
            sx={{ 
              p: 2, 
              display: 'flex', 
              alignItems: 'center',
              backgroundColor: 'rgba(25, 118, 210, 0.08)',
              transition: 'all 0.3s ease',
              '&:hover': {
                transform: 'translateY(-3px)'
              }
            }}
          >
            <Avatar sx={{ bgcolor: 'primary.main', mr: 2 }}>
              <SecurityIcon />
            </Avatar>
            <Box>
              <Typography variant="subtitle1" fontWeight={600}>Secure Booking</Typography>
              <Typography variant="body2" color="text.secondary">Your personal data is protected</Typography>
            </Box>
          </Paper>
        </Grid>
        
        <Grid item xs={12} md={6}>
          <Paper 
            sx={{ 
              p: 2, 
              display: 'flex', 
              alignItems: 'center',
              backgroundColor: 'rgba(245, 124, 0, 0.08)',
              transition: 'all 0.3s ease',
              '&:hover': {
                transform: 'translateY(-3px)'
              }
            }}
          >
            <Avatar sx={{ bgcolor: 'warning.main', mr: 2 }}>
              <SupportIcon />
            </Avatar>
            <Box>
              <Typography variant="subtitle1" fontWeight={600}>24/7 Support</Typography>
              <Typography variant="body2" color="text.secondary">Contact us anytime during your trip</Typography>
            </Box>
          </Paper>
        </Grid>
      </Grid>
      
      {/* Pricing Summary Section */}
      {calculatedPrice > 0 && (
        <SectionPaper>
          <SectionHeader>
            <SectionIcon bgcolor="#4caf50">
              <AttachMoneyIcon />
            </SectionIcon>
            <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
              Package Pricing Summary
            </Typography>
          </SectionHeader>
          
          <Box 
            sx={{ 
              background: 'linear-gradient(135deg,rgb(76, 119, 175) 0%,rgb(69, 86, 160) 100%)',
              color: 'white',
              p: 3,
              borderRadius: 2,
              textAlign: 'center'
            }}
          >
            <Typography variant="h5" sx={{ fontWeight: 'bold', mb: 1 }}>
              SGD {calculatedPrice.toLocaleString()}/adult
            </Typography>
            <Typography variant="body1" sx={{ opacity: 1, color: 'white' }}>
              Approximate Total Package Cost
            </Typography>
            <Typography variant="caption" sx={{ opacity: 0.8, display: 'block', mt: 1 }}>
              *Final price may vary based on actual selections and seasonal rates
            </Typography>
          </Box>

          <Box sx={{ mt: 2 }}>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
              <strong>Price includes:</strong>
            </Typography>
            <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1 }}>
              {selectedServices.map((service) => (
                <Chip
                  key={service}
                  label={formatServiceName(service)}
                  icon={getServiceIcon(service)}
                  size="small"
                  color="primary"
                  variant="outlined"
                />
              ))}
            </Box>
          </Box>

          {bookingDetails?.guestCounts && (
            <Box sx={{ mt: 2, p: 2, backgroundColor: '#f5f5f5', borderRadius: 1 }}>
              <Typography variant="body2" color="text.secondary">
                <strong>Based on:</strong> {' '}
                {(bookingDetails.guestCounts.Adults || 1)} Adult(s)
                {bookingDetails.guestCounts.Children > 0 && `, ${bookingDetails.guestCounts.Children} Child(ren)`}
                {bookingDetails.guestCounts.Infants > 0 && `, ${bookingDetails.guestCounts.Infants} Infant(s)`}
                {bookingDetails.checkinDate && bookingDetails.checkoutDate && (
                  <span>
                    {' • '} 
                    {Math.max(1, Math.ceil((new Date(bookingDetails.checkoutDate) - new Date(bookingDetails.checkinDate)) / (24 * 60 * 60 * 1000)))} day(s)
                  </span>
                )}
              </Typography>
            </Box>
          )}
        </SectionPaper>
      )}
      
      {/* Action buttons */}
      <Box sx={{ 
        display: 'flex', 
        justifyContent: 'space-between', 
        mt: { xs: 2, sm: 2.5, md: 4 },
        flexDirection: { xs: 'column', sm: 'row' },
        gap: { xs: 1.5, sm: 0 }
      }}>
        <ActionButton 
          variant="outlined" 
          color="primary" 
          startIcon={<ArrowBackIcon />}
          onClick={onBack}
          disabled={submitting}
          sx={{ width: { xs: '100%', sm: 'auto' } }}
        >
          Back to Services
        </ActionButton>
        
        <ActionButton 
          variant="contained" 
          color="primary" 
          endIcon={submitting ? null : <SendIcon />}
          onClick={handleSubmit}
          disabled={submitting || enquiryStatus === "submitted"}
          sx={{ width: { xs: '100%', sm: 'auto' } }}
        >
          {submitting ? (
            <>
              <CircularProgress size={20} sx={{ mr: 1, color: 'white' }} /> 
              Submitting...
            </>
          ) : enquiryStatus === "submitted" ? (
            <>
              <CheckCircleIcon sx={{ mr: 1 }} />
              Booking Enquiry 
            </>
          ) : (
            " Booking Enquiry"
          )}
        </ActionButton>
      </Box>
      
      {/* Loading backdrop */}
      <Backdrop
        sx={{ color: '#fff', zIndex: (theme) => theme.zIndex.drawer + 1 }}
        open={submitting}
      >
        <Box sx={{ textAlign: 'center' }}>
          <CircularProgress color="inherit" />
          <Typography variant="h6" sx={{ mt: 2, color: 'white' }}>
            Submitting your booking...
          </Typography>
        </Box>
      </Backdrop>
      
      {/* Success message */}
      <Snackbar
        open={submitSuccess}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
      >
        <Alert 
          onClose={handleCloseSnackbar} 
          severity="success" 
          sx={{ width: '100%', boxShadow: "0 4px 20px rgba(0, 0, 0, 0.15)" }}
        >
          Your booking has been successfully submitted!
        </Alert>
      </Snackbar>
    </Container>
  );
};

export default ConfirmDetails;
