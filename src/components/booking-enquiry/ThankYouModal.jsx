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
}));

const StatusChip = styled(Chip)(({ theme }) => ({
  padding: theme.spacing(0.5, 1),
  fontWeight: 600,
  boxShadow: '0 2px 5px rgba(0, 0, 0, 0.1)',
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
          
          <Box sx={{ textAlign: 'center', mb: 4, mt: 2 }}>
            <CheckCircleIcon sx={{ fontSize: 60, color: 'success.main', mb: 2 }} />
            <Typography variant="h5" fontWeight={700} sx={{ mb: 1 }}>
              Booking Submitted Successfully!
            </Typography>
            <Typography variant="body1" color="text.secondary">
              Your booking enquiry has been received and is being processed.
            </Typography>
          </Box>
          
          <Divider sx={{ mb: 3 }} />
          
          <Box sx={{ textAlign: 'center', mb: 3 }}>
            <Typography variant="subtitle1" fontWeight={600} sx={{ mb: 1 }}>
              Booking Reference
            </Typography>
            <Chip
              label={getReferenceId()}
              color="primary"
              sx={{ 
                fontSize: '1.1rem', 
                padding: '20px 15px',
                fontWeight: 700,
                boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)',
                cursor: 'default'
              }}
            />
          </Box>
          
          <Box sx={{ textAlign: 'center', mb: 3 }}>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
              Status
            </Typography>
            <StatusChip
              icon={<CheckCircleIcon />}
              label="Enquiry Submitted"
              color="success"
            />
          </Box>
          
          <Box sx={{ mt: 4, textAlign: 'end' }}>
            <Button
              variant="contained"
              color="primary"
              onClick={handleCloseModal}
              sx={{ 
                px: 3, 
                py: 1, 
                borderRadius: 2,
                boxShadow: '0 4px 10px rgba(53, 84, 209, 0.25)',
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