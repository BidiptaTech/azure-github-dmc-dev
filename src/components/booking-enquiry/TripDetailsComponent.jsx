import React, { useState, useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  Box,
  Typography,
  Paper,
  Grid,
  TextField,
  Divider,
  Avatar,
} from "@mui/material";
import { styled } from "@mui/material/styles";
import {
  CalendarMonth as CalendarIcon,
  People as PeopleIcon,
  LocationOn as LocationIcon,
  ChildCare as ChildIcon,
  Male as MaleIcon,
  Female as FemaleIcon,
} from "@mui/icons-material";
import Woman2TwoToneIcon from '@mui/icons-material/Woman2TwoTone';
import Man2TwoToneIcon from '@mui/icons-material/Man2TwoTone';
import { updateServiceDetails } from "@/slice/common/EnquirySlice";

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

const InfoField = styled(Box)(({ theme }) => ({
  display: "flex",
  flexDirection: "column",
  gap: theme.spacing(0.5),
  flex: 1,
  minWidth: 0,
}));

const InfoLabel = styled(Typography)(({ theme }) => ({
  fontSize: "0.75rem",
  fontWeight: 600,
  color: theme.palette.text.secondary,
  textTransform: "uppercase",
  letterSpacing: "0.5px",
  display: "flex",
  alignItems: "center",
  gap: theme.spacing(0.5),
}));

const InfoValue = styled(Typography)(({ theme }) => ({
  fontSize: "0.95rem",
  fontWeight: 500,
  color: theme.palette.text.primary,
}));

const TripDetailsComponent = ({ mode = 'view' }) => {
  const dispatch = useDispatch();
  const bookingDetails = useSelector((state) => state.enquiry);
  
  const [countryValue, setCountryValue] = useState("");
  const [cityValue, setCityValue] = useState("");

  // Initialize country and city values from Redux state
  useEffect(() => {
    if (bookingDetails.searchLocation) {
      setCountryValue(bookingDetails.searchLocation.country || "");
      setCityValue(bookingDetails.searchLocation.city || "");
    }
  }, [bookingDetails.searchLocation]);

  // Format date to be more readable
  const formatDate = (dateString) => {
    if (!dateString) return "Not specified";
    
    // If dateString is in format DD/MM/YYYY
    const parts = dateString.split('/');
    if (parts.length === 3) {
      return `${parts[0]} ${getMonthName(parts[1])} ${parts[2]}`;
    }
    
    return dateString;
  };
  
  const getMonthName = (month) => {
    const months = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];
    return months[parseInt(month) - 1] || month;
  };

  // Handle country and city change (only in edit mode)
  const handleCountryChange = (e) => {
    if (mode === 'edit') {
      setCountryValue(e.target.value);
      if (bookingDetails.searchLocation) {
        dispatch(updateServiceDetails({
          service: 'searchLocation',
          data: { 
            ...bookingDetails.searchLocation,
            country: e.target.value 
          }
        }));
      }
    }
  };

  const handleCityChange = (e) => {
    if (mode === 'edit') {
      setCityValue(e.target.value);
      if (bookingDetails.searchLocation) {
        dispatch(updateServiceDetails({
          service: 'searchLocation',
          data: { 
            ...bookingDetails.searchLocation,
            city: e.target.value 
          }
        }));
      }
    }
  };

  return (
    <SectionPaper>
      <SectionHeader>
        <SectionIcon>
          <CalendarIcon />
        </SectionIcon>
        <Typography variant="h6" component="h2" fontWeight={600}>
          Trip Details
        </Typography>
      </SectionHeader>
      
      <Divider sx={{ mb: 3 }} />
      
      {/* Single Row Layout - All fields in one line */}
      <Box 
        sx={{ 
          display: 'flex', 
          gap: { xs: 1.5, sm: 2, md: 3 },
          flexWrap: 'nowrap',
          alignItems: 'flex-start',
          width: '100%',
          overflowX: 'auto',
          overflowY: 'hidden',
          '&::-webkit-scrollbar': {
            height: '6px',
          },
          '&::-webkit-scrollbar-track': {
            background: 'rgba(0,0,0,0.05)',
            borderRadius: '3px',
          },
          '&::-webkit-scrollbar-thumb': {
            background: 'rgba(102, 126, 234, 0.3)',
            borderRadius: '3px',
            '&:hover': {
              background: 'rgba(102, 126, 234, 0.5)',
            },
          },
        }}
      >
        {/* Country Field */}
        <InfoField sx={{ 
          minWidth: '160px',
          width: '160px',
          flexShrink: 0
        }}>
          <InfoLabel>
            <LocationIcon sx={{ fontSize: 16, color: 'primary.main' }} />
            Country
          </InfoLabel>
          <TextField
            value={countryValue}
            onChange={handleCountryChange}
            disabled={mode === 'view'}
            size="small"
            variant="outlined"
            placeholder="Select country"
            fullWidth
            InputProps={{
              readOnly: mode === 'view',
              sx: { 
                fontSize: '0.95rem',
                fontWeight: 500,
                '& .MuiOutlinedInput-notchedOutline': {
                  borderColor: 'rgba(0, 0, 0, 0.12)',
                },
                '&:hover .MuiOutlinedInput-notchedOutline': {
                  borderColor: 'primary.main',
                },
                '&.Mui-disabled': {
                  backgroundColor: 'transparent',
                }
              }
            }}
            sx={{
              '& .MuiInputBase-input.Mui-disabled': {
                WebkitTextFillColor: 'rgba(0, 0, 0, 0.87)',
                color: 'rgba(0, 0, 0, 0.87)',
              }
            }}
          />
        </InfoField>

        {/* City Field */}
        <InfoField sx={{ 
          minWidth: '160px',
          width: '160px',
          flexShrink: 0
        }}>
          <InfoLabel>
            <LocationIcon sx={{ fontSize: 16, color: 'primary.main' }} />
            City
          </InfoLabel>
          <TextField
            value={cityValue}
            onChange={handleCityChange}
            disabled={mode === 'view'}
            size="small"
            variant="outlined"
            placeholder="Select city"
            fullWidth
            InputProps={{
              readOnly: mode === 'view',
              sx: { 
                fontSize: '0.95rem',
                fontWeight: 500,
                '& .MuiOutlinedInput-notchedOutline': {
                  borderColor: 'rgba(0, 0, 0, 0.12)',
                },
                '&:hover .MuiOutlinedInput-notchedOutline': {
                  borderColor: 'primary.main',
                },
                '&.Mui-disabled': {
                  backgroundColor: 'transparent',
                }
              }
            }}
            sx={{
              '& .MuiInputBase-input.Mui-disabled': {
                WebkitTextFillColor: 'rgba(0, 0, 0, 0.87)',
                color: 'rgba(0, 0, 0, 0.87)',
              }
            }}
          />
        </InfoField>

        {/* Check-in Field */}
        <InfoField sx={{ 
          minWidth: '150px',
          width: '150px',
          flexShrink: 0
        }}>
          <InfoLabel>
            <CalendarIcon sx={{ fontSize: 16, color: 'primary.main' }} />
            Check-in
          </InfoLabel>
          <Box 
            sx={{ 
              px: 1.5, 
              py: 1.2,
              border: '1px solid rgba(0, 0, 0, 0.12)',
              borderRadius: 1,
              backgroundColor: 'rgba(0, 0, 0, 0.02)',
              transition: 'all 0.2s ease',
              whiteSpace: 'nowrap',
              '&:hover': {
                borderColor: 'primary.main',
                backgroundColor: 'rgba(25, 118, 210, 0.04)',
              }
            }}
          >
            <InfoValue>
              {formatDate(bookingDetails.checkIn)}
            </InfoValue>
          </Box>
        </InfoField>

        {/* Check-out Field */}
        <InfoField sx={{ 
          minWidth: '150px',
          width: '150px',
          flexShrink: 0
        }}>
          <InfoLabel>
            <CalendarIcon sx={{ fontSize: 16, color: 'primary.main' }} />
            Check-out
          </InfoLabel>
          <Box 
            sx={{ 
              px: 1.5, 
              py: 1.2,
              border: '1px solid rgba(0, 0, 0, 0.12)',
              borderRadius: 1,
              backgroundColor: 'rgba(0, 0, 0, 0.02)',
              transition: 'all 0.2s ease',
              whiteSpace: 'nowrap',
              '&:hover': {
                borderColor: 'primary.main',
                backgroundColor: 'rgba(25, 118, 210, 0.04)',
              }
            }}
          >
            <InfoValue>
              {formatDate(bookingDetails.checkOut)}
            </InfoValue>
          </Box>
        </InfoField>

        {/* Guests Field */}
        <InfoField sx={{ 
          minWidth: '200px', 
          width: '200px',
          flexShrink: 0
        }}>
          <InfoLabel>
            <PeopleIcon sx={{ fontSize: 16, color: 'primary.main' }} />
            Guests
          </InfoLabel>
          <Box 
            sx={{ 
              px: 1.5, 
              py: 1,
              border: '1px solid rgba(0, 0, 0, 0.12)',
              borderRadius: 1,
              backgroundColor: 'rgba(0, 0, 0, 0.02)',
              transition: 'all 0.2s ease',
              display: 'flex',
              alignItems: 'center',
              gap: 1.5,
              flexWrap: 'nowrap',
              minHeight: '38px',
              whiteSpace: 'nowrap',
              '&:hover': {
                borderColor: 'primary.main',
                backgroundColor: 'rgba(25, 118, 210, 0.04)',
              }
            }}
          >
            {bookingDetails.guests ? (
              <>
                {/* Male Adults - Using Male icon as person icon */}
                {(bookingDetails.guests.maleCount > 0 || parseInt(bookingDetails.guests.maleCount) > 0) && (
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                    <Man2TwoToneIcon sx={{ fontSize: 18, color: '#1976d2' }} />
                    <Typography sx={{ fontSize: '0.9rem', fontWeight: 600, color: 'text.primary' }}>
                      {bookingDetails.guests.maleCount || 0}
                    </Typography>
                  </Box>
                )}
                
                {/* Female Adults - Using Female icon as person icon */}
                {(bookingDetails.guests.femaleCount > 0 || parseInt(bookingDetails.guests.femaleCount) > 0) && (
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                    <Woman2TwoToneIcon sx={{ fontSize: 18, color: '#e91e63' }} />
                    <Typography sx={{ fontSize: '0.9rem', fontWeight: 600, color: 'text.primary' }}>
                      {bookingDetails.guests.femaleCount || 0}
                    </Typography>
                  </Box>
                )}
                
                {/* Children */}
                {(bookingDetails.guests.children > 0 || bookingDetails.guests.children === '0' || parseInt(bookingDetails.guests.children) > 0) && (
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                    <ChildIcon sx={{ fontSize: 18, color: '#ed6c02' }} />
                    <Typography sx={{ fontSize: '0.9rem', fontWeight: 600, color: 'text.primary' }}>
                      {bookingDetails.guests.children || 0}
                    </Typography>
                  </Box>
                )}
                
                {/* Infants */}
                {(bookingDetails.guests.infant > 0 || bookingDetails.guests.infant === '0' || parseInt(bookingDetails.guests.infant) > 0) && (
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                    <img 
                      src="/icons/infant.png" 
                      alt="infant" 
                      style={{ width: 18, height: 18, objectFit: 'contain' }}
                    />
                    <Typography sx={{ fontSize: '0.9rem', fontWeight: 600, color: 'text.primary' }}>
                      {bookingDetails.guests.infant || 0}
                    </Typography>
                  </Box>
                )}
                
                {/* Show "Not specified" if all counts are 0 or empty */}
                {(!bookingDetails.guests.maleCount && !bookingDetails.guests.femaleCount && 
                  !bookingDetails.guests.children && !bookingDetails.guests.infant) && (
                  <Typography sx={{ fontSize: '0.9rem', fontWeight: 500, color: 'text.secondary' }}>
                    Not specified
                  </Typography>
                )}
              </>
            ) : (
              <Typography sx={{ fontSize: '0.9rem', fontWeight: 500, color: 'text.secondary' }}>
                Not specified
              </Typography>
            )}
          </Box>
        </InfoField>
      </Box>
    </SectionPaper>
  );
};

export default TripDetailsComponent;

