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
import PersonIcon from "@mui/icons-material/Person";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import CurrencyExchangeIcon from "@mui/icons-material/CurrencyExchange";
import ChildCareIcon from "@mui/icons-material/ChildCare";
import GroupIcon from "@mui/icons-material/Group";
import TodayIcon from "@mui/icons-material/Today";
import ScheduleIcon from "@mui/icons-material/Schedule";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import LanguageIcon from "@mui/icons-material/Language";
import InfoIcon from "@mui/icons-material/Info";
import SubtitlesIcon from "@mui/icons-material/Subtitles";
import TranslateIcon from "@mui/icons-material/Translate";
import HourglassBottomIcon from "@mui/icons-material/HourglassBottom";
import LocalPhoneIcon from "@mui/icons-material/LocalPhone";
import EmailIcon from "@mui/icons-material/Email";
import BadgeIcon from "@mui/icons-material/Badge";
import dayjs from "dayjs";
import { useSelector } from "react-redux";

// Function to capitalize first letter
const capitalizeFirstLetter = (string) => {
  if (!string) return "N/A";
  const str = String(string);
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

const GuideBookingModal = ({ open, onClose, booking }) => {
  // Add selectors for currency conversion
  const priceMode = useSelector((state) => state.hotels.searchState.priceMode) || 'dmc';
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  
  // Calculate converted prices
  const conversionRate = priceMode === "dmc" ? exchangeRate : usdExchangeRate;
  const convertedPrice = (booking?.totalPrice || 0) * conversionRate;
  
  // Calculate guest counts
  const adultCount = booking.adultCount || 0;
  const childCount = booking.childCount || 0;
  const totalGuests = adultCount + childCount;

  if (!booking) return null;

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
          <PersonIcon fontSize="large" />
          <Typography variant="h6" sx={{ fontWeight: 'bold' }}>Guide Booking Details</Typography>
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
          {/* Guide Image and Basic Info Card */}
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
                  {booking.service_details?.master_image && (
                    <CardMedia
                      component="img"
                      height="200"
                      image={booking.service_details?.master_image}
                      alt={booking.guideName}
                      sx={{
                        objectFit: "cover",
                        height: '100%',
                      }}
                    />
                  )}
                </Grid>
                <Grid item xs={12} md={8}>
                  <CardContent sx={{ p: 3 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                      <BadgeIcon sx={{ color: '#3554D1', mr: 1, fontSize: 28 }} />
                      <Typography variant="h5" sx={{ fontWeight: 'bold' }}>
                        {booking.guideName || "N/A"}
                      </Typography>
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                      <LocationOnIcon sx={{ color: '#3554D1', mr: 1 }} />
                      <Typography variant="body1">
                        {`${booking.service_details?.city || ""}, ${booking.service_details?.country || ""}`}
                      </Typography>
                    </Box>
                    
                    <Grid container spacing={2}>
                      <Grid item xs={12} sm={6}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <SubtitlesIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body2" color="textSecondary">
                            Guide Type
                          </Typography>
                        </Box>
                        <Typography variant="body1" sx={{ fontWeight: 'medium', ml: 4 }}>
                          {capitalizeFirstLetter(booking.guideType) || "N/A"}
                        </Typography>
                      </Grid>
                      <Grid item xs={12} sm={6}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <TranslateIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body2" color="textSecondary">
                            Languages
                          </Typography>
                        </Box>
                        <Box sx={{ ml: 4, display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
                          {booking.service_details?.languages && booking.service_details.languages.map((language, index) => (
                            <Chip
                              key={index}
                              label={language}
                              size="small"
                              color="primary"
                              variant="outlined"
                              sx={{ fontWeight: 'medium', mb: 0.5 }}
                            />
                          ))}
                        </Box>
                      </Grid>
                    </Grid>
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          </Grid>

          {/* Booking Information Card */}
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
                  <ScheduleIcon sx={{ color: '#3554D1', mr: 1, fontSize: 28 }} />
                  <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                    Booking Schedule
                  </Typography>
                </Box>
                
                <Grid container spacing={3}>
                  {/* Booking Dates */}
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
                        <EventAvailableIcon sx={{ color: '#3554D1', mr: 1 }} />
                        <Typography variant="subtitle1" sx={{ fontWeight: 'bold', ml: 1 }}>
                          Date Range
                        </Typography>
                      </Box>
                      
                      <Divider sx={{ my: 1 }} />
                      
                      <Box sx={{ mt: 2 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mt: 2 }}>
                          <CalendarTodayIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                            Start Date: {booking.startDate 
                              ? dayjs(booking.startDate).format("DD MMM YYYY") 
                              : "N/A"}
                          </Typography>
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mt: 2 }}>
                          <CalendarTodayIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body1">
                            End Date: {booking.endDate 
                              ? dayjs(booking.endDate).format("DD MMM YYYY") 
                              : "N/A"}
                          </Typography>
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mt: 2 }}>
                          <HourglassBottomIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body1">
                            Duration: {booking.days || 0} days
                          </Typography>
                        </Box>
                      </Box>
                    </Paper>
                  </Grid>
                  
                  {/* Daily Hours */}
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
                        <AccessTimeIcon sx={{ color: '#3554D1', mr: 1 }} />
                        <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                          Daily Hours
                        </Typography>
                      </Box>
                      
                      <Divider sx={{ my: 1 }} />
                      
                      <Box sx={{ mt: 2 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', mt: 2 }}>
                          <AccessTimeIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                            Start Time: {booking.service_details?.startTime || "N/A"}
                          </Typography>
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mt: 2 }}>
                          <AccessTimeIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body1">
                            End Time: {booking.service_details?.endTime || "N/A"}
                          </Typography>
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mt: 2 }}>
                          <HourglassBottomIcon sx={{ color: '#3554D1', mr: 1 }} />
                          <Typography variant="body1">
                            Hours Per Day: {booking.service_details?.hoursPerDay || "N/A"}
                          </Typography>
                        </Box>
                      </Box>
                    </Paper>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>

          {/* Guest Information */}
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
                    Guest Information
                  </Typography>
                </Box>
                
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
              </CardContent>
            </Card>
          </Grid>

          {/* Guide Contact Information */}
          {(booking.service_details?.phone || booking.service_details?.email) && (
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
                    <InfoIcon sx={{ color: '#3554D1', mr: 1, fontSize: 28 }} />
                    <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                      Contact Information
                    </Typography>
                  </Box>
                  
                  <Paper 
                    elevation={1} 
                    sx={{ 
                      p: 2, 
                      borderRadius: '12px',
                      backgroundColor: 'rgba(53, 84, 209, 0.05)',
                    }}
                  >
                    <Grid container spacing={2}>
                      {booking.service_details?.phone && (
                        <Grid item xs={12} sm={6}>
                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                            <LocalPhoneIcon sx={{ color: '#3554D1' }} />
                            <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                              {booking.service_details.phone}
                            </Typography>
                          </Box>
                        </Grid>
                      )}
                      
                      {booking.service_details?.email && (
                        <Grid item xs={12} sm={6}>
                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                            <EmailIcon sx={{ color: '#3554D1' }} />
                            <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                              {booking.service_details.email}
                            </Typography>
                          </Box>
                        </Grid>
                      )}
                    </Grid>
                  </Paper>
                </CardContent>
              </Card>
            </Grid>
          )}

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
                                label="Marketplace"
                                color="primary"
                                sx={{ fontWeight: 'bold' }}
                              />
                            ) : type === "dmc" ? (
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
                      <Typography variant="subtitle2" color="textSecondary" sx={{ mb: 1 }}>
                        Total Price
                      </Typography>
                      
                      <Box sx={{ 
                        display: 'flex', 
                        flexDirection: 'column',
                        gap: 0.5
                      }}>
                        <Chip 
                          icon={<CurrencyExchangeIcon />}
                          label={`${currencyCode} ${convertedPrice.toFixed(2)}`}
                          color="primary"
                          sx={{ fontWeight: 'bold', fontSize: '1rem', py: 1 }}
                        />
                        <Chip 
                          label={`${usdCurrencyCode} ${((booking?.totalPrice || 0) * usdExchangeRate).toFixed(2)}`}
                          variant="outlined"
                          size="small"
                          sx={{ fontWeight: 'medium' }}
                        />
                        <Chip 
                          label={`SGD ${(booking?.totalPrice || 0).toFixed(2)}`}
                          variant="outlined"
                          size="small"
                          sx={{ fontWeight: 'medium' }}
                        />
                      </Box>
                    </Paper>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>
          
          {/* Action Buttons */}
          <Grid item xs={12} sx={{ display: 'flex', justifyContent: 'flex-end', mt: 2 }}>
            <Button
              variant="outlined"
              color="primary"
              onClick={onClose}
              sx={{ mr: 2, borderRadius: '8px', px: 3 }}
            >
              Close
            </Button>
            <Button
              variant="contained"
              color="primary"
              sx={{ 
                borderRadius: '8px', 
                px: 3,
                background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
              }}
            >
              Print Details
            </Button>
          </Grid>
        </Grid>
      </DialogContent>
    </Dialog>
  );
};

export default GuideBookingModal; 