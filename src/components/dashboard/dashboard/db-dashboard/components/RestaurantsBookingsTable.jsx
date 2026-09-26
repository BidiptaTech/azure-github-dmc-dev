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
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../../../slice/dmc/dmcSlice"; // Import DMC slice selectors
import dayjs from "dayjs";
import VisibilityIcon from "@mui/icons-material/Visibility";
import CancelIcon from "@mui/icons-material/Cancel";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import RestaurantBookingModal from "./RestaurantBookingModal";
import { Typography, Box, Chip, Avatar, alpha, Stack, Tooltip, Snackbar, Alert, Modal, TextField, Skeleton } from "@mui/material";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import PersonIcon from "@mui/icons-material/Person";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import MenuBookIcon from "@mui/icons-material/MenuBook";
import BrunchDiningIcon from "@mui/icons-material/BrunchDining";
import PriceCheckIcon from "@mui/icons-material/PriceCheck";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import { singleBooking } from "@/slice/common/commonSlice";
import { fetchViewDetails } from "@/slice/common/ViewDetails";

// Function to capitalize first letter
const capitalizeFirstLetter = (string) => {
  if (!string) return "N/A";
  // Convert to string in case it's a number or other type
  const str = String(string);
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
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

// Get meal type icon and style
const getMealTypeStyle = (mealType) => {
  const lowerCaseMealType = mealType?.toLowerCase() || '';
  
  if (lowerCaseMealType.includes('breakfast')) {
    return {
      icon: <BrunchDiningIcon fontSize="small" />,
      color: "#1976D2",
      bgcolor: alpha("#1976D2", 0.1)
    };
  } else if (lowerCaseMealType.includes('lunch')) {
    return {
      icon: <RestaurantIcon fontSize="small" />,
      color: "#00796B",
      bgcolor: alpha("#00796B", 0.1)
    };
  } else if (lowerCaseMealType.includes('dinner')) {
    return {
      icon: <MenuBookIcon fontSize="small" />,
      color: "#7B1FA2",
      bgcolor: alpha("#7B1FA2", 0.1)
    };
  } else {
    return {
      icon: <RestaurantIcon fontSize="small" />,
      color: "#FF5722",
      bgcolor: alpha("#FF5722", 0.1)
    };
  }
};

const RestaurantsBookingsTable = React.memo(({ onCountChange }) => {
  const dispatch = useDispatch();
  const { bookings, status, error } = useSelector((state) => state.viewDetails);
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [showSuccessToast, setShowSuccessToast] = useState(false);
  const [showCancelConfirmModal, setShowCancelConfirmModal] = useState(false);
  const [cancelReason, setCancelReason] = useState("");
  const [bookingToCancel, setBookingToCancel] = useState(null);
  const tourStatus = useMemo(() => bookings?.tour?.status, [bookings?.tour?.status]);
  
  // Get tax percentage from auth slice instead of restaurants
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  
  // Memoize the restaurant bookings count
  const restaurantBookingsCount = useMemo(() => bookings?.restaurant?.length || 0, [bookings?.restaurant?.length]);

  // Only update count when it actually changes
  useEffect(() => {
    onCountChange(restaurantBookingsCount);
  }, [restaurantBookingsCount, onCountChange]);

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
              background: "linear-gradient(90deg, #D32F2F 0%, #F44336 100%)",
              "& .MuiTableCell-head": {
                fontWeight: "bold",
                py: 1.8,
                whiteSpace: "nowrap",
              },
            }}
          >
            <TableCell sx={{ color: "#fff", width: '120px' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                <CalendarTodayIcon fontSize="small" />
                <Typography variant="body1" fontWeight="bold" color="white">Booking Date</Typography>
              </Box>
            </TableCell>
            <TableCell sx={{ color: "#fff", width: '150px' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                <RestaurantIcon fontSize="small" />
                <Typography variant="body1" fontWeight="bold" color="white">Restaurant</Typography>
              </Box>
            </TableCell>
            <TableCell sx={{ color: "#fff", width: '100px' }}>Visit Time</TableCell>
            <TableCell sx={{ color: "#fff", width: '100px' }}>Meal Type</TableCell>
            <TableCell sx={{ color: "#fff", width: '80px' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                <PersonIcon fontSize="small" />
                <Typography variant="body1" fontWeight="bold" color="white">Adults</Typography>
              </Box>
            </TableCell>
            <TableCell sx={{ color: "#fff", width: '80px' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                <ChildCareIcon fontSize="small" />
                <Typography variant="body1" fontWeight="bold" color="white">Children</Typography>
              </Box>
            </TableCell>
            <TableCell sx={{ color: "#fff", width: '100px' }}>Mode</TableCell>
            <TableCell sx={{ color: "#fff", width: '100px' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                <PriceCheckIcon fontSize="small" />
                <Typography variant="body1" fontWeight="bold" color="white">Price</Typography>
              </Box>
            </TableCell>
            <TableCell sx={{ color: "#fff", width: '100px' }}>Status</TableCell>
            <TableCell sx={{ color: "#fff", width: '140px' }}>Actions</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {/* Generate 5 skeleton rows */}
          {Array.from({ length: 5 }).map((_, index) => (
            <TableRow key={index}>
              <TableCell>
                <Skeleton variant="rectangular" width={120} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Skeleton variant="circular" width={24} height={24} />
                  <Skeleton variant="rectangular" width={130} height={20} sx={{ borderRadius: 1 }} />
                </Box>
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={80} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={80} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell align="center">
                <Skeleton variant="rectangular" width={60} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell align="center">
                <Skeleton variant="rectangular" width={60} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={80} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={90} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Skeleton variant="rectangular" width={80} height={24} sx={{ borderRadius: 1 }} />
              </TableCell>
              <TableCell>
                <Box sx={{ display: "flex", gap: "5px" }}>
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

  const handleView = (booking) => {
    if (booking) {  // Only set selected booking if it exists
      setSelectedBooking(booking);
      setIsModalOpen(true);
    }
  };

  const handleCancel = (booking) => {
    // Show confirmation modal instead of directly cancelling
    setBookingToCancel(booking);
    setCancelReason("");
    setShowCancelConfirmModal(true);
  };

  const handleConfirmCancel = async () => {
    if (!cancelReason.trim()) {
      // Don't proceed if reason is empty
      return;
    }

    const booking = bookingToCancel;
    // For restaurant booking, use the appropriate booking ID and tour ID
    const bookingId = booking.entry_booking_id || booking.exit_booking_id || booking.booking_id;
    // Get tour_id from the root bookings object since it's not in individual booking objects
    const tourId = bookings?.tour?.tour_id;
    
    if (bookingId && tourId) {
      try {
        const result = await dispatch(singleBooking({bookingId: bookingId, tourId: tourId, cancelReason: cancelReason}));
        console.log("Cancel restaurant booking:", { bookingId, tourId, booking, reason: cancelReason });
        
        // Check if cancellation was successful
                 if (result.meta.requestStatus === 'fulfilled') {
           console.log("Restaurant booking cancelled successfully");
           // Show success toaster
           setShowSuccessToast(true);
           // Refresh data to show updated state
           dispatch(fetchViewDetails({ tour_id: tourId }));
           // Close the confirmation modal
           setShowCancelConfirmModal(false);
           setCancelReason("");
           setBookingToCancel(null);
         } else if (result.meta.requestStatus === 'rejected') {
          console.error("Failed to cancel restaurant booking:", result.error);
        }
      } catch (error) {
        console.error("Error cancelling restaurant booking:", error);
      }
    } else {
      console.error("Missing data for cancellation:", { bookingId, tourId, booking });
    }
  };

  const handleCancelModalClose = () => {
    setShowCancelConfirmModal(false);
    setCancelReason("");
    setBookingToCancel(null);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    // Add a small delay before clearing the selected booking
    setTimeout(() => {
      setSelectedBooking(null);
    }, 300); // Delay to allow modal animation to complete
  };

  return (
    <>
      <TableContainer 
        component={Paper} 
        elevation={2}
        sx={{ 
          borderRadius: 2,
          overflow: 'hidden',
          mb: 3,
          width: '100%',
          overflowX: 'auto'
        }}
      >
        <Table size="small" sx={{ minWidth: 1200 }}>
          <TableHead>
            <TableRow sx={{ 
              background: "linear-gradient(90deg, #D32F2F 0%, #F44336 100%)",
              '& .MuiTableCell-head': {
                fontWeight: 'bold',
                py: 1.8,
                whiteSpace: 'nowrap'
              }
            }}>
              <TableCell sx={{ color: "#fff", width: '120px' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <CalendarTodayIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Booking Date</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '150px' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <RestaurantIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Restaurant</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '100px' }}>Visit Time</TableCell>
              <TableCell sx={{ color: "#fff", width: '100px' }}>Meal Type</TableCell>
              <TableCell sx={{ color: "#fff", width: '80px' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <PersonIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Adults</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '80px' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <ChildCareIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Children</Typography>
                </Box>
              </TableCell>
              {/* <TableCell sx={{ color: "#fff", width: '120px' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <DirectionsCarIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Transport</Typography>
                </Box>
              </TableCell> */}
              <TableCell sx={{ color: "#fff", width: '100px' }}>Mode</TableCell>
              <TableCell sx={{ color: "#fff", width: '100px' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <PriceCheckIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Price</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '100px' }}>Status</TableCell>
              <TableCell sx={{ color: "#fff", width: '140px' }}>Actions</TableCell>
            </TableRow>
          </TableHead>

          <TableBody>
            {bookings?.restaurant?.length > 0 ? (
              bookings.restaurant.map((booking, index) => {
                const statusText = getStatusText(booking.status);
                const statusStyle = getStatusStyle(statusText);
                const mealTypeStyle = getMealTypeStyle(booking.mealType);
                
                return (
                  <TableRow 
                    key={index}
                    sx={{
                      backgroundColor: index % 2 === 0 ? "#fafafa" : "#ffffff",
                      "&:hover": { backgroundColor: alpha("#F44336", 0.04) },
                      transition: "background-color 0.3s ease"
                    }}
                  >
                    <TableCell>
                      <Chip
                        size="small"
                        label={booking.bookingDate
                          ? dayjs(booking.bookingDate).format("dddd DD MMM YY")
                          : "N/A"}
                        sx={{
                          bgcolor: alpha('#4CAF50', 0.1),
                          color: '#2E7D32',
                          fontWeight: 'medium',
                          height: '24px',
                          fontSize: '0.75rem',
                          '& .MuiChip-label': {
                            px: 0.8
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <Avatar 
                          sx={{ 
                            width: 24, 
                            height: 24, 
                            bgcolor: alpha('#F44336', 0.1),
                            color: '#D32F2F',
                            fontSize: '14px',
                            fontWeight: 'bold'
                          }}
                        >
                          {booking.restaurantName?.charAt(0) || "R"}
                        </Avatar>
                        <Tooltip title={booking.restaurantName || "N/A"} placement="top">
                          <Typography variant="body2" fontWeight="500" noWrap sx={{ maxWidth: 130 }}>
                            {booking.restaurantName || "N/A"}
                          </Typography>
                        </Tooltip>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="small"
                        icon={<AccessTimeIcon style={{ fontSize: '12px' }} />}
                        label={booking.visitTime || "N/A"}
                        sx={{
                          bgcolor: alpha('#FF9800', 0.1),
                          color: '#E65100',
                          height: '24px',
                          fontSize: '0.75rem',
                          '& .MuiChip-icon': {
                            color: '#E65100'
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="small"
                        icon={mealTypeStyle.icon}
                        label={capitalizeFirstLetter(booking.mealType) || "N/A"}
                        sx={{
                          bgcolor: mealTypeStyle.bgcolor,
                          color: mealTypeStyle.color,
                          height: '24px',
                          fontSize: '0.75rem',
                          '& .MuiChip-icon': {
                            color: mealTypeStyle.color,
                            fontSize: '12px'
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell align="center">
                      <Chip
                        size="small"
                        icon={<PersonIcon style={{ fontSize: '12px' }} />}
                        label={booking.adultCount ?? "0"}
                        sx={{
                          bgcolor: alpha('#1976D2', 0.1),
                          color: '#1565C0',
                          height: '24px',
                          minWidth: '60px',
                          fontSize: '0.75rem',
                          '& .MuiChip-icon': {
                            color: '#1565C0'
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell align="center">
                      <Chip
                        size="small"
                        icon={<ChildCareIcon style={{ fontSize: '12px' }} />}
                        label={booking.childCount ?? "0"}
                        sx={{
                          bgcolor: alpha('#9C27B0', 0.1),
                          color: '#7B1FA2',
                          height: '24px',
                          minWidth: '60px',
                          fontSize: '0.75rem',
                          '& .MuiChip-icon': {
                            color: '#7B1FA2'
                          }
                        }}
                      />
                    </TableCell>
                    {/* <TableCell>
                      {booking.transport ? (
                        <Box sx={{ maxWidth: '110px' }}>
                          <Chip
                            size="small"
                            icon={<DirectionsCarIcon style={{ fontSize: '12px' }} />}
                            label={booking.transport.transport_type === 'shared' ? 'Shared' : 'Private'}
                            sx={{
                              fontWeight: "medium",
                              bgcolor: booking.transport.transport_type === 'shared' 
                                ? alpha('#4CAF50', 0.1) 
                                : alpha('#FF9800', 0.1),
                              color: booking.transport.transport_type === 'shared' 
                                ? '#2E7D32' 
                                : '#E65100',
                              height: '24px',
                              fontSize: '0.75rem',
                              '& .MuiChip-icon': {
                                color: booking.transport.transport_type === 'shared' 
                                  ? '#2E7D32' 
                                  : '#E65100'
                              },
                              '& .MuiChip-label': {
                                px: 0.8
                              }
                            }}
                          />
                          {booking.transport?.vehicle_name && (
                            <Tooltip title={booking.transport.vehicle_name} placement="top">
                              <Typography 
                                variant="caption" 
                                display="block" 
                                sx={{ 
                                  color: booking.transport.transport_type === 'shared' 
                                    ? '#2E7D32' 
                                    : '#E65100', 
                                  fontSize: '0.7rem',
                                  mt: 0.5,
                                  fontWeight: 'medium',
                                  overflow: 'hidden',
                                  textOverflow: 'ellipsis',
                                  whiteSpace: 'nowrap'
                                }}
                              >
                                {booking.transport.vehicle_name}
                              </Typography>
                            </Tooltip>
                          )}
                        </Box>
                      ) : (
                        <Chip
                          size="small"
                          label="No Transport"
                          sx={{
                            fontWeight: "medium",
                            bgcolor: alpha('#9E9E9E', 0.1),
                            color: '#616161',
                            height: '24px',
                            fontSize: '0.75rem',
                            '& .MuiChip-label': {
                              px: 0.8
                            }
                          }}
                        />
                      )}
                    </TableCell> */}
                    <TableCell>
                      {Array.isArray(booking.priceTypes) && booking.priceTypes.length > 0
                        ? (booking.priceTypes[0] === "travClicks" || booking.priceTypes[0] === "travclicks") ? (
                          <Chip 
                            label="Travclicks" 
                            size="small"
                            sx={{
                              bgcolor: alpha('#009688', 0.1),
                              color: '#00796B',
                              fontWeight: 'medium',
                              height: '24px',
                              fontSize: '0.75rem',
                              border: `1px solid ${alpha('#009688', 0.3)}`,
                              '& .MuiChip-label': {
                                px: 1
                              }
                            }}
                          />
                        ) : booking.priceTypes[0] === "dmc" ? (
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
                            <Typography variant="body2" fontWeight="medium" color="#E65100" noWrap sx={{ maxWidth: 80 }}>
                              {dmcCompanyName}
                            </Typography>
                          </Box>
                        ) : (
                          <Typography variant="body2" fontWeight="medium">
                            {capitalizeFirstLetter(booking.priceTypes[0])}
                          </Typography>
                        )
                        : <Typography variant="body2">N/A</Typography>
                      }
                    </TableCell>
                    <TableCell>
                      {PriceHide === "0" ? (
                        <>
                          <Chip
                            size="medium"
                            icon={<PriceCheckIcon fontSize="small" />}
                            label={
                              booking.totalPrice
                                ? `SGD ${Math.ceil(
                                    Math.ceil(booking.totalPrice)
                                    //  +
                                    //   (Math.ceil(booking.totalPrice) * sgdTax) /
                                    //     100
                                  )}`
                                : "N/A"
                            }
                            sx={{
                              fontWeight: "bold",
                              bgcolor: alpha("#673AB7", 0.1),
                              color: "#5E35B1",
                              height: "26px",
                              fontSize: "0.85rem",
                              "& .MuiChip-icon": {
                                color: "#5E35B1",
                              },
                            }}
                          />
                          {/* {sgdTax > 0 && (
                            <Typography
                              variant="caption"
                              display="block"
                              sx={{
                                color: "#5E35B1",
                                fontSize: "0.59rem",
                                mt: 0.1,
                                fontWeight: "medium",
                                textAlign: "center",
                                maxWidth: "90px",
                              }}
                            >
                              (incl. {sgdTax}% tax)
                            </Typography>
                          )} */}
                        </>
                      ) : (
                        <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                          Price Hidden
                        </div>
                      )}
                    </TableCell>
                    <TableCell>
                      <Chip
                        label={statusText}
                        size="small"
                        sx={{
                          color: statusStyle.color,
                          bgcolor: statusStyle.bgcolor,
                          border: statusStyle.border,
                          fontWeight: 'medium',
                          height: '24px',
                          fontSize: '0.75rem'
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      <Box sx={{ display: "flex", gap: "5px" }}>
                        <Button
                          variant="contained"
                          size="small"
                          startIcon={<VisibilityIcon style={{ fontSize: '14px' }} />}
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
                            px: 0.8,
                            py: 0.5,
                            minWidth: '60px',
                            fontSize: '0.7rem',
                            height: '26px',
                            '& .MuiButton-startIcon': {
                              marginRight: '3px'
                            }
                          }}
                        >
                          View
                        </Button>
                        {tourStatus !== "Actual" && (
                        <Button
                          variant="contained"
                          size="small"
                          startIcon={<CancelIcon style={{ fontSize: '14px' }} />}
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
                            px: 0.8,
                            py: 0.5,
                            minWidth: '60px',
                            fontSize: '0.7rem',
                            height: '26px',
                            '& .MuiButton-startIcon': {
                              marginRight: '3px'
                            }
                          }}
                        >
                          Cancel
                        </Button>
                        )}
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
                        bgcolor: alpha('#F44336', 0.1),
                        color: '#D32F2F',
                        width: 40,
                        height: 40,
                        mb: 1
                      }}
                    >
                      <RestaurantIcon />
                    </Avatar>
                    <Typography variant="body1" color="textSecondary">
                      No restaurant bookings available.
                    </Typography>
                  </Box>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      {isModalOpen && selectedBooking && (
        <RestaurantBookingModal
          open={isModalOpen}
          onClose={handleCloseModal}
          booking={selectedBooking}
        />
      )}

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

RestaurantsBookingsTable.displayName = 'RestaurantsBookingsTable';

export default RestaurantsBookingsTable;
