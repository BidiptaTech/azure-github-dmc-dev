import React from "react";
import { useSelector } from "react-redux";
import {
  Box,
  Typography,
  Paper,
  Chip,
  Divider,
  Avatar,
} from "@mui/material";
import { styled } from "@mui/material/styles";
import {
  AttachMoney as AttachMoneyIcon,
  Person as PersonIcon,
  People as PeopleIcon,
  Hotel as HotelIcon,
  DirectionsCar as CarIcon,
  Explore as ExploreIcon,
  Tour as TourIcon,
  Restaurant as RestaurantIcon,
  CheckCircle as CheckCircleIcon,
} from "@mui/icons-material";

// Styled components
const SectionPaper = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(3),
  marginBottom: theme.spacing(3),
  borderRadius: theme.spacing(2),
  boxShadow: "0 4px 20px rgba(0, 0, 0, 0.08)",
  transition: "all 0.3s ease",
  overflow: "hidden",
  "&:hover": {
    boxShadow: "0 8px 30px rgba(0, 0, 0, 0.12)",
    transform: "translateY(-3px)"
  },
  // Mobile and tablet responsive styling
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(1.5),
    marginBottom: theme.spacing(1.5),
    borderRadius: theme.spacing(1.5),
  },
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(1),
    marginBottom: theme.spacing(1),
    borderRadius: theme.spacing(1),
  }
}));

const SectionHeader = styled(Box)(({ theme }) => ({
  display: "flex",
  alignItems: "center",
  marginBottom: theme.spacing(2),
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    marginBottom: theme.spacing(1),
    flexDirection: 'column',
    alignItems: 'flex-start',
    gap: theme.spacing(0.5)
  }
}));

const SectionIcon = styled(Avatar)(({ theme, bgcolor }) => ({
  backgroundColor: bgcolor || theme.palette.primary.main,
  color: theme.palette.common.white,
  marginRight: theme.spacing(2),
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    marginRight: 0,
    marginBottom: theme.spacing(1),
    width: 32,
    height: 32
  }
}));

// Helper function to format service name
const formatServiceName = (key) => {
  return key
    .replace(/([A-Z])/g, " $1")
    .replace(/^./, (str) => str.toUpperCase());
};

// Helper function to get icon for service
const getServiceIcon = (service) => {
  switch(service) {
    case "hotel": return <HotelIcon />;
    case "entryExitPort": return <CarIcon />;
    case "attraction": return <ExploreIcon />;
    case "localTour": return <TourIcon />;
    case "tourGuide": return <PersonIcon />;
    case "restaurant": return <RestaurantIcon />;
    default: return <CheckCircleIcon />;
  }
};

const PricingSummaryComponent = () => {
  const bookingDetails = useSelector((state) => state.enquiry);
  const selectedServices = useSelector((state) => state.enquiry.selectedServices || []);
  
  // Get total price from Redux (already calculated in BookingEnquiries or ConfirmDetails)
  const totalPriceFromRedux = bookingDetails.calculatedPrice || 0;

  // Calculate total guests
  const adults = parseInt(bookingDetails.guestCounts?.Adults || bookingDetails.guests?.adults || 1);
  const children = parseInt(bookingDetails.guestCounts?.Children || bookingDetails.guests?.children || 0);
  const infants = parseInt(bookingDetails.guestCounts?.Infants || bookingDetails.guests?.infant || 0);
  const totalGuests = adults + children + infants;

  // Calculate per-person price from total
  const pricePerPerson = totalGuests > 0 ? Math.round(totalPriceFromRedux / totalGuests) : 0;
  const totalPrice = totalPriceFromRedux;

  // Calculate days
  const calculateDays = () => {
    const checkinDate = bookingDetails.checkinDate || bookingDetails.checkIn;
    const checkoutDate = bookingDetails.checkoutDate || bookingDetails.checkOut;
    
    if (checkinDate && checkoutDate) {
      return Math.max(1, Math.ceil((new Date(checkoutDate) - new Date(checkinDate)) / (24 * 60 * 60 * 1000)));
    }
    return 1;
  };

  const totalDays = calculateDays();

  // Debug logging
  console.log("💰 PricingSummaryComponent - Rendering with:", {
    totalPriceFromRedux,
    totalGuests,
    pricePerPerson,
    totalPrice,
    selectedServices
  });

  if (!totalPrice || totalPrice === 0) {
    console.log("⚠️ PricingSummaryComponent - Not rendering (totalPrice is 0)");
    return null;
  }

  return (
    <Paper sx={{ 
      p: 2, 
      mb: 3, 
      borderRadius: 2,
      boxShadow: "0 2px 12px rgba(0, 0, 0, 0.08)",
      border: '1px solid rgba(0, 0, 0, 0.08)'
    }}>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
        <Avatar sx={{ bgcolor: '#4caf50', width: 36, height: 36, mr: 1.5 }}>
          <AttachMoneyIcon sx={{ fontSize: 20 }} />
        </Avatar>
        <Typography variant="h6" sx={{ fontWeight: 600, fontSize: '1.1rem' }}>
          Package Pricing Summary
        </Typography>
      </Box>
      
      <Divider sx={{ mb: 2 }} />
      
      {/* Compact Pricing Display */}
      <Box sx={{ display: 'flex', gap: 1.5, mb: 2, flexDirection: { xs: 'column', sm: 'row' } }}>
        {/* Per Person Price Box */}
        <Box 
          sx={{ 
            flex: 1,
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            color: 'white',
            p: 1.5,
            borderRadius: 1.5,
            display: 'flex',
            alignItems: 'center',
            gap: 1.5,
            boxShadow: '0 2px 8px rgba(102, 126, 234, 0.25)',
            transition: 'all 0.2s ease',
            '&:hover': {
              transform: 'translateY(-2px)',
              boxShadow: '0 4px 12px rgba(102, 126, 234, 0.3)',
            }
          }}
        >
          <Box sx={{ 
            display: 'flex', 
            alignItems: 'center', 
            justifyContent: 'center',
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            borderRadius: '50%',
            width: 40,
            height: 40,
            flexShrink: 0
          }}>
            <PersonIcon sx={{ fontSize: 22 }} />
          </Box>
          <Box sx={{ flex: 1 }}>
            <Typography variant="caption" sx={{ 
              display: 'block',
              fontSize: '0.7rem',
              fontWeight: 500,
              mb: 0.25,
              opacity: 0.9
            }}>
              Per Person
            </Typography>
            <Typography variant="h5" sx={{ fontWeight: 700, lineHeight: 1 }}>
              SGD {pricePerPerson.toLocaleString()}
            </Typography>
          </Box>
        </Box>

        {/* Total Price Box */}
        <Box 
          sx={{ 
            flex: 1,
            background: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            color: 'white',
            p: 1.5,
            borderRadius: 1.5,
            display: 'flex',
            alignItems: 'center',
            gap: 1.5,
            boxShadow: '0 2px 8px rgba(245, 87, 108, 0.25)',
            transition: 'all 0.2s ease',
            '&:hover': {
              transform: 'translateY(-2px)',
              boxShadow: '0 4px 12px rgba(245, 87, 108, 0.3)',
            }
          }}
        >
          <Box sx={{ 
            display: 'flex', 
            alignItems: 'center', 
            justifyContent: 'center',
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            borderRadius: '50%',
            width: 40,
            height: 40,
            flexShrink: 0
          }}>
            <PeopleIcon sx={{ fontSize: 22 }} />
          </Box>
          <Box sx={{ flex: 1 }}>
            <Typography variant="caption" sx={{ 
              display: 'block',
              fontSize: '0.7rem',
              fontWeight: 500,
              mb: 0.25,
              opacity: 0.9
            }}>
              Total Price
            </Typography>
            <Typography variant="h5" sx={{ fontWeight: 700, lineHeight: 1, mb: 0.25 }}>
              SGD {totalPrice.toLocaleString()}
            </Typography>
            <Typography variant="caption" sx={{ fontSize: '0.65rem', opacity: 0.85 }}>
              {totalGuests} guest{totalGuests !== 1 ? 's' : ''} · {totalDays} day{totalDays !== 1 ? 's' : ''}
            </Typography>
          </Box>
        </Box>
      </Box>

      {/* Compact Info Section */}
      <Box sx={{ mb: 2 }}>
        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mb: 1.5 }}>
          {selectedServices.map((service) => (
            <Chip
              key={service}
              label={formatServiceName(service)}
              icon={getServiceIcon(service)}
              size="small"
              color="primary"
              variant="outlined"
              sx={{ height: 24, fontSize: '0.75rem' }}
            />
          ))}
        </Box>
      </Box>

      {/* Compact Calculation Info */}
      <Box sx={{ 
        p: 1.5, 
        mb: 2,
        backgroundColor: '#f5f5f5', 
        borderRadius: 1,
        display: 'flex',
        flexWrap: 'wrap',
        gap: 1.5,
        alignItems: 'center'
      }}>
        <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.75rem', fontWeight: 600 }}>
          Based on:
        </Typography>
        <Chip label={`${totalDays} day${totalDays !== 1 ? 's' : ''}`} size="small" sx={{ height: 22, fontSize: '0.7rem' }} />
        <Chip label={`${adults} adult${adults !== 1 ? 's' : ''}`} size="small" sx={{ height: 22, fontSize: '0.7rem' }} />
        {children > 0 && (
          <Chip label={`${children} child${children !== 1 ? 'ren' : ''}`} size="small" sx={{ height: 22, fontSize: '0.7rem' }} />
        )}
        {infants > 0 && (
          <Chip label={`${infants} infant${infants !== 1 ? 's' : ''}`} size="small" sx={{ height: 22, fontSize: '0.7rem' }} />
        )}
      </Box>

      {/* Compact Disclaimer */}
      <Box sx={{ 
        p: 1.5, 
        backgroundColor: 'rgba(255, 152, 0, 0.08)', 
        borderRadius: 1,
        border: '1px solid rgba(255, 152, 0, 0.2)'
      }}>
        <Typography variant="caption" sx={{ color: '#ed6c02', fontWeight: 500, fontSize: '0.75rem', display: 'block', textAlign: 'center' }}>
          ⚠️ Final price may vary based on actual selections and seasonal rates
        </Typography>
      </Box>
    </Paper>
  );
};

export default PricingSummaryComponent;

