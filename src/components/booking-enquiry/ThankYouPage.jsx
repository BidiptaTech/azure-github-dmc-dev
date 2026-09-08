import React, { useEffect, useState } from 'react';
import { Box, Typography, Paper, Button, Container, Grid, Divider, Chip } from '@mui/material';
import { styled } from '@mui/material/styles';
import { 
  CheckCircle as CheckCircleIcon,
  Home as HomeIcon,
  Dashboard as DashboardIcon,
  Schedule as ScheduleIcon
} from '@mui/icons-material';

const GradientBox = styled(Box)(({ theme }) => ({
  background: 'linear-gradient(135deg, #3554d1 0%, #6c7ee1 100%)',
  padding: theme.spacing(3),
  borderRadius: theme.spacing(2),
  color: 'white',
  textAlign: 'center',
  marginBottom: theme.spacing(4),
  boxShadow: '0 8px 20px rgba(53, 84, 209, 0.2)',
}));

const StyledPaper = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(4),
  borderRadius: theme.spacing(2),
  boxShadow: '0 10px 30px rgba(0, 0, 0, 0.1)',
  maxWidth: '800px',
  margin: '0 auto',
  backgroundColor: 'rgba(255, 255, 255, 0.95)',
}));

const ActionButton = styled(Button)(({ theme }) => ({
  padding: theme.spacing(1.5, 3),
  borderRadius: theme.spacing(1.5),
  fontWeight: 600,
  boxShadow: '0 4px 10px rgba(0, 0, 0, 0.1)',
  transition: 'all 0.3s ease',
  '&:hover': {
    transform: 'translateY(-3px)',
    boxShadow: '0 6px 15px rgba(0, 0, 0, 0.15)',
  },
}));

const StatusChip = styled(Chip)(({ theme }) => ({
  padding: theme.spacing(0.5, 1),
  fontWeight: 600,
  boxShadow: '0 2px 5px rgba(0, 0, 0, 0.1)',
}));

const ThankYouPage = ({ onBackToHome }) => {
  const [enquiryData, setEnquiryData] = useState(null);
  const [countdown, setCountdown] = useState(10);
  
  // Retrieve enquiry data from localStorage on component mount
  useEffect(() => {
    const enquiryDataString = localStorage.getItem('enquiryData');
    if (enquiryDataString) {
      try {
        const parsedData = JSON.parse(enquiryDataString);
        setEnquiryData(parsedData);
      } catch (error) {
        console.error('Error parsing enquiry data:', error);
      }
    }
    
    // Start countdown for auto-redirect
    const timer = setInterval(() => {
      setCountdown(prevCount => {
        if (prevCount <= 1) {
          clearInterval(timer);
          window.location.href = '/dashboard/db-dashboard';
          return 0;
        }
        return prevCount - 1;
      });
    }, 1000);
    
    // Clean up the timer and localStorage on unmount
    return () => {
      clearInterval(timer);
      localStorage.removeItem('enquirySubmitted');
      localStorage.removeItem('enquiryData');
    };
  }, []);
  
  // Generate a reference ID from the enquiry data
  const getReferenceId = () => {
    if (!enquiryData) return 'ENQ-' + Math.random().toString(36).substring(2, 10).toUpperCase();
    
    // Try to extract an ID from the data
    const id = enquiryData.data?.enquiry_id || 
               enquiryData.enquiry_id || 
               enquiryData.id || 
               enquiryData.order?.id;
               
    if (id) return `ENQ-${id}`;
    
    // Generate a random ID if none is found
    return 'ENQ-' + Math.random().toString(36).substring(2, 10).toUpperCase();
  };
  
  // Handle dashboard navigation
  const goToDashboard = () => {
    window.location.href = '/dashboard/db-dashboard';
  };

  return (
    <Container maxWidth="md">
      <StyledPaper>
        <GradientBox>
          <CheckCircleIcon sx={{ fontSize: 60, mb: 2 }} />
          <Typography variant="h4" fontWeight={700} sx={{ mb: 1 }}>
            Booking Submitted Successfully!
          </Typography>
          <Typography variant="body1">
            Your booking enquiry has been received and is being processed.
          </Typography>
        </GradientBox>
        
        <Box sx={{ textAlign: 'center', mb: 4 }}>
          <Typography variant="h5" fontWeight={600} sx={{ mb: 2 }}>
            Booking Reference:
          </Typography>
          <Chip
            label={getReferenceId()}
            color="primary"
            sx={{ 
              fontSize: '1.2rem', 
              padding: '25px 15px',
              fontWeight: 700,
              boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)',
              cursor: 'default'
            }}
          />
        </Box>
        
        <Divider sx={{ mb: 4 }} />
        
        <Grid container spacing={3} justifyContent="center" sx={{ mb: 4 }}>
          <Grid item xs={12} sm={6}>
            <Box sx={{ textAlign: 'center' }}>
              <Typography variant="body1" color="text.secondary" sx={{ mb: 1 }}>
                Status
              </Typography>
              <StatusChip
                icon={<CheckCircleIcon />}
                label="Enquiry Submitted"
                color="success"
              />
            </Box>
          </Grid>
          <Grid item xs={12} sm={6}>
            <Box sx={{ textAlign: 'center' }}>
              <Typography variant="body1" color="text.secondary" sx={{ mb: 1 }}>
                Auto-redirect to dashboard in
              </Typography>
              <Box sx={{ 
                display: 'inline-flex',
                alignItems: 'center',
                gap: 1
              }}>
                <ScheduleIcon color="primary" />
                <Typography variant="h6" fontWeight={600}>
                  {countdown} seconds
                </Typography>
              </Box>
            </Box>
          </Grid>
        </Grid>
        
        <Box sx={{ display: 'flex', justifyContent: 'center', gap: 2, flexWrap: 'wrap' }}>
          <ActionButton
            variant="contained"
            color="primary"
            startIcon={<DashboardIcon />}
            onClick={goToDashboard}
          >
            Go to Dashboard
          </ActionButton>
          <ActionButton
            variant="outlined"
            color="primary"
            startIcon={<HomeIcon />}
            onClick={onBackToHome}
          >
            Back to Home
          </ActionButton>
        </Box>
      </StyledPaper>
    </Container>
  );
};

export default ThankYouPage; 