import React from 'react';
import { Chip } from "@mui/material";
import {
  CheckCircleOutline,
  DonutLarge,
  Close,
  EventAvailable,
  AttachMoney,
  CreditCard
} from "@mui/icons-material";

// Get status chip based on status value
export const StatusChip = ({ status }) => {
  let color = "default";
  let icon = <CheckCircleOutline fontSize="small" />;
  
  // Handle numeric or string status values
  const statusStr = typeof status === 'string' 
      ? status.toLowerCase() 
      : status === 1 
          ? 'confirmed' 
          : status === 2 
              ? 'pending' 
              : status === 3 
                  ? 'cancelled' 
                  : status === 4 
                      ? 'completed' 
                      : 'pending';
  
  switch (statusStr) {
    case "confirmed":
    case 1:
      color = "success";
      icon = <CheckCircleOutline fontSize="small" />;
      break;
    case "pending":
    case 2:
      color = "warning";
      icon = <DonutLarge fontSize="small" />;
      break;
    case "cancelled":
    case 3:
      color = "error";
      icon = <Close fontSize="small" />;
      break;
    case "completed":
    case 4:
      color = "info";
      icon = <EventAvailable fontSize="small" />;
      break;
    default:
      color = "default";
  }
  
  // Convert status to display text
  const displayText = typeof statusStr === 'number' 
      ? statusStr === 1 
          ? 'Confirmed' 
          : statusStr === 2 
              ? 'Pending' 
              : statusStr === 3 
                  ? 'Cancelled' 
                  : statusStr === 4 
                      ? 'Completed' 
                      : 'Pending'
      : statusStr.charAt(0).toUpperCase() + statusStr.slice(1);
  
  return (
    <Chip 
      icon={icon}
      label={displayText} 
      color={color} 
      size="small" 
      sx={{ 
        fontWeight: 500, 
        minWidth: '80px', 
        height: '24px',
        fontSize: '0.75rem',
        '& .MuiChip-icon': { fontSize: '14px' },
        '& .MuiChip-label': { px: 1 }
      }} 
    />
  );
};

// Get payment status chip
export const PaymentStatusChip = ({ status }) => {
  let color = "default";
  let icon = <AttachMoney fontSize="small" />;
  
  const statusStr = typeof status === 'string' ? status.toLowerCase() : 'unpaid';
  
  switch (statusStr) {
    case "paid":
      color = "success";
      icon = <AttachMoney fontSize="small" />;
      break;
    case "partial":
      color = "warning";
      icon = <CreditCard fontSize="small" />;
      break;
    case "unpaid":
      color = "error";
      icon = <CreditCard fontSize="small" />;
      break;
    case "refunded":
      color = "default";
      icon = <AttachMoney fontSize="small" />;
      break;
    default:
      color = "default";
  }
  
  return (
    <Chip 
      icon={icon}
      label={statusStr.charAt(0).toUpperCase() + statusStr.slice(1)} 
      color={color} 
      size="small" 
      variant="outlined"
      sx={{ fontWeight: 500, minWidth: '90px', '& .MuiChip-icon': { fontSize: '16px' } }} 
    />
  );
}; 