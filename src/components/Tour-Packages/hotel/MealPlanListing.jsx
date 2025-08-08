import React, { useState, useEffect, useRef } from "react";
import { styled } from "@mui/material/styles";
import {
  Box,
  TextField,
  Typography,
  Paper,
  List,
  ListItem,
  Chip,
  Card,
  Button
} from "@mui/material";
import RestaurantMenuIcon from "@mui/icons-material/RestaurantMenu";
import AddIcon from "@mui/icons-material/Add";

// Styled components
const SearchContainer = styled(Box)(({ theme }) => ({
  position: "relative",
  marginBottom: theme.spacing(2),
}));

const DropdownContainer = styled(Paper)(({ theme }) => ({
  position: "absolute",
  width: "100%",
  maxHeight: 350,
  overflowY: "auto",
  zIndex: 20,
  marginTop: theme.spacing(0.5),
  boxShadow: theme.shadows[3],
}));

const MealPlanOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  justifyContent: "space-between",
  alignItems: "flex-start",
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
  },
}));

const MealPlanInfo = styled(Box)({
  flex: 1,
});

const SelectedMealPlan = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(1),
  padding: theme.spacing(1, 1.5),
  backgroundColor: theme.palette.grey[50],
  borderRadius: theme.shape.borderRadius,
  borderLeft: `3px solid ${theme.palette.primary.main}`,
  fontSize: 13,
}));

const SelectedLabel = styled("span")(({ theme }) => ({
  fontWeight: 500,
  marginRight: theme.spacing(0.5),
  color: theme.palette.primary.main,
}));

const CountBadge = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  marginLeft: theme.spacing(1),
  height: 20,
  fontSize: 12,
}));

const MealPlanListing = ({ onSelect, initialMealPlans = [], selectedMealPlanId = '' }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedMealPlan, setSelectedMealPlan] = useState(null);
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Default meal plans if none provided
  const mealPlans = initialMealPlans.length > 0 ? initialMealPlans : [
    { id: 'self', title: 'Room Only', description: 'No meals included', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
    { id: 'breakfast', title: 'Room with Breakfast', description: 'Includes daily breakfast', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
    { id: 'lunch', title: 'Room with Lunch', description: 'Includes daily lunch', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
    { id: 'dinner', title: 'Room with Dinner', description: 'Includes daily dinner', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
    { id: 'fullboard', title: 'Room with Full Board', description: 'Includes breakfast, lunch and dinner', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
    { id: 'allinclusive', title: 'Room with All Inclusive', description: 'All meals and selected drinks included', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> }
  ];

  // Initialize selected meal plan from props
  useEffect(() => {
    if (selectedMealPlanId) {
      const foundMealPlan = mealPlans.find(plan => plan.id === selectedMealPlanId);
      if (foundMealPlan) {
        setSelectedMealPlan(foundMealPlan);
      }
    }
  }, [selectedMealPlanId, mealPlans]);

  // Filter meal plans based on search term
  const filteredMealPlans = mealPlans.filter((plan) =>
    plan.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    plan.description.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Handle clicking outside to close dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        dropdownRef.current &&
        !dropdownRef.current.contains(event.target) &&
        !inputRef.current.contains(event.target)
      ) {
        setIsDropdownOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  // Handle meal plan selection
  const handleMealPlanSelect = (mealPlan) => {
    if (selectedMealPlan && selectedMealPlan.id === mealPlan.id) {
      // If clicking the same meal plan, unselect it
      setSelectedMealPlan(null);
      if (onSelect) onSelect(null);
    } else {
      setSelectedMealPlan(mealPlan);
      if (onSelect) onSelect(mealPlan);
    }
    setSearchTerm("");
    setIsDropdownOpen(false);
  };

  // Handle unselect
  const handleUnselect = () => {
    setSelectedMealPlan(null);
    if (onSelect) onSelect(null);
  };

  return (
    <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
      <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 2 }}>
        Meal Plans 
        <CountBadge 
          label={mealPlans.length} 
          size="small" 
          variant="outlined" 
        />
      </Typography>
      
      <SearchContainer>
        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for meal plans..."
          value={searchTerm}
          onChange={(e) => {
            setSearchTerm(e.target.value);
            setIsDropdownOpen(true);
          }}
          onFocus={() => setIsDropdownOpen(true)}
          InputProps={{
            startAdornment: <RestaurantMenuIcon color="action" sx={{ mr: 1 }} />
          }}
        />

        {isDropdownOpen && (
          <DropdownContainer ref={dropdownRef}>
            <List disablePadding>
              {filteredMealPlans.length > 0 ? (
                filteredMealPlans.map((plan) => (
                  <MealPlanOption
                    key={plan.id}
                    onClick={() => handleMealPlanSelect(plan)}
                  >
                    <MealPlanInfo>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        {plan.icon}
                        <Typography variant="subtitle2" fontWeight={500} sx={{ ml: 0.5 }}>
                          {plan.title}
                        </Typography>
                      </Box>
                      <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12, mt: 0.5 }}>
                        {plan.description}
                      </Typography>
                    </MealPlanInfo>
                    {selectedMealPlan && selectedMealPlan.id === plan.id && (
                      <Chip 
                        label="Selected" 
                        size="small" 
                        color="primary" 
                        sx={{ height: 24 }} 
                      />
                    )}
                  </MealPlanOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>No meal plans match your search</Typography>
                </Box>
              )}
            </List>
          </DropdownContainer>
        )}
      </SearchContainer>

      {selectedMealPlan && (
        <SelectedMealPlan>
          <SelectedLabel>Selected:</SelectedLabel> {selectedMealPlan.title}
        </SelectedMealPlan>
      )}
      
      {/* Selected Meal Plan Card */}
      {selectedMealPlan && (
        <Card elevation={3} sx={{ 
          borderRadius: 2,
          border: '2px solid #3554D1',
          mb: 2,
          mt: 2
        }}>
          <Box sx={{ 
            bgcolor: '#3554D1', 
            color: 'white', 
            py: 1.5, 
            px: 2,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              {selectedMealPlan.icon}
              <Typography variant="subtitle1" sx={{ ml: 1 }}>
                {selectedMealPlan.title}
              </Typography>
            </Box>
            <Button
              variant="outlined"
              size="small"
              onClick={handleUnselect}
              sx={{ 
                color: 'white', 
                borderColor: 'white',
                '&:hover': {
                  borderColor: 'white',
                  bgcolor: 'rgba(255, 255, 255, 0.1)'
                }
              }}
            >
              Unselect
            </Button>
          </Box>
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            py: 2,
            px: 2,
            bgcolor: 'rgba(53, 84, 209, 0.05)'
          }}>
            <Box sx={{ 
              py: 1,
              px: 3,
              bgcolor: 'rgba(255, 255, 255, 0.7)',
              borderRadius: '4px'
            }}>
              <Typography variant="body2" color="text.secondary">
                Price available on request
              </Typography>
            </Box>
            <Typography variant="body2" color="primary" fontWeight={500}>
              Currently Selected
            </Typography>
          </Box>
        </Card>
      )}
      
     
    </Box>
  );
};

export default MealPlanListing; 