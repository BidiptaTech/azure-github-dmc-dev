import React from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  IconButton,
  Typography,
  Grid,
  Divider,
  Card,
  CardMedia,
  CardContent,
  Paper,
  Box,
  Chip,
  Avatar,
  AvatarGroup,
  Button,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import HotelIcon from "@mui/icons-material/Hotel";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import KingBedOutlinedIcon from "@mui/icons-material/KingBedOutlined";
import BedOutlinedIcon from "@mui/icons-material/BedOutlined";
import BedroomParentOutlinedIcon from "@mui/icons-material/BedroomParentOutlined";
import SingleBedOutlinedIcon from "@mui/icons-material/SingleBedOutlined";
import RestaurantMenuIcon from "@mui/icons-material/RestaurantMenu";
import CurrencyExchangeIcon from "@mui/icons-material/CurrencyExchange";
import BabyChangingStationIcon from "@mui/icons-material/BabyChangingStation";
import GroupIcon from "@mui/icons-material/Group";
import PersonIcon from "@mui/icons-material/Person";
import WomanIcon from "@mui/icons-material/Woman";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import CribIcon from "@mui/icons-material/Crib";
import dayjs from "dayjs";
import { useSelector } from "react-redux";
import { alpha } from "@mui/material/styles";

// Function to capitalize first letter
const capitalizeFirstLetter = (string) => {
  if (!string) return "N/A";
  const str = String(string);
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

// Utility function for safe parsing of numeric values
const safeParse = (value, defaultValue = 0) => {
  if (value === undefined || value === null || value === '') return defaultValue;
  
  // Handle string representations
  if (typeof value === 'string') {
    // Try to parse the string
    const trimmed = value.trim();
    // Handle currency symbols or other characters
    const numericString = trimmed.replace(/[^0-9.-]+/g, '');
    const parsed = parseFloat(numericString);
    return isNaN(parsed) ? defaultValue : parsed;
  }
  
  // Handle numeric values directly
  if (typeof value === 'number') {
    return isNaN(value) ? defaultValue : value;
  }
  
  // Handle other cases
  return defaultValue;
};

// Function to format dates consistently, similar to HotelModal
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

// Helper function to get baby cot price from different possible locations
const getBabyCotPrice = (bed) => {
  // Check various possible locations for baby_cot_price
  if (bed?.baby_cot_price !== undefined && bed.baby_cot_price !== 0) {
    return { value: bed.baby_cot_price, isDefault: false };
  }
  if (bed?.babyCotPrice !== undefined && bed.babyCotPrice !== 0) {
    return { value: bed.babyCotPrice, isDefault: false };
  }
  if (bed?.baby_cot_details?.price !== undefined && bed.baby_cot_details.price !== 0) {
    return { value: bed.baby_cot_details.price, isDefault: false };
  }
  if (bed?.baby && bed?.baby.price !== undefined && bed.baby.price !== 0) {
    return { value: bed.baby.price, isDefault: false };
  }
  if (bed?.extras?.baby_cot_price !== undefined && bed.extras.baby_cot_price !== 0) {
    return { value: bed.extras.baby_cot_price, isDefault: false };
  }
  
  // Helper function to find all properties with specific keywords
  const findPropertiesWithKeyword = (obj, keywords) => {
    const results = {};
    
    const search = (obj, path = '') => {
      if (!obj || typeof obj !== 'object') return;
      
      Object.keys(obj).forEach(key => {
        const currentPath = path ? `${path}.${key}` : key;
        const value = obj[key];
        
        // Check if key contains any of the keywords
        if (keywords.some(keyword => key.toLowerCase().includes(keyword.toLowerCase()))) {
          results[currentPath] = value;
        }
        
        // Continue searching if it's an object or array
        if (value && typeof value === 'object') {
          search(value, currentPath);
        }
      });
    };
    
    search(obj);
    return results;
  };
  
  // Log all potential properties related to baby cot
  console.log('Potential baby cot fields:', findPropertiesWithKeyword(bed, ['baby', 'cot', 'infant']));
  
  // If none of the locations have a value, try a default price
  if (bed?.baby_cot === true || bed?.baby_cot === 1) {
    return { value: "60.00", isDefault: true }; // Default price if baby cot is included but no price is specified
  }
  
  return { value: 0, isDefault: false }; // Default fallback
};

// Map bed types to icons
const getBedTypeIcon = (bedType) => {
  const normalizedBedType = bedType?.split(' ')[0]?.toLowerCase();
  
  switch (normalizedBedType) {
    case 'king':
      return <KingBedOutlinedIcon />;
    case 'queen':
      return <BedOutlinedIcon />;
    case 'twin':
      return <BedroomParentOutlinedIcon />;
    case 'single':
      return <SingleBedOutlinedIcon />;
    default:
      return <BedOutlinedIcon />;
  }
};

const HotelBookingModal = ({ open, onClose, booking }) => {
  // Add selectors for DMC and currency info
  console.log("booking", booking);
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  const priceMode =
    useSelector((state) => state.hotels.searchState.priceMode) || "dmc";
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  
  // Get tax percentages from auth slice
  const currentTax = useSelector((state) => state.auth.currentTax || 0);
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const usdTax = useSelector((state) => state.auth.usdTax || 0);
    const PriceHide = useSelector((state) => state.auth.PriceHide);
  

  // Add keyboard shortcut to toggle debug mode
  React.useEffect(() => {
    const handleKeyDown = (e) => {
      // Toggle debug mode with Ctrl+Shift+D
      if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        const currentDebugMode = window.localStorage.getItem('debug_mode') === 'true';
        window.localStorage.setItem('debug_mode', (!currentDebugMode).toString());
        console.log(`Debug mode ${!currentDebugMode ? 'enabled' : 'disabled'}`);
        // Force a re-render
        setDebugModeActive(!currentDebugMode);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, []);

  // State to track debug mode for re-rendering
  const [debugModeActive, setDebugModeActive] = React.useState(
    window.localStorage.getItem('debug_mode') === 'true'
  );

  if (!booking) return null;

  // Calculate total occupancy
  const getTotalOccupancy = () => {
    let total = 0;
    booking.rooms?.forEach(room => {
      room.beds?.forEach(bed => {
        total += bed.head_count || 0;
      });
    });
    return total;
  };

  // Get max occupancy - changed to count number of beds
  const getMaxOccupancy = () => {
    let bedCount = 0;
    booking.rooms?.forEach(room => {
      bedCount += room.beds?.length || 0;
    });
    return bedCount;
  };
  
  // Get check-in and check-out dates from booking
  const getCheckInDate = () => {
    if (booking.bookingDate && Array.isArray(booking.bookingDate) && booking.bookingDate.length > 0) {
      return formatDate(booking.bookingDate[0]);
    }
    return "N/A";
  };
  
  const getCheckOutDate = () => {
    if (booking.bookingDate && Array.isArray(booking.bookingDate) && booking.bookingDate.length > 1) {
      return formatDate(booking.bookingDate[1]);
    }
    return "N/A";
  };

  return (
    <Dialog 
      open={open} 
      onClose={onClose} 
      maxWidth="md" 
      fullWidth
      PaperProps={{
        sx: {
          borderRadius: '12px',
          overflow: 'hidden'
        }
      }}
    >
      <DialogTitle
        sx={{
          background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
          color: "#fff",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          py: 2,
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <HotelIcon fontSize="large" />
          <Typography variant="h6" sx={{ fontWeight: 'bold' }}>Hotel Booking Details</Typography>
        </Box>
        <IconButton
          edge="end"
          color="inherit"
          onClick={onClose}
          aria-label="close"
          sx={{ color: "#fff" }}
        >
          <CloseIcon />
        </IconButton>
      </DialogTitle>
      <DialogContent sx={{ mt: 2, position: "relative", p: 3 }}>
        <Grid container spacing={3}>
          {/* Hotel Image and Basic Info Card */}
          <Grid item xs={12}>
            <Card 
              elevation={2}
              sx={{ 
                borderRadius: '16px',
                overflow: 'hidden',
                transition: 'transform 0.3s ease, box-shadow 0.3s ease',
                '&:hover': {
                  transform: 'translateY(-4px)',
                  boxShadow: '0 8px 24px rgba(0,0,0,0.12)',
                }
              }}
            >
              <Grid container>
                <Grid item xs={12} md={4}>
                  <Box
                    sx={{
                      height: '100%',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      backgroundColor: 'rgba(53, 84, 209, 0.05)',
                      minHeight: '220px',
                      position: 'relative',
                      overflow: 'hidden',
                    }}
                  >
                    {booking.hotelDetails?.image ? (
                      <CardMedia
                        component="img"
                        image={booking.hotelDetails.image}
                        alt={booking.hotelDetails.hotel_name || "Hotel"}
                        sx={{
                          width: '100%',
                          height: '100%',
                          objectFit: "cover",
                          position: 'absolute',
                          top: 0,
                          left: 0,
                        }}
                      />
                    ) : (
                      <Box
                        sx={{
                          display: 'flex',
                          flexDirection: 'column',
                          alignItems: 'center',
                          justifyContent: 'center',
                          p: 2,
                        }}
                      >
                        <HotelIcon
                          sx={{ fontSize: 80, color: '#3554D1', opacity: 0.7, mb: 2 }}
                        />
                        <Typography variant="body2" color="textSecondary" align="center">
                          No hotel image available
                        </Typography>
                      </Box>
                    )}
                  </Box>
                </Grid>
                <Grid item xs={12} md={8}>
                  <CardContent sx={{ p: 3 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                      <HotelIcon sx={{ color: '#3554D1', mr: 1, fontSize: 28 }} />
                      <Typography variant="h5" sx={{ fontWeight: 'bold' }}>
                        {booking.hotelDetails?.hotel_name || "N/A"}
                      </Typography>
                    </Box>
                    
                    {booking.hotelDetails?.location && (
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                        <LocationOnIcon sx={{ color: '#3554D1', mr: 1 }} />
                        <Typography variant="body1">
                          {booking.hotelDetails.location}
                        </Typography>
                      </Box>
                    )}
                    
                    <Grid container spacing={2}>
                      <Grid item xs={12} sm={6}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <CalendarTodayIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body2" color="textSecondary">
                            Check-In
                          </Typography>
                        </Box>
                        <Typography variant="body1" sx={{ fontWeight: 'medium', ml: 4 }}>
                          {getCheckInDate()}
                        </Typography>
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <EventAvailableIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body2" color="textSecondary">
                            Check-Out
                          </Typography>
                        </Box>
                        <Typography variant="body1" sx={{ fontWeight: 'medium', ml: 4 }}>
                          {getCheckOutDate()}
                        </Typography>
                      </Grid>
                    </Grid>
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          </Grid>

          {/* Occupancy Overview Card */}
          <Grid item xs={12}>
            <Card 
              elevation={2}
              sx={{ 
                borderRadius: '16px',
                overflow: 'hidden',
                transition: 'transform 0.3s ease, box-shadow 0.3s ease',
                '&:hover': {
                  transform: 'translateY(-4px)',
                  boxShadow: '0 8px 24px rgba(0,0,0,0.12)',
                },
                mb: 2
              }}
            >
              <CardContent sx={{ p: 3 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                  <GroupIcon sx={{ color: '#3554D1', mr: 1, fontSize: 28 }} />
                  <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                    Occupancy Overview
                  </Typography>
                </Box>
                
                <Grid container spacing={3}>
                  {/* Actual Occupancy */}
                  <Grid item xs={12} md={6}>
                    <Paper 
                      elevation={1} 
                      sx={{ 
                        p: 2, 
                        borderRadius: '12px',
                        backgroundColor: 'rgba(53, 84, 209, 0.05)'
                      }}
                    >
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        <PersonIcon sx={{ color: '#3554D1', mr: 1 }} />
                        <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                          Actual Occupancy ({getTotalOccupancy()})
                        </Typography>
                      </Box>
                      
                      <Divider sx={{ my: 1 }} />
                      
                      <Box sx={{ display: 'flex', justifyContent: 'center', mt: 2 }}>
                        <AvatarGroup max={10} sx={{ '& .MuiAvatar-root': { width: 35, height: 35 } }}>
                          {Array.from({ length: getTotalOccupancy() }).map((_, i) => (
                            <Avatar 
                              key={`occupancy-${i}`} 
                              sx={{ 
                                bgcolor: i % 3 === 0 ? '#3554D1' : i % 3 === 1 ? '#E91E63' : '#FF9800',
                                color: 'white',
                              }}
                            >
                              {i % 3 === 0 ? <PersonIcon fontSize="small" /> : 
                               i % 3 === 1 ? <WomanIcon fontSize="small" /> : 
                               <ChildCareIcon fontSize="small" />}
                            </Avatar>
                          ))}
                        </AvatarGroup>
                      </Box>
                    </Paper>
                  </Grid>
                  
                  {/* Max Occupancy */}
                  <Grid item xs={12} md={6}>
                    <Paper 
                      elevation={1} 
                      sx={{ 
                        p: 2, 
                        borderRadius: '12px'
                      }}
                    >
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        <GroupIcon sx={{ color: '#3554D1', mr: 1 }} />
                        <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                          Beds Booked ({getMaxOccupancy()})
                        </Typography>
                      </Box>
                      
                      <Divider sx={{ my: 1 }} />
                      
                      <Box sx={{ display: 'flex', flexDirection: 'column', mt: 2 }}>
                        {booking.rooms?.map((room, roomIndex) => 
                          room.beds?.map((bed, bedIndex) => (
                            <Box 
                              key={`bed-item-${roomIndex}-${bedIndex}`}
                              sx={{ 
                                display: 'flex', 
                                alignItems: 'center',
                                p: 1,
                                mb: 1,
                                borderRadius: '8px',
                                bgcolor: 'rgba(53, 84, 209, 0.05)',
                                border: '1px solid',
                                borderColor: 'rgba(53, 84, 209, 0.1)'
                              }}
                            >
                              <Avatar 
                                sx={{ 
                                  bgcolor: 'rgba(53, 84, 209, 0.1)',
                                  color: '#3554D1',
                                  mr: 1.5
                                }}
                              >
                                {getBedTypeIcon(bed.bed_type)}
                              </Avatar>
                              <Box>
                                <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                                  {capitalizeFirstLetter(bed.bed_type || "Standard Bed")}
                                </Typography>
                                <Typography variant="body2" color="text.secondary">
                                  Room: {capitalizeFirstLetter(room.room_type || `Room ${roomIndex + 1}`)} 
                                </Typography>
                              </Box>
                            </Box>
                          ))
                        )}
                      </Box>
                    </Paper>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>

          {/* Room Details */}
          <Grid item xs={12}>
            <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 2, display: 'flex', alignItems: 'center' }}>
              <HotelIcon sx={{ mr: 1, color: '#3554D1' }} />
              Room Details
            </Typography>
            
            {booking.rooms?.map((room, index) => (
              <Card 
                key={index}
                elevation={2}
                sx={{ 
                  borderRadius: '16px',
                  overflow: 'hidden',
                  transition: 'transform 0.3s ease, box-shadow 0.3s ease',
                  '&:hover': {
                    transform: 'translateY(-4px)',
                    boxShadow: '0 8px 24px rgba(0,0,0,0.12)',
                  },
                  mb: 2
                }}
              >
                <CardContent sx={{ p: 3 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                    <Chip 
                      icon={<HotelIcon />}
                      label={capitalizeFirstLetter(room.room_type)}
                      color="primary"
                      sx={{ fontWeight: 'bold', fontSize: '1rem', py: 2, px: 1 }}
                    />
                  </Box>
                  
                  <Grid container spacing={3}>
                    {/* Bed Type & Meal Details Card */}
                    <Grid item xs={12} md={6}>
                      <Paper 
                        elevation={1} 
                        sx={{ 
                          p: 2, 
                          borderRadius: '12px',
                          height: '100%',
                          bgcolor: 'white',
                          border: `1px solid ${alpha('#3554D1', 0.1)}`
                        }}
                      >
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <BedOutlinedIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                            Bed & Meal Details
                          </Typography>
                        </Box>
                        
                        <Divider sx={{ my: 1 }} />
                        
                        <Box sx={{ mt: 2 }}>
                          {room.beds?.map((bed, bedIndex) => (
                            <Box 
                              key={`bed-details-${bedIndex}`} 
                              sx={{ 
                                mb: 2,
                                p: 1.5,
                                borderRadius: '8px',
                                bgcolor: bedIndex % 2 === 0 ? alpha('#3554D1', 0.04) : 'transparent',
                                border: '1px solid',
                                borderColor: alpha('#3554D1', 0.1)
                              }}
                            >
                              {/* Bed Type Header */}
                              <Box sx={{ 
                                display: 'flex', 
                                alignItems: 'center',
                                mb: 1,
                                pb: 1,
                                borderBottom: `1px dashed ${alpha('#3554D1', 0.2)}`
                              }}>
                                <Avatar
                                  sx={{
                                    bgcolor: alpha('#3554D1', 0.1),
                                    color: '#3554D1',
                                    width: 36,
                                    height: 36,
                                    mr: 1.5
                                  }}
                                >
                                  {getBedTypeIcon(bed.bed_type)}
                                </Avatar>
                                <Box>
                                  <Typography variant="body1" sx={{ fontWeight: 'medium', color: '#3554D1' }}>
                                    {capitalizeFirstLetter(bed.bed_type)}
                                  </Typography>
                                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                    <PersonIcon sx={{ fontSize: '0.875rem', color: 'text.secondary', mr: 0.5 }} />
                                    <Typography variant="body2" color="text.secondary">
                                      {bed.head_count} {bed.head_count === 1 ? 'person' : 'persons'}
                                    </Typography>
                                  </Box>
                                </Box>
                              </Box>
                              
                              {/* Meal Types */}
                              <Box sx={{ ml: 0.5, mb: 1.5 }}>
                                <Typography variant="body2" color="text.secondary" sx={{ 
                                  mb: 0.5,
                                  display: 'flex',
                                  alignItems: 'center'
                                }}>
                                  <RestaurantMenuIcon sx={{ fontSize: '1rem', mr: 0.5 }} />
                                  Meal Options:
                                </Typography>
                                
                                {bed.mealTypes?.length > 0 ? (
                                  <Box sx={{ 
                                    display: 'flex', 
                                    flexWrap: 'wrap', 
                                    gap: 0.8,
                                    ml: 2
                                  }}>
                                    {bed.mealTypes.map((meal, mealIndex) => (
                                      <Chip
                                        key={`meal-${bedIndex}-${mealIndex}`}
                                        icon={<RestaurantMenuIcon style={{ fontSize: '14px' }} />}
                                        label={capitalizeFirstLetter(meal)}
                                        variant="outlined"
                                        size="small"
                                        color="primary"
                                        sx={{ 
                                          fontWeight: 'medium',
                                          fontSize: '0.75rem',
                                          height: '24px'
                                        }}
                                      />
                                    ))}
                                  </Box>
                                ) : (
                                  <Typography variant="body2" sx={{ ml: 2, color: 'text.secondary' }}>
                                    No meal options for this bed
                                  </Typography>
                                )}
                              </Box>
                              
                              {/* Baby Cot - show when included (true or 1) */}
                              {(bed.baby_cot === true || bed.baby_cot === 1) && (
                                <Box sx={{ ml: 0.5 }}>
                                  {/* Debug info */}
                                  {debugModeActive && (
                                    <Box sx={{ 
                                      mb: 1, 
                                      p: 1, 
                                      borderRadius: '4px',
                                      bgcolor: alpha('#FF9800', 0.1),
                                      border: '1px dashed',
                                      borderColor: alpha('#FF9800', 0.3),
                                      fontSize: '12px'
                                    }}>
                                      <Typography variant="caption" sx={{ fontFamily: 'monospace', whiteSpace: 'pre-wrap' }}>
                                        DEBUG: Baby Cot Price{'\n'}
                                        Raw value: {String(bed.baby_cot_price)}{'\n'}
                                        Data type: {typeof bed.baby_cot_price}{'\n'}
                                        Default used: {String(getBabyCotPrice(bed).isDefault)}{'\n'}
                                        Parsed value: {safeParse(getBabyCotPrice(bed).value)}
                                      </Typography>
                                    </Box>
                                  )}
                                  
                                  <Typography variant="body2" color="text.secondary" sx={{
                                    mb: 0.5,
                                    display: 'flex',
                                    alignItems: 'center'
                                  }}>
                                    <BabyChangingStationIcon sx={{ fontSize: '1rem', mr: 0.5 }} />
                                    Baby Cot:
                                  </Typography>
                                  
                                  <Box sx={{ ml: 2 }}>
                                    {/* More compact layout with price info */}
                                    <Box 
                                      sx={{ 
                                        p: 1.5,
                                        borderRadius: '8px',
                                        bgcolor: alpha('#4CAF50', 0.05),
                                        border: '1px solid',
                                        borderColor: alpha('#4CAF50', 0.2),
                                        display: 'flex',
                                        flexDirection: 'column',
                                        gap: 1
                                      }}
                                    >
                                      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                          <BabyChangingStationIcon style={{ color: '#4CAF50', marginRight: '8px' }} />
                                          <Typography variant="body2" sx={{ fontWeight: 'medium', color: '#4CAF50' }}>
                                            Baby Cot 
                                          </Typography>
                                        </Box>
                                        <Chip 
                                          label="Included"
                                          size="small"
                                          color="success"
                                          sx={{ 
                                            fontWeight: 'medium',
                                            fontSize: '0.75rem',
                                            height: '24px'
                                          }}
                                        />
                                      </Box>
                                      
                                      {/* Debug console log for all price data */}
                                      {console.log('Baby cot data:', bed.baby_cot, 'Price value:', bed.baby_cot_price, 'Type:', typeof bed.baby_cot_price)}
                                      {console.log('All bed data:', bed)}
                                      
                                      {(() => {
                                        const babyCotPriceData = getBabyCotPrice(bed);
                                        console.log('Extracted price:', babyCotPriceData.value, 'Is Default:', babyCotPriceData.isDefault, 'Parsed value:', safeParse(babyCotPriceData.value));
                                        
                                        return (
                                          <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                                            <Typography variant="body2" color="text.secondary" sx={{ 
                                              fontSize: '0.75rem',
                                              display: 'flex',
                                              alignItems: 'center',
                                              gap: 0.5
                                            }}>
                                              Price:
                                              {/* {babyCotPriceData.isDefault && (
                                                <Chip 
                                                  label="Default Value" 
                                                  size="small"
                                                  color="info"
                                                  sx={{ height: '16px', fontSize: '0.625rem' }}
                                                />
                                              )} */}
                                            </Typography>
                                            <Box sx={{ display: 'flex', gap: 0.8, flexWrap: 'wrap' }}>
                                              <Chip 
                                                icon={<CurrencyExchangeIcon style={{ fontSize: '0.875rem' }} />}
                                                label={`${currencyCode} ${(safeParse(babyCotPriceData.value) * exchangeRate).toFixed(2)}`}
                                                size="small"
                                                color="primary"
                                                sx={{ 
                                                  fontWeight: 'medium',
                                                  height: '24px' 
                                                }}
                                              />
                                              <Chip 
                                                label={`${usdCurrencyCode} ${(safeParse(babyCotPriceData.value) * usdExchangeRate).toFixed(2)}`}
                                                variant="outlined"
                                                size="small"
                                                sx={{ height: '24px', fontSize: '0.75rem' }}
                                              />
                                              <Chip 
                                                label={`SGD ${safeParse(babyCotPriceData.value).toFixed(2)}`}
                                                variant="outlined"
                                                size="small"
                                                sx={{ height: '24px', fontSize: '0.75rem' }}
                                              />
                                            </Box>
                                          </Box>
                                        );
                                      })()}
                                    </Box>
                                  </Box>
                                </Box>
                              )}
                            </Box>
                          ))}
                        </Box>
                      </Paper>
                    </Grid>
                    
                    {/* Price Details Card */}
                    <Grid item xs={12} md={6}>
                      <Paper 
                        elevation={1} 
                        sx={{ 
                          p: 2, 
                          borderRadius: '12px',
                          height: '100%',
                          bgcolor: 'white',
                          border: `1px solid ${alpha('#3554D1', 0.1)}`
                        }}
                      >
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <CurrencyExchangeIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                            Price Details
                          </Typography>
                        </Box>
                        
                        <Divider sx={{ my: 1 }} />
                        
                        {/* Prices */}
                        <Box sx={{ mt: 2 }}>
                          {room.beds?.map((bed, bedIndex) => {
                            return (
                              <Box 
                                key={`bed-price-${bedIndex}`}
                                sx={{
                                  mb: 2,
                                  p: 1.5,
                                  borderRadius: '8px',
                                  bgcolor: bedIndex % 2 === 0 ? alpha('#3554D1', 0.04) : 'transparent',
                                  border: '1px solid',
                                  borderColor: alpha('#3554D1', 0.1)
                                }}
                              >
                                {/* Bed Type Header */}
                                <Box sx={{ 
                                  display: 'flex', 
                                  alignItems: 'center',
                                  mb: 1,
                                  pb: 1,
                                  borderBottom: `1px dashed ${alpha('#3554D1', 0.2)}`
                                }}>
                                  <Avatar
                                    sx={{
                                      bgcolor: alpha('#3554D1', 0.1),
                                      color: '#3554D1',
                                      width: 36,
                                      height: 36,
                                      mr: 1.5
                                    }}
                                  >
                                    {getBedTypeIcon(bed.bed_type)}
                                  </Avatar>
                                  <Box>
                                    <Typography variant="body1" sx={{ fontWeight: 'medium', color: '#3554D1' }}>
                                      {capitalizeFirstLetter(bed.bed_type)}
                                    </Typography>
                                    <Typography variant="body2" color="text.secondary">
                                      All meal options with prices
                                    </Typography>
                                  </Box>
                                </Box>
                                
                                {/* All Meal Prices */}
                                <Box sx={{ mt: 1.5, ml: 1 }}>
                                  {bed.mealTypes?.length > 0 ? (
                                    bed.mealTypes.map((mealType, mealIndex) => {
                                      // Get price for this meal type
                                      const mealKey = `meal_${mealIndex + 1}`;
                                      const mealPrice = safeParse(bed.selectedMeals?.[mealKey]?.price) || 0;
                                      const conversionRate = priceMode === "dmc" ? exchangeRate : usdExchangeRate;
                                      const convertedPrice = mealPrice * conversionRate;
                                      
                                      return (
                                        <Box 
                                          key={`meal-price-${bedIndex}-${mealIndex}`} 
                                          sx={{ 
                                            display: 'flex', 
                                            flexDirection: 'column',
                                            mb: mealIndex < bed.mealTypes.length - 1 ? 2 : 0,
                                            p: 1,
                                            borderRadius: '8px',
                                            bgcolor: alpha('#3554D1', 0.02),
                                            border: '1px dashed',
                                            borderColor: alpha('#3554D1', 0.15)
                                          }}
                                        >
                                          <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                                            <RestaurantMenuIcon sx={{ color: '#3554D1', fontSize: '1.1rem', mr: 1 }} />
                                            <Typography variant="body2" sx={{ fontWeight: 'medium' }}>
                                              {capitalizeFirstLetter(mealType)}
                                            </Typography>
                                          </Box>
                                          
                                          {mealPrice > 0 ? (
                                            <Box sx={{ 
                                              display: 'flex', 
                                              flexDirection: 'column',
                                              gap: 0.5,
                                              ml: 1
                                            }}>
                                              <Typography variant="body2" color="text.secondary" sx={{ mb: 0.5, fontSize: '0.75rem' }}>
                                                Price:
                                              </Typography>
                                              {PriceHide ==="0"?(
                                                 <Box sx={{ display: 'flex', gap: 0.8, flexWrap: 'wrap' }}>
                                                 <Chip 
                                                   icon={<CurrencyExchangeIcon style={{ fontSize: '0.875rem' }} />}
                                                   label={`${currencyCode} ${convertedPrice.toFixed(2)}`}
                                                   size="small"
                                                   color="primary"
                                                   sx={{ 
                                                     fontWeight: 'medium',
                                                     height: '24px' 
                                                   }}
                                                 />
                                                 <Chip 
                                                   label={`${usdCurrencyCode} ${(mealPrice * usdExchangeRate).toFixed(2)}`}
                                                   variant="outlined"
                                                   size="small"
                                                   sx={{ height: '24px', fontSize: '0.75rem' }}
                                                 />
                                                 <Chip 
                                                   label={`SGD ${mealPrice.toFixed(2)}`}
                                                   variant="outlined"
                                                   size="small"
                                                   sx={{ height: '24px', fontSize: '0.75rem' }}
                                                 />
                                               </Box>

                                              ):(
                                                <div className="text-15 lh-12">
                                                  Price available on request
                                                   </div>
                                                
                                              )
                                              
                                              }
                                             
                                            </Box>
                                          ) : (
                                            <Box sx={{ ml: 1, display: 'flex', alignItems: 'center' }}>
                                              <CurrencyExchangeIcon sx={{ color: 'text.secondary', mr: 1, opacity: 0.6, fontSize: '0.875rem' }} />
                                              <Typography variant="body2" color="text.secondary" fontSize="0.75rem">
                                                Price not available
                                              </Typography>
                                            </Box>
                                          )}
                                        </Box>
                                      );
                                    })
                                  ) : (
                                    <Box sx={{ display: 'flex', alignItems: 'center', ml: 1 }}>
                                      <RestaurantMenuIcon sx={{ color: 'text.secondary', mr: 1, opacity: 0.6 }} />
                                      <Typography variant="body2" color="text.secondary">
                                        No meal options available
                                      </Typography>
                                    </Box>
                                  )}
                                </Box>
                                
                                {/* Total Bed Price - sum of all meal prices */}
                                {bed.selectedMeals && (
                                  <Box sx={{ 
                                    mt: 2, 
                                    pt: 1.5,
                                    borderTop: `1px dashed ${alpha('#3554D1', 0.2)}`
                                  }}>
                                    <Typography variant="body2" fontWeight="medium" color="#3554D1" sx={{ mb: 0.5 }}>
                                      Total Price for {capitalizeFirstLetter(bed.bed_type)}:
                                    </Typography>

                                    {PriceHide ==="0"?(
                                      <Box sx={{ 
                                        bgcolor: alpha('#3554D1', 0.08),
                                        borderRadius: '8px',
                                        p: 1,
                                        display: 'flex',
                                        justifyContent: 'space-between',
                                        alignItems: 'center'
                                      }}>
                                        <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                          <CurrencyExchangeIcon sx={{ color: '#3554D1', mr: 1 }} />
                                          <Typography variant="body1" fontWeight="bold" color="#3554D1">
                                            {(() => {
                                              // Calculate sum of all meal prices
                                              let totalMealPrice = 0;
                                              if (bed.selectedMeals) {
                                                Object.keys(bed.selectedMeals).forEach(mealKey => {
                                                  totalMealPrice += safeParse(bed.selectedMeals[mealKey]?.price);
                                                });
                                              }
                                              
                                              // Add baby cot price if included
                                              const babyCotPriceData = getBabyCotPrice(bed);
                                              const babyCotPrice = (bed.baby_cot === true || bed.baby_cot === 1) 
                                                ? safeParse(babyCotPriceData.value) 
                                                : 0;
                                              
                                              // Debug baby cot price calculation
                                             // console.log('Total meal price:', totalMealPrice, 'Baby cot price:', babyCotPrice, 'Is Default:', babyCotPriceData.isDefault);
                                              
                                              const totalBedPrice = totalMealPrice + babyCotPrice;
                                              const conversionRate = priceMode === "dmc" ? exchangeRate : usdExchangeRate;
                                              return `${currencyCode} ${(totalBedPrice * conversionRate).toFixed(2)}`;
                                            })()}
                                          </Typography>
                                        </Box>
                                        <Box sx={{ display: 'flex', gap: 0.5 }}>
                                          <Chip 
                                            label="All Meals"
                                            size="small"
                                            color="primary"
                                            sx={{ height: '20px', fontSize: '0.625rem' }}
                                          />
                                          {(bed.baby_cot === true || bed.baby_cot === 1) && (
                                            <Chip 
                                              icon={<BabyChangingStationIcon style={{ fontSize: '12px' }} />}
                                              label="Baby Cot"
                                              size="small"
                                              color="success"
                                              sx={{ height: '20px', fontSize: '0.625rem' }}
                                            />
                                          )}
                                        </Box>
                                      </Box>

                                    ):(
                                      <div className="text-15 lh-12">
                                      Price available on request
                                    </div>
                                    )}
                                    
                                    
                                  </Box>
                                )}
                              </Box>
                            );
                          })}
                        </Box>
                      </Paper>
                    </Grid>
                  </Grid>
                </CardContent>
              </Card>
            ))}
          </Grid>

          {/* Price Information */}
          <Grid item xs={12}>
            <Card 
              elevation={3}
              sx={{ 
                borderRadius: '16px',
                overflow: 'hidden',
                transition: 'transform 0.3s ease, box-shadow 0.3s ease',
                '&:hover': {
                  transform: 'translateY(-4px)',
                  boxShadow: '0 8px 24px rgba(0,0,0,0.12)',
                },
                background: 'linear-gradient(135deg, rgba(53, 84, 209, 0.05) 0%, rgba(53, 84, 209, 0.1) 100%)',
              }}
            >
              <CardContent sx={{ p: 3 }}>
                <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 2, display: 'flex', alignItems: 'center' }}>
                  <CurrencyExchangeIcon sx={{ mr: 1, color: '#3554D1' }} />
                  Price Summary
                </Typography>
                
                <Grid container spacing={3}>
                  {/* Price Mode */}
                  <Grid item xs={12} md={6}>
                    <Paper 
                      elevation={1} 
                      sx={{ 
                        p: 2, 
                        borderRadius: '12px',
                      }}
                    >
                      <Typography variant="subtitle2" color="textSecondary" sx={{ mb: 1 }}>
                        Price Mode
                      </Typography>
                      
                      <Box sx={{ 
                        display: 'flex', 
                        alignItems: 'center',
                        p: 1,
                        borderRadius: '8px',
                        backgroundColor: 'rgba(53, 84, 209, 0.05)'
                      }}>
                        {booking.priceMode === "travClicks" ||
                        booking.priceMode === "travclicks" ||
                        booking.priceMode === "travclick" ? (
                          <Chip 
                            label="Travcliks"
                            color="primary"
                            sx={{ fontWeight: 'bold' }}
                          />
                        ) : booking.priceMode === "dmc" ? (
                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                            {DmcLogo && (
                              <Avatar
                                src={DmcLogo}
                                alt="DMC Logo"
                                sx={{ width: 32, height: 32 }}
                              />
                            )}
                            <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                              {`${DmcName || "DMC"}'s Mode`}
                            </Typography>
                          </Box>
                        ) : (
                          <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                            {capitalizeFirstLetter(booking.priceMode)}
                          </Typography>
                        )}
                      </Box>
                    </Paper>
                  </Grid>
                  
                  {/* Total Price */}
                  <Grid item xs={12} md={6}>
                  
                       <Paper 
                       elevation={1} 
                       sx={{ 
                         p: 2, 
                         borderRadius: '12px',
                         //backgroundColor: 'rgba(53, 84, 209, 0.05)'
                       }}
                     >
                        {PriceHide==="0"?(
                          <>
                       <Box sx={{ 
                         display: 'flex', 
                         justifyContent: 'space-between',
                         mb: 1.5
                       }}>
                         <Typography variant="subtitle2" color="textSecondary">
                           Total Price
                         </Typography>
                         <Chip
                           label={`Includes tax`}
                           size="small"
                           color="success"
                           variant="outlined"
                           sx={{ height: '24px', fontSize: '0.75rem' }}
                         />
                       </Box>
                       
                       <Box sx={{ 
                         display: 'flex', 
                         flexDirection: 'column',
                         gap: 1.5,
                         p: 1.5,
                         background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
                         borderRadius: '8px',
                         color: 'white',
                         boxShadow: '0 3px 8px rgba(53, 84, 209, 0.2)',
                       }}>
                         <Box sx={{ 
                           display: 'flex', 
                           justifyContent: 'space-between', 
                           alignItems: 'center',
                           mb: 1,
                           pb: 1,
                           borderBottom: '1px solid rgba(255, 255, 255, 0.2)'
                         }}>
                           <Typography 
                             sx={{ 
                               fontSize: '0.85rem', 
                               color: 'rgba(255, 255, 255, 0.9)',
                               fontWeight: 'medium'
                             }}
                           >
                             Tax Rates
                           </Typography>
                         </Box>
 
                         {/* Calculate price with tax */}
                         {(() => {
                           const basePrice = safeParse(booking.totalPrice) || 0;
                           
                           // Step 1: Ceiling the base prices
                           const sgdPrice = Math.ceil(basePrice);
                           const usdPrice = Math.ceil(basePrice * usdExchangeRate);
                           const convertedPrice = Math.ceil(basePrice * exchangeRate);
                           
                           // Step 2: Calculate tax amounts for all currencies
                           const currentTaxAmount = Math.ceil((convertedPrice * currentTax) / 100);
                           const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
                           const usdTaxAmount = Math.ceil((usdPrice * usdTax) / 100);
                           
                           // Step 3: Calculate grand totals for all currencies
                           const convertedGrandTotal = convertedPrice + currentTaxAmount;
                           const sgdGrandTotal = sgdPrice + sgdTaxAmount;
                           const usdGrandTotal = usdPrice + usdTaxAmount;
                           
                           return (
                             <>
                               {/* Current Currency Section */}
                               <Box sx={{ mb: 1.5 }}>
                                 <Typography sx={{ 
                                   fontSize: '0.85rem', 
                                   color: 'rgba(255, 255, 255, 0.8)',
                                   mb: 0.5,
                                   fontWeight: 'medium'
                                 }}>
                                   {currencyCode}
                                 </Typography>
                                 
                                 {/* Base Price */}
                                 <Box sx={{ 
                                   display: 'flex', 
                                   justifyContent: 'space-between', 
                                   alignItems: 'center',
                                   py: 0.5,
                                 }}>
                                   <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)' }}>
                                     Base Price (Without Tax)
                                   </Typography>
                                   <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                     {convertedPrice.toFixed(2)}
                                   </Typography>
                                 </Box>
                                 
                                 {/* Tax Amount */}
                                 <Box sx={{ 
                                   display: 'flex', 
                                   justifyContent: 'space-between', 
                                   alignItems: 'center',
                                   py: 0.5,
                                 }}>
                                   <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)' }}>
                                     Tax Amount ({currentTax}%)
                                   </Typography>
                                   <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                     {currentTaxAmount.toFixed(2)}
                                   </Typography>
                                 </Box>
                                 
                                 {/* Total With Tax */}
                                 <Box sx={{ 
                                   display: 'flex', 
                                   justifyContent: 'space-between', 
                                   alignItems: 'center',
                                   py: 0.5,
                                   mt: 0.5,
                                   borderTop: '1px dotted rgba(255, 255, 255, 0.3)',
                                   borderBottom: '1px solid rgba(255, 255, 255, 0.2)'
                                 }}>
                                   <Typography sx={{ fontWeight: 'bold', fontSize: '0.85rem', color: "white" }}>
                                     Total (With Tax)
                                   </Typography>
                                   <Typography sx={{ fontWeight: 'bold', fontSize: '0.95rem', color: "white" }}>
                                     {convertedGrandTotal.toFixed(2)}
                                   </Typography>
                                 </Box>
                               </Box>
                               
                               {/* Other currencies with tax included */}
                               <Box sx={{ mt: 1 }}>
                                 <Typography sx={{ 
                                   fontSize: '0.8rem', 
                                   color: 'rgba(255, 255, 255, 0.7)',
                                   mb: 0.5
                                 }}>
                                   Other Currencies
                                 </Typography>
                                 
                                 <Box sx={{ 
                                   display: 'flex', 
                                   justifyContent: 'space-between', 
                                   alignItems: 'center',
                                   py: 0.5,
                                 }}>
                                   <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                     <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                       {usdCurrencyCode}
                                     </Typography>
                                     <Typography 
                                       sx={{ 
                                         fontSize: '0.7rem', 
                                         color: 'rgba(255, 255, 255, 0.7)',
                                         ml: 0.5
                                       }}
                                     >
                                       ({usdTax}%)
                                     </Typography>
                                   </Box>
                                   <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                     {usdGrandTotal.toFixed(2)}
                                   </Typography>
                                 </Box>
                                 
                                 <Box sx={{ 
                                   display: 'flex', 
                                   justifyContent: 'space-between', 
                                   alignItems: 'center',
                                   py: 0.5,
                                 }}>
                                   <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                     <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                       SGD
                                     </Typography>
                                     <Typography 
                                       sx={{ 
                                         fontSize: '0.7rem', 
                                         color: 'rgba(255, 255, 255, 0.7)',
                                         ml: 0.5
                                       }}
                                     >
                                       ({sgdTax}%)
                                     </Typography>
                                   </Box>
                                   <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                     {sgdGrandTotal.toFixed(2)}
                                   </Typography>
                                 </Box>
                               </Box>
                             </>
                           );
                         })()}
                       </Box>
                       </>
                          ):(
                            <div className="text-15 lh-12">
                            Price available on request
                          </div>
      
                          )}
                     </Paper>

                 
                   
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>
          
          {/* Action Buttons */}
          <Grid item xs={12} sx={{ display: 'flex', justifyContent: 'center ', mt: 2 }}>
            <Button
              variant="contained"
              color="primary"
              onClick={onClose}
              sx={{ mr: 2, borderRadius: '8px', px: 3,
                background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
               }}
            >
              Close
            </Button>
            {/* <Button
              variant="contained"
              color="primary"
              sx={{ 
                borderRadius: '8px', 
                px: 3,
                background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
              }}
            >
              Print Details
            </Button> */}
          </Grid>
        </Grid>
      </DialogContent>
    </Dialog>
  );
};

export default HotelBookingModal;
