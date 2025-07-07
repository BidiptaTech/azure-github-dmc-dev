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
  styled
} from '@mui/material';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import ElderlyIcon from '@mui/icons-material/Elderly';
import ConfirmationNumberIcon from '@mui/icons-material/ConfirmationNumber';
import CloseIcon from '@mui/icons-material/Close';
import CategoryIcon from '@mui/icons-material/Category';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import GroupIcon from '@mui/icons-material/Group';
import InfoIcon from '@mui/icons-material/Info';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
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

const BookingSummaryModal = ({ 
  open, 
  onClose, 
  bookingData,
  bookingIndex,
}) => {
  const currencyCode = useSelector((state) => state.auth.currencyCode) || "SGD";
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate) || 1;
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);
  const dmcName = useSelector((state) => state.auth.DmcName) || "DMC";
  console.log("bookingData", bookingData);
  if (!bookingData) return null;

  // Calculate total price
  const calculateTotalPrice = () => {
    const adultTotal = bookingData.adultPrice * bookingData.pax.Adults;
    const childTotal = bookingData.childPrice * bookingData.pax.Children;
    const seniorTotal = bookingData.seniorPrice * bookingData.pax.Seniors;
    return adultTotal + childTotal + seniorTotal;
  };

  const formatPrice = (price) => {
    if (!price) return '0.00';
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
      aria-labelledby="booking-summary-modal"
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
              Booking Summary
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
                {bookingData.priceType === 'nri' ? 'Foreigner Pricing' : 'Local Pricing'}
              </Typography>
            </Box>
          </Box>

          <Divider />

          {/* Booking Date */}
          {bookingData && (
            <SummarySection>
              <Box
                sx={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  p: 2,
                  backgroundColor: 'primary.main',
                  color: 'white',
                  borderRadius: 2,
                  gap: 1
                }}
              >
                <CalendarTodayIcon sx={{ color: 'white' }} />
                <Typography variant="h6" sx={{ fontWeight: 600, color: 'white' }}>
                  Booking Date: {bookingData.bookingDate}
                </Typography>
              </Box>
            </SummarySection>
          )}

          {/* Attraction Details */}
          <SummarySection>
            <Card sx={{ display: 'flex', mb: 2 }}>
              <CardMedia
                component="img"
                sx={{ width: 200, height: 200, objectFit: 'cover' }}
                image={bookingData.image}
                alt={bookingData.attraction}
              />
              <Box sx={{ display: 'flex', flexDirection: 'column', flex: 1 }}>
                <CardContent>
                  <Typography variant="h6" gutterBottom>
                    {bookingData.attraction}
                  </Typography>
                  <DetailRow>
                    <LocationOnIcon color="primary" />
                    <Typography>
                      {bookingData.city !== 'Not specified' ? `${bookingData.city}, ` : ''}{bookingData.country}
                    </Typography>
                  </DetailRow>
                  <Box 
                    sx={{ 
                      mt: 2,
                      p: 1.5,
                      borderRadius: 1,
                      bgcolor: 'primary.main',
                      color: 'white',
                      display: 'flex',
                      alignItems: 'center',
                      gap: 1
                    }}
                  >
                    <AccessTimeIcon sx={{ color: 'white' }} />
                    <Box>
                      <Typography variant="subtitle2" sx={{ fontWeight: 600, mb: 0.5 }}>
                        Opening Hours
                      </Typography>
                      <Typography sx={{ fontSize: '1rem',color: 'white', }}>
                        {bookingData.openingHours}
                      </Typography>
                    </Box>
                  </Box>
                  {/* <Typography variant="body2" color="text.secondary" sx={{ mt: 2 }}>
                    {bookingData.description}
                  </Typography> */}
                </CardContent>
              </Box>
            </Card>
          </SummarySection>

          {/* Ticket Details */}
          <SummarySection>
            <Typography variant="h6" gutterBottom>
              <ConfirmationNumberIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
              Ticket Information
            </Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Selected Ticket
                  </Typography>
                  <Typography variant="body1">
                    {bookingData.ticketType}
                  </Typography>
                  {/* {bookingData.ticketDescription && (
                    <Typography variant="body2" color="text.secondary">
                      {bookingData.ticketDescription}
                    </Typography>
                  )} */}
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Time Slot
                  </Typography>
                  <Typography variant="body1">
                    {bookingData.timeSlot}
                  </Typography>
                  {/* <Typography variant="body2" color="text.secondary">
                    Duration: {bookingData.duration}
                  </Typography> */}
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Booking Mode
                  </Typography>
                  {/* <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                     {bookingData.mode.toUpperCase()}
                  </Typography> */}
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
                          Adults: {bookingData.pax.Adults}
                        </Typography>
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                        <ChildCareIcon sx={{ mr: 1, color: 'primary.main' }} />
                        <Typography>
                          Children: {bookingData.pax.Children}
                        </Typography>
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <ElderlyIcon sx={{ mr: 1, color: 'primary.main' }} />
                        <Typography>
                          Seniors: {bookingData.pax.Seniors}
                        </Typography>
                      </Box>
                    </Box>
                  </Box>
                </PriceCard>
              </Grid>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    Total Price
                  </Typography>
                  {formatPrice(calculateTotalPrice())}
                  {/* {bookingData.tax_percentage && (
                    <Typography variant="caption" color="text.secondary">
                      *Prices are subject to {bookingData.tax_percentage}% tax
                    </Typography>
                  )} */}
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

export default BookingSummaryModal; 