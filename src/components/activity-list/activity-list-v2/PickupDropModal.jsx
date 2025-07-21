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
import VisibilityIcon from "@mui/icons-material/Visibility";
import CancelIcon from "@mui/icons-material/Cancel";
import AirportShuttleIcon from "@mui/icons-material/AirportShuttle";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import BookmarkIcon from "@mui/icons-material/Bookmark";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import PeopleIcon from "@mui/icons-material/People";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import PriceCheckIcon from "@mui/icons-material/PriceCheck";
import FlightTakeoffIcon from "@mui/icons-material/FlightTakeoff";
import FlightLandIcon from "@mui/icons-material/FlightLand";
import CheckCircleOutlineIcon from "@mui/icons-material/CheckCircleOutline";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import TimerIcon from "@mui/icons-material/Timer";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import PickUpDropBookingModal from "@/components/dashboard/dashboard/db-dashboard/components/PickUpDropBookingModal";
import { useSelector } from "react-redux";
export default function PickupDropModal({
  open,
  onClose,
  bookings = [],
  date,
}) {
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isViewModalOpen, setIsViewModalOpen] = useState(false);
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

  // Pre-process bookings to ensure proper display for both entry and exit ports
  const processedBookings = React.useMemo(() => {
    if (!Array.isArray(bookings)) return [];

    return bookings.map((booking) => {
      // Add a dynamic type property if it doesn't exist
      const processedBooking = { ...booking };

      // Handle entry port bookings
      if (booking.entrypickup || booking.entrydropoff) {
        processedBooking._displayType = "Entry Port";
        processedBooking._displayIcon = <FlightLandIcon />;
      }
      // Handle exit port bookings
      else if (booking.exitpickup || booking.exitdropoff) {
        processedBooking._displayType = "Exit Port";
        processedBooking._displayIcon = <FlightTakeoffIcon />;
      } else {
        processedBooking._displayType = "Transport";
        processedBooking._displayIcon = <AirportShuttleIcon />;
      }

      return processedBooking;
    });
  }, [bookings]);

  // Replace the allBookings declaration with this:
  const allBookings = processedBookings;

  // Add debug info to console
  console.log("PROCESSED BOOKINGS:", processedBookings);
  console.log(
    "ENTRY BOOKINGS COUNT:",
    processedBookings.filter((b) => b._displayType === "Entry Port").length
  );
  console.log(
    "EXIT BOOKINGS COUNT:",
    processedBookings.filter((b) => b._displayType === "Exit Port").length
  );

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
            width: "98%",
            maxWidth: "1400px",
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
                  <AirportShuttleIcon fontSize="small" />
                </Avatar>
                <Typography variant="h6" fontWeight="600" fontSize="1.25rem">
                  Airport Travel Bookings
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

          <Box sx={{ p: 1.5 }}>
            {/* All Bookings */}
            {allBookings.length > 0 ? (
              <Paper
                sx={{
                  width: "100%",
                  overflow: "hidden",
                  boxShadow: "0 2px 10px rgba(0, 0, 0, 0.05)",
                  borderRadius: 2,
                }}
              >
                {/* <Box sx={{ 
                  background: "linear-gradient(90deg, #1E88E5 0%, #42A5F5 100%)",
                  py: 1,
                  px: 2,
                  display: 'flex',
                  alignItems: 'center',
                  gap: 1
                }}>
                  <AirportShuttleIcon sx={{ color: 'white' }} />
                  <Typography variant="h6" color="white" fontWeight="600">
                    All Travel Bookings
                  </Typography>
                </Box> */}
                <Table size="small" sx={{ minWidth: 900 }}>
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
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "12%",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <AirportShuttleIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Type
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "12%",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <LocationOnIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Pick Up
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "12%",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <LocationOnIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Drop Off
                          </Typography>
                        </Box>
                      </TableCell>
                      {/* <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "9%",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <EventAvailableIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Date
                          </Typography>
                        </Box>
                      </TableCell> */}
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "7%",
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
                            Time
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "6%",
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
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "6%",
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
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "10%",
                        }}
                      >
                        <Box
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 0.5,
                          }}
                        >
                          <DirectionsCarIcon fontSize="small" />
                          <Typography
                            variant="body1"
                            fontWeight="bold"
                            color="white"
                          >
                            Vehicle
                          </Typography>
                        </Box>
                      </TableCell>
                      <TableCell
                        sx={{
                          color: "white",
                          fontWeight: "bold",
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "7%",
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
                          py: 1.5,
                          whiteSpace: "nowrap",
                          width: "15%",
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
                    {allBookings.map((booking, index) => {
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
                          <TableCell sx={{ py: 1 }}>
                            <Stack direction="row" spacing={0.5}>
                              <Chip
                                icon={typeInfo.icon}
                                label={
                                  bookingType.charAt(0).toUpperCase() +
                                  bookingType.slice(1).toLowerCase()
                                }
                                size="small"
                                sx={{
                                  bgcolor: typeInfo.bg,
                                  color: typeInfo.color,
                                  fontWeight: 500,
                                  height: "32px",
                                  fontSize: "0.875rem",
                                  "& .MuiChip-icon": {
                                    color: "inherit",
                                    fontSize: "18px",
                                  },
                                  "& .MuiChip-label": {
                                    px: 0.8,
                                  },
                                }}
                              />
                            </Stack>
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Stack direction="row" spacing={0.5}>
                              <Chip
                                icon={booking._displayIcon}
                                label={booking._displayType}
                                size="medium"
                                sx={{
                                  bgcolor: alpha("#1976d2", 0.1),
                                  color: "#1976d2",
                                  fontWeight: 500,
                                  height: "32px",
                                  fontSize: "0.875rem",
                                  "& .MuiChip-icon": {
                                    color: "#1976d2",
                                    fontSize: "18px",
                                  },
                                  "& .MuiChip-label": {
                                    px: 1.2,
                                    py: 0.5,
                                  },
                                }}
                              />
                            </Stack>
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Tooltip
                              title={
                                booking.entrypickup ||
                                booking.exitpickup ||
                                "N/A"
                              }
                            >
                              <Box
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  gap: 0.5,
                                }}
                              >
                                <LocationOnIcon
                                  sx={{ color: "#1976d2" }}
                                  fontSize="small"
                                />
                                <Typography
                                  variant="body2"
                                  fontWeight="500"
                                  sx={{
                                    maxWidth: "120px",
                                    overflow: "hidden",
                                    textOverflow: "ellipsis",
                                    whiteSpace: "nowrap",
                                  }}
                                >
                                  {booking.entrypickup ||
                                    booking.exitpickup ||
                                    "N/A"}
                                </Typography>
                              </Box>
                            </Tooltip>
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Tooltip
                              title={
                                booking.entrydropoff ||
                                booking.exitdropoff ||
                                "N/A"
                              }
                            >
                              <Box
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  gap: 0.5,
                                }}
                              >
                                <LocationOnIcon
                                  sx={{ color: "#E65100" }}
                                  fontSize="small"
                                />
                                <Typography
                                  variant="body2"
                                  fontWeight="500"
                                  sx={{
                                    maxWidth: "120px",
                                    overflow: "hidden",
                                    textOverflow: "ellipsis",
                                    whiteSpace: "nowrap",
                                  }}
                                >
                                  {booking.entrydropoff ||
                                    booking.exitdropoff ||
                                    "N/A"}
                                </Typography>
                              </Box>
                            </Tooltip>
                          </TableCell>
                          {/* <TableCell sx={{ py: 1 }}>
                            <Chip
                              icon={
                                <EventAvailableIcon
                                  style={{ fontSize: "12px" }}
                                />
                              }
                              label={formatDate(
                                booking.bookingDate ||
                                  booking.exitpickupdate ||
                                  "N/A"
                              )}
                              size="small"
                              sx={{
                                bgcolor: alpha("#1976d2", 0.1),
                                color: "#1976d2",
                                fontWeight: "medium",
                                height: "24px",
                                fontSize: "0.75rem",
                                "& .MuiChip-icon": {
                                  color: "#1976d2",
                                },
                              }}
                            />
                         
                            {booking.exitpickupdate &&
                              booking.bookingDate &&
                              booking.exitpickupdate !==
                                booking.bookingDate && (
                                <Box mt={0.5}>
                                  <Chip
                                    icon={
                                      <EventAvailableIcon
                                        style={{ fontSize: "12px" }}
                                      />
                                    }
                                    label={`Exit: ${formatDate(
                                      booking.exitpickupdate
                                    )}`}
                                    size="small"
                                    sx={{
                                      bgcolor: alpha("#FF9800", 0.1),
                                      color: "#E65100",
                                      fontWeight: "medium",
                                      height: "24px",
                                      fontSize: "0.75rem",
                                      "& .MuiChip-icon": {
                                        color: "#E65100",
                                      },
                                    }}
                                  />
                                </Box>
                              )}
                          </TableCell> */}
                          <TableCell sx={{ py: 1 }}>
                            <Chip
                              icon={
                                <AccessTimeIcon style={{ fontSize: "12px" }} />
                              }
                              label={
                                booking.entrytime || booking.exittime || "N/A"
                              }
                              size="small"
                              sx={{
                                bgcolor: alpha("#4CAF50", 0.1),
                                color: "#2E7D32",
                                fontWeight: "medium",
                                height: "24px",
                                fontSize: "0.75rem",
                                "& .MuiChip-icon": {
                                  color: "#2E7D32",
                                },
                              }}
                            />
                          </TableCell>
                          <TableCell align="center" sx={{ py: 1 }}>
                            <Chip
                              icon={<PeopleIcon style={{ fontSize: "12px" }} />}
                              label={booking.adults || 0}
                              size="small"
                              sx={{
                                fontWeight: "medium",
                                bgcolor: alpha("#1976d2", 0.1),
                                color: "#1976d2",
                                height: "24px",
                                fontSize: "0.75rem",
                                "& .MuiChip-icon": {
                                  color: "#1976d2",
                                },
                              }}
                            />
                          </TableCell>
                          <TableCell align="center" sx={{ py: 1 }}>
                            <Chip
                              icon={
                                <ChildCareIcon style={{ fontSize: "12px" }} />
                              }
                              label={booking.children || 0}
                              size="small"
                              sx={{
                                fontWeight: "medium",
                                bgcolor: alpha("#FF9800", 0.1),
                                color: "#E65100",
                                height: "24px",
                                fontSize: "0.75rem",
                                "& .MuiChip-icon": {
                                  color: "#E65100",
                                },
                              }}
                            />
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Tooltip title={booking.vehicles_name || "N/A"}>
                              <Box
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  gap: 0.5,
                                }}
                              >
                                <DirectionsCarIcon
                                  sx={{ color: "#5E35B1" }}
                                  fontSize="small"
                                />
                                <Typography
                                  variant="body2"
                                  fontWeight="500"
                                  sx={{
                                    maxWidth: "90px",
                                    overflow: "hidden",
                                    textOverflow: "ellipsis",
                                    whiteSpace: "nowrap",
                                  }}
                                >
                                  {booking.vehicles_name || "N/A"}
                                </Typography>
                              </Box>
                            </Tooltip>
                          </TableCell>
                          <TableCell>
                            {PriceHide === "0" ? (
                              <>
                                <Chip
                                  size="small"
                                  icon={
                                    <PriceCheckIcon
                                      style={{ fontSize: "12px" }}
                                    />
                                  }
                                  label={
                                    Math.ceil(
                                      Math.ceil(booking.totalPrice) +
                                        (Math.ceil(booking.totalPrice) *
                                          parseFloat(sgdTax)) /
                                          100
                                    ) || "N/A"
                                  }
                                  sx={{
                                    fontWeight: "bold",
                                    bgcolor: alpha("#673AB7", 0.1),
                                    color: "#5E35B1",
                                    height: "24px",
                                    fontSize: "0.75rem",
                                    "& .MuiChip-icon": {
                                      color: "#5E35B1",
                                    },
                                    "& .MuiChip-label": {
                                      px: 0.8,
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
                                      mt: 0,
                                      fontWeight: "medium",
                                      textAlign: "center",
                                      maxWidth: "90px",
                                      whiteSpace: "nowrap",
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
                            <Stack direction="row" spacing={0.5}>
                              <Button
                                variant="contained"
                                size="small"
                                startIcon={
                                  <VisibilityIcon
                                    style={{ fontSize: "16px" }}
                                  />
                                }
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
                                  borderRadius: 1.5,
                                  boxShadow: `0 2px 8px ${alpha(
                                    typeInfo.bg,
                                    0.3
                                  )}`,
                                  textTransform: "none",
                                  fontWeight: 500,
                                  px: 1,
                                  py: 0.5,
                                  minWidth: "65px",
                                  fontSize: "0.75rem",
                                  height: "28px",
                                  "& .MuiButton-startIcon": {
                                    marginRight: "4px",
                                  },
                                }}
                              >
                                View
                              </Button>

                              {/* <Button
                                variant="contained"
                                size="small"
                                startIcon={<CancelIcon style={{ fontSize: '16px' }} />}
                                onClick={() => {}}
                                sx={{
                                  background: "linear-gradient(135deg, #f44336 0%, #e57373 100%)",
                                  color: "white",
                                  "&:hover": {
                                    background: "#c62828",
                                    boxShadow: `0 4px 12px ${alpha('#f44336', 0.4)}`,
                                  },
                                  borderRadius: 1.5,
                                  boxShadow: `0 2px 8px ${alpha('#f44336', 0.3)}`,
                                  textTransform: 'none',
                                  fontWeight: 500,
                                  px: 1,
                                  py: 0.5,
                                  minWidth: '75px',
                                  fontSize: '0.75rem',
                                  height: '28px',
                                  '& .MuiButton-startIcon': {
                                    marginRight: '4px'
                                  }
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
                  <AirportShuttleIcon />
                </Avatar>
                <Typography
                  variant="h6"
                  sx={{ color: "#1976d2", fontWeight: 500 }}
                >
                  No airport travel bookings found
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ color: "#757575", maxWidth: 400 }}
                >
                  There are no airport travel bookings registered. Try selecting
                  a different date or check back later.
                </Typography>
              </Box>
            )}
          </Box>
        </Box>
      </Modal>

      {isViewModalOpen && selectedBooking && (
        <PickUpDropBookingModal
          open={isViewModalOpen}
          onClose={handleCloseViewModal}
          booking={selectedBooking}
        />
      )}
    </>
  );
}
