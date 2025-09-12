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
  Tooltip,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import CurrencyExchangeIcon from "@mui/icons-material/CurrencyExchange";
import PersonIcon from "@mui/icons-material/Person";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import GroupIcon from "@mui/icons-material/Group";
import DrinkIcon from "@mui/icons-material/LocalDrink";
import NoDrinksIcon from "@mui/icons-material/NoDrinks";
import CircleIcon from "@mui/icons-material/Circle";
import SquareIcon from "@mui/icons-material/Square";
import MenuBookIcon from "@mui/icons-material/MenuBook";
import RestaurantMenuIcon from "@mui/icons-material/RestaurantMenu";
import FoodBankIcon from "@mui/icons-material/FoodBank";
import FastfoodIcon from "@mui/icons-material/Fastfood";
import BrunchDiningIcon from "@mui/icons-material/BrunchDining";
import RamenDiningIcon from "@mui/icons-material/RamenDining";
import EmojiFoodBeverageIcon from "@mui/icons-material/EmojiFoodBeverage";
import SoupKitchenIcon from "@mui/icons-material/SoupKitchen";
import dayjs from "dayjs";
import { useSelector } from "react-redux";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../../../slice/dmc/dmcSlice"; // Import DMC slice selectors

// Add formatDate utility function
const formatDate = (date) => {
  if (!date) return "N/A";
  
  try {
    // Convert any date format to "Sun, 20 Apr'25"
    const dateObj = new Date(date);
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
  
  return date;
};

// Function to capitalize first letter
const capitalizeFirstLetter = (string) => {
  if (!string) return "N/A";
  const str = String(string);
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

// Get icon for meal type
const getMealTypeIcon = (mealType) => {
  const lowerCaseMealType = mealType?.toLowerCase() || '';
  
  if (lowerCaseMealType.includes('breakfast')) {
    return <EmojiFoodBeverageIcon />;
  } else if (lowerCaseMealType.includes('lunch')) {
    return <FastfoodIcon />;
  } else if (lowerCaseMealType.includes('dinner')) {
    return <RamenDiningIcon />;
  } else if (lowerCaseMealType.includes('brunch')) {
    return <BrunchDiningIcon />;
  } else {
    return <RestaurantMenuIcon />;
  }
};

const RestaurantBookingModal = ({ open, onClose, booking }) => {
  // Add state for showing more/less items
  const [expanded, setExpanded] = React.useState(false);
  const MAX_WORDS = 100; // Maximum words to show initially

  // Add early return if booking is not provided
  if (!open || !booking) {
    return null;
  }

  // Add selectors for currency conversion
  const priceMode = useSelector((state) => state.hotels.searchState.priceMode) || 'dmc';
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  const { bookings } = useSelector((state) => state.viewDetails);

  // Get tax percentages from auth slice like in index2.jsx
  const currentTax = useSelector((state) => state.auth.currentTax || 0);
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const usdTax = useSelector((state) => state.auth.usdTax || 0);

  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // Calculate guest counts with safety checks
  const adultCount = parseInt(booking?.adultCount) || 0;
  const childCount = parseInt(booking?.childCount) || 0;
  const totalGuests = adultCount + childCount;

  // Calculate price with tax
  const basePrice = parseFloat(booking?.totalPrice) || 0;
  
  // Add transport price calculation (for display purposes)
  let transportPrice = 0;
  let mealPrice = basePrice;
  
  // Calculate transport price if available
  if (booking?.transport) {
    const transport = booking.transport;
    
    // If shared transport, multiply price by total pax
    if (transport.transport_type === 'shared') {
      transportPrice = parseFloat(transport.price) * totalGuests;
    } else {
      // For private transport, use price as is
      transportPrice = parseFloat(transport.price);
    }
    
    // Adjust meal price (totalPrice already includes transport)
    mealPrice = basePrice - transportPrice;
  }
  
  // Ceiling the base prices
  const sgdPrice = Math.ceil(basePrice);
  const usdPrice = Math.ceil(basePrice * usdExchangeRate);
  const convertedPrice = Math.ceil(basePrice * exchangeRate);
  
  // Calculate tax amount based on ceiling prices and tax rates from auth slice
  const currentTaxAmount = Math.ceil((convertedPrice * currentTax) / 100);
  const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
  const usdTaxAmount = Math.ceil((usdPrice * usdTax) / 100);
  
  // Calculate grand totals
  const sgdGrandTotal = sgdPrice + sgdTaxAmount;
  const usdGrandTotal = usdPrice + usdTaxAmount;
  const convertedGrandTotal = convertedPrice + currentTaxAmount;
  
  // Check if current tax matches SGD or USD tax to hide respective portions
  const showSgdPortion = currentTax !== sgdTax;
  const showUsdPortion = currentTax !== usdTax;

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
          <RestaurantIcon fontSize="large" />
          <Typography variant="h6" sx={{ fontWeight: 'bold' }}>Restaurant Booking Details</Typography>
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
          {/* Restaurant Image and Basic Info Card */}
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
                    {/* Check all possible image paths */}
                    {(booking.restaurant?.masterImage || 
                      booking.restaurant?.image || 
                      booking.service_details?.master_image || 
                      booking.service_details?.image || 
                      bookings?.restaurant?.[0]?.service_details?.master_image) ? (
                      <CardMedia
                        component="img"
                        image={booking.restaurant?.masterImage || 
                              booking.restaurant?.image || 
                              booking.service_details?.master_image || 
                              booking.service_details?.image || 
                              bookings?.restaurant?.[0]?.service_details?.master_image}
                        alt={booking.restaurantName || booking.restaurant?.name || "Restaurant"}
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
                        <RestaurantIcon
                          sx={{ fontSize: 80, color: '#3554D1', opacity: 0.7, mb: 2 }}
                        />
                        <Typography variant="body2" color="textSecondary" align="center">
                          No restaurant image available
                        </Typography>
                      </Box>
                    )}
                  </Box>
                </Grid>
                <Grid item xs={12} md={8}>
                  <CardContent sx={{ p: 3 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                      <RestaurantIcon sx={{ color: '#3554D1', mr: 1, fontSize: 28 }} />
                      <Typography variant="h5" sx={{ fontWeight: 'bold' }}>
                        {booking.restaurantName || "N/A"}
                      </Typography>
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                      <LocationOnIcon sx={{ color: '#3554D1', mr: 1 }} />
                      <Typography variant="body1">
                        {`${booking.service_details?.city || bookings?.restaurant?.[0]?.service_details?.city || ""}, ${
                          booking.service_details?.country || bookings?.restaurant?.[0]?.service_details?.country || ""
                        }`}
                      </Typography>
                    </Box>
                    
                    <Grid container spacing={2}>
                      <Grid item xs={12} sm={6}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <CalendarTodayIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body2" color="textSecondary">
                            Booking Date
                          </Typography>
                        </Box>
                        <Typography variant="body1" sx={{ fontWeight: 'medium', ml: 4 }}>
                          {booking.bookingDate
                            ? formatDate(booking.bookingDate)
                            : "N/A"}
                        </Typography>
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <AccessTimeIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body2" color="textSecondary">
                            Visit Time
                          </Typography>
                        </Box>
                        <Typography variant="body1" sx={{ fontWeight: 'medium', ml: 4 }}>
                          {booking.visitTime || "N/A"}
                        </Typography>
                      </Grid>
                    </Grid>
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          </Grid>

          {/* Meal Information Card */}
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
                  <MenuBookIcon sx={{ color: '#3554D1', mr: 1, fontSize: 28 }} />
                  <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                    Meal Information
                  </Typography>
                </Box>
                
                <Grid container spacing={3}>
                  {/* Meal Type */}
                  <Grid item xs={12} md={6}>
                    <Paper 
                      elevation={1} 
                      sx={{ 
                        p: 2, 
                        borderRadius: '12px',
                        backgroundColor: 'rgba(53, 84, 209, 0.05)',
                        height: '100%'
                      }}
                    >
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        {getMealTypeIcon(booking.mealType)}
                        <Typography variant="subtitle1" sx={{ fontWeight: 'bold', ml: 1 }}>
                          Meal Type
                        </Typography>
                      </Box>
                      
                      <Divider sx={{ my: 1 }} />
                      
                      <Box sx={{ mt: 2 }}>
                        <Chip 
                          icon={getMealTypeIcon(booking.mealType)} 
                          label={capitalizeFirstLetter(booking.mealType)}
                          color="primary"
                          sx={{ fontWeight: 'bold', mb: 2 }}
                        />
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mt: 2 }}>
                          <FoodBankIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                            Specific Type: {capitalizeFirstLetter(booking.mealSpecificType)}
                          </Typography>
                        </Box>
                        
                        {booking.service_details?.cuisine && (
                          <Box sx={{ display: 'flex', alignItems: 'center', mt: 2 }}>
                            <SoupKitchenIcon sx={{ color: '#3554D1', mr: 1 }} />
                            <Typography variant="body1">
                              Cuisine: {booking.service_details?.cuisine || bookings?.restaurant?.[0]?.service_details?.cuisine || "N/A"}
                            </Typography>
                          </Box>
                        )}
                      </Box>
                    </Paper>
                  </Grid>
                  
                  {/* Guest Information */}
                  <Grid item xs={12} md={6}>
                    <Paper 
                      elevation={1} 
                      sx={{ 
                        p: 2, 
                        borderRadius: '12px',
                        height: '100%'
                      }}
                    >
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        <GroupIcon sx={{ color: '#3554D1', mr: 1 }} />
                        <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                          Guest Count: {totalGuests}
                        </Typography>
                      </Box>
                      
                      <Divider sx={{ my: 1 }} />
                      
                      <Box sx={{ 
                        display: 'flex', 
                        flexDirection: 'column',
                        gap: 1.5, 
                        mt: 2
                      }}>
                        {/* Adult count */}
                        {adultCount > 0 && (
                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                            <Chip
                              label="Adults"
                              sx={{
                                backgroundColor: 'rgba(53, 84, 209, 0.1)',
                                color: '#3554D1',
                                fontWeight: 'bold',
                                minWidth: '70px',
                              }}
                            />
                            <AvatarGroup max={10} sx={{ '& .MuiAvatar-root': { width: 30, height: 30, fontSize: '0.8rem' } }}>
                              {Array.from({ length: adultCount }).map((_, i) => (
                                <Avatar 
                                  key={`adult-avatar-${i}`} 
                                  sx={{ 
                                    bgcolor: '#3554D1',
                                    color: 'white',
                                  }}
                                >
                                  <PersonIcon fontSize="small" />
                                </Avatar>
                              ))}
                            </AvatarGroup>
                            <Typography variant="body2" color="text.secondary">
                              {adultCount} {adultCount === 1 ? 'Adult' : 'Adults'}
                            </Typography>
                          </Box>
                        )}
                        
                        {/* Child count */}
                        {childCount > 0 && (
                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                            <Chip
                              label="Children"
                              sx={{
                                backgroundColor: 'rgba(255, 152, 0, 0.1)',
                                color: '#FF9800',
                                fontWeight: 'bold',
                                minWidth: '70px',
                              }}
                            />
                            <AvatarGroup max={10} sx={{ '& .MuiAvatar-root': { width: 30, height: 30, fontSize: '0.8rem' } }}>
                              {Array.from({ length: childCount }).map((_, i) => (
                                <Avatar 
                                  key={`child-avatar-${i}`} 
                                  sx={{ 
                                    bgcolor: '#FF9800',
                                    color: 'white',
                                  }}
                                >
                                  <ChildCareIcon fontSize="small" />
                                </Avatar>
                              ))}
                            </AvatarGroup>
                            <Typography variant="body2" color="text.secondary">
                              {childCount} {childCount === 1 ? 'Child' : 'Children'}
                            </Typography>
                          </Box>
                        )}
                      </Box>
                    </Paper>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
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
                        {booking.priceTypes?.map((type, index) => (
                          <React.Fragment key={index}>
                            {index > 0 && <Divider orientation="vertical" flexItem sx={{ mx: 1 }} />}
                            {type === "travClicks" || type === "travclicks" ? (
                              <Chip 
                                label="Travclicks"
                                color="primary"
                                sx={{ fontWeight: 'bold' }}
                              />
                            ) : type === "dmc" ? (
                              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                                {dmcLogo && (
                                  <Avatar
                                    src={dmcLogo}
                                    alt={`${dmcCompanyName} Logo`}
                                    sx={{ width: 32, height: 32 }}
                                  />
                                )}
                                <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                                  {`${dmcCompanyName}'s Mode`}
                                </Typography>
                              </Box>
                            ) : (
                              <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                                {capitalizeFirstLetter(type)}
                              </Typography>
                            )}
                          </React.Fragment>
                        )) || "N/A"}
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
                      }}
                    >
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
                      
                      {PriceHide === "0" ? (
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
                                Meal Price
                              </Typography>
                              <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                {Math.ceil(mealPrice * exchangeRate)}
                              </Typography>
                            </Box>
                            
                            {/* Transport Price if available */}
                            {transportPrice > 0 && (
                              <Box sx={{ 
                                display: 'flex', 
                                justifyContent: 'space-between', 
                                alignItems: 'center',
                                py: 0.5,
                              }}>
                                <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)' }}>
                                  Transport {booking.transport?.vehicle_name && `(${booking.transport.vehicle_name})`} {booking.transport?.transport_type && `- ${capitalizeFirstLetter(booking.transport.transport_type)}`}
                                </Typography>
                                <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                  {Math.ceil(transportPrice * exchangeRate)}
                                </Typography>
                              </Box>
                            )}
                            
                            {/* Base Price (Subtotal) */}
                            <Box sx={{ 
                              display: 'flex', 
                              justifyContent: 'space-between', 
                              alignItems: 'center',
                              py: 0.5,
                              borderTop: '1px dotted rgba(255, 255, 255, 0.3)',
                              mt: 0.5,
                            }}>
                              <Typography sx={{ fontSize: '0.8rem', color: 'rgba(255, 255, 255, 0.7)' }}>
                                Base Price (Subtotal)
                              </Typography>
                              <Typography sx={{ fontSize: '0.85rem', color: 'rgba(255, 255, 255, 0.9)' }}>
                                {convertedPrice}
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
                                {currentTaxAmount}
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
                                {convertedGrandTotal}
                              </Typography>
                            </Box>
                          </Box>
                          
                          {/* Other currencies with tax included */}
                          {(showUsdPortion || true) && (
                            <Box sx={{ mt: 1 }}>
                              <Typography sx={{ 
                                fontSize: '0.8rem', 
                                color: 'rgba(255, 255, 255, 0.7)',
                                mb: 0.5
                              }}>
                                Other Currencies
                              </Typography>
                              
                              {showUsdPortion && (
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
                                    {usdGrandTotal}
                                  </Typography>
                                </Box>
                              )}
                              
                              {/* Always show SGD prices */}
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
                                  {sgdGrandTotal}
                                </Typography>
                              </Box>
                            </Box>
                          )}
                        </Box>
                      ) : (
                        <Box 
                          sx={{
                            p: 3,
                            textAlign: 'center',
                            color: 'gray',
                            fontSize: '1rem',
                            fontWeight: 'bold',
                          }}
                        >
                          Price is Hidden.
                        </Box>
                      )}
                    </Paper>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>

          {/* Meal Details */}
          {booking.MealDescription && booking.MealDescription.length > 0 && (
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
                }}
              >
                <CardContent sx={{ p: 3 }}>
                  <Box sx={{ 
                    display: 'flex', 
                    alignItems: 'center', 
                    mb: 2,
                    gap: 1
                  }}>
                    <RestaurantMenuIcon sx={{ color: '#3554D1', fontSize: 28 }} />
                    <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                      Meal Details
                    </Typography>
                    
                    {/* Only show these indicators if it's not a buffet */}
                    {booking.mealSpecificType !== "Buffet" && (
                      <>
                        {booking.mealSpecificType === "Set Menu" && booking.MealDescription[0]?.item_type && (
                          <Box sx={{ display: 'flex', alignItems: 'center', ml: 2 }}>
                            <Tooltip title={booking.MealDescription[0].item_type === "Veg" ? "Vegetarian" : "Non-Vegetarian"} arrow placement="top">
                              <div style={{ position: "relative", display: "inline-flex" }}>
                                <SquareIcon
                                  sx={{
                                    color: booking.MealDescription[0].item_type === "Veg" ? "#2ecc71" : "#e74c3c",
                                    fontSize: 16,
                                  }}
                                />
                                <CircleIcon
                                  sx={{
                                    color: "#fff",
                                    fontSize: 8,
                                    position: "absolute",
                                    top: "50%",
                                    left: "50%",
                                    transform: "translate(-50%, -50%)",
                                  }}
                                />
                              </div>
                            </Tooltip>
                            <Typography variant="body2" sx={{ ml: 0.5, fontWeight: 'medium' }}>
                              {booking.MealDescription[0].item_type}
                            </Typography>
                          </Box>
                        )}
                        
                        {booking.MealDescription[0]?.category && (
                          <Box sx={{ display: 'flex', alignItems: 'center', ml: 1 }}>
                            {booking.MealDescription[0].category === "Alcoholic" ? (
                              <Tooltip title="Alcoholic Beverage" arrow placement="top">
                                <DrinkIcon sx={{ color: "#e74c3c", fontSize: 20 }} />
                              </Tooltip>
                            ) : booking.MealDescription[0].category === "Non Alcoholic" ? (
                              <Tooltip title="Non-Alcoholic Beverage" arrow placement="top">
                                <NoDrinksIcon sx={{ color: "#2ecc71", fontSize: 20 }} />
                              </Tooltip>
                            ) : null}
                            <Typography variant="body2" sx={{ ml: 0.5, fontWeight: 'medium' }}>
                              {booking.MealDescription[0].category}
                            </Typography>
                          </Box>
                        )}
                      </>
                    )}
                  </Box>
                  
                  <Divider sx={{ mb: 2 }} />
                  
                  <Box sx={{ mt: 2 }}>
                    <Grid container spacing={2}>
                      {booking.MealDescription.map((meal, index) => {
                        const plainText = meal.name?.replace(/<[^>]+>/g, "") || "";
                        const words = plainText.trim().split(/\s+/);
                        const shouldTruncate = words.length > MAX_WORDS;
                        const truncatedText = shouldTruncate
                          ? words.slice(0, MAX_WORDS).join(" ") + "..."
                          : plainText;

                        return (
                          <Grid item xs={12} sm={6} key={index}>
                            <Paper 
                              elevation={1} 
                              sx={{ 
                                p: 2, 
                                borderRadius: '12px',
                                backgroundColor: index % 2 === 0 ? 'rgba(53, 84, 209, 0.05)' : 'transparent',
                                height: '100%'
                              }}
                            >
                              <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 1, mb: 1 }}>
                                {/* Only show these indicators if it's not a buffet */}
                                {booking.mealSpecificType !== "Buffet" && (
                                  <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', mt: 0.5 }}>
                                    {meal.item_type && (
                                      <Tooltip title={meal.item_type === "Veg" ? "Vegetarian" : "Non-Vegetarian"} arrow placement="top">
                                        <div style={{ position: "relative", display: "inline-flex" }}>
                                          <SquareIcon
                                            sx={{
                                              color: meal.item_type === "Veg" ? "#2ecc71" : "#e74c3c",
                                              fontSize: 16,
                                            }}
                                          />
                                          <CircleIcon
                                            sx={{
                                              color: "#fff",
                                              fontSize: 8,
                                              position: "absolute",
                                              top: "50%",
                                              left: "50%",
                                              transform: "translate(-50%, -50%)",
                                            }}
                                          />
                                        </div>
                                      </Tooltip>
                                    )}
                                    {meal.category && (
                                      meal.category === "Alcoholic" ? (
                                        <Tooltip title="Alcoholic Beverage" arrow placement="top">
                                          <DrinkIcon sx={{ color: "#e74c3c", fontSize: 16, mt: 0.5 }} />
                                        </Tooltip>
                                      ) : meal.category === "Non Alcoholic" ? (
                                        <Tooltip title="Non-Alcoholic Beverage" arrow placement="top">
                                          <NoDrinksIcon sx={{ color: "#2ecc71", fontSize: 16, mt: 0.5 }} />
                                        </Tooltip>
                                      ) : null
                                    )}
                                  </Box>
                                )}
                                
                                <Box>
                                  <Typography variant="subtitle2" sx={{ fontWeight: 'bold', color: '#3554D1' }}>
                                    {meal.item_name}
                                  </Typography>
                                  <Typography variant="body2">
                                    {expanded || !shouldTruncate ? meal.name : truncatedText}
                                  </Typography>
                                  {shouldTruncate && (
                                    <Button
                                      onClick={() => setExpanded(!expanded)}
                                      sx={{
                                        color: '#3554D1',
                                        textTransform: 'none',
                                        fontSize: '0.875rem',
                                        p: 0,
                                        mt: 0.5,
                                        '&:hover': {
                                          backgroundColor: 'transparent',
                                          textDecoration: 'underline',
                                        },
                                      }}
                                    >
                                      {expanded ? "See less" : "See more"}
                                    </Button>
                                  )}
                                </Box>
                              </Box>
                            </Paper>
                          </Grid>
                        );
                      })}
                    </Grid>
                  </Box>
                </CardContent>
              </Card>
            </Grid>
          )}
          
          {/* Action Buttons */}
          <Grid item xs={12} sx={{ display: 'flex', justifyContent: 'center', mt: 2 }}>
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

export default RestaurantBookingModal;
