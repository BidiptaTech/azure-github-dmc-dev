import React, { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import { 
  Box, 
  Typography, 
  Paper, 
  Button, 
  Modal, 
  Fade, 
  Backdrop, 
  Divider, 
  Chip,
  IconButton
} from '@mui/material';
import { styled } from '@mui/material/styles';
import { 
  CheckCircle as CheckCircleIcon,
  Close as CloseIcon
} from '@mui/icons-material';

const StyledModal = styled(Modal)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  padding: theme.spacing(2),
  // Mobile responsive adjustments
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(1),
    alignItems: 'flex-start',
    paddingTop: theme.spacing(4)
  }
}));

const StyledPaper = styled(Paper)(({ theme }) => ({
  position: 'relative',
  padding: theme.spacing(5, 4, 4),
  borderRadius: theme.spacing(2),
  boxShadow: '0 10px 30px rgba(0, 0, 0, 0.25)',
  maxWidth: '600px',
  width: '100%',
  margin: theme.spacing(2),
  backgroundColor: '#fff',
  outline: 'none',
  overflow: 'hidden',
  // Mobile and tablet responsive styling
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(3, 3, 3),
    margin: theme.spacing(1.5),
    maxWidth: '500px',
    borderRadius: theme.spacing(1.5),
  },
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(2, 2, 2),
    margin: theme.spacing(1),
    maxWidth: '100%',
    borderRadius: theme.spacing(1),
    boxShadow: '0 8px 25px rgba(0, 0, 0, 0.2)',
  }
}));

const GradientHeader = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  height: '8px',
  background: 'linear-gradient(90deg, #3554d1, #6c7ee1)',
}));

const CloseButton = styled(IconButton)(({ theme }) => ({
  position: 'absolute',
  top: theme.spacing(1.5),
  right: theme.spacing(1.5),
  color: theme.palette.grey[500],
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    top: theme.spacing(1),
    right: theme.spacing(1),
    padding: theme.spacing(0.5),
    '& .MuiSvgIcon-root': {
      fontSize: '1.2rem'
    }
  }
}));

const StatusChip = styled(Chip)(({ theme }) => ({
  padding: theme.spacing(0.5, 1),
  fontWeight: 600,
  boxShadow: '0 2px 5px rgba(0, 0, 0, 0.1)',
  // Mobile responsive styling
  [theme.breakpoints.down('sm')]: {
    padding: theme.spacing(0.25, 0.75),
    fontSize: '0.875rem',
    '& .MuiChip-label': {
      padding: theme.spacing(0.25, 0.5)
    }
  }
}));

// Removed ProgressWrapper since we no longer need countdown progress

const ThankYouModal = ({ open, onClose, redirectUrl = '/dashboard/db-dashboard', delay = 7 }) => {
  // Removed countdown state since we don't want auto-redirect
  
  // Get enquiry data from Redux state only
  const enquiryState = useSelector((state) => state.enquiry);
  const { enquiryId, multiEnqId, tourId, id } = enquiryState;
  
  useEffect(() => {
    console.log('ThankYouModal useEffect triggered:', { 
      open, 
      enquiryId, 
      multiEnqId, 
      tourId, 
      id,
      redirectUrl 
    });
    
    if (!open) return;
    
    console.log('ThankYouModal is opening with Redux state:', { enquiryId, multiEnqId, tourId, id });
    
    // Removed countdown and auto-redirect functionality
  }, [open, redirectUrl, enquiryId, multiEnqId, tourId, id]);
  
  const handleCloseModal = () => {
    if (onClose) onClose();
  };
  
  // Generate a reference ID from Redux state only
  const getReferenceId = () => {
    console.log('getReferenceId called with Redux state:', { enquiryId, multiEnqId, tourId, id });
    
    // Priority 1: Use multi-enquiry ID (highest priority)
    if (multiEnqId) {
      console.log('Using multi-enquiry ID from Redux:', multiEnqId);
      return `${multiEnqId}`;
    }
    
    // Priority 2: Use enquiry ID
    if (enquiryId) {
      console.log('Using enquiry ID from Redux:', enquiryId);
      return `ENQ-${enquiryId}`;
    }
    
    // Priority 3: Use tour ID
    if (tourId) {
      console.log('Using tour ID from Redux:', tourId);
      return `TOUR-${tourId}`;
    }
    
    // Priority 4: Use fallback ID
    if (id) {
      console.log('Using fallback ID from Redux:', id);
      return `ENQ-${id}`;
    }
    
    // Priority 5: Generate a random ID if none is found
    const randomId = 'ENQ-' + Math.random().toString(36).substring(2, 10).toUpperCase();
    console.log('Generated random ID:', randomId);
    return randomId;
  };
  
  // Removed progress calculation since we don't have countdown anymore
  
  return (
    <StyledModal
      open={open}
      onClose={handleCloseModal}
      closeAfterTransition
      BackdropComponent={Backdrop}
      BackdropProps={{
        timeout: 500,
        style: { backgroundColor: 'rgba(0, 0, 0, 0.8)' }
      }}
    >
      <Fade in={open}>
        <StyledPaper>
          <GradientHeader />
          <CloseButton onClick={handleCloseModal}>
            <CloseIcon />
          </CloseButton>
          
          <Box sx={{ 
            textAlign: 'center', 
            mb: { xs: 3, sm: 4 }, 
            mt: { xs: 1, sm: 2 } 
          }}>
            <CheckCircleIcon sx={{ 
              fontSize: { xs: 48, sm: 60 }, 
              color: 'success.main', 
              mb: { xs: 1.5, sm: 2 } 
            }} />
            <Typography 
              variant="h5" 
              fontWeight={700} 
              sx={{ 
                mb: { xs: 0.5, sm: 1 },
                fontSize: { xs: '1.25rem', sm: '1.5rem', md: '1.5rem' }
              }}
            >
              Booking Submitted Successfully!
            </Typography>
            <Typography 
              variant="body1" 
              color="text.secondary"
              sx={{
                fontSize: { xs: '0.875rem', sm: '1rem' }
              }}
            >
              Your booking enquiry has been received and is being processed.
            </Typography>
          </Box>
          
          <Divider sx={{ mb: { xs: 2, sm: 3 } }} />
          
          <Box sx={{ textAlign: 'center', mb: { xs: 2, sm: 3 } }}>
            <Typography 
              variant="subtitle1" 
              fontWeight={600} 
              sx={{ 
                mb: { xs: 0.5, sm: 1 },
                fontSize: { xs: '0.875rem', sm: '1rem' }
              }}
            >
              Booking Reference
            </Typography>
            <Chip
              label={getReferenceId()}
              color="primary"
              sx={{ 
                fontSize: { xs: '0.875rem', sm: '1.1rem' }, 
                padding: { xs: '12px 10px', sm: '20px 15px' },
                fontWeight: 700,
                boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)',
                cursor: 'default',
                maxWidth: '100%',
                wordBreak: 'break-all'
              }}
            />
          </Box>
          
          <Box sx={{ textAlign: 'center', mb: { xs: 2, sm: 3 } }}>
            <Typography 
              variant="body2" 
              color="text.secondary" 
              sx={{ 
                mb: { xs: 0.5, sm: 1 },
                fontSize: { xs: '0.75rem', sm: '0.875rem' }
              }}
            >
              Status
            </Typography>
            <StatusChip
              icon={<CheckCircleIcon sx={{ fontSize: { xs: '1rem', sm: '1.25rem' } }} />}
              label="Enquiry Submitted"
              color="success"
            />
          </Box>
          
          <Box sx={{ 
            mt: { xs: 3, sm: 4 }, 
            textAlign: { xs: 'center', sm: 'end' } 
          }}>
            <Button
              variant="contained"
              color="primary"
              onClick={handleCloseModal}
              sx={{ 
                px: { xs: 2, sm: 3 }, 
                py: { xs: 0.75, sm: 1 }, 
                borderRadius: 2,
                boxShadow: '0 4px 10px rgba(53, 84, 209, 0.25)',
                fontSize: { xs: '0.875rem', sm: '1rem' },
                minWidth: { xs: '120px', sm: 'auto' }
              }}
            >
              Close
            </Button>
          </Box>
        </StyledPaper>
      </Fade>
    </StyledModal>
  );
};

export default ThankYouModal; 