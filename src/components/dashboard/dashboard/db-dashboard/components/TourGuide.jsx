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
import TourGuideBookingModal from "./TourGuideBookingModal";
import { Typography, Box, Chip, Avatar, alpha } from "@mui/material";
import TourIcon from "@mui/icons-material/Tour";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import PersonIcon from "@mui/icons-material/Person";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import HourglassEmptyIcon from "@mui/icons-material/HourglassEmpty";
import EmojiPeopleIcon from "@mui/icons-material/EmojiPeople";
import PaymentsIcon from "@mui/icons-material/Payments";

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

const TourGuide = React.memo(({ onCountChange }) => {
  const { bookings, status, error } = useSelector((state) => state.viewDetails);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const { DmcLogo, DmcName } = useSelector((state) => state.auth);
  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // Memoize the tour guide bookings count
  const tourGuideCount = useMemo(
    () => bookings?.guide?.length || 0,
    [bookings?.guide?.length]
  );

  // Only update count when it actually changes
  useEffect(() => {
    onCountChange(tourGuideCount);
  }, [tourGuideCount, onCountChange]);

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

  const handleCancel = (booking) => {
    // Handle cancel action
    console.log("Cancel booking:", booking);
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
        }}
      >
        <Table>
          <TableHead>
            <TableRow
              sx={{
                background: "linear-gradient(90deg, #009688 0%, #00796B 100%)",
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
                    Pickup Location
                  </Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                  <AccessTimeIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">
                    Pickup Time
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
                  <EmojiPeopleIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">
                    Guide
                  </Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff" }}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                  <HourglassEmptyIcon fontSize="small" />
                  <Typography variant="body1" fontWeight="bold" color="white">
                    Duration
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
            {bookings?.guide?.length > 0 ? (
              bookings.guide.map((booking, index) => {
                const statusText = getStatusText(booking.status);
                const statusStyle = getStatusStyle(statusText);

                return (
                  <TableRow
                    key={index}
                    sx={{
                      backgroundColor: index % 2 === 0 ? "#fafafa" : "#ffffff",
                      "&:hover": { backgroundColor: alpha("#009688", 0.04) },
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
                          width: "140px",
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
                          maxWidth: "180px",
                        }}
                      >
                        <LocationOnIcon
                          fontSize="small"
                          sx={{ color: "#009688", flexShrink: 0 }}
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
                          {booking.entrypickup || "N/A"}
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
                      <Box
                        sx={{ display: "flex", alignItems: "center", gap: 1 }}
                      >
                        <Avatar
                          sx={{
                            width: 28,
                            height: 28,
                            bgcolor: alpha("#009688", 0.1),
                            color: "#00796B",
                            fontSize: "14px",
                            fontWeight: "bold",
                          }}
                        >
                          {booking.guide_name?.charAt(0) || "G"}
                        </Avatar>
                        <Typography variant="body1" fontWeight="500">
                          {booking.guide_name || "N/A"}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="small"
                        icon={<HourglassEmptyIcon fontSize="small" />}
                        label={`${booking.hours || "0"} hours`}
                        sx={{
                          bgcolor: alpha("#009688", 0.1),
                          color: "#00796B",
                          height: "26px",
                          fontSize: "0.85rem",
                          "& .MuiChip-icon": {
                            color: "#00796B",
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
                        bgcolor: alpha("#009688", 0.1),
                        color: "#00796B",
                        width: 40,
                        height: 40,
                        mb: 1,
                      }}
                    >
                      <TourIcon />
                    </Avatar>
                    <Typography variant="body1" color="textSecondary">
                      No tour guide bookings available.
                    </Typography>
                  </Box>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      <TourGuideBookingModal
        open={isModalOpen}
        onClose={handleCloseModal}
        booking={selectedBooking}
      />
    </>
  );
});

TourGuide.displayName = "TourGuide";

export default TourGuide;
