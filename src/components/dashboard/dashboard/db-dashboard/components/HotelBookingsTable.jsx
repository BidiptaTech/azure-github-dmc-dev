import React, { useState, useEffect, useMemo } from "react";
import Table from "@mui/material/Table";
import TableBody from "@mui/material/TableBody";
import TableCell from "@mui/material/TableCell";
import TableContainer from "@mui/material/TableContainer";
import TableHead from "@mui/material/TableHead";
import TableRow from "@mui/material/TableRow";
import Paper from "@mui/material/Paper";
import Button from "@mui/material/Button";
import { useSelector, useDispatch } from "react-redux";
import dayjs from "dayjs";
import VisibilityIcon from "@mui/icons-material/Visibility";
import CancelIcon from "@mui/icons-material/Cancel";
import HotelIcon from "@mui/icons-material/Hotel";
import HotelBookingModal from "./HotelBookingModal";
import { Typography, Box, Chip, Avatar, alpha, Snackbar, Alert, Modal, TextField,Skeleton } from "@mui/material";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../../../slice/dmc/dmcSlice"; // Import DMC slice selectors
import { singleBooking } from "@/slice/common/commonSlice";
import { fetchViewDetails } from "@/slice/common/ViewDetails";

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
  const dispatch = useDispatch();
  const { bookings, status, error } = useSelector((state) => state.viewDetails);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [showSuccessToast, setShowSuccessToast] = useState(false);
  const [showCancelConfirmModal, setShowCancelConfirmModal] = useState(false);
  const [cancelReason, setCancelReason] = useState("");
  const [bookingToCancel, setBookingToCancel] = useState(null);
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
    // Show confirmation modal instead of directly cancelling
    setBookingToCancel(booking);
    setCancelReason("");
    setShowCancelConfirmModal(true);
  }, []);

  const handleConfirmCancel = React.useCallback(async () => {
    if (!cancelReason.trim()) {
      // Don't proceed if reason is empty
      return;
    }

    const booking = bookingToCancel;
    // For hotel booking, use the appropriate booking ID and tour ID
    const bookingId = booking.entry_booking_id || booking.exit_booking_id || booking.booking_id;
    // Get tour_id from the root bookings object since it's not in individual booking objects
    const tourId = bookings?.tour?.tour_id;
    
    if (bookingId && tourId) {
      try {
        const result = await dispatch(singleBooking({bookingId: bookingId, tourId: tourId, cancelReason: cancelReason}));
        console.log("Cancel hotel booking:", { bookingId, tourId, booking, reason: cancelReason });
        
        // Check if cancellation was successful
                 if (result.meta.requestStatus === 'fulfilled') {
           console.log("Hotel booking cancelled successfully");
           // Show success toaster
           setShowSuccessToast(true);
           // Refresh data to show updated state
           dispatch(fetchViewDetails({ tour_id: tourId }));
           // Close the confirmation modal
           setShowCancelConfirmModal(false);
           setCancelReason("");
           setBookingToCancel(null);
         } else if (result.meta.requestStatus === 'rejected') {
          console.error("Failed to cancel hotel booking:", result.error);
        }
      } catch (error) {
        console.error("Error cancelling hotel booking:", error);
      }
    } else {
      console.error("Missing data for cancellation:", { bookingId, tourId, booking });
    }
  }, [dispatch, bookings?.tour?.tour_id, cancelReason]);

  const handleCancelModalClose = React.useCallback(() => {
    setShowCancelConfirmModal(false);
    setCancelReason("");
    setBookingToCancel(null);
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
    <TableContainer
      component={Paper}
      elevation={1}
      sx={{
        borderRadius: 1,
        overflow: "hidden",
        mb: 3,
        maxHeight: '70vh',
        overflowX: 'auto',
        overflowY: 'auto',
        '&::-webkit-scrollbar': {
          width: '8px',
          height: '8px',
        },
        '&::-webkit-scrollbar-track': {
          background: '#f1f1f1',
          borderRadius: '4px',
        },
        '&::-webkit-scrollbar-thumb': {
          background: '#c1c1c1',
          borderRadius: '4px',
          '&:hover': {
            background: '#a8a8a8',
          },
        },
      }}
    >
      <Table sx={{ minWidth: 1200 }}>
        <TableHead>
          <TableRow
            sx={{
              background: "linear-gradient(90deg, #1E88E5 0%, #42A5F5 100%)",
              "& .MuiTableCell-head": {
                fontWeight: "bold",
                py: 1.8,
                whiteSpace: "nowrap",
              },
            }}
          >
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
            <TableCell sx={{ color: "#fff" }}>Status</TableCell>
            <TableCell sx={{ color: "#fff" }}>Actions</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {/* Generate 5 skeleton rows */}
          {Array.from({ length: 5 }).map((_, index) => (
            <TableRow key={index}>
              <TableCell>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Skeleton variant="circular" width={28} height={28} />
                  <Skeleton variant="rectangular" width={120} height={20} sx={{ borderRadius: 1 }} />
                </Box>
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={100} height={26} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={100} height={26} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={100} height={20} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={120} height={20} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={80} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={100} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={100} height={26} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={80} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Box sx={{ display: "flex", gap: "8px" }}>
                  <Skeleton variant="rectangular" width={60} height={26} sx={{ borderRadius: 1.5 }} />
                  <Skeleton variant="rectangular" width={60} height={26} sx={{ borderRadius: 1.5 }} />
                </Box>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
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
          maxHeight: '70vh',
          overflowX: 'auto',
          overflowY: 'auto',
          '&::-webkit-scrollbar': {
            width: '8px',
            height: '8px',
          },
          '&::-webkit-scrollbar-track': {
            background: '#f1f1f1',
            borderRadius: '4px',
          },
          '&::-webkit-scrollbar-thumb': {
            background: '#c1c1c1',
            borderRadius: '4px',
            '&:hover': {
              background: '#a8a8a8',
            },
          },
        }}
      >
        <Table sx={{ minWidth: 1200 }}>
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

      {/* Cancel Confirmation Modal */}
      <Modal
        open={showCancelConfirmModal}
        onClose={handleCancelModalClose}
        aria-labelledby="cancel-confirmation-modal"
        aria-describedby="cancel-confirmation-description"
        sx={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
        }}
      >
        <Box
          sx={{
            position: 'relative',
            width: 400,
            bgcolor: 'background.paper',
            borderRadius: 2,
            boxShadow: 24,
            p: 4,
            outline: 'none',
          }}
        >
          {/* Header */}
          <Box sx={{ mb: 3, textAlign: 'center' }}>
            <Typography variant="h6" component="h2" sx={{ fontWeight: 600, color: '#d32f2f' }}>
              Cancel Booking
            </Typography>
            <Typography variant="body1" sx={{ mt: 1, color: 'text.secondary' }}>
              Are you sure you want to cancel this booking?
            </Typography>
          </Box>

          {/* Reason Input */}
          <Box sx={{ mb: 3 }}>
            <Typography variant="body2" component="label" sx={{ fontWeight: 500, mb: 1, display: 'block' }}>
              Reason for Cancellation *
            </Typography>
            <TextField
              fullWidth
              multiline
              rows={3}
              variant="outlined"
              placeholder="Please provide a reason for cancellation..."
              value={cancelReason}
              onChange={(e) => setCancelReason(e.target.value)}
              error={!cancelReason.trim()}
              helperText={!cancelReason.trim() ? "Reason is required" : ""}
              sx={{
                '& .MuiOutlinedInput-root': {
                  '&:hover fieldset': {
                    borderColor: '#d32f2f',
                  },
                  '&.Mui-focused fieldset': {
                    borderColor: '#d32f2f',
                  },
                },
              }}
            />
          </Box>

          {/* Action Buttons */}
          <Box sx={{ display: 'flex', gap: 2, justifyContent: 'flex-end' }}>
            <Button
              variant="outlined"
              onClick={handleCancelModalClose}
              sx={{
                borderColor: '#757575',
                color: '#757575',
                '&:hover': {
                  borderColor: '#424242',
                  backgroundColor: 'rgba(117, 117, 117, 0.05)',
                },
              }}
            >
              No
            </Button>
            <Button
              variant="contained"
              onClick={handleConfirmCancel}
              disabled={!cancelReason.trim()}
              sx={{
                backgroundColor: '#d32f2f',
                '&:hover': {
                  backgroundColor: '#c62828',
                },
                '&:disabled': {
                  backgroundColor: '#e0e0e0',
                  color: '#9e9e9e',
                },
              }}
            >
              Yes, Cancel
            </Button>
          </Box>
        </Box>
      </Modal>

      {/* Success Toaster */}
      <Snackbar
        open={showSuccessToast}
        autoHideDuration={3000}
        onClose={() => setShowSuccessToast(false)}
        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
      >
        <Alert
          onClose={() => setShowSuccessToast(false)}
          severity="success"
          sx={{ width: '100%' }}
        >
          Successfully Cancelled
        </Alert>
      </Snackbar>
    </>
  );
});

HotelBookingsTable.displayName = 'HotelBookingsTable';

export default HotelBookingsTable;
