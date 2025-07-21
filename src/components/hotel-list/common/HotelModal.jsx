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
  Divider,
  Avatar,
  useTheme,
  alpha,
  Stack,
  Tooltip,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import VisibilityIcon from "@mui/icons-material/Visibility";
import HotelIcon from "@mui/icons-material/Hotel";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import BookmarkIcon from "@mui/icons-material/Bookmark";
import RoomServiceIcon from "@mui/icons-material/RoomService";
import BedIcon from "@mui/icons-material/Bed";
import PersonIcon from "@mui/icons-material/Person";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import PriceCheckIcon from "@mui/icons-material/PriceCheck";
import EventIcon from "@mui/icons-material/Event";
import CheckCircleOutlineIcon from "@mui/icons-material/CheckCircleOutline";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import TimerIcon from "@mui/icons-material/Timer";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import { useSelector } from "react-redux";
import HotelBookingModal from "@/components/dashboard/dashboard/db-dashboard/components/HotelBookingModal";

export default function HotelModal({ open, onClose, bookings = [], date }) {
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isViewModalOpen, setIsViewModalOpen] = useState(false);
  const theme = useTheme();

  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const dmcName = useSelector((state) => state.auth.DmcName) || 'DMC';
  const DmcLogo = useSelector((state) => state.auth.DmcLogo);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  
  // Get tax percentages from auth slice
  const currentTax = useSelector((state) => state.auth.currentTax || 0);
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const usdTax = useSelector((state) => state.auth.usdTax || 0);

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

  // Function to format price
  const formatPrice = (price) => {
    if (typeof price !== 'number') return '0.00';
    return price.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  };

  // Check if a date falls within a booking's date range
  const isDateInBookingRange = (booking, targetDate) => {
    if (!booking.bookingDate || !Array.isArray(booking.bookingDate) || booking.bookingDate.length < 2) {
      return false;
    }
    
    // Convert all dates to YYYY-MM-DD format for comparison
    const standardizeDate = (dateStr) => {
      if (dateStr.includes("/")) {
        const [day, month, year] = dateStr.split("/");
        return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
      }
      return dateStr; // Already in YYYY-MM-DD format
    };
    
    const startDate = standardizeDate(booking.bookingDate[0]);
    const endDate = standardizeDate(booking.bookingDate[1]);
    const checkDate = targetDate; // Already in YYYY-MM-DD format
    
    return checkDate >= startDate && checkDate <= endDate;
  };

  // Filter bookings by date range
  const filteredBookings = Array.isArray(bookings) 
    ? bookings.filter(booking => isDateInBookingRange(booking, date))
    : [];

  // Extract meal information from the booking
  const getMealInfo = (booking) => {
    if (!booking.rooms || !booking.rooms.length) return "N/A";
    
    const allMeals = [];
    booking.rooms.forEach(room => {
      if (room.beds && Array.isArray(room.beds)) {
        room.beds.forEach(bed => {
          if (bed.mealTypes && bed.mealTypes.length) {
            allMeals.push(...bed.mealTypes);
          }
        });
      }
    });
    
    // Now return a list with line breaks (will display as blocks)
    return allMeals.length > 0 ? allMeals.join("\n") : "N/A";
  };

  // Get total persons count
  const getPersonCount = (booking) => {
    if (!booking.rooms || !booking.rooms.length) return 0;
    
    let totalCount = 0;
    booking.rooms.forEach(room => {
      if (room.beds && Array.isArray(room.beds)) {
        room.beds.forEach(bed => {
          totalCount += bed.head_count || 0;
        });
      }
    });
    
    return totalCount;
  };

  // Function to get booking type color and icon
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

  const handleView = (booking) => {
    setSelectedBooking(booking);
    setIsViewModalOpen(true);
  };

  const handleCloseViewModal = () => {
    setIsViewModalOpen(false);
    setSelectedBooking(null);
  };

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
                  <HotelIcon fontSize="small" />
                </Avatar>
                <Typography
                  variant="h6"
                  fontWeight="600"
                  fontSize="1.25rem"
                >
                  Hotel Bookings
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
          
          <Box sx={{ p: 2 }}>
          {filteredBookings.length > 0 ? (
              <Paper sx={{ 
                width: "100%", 
                overflow: "hidden", 
                boxShadow: "0 2px 10px rgba(0, 0, 0, 0.05)",
                borderRadius: 2,
              }}>
                <Table size="medium" sx={{ minWidth: 900 }}>
                <TableHead>
                    <TableRow sx={{ 
                      background: "linear-gradient(90deg, #1E88E5 0%, #42A5F5 100%)" 
                    }}>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <HotelIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Hotel</Typography>
                        </Box>
                    </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <BookmarkIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Booking Type</Typography>
                        </Box>
                    </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <RoomServiceIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Room</Typography>
                        </Box>
                    </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <BedIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Bed Type</Typography>
                        </Box>
                    </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <RestaurantIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Meals</Typography>
                        </Box>
                    </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <PersonIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Guests</Typography>
                        </Box>
                    </TableCell>
                      {/* <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <EventIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Check In/Out</Typography>
                        </Box>
                      </TableCell> */}
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <PriceCheckIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Price</Typography>
                        </Box>
                    </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Typography variant="body1" fontWeight="bold" color="white">Mode</Typography>
                    </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.8, whiteSpace: 'nowrap' }}>
                        <Typography variant="body1" fontWeight="bold" color="white">Actions</Typography>
                    </TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {filteredBookings.map((booking, index) => {
                    // Extract room and bed information
                      const rooms = booking.rooms || [];
                      const roomTypes = rooms.map(room => room.room_type).filter(Boolean);
                      const bedTypes = rooms.flatMap(room => 
                        (room.beds || []).map(bed => bed.bed_type)
                      ).filter(Boolean);
                      
                    const basePrice = parseFloat(booking.totalPrice) || 0;
                    const convertedPrice = basePrice * exchangeRate; // Convert to selected currency

                      // Get booking type and color
                      const bookingType = booking.bookingType || "N/A";
                      const typeInfo = getBookingTypeInfo(bookingType);

                      // Extract meal information for display as blocks
                      const mealInfo = getMealInfo(booking);
                      const mealItems = mealInfo.split("\n");

                    return (
                      <TableRow
                        key={index}
                        sx={{
                            backgroundColor: index % 2 === 0 ? "#fafafa" : "#ffffff",
                            "&:hover": { backgroundColor: alpha(typeInfo.lightBg, 0.5) },
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
                              <Tooltip title={booking.hotelDetails?.hotel_name || "N/A"}>
                                <Typography variant="body1" fontWeight="500" noWrap sx={{ maxWidth: 150 }}>
                                  {booking.hotelDetails?.hotel_name || "N/A"}
                                </Typography>
                              </Tooltip>
                            </Box>
                          </TableCell>
                          <TableCell>
                            <Chip 
                              icon={typeInfo.icon}
                              label={bookingType.charAt(0).toUpperCase() + bookingType.slice(1).toLowerCase()}
                              size="medium"
                              sx={{ 
                                bgcolor: typeInfo.bg, 
                                color: typeInfo.color,
                                fontWeight: "medium",
                                height: '28px',
                                fontSize: '0.9rem',
                                '& .MuiChip-icon': {
                                  color: 'inherit',
                                  fontSize: '18px'
                                },
                                '& .MuiChip-label': {
                                  px: 1
                                }
                              }}
                            />
                          </TableCell>
                          <TableCell>
                            <Stack direction="column" spacing={0.5}>
                              {roomTypes.map((roomType, idx) => (
                                <Typography 
                                  key={idx} 
                                  variant="body1" 
                                  sx={{ 
                                    color: '#424242',
                                    display: 'block',
                                    fontSize: '0.9rem'
                                  }}
                                >
                                  {roomType}{idx < roomTypes.length - 1 ? ',' : ''}
                                </Typography>
                              ))}
                            </Stack>
                          </TableCell>
                          <TableCell>
                            <Stack direction="column" spacing={0.5}>
                              {bedTypes.map((bedType, idx) => (
                                <Typography 
                                  key={idx} 
                                  variant="body1" 
                                  sx={{ 
                                    color: '#424242',
                                    display: 'block',
                                    fontSize: '0.9rem'
                                  }}
                                >
                                  {bedType}{idx < bedTypes.length - 1 ? ',' : ''}
                                </Typography>
                              ))}
                            </Stack>
                          </TableCell>
                          <TableCell>
                            <Stack direction="column" spacing={0.5}>
                              {mealItems.map((meal, idx) => (
                                <Typography 
                                  key={idx} 
                                  variant="body1" 
                                  sx={{ 
                                    color: '#424242',
                                    display: 'block',
                                    fontSize: '0.9rem'
                                  }}
                                >
                                  {meal}{idx < mealItems.length - 1 ? ',' : ''}
                                </Typography>
                              ))}
                            </Stack>
                          </TableCell>
                          <TableCell align="center">
                            <Chip 
                              icon={<PersonIcon style={{ fontSize: '16px' }} />}
                              label={getPersonCount(booking)}
                              size="medium"
                              sx={{ 
                                fontWeight: "medium",
                                bgcolor: alpha('#1976d2', 0.1),
                                color: '#1976d2',
                                height: '28px',
                                fontSize: '0.9rem',
                                '& .MuiChip-icon': {
                                  color: '#1976d2'
                                }
                              }}
                            />
                          </TableCell>
                          {/* <TableCell>
                            <Stack direction="row" spacing={0.5} alignItems="center">
                              <Chip
                                size="medium"
                                label={formatDate(booking.bookingDate[0] || "N/A")}
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
                              <Typography variant="body2" fontWeight="bold">→</Typography>
                              <Chip
                                size="medium"
                                label={formatDate(booking.bookingDate[1] || "N/A")}
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
                            </Stack>
                          </TableCell> */}
                          {PriceHide === "0" ?
                          (
                        <TableCell>
                            <Chip
                              size="medium"
                              icon={<PriceCheckIcon style={{ fontSize: '16px' }} />}
                              label={(() => {
                                // Calculate price with tax using sgdTax from auth slice
                                const basePrice = parseFloat(booking.totalPrice) || 0;
                                const sgdPrice = Math.ceil(basePrice);
                                const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
                                const sgdGrandTotal = sgdPrice + sgdTaxAmount;
                                
                                return sgdTax > 0 
                                  ? `SGD ${formatPrice(sgdGrandTotal)}`
                                  : `SGD ${formatPrice(sgdPrice)}`;
                              })()}
                              sx={{
                                fontWeight: "bold",
                                bgcolor: alpha('#673AB7', 0.1),
                                color: '#5E35B1',
                                height: '28px',
                                fontSize: '0.9rem',
                                '& .MuiChip-icon': {
                                  color: '#5E35B1'
                                },
                                '& .MuiChip-label': {
                                  px: 0.8
                                }
                              }}
                            />
                            {sgdTax > 0 && (
                              <Typography 
                                variant="caption" 
                                display="block" 
                                sx={{ 
                                  color: '#5E35B1', 
                                  fontSize: '0.75rem',
                                  mt: 0.5,
                                  fontWeight: 'medium'
                                }}
                              >
                                (incl. {sgdTax}% tax)
                              </Typography>
                            )}
                        </TableCell>
                        ):(
                          <TableCell>
                            <Typography variant="body1" fontWeight="medium">
                              Price available on request
                            </Typography>
                          </TableCell>
                        )}
                        <TableCell>
                          {booking.priceMode === "travclicks" ? (
                              <Chip 
                                label="Marketplace" 
                                size="medium"
                                sx={{
                                  bgcolor: alpha('#009688', 0.1),
                                  color: '#00796B',
                                  fontWeight: 'medium',
                                  height: '28px',
                                  fontSize: '0.9rem',
                                  border: `1px solid ${alpha('#009688', 0.3)}`,
                                  '& .MuiChip-label': {
                                    px: 1
                                  }
                                }}
                              />
                          ) : booking.priceMode === "dmc" ? (
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
                                    {dmcName?.charAt(0) || "D"}
                                  </Avatar>
                                )}
                                <Typography variant="body1" fontWeight="medium" color="#E65100">
                                  {dmcName || "DMC"}
                                </Typography>
                              </Box>
                            ) : (
                              <Typography variant="body1" fontWeight="medium">
                                {booking.priceMode && typeof booking.priceMode === 'string' ? 
                                  booking.priceMode.charAt(0).toUpperCase() +
                                  booking.priceMode.slice(1).toLowerCase() : 
                                  "Mode not available"}
                              </Typography>
                          )}
                        </TableCell>
                        <TableCell>
                            <Button
                              variant="contained"
                              size="medium"
                              startIcon={<VisibilityIcon />}
                              onClick={() => handleView(booking)}
                              sx={{
                                background: `linear-gradient(135deg, ${typeInfo.bg} 0%, ${alpha(typeInfo.bg, 0.8)} 100%)`,
                                color: 'white',
                                "&:hover": {
                                  background: typeInfo.bg,
                                  boxShadow: `0 4px 12px ${alpha(typeInfo.bg, 0.4)}`,
                                },
                                borderRadius: 2,
                                boxShadow: `0 2px 8px ${alpha(typeInfo.bg, 0.3)}`,
                                textTransform: 'none',
                                fontWeight: 500,
                                px: 2,
                                py: 0.8,
                                fontSize: '0.9rem'
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
                  <HotelIcon />
                </Avatar>
            <Typography
                  variant="h6"
                  sx={{ color: '#1976d2', fontWeight: 500 }}
            >
                  No hotel bookings found for {formatDate(date)}
                </Typography>
                <Typography variant="body1" sx={{ color: '#757575', maxWidth: 400 }}>
                  There are no bookings registered for this date. Try selecting a different date or check back later.
            </Typography>
              </Box>
          )}
          </Box>
        </Box>
      </Modal>

      {isViewModalOpen && selectedBooking && (
        <HotelBookingModal
          open={isViewModalOpen}
          onClose={handleCloseViewModal}
          booking={selectedBooking}
         />
      )}
    </>
  );
}
