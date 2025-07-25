import React from "react";
import PropTypes from "prop-types";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  IconButton,
  Typography,
  Grid,
  Divider,
  Card,
  CardMedia,
  CardContent,
  Paper,
  Box,
  Chip,
  Avatar,
  AvatarGroup,
  Button,
  alpha,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import AttractionIcon from "@mui/icons-material/Attractions";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import CurrencyExchangeIcon from "@mui/icons-material/CurrencyExchange";
import PersonIcon from "@mui/icons-material/Person";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import ElderlyIcon from "@mui/icons-material/Elderly";
import GroupIcon from "@mui/icons-material/Group";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import ConfirmationNumberIcon from "@mui/icons-material/ConfirmationNumber";
import { useSelector } from "react-redux";

// Utility functions
const utils = {
  capitalizeFirstLetter: (string) => {
    if (!string) return "N/A";
    const str = String(string);
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
  },

  formatPackageName: (packageName) => {
    if (!packageName) return "N/A";
    return packageName
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ');
  },

  formatDate: (date) => {
    if (!date) return "N/A";

    try {
      // Convert any date format to "Sun, 20 Apr'25"
      const dateObj = new Date(date);
      if (!isNaN(dateObj.getTime())) {
        const day = dateObj.getDate();
        const month = dateObj.toLocaleString("en-US", { month: "short" });
        const year = dateObj.getFullYear().toString().slice(2);
        const weekday = dateObj.toLocaleString("en-US", { weekday: "short" });
        return `${weekday}, ${day} ${month}'${year}`;
      }
    } catch (error) {
      console.error("Error formatting date:", error);
    }

    return date;
  },

  calculatePrices: (totalPrice, exchangeRate, usdExchangeRate) => {
    const sgdPrice = parseFloat(totalPrice) || 0;
    const usdPrice = sgdPrice * usdExchangeRate;
    const convertedPrice = sgdPrice * exchangeRate;
    return { sgdPrice, usdPrice, convertedPrice };
  },

  formatSelectionType: (selection) => {
    switch (selection) {
      case "withoutTraveller":
      case "withoutTransport":
        return "Only Attraction";
      case "withPrivate":
        return "Attraction with Transport (Private)";
      case "withShare":
        return "Attraction with Transport (Share)";
      default:
        return "Only Attraction";
    }
  },
};

// Reusable components
const InfoField = ({ label, value }) => (
  <Grid item xs={12} sm={6}>
    <Typography
      variant="subtitle2"
      color="textSecondary"
      sx={{ fontSize: "0.75rem" }}
    >
      {label}
    </Typography>
    <Typography variant="body2">{value}</Typography>
  </Grid>
);

const SectionTitle = ({ title }) => (
  <Grid item xs={12}>
    <Typography variant="subtitle1" gutterBottom sx={{ mt: 1 }}>
      {title}
    </Typography>
    <Divider sx={{ mb: 1 }} />
  </Grid>
);

// Main component
const AttractionBookingModal = ({ open, onClose, booking }) => {
  // Add selector for DMC info
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  const { bookings } = useSelector((state) => state.viewDetails);

  // Add these selectors at the top with other selectors
  const priceMode =
    useSelector((state) => state.hotels.searchState.priceMode) || "dmc";
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);

  // Get tax percentages from auth slice like in index2.jsx
  const currentTax = useSelector((state) => state.auth.currentTax || 0);
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const usdTax = useSelector((state) => state.auth.usdTax || 0);

  // IMPORTANT: Get the attractionDetails tax_percentage before using any conditional returns
  const attractionDetailsTaxPercentage = useSelector(
    (state) => state.attractions.attractionDetails?.tax_percentage || 0
  );

  // Add PriceHide selector at the top with other hooks
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  if (!booking) return null;

  // Debug logging to see what data is received
  console.log('AttractionBookingModal - Received booking:', booking);
  console.log('AttractionBookingModal - bookingType:', booking.bookingType);
  console.log('AttractionBookingModal - package_details:', booking.package_details);
  console.log('AttractionBookingModal - package_type:', booking.package_type);

  // Data normalization
  const serviceDetails =
    booking.service_details ||
    booking.service ||
    bookings?.attraction?.[0]?.service_details ||
    {};

  // Calculate tax percentage after serviceDetails is defined - REMOVING THIS AS WE'RE USING AUTH SLICE TAXES
  // const taxPercentage = attractionDetailsTaxPercentage ||
  //   serviceDetails?.tax_percentage ||
  //   booking?.service_details?.tax_percentage || 0;

  const masterImage =
    serviceDetails.master_image ||
    serviceDetails.masterImage ||
    bookings?.attraction?.[0]?.service_details?.master_image;

  // For location and country, check multiple possible paths
  const location =
    serviceDetails.location ||
    booking.location ||
    booking.service?.location ||
    bookings?.attraction?.[0]?.service_details?.location ||
    "N/A";

  const country =
    serviceDetails.country ||
    booking.country ||
    booking.service?.country ||
    booking.destination || // Check if country is in destination
    serviceDetails.destination ||
    bookings?.attraction?.[0]?.service_details?.country ||
    "N/A";

  // Calculate guest counts
  const adultCount = booking.adultCount || 0;
  const childCount = booking.childCount || 0;
  const seniorCount = booking.seniorCount || 0;
  const totalGuests = adultCount + childCount + seniorCount;

  // Calculate price with tax
  const basePrice = parseFloat(booking.totalPrice) || 0;

  // Ceiling the base prices
  const sgdPrice = Math.ceil(basePrice);
  const usdPrice = Math.ceil(basePrice * usdExchangeRate);
  const convertedPrice = Math.ceil(basePrice * exchangeRate);

  // Calculate tax amount based on ceiling prices and tax rates from auth slice
  const currentTaxAmount = Math.ceil((convertedPrice * currentTax) / 100);
  const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
  const usdTaxAmount = Math.ceil((usdPrice * usdTax) / 100);

  // Calculate grand totals
  const sgdGrandTotal = sgdPrice + sgdTaxAmount;
  const usdGrandTotal = usdPrice + usdTaxAmount;
  const convertedGrandTotal = convertedPrice + currentTaxAmount;

  // Check if current tax matches SGD or USD tax to hide respective portions
  const showSgdPortion = currentTax !== sgdTax;
  const showUsdPortion = currentTax !== usdTax;

  // Get transportation type
  const transportType = utils.formatSelectionType(booking.Selection);

  return (
    <Dialog
      open={open}
      onClose={onClose}
      maxWidth="md"
      fullWidth
      PaperProps={{
        sx: {
          borderRadius: "12px",
          overflow: "hidden",
        },
      }}
    >
      <DialogTitle
        sx={{
          background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
          color: "#fff",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          py: 2,
        }}
      >
        <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
          <AttractionIcon fontSize="large" />
          <Typography variant="h6" sx={{ fontWeight: "bold" }}>
            Attraction Booking Details
          </Typography>
        </Box>
        <IconButton
          edge="end"
          color="inherit"
          onClick={onClose}
          aria-label="close"
          sx={{ color: "#fff" }}
        >
          <CloseIcon />
        </IconButton>
      </DialogTitle>
      <DialogContent sx={{ mt: 2, position: "relative", p: 3 }}>
        <Grid container spacing={3}>
          {/* Attraction/Package Information */}
          <Grid item xs={12}>
            {(booking.bookingType === 'package' || booking.package_type === 1) && (booking.package_details || booking.packageAttractions) ? (
              // Package Display
              <Card
                elevation={2}
                sx={{
                  borderRadius: "16px",
                  overflow: "hidden",
                  transition: "transform 0.3s ease, box-shadow 0.3s ease",
                  "&:hover": {
                    transform: "translateY(-4px)",
                    boxShadow: "0 8px 24px rgba(0,0,0,0.12)",
                  },
                }}
              >
                <CardContent sx={{ p: 3 }}>
                  <Box sx={{ display: "flex", alignItems: "center", mb: 3 }}>
                    <AttractionIcon
                      sx={{ color: "#FF9800", mr: 1, fontSize: 28 }}
                    />
                                         <Typography variant="h5" sx={{ fontWeight: "bold" }}>
                       {utils.formatPackageName(booking.package_details?.package_name || booking.ticketName) || "Package Booking"}
                     </Typography>
                    <Chip
                      label="Package"
                      sx={{
                        ml: 2,
                        bgcolor: alpha('#FF9800', 0.1),
                        color: '#E65100',
                        fontWeight: 'bold',
                        fontSize: '0.75rem'
                      }}
                    />
                  </Box>

                  <Grid container spacing={2} sx={{ mb: 3 }}>
                    <Grid item xs={12} sm={6}>
                      <Box
                        sx={{ display: "flex", alignItems: "center", mb: 1 }}
                      >
                        <CalendarTodayIcon sx={{ color: "#FF9800", mr: 1 }} />
                        <Typography variant="body2" color="textSecondary">
                          Booking Date
                        </Typography>
                      </Box>
                      <Typography
                        variant="body1"
                        sx={{ fontWeight: "medium", ml: 4 }}
                      >
                        {booking.bookingDate
                          ? utils.formatDate(booking.bookingDate)
                          : "N/A"}
                      </Typography>
                    </Grid>
                    <Grid item xs={12} sm={6}>
                      <Box
                        sx={{ display: "flex", alignItems: "center", mb: 1 }}
                      >
                        <AttractionIcon sx={{ color: "#FF9800", mr: 1 }} />
                        <Typography variant="body2" color="textSecondary">
                          Total Attractions
                        </Typography>
                      </Box>
                      <Typography
                        variant="body1"
                        sx={{ fontWeight: "medium", ml: 4 }}
                      >
                        {booking.package_details?.package_total_attractions || booking.package_details?.package_attractions?.length || booking.packageAttractions?.length || 0}
                      </Typography>
                    </Grid>
                  </Grid>

                  <Typography variant="h6" sx={{ fontWeight: "bold", mb: 2 }}>
                    Attractions in Package:
                  </Typography>
                  
                  <Grid container spacing={2}>
                    {(booking.package_details?.package_attractions || booking.packageAttractions)?.map((attraction, index) => (
                      <Grid item xs={12} md={6} key={index}>
                        <Card
                          elevation={1}
                          sx={{
                            borderRadius: "12px",
                            overflow: "hidden",
                            height: "100%",
                            transition: "transform 0.2s ease",
                            "&:hover": {
                              transform: "translateY(-2px)",
                            },
                          }}
                        >
                          <Grid container>
                            <Grid item xs={4}>
                              <Box
                                sx={{
                                  height: "100px",
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "center",
                                  backgroundColor: "rgba(255, 152, 0, 0.05)",
                                  position: "relative",
                                  overflow: "hidden",
                                }}
                              >
                                {attraction.master_image || attraction.image ? (
                                  <CardMedia
                                    component="img"
                                    image={attraction.master_image || attraction.image}
                                    alt={attraction.name || attraction.attraction_name || "Attraction"}
                                    sx={{
                                      width: "100%",
                                      height: "100%",
                                      objectFit: "cover",
                                      position: "absolute",
                                      top: 0,
                                      left: 0,
                                    }}
                                  />
                                ) : (
                                  <AttractionIcon
                                    sx={{
                                      fontSize: 40,
                                      color: "#FF9800",
                                      opacity: 0.7,
                                    }}
                                  />
                                )}
                              </Box>
                            </Grid>
                            <Grid item xs={8}>
                              <CardContent sx={{ p: 2, "&:last-child": { pb: 2 } }}>
                                <Typography variant="subtitle1" sx={{ fontWeight: "bold", mb: 1 }}>
                                  {attraction.name || attraction.attraction_name}
                                </Typography>
                                <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                                  <LocationOnIcon sx={{ color: "#FF9800", mr: 0.5, fontSize: 16 }} />
                                  <Typography variant="body2" color="textSecondary">
                                    {attraction.location || attraction.city}, {attraction.country}
                                  </Typography>
                                </Box>
                              </CardContent>
                            </Grid>
                          </Grid>
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </CardContent>
              </Card>
            ) : (
              // Single Attraction Display
              <Card
                elevation={2}
                sx={{
                  borderRadius: "16px",
                  overflow: "hidden",
                  transition: "transform 0.3s ease, box-shadow 0.3s ease",
                  "&:hover": {
                    transform: "translateY(-4px)",
                    boxShadow: "0 8px 24px rgba(0,0,0,0.12)",
                  },
                }}
              >
                <Grid container>
                  <Grid item xs={12} md={4}>
                    <Box
                      sx={{
                        height: "100%",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        backgroundColor: "rgba(53, 84, 209, 0.05)",
                        minHeight: "220px",
                        position: "relative",
                        overflow: "hidden",
                      }}
                    >
                      {masterImage ? (
                        <CardMedia
                          component="img"
                          image={masterImage}
                          alt={booking.AttractionName || "Attraction"}
                          sx={{
                            width: "100%",
                            height: "100%",
                            objectFit: "cover",
                            position: "absolute",
                            top: 0,
                            left: 0,
                          }}
                        />
                      ) : (
                        <Box
                          sx={{
                            display: "flex",
                            flexDirection: "column",
                            alignItems: "center",
                            justifyContent: "center",
                            p: 2,
                          }}
                        >
                          <AttractionIcon
                            sx={{
                              fontSize: 80,
                              color: "#3554D1",
                              opacity: 0.7,
                              mb: 2,
                            }}
                          />
                          <Typography
                            variant="body2"
                            color="textSecondary"
                            align="center"
                          >
                            No attraction image available
                          </Typography>
                        </Box>
                      )}
                    </Box>
                  </Grid>
                  <Grid item xs={12} md={8}>
                    <CardContent sx={{ p: 3 }}>
                      <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                        <AttractionIcon
                          sx={{ color: "#3554D1", mr: 1, fontSize: 28 }}
                        />
                        <Typography variant="h5" sx={{ fontWeight: "bold" }}>
                          {booking.AttractionName || "N/A"}
                        </Typography>
                      </Box>

                      <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                        <LocationOnIcon sx={{ color: "#3554D1", mr: 1 }} />
                        <Typography variant="body1">
                          {`${location}, ${country}`}
                        </Typography>
                      </Box>

                      <Grid container spacing={2}>
                        <Grid item xs={12} sm={6}>
                          <Box
                            sx={{ display: "flex", alignItems: "center", mb: 1 }}
                          >
                            <CalendarTodayIcon sx={{ color: "#3554D1", mr: 1 }} />
                            <Typography variant="body2" color="textSecondary">
                              Booking Date
                            </Typography>
                          </Box>
                          <Typography
                            variant="body1"
                            sx={{ fontWeight: "medium", ml: 4 }}
                          >
                            {booking.bookingDate
                              ? utils.formatDate(booking.bookingDate)
                              : "N/A"}
                          </Typography>
                        </Grid>
                        <Grid item xs={12} sm={6}>
                          <Box
                            sx={{ display: "flex", alignItems: "center", mb: 1 }}
                          >
                            <ConfirmationNumberIcon
                              sx={{ color: "#3554D1", mr: 1 }}
                            />
                            <Typography variant="body2" color="textSecondary">
                              Ticket Name
                            </Typography>
                          </Box>
                          <Typography
                            variant="body1"
                            sx={{ fontWeight: "medium", ml: 4 }}
                          >
                            {booking?.ticketName}
                          </Typography>
                        </Grid>
                      </Grid>
                    </CardContent>
                  </Grid>
                </Grid>
              </Card>
            )}
          </Grid>

          {/* Guest Information Card */}
          <Grid item xs={12} container spacing={2}>
            <Grid item xs={12} md={6}>
              <Card
                elevation={2}
                sx={{
                  borderRadius: "16px",
                  overflow: "hidden",
                  transition: "transform 0.3s ease, box-shadow 0.3s ease",
                  "&:hover": {
                    transform: "translateY(-4px)",
                    boxShadow: "0 8px 24px rgba(0,0,0,0.12)",
                  },
                  mb: 2,
                  height: "100%", // Ensures the card takes full height
                }}
              >
                <CardContent sx={{ p: 3 }}>
                  <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                    <GroupIcon sx={{ color: "#3554D1", mr: 1, fontSize: 28 }} />
                    <Typography variant="h6" sx={{ fontWeight: "bold" }}>
                      Guest Information
                    </Typography>
                  </Box>

                  <Paper
                    elevation={1}
                    sx={{
                      p: 2,
                      borderRadius: "12px",
                      height: "100%",
                      backgroundColor: "rgba(53, 84, 209, 0.05)",
                    }}
                  >
                    <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                      <GroupIcon sx={{ color: "#3554D1", mr: 1 }} />
                      <Typography
                        variant="subtitle1"
                        sx={{ fontWeight: "bold" }}
                      >
                        Guest Count: {totalGuests}
                      </Typography>
                    </Box>

                    <Divider sx={{ my: 1 }} />

                    <Box
                      sx={{
                        display: "flex",
                        flexDirection: "column",
                        gap: 1.5,
                        mt: 2,
                      }}
                    >
                      {/* Adult count */}
                      {adultCount > 0 && (
                        <Box
                          sx={{ display: "flex", alignItems: "center", gap: 1 }}
                        >
                          <Chip
                            label="Adults"
                            sx={{
                              backgroundColor: "rgba(53, 84, 209, 0.1)",
                              color: "#3554D1",
                              fontWeight: "bold",
                              minWidth: "70px",
                            }}
                          />
                          <AvatarGroup
                            max={10}
                            sx={{
                              "& .MuiAvatar-root": {
                                width: 30,
                                height: 30,
                                fontSize: "0.8rem",
                              },
                            }}
                          >
                            {Array.from({ length: adultCount }).map((_, i) => (
                              <Avatar
                                key={`adult-avatar-${i}`}
                                sx={{
                                  bgcolor: "#3554D1",
                                  color: "white",
                                }}
                              >
                                <PersonIcon fontSize="small" />
                              </Avatar>
                            ))}
                          </AvatarGroup>
                          <Typography variant="body2" color="text.secondary">
                            {adultCount} {adultCount === 1 ? "Adult" : "Adults"}
                          </Typography>
                        </Box>
                      )}

                      {/* Child count */}
                      {childCount > 0 && (
                        <Box
                          sx={{ display: "flex", alignItems: "center", gap: 1 }}
                        >
                          <Chip
                            label="Children"
                            sx={{
                              backgroundColor: "rgba(255, 152, 0, 0.1)",
                              color: "#FF9800",
                              fontWeight: "bold",
                              minWidth: "70px",
                            }}
                          />
                          <AvatarGroup
                            max={10}
                            sx={{
                              "& .MuiAvatar-root": {
                                width: 30,
                                height: 30,
                                fontSize: "0.8rem",
                              },
                            }}
                          >
                            {Array.from({ length: childCount }).map((_, i) => (
                              <Avatar
                                key={`child-avatar-${i}`}
                                sx={{
                                  bgcolor: "#FF9800",
                                  color: "white",
                                }}
                              >
                                <ChildCareIcon fontSize="small" />
                              </Avatar>
                            ))}
                          </AvatarGroup>
                          <Typography variant="body2" color="text.secondary">
                            {childCount}{" "}
                            {childCount === 1 ? "Child" : "Children"}
                          </Typography>
                        </Box>
                      )}

                      {/* Senior count */}
                      {seniorCount > 0 && (
                        <Box
                          sx={{ display: "flex", alignItems: "center", gap: 1 }}
                        >
                          <Chip
                            label="Seniors"
                            sx={{
                              backgroundColor: "rgba(76, 175, 80, 0.1)",
                              color: "#4CAF50",
                              fontWeight: "bold",
                              minWidth: "70px",
                            }}
                          />
                          <AvatarGroup
                            max={10}
                            sx={{
                              "& .MuiAvatar-root": {
                                width: 30,
                                height: 30,
                                fontSize: "0.8rem",
                              },
                            }}
                          >
                            {Array.from({ length: seniorCount }).map((_, i) => (
                              <Avatar
                                key={`senior-avatar-${i}`}
                                sx={{
                                  bgcolor: "#4CAF50",
                                  color: "white",
                                }}
                              >
                                <ElderlyIcon fontSize="small" />
                              </Avatar>
                            ))}
                          </AvatarGroup>
                          <Typography variant="body2" color="text.secondary">
                            {seniorCount}{" "}
                            {seniorCount === 1 ? "Senior" : "Seniors"}
                          </Typography>
                        </Box>
                      )}
                    </Box>
                  </Paper>
                </CardContent>
              </Card>
            </Grid>

            <Grid item xs={12} md={6}>
              <Card
                elevation={3}
                sx={{
                  borderRadius: "16px",
                  overflow: "hidden",
                  transition: "transform 0.3s ease, box-shadow 0.3s ease",
                  "&:hover": {
                    transform: "translateY(-4px)",
                    boxShadow: "0 8px 24px rgba(0,0,0,0.12)",
                  },
                  background:
                    "linear-gradient(135deg, rgba(53, 84, 209, 0.05) 0%, rgba(53, 84, 209, 0.1) 100%)",
                  height: "100%", // Ensures the card takes full height
                }}
              >
                <CardContent sx={{ p: 3 }}>
                  <Typography
                    variant="h6"
                    sx={{
                      fontWeight: "bold",
                      mb: 2,
                      display: "flex",
                      alignItems: "center",
                    }}
                  >
                    <CurrencyExchangeIcon sx={{ mr: 1, color: "#3554D1" }} />
                    Price Summary
                  </Typography>

                  <Grid container spacing={3}>
                    {/* Price Mode */}
                    <Grid item xs={12} md={12}>
                      <Paper
                        elevation={1}
                        sx={{
                          p: 2,
                          borderRadius: "12px",
                        }}
                      >
                        <Typography
                          variant="subtitle2"
                          color="textSecondary"
                          sx={{ mb: 1 }}
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
                          {booking.mode === "travClicks" ||
                          booking.mode === "travclicks" ? (
                            <Chip
                              label="Travclicks"
                              color="primary"
                              sx={{ fontWeight: "bold" }}
                            />
                          ) : booking.mode === "dmc" ? (
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
                              {utils.capitalizeFirstLetter(booking.mode)}
                            </Typography>
                          )}
                        </Box>
                      </Paper>
                    </Grid>

                    {/* Total Price */}
                    <Grid item xs={12} md={12}>
                      <Paper
                        elevation={1}
                        sx={{
                          p: 2,
                          borderRadius: "12px",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            justifyContent: "space-between",
                            mb: 1.5,
                          }}
                        >
                          <Typography variant="subtitle2" color="textSecondary">
                            Total Price
                          </Typography>
                          <Chip
                            label={`Includes tax`}
                            size="small"
                            color="success"
                            variant="outlined"
                            sx={{ height: "24px", fontSize: "0.75rem" }}
                          />
                        </Box>

                        {PriceHide === "0" ? (
                          <Box
                            sx={{
                              display: "flex",
                              flexDirection: "column",
                              gap: 1.5,
                              p: 1.5,
                              background:
                                "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
                              borderRadius: "8px",
                              color: "white",
                              boxShadow: "0 3px 8px rgba(53, 84, 209, 0.2)",
                            }}
                          >
                            <Box
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
                                Tax Rates
                              </Typography>
                            </Box>

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

                              {/* Base Price */}
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
                                  Base Price (Without Tax)
                                </Typography>
                                <Typography
                                  sx={{
                                    fontSize: "0.85rem",
                                    color: "rgba(255, 255, 255, 0.9)",
                                  }}
                                >
                                  {convertedPrice}
                                </Typography>
                              </Box>

                              {/* Tax Amount */}
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
                                  Tax Amount ({currentTax}%)
                                </Typography>
                                <Typography
                                  sx={{
                                    fontSize: "0.85rem",
                                    color: "rgba(255, 255, 255, 0.9)",
                                  }}
                                >
                                  {currentTaxAmount}
                                </Typography>
                              </Box>

                              {/* Total With Tax */}
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
                                  Total (With Tax)
                                </Typography>
                                <Typography
                                  sx={{
                                    fontWeight: "bold",
                                    fontSize: "0.95rem",
                                    color: "white",
                                  }}
                                >
                                  {convertedGrandTotal}
                                </Typography>
                              </Box>
                            </Box>

                            {/* Other currencies with tax included */}
                            {(showUsdPortion || true) && (
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

                                {showUsdPortion && (
                                  <Box
                                    sx={{
                                      display: "flex",
                                      justifyContent: "space-between",
                                      alignItems: "center",
                                      py: 0.5,
                                    }}
                                  >
                                    <Box
                                      sx={{
                                        display: "flex",
                                        alignItems: "center",
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
                                          fontSize: "0.7rem",
                                          color: "rgba(255, 255, 255, 0.7)",
                                          ml: 0.5,
                                        }}
                                      >
                                        ({usdTax}%)
                                      </Typography>
                                    </Box>
                                    <Typography
                                      sx={{
                                        fontSize: "0.85rem",
                                        color: "rgba(255, 255, 255, 0.9)",
                                      }}
                                    >
                                      {usdGrandTotal}
                                    </Typography>
                                  </Box>
                                )}

                                {/* Always show SGD prices */}
                                <Box
                                  sx={{
                                    display: "flex",
                                    justifyContent: "space-between",
                                    alignItems: "center",
                                    py: 0.5,
                                  }}
                                >
                                  <Box
                                    sx={{
                                      display: "flex",
                                      alignItems: "center",
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
                                        fontSize: "0.7rem",
                                        color: "rgba(255, 255, 255, 0.7)",
                                        ml: 0.5,
                                      }}
                                    >
                                      ({sgdTax}%)
                                    </Typography>
                                  </Box>
                                  <Typography
                                    sx={{
                                      fontSize: "0.85rem",
                                      color: "rgba(255, 255, 255, 0.9)",
                                    }}
                                  >
                                    {sgdGrandTotal}
                                  </Typography>
                                </Box>
                              </Box>
                            )}
                          </Box>
                        ) : (
                          <Box
                            sx={{
                              p: 3,
                              textAlign: "center",
                              color: "gray",
                              fontSize: "1rem",
                              fontWeight: "bold",
                            }}
                          >
                            Price is Hidden.
                          </Box>
                        )}
                      </Paper>
                    </Grid>
                  </Grid>
                </CardContent>
              </Card>
            </Grid>
          </Grid>

          {/* Action Buttons */}
          <Grid
            item
            xs={12}
            sx={{ display: "flex", justifyContent: "center", mt: 2 }}
          >
            <Button
              variant="contained"
              color="primary"
              onClick={onClose}
              sx={{
                mr: 2,
                borderRadius: "8px",
                px: 3,
                background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
              }}
            >
              Close
            </Button>
            {/* <Button
              variant="contained"
              color="primary"
              sx={{ 
                borderRadius: '8px', 
                px: 3,
                background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
              }}
            >
              Print Details
            </Button> */}
          </Grid>
        </Grid>
      </DialogContent>
    </Dialog>
  );
};

// Styles
const styles = {
  dialogTitle: {
    backgroundColor: "#007bff",
    color: "#fff",
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    py: 1,
  },
  stickyImage: {
    position: "sticky",
    top: 16,
    height: "fit-content",
    zIndex: 1,
  },
  dmcContainer: {
    display: "flex",
    alignItems: "center",
    gap: "8px",
  },
  dmcLogo: {
    width: "24px",
    height: "24px",
    objectFit: "contain",
  },
  description: {
    "& strong": {
      color: "#007bff",
      fontWeight: 600,
    },
  },
};

// PropTypes
AttractionBookingModal.propTypes = {
  open: PropTypes.bool.isRequired,
  onClose: PropTypes.func.isRequired,
  booking: PropTypes.shape({
    AttractionName: PropTypes.string,
    bookingDate: PropTypes.string,
    adultCount: PropTypes.number,
    childCount: PropTypes.number,
    Selection: PropTypes.string,
    mode: PropTypes.string,
    totalPrice: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
    service_details: PropTypes.object,
  }),
};

InfoField.propTypes = {
  label: PropTypes.string.isRequired,
  value: PropTypes.node.isRequired,
};

SectionTitle.propTypes = {
  title: PropTypes.string.isRequired,
};

export default AttractionBookingModal;
