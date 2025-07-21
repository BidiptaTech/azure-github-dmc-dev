import React, { useState } from 'react';
import { 
  Box, Typography, Button, Menu, MenuItem, Chip, Avatar,
  Divider
} from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import PersonIcon from '@mui/icons-material/Person';
import RestaurantMenuIcon from '@mui/icons-material/RestaurantMenu';

/**
 * Meal Plan Selection component
 * @param {Object} props Component props
 * @returns {JSX.Element} Meal plan selection component
 */
const MealPlanSelection = ({ 
  selectedGuests, 
  guestMealPlans, 
  handleGuestMealPlanChange, 
  handleGuestCountChange, // Add this prop for updating guest count
  bedType, 
  searchCriteria,
  mealPlans
}) => {
  const [guestMealAnchorEl, setGuestMealAnchorEl] = useState(null);
  const isGuestMealMenuOpen = Boolean(guestMealAnchorEl);
  
  // Helper function to get icon for meal plans
  const getMealPlanIcon = () => {
    return <RestaurantMenuIcon sx={{ fontSize: 18 }} />;
  };
  
  return (
    <Box sx={{ mt: 2 }}>
      <Button
        fullWidth
        variant="outlined"
        disabled={!bedType}
        onClick={(event) => setGuestMealAnchorEl(event.currentTarget)}
        sx={{ 
          justifyContent: 'flex-start',
          textAlign: 'left',
          py: 1.5,
          px: 2,
          color: 'text.primary',
          borderColor: 'rgba(0, 0, 0, 0.23)',
          '&:hover': {
            borderColor: 'rgba(0, 0, 0, 0.87)',
          }
        }}
        endIcon={<ExpandMoreIcon />}
      >
        {(() => {
          // Try multiple possible property names for guest data
          const adults = parseInt(searchCriteria?.guests?.Adults || searchCriteria?.guests?.adults || 1);
          const children = parseInt(searchCriteria?.guests?.Children || searchCriteria?.guests?.children || 0);
          const infants = parseInt(searchCriteria?.guests?.Infants || searchCriteria?.guests?.infants || 0);
          
          // Calculate total tour guests (adults + children, infants usually don't need separate beds/meals)
          const totalTourGuests = adults + children;
          const maxBedOccupancy = bedType?.max_occupancy || totalTourGuests;
          
          console.log("MealPlanSelection - Guest calculation:", {
            searchCriteria: searchCriteria?.guests,
            adults,
            children,
            infants,
            totalTourGuests,
            maxBedOccupancy,
            selectedGuests
          });
          
          if (selectedGuests === 0) {
            return <Typography variant="body2" color="text.secondary">
              Select number of guests (Tour: {totalTourGuests} [A:{adults}, C:{children}]{bedType ? `, Bed: ${maxBedOccupancy}` : ''})
            </Typography>;
          }
          if (guestMealPlans.length === 0 || guestMealPlans.every(plan => (plan?.id || plan) === 'self')) {
            return <Typography variant="body2">
              {selectedGuests}/{totalTourGuests} guest{selectedGuests > 1 ? 's' : ''} - Select meal plans
            </Typography>;
          }
          const summary = guestMealPlans.map((plan, index) => {
            // Handle both old format (string) and new format (object)
            const planId = plan?.id || plan;
            const planName = plan?.name || mealPlans.find(p => p.id === planId)?.title || 'Room Only';
            return `Guest ${index + 1}: ${planName}`;
          }).join(', ');
          return <Typography variant="body2" noWrap>
            {summary.length > 50 ? `${selectedGuests}/${totalTourGuests} guests with meal plans` : summary}
          </Typography>;
        })()}
      </Button>
      
      <Menu
        anchorEl={guestMealAnchorEl}
        open={isGuestMealMenuOpen}
        onClose={(event, reason) => {
          // Only close on backdrop click or escape key
          if (reason === 'backdropClick' || reason === 'escapeKeyDown') {
            setGuestMealAnchorEl(null);
          }
        }}
        MenuListProps={{
          'aria-labelledby': 'guest-meal-button',
          sx: { maxHeight: 400, width: 350 }
        }}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'left',
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'left',
        }}
      >
        {/* Guest count section */}
        <MenuItem disabled sx={{ bgcolor: '#e3f2fd', fontWeight: 'bold' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
            <Typography variant="subtitle2">Number of Guests</Typography>
            <Typography variant="caption" color="text.secondary">
              {(() => {
                const adults = parseInt(searchCriteria?.guests?.Adults || searchCriteria?.guests?.adults || 1);
                const children = parseInt(searchCriteria?.guests?.Children || searchCriteria?.guests?.children || 0);
                const totalTourGuests = adults + children;
                const maxBedOccupancy = bedType?.max_occupancy || totalTourGuests;
                return `Tour: ${totalTourGuests} [A:${adults}, C:${children}]${bedType ? ` | Bed: ${maxBedOccupancy}` : ''}`;
              })()}
            </Typography>
          </Box>
        </MenuItem>
        
        {/* Guest selection options */}
        {(() => {
          // Get total guests from tour package with consistent property name handling
          const adults = parseInt(searchCriteria?.guests?.Adults || searchCriteria?.guests?.adults || 1);
          const children = parseInt(searchCriteria?.guests?.Children || searchCriteria?.guests?.children || 0);
          const totalTourGuests = adults + children;
          
          // Get bed occupancy limit
          const maxBedOccupancy = bedType?.max_occupancy || totalTourGuests;
          
          // Calculate maximum selectable guests (minimum of tour guests and bed capacity)
          const maxSelectableGuests = Math.min(totalTourGuests, maxBedOccupancy);
          
          return Array.from({ length: maxSelectableGuests }, (_, i) => i + 1).map((num) => (
            <MenuItem 
              key={`guest-count-${num}`}
              onClick={() => {
                if (handleGuestCountChange) {
                  handleGuestCountChange(num);
                }
              }}
              sx={{ 
                pl: 4,
                bgcolor: selectedGuests === num ? 'rgba(53, 84, 209, 0.08)' : 'transparent'
              }}
            >
              <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', justifyContent: 'space-between' }}>
                <Typography>
                  {num} {num === 1 ? 'Guest' : 'Guests'}
                  {num === totalTourGuests && (
                    <Chip label="From Tour" size="small" color="info" sx={{ ml: 1, height: 18, fontSize: '0.6rem' }} />
                  )}
                  {num === maxBedOccupancy && bedType && (
                    <Chip label="Max Bed" size="small" color="warning" sx={{ ml: 1, height: 18, fontSize: '0.6rem' }} />
                  )}
                </Typography>
                {selectedGuests === num && (
                  <Chip label="✓ Selected" size="small" color="primary" sx={{ height: 20 }} />
                )}
              </Box>
            </MenuItem>
          ));
        })()}

        {/* Divider */}
        {selectedGuests > 0 && (
          <MenuItem disabled sx={{ borderBottom: '2px solid #e0e0e0', py: 0, minHeight: 'auto' }}>
            <Box sx={{ width: '100%' }} />
          </MenuItem>
        )}

        {/* Meal plans for each guest */}
        {selectedGuests > 0 && Array.from({ length: selectedGuests }).map((_, guestIndex) => (
          <Box key={guestIndex}>
            {/* Guest header with person icon */}
            <MenuItem disabled sx={{ bgcolor: '#f5f5f5', fontWeight: 'bold' }}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <PersonIcon sx={{ mr: 1, fontSize: 18 }} />
                <Typography variant="subtitle2">
                  Guest {guestIndex + 1}
                </Typography>
              </Box>
            </MenuItem>
            
            {/* Meal options for this guest */}
            {mealPlans.map((plan) => (
              <MenuItem 
                key={`${guestIndex}-${plan.id}`}
                onClick={() => handleGuestMealPlanChange(guestIndex, plan.id)}
                sx={{ 
                  pl: 4,
                  bgcolor: (guestMealPlans[guestIndex]?.id || guestMealPlans[guestIndex]) === plan.id ? 'rgba(53, 84, 209, 0.08)' : 'transparent'
                }}
              >
                <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', justifyContent: 'space-between' }}>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    {getMealPlanIcon()}
                    <Typography sx={{ ml: 1 }}>{plan.title}</Typography>
                    {(guestMealPlans[guestIndex]?.id || guestMealPlans[guestIndex]) === plan.id && (
                      <Chip label="✓ Selected" size="small" color="primary" sx={{ ml: 1, height: 20 }} />
                    )}
                  </Box>
                  <Typography variant="body2" color="text.secondary">
                    {plan.price > 0 ? `$${parseFloat(plan.price).toFixed(2)}` : 'Free'}
                  </Typography>
                </Box>
              </MenuItem>
            ))}
            
            {/* Divider between guests */}
            {guestIndex < selectedGuests - 1 && (
              <MenuItem disabled sx={{ borderBottom: '1px solid #e0e0e0', py: 0, minHeight: 'auto' }}>
                <Box sx={{ width: '100%' }} />
              </MenuItem>
            )}
          </Box>
        ))}
      </Menu>
      
      {/* Summary */}
      {guestMealPlans.length > 0 && guestMealPlans.some(plan => (plan?.id || plan) && (plan?.id || plan) !== 'self') && (
        <Box sx={{ mt: 2, p: 1, bgcolor: 'rgba(53, 84, 209, 0.05)', borderRadius: 1 }}>
          <Typography variant="body2" color="text.secondary" sx={{ mb: 0.5 }}>
            <strong>Total Cost:</strong> ${guestMealPlans.reduce((total, mealPlanData) => {
              // Handle both old format (string) and new format (object)
              if (mealPlanData?.price !== undefined) {
                return total + mealPlanData.price;
              } else {
                const plan = mealPlans.find(p => p.id === mealPlanData);
                return total + (plan?.price || 0);
              }
            }, 0).toFixed(2)}
          </Typography>
        </Box>
      )}
    </Box>
  );
};

export default MealPlanSelection; 