import React, { useEffect, useState, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  selectUserInfo,
  selectBookingResponse,
} from "@/slice/common/customerInfo";
import {
  createBooking,
  setRestaurantsService,
} from "@/slice/restaurant/RestaurantsSlice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { useNavigate, useLocation } from "react-router-dom";
import { toast } from "react-toastify";
import Cookies from "js-cookie";
import {
  Box,
  Typography,
  Card,
  CardContent,
  Grid,
  styled,
  Badge,
  Divider,
  Avatar,
  Chip,
  CardMedia,
  Paper,
  Button,
  Tooltip,
} from "@mui/material";
import PersonIcon from "@mui/icons-material/Person";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import DrinkIcon from "@mui/icons-material/LocalDrink";
import NoDrinksIcon from "@mui/icons-material/NoDrinks";
import CircleIcon from "@mui/icons-material/Circle";
import SquareIcon from "@mui/icons-material/Square";
import dayjs from "dayjs";

import { alpha } from "@mui/material/styles";
import EmojiFoodBeverageIcon from "@mui/icons-material/EmojiFoodBeverage";
import FastfoodIcon from "@mui/icons-material/Fastfood";
import RamenDiningIcon from "@mui/icons-material/RamenDining";
import BrunchDiningIcon from "@mui/icons-material/BrunchDining";
import RestaurantMenuIcon from "@mui/icons-material/RestaurantMenu";
import SoupKitchenIcon from "@mui/icons-material/SoupKitchen";
import EmailIcon from '@mui/icons-material/Email';
import LocalPhoneIcon from '@mui/icons-material/LocalPhone';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import AirportShuttleIcon from '@mui/icons-material/AirportShuttle';
import LocalTaxiIcon from '@mui/icons-material/LocalTaxi';
import GroupIcon from '@mui/icons-material/Group';
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../slice/dmc/dmcSlice";

const RoomCard = styled(Card)(({ theme }) => ({
  background: "white",
  borderRadius: "15px",
  marginBottom: "20px",
  transition: "transform 0.2s ease-in-out",
  "&:hover": {
    transform: "translateY(-5px)",
    boxShadow: "0 8px 20px rgba(53, 84, 209, 0.15)",
  },
}));

const RoomTypeHeader = styled(Box)(({ theme }) => ({
  background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
  padding: "15px 20px",
  borderTopLeftRadius: "15px",
  borderTopRightRadius: "15px",
  color: "white",
}));


const getMealTypeIcon = (mealType) => {
  const lowerCaseMealType = mealType?.toLowerCase() || '';
  
  if (lowerCaseMealType.includes('breakfast')) {
    return <EmojiFoodBeverageIcon sx={{ color: "#3554D1" }} />;
  } else if (lowerCaseMealType.includes('lunch')) {
    return <FastfoodIcon sx={{ color: "#3554D1" }} />;
  } else if (lowerCaseMealType.includes('dinner')) {
    return <RamenDiningIcon sx={{ color: "#3554D1" }} />;
  } else if (lowerCaseMealType.includes('brunch')) {
    return <BrunchDiningIcon sx={{ color: "#3554D1" }} />;
  } else {
    return <RestaurantMenuIcon sx={{ color: "#3554D1" }} />;
  }
};

// Add this utility function near the top of your component or import it
const capitalizeFirstLetter = (string) => {
  if (!string) return '';
  return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
};

// Add transport icon helper function
const getTransportIcon = (type) => {
  if (!type) return <DirectionsCarIcon sx={{ color: "#3554D1" }} />;
  
  switch(type.toLowerCase()) {
    case 'luxury':
      return <DirectionsCarIcon sx={{ color: "#3554D1" }} />;
    case 'salon car':
      return <LocalTaxiIcon sx={{ color: "#3554D1" }} />;
    case 'combi van':
      return <AirportShuttleIcon sx={{ color: "#3554D1" }} />;
    default:
      return <DirectionsCarIcon sx={{ color: "#3554D1" }} />;
  }
};

export default function Index2() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useLocation();

  const userInfo = useSelector(selectUserInfo);
  // console.log('userInfo in restaurant index2:', userInfo);
  
  // const bookingResponse = useSelector(selectBookingResponse);
  const restaurantBookings = useSelector(
    (state) => state.restaurants?.restaurantBookings || []
  );
  // console.log('restaurantBookings restaurantBookings',restaurantBookings);
  
  const selectedRestaurant = useSelector(
    (state) => state.restaurants?.selectedRestaurant || {}
  );
  //  console.log('selectedRestaurant 13333333333',selectedRestaurant.id);
  
  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || "DMC";


  const [showMoreIndex, setShowMoreIndex] = useState(null);

const truncateToWords = (text, wordLimit) => {
  const words = text.split(" ");
  if (words.length <= wordLimit) return text;
  return words.slice(0, wordLimit).join(" ") + "...";
};


  // Add these Redux selectors at the top of the component
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  const currentTax = useSelector((state) => state.auth.currentTax || 0);
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const usdTax = useSelector((state) => state.auth.usdTax || 0);
  const bookingType = useSelector((state) => state.common.bookingType);
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // const PriceHide = 1;

  // const [showEnquiry, setShowEnquiry] = useState(false);
  // const [enquiryAmount, setEnquiryAmount] = useState(() => {
  //   return restaurantBookings?.[0]?.data?.[0]?.totalPrice || "";
  // });
  // const [enquiryComment, setEnquiryComment] = useState("");
  // const [commentError, setCommentError] = useState(false);

  // Get the booking mode from the data
  // const bookingMode = restaurantBookings?.[0]?.data?.[0]?.mode || "dmc";

  // Debug log
  // useEffect(() => {
  //   console.log(
  //     "Restaurant Bookings:",
  //     JSON.stringify(restaurantBookings, null, 2)
  //   );
  // }, [restaurantBookings]);

  useEffect(() => {
    if (!userInfo || !userInfo.fullName) {
      navigate("/dashboard/db-dashboard/tour-single/:id");
    }
  }, [userInfo, navigate]);

  // Add these console logs for debugging
  // useEffect(() => {
  //   console.log('UserInfo:', userInfo);
  //   console.log('Restaurant Bookings:', restaurantBookings);
  //   console.log('Selected Restaurant:', selectedRestaurant);
  // }, [userInfo, restaurantBookings, selectedRestaurant]);

  // Track if we've navigated back
  const [hasNavigatedBack, setHasNavigatedBack] = useState(false);
  
  // Add a ref to store original data
  const originalDataRef = useRef(null);

  // Detect navigation state
  useEffect(() => {
    // If we don't have a ref to the original data, save it
    if (!originalDataRef.current && restaurantBookings?.length > 0) {
      originalDataRef.current = {
        restaurantBookings: JSON.parse(JSON.stringify(restaurantBookings)),
        selectedRestaurant: JSON.parse(JSON.stringify(selectedRestaurant))
      };
      // console.log('Saved original booking data:', originalDataRef.current);
    }
    
    // Check if we might have navigated back (location key or state might indicate this)
    const maybeNavigatedBack = location.key !== undefined || location.state?.from === 'details';
    if (maybeNavigatedBack && !hasNavigatedBack) {
      setHasNavigatedBack(true);
      // console.log('Detected navigation back to this page');
    }
  }, [location, restaurantBookings, selectedRestaurant, hasNavigatedBack]);

  // Restore data if needed when we've navigated back
  useEffect(() => {
    if (hasNavigatedBack && originalDataRef.current) {
      // If we have navigated back and have original data, we might need to restore it
      // console.log('Checking if data needs to be restored after navigation');
      
      const currentBookings = restaurantBookings || [];
      const originalBookings = originalDataRef.current.restaurantBookings || [];
      
      // Check if the current data might be missing important fields
      const currentBookingData = currentBookings[0]?.data?.[0];
      const originalBookingData = originalBookings[0]?.data?.[0];
      
      const isCurrentDataIncomplete = !currentBookingData?.restaurantId || 
                                     !currentBookings.length;
      
      if (isCurrentDataIncomplete && originalBookingData?.restaurantId) {
        // console.log('Restoring original booking data after navigation');
        // Dispatch actions to restore the original state
        // You'll need the actual action creators from your redux store
        if (typeof dispatch(setRestaurantsService) === 'function') {
          dispatch(setRestaurantsService(originalDataRef.current.restaurantBookings));
        }
        
        // You may need to add additional action dispatches here to fully restore state
      }
    }
  }, [hasNavigatedBack, restaurantBookings, dispatch]);

  // Log full state on each render for debugging
  // useEffect(() => {
  //   console.log('Current state:', {
  //     userInfo,
  //     restaurantBookings,
  //     selectedRestaurant,
  //     hasNavigatedBack
  //   });
  // }, [userInfo, restaurantBookings, selectedRestaurant, hasNavigatedBack]);

  // Add state for tracking button loading
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isEnquiring, setIsEnquiring] = useState(false);

  const handleFinalSubmit = async () => {
     try {
       // Set loading state
       setIsSubmitting(true);
       
       if (!userInfo || !userInfo.fullName) {
         toast.error(
           "Customer information is missing. Please fill out the form first."
         );
         navigate("/dashboard/db-dashboard/tour-single/:id");
         setIsSubmitting(false); // Reset loading state
         return;
       }

      // Add debug log for phone values before submission
      // console.log('Phone details before booking submission:', {
      //   phone: userInfo.phone,
      //   countryCode: userInfo.countryCode,
      //   fullUserInfo: userInfo
      // });

      // Calculate total price including transport if available
      const mealPrice = restaurantBookings?.[0]?.data?.[0]?.totalPrice || 0;
      
      // Calculate transport price based on type
      let transportPrice = 0;
      const transport = restaurantBookings?.[0]?.data?.[0]?.transport;
      
      if (transport) {
        const adultCount = restaurantBookings?.[0]?.data?.[0]?.adultCount || 0;
        const childCount = restaurantBookings?.[0]?.data?.[0]?.childCount || 0;
        const totalPax = adultCount + childCount;
        
        // If shared transport, multiply price by total pax
        if (transport.transport_type === 'shared') {
          transportPrice = transport.price * totalPax;
        } else {
          // For private transport, use price as is
          transportPrice = transport.price;
        }
      }
      
      const totalPrice = mealPrice + transportPrice;
 
       const bookingDetails = {
         agent_id: Cookies.get("AgentId") || "0",
         data: [
           {
             ...userInfo,
             bookingDate: restaurantBookings?.[0]?.data?.[0]?.bookingDate,
             visitTime: restaurantBookings?.[0]?.data?.[0]?.visitTime,
             adultCount: restaurantBookings?.[0]?.data?.[0]?.adultCount,
             childCount: restaurantBookings?.[0]?.data?.[0]?.childCount || 0,
             restaurantId: restaurantBookings?.[0]?.data?.[0]?.restaurantId || selectedRestaurant.id,
             restaurantName: restaurantBookings?.[0]?.data?.[0]?.restaurantName,
             mealType: restaurantBookings?.[0]?.data?.[0]?.mealType,
             mealSpecificType:
               restaurantBookings?.[0]?.data?.[0]?.mealSpecificType,
             MealDescription:
               restaurantBookings?.[0]?.data?.[0]?.MealDescription,
            totalPrice: totalPrice,
            mealPrice: mealPrice,
            transport: restaurantBookings?.[0]?.data?.[0]?.transport || null,
            transportPrice: transportPrice,
             priceTypes: restaurantBookings?.[0]?.data?.[0]?.priceTypes,
             bookingType: "booking",
           },
         ],
         tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
         type: "restaurant",
         bookingType: "booking",
       };
 
       const response = await dispatch(createBooking(bookingDetails)).unwrap();
 
       if (response?.service?.date_service) {
         dispatch(setDateService(response.service.date_service));
         dispatch(setRestaurantsService(response.service.data));
         toast.success("Booking successful!", {
           position: "top-center",
           autoClose: 3000,
         });
 
         navigate("/dashboard/db-dashboard/restaurants-thank-you", {
           state: { bookingResponse: response },
         });
       }
     } catch (error) {
       console.error("Error during submission:", error);
       toast.error("Something went wrong. Please try again later.", {
         position: "top-center",
         autoClose: 3000,
       });
     } finally {
       // Reset loading state
       setIsSubmitting(false);
     }
   };

  // const handleAmountChange = (e) => {
  //   const newValue = parseFloat(e.target.value);
  //   const totalPrice = restaurantBookings?.[0]?.data?.[0]?.totalPrice || 0;

  //   if (newValue <= totalPrice && newValue > 0) {
  //     setEnquiryAmount(newValue);
  //   }
  // };

   const handleEnquirySubmit = async () => {
     try {
       // Set loading state
       setIsEnquiring(true);
       
       if (!userInfo || !userInfo.fullName) {
         toast.error("Customer information is missing. Please fill out the form first.");
         navigate("/dashboard/db-dashboard/tour-single/:id");
         setIsEnquiring(false); // Reset loading state
         return;
       }

       // Add debug log for phone values before enquiry submission
      //  console.log('Phone details before enquiry submission:', {
      //    phone: userInfo.phone,
      //    countryCode: userInfo.countryCode,
      //    fullUserInfo: userInfo
      //  });

       // Calculate total price including transport if available
       const mealPrice = restaurantBookings?.[0]?.data?.[0]?.totalPrice || 0;
       
       // Calculate transport price based on type
       let transportPrice = 0;
       const transport = restaurantBookings?.[0]?.data?.[0]?.transport;
       
       if (transport) {
         const adultCount = restaurantBookings?.[0]?.data?.[0]?.adultCount || 0;
         const childCount = restaurantBookings?.[0]?.data?.[0]?.childCount || 0;
         const totalPax = adultCount + childCount;
         
         // If shared transport, multiply price by total pax
         if (transport.transport_type === 'shared') {
           transportPrice = transport.price * totalPax;
         } else {
           // For private transport, use price as is
           transportPrice = transport.price;
         }
       }
       
       const totalPrice = mealPrice + transportPrice;

       const enquiryDetails = {
         agent_id: Cookies.get("AgentId") || "0",
         data: [
           {
             ...userInfo,
             bookingDate: restaurantBookings?.[0]?.data?.[0]?.bookingDate,
             visitTime: restaurantBookings?.[0]?.data?.[0]?.visitTime,
             adultCount: restaurantBookings?.[0]?.data?.[0]?.adultCount,
             childCount: restaurantBookings?.[0]?.data?.[0]?.childCount || 0,
             restaurantId: restaurantBookings?.[0]?.data?.[0]?.restaurantId || selectedRestaurant.id,
             restaurantName: restaurantBookings?.[0]?.data?.[0]?.restaurantName,
             mealType: restaurantBookings?.[0]?.data?.[0]?.mealType,
             mealSpecificType:
               restaurantBookings?.[0]?.data?.[0]?.mealSpecificType,
             MealDescription:
               restaurantBookings?.[0]?.data?.[0]?.MealDescription,
             totalPrice: totalPrice,
             mealPrice: mealPrice,
             transport: restaurantBookings?.[0]?.data?.[0]?.transport || null,
             transportPrice: transportPrice,
             priceTypes: restaurantBookings?.[0]?.data?.[0]?.priceTypes,
             bookingType: "enquiry",
           },
         ],
         tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
         type: "restaurant",
         bookingType: "enquiry",
       };

      //  console.log("Sending enquiry data:", enquiryDetails);
       const response = await dispatch(createBooking(enquiryDetails)).unwrap();
      //  console.log('Enquiry response:', response);

       // Only proceed if we have valid response data
       if (!response?.service?.date_service) {
         // If we don't have the expected response structure, throw an error
         throw new Error("Invalid response from server. Please try again.");
       }

       // If we get here, everything was successful
       dispatch(setDateService(response.service.date_service));
       dispatch(setRestaurantsService(response.service.data));
       
       toast.success("Enquiry submitted successfully!", {
         position: "top-center",
         autoClose: 3000,
       });

       // Navigate only after successful submission
       navigate("/dashboard/db-dashboard/restaurants-thank-you", {
         state: { bookingResponse: response },
       });
       
     } catch (error) {
       console.error("Error during enquiry submission:", error);
       // Show error message and don't navigate away
       toast.error("Something went wrong. Please try again later.", {
         position: "top-center",
         autoClose: 5000,
       });
       // Don't navigate to thank you page if there was an error
     } finally {
       // Reset loading state
       setIsEnquiring(false);
     }
   };

  // Modified back button to help with state preservation
  const handleBackClick = () => {
    // Save current state before navigating back
    originalDataRef.current = {
      restaurantBookings: JSON.parse(JSON.stringify(restaurantBookings)),
      selectedRestaurant: JSON.parse(JSON.stringify(selectedRestaurant))
    };
    
    // Navigate back with state indicator
    navigate('/dashboard/db-dashboard/restaurants-details/:id', { 
      state: { fromBooking: true } 
    });
  };

  // Helper function to safely render meal type
  const renderMealType = (mealType) => {
    if (!mealType) return "N/A";
    if (typeof mealType === "string") return capitalizeFirstLetter(mealType);

    // If it's an object, return specific properties with capitalization
    return mealType.item_name ? capitalizeFirstLetter(mealType.item_name) : 
           mealType.name ? capitalizeFirstLetter(mealType.name) : "N/A";
  };

  // Helper function to format date
  const formatDate = (date) => {
    if (!date) return "Not Selected";
    return dayjs(date).format("ddd, D MMM'YY");
  };

  // Format price with commas
  const formatPrice = (price) => {
    if (typeof price !== "number") return "0.00";

    return price.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  return (
    <Box
      sx={{
        borderRadius: "12px",
        overflow: "hidden",
        boxShadow: "0 4px 16px rgba(0, 0, 0, 0.08)",
        backgroundColor: "white",
        p: { xs: 2, sm: 2.5, md: 3 },
        maxWidth: "1200px",
        mx: "auto",
      }}
    >
      {/* Back Button with the updated click handler */}
      <Box sx={{
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center",
        mb: 3,
      }}>
        <Button
          startIcon={<Box component="span">←</Box>}
          onClick={handleBackClick}
          sx={{
            color: "#3554D1",
            fontWeight: "medium",
            fontSize: "1rem",
            "&:hover": {
              backgroundColor: "rgba(53, 84, 209, 0.05)",
            },
          }}
        >
          Back to Details
        </Button>
      </Box>

      {/* Header Section with Gradient Background */}
      <Box
        sx={{
          background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
          borderRadius: "8px",
          p: 2,
          mb: 2.5,
          color: "white",
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
        }}
      >
        <Box sx={{ display: "flex", alignItems: "center" }}>
          <RestaurantIcon sx={{ fontSize: 24, mr: 1 }} />
          <Typography
            variant="h6"
            sx={{ fontWeight: "bold", fontSize: "1.1rem" }}
          >
            Restaurant Booking Details
          </Typography>
        </Box>
      
      </Box>

      {/* Restaurant Image and Name Section - Modern Design */}
      <Card
        elevation={2}
        sx={{
          borderRadius: "12px",
          overflow: "hidden",
          transition: "transform 0.2s ease, box-shadow 0.2s ease",
          "&:hover": {
            transform: "translateY(-3px)",
            boxShadow: "0 6px 16px rgba(0,0,0,0.1)",
          },
          mb: 2.5,
        }}
      >
        <Grid container>
          <Grid
            item
            xs={12}
            md={4}
            sx={{
              minHeight: { xs: "200px", md: "240px" },
              position: "relative",
              "&::after": {
                content: '""',
                position: "absolute",
                bottom: 0,
                left: 0,
                width: "100%",
                height: "30%",
                background:
                  "linear-gradient(to top, rgba(0,0,0,0.5), rgba(0,0,0,0))",
                zIndex: 1,
                pointerEvents: "none",
              },
            }}
          >
            <CardMedia
              component="img"
              image={
                selectedRestaurant?.master_image ||
                "https://via.placeholder.com/300x300?text=Restaurant+Image"
              }
              alt={selectedRestaurant?.name || "Restaurant"}
              sx={{
                width: "100%",
                height: "100%",
                position: "absolute",
                top: 0,
                left: 0,
                objectFit: "cover",
                objectPosition: "center",
              }}
            />
            {selectedRestaurant?.name && (
              <Box
                sx={{
                  position: "absolute",
                  bottom: 8,
                  left: 8,
                  zIndex: 2,
                  display: { xs: "flex", md: "none" },
                  alignItems: "center",
                }}
              >
                <Chip
                  label={selectedRestaurant.name}
                  sx={{
                    bgcolor: "rgba(255, 255, 255, 0.9)",
                    color: "#3554D1",
                    fontWeight: "bold",
                    fontSize: "0.8rem",
                  }}
                  size="small"
                />
              </Box>
            )}
          </Grid>
          <Grid item xs={12} md={8}>
            <CardContent sx={{ p: { xs: 2, md: 2.5 } }}>
              <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                <RestaurantIcon
                  sx={{ color: "#3554D1", mr: 1, fontSize: 20 }}
                />
                <Typography
                  variant="h6"
                  sx={{ fontWeight: "bold", fontSize: "1.1rem" }}
                >
                  {selectedRestaurant?.name || "Restaurant Name"}
                </Typography>
              </Box>

              <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                <LocationOnIcon
                  sx={{ color: "#3554D1", mr: 1, fontSize: 18 }}
                />
                <Typography variant="body2" sx={{ fontSize: "0.95rem" }}>
                  {selectedRestaurant?.city},{" "}
                  {selectedRestaurant?.country || ""}
                </Typography>
              </Box>

              <Box
                sx={{
                  p: 1.5,
                  backgroundColor: "rgba(53, 84, 209, 0.03)",
                  borderRadius: "8px",
                  border: "1px solid rgba(53, 84, 209, 0.1)",
                }}
              >
                <Grid container spacing={2}>
                  <Grid item xs={12} sm={6}>
                    <Box
                      sx={{
                        display: "flex",
                        alignItems: "center",
                        p: 1,
                        borderRadius: "6px",
                        backgroundColor: "white",
                        border: "1px solid rgba(53, 84, 209, 0.1)",
                        transition: "transform 0.2s ease",
                        "&:hover": {
                          transform: "translateY(-2px)",
                          boxShadow: "0 2px 8px rgba(0,0,0,0.05)",
                        },
                      }}
                    >
                      <Avatar
                        sx={{
                          bgcolor: "#3554D1",
                          width: 32,
                          height: 32,
                          mr: 1.5,
                        }}
                      >
                        <CalendarTodayIcon sx={{ fontSize: 16 }} />
                      </Avatar>
                      <Box>
                        <Typography
                          variant="caption"
                          color="textSecondary"
                          sx={{ fontSize: "0.8rem" }}
                        >
                          Booking Date
                        </Typography>
                        <Typography
                          variant="body2"
                          sx={{ fontWeight: "medium", fontSize: "0.95rem" }}
                        >
                          {restaurantBookings?.[0]?.data?.[0]?.bookingDate
                            ? formatDate(
                                restaurantBookings?.[0]?.data?.[0]?.bookingDate
                              )
                            : "Not Selected"}
                        </Typography>
                      </Box>
                    </Box>
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <Box
                      sx={{
                        display: "flex",
                        alignItems: "center",
                        p: 1,
                        borderRadius: "6px",
                        backgroundColor: "white",
                        border: "1px solid rgba(53, 84, 209, 0.1)",
                        transition: "transform 0.2s ease",
                        "&:hover": {
                          transform: "translateY(-2px)",
                          boxShadow: "0 2px 8px rgba(0,0,0,0.05)",
                        },
                      }}
                    >
                      <Avatar
                        sx={{
                          bgcolor: "#3554D1",
                          width: 32,
                          height: 32,
                          mr: 1.5,
                        }}
                      >
                        <AccessTimeIcon sx={{ fontSize: 16 }} />
                      </Avatar>
                      <Box>
                        <Typography
                          variant="caption"
                          color="textSecondary"
                          sx={{ fontSize: "0.8rem" }}
                        >
                          Visit Time
                        </Typography>
                        <Typography
                          variant="body2"
                          sx={{ fontWeight: "medium", fontSize: "0.95rem" }}
                        >
                          {restaurantBookings?.[0]?.data?.[0]?.visitTime ||
                            "N/A"}
                        </Typography>
                      </Box>
                    </Box>
                  </Grid>
                </Grid>
              </Box>
            </CardContent>
          </Grid>
        </Grid>
      </Card>

      {/* Booking Details Section */}
      <Box sx={{ mb: 2.5 }}>
        <Typography
          variant="subtitle1"
          sx={{
            fontWeight: "bold",
            mb: 1.5,
            display: "flex",
            alignItems: "center",
            fontSize: "1rem",
          }}
        >
          <RestaurantIcon sx={{ mr: 1, color: "#3554D1", fontSize: 20 }} />
          Booking Details
        </Typography>

        {restaurantBookings.length > 0 ? (
          <Box>
            {restaurantBookings.map((booking, index) => {
              const data = booking?.data?.[0] || {};
              return (
                <RoomCard key={index} sx={{ mb: 2 }}>
                  <RoomTypeHeader sx={{ py: 1.5, px: 2 }}>
                    <Typography variant="subtitle1" sx={{ fontWeight: 600, fontSize: "1rem" }}>
                      {renderMealType(data?.mealType) || "Meal Details"}
                    </Typography>
                  </RoomTypeHeader>

                  <CardContent sx={{ p: 2 }}>
                    <Grid container spacing={2}>
                      {/* Meal Details - Left Side (Swapped from right) */}
                      <Grid item xs={12} md={6}>
                        <Paper
                          elevation={1}
                          sx={{
                            p: 1.5,
                            borderRadius: "8px",
                            height: "100%",
                            display: "flex",
                            flexDirection: "column",
                          }}
                        >
                          <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                            <RestaurantIcon sx={{ color: "#3554D1", mr: 1, fontSize: 20 }} />
                            <Typography variant="body1" sx={{ fontWeight: "bold", fontSize: "1.05rem" }}>
                              Meal Details
                            </Typography>
                          </Box>

                          <Divider sx={{ my: 0.75 }} />

                          <Box
                            sx={{
                              mb: 3,
                              pb: 2,
                              borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                              "&:last-child": {
                                borderBottom: "none",
                                mb: 0,
                                pb: 0,
                              },
                            }}
                          >
                            <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                              <Box
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  backgroundColor: "#F8FAFF",
                                  p: 1.5,
                                  borderRadius: "8px",
                                  border: "1px solid rgba(53, 84, 209, 0.1)",
                                }}
                              >
                                {getMealTypeIcon(data?.mealType)}
                                <Typography variant="body1" sx={{ ml: 1 }}>
                                  {capitalizeFirstLetter(data?.mealType || "")} - {capitalizeFirstLetter(data?.mealSpecificType || "")}
                                </Typography>
                              </Box>

                              {/* Cuisine Box */}
                              {selectedRestaurant?.cuisine && (
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    backgroundColor: "#F8FAFF",
                                    p: 1.5,
                                    borderRadius: "8px",
                                    border: "1px solid rgba(53, 84, 209, 0.1)",
                                  }}
                                >
                                  <SoupKitchenIcon sx={{ color: "#3554D1" }} />
                                  <Typography variant="body1" sx={{ ml: 1 }}>
                                    Cuisine: {selectedRestaurant.cuisine}
                                  </Typography>
                                </Box>
                              )}
                            </Box>
                          </Box>

                          {/* Selected Items Section */}
                          {data?.MealDescription &&
                            data?.mealSpecificType !== "Buffet" && (
                              <Box
                                sx={{
                                  mb: 3,
                                  pb: 2,
                                  borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                                  "&:last-child": {
                                    borderBottom: "none",
                                    mb: 0,
                                    pb: 0,
                                  },
                                }}
                              >
                                <Typography
                                  variant="subtitle1"
                                  sx={{
                                    fontWeight: 600,
                                    color: "#3554D1",
                                    mb: 2,
                                  }}
                                >
                                  Selected Items:
                                </Typography>
                                {Array.isArray(data.MealDescription) &&
                                  data.MealDescription.map((item, idx) => (
                                    <Box
                                      key={idx}
                                      sx={{
                                        display: "flex",
                                        justifyContent: "space-between",
                                        alignItems: "flex-start",
                                        backgroundColor: "#F8FAFF",
                                        p: 1.5,
                                        borderRadius: "8px",
                                        border: "1px solid rgba(53, 84, 209, 0.1)",
                                        mb: 1,
                                      }}
                                    >
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "flex-start",
                                          gap: 1,
                                          flex: 1,
                                        }}
                                      >
                                        {/* Item Details */}
                                      {item.name && (
  <Box>
    <Typography
      variant="body2"
      color="text.secondary"
      sx={{ mb: 0.5 }}
    >
      {showMoreIndex === idx ? item.name : truncateToWords(item.name, 100)}
    </Typography>
    {item.name.split(' ').length > 100 && (
      <Button
        size="small"
        onClick={() =>
          setShowMoreIndex(showMoreIndex === idx ? null : idx)
        }
        sx={{ textTransform: 'none', mt: 0.5, p: 0, minHeight: 0, minWidth: 0 }}
      >
        {showMoreIndex === idx ? "Show Less" : "Show More"}
      </Button>
    )}
  </Box>
)}


                                        {/* Indicators and Quantity */}
                                        <Box 
                                          sx={{ 
                                            display: 'flex', 
                                            alignItems: 'center', 
                                            gap: 2,
                                            minWidth: 'fit-content'
                                          }}
                                        >
                                          {/* Indicators */}
                                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                                            {/* Veg/Non-Veg Indicator */}
                                            {item.item_type && (
                                              <Tooltip
                                                title={
                                                  item.item_type === "Veg"
                                                    ? "Vegetarian"
                                                    : "Non-Vegetarian"
                                                }
                                                arrow
                                                placement="top"
                                              >
                                                <Box
                                                  sx={{
                                                    position: "relative",
                                                    display: "inline-flex",
                                                  }}
                                                >
                                                  <SquareIcon
                                                    sx={{
                                                      color:
                                                        item.item_type === "Veg"
                                                          ? "#2ecc71"
                                                          : "#e74c3c",
                                                      fontSize: 14,
                                                    }}
                                                  />
                                                  <CircleIcon
                                                    sx={{
                                                      color: "#fff",
                                                      fontSize: 6,
                                                      position: "absolute",
                                                      top: "50%",
                                                      left: "50%",
                                                      transform:
                                                        "translate(-50%, -50%)",
                                                    }}
                                                  />
                                                </Box>
                                              </Tooltip>
                                            )}

                                            {/* Alcoholic/Non-Alcoholic Indicator */}
                                            {item.category &&
                                              (item.category === "Alcoholic" ? (
                                                <Tooltip
                                                  title="Alcoholic Beverage"
                                                  arrow
                                                  placement="top"
                                                >
                                                  <DrinkIcon
                                                    sx={{
                                                      color: "#e74c3c",
                                                      fontSize: 14,
                                                    }}
                                                  />
                                                </Tooltip>
                                              ) : item.category === "Non Alcoholic" ? (
                                                <Tooltip
                                                  title="Non-Alcoholic Beverage"
                                                  arrow
                                                  placement="top"
                                                >
                                                  <NoDrinksIcon
                                                    sx={{
                                                      color: "#2ecc71",
                                                      fontSize: 14,
                                                    }}
                                                  />
                                                </Tooltip>
                                              ) : null)}
                                          </Box>

                                          {/* Quantity and Price */}
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            Quantity: {item.quantity} × SGD{" "}
                                            {formatPrice(item.price)}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Box>
                                  ))}
                              </Box>
                            )}
                        </Paper>
                      </Grid>

                      {/* Guest Details - Right Side (Swapped from left) */}
                      <Grid item xs={12} md={6}>
                        <Paper
                          elevation={1}
                          sx={{
                            p: 1.5,
                            borderRadius: "8px",
                            height: "100%",
                          }}
                        >
                          <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                            <PersonIcon sx={{ color: "#3554D1", mr: 1, fontSize: 20 }} />
                            <Typography variant="body1" sx={{ fontWeight: "bold", fontSize: "1.05rem" }}>
                              Guest Details
                            </Typography>
                          </Box>

                          <Divider sx={{ my: 0.75 }} />

                          {/* Adults Section */}
                          <Box sx={{
                            p: 1.25,
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "space-between",
                            borderRadius: "6px",
                            backgroundColor: "rgba(53, 84, 209, 0.05)",
                            mb: 1.5,
                          }}>
                            <Typography variant="body1" sx={{ fontWeight: "medium", fontSize: "1rem" }}>
                              Adults
                            </Typography>
                            <Badge
                              badgeContent={data?.adultCount || 0}
                              color="primary"
                              sx={{
                                "& .MuiBadge-badge": {
                                  fontSize: "0.8rem",
                                  height: "20px",
                                  minWidth: "20px",
                                  fontWeight: "bold",
                                },
                              }}
                              overlap="circular"
                            >
                              <Avatar sx={{ bgcolor: "#3554D1", width: 32, height: 32 }}>
                                <PersonIcon sx={{ fontSize: 18, color: "white" }} />
                              </Avatar>
                            </Badge>
                          </Box>

                          {/* Children Section - Only show if there are any */}
                          {data?.childCount > 0 && (
                            <Box sx={{
                              p: 1,
                              borderRadius: "6px",
                              border: "1px solid rgba(255, 152, 0, 0.3)",
                              bgcolor: "rgba(255, 152, 0, 0.05)",
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                              mb: 1.5,
                            }}>
                              <Typography variant="body1" sx={{ fontSize: "1rem" }}>
                                Children
                              </Typography>
                              <Chip
                                size="medium"
                                label={data.childCount}
                                color="warning"
                                icon={<ChildCareIcon sx={{ fontSize: 18 }} />}
                                sx={{ height: "24px", fontSize: "0.9rem" }}
                              />
                            </Box>
                          )}
                          
                          {/* Customer Information Section - Added below Guest Details */}
                          <Box sx={{ mt: 2 }}>
                            <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                              <PersonIcon sx={{ color: "#3554D1", mr: 1, fontSize: 20 }} />
                              <Typography variant="body1" sx={{ fontWeight: "bold", fontSize: "1.05rem" }}>
                                Customer Information
                              </Typography>
                            </Box>
                            
                            <Divider sx={{ my: 0.75 }} />
                            
                            <Box sx={{ mt: 1.5, display: 'flex', flexDirection: 'column', gap: 1.5 }}>
                              {/* Full Name */}
                              <Box sx={{ 
                                display: 'flex', 
                                alignItems: 'center', 
                                justifyContent: 'space-between',
                                p: 1,
                                backgroundColor: 'white',
                                borderRadius: '6px',
                                border: '1px solid rgba(53, 84, 209, 0.1)',
                              }}>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <PersonIcon sx={{ color: '#3554D1', mr: 1.5, fontSize: 20 }} />
                                  <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.95rem' }}>
                                    Full Name
                                  </Typography>
                                </Box>
                                <Typography variant="body1" sx={{ fontWeight: 500, fontSize: '1rem' }}>
                                  {userInfo?.fullName || 'N/A'}
                                </Typography>
                              </Box>

                              {/* Email */}
                              <Box sx={{ 
                                display: 'flex', 
                                alignItems: 'center', 
                                justifyContent: 'space-between',
                                p: 1,
                                backgroundColor: 'white',
                                borderRadius: '6px',
                                border: '1px solid rgba(53, 84, 209, 0.1)',
                              }}>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <EmailIcon sx={{ color: '#3554D1', mr: 1.5, fontSize: 20 }} />
                                  <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.95rem' }}>
                                    Email
                                  </Typography>
                                </Box>
                                <Typography variant="body1" sx={{ fontWeight: 500, fontSize: '1rem' }}>
                                  {userInfo?.email || 'N/A'}
                                </Typography>
                              </Box>

                              {/* Phone */}
                              <Box sx={{ 
                                display: 'flex', 
                                alignItems: 'center', 
                                justifyContent: 'space-between',
                                p: 1,
                                backgroundColor: 'white',
                                borderRadius: '6px',
                                border: '1px solid rgba(53, 84, 209, 0.1)',
                              }}>
                                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                  <LocalPhoneIcon sx={{ color: '#3554D1', mr: 1.5, fontSize: 20 }} />
                                  <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.95rem' }}>
                                    Phone
                                  </Typography>
                                </Box>
                                <Typography variant="body1" sx={{ fontWeight: 500, fontSize: '1rem', display: 'flex', alignItems: 'center' }}>
                                  {userInfo?.countryCode && (
                                    <Box component="span" sx={{ 
                                      color: 'rgba(0, 0, 0, 0.6)', 
                                      mr: 0.5, 
                                      fontWeight: 400,
                                      fontSize: '0.95rem',
                                      bgcolor: 'rgba(0, 0, 0, 0.05)',
                                      px: 0.8,
                                      py: 0.2,
                                      borderRadius: '4px'
                                    }}>
                                      {userInfo.countryCode}
                                    </Box>
                                  )}
                                  {userInfo?.phone || 'N/A'}
                                </Typography>
                              </Box>

                              {/* Country */}
                             
                            </Box>
                          </Box>
                        </Paper>
                      </Grid>
                    </Grid>
                  </CardContent>
                </RoomCard>
              );
            })}

            {/* Total Price Summary Card */}
            <Card
              elevation={2}
              sx={{
                mt: 2,
                borderRadius: "10px",
                overflow: "hidden",
                transition: "transform 0.2s ease, box-shadow 0.2s ease",
                "&:hover": {
                  transform: "translateY(-3px)",
                  boxShadow: "0 6px 16px rgba(0,0,0,0.1)",
                },
                background:
                  "linear-gradient(135deg, rgba(53, 84, 209, 0.03) 0%, rgba(53, 84, 209, 0.08) 100%)",
              }}
            >
              <CardContent sx={{ p: 2 }}>
                <Typography
                  variant="subtitle1"
                  sx={{
                    fontWeight: "bold",
                    mb: 1.5,
                    display: "flex",
                    alignItems: "center",
                    fontSize: "1rem",
                  }}
                >
                  <Box
                    component="span"
                    sx={{
                      display: "inline-flex",
                      alignItems: "center",
                      justifyContent: "center",
                      width: 26,
                      height: 26,
                      borderRadius: "50%",
                      backgroundColor: "#3554D1",
                      color: "white",
                      mr: 1,
                      fontSize: "0.9rem",
                    }}
                  >
                    $
                  </Box>
                  Total Price Summary
                </Typography>

                <Grid container spacing={2}>
                  <Grid item xs={12} md={6}>
                    <Paper
                      elevation={0}
                      sx={{
                        p: 1.5,
                        borderRadius: "8px",
                        bgcolor: "rgba(255,255,255,0.8)",
                        border: "1px solid rgba(53, 84, 209, 0.1)",
                      }}
                    >
                      <Typography
                        variant="body2"
                        color="textSecondary"
                        sx={{ mb: 0.5, display: "block", fontSize: "0.95rem" }}
                      >
                        Booking Type
                      </Typography>

                      {Array.isArray(
                        restaurantBookings?.[0]?.data?.[0]?.priceTypes
                      ) &&
                      restaurantBookings?.[0]?.data?.[0]?.priceTypes?.length >
                        0 ? (
                        restaurantBookings[0].data[0].priceTypes[0] ===
                          "travClicks" ||
                        restaurantBookings[0].data[0].priceTypes[0] ===
                          "travclicks" ? (
                          <Chip
                            label="Travclicks"
                            size="small"
                            sx={{
                              bgcolor: alpha("#009688", 0.1),
                              color: "#00796B",
                              fontWeight: "medium",
                              height: "24px",
                              fontSize: "0.75rem",
                              border: `1px solid ${alpha("#009688", 0.3)}`,
                              "& .MuiChip-label": {
                                px: 1,
                              },
                            }}
                          />
                        ) : restaurantBookings[0].data[0].priceTypes[0] ===
                          "dmc" ? (
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              gap: 0.8,
                            }}
                          >
                            {dmcLogo ? (
                              <Avatar
                                src={dmcLogo}
                                alt={`${dmcCompanyName} Logo`}
                                sx={{ width: 24, height: 24 }}
                              />
                            ) : (
                              <Avatar
                                sx={{
                                  width: 24,
                                  height: 24,
                                  bgcolor: alpha("#FF9800", 0.2),
                                  color: "#E65100",
                                  fontSize: "12px",
                                }}
                              >
                                {dmcCompanyName?.charAt(0) || "D"}
                              </Avatar>
                            )}
                            <Typography
                              variant="body2"
                              fontWeight="medium"
                              color="#E65100"
                              sx={{ }}
                            >
                              {`${dmcCompanyName}'s Mode`}
                            </Typography>
                          </Box>
                        ) : (
                          <Typography variant="body2" fontWeight="medium">
                            {capitalizeFirstLetter(
                              restaurantBookings[0].data[0].priceTypes[0]
                            )}
                          </Typography>
                        )
                      ) : (
                        <Typography variant="body2">N/A</Typography>
                      )}
                    </Paper>
                  </Grid>

                  <Grid item xs={12} md={6}>
                    <Paper
                      elevation={0}
                      sx={{
                        p: 1.5,
                        borderRadius: "8px",
                        bgcolor: "rgba(255,255,255,0.8)",
                        border: "1px solid rgba(53, 84, 209, 0.1)",
                      }}
                    >
                      <Box
                        sx={{
                          display: "flex",
                          flexDirection: "column",
                          gap: 0.75,
                        }}
                      >
                        <Box
                          sx={{
                            p: 1.5,
                            background:
                              "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
                            borderRadius: "8px",
                            color: "white",
                            boxShadow: "0 3px 8px rgba(53, 84, 209, 0.2)",
                            mb: 1,
                          }}
                        >
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                              mb: 1,
                            }}
                          >
                            <Typography
                              variant="body1"
                              sx={{
                                fontWeight: "bold",
                                fontSize: "1rem",
                                color: "white",
                              }}
                            >
                              Final Price
                            </Typography>

                            {/* <Chip
                              size="small"
                              label="Tax for display only"
                              color="warning"
                              variant="outlined"
                              sx={{
                                height: "22px",
                                fontSize: "0.75rem",
                                backgroundColor: "rgba(255, 255, 255, 0.1)",
                                color: "white",
                                borderColor: "rgba(255, 255, 255, 0.3)",
                              }}
                            /> */}
                          </Box>

                          {(() => {
                            // Calculate meal price
                            const mealPrice = restaurantBookings?.[0]?.data?.[0]?.totalPrice || 0;
                            
                            // Add transport price if available
                            let transportPrice = 0;
                            const transport = restaurantBookings?.[0]?.data?.[0]?.transport;
                            
                            if (transport) {
                              const adultCount = restaurantBookings?.[0]?.data?.[0]?.adultCount || 0;
                              const childCount = restaurantBookings?.[0]?.data?.[0]?.childCount || 0;
                              const totalPax = adultCount + childCount;
                              
                              // If shared transport, multiply price by total pax
                              if (transport.transport_type === 'shared') {
                                transportPrice = transport.price * totalPax;
                              } else {
                                // For private transport, use price as is
                                transportPrice = transport.price;
                              }
                            }
                            
                            // Calculate total base price (without tax)
                            const basePrice = mealPrice + transportPrice;
                            
                            // Format in current currency
                            const convertedPrice = basePrice * exchangeRate;
                            
                            // Tax amounts (for display only)
                            const taxAmount = Math.ceil((convertedPrice * currentTax) / 100);
                            
                            // Grand total (with tax - for display only)
                            const grandTotal = convertedPrice + taxAmount;

                            // Calculate USD equivalent
                            const usdPrice = basePrice * usdExchangeRate;
                            const usdTaxAmount = Math.ceil((usdPrice * usdTax) / 100);
                            
                            // SGD is the base price already
                            const sgdPrice = basePrice;
                            const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);

                            return (
                              <>
                                {PriceHide === "0" ? (
                                  // Show prices when PriceHide is "0"
                                  <>
                                    <Box sx={{ 
                                      display: 'flex', 
                                      justifyContent: 'space-between', 
                                      alignItems: 'center',
                                        py: 0.5,
                                      }}>
                                        <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)' }}>
                                        Meal Price
                                      </Typography>
                                        <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                        {currencyCode} {formatPrice(Math.ceil(mealPrice * exchangeRate))}
                                        </Typography>
                                    </Box>

                                    {/* {transportPrice > 0 && (
                                      <Box sx={{ 
                                        display: 'flex', 
                                        justifyContent: 'space-between', 
                                        alignItems: 'center',
                                        py: 0.5,
                                      }}>
                                        <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)' }}>
                                          Transport ({transport.vehicle_name}, {transport.transport_type})
                                      </Typography>
                                        <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                          {currencyCode} {formatPrice(transportPrice * exchangeRate)}
                                        </Typography>
                                      </Box>
                                    )} */}
                                      
                                    {/* Base price (what's sent to the server) */}
                                      {/* <Box sx={{ 
                                        display: 'flex', 
                                        justifyContent: 'space-between', 
                                        alignItems: 'center',
                                        py: 0.5,
                                      mt: 0.5,
                                      borderTop: '1px dotted rgba(255, 255, 255, 0.3)',
                                      }}>
                                      <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.8)', fontWeight: 500 }}>
                                          Base Price (Without Tax)
                                        </Typography>
                                      <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)', fontWeight: 500 }}>
                                        {currencyCode} {formatPrice(convertedPrice)}
                                        </Typography>
                                      </Box> */}
                                      
                                    {/* Tax amount - display only */}
                                      {/* <Box sx={{ 
                                        display: 'flex', 
                                        justifyContent: 'space-between', 
                                        alignItems: 'center',
                                        py: 0.5,
                                      }}>
                                      <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)', fontStyle: 'italic' }}>
                                        + Tax ({currentTax}%)
                                        </Typography>
                                      <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.8)' }}>
                                        {currencyCode} {formatPrice(taxAmount)}
                                        </Typography>
                                      </Box> */}
                                      
                                    {/* Total with tax (for display only) */}
                                      {/* <Box sx={{ 
                                        display: 'flex', 
                                        justifyContent: 'space-between', 
                                        alignItems: 'center',
                                        py: 0.5,
                                        mt: 0.5,
                                      pt: 0.5,
                                      borderTop: '1px solid rgba(255, 255, 255, 0.3)',
                                      }}>
                                      <Typography sx={{ fontSize: '0.9rem', color: 'white', fontWeight: 'bold' }}>
                                        Total with Tax
                                        </Typography>
                                      <Typography sx={{ fontSize: '1rem', color: 'white', fontWeight: 'bold' }}>
                                        {currencyCode} {formatPrice(grandTotal)}
                                        </Typography>
                                    </Box> */}
                                    
                                    {/* USD Equivalent - always show if not USD */}
                                    {currencyCode !== 'USD' && (
                                          <Box sx={{ 
                                            display: 'flex', 
                                            justifyContent: 'space-between', 
                                            alignItems: 'center',
                                            py: 0.5,
                                        mt: 0.5,
                                          }}>
                                        <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)' }}>
                                          USD Total 
                                              </Typography>
                                        <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.8)' }}>
                                          USD {formatPrice(Math.ceil(usdPrice))}
                                            </Typography>
                                          </Box>
                                        )}
                                        
                                    {/* SGD Equivalent - always show if not SGD */}
                                    {currencyCode !== 'SGD' && (
                                          <Box sx={{ 
                                            display: 'flex', 
                                            justifyContent: 'space-between', 
                                            alignItems: 'center',
                                            py: 0.5,
                                          }}>
                                        <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)' }}>
                                          SGD Total 
                                              </Typography>
                                        <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.8)' }}>
                                          SGD {formatPrice(Math.ceil(sgdPrice))}
                                            </Typography>
                                      </Box>
                                    )}
                                  </>
                                ) : (
                                  // Show message when PriceHide is not "0"
                                  <Box sx={{ 
                                    display: 'flex', 
                                    justifyContent: 'center', 
                                    alignItems: 'center',
                                    py: 2,
                                  }}>
                                    <Typography sx={{ fontSize: '1rem', color: 'white', fontWeight: 'bold' }}>
                                      Price available on request
                                    </Typography>
                                  </Box>
                                )}
                              </>
                            );
                          })()}
                        </Box>
                      </Box>
                    </Paper>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Box>
        ) : (
          <Box
            sx={{
              p: 3,
              textAlign: "center",
              borderRadius: 2,
              backgroundColor: "rgba(53, 84, 209, 0.03)",
            }}
          >
            No booking details available.
          </Box>
        )}
      </Box>

      {/* Action Buttons - More Compact */}
      <Box
        sx={{
          display: "flex",
          justifyContent: "center",
          gap: "10px",
          mt: 3,
        }}
      >
        {/* Only show booking button if not in enquiry mode */}
        {bookingType !== 'enquiry' && (
          <Button
            variant="contained"
            onClick={handleFinalSubmit}
            startIcon={<Box component="span">✓</Box>}
            size="medium"
            sx={{
              borderRadius: "6px",
              px: 2.5,
              py: 1,
              background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
              fontWeight: "bold",
              fontSize: "1.05rem",
              textTransform: "none",
            }}
            disabled={isSubmitting}
          >
            {isSubmitting ? 'Booking...' : 'Book Now'}
          </Button>
        )}

        {/* Only show enquiry button if not in booking mode */}
        {bookingType !== 'booking' && (
          <Button
            variant="outlined"
            onClick={handleEnquirySubmit}
            size="medium"
            sx={{
              borderRadius: "6px",
              px: 2.5,
              py: 1,
              borderColor: "#3554D1",
              color: "#3554D1",
              fontWeight: "bold",
              fontSize: "1.05rem",
              textTransform: "none",
              "&:hover": {
                borderColor: "#3554D1",
                backgroundColor: "rgba(53, 84, 209, 0.05)",
              },
            }}
            disabled={isEnquiring}
          >
            {isEnquiring ? 'Enquiring...' : 'Make an Enquiry'}
          </Button>
        )}
      </Box>
    </Box>
  );
}
