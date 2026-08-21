import { useEffect, useState } from "react";
import { styled } from "@mui/material/styles";
import {
  Box,
  Typography,
  Card,
  CardContent,
  Grid,
  Stack,
  Divider,
} from "@mui/material";
import dayjs from "dayjs";
import { useSelector } from "react-redux";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import PersonIcon from "@mui/icons-material/Person";
import CalendarMonthIcon from "@mui/icons-material/CalendarMonth";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import LocalOfferIcon from "@mui/icons-material/LocalOffer";
import PricingSummary from "./PricingSummary";
// import { Box, Typography, Grid } from '@mui/material';
//import {  RoomTypeHeader, BedTypeChip } from './StyledComponents';
// import RestaurantIcon from '@mui/icons-material/Restaurant';
import BedIcon from "@mui/icons-material/KingBed";
// import { Restaurant as RestaurantIcon } from '@mui/icons-material';

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

const BookingDetails = () => {
  const restaurantBookings = useSelector(
    (state) => state.restaurants?.restaurantBookings || []
  );
  const restaurantsDetails = useSelector(
    (state) => state.restaurants.selectedRestaurant
  );
  // console.log('restaurantsDetails',restaurantsDetails.tax_percentage);
  
  // Format date to "Mon, DD MMM'YY" format
  const formatDate = (dateString) => {
    if (!dateString) return "Not Selected";
    const date = dayjs(dateString);
    return date.format("ddd, DD MMM'YY"); // e.g., Mon, 12 May'25
  };

  // Format price with commas for thousands
  const formatPrice = (price) => {
    if (typeof price !== "number") return "0.00";
    return price.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  return (
    <div className="px-30 py-30 border-light rounded-4">
      <div className="text-20 fw-500 mb-30">Your booking details</div>
      <div className="row x-gap-15 y-gap-20">
        <div className="col-auto">
          <img
            src={
              restaurantsDetails?.master_image ||
              "https://via.placeholder.com/140"
            }
            alt="image"
            className="size-140 rounded-4 object-cover"
          />
        </div>
        <div className="col">
          {/* <div className="d-flex x-gap-5 pb-10">
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
          </div> */}
          <div className="lh-17 fw-500">
            {restaurantsDetails?.name || "Restaurant Name"}
          </div>
          <div className="text-14 lh-15 mt-5">
            {restaurantsDetails?.city}, {restaurantsDetails?.country}
          </div>
        </div>
      </div>

      <div className="border-top-light mt-30 mb-20" />

      {restaurantBookings.length > 0 &&
        restaurantBookings.map((booking, index) => {
          const data = booking?.data?.[0] || {};

          return (
            <div key={index} className="row y-gap-20 justify-between">
              <Box mt={2} width="100%">
                <RoomCard key={index}>
                  <RoomTypeHeader>
                    <Typography
                      variant="h6"
                      sx={{ fontWeight: 600, fontSize: "1.1rem" }}
                    >
                      Guest, Meal and Items Details
                    </Typography>
                  </RoomTypeHeader>

                  <CardContent sx={{ p: 3 }}>
                    <Box
                      sx={{
                        mb: 3,
                        pb: 2,
                        borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                        "&:last-child": { borderBottom: "none", mb: 0, pb: 0 },
                      }}
                    >
                      <Grid container spacing={2}>
                        {/* Booking Date and Time Section */}
                        <Grid item xs={12}>
                          <Box
                            sx={{
                              mb: 3,
                              pb: 2,
                              borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                            }}
                          >
                            <Typography
                              variant="subtitle1"
                              sx={{ fontWeight: 600, color: "#3554D1", mb: 2 }}
                            >
                              Booking Details:
                            </Typography>
                            <Box
                              sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}
                            >
                              <Box
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  backgroundColor: "#F8FAFF",
                                  p: 1.5,
                                  borderRadius: "8px",
                                  border: "1px solid rgba(53, 84, 209, 0.1)",
                                  boxShadow: "0 2px 8px rgba(53, 84, 209, 0.08)",
                                  transition: "all 0.2s ease",
                                  "&:hover": {
                                    boxShadow: "0 4px 12px rgba(53, 84, 209, 0.15)",
                                    backgroundColor: "#F0F5FF",
                                  }
                                }}
                              >
                                <CalendarMonthIcon sx={{ color: "#3554D1", mr: 1 }} />
                                <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                  {data?.bookingDate
                                    ? formatDate(data.bookingDate)
                                    : "Date not selected"}
                                </Typography>
                              </Box>

                              {data?.visitTime && (
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    backgroundColor: "#F8FAFF",
                                    p: 1.5,
                                    borderRadius: "8px",
                                    border: "1px solid rgba(53, 84, 209, 0.1)",
                                    boxShadow: "0 2px 8px rgba(53, 84, 209, 0.08)",
                                    transition: "all 0.2s ease",
                                    "&:hover": {
                                      boxShadow: "0 4px 12px rgba(53, 84, 209, 0.15)",
                                      backgroundColor: "#F0F5FF",
                                    }
                                  }}
                                >
                                  <AccessTimeIcon sx={{ color: "#3554D1", mr: 1 }} />
                                  <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                    {data.visitTime}
                                  </Typography>
                                </Box>
                              )}
                            </Box>
                          </Box>
                        </Grid>
                        
                        {/* Guest Details Section */}
                        <Grid item xs={12}>
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
                              sx={{ fontWeight: 600, color: "#3554D1", mb: 2 }}
                            >
                              Guest Details:
                            </Typography>
                            <Box
                              sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}
                            >
                              <Box
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  backgroundColor: "#F8FAFF",
                                  p: 1.5,
                                  borderRadius: "8px",
                                  border: "1px solid rgba(53, 84, 209, 0.1)",
                                  boxShadow: "0 2px 8px rgba(53, 84, 209, 0.08)",
                                  transition: "all 0.2s ease",
                                  "&:hover": {
                                    boxShadow: "0 4px 12px rgba(53, 84, 209, 0.15)",
                                    backgroundColor: "#F0F5FF",
                                  }
                                }}
                              >
                                <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                                <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                  Adults: {data?.adultCount || 0}
                                </Typography>
                              </Box>

                              {data?.childCount > 0 && (
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    backgroundColor: "#F8FAFF",
                                    p: 1.5,
                                    borderRadius: "8px",
                                    border: "1px solid rgba(53, 84, 209, 0.1)",
                                    boxShadow: "0 2px 8px rgba(53, 84, 209, 0.08)",
                                    transition: "all 0.2s ease",
                                    "&:hover": {
                                      boxShadow: "0 4px 12px rgba(53, 84, 209, 0.15)",
                                      backgroundColor: "#F0F5FF",
                                    }
                                  }}
                                >
                                  <PersonIcon
                                    sx={{ color: "#3554D1", mr: 1 }}
                                  />
                                  <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                    Children: {data.childCount}
                                  </Typography>
                                </Box>
                              )}

                              {/* Add total pax for transport calculation reference */}
                              {(data?.adultCount > 0 || data?.childCount > 0) && data?.transport && (
                                <Box
                                  sx={{
                                    display: "flex",
                                    alignItems: "center",
                                    backgroundColor: "#F8FAFF",
                                    p: 1.5,
                                    borderRadius: "8px",
                                    border: "1px solid rgba(53, 84, 209, 0.1)",
                                    boxShadow: "0 2px 8px rgba(53, 84, 209, 0.08)",
                                    transition: "all 0.2s ease",
                                    "&:hover": {
                                      boxShadow: "0 4px 12px rgba(53, 84, 209, 0.15)",
                                      backgroundColor: "#F0F5FF",
                                    }
                                  }}
                                >
                                  <PersonIcon
                                    sx={{ color: "#3554D1", mr: 1 }}
                                  />
                                  <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                    Total Pax: {(data?.adultCount || 0) + (data?.childCount || 0)}
                                  </Typography>
                                </Box>
                              )}
                            </Box>
                          </Box>
                        </Grid>

                        {/* Meal Details Section */}
                        <Grid item xs={12}>
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
                              sx={{ fontWeight: 600, color: "#3554D1", mb: 2 }}
                            >
                              Meal Details:
                            </Typography>
                            <Box
                              sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}
                            >
                              <Box
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  backgroundColor: "#F8FAFF",
                                  p: 1.5,
                                  borderRadius: "8px",
                                  border: "1px solid rgba(53, 84, 209, 0.1)",
                                  boxShadow: "0 2px 8px rgba(53, 84, 209, 0.08)",
                                  transition: "all 0.2s ease",
                                  "&:hover": {
                                    boxShadow: "0 4px 12px rgba(53, 84, 209, 0.15)",
                                    backgroundColor: "#F0F5FF",
                                  }
                                }}
                              >
                                <RestaurantIcon
                                  sx={{ color: "#3554D1", mr: 1 }}
                                />
                                <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                  {data?.mealType
                                    ? data.mealType.charAt(0).toUpperCase() +
                                      data.mealType.slice(1)
                                    : "N/A"}{" "}
                                  - {data?.mealSpecificType || "N/A"}
                                </Typography>
                              </Box>
                            </Box>
                          </Box>
                        </Grid>

                        {/* Selected Items Section */}
                        {data?.MealDescription && (
                          <Grid item xs={12}>
                            <Box
                              sx={{
                                mb: 3,
                                pb: 2,
                                borderBottom:
                                  "1px dashed rgba(53, 84, 209, 0.2)",
                                "&:last-child": {
                                  borderBottom: "none",
                                  mb: 0,
                                  pb: 0,
                                },
                              }}
                            >
                              {data?.mealSpecificType !== "Buffet" && (
                                <>
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
                                          alignItems: "center",
                                          backgroundColor: "#F8FAFF",
                                          p: 1.5,
                                          borderRadius: "8px",
                                          border:
                                            "1px solid rgba(53, 84, 209, 0.1)",
                                          mb: 1,
                                          boxShadow: "0 2px 8px rgba(53, 84, 209, 0.08)",
                                          transition: "all 0.2s ease",
                                          "&:hover": {
                                            boxShadow: "0 4px 12px rgba(53, 84, 209, 0.15)",
                                            backgroundColor: "#F0F5FF",
                                          }
                                        }}
                                      >
                                        <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                          {item.item_name}
                                        </Typography>
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                          sx={{ fontWeight: 500 }}
                                        >
                                          Quantity: {item.quantity} × SGD{" "}
                                          {formatPrice(item.price)}
                                        </Typography>
                                      </Box>
                                    ))}
                                </>
                              )}
                            </Box>
                          </Grid>
                        )}
                      </Grid>
                    </Box>
                  </CardContent>
                </RoomCard>
              </Box>
            </div>
          );
        })}

      <div className="border-top-light mt-30 mb-20" />

      <PricingSummary
        totalPrice={parseFloat(
          restaurantBookings[0]?.data?.[0]?.totalPrice || 0
        )}
      />
    </div>
  );
};

export default BookingDetails;
