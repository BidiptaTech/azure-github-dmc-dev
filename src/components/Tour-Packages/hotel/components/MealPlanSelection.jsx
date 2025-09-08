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
  selectedMealPlan, // Changed from guestMealPlans to single meal plan
  handleMealPlanChange, // Changed from handleGuestMealPlanChange to single handler
  handleGuestCountChange, 
  bedType, 
  searchCriteria,
  mealPlans,
  roomData = null,
  isLoading = false
}) => {
  const [mealPlanAnchorEl, setMealPlanAnchorEl] = useState(null);
  const isMealPlanMenuOpen = Boolean(mealPlanAnchorEl);
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
      <Box sx={{ width: '100%', mt: 0 }}>
        <Paper 
          elevation={1} 
          sx={{ 
            height: '56px',
            display: 'flex',
            alignItems: 'center',
            px: 2,
            bgcolor: '#f5f5f5',
            borderRadius: 1,
            border: '1px solid rgba(0, 0, 0, 0.23)'
          }}
        >
          <Typography variant="body2">Loading meal plan options...</Typography>
        </Paper>
      </Box>
    );
  }
  
  // Check if room data or bed type is missing
  if (!bedType) {
    return (
      <Box sx={{ width: '100%', mt: 0 }}>
        <Paper 
          elevation={0}
          sx={{ 
            height: '56px',
            display: 'flex',
            alignItems: 'center',
            px: 2,
            bgcolor: 'rgba(25, 118, 210, 0.04)',
            border: '1px solid rgba(25, 118, 210, 0.2)',
            borderRadius: 1
          }}
        >
          <Typography variant="body2" sx={{ fontSize: '0.875rem', color: 'primary.main' }}>
            Please select a bed type first
          </Typography>
        </Paper>
      </Box>
    );
  }
  
  return (
    <Box sx={{ mt: 0 }}>
      <Button
        fullWidth
        variant="outlined"
        disabled={!bedType}
        onClick={(event) => setMealPlanAnchorEl(event.currentTarget)}
        sx={{ 
          height: '56px',
          justifyContent: 'flex-start',
          textAlign: 'left',
          px: 1.5,
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
          
          if (selectedGuests === 0) {
            return <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.75rem' }}>
              Select number of guests (Tour: {totalTourGuests} [A:{adults}, C:{children}]{bedType ? `, Bed: ${maxBedOccupancy}` : ''})
            </Typography>;
          }
          
          if (!selectedMealPlan) {
            return <Typography variant="body2" sx={{ fontSize: '0.75rem' }}>
              {selectedGuests} guest{selectedGuests > 1 ? 's' : ''} - Select meal plan for room
            </Typography>;
          }
          
          // Display selected meal plan for the room
          const selectedPlan = mealPlans.find(p => p.id === selectedMealPlan);
          const planName = selectedPlan?.title || 'Room Only';
          
          return <Typography variant="body2" noWrap sx={{ fontSize: '0.75rem' }}>
            {selectedGuests} guest{selectedGuests > 1 ? 's' : ''} - {planName}
          </Typography>;
        })()}
      </Button>
      
      <Menu
        anchorEl={mealPlanAnchorEl}
        open={isMealPlanMenuOpen}
        onClose={(event, reason) => {
          // Only close on backdrop click or escape key
          if (reason === 'backdropClick' || reason === 'escapeKeyDown') {
            setMealPlanAnchorEl(null);
          }
        }}
        MenuListProps={{
          'aria-labelledby': 'meal-plan-button',
          sx: { maxHeight: 350, maxWidth: 620 }
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
        <MenuItem disabled sx={{ bgcolor: '#e3f2fd', fontWeight: 'bold', borderBottom: '1px solid #bbdefb' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
            <Typography variant="subtitle2" sx={{ fontSize: '0.8rem' }}>Number of Guests</Typography>
            <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
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
          const adults = parseInt(searchCriteria?.guests?.Adults || searchCriteria?.guests?.adults || 1);
          const children = parseInt(searchCriteria?.guests?.Children || searchCriteria?.guests?.children || 0);
          const totalTourGuests = adults + children;
          const maxBedOccupancy = bedType?.max_occupancy || totalTourGuests;
          const maxSelectableGuests = Math.min(totalTourGuests, maxBedOccupancy);
          
          return Array.from({ length: maxSelectableGuests }, (_, i) => i + 1).map((num) => (
            <MenuItem 
              key={`guest-count-${num}`}
              onClick={() => {
                if (handleGuestCountChange) {
                  handleGuestCountChange(num);
                }
                // Don't close menu after guest count change so user can select meal plan
              }}
              sx={{ 
                pl: 3,
                bgcolor: selectedGuests === num ? 'rgba(53, 84, 209, 0.08)' : 'transparent'
              }}
            >
              <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', justifyContent: 'space-between' }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  {Array.from({ length: num }).map((_, iconIndex) => (
                    <IoPersonSharp 
                      key={iconIndex}
                      size={14}
                      style={{ 
                        color: '#3554D1',
                        marginRight: '1px'
                      }}
                    />
                  ))}
                  <Typography sx={{ ml: 0.8, fontSize: '0.75rem' }}>
                    {num} {num === 1 ? 'Guest' : 'Guests'}
                    {num === totalTourGuests && (
                      <Chip label="From Tour" size="small" color="info" sx={{ ml: 0.8, height: 16, fontSize: '0.55rem' }} />
                    )}
                    {num === maxBedOccupancy && bedType && (
                      <Chip label="Max Bed" size="small" color="warning" sx={{ ml: 0.8, height: 16, fontSize: '0.55rem' }} />
                    )}
                  </Typography>
                </Box>
                {selectedGuests === num && (
                  <Chip label="✓ Selected" size="small" color="primary" sx={{ height: 18, fontSize: '0.65rem' }} />
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

        {/* Room meal plan selection */}
        {selectedGuests > 0 && (
          <>
            <MenuItem disabled sx={{ 
              bgcolor: '#e6f2ff', 
              fontWeight: 'bold',
              padding: '8px',
              borderRadius: '3px',
              boxShadow: '0 1px 2px rgba(0,0,0,0.05)',
              my: 0.8
            }}>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <RestaurantMenuIcon size={16} style={{ marginRight: '6px', color: '#3554D1' }} />
                  <Typography variant="subtitle2" sx={{ fontSize: '0.8rem' }}>
                    Meal Plan for Room ({selectedGuests} guest{selectedGuests > 1 ? 's' : ''})
                  </Typography>
                </Box>
              </Box>
            </MenuItem>
            
            {/* Meal plan options for the room */}
            {mealPlans.map((plan) => (
              <MenuItem 
                key={plan.id}
                onClick={() => {
                  console.log("MealPlanSelection - Meal plan clicked:", plan.id, plan.title);
                  if (handleMealPlanChange) {
                    handleMealPlanChange(plan.id);
                    console.log("MealPlanSelection - Handler called");
                  } else {
                    console.warn("MealPlanSelection - handleMealPlanChange not provided");
                  }
                  // Close the menu after selection
                  setMealPlanAnchorEl(null);
                }}
                sx={{ 
                  pl: 3,
                  bgcolor: selectedMealPlan === plan.id ? 'rgba(53, 84, 209, 0.08)' : 'transparent'
                }}
              >
                <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', justifyContent: 'space-between' }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', flex: 1 }}>
                    {getMealPlanIcon(plan.id)}
                    <Box sx={{ ml: 1 }}>
                      <Typography variant="body2" sx={{ fontWeight: 500 }}>{plan.title}</Typography>
                      {plan.description && PriceHide === "0" && (
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block' }}>
                          {plan.description}
                        </Typography>
                      )}
                    </Box>
                  </Box>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    {selectedMealPlan === plan.id && (
                      <Chip label="✓ Selected" size="small" color="primary" sx={{ mr: 1, height: 20 }} />
                    )}
                    <Box sx={{ textAlign: 'right' }}>
                      <Typography variant="body2" fontWeight="500" color="primary.main">
                        {PriceHide === "0" ? (
                          plan.totalPrice > 0 ? `$${parseFloat(plan.totalPrice).toFixed(2)}` : 'Free'
                        ) : (
                          <Typography variant="caption" color="text.secondary">
                            Price hidden
                          </Typography>
                        )}
                      </Typography>
                      {PriceHide === "0" && plan.personCount > 1 && plan.totalPrice > 0 && (
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block' }}>
                          ${(plan.totalPrice / plan.personCount).toFixed(2)} per person
                        </Typography>
                      )}
                      {PriceHide === "0" && plan.price > 0 && (
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'block', fontSize: '0.6rem' }}>
                          Meal: ${parseFloat(plan.price).toFixed(2)}
                        </Typography>
                      )}
                    </Box>
                  </Box>
                </Box>
              </MenuItem>
            ))}
          </>
        )}
      </Menu>
     
    </Box>
  );
};

export default MealPlanSelection; 