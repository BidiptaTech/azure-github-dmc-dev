import React from "react";
import {
  Box,
  Card,
  CardContent,
  Chip,
  Divider,
  Grid,
  Paper,
  Stack,
  Typography,
  alpha,
  Avatar,
  CardMedia,
  Badge,
} from "@mui/material";
import LocalPhoneIcon from "@mui/icons-material/LocalPhone";
import EmailIcon from "@mui/icons-material/Email";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import PeopleIcon from "@mui/icons-material/People";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import StarIcon from "@mui/icons-material/Star";
import SchoolIcon from "@mui/icons-material/School";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import FlightLandIcon from "@mui/icons-material/FlightLand";
import { useSelector } from "react-redux";
import dayjs from "dayjs";

const EntryPortPointBooking = ({
  bookingDetails,
  currencyCode,
  usdCurrencyCode,
  exchangeRate,
  usdExchangeRate,
  Tax,
}) => {
  const type = useSelector((state) => state.tourguide.type);
  if (!bookingDetails) return null;

  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // Get DMC info from Redux
  const DmcName = useSelector((state) => state.auth.DmcName);
  const DmcLogo = useSelector((state) => state.auth.DmcLogo);

  // Format price with commas
  const formatPrice = (price) => {
    // Check if price is undefined, null, NaN, or not a number
    if (
      price === undefined ||
      price === null ||
      isNaN(price) ||
      typeof price !== "number"
    ) {
      return "0";
    }

    return Math.ceil(price).toLocaleString("en-US");
  };

  // Helper function to safely parse price values
  const safeParseFloat = (value) => {
    if (
      value === undefined ||
      value === null ||
      value === "" ||
      isNaN(parseFloat(value))
    ) {
      return 0;
    }
    return parseFloat(value);
  };
  // Format date function
  const formatDate = (date) => {
    if (!date) return "Not Selected";
    return dayjs(date).format("ddd, D MMM'YY");
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
          {type === "entryport" ? (
            <FlightLandIcon sx={{ fontSize: 24, mr: 1 }} />
          ) : (
            <DirectionsCarIcon sx={{ fontSize: 24, mr: 1 }} />
          )}
          <Typography
            variant="h6"
            sx={{ fontWeight: "bold", fontSize: "1.1rem" }}
          >
            {type === "entryport"
              ? "Entry Port Transfer"
              : "Point to Point Transfer"}
          </Typography>
        </Box>
        <Chip
          label={bookingDetails.type === "Sharable" ? "Sharable" : "Private"}
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

      {/* Vehicle Image and Details Section - Modern Design */}
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
              image={bookingDetails.image || "/placeholder-car.jpg"}
              alt={bookingDetails.vehicles_name || "Vehicle"}
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
            {bookingDetails.vehicles_name && (
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
                  label={bookingDetails.vehicles_name}
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
                <DirectionsCarIcon
                  sx={{ color: "#3554D1", mr: 1, fontSize: 20 }}
                />
                <Typography
                  variant="h6"
                  sx={{ fontWeight: "bold", fontSize: "1.1rem" }}
                >
                  {bookingDetails.vehicles_name || "Vehicle Name"}
                </Typography>
              </Box>

              <Stack direction="row" spacing={1} sx={{ mb: 2 }}>
                <Chip
                  size="small"
                  label={
                    bookingDetails.type === "entryport"
                      ? "Entry Port Transfer"
                      : "Point to Point"
                  }
                  sx={{
                    height: "22px",
                    fontSize: "0.75rem",
                    fontWeight: 500,
                    bgcolor: alpha("#3554D1", 0.1),
                    color: "#3554D1",
                  }}
                />

                {bookingDetails.sharable && (
                  <Chip
                    size="small"
                    label="Sharable"
                    sx={{
                      height: "22px",
                      fontSize: "0.75rem",
                      fontWeight: 500,
                      bgcolor: alpha("#4CAF50", 0.1),
                      color: "#4CAF50",
                    }}
                  />
                )}
              </Stack>

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
                          {formatDate(bookingDetails.bookingDate)}
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
                          Transfer Time
                        </Typography>
                        <Typography
                          variant="body2"
                          sx={{ fontWeight: "medium", fontSize: "0.95rem" }}
                        >
                          {bookingDetails.entrytime || "Not specified"}
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
          <DirectionsCarIcon sx={{ mr: 1, color: "#3554D1", fontSize: 20 }} />
          Transfer Details
        </Typography>

        <Card elevation={2} sx={{ borderRadius: "10px", mb: 2.5 }}>
          <Box
            sx={{
              py: 1.5,
              px: 2,
              background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
              color: "white",
            }}
          >
            <Typography
              variant="subtitle1"
              sx={{ fontWeight: 600, fontSize: "1rem" }}
            >
              {type === "entryport"
                ? "Entry Port Transfer Details"
                : "Point to Point Transfer Details"}
            </Typography>
          </Box>

          <CardContent sx={{ p: 2 }}>
            <Grid container spacing={2}>
              {/* Trip Details - Left Side */}
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
                    <LocationOnIcon
                      sx={{ color: "#3554D1", mr: 1, fontSize: 20 }}
                    />
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: "bold", fontSize: "1.05rem" }}
                    >
                      Route Details
                    </Typography>
                  </Box>

                  <Divider sx={{ my: 0.75 }} />

                  {/* Pickup Information */}
                  <Box
                    sx={{
                      p: 1.25,
                      display: "flex",
                      flexDirection: "column",
                      borderRadius: "6px",
                      backgroundColor: "rgba(53, 84, 209, 0.05)",
                      mb: 1.5,
                    }}
                  >
                    <Typography
                      variant="body2"
                      color="textSecondary"
                      sx={{ fontSize: "0.85rem", mb: 0.5 }}
                    >
                      Pickup Location:
                    </Typography>
                    <Box
                      sx={{
                        display: "flex",
                        alignItems: "flex-start",
                        gap: 0.75,
                      }}
                    >
                      <LocationOnIcon
                        sx={{ color: "#3554D1", fontSize: "1.1rem", mt: 0.25 }}
                      />
                      <Typography
                        variant="body1"
                        sx={{ fontSize: "0.95rem", fontWeight: 500 }}
                      >
                        {bookingDetails.entrypickup || "Not specified"}
                      </Typography>
                    </Box>
                  </Box>

                  {/* Dropoff Information */}
                  <Box
                    sx={{
                      p: 1.25,
                      display: "flex",
                      flexDirection: "column",
                      borderRadius: "6px",
                      backgroundColor: "rgba(230, 81, 0, 0.05)",
                      border: "1px solid rgba(230, 81, 0, 0.1)",
                      mb: 1.5,
                    }}
                  >
                    <Typography
                      variant="body2"
                      color="textSecondary"
                      sx={{ fontSize: "0.85rem", mb: 0.5 }}
                    >
                      Drop-off Location:
                    </Typography>
                    <Box
                      sx={{
                        display: "flex",
                        alignItems: "flex-start",
                        gap: 0.75,
                      }}
                    >
                      <LocationOnIcon
                        sx={{ color: "#E65100", fontSize: "1.1rem", mt: 0.25 }}
                      />
                      <Typography
                        variant="body1"
                        sx={{
                          fontSize: "0.95rem",
                          fontWeight: 500,
                          color: "#E65100",
                        }}
                      >
                        {bookingDetails.entrydropoff || "Not specified"}
                      </Typography>
                    </Box>
                  </Box>

                  {/* Time Information */}
                  <Box
                    sx={{
                      p: 1.5,
                      borderRadius: "6px",
                      backgroundColor: "#F8FAFF",
                      border: "1px solid rgba(53, 84, 209, 0.1)",
                      mt: 1.25,
                    }}
                  >
                    <Grid container spacing={1.5}>
                      <Grid item xs={6}>
                        <Typography
                          variant="body2"
                          color="text.secondary"
                          sx={{ fontSize: "0.95rem" }}
                        >
                          Date:
                        </Typography>
                      </Grid>
                      <Grid item xs={6} sx={{ textAlign: "right" }}>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: 500, fontSize: "1rem" }}
                        >
                          {formatDate(bookingDetails.bookingDate)}
                        </Typography>
                      </Grid>

                      <Grid item xs={6}>
                        <Typography
                          variant="body2"
                          color="text.secondary"
                          sx={{ fontSize: "0.95rem" }}
                        >
                          Time:
                        </Typography>
                      </Grid>
                      <Grid item xs={6} sx={{ textAlign: "right" }}>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: 500, fontSize: "1rem" }}
                        >
                          {bookingDetails.entrytime || "Not specified"}
                        </Typography>
                      </Grid>

                      <Grid item xs={6}>
                        <Typography
                          variant="body2"
                          color="text.secondary"
                          sx={{ fontSize: "0.95rem" }}
                        >
                          Distance:
                        </Typography>
                      </Grid>
                      <Grid item xs={6} sx={{ textAlign: "right" }}>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: 500, fontSize: "1rem" }}
                        >
                          {bookingDetails.distance
                            ? `${bookingDetails.distance} km`
                            : "Not specified"}
                        </Typography>
                      </Grid>
                    </Grid>
                  </Box>
                </Paper>
              </Grid>

              {/* Guest Info - Right Side */}
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
                    <PeopleIcon
                      sx={{ color: "#3554D1", mr: 1, fontSize: 20 }}
                    />
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: "bold", fontSize: "1.05rem" }}
                    >
                      Passenger Details
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
                      badgeContent={bookingDetails.adults || 0}
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
                        <PeopleIcon
                          sx={{
                            fontSize: 18,
                            color: "white",
                          }}
                        />
                      </Avatar>
                    </Badge>
                  </Box>

                  {/* Children Section - Only show if there are any */}
                  {bookingDetails.children > 0 && (
                    <Box
                      sx={{
                        p: 1.25,
                        borderRadius: "6px",
                        border: "1px solid rgba(255, 152, 0, 0.3)",
                        bgcolor: "rgba(255, 152, 0, 0.05)",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "space-between",
                        mb: 1.5,
                      }}
                    >
                      <Typography variant="body1" sx={{ fontSize: "1rem" }}>
                        Children
                      </Typography>
                      <Chip
                        size="medium"
                        label={bookingDetails.children}
                        color="warning"
                        icon={<ChildCareIcon sx={{ fontSize: 18 }} />}
                        sx={{ height: "24px", fontSize: "0.9rem" }}
                      />
                    </Box>
                  )}

                  {/* Customer Info */}
                  <Box
                    sx={{
                      p: 1.5,
                      borderRadius: "6px",
                      backgroundColor: "#F8FAFF",
                      border: "1px solid rgba(53, 84, 209, 0.1)",
                      mt: "auto",
                      flexGrow: 1,
                    }}
                  >
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 600, fontSize: "1rem", mb: 1.5 }}
                    >
                      Customer Information
                    </Typography>

                    <Grid container spacing={1.5}>
                      <Grid item xs={12}>
                        <Typography
                          variant="body2"
                          color="text.secondary"
                          sx={{ fontSize: "0.85rem", mb: 0.25 }}
                        >
                          Name:
                        </Typography>
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: 500, fontSize: "0.95rem", mb: 1 }}
                        >
                          {bookingDetails.userInfo?.fullName || "Not specified"}
                        </Typography>
                      </Grid>

                      <Grid item xs={12} sm={6}>
                        <Typography
                          variant="body2"
                          color="text.secondary"
                          sx={{ fontSize: "0.85rem", mb: 0.25 }}
                        >
                          Email:
                        </Typography>
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <EmailIcon
                            sx={{ color: "#3554D1", fontSize: "0.9rem" }}
                          />
                          <Typography
                            variant="body2"
                            sx={{ fontWeight: 500, fontSize: "0.9rem" }}
                          >
                            {bookingDetails.userInfo?.email || "Not specified"}
                          </Typography>
                        </Box>
                      </Grid>

                      <Grid item xs={12} sm={6}>
                        <Typography
                          variant="body2"
                          color="text.secondary"
                          sx={{ fontSize: "0.85rem", mb: 0.25 }}
                        >
                          Phone:
                        </Typography>
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <LocalPhoneIcon
                            sx={{ color: "#3554D1", fontSize: "0.9rem" }}
                          />
                          <Typography
                            variant="body2"
                            sx={{ fontWeight: 500, fontSize: "0.9rem" }}
                          >
                            {bookingDetails.userInfo?.countryCode || ""}{" "}
                            {bookingDetails.userInfo?.phone || "Not specified"}
                          </Typography>
                        </Box>
                      </Grid>
                    </Grid>
                  </Box>
                </Paper>
              </Grid>
            </Grid>
          </CardContent>
        </Card>
      </Box>

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
            Price Summary
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
                  Booking Mode
                </Typography>

                {bookingDetails.Mode === "dmc" ? (
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
                  <Chip
                    label="TravelClicks"
                    color="primary"
                    size="small"
                    sx={{
                      fontWeight: "medium",
                      height: "24px",
                      fontSize: "0.8rem",
                      bgcolor: alpha("#009688", 0.1),
                      color: "#00796B",
                      border: `1px solid ${alpha("#009688", 0.3)}`,
                    }}
                  />
                )}

                {bookingDetails.sharable && (
                  <Chip
                    label="Sharable"
                    color="success"
                    size="small"
                    sx={{
                      ml: 1,
                      fontWeight: "medium",
                      height: "24px",
                      fontSize: "0.8rem",
                    }}
                  />
                )}
              </Paper>
            </Grid>
            {PriceHide === "0" && (
              <>
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

                        <Box
                          sx={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                            mb: 1,
                            pb: 1,
                            borderBottom: "1px solid rgba(255, 255, 255, 0.2)",
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
                              {formatPrice(
                                safeParseFloat(bookingDetails.totalPrice) *
                                  safeParseFloat(exchangeRate || 1)
                              )}
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
                              {formatPrice(
                                safeParseFloat(bookingDetails.totalPrice) *
                                  (safeParseFloat(currentTax) / 100) *
                                  safeParseFloat(exchangeRate || 1)
                              )}
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
                              borderTop: "1px dotted rgba(255, 255, 255, 0.3)",
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
                              {formatPrice(
                                (safeParseFloat(bookingDetails.totalPrice) +
                                  (safeParseFloat(bookingDetails.totalPrice) *
                                    safeParseFloat(currentTax)) /
                                    100) *
                                  safeParseFloat(exchangeRate || 1)
                              )}
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
                                sx={{ display: "flex", alignItems: "center" }}
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
                                {formatPrice(
                                  (safeParseFloat(bookingDetails.totalPrice) +
                                    (safeParseFloat(bookingDetails.totalPrice) *
                                      safeParseFloat(usdTax)) /
                                      100) *
                                    safeParseFloat(usdExchangeRate || 1)
                                )}
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
                                sx={{ display: "flex", alignItems: "center" }}
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
                                {formatPrice(
                                  safeParseFloat(bookingDetails.totalPrice) +
                                    (safeParseFloat(bookingDetails.totalPrice) *
                                      safeParseFloat(sgdTax)) /
                                      100 || 0
                                )}
                              </Typography>
                            </Box>
                          )}
                        </Box>
                      </Box>
                    </Box>
                  </Paper>
                </Grid>
              </>
            )}
          </Grid>
        </CardContent>
      </Card>
    </Box>
  );
};

export default EntryPortPointBooking;
