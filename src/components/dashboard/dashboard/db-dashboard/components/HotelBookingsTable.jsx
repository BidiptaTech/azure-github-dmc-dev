import React, { useState, useEffect, useMemo } from "react";
import Table from "@mui/material/Table";
import TableBody from "@mui/material/TableBody";
import TableCell from "@mui/material/TableCell";
import TableContainer from "@mui/material/TableContainer";
import TableHead from "@mui/material/TableHead";
import TableRow from "@mui/material/TableRow";
import Paper from "@mui/material/Paper";
import Button from "@mui/material/Button";
import { useSelector } from "react-redux";
import dayjs from "dayjs";
import VisibilityIcon from "@mui/icons-material/Visibility";
import CancelIcon from "@mui/icons-material/Cancel";
import HotelIcon from "@mui/icons-material/Hotel";
import HotelBookingModal from "./HotelBookingModal";
import { Typography, Box, Chip, Avatar, alpha } from "@mui/material";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../../../slice/dmc/dmcSlice"; // Import DMC slice selectors

// Function to capitalize first letter
const capitalizeFirstLetter = (string) => {
  if (!string) return "N/A";
  return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
};

// Map numeric status to text
const getStatusText = (status) => {
  switch (status) {
    case 1:
      return "Completed";
    case 2:
      return "Pending";
    case 3:
      return "Confirmed";
    case 4:
      return "Cancelled";
    default:
      return "Unknown";
  }
};

// Function to get status text color and background
const getStatusStyle = (statusText) => {
  switch (statusText) {
    case "Completed":
      return { 
        color: "#1E8E3E", 
        bgcolor: alpha("#1E8E3E", 0.1),
        border: `1px solid ${alpha("#1E8E3E", 0.3)}`
      };
    case "Pending":
      return { 
        color: "#F9AB00", 
        bgcolor: alpha("#F9AB00", 0.1),
        border: `1px solid ${alpha("#F9AB00", 0.3)}`
      };
    case "Confirmed":
      return { 
        color: "#1A73E8", 
        bgcolor: alpha("#1A73E8", 0.1),
        border: `1px solid ${alpha("#1A73E8", 0.3)}`
      };
    case "Cancelled":
      return { 
        color: "#D93025", 
        bgcolor: alpha("#D93025", 0.1),
        border: `1px solid ${alpha("#D93025", 0.3)}`
      };
    default:
      return { 
        color: "#5F6368", 
        bgcolor: alpha("#5F6368", 0.1),
        border: `1px solid ${alpha("#5F6368", 0.3)}`
      };
  }
};

const HotelBookingsTable = React.memo(({ onCountChange }) => {
  const { bookings, status, error } = useSelector((state) => state.viewDetails);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  

  // Memoize the hotel bookings count
  const hotelBookingsCount = useMemo(() => bookings?.hotel?.length || 0, [bookings?.hotel?.length]);

  // Only update count when it actually changes
  useEffect(() => {
    onCountChange(hotelBookingsCount);
  }, [hotelBookingsCount, onCountChange]);

  const handleView = React.useCallback((booking) => {
    setSelectedBooking(booking);
    setIsModalOpen(true);
  }, []);

  const handleCancel = React.useCallback((booking) => {
    console.log("Cancel booking:", booking);
  }, []);

  const handleCloseModal = React.useCallback(() => {
    setIsModalOpen(false);
    setSelectedBooking(null);
  }, []);

  // Function to format date
  const formatDate = (dateArray, index) => {
    if (!dateArray || !Array.isArray(dateArray) || !dateArray[index]) return "N/A";
    return dayjs(dateArray[index]).format("DD MMM YYYY");
  };

  // Function to extract meal types from beds
  const getMealTypes = (rooms) => {
    if (!rooms || !rooms.length || !rooms[0].beds || !rooms[0].beds.length) return [];
    return rooms[0].beds[0].mealTypes || [];
  };

  if (status === "loading") return (
    <Box sx={{ 
      p: 4, 
      display: 'flex', 
      justifyContent: 'center',
      alignItems: 'center',
      bgcolor: alpha('#1976d2', 0.04), 
      borderRadius: 2 
    }}>
      <Typography variant="body1" color="primary">Loading bookings...</Typography>
    </Box>
  );
  
  if (status === "failed") return (
    <Box sx={{ 
      p: 4, 
      display: 'flex', 
      justifyContent: 'center',
      alignItems: 'center',
      bgcolor: alpha('#d32f2f', 0.04), 
      borderRadius: 2 
    }}>
      <Typography variant="body1" color="error">Error: {error}</Typography>
    </Box>
  );

  return (
    <>
      <TableContainer 
        component={Paper} 
        elevation={2}
        sx={{ 
          borderRadius: 2,
          overflow: 'hidden',
          mb: 3,
        }}
      >
        <Table>
          <TableHead>
            <TableRow sx={{ 
              background: "linear-gradient(90deg, #1E88E5 0%, #42A5F5 100%)",
              '& .MuiTableCell-head': {
                fontWeight: 'bold',
                py: 1.8,
                whiteSpace: 'nowrap'
              }
            }}>
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <HotelIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Hotel Name</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff" }}>Check-In</TableCell>
              <TableCell sx={{ color: "#fff" }}>Check-Out</TableCell>
              <TableCell sx={{ color: "#fff" }}>Room Type</TableCell>
              <TableCell sx={{ color: "#fff" }}>Bed Details</TableCell>
              <TableCell sx={{ color: "#fff" }}>Meal Type</TableCell>
              <TableCell sx={{ color: "#fff" }}>Price Mode</TableCell>
              <TableCell sx={{ color: "#fff" }}>Total Price</TableCell>
              {/* <TableCell sx={{ color: "#fff" }}>Special Requests</TableCell> */}
              <TableCell sx={{ color: "#fff" }}>Status</TableCell>
              <TableCell sx={{ color: "#fff" }}>Actions</TableCell>
            </TableRow>
          </TableHead>

          <TableBody>
            {bookings?.hotel?.length > 0 ? (
              bookings.hotel.map((booking, index) => {
                // Default to "Confirmed" status if not provided
                const statusText = booking.status ? getStatusText(booking.status) : "NA";
                const statusStyle = getStatusStyle(statusText);
                const mealTypes = getMealTypes(booking.rooms);
                
                return (
                  <TableRow 
                    key={index}
                    sx={{
                      backgroundColor: index % 2 === 0 ? "#fafafa" : "#ffffff",
                      "&:hover": { backgroundColor: alpha("#1976d2", 0.04) },
                      transition: "background-color 0.3s ease"
                    }}
                  >
                    <TableCell>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <Avatar 
                          sx={{ 
                            width: 28, 
                            height: 28, 
                            bgcolor: alpha('#1976d2', 0.1),
                            color: '#1976d2',
                            fontSize: '14px',
                            fontWeight: 'bold'
                          }}
                        >
                          {booking.hotelDetails?.hotel_name?.charAt(0) || "H"}
                        </Avatar>
                        <Typography variant="body1" fontWeight="500">
                          {booking.hotelDetails?.hotel_name || "N/A"}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="medium"
                        label={formatDate(booking.bookingDate, 0)}
                        sx={{
                          bgcolor: alpha('#4CAF50', 0.1),
                          color: '#2E7D32',
                          fontWeight: 'medium',
                          height: '26px',
                          fontSize: '0.85rem',
                          '& .MuiChip-label': {
                            px: 0.8
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="medium"
                        label={formatDate(booking.bookingDate, 1)}
                        sx={{
                          bgcolor: alpha('#F44336', 0.1),
                          color: '#C62828',
                          fontWeight: 'medium',
                          height: '26px',
                          fontSize: '0.85rem',
                          '& .MuiChip-label': {
                            px: 0.8
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2" sx={{ fontWeight: 500 }}>
                        {booking.rooms?.[0]?.room_type || "N/A"}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      {booking.rooms?.[0]?.beds?.[0] ? (
                        <Typography variant="body2">
                          {`${capitalizeFirstLetter(
                            booking.rooms[0].beds[0].bed_type
                          )} (${booking.rooms[0].beds[0].head_count} persons)`}
                        </Typography>
                      ) : "N/A"}
                    </TableCell>
                    <TableCell>
                      {mealTypes.length > 0 ? (
                        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
                          {mealTypes.map((type, i) => (
                            <Chip
                              key={i}
                              label={capitalizeFirstLetter(type)}
                              size="small"
                              sx={{
                                bgcolor: alpha('#673AB7', 0.1),
                                color: '#5E35B1',
                                fontSize: '0.75rem',
                                height: '22px'
                              }}
                            />
                          ))}
                        </Box>
                      ) : "N/A"}
                    </TableCell>
                    <TableCell>
                      {booking.priceMode === "travClicks" || booking.priceMode === "travclicks" ||  booking.priceMode ==="travclick" ? (
                        <Chip 
                          label="Travcliks" 
                          size="small"
                          sx={{
                            bgcolor: alpha('#009688', 0.1),
                            color: '#00796B',
                            fontWeight: 'medium',
                            height: '24px',
                            fontSize: '0.75rem',
                            border: `1px solid ${alpha('#009688', 0.3)}`
                          }}
                        />
                      ) : booking.priceMode === "dmc" ? (
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.8 }}>
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
                                bgcolor: alpha('#FF9800', 0.2),
                                color: '#E65100',
                                fontSize: '12px'
                              }}
                            >
                              {dmcCompanyName?.charAt(0) || "D"}
                            </Avatar>
                          )}
                          <Typography variant="body2" fontWeight="medium" color="#E65100">
                            {dmcCompanyName}
                          </Typography>
                        </Box>
                      ) : (
                        capitalizeFirstLetter(booking.priceMode)
                      )}
                    </TableCell>
                    

                    <TableCell>
                      {PriceHide ==="0"?(
                         <Chip
                         size="medium"
                         label={booking.totalPrice ? `SGD ${booking.totalPrice}` : "N/A"}
                         sx={{
                           fontWeight: "bold",
                           bgcolor: alpha('#673AB7', 0.1),
                           color: '#5E35B1',
                           height: '26px',
                           fontSize: '0.85rem',
                         }}
                       />
                      ):(
                        <div className="text-15 lh-12">
                        Price available on request
                      </div>
                      )}
                     
                    </TableCell>
                    {/* <TableCell>
                      <Typography
                        variant="body2"
                        sx={{
                          maxWidth: '150px',
                          overflow: 'hidden',
                          textOverflow: 'ellipsis',
                          whiteSpace: 'nowrap'
                        }}
                      >
                        {booking.specialRequests || "None"}
                      </Typography>
                    </TableCell> */}
                    <TableCell>
                      <Chip
                        label={statusText}
                        size="small"
                        sx={{
                          color: statusStyle.color,
                          bgcolor: statusStyle.bgcolor,
                          border: statusStyle.border,
                          fontWeight: 'medium',
                          fontSize: '0.75rem'
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      <Box sx={{ display: "flex", gap: "8px" }}>
                        <Button
                          variant="contained"
                          size="small"
                          startIcon={<VisibilityIcon />}
                          onClick={() => handleView(booking)}
                          sx={{
                            backgroundColor: "#4CAF50",
                            "&:hover": {
                              backgroundColor: "#45a049",
                              boxShadow: `0 4px 8px ${alpha("#4CAF50", 0.3)}`,
                            },
                            borderRadius: 1.5,
                            textTransform: 'none',
                            boxShadow: `0 2px 4px ${alpha("#4CAF50", 0.2)}`,
                          }}
                        >
                          View
                        </Button>
                        <Button
                          variant="contained"
                          size="small"
                          startIcon={<CancelIcon />}
                          onClick={() => handleCancel(booking)}
                          sx={{
                            backgroundColor: "#f44336",
                            "&:hover": {
                              backgroundColor: "#da190b",
                              boxShadow: `0 4px 8px ${alpha("#f44336", 0.3)}`,
                            },
                            borderRadius: 1.5,
                            textTransform: 'none',
                            boxShadow: `0 2px 4px ${alpha("#f44336", 0.2)}`,
                          }}
                        >
                          Cancel
                        </Button>
                      </Box>
                    </TableCell>
                  </TableRow>
                );
              })
            ) : (
              <TableRow>
                <TableCell colSpan={11} align="center">
                  <Box sx={{ 
                    py: 3,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: 1
                  }}>
                    <Avatar 
                      sx={{ 
                        bgcolor: alpha('#1976d2', 0.1),
                        color: '#1976d2',
                        width: 40,
                        height: 40,
                        mb: 1
                      }}
                    >
                      <HotelIcon />
                    </Avatar>
                    <Typography variant="body1" color="textSecondary">
                      No hotel bookings available.
                    </Typography>
                  </Box>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      <HotelBookingModal
        open={isModalOpen}
        onClose={handleCloseModal}
        booking={selectedBooking}
      />
    </>
  );
});

HotelBookingsTable.displayName = 'HotelBookingsTable';

export default HotelBookingsTable;
