import React, { useState, useMemo } from 'react';
import { 
  Box, Typography, Button, Menu, MenuItem, Chip, Avatar,
  Divider, Paper, Alert
} from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import PersonIcon from '@mui/icons-material/Person';
import RestaurantMenuIcon from '@mui/icons-material/RestaurantMenu';
import FreeBreakfastIcon from '@mui/icons-material/FreeBreakfast';
import LunchDiningIcon from '@mui/icons-material/LunchDining';
import DinnerDiningIcon from '@mui/icons-material/DinnerDining';
import { IoPersonSharp } from "react-icons/io5";
import { useSelector } from 'react-redux';

/**
 * Meal Plan Selection component
 * @param {Object} props Component props
 * @returns {JSX.Element} Meal plan selection component
 */
const MealPlanSelection = ({ 
  selectedGuests, 
  guestMealPlans, 
  handleGuestMealPlanChange, 
  handleGuestCountChange, 
  bedType, 
  searchCriteria,
  mealPlans,
  roomData = null, // Added room data prop similar to RenderRoomCards
  isLoading = false
}) => {
  const [guestMealAnchorEl, setGuestMealAnchorEl] = useState(null);
  const isGuestMealMenuOpen = Boolean(guestMealAnchorEl);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // Helper function to get icon for meal plans based on plan ID
  const getMealPlanIcon = (planId) => {
    switch(planId) {
      case 'breakfast':
      case 'breakfast_lunch':
      case 'breakfast_dinner':
        return <FreeBreakfastIcon sx={{ fontSize: 18 }} />;
      case 'lunch':
      case 'lunch_dinner':
        return <LunchDiningIcon sx={{ fontSize: 18 }} />;
      case 'dinner':
        return <DinnerDiningIcon sx={{ fontSize: 18 }} />;
      case 'fullboard':
        return <RestaurantMenuIcon sx={{ fontSize: 18 }} />;
      default:
        return <RestaurantMenuIcon sx={{ fontSize: 18 }} />;
    }
  };
  

  
  // Check if loading or missing essential data
  if (isLoading) {
    return (
      <Box sx={{ width: '100%', mt: 2 }}>
        <Paper elevation={1} sx={{ p: 2, bgcolor: '#f5f5f5', borderRadius: 2 }}>
          <Typography variant="body2">Loading meal plan options...</Typography>
        </Paper>
      </Box>
    );
  }
  
  // Check if room data or bed type is missing
  if (!bedType) {
    return (
      <Box sx={{ width: '100%', mt: 2 }}>
        <Alert 
          severity="info" 
          sx={{ 
            borderRadius: '12px'
          }}
        >
          <Typography variant="body2">
            Please select a bed type to view available meal plans.
          </Typography>
        </Alert>
      </Box>
    );
  }
  
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
          
          // console.log("MealPlanSelection - Guest calculation:", {
          //   searchCriteria: searchCriteria?.guests,
          //   adults,
          //   children,
          //   infants,
          //   totalTourGuests,
          //   maxBedOccupancy,
          //   selectedGuests
          // });
          
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
        {/* Guest count section with improved styling */}
        <MenuItem disabled sx={{ bgcolor: '#e3f2fd', fontWeight: 'bold', borderBottom: '1px solid #bbdefb' }}>
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
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  {/* Display person icons similar to RenderRoomCards */}
                  {Array.from({ length: num }).map((_, iconIndex) => (
                    <IoPersonSharp 
                      key={iconIndex}
                      size={16}
                      style={{ 
                        color: '#3554D1',
                        marginRight: '2px'
                      }}
                    />
                  ))}
                  <Typography sx={{ ml: 1 }}>
                    {num} {num === 1 ? 'Guest' : 'Guests'}
                    {num === totalTourGuests && (
                      <Chip label="From Tour" size="small" color="info" sx={{ ml: 1, height: 18, fontSize: '0.6rem' }} />
                    )}
                    {num === maxBedOccupancy && bedType && (
                      <Chip label="Max Bed" size="small" color="warning" sx={{ ml: 1, height: 18, fontSize: '0.6rem' }} />
                    )}
                  </Typography>
                </Box>
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
            {/* Guest header with improved styling similar to RenderRoomCards */}
            <MenuItem disabled sx={{ 
              bgcolor: '#e6f2ff', 
              fontWeight: 'bold',
              padding: '8px',
              borderRadius: '4px',
              boxShadow: '0 1px 3px rgba(0,0,0,0.05)',
              my: 1
            }}>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <IoPersonSharp 
                    size={18}
                    style={{ 
                      color: '#3554D1',
                      marginRight: '8px'
                    }}
                  />
                  <Typography variant="subtitle2">
                    Guest {guestIndex + 1}
                  </Typography>
                </Box>
                {/* Show person count in similar style to RenderRoomCards */}
                <Typography variant="caption" color="text.secondary">
                  Person {guestIndex + 1} of {selectedGuests}
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
                    {getMealPlanIcon(plan.id)}
                    <Box sx={{ ml: 1 }}>
                      <Typography variant="body2" sx={{ fontWeight: 500 }}>{plan.title}</Typography>

                      
                      {plan.description && PriceHide === "0" && (
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block' }}>
                          {plan.description}
                        </Typography>
                      )}
                    </Box>
                    {(guestMealPlans[guestIndex]?.id || guestMealPlans[guestIndex]) === plan.id && (
                      <Chip label="✓ Selected" size="small" color="primary" sx={{ ml: 1, height: 20 }} />
                    )}
                  </Box>
                  <Box>
                    <Typography variant="body2" fontWeight="500" color="primary.main">
                            {PriceHide === "0" ? (
                        plan.price > 0 ? `$${parseFloat(plan.price).toFixed(2)}` : 'Free'
                     ):(
                      <Typography variant="caption" color="text.secondary">
                        Price hide
                      </Typography>
                     )}
                    </Typography>
                    
                    {PriceHide === "0" && plan.personCount > 1 && plan.price > 0 && (
                      <Typography variant="caption" color="text.secondary">
                        ${(plan.price / plan.personCount).toFixed(2)} per person
                      </Typography>
                    )}
                  </Box>
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
     
    </Box>
  );
};

export default MealPlanSelection; 