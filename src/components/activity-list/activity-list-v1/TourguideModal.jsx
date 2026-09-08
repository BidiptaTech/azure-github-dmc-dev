import React, { useState } from "react";
import {
  Modal,
  Box,
  Typography,
  Table,
  TableHead,
  TableBody,
  TableRow,
  TableCell,
  Paper,
  IconButton,
  Chip,
  Button,
  Avatar,
  Stack,
  Tooltip,
  alpha,
  useTheme,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import TourGuideBookingModal from "@/components/dashboard/dashboard/db-dashboard/components/TourGuideBookingModal";
import VisibilityIcon from "@mui/icons-material/Visibility";
import CancelIcon from "@mui/icons-material/Cancel";
import TourIcon from "@mui/icons-material/Tour";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import LocationCityIcon from "@mui/icons-material/LocationCity";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import PeopleIcon from "@mui/icons-material/People";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import TimerIcon from "@mui/icons-material/Timer";
import PriceCheckIcon from "@mui/icons-material/PriceCheck";
import CheckCircleOutlineIcon from "@mui/icons-material/CheckCircleOutline";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import BookmarkIcon from "@mui/icons-material/Bookmark";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import { useSelector } from "react-redux";
export default function TourguideModal({ open, onClose, bookings = [], date }) {
  const [isViewModalOpen, setIsViewModalOpen] = useState(false);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const theme = useTheme();
  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  const formatDate = (inputDate) => {
    if (!inputDate) return "N/A";

    // Handle different date formats
    if (inputDate.includes("/")) {
      const [day, month, year] = inputDate.split("/");
      return `${day}-${month}-${year}`;
    } else if (inputDate.includes("-")) {
      const [year, month, day] = inputDate.split("-");
      return `${day}-${month}-${year}`;
    }

    return inputDate;
  };

  // Function to get booking type color and icon
  const getBookingTypeInfo = (type) => {
    switch (type?.toLowerCase()) {
      case "booking":
        return {
          bg: "#1E8E3E",
          color: "white",
          lightBg: "#E6F4EA",
          icon: <CheckCircleOutlineIcon fontSize="small" />,
        };
      case "enquiry":
        return {
          bg: "#F9AB00",
          color: "white",
          lightBg: "#FEF7E0",
          icon: <HelpOutlineIcon fontSize="small" />,
        };
      case "pending":
        return {
          bg: "#1A73E8",
          color: "white",
          lightBg: "#E8F0FE",
          icon: <TimerIcon fontSize="small" />,
        };
      case "cancelled":
        return {
          bg: "#D93025",
          color: "white",
          lightBg: "#FCE8E6",
          icon: <CancelOutlinedIcon fontSize="small" />,
        };
      default:
        return {
          bg: "#5F6368",
          color: "white",
          lightBg: "#F1F3F4",
          icon: <BookmarkIcon fontSize="small" />,
        };
    }
  };

  const handleView = (booking) => {
    setSelectedBooking(booking);
    setIsViewModalOpen(true);
  };

  const handleCloseViewModal = () => {
    setIsViewModalOpen(false);
    setSelectedBooking(null);
  };

  // Filter bookings based on the selected date
  const filteredBookings = Array.isArray(bookings)
    ? bookings.filter((booking) => {
        return booking.bookingDate === date;
      })
    : [];

  return (
    <>
      <Modal
        open={open}
        onClose={onClose}
        aria-labelledby="modal-title"
        aria-describedby="modal-description"
      >
        <Box
          sx={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
            width: "95%",
            maxWidth: "1300px",
            maxHeight: "90vh",
            overflowY: "auto",
            bgcolor: "background.paper",
            borderRadius: 2,
            boxShadow: "0 8px 32px rgba(0, 0, 0, 0.15)",
            p: 0,
            "&::-webkit-scrollbar": {
              width: "6px",
            },
            "&::-webkit-scrollbar-track": {
              background: "#f1f1f1",
            },
            "&::-webkit-scrollbar-thumb": {
              background: "#bdbdbd",
              borderRadius: "10px",
              "&:hover": {
                background: "#a5a5a5",
              },
            },
          }}
        >
          {/* Compact header with title and date in one line */}
          <Box
            sx={{
              background: "linear-gradient(90deg, #1976d2 0%, #0D47A1 100%)",
              color: "white",
              py: 1.5,
              px: 2.5,
              borderTopLeftRadius: 8,
              borderTopRightRadius: 8,
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
            }}
          >
            <Stack direction="row" spacing={3} alignItems="center">
              <Stack direction="row" spacing={1} alignItems="center">
                <Avatar
                  sx={{
                    bgcolor: "white",
                    color: "#1976d2",
                    width: 36,
                    height: 36,
                  }}
                >
                  <TourIcon fontSize="small" />
                </Avatar>
                <Typography variant="h6" fontWeight="600" fontSize="1.25rem">
                  Tour Guide Bookings
                </Typography>
              </Stack>

              <Stack
                direction="row"
                spacing={1}
                alignItems="center"
                sx={{
                  bgcolor: alpha("#fff", 0.15),
                  borderRadius: 1.5,
                  px: 1.5,
                  py: 0.75,
                }}
              >
                <EventAvailableIcon fontSize="medium" />
                <Typography fontWeight={500} fontSize="1rem" color="white">
                  {formatDate(date)}
                </Typography>
              </Stack>
            </Stack>

            <IconButton
              onClick={onClose}
              size="small"
              sx={{
                color: "white",
                bgcolor: "rgba(255,255,255,0.1)",
                "&:hover": { bgcolor: "rgba(255, 255, 255, 0.2)" },
              }}
            >
              <CloseIcon fontSize="small" />
            </IconButton>
          </Box>

          <Box sx={{ p: 2 }}>
            {filteredBookings.length > 0 ? (
              <Paper
                sx={{
                  width: "100%",
                  overflow: "hidden",
                  boxShadow: "0 2px 10px rgba(0, 0, 0, 0.05)",
                  borderRadius: 2,
                }}
              >
                <Table size="medium" sx={{ minWidth: 900 }}>
                  <TableHead>
                    <TableRow
                      sx={{
                        background:
                          "linear-gradient(90deg, #1E88E5 0%, #42A5F5 100%)",
                      }}
                    >
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <TourIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Tour Guide
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <BookmarkIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Booking Type
                          </Typography>
                        </Box>
                      </TableCell>
                      {/* <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <EventAvailableIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Booking Date</Typography>
                        </Box>
                      </TableCell> */}
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <LocationCityIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            City
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <AccessTimeIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Start Time
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <PeopleIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Adults
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <ChildCareIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Children
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <TimerIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Hours
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <PriceCheckIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Price
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.8,
                          whiteSpace: "nowrap",
                        }}
                      >
                        <Typography
                          variant="body1"
                          fontWeight="bold"
                          color="white"
                        >
                          Actions
                        </Typography>
                      </TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {filteredBookings.map((booking, index) => {
                      // Get booking type and color
                      const bookingType = booking.bookingType || "booking";
                      const typeInfo = getBookingTypeInfo(bookingType);

                      return (
                        <TableRow
                          key={index}
                          sx={{
                            backgroundColor:
                              index % 2 === 0 ? "#fafafa" : "#ffffff",
                            "&:hover": {
                              backgroundColor: alpha(typeInfo.lightBg, 0.5),
                            },
                            transition: "background-color 0.3s ease",
                          }}
                        >
                          <TableCell>
                            <Box
                              sx={{
                                display: "flex",
                                alignItems: "center",
                                gap: 1,
                              }}
                            >
                              <Avatar
                                sx={{
                                  width: 28,
                                  height: 28,
                                  bgcolor: alpha("#1976d2", 0.1),
                                  color: "#1976d2",
                                  fontSize: "14px",
                                  fontWeight: "bold",
                                }}
                              >
                                {booking.guide_name?.charAt(0) || "G"}
                              </Avatar>
                              <Tooltip title={booking.guide_name || "N/A"}>
                                <Typography
                                  variant="body1"
                                  fontWeight="500"
                                  noWrap
                                  sx={{ maxWidth: 150 }}
                                >
                                  {booking.guide_name || "N/A"}
                                </Typography>
                              </Tooltip>
                            </Box>
                          </TableCell>
                          <TableCell>
                            <Chip
                              icon={typeInfo.icon}
                              label={
                                bookingType.charAt(0).toUpperCase() +
                                bookingType.slice(1).toLowerCase()
                              }
                              size="medium"
                              sx={{
                                bgcolor: typeInfo.bg,
                                color: typeInfo.color,
                                fontWeight: "medium",
                                height: "28px",
                                fontSize: "0.9rem",
                                "& .MuiChip-icon": {
                                  color: "inherit",
                                  fontSize: "18px",
                                },
                                "& .MuiChip-label": {
                                  px: 1,
                                },
                              }}
                            />
                          </TableCell>
                          {/* <TableCell>
                            <Chip
                              icon={<EventAvailableIcon style={{ fontSize: '16px' }} />}
                              label={formatDate(booking.bookingDate) || "N/A"}
                              size="medium"
                              sx={{ 
                                bgcolor: alpha('#1976d2', 0.1),
                                color: '#1976d2',
                                fontWeight: "medium",
                                height: '28px',
                                fontSize: '0.9rem',
                                '& .MuiChip-icon': {
                                  color: '#1976d2'
                                }
                              }}
                            />
                          </TableCell> */}
                          <TableCell>
                            <Typography
                              variant="body1"
                              sx={{ color: "#424242" }}
                            >
                              {booking.entrypickup || "N/A"}
                            </Typography>
                          </TableCell>
                          <TableCell>
                            <Chip
                              icon={
                                <AccessTimeIcon style={{ fontSize: "16px" }} />
                              }
                              label={booking.entrytime || "N/A"}
                              size="medium"
                              sx={{
                                bgcolor: alpha("#4CAF50", 0.1),
                                color: "#2E7D32",
                                fontWeight: "medium",
                                height: "28px",
                                fontSize: "0.9rem",
                                "& .MuiChip-icon": {
                                  color: "#2E7D32",
                                },
                              }}
                            />
                          </TableCell>
                          <TableCell align="center">
                            <Chip
                              icon={<PeopleIcon style={{ fontSize: "16px" }} />}
                              label={booking.adults}
                              size="medium"
                              sx={{
                                fontWeight: "medium",
                                bgcolor: alpha("#1976d2", 0.1),
                                color: "#1976d2",
                                height: "28px",
                                fontSize: "0.9rem",
                                "& .MuiChip-icon": {
                                  color: "#1976d2",
                                },
                              }}
                            />
                          </TableCell>
                          <TableCell align="center">
                            <Chip
                              icon={
                                <ChildCareIcon style={{ fontSize: "16px" }} />
                              }
                              label={booking.children}
                              size="medium"
                              sx={{
                                fontWeight: "medium",
                                bgcolor: alpha("#FF9800", 0.1),
                                color: "#E65100",
                                height: "28px",
                                fontSize: "0.9rem",
                                "& .MuiChip-icon": {
                                  color: "#E65100",
                                },
                              }}
                            />
                          </TableCell>
                          <TableCell align="center">
                            <Chip
                              icon={<TimerIcon style={{ fontSize: "16px" }} />}
                              label={booking.hours}
                              size="medium"
                              sx={{
                                fontWeight: "medium",
                                bgcolor: alpha("#673AB7", 0.1),
                                color: "#5E35B1",
                                height: "28px",
                                fontSize: "0.9rem",
                                "& .MuiChip-icon": {
                                  color: "#5E35B1",
                                },
                              }}
                            />
                          </TableCell>
                          <TableCell>
                            {PriceHide === "0" ? (
                              <>
                                <Chip
                                  size="medium"
                                  icon={
                                    <PriceCheckIcon
                                      style={{ fontSize: "16px" }}
                                    />
                                  }
                                  label={`${Math.ceil(
                                    Math.ceil(booking.totalPrice)
                                  )}`}
                                  // Math.ceil(booking.totalPrice) +
                                  //     (Math.ceil(booking.totalPrice) * sgdTax) /
                                  //       100
                                  // )}`}
                                  sx={{
                                    fontWeight: "bold",
                                    bgcolor: alpha("#673AB7", 0.1),
                                    color: "#5E35B1",
                                    height: "28px",
                                    fontSize: "0.9rem",
                                    "& .MuiChip-icon": {
                                      color: "#5E35B1",
                                    },
                                    "& .MuiChip-label": {
                                      px: 0.8,
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
                            <Stack direction="row" spacing={1}>
                              <Button
                                variant="contained"
                                size="medium"
                                startIcon={<VisibilityIcon />}
                                onClick={() => handleView(booking)}
                                sx={{
                                  background: `linear-gradient(135deg, ${
                                    typeInfo.bg
                                  } 0%, ${alpha(typeInfo.bg, 0.8)} 100%)`,
                                  color: "white",
                                  "&:hover": {
                                    background: typeInfo.bg,
                                    boxShadow: `0 4px 12px ${alpha(
                                      typeInfo.bg,
                                      0.4
                                    )}`,
                                  },
                                  borderRadius: 2,
                                  boxShadow: `0 2px 8px ${alpha(
                                    typeInfo.bg,
                                    0.3
                                  )}`,
                                  textTransform: "none",
                                  fontWeight: 500,
                                  px: 2,
                                  py: 0.8,
                                  fontSize: "0.9rem",
                                }}
                              >
                                View
                              </Button>

                              {/* <Button
                                variant="contained"
                                size="medium"
                                startIcon={<CancelIcon />}
                                onClick={() => {}}
                                sx={{
                                  background: "linear-gradient(135deg, #f44336 0%, #e57373 100%)",
                                  color: "white",
                                  "&:hover": {
                                    background: "#c62828",
                                    boxShadow: `0 4px 12px ${alpha('#f44336', 0.4)}`,
                                  },
                                  borderRadius: 2,
                                  boxShadow: `0 2px 8px ${alpha('#f44336', 0.3)}`,
                                  textTransform: 'none',
                                  fontWeight: 500,
                                  px: 2,
                                  py: 0.8,
                                  fontSize: '0.9rem'
                                }}
                              >
                                Cancel
                              </Button> */}
                            </Stack>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </Paper>
            ) : (
              <Box
                sx={{
                  textAlign: "center",
                  py: 4,
                  bgcolor: alpha("#1976d2", 0.04),
                  borderRadius: 2,
                  border: `1px dashed ${alpha("#1976d2", 0.2)}`,
                  display: "flex",
                  flexDirection: "column",
                  alignItems: "center",
                  gap: 1.5,
                }}
              >
                <Avatar
                  sx={{
                    bgcolor: alpha("#1976d2", 0.1),
                    color: "#1976d2",
                    width: 48,
                    height: 48,
                  }}
                >
                  <TourIcon />
                </Avatar>
                <Typography
                  variant="h6"
                  sx={{ color: "#1976d2", fontWeight: 500 }}
                >
                  No tour guide bookings found for {formatDate(date)}
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ color: "#757575", maxWidth: 400 }}
                >
                  There are no tour guide bookings registered for this date. Try
                  selecting a different date or check back later.
                </Typography>
              </Box>
            )}
          </Box>
        </Box>
      </Modal>

      {isViewModalOpen && selectedBooking && (
        <TourGuideBookingModal
          open={isViewModalOpen}
          onClose={handleCloseViewModal}
          booking={selectedBooking}
        />
      )}
    </>
  );
}
