import React, { useEffect, useState, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  selectUserInfo,
  selectBookingResponse,
  setBookingResponse,
  setUserInfo,
} from "../../../slice/common/customerInfo";
import {
  createBooking,
  setAttractionService,
} from "../../../slice/attractions/attractionSlice";
import { setDateService } from "../../../slice/common/dateServicesSlice";
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
  TextField,
  InputAdornment,
  Button,
} from "@mui/material";
import PersonIcon from "@mui/icons-material/Person";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import AttractionsIcon from "@mui/icons-material/Attractions";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import ElderlyIcon from "@mui/icons-material/Elderly";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import EmailIcon from "@mui/icons-material/Email";
import PhoneIcon from "@mui/icons-material/Phone";
import PublicIcon from "@mui/icons-material/Public";
import LocalPhoneIcon from "@mui/icons-material/LocalPhone";
import dayjs from "dayjs";
import { alpha } from "@mui/material/styles";
import {
  setBookingType,
  setBookingMode,
  setIsNavigating,
} from "../../../slice/common/commonSlice";

// Styled components for cards and headers
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

export default function Index2() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const initialLoadRef = useRef(true);

  // Add loading states for buttons
  const [isBookingLoading, setIsBookingLoading] = useState(false);
  const [isEnquiryLoading, setIsEnquiryLoading] = useState(false);

  // Get the bookingMode from Redux
  const bookingMode = useSelector((state) => state.common.bookingMode);

  // Get the mode from the attraction booking data
  const attractionBookingMode = useSelector(
    (state) => state.attractions?.attractionBookings?.[0]?.data?.[0]?.mode
  );

  // Add an effect to ensure the mode is consistently set
  useEffect(() => {
    // If we have attraction booking data but no bookingMode set,
    // or if they're inconsistent, update Redux
    if (attractionBookingMode && attractionBookingMode !== bookingMode) {
      // console.log(`Updating bookingMode from ${bookingMode} to ${attractionBookingMode}`);
      dispatch(setBookingMode(attractionBookingMode));
    }
  }, [attractionBookingMode, bookingMode, dispatch]);

  // Add debugging for attractionBookingMode
  // useEffect(() => {
  //   console.log('attractionBookingMode complete object:', attractionBookingMode);
  //   if (attractionBookingMode?.prices) {
  //     console.log('Found prices object in attractionBookingMode:', attractionBookingMode.prices);
  //   }
  // }, [attractionBookingMode]);

  // Get data from Redux state
  const userInfo = useSelector(selectUserInfo);
  // console.log('userInfo in index2:', userInfo);

  const attractionBookings = useSelector(
    (state) => state.attractions?.attractionBookings || []
  );
  console.log("attractionBookings in index2:", attractionBookings);
  // console.log('Current bookingMode in index2:', bookingMode);

  const attractionDetails = useSelector(
    (state) => state.attractions.attractionDetails
  );
  // console.log('attractionDetails srk................................................',attractionDetails);

  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  const bookingType = useSelector((state) => state.common.bookingType);

  // console.log('DmcName, DmcLogo in index2:', { DmcName, DmcLogo });

  // State for enquiry form
  const [showEnquiry, setShowEnquiry] = useState(false);
  const [enquiryAmount, setEnquiryAmount] = useState(() => {
    return attractionBookings?.[0]?.data?.[0]?.totalPrice || "";
  });
  const [enquiryComment, setEnquiryComment] = useState("");
  const [commentError, setCommentError] = useState(false);

  // Add a state to control if we should validate data
  // const [shouldValidate, setShouldValidate] = useState(false);

  // Redirect if no user info
  useEffect(() => {
    if (!userInfo || !userInfo.fullName) {
      navigate("/dashboard/db-dashboard/tour-single/:id");
    }
  }, [userInfo, navigate]);

  // Update enquiry amount when bookings change
  useEffect(() => {
    setEnquiryAmount(attractionBookings?.[0]?.data?.[0]?.totalPrice || "");
  }, [attractionBookings]);

  // Add an effect to synchronize bookingMode with attractionBookings
  useEffect(() => {
    if (
      attractionBookings?.length > 0 &&
      attractionBookings[0]?.data?.length > 0
    ) {
      const bookingData = attractionBookings[0].data[0];
      if (bookingData.mode && bookingData.mode !== bookingMode) {
        // console.log(`Updating bookingMode from ${bookingMode} to ${bookingData.mode} based on attractionBookings`);
        dispatch(setBookingMode(bookingData.mode));
      }
    }
  }, [attractionBookings, bookingMode, dispatch]);

  // Format date function
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

  // Capitalize first letter helper function
  const capitalizeFirstLetter = (str) => {
    return typeof str === "string" && str.length > 0
      ? str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
      : "N/A";
  };

  // Add a function to safely go back to the details page
  const handleBackToDetails = () => {
    // Get the tour ID from tourdetails
    const tourId = tourdetails?.tour_id || "";

    // Navigate with the right ID parameter
    navigate(`/dashboard/db-dashboard/tour-single/${tourId || ":id"}`, {
      state: { fromBackButton: true },
    });
  };

  // Submit booking function
  const handleFinalSubmit = async () => {
    try {
      // Set local loading state
      setIsBookingLoading(true);

      if (!userInfo || !userInfo.fullName) {
        toast.error(
          "Customer information is missing. Please fill out the form first."
        );
        setIsBookingLoading(false);
        return;
      }

      // Create a formData object from userInfo to match CustomerInfo.jsx exactly
      const formData = { ...userInfo };

      // Ensure phone and countryCode are correctly formatted
      // console.log('Phone details before submission:', {
      //   phone: formData.phone,
      //   countryCode: formData.countryCode,
      //   fullUserInfo: formData
      // });

      // console.log('Starting booking submission from INDEX2.JSX - this should be distinctive in logs');

      // Set navigation state to true before API call but AFTER we've validated data
      //dispatch(setIsNavigating(true));

      const isPackageBooking = attractionBookings?.[0]?.data?.[0]?.package_type === 1;
      
      // Get package details if it's a package booking
      const packageDetails = isPackageBooking ? {
        package_id: attractionBookings?.[0]?.data?.[0]?.package_attraction_id,
        package_name: attractionBookings?.[0]?.data?.[0]?.ticketName,
        package_attractions: attractionBookings?.[0]?.data?.[0]?.packageDetails?.attractions || [],
        package_description: attractionBookings?.[0]?.data?.[0]?.packageDetails?.description || "",
        package_adult_price: attractionBookings?.[0]?.data?.[0]?.dmc_adult_price || 0,
        package_child_price: attractionBookings?.[0]?.data?.[0]?.dmc_child_price || 0,
        package_senior_price: attractionBookings?.[0]?.data?.[0]?.dmc_senior_price || 0,
        package_total_attractions: attractionBookings?.[0]?.data?.[0]?.packageDetails?.attractions?.length || 0
      } : null;
      
      const bookingDetails = {
        agent_id: Cookies.get("AgentId") || "0",
        data: [
          {
            ...formData, // Use formData instead of userInfo
            bookingDate: attractionBookings?.[0]?.data?.[0]?.bookingDate,
            visitTime: attractionBookings?.[0]?.data?.[0]?.visitTime,
            adultCount: attractionBookings?.[0]?.data?.[0]?.adultCount,
            childCount: attractionBookings?.[0]?.data?.[0]?.childCount || 0,
            seniorCount: attractionBookings?.[0]?.data?.[0]?.seniorCount || 0,
            AttractionId:
              attractionBookings?.[0]?.data?.[0]?.AttractionId ||
              attractionDetails?.id ||
              0,
            AttractionName: attractionBookings?.[0]?.data?.[0]?.AttractionName,
            ticketId: attractionBookings?.[0]?.data?.[0]?.ticketId,
            ticketName: attractionBookings?.[0]?.data?.[0]?.ticketName,
            ticket_details: attractionBookings?.[0]?.data?.[0]
              ?.ticket_details || {
              adult_price:
                attractionBookings?.[0]?.data?.[0]?.dmc_adult_price || 0,
              child_price:
                attractionBookings?.[0]?.data?.[0]?.dmc_child_price || 0,
              senior_price:
                attractionBookings?.[0]?.data?.[0]?.dmc_senior_price || 0,
              description:
                attractionBookings?.[0]?.data?.[0]?.description || "",
            },
            transport: attractionBookings?.[0]?.data?.[0]?.transport || null,
            Selection: (() => {
              const transport = attractionBookings?.[0]?.data?.[0]?.transport;
              if (!transport) return "withoutTransport";
              return transport.type === "private" ? "withPrivate" : "withShare";
            })(),
            mode:
              attractionBookingMode?.prices?.mode ||
              attractionDetails?.prices?.mode,
            totalPrice: attractionBookings?.[0]?.data?.[0]?.totalPrice,
            nri:
              attractionBookings?.[0]?.data?.[0]?.ticket_details?.nri ||
              "residential",
            price: attractionBookings?.[0]?.data?.[0]?.totalPrice,
            prices: {
              price: attractionBookings?.[0]?.data?.[0]?.totalPrice,
            },
            dmc_id: attractionBookings?.[0]?.data?.[0]?.dmc_id || null,
            bookingType: "booking",
            package_type: attractionBookings?.[0]?.data?.[0]?.package_type || 0,
            package_attraction_id: attractionBookings?.[0]?.data?.[0]?.package_attraction_id || null,
            ...(isPackageBooking && packageDetails && { package_details: packageDetails })
          },
        ],
        tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
        type: isPackageBooking ? "attraction_package" : "attraction",
        bookingType: "booking",
        // Add a source flag to track which component sent the request
        requestSource: "index2",
      };

      // Force a direct field for PHP's $price variable
      bookingDetails.price = bookingDetails.data[0].totalPrice;

      // Add debug logging
      // console.log('userInfo:', userInfo);
      // console.log('Submitting booking details:', JSON.stringify(bookingDetails, null, 2));
      // console.log('attractionBookings:', attractionBookings);
      // console.log('attractionBookingMode before submit:', attractionBookingMode);
      // if (attractionBookingMode?.prices) {
      //   console.log('Price from attractionBookingMode.prices:', attractionBookingMode.prices);
      // }
      // console.log('Price field check:', {
      //   totalPrice: bookingDetails.data[0].totalPrice,
      //   price: bookingDetails.data[0].price,
      //   directPrice: bookingDetails.price,
      //   priceFromPrices: bookingDetails.data[0].prices?.price,
      //   mode: bookingDetails.data[0].mode,
      //   // The PHP error is looking for $price which should come from totalPrice
      //   phpInfo: "PHP file: TourController.php, Line: 986, Undefined variable $price"
      // });

      // Create a proxy function to monitor the exact data sent
      // const createBookingWithMonitoring = async (bookingData) => {
      //   // console.log('BEFORE DISPATCH - Final booking data structure:', JSON.stringify(bookingData, null, 2));
      //   try {
      //     const result = await dispatch(createBooking(bookingData)).unwrap();
      //     // console.log('AFTER DISPATCH - Booking succeeded with result:', result);
      //     return result;
      //   } catch (error) {
      //     console.error('AFTER DISPATCH - Booking failed with error:', error);
      //     throw error;
      //   }
      // };

      // Use the monitoring function to make the API call
      //const response = await createBookingWithMonitoring(bookingDetails);
      const response = await dispatch(createBooking(bookingDetails)).unwrap();

      if (response?.service?.date_service) {
        dispatch(setDateService(response.service.date_service));
        dispatch(setAttractionService(response.service.data));
        dispatch(setUserInfo(formData));
        dispatch(setBookingResponse(response));
        dispatch(setBookingType(response?.order?.bookingType));

        toast.success("Booking successful!", {
          position: "top-center",
          autoClose: 3000,
        });

        // Navigate to thank you page, but don't reset isNavigating state until
        // AFTER navigation is complete to avoid flashing components
        navigate("/dashboard/db-dashboard/attraction-thank-you", {
          state: {
            bookingResponse: response,
            from: "booking",
          },
        });

        // We leave the loading state as true since we're navigating away
        // It will reset when the component unmounts
      } else {
        setIsBookingLoading(false); // Reset local loading state on error
        //dispatch(setIsNavigating(false)); // Reset navigation state on error
        throw new Error("Invalid response received from the server.");
      }
    } catch (error) {
      console.error("Error during submission:", error);
      console.error("Error details:", JSON.stringify(error, null, 2));

      // Reset loading states on error
      setIsBookingLoading(false);
      //dispatch(setIsNavigating(false));

      toast.error(
        error.message || "Something went wrong. Please try again later.",
        {
          position: "top-center",
          autoClose: 3000,
        }
      );
    }
  };

  // Handle amount change for enquiry
  const handleAmountChange = (e) => {
    const newValue = parseFloat(e.target.value);
    const totalPrice = attractionBookings?.[0]?.data?.[0]?.totalPrice || 0;

    // Don't allow values above the total price or below 1
    if (newValue <= totalPrice && newValue > 0) {
      setEnquiryAmount(newValue);
    }
  };

  // Submit enquiry function
  const handleEnquirySubmit = async () => {
    try {
      // Set local loading state
      setIsEnquiryLoading(true);

      if (!userInfo || !userInfo.fullName) {
        toast.error(
          "Customer information is missing. Please fill out the form first."
        );
        setIsEnquiryLoading(false);
        return;
      }

      // Create a formData object from userInfo to match CustomerInfo.jsx exactly
      const formData = { ...userInfo };

      // Ensure phone and countryCode are correctly formatted
      // console.log('Phone details before enquiry submission:', {
      //   phone: formData.phone,
      //   countryCode: formData.countryCode,
      //   fullUserInfo: formData
      // });

      // console.log('Starting enquiry submission from INDEX2.JSX');

      // Set navigation state to true before API call but AFTER we've validated data
      //dispatch(setIsNavigating(true));

      const isPackageBooking = attractionBookings?.[0]?.data?.[0]?.package_type === 1;
      
      // Get package details if it's a package booking
      const packageDetails = isPackageBooking ? {
        package_id: attractionBookings?.[0]?.data?.[0]?.package_attraction_id,
        package_name: attractionBookings?.[0]?.data?.[0]?.ticketName,
        package_attractions: attractionBookings?.[0]?.data?.[0]?.packageDetails?.attractions || [],
        package_description: attractionBookings?.[0]?.data?.[0]?.packageDetails?.description || "",
        package_adult_price: attractionBookings?.[0]?.data?.[0]?.dmc_adult_price || 0,
        package_child_price: attractionBookings?.[0]?.data?.[0]?.dmc_child_price || 0,
        package_senior_price: attractionBookings?.[0]?.data?.[0]?.dmc_senior_price || 0,
        package_total_attractions: attractionBookings?.[0]?.data?.[0]?.packageDetails?.attractions?.length || 0
      } : null;
      
      const enquiryDetails = {
        agent_id: Cookies.get("AgentId") || "0",
        data: [
          {
            ...formData, // Use formData instead of userInfo
            bookingDate: attractionBookings?.[0]?.data?.[0]?.bookingDate,
            visitTime: attractionBookings?.[0]?.data?.[0]?.visitTime,
            adultCount: attractionBookings?.[0]?.data?.[0]?.adultCount,
            childCount: attractionBookings?.[0]?.data?.[0]?.childCount || 0,
            seniorCount: attractionBookings?.[0]?.data?.[0]?.seniorCount || 0,
            AttractionId:
              attractionBookings?.[0]?.data?.[0]?.AttractionId ||
              attractionDetails?.id ||
              0,
            AttractionName: attractionBookings?.[0]?.data?.[0]?.AttractionName,
            ticketId: attractionBookings?.[0]?.data?.[0]?.ticketId,
            ticketName: attractionBookings?.[0]?.data?.[0]?.ticketName,
            ticket_details: attractionBookings?.[0]?.data?.[0]
              ?.ticket_details || {
              adult_price:
                attractionBookings?.[0]?.data?.[0]?.dmc_adult_price || 0,
              child_price:
                attractionBookings?.[0]?.data?.[0]?.dmc_child_price || 0,
              senior_price:
                attractionBookings?.[0]?.data?.[0]?.dmc_senior_price || 0,
              description:
                attractionBookings?.[0]?.data?.[0]?.description || "",
            },
            transport: attractionBookings?.[0]?.data?.[0]?.transport || null,
            Selection: (() => {
              const transport = attractionBookings?.[0]?.data?.[0]?.transport;
              if (!transport) return "withoutTransport";
              return transport.type === "private" ? "withPrivate" : "withShare";
            })(),
            mode:
              attractionBookingMode?.prices?.mode ||
              attractionDetails?.prices?.mode,
            totalPrice: attractionBookings?.[0]?.data?.[0]?.totalPrice,
            nri:
              attractionBookings?.[0]?.data?.[0]?.ticket_details?.nri ||
              "residential",
            price: attractionBookings?.[0]?.data?.[0]?.totalPrice,
            prices: {
              price: attractionBookings?.[0]?.data?.[0]?.totalPrice,
            },
            dmc_id: attractionBookings?.[0]?.data?.[0]?.dmc_id || null,
            bookingType: "enquiry",
            package_type: attractionBookings?.[0]?.data?.[0]?.package_type || 0,
            package_attraction_id: attractionBookings?.[0]?.data?.[0]?.package_attraction_id || null,
            ...(isPackageBooking && packageDetails && { package_details: packageDetails })
          },
        ],
        tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
        type: isPackageBooking ? "attraction_package" : "attraction",
        bookingType: "enquiry",
      };

      // Force a direct field for PHP's $price variable
      enquiryDetails.price = enquiryDetails.data[0].totalPrice;

      // Add debug logging
      // console.log('Submitting enquiry details:', JSON.stringify(enquiryDetails, null, 2));

      const response = await dispatch(createBooking(enquiryDetails)).unwrap();

      if (response?.service?.date_service) {
        dispatch(setDateService(response.service.date_service));
        dispatch(setAttractionService(response.service.data));
        dispatch(setUserInfo(formData));
        dispatch(setBookingResponse(response));
        dispatch(setBookingType(response?.order?.bookingType));

        toast.success("Enquiry submitted successfully!", {
          position: "top-center",
          autoClose: 3000,
        });

        // Clear form and close enquiry section
        setShowEnquiry(false);
        setEnquiryComment("");

        // Navigate directly without nested timeouts to avoid flashing
        navigate("/dashboard/db-dashboard/attraction-thank-you", {
          state: {
            bookingResponse: response,
            from: "enquiry",
          },
        });

        // We leave the loading state as true since we're navigating away
        // It will reset when the component unmounts
      } else {
        setIsEnquiryLoading(false); // Reset local loading state on error
        //dispatch(setIsNavigating(false)); // Reset navigation state on error
        throw new Error("Invalid response received from the server.");
      }
    } catch (error) {
      console.error("Error during enquiry submission:", error);

      // Reset loading states on error
      setIsEnquiryLoading(false);
      //dispatch(setIsNavigating(false));

      toast.error(
        error.message || "Something went wrong. Please try again later.",
        {
          position: "top-center",
          autoClose: 3000,
        }
      );
    }
  };

  // Get booking data
  const bookingData = attractionBookings?.[0]?.data?.[0] || {};
  const totalPrice = bookingData?.totalPrice || 0;

  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // const PriceHide = 1;

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
      {/* Back Button */}
      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          mb: 3,
        }}
      >
        <Button
          startIcon={<Box component="span">←</Box>}
          onClick={handleBackToDetails}
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
          <AttractionsIcon sx={{ fontSize: 24, mr: 1 }} />
          <Typography
            variant="h6"
            sx={{ fontWeight: "bold", fontSize: "1.1rem" }}
          >
            Attraction Booking Details
          </Typography>
        </Box>
        {/* <Chip 
          label={bookingData?.mode || 'Standard'} 
          size="small"
          sx={{ 
            backgroundColor: 'rgba(255, 255, 255, 0.2)', 
            color: 'white',
            fontWeight: 'bold',
            height: '24px',
            fontSize: '0.8rem'
          }} 
        /> */}
      </Box>

      {/* Attraction Image and Name Section - Modern Design */}
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
                attractionDetails?.master_image ||
                "https://via.placeholder.com/300x300?text=Attraction+Image"
              }
              alt={attractionDetails?.name || "Attraction"}
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
            {attractionDetails?.name && (
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
                  label={attractionDetails.name}
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
                <AttractionsIcon
                  sx={{ color: "#3554D1", mr: 1, fontSize: 20 }}
                />
                <Typography
                  variant="h6"
                  sx={{ fontWeight: "bold", fontSize: "1.1rem" }}
                >
                  {attractionDetails?.name || "Attraction Name"}
                </Typography>
              </Box>

              <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                <LocationOnIcon
                  sx={{ color: "#3554D1", mr: 1, fontSize: 18 }}
                />
                <Typography variant="body2" sx={{ fontSize: "0.95rem" }}>
                  {attractionDetails?.location},{" "}
                  {attractionDetails?.country || ""}
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
                          {formatDate(bookingData?.bookingDate)}
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
                          {bookingData?.visitTime || "N/A"}
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
          <AttractionsIcon sx={{ mr: 1, color: "#3554D1", fontSize: 20 }} />
          Booking Details
        </Typography>

        {attractionBookings.length > 0 ? (
          <Box>
            {attractionBookings.map((booking, index) => {
              const data = booking?.data?.[0] || {};
              const selectionText = (() => {
                switch (data.Selection) {
                  case "withoutTraveller":
                    return "Only Attraction";
                  case "withPrivate":
                    return "Attraction With Transfer (Private)";
                  case "withShare":
                    return "Attraction With Transfer (Share)";
                  default:
                    return "Not selected";
                }
              })();
              return (
                <RoomCard key={index} sx={{ mb: 2 }}>
                  <RoomTypeHeader sx={{ py: 1.5, px: 2 }}>
                    <Box
                      sx={{
                        display: "flex",
                        justifyContent: "space-between",
                        alignItems: "center",
                      }}
                    >
                      <Typography
                        variant="subtitle1"
                        sx={{ fontWeight: 600, fontSize: "1rem" }}
                      >
                        {data?.AttractionName || "Attraction Details"}
                      </Typography>
                      <Chip
                        // label={selectionText}
                        label={data?.ticketName}
                        size="small"
                        sx={{
                          bgcolor: "rgba(255, 255, 255, 0.2)",
                          color: "white",
                          fontWeight: "medium",
                          height: "24px",
                          fontSize: "0.8rem",
                          "& .MuiChip-label": {
                            px: 1,
                          },
                        }}
                      />
                    </Box>
                  </RoomTypeHeader>

                  <CardContent sx={{ p: 2 }}>
                    <Grid container spacing={2}>
                      {/* Guest Details - Left Side */}
                      <Grid item xs={12} md={6}>
                        <Paper
                          elevation={1}
                          sx={{
                            p: 1.5,
                            borderRadius: "8px",
                            height: "100%",
                          }}
                        >
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              mb: 1,
                            }}
                          >
                            <PersonIcon
                              sx={{ color: "#3554D1", mr: 1, fontSize: 20 }}
                            />
                            <Typography
                              variant="body1"
                              sx={{ fontWeight: "bold", fontSize: "1.05rem" }}
                            >
                              Guest Details
                            </Typography>
                          </Box>

                          <Divider sx={{ my: 0.75 }} />

                          {/* Adults Section */}
                          <Box
                            sx={{
                              p: 1.25,
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                              borderRadius: "6px",
                              backgroundColor: "rgba(53, 84, 209, 0.05)",
                              mb: 1.5,
                            }}
                          >
                            <Typography
                              variant="body1"
                              sx={{ fontWeight: "medium", fontSize: "1rem" }}
                            >
                              Adults
                            </Typography>
                            <Badge
                              badgeContent={data?.adultCount || 0}
                              color="error"
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
                              <Avatar
                                sx={{
                                  bgcolor: "#3554D1",
                                  width: 32,
                                  height: 32,
                                }}
                              >
                                <PersonIcon
                                  sx={{
                                    fontSize: 18,
                                    color: "white",
                                  }}
                                />
                              </Avatar>
                            </Badge>
                          </Box>

                          {/* Children Section - Only show if there are any */}
                          {data?.childCount > 0 && (
                            <Box
                              sx={{
                                p: 1,
                                borderRadius: "6px",
                                border: "1px solid rgba(255, 152, 0, 0.3)",
                                bgcolor: "rgba(255, 152, 0, 0.05)",
                                display: "flex",
                                alignItems: "center",
                                justifyContent: "space-between",
                                mb: 1.5,
                              }}
                            >
                              <Typography
                                variant="body1"
                                sx={{ fontSize: "1rem" }}
                              >
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

                          {/* Seniors Section - Only show if there are any */}
                          {data?.seniorCount > 0 && (
                            <Box
                              sx={{
                                p: 1,
                                borderRadius: "6px",
                                border: "1px solid rgba(76, 175, 80, 0.3)",
                                bgcolor: "rgba(76, 175, 80, 0.05)",
                                display: "flex",
                                alignItems: "center",
                                justifyContent: "space-between",
                                mb: 1.5,
                              }}
                            >
                              <Typography
                                variant="body1"
                                sx={{ fontSize: "1rem" }}
                              >
                                Seniors
                              </Typography>
                              <Chip
                                size="medium"
                                label={data.seniorCount}
                                color="success"
                                icon={<ElderlyIcon sx={{ fontSize: 18 }} />}
                                sx={{ height: "24px", fontSize: "0.9rem" }}
                              />
                            </Box>
                          )}
                        </Paper>
                      </Grid>

                      {/* Right side - Customer Information (replacing Price Details) */}
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
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              mb: 1,
                            }}
                          >
                            <PersonIcon
                              sx={{ color: "#3554D1", mr: 1, fontSize: 20 }}
                            />
                            <Typography
                              variant="body1"
                              sx={{ fontWeight: "bold", fontSize: "1.05rem" }}
                            >
                              Customer Information
                            </Typography>
                          </Box>

                          <Divider sx={{ my: 0.75 }} />

                          <Box
                            sx={{
                              p: 1.5,
                              backgroundColor: "#F8FAFF",
                              borderRadius: "6px",
                              border: "1px solid rgba(53, 84, 209, 0.1)",
                              mt: 1,
                              flexGrow: 1,
                              display: "flex",
                              flexDirection: "column",
                              justifyContent: "space-between",
                            }}
                          >
                            <Grid container spacing={1.5}>
                              {/* Full Name */}
                              <Grid item xs={12}>
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "space-between",
                                    p: 1,
                                    backgroundColor: "white",
                                    borderRadius: "6px",
                                    border: "1px solid rgba(53, 84, 209, 0.1)",
                                  }}
                                >
                                  <Box
                                    sx={{
                                      display: "flex",
                                      alignItems: "center",
                                    }}
                                  >
                                    <PersonIcon
                                      sx={{
                                        color: "#3554D1",
                                        mr: 1.5,
                                        fontSize: 20,
                                      }}
                                    />
                                    <Typography
                                      variant="body2"
                                      color="text.secondary"
                                      sx={{ fontSize: "0.95rem" }}
                                    >
                                      Full Name
                                    </Typography>
                                  </Box>
                                  <Typography
                                    variant="body1"
                                    sx={{ fontWeight: 500, fontSize: "1rem" }}
                                  >
                                    {userInfo?.fullName || "N/A"}
                                  </Typography>
                                </Box>
                              </Grid>

                              {/* Email */}
                              <Grid item xs={12}>
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "space-between",
                                    p: 1,
                                    backgroundColor: "white",
                                    borderRadius: "6px",
                                    border: "1px solid rgba(53, 84, 209, 0.1)",
                                  }}
                                >
                                  <Box
                                    sx={{
                                      display: "flex",
                                      alignItems: "center",
                                    }}
                                  >
                                    <EmailIcon
                                      sx={{
                                        color: "#3554D1",
                                        mr: 1.5,
                                        fontSize: 20,
                                      }}
                                    />
                                    <Typography
                                      variant="body2"
                                      color="text.secondary"
                                      sx={{ fontSize: "0.95rem" }}
                                    >
                                      Email
                                    </Typography>
                                  </Box>
                                  <Typography
                                    variant="body1"
                                    sx={{ fontWeight: 500, fontSize: "1rem" }}
                                  >
                                    {userInfo?.email || "N/A"}
                                  </Typography>
                                </Box>
                              </Grid>

                              {/* Phone */}
                              <Grid item xs={12}>
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "space-between",
                                    p: 1,
                                    backgroundColor: "white",
                                    borderRadius: "6px",
                                    border: "1px solid rgba(53, 84, 209, 0.1)",
                                  }}
                                >
                                  <Box
                                    sx={{
                                      display: "flex",
                                      alignItems: "center",
                                    }}
                                  >
                                    <LocalPhoneIcon
                                      sx={{
                                        color: "#3554D1",
                                        mr: 1.5,
                                        fontSize: 20,
                                      }}
                                    />
                                    <Typography
                                      variant="body2"
                                      color="text.secondary"
                                      sx={{ fontSize: "0.95rem" }}
                                    >
                                      Phone
                                    </Typography>
                                  </Box>
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      fontWeight: 500,
                                      fontSize: "1rem",
                                      display: "flex",
                                      alignItems: "center",
                                    }}
                                  >
                                    {userInfo?.countryCode && (
                                      <Box
                                        component="span"
                                        sx={{
                                          color: "rgba(0, 0, 0, 0.6)",
                                          mr: 0.5,
                                          fontWeight: 400,
                                          fontSize: "0.95rem",
                                          bgcolor: "rgba(0, 0, 0, 0.05)",
                                          px: 0.8,
                                          py: 0.2,
                                          borderRadius: "4px",
                                        }}
                                      >
                                        {userInfo.countryCode}
                                      </Box>
                                    )}
                                    {userInfo?.phone || "N/A"}
                                  </Typography>
                                </Box>
                              </Grid>
                            </Grid>

                            {/* Summary Card - Bottom */}
                          </Box>
                        </Paper>
                      </Grid>
                    </Grid>
                  </CardContent>
                </RoomCard>
              );
            })}

            {/* Total Price Summary */}
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
                      {/* <Typography variant="body2" color="textSecondary" sx={{ mb: 0.5, display: 'block', fontSize: '0.95rem' }}>
                        Booking Type
                      </Typography> */}

                      {attractionDetails?.prices?.mode ? (
                        attractionDetails?.prices?.mode === "travClicks" ||
                        attractionDetails?.prices?.mode === "travclicks" ? (
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
                        ) : attractionDetails?.prices?.mode === "dmc" ? (
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              gap: 0.8,
                            }}
                          >
                            {DmcLogo ? (
                              <Avatar
                                src={DmcLogo}
                                alt="DMC Logo"
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
                                {DmcName?.charAt(0) || "D"}
                              </Avatar>
                            )}
                            <Typography
                              variant="body2"
                              fontWeight="medium"
                              color="#E65100"
                            >
                              {`${DmcName || "DMC"}'s Mode`}
                            </Typography>
                          </Box>
                        ) : (
                          <Typography variant="body2" fontWeight="medium">
                            {capitalizeFirstLetter(bookingData.mode)}
                          </Typography>
                        )
                      ) : bookingMode ? (
                        bookingMode === "travclicks" ? (
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
                        ) : (
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              gap: 0.8,
                            }}
                          >
                            {DmcLogo ? (
                              <Avatar
                                src={DmcLogo}
                                alt="DMC Logo"
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
                                {DmcName?.charAt(0) || "D"}
                              </Avatar>
                            )}
                            <Typography
                              variant="body2"
                              fontWeight="medium"
                              color="#E65100"
                            >
                              {`${DmcName || "DMC"}'s Mode`}
                            </Typography>
                          </Box>
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

                            <Chip
                              size="small"
                              label="All inclusive"
                              color="success"
                              variant="outlined"
                              sx={{
                                height: "22px",
                                fontSize: "0.75rem",
                                backgroundColor: "rgba(255, 255, 255, 0.1)",
                                color: "white",
                                borderColor: "rgba(255, 255, 255, 0.3)",
                              }}
                            />
                          </Box>

                          {(() => {
                            // Get currency information from Redux store
                            const currencySymbol = useSelector(
                              (state) => state.auth.currencySymbol
                            );
                            const currencyCode = useSelector(
                              (state) => state.auth.currencyCode
                            );
                            const exchangeRate = useSelector(
                              (state) => state.auth.exchangeRate
                            );
                            const usdExchangeRate = useSelector(
                              (state) => state.auth.usdExchangeRate
                            );
                            const usdCurrencyCode = useSelector(
                              (state) => state.auth.usdCurrencyCode
                            );
                            const currentTax = useSelector(
                              (state) => state.auth.currentTax || 0
                            );
                            const sgdTax = useSelector(
                              (state) => state.auth.sgdTax || 0
                            );
                            const usdTax = useSelector(
                              (state) => state.auth.usdTax || 0
                            );
                            const bookingType = useSelector(
                              (state) => state.common.bookingType
                            );

                            // Calculate tour price
                            const tourPrice = totalPrice || 0;

                            // Add transport price if available
                            let transportPrice = 0;
                            const transport =
                              attractionBookings?.[0]?.data?.[0]?.transport;
                            // console.log('Transport.......', transport);

                            if (transport) {
                              const adultCount =
                                attractionBookings?.[0]?.data?.[0]
                                  ?.adultCount || 0;
                              const childCount =
                                attractionBookings?.[0]?.data?.[0]
                                  ?.childCount || 0;
                              const totalPax = adultCount + childCount;

                              // If shared transport, multiply price by total pax
                              if (transport.transport_type === "shared") {
                                transportPrice = transport.price * totalPax;
                              } else {
                                // For private transport, use price as is
                                transportPrice = transport.price;
                              }
                            }

                            // Calculate total base price (without tax)
                            const basePrice = tourPrice + transportPrice;

                            // Format in current currency
                            const convertedPrice = basePrice * exchangeRate;

                            // Tax amounts (for display only)
                            const taxAmount = Math.ceil(
                              (convertedPrice * currentTax) / 100
                            );

                            // Grand total (with tax - for display only)
                            const grandTotal = convertedPrice + taxAmount;

                            // Calculate USD equivalent
                            const usdPrice = basePrice * usdExchangeRate;
                            const usdTaxAmount = Math.ceil(
                              (usdPrice * usdTax) / 100
                            );

                            // SGD is the base price already
                            const sgdPrice = basePrice;
                            const sgdTaxAmount = Math.ceil(
                              (sgdPrice * sgdTax) / 100
                            );

                            return (
                              <>
                                {PriceHide === "0" ? (
                                  // Show prices when PriceHide is "0"
                                  <>
                                    <Box
                                      sx={{
                                        display: "flex",
                                        justifyContent: "space-between",
                                        alignItems: "center",
                                        py: 0.5,
                                      }}
                                    >
                                      <Typography
                                        sx={{
                                          fontSize: "0.8rem",
                                          color: "rgba(255, 255, 255, 0.7)",
                                        }}
                                      >
                                        Tour Price
                                      </Typography>
                                      <Typography
                                        sx={{
                                          fontSize: "0.85rem",
                                          color: "rgba(255, 255, 255, 0.9)",
                                        }}
                                      >
                                        {currencyCode}{" "}
                                        {formatPrice(tourPrice * exchangeRate)}
                                      </Typography>
                                    </Box>

                                    {transportPrice > 0 && (
                                      <Box
                                        sx={{
                                          display: "flex",
                                          justifyContent: "space-between",
                                          alignItems: "center",
                                          py: 0.5,
                                        }}
                                      >
                                        <Typography
                                          sx={{
                                            fontSize: "0.8rem",
                                            color: "rgba(255, 255, 255, 0.7)",
                                          }}
                                        >
                                          Transport ({transport.vehicle_name},{" "}
                                          {transport.vehicle_type})
                                        </Typography>
                                        <Typography
                                          sx={{
                                            fontSize: "0.85rem",
                                            color: "rgba(255, 255, 255, 0.9)",
                                          }}
                                        >
                                          {currencyCode}{" "}
                                          {formatPrice(
                                            transportPrice * exchangeRate
                                          )}
                                        </Typography>
                                      </Box>
                                    )}

                                    {/* Base price (what's sent to the server) */}
                                    <Box
                                      sx={{
                                        display: "flex",
                                        justifyContent: "space-between",
                                        alignItems: "center",
                                        py: 0.5,
                                        mt: 0.5,
                                        borderTop:
                                          "1px dotted rgba(255, 255, 255, 0.3)",
                                      }}
                                    >
                                      <Typography
                                        sx={{
                                          fontSize: "0.8rem",
                                          color: "rgba(255, 255, 255, 0.8)",
                                          fontWeight: 500,
                                        }}
                                      >
                                        Base Price (Without Tax)
                                      </Typography>
                                      <Typography
                                        sx={{
                                          fontSize: "0.85rem",
                                          color: "rgba(255, 255, 255, 0.9)",
                                          fontWeight: 500,
                                        }}
                                      >
                                        {currencyCode}{" "}
                                        {formatPrice(convertedPrice)}
                                      </Typography>
                                    </Box>

                                    {/* Tax amount - display only */}
                                    <Box
                                      sx={{
                                        display: "flex",
                                        justifyContent: "space-between",
                                        alignItems: "center",
                                        py: 0.5,
                                      }}
                                    >
                                      <Typography
                                        sx={{
                                          fontSize: "0.8rem",
                                          color: "rgba(255, 255, 255, 0.7)",
                                          fontStyle: "italic",
                                        }}
                                      >
                                        + Tax ({currentTax}%)
                                      </Typography>
                                      <Typography
                                        sx={{
                                          fontSize: "0.85rem",
                                          color: "rgba(255, 255, 255, 0.8)",
                                        }}
                                      >
                                        {currencyCode} {formatPrice(taxAmount)}
                                      </Typography>
                                    </Box>

                                    {/* Total with tax (for display only) */}
                                    <Box
                                      sx={{
                                        display: "flex",
                                        justifyContent: "space-between",
                                        alignItems: "center",
                                        py: 0.5,
                                        mt: 0.5,
                                        pt: 0.5,
                                        borderTop:
                                          "1px solid rgba(255, 255, 255, 0.3)",
                                      }}
                                    >
                                      <Typography
                                        sx={{
                                          fontSize: "0.9rem",
                                          color: "white",
                                          fontWeight: "bold",
                                        }}
                                      >
                                        Total with Tax
                                      </Typography>
                                      <Typography
                                        sx={{
                                          fontSize: "1rem",
                                          color: "white",
                                          fontWeight: "bold",
                                        }}
                                      >
                                        {currencyCode} {formatPrice(grandTotal)}
                                      </Typography>
                                    </Box>

                                    {/* USD Equivalent - always show if not USD */}
                                    {currencyCode !== "USD" && (
                                      <Box
                                        sx={{
                                          display: "flex",
                                          justifyContent: "space-between",
                                          alignItems: "center",
                                          py: 0.5,
                                          mt: 0.5,
                                        }}
                                      >
                                        <Typography
                                          sx={{
                                            fontSize: "0.8rem",
                                            color: "rgba(255, 255, 255, 0.7)",
                                          }}
                                        >
                                          USD Total (with {usdTax}% tax)
                                        </Typography>
                                        <Typography
                                          sx={{
                                            fontSize: "0.85rem",
                                            color: "rgba(255, 255, 255, 0.8)",
                                          }}
                                        >
                                          USD{" "}
                                          {formatPrice(usdPrice + usdTaxAmount)}
                                        </Typography>
                                      </Box>
                                    )}

                                    {/* SGD Equivalent - always show if not SGD */}
                                    {currencyCode !== "SGD" && (
                                      <Box
                                        sx={{
                                          display: "flex",
                                          justifyContent: "space-between",
                                          alignItems: "center",
                                          py: 0.5,
                                        }}
                                      >
                                        <Typography
                                          sx={{
                                            fontSize: "0.8rem",
                                            color: "rgba(255, 255, 255, 0.7)",
                                          }}
                                        >
                                          SGD Total (with {sgdTax}% tax)
                                        </Typography>
                                        <Typography
                                          sx={{
                                            fontSize: "0.85rem",
                                            color: "rgba(255, 255, 255, 0.8)",
                                          }}
                                        >
                                          SGD{" "}
                                          {formatPrice(sgdPrice + sgdTaxAmount)}
                                        </Typography>
                                      </Box>
                                    )}
                                  </>
                                ) : (
                                  // Show message when PriceHide is not "0"
                                  <Box
                                    sx={{
                                      display: "flex",
                                      justifyContent: "center",
                                      alignItems: "center",
                                      py: 2,
                                    }}
                                  >
                                    <Typography
                                      sx={{
                                        fontSize: "1rem",
                                        color: "white",
                                        fontWeight: "bold",
                                      }}
                                    >
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
        {bookingType !== "enquiry" && (
          <Button
            variant="contained"
            onClick={handleFinalSubmit}
            startIcon={isBookingLoading ? null : <Box component="span">✓</Box>}
            size="medium"
            disabled={isBookingLoading || isEnquiryLoading}
            sx={{
              borderRadius: "6px",
              px: 2.5,
              py: 1,
              background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
              fontWeight: "bold",
              fontSize: "1.05rem",
              textTransform: "none",
            }}
          >
            {isBookingLoading ? (
              <Box sx={{ display: "flex", alignItems: "center" }}>
                <Box
                  sx={{
                    width: 16,
                    height: 16,
                    mr: 1,
                    animation: "spin 1s linear infinite",
                    "@keyframes spin": {
                      "0%": { transform: "rotate(0deg)" },
                      "100%": { transform: "rotate(360deg)" },
                    },
                  }}
                >
                  ◌
                </Box>
                Booking...
              </Box>
            ) : (
              "Book Now"
            )}
          </Button>
        )}

        {bookingType !== "booking" && (
          <Button
            variant="outlined"
            onClick={handleEnquirySubmit}
            size="medium"
            disabled={isBookingLoading || isEnquiryLoading}
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
          >
            {isEnquiryLoading ? (
              <Box sx={{ display: "flex", alignItems: "center" }}>
                <Box
                  sx={{
                    width: 16,
                    height: 16,
                    mr: 1,
                    animation: "spin 1s linear infinite",
                    "@keyframes spin": {
                      "0%": { transform: "rotate(0deg)" },
                      "100%": { transform: "rotate(360deg)" },
                    },
                  }}
                >
                  ◌
                </Box>
                Sending Enquiry...
              </Box>
            ) : (
              "Make an Enquiry"
            )}
          </Button>
        )}
      </Box>
    </Box>
  );
}
