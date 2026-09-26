import React, { useState, useEffect } from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, Typography } from '@mui/material';

const ModalTest = ({ open, onClose, bookingId }) => {
  // Track internal open state separately from props
  const [internalOpen, setInternalOpen] = useState(false);
  
  // Sync external open prop to internal state
  useEffect(() => {
    if (open) {
      console.log("ModalTest: Setting internal open state to true");
      setInternalOpen(true);
    }
  }, [open]);
  
  // Handle close action
  const handleClose = () => {
    console.log("ModalTest: Handling close action");
    // Set internal state first
    setInternalOpen(false);
    
    // Notify parent component with a delay (allow animation to complete)
    setTimeout(() => {
      console.log("ModalTest: Notifying parent component of close");
      if (onClose) onClose();
    }, 300);
  };
  
  return (
    <Dialog 
      open={internalOpen} 
      onClose={handleClose}
      keepMounted
    >
      <DialogTitle>Test Modal</DialogTitle>
      <DialogContent>
        <Typography>
          Booking ID: {bookingId || 'Not provided'}
        </Typography>
        <Typography>
          Internal open state: {internalOpen ? 'Open' : 'Closed'}
        </Typography>
        <Typography>
          External open prop: {open ? 'Open' : 'Closed'}
        </Typography>
      </DialogContent>
      <DialogActions>
        <Button onClick={handleClose}>Close</Button>
      </DialogActions>
    </Dialog>
  );
};

export default ModalTest; 