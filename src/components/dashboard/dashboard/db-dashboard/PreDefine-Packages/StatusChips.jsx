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
  let displayText = "Pending";
  
  // Convert status to number if it's a string
  const statusNum = typeof status === 'string' ? parseInt(status, 10) : status;
  
  // Handle numeric status values from API
  switch (statusNum) {
    case 1: // Confirm
      color = "success";
      icon = <CheckCircleOutline fontSize="small" />;
      displayText = "Confirm";
      break;
    case 2: // Definite
      color = "info";
      icon = <EventAvailable fontSize="small" />;
      displayText = "Definite";
      break;
    case 3: // Actual
      color = "primary";
      icon = <DonutLarge fontSize="small" />;
      displayText = "Actual";
      break;
    case 4: // Cancelled
      color = "error";
      icon = <Close fontSize="small" />;
      displayText = "Cancelled";
      break;
    case 5: // Refund-pending
      color = "warning";
      icon = <AttachMoney fontSize="small" />;
      displayText = "Refund-pending";
      break;
    case 6: // Refunded
      color = "default";
      icon = <AttachMoney fontSize="small" />;
      displayText = "Refunded";
      break;
    case 7: // Cancel-confirm
      color = "error";
      icon = <Close fontSize="small" />;
      displayText = "Cancel-confirm";
      break;
    case 8: // Completed
      color = "success";
      icon = <EventAvailable fontSize="small" />;
      displayText = "Completed";
      break;
    default:
      color = "default";
      icon = <DonutLarge fontSize="small" />;
      displayText = "Pending";
  }
  
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