import React, { useState, useEffect } from "react";
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
  Button,
  Avatar,
  Stack,
  Tooltip,
  alpha,
  Chip,
  useTheme
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import VisibilityIcon from "@mui/icons-material/Visibility";
// import CancelIcon from "@mui/icons-material/Cancel";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import BookmarkIcon from "@mui/icons-material/Bookmark";
import PeopleIcon from "@mui/icons-material/People";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import ElderlyIcon from "@mui/icons-material/Elderly";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import AttractionsIcon from "@mui/icons-material/Attractions";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import PriceCheckIcon from "@mui/icons-material/PriceCheck";
import CheckCircleOutlineIcon from "@mui/icons-material/CheckCircleOutline";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import TimerIcon from "@mui/icons-material/Timer";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
// import dayjs from "dayjs";
import AttractionBookingModal from "../../dashboard/dashboard/db-dashboard/components/AttractionBookingModal";
import { useSelector, useDispatch } from "react-redux";
import { fetchAttractionDetails } from "../../../slice/attractions/attractionSlice";

export default function AttractionModal({
  open,
  onClose,
  bookings = [],
  date,
}) {
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isViewModalOpen, setIsViewModalOpen] = useState(false);
  const [enrichedBooking, setEnrichedBooking] = useState(null);
  const dispatch = useDispatch();
  const attractionDetails = useSelector((state) => state.attractions?.attractionDetails);
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  const theme = useTheme();
  // Get tax percentage from auth slice instead of attractions
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  useEffect(() => {
    // When a booking is selected, fetch its attraction details
    if (selectedBooking) {
      dispatch(fetchAttractionDetails({ 
        attractionId: selectedBooking.attractionId,
        price_mode: selectedBooking.mode || 'dmc',
        dmc_id: selectedBooking.dmc_id
      }));
    }
  }, [selectedBooking?.attractionId, dispatch]);

  const handleView = (booking) => {
    // Determine if this is a package booking
    const isPackageBooking = booking.package_type === 1 || booking.bookingType === 'package';
    
    // Ensure package_details are preserved or constructed if missing
    let packageDetails = booking.package_details;
    if (isPackageBooking && !packageDetails) {
      // Try to construct package details from available data
      packageDetails = {
        package_name: booking.ticketName || booking.AttractionName || 'Package',
        package_attractions: booking.packageAttractions || [],
        package_description: booking.packageDescription || '',
        package_total_attractions: booking.packageAttractions?.length || 0
      };
    }
    
    // Combine booking data with service details
    const enrichedBooking = {
      ...booking,
      // Set bookingType to 'package' if it's a package booking, otherwise keep existing or default to 'attraction'
      bookingType: isPackageBooking ? 'package' : (booking.bookingType || 'attraction'),
      // Ensure package_details are included if it's a package booking
      ...(isPackageBooking && packageDetails && { package_details: packageDetails }),
      service_details: {
        master_image: attractionDetails?.master_image,
        location: attractionDetails?.location,
        country: attractionDetails?.country,
        description: attractionDetails?.description
      }
    };

    // Debug logging to see what data is being passed
    console.log('AttractionModal - Original booking:', booking);
    console.log('AttractionModal - Is package booking:', isPackageBooking);
    console.log('AttractionModal - Package details:', packageDetails);
    console.log('AttractionModal - Enriched booking:', enrichedBooking);

    setSelectedBooking(booking);
    setEnrichedBooking(enrichedBooking);
    setIsViewModalOpen(true);
  };

  const handleCloseViewModal = () => {
    setIsViewModalOpen(false);
    setSelectedBooking(null);
    setEnrichedBooking(null);
  };

  // Helper function to capitalize first letter of each word
  const capitalizeWords = (str) => {
    if (!str) return "";
    return str.replace(/\b\w/g, (char) => char.toUpperCase());
  };

  // Format date for display
  const formatDate = (inputDate) => {
    if (!inputDate) return "N/A";
    
    try {
      // Convert any date format to "Sun, 20 Apr'25"
      const dateObj = new Date(inputDate);
      if (!isNaN(dateObj.getTime())) {
        const day = dateObj.getDate();
        const month = dateObj.toLocaleString('en-US', { month: 'short' });
        const year = dateObj.getFullYear().toString().slice(2);
        const weekday = dateObj.toLocaleString('en-US', { weekday: 'short' });
        return `${weekday}, ${day} ${month}'${year}`;
      }
    } catch (error) {
      console.error("Error formatting date:", error);
    }
    
    return inputDate;
  };

  // Function to get booking type info (for styling)
  const getBookingTypeInfo = (type) => {
    switch(type?.toLowerCase()) {
      case 'booking':
        return { 
          bg: '#1E8E3E', 
          color: 'white',
          lightBg: '#E6F4EA',
          icon: <CheckCircleOutlineIcon fontSize="small" />
        };
      case 'enquiry':
        return { 
          bg: '#F9AB00', 
          color: 'white',
          lightBg: '#FEF7E0',
          icon: <HelpOutlineIcon fontSize="small" />
        };
      case 'pending':
        return { 
          bg: '#1A73E8', 
          color: 'white',
          lightBg: '#E8F0FE',
          icon: <TimerIcon fontSize="small" />
        };
      case 'cancelled':
        return { 
          bg: '#D93025', 
          color: 'white',
          lightBg: '#FCE8E6',
          icon: <CancelOutlinedIcon fontSize="small" />
        };
      default:
        return { 
          bg: '#5F6368', 
          color: 'white',
          lightBg: '#F1F3F4',
          icon: <BookmarkIcon fontSize="small" />
        };
    }
  };

  // Filter bookings based on the selected date
  const filteredBookings = Array.isArray(bookings)
    ? bookings.filter((booking) => booking.bookingDate === date)
    : [];
     console.log('filteredBookings',filteredBookings);
    

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
            maxWidth: "1600px",
            maxHeight: "90vh",
            overflowY: "auto",
            bgcolor: "background.paper",
            borderRadius: 2,
            boxShadow: "0 8px 32px rgba(0, 0, 0, 0.15)",
            p: 0,
            '&::-webkit-scrollbar': {
              width: '6px',
            },
            '&::-webkit-scrollbar-track': {
              background: '#f1f1f1',
            },
            '&::-webkit-scrollbar-thumb': {
              background: '#bdbdbd',
              borderRadius: '10px',
              '&:hover': {
                background: '#a5a5a5',
              },
            },
          }}
        >
          {/* Compact header with title and date in one line */}
          <Box sx={{
            background: "linear-gradient(90deg, #1976d2 0%, #0D47A1 100%)",
            color: "white",
            py: 1.5,
            px: 2.5,
            borderTopLeftRadius: 8,
            borderTopRightRadius: 8,
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center"
          }}>
            <Stack direction="row" spacing={3} alignItems="center">
              <Stack direction="row" spacing={1} alignItems="center">
                <Avatar sx={{ 
                  bgcolor: 'white', 
                  color: '#1976d2', 
                  width: 36, 
                  height: 36
                }}>
                  <AttractionsIcon fontSize="small" />
                </Avatar>
                <Typography
                  variant="h6"
                  fontWeight="600"
                  fontSize="1.25rem"
                >
                  Attraction Bookings
                </Typography>
              </Stack>
              
              <Stack direction="row" spacing={1} alignItems="center" sx={{ 
                bgcolor: alpha('#fff', 0.15), 
                borderRadius: 1.5, 
                px: 1.5, 
                py: 0.75 
              }}>
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
                bgcolor: 'rgba(255,255,255,0.1)',
                "&:hover": { bgcolor: "rgba(255, 255, 255, 0.2)" },
              }}
            >
              <CloseIcon fontSize="small" />
            </IconButton>
          </Box>
          
          <Box sx={{ p: 1.5 }}>
            {filteredBookings.length > 0 ? (
              <Paper sx={{ 
                width: "100%", 
                overflow: "hidden", 
                boxShadow: "0 2px 10px rgba(0, 0, 0, 0.05)",
                borderRadius: 2
              }}>
                <Table size="small" sx={{ minWidth: 900 }}>
                  <TableHead>
                    <TableRow sx={{ 
                      background: "linear-gradient(90deg, #1E88E5 0%, #42A5F5 100%)" 
                    }}>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '12%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <AttractionsIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Attraction</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '8%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <BookmarkIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Booking Type</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '7%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <AccessTimeIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Time</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '6%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <PeopleIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Adults</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '6%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <ChildCareIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Children</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '6%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <ElderlyIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Seniors</Typography>
                        </Box>
                      </TableCell>
                      {/* <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '15%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <DirectionsCarIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Selection</Typography>
                        </Box>
                      </TableCell> */}
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '12%' }}>
                        <Typography variant="body1" fontWeight="bold" color="white">Mode</Typography>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '10%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <PriceCheckIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Price</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '8%' }}>
                        <Typography variant="body1" fontWeight="bold" color="white">Actions</Typography>
                      </TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {filteredBookings.map((booking, index) => {
                      // Default to 'booking' type for attraction bookings
                      const bookingType = booking.bookingType || "booking";
                      const typeInfo = getBookingTypeInfo(bookingType);
                      
                      return (
                        <TableRow
                          key={index}
                          sx={{
                            backgroundColor: index % 2 === 0 ? "#fafafa" : "#ffffff",
                            "&:hover": { backgroundColor: alpha(typeInfo.lightBg, 0.5) },
                            transition: "background-color 0.3s ease"
                          }}
                        >
                          <TableCell sx={{ py: 1 }}>
                            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                              <Avatar 
                                sx={{ 
                                  width: 28, 
                                  height: 28, 
                                  bgcolor: booking.package_type === 1 ? alpha('#9c27b0', 0.1) : alpha('#1976d2', 0.1),
                                  color: booking.package_type === 1 ? '#9c27b0' : '#1976d2',
                                  fontSize: '14px',
                                  fontWeight: 'bold'
                                }}
                              >
                                {booking.package_type === 1 
                                  ? (capitalizeWords(booking.package_details?.package_name)?.charAt(0) || "P")
                                  : (booking.AttractionName?.charAt(0) || "A")
                                }
                              </Avatar>
                              <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                                <Tooltip title={booking.package_type === 1 
                                  ? capitalizeWords(booking.package_details?.package_name) || "N/A"
                                  : (booking.AttractionName || "N/A")
                                }>
                                  <Typography variant="body2" fontWeight="500" noWrap sx={{ maxWidth: 160 }}>
                                    {booking.package_type === 1 
                                      ? capitalizeWords(booking.package_details?.package_name) || "N/A"
                                      : (booking.AttractionName || "N/A")
                                    }
                                  </Typography>
                                </Tooltip>
                                {booking.package_type === 1 && (
                                  <Chip
                                    label="Package"
                                    size="small"
                                    sx={{
                                      bgcolor: alpha('#9c27b0', 0.1),
                                      color: '#9c27b0',
                                      fontWeight: 'bold',
                                      height: '18px',
                                      fontSize: '0.65rem',
                                      width: 'fit-content',
                                      '& .MuiChip-label': {
                                        px: 0.8
                                      }
                                    }}
                                  />
                                )}
                              </Box>
                            </Box>
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Chip 
                              icon={typeInfo.icon}
                              label={bookingType.charAt(0).toUpperCase() + bookingType.slice(1).toLowerCase()}
                              size="small"
                              sx={{ 
                                bgcolor: typeInfo.bg, 
                                color: typeInfo.color,
                                fontWeight: "medium",
                                height: '24px',
                                fontSize: '0.75rem',
                                '& .MuiChip-icon': {
                                  color: 'inherit',
                                  fontSize: '14px'
                                },
                                '& .MuiChip-label': {
                                  px: 1
                                }
                              }}
                            />
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Chip
                              icon={<AccessTimeIcon style={{ fontSize: '12px' }} />}
                              label={booking.visitTime || "N/A"}
                              size="small"
                              sx={{ 
                                bgcolor: alpha('#4CAF50', 0.1),
                                color: '#2E7D32',
                                fontWeight: "medium",
                                height: '24px',
                                fontSize: '0.75rem',
                                '& .MuiChip-icon': {
                                  color: '#2E7D32'
                                }
                              }}
                            />
                          </TableCell>
                          <TableCell align="center" sx={{ py: 1 }}>
                            <Chip 
                              icon={<PeopleIcon style={{ fontSize: '12px' }} />}
                              label={booking.adultCount || 0}
                              size="small"
                              sx={{ 
                                fontWeight: "medium",
                                bgcolor: alpha('#1976d2', 0.1),
                                color: '#1976d2',
                                height: '24px',
                                fontSize: '0.75rem',
                                '& .MuiChip-icon': {
                                  color: '#1976d2'
                                }
                              }}
                            />
                          </TableCell>
                          <TableCell align="center" sx={{ py: 1 }}>
                            <Chip 
                              icon={<ChildCareIcon style={{ fontSize: '12px' }} />}
                              label={booking.childCount || 0}
                              size="small"
                              sx={{ 
                                fontWeight: "medium",
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
                          <TableCell sx={{ py: 1 }}>
                            <Chip
                              icon={<ElderlyIcon style={{ fontSize: '12px' }} />}
                              label={booking.seniorCount || 0}
                              size="small"
                              sx={{ 
                                fontWeight: "medium",
                                bgcolor: alpha('#4CAF50', 0.1),
                                color: '#2E7D32',
                                height: '24px',
                                fontSize: '0.75rem',
                                '& .MuiChip-icon': {
                                  color: '#2E7D32'
                                }
                              }}
                            />
                          </TableCell>
                          {/* <TableCell sx={{ py: 1 }}>
                            <Chip
                              size="small"
                              label={
                                booking.Selection === "withoutTraveller" || booking.Selection === "withoutTransport" || !booking.Selection
                                  ? `Only Attraction`
                                  : booking.Selection === "withPrivate"
                                  ? `Attraction With Transport (Private)`
                                  : booking.Selection === "withShare"
                                  ? `Attraction With Transport (Share)`
                                  : "Only Attraction"
                              }
                              sx={{
                                fontWeight: "medium",
                                bgcolor: alpha('#673AB7', 0.1),
                                color: '#5E35B1',
                                height: '24px',
                                fontSize: '0.75rem',
                                '& .MuiChip-label': {
                                  px: 0.8
                                }
                              }}
                            />
                          </TableCell> */}
                          <TableCell sx={{ py: 1 }}>
                            {booking.mode === "travclicks" ? (
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
                            ) : booking.mode === "dmc" ? (
                              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.8 }}>
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
                                      bgcolor: alpha('#FF9800', 0.2),
                                      color: '#E65100',
                                      fontSize: '12px'
                                    }}
                                  >
                                    {DmcName?.charAt(0) || "D"}
                                  </Avatar>
                                )}
                                <Typography variant="body2" fontWeight="medium" color="#E65100" noWrap sx={{ maxWidth: 100 }}>
                                  {DmcName || "DMC"}
                                </Typography>
                              </Box>
                            ) : (
                              <Typography variant="body2" fontWeight="medium">
                                {booking.mode?.charAt(0).toUpperCase() +
                                booking.mode?.slice(1).toLowerCase()}
                              </Typography>
                            )}
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Chip
                              size="small"
                              icon={<PriceCheckIcon style={{ fontSize: '12px' }} />}
                              label={(() => {
                                if (PriceHide !== "0") {
                                  return "Price Hidden";
                                }
                                // Calculate price with tax using sgdTax from auth slice
                                const basePrice = booking.totalPrice || 0;
                                const sgdPrice = Math.ceil(basePrice);
                                const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
                                const sgdGrandTotal = sgdPrice + sgdTaxAmount;
                                
                                return sgdTax > 0 
                                  ? `SGD ${sgdGrandTotal}`
                                  : `SGD ${sgdPrice}`;
                              })()}
                              sx={{
                                fontWeight: "bold",
                                bgcolor: alpha('#673AB7', 0.1),
                                color: '#5E35B1',
                                height: 'auto',
                                minHeight: '24px',
                                width: '100%',
                                maxWidth: '100px',
                                fontSize: '0.75rem',
                                py: 0.5,
                                position: 'relative',
                                '& .MuiChip-icon': {
                                  color: '#5E35B1',
                                  marginLeft: '5px'
                                },
                                '& .MuiChip-label': {
                                  px: 0.8,
                                  whiteSpace: 'nowrap',
                                  overflow: 'hidden',
                                  textOverflow: 'ellipsis'
                                }
                              }}
                            />
                            {PriceHide === "0" && sgdTax > 0 && (
                              <Typography 
                                variant="caption" 
                                display="block" 
                                sx={{ 
                                  color: '#5E35B1', 
                                  fontSize: '0.7rem',
                                  mt: 0.5,
                                  fontWeight: 'medium'
                                }}
                              >
                                (incl. {sgdTax}% tax)
                              </Typography>
                            )}
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Button
                              variant="contained"
                              size="small"
                              startIcon={<VisibilityIcon style={{ fontSize: '16px' }} />}
                              onClick={() => handleView(booking)}
                              sx={{
                                background: `linear-gradient(135deg, ${typeInfo.bg} 0%, ${alpha(typeInfo.bg, 0.8)} 100%)`,
                                color: 'white',
                                "&:hover": {
                                  background: typeInfo.bg,
                                  boxShadow: `0 4px 12px ${alpha(typeInfo.bg, 0.4)}`,
                                },
                                borderRadius: 1.5,
                                boxShadow: `0 2px 8px ${alpha(typeInfo.bg, 0.3)}`,
                                textTransform: 'none',
                                fontWeight: 500,
                                px: 1,
                                py: 0.5,
                                minWidth: '65px',
                                fontSize: '0.75rem',
                                height: '28px',
                                '& .MuiButton-startIcon': {
                                  marginRight: '4px'
                                }
                              }}
                            >
                              View
                            </Button>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </Paper>
            ) : (
              <Box sx={{ 
                textAlign: "center",
                py: 4,
                bgcolor: alpha('#1976d2', 0.04),
                borderRadius: 2,
                border: `1px dashed ${alpha('#1976d2', 0.2)}`,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                gap: 1.5
              }}>
                <Avatar 
                  sx={{ 
                    bgcolor: alpha('#1976d2', 0.1),
                    color: '#1976d2',
                    width: 48,
                    height: 48
                  }}
                >
                  <AttractionsIcon />
                </Avatar>
                <Typography
                  variant="h6"
                  sx={{ color: '#1976d2', fontWeight: 500 }}
                >
                  No attraction bookings found
                </Typography>
                <Typography variant="body1" sx={{ color: '#757575', maxWidth: 400 }}>
                  There are no attraction bookings registered for this date. Try selecting a different date or check back later.
                </Typography>
              </Box>
            )}
          </Box>
        </Box>
      </Modal>

      {isViewModalOpen && enrichedBooking && (
        <AttractionBookingModal
          open={isViewModalOpen}
          onClose={handleCloseViewModal}
          booking={enrichedBooking}
        />
      )}
    </>
  );
}
