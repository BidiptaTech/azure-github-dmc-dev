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
  
  switch(status.toLowerCase()) {
    case "confirmed":
      color = "success";
      icon = <CheckCircleOutline fontSize="small" />;
      break;
    case "pending":
      color = "warning";
      icon = <DonutLarge fontSize="small" />;
      break;
    case "cancelled":
      color = "error";
      icon = <Close fontSize="small" />;
      break;
    case "completed":
      color = "info";
      icon = <EventAvailable fontSize="small" />;
      break;
    default:
      color = "default";
  }
  
  return (
    <Chip 
      icon={icon}
      label={status} 
      color={color} 
      size="small" 
      sx={{ fontWeight: 500, minWidth: '100px', '& .MuiChip-icon': { fontSize: '16px' } }} 
    />
  );
};

// Get payment status chip
export const PaymentStatusChip = ({ status }) => {
  let color = "default";
  let icon = <AttachMoney fontSize="small" />;
  
  switch(status.toLowerCase()) {
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
      label={status} 
      color={color} 
      size="small" 
      variant="outlined"
      sx={{ fontWeight: 500, minWidth: '90px', '& .MuiChip-icon': { fontSize: '16px' } }} 
    />
  );
}; 