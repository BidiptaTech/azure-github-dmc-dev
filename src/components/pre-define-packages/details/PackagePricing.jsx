import React from 'react';
import { Paper, Typography, Box, Button, Grid, Divider } from '@mui/material';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import PersonIcon from '@mui/icons-material/Person';
import { useSelector } from 'react-redux';

const PackagePricing = ({ packageData }) => {
  // Get search params from Redux store
  const { searchParams } = useSelector(state => state.prePackages);
  
  // Calculate total price based on number of adults and children
  const adultPrice = parseFloat(packageData.price_adult) || 0;
  const childPrice = parseFloat(packageData.price_child) || 0;
  
  // Use search params for passenger counts if available, otherwise fallback to packageData
  const adultCount = searchParams?.adults ? parseInt(searchParams.adults) : 1;
  const childCount = searchParams?.children ? parseInt(searchParams.children) : 0;
  const maleCount = searchParams?.male_count ? parseInt(searchParams.male_count) : 0;
  const femaleCount = searchParams?.female_count ? parseInt(searchParams.female_count) : 0;
  
  const totalAdultPrice = adultPrice * adultCount;
  const totalChildPrice = childPrice * childCount;
  const totalPrice = totalAdultPrice + totalChildPrice;
  
  // Check if child price is available
  const hasChildPrice = packageData.price_child && parseFloat(packageData.price_child) > 0;
  
  return (
    <Paper elevation={2} sx={{ borderRadius: '12px', overflow: 'hidden', height: '100%' }}>
      <Box sx={{ bgcolor: 'primary.main', color: 'primary.contrastText', px: 3, py: 1.5, display: 'flex', alignItems: 'center' }}>
        <AttachMoneyIcon sx={{ mr: 1 }} />
        <Typography variant="h6" fontWeight="bold">Price Summary</Typography>
      </Box>
      
      <Box sx={{ p: 3 }}>
        {/* Adult Price Section */}
        <Box sx={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: 'center',
          py: 1,
          borderBottom: '1px dashed #e0e0e0'
        }}>
          <Typography variant="body1" fontWeight="medium">
            Adult Price
          </Typography>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <AttachMoneyIcon color="primary" />
            <Typography variant="h6" color="primary" fontWeight="bold">
              {packageData.price_adult}
            </Typography>
          </Box>
        </Box>
        
        {/* Adult Count and Total */}
        <Box sx={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: 'center',
          py: 1,
          borderBottom: '1px dashed #e0e0e0'
        }}>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <PersonIcon fontSize="small" sx={{ mr: 0.5 }} />
            <Typography variant="body2">
              {adultCount} {adultCount > 1 ? 'Adults' : 'Adult'} 
              {(maleCount > 0 || femaleCount > 0) && (
                <Typography component="span" variant="caption" sx={{ ml: 0.5 }}>
                  ({maleCount} male, {femaleCount} female)
                </Typography>
              )}
            </Typography>
          </Box>
          <Typography variant="body1" fontWeight="medium">
            ${totalAdultPrice.toFixed(2)}
          </Typography>
        </Box>
        
        {/* Child Price Section - Only show if child price exists */}
        {hasChildPrice && childCount > 0 && (
          <>
            <Box sx={{ 
              display: 'flex', 
              justifyContent: 'space-between', 
              alignItems: 'center',
              py: 1,
              borderBottom: '1px dashed #e0e0e0'
            }}>
              <Typography variant="body1" fontWeight="medium">
                Child Price
              </Typography>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <AttachMoneyIcon color="primary" />
                <Typography variant="h6" fontWeight="bold">
                  {packageData.price_child}
                </Typography>
              </Box>
            </Box>
            
            {/* Child Count and Total - Only show if there are children */}
            <Box sx={{ 
              display: 'flex', 
              justifyContent: 'space-between', 
              alignItems: 'center',
              py: 1,
              borderBottom: '1px dashed #e0e0e0'
            }}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <PersonIcon fontSize="small" sx={{ mr: 0.5 }} />
                <Typography variant="body2">
                  {childCount} {childCount > 1 ? 'Children' : 'Child'}
                </Typography>
              </Box>
              <Typography variant="body1" fontWeight="medium">
                ${totalChildPrice.toFixed(2)}
              </Typography>
            </Box>
          </>
        )}
        
        {/* Duration */}
        <Box sx={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: 'center',
          py: 1,
          borderBottom: '1px dashed #e0e0e0'
        }}>
          <Typography variant="body1" fontWeight="medium">
            Duration
          </Typography>
          <Typography variant="body1" fontWeight="bold">
            {packageData.duration_days} Days
          </Typography>
        </Box>
        
        {/* Travel Dates */}
        {searchParams?.check_in && searchParams?.check_out && (
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            py: 1,
            borderBottom: '1px dashed #e0e0e0'
          }}>
            <Typography variant="body1" fontWeight="medium">
              Travel Dates
            </Typography>
            <Typography variant="body2" fontWeight="medium">
              {new Date(searchParams.check_in).toLocaleDateString()} - {new Date(searchParams.check_out).toLocaleDateString()}
            </Typography>
          </Box>
        )}
        
        {/* Total Price */}
        <Box sx={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: 'center',
          py: 1.5,
          mt: 1,
          bgcolor: 'primary.light',
          px: 2,
          borderRadius: '8px'
        }}>
          <Typography variant="h6" fontWeight="bold">
            Total Price
          </Typography>
          <Typography variant="h6" color="primary.dark" fontWeight="bold">
            ${totalPrice.toFixed(2)}
          </Typography>
        </Box>
        
        <Button 
          variant="contained" 
          color="primary" 
          fullWidth 
          size="large"
          sx={{ py: 1.5, mt: 2 }}
        >
          Book This Package
        </Button>
        <Typography variant="body2" color="text.secondary" align="center" sx={{ mt: 1 }}>
          * Prices are per person
        </Typography>
      </Box>
    </Paper>
  );
};

export default PackagePricing; 