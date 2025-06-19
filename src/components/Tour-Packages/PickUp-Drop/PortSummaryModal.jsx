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

  // Check if modal should open
  if (!open) return null;
  
  // If no booking data is available, show a loading or error state
  if (!bookingData) {
    return (
      <StyledModal
        open={open}
        onClose={onClose}
        aria-labelledby="port-summary-modal-loading"
      >
        <ModalContent sx={{ maxWidth: 500, textAlign: 'center', py: 4 }}>
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
          <Alert severity="warning" sx={{ mb: 2 }}>
            Complete booking information is not available
          </Alert>
          <Typography variant="body1" sx={{ mb: 3 }}>
            Please ensure all fields are filled to view the complete summary.
          </Typography>
          <Button variant="outlined" onClick={onClose}>
            Close
          </Button>
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
  };

  return (
    <StyledModal
      open={open}
      onClose={onClose}
      aria-labelledby="port-summary-modal"
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
              {portType} Transport Summary
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <Typography
                component="span"
                sx={{
                  px: 1.5,
                  py: 0.5,
                  bgcolor: portType === "Entry Port" ? 'primary.main' : 'secondary.main',
                  color: 'white',
                  borderRadius: 1,
                  fontSize: '0.875rem',
                  fontWeight: 500
                }}
              >
                {portType} Service
              </Typography>
            </Box>
          </Box>

          <Divider />

          {/* Vehicle Details */}
          <SummarySection>
            <Card sx={{ display: 'flex', mb: 2 }}>
              <CardMedia
                component="img"
                sx={{ width: 200, height: 200, objectFit: 'cover' }}
                image={bookingData.vehicleImage || '/images/car-placeholder.jpg'}
                alt={bookingData.vehicleName || 'Selected Vehicle'}
              />
              <Box sx={{ display: 'flex', flexDirection: 'column', flex: 1 }}>
                <CardContent>
                  <Typography variant="h6" gutterBottom>
                    {bookingData.vehicleName || 'Selected Vehicle'}
                  </Typography>
                  {bookingData.city && bookingData.country && (
                    <DetailRow>
                      <LocationOnIcon color="primary" />
                      <Typography>
                        {bookingData.city}, {bookingData.country}
                      </Typography>
                    </DetailRow>
                  )}
                  {(bookingData.vehicleType || bookingData.vehicleModel) && (
                    <DetailRow>
                      <DirectionsCarIcon color="primary" />
                      <Typography>
                        {bookingData.vehicleType || 'Transport Vehicle'}
                        {bookingData.vehicleModel && `, ${bookingData.vehicleModel}`}
                      </Typography>
                    </DetailRow>
                  )}
                  <Box 
                    sx={{ 
                      mt: 2,
                      p: 1.5,
                      borderRadius: 1,
                      bgcolor: portType === "Entry Port" ? 'primary.main' : 'secondary.main',
                      color: 'white',
                      display: 'flex',
                      alignItems: 'center',
                      gap: 1
                    }}
                  >
                    <LocationOnIcon sx={{ color: 'white' }} />
                    <Box>
                      <Typography variant="subtitle2" sx={{ fontWeight: 600, mb: 0.5 }}>
                        {portType} Transfer
                      </Typography>
                    </Box>
                  </Box>
                </CardContent>
              </Box>
            </Card>
          </SummarySection>

          {/* Journey Details Section */}
          <SummarySection>
            <Typography variant="h6" gutterBottom>
              <MyLocationIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
              Journey Details
            </Typography>
            <Paper sx={{ p: 2, bgcolor: 'background.default' }}>
              <Grid container spacing={2}>
                {/* Pickup Location */}
                <Grid item xs={12} md={6}>
                  <PriceCard>
                    <Typography variant="subtitle1" color="primary" gutterBottom>
                      <LocationOnIcon fontSize="small" sx={{ mr: 0.5, verticalAlign: 'text-bottom' }} />
                      Pick-up Location
                    </Typography>
                    <Typography variant="body1">
                      {bookingData.pickupLocation || 'Not specified'}
                    </Typography>
                  </PriceCard>
                </Grid>
                
                {/* Dropoff Location */}
                <Grid item xs={12} md={6}>
                  <PriceCard>
                    <Typography variant="subtitle1" color="primary" gutterBottom>
                      <LocationCityIcon fontSize="small" sx={{ mr: 0.5, verticalAlign: 'text-bottom' }} />
                      Drop-off Location
                    </Typography>
                    <Typography variant="body1">
                      {bookingData.dropoffLocation || 'Not specified'}
                    </Typography>
                  </PriceCard>
                </Grid>
                
                {/* Pickup Time */}
                <Grid item xs={12} md={6}>
                  <PriceCard>
                    <Typography variant="subtitle1" color="primary" gutterBottom>
                      <AccessTimeIcon fontSize="small" sx={{ mr: 0.5, verticalAlign: 'text-bottom' }} />
                      Pick-up Time
                    </Typography>
                    <Typography variant="body1">
                      {bookingData.pickupTime || 'Not specified'}
                    </Typography>
                  </PriceCard>
                </Grid>
                
                {/* Pickup Date */}
                <Grid item xs={12} md={6}>
                  <PriceCard>
                    <Typography variant="subtitle1" color="primary" gutterBottom>
                      <CalendarTodayIcon fontSize="small" sx={{ mr: 0.5, verticalAlign: 'text-bottom' }} />
                      Pick-up Date
                    </Typography>
                    <Typography variant="body1">
                      {bookingData.pickupDate || 'Not specified'}
                    </Typography>
                  </PriceCard>
                </Grid>
              </Grid>
            </Paper>
          </SummarySection>

          {/* Vehicle Details */}
          <SummarySection>
            <Typography variant="h6" gutterBottom>
              <DirectionsCarIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
              Vehicle Details
            </Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Vehicle Type
                  </Typography>
                  <Typography variant="body1">
                    {bookingData.vehicleType || 'Standard Vehicle'}
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    Model: {bookingData.vehicleModel || 'Standard Model'}
                  </Typography>
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Pricing Mode
                  </Typography>
                  <Typography variant="body1">
                    {bookingData.priceMode || 'Private'}
                  </Typography>
                  {bookingData.seatingCapacity && (
                    <Typography variant="body2" color="text.secondary">
                      Capacity: {bookingData.seatingCapacity} persons
                    </Typography>
                  )}
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Booking Mode
                  </Typography>
                  {bookingData.mode === 'dmc' && (
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
                        {dmcName}'s Mode
                      </Typography>
                    </Box>
                  )}
                  {bookingData.mode === 'travclicks' && (
                    <Typography variant="body1">
                      Travclicks Mode
                    </Typography>
                  )}
                </PriceCard>
              </Grid>
            </Grid>
          </SummarySection>

          {/* Guest Details and Total Price */}
          <SummarySection>
            <Typography variant="h6" gutterBottom>
              <GroupIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
              Guest Details & Price
            </Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={8}>
                <PriceCard>
                  <Box sx={{ display: 'flex', gap: 3 }}>
                    <Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        <PersonIcon sx={{ mr: 1, color: 'primary.main' }} />
                        <Typography>
                          Adults: {bookingData.adults || 0}
                        </Typography>
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        <ChildCareIcon sx={{ mr: 1, color: 'primary.main' }} />
                        <Typography>
                          Children: {bookingData.children || 0}
                        </Typography>
                      </Box>
                    </Box>
                  </Box>
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    <AttachMoneyIcon fontSize="small" sx={{ mr: 0.5, verticalAlign: 'text-bottom' }} />
                    Total Price
                  </Typography>
                  {formatPrice(bookingData.price)}
                  {bookingData.taxPercentage && (
                    <Typography variant="caption" color="text.secondary">
                      *Prices are subject to {bookingData.taxPercentage}% tax
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

export default PortSummaryModal; 