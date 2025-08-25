import React, { useEffect, useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import { useLocation } from "react-router-dom";
import {
  guideslice,
  setData,
  setbookingtype,
} from "@/slice/tourguide/guideslice";
import {
  Localtourslice,
  setpointdata,
  sethourlydata,
  setbookingtype3,
} from "@/slice/localtour/Localslice";
import {
  submitPickupDrop,
  setentrydata,
  setexitdata,
  setResponse,
  setbookingtype1,
} from "@/slice/port/pickupDropSlice";
import {
  selectUserInfo,
  selectBookingResponse,
} from "@/slice/common/customerInfo";
import { setBookingType } from "@/slice/common/commonSlice";
import {
  createBooking,
  setRestaurantsService,
} from "@/slice/restaurant/RestaurantsSlice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { useNavigate } from "react-router-dom";
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
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import AttractionsIcon from "@mui/icons-material/Attractions";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import TourIcon from "@mui/icons-material/Tour";
import EventIcon from "@mui/icons-material/Event";
import GroupIcon from "@mui/icons-material/Group";
import AttachMoneyIcon from "@mui/icons-material/AttachMoney";
import LanguageIcon from "@mui/icons-material/Language";
import dayjs from "dayjs";

// Import components at the top of the file
import EntryPortPointBooking from "./components/EntryPortPointBooking";
import ExitPortBooking from "./components/ExitPortBooking";
import TravelHourlyBooking from "./components/TravelHourlyBooking";
import GuideBooking from "./components/GuideBooking";

// Import necessary selectors
// import {
//   selectGuideData,
//   selectEntryData,
//   selectExitData,
//   selectPointData,
//   selectHourlyData,
// } from "../../../selectors/bookingSelectors";

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

const StyledCardContent = styled(CardContent)(({ theme }) => ({
  padding: "25px !important",
  background: "linear-gradient(to bottom, #FFFFFF, #F8FAFF)",
}));

const InfoBox = styled(Box)(({ theme }) => ({
  backgroundColor: "#F8FAFF",
  padding: "15px 20px",
  borderRadius: "12px",
  border: "1px solid rgba(53, 84, 209, 0.1)",
  transition: "all 0.2s ease",
  "&:hover": {
    backgroundColor: "#F0F4FF",
    borderColor: "rgba(53, 84, 209, 0.2)",
  },
}));

export default function Index2() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const guide = location.state?.guide;
  const vehicles = location.state?.vehicles;
  const type = useSelector((state) => state.tourguide.type);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const [showEnquiry, setShowEnquiry] = useState(false);
  const [commentError, setCommentError] = useState(false);
  // Add state for tracking button loading
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isEnquiring, setIsEnquiring] = useState(false);
  const bookingType = useSelector((state) => state.common.bookingType);
  const [enquiryComment, setEnquiryComment] = useState("");

  //   const tax = (totalPrice * 18) / 100;
  //   const amount = totalPrice - tax;

  const userInfo = useSelector(selectUserInfo);
  const guideData = useSelector(selectUserInfo);
  const entryData = useSelector(selectUserInfo);
  const exitData = useSelector(selectUserInfo);
  const pointData = useSelector(selectUserInfo);
  const hourlyData = useSelector(selectUserInfo);
  const pointzoneData = useSelector(selectUserInfo);
  const image = useSelector((state) => state.tourguide.image);
  const guideBookingDetails = useSelector((state) => state.tourguide.details);
  const entryportBookingDetails = useSelector(
    (state) => state.pickupDrop.details
  );
  const exitportBookingDetails = useSelector(
    (state) => state.pickupDrop.details1
  );
  const travelpointBookingDetails = useSelector(
    (state) => state.localtour.details
  );
  const travelhourlyBookingDetails = useSelector(
    (state) => state.localtour.details1
  );
  const travelpointzoneBookingDetails = useSelector(
    (state) => state.localtour.details
  );
  const mode =
    type === "guide"
      ? useSelector((state) => state.tourguide.selectedGuide?.mode)
      : type === "entryport" || type === "exitport"
      ? useSelector((state) => state.pickupDrop.selectedVehicle?.mode)
      : type === "travelpoint" ||
        type === "travelhourly" ||
        type === "travelpointzone"
      ? useSelector((state) => state.localtour.selectedVehicle?.mode)
      : "dmc";
  console.log("mode88", mode);

  const bookingDetails =
    type === "guide"
      ? guideBookingDetails
      : type === "entryport"
      ? entryportBookingDetails
      : type === "exitport"
      ? exitportBookingDetails
      : type === "travelpoint"
      ? travelpointBookingDetails
      : type === "travelhourly"
      ? travelhourlyBookingDetails
      : type === "travelpointzone"
      ? travelpointzoneBookingDetails
      : null;
  const [enquiryAmount, setEnquiryAmount] = useState(
    bookingDetails[0]?.totalPrice
  );
  //   useEffect(() => {
  //     if (!userInfo || !userInfo.fullName) {
  //       navigate("/dashboard/db-dashboard/tour-single/:id");
  //     }
  //   }, [userInfo, navigate]);

  // Handle form change and dispatch actions based on type
  console.log("bookingDetailsindex2", bookingDetails);
  const handleFormChange = () => {
    // Determine which data to update based on type
    let currentData;
    switch (type) {
      case "guide":
        currentData = { ...guideData };
        break;
      case "entryport":
        currentData = { ...entryData };
        break;
      case "exitport":
        currentData = { ...exitData };
        break;
      case "travelpoint":
        currentData = { ...pointData };
        break;
      case "travelhourly":
        currentData = { ...hourlyData };
        break;
      case "travelpointzone":
        currentData = { ...pointzoneData };
        break;
      default:
        console.error("Invalid booking type:", type);
        return;
    }

    // Create a copy of the current data, preserving its structure but without userInfo
    const updatedData = Object.assign({}, currentData);

    // Initialize a new userInfo object
    const newUserInfo = {};

    // Only add properties from userInfo that have values to the new userInfo object
    if (userInfo) {
      Object.keys(userInfo).forEach((key) => {
        if (
          userInfo[key] !== undefined &&
          userInfo[key] !== null &&
          userInfo[key] !== ""
        ) {
          newUserInfo[key] = userInfo[key];
        }
      });
    }

    // Add the new userInfo object to updatedData only if it has properties
    if (Object.keys(newUserInfo).length > 0) {
      updatedData.userInfo = newUserInfo;
    }

    // Dispatch the updated data
    switch (type) {
      case "guide":
        dispatch(setData(updatedData));
        break;
      case "entryport":
        dispatch(setentrydata(updatedData));
        break;
      case "exitport":
        dispatch(setexitdata(updatedData));
        break;
      case "travelpoint":
        dispatch(setpointdata(updatedData));
        break;
      case "travelhourly":
        dispatch(sethourlydata(updatedData));
        break;
      case "travelpointzone":
        dispatch(setpointdata(updatedData));
        break;
    }
  };

  // Use useEffect to call handleFormChange when userInfo or type changes
  useEffect(() => {
    if (userInfo) {
      handleFormChange();
    }
  }, [userInfo, type]);

  // const handleEnquiryAmountChange = (e) => {
  //   const newValue = parseFloat(e.target.value);
  //   if (newValue <= bookingDetails[0]?.totalPrice && newValue > 0) {
  //     setEnquiryAmount(newValue);

  //     // Update form state
  //     const updatedForm = {
  //       ...bookingDetails[0],
  //       enquiryAmount: newValue,
  //     };

  //     if (type === "guide") {
  //       dispatch(setData(updatedForm));
  //     } else if (type === "entryport") {
  //       dispatch(setentrydata(updatedForm));
  //     } else if (type === "exitport") {
  //       dispatch(setexitdata(updatedForm));
  //     } else if (type === "travelpoint") {
  //       dispatch(setpointdata(updatedForm));
  //     } else if (type === "travelhourly") {
  //       dispatch(sethourlydata(updatedForm));
  //     } else {
  //       console.error("Invalid booking type:", type);
  //     }
  //   }
  // };

  // Update the handleEnquiryCommentChange function
  // const handleEnquiryCommentChange = (e) => {
  //   const newValue = e.target.value;
  //   setEnquiryComment(newValue);

  //   // Update the form with the new enquiry comment
  //   const updatedForm = {
  //     ...bookingDetails[0],
  //     enquiryComment: newValue,
  //   };

  //   if (type === "guide") {
  //     dispatch(setData(updatedForm));
  //   } else if (type === "entryport") {
  //     dispatch(setentrydata(updatedForm));
  //   } else if (type === "exitport") {
  //     dispatch(setexitdata(updatedForm));
  //   } else if (type === "travelpoint") {
  //     dispatch(setpointdata(updatedForm));
  //   } else if (type === "travelhourly") {
  //     dispatch(sethourlydata(updatedForm));
  //   } else {
  //     console.error("Invalid booking type:", type);
  //   }

  //   setCommentError(false);
  // };

  const handleClick = () => {
    if (type === "entryport" || type === "exitport") {
      navigate("/dashboard/db-dashboard/activity-single-1", {
        state: { vehicles: vehicles },
      });
    } else if (
      type === "travelpoint" ||
      type === "travelhourly" ||
      type === "travelpointzone"
    ) {
      navigate("/dashboard/db-dashboard/activity-single-2", {
        state: { vehicles: vehicles },
      });
    } else if (type === "guide") {
      navigate("/dashboard/db-dashboard/activity-single", {
        state: { guide: guide },
      });
    }
  };

  const handleSubmit = async () => {
    let response = null;
    dispatch(setbookingtype("booking"));
    dispatch(setbookingtype3("booking"));
    dispatch(setbookingtype1("booking"));
    setIsSubmitting(true);

    // Create payload with static bookingType
    const updatedBookingDetails = {
      ...bookingDetails[0],
      bookingType: "booking", // Add static bookingType
    };

    // Update Redux state with the booking details
    if (type === "guide") {
      dispatch(setData(updatedBookingDetails));
      response = await dispatch(guideslice()).unwrap();
    } else if (type === "entryport") {
      dispatch(setentrydata(updatedBookingDetails));
      response = await dispatch(
        submitPickupDrop({ selectedType: "entry" })
      ).unwrap();
    } else if (type === "exitport") {
      dispatch(setexitdata(updatedBookingDetails));
      response = await dispatch(
        submitPickupDrop({ selectedType: "exit" })
      ).unwrap();
    } else if (type === "travelpoint") {
      dispatch(setpointdata(updatedBookingDetails));
      response = await dispatch(
        Localtourslice({ selectedType: "travelpoint" })
      ).unwrap();
    } else if (type === "travelhourly") {
      dispatch(sethourlydata(updatedBookingDetails));
      response = await dispatch(
        Localtourslice({ selectedType: "travelhourly" })
      ).unwrap();
    } else if (type === "travelpointzone") {
      dispatch(setpointdata(updatedBookingDetails));
      response = await dispatch(
        Localtourslice({ selectedType: "travelpointzone" })
      ).unwrap();
    } else {
      console.error("Invalid booking type:", type);
      setIsSubmitting(false);
      toast.error("Invalid booking type. Please try again.", {
        position: "top-center",
        autoClose: 3000,
      });
      return;
    }

    console.log("API Response:", response);

    if (response) {
      dispatch(setResponse(response));

      dispatch(setBookingType(response.order?.bookingType));
      // Check that service exists and has the expected structure
      if (response.service && response.service.date_service) {
        dispatch(setDateService(response.service.date_service));
        toast.success("Booking successful!", {
          position: "top-center",
          autoClose: 3000,
        });
      }

      // Process travel data - add this section
      if (
        response.service &&
        response.service.data &&
        Array.isArray(response.service.data)
      ) {
        const travelPointData = response.service.data.filter(
          (item) => item.bookingType && item.selectedHours === undefined
        );
        const travelHourlyData = response.service.data.filter(
          (item) => item.selectedHours !== undefined
        );

        console.log(
          "Process in component - Travel point data:",
          travelPointData
        );
        console.log(
          "Process in component - Travel hourly data:",
          travelHourlyData
        );

        // You could dispatch additional actions here if needed
      }

      setTimeout(() => {
        navigate("/dashboard/db-dashboard/ThankYou");
      }, 500);
      setIsSubmitting(false);
    } else {
      throw new Error("Invalid response received from the server.");
    }
  };

  const handleEnquirySubmit = async () => {
    try {
      // First validate the comment
      // if (!enquiryComment.trim()) {
      //   setCommentError(true);
      //   toast.error("Please enter a comment for your enquiry");
      //   return;
      // }
      let response = null;
      dispatch(setbookingtype("enquiry"));
      dispatch(setbookingtype3("enquiry"));
      dispatch(setbookingtype1("enquiry"));
      setIsEnquiring(true);

      // Create payload with static bookingType for all services
      const updatedBookingDetails = {
        ...bookingDetails[0],
        bookingType: "enquiry", // Add static bookingType
        // enquiryAmount: enquiryAmount,
        // enquiryComment: enquiryComment,
      };

      // Update Redux state with the updated booking details
      if (type === "guide") {
        dispatch(setData(updatedBookingDetails));
        response = await dispatch(guideslice()).unwrap();
      } else if (type === "entryport") {
        dispatch(setentrydata(updatedBookingDetails));
        response = await dispatch(
          submitPickupDrop({ selectedType: "entry" })
        ).unwrap();
      } else if (type === "exitport") {
        dispatch(setexitdata(updatedBookingDetails));
        response = await dispatch(
          submitPickupDrop({ selectedType: "exit" })
        ).unwrap();
      } else if (type === "travelpoint") {
        dispatch(setpointdata(updatedBookingDetails));
        response = await dispatch(
          Localtourslice({ selectedType: "travelpoint" })
        ).unwrap();
      } else if (type === "travelhourly") {
        dispatch(sethourlydata(updatedBookingDetails));
        response = await dispatch(
          Localtourslice({ selectedType: "travelhourly" })
        ).unwrap();
      } else if (type === "travelpointzone") {
        dispatch(setpointdata(updatedBookingDetails));
        response = await dispatch(
          Localtourslice({ selectedType: "travelpointzone" })
        ).unwrap();
      } else {
        console.error("Invalid booking type:", type);
        toast.error("Invalid booking type. Please try again.", {
          position: "top-center",
          autoClose: 3000,
        });
        return;
      }

      console.log("API Response:", response);

      // Dispatch the response only if it's valid
      if (response) {
        dispatch(setResponse(response));
        dispatch(setBookingType(response.order?.bookingType));

        // If the response contains a service date, update Redux state
        if (response?.service?.date_service) {
          dispatch(setDateService(response.service.date_service));
          toast.success("Enquiry submitted successfully!", {
            position: "top-center",
            autoClose: 3000,
          });
        }

        // Navigate to the Thank You page after a short delay to ensure Redux updates
        setTimeout(() => {
          navigate("/dashboard/db-dashboard/ThankYou");
        }, 500);
        setIsEnquiring(false);
      } else {
        throw new Error("Invalid response received from the server.");
      }
    } catch (error) {
      console.error("Error during submission:", error);
      setIsEnquiring(false);
      // Handle errors and show user-friendly messages
      toast.error(
        error.message || "Something went wrong. Please try again later.",
        {
          position: "top-center",
          autoClose: 3000,
        }
      );
    }
  };

  // Add these helper functions to your component, before the return statement

  // Function to get the price for the current service type with exchange rate applied
  // Comment out the helper functions if they were added
  /*
  const getPriceForCurrentType = (rate) => {
    // Check all possible data sources for price based on the current type
    if (type === "pointtopoint" && pointtopoint && pointtopoint[0]?.totalPrice) {
      return (pointtopoint[0].totalPrice * rate).toFixed(2);
    } 
    else if (type === "travelhourly" && hourly && hourly[0]?.totalPrice) {
      return (hourly[0].totalPrice * rate).toFixed(2);
    }
    else if (type === "entryport" && entryport && entryport[0]?.totalPrice) {
      return (entryport[0].totalPrice * rate).toFixed(2);
    }
    else if (type === "exitport" && exitport && exitport[0]?.totalPrice) {
      return (exitport[0].totalPrice * rate).toFixed(2);
    }
    // Add more service types as needed
    else {
      return "0.00"; // Fallback if no price is available
    }
  };

  // Function to get price in SGD (base currency)
  const getPriceInSGD = () => {
    if (type === "pointtopoint" && pointtopoint && pointtopoint[0]?.totalPrice) {
      return pointtopoint[0].totalPrice;
    } 
    else if (type === "travelhourly" && hourly && hourly[0]?.totalPrice) {
      return hourly[0].totalPrice;
    }
    else if (type === "entryport" && entryport && entryport[0]?.totalPrice) {
      return entryport[0].totalPrice;
    }
    else if (type === "exitport" && exitport && exitport[0]?.totalPrice) {
      return exitport[0].totalPrice;
    }
    // Add more service types as needed
    else {
      return "0.00"; // Fallback if no price is available
    }
  };
  */

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
        mt: 3,
      }}
    >
      {/* Header Section with Gradient Background */}
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
          onClick={handleClick}
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
          {type === "guide" ? (
            <TourIcon sx={{ fontSize: 24, mr: 1 }} />
          ) : (
            <DirectionsCarIcon sx={{ fontSize: 24, mr: 1 }} />
          )}
          <Typography
            variant="h6"
            sx={{ fontWeight: "bold", fontSize: "1.1rem" }}
          >
            {type === "guide"
              ? "Tour Guide"
              : type === "travelhourly" ||
                type === "travelpoint" ||
                type === "travelpointzone"
              ? "Local Transfer"
              : "Airport Transfer"}{" "}
            Booking Details
          </Typography>
        </Box>
        <Chip
          label={
            type === "guide"
              ? "Guide"
              : type === "travelhourly"
              ? "Hourly"
              : type === "travelpoint" || type === "travelpointzone"
              ? "Point to Point"
              : type === "entryport"
              ? "Entry Port"
              : "Exit Port"
          }
          size="small"
          sx={{
            backgroundColor: "rgba(255, 255, 255, 0.2)",
            color: "white",
            fontWeight: "bold",
            height: "24px",
            fontSize: "0.8rem",
          }}
        />
      </Box>

      {type === "entryport" ||
      type === "travelpoint" ||
      type === "travelpointzone" ? (
        <EntryPortPointBooking
          bookingDetails={bookingDetails[0]}
          currencyCode={currencyCode}
          usdCurrencyCode={usdCurrencyCode}
          exchangeRate={exchangeRate}
          usdExchangeRate={usdExchangeRate}
          Tax={bookingDetails[0].Tax}
        />
      ) : type === "exitport" ? (
        <ExitPortBooking
          bookingDetails={bookingDetails[0]}
          currencyCode={currencyCode}
          usdCurrencyCode={usdCurrencyCode}
          exchangeRate={exchangeRate}
          usdExchangeRate={usdExchangeRate}
          Tax={bookingDetails[0].Tax}
        />
      ) : type === "travelhourly" ? (
        <TravelHourlyBooking
          bookingDetails={bookingDetails[0]}
          currencyCode={currencyCode}
          usdCurrencyCode={usdCurrencyCode}
          exchangeRate={exchangeRate}
          usdExchangeRate={usdExchangeRate}
          Tax={bookingDetails[0].Tax}
        />
      ) : type === "guide" ? (
        <GuideBooking
          bookingDetails={bookingDetails[0]}
          currencyCode={currencyCode}
          usdCurrencyCode={usdCurrencyCode}
          exchangeRate={exchangeRate}
          usdExchangeRate={usdExchangeRate}
          Tax={bookingDetails[0].Tax}
        />
      ) : (
        // Default case - perhaps a generic booking display or error message
        <Typography variant="h6" color="error" align="center" sx={{ my: 4 }}>
          Unknown booking type: {type}
        </Typography>
      )}

      {/* Action Buttons */}
      <Box
        sx={{
          display: "flex",
          justifyContent: "center",
          gap: "10px",
          mt: 3,
          mb: 2,
        }}
      >
        {/* Show Book Now button only if bookingType is "booking" */}
        {bookingType === "booking" && (
          <Button
            variant="contained"
            onClick={handleSubmit}
            startIcon={<Box component="span">✓</Box>}
            size="medium"
            disabled={isSubmitting}
            sx={{
              borderRadius: "6px",
              px: 2.5,
              py: 1,
              background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
              fontWeight: "bold",
              fontSize: "1.05rem",
              textTransform: "none",
              "&:hover": {
                background: "linear-gradient(135deg, #2c44a9 0%, #435ce0 100%)",
                boxShadow: "0 4px 10px rgba(53, 84, 209, 0.3)",
              },
              "&:disabled": {
                background: "linear-gradient(135deg, #a0a0a0 0%, #c0c0c0 100%)",
                color: "rgba(255, 255, 255, 0.7)",
              },
            }}
          >
            {isSubmitting ? "Booking..." : "Book Now"}
          </Button>
        )}

        {/* Show Make an Enquiry button only if mode is "dmc" AND bookingType is "enquiry" */}
        {mode === "dmc" && bookingType === "enquiry" && (
          <Button
            variant="outlined"
            onClick={handleEnquirySubmit}
            size="medium"
            disabled={isEnquiring}
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
            {isEnquiring ? "Enquiring..." : "Make an Enquiry"}
          </Button>
        )}
      </Box>

      {/* Enquiry Form */}
      {/* {showEnquiry && (
        <Card
          elevation={2}
          sx={{
            borderRadius: "10px",
            overflow: "hidden",
            transition: "transform 0.2s ease, box-shadow 0.2s ease",
            "&:hover": {
              transform: "translateY(-3px)",
              boxShadow: "0 6px 16px rgba(0,0,0,0.1)",
            },
            mt: 2,
            mb: 3,
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
                ?
              </Box>
              Enquiry Details
            </Typography>

            <Grid container spacing={2}>
              <Grid item xs={12} md={6}>
                <Paper
                  elevation={0}
                  sx={{
                    p: 1.5,
                    borderRadius: "8px",
                    border: "1px solid rgba(53, 84, 209, 0.1)",
                    bgcolor: "rgba(53, 84, 209, 0.03)",
                  }}
                >
                  <Typography
                    variant="body2"
                    color="textSecondary"
                    sx={{ mb: 0.5, display: "block", fontSize: "0.95rem" }}
                  >
                    Negotiated Amount
                  </Typography>

                  <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                    <TextField
                      fullWidth
                      type="number"
                      value={enquiryAmount}
                      onChange={(e) => handleEnquiryAmountChange(e)}
                      InputProps={{
                        inputProps: {
                          max: bookingDetails[0]?.totalPrice,
                          min: 1,
                          style: { fontSize: "1rem" },
                        },
                        startAdornment: (
                          <InputAdornment position="start">$</InputAdornment>
                        ),
                      }}
                      variant="outlined"
                      size="small"
                      sx={{ mb: 0.5 }}
                    />
                  </Box>

                  <Typography
                    variant="body2"
                    color="textSecondary"
                    sx={{ fontSize: "0.9rem" }}
                  >
                    Maximum amount: ${bookingDetails[0]?.totalPrice}
                  </Typography>
                </Paper>
              </Grid>

              <Grid item xs={12}>
                <Paper
                  elevation={0}
                  sx={{
                    p: 1.5,
                    borderRadius: "8px",
                    border: "1px solid rgba(53, 84, 209, 0.1)",
                    bgcolor: "rgba(53, 84, 209, 0.03)",
                  }}
                >
                  <Typography
                    variant="body2"
                    color="textSecondary"
                    sx={{ mb: 0.5, display: "block", fontSize: "0.95rem" }}
                  >
                    Comment <span style={{ color: "#f44336" }}>*</span>
                  </Typography>

                  <TextField
                    fullWidth
                    multiline
                    rows={3}
                    placeholder="Enter your comment for this enquiry"
                    value={enquiryComment}
                    onChange={(e) => handleEnquiryCommentChange(e)}
                    error={commentError}
                    helperText={commentError ? "Comment is required" : ""}
                    variant="outlined"
                    size="small"
                    InputProps={{
                      style: { fontSize: "1rem" },
                    }}
                  />
                </Paper>
              </Grid>

              <Grid
                item
                xs={12}
                sx={{ display: "flex", justifyContent: "flex-end" }}
              >
                <Button
                  variant="contained"
                  onClick={handleEnquirySubmit}
                  size="medium"
                  sx={{
                    borderRadius: "6px",
                    px: 2,
                    py: 0.75,
                    background:
                      "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
                    textTransform: "none",
                    fontWeight: "medium",
                    fontSize: "0.95rem",
                    "&:hover": {
                      background:
                        "linear-gradient(135deg, #2c44a9 0%, #435ce0 100%)",
                      boxShadow: "0 4px 10px rgba(53, 84, 209, 0.3)",
                    },
                  }}
                >
                  Submit Enquiry
                </Button>
              </Grid>
            </Grid>
          </CardContent>
        </Card>
      )} */}
    </Box>
  );
}
