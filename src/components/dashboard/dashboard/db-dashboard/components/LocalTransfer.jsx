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
import LocalTransferBookingModal from "./LocalTransferBookingModal";
import { Typography, Box, Chip, Avatar, alpha, Snackbar, Alert } from "@mui/material";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import PersonIcon from "@mui/icons-material/Person";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import DirectionsIcon from "@mui/icons-material/Directions";
import AirportShuttleIcon from "@mui/icons-material/AirportShuttle";
import PaymentsIcon from "@mui/icons-material/Payments";
import { singleBooking } from "@/slice/common/commonSlice";

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
        border: `1px solid ${alpha("#1E8E3E", 0.3)}`,
      };
    case "Pending":
      return {
        color: "#F9AB00",
        bgcolor: alpha("#F9AB00", 0.1),
        border: `1px solid ${alpha("#F9AB00", 0.3)}`,
      };
    case "Confirmed":
      return {
        color: "#1A73E8",
        bgcolor: alpha("#1A73E8", 0.1),
        border: `1px solid ${alpha("#1A73E8", 0.3)}`,
      };
    case "Cancelled":
      return {
        color: "#D93025",
        bgcolor: alpha("#D93025", 0.1),
        border: `1px solid ${alpha("#D93025", 0.3)}`,
      };
    default:
      return {
        color: "#5F6368",
        bgcolor: alpha("#5F6368", 0.1),
        border: `1px solid ${alpha("#5F6368", 0.3)}`,
      };
  }
};

const LocalTransfer = React.memo(({ onCountChange}) => {
  const dispatch = useDispatch();
  const { bookings, status, error } = useSelector((state) => state.viewDetails);
  console.log("bookings", bookings, "status", status, "error", error);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [showSuccessToast, setShowSuccessToast] = useState(false);
  const { DmcLogo, DmcName } = useSelector((state) => state.auth);
  const travelPointData = bookings?.travel_point || [];
  const travelHourlyData = bookings?.travel_hourly || [];
  const travelZoneData = bookings?.local_transport || [];
  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // Memoize the local transfer bookings count
  const localTransferCount = useMemo(
    () => travelPointData.length + travelHourlyData.length + travelZoneData.length || 0,
    [travelPointData.length, travelHourlyData.length, travelZoneData.length]
  );

  const combinedData = useMemo(() => {
    return [...travelPointData, ...travelHourlyData, ...travelZoneData];
  }, [travelPointData, travelHourlyData, travelZoneData]);
  // Only update count when it actually changes
  useEffect(() => {
    onCountChange(localTransferCount);
  }, [localTransferCount, onCountChange]);

  if (status === "loading")
    return (
      <Box
        sx={{
          p: 4,
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          bgcolor: alpha("#1976d2", 0.04),
          borderRadius: 2,
        }}
      >
        <Typography variant="body1" color="primary">
          Loading bookings...
        </Typography>
      </Box>
    );

  if (status === "failed")
    return (
      <Box
        sx={{
          p: 4,
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          bgcolor: alpha("#d32f2f", 0.04),
          borderRadius: 2,
        }}
      >
        <Typography variant="body1" color="error">
          Error: {error}
        </Typography>
      </Box>
    );

  const handleView = (booking) => {
    setSelectedBooking(booking);
    setIsModalOpen(true);
  };

  const handleCancel = async (booking) => {
    // Handle cancel action
    try {
      // For local transfer, use the appropriate booking ID and tour ID
      const bookingId = booking.entry_booking_id || booking.exit_booking_id || booking.booking_id;
      // Get tour_id from the root bookings object since it's not in individual booking objects
      const tourId = bookings?.tour?.tour_id;
      
      if (bookingId && tourId) {
        const result = await dispatch(singleBooking({bookingId: bookingId, tourId: tourId}));
        console.log("Cancel local transfer booking:", { bookingId, tourId, booking });
        
        // Check if cancellation was successful
        if (result.meta.requestStatus === 'fulfilled') {
          console.log("Local transfer booking cancelled successfully");
          // Show success toaster
          setShowSuccessToast(true);
        } else if (result.meta.requestStatus === 'rejected') {
          console.error("Failed to cancel local transfer booking:", result.error);
        }
      } else {
        console.error("Missing data for cancellation:", { bookingId, tourId, booking });
      }
    } catch (error) {
      console.error("Error cancelling local transfer booking:", error);
    }
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setSelectedBooking(null);
  };

  return (
    <>
      <TableContainer
        component={Paper}
        elevation={2}
        sx={{
          borderRadius: 2,
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
                background: "linear-gradient(90deg, #3F51B5 0%, #5C6BC0 100%)",
                "& .MuiTableCell-head": {
                  fontWeight: "bold",
                  py: 1.8,
                  whiteSpace: "nowrap",
                },
              }}
            >
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                  <CalendarTodayIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">
                    Booking Date
                  </Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                  <LocationOnIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">
                    Pickup
                  </Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                  <DirectionsIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">
                    Drop-off
                  </Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                  <AccessTimeIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">
                    Time
                  </Typography>
                </Box>
              </TableCell>
              {/* <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <PersonIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Adults</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <ChildCareIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">Children</Typography>
                </Box>
              </TableCell> */}
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                  <DirectionsCarIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">
                    Vehicle
                  </Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff" }}>Price</TableCell>
              <TableCell sx={{ color: "#fff" }}>Mode</TableCell>
              <TableCell sx={{ color: "#fff" }}>Status</TableCell>
              <TableCell sx={{ color: "#fff" }}>Actions</TableCell>
            </TableRow>
          </TableHead>

          <TableBody>
            {combinedData.length > 0 ? (
              combinedData.map((booking, index) => {
                const statusText = getStatusText(booking.status);
                const statusStyle = getStatusStyle(statusText);

                return (
                  <TableRow
                    key={index}
                    sx={{
                      backgroundColor: index % 2 === 0 ? "#fafafa" : "#ffffff",
                      "&:hover": { backgroundColor: alpha("#3F51B5", 0.04) },
                      transition: "background-color 0.3s ease",
                    }}
                  >
                    <TableCell>
                      <Chip
                        size="medium"
                        label={
                          booking.bookingDate
                            ? dayjs(booking.bookingDate).format(
                                "dddd DD MMM YY"
                              )
                            : "N/A"
                        }
                        sx={{
                          bgcolor: alpha("#4CAF50", 0.1),
                          color: "#2E7D32",
                          fontWeight: "medium",
                          height: "26px",
                          fontSize: "0.85rem",
                          "& .MuiChip-label": {
                            px: 0.8,
                          },
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      <Box
                        sx={{
                          display: "flex",
                          alignItems: "center",
                          gap: 1,
                          maxWidth: "100px",
                        }}
                      >
                        <LocationOnIcon
                          fontSize="small"
                          sx={{ color: "#3F51B5", flexShrink: 0 }}
                        />
                        <Typography
                          variant="body2"
                          sx={{
                            fontWeight: 500,
                            whiteSpace: "nowrap",
                            overflow: "hidden",
                            textOverflow: "ellipsis",
                          }}
                        >
                          {booking.entrypickup?.split(",")[0] || "N/A"}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Box
                        sx={{
                          display: "flex",
                          alignItems: "center",
                          gap: 1,
                          maxWidth: "100px",
                        }}
                      >
                        <DirectionsIcon
                          fontSize="small"
                          sx={{ color: "#7B1FA2", flexShrink: 0 }}
                        />
                        <Typography
                          variant="body2"
                          sx={{
                            fontWeight: 500,
                            whiteSpace: "nowrap",
                            overflow: "hidden",
                            textOverflow: "ellipsis",
                          }}
                        >
                          {booking.entrydropoff?.split(",")[0] || "N/A"}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="small"
                        icon={<AccessTimeIcon fontSize="small" />}
                        label={booking.entrytime || "N/A"}
                        sx={{
                          bgcolor: alpha("#FF9800", 0.1),
                          color: "#E65100",
                          height: "26px",
                          fontSize: "0.85rem",
                        }}
                      />
                    </TableCell>
                    {/* <TableCell align="center">
                      <Chip
                        size="small"
                        icon={<PersonIcon fontSize="small" />}
                        label={booking.adults ?? "0"}
                        sx={{
                          bgcolor: alpha('#1976D2', 0.1),
                          color: '#1565C0',
                          height: '24px',
                          minWidth: '60px',
                          fontSize: '0.85rem',
                          '& .MuiChip-icon': {
                            color: '#1565C0'
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell align="center">
                      <Chip
                        size="small"
                        icon={<ChildCareIcon fontSize="small" />}
                        label={booking.children ?? "0"}
                        sx={{
                          bgcolor: alpha('#9C27B0', 0.1),
                          color: '#7B1FA2',
                          height: '24px',
                          minWidth: '60px',
                          fontSize: '0.85rem',
                          '& .MuiChip-icon': {
                            color: '#7B1FA2'
                          }
                        }}
                      />
                    </TableCell> */}
                    <TableCell>
                      <Chip
                        size="small"
                        icon={<AirportShuttleIcon fontSize="small" />}
                        label={booking.vehicles_name || "N/A"}
                        sx={{
                          bgcolor: alpha("#3F51B5", 0.1),
                          color: "#303F9F",
                          height: "26px",
                          fontSize: "0.85rem",
                          "& .MuiChip-icon": {
                            color: "#303F9F",
                          },
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      {PriceHide === "0" ? (
                        <>
                          <Chip
                            size="medium"
                            icon={<PaymentsIcon fontSize="small" />}
                            label={
                              booking.totalPrice
                                ? `SGD ${Math.ceil(
                                    Math.ceil(booking.totalPrice) +
                                      (Math.ceil(booking.totalPrice) * sgdTax) /
                                        100
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
                          {sgdTax > 0 && (
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
                          )}
                        </>
                      ) : (
                        <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                          Price Hidden
                        </div>
                      )}
                    </TableCell>
                    <TableCell>
                      <Box
                        sx={{
                          display: "flex",
                          alignItems: "center",
                          p: 0.5,
                          borderRadius: "2px",
                          backgroundColor: "rgba(53, 84, 209, 0.05)",
                        }}
                      >
                        {booking.Mode === "travClicks" ||
                        booking.Mode === "travclicks" ? (
                          <Chip
                            label="Travclicks"
                            color="primary"
                            sx={{ fontSize: "0.7rem" }}
                          />
                        ) : booking.Mode === "dmc" ? (
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              gap: 0.8,
                            }}
                          >
                            {DmcLogo && (
                              <Avatar
                                src={DmcLogo}
                                alt="DMC Logo"
                                sx={{ width: 20, height: 20 }}
                              />
                            )}
                            <Typography
                              variant="body3"
                              fontWeight="medium"
                              color="#E65100"
                            >
                              {`${DmcName || "DMC"}`}
                            </Typography>
                          </Box>
                        ) : (
                          <Typography
                            variant="body3"
                            fontWeight="medium"
                            color="#E65100"
                          >
                            {capitalizeFirstLetter(booking.Mode)}
                          </Typography>
                        )}
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Chip
                        label={statusText}
                        size="small"
                        sx={{
                          color: statusStyle.color,
                          bgcolor: statusStyle.bgcolor,
                          border: statusStyle.border,
                          fontWeight: "medium",
                          fontSize: "0.75rem",
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
                            textTransform: "none",
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
                            textTransform: "none",
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
                  <Box
                    sx={{
                      py: 3,
                      display: "flex",
                      flexDirection: "column",
                      alignItems: "center",
                      gap: 1,
                    }}
                  >
                    <Avatar
                      sx={{
                        bgcolor: alpha("#3F51B5", 0.1),
                        color: "#303F9F",
                        width: 40,
                        height: 40,
                        mb: 1,
                      }}
                    >
                      <DirectionsCarIcon />
                    </Avatar>
                    <Typography variant="body1" color="textSecondary">
                      No local transfers available.
                    </Typography>
                  </Box>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      <LocalTransferBookingModal
        open={isModalOpen}
        onClose={handleCloseModal}
        booking={selectedBooking}
      />

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

LocalTransfer.displayName = "LocalTransfer";

export default LocalTransfer;
