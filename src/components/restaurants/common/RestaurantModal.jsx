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
  Tooltip,
  Avatar,
  Stack,
  alpha,
  Chip,
  useTheme
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import VisibilityIcon from "@mui/icons-material/Visibility";
import CancelIcon from "@mui/icons-material/Cancel";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import BookmarkIcon from "@mui/icons-material/Bookmark";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import PeopleIcon from "@mui/icons-material/People";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import DrinkIcon from "@mui/icons-material/LocalDrink";
import NoDrinksIcon from "@mui/icons-material/NoDrinks";
import MenuBookIcon from "@mui/icons-material/MenuBook";
import CircleIcon from "@mui/icons-material/Circle";
import SquareIcon from "@mui/icons-material/Square";
import PriceCheckIcon from "@mui/icons-material/PriceCheck";
import CheckCircleOutlineIcon from "@mui/icons-material/CheckCircleOutline";
import HelpOutlineIcon from "@mui/icons-material/HelpOutline";
import TimerIcon from "@mui/icons-material/Timer";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import dayjs from "dayjs";
import RestaurantBookingModal from "../../dashboard/dashboard/db-dashboard/components/RestaurantBookingModal";
import { useSelector, useDispatch } from "react-redux";
import { fetchRestaurantsDetails } from "../../../slice/restaurant/RestaurantsSlice";

export default function RestaurantModal({
  open,
  onClose,
  bookings = [],
  date,
}) {
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isViewModalOpen, setIsViewModalOpen] = useState(false);
  const [currentRestaurantDetails, setCurrentRestaurantDetails] = useState(null);
  const dispatch = useDispatch();
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  const theme = useTheme();
  // Get tax percentage from auth slice instead of restaurants
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // Get restaurants list from Redux as fallback
  const restaurants = useSelector((state) => state.restaurants.restaurants);

  const handleView = async (booking) => {
    try {
      console.log("Original booking data:", booking);
      
      // First, try to get restaurant details from the restaurants list
      let restaurantDetails = null;
      if (booking.restaurantId && restaurants) {
        restaurantDetails = restaurants.find(r => r.id === booking.restaurantId);
        console.log("Found restaurant details from Redux:", restaurantDetails);
      }
      
      // Create enriched booking with available restaurant details
      const enrichedBooking = {
        ...booking,
        service_details: {
          master_image: restaurantDetails?.master_image || restaurantDetails?.image || null,
          cuisine: restaurantDetails?.cuisine || restaurantDetails?.cuisine_type || null,
          city: restaurantDetails?.city || null,
          country: restaurantDetails?.country || null
        },
        // Ensure MealDescription is properly structured
        MealDescription: booking.MealDescription || []
      };

      console.log("Enriched booking with fallback data:", enrichedBooking);

      // Then fetch restaurant details specifically for this booking to get the most up-to-date info
      if (booking.restaurantId) {
        const result = await dispatch(fetchRestaurantsDetails({ 
          restaurantId: booking.restaurantId,
          price_mode: booking.priceTypes?.[0] || 'dmc'
        }));

        console.log("API result for restaurant details:", result);

        // Update the booking with the fetched restaurant details
        if (result.payload) {
          const updatedBooking = {
            ...enrichedBooking,
            service_details: {
              master_image: result.payload.master_image || enrichedBooking.service_details.master_image,
              cuisine: result.payload.cuisine || enrichedBooking.service_details.cuisine,
              city: result.payload.city || enrichedBooking.service_details.city,
              country: result.payload.country || enrichedBooking.service_details.country
            }
          };
          console.log("Final booking data with API details:", updatedBooking);
          setSelectedBooking(updatedBooking);
          setIsViewModalOpen(true);
        } else {
          // If no restaurant details found from API, use the fallback data
          console.log("No API data found, using fallback data");
          setSelectedBooking(enrichedBooking);
          setIsViewModalOpen(true);
        }
      } else {
        // If no restaurantId, show the booking without restaurant details
        console.log("No restaurantId found, showing booking without restaurant details");
        setSelectedBooking(enrichedBooking);
        setIsViewModalOpen(true);
      }
    } catch (error) {
      console.error("Error fetching restaurant details:", error);
      // If there's an error, still show the booking without restaurant details
      const enrichedBooking = {
        ...booking,
        service_details: {
          master_image: null,
          cuisine: null,
          city: null,
          country: null
        },
        MealDescription: booking.MealDescription || []
      };
      setSelectedBooking(enrichedBooking);
      setIsViewModalOpen(true);
    }
  };

  const handleCloseViewModal = () => {
    setIsViewModalOpen(false);
    setSelectedBooking(null);
    setCurrentRestaurantDetails(null);
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

  const filteredBookings = Array.isArray(bookings)
    ? bookings.filter((booking) => booking.bookingDate === date)
    : [];
    
  const capitalizeFirstLetter = (str) => {
    return typeof str === "string" && str.length > 0
      ? str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
      : "N/A";
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
            width: "98%",
            maxWidth: "1400px",
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
                  <RestaurantIcon fontSize="small" />
                </Avatar>
                <Typography
                  variant="h6"
                  fontWeight="600"
                  fontSize="1.25rem"
                >
                  Restaurant Bookings
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
                <Table 
                  size="small" 
                  sx={{ 
                    minWidth: 900,
                  }}
                >
                  <TableHead>
                    <TableRow sx={{ 
                      background: "linear-gradient(90deg, #1E88E5 0%, #42A5F5 100%)" 
                    }}>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '14%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <RestaurantIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Restaurant</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '10%' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <BookmarkIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Booking Type</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '100px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <AccessTimeIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Time</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '90px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <PeopleIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Adults</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '90px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <ChildCareIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Children</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '120px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <MenuBookIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Meal</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '120px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <MenuBookIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Meal Type</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '120px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <Typography variant="body1" fontWeight="bold" color="white">Mode</Typography>
                        </Box>
                      </TableCell>
                      {/* <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '120px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <DirectionsCarIcon fontSize="small" />
                          <Typography variant="body1" fontWeight="bold" color="white">Transport</Typography>
                        </Box>
                      </TableCell> */}
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '120px' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                          <Typography variant="body1" fontWeight="bold" color="white">Price</Typography>
                        </Box>
                      </TableCell>
                      <TableCell sx={{ color: "white", fontWeight: "bold", py: 1.5, whiteSpace: 'nowrap', width: '100px' }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                        <Typography variant="body1" fontWeight="bold" color="white">Actions</Typography>
                        </Box>
                      </TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {filteredBookings.map((booking, index) => {
                      // Default to 'booking' type for restaurant bookings
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
                                  bgcolor: alpha('#1976d2', 0.1),
                                  color: '#1976d2',
                                  fontSize: '14px',
                                  fontWeight: 'bold'
                                }}
                              >
                                {booking.restaurantName?.charAt(0) || "R"}
                              </Avatar>
                              <Tooltip title={booking.restaurantName || "N/A"}>
                                <Typography variant="body2" fontWeight="500" noWrap sx={{ maxWidth: 150 }}>
                                  {booking.restaurantName || "N/A"}
                                </Typography>
                              </Tooltip>
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
                              size="small"
                              label={capitalizeFirstLetter(booking.mealType)}
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
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
                            <Chip
                              size="small"
                              label={booking.mealSpecificType || "N/A"}
                              sx={{
                                fontWeight: "medium",
                                bgcolor: alpha('#009688', 0.1),
                                color: '#00796B',
                                height: '24px',
                                fontSize: '0.75rem',
                                '& .MuiChip-label': {
                                  px: 0.8
                                }
                              }}
                            />
                          </TableCell>
                          <TableCell sx={{ py: 1 }}>
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
                                  <Typography variant="body2" fontWeight="medium" color="#E65100" noWrap sx={{ maxWidth: 80 }}>
                                    {DmcName || "DMC"}
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
                          {/* <TableCell sx={{ py: 1 }}>
                            {booking.transport ? (
                              <Chip
                                size="small"
                                icon={<DirectionsCarIcon style={{ fontSize: '12px' }} />}
                                label={(() => {
                                  const transport = booking.transport;
                                  
                                  if (transport.transport_type === 'shared') {
                                    return 'Shared';
                                  } else {
                                    return 'Private';
                                  }
                                })()}
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
                            {booking.transport && (
                              <Typography 
                                variant="caption" 
                                display="block" 
                                sx={{ 
                                  color: booking.transport.transport_type === 'shared' 
                                    ? '#2E7D32' 
                                    : '#E65100', 
                                  fontSize: '0.7rem',
                                  mt: 0.5,
                                  fontWeight: 'medium'
                                }}
                              >
                                {booking.transport.vehicle_name}
                              </Typography>
                            )}
                          </TableCell> */}
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
                                maxWidth: '90px',
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
                  <RestaurantIcon />
                </Avatar>
                <Typography
                  variant="h6"
                  sx={{ color: '#1976d2', fontWeight: 500 }}
                >
                  No restaurant bookings found
                </Typography>
                <Typography variant="body1" sx={{ color: '#757575', maxWidth: 400 }}>
                  There are no restaurant bookings registered for this date. Try selecting a different date or check back later.
                </Typography>
              </Box>
            )}
          </Box>
        </Box>
      </Modal>

      {isViewModalOpen && selectedBooking && (
        <RestaurantBookingModal
          open={isViewModalOpen}
          onClose={handleCloseViewModal}
          booking={selectedBooking}
        />
      )}
    </>
  );
}
