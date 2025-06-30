import { useState } from "react";
import {
  Box,
  Typography,
  Card,
  CardContent,
  Grid,
  Divider,
  Paper,
  Stack,
  useTheme,
  Rating,
  Chip,
} from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import { useSelector } from "react-redux";
import styled from "@emotion/styled";
import PersonIcon from "@mui/icons-material/Person";
import CalendarMonthIcon from "@mui/icons-material/CalendarMonth";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import LocalOfferIcon from "@mui/icons-material/LocalOffer";
import ElderlyIcon from "@mui/icons-material/Elderly";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import dayjs from "dayjs";
import PricingSummary from "./PricingSummary";

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

const BookingDetails = () => {
  const theme = useTheme();

  const attractionBookings = useSelector(
    (state) => state.attractions?.attractionBookings || []
  );
    console.log("attractionBookings", attractionBookings);

  const attractionDetails = useSelector(
    (state) => state.attractions.attractionDetails
  );
  //  console.log("attractionDetails", attractionDetails);

  const descriptionText = attractionDetails?.description || "";
  const plainText = descriptionText.replace(/<\/?[^>]+(>|$)/g, "");
  const shortDescription = plainText.slice(0, 200);

  // Helper function to get selection text
  const getSelectionText = (selection) => {
    switch (selection) {
      case 'withoutTraveller':
        return 'Only Attraction';
      case 'withPrivate':
        return 'Attraction With Transfer (Private)';
      case 'withShare':
        return 'Attraction With Transfer (Share)';
      default:
        return 'Not selected';
    }
  };

  // Format date to "Mon, 12 May'25" format
  const formatDate = (dateString) => {
    if (!dateString) return "Not Selected";
    const date = dayjs(dateString);
    return date.format("ddd, DD MMM'YY"); // e.g., Mon, 12 May'25
  };

  return (
    <div className="px-30 py-30 border-light rounded-4">
      <div className="text-20 fw-500 mb-30">Your booking details</div>
      <div className="row x-gap-15 y-gap-20">
        <div className="col-auto">
          <img
            src={attractionDetails?.master_image || "https://via.placeholder.com/140"}
            alt="image"
            className="size-140 rounded-4 object-cover"
          />
        </div>
        <div className="col">
          <div className="lh-17 fw-500">
            {attractionDetails?.name || "Attraction Name"}
          </div>
          <div className="text-14 lh-15 mt-5">
            {attractionDetails?.location}, {attractionDetails?.country}
          </div>
        </div>
      </div>

      <div className="border-top-light mt-30 mb-20" />

      {attractionBookings.length > 0 ? (
        attractionBookings.map((booking, index) => {
          const data = booking?.data?.[0] || {};
          return (
            <div key={index} className="row y-gap-20 justify-between">
              <Box mt={2} width="100%">
                <RoomCard>
                  <RoomTypeHeader>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <Typography variant="h6" sx={{ fontWeight: 600, fontSize: "1.1rem" }}>
                        Guest Details & Booking Information
                      </Typography>
                      {/* {data?.Selection && (
                        <Chip
                          label={getSelectionText(data.Selection)}
                          size="small"
                          sx={{
                            bgcolor: 'rgba(255, 255, 255, 0.2)',
                            color: 'white',
                            fontWeight: 'medium',
                            height: '24px',
                            fontSize: '0.8rem',
                            '& .MuiChip-label': {
                              px: 1
                            }
                          }}
                        />
                      )} */}
                    </Box>
                  </RoomTypeHeader>

                  <CardContent sx={{ p: 3 }}>
                    <Box sx={{
                      mb: 3,
                      pb: 2,
                      borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                      "&:last-child": { borderBottom: "none", mb: 0, pb: 0 },
                    }}>
                      <Grid container spacing={2}>
                        {/* Booking Date and Time Section */}
                        <Grid item xs={12}>
                          <Box sx={{
                            mb: 3,
                            pb: 2,
                            borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                          }}>
                            <Typography variant="subtitle1" sx={{ fontWeight: 600, color: "#3554D1", mb: 2 }}>
                              Booking Details:
                            </Typography>
                            <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                              <Box sx={{
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
                              }}>
                                <CalendarMonthIcon sx={{ color: "#3554D1", mr: 1 }} />
                                <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                  {data?.bookingDate 
                                    ? formatDate(data.bookingDate)
                                    : "Date not selected"}
                                </Typography>
                              </Box>

                              {data?.visitTime && (
                                <Box sx={{
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
                                }}>
                                  <AccessTimeIcon sx={{ color: "#3554D1", mr: 1 }} />
                                  <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                    {data.visitTime}
                                  </Typography>
                                </Box>
                              )}
                              
                              {data?.ticketName && (
                                <Box sx={{
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
                                }}>
                                  <LocalOfferIcon sx={{ color: "#3554D1", mr: 1 }} />
                                  <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                    {data.ticketName}
                                  </Typography>
                                </Box>
                              )}
                            </Box>
                          </Box>
                        </Grid>
                        
                        {/* Guest Details Section */}
                        <Grid item xs={12}>
                          <Box sx={{
                            mb: 3,
                            pb: 2,
                            borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                          }}>
                            <Typography variant="subtitle1" sx={{ fontWeight: 600, color: "#3554D1", mb: 2 }}>
                              Guest Details:
                            </Typography>
                            <Box sx={{ display: "flex", gap: 2, flexWrap: "wrap" }}>
                              <Box sx={{
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
                              }}>
                                <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                                <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                  Adults: {data?.adultCount || 0}
                                </Typography>
                              </Box>

                              {data?.childCount > 0 && (
                                <Box sx={{
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
                                }}>
                                  <ChildCareIcon sx={{ color: "#3554D1", mr: 1 }} />
                                  <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                    Children: {data.childCount}
                                  </Typography>
                                </Box>
                              )}

                              {data?.seniorCount > 0 && (
                                <Box sx={{
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
                                }}>
                                  <ElderlyIcon sx={{ color: "#3554D1", mr: 1 }} />
                                  <Typography variant="body1" sx={{ fontWeight: 500 }}>
                                    Seniors: {data.seniorCount}
                                  </Typography>
                                </Box>
                              )}
                            </Box>
                          </Box>
                        </Grid>
                      </Grid>
                    </Box>
                  </CardContent>
                </RoomCard>
              </Box>
            </div>
          );
        })
      ) : (
        <Typography variant="body2" color="text.secondary">
          No attraction bookings available.
        </Typography>
      )}

      {/* Price Summary Section */}
      <div className="border-top-light mt-30 mb-20" />
      <PricingSummary
        totalPrice={parseFloat(attractionBookings[0]?.data?.[0]?.totalPrice || 0)}
      />
    </div>
  );
};

export default BookingDetails;
