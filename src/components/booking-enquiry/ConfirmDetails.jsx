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
  Tour as TourIcon
} from "@mui/icons-material";
import { 
  updateServiceDetails, 
  updateCalculatedPrice, 
  clearServiceDetails, 
  clearSpecificService,
  setCustomerName,
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
import DMCSelectionComponent from "./DMCSelectionComponent";
import TripDetailsComponent from "./TripDetailsComponent";
import PricingSummaryComponent from "./PricingSummaryComponent";


// Styled components
const SectionPaper = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(2.5),
  marginBottom: theme.spacing(2),
  borderRadius: 13,
  boxShadow: "none",
  transition: "none",
  overflow: "hidden",
  background: "#fff",
  border: "1px solid #e4eaf3",
  position: "relative",
  "&:hover": {
    boxShadow: "none",
    transform: "none",
  },
  [theme.breakpoints.down("md")]: {
    padding: theme.spacing(2),
    marginBottom: theme.spacing(1.5),
  },
  [theme.breakpoints.down("sm")]: {
    padding: theme.spacing(1.5),
    marginBottom: theme.spacing(1.25),
  },
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
  padding: theme.spacing(1.25, 1.5),
  borderRadius: 10,
  marginBottom: theme.spacing(1),
  backgroundColor: "#f7f9fc",
  border: "1px solid #e4eaf3",
  transition: "none",
  position: "relative",
  overflow: "hidden",
  "&:hover": {
    backgroundColor: "#eef3ff",
    transform: "none",
    boxShadow: "none",
    borderColor: "#c9d7ff",
  },
  [theme.breakpoints.down("sm")]: {
    padding: theme.spacing(1),
    marginBottom: theme.spacing(0.5),
    flexDirection: "column",
    alignItems: "flex-start",
    gap: theme.spacing(0.5),
  },
}));

const ServiceCard = styled(Card)(({ theme, servicecolor }) => ({
  marginBottom: theme.spacing(1.5),
  transition: "none",
  borderLeft: `3px solid ${servicecolor || "#3554D1"}`,
  borderRadius: 12,
  background: "#fff",
  border: "1px solid #e4eaf3",
  boxShadow: "none",
  position: "relative",
  "&:hover": {
    boxShadow: "none",
    transform: "none",
  },
  [theme.breakpoints.down("sm")]: {
    marginBottom: theme.spacing(1),
  },
}));

const ActionButton = styled(Button)(({ theme }) => ({
  padding: theme.spacing(1.25, 2.5),
  borderRadius: 10,
  boxShadow: "none",
  textTransform: "none",
  fontWeight: 600,
  transition: "filter 0.15s ease",
  "&:hover": {
    transform: "none",
    boxShadow: "none",
    filter: "brightness(0.97)",
  },
  [theme.breakpoints.down("sm")]: {
    padding: theme.spacing(1, 1.5),
    fontSize: "0.875rem",
  },
}));

// Service colors — partner palette
const serviceColors = {
  hotel: "#3554D1",
  entryExitPort: "#0d9488",
  attraction: "#c2410c",
  localTour: "#1d4ed8",
  tourGuide: "#b45309",
  restaurant: "#0369a1",
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
    default: return <CheckCircleIcon />;
  }
};

const ConfirmDetails = ({ bookingOptions, onBack, onComplete, resetBookingOptions }) => {
  // Scroll to top when component mounts
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  // Handle scroll for floating price summary
  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 400) {
        setShowFloatingPrice(true);
      } else {
        setShowFloatingPrice(false);
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  const dispatch = useDispatch();
  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState(null);
  const [submitSuccess, setSubmitSuccess] = useState(false);
  const [localEnquiryId, setLocalEnquiryId] = useState(null);
  const [servicePrices, setServicePrices] = useState({});
  const [expandedService, setExpandedService] = useState(null);
  const [showFloatingPrice, setShowFloatingPrice] = useState(false);
  
  const bookingDetails = useSelector((state) => state.enquiry);
  const customerName = bookingDetails.customer_name || "";
  const serviceDetails = useSelector((state) => state.enquiry.serviceDetails || {});
  const selectedServices = useSelector((state) => state.enquiry.selectedServices || Object.keys(bookingOptions || {}).filter(key => bookingOptions[key]));
  
  // Get selected DMC IDs to watch for changes
  const selectedDmcIds = useSelector((state) => state.dmc.selectedDmcIds || []);
  
  // Ref to track previous DMC IDs
  const prevDmcIdsRef = React.useRef(selectedDmcIds);
  
  // Get total price from Redux (already calculated in BookingEnquiries)
  const totalPrice = bookingDetails.calculatedPrice || 0;
  
  // // Debug: Log price on component mount and when it changes
  // useEffect(() => {
  //   console.log("💰 ConfirmDetails - Total Price from Redux:", totalPrice);
  //   console.log("💰 Full bookingDetails.calculatedPrice:", bookingDetails.calculatedPrice);
  //   console.log("💰 Selected Services:", selectedServices);
  // }, [totalPrice, bookingDetails.calculatedPrice, selectedServices]);
  
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
    
    // If serviceDetails is empty or all services are cleared, reset servicePrices
    const hasAnyServiceData = serviceDetails && Object.keys(serviceDetails).some(key => {
      const data = serviceDetails[key];
      return data && Object.keys(data).length > 0;
    });
    
    if (!hasAnyServiceData) {
      console.log("⚠️ No service details found - clearing service prices");
      setServicePrices({});
    }
  }, [serviceDetails, selectedServices]);

  // Detect DMC changes and go back to service selection
  useEffect(() => {
    const dmcsChanged = JSON.stringify(prevDmcIdsRef.current) !== JSON.stringify(selectedDmcIds);
    
    if (dmcsChanged && prevDmcIdsRef.current.length > 0) {
      console.log("🔄 DMCs changed in ConfirmDetails - navigating back to service selection");
      console.log("Previous DMCs:", prevDmcIdsRef.current);
      console.log("Current DMCs:", selectedDmcIds);
      
      // Clear local state
      setServicePrices({});
      setExpandedService(null);
      
      // Clear Redux calculated price
      dispatch(updateCalculatedPrice(0));
      
      // Navigate back to BookingEnquiries page
      if (onBack && typeof onBack === 'function') {
        onBack();
      }
    }
    
    // Update the ref for next comparison
    prevDmcIdsRef.current = selectedDmcIds;
  }, [selectedDmcIds, onBack, dispatch]);

  // Calculate price on component mount
  useEffect(() => {
    console.log("ConfirmDetails - Component mounted, checking if price calculation needed...");
    console.log("Selected Services:", selectedServices);
    console.log("Current total price from Redux:", totalPrice);
    
    if (selectedServices && selectedServices.length > 0) {
      console.log("Calculating price in ConfirmDetails...");
      
      // Recalculate price
      let calculatedTotal = 0;
      const individualPrices = {};
      const guestCounts = bookingDetails?.guestCounts || bookingDetails?.guests || {};
      const adults = parseInt(guestCounts.Adults || guestCounts.adults || 1) || 1;
      const children = parseInt(guestCounts.Children || guestCounts.children || 0) || 0;
      const infants = parseInt(guestCounts.Infants || guestCounts.infant || 0) || 0;
      const totalPersons = adults + children + infants;
      
      console.log("Guest counts:", { adults, children, infants, totalPersons });
      
      const checkinDate = bookingDetails?.checkIn;
      const checkoutDate = bookingDetails?.checkOut;
      
      console.log("Date data:", { checkinDate, checkoutDate });
      
      let totalDays = 1;
      if (checkinDate && checkoutDate) {
        // Parse DD/MM/YYYY format correctly
        const parseDate = (dateStr) => {
          if (!dateStr) return null;
          const parts = dateStr.split('/');
          if (parts.length === 3) {
            // Convert DD/MM/YYYY to YYYY-MM-DD for proper parsing
            return new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
          }
          return new Date(dateStr);
        };
        
        const checkIn = parseDate(checkinDate);
        const checkOut = parseDate(checkoutDate);
        
        if (checkIn && checkOut && !isNaN(checkIn) && !isNaN(checkOut)) {
          const daysDiff = Math.ceil((checkOut - checkIn) / (24 * 60 * 60 * 1000));
          totalDays = Math.max(1, daysDiff);
          console.log("✅ Parsed dates correctly:", {
            checkIn: checkIn.toDateString(),
            checkOut: checkOut.toDateString(),
            daysDiff: totalDays
          });
        } else {
          console.warn("⚠️ Failed to parse dates, using default 1 day");
        }
      }
      
      // Safety check for NaN
      if (isNaN(totalDays)) {
        console.warn("⚠️ totalDays is NaN, defaulting to 1");
        totalDays = 1;
      }
      
      console.log("Trip duration:", totalDays, "days");

      // Calculate based on selected services
      if (selectedServices.includes("hotel") && serviceDetails.hotel) {
        const hotels = serviceDetails.hotel.preferredHotels || [];
        console.log("Hotel calculation - hotels:", hotels.length);
        let hotelTotal = 0;
        hotels.forEach(hotel => {
          const pricePerDay = parseFloat(hotel.single_base_price) || 0;
          if (pricePerDay > 0) {
            const hotelPrice = pricePerDay * totalDays;
            hotelTotal += hotelPrice;
            calculatedTotal += hotelPrice;
            console.log("  Hotel price added:", hotelPrice, "Total now:", calculatedTotal);
          }
        });
        if (hotelTotal > 0) {
          individualPrices.hotel = hotelTotal;
        }
      }

      if (selectedServices.includes("entryExitPort") && serviceDetails.entryExitPort) {
        let transferCount = 0;
        if (serviceDetails.entryExitPort.showEntryPort !== false) transferCount++;
        if (serviceDetails.entryExitPort.showExitPort === true) transferCount++;
        const cars = serviceDetails.entryExitPort.preferredCars || [];
        console.log("Port calculation - transfers:", transferCount, "cars:", cars.length);
        let portTotal = 0;
        if (cars.length > 0) {
          cars.forEach(car => {
            const pricePerTransfer = parseFloat(car.base_price) || 0;
            if (pricePerTransfer > 0 && transferCount > 0) {
              const carPrice = pricePerTransfer * transferCount;
              portTotal += carPrice;
              calculatedTotal += carPrice;
              console.log("  Car price added:", carPrice, "Total now:", calculatedTotal);
            }
          });
        }
        // Don't add default price if no cars selected
        if (portTotal > 0) {
          individualPrices.entryExitPort = portTotal;
        }
      }

      if (selectedServices.includes("attraction") && serviceDetails.attraction) {
        const attractions = serviceDetails.attraction.selectedAttractions || [];
        console.log("Attraction calculation - attractions:", attractions.length);
        let attractionTotal = 0;
        const personsForAttraction = adults + children; // Exclude infants from attraction pricing
        attractions.forEach(attraction => {
          const pricePerPerson = parseFloat(attraction.base_price) || 0;
          if (pricePerPerson > 0) {
            const attractionPrice = pricePerPerson * personsForAttraction;
            attractionTotal += attractionPrice;
            calculatedTotal += attractionPrice;
            console.log("  Attraction price added:", attractionPrice, `for ${personsForAttraction} persons (${adults} adults + ${children} children, infants excluded)`, "Total now:", calculatedTotal);
          }
        });
        if (attractionTotal > 0) {
          individualPrices.attraction = attractionTotal;
        }
      }

      if (selectedServices.includes("localTour") && serviceDetails.localTour) {
        const cars = serviceDetails.localTour.preferredCars || [];
        console.log("Local tour calculation - cars:", cars.length, cars);
        let localTourTotal = 0;
        if (cars.length > 0) {
          cars.forEach(car => {
            const carPrice = parseFloat(car.base_price) || 0;
            if (carPrice > 0) {
              localTourTotal += carPrice;
              calculatedTotal += carPrice;
              console.log("  Local tour car price added (flat rate):", carPrice, "Total now:", calculatedTotal);
            }
          });
        }
        // Only add to individualPrices if there's actually a price
        if (localTourTotal > 0) {
          individualPrices.localTour = localTourTotal;
        }
      }

      if (selectedServices.includes("tourGuide") && serviceDetails.tourGuide) {
        const guides = serviceDetails.tourGuide.preferredGuides || [];
        console.log("Tour guide calculation - guides:", guides.length, guides);
        let guideTotal = 0;
        guides.forEach(guide => {
          const guidePrice = parseFloat(guide.base_price) || 0;
          if (guidePrice > 0) {
            guideTotal += guidePrice;
            calculatedTotal += guidePrice;
            console.log("  Guide price added (flat rate):", guidePrice, "Total now:", calculatedTotal);
          }
        });
        // Only add to individualPrices if there's actually a price
        if (guideTotal > 0) {
          individualPrices.tourGuide = guideTotal;
        }
      }

      if (selectedServices.includes("restaurant") && serviceDetails.restaurant) {
        const restaurantData = serviceDetails.restaurant.selectedRestaurants || [];
        console.log("Restaurant calculation - restaurantData:", restaurantData.length, restaurantData);
        let restaurantTotal = 0;
        const personsForRestaurant = adults + children; // Exclude infants from restaurant pricing
        
        // Check if new format (with dates and meals)
        if (restaurantData.length > 0 && restaurantData[0]?.date && restaurantData[0]?.restaurants) {
          // New format: iterate through dates and meals
          restaurantData.forEach(dateEntry => {
            dateEntry.restaurants.forEach(entry => {
              const meal = entry.meal;
              let mealPrice = 0;
              
              if (meal) {
                // Calculate based on meal type (exclude infants)
                if (meal.set_menu_price) {
                  mealPrice = parseFloat(meal.set_menu_price) * personsForRestaurant; // Exclude infants
                } else {
                  // Calculate for adults and children separately (infants excluded)
                  const adultPrice = parseFloat(meal.adult_price) || 0;
                  const childPrice = parseFloat(meal.child_price) || 0;
                  mealPrice = (adultPrice * adults) + (childPrice * children); // Infants excluded
                }
              } else {
                // Fallback to base price (exclude infants)
                const restaurant = entry.restaurant;
                const basePrice = parseFloat(restaurant['base-price']) || 0;
                if (basePrice > 0) {
                  mealPrice = basePrice * personsForRestaurant; // Exclude infants
                }
              }
              
              restaurantTotal += mealPrice;
              calculatedTotal += mealPrice;
              console.log("  Meal price added:", mealPrice, `for ${personsForRestaurant} persons (${adults} adults + ${children} children, infants excluded)`, "Total now:", calculatedTotal);
            });
          });
        } else {
          // Old format: flat array of restaurants (exclude infants)
          restaurantData.forEach(restaurant => {
            const pricePerPerson = parseFloat(restaurant['base-price']) || 0;
            if (pricePerPerson > 0) {
              const restaurantPrice = pricePerPerson * personsForRestaurant; // Exclude infants
              restaurantTotal += restaurantPrice;
              calculatedTotal += restaurantPrice;
              console.log("  Restaurant price added:", restaurantPrice, `for ${personsForRestaurant} persons (${adults} adults + ${children} children, infants excluded)`, "Total now:", calculatedTotal);
            }
          });
        }
        if (restaurantTotal > 0) {
          individualPrices.restaurant = restaurantTotal;
        }
      }
      
      console.log("Final calculatedTotal before rounding:", calculatedTotal);
      
      // Safety check for NaN
      if (isNaN(calculatedTotal)) {
        console.error("❌ calculatedTotal is NaN! Setting to 0");
        calculatedTotal = 0;
      }

      // calculatedTotal is already the total
      // Hotels: price × days
      // Guides & Local Tours: flat rate (not multiplied)
      // Attractions & Restaurants: price × persons
      // Transfers: price × transfers
      const roundedTotalPrice = Math.round(calculatedTotal);
      
      // Final safety check
      const safeTotalPrice = isNaN(roundedTotalPrice) ? 0 : roundedTotalPrice;
      
      console.log("✅ Calculated price in ConfirmDetails:", {
        totalForAllServices: roundedTotalPrice,
        totalGuests: totalPersons,
        totalPrice: safeTotalPrice,
        individualPrices
      });
      
      dispatch(updateCalculatedPrice(safeTotalPrice));
      setServicePrices(individualPrices);
    } else {
      console.log("⚠️ No selected services, skipping price calculation");
      // Clear prices when no services are selected
      setServicePrices({});
      dispatch(updateCalculatedPrice(0));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedServices, serviceDetails]); // Recalculate when selectedServices or serviceDetails change

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

  // Helper function to render price breakdown for each service with individual item prices
  const renderPriceBreakdown = (service) => {
    const price = servicePrices[service];
    if (!price) return null;

    const guestCounts = bookingDetails?.guestCounts || bookingDetails?.guests || {};
    const adults = parseInt(guestCounts.Adults || guestCounts.adults || 1) || 1;
    const children = parseInt(guestCounts.Children || guestCounts.children || 0) || 0;
    const infants = parseInt(guestCounts.Infants || guestCounts.infant || 0) || 0;
    const personsForAttraction = adults + children; // Exclude infants for attractions
    const personsForRestaurant = adults + children; // Exclude infants for restaurants
    
    const checkinDate = bookingDetails?.checkIn;
    const checkoutDate = bookingDetails?.checkOut;
    let totalDays = 1;
    if (checkinDate && checkoutDate) {
      // Parse DD/MM/YYYY format correctly
      const parseDate = (dateStr) => {
        if (!dateStr) return null;
        const parts = dateStr.split('/');
        if (parts.length === 3) {
          // Convert DD/MM/YYYY to YYYY-MM-DD for proper parsing
          return new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
        }
        return new Date(dateStr);
      };
      
      const checkIn = parseDate(checkinDate);
      const checkOut = parseDate(checkoutDate);
      
      if (checkIn && checkOut && !isNaN(checkIn) && !isNaN(checkOut)) {
        const daysDiff = Math.ceil((checkOut - checkIn) / (24 * 60 * 60 * 1000));
        totalDays = Math.max(1, daysDiff);
      }
    }

    let breakdown = [];
    let showTotal = false;

    switch(service) {
      case "hotel":
        const hotels = serviceDetails.hotel?.preferredHotels || [];
        hotels.forEach((hotel, index) => {
          const hotelName = typeof hotel === 'object' ? hotel.name || `Hotel ${index + 1}` : hotel;
          const pricePerDay = parseFloat(hotel.single_base_price) || 0;
          const hotelTotal = pricePerDay * totalDays;
          breakdown.push({
            label: `${hotelName}: SGD ${pricePerDay.toLocaleString()} × ${totalDays} day${totalDays > 1 ? 's' : ''}`,
            value: `SGD ${hotelTotal.toLocaleString()}`
          });
        });
        showTotal = hotels.length > 1;
        break;
        
      case "entryExitPort":
        const cars = serviceDetails.entryExitPort?.preferredCars || [];
        let transferCount = 0;
        if (serviceDetails.entryExitPort?.showEntryPort !== false) transferCount++;
        if (serviceDetails.entryExitPort?.showExitPort === true) transferCount++;
        
        cars.forEach((car, index) => {
          const carName = typeof car === 'string' ? car : (car.name || car.vehicle_name || `Vehicle ${index + 1}`);
          const pricePerTransfer = parseFloat(car.base_price) || 0;
          const carTotal = pricePerTransfer * transferCount;
          breakdown.push({
            label: `${carName}: SGD ${pricePerTransfer.toLocaleString()} × ${transferCount} transfer${transferCount > 1 ? 's' : ''}`,
            value: `SGD ${carTotal.toLocaleString()}`
          });
        });
        showTotal = cars.length > 1;
        break;
        
      case "attraction":
        const attractions = serviceDetails.attraction?.selectedAttractions || [];
        attractions.forEach((attraction, index) => {
          const attractionName = attraction.name || `Attraction ${index + 1}`;
          const pricePerPerson = parseFloat(attraction.base_price) || 0;
          const attractionTotal = pricePerPerson * personsForAttraction;
          breakdown.push({
            label: `${attractionName}: SGD ${pricePerPerson.toLocaleString()} × ${personsForAttraction} guest${personsForAttraction > 1 ? 's' : ''}`,
            value: `SGD ${attractionTotal.toLocaleString()}`,
            subtitle: '(infants excluded)'
          });
        });
        showTotal = attractions.length > 1;
        break;
        
      case "localTour":
        const localTourCars = serviceDetails.localTour?.preferredCars || [];
        localTourCars.forEach((car, index) => {
          const carName = typeof car === 'string' ? car : (car.name || car.vehicle_name || `Vehicle ${index + 1}`);
          const carPrice = parseFloat(car.base_price) || 0;
          breakdown.push({
            label: `${carName} (Flat Rate)`,
            value: `SGD ${carPrice.toLocaleString()}`
          });
        });
        showTotal = localTourCars.length > 1;
        break;
        
      case "tourGuide":
        const guides = serviceDetails.tourGuide?.preferredGuides || [];
        guides.forEach((guide, index) => {
          const guideName = typeof guide === 'string' ? guide : (guide.name || `Guide ${index + 1}`);
          const guidePrice = parseFloat(guide.base_price) || 0;
          breakdown.push({
            label: `${guideName} (Flat Rate)`,
            value: `SGD ${guidePrice.toLocaleString()}`
          });
        });
        showTotal = guides.length > 1;
        break;
        
      case "restaurant":
        const restaurantData = serviceDetails.restaurant?.selectedRestaurants || [];
        
        // Check if new format (with dates and meals)
        if (restaurantData.length > 0 && restaurantData[0]?.date && restaurantData[0]?.restaurants) {
          // New format: Group by date and show individual meals
          restaurantData.forEach(dateEntry => {
            const dateObj = new Date(dateEntry.date);
            const dateStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            
            dateEntry.restaurants.forEach(entry => {
              const restaurantName = entry.restaurant?.name || 'Restaurant';
              const meal = entry.meal;
              const mealPeriod = entry.mealPeriod || meal?.meal_period || '';
              const mealType = meal?.meal_type || '';
              
              let mealPrice = 0;
              let priceLabel = '';
              
              if (meal) {
                if (meal.set_menu_price) {
                  const setMenuPrice = parseFloat(meal.set_menu_price) || 0;
                  mealPrice = setMenuPrice * personsForRestaurant;
                  priceLabel = `SGD ${setMenuPrice.toLocaleString()} × ${personsForRestaurant} guest${personsForRestaurant > 1 ? 's' : ''}`;
                } else {
                  const adultPrice = parseFloat(meal.adult_price) || 0;
                  const childPrice = parseFloat(meal.child_price) || 0;
                  mealPrice = (adultPrice * adults) + (childPrice * children);
                  priceLabel = `SGD ${adultPrice.toLocaleString()}/adult × ${adults} + SGD ${childPrice.toLocaleString()}/child × ${children}`;
                }
              }
              
              breakdown.push({
                label: `${dateStr} - ${restaurantName} (${mealPeriod}${mealType ? ' - ' + mealType : ''})`,
                sublabel: priceLabel,
                value: `SGD ${mealPrice.toLocaleString()}`,
                subtitle: '(infants excluded)'
              });
            });
          });
          showTotal = breakdown.length > 1;
        } else {
          // Old format: flat array
          restaurantData.forEach((restaurant, index) => {
            const restaurantName = restaurant.name || `Restaurant ${index + 1}`;
            const pricePerPerson = parseFloat(restaurant['base-price']) || 0;
            const restaurantTotal = pricePerPerson * personsForRestaurant;
            breakdown.push({
              label: `${restaurantName}: SGD ${pricePerPerson.toLocaleString()} × ${personsForRestaurant} guest${personsForRestaurant > 1 ? 's' : ''}`,
              value: `SGD ${restaurantTotal.toLocaleString()}`,
              subtitle: '(infants excluded)'
            });
          });
          showTotal = restaurantData.length > 1;
        }
        break;
        
      default:
        return null;
    }

    if (breakdown.length === 0) return null;

    return (
      <Box 
        sx={{ 
          mt: 2, 
          p: 2, 
          bgcolor: `${serviceColors[service]}10`,
          borderRadius: 1.5,
          border: `1px solid ${serviceColors[service]}30`
        }}
      >
        <Typography variant="caption" fontWeight={600} color={serviceColors[service]} sx={{ display: 'block', mb: 1.5, fontSize: '0.8rem' }}>
          💰 Price Breakdown
        </Typography>
        {breakdown.map((item, index) => (
          <Box 
            key={index}
            sx={{ 
              mb: index < breakdown.length - 1 ? 1.5 : (showTotal ? 1.5 : 0),
              pb: index < breakdown.length - 1 ? 1.5 : (showTotal ? 1.5 : 0),
              borderBottom: index < breakdown.length - 1 || showTotal ? `1px dashed ${serviceColors[service]}30` : 'none'
            }}
          >
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: item.sublabel ? 0.5 : 0 }}>
              <Typography variant="body2" color="text.primary" sx={{ fontWeight: 500, flex: 1, pr: 2 }}>
                {item.label}
              </Typography>
              <Typography variant="body2" fontWeight={700} color={serviceColors[service]} sx={{ whiteSpace: 'nowrap' }}>
                {item.value}
              </Typography>
            </Box>
            {item.sublabel && (
              <Typography variant="caption" color="text.secondary" sx={{ display: 'block', fontSize: '0.7rem', fontStyle: 'italic' }}>
                {item.sublabel}
              </Typography>
            )}
            {item.subtitle && (
              <Typography variant="caption" color="text.secondary" sx={{ display: 'block', fontSize: '0.7rem', fontStyle: 'italic' }}>
                {item.subtitle}
              </Typography>
            )}
          </Box>
        ))}
        
        {/* Show total if multiple items */}
        {showTotal && (
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between',
            alignItems: 'center',
            pt: 1,
            mt: 0.5
          }}>
            <Typography variant="body2" fontWeight={700} color="text.primary">
              Total:
            </Typography>
            <Typography variant="h6" fontWeight={700} color={serviceColors[service]}>
              SGD {price.toLocaleString()}
            </Typography>
          </Box>
        )}
      </Box>
    );
  };

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
                {Array.isArray(details.selectedRestaurants) && details.selectedRestaurants.length > 0 ? (
                  // Check if new format (with dates and meals)
                  details.selectedRestaurants[0]?.date && details.selectedRestaurants[0]?.restaurants ? (
                    // New format: grouped by date with meals
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                      {details.selectedRestaurants.map((dateEntry, dateIndex) => (
                        <Box key={dateIndex} sx={{ 
                          p: 1.5, 
                          bgcolor: 'rgba(2, 136, 209, 0.05)',
                          borderRadius: 1,
                          border: '1px solid rgba(2, 136, 209, 0.2)'
                        }}>
                          <Typography variant="caption" fontWeight={600} color="primary" sx={{ mb: 1, display: 'block' }}>
                            📅 {new Date(dateEntry.date).toLocaleDateString('en-US', { 
                              weekday: 'short', 
                              year: 'numeric', 
                              month: 'short', 
                              day: 'numeric' 
                            })}
                          </Typography>
                          <List dense disablePadding>
                            {dateEntry.restaurants.map((entry, idx) => {
                              const mealIcons = {
                                Breakfast: '🌅',
                                Lunch: '☀️',
                                Dinner: '🌙'
                              };
                              const mealPeriod = entry.mealPeriod || entry.meal?.meal_period || 'Lunch';
                              return (
                                <ListItem key={idx} dense disableGutters sx={{ py: 0.5 }}>
                                  <ListItemIcon sx={{ minWidth: 30 }}>
                                    <RestaurantIcon fontSize="small" sx={{ color: serviceColors.restaurant }} />
                                  </ListItemIcon>
                                  <ListItemText 
                                    primary={
                                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, flexWrap: 'wrap' }}>
                                        <Typography variant="body2">
                                          {entry.restaurant?.name || entry.restaurant || 'Restaurant'}
                                        </Typography>
                                        <Chip 
                                          size="small" 
                                          label={`${mealIcons[mealPeriod] || ''} ${mealPeriod}`}
                                          sx={{ 
                                            height: 20,
                                            fontSize: 10,
                                            fontWeight: 600,
                                            bgcolor: mealPeriod === 'Breakfast' ? '#FFE0B2' : 
                                                     mealPeriod === 'Lunch' ? '#FFF9C4' : '#E1BEE7',
                                            color: mealPeriod === 'Breakfast' ? '#E65100' : 
                                                   mealPeriod === 'Lunch' ? '#F57F17' : '#6A1B9A',
                                            border: mealPeriod === 'Breakfast' ? '1px solid #FFB74D' : 
                                                    mealPeriod === 'Lunch' ? '1px solid #FFF59D' : '1px solid #CE93D8'
                                          }}
                                        />
                                        {entry.meal && (
                                          <>
                                            <Chip 
                                              size="small" 
                                              label={entry.meal.meal_type}
                                              sx={{ 
                                                height: 18, 
                                                fontSize: 9, 
                                                fontWeight: 600,
                                                bgcolor: '#BBDEFB',
                                                color: '#0D47A1',
                                                border: '1px solid #90CAF9'
                                              }}
                                            />
                                            <Chip 
                                              size="small" 
                                              label={entry.meal.item_type}
                                              sx={{ 
                                                height: 18, 
                                                fontSize: 9,
                                                fontWeight: 600,
                                                bgcolor: entry.meal.item_type === 'Vegetarian' ? '#C8E6C9' : '#FFCDD2',
                                                color: entry.meal.item_type === 'Vegetarian' ? '#1B5E20' : '#B71C1C',
                                                border: entry.meal.item_type === 'Vegetarian' ? '1px solid #81C784' : '1px solid #E57373'
                                              }}
                                            />
                                          </>
                                        )}
                                      </Box>
                                    }
                                  />
                                </ListItem>
                              );
                            })}
                          </List>
                        </Box>
                      ))}
                    </Box>
                  ) : (
                    // Old format: simple list
                    <List dense disablePadding>
                      {details.selectedRestaurants.map((restaurant, index) => (
                        <ListItem key={index} dense disableGutters>
                          <ListItemIcon sx={{ minWidth: 30 }}>
                            <RestaurantIcon fontSize="small" sx={{ color: serviceColors.restaurant }} />
                          </ListItemIcon>
                          <ListItemText primary={restaurant.name || restaurant} />
                        </ListItem>
                      ))}
                    </List>
                  )
                ) : (
                  <Typography variant="body2" color="text.secondary">No restaurants selected</Typography>
                )}
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

    const trimmedClientName = (customerName || "").trim();
    
    // Build the payload for create-enquiry API
    const buildEnquiryPayload = () => {
      console.log("Sending total price to API:", totalPrice);
      
      const payload = {
        approx_price: totalPrice,
        customer_name: trimmedClientName,
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
          // Check if new format (with dates and meals)
          if (restaurantDetails.selectedRestaurants[0]?.date && restaurantDetails.selectedRestaurants[0]?.restaurants) {
            // New format: Minimal structure - only IDs in nested array format
            const groupedByDate = [];
            
            restaurantDetails.selectedRestaurants.forEach(dateEntry => {
              // Create an object to group restaurants for this date
              const dateRestaurants = {};
              
              dateEntry.restaurants.forEach(entry => {
                const restaurantId = entry.restaurant?.id || entry.restaurant?.restaurant_id;
                const mealId = entry.meal?.meal_id;
                
                if (restaurantId && mealId) {
                  // Initialize restaurant meal array if not exists
                  if (!dateRestaurants[restaurantId]) {
                    dateRestaurants[restaurantId] = [];
                  }
                  
                  // Add only meal_id to this restaurant's array
                  if (!dateRestaurants[restaurantId].includes(mealId)) {
                    dateRestaurants[restaurantId].push(mealId);
                  }
                }
              });
              
              // Convert to nested array format: {date, restaurants: [{restaurant_id, meal_ids}]}
              if (Object.keys(dateRestaurants).length > 0) {
                const restaurantsArray = Object.keys(dateRestaurants).map(restaurantId => ({
                  restaurant_id: parseInt(restaurantId),
                  meal_ids: dateRestaurants[restaurantId]
                }));
                
                groupedByDate.push({
                  date: dateEntry.date,
                  restaurants: restaurantsArray
                });
              }
            });
            
            // Send everything in restaurant_ids field
            payload.restaurant_ids = groupedByDate;
            
            // Log to verify structure
            console.log('🍽️ Restaurant Payload - Grouped Structure:', {
              totalDates: groupedByDate.length,
              groupedData: groupedByDate
            });
          } else {
            // Old format: simple array
            payload.restaurant_ids = restaurantDetails.selectedRestaurants.map(restaurant => 
              restaurant.id || restaurant.restaurant_id || restaurant
            );
          }
        }
      }

      // console.log("Built enquiry payload:", payload);
      
      // // Additional logging for restaurant data
      // if (payload.restaurant_ids && Array.isArray(payload.restaurant_ids) && payload.restaurant_ids.length > 0 && payload.restaurant_ids[0]?.date) {
      //   console.log('');
      //   console.log('═══════════════════════════════════════════════════════');
      //   console.log('📋 RESTAURANT DATA (In restaurant_ids field)');
      //   console.log('═══════════════════════════════════════════════════════');
      //   console.log('');
      //   console.log('📅 Format: [{date, restaurants: [{restaurant_id, meal_ids}]}]');
      //   console.log('');
      //   console.log('📊 restaurant_ids Payload:');
      //   console.log(JSON.stringify(payload.restaurant_ids, null, 2));
      //   console.log('');
      //   console.log('💡 Readable Format:');
      //   payload.restaurant_ids.forEach((dateEntry, idx) => {
      //     console.log(`  Date ${idx + 1}: ${dateEntry.date}`);
      //     dateEntry.restaurants.forEach((restaurant) => {
      //       console.log(`    → Restaurant ID: ${restaurant.restaurant_id}, Meal IDs: [${restaurant.meal_ids.join(', ')}]`);
      //     });
      //   });
      //   console.log('');
      //   console.log('═══════════════════════════════════════════════════════');
      //   console.log('');
      // }
      
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
              window.location.href = '/app';
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


  const adultsCount = parseInt(bookingDetails?.guests?.adults || 0, 10) || 0;
  const childrenCount = parseInt(bookingDetails?.guests?.children || 0, 10) || 0;
  const guestsWithoutInfants = adultsCount + childrenCount;
  const avgPerPerson =
    guestsWithoutInfants > 0 ? Math.round(totalPrice / guestsWithoutInfants) : 0;

  return (
    <Box className="enquiry-review" sx={{ width: "100%", margin: "0 auto" }}>
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Quotations &amp; enquiries</div>
          <h1 className="partner-title">Review your booking</h1>
          <p className="partner-lede">
            Please review all your selected services and confirm your booking
            details.
          </p>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--secondary"
          onClick={onBack}
          disabled={submitting}
        >
          ← Back to services
        </button>
      </div>

      <section className="partner-card enquiry-review__trip">
        <h2 className="partner-card__title">Trip details</h2>
        <p className="partner-card__sub">
          Destination, dates and travellers for this enquiry.
        </p>
        <div className="enquiry-review__client">
          <div className="partner-field">
            <label htmlFor="enquiry-client-name">Client / group name</label>
            <input
              id="enquiry-client-name"
              type="text"
              value={customerName}
              placeholder="e.g. Smith family / ABC Corp incentive"
              disabled={submitting || enquiryStatus === "submitted"}
              onChange={(e) => {
                dispatch(setCustomerName(e.target.value));
                if (submitError) setSubmitError(null);
              }}
            />
          </div>
        </div>
        <div className="enquiry-review__trip-body">
          <TripDetailsComponent mode="view" />
        </div>
      </section>
      
      {/* Main Grid Layout */}
      <Grid container spacing={{ xs: 2, sm: 2.5, md: 2.5 }} className="enquiry-review__grid">
        {/* Left Column - Selected DMCs Display */}
        <Grid item xs={12} md={4} lg={3}>
          <section className="partner-card enquiry-review__dmc">
            <h2 className="partner-card__title">DMC partners</h2>
            <p className="partner-card__sub">Partners receiving this enquiry.</p>
            <DMCSelectionComponent 
              mode="summary"
              showLocationSection={false}
            />
          </section>
        </Grid>

        {/* Right Column - Booking Details */}
        <Grid item xs={12} md={8} lg={9}>
            
      {/* Selected services section */}
      <section className="partner-card enquiry-review__services">
        <h2 className="partner-card__title">Selected services</h2>
        <p className="partner-card__sub">
          Expand a service to check inclusions and pricing.
        </p>
        
        {/* Quick Stats Overview */}
        {selectedServices && selectedServices.length > 0 && (
          <Box sx={{ mb: 2, mt: 1 }}>
            <div className="partner-summary-grid enquiry-review__stats">
              <div className="partner-stat" style={{ cursor: "default" }}>
                <span className="partner-stat__label">Services selected</span>
                <strong className="partner-stat__value">
                  {selectedServices.length}
                </strong>
              </div>
              <div className="partner-stat" style={{ cursor: "default" }}>
                <span className="partner-stat__label">Total estimate</span>
                <strong className="partner-stat__value" style={{ fontSize: 22 }}>
                  SGD {Math.round(totalPrice).toLocaleString()}
                </strong>
              </div>
              <div className="partner-stat" style={{ cursor: "default" }}>
                <span className="partner-stat__label">Avg. per person</span>
                <strong className="partner-stat__value" style={{ fontSize: 22 }}>
                  SGD {avgPerPerson.toLocaleString()}
                </strong>
              </div>
              <div className="partner-stat" style={{ cursor: "default" }}>
                <span className="partner-stat__label">Nights</span>
                <strong className="partner-stat__value">
                  {(() => {
                    const checkinDate = bookingDetails?.checkIn;
                    const checkoutDate = bookingDetails?.checkOut;
                    if (checkinDate && checkoutDate) {
                      const parseDate = (dateStr) => {
                        if (!dateStr) return null;
                        const parts = dateStr.split("/");
                        if (parts.length === 3) {
                          return new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
                        }
                        return new Date(dateStr);
                      };
                      const checkIn = parseDate(checkinDate);
                      const checkOut = parseDate(checkoutDate);
                      if (checkIn && checkOut && !isNaN(checkIn) && !isNaN(checkOut)) {
                        return Math.max(
                          1,
                          Math.ceil((checkOut - checkIn) / (24 * 60 * 60 * 1000))
                        );
                      }
                    }
                    return 1;
                  })()}
                </strong>
              </div>
            </div>
          </Box>
        )}
        
        <Divider sx={{ mb: 2.5, borderColor: "#e4eaf3" }} />
        
        {selectedServices && selectedServices.length > 0 ? (
          <Grid container spacing={2}>
            {selectedServices.map((service) => (
              <Grid item xs={12} md={6} key={service}>
                <ServiceCard 
                  servicecolor={serviceColors[service]}
                  sx={{
                    position: 'relative',
                    overflow: 'visible',
                    cursor: 'pointer',
                  }}
                  onClick={() => setExpandedService(expandedService === service ? null : service)}
                >
                  {/* Price Badge */}
                  <Box
                    sx={{
                      position: 'absolute',
                      top: -10,
                      right: 14,
                      bgcolor: serviceColors[service],
                      color: 'white',
                      px: 1.5,
                      py: 0.4,
                      borderRadius: '999px',
                      boxShadow: 'none',
                      display: 'flex',
                      alignItems: 'center',
                      gap: 0.5,
                      zIndex: 1,
                    }}
                  >
                    <Typography variant="body2" fontWeight={700}>
                      SGD {servicePrices[service]?.toLocaleString() || '0'}
                    </Typography>
                  </Box>

                  <CardContent sx={{ pt: 2.5, pb: "16px !important" }}>
                    <Box display="flex" alignItems="center" justifyContent="space-between" mb={1.5}>
                      <Box display="flex" alignItems="center">
                        <Avatar 
                          sx={{ 
                            bgcolor: `${serviceColors[service]}18`,
                            color: serviceColors[service],
                            mr: 1.5,
                            width: 40,
                            height: 40,
                            boxShadow: 'none',
                          }}
                        >
                          {getServiceIcon(service)}
                        </Avatar>
                        <Box>
                          <Typography variant="subtitle1" fontWeight={700} sx={{ color: "#14213d" }}>
                            {formatServiceName(service)}
                          </Typography>
                          <Typography variant="caption" color="text.secondary">
                            Click to {expandedService === service ? 'collapse' : 'expand'} details
                          </Typography>
                        </Box>
                      </Box>
                      <Chip
                        label="Included"
                        size="small"
                        sx={{
                          bgcolor: `${serviceColors[service]}14`,
                          color: serviceColors[service],
                          fontWeight: 600,
                          border: `1px solid ${serviceColors[service]}33`
                        }}
                      />
                    </Box>
                    
                    <Divider sx={{ mb: 2 }} />
                    
                    <Accordion 
                      elevation={0} 
                      expanded={expandedService === service}
                      onChange={() => setExpandedService(expandedService === service ? null : service)}
                      sx={{ 
                        '&:before': { display: 'none' },
                        bgcolor: 'transparent'
                      }}
                    >
                      <AccordionSummary 
                        expandIcon={<ExpandMoreIcon />}
                        sx={{ 
                          p: 0, 
                          minHeight: 'auto',
                          cursor: 'pointer',
                          '&:hover': {
                            bgcolor: `${serviceColors[service]}12`,
                            borderRadius: 1,
                          },
                          borderRadius: 1,
                          px: 1.5,
                          py: 1,
                          transition: 'all 0.2s ease'
                        }}
                      >
                        <Typography 
                          color="primary" 
                          fontWeight={600}
                          sx={{ display: 'flex', alignItems: 'center', gap: 1 }}
                        >
                          📋 Service Details
                        </Typography>
                      </AccordionSummary>
                      <AccordionDetails 
                        sx={{ 
                          pt: 2, 
                          pb: 2,
                          px: 2,
                          maxHeight: '400px',
                          minHeight: '100px',
                          overflowY: 'auto',
                          overflowX: 'hidden',
                          position: 'relative',
                          overscrollBehavior: 'contain',
                          WebkitOverflowScrolling: 'touch',
                          display: 'block',
                          // Ensure scrolling works
                          scrollBehavior: 'smooth',
                          // Ensure scrollbar is interactive
                          pointerEvents: 'auto',
                          // Custom scrollbar styling
                          '&::-webkit-scrollbar': {
                            width: '8px',
                            display: 'block'
                          },
                          '&::-webkit-scrollbar-track': {
                            bgcolor: `${serviceColors[service]}08`,
                            borderRadius: '10px',
                            margin: '8px 0',
                            pointerEvents: 'auto'
                          },
                          '&::-webkit-scrollbar-thumb': {
                            bgcolor: `${serviceColors[service]}60`,
                            borderRadius: '10px',
                            cursor: 'grab',
                            pointerEvents: 'auto',
                            '&:hover': {
                              bgcolor: `${serviceColors[service]}80`
                            },
                            '&:active': {
                              cursor: 'grabbing',
                              bgcolor: `${serviceColors[service]}90`
                            }
                          },
                          // Firefox scrollbar
                          scrollbarWidth: 'thin',
                          scrollbarColor: `${serviceColors[service]}60 ${serviceColors[service]}08`
                        }}
                        onClick={(e) => {
                          // Prevent accordion toggle when clicking inside details
                          // Scrollbar clicks will naturally not trigger this since they're on pseudo-elements
                          e.stopPropagation();
                        }}
                        onWheel={(e) => {
                          // Allow natural scrolling - only prevent propagation at boundaries to avoid parent scroll
                          const element = e.currentTarget;
                          const { scrollTop, scrollHeight, clientHeight } = element;
                          
                          // Only interfere if content is actually scrollable
                          if (scrollHeight > clientHeight + 1) {
                            const delta = e.deltaY;
                            const tolerance = 2; // Small tolerance for boundary detection
                            const isAtTop = scrollTop <= tolerance;
                            const isAtBottom = scrollTop + clientHeight >= scrollHeight - tolerance;
                            
                            // Only stop propagation when at boundaries to prevent parent scroll
                            // This allows normal scrolling within the container
                            if ((isAtTop && delta < 0) || (isAtBottom && delta > 0)) {
                              e.stopPropagation();
                            }
                            // Otherwise, let the scroll happen naturally - don't call preventDefault or stopPropagation
                          }
                        }}
                      >
                        {renderServiceDetails(service)}
                        {renderPriceBreakdown(service)}
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
      </section>

      <section className="partner-card enquiry-review__trust">
        <div className="enquiry-review__trust-grid">
          <div className="enquiry-review__trust-item">
            <span className="enquiry-review__trust-icon" aria-hidden="true">
              <SecurityIcon fontSize="small" />
            </span>
            <div>
              <strong>Secure booking</strong>
              <p>Your personal data is protected</p>
            </div>
          </div>
          <div className="enquiry-review__trust-item">
            <span className="enquiry-review__trust-icon enquiry-review__trust-icon--amber" aria-hidden="true">
              <SupportIcon fontSize="small" />
            </span>
            <div>
              <strong>24/7 support</strong>
              <p>Contact us anytime during your trip</p>
            </div>
          </div>
        </div>
      </section>

      <div
        data-action-buttons
        className="enquiry-review__actions"
      >
        <button
          type="button"
          className="partner-btn partner-btn--secondary"
          onClick={onBack}
          disabled={submitting}
        >
          ← Back to services
        </button>
        
        <button
          type="button"
          className="partner-btn partner-btn--primary"
          onClick={handleSubmit}
          disabled={submitting || enquiryStatus === "submitted"}
          style={{ minWidth: 180 }}
        >
          {submitting ? (
            "Submitting…"
          ) : enquiryStatus === "submitted" ? (
            "Enquiry submitted"
          ) : (
            "Submit booking enquiry →"
          )}
        </button>
      </div>
      
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
        </Grid>
      </Grid>

  
    </Box>
  );
};

export default ConfirmDetails;
