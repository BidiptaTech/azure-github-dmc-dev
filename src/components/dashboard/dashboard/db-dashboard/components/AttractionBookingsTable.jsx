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
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../../../slice/dmc/dmcSlice"; // Import DMC slice selectors
import dayjs from "dayjs";
import VisibilityIcon from "@mui/icons-material/Visibility";
import CancelIcon from "@mui/icons-material/Cancel";
import AttractionsIcon from "@mui/icons-material/Attractions";
import AttractionBookingModal from "./AttractionBookingModal";
import { Typography, Box, Chip, Avatar, alpha, Stack, IconButton } from "@mui/material";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import CardTravelIcon from "@mui/icons-material/CardTravel";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import ConfirmationNumberIcon from "@mui/icons-material/ConfirmationNumber";
import FilterListIcon from "@mui/icons-material/FilterList";
import PriceCheckIcon from "@mui/icons-material/PriceCheck";
import PersonIcon from "@mui/icons-material/Person";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import ElderlyIcon from "@mui/icons-material/Elderly";

// Function to capitalize first letter
const capitalizeFirstLetter = (string) => {
  if (!string) return "N/A";
  return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
};

// Function to get initials from package name
const getPackageInitials = (packageName) => {
  if (!packageName) return "P";
  return packageName
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2); // Limit to 2 characters for better display
};

// Function to format package name with proper capitalization
const formatPackageName = (packageName) => {
  if (!packageName) return "N/A";
  return packageName
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
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

// Get selection icon and style based on transport type
const getSelectionStyle = (selection) => {
  switch (selection) {
    case "withPrivate":
      return {
        icon: <DirectionsCarIcon fontSize="small" />,
        label: "Private Transport",
        color: "#1976D2",
        bgcolor: alpha('#1976D2', 0.1)
      };
    case "withShare":
      return {
        icon: <DirectionsCarIcon fontSize="small" />,
        label: "Shared Transport",
        color: "#7B1FA2",
        bgcolor: alpha('#7B1FA2', 0.1)
      };
    case "withoutTraveller":
    case "withoutTransport":
    default:
      return {
        icon: <AttractionsIcon fontSize="small" />,
        label: "Only Attraction",
        color: "#00796B",
        bgcolor: alpha('#00796B', 0.1)
      };
  }
};

const AttractionBookingsTable = React.memo(({ onCountChange }) => {
  const { bookings, status, error } = useSelector((state) => state.viewDetails);
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  
  // Get tax percentage from auth slice instead of attractions
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  
  // Combine attraction and attraction_package bookings
  const combinedBookings = useMemo(() => {
    const attractionBookings = (bookings?.attraction || []).map(booking => ({
      ...booking,
      bookingType: 'attraction'
    }));
    
    const packageBookings = (bookings?.attraction_package || []).map(booking => ({
      ...booking,
      bookingType: 'package'
    }));
    
    return [...attractionBookings, ...packageBookings];
  }, [bookings?.attraction, bookings?.attraction_package]);
  
  // Memoize the combined bookings count
  const totalBookingsCount = useMemo(() => combinedBookings.length, [combinedBookings.length]);

  // Only update count when it actually changes
  useEffect(() => {
    onCountChange(totalBookingsCount);
  }, [totalBookingsCount, onCountChange]);

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
      {/* Header Section */}
      <Paper
        elevation={2}
        sx={{
          borderRadius: "8px 8px 0 0",
          mb: 0,
          overflow: "hidden",
        }}
      >
        <Box sx={{
          background: "linear-gradient(90deg, #00796B 0%, #009688 100%)",
          color: "white",
          py: 1.5,
          px: 2.5,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center"
        }}>
          <Stack direction="row" spacing={3} alignItems="center">
            <Stack direction="row" spacing={1} alignItems="center">
              <Avatar sx={{ 
                bgcolor: 'white', 
                color: '#00796B', 
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
                Attraction & Experiences Bookings
              </Typography>
            </Stack>
            
            {totalBookingsCount > 0 && (
              <Stack direction="row" spacing={1} alignItems="center" sx={{ 
                bgcolor: alpha('#fff', 0.15), 
                borderRadius: 1.5, 
                px: 1.5, 
                py: 0.75 
              }}>
                <ConfirmationNumberIcon fontSize="small" />
                <Typography fontWeight={500} fontSize="1rem" color="white">
                  {totalBookingsCount} {totalBookingsCount === 1 ? 'Booking' : 'Bookings'}
                </Typography>
              </Stack>
            )}
          </Stack>
          
          <IconButton
            size="small"
            sx={{
              color: "white",
              bgcolor: 'rgba(255,255,255,0.1)',
              "&:hover": { bgcolor: "rgba(255, 255, 255, 0.2)" },
            }}
          >
            <FilterListIcon fontSize="small" />
          </IconButton>
        </Box>
      </Paper>

      <TableContainer 
        component={Paper} 
        elevation={2}
        sx={{ 
          borderRadius: "0 0 8px 8px",
          overflow: 'auto',
          mb: 3,
          mt: -1,
          maxWidth: '100%'
        }}
      >
        <Table size="small">
          <TableHead>
            <TableRow sx={{ 
              background: "linear-gradient(90deg, #00796B 0%, #009688 100%)",
              '& .MuiTableCell-head': {
                fontWeight: 'bold',
                py: 1.5,
                whiteSpace: 'nowrap',
                fontSize: '0.85rem'
              }
            }}>
              <TableCell sx={{ color: "#fff", width: '10%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <CalendarTodayIcon fontSize="small" />
                  <Typography variant="body2" fontWeight="bold" color="white">Booking Date</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '15%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <AttractionsIcon fontSize="small" />
                  <Typography variant="body2" fontWeight="bold" color="white">Attraction Name</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '8%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <AccessTimeIcon fontSize="small" />
                  <Typography variant="body2" fontWeight="bold" color="white">Time</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '7%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <PersonIcon fontSize="small" />
                  <Typography variant="body2" fontWeight="bold" color="white">Adults</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '7%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <ChildCareIcon fontSize="small" />
                  <Typography variant="body2" fontWeight="bold" color="white">Children</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '7%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <ElderlyIcon fontSize="small" />
                  <Typography variant="body2" fontWeight="bold" color="white">Seniors</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '9%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  <PriceCheckIcon fontSize="small" />
                  <Typography variant="body2" fontWeight="bold" color="white">Price</Typography>
                </Box>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '8%' }}>
                <Typography variant="body2" fontWeight="bold" color="white">Mode</Typography>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '8%' }}>
                <Typography variant="body2" fontWeight="bold" color="white">Status</Typography>
              </TableCell>
              <TableCell sx={{ color: "#fff", width: '15%' }}>
                <Typography variant="body2" fontWeight="bold" color="white">Actions</Typography>
              </TableCell>
            </TableRow>
          </TableHead>

          <TableBody>
            {combinedBookings.length > 0 ? (
              combinedBookings.map((booking, index) => {
                const statusText = getStatusText(booking.status);
                const statusStyle = getStatusStyle(statusText);
                const selectionStyle = getSelectionStyle(booking.Selection);
                
                return (
                  <TableRow 
                    key={index}
                    sx={{
                      backgroundColor: index % 2 === 0 ? "#fafafa" : "#ffffff",
                      "&:hover": { backgroundColor: alpha("#00796B", 0.04) },
                      transition: "background-color 0.3s ease"
                    }}
                  >
                    <TableCell sx={{ py: 0.75 }}>
                      <Chip
                        size="small"
                        label={booking.bookingDate
                          ? dayjs(booking.bookingDate).format("DD MMM YY")
                          : "N/A"}
                        sx={{
                          bgcolor: alpha('#4CAF50', 0.1),
                          color: '#2E7D32',
                          fontWeight: 'medium',
                          height: '22px',
                          fontSize: '0.7rem',
                          '& .MuiChip-label': {
                            px: 0.8
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell sx={{ py: 0.75 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                        <Avatar 
                          sx={{ 
                            width: 24, 
                            height: 24, 
                            bgcolor: booking.bookingType === 'package' ? alpha('#FF9800', 0.1) : alpha('#00796B', 0.1),
                            color: booking.bookingType === 'package' ? '#E65100' : '#00796B',
                            fontSize: '10px',
                            fontWeight: 'bold'
                          }}
                        >
                          {booking.bookingType === 'package' 
                            ? getPackageInitials(booking.ticketName)
                            : (booking.AttractionName?.charAt(0).toUpperCase() || "A")
                          }
                        </Avatar>
                        <Box sx={{ display: 'flex', flexDirection: 'column', minWidth: 0 }}>
                          <Typography variant="body2" fontWeight="500" noWrap sx={{ maxWidth: 120 }}>
                            {booking.bookingType === 'package' 
                              ? formatPackageName(booking.ticketName)
                              : (booking.AttractionName || "N/A")
                            }
                          </Typography>
                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5, mt: 0.25 }}>
                            {booking.bookingType === 'package' ? (
                              <CardTravelIcon style={{ fontSize: '10px', color: '#E65100' }} />
                            ) : (
                              <AttractionsIcon style={{ fontSize: '10px', color: '#00796B' }} />
                            )}
                            <Typography variant="caption" sx={{ 
                              fontSize: '0.65rem', 
                              color: booking.bookingType === 'package' ? '#E65100' : '#00796B',
                              fontWeight: 'medium'
                            }}>
                              {booking.bookingType === 'package' ? 'Package' : 'Attraction'}
                            </Typography>
                          </Box>
                        </Box>
                      </Box>
                    </TableCell>
                    <TableCell sx={{ py: 0.75 }}>
                      <Typography variant="body2" sx={{ fontSize: '0.8rem' }}>
                        {booking.visitTime || "N/A"}
                      </Typography>
                    </TableCell>
                    <TableCell align="center" sx={{ py: 0.75 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <Typography variant="body2" sx={{ 
                          fontSize: '0.8rem', 
                          fontWeight: 'medium', 
                          color: '#1565C0'
                        }}>
                          {booking.adultCount ?? "0"}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell align="center" sx={{ py: 0.75 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <Typography variant="body2" sx={{ 
                          fontSize: '0.8rem', 
                          fontWeight: 'medium', 
                          color: '#7B1FA2'
                        }}>
                          {booking.childCount ?? "0"}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell align="center" sx={{ py: 0.75 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <Typography variant="body2" sx={{ 
                          fontSize: '0.8rem', 
                          fontWeight: 'medium', 
                          color: '#2E7D32'
                        }}>
                          {booking.seniorCount ?? "0"}
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell sx={{ py: 0.75 }}>
                      <Chip
                        size="small"
                        icon={<PriceCheckIcon style={{ fontSize: '12px' }} />}
                        label={(() => {
                          // Check PriceHide before displaying the price
                          if (PriceHide !== "0") {
                            return "Price Hidden";
                          }
                          
                          let basePrice = 0;
                          
                          // Calculate price based on booking type
                          if (booking.bookingType === 'package' && booking.ticket_details) {
                            // For package bookings, calculate total from ticket details
                            const adultPrice = (booking.ticket_details.adult_price || 0) * (booking.adultCount || 0);
                            const childPrice = (booking.ticket_details.child_price || 0) * (booking.childCount || 0);
                            const seniorPrice = (booking.ticket_details.senior_price || 0) * (booking.seniorCount || 0);
                            basePrice = adultPrice + childPrice + seniorPrice;
                          } else {
                            // For attraction bookings, use totalPrice
                            basePrice = booking.totalPrice || 0;
                          }
                          
                          const sgdPrice = Math.ceil(basePrice);
                          const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
                          const sgdGrandTotal = sgdPrice + sgdTaxAmount;
                          
                          return sgdTax > 0 
                            ? `SGD ${sgdGrandTotal} (incl. ${sgdTax}% tax)`
                            : `SGD ${sgdPrice}`;
                        })()}
                        sx={{
                          fontWeight: "bold",
                          bgcolor: alpha('#673AB7', 0.1),
                          color: '#5E35B1',
                          height: 'auto',
                          minHeight: '24px',
                          fontSize: '0.75rem',
                          py: 0.5,
                          maxWidth: '90px',
                          position: 'relative',
                          '& .MuiChip-icon': {
                            color: '#5E35B1'
                          },
                          '& .MuiChip-label': {
                            px: 0.8,
                            whiteSpace: 'normal',
                            overflow: 'hidden',
                            textOverflow: 'ellipsis',
                          }
                        }}
                      />
                    </TableCell>
                    <TableCell sx={{ py: 0.75 }}>
                      {booking.mode === "travClicks" || booking.mode === "travclicks" ? (
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
                        <Typography variant="body2" sx={{ fontSize: '0.75rem', fontWeight: 'medium' }}>
                          {booking.mode?.charAt(0).toUpperCase() +
                          booking.mode?.slice(1).toLowerCase()}
                        </Typography>
                      )}
                    </TableCell>
                    <TableCell sx={{ py: 0.75 }}>
                      <Typography variant="body2" sx={{ 
                        fontSize: '0.75rem', 
                        fontWeight: 'medium',
                        color: statusStyle.color
                      }}>
                        {statusText}
                      </Typography>
                    </TableCell>
                    <TableCell sx={{ py: 0.75 }}>
                      <Box sx={{ display: "flex", gap: "4px" }}>
                        <Button
                          variant="contained"
                          size="small"
                          startIcon={<VisibilityIcon style={{ fontSize: '14px' }} />}
                          onClick={() => handleView(booking)}
                          sx={{
                            backgroundColor: "#4CAF50",
                            "&:hover": {
                              backgroundColor: "#45a049",
                            },
                            borderRadius: 1,
                            textTransform: 'none',
                            px: 0.8,
                            py: 0.3,
                            minWidth: '60px',
                            fontSize: '0.7rem',
                            height: '24px',
                            '& .MuiButton-startIcon': {
                              marginRight: '2px'
                            }
                          }}
                        >
                          View
                        </Button>
                        <Button
                          variant="contained"
                          size="small"
                          startIcon={<CancelIcon style={{ fontSize: '14px' }} />}
                          onClick={() => handleCancel(booking)}
                          sx={{
                            backgroundColor: "#f44336",
                            "&:hover": {
                              backgroundColor: "#da190b",
                            },
                            borderRadius: 1,
                            textTransform: 'none',
                            px: 0.8,
                            py: 0.3,
                            minWidth: '60px',
                            fontSize: '0.7rem',
                            height: '24px',
                            '& .MuiButton-startIcon': {
                              marginRight: '2px'
                            }
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
                <TableCell colSpan={10} align="center">
                  <Box sx={{ 
                    py: 3,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: 1
                  }}>
                    <Avatar 
                      sx={{ 
                        bgcolor: alpha('#00796B', 0.1),
                        color: '#00796B',
                        width: 40,
                        height: 40,
                        mb: 1
                      }}
                    >
                      <AttractionsIcon />
                    </Avatar>
                    <Typography variant="body1" color="textSecondary">
                      No attraction or package bookings available.
                    </Typography>
                  </Box>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      <AttractionBookingModal
        open={isModalOpen}
        onClose={handleCloseModal}
        booking={selectedBooking}
      />
    </>
  );
});

AttractionBookingsTable.displayName = 'AttractionBookingsTable';

export default AttractionBookingsTable;
