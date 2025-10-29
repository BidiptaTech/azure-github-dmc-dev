import React, { useEffect, useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import { selectUserInfo } from "@/slice/common/customerInfo";
import {
  setHotelBooking,
  hottelBookingDataSubmit,
  setHotelService,
} from "@/slice/hotel/hotelSlice";
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
  TextField,
  InputAdornment,
  Button,
} from "@mui/material";
import PersonIcon from "@mui/icons-material/Person";
import HotelIcon from "@mui/icons-material/Hotel";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import BedIcon from "@mui/icons-material/Bed";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import CribIcon from "@mui/icons-material/Crib";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import BabyChangingStationIcon from "@mui/icons-material/BabyChangingStation";
import dayjs from "dayjs";
import { setHotelDetails } from "@/slice/hotel/HotelDetailsSlice";
import { setPriceMode } from "@/slice/hotel/CategorySlice";
import { setBookingType, setHaveBooking } from "@/slice/common/commonSlice";
import DoNotDisturbIcon from "@mui/icons-material/DoNotDisturb";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";

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

const BedTypeChip = styled(Box)(({ theme }) => ({
  display: "inline-flex",
  alignItems: "center",
  background: "#E6F2FF",
  padding: "8px 15px",
  borderRadius: "20px",
  marginRight: "10px",
  color: "#3554D1",
  fontWeight: 500,
  "& .MuiSvgIcon-root": {
    marginRight: "5px",
  },
}));

// Helper function to group meal data by bed type
const groupMealsByBedType = (bed) => {
  if (!bed.selectedMeals) {
    return {};
  }

  // Initialize meal counts and prices
  let mealData = {};

  // Process each selected meal individually
  Object.entries(bed.selectedMeals).forEach(([key, meal]) => {
    const mealType = meal.type;
    const mealPrice = parseFloat(meal.price) || 0;

    // If this meal type already exists, update the count
    if (mealData[mealType]) {
      mealData[mealType].count += 1;
      // Add the price for each additional meal
      mealData[mealType].totalPrice += mealPrice;
    } else {
      // Create a new entry for this meal type
      mealData[mealType] = {
        count: 1,
        type: mealType,
        price: mealPrice,
        totalPrice: mealPrice, // Initialize total price
      };
    }
  });

  return mealData;
};

export default function Index2() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const mode = useSelector((state) => state.category.priceMode);
  // console.log(mode, "mode");
  const bookingType = useSelector((state) => state.common.bookingType);
  // Get data from Redux state
  const userInfo = useSelector(selectUserInfo);
  const hotelData = useSelector((state) => state.hotels?.submitHotels || {});
  console.log("hotelData", hotelData);
  const hotelDetails = useSelector(
    (state) => state.hoteldetails?.bookingDetails || {}
  );
  console.log("Current hotel details in index2.jsx:", hotelDetails);
  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const bookingDates = useSelector((state) => state.hotels.searchState);
  const categoryPriceMode = useSelector((state) => state.category?.priceMode);

  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // Get data directly from location state (similar to index.jsx)
  const rooms = location.state?.bookingArray || location.state?.rooms || [];
  const totalPrice = location.state?.totalPrice || 0;
  const priceMode = location.state?.priceMode || categoryPriceMode || "both";
  const priceModeId = useSelector((state) => state.category.priceModeId);
  const { roomDatas } = useSelector((state) => state.rooms);
  const { check_in_time, check_out_time } = roomDatas;
  console.log(check_in_time, check_out_time, "check_in_time,check_out_time");

  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  // console.log("Location state:", location.state);
  // console.log("Room data from location:", rooms);
  // console.log("Hotel Data from Redux:", hotelData);
  // console.log("Hotel Details:", hotelDetails);

  // const [showEnquiry, setShowEnquiry] = useState(false);
  // const [enquiryAmount, setEnquiryAmount] = useState(() => {
  //   return totalPrice || hotelData?.totalPrice || "";
  // });
  // const [enquiryComment, setEnquiryComment] = useState("");
  // const [commentError, setCommentError] = useState(false);
  const [responseData, setResponseData] = useState(null);

  // Add state for tracking button loading
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isEnquiring, setIsEnquiring] = useState(false);

  // If no user info, redirect back
  useEffect(() => {
    if (!userInfo || !userInfo.fullName) {
      navigate("/dashboard/db-dashboard/hotel-details");
    }
  }, [userInfo, navigate]);

  // Update enquiry amount when totalPrice changes
  // useEffect(() => {
  //   setEnquiryAmount(totalPrice || hotelData?.totalPrice || "");
  // }, [totalPrice, hotelData]);

  // Initialize data in Redux if not already there
  useEffect(() => {
    // If we have rooms from location but not in Redux, put them there
    if ((rooms && rooms.length > 0) || totalPrice) {
      const payload = {
        rooms: rooms || [],
        totalPrice: totalPrice || 0,
        priceMode: priceMode || "dmc",
        priceModeId: priceModeId || "",
      };
      console.log("Setting hotel booking from location state:", payload);
      dispatch(setHotelBooking(payload));
    }
  }, [rooms, totalPrice, priceMode, priceModeId, dispatch]);

  const handleFinalSubmit = async () => {
    // Prevent multiple submissions
    if (isSubmitting) {
      return;
    }

    try {
      setIsSubmitting(true);

      if (!userInfo || !userInfo.fullName) {
        toast.error(
          "Customer information is missing. Please fill out the form first."
        );
        navigate("/dashboard/db-dashboard/hotel-details");
        return;
      }

      // Use rooms from location state first, then fallback to Redux state
      const roomsToSubmit =
        rooms && rooms.length > 0 ? rooms : hotelData?.rooms || [];

      const payload = {
        ...userInfo,
        rooms: roomsToSubmit,
        bookingType: "booking",
        totalPrice: totalPrice || hotelData?.totalPrice || 0,
        priceMode: priceMode || hotelData?.priceMode || "both",
        priceModeId: priceModeId || "",

        hotelDetails: {
          hotel_id: hotelDetails?.hotel_id || "",
          hotel_name: hotelDetails?.hotel_name || "",
          checkInTime: hotelDetails?.check_in_time || "",
          checkOutTime: hotelDetails?.check_out_time || "",
          location: hotelDetails?.location || "",
          image: hotelDetails?.image || "",
          cancellation_charge: hotelDetails?.cancellation_charge || "",
        },
        bookingDate: [bookingDates?.ucheckIn, bookingDates?.ucheckOut],
      };

      // Update Redux state with the final data
      dispatch(setHotelDetails(payload.hotelDetails));
      dispatch(setHotelBooking(payload));

      console.log("Submitting hotel booking details:", payload);

      // Submit the booking
      const response = await dispatch(
        hottelBookingDataSubmit("booking")
      ).unwrap();

      setResponseData(response);
      dispatch(setHaveBooking(true));
      dispatch(setBookingType(response.order?.bookingType));
      if (response?.service?.date_service) {
        dispatch(setDateService(response.service.date_service));
        dispatch(setHotelService(response?.service?.data));

        toast.success("Booking successful!", {
          position: "top-center",
          autoClose: 3000,
        });

        dispatch(setPriceMode("both"));

        navigate("/dashboard/db-dashboard/thank-you", {
          state: { bookingResponse: response },
          replace: true,
        });
      }
    } catch (error) {
      console.error("Error during submission:", error);
      toast.error("Something went wrong. Please try again later.", {
        position: "top-center",
        autoClose: 3000,
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  // const handleAmountChange = (e) => {
  //   const newValue = parseFloat(e.target.value);
  //   const maxPrice = totalPrice || hotelData?.totalPrice || 0;

  //   // Don't allow values above the total price or below 1
  //   if (newValue <= maxPrice && newValue > 0) {
  //     setEnquiryAmount(newValue);
  //   }
  // };

  const handleEnquirySubmit = async () => {
    // Prevent multiple submissions
    if (isEnquiring) {
      return;
    }

    try {
      setIsEnquiring(true);

      // if (!enquiryComment.trim()) {
      //   setCommentError(true);
      //   toast.error("Please enter a comment");
      //   return;
      // }

      // // Reset error state
      // setCommentError(false);

      // Use rooms from location state first, then fallback to Redux state
      const roomsToSubmit =
        rooms && rooms.length > 0 ? rooms : hotelData?.rooms || [];

      const payload = {
        ...userInfo,
        rooms: roomsToSubmit,
        totalPrice: totalPrice || hotelData?.totalPrice || 0,
        //enquiryPrice: enquiryAmount,
        bookingType: "enquiry",
        // comment: enquiryComment,
        priceMode: priceMode || hotelData?.priceMode || "both",
        priceModeId: priceModeId || "",
        hotelDetails: {
          hotel_id: hotelDetails?.hotel_id || "",
          hotel_name: hotelDetails?.hotel_name || "",
          checkInTime: hotelDetails?.check_in_time || "",
          checkOutTime: hotelDetails?.check_out_time || "",
          location: hotelDetails?.location || "",
          image: hotelDetails?.image || "",
          cancellation_charge: hotelDetails?.cancellation_charge || "",
        },
        bookingDate: [bookingDates?.ucheckIn, bookingDates?.ucheckOut],
      };

      // Update Redux state with the enquiry data
      dispatch(setHotelDetails(payload.hotelDetails));
      dispatch(setHotelBooking(payload));

      console.log("Submitting hotel enquiry details:", payload);

      const response = await dispatch(
        hottelBookingDataSubmit("enquiry")
      ).unwrap();

      setResponseData(response);
      dispatch(setHaveBooking(true));
      dispatch(setBookingType(response.order?.bookingType));

      if (response?.service?.date_service) {
        dispatch(setDateService(response.service.date_service));
        dispatch(setHotelService(response?.service?.data));

        toast.success("Enquiry submitted successfully!", {
          position: "top-center",
          autoClose: 3000,
        });

        // Clear form and close enquiry section
        // setShowEnquiry(false);
        // setEnquiryComment("");

        dispatch(setPriceMode("both"));

        navigate("/dashboard/db-dashboard/thank-you", {
          state: { bookingResponse: response },
          replace: true,
        });
      }
    } catch (error) {
      console.error("Error during enquiry submission:", error);
      toast.error("Something went wrong. Please try again later.", {
        position: "top-center",
        autoClose: 3000,
      });
    } finally {
      setIsEnquiring(false);
    }
  };

  // Function to format date
  const formatDate = (date) => {
    if (!date) return "Not Selected";
    return dayjs(date).format("DD MMM YYYY");
  };

  // Format time to 12-hour format with AM/PM
  const formatTime = (timeString) => {
    if (!timeString) return "";

    // Check if the time is already in a standard format
    let hours, minutes, period;

    // Try to extract time from different formats (HH:MM, HH:MM:SS, etc.)
    const timeRegex = /(\d{1,2})[:\.](\d{2})(?::\d{2})?\s*(am|pm|AM|PM)?/;
    const match = timeString.match(timeRegex);

    if (match) {
      hours = parseInt(match[1], 10);
      minutes = match[2];
      period = match[3] ? match[3].toUpperCase() : null;

      // Store original hours for 24-hour format
      const hours24 = period
        ? period.toUpperCase() === "PM" && hours < 12
          ? hours + 12
          : hours
        : hours;

      // If no AM/PM is provided but hours are in 24-hour format
      if (!period) {
        period = hours >= 12 ? "PM" : "AM";
        hours = hours % 12 || 12; // Convert to 12-hour format
      }

      // Format 24-hour time (ensure leading zeros)
      const formattedHours24 = hours24.toString().padStart(2, "0");
      const formattedMinutes = minutes.padStart(2, "0");
      const time24 = `${formattedHours24}:${formattedMinutes}`;

      // Return both formats
      return `${hours}:${minutes} ${period} (${time24})`;
    } else {
      // If we can't parse the time, return the original string
      return timeString;
    }
  };

  // Function to format price with commas for thousands
  const formatPrice = (price) => {
    if (typeof price !== "number") return "0.00";

    return price.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  // Use rooms from either location or Redux state
  const roomsToDisplay =
    rooms && rooms.length > 0 ? rooms : hotelData?.rooms || [];

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
          onClick={() => navigate("/dashboard/db-dashboard/hotel-details")}
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
      {/* Header Section with Gradient Background - More Compact */}
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
          <HotelIcon sx={{ fontSize: 24, mr: 1 }} />
          <Typography variant="h6" sx={{ fontWeight: "bold" }}>
            Hotel Booking Details
          </Typography>
        </Box>
        <Chip
          label={priceMode || hotelData?.priceMode || "Standard"}
          size="small"
          sx={{
            backgroundColor: "rgba(255, 255, 255, 0.2)",
            color: "white",
            fontWeight: "bold",
            height: "24px",
          }}
        />
      </Box>

      {/* Hotel Image and Name Section - More Compact with Fixed Image Size */}
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
                hotelDetails?.image ||
                "https://via.placeholder.com/300x300?text=Hotel+Image"
              }
              alt={hotelDetails?.hotel_name || "Hotel"}
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
            {hotelDetails?.hotel_name && (
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
                  label={hotelDetails.hotel_name}
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
                <HotelIcon sx={{ color: "#3554D1", mr: 1, fontSize: 20 }} />
                <Typography variant="h6" sx={{ fontWeight: "bold" }}>
                  {hotelDetails?.hotel_name || "Hotel Name"}
                </Typography>
              </Box>

              <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                <LocationOnIcon
                  sx={{ color: "#3554D1", mr: 1, fontSize: 18 }}
                />
                <Typography variant="body2" sx={{ fontSize: "0.95rem" }}>
                  {hotelDetails?.location || "Location"}
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
                          Check-In
                        </Typography>
                        <Typography
                          variant="body2"
                          sx={{ fontWeight: "medium", fontSize: "0.95rem" }}
                        >
                          {formatDate(bookingDates?.ucheckIn)}
                        </Typography>
                        <Typography
                          variant="caption"
                          color="primary"
                          sx={{ display: "block", fontSize: "0.8rem" }}
                        >
                          {formatTime(check_in_time)}
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
                        <CalendarTodayIcon sx={{ fontSize: 16 }} />
                      </Avatar>
                      <Box>
                        <Typography
                          variant="caption"
                          color="textSecondary"
                          sx={{ fontSize: "0.8rem" }}
                        >
                          Check-Out
                        </Typography>
                        <Typography
                          variant="body2"
                          sx={{ fontWeight: "medium", fontSize: "0.95rem" }}
                        >
                          {formatDate(bookingDates?.ucheckOut)}
                        </Typography>
                        <Typography
                          variant="caption"
                          color="primary"
                          sx={{ display: "block", fontSize: "0.8rem" }}
                        >
                          {formatTime(check_out_time)}
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

      <Box sx={{ mb: 2.5 }}>
        <Typography
          variant="subtitle1"
          sx={{
            fontWeight: "bold",
            mb: 1.5,
            display: "flex",
            alignItems: "center",
            fontSize: "1.05rem",
          }}
        >
          <HotelIcon sx={{ mr: 1, color: "#3554D1", fontSize: 20 }} />
          Room Details
        </Typography>

        {roomsToDisplay && roomsToDisplay.length > 0 ? (
          <Box>
            {roomsToDisplay.map((room, index) => (
              <RoomCard key={index} sx={{ mb: 2 }}>
                <RoomTypeHeader sx={{ py: 1.5, px: 2 }}>
                  <Typography
                    variant="subtitle1"
                    sx={{ fontWeight: 600, fontSize: "1rem" }}
                  >
                    {room.room_type ||
                      room.room_category ||
                      `Room ${index + 1}`}
                  </Typography>
                </RoomTypeHeader>

                <CardContent sx={{ p: 2 }}>
                  {room.beds &&
                    Array.isArray(room.beds) &&
                    room.beds.map((bed) => {
                      const groupedMeals = groupMealsByBedType(bed);
                      const totalOccupancyForBed = bed.head_count || 0;

                      // Calculate prices with exchange rates
                      const basePrice = parseFloat(bed.price) || 0;
                      const totalBedPrice = basePrice * totalOccupancyForBed;

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
                      const usdCurrencySymbol = useSelector(
                        (state) => state.auth.usdCurrencySymbol
                      );
                      const usdCurrencyCode = useSelector(
                        (state) => state.auth.usdCurrencyCode
                      );

                      // Convert prices
                      const price = totalPrice || hotelData?.totalPrice || 0;
                      const conversionRate = exchangeRate;
                      const convertedTotalBedPrice =
                        totalBedPrice * conversionRate;
                      const usdTotalBedPrice = totalBedPrice * usdExchangeRate;

                      return (
                        <Box
                          key={bed.bed_id}
                          sx={{
                            mb: 2,
                            pb: 2,
                            borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                            "&:last-child": {
                              borderBottom: "none",
                              mb: 0,
                              pb: 0,
                            },
                          }}
                        >
                          {/* Bed Type Header - More Compact */}
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                              mb: 1.5,
                              pb: 1,
                              borderBottom: "1px solid rgba(53, 84, 209, 0.1)",
                            }}
                          >
                            <Chip
                              icon={<BedIcon sx={{ fontSize: 18 }} />}
                              label={bed.bed_type || "Standard Bed"}
                              color="primary"
                              size="small"
                              sx={{ fontWeight: "bold", fontSize: "0.9rem" }}
                            />
                            <Chip
                              icon={<PersonIcon sx={{ fontSize: 18 }} />}
                              label={`${totalOccupancyForBed} ${
                                totalOccupancyForBed > 1 ? "Persons" : "Person"
                              }`}
                              variant="outlined"
                              color="primary"
                              size="small"
                              sx={{ fontWeight: "medium", fontSize: "0.9rem" }}
                            />
                          </Box>

                          <Grid container spacing={2}>
                            {/* Meal Details - Left Side */}
                            <Grid item xs={12} md={6}>
                              <Paper
                                elevation={1}
                                sx={{
                                  p: 1.5,
                                  borderRadius: "8px",
                                  height: "100%",
                                  backgroundColor: "rgba(53, 84, 209, 0.03)",
                                }}
                              >
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    mb: 1,
                                  }}
                                >
                                  <RestaurantIcon
                                    sx={{
                                      color: "#3554D1",
                                      mr: 1,
                                      fontSize: 18,
                                    }}
                                  />
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      fontWeight: "bold",
                                      fontSize: "1.05rem",
                                    }}
                                  >
                                    Meal Options
                                  </Typography>
                                </Box>

                                <Divider sx={{ my: 0.5 }} />

                                <Box
                                  sx={{
                                    // maxHeight: "150px",
                                    // overflowY: "auto",
                                    pt: 0.5,
                                  }}
                                >
                                  {Object.values(groupedMeals).length > 0 ? (
                                    Object.values(groupedMeals).map(
                                      (mealDetails) => {
                                        // Calculate meal prices
                                        const mealPrice =
                                          parseFloat(mealDetails.price) || 0;
                                        const mealCount =
                                          mealDetails.count || 1;
                                        const totalMealPrice =
                                          mealDetails.totalPrice ||
                                          mealPrice * mealCount;
                                        const convertedTotalMealPrice =
                                          totalMealPrice * conversionRate;

                                        return (
                                          <Box
                                            key={mealDetails.type}
                                            sx={{
                                              display: "flex",
                                              alignItems: "center",
                                              mb: 1,
                                              backgroundColor: "#F8FAFF",
                                              p: 1,
                                              borderRadius: "8px",
                                              border:
                                                "1px solid rgba(53, 84, 209, 0.1)",
                                            }}
                                          >
                                            <Box
                                              sx={{
                                                display: "flex",
                                                alignItems: "center",
                                                flex: 1,
                                              }}
                                            >
                                              {/* Restaurant icon with meal count badge */}
                                              <Box
                                                sx={{
                                                  position: "relative",
                                                  mr: 2,
                                                }}
                                              >
                                                {/* Status badge above restaurant icon */}
                                                <Box
                                                  sx={{
                                                    position: "absolute",
                                                    top: -15,
                                                    right: -10,
                                                    zIndex: 1,
                                                    backgroundColor:
                                                      mealDetails.type.toLowerCase() ===
                                                      "room only"
                                                        ? "#FFF2F0"
                                                        : "#F6FFED",
                                                    borderRadius: "50%",
                                                    width: 20,
                                                    height: 20,
                                                    display: "flex",
                                                    justifyContent: "center",
                                                    alignItems: "center",
                                                    border: `1px solid ${
                                                      mealDetails.type.toLowerCase() ===
                                                      "room only"
                                                        ? "#FFCCC7"
                                                        : "#B7EB8F"
                                                    }`,
                                                  }}
                                                >
                                                  {mealDetails.type.toLowerCase() ===
                                                  "room only" ? (
                                                    <DoNotDisturbIcon
                                                      sx={{
                                                        color: "#FF4D4F",
                                                        fontSize: "0.8rem",
                                                      }}
                                                    />
                                                  ) : (
                                                    <CheckCircleIcon
                                                      sx={{
                                                        color: "#52C41A",
                                                        fontSize: "0.8rem",
                                                      }}
                                                    />
                                                  )}
                                                </Box>

                                                <RestaurantIcon
                                                  sx={{
                                                    color: "#3554D1",
                                                    fontSize: "1.5rem",
                                                  }}
                                                />
                                              </Box>

                                              <Badge
                                                badgeContent={mealDetails.count}
                                                color="primary"
                                                sx={{
                                                  "& .MuiBadge-badge": {
                                                    fontSize: "0.7rem",
                                                    height: "20px",
                                                    minWidth: "20px",
                                                  },
                                                }}
                                              >
                                                <Typography
                                                  variant="body1"
                                                  sx={{ ml: 1 }}
                                                >
                                                  {mealDetails.type}
                                                </Typography>
                                              </Badge>
                                            </Box>

                                            {PriceHide === "0" ? (
                                              <Box sx={{ textAlign: "right" }}>
                                                <Typography
                                                  variant="body1"
                                                  sx={{
                                                    fontWeight: 500,
                                                    color: "#3554D1",
                                                  }}
                                                >
                                                  {currencyCode}{" "}
                                                  {formatPrice(
                                                    convertedTotalMealPrice
                                                  )}
                                                  /night
                                                </Typography>
                                                <Typography
                                                  variant="caption"
                                                  sx={{
                                                    display: "block",
                                                    color: "text.secondary",
                                                  }}
                                                >
                                                  {usdCurrencyCode}{" "}
                                                  {formatPrice(
                                                    totalMealPrice *
                                                      usdExchangeRate
                                                  )}
                                                  /night
                                                </Typography>
                                                <Typography
                                                  variant="caption"
                                                  sx={{
                                                    display: "block",
                                                    color: "text.secondary",
                                                  }}
                                                >
                                                  SGD{" "}
                                                  {formatPrice(totalMealPrice)}
                                                  /night
                                                </Typography>
                                              </Box>
                                            ) : (
                                              <Box sx={{ textAlign: "right" }}>
                                                <Typography
                                                  variant="body1"
                                                  sx={{
                                                    fontWeight: 500,
                                                    color: "#3554D1",
                                                  }}
                                                >
                                                  Price available on request
                                                </Typography>
                                              </Box>
                                            )}
                                          </Box>
                                        );
                                      }
                                    )
                                  ) : (
                                    <Typography
                                      variant="body2"
                                      color="text.secondary"
                                      sx={{
                                        textAlign: "center",
                                        display: "block",
                                        py: 1,
                                        fontSize: "0.95rem",
                                      }}
                                    >
                                      No meal options selected
                                    </Typography>
                                  )}
                                </Box>

                                {/* Baby Cot Section - Show only if baby cot is selected */}
                                {(bed.baby_cot === true ||
                                  bed.baby_cot === 1) && (
                                  <Box sx={{ mt: 1.5 }}>
                                    <Divider sx={{ mb: 1.5 }} />
                                    <Typography
                                      variant="subtitle2"
                                      sx={{
                                        fontWeight: "bold",
                                        fontSize: "0.95rem",
                                        mb: 1,
                                        display: "flex",
                                        alignItems: "center",
                                      }}
                                    >
                                      <BabyChangingStationIcon
                                        sx={{
                                          color: "#3554D1",
                                          mr: 1,
                                          fontSize: 18,
                                        }}
                                      />
                                      Baby Cot
                                    </Typography>

                                    <Box
                                      sx={{
                                        display: "flex",
                                        alignItems: "center",
                                        mb: 1,
                                        backgroundColor:
                                          "rgba(76, 175, 80, 0.08)",
                                        p: 1,
                                        borderRadius: "8px",
                                        border:
                                          "1px solid rgba(76, 175, 80, 0.2)",
                                      }}
                                    >
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                          flex: 1,
                                        }}
                                      >
                                        <Box
                                          sx={{ position: "relative", mr: 2 }}
                                        >
                                          <Box
                                            sx={{
                                              position: "absolute",
                                              top: -10,
                                              right: -5,
                                              zIndex: 1,
                                              backgroundColor: "#F6FFED",
                                              borderRadius: "50%",
                                              width: 18,
                                              height: 18,
                                              display: "flex",
                                              justifyContent: "center",
                                              alignItems: "center",
                                              border: "1px solid #B7EB8F",
                                            }}
                                          >
                                            <CheckCircleIcon
                                              sx={{
                                                color: "#52C41A",
                                                fontSize: "0.7rem",
                                              }}
                                            />
                                          </Box>
                                          <BabyChangingStationIcon
                                            sx={{
                                              color: "#4CAF50",
                                              fontSize: "1.5rem",
                                            }}
                                          />
                                        </Box>
                                        <Typography
                                          variant="body1"
                                          sx={{ fontWeight: "medium" }}
                                        >
                                          Baby Cot
                                        </Typography>
                                      </Box>

                                      {PriceHide === "0" ? (
                                        <Box sx={{ textAlign: "right" }}>
                                          <Typography
                                            variant="body1"
                                            sx={{
                                              fontWeight: 500,
                                              color: "#4CAF50",
                                            }}
                                          >
                                            {currencyCode}{" "}
                                            {formatPrice(
                                              parseFloat(
                                                bed.baby_cot_price || 0
                                              ) * conversionRate
                                            )}
                                            /night
                                          </Typography>
                                          <Typography
                                            variant="caption"
                                            sx={{
                                              display: "block",
                                              color: "text.secondary",
                                            }}
                                          >
                                            {usdCurrencyCode}{" "}
                                            {formatPrice(
                                              parseFloat(
                                                bed.baby_cot_price || 0
                                              ) * usdExchangeRate
                                            )}
                                            /night
                                          </Typography>
                                          <Typography
                                            variant="caption"
                                            sx={{
                                              display: "block",
                                              color: "text.secondary",
                                            }}
                                          >
                                            SGD{" "}
                                            {formatPrice(
                                              parseFloat(
                                                bed.baby_cot_price || 0
                                              )
                                            )}
                                            /night
                                          </Typography>
                                        </Box>
                                      ) : (
                                        <Box sx={{ textAlign: "right" }}>
                                          <Typography
                                            variant="body1"
                                            sx={{
                                              fontWeight: 500,
                                              color: "#4CAF50",
                                            }}
                                          >
                                            Price available on request
                                          </Typography>
                                        </Box>
                                      )}
                                    </Box>
                                  </Box>
                                )}
                              </Paper>
                            </Grid>

                            {/* Guest Details - Right Side */}
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
                                    sx={{
                                      color: "#3554D1",
                                      mr: 1,
                                      fontSize: 18,
                                    }}
                                  />
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      fontWeight: "bold",
                                      fontSize: "1.05rem",
                                    }}
                                  >
                                    Guest Details
                                  </Typography>
                                </Box>

                                <Divider sx={{ my: 0.5 }} />

                                {/* Adults Section */}
                                <Box
                                  sx={{
                                    p: 1,
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
                                    sx={{
                                      fontWeight: "medium",
                                      fontSize: "1rem",
                                    }}
                                  >
                                    Adults
                                  </Typography>
                                  <Badge
                                    badgeContent={totalOccupancyForBed}
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

                                {/* Child and Infant Counts - Only show if there are any */}
                                {(() => {
                                  const tourData = useSelector(
                                    (state) => state.hotels.tourdetails || {}
                                  );
                                  const childCount =
                                    bed.child_count ||
                                    tourData?.tour_child_adult ||
                                    0;
                                  const infantCount =
                                    bed.infant_count ||
                                    tourData?.tour_infant ||
                                    0;

                                  if (childCount > 0 || infantCount > 0) {
                                    return (
                                      <Box sx={{ mb: 1.5 }}>
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                          sx={{
                                            display: "block",
                                            mb: 0.5,
                                            fontSize: "0.95rem",
                                          }}
                                        >
                                          Additional Guests
                                        </Typography>

                                        <Grid container spacing={1}>
                                          {childCount > 0 && (
                                            <Grid item xs={6}>
                                              <Box
                                                sx={{
                                                  p: 0.75,
                                                  borderRadius: "6px",
                                                  border:
                                                    "1px solid rgba(255, 152, 0, 0.3)",
                                                  bgcolor:
                                                    "rgba(255, 152, 0, 0.05)",
                                                  display: "flex",
                                                  alignItems: "center",
                                                  justifyContent:
                                                    "space-between",
                                                }}
                                              >
                                                <Typography
                                                  variant="body1"
                                                  sx={{ fontSize: "1rem" }}
                                                >
                                                  Children
                                                </Typography>
                                                <Chip
                                                  size="small"
                                                  label={childCount}
                                                  color="warning"
                                                  icon={
                                                    <ChildCareIcon
                                                      sx={{ fontSize: 18 }}
                                                    />
                                                  }
                                                  sx={{
                                                    height: "24px",
                                                    fontSize: "0.9rem",
                                                  }}
                                                />
                                              </Box>
                                            </Grid>
                                          )}

                                          {infantCount > 0 && (
                                            <Grid item xs={6}>
                                              <Box
                                                sx={{
                                                  p: 0.75,
                                                  borderRadius: "6px",
                                                  border:
                                                    "1px solid rgba(156, 39, 176, 0.3)",
                                                  bgcolor:
                                                    "rgba(156, 39, 176, 0.05)",
                                                  display: "flex",
                                                  alignItems: "center",
                                                  justifyContent:
                                                    "space-between",
                                                }}
                                              >
                                                <Typography
                                                  variant="body1"
                                                  sx={{ fontSize: "1rem" }}
                                                >
                                                  Infants
                                                </Typography>
                                                <Chip
                                                  size="small"
                                                  label={infantCount}
                                                  color="secondary"
                                                  icon={
                                                    <CribIcon
                                                      sx={{ fontSize: 18 }}
                                                    />
                                                  }
                                                  sx={{
                                                    height: "24px",
                                                    fontSize: "0.9rem",
                                                  }}
                                                />
                                              </Box>
                                            </Grid>
                                          )}
                                        </Grid>
                                      </Box>
                                    );
                                  }
                                  return null;
                                })()}

                                {/* Price Summary - More Compact */}
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

                                  // Get base price (NO TAX APPLIED)
                                  const basePrice =
                                    totalPrice || hotelData?.totalPrice || 0;

                                  // Ceiling the base prices (NO TAX)
                                  const sgdPrice = Math.ceil(basePrice);
                                  const usdPrice = Math.ceil(
                                    basePrice * usdExchangeRate
                                  );
                                  const convertedPrice = Math.ceil(
                                    basePrice * exchangeRate
                                  );

                                  // Grand totals (WITHOUT TAX)
                                  const convertedGrandTotal = convertedPrice;
                                  const sgdGrandTotal = sgdPrice;
                                  const usdGrandTotal = usdPrice;

                                  return (
                                    <>
                                      {PriceHide === "0" ? (
                                        <>
                                          {/* <Box
                                            sx={{
                                              display: "flex",
                                              justifyContent: "space-between",
                                              alignItems: "center",
                                              mb: 1,
                                              pb: 1,
                                              borderBottom:
                                                "1px solid rgba(255, 255, 255, 0.2)",
                                            }}
                                          >
                                            <Typography
                                              sx={{
                                                fontSize: "0.85rem",
                                                color:
                                                  "rgba(255, 255, 255, 0.9)",
                                                fontWeight: "medium",
                                              }}
                                            >
                                              Tax Rate
                                            </Typography>
                                            <Chip
                                              label={`${currentTax}%`}
                                              size="small"
                                              sx={{
                                                bgcolor:
                                                  "rgba(255, 255, 255, 0.2)",
                                                color: "white",
                                                fontWeight: "medium",
                                                height: "20px",
                                                fontSize: "0.7rem",
                                              }}
                                            />
                                          </Box> */}

                                          {/* Current Currency Section */}
                                          <Box sx={{ mb: 1.5 }}>
                                            <Typography
                                              sx={{
                                                fontSize: "0.85rem",
                                                color:
                                                  "rgba(255, 255, 255, 0.8)",
                                                mb: 0.5,
                                                fontWeight: "medium",
                                              }}
                                            >
                                              {currencyCode}
                                            </Typography>

                                            {/* Total Price (Without Tax) */}
                                            <Box
                                              sx={{
                                                display: "flex",
                                                justifyContent: "space-between",
                                                alignItems: "center",
                                                py: 0.5,
                                                mt: 0.5,
                                                borderTop:
                                                  "1px dotted rgba(255, 255, 255, 0.3)",
                                                borderBottom:
                                                  "1px solid rgba(255, 255, 255, 0.2)",
                                              }}
                                            >
                                              <Typography
                                                sx={{
                                                  fontWeight: "bold",
                                                  fontSize: "0.85rem",
                                                  color: "white",
                                                }}
                                              >
                                                Total Price
                                              </Typography>
                                              <Typography
                                                sx={{
                                                  fontWeight: "bold",
                                                  fontSize: "0.95rem",
                                                  color: "white",
                                                }}
                                              >
                                                {formatPrice(
                                                  convertedGrandTotal
                                                )}
                                              </Typography>
                                            </Box>
                                          </Box>

                                          {/* Other currencies (without tax) */}
                                          <Box sx={{ mt: 1 }}>
                                            <Typography
                                              sx={{
                                                fontSize: "0.8rem",
                                                color:
                                                  "rgba(255, 255, 255, 0.7)",
                                                mb: 0.5,
                                              }}
                                            >
                                              Other Currencies
                                            </Typography>

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
                                                  fontSize: "0.85rem",
                                                  color:
                                                    "rgba(255, 255, 255, 0.9)",
                                                }}
                                              >
                                                {usdCurrencyCode}
                                              </Typography>
                                              <Typography
                                                sx={{
                                                  fontSize: "0.85rem",
                                                  color:
                                                    "rgba(255, 255, 255, 0.9)",
                                                }}
                                              >
                                                {formatPrice(usdGrandTotal)}
                                              </Typography>
                                            </Box>

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
                                                  fontSize: "0.85rem",
                                                  color:
                                                    "rgba(255, 255, 255, 0.9)",
                                                }}
                                              >
                                                SGD
                                              </Typography>
                                              <Typography
                                                sx={{
                                                  fontSize: "0.85rem",
                                                  color:
                                                    "rgba(255, 255, 255, 0.9)",
                                                }}
                                              >
                                                {formatPrice(sgdGrandTotal)}
                                              </Typography>
                                            </Box>
                                          </Box>
                                        </>
                                      ) : (
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
                              </Paper>
                            </Grid>
                          </Grid>
                        </Box>
                      );
                    })}

                  {/* If no beds array or empty, show basic room info - More Compact */}
                  {(!room.beds ||
                    !Array.isArray(room.beds) ||
                    room.beds.length === 0) && (
                    <Grid container spacing={2}>
                      <Grid item xs={12} md={6}>
                        <Box sx={{ mb: 1.5 }}>
                          <Typography
                            variant="subtitle2"
                            sx={{
                              color: "#3554D1",
                              fontWeight: 500,
                              mb: 0.5,
                              fontSize: "1.05rem",
                            }}
                          >
                            Room Information
                          </Typography>

                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              backgroundColor: "#F8FAFF",
                              p: 1,
                              borderRadius: "6px",
                              border: "1px solid rgba(53, 84, 209, 0.1)",
                              mb: 0.75,
                            }}
                          >
                            <HotelIcon
                              sx={{ color: "#3554D1", mr: 1, fontSize: 16 }}
                            />
                            <Typography
                              variant="body1"
                              sx={{ fontSize: "1rem" }}
                            >
                              {room.room_category ||
                                room.room_type ||
                                "Standard Room"}
                            </Typography>
                          </Box>

                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              backgroundColor: "#F8FAFF",
                              p: 1,
                              borderRadius: "6px",
                              border: "1px solid rgba(53, 84, 209, 0.1)",
                              mb: 0.75,
                            }}
                          >
                            <PersonIcon
                              sx={{ color: "#3554D1", mr: 1, fontSize: 16 }}
                            />
                            <Typography
                              variant="body1"
                              sx={{ fontSize: "1rem" }}
                            >
                              Room Capacity: {room.capacity || "N/A"}
                            </Typography>
                          </Box>
                        </Box>
                      </Grid>

                      <Grid item xs={12} md={6}>
                        <Box>
                          <Typography
                            variant="subtitle2"
                            sx={{
                              color: "#3554D1",
                              fontWeight: 500,
                              mb: 0.5,
                              fontSize: "1.05rem",
                            }}
                          >
                            Price Details
                          </Typography>

                          <Box
                            sx={{
                              p: 1.5,
                              backgroundColor: "#F8FAFF",
                              borderRadius: "6px",
                              border: "1px solid rgba(53, 84, 209, 0.1)",
                            }}
                          >
                            <Grid container spacing={0.5}>
                              <Grid item xs={6}>
                                <Typography
                                  variant="body2"
                                  color="text.secondary"
                                  sx={{ fontSize: "0.95rem" }}
                                >
                                  Room Price:
                                </Typography>
                              </Grid>
                              <Grid item xs={6} sx={{ textAlign: "right" }}>
                                <Typography
                                  variant="body1"
                                  sx={{ fontWeight: 500, fontSize: "1rem" }}
                                >
                                  ${room.price || 0}
                                </Typography>
                              </Grid>

                              {room.tax && (
                                <>
                                  <Grid item xs={6}>
                                    <Typography
                                      variant="body2"
                                      color="text.secondary"
                                      sx={{ fontSize: "0.95rem" }}
                                    >
                                      Tax:
                                    </Typography>
                                  </Grid>
                                  <Grid item xs={6} sx={{ textAlign: "right" }}>
                                    <Typography
                                      variant="body1"
                                      sx={{ fontWeight: 500, fontSize: "1rem" }}
                                    >
                                      ${room.tax || 0}
                                    </Typography>
                                  </Grid>
                                </>
                              )}

                              <Grid item xs={12}>
                                <Divider sx={{ my: 0.75 }} />
                              </Grid>

                              <Grid item xs={6}>
                                <Typography
                                  variant="body1"
                                  sx={{ fontWeight: 600, fontSize: "1.05rem" }}
                                >
                                  Room Total:
                                </Typography>
                              </Grid>
                              <Grid item xs={6} sx={{ textAlign: "right" }}>
                                <Typography
                                  variant="body1"
                                  sx={{ fontWeight: 600, fontSize: "1.05rem" }}
                                >
                                  ${(room.price || 0) + (room.tax || 0)}
                                </Typography>
                              </Grid>
                            </Grid>
                          </Box>
                        </Box>
                      </Grid>
                    </Grid>
                  )}
                </CardContent>
              </RoomCard>
            ))}

            {/* Total Price Summary - More Compact */}
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
                        Price Mode
                      </Typography>
                      <Box
                        sx={{
                          display: "flex",
                          alignItems: "center",
                          p: 1,
                          borderRadius: "8px",
                          backgroundColor: "rgba(53, 84, 209, 0.05)",
                        }}
                      >
                        {priceMode === "travClicks" ||
                        priceMode === "travclicks" ||
                        priceMode === "travclick" ? (
                          <Chip
                            label="travcliks"
                            color="primary"
                            sx={{ fontWeight: "bold" }}
                          />
                        ) : priceMode === "dmc" ? (
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              gap: 1,
                            }}
                          >
                            {DmcLogo && (
                              <Avatar
                                src={DmcLogo}
                                alt="DMC Logo"
                                sx={{ width: 32, height: 32 }}
                              />
                            )}
                            <Typography
                              variant="body1"
                              sx={{ fontWeight: "medium" }}
                            >
                              {`${DmcName || "DMC"}'s Mode`}
                            </Typography>
                          </Box>
                        ) : (
                          <Typography
                            variant="body1"
                            sx={{ fontWeight: "medium" }}
                          >
                            {capitalizeFirstLetter(priceMode)}
                          </Typography>
                        )}
                      </Box>
                      {/* <Chip
                        label={priceMode || hotelData?.priceMode || "Standard"}
                        color="primary"
                        size="small"
                        sx={{
                          fontWeight: "medium",
                          height: "24px",
                          fontSize: "0.8rem",
                        }}
                      /> */}
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
                      {/* <Typography variant="body2" color="textSecondary" sx={{ mb: 0.5, display: 'block', fontSize: '0.95rem' }}>
                        Total Price
                      </Typography> */}

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

                            // Get base price (NO TAX APPLIED)
                            const basePrice =
                              totalPrice || hotelData?.totalPrice || 0;

                            // Ceiling the base prices (NO TAX)
                            const sgdPrice = Math.ceil(basePrice);
                            const usdPrice = Math.ceil(
                              basePrice * usdExchangeRate
                            );
                            const convertedPrice = Math.ceil(
                              basePrice * exchangeRate
                            );

                            // Grand totals (WITHOUT TAX)
                            const convertedGrandTotal = convertedPrice;
                            const sgdGrandTotal = sgdPrice;
                            const usdGrandTotal = usdPrice;

                            return (
                              <>
                                {PriceHide === "0" ? (
                                  <>
                                    {/* <Box
                                      sx={{
                                        display: "flex",
                                        justifyContent: "space-between",
                                        alignItems: "center",
                                        mb: 1,
                                        pb: 1,
                                        borderBottom:
                                          "1px solid rgba(255, 255, 255, 0.2)",
                                      }}
                                    >
                                      <Typography
                                        sx={{
                                          fontSize: "0.85rem",
                                          color: "rgba(255, 255, 255, 0.9)",
                                          fontWeight: "medium",
                                        }}
                                      >
                                        Tax Rate
                                      </Typography>
                                      <Chip
                                        label={`${currentTax}%`}
                                        size="small"
                                        sx={{
                                          bgcolor: "rgba(255, 255, 255, 0.2)",
                                          color: "white",
                                          fontWeight: "medium",
                                          height: "20px",
                                          fontSize: "0.7rem",
                                        }}
                                      />
                                    </Box> */}

                                    {/* Current Currency Section */}
                                    <Box sx={{ mb: 1.5 }}>
                                      <Typography
                                        sx={{
                                          fontSize: "0.85rem",
                                          color: "rgba(255, 255, 255, 0.8)",
                                          mb: 0.5,
                                          fontWeight: "medium",
                                        }}
                                      >
                                        {currencyCode}
                                      </Typography>

                                      {/* Total Price (Without Tax) */}
                                      <Box
                                        sx={{
                                          display: "flex",
                                          justifyContent: "space-between",
                                          alignItems: "center",
                                          py: 0.5,
                                          mt: 0.5,
                                          borderTop:
                                            "1px dotted rgba(255, 255, 255, 0.3)",
                                          borderBottom:
                                            "1px solid rgba(255, 255, 255, 0.2)",
                                        }}
                                      >
                                        <Typography
                                          sx={{
                                            fontWeight: "bold",
                                            fontSize: "0.85rem",
                                            color: "white",
                                          }}
                                        >
                                          Total Price
                                        </Typography>
                                        <Typography
                                          sx={{
                                            fontWeight: "bold",
                                            fontSize: "0.95rem",
                                            color: "white",
                                          }}
                                        >
                                          {formatPrice(convertedGrandTotal)}
                                        </Typography>
                                      </Box>
                                    </Box>

                                    {/* Other currencies (without tax) */}
                                    <Box sx={{ mt: 1 }}>
                                      <Typography
                                        sx={{
                                          fontSize: "0.8rem",
                                          color: "rgba(255, 255, 255, 0.7)",
                                          mb: 0.5,
                                        }}
                                      >
                                        Other Currencies
                                      </Typography>

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
                                            fontSize: "0.85rem",
                                            color: "rgba(255, 255, 255, 0.9)",
                                          }}
                                        >
                                          {usdCurrencyCode}
                                        </Typography>
                                        <Typography
                                          sx={{
                                            fontSize: "0.85rem",
                                            color: "rgba(255, 255, 255, 0.9)",
                                          }}
                                        >
                                          {formatPrice(usdGrandTotal)}
                                        </Typography>
                                      </Box>

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
                                            fontSize: "0.85rem",
                                            color: "rgba(255, 255, 255, 0.9)",
                                          }}
                                        >
                                          SGD
                                        </Typography>
                                        <Typography
                                          sx={{
                                            fontSize: "0.85rem",
                                            color: "rgba(255, 255, 255, 0.9)",
                                          }}
                                        >
                                          {formatPrice(sgdGrandTotal)}
                                        </Typography>
                                      </Box>
                                    </Box>
                                  </>
                                ) : (
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
              border: "1px dashed rgba(53, 84, 209, 0.2)",
            }}
          >
            <Typography
              variant="body1"
              color="text.secondary"
              sx={{ fontSize: "0.95rem" }}
            >
              No room details available. Please go back and select rooms.
            </Typography>
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
        {bookingType === "booking" && (
          <Button
            variant="contained"
            onClick={handleFinalSubmit}
            disabled={isSubmitting}
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
              "&:disabled": {
                background: "rgba(53, 84, 209, 0.5)",
                color: "rgba(255, 255, 255, 0.7)",
              },
            }}
          >
            {isSubmitting ? "Booking..." : "Book Now"}
          </Button>
        )}
        {(mode === "dmc" || (Array.isArray(mode) && mode[0] === "dmc")) &&
          bookingType === "enquiry" && (
            <Button
              variant="outlined"
              onClick={handleEnquirySubmit}
              disabled={isEnquiring}
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
                "&:disabled": {
                  borderColor: "rgba(53, 84, 209, 0.3)",
                  color: "rgba(53, 84, 209, 0.5)",
                },
              }}
            >
              {isEnquiring ? "Enquiring..." : "Make an Enquiry"}
            </Button>
          )}
      </Box>
    </Box>
  );
}
