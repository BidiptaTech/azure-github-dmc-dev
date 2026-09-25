import React from 'react';
import { Paper, Typography, Box, Divider, List, ListItem, ListItemIcon, ListItemText } from '@mui/material';
import GavelIcon from '@mui/icons-material/Gavel';
import InfoIcon from '@mui/icons-material/Info';

const TermsConditions = ({ packageData }) => {
  // Function to parse and format T&C content
  const formatTerms = (content) => {
    if (!content) return [];
    
    // If content is already an array, return it
    if (Array.isArray(content)) return content;
    
    // Otherwise, try to split by newlines or periods
    if (content.includes('\n')) {
      return content.split('\n').filter(item => item.trim() !== '');
    }
    
    if (content.includes('.')) {
      return content.split('.').filter(item => item.trim() !== '');
    }
    
    // If no clear delimiter, return as a single item
    return [content];
  };
  
  const termsConditions = formatTerms(packageData.terms_conditions);
  
  // Default terms if none provided
  const defaultTerms = [
    "Bookings must be made at least 7 days in advance.",
    "Full payment is required to confirm your booking.",
    "Cancellations made 30 days or more before departure will receive a full refund minus processing fees.",
    "Cancellations made less than 30 days before departure are non-refundable.",
    "The tour operator reserves the right to modify the itinerary due to unforeseen circumstances.",
    "Travel insurance is recommended for all participants.",
    "Participants must have valid travel documents.",
    "The operator is not responsible for any loss, injury, or damage during the tour."
  ];
  
  const termsList = termsConditions.length > 0 ? termsConditions : defaultTerms;
  
  return (
    <Paper elevation={1} sx={{ p: 3, borderRadius: '12px' }}>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
        <GavelIcon sx={{ mr: 1 }} />
        <Typography variant="h5" fontWeight="bold">
          Terms & Conditions
        </Typography>
      </Box>
      
      <Typography variant="body1" paragraph color="text.secondary">
        Please read the following terms and conditions carefully before booking:
      </Typography>
      
      <Divider sx={{ my: 2 }} />
      
      <List>
        {termsList.map((term, index) => (
          <ListItem key={index} alignItems="flex-start">
            <ListItemIcon>
              <InfoIcon color="action" />
            </ListItemIcon>
            <ListItemText primary={term.trim()} />
          </ListItem>
        ))}
      </List>
      
      <Divider sx={{ my: 2 }} />
      
      <Box sx={{ mt: 3 }}>
        <Typography variant="body2" color="text.secondary">
          By booking this package, you agree to all terms and conditions listed above. For a complete version of our terms and conditions, please contact our customer support.
        </Typography>
      </Box>
    </Paper>
  );
};

export default TermsConditions; 