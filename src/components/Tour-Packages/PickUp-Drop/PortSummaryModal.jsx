import React from 'react';
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
  styled,
  Alert,
  CircularProgress
} from '@mui/material';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import CloseIcon from '@mui/icons-material/Close';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import GroupIcon from '@mui/icons-material/Group';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import MyLocationIcon from '@mui/icons-material/MyLocation';
import LocationCityIcon from '@mui/icons-material/LocationCity';
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
  padding: theme.spacing(2),
  backgroundColor: theme.palette.background.paper,
  borderRadius: theme.spacing(1.5),
  boxShadow: theme.shadows[5],
}));

const SummarySection = styled(Box)(({ theme }) => ({
  marginBottom: theme.spacing(2),
}));

const DetailRow = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  gap: theme.spacing(1),
  marginBottom: theme.spacing(0.5),
}));

const PortSummaryModal = ({ 
  open, 
  onClose, 
  bookingData,
  portType
}) => {
  const currencyCode = useSelector((state) => state.auth.currencyCode) || "SGD";
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate) || 1;
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);
  const dmcName = useSelector((state) => state.auth.DmcName) || "DMC";
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // Determine color theme based on port type
  const getColorTheme = () => {
    if (portType === "Entry Port") {
      return {
        primary: '#3b82f6',
        secondary: '#1e40af',
        gradient: 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)',
        light: 'rgba(59, 130, 246, 0.1)',
        name: 'Entry Port'
      };
    } else if (portType === "Exit Port") {
      return {
        primary: '#10b981',
        secondary: '#059669',
        gradient: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
        light: 'rgba(16, 185, 129, 0.1)',
        name: 'Exit Port'
      };
    }
    return {
      primary: '#3b82f6',
      secondary: '#1e40af',
      gradient: 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)',
      light: 'rgba(59, 130, 246, 0.1)',
      name: 'Port Service'
    };
  };

  const colorTheme = getColorTheme();

  if (!open) return null;
  
  if (!bookingData) {
    return (
      <StyledModal
        open={open}
        onClose={onClose}
        aria-labelledby="port-summary-modal-loading"
        sx={{ zIndex: zone_on ? 1000 : 1300 }}
      >
        <ModalContent sx={{ maxWidth: 400, textAlign: 'center', py: 3 }}>
          <IconButton
            onClick={onClose}
            sx={{ position: 'absolute', right: 8, top: 8, color: 'grey.500' }}
          >
            <CloseIcon />
          </IconButton>
          <Alert severity="warning" sx={{ mb: 2 }}>
            Complete booking information is not available
          </Alert>
          <Typography variant="body2" sx={{ mb: 2 }}>
            Please ensure all fields are filled to view the complete summary.
          </Typography>
          <Button variant="outlined" onClick={onClose}>Close</Button>
        </ModalContent>
      </StyledModal>
    );
  }

  // Format the price with currency conversions
  const formatPrice = (price) => {
    if (!price && price !== 0) return '0.00';
    const mainPrice = Math.ceil(price * exchangeRate);
    const usdPrice = Math.ceil(price * usdExchangeRate);
    const sgdPrice = Math.ceil(price);

    return (
      <Stack spacing={0.5}>
        <Typography variant="h6" color="primary" sx={{ fontWeight: 600, color: 'white' }}>
          {currencyCode} {mainPrice}
        </Typography>
        {currencyCode !== 'USD' && (
          <Typography variant="caption" color="text.secondary" sx={{ color: 'white' }}>
            USD {usdPrice}
          </Typography>
        )}
        {currencyCode !== 'SGD' && (
          <Typography variant="caption" color="text.secondary" sx={{ color: 'white' }}>
            SGD {sgdPrice}
          </Typography>
        )}
      </Stack>
    );
  };

  return (
    <StyledModal open={open} onClose={onClose} aria-labelledby="port-summary-modal">
      <ModalContent>
        <IconButton
          onClick={onClose}
          sx={{ position: 'absolute', right: 8, top: 8, color: 'grey.500' }}
        >
          <CloseIcon />
        </IconButton>

        <Stack spacing={2}>
          {/* Header */}
          <Box 
            sx={{ 
              background: colorTheme.gradient,
              color: 'white',
              p: 2,
              borderRadius: 1.5,
              mb: 1
            }}
          >
            <Typography variant="h5" component="h2" sx={{ fontWeight: 600, mb: 0.5 }}>
              {colorTheme.name} Summary
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, flexWrap: 'wrap' }}>
              <Box
                sx={{
                  px: 1.5,
                  py: 0.5,
                  bgcolor: 'rgba(255, 255, 255, 0.2)',
                  borderRadius: 1,
                  fontSize: '0.75rem',
                  fontWeight: 500
                }}
              >
                Premium Service
              </Box>
              {bookingData?.mode === 'dmc' && dmcName && (
                <Box
                  sx={{
                    px: 1.5,
                    py: 0.5,
                    bgcolor: 'rgba(255, 255, 255, 0.2)',
                    borderRadius: 1,
                    fontSize: '0.75rem',
                    fontWeight: 500
                  }}
                >
                  {dmcName}
                </Box>
              )}
              {/* Booking Date in Header */}
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                gap: 0.5,
                px: 1.5,
                py: 0.5,
                bgcolor: 'rgba(255, 255, 255, 0.15)',
                borderRadius: 1,
                border: '1px solid rgba(255, 255, 255, 0.2)'
              }}>
                <CalendarTodayIcon sx={{ fontSize: 14, color: 'white' }} />
                <Typography variant="caption" sx={{ color: 'rgba(255, 255, 255, 0.9)' }}>
                  Booking Date:
                </Typography>
                <Typography variant="caption" sx={{ fontWeight: 600, color: 'white' }}>
                  {bookingData?.bookingDate || 'Not specified'}
                </Typography>
              </Box>
            </Box>
          </Box>

          {/* Vehicle Overview */}
          <SummarySection>
            <Card 
              sx={{ 
                display: 'flex', 
                border: `1px solid ${colorTheme.primary}`,
                borderRadius: 2,
                overflow: 'hidden',
                height: 140
              }}
            >
              <CardMedia
                component="img"
                sx={{ 
                  width: 120, 
                  height: 140, 
                  objectFit: 'cover'
                }}
                image={bookingData.vehicleImage || '/images/car-placeholder.jpg'}
                alt={bookingData.vehicleName || 'Selected Vehicle'}
              />
              <Box sx={{ display: 'flex', flexDirection: 'column', flex: 1 }}>
                <CardContent sx={{ p: 1.5, pb: 0 }}>
                  <Typography variant="h6" sx={{ fontWeight: 600, color: colorTheme.primary, mb: 0.5 }}>
                    {bookingData.vehicleName || 'Selected Vehicle'}
                  </Typography>
                  {bookingData.city && bookingData.country && (
                    <DetailRow>
                      <LocationOnIcon sx={{ color: colorTheme.primary, fontSize: 16 }} />
                      <Typography variant="body2" sx={{ fontWeight: 500 }}>
                        {bookingData.city}, {bookingData.country}
                      </Typography>
                    </DetailRow>
                  )}
                  <DetailRow>
                    <DirectionsCarIcon sx={{ color: colorTheme.primary, fontSize: 16 }} />
                    <Typography variant="body2" sx={{ fontWeight: 500 }}>
                      {bookingData.vehicleType || 'Transport Vehicle'}
                    </Typography>
                  </DetailRow>
                </CardContent>
                <Box 
                  sx={{ 
                    mt: 'auto',
                    p: 1,
                    background: colorTheme.gradient,
                    color: 'white'
                  }}
                >
                  <Typography variant="body2" sx={{ fontWeight: 600, color: 'white' }}>
                    {colorTheme.name} Transfer
                  </Typography>
                </Box>
              </Box>
            </Card>
          </SummarySection>

          {/* Journey & Guest Details Combined */}
          <SummarySection>
            <Grid container spacing={2}>
              {/* Journey Details */}
              <Grid item xs={12} md={8}>
                <Paper 
                  sx={{ 
                    p: 2, 
                    background: colorTheme.light,
                    border: `1px solid ${colorTheme.primary}`,
                    borderRadius: 1.5,
                    height: '100%'
                  }}
                >
                  <Typography variant="subtitle1" gutterBottom sx={{ color: colorTheme.primary, fontWeight: 600, mb: 1 }}>
                    <MyLocationIcon sx={{ mr: 0.5, fontSize: 18 }} />
                    Journey Details
                  </Typography>
                  <Grid container spacing={1.5}>
                    <Grid item xs={6}>
                      <Typography variant="caption" color="text.secondary">Pick-up</Typography>
                      <Typography variant="body2" sx={{ fontWeight: 500 }}>
                        {bookingData.pickupLocation || 'Not specified'}
                      </Typography>
                    </Grid>
                    <Grid item xs={6}>
                      <Typography variant="caption" color="text.secondary">Drop-off</Typography>
                      <Typography variant="body2" sx={{ fontWeight: 500 }}>
                        {bookingData.dropoffLocation || 'Not specified'}
                      </Typography>
                    </Grid>
                    <Grid item xs={6}>
                      <Typography variant="caption" color="text.secondary">Booking Date</Typography>
                      <Typography variant="body2" sx={{ fontWeight: 600, color: colorTheme.primary }}>
                        {bookingData.bookingDate || 'Not specified'}
                      </Typography>
                      <Typography variant="caption" color="text.secondary" sx={{ fontStyle: 'italic' }}>
                        Service date for this transfer
                      </Typography>
                    </Grid>
                    <Grid item xs={6}>
                      <Typography variant="caption" color="text.secondary">Time</Typography>
                      <Typography variant="body2" sx={{ fontWeight: 500 }}>
                        {bookingData.pickupTime || 'Not specified'}
                      </Typography>
                    </Grid>
                  </Grid>
                </Paper>
              </Grid>
              
              {/* Guests & Price */}
              <Grid item xs={12} md={4}>
                <Paper 
                  sx={{ 
                    p: 2, 
                    background: colorTheme.gradient,
                    color: 'white',
                    borderRadius: 1.5,
                    textAlign: 'center',
                    height: '100%'
                  }}
                >
                  <Typography variant="subtitle1" gutterBottom sx={{ fontWeight: 600, mb: 1 }}>
                    <GroupIcon sx={{ mr: 0.5, fontSize: 18 }} />
                    Guests & Price
                  </Typography>
                  <Box sx={{ display: 'flex', justifyContent: 'space-around', mb: 1.5 }}>
                    <Box>
                      <Typography variant="h6" sx={{ fontWeight: 600 }}>
                        {bookingData.adults || 0}
                      </Typography>
                      <Typography variant="caption">Adults</Typography>
                    </Box>
                    <Box>
                      <Typography variant="h6" sx={{ fontWeight: 600 }}>
                        {bookingData.children || 0}
                      </Typography>
                      <Typography variant="caption">Children</Typography>
                    </Box>
                  </Box>
                  <Divider sx={{ bgcolor: 'rgba(255,255,255,0.3)', mb: 1 }} />
                  {PriceHide !== "1" ? (
                    formatPrice(bookingData.price)
                  ):(
                    <Typography variant="caption" color="text.secondary">
                      Pricing hidden
                    </Typography>
                  )}
                  {bookingData.taxPercentage && (
                    <Typography variant="caption" sx={{ opacity: 0.8, display: 'block', mt: 0.5 }}>
                      *Inc. {bookingData.taxPercentage}% tax
                    </Typography>
                  )}
                </Paper>
              </Grid>
            </Grid>
          </SummarySection>

          {/* Service Provider */}
          {bookingData.mode === 'dmc' && dmcName && (
            <SummarySection>
              <Paper 
                sx={{ 
                  p: 1.5, 
                  border: `1px solid ${colorTheme.primary}`,
                  borderRadius: 1.5
                }}
              >
                <Typography variant="subtitle2" gutterBottom sx={{ color: colorTheme.primary, fontWeight: 600 }}>
                  Service Provider
                </Typography>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  {dmcLogo && (
                    <Box
                      component="img"
                      src={dmcLogo}
                      alt={dmcName}
                      sx={{
                        width: 32,
                        height: 32,
                        objectFit: 'contain',
                        borderRadius: 1,
                        border: `1px solid ${colorTheme.light}`
                      }}
                    />
                  )}
                  <Typography variant="body2" sx={{ fontWeight: 500 }}>
                    {dmcName}
                  </Typography>
                </Box>
              </Paper>
            </SummarySection>
          )}

          {/* Action Button */}
          <Box sx={{ display: 'flex', justifyContent: 'center', mt: 2 }}>
            <Button 
              variant="contained" 
              onClick={onClose}
              size="medium"
              sx={{
                minWidth: 120,
                px: 3,
                py: 1,
                borderRadius: 1.5,
                background: colorTheme.gradient,
                fontWeight: 600,
                textTransform: 'none',
                '&:hover': {
                  background: colorTheme.secondary,
                },
              }}
            >
              Close
            </Button>
          </Box>
        </Stack>
      </ModalContent>
    </StyledModal>
  );
};

export default PortSummaryModal; 