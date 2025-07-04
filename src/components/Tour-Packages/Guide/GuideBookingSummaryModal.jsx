import React, { useMemo } from 'react';
import {
  Typography,
  Box,
  Modal,
  Paper,
  Stack,
  Divider,
  IconButton,
  Card,
  CardContent,
  CardMedia,
  Grid,
  Button,
  styled
} from '@mui/material';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import CloseIcon from '@mui/icons-material/Close';
import CategoryIcon from '@mui/icons-material/Category';
import TimerIcon from '@mui/icons-material/Timer';
import TranslateIcon from '@mui/icons-material/Translate';
import WorkIcon from '@mui/icons-material/Work';
import GroupIcon from '@mui/icons-material/Group';
import NightsStayIcon from '@mui/icons-material/NightsStay';
import WbSunnyIcon from '@mui/icons-material/WbSunny';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import { useSelector } from 'react-redux';

// Styled components
const StyledModal = styled(Modal)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
}));

const ModalContent = styled(Paper)(({ theme }) => ({
  position: 'relative',
  width: '90%',
  maxWidth: 800,
  maxHeight: '90vh',
  overflow: 'auto',
  padding: theme.spacing(3),
  backgroundColor: theme.palette.background.paper,
  borderRadius: theme.spacing(2),
  boxShadow: theme.shadows[5],
}));

const SummarySection = styled(Box)(({ theme }) => ({
  marginBottom: theme.spacing(3),
}));

const DetailRow = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  gap: theme.spacing(1),
  marginBottom: theme.spacing(1),
}));

const PriceCard = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(2),
  height: '100%',
  display: 'flex',
  flexDirection: 'column',
  gap: theme.spacing(1)
}));

const PriceRow = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  padding: theme.spacing(1, 0),
  borderBottom: `1px solid ${theme.palette.divider}`,
  '&:last-child': {
    borderBottom: 'none',
    paddingBottom: 0
  }
}));

const GuideBookingSummaryModal = ({ open, onClose, bookingData, bookingIndex, guideDetails }) => {
  const currencyCode = useSelector((state) => state.auth.currencyCode) || "SGD";
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate) || 1;
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);
  const dmcName = useSelector((state) => state.auth.DmcName) || "DMC";
  const guideDetailsFromState = useSelector((state) => state.tourguide.guideDetails);

  // Pre-calculate formatted prices to avoid recalculations during render
  const formattedPriceContent = useMemo(() => {
    if (!bookingData?.priceBreakdown) return (
      <Stack spacing={0.5}>
        <Typography variant="h5" color="primary" sx={{ fontWeight: 600 }}>
          {currencyCode} 0.00
        </Typography>
      </Stack>
    );
    
    const { basePrice, nightSurcharge, totalPrice, nightHours, dayHours } = bookingData.priceBreakdown;
    
    const mainPrice = Math.ceil(totalPrice * exchangeRate);
    const usdPrice = Math.ceil(totalPrice * usdExchangeRate);
    const sgdPrice = Math.ceil(totalPrice);
    
    const formattedBasePrice = Math.ceil(basePrice * exchangeRate);
    const formattedNightSurcharge = Math.ceil(nightSurcharge * exchangeRate);
    
    // Only show breakdown if there are night hours
    const hasNightHours = nightHours > 0;

    return (
      <Stack spacing={1}>
        {hasNightHours && (
          <>
            <PriceRow>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <WbSunnyIcon sx={{ mr: 1, color: '#1E40AF', fontSize: 18 }} />
                <Typography variant="body2">
                  Base Price ({dayHours + nightHours} hours)
                </Typography>
              </Box>
              <Typography variant="body2">
                {currencyCode} {formattedBasePrice}
              </Typography>
            </PriceRow>
            
            <PriceRow>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <NightsStayIcon sx={{ mr: 1, color: '#B45309', fontSize: 18 }} />
                <Typography variant="body2">
                  Night Surcharge ({nightHours} hours)
                </Typography>
              </Box>
              <Typography variant="body2" color="#B45309">
                + {currencyCode} {formattedNightSurcharge}
              </Typography>
            </PriceRow>
            
            <Box sx={{ height: 8 }} />
          </>
        )}
        
        <Typography variant="h5" color="primary" sx={{ fontWeight: 600 }}>
          {currencyCode} {mainPrice}
        </Typography>
        
        {currencyCode !== 'USD' && (
          <Typography variant="body2" color="text.secondary">
            USD {usdPrice}
          </Typography>
        )}
        {currencyCode !== 'SGD' && (
          <Typography variant="body2" color="text.secondary">
            SGD {sgdPrice}
          </Typography>
        )}
      </Stack>
    );
  }, [bookingData, currencyCode, exchangeRate, usdExchangeRate]);

  // Format night hours for display
  const formattedNightHours = useMemo(() => {
    const formatTimeStr = (timeStr) => {
      if (!timeStr) return '';
      if (timeStr.includes("AM") || timeStr.includes("PM")) {
        return timeStr;
      }
      // Convert 24h format to AM/PM
      const [hours, minutes] = timeStr.split(":");
      const hour = parseInt(hours, 10);
      const min = minutes || "00";
      const period = hour >= 12 ? "PM" : "AM";
      const displayHour = hour % 12 === 0 ? 12 : hour % 12;
      return `${displayHour}:${min} ${period}`;
    };

    if (!guideDetails) return '';
    
    const nightStartTime = guideDetails.night_start_time || "21:00";
    const nightEndTime = guideDetails.night_end_time || "00:00";

    const startFormatted = formatTimeStr(nightStartTime);
    const endFormatted = formatTimeStr(nightEndTime);

    return `${startFormatted} - ${endFormatted}`;
  }, [guideDetails]);

  // If we don't have the required data, render nothing or a placeholder
  if (!bookingData || !guideDetails) {
    return null;
  }

  // Calculate end time based on pickup time and duration
  const calculateEndTime = (startTime, hours) => {
    if (!startTime || !hours) return '';
    
    const parseTime = (timeStr) => {
      const [timePart, period] = timeStr.split(" ");
      let [hour, minute] = timePart.split(":").map(num => parseInt(num, 10));
      if (period === "PM" && hour !== 12) hour += 12;
      else if (period === "AM" && hour === 12) hour = 0;
      return { hour, minute };
    };
    
    const formatTime = (hour, minute) => {
      const h = hour % 12 === 0 ? 12 : hour % 12;
      const period = hour >= 12 ? "PM" : "AM";
      return `${h.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')} ${period}`;
    };
    
    const { hour, minute } = parseTime(startTime);
    const newHour = (hour + parseInt(hours, 10)) % 24;
    
    return formatTime(newHour, minute);
  };
  
  const endTime = calculateEndTime(bookingData.pickUpTime, bookingData.hourlyPackage);
  const { nightHours, dayHours } = bookingData.priceBreakdown || { nightHours: 0, dayHours: 0 };
  const totalHours = parseInt(bookingData.hourlyPackage, 10) || 0;

  return (
    <StyledModal
      open={open}
      onClose={onClose}
      aria-labelledby="guide-booking-summary-modal"
    >
      <ModalContent>
        <IconButton
          onClick={onClose}
          sx={{
            position: 'absolute',
            right: 8,
            top: 8,
            color: 'grey.500',
          }}
        >
          <CloseIcon />
        </IconButton>

        <Stack spacing={3}>
          {/* Header */}
          <Box>
            <Typography variant="h5" component="h2" gutterBottom sx={{ fontWeight: 600 }}>
              Guide Booking Summary
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <Typography variant="subtitle1" color="text.secondary">
                Booking #{bookingIndex + 1}
              </Typography>
              <Typography
                component="span"
                sx={{
                  px: 1.5,
                  py: 0.5,
                  bgcolor: 'primary.main',
                  color: 'white',
                  borderRadius: 1,
                  fontSize: '0.875rem',
                  fontWeight: 500,
                  ml: 1
                }}
              >
                {guideDetails.mode === 'dmc' ? 'DMC Guide' : 'Travclicks Guide'}
              </Typography>
            </Box>
          </Box>

          <Divider />

          {/* Guide Details */}
          <SummarySection>
            <Card sx={{ display: 'flex', mb: 2 }}>
              <CardMedia
                component="img"
                sx={{ width: 200, height: 200, objectFit: 'cover' }}
                image={guideDetails.image || '/placeholder-guide.jpg'}
                alt={guideDetails.guide_name}
              />
              <Box sx={{ display: 'flex', flexDirection: 'column', flex: 1 }}>
                <CardContent>
                  <Typography variant="h6" gutterBottom>
                    {guideDetails.guide_name}
                  </Typography>
                  <DetailRow>
                    <WorkIcon color="primary" />
                    <Typography>
                      Experience: {guideDetails.experience_years} years
                    </Typography>
                  </DetailRow>
                  <DetailRow>
                    <TranslateIcon color="primary" />
                    <Typography>
                      Languages: {guideDetails.languages?.map(lang => `${lang.language} (${lang.proficiency})`).join(', ')}
                    </Typography>
                  </DetailRow>
                </CardContent>
              </Box>
            </Card>
          </SummarySection>

          {/* Booking Details */}
          <SummarySection>
            <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
              <TimerIcon sx={{ mr: 1 }} />
              Booking Information
            </Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Pick Up Time
                  </Typography>
                  <Typography variant="body1">
                    {bookingData.pickUpTime}
                  </Typography>
                  <Typography variant="body1">
                    End Time: {endTime}
                  </Typography>
                  {formattedNightHours && (
                    <Box sx={{ mt: 1, pt: 1, borderTop: '1px dashed', borderColor: 'divider' }}>
                      <Typography variant="caption" color="text.secondary" sx={{ display: 'flex', alignItems: 'center' }}>
                        <NightsStayIcon sx={{ mr: 0.5, fontSize: 14, color: '#B45309' }} />
                        Night hours: {formattedNightHours}
                      </Typography>
                    </Box>
                  )}
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Duration Package
                  </Typography>
                  <Typography variant="body1">
                    {bookingData.hourlyPackage} Hours
                  </Typography>
                  {(nightHours > 0 || dayHours > 0) && (
                    <Box sx={{ mt: 1, display: 'flex', flexWrap: 'wrap', gap: 1 }}>
                      {dayHours > 0 && (
                        <Box sx={{ 
                          display: 'flex', 
                          alignItems: 'center', 
                          bgcolor: 'rgba(219, 234, 254, 0.5)', 
                          px: 1, 
                          py: 0.5, 
                          borderRadius: 1,
                          fontSize: '0.75rem'
                        }}>
                          <WbSunnyIcon sx={{ mr: 0.5, fontSize: 14, color: '#1E40AF' }} />
                          {dayHours} day hours
                        </Box>
                      )}
                      {nightHours > 0 && (
                        <Box sx={{ 
                          display: 'flex', 
                          alignItems: 'center',
                          bgcolor: 'rgba(254, 215, 215, 0.5)',
                          px: 1, 
                          py: 0.5, 
                          borderRadius: 1,
                          fontSize: '0.75rem',
                          color: '#B45309'
                        }}>
                          <NightsStayIcon sx={{ mr: 0.5, fontSize: 14, color: '#B45309' }} />
                          {nightHours} night hours
                        </Box>
                      )}
                    </Box>
                  )}
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Booking Mode
                  </Typography>
                  {guideDetails.mode === 'dmc' && (
                    <Box sx={{ 
                      display: 'flex', 
                      alignItems: 'center', 
                      gap: 1,
                      mt: 1,
                      pt: 1,
                      borderTop: '1px solid',
                      borderColor: 'divider'
                    }}>
                      {dmcLogo && (
                        <Box
                          component="img"
                          src={dmcLogo}
                          alt={dmcName}
                          sx={{
                            width: 40,
                            height: 40,
                            objectFit: 'contain',
                            borderRadius: '4px'
                          }}
                        />
                      )}
                      <Typography
                        sx={{
                          fontSize: '1rem',
                          fontWeight: 500,
                          color: 'text.primary'
                        }}
                      >
                        {dmcName}'s Guide
                      </Typography>
                    </Box>
                  )}
                </PriceCard>
              </Grid>
            </Grid>
          </SummarySection>

          {/* Guest Details and Total Price */}
          <SummarySection>
            <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
              <GroupIcon sx={{ mr: 1 }} />
              Guest Details & Price
            </Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={7}>
                <PriceCard>
                  <Box sx={{ display: 'flex', gap: 3 }}>
                    <Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        <PersonIcon sx={{ mr: 1, color: 'primary.main' }} />
                        <Typography>
                          Adults: {bookingData.pax.Adults}
                        </Typography>
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <ChildCareIcon sx={{ mr: 1, color: 'primary.main' }} />
                        <Typography>
                          Children: {bookingData.pax.Children}
                        </Typography>
                      </Box>
                    </Box>
                  </Box>
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={5}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                    <AttachMoneyIcon sx={{ mr: 0.5 }} />
                    Price Breakdown
                  </Typography>
                  {formattedPriceContent}
                  {guideDetails.tax_percentage && (
                    <Typography variant="caption" color="text.secondary">
                      *Prices are subject to {guideDetails.tax_percentage}% tax
                    </Typography>
                  )}
                </PriceCard>
              </Grid>
            </Grid>
          </SummarySection>

          {/* Action Buttons */}
          <Box sx={{ display: 'flex', gap: 2, justifyContent: 'flex-end', mt: 2 }}>
            <Button variant="outlined" onClick={onClose}>
              Close
            </Button>
          </Box>
        </Stack>
      </ModalContent>
    </StyledModal>
  );
};

export default GuideBookingSummaryModal; 

