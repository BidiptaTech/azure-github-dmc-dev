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
  Chip,
  Avatar,
  alpha
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
import ListIcon from '@mui/icons-material/List';
import PackageIcon from '@mui/icons-material/Inventory';
import VerifiedIcon from '@mui/icons-material/Verified';
import { useSelector } from 'react-redux';
import { capitalizeWords } from '../../../utils/textUtils';

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

  // Check if this is a package booking
  const isPackage = bookingData.type === 'attraction_package' || bookingData.ticketId?.startsWith('pkg_');

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

          {/* Package Includes Section - Show at top for packages */}
          {isPackage && bookingData.packageAttractions && bookingData.packageAttractions.length > 0 && (
            <SummarySection>
              <Box 
                sx={{ 
                  mb: 3,
                  p: 2.5,
                  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                  borderRadius: 3,
                  color: 'white',
                  textAlign: 'center'
                }}
              >
                <Typography variant="h5" sx={{ fontWeight: 700, mb: 1 }}>
                  <PackageIcon sx={{ mr: 1.5, fontSize: 32 }} />
                  Package Includes
                </Typography>
                <Typography variant="subtitle1" sx={{ opacity: 0.9 }}>
                  {bookingData.packageAttractions.length} Amazing Attractions
                </Typography>
              </Box>
              
              <Grid container spacing={3}>
                {bookingData.packageAttractions.map((attraction, index) => (
                  <Grid item xs={12} sm={6} md={4} key={`attraction-${attraction.attraction_id || index}`}>
                    <Card 
                      elevation={0}
                      sx={{
                        height: '100%',
                        border: '2px solid',
                        borderColor: 'primary.light',
                        borderRadius: 3,
                        transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                        position: 'relative',
                        overflow: 'hidden',
                        '&:hover': {
                          boxShadow: '0 12px 32px rgba(102, 126, 234, 0.25)',
                          transform: 'translateY(-8px) scale(1.02)',
                          borderColor: 'primary.main',
                          '& .attraction-number': {
                            transform: 'scale(1.1) rotate(5deg)',
                          },
                          '& .attraction-image': {
                            transform: 'scale(1.1)',
                          }
                        }
                      }}
                    >
                      {/* Attraction Number Badge */}
                      <Box
                        className="attraction-number"
                        sx={{
                          position: 'absolute',
                          top: 12,
                          left: 12,
                          zIndex: 2,
                          transition: 'all 0.3s ease'
                        }}
                      >
                        <Avatar 
                          sx={{ 
                            width: 32, 
                            height: 32, 
                            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                            color: 'white',
                            fontSize: '0.875rem',
                            fontWeight: 700,
                            boxShadow: '0 4px 12px rgba(102, 126, 234, 0.4)'
                          }}
                        >
                          {index + 1}
                        </Avatar>
                      </Box>
                      
                      {/* Image Container */}
                      <Box sx={{ position: 'relative', overflow: 'hidden' }}>
                        <CardMedia
                          component="img"
                          className="attraction-image"
                          sx={{ 
                            height: 180, 
                            objectFit: 'cover',
                            transition: 'transform 0.4s ease'
                          }}
                          image={attraction.master_image}
                          alt={attraction.name}
                        />
                        {/* Gradient Overlay */}
                        <Box
                          sx={{
                            position: 'absolute',
                            bottom: 0,
                            left: 0,
                            right: 0,
                            height: '60px',
                            background: 'linear-gradient(transparent, rgba(0,0,0,0.7))',
                          }}
                        />
                      </Box>
                      
                      <CardContent sx={{ p: 2.5 }}>
                        <Typography 
                          variant="h6" 
                          fontWeight={700}
                          sx={{ 
                            mb: 1, 
                            lineHeight: 1.3,
                            color: 'primary.main',
                            fontSize: '1.1rem'
                          }}
                        >
                          {capitalizeWords(attraction.name)}
                        </Typography>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                          <LocationOnIcon 
                            sx={{ 
                              mr: 1, 
                              color: 'secondary.main',
                              fontSize: 20 
                            }} 
                          />
                          <Typography 
                            variant="body2" 
                            color="text.secondary"
                            sx={{ 
                              fontSize: '0.9rem',
                              fontWeight: 500
                            }}
                          >
                            {attraction.location}, {attraction.country}
                          </Typography>
                        </Box>
                        
                        {/* Package Badge */}
                        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                          <Chip
                            label="Included"
                            color="success"
                            size="small"
                            icon={<VerifiedIcon />}
                            sx={{ 
                              fontWeight: 600,
                              '& .MuiChip-icon': { fontSize: 16 }
                            }}
                          />
                          <Typography 
                            variant="caption" 
                            sx={{ 
                              color: 'primary.main',
                              fontWeight: 600,
                              fontSize: '0.8rem'
                            }}
                          >
                            Attraction {index + 1}
                          </Typography>
                        </Box>
                      </CardContent>
                    </Card>
                  </Grid>
                ))}
              </Grid>
              
              {/* Package Summary */}
              <Box 
                sx={{ 
                  mt: 4,
                  p: 3,
                  background: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                  borderRadius: 3,
                  color: 'white',
                  textAlign: 'center'
                }}
              >
                <Typography variant="h6" sx={{ fontWeight: 700, mb: 1 }}>
                  <VerifiedIcon sx={{ mr: 1, fontSize: 24 }} />
                  Complete Package Experience
                </Typography>
                <Typography variant="body1" sx={{ opacity: 0.9 }}>
                  Enjoy {bookingData.packageAttractions.length} carefully curated attractions in one convenient package
                </Typography>
              </Box>
            </SummarySection>
          )}

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

          {/* Attraction Details - Only show for individual tickets, not packages */}
          {!isPackage && (
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
                    {capitalizeWords(bookingData.attraction)}
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
                  </CardContent>
                </Box>
              </Card>
            </SummarySection>
          )}

          {/* Ticket/Package Details */}
          <SummarySection>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
              {isPackage ? (
                <PackageIcon sx={{ mr: 1, color: 'secondary.main', fontSize: 28 }} />
              ) : (
                <ConfirmationNumberIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
              )}
              <Typography variant="h6">
                {isPackage ? 'Package Information' : 'Ticket Information'}
              </Typography>
              {isPackage && (
                <Chip 
                  label="Package" 
                  color="secondary" 
                  size="small" 
                  sx={{ ml: 1.5, height: '24px' }} 
                />
              )}
            </Box>
            
            <Grid container spacing={2}>
              <Grid item xs={12} md={4}>
                <PriceCard>
                  <Typography variant="subtitle1" color="primary" gutterBottom>
                    {isPackage ? 'Selected Package' : 'Selected Ticket'}
                  </Typography>
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <Typography variant="body1" fontWeight={500}>
                      {capitalizeWords(bookingData.ticketType)}
                    </Typography>
                    {isPackage && (
                      <VerifiedIcon color="secondary" fontSize="small" />
                    )}
                  </Box>
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