import React from "react";
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
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import CurrencyExchangeIcon from "@mui/icons-material/CurrencyExchange";
import PersonIcon from "@mui/icons-material/Person";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import DriveEtaIcon from "@mui/icons-material/DriveEta";
import RouteIcon from "@mui/icons-material/Route";
import TimelineIcon from "@mui/icons-material/Timeline";
import HourglassEmptyIcon from "@mui/icons-material/HourglassEmpty";
import TourIcon from "@mui/icons-material/Tour";
import GroupIcon from "@mui/icons-material/Group";
import InfoIcon from "@mui/icons-material/Info";
import DescriptionIcon from "@mui/icons-material/Description";
import dayjs from "dayjs";
import { useSelector } from "react-redux";

// Function to capitalize first letter
const capitalizeFirstLetter = (string) => {
  if (!string) return "N/A";
  const str = String(string);
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

const LocalTransferBookingModal = ({ open, onClose, booking }) => {
  console.log("booking", booking);
  // Add selectors for DMC and currency info
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  const priceMode =
    useSelector((state) => state.hotels.searchState.priceMode) || "dmc";
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  if (!booking) return null;
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

  // Calculate price
  // const totalPrice = parseFloat(booking?.price || booking?.totalPrice) || 0;
  // const conversionRate = priceMode === "dmc" ? exchangeRate : usdExchangeRate;
  // const convertedPrice =
  //   (totalPrice + (totalPrice * currentTax) / 100) * conversionRate;

  // Calculate guest count
  const adultCount = Number(booking.adults) || 0;
  const childCount = Number(booking.children) || 0;
  const totalGuests = adultCount + childCount;

  // Get tour type
  const getTourType = () => {
    if (booking.type === "travel_point") {
      return {
        name: "Point to Point Transfer",
        icon: <RouteIcon fontSize="large" />,
      };
    } else if (booking.type === "travel_hourly") {
      return {
        name: "Hourly Tour",
        icon: <HourglassEmptyIcon fontSize="large" />,
      };
    } else if (booking.type === "local_transport") {
      return {
        name: "Travel Zone",
        icon: <TourIcon fontSize="large" />,
      };
    } else {
      return {
        name: "Local Tour",
        icon: <TourIcon fontSize="large" />,
      };
    }
  };

  const tourType = getTourType();

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
          {tourType.icon}
          <Typography variant="h6" sx={{ fontWeight: "bold" }}>
            {tourType.name} Details
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
          {/* Vehicle Image and Basic Info Card */}
          <Grid item xs={12}>
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
                    {booking?.image ? (
                      <CardMedia
                        component="img"
                        image={booking.image}
                        alt="Vehicle"
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
                        <DirectionsCarIcon
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
                          No vehicle image available
                        </Typography>
                      </Box>
                    )}
                  </Box>
                </Grid>
                <Grid item xs={12} md={8}>
                  <CardContent sx={{ p: 3 }}>
                    <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                      <DriveEtaIcon
                        sx={{ color: "#3554D1", mr: 1, fontSize: 28 }}
                      />
                      <Typography variant="h5" sx={{ fontWeight: "bold" }}>
                        {booking.service_details?.vehicle_name || booking?.vehicles_name || tourType.name}
                      </Typography>
                    </Box>

                    <Grid container spacing={2}>
                      <Grid item xs={12} sm={6}>
                        <Box
                          sx={{ display: "flex", alignItems: "center", mb: 1 }}
                        >
                          <CalendarTodayIcon sx={{ color: "#3554D1", mr: 1 }} />
                          <Typography variant="body2" color="textSecondary">
                            Tour Date
                          </Typography>
                        </Box>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: "medium", ml: 4 }}
                        >
                          {booking.bookingDate || booking.pickupdate
                            ? dayjs(
                                booking.bookingDate || booking.pickupdate
                              ).format("dddd DD MMM YY")
                            : "N/A"}
                        </Typography>
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <Box
                          sx={{ display: "flex", alignItems: "center", mb: 1 }}
                        >
                          <AccessTimeIcon sx={{ color: "#3554D1", mr: 1 }} />
                          <Typography variant="body2" color="textSecondary">
                            Pickup Time
                          </Typography>
                        </Box>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: "medium", ml: 4 }}
                        >
                          {booking.entrytime || "N/A"}
                        </Typography>
                      </Grid>

                      {booking.selectedHours && (
                        <Grid item xs={12} sm={6}>
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              mb: 1,
                            }}
                          >
                            <HourglassEmptyIcon
                              sx={{ color: "#3554D1", mr: 1 }}
                            />
                            <Typography variant="body2" color="textSecondary">
                              Tour Duration
                            </Typography>
                          </Box>
                          <Typography
                            variant="body1"
                            sx={{ fontWeight: "medium", ml: 4 }}
                          >
                            {booking.selectedHours} hours
                          </Typography>
                        </Grid>
                      )}
                    </Grid>
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          </Grid>

          {/* Location Details Card */}
          <Grid item xs={12}>
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
              }}
            >
              <CardContent sx={{ p: 3 }}>
                <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                  <LocationOnIcon
                    sx={{ color: "#3554D1", mr: 1, fontSize: 28 }}
                  />
                  <Typography variant="h6" sx={{ fontWeight: "bold" }}>
                    Location Details
                  </Typography>
                </Box>

                <Grid container spacing={3}>
                  {/* Pickup Location */}
                  <Grid item xs={12} md={6}>
                    <Paper
                      elevation={1}
                      sx={{
                        p: 2,
                        borderRadius: "12px",
                        backgroundColor: "rgba(53, 84, 209, 0.05)",
                        height: "100%",
                      }}
                    >
                      <Box
                        sx={{ display: "flex", alignItems: "center", mb: 1 }}
                      >
                        <RouteIcon sx={{ color: "#3554D1", mr: 1 }} />
                        <Typography
                          variant="subtitle1"
                          sx={{ fontWeight: "bold", ml: 1 }}
                        >
                          Pickup Location
                        </Typography>
                      </Box>

                      <Divider sx={{ my: 1 }} />

                      <Box sx={{ mt: 2 }}>
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "flex-start",
                            mt: 2,
                          }}
                        >
                          <LocationOnIcon
                            sx={{ color: "#3554D1", mr: 1, mt: 0.5 }}
                          />
                          <Typography
                            variant="body1"
                            sx={{ fontWeight: "medium" }}
                          >
                            {booking.entrypickup || "N/A"}
                          </Typography>
                        </Box>

                        <Box
                          sx={{ display: "flex", alignItems: "center", mt: 2 }}
                        >
                          <AccessTimeIcon sx={{ color: "#3554D1", mr: 1 }} />
                          <Typography variant="body1">
                            Time: {booking.entrytime || "N/A"}
                          </Typography>
                        </Box>
                      </Box>
                    </Paper>
                  </Grid>

                  {/* Drop-off Location */}
                  <Grid item xs={12} md={6}>
                    <Paper
                      elevation={1}
                      sx={{
                        p: 2,
                        borderRadius: "12px",
                        height: "100%",
                      }}
                    >
                      <Box
                        sx={{ display: "flex", alignItems: "center", mb: 1 }}
                      >
                        <RouteIcon sx={{ color: "#3554D1", mr: 1 }} />
                        <Typography
                          variant="subtitle1"
                          sx={{ fontWeight: "bold" }}
                        >
                          Drop-off Location
                        </Typography>
                      </Box>

                      <Divider sx={{ my: 1 }} />

                      <Box
                        sx={{
                          mt: 2,
                          display: "flex",
                          alignItems: "flex-start",
                        }}
                      >
                        <LocationOnIcon
                          sx={{ color: "#3554D1", mr: 1, mt: 0.5 }}
                        />
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: "medium" }}
                        >
                          {booking.entrydropoff || "N/A"}
                        </Typography>
                      </Box>
                    </Paper>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>

          <Grid item xs={12}>
            <Grid container spacing={2}>
              {/* Guest Information Card */}
              <Grid
                item
                xs={6}
                sx={{ display: "flex", flexDirection: "column" }}
              >
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
                    flex: 1, // Ensures the card takes full height
                  }}
                >
                  <CardContent sx={{ p: 3 }}>
                    <Box sx={{ display: "flex", alignItems: "center", mb: 0 }}>
                      <GroupIcon
                        sx={{ color: "#3554D1", mr: 1, fontSize: 28 }}
                      />
                      <Typography variant="h6" sx={{ fontWeight: "bold" }}>
                        Guest Information
                      </Typography>
                    </Box>

                    <Paper
                      elevation={1}
                      sx={{
                        p: 2,
                        borderRadius: "12px",
                        mt: 2,
                        height: "100%",
                      }}
                    >
                      <Box
                        sx={{ display: "flex", alignItems: "center", mb: 1 }}
                      >
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
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              gap: 1,
                            }}
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
                              {Array.from({ length: adultCount }).map(
                                (_, i) => (
                                  <Avatar
                                    key={`adult-avatar-${i}`}
                                    sx={{
                                      bgcolor: "#3554D1",
                                      color: "white",
                                    }}
                                  >
                                    <PersonIcon fontSize="small" />
                                  </Avatar>
                                )
                              )}
                            </AvatarGroup>
                            <Typography variant="body2" color="text.secondary">
                              {adultCount}{" "}
                              {adultCount === 1 ? "Adult" : "Adults"}
                            </Typography>
                          </Box>
                        )}

                        {/* Child count */}
                        {childCount > 0 && (
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              gap: 1,
                            }}
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
                              {Array.from({ length: childCount }).map(
                                (_, i) => (
                                  <Avatar
                                    key={`child-avatar-${i}`}
                                    sx={{
                                      bgcolor: "#FF9800",
                                      color: "white",
                                    }}
                                  >
                                    <ChildCareIcon fontSize="small" />
                                  </Avatar>
                                )
                              )}
                            </AvatarGroup>
                            <Typography variant="body2" color="text.secondary">
                              {childCount}{" "}
                              {childCount === 1 ? "Child" : "Children"}
                            </Typography>
                          </Box>
                        )}
                      </Box>
                    </Paper>
                  </CardContent>
                </Card>
              </Grid>

              {/* Price Information */}
              <Grid
                item
                xs={6}
                sx={{ display: "flex", flexDirection: "column" }}
              >
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
                    flex: 1, // Ensures the card takes full height
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
                            {booking.Mode === "travClicks" ||
                            booking.Mode === "travclicks" ? (
                              <Chip
                                label="Travclicks"
                                color="primary"
                                sx={{ fontWeight: "bold" }}
                              />
                            ) : booking.Mode === "dmc" ? (
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
                                {capitalizeFirstLetter(booking.Mode)}
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
                          {PriceHide === "0" ? (
                            <>
                              <Box
                                sx={{
                                  display: "flex",
                                  justifyContent: "space-between",
                                  mb: 1.5,
                                }}
                              >
                                <Typography
                                  variant="subtitle2"
                                  color="textSecondary"
                                >
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
                                  {/* <Box
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
                                  </Box> */}

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
                                      Total (Without Tax)
                                    </Typography>
                                    <Typography
                                      sx={{
                                        fontWeight: "bold",
                                        fontSize: "0.95rem",
                                        color: "white",
                                      }}
                                    >
                                      {convertedPrice}
                                    </Typography>
                                  </Box>
                                </Box>

                                {/* Other currencies with tax included */}
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
                                  {currencyCode !== "USD" && (
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
                                        {/* <Typography
                                          sx={{
                                            fontSize: "0.7rem",
                                            color: "rgba(255, 255, 255, 0.7)",
                                            ml: 0.5,
                                          }}
                                        >
                                          ({usdTax}%)
                                        </Typography> */}
                                      </Box>
                                      <Typography
                                        sx={{
                                          fontSize: "0.85rem",
                                          color: "rgba(255, 255, 255, 0.9)",
                                        }}
                                      >
                                        {usdPrice}
                                      </Typography>
                                    </Box>
                                  )}
                                  {currencyCode !== "SGD" && (
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
                                        {/* <Typography
                                          sx={{
                                            fontSize: "0.7rem",
                                            color: "rgba(255, 255, 255, 0.7)",
                                            ml: 0.5,
                                          }}
                                        >
                                          ({sgdTax}%)
                                        </Typography> */}
                                      </Box>
                                      <Typography
                                        sx={{
                                          fontSize: "0.85rem",
                                          color: "rgba(255, 255, 255, 0.9)",
                                        }}
                                      >
                                        {sgdPrice}
                                      </Typography>
                                    </Box>
                                  )}
                                </Box>
                              </Box>
                            </>
                          ) : (
                            <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                              Price Hidden
                            </div>
                          )}
                        </Paper>
                      </Grid>
                    </Grid>
                  </CardContent>
                </Card>
              </Grid>
            </Grid>
          </Grid>

          {/* Vehicle Information */}
          {/* <Grid item xs={12}>
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
                <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                  <DirectionsCarIcon
                    sx={{ color: "#3554D1", mr: 1, fontSize: 28 }}
                  />
                  <Typography variant="h6" sx={{ fontWeight: "bold" }}>
                    Vehicle Information
                  </Typography>
                </Box>

                <Grid container spacing={2}>
                  {booking.service_details?.vehicle_name && (
                    <Grid item xs={12} sm={6} md={3}>
                      <Paper
                        elevation={1}
                        sx={{
                          p: 2,
                          borderRadius: "12px",
                          backgroundColor: "rgba(53, 84, 209, 0.05)",
                          height: "100%",
                        }}
                      >
                        <Typography
                          variant="subtitle2"
                          color="textSecondary"
                          sx={{ mb: 1 }}
                        >
                          Vehicle Name
                        </Typography>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: "medium" }}
                        >
                          {booking.service_details.vehicle_name}
                        </Typography>
                      </Paper>
                    </Grid>
                  )}

                  {booking.service_details?.vehicle_model && (
                    <Grid item xs={12} sm={6} md={3}>
                      <Paper
                        elevation={1}
                        sx={{
                          p: 2,
                          borderRadius: "12px",
                          height: "100%",
                        }}
                      >
                        <Typography
                          variant="subtitle2"
                          color="textSecondary"
                          sx={{ mb: 1 }}
                        >
                          Vehicle Model
                        </Typography>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: "medium" }}
                        >
                          {booking.service_details.vehicle_model}
                        </Typography>
                      </Paper>
                    </Grid>
                  )}

                  {booking.service_details?.vehicle_type && (
                    <Grid item xs={12} sm={6} md={3}>
                      <Paper
                        elevation={1}
                        sx={{
                          p: 2,
                          borderRadius: "12px",
                          backgroundColor: "rgba(53, 84, 209, 0.05)",
                          height: "100%",
                        }}
                      >
                        <Typography
                          variant="subtitle2"
                          color="textSecondary"
                          sx={{ mb: 1 }}
                        >
                          Vehicle Type
                        </Typography>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: "medium" }}
                        >
                          {booking.service_details.vehicle_type}
                        </Typography>
                      </Paper>
                    </Grid>
                  )}

                  {booking.service_details?.seating_capacity && (
                    <Grid item xs={12} sm={6} md={3}>
                      <Paper
                        elevation={1}
                        sx={{
                          p: 2,
                          borderRadius: "12px",
                          height: "100%",
                        }}
                      >
                        <Typography
                          variant="subtitle2"
                          color="textSecondary"
                          sx={{ mb: 1 }}
                        >
                          Seating Capacity
                        </Typography>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: "medium" }}
                        >
                          {booking.service_details.seating_capacity} people
                        </Typography>
                      </Paper>
                    </Grid>
                  )}
                </Grid>
              </CardContent>
            </Card>
          </Grid> */}

          {/* Vehicle Description */}
          {/* {booking.service_details?.description && (
            <Grid item xs={12}>
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
                  <Box sx={{ display: "flex", alignItems: "center", mb: 2 }}>
                    <DescriptionIcon
                      sx={{ color: "#3554D1", mr: 1, fontSize: 28 }}
                    />
                    <Typography variant="h6" sx={{ fontWeight: "bold" }}>
                      Vehicle Description
                    </Typography>
                  </Box>

                  <Paper
                    elevation={1}
                    sx={{
                      p: 2,
                      borderRadius: "12px",
                    }}
                  >
                    <Typography
                      variant="body1"
                      dangerouslySetInnerHTML={{
                        __html: booking.service_details.description,
                      }}
                      sx={{
                        "& strong": {
                          color: "#3554D1",
                          fontWeight: 600,
                        },
                      }}
                    />
                  </Paper>
                </CardContent>
              </Card>
            </Grid>
          )} */}

          {/* Special Requests */}
          {/* {booking.specialRequests && (
            <Grid item xs={12}>
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
                  <Box
                    sx={{
                      display: "flex",
                      alignItems: "center",
                      mb: 2,
                      gap: 1,
                    }}
                  >
                    <InfoIcon sx={{ color: "#3554D1", fontSize: 28 }} />
                    <Typography variant="h6" sx={{ fontWeight: "bold" }}>
                      Special Requests
                    </Typography>
                  </Box>

                  <Divider sx={{ mb: 2 }} />

                  <Box sx={{ mt: 2, px: 1 }}>
                    <Typography variant="body1" sx={{ lineHeight: 1.6 }}>
                      {booking.specialRequests}
                    </Typography>
                  </Box>
                </CardContent>
              </Card>
            </Grid>
          )} */}

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
                background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
                borderRadius: "8px",
                px: 3,
              }}
            >
              Close
            </Button>
          </Grid>
        </Grid>
      </DialogContent>
    </Dialog>
  );
};

export default LocalTransferBookingModal;
