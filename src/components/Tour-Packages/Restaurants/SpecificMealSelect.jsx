import React, { useState, useEffect } from 'react';
import {
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Box,
  Typography,
  Chip,
  Modal,
  Paper,
  Stack,
  Button,
  IconButton,
  Grid,
  Radio,
  RadioGroup,
  FormControlLabel,
  Divider,
  Card,
  CardContent,
  Fade,
  Checkbox,
  List,
  ListItem,
  ListItemText,
} from '@mui/material';
import { styled } from '@mui/material/styles';
import FastfoodIcon from '@mui/icons-material/Fastfood';
import BuffetIcon from '@mui/icons-material/Restaurant';
import MenuBookIcon from '@mui/icons-material/MenuBook';
import CloseIcon from '@mui/icons-material/Close';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import ExpandLessIcon from '@mui/icons-material/ExpandLess';
import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import { useSelector } from 'react-redux';

// Styled components
const StyledMenuItem = styled(MenuItem)(({ theme }) => ({
  padding: '8px 12px',
  display: 'flex',
  alignItems: 'center',
  backgroundColor: 'rgba(232, 245, 233, 0.85)',
  color: '#2e7d32',
  borderRadius: '4px',
  margin: '3px 6px',
  transition: 'all 0.2s ease',

  '&:hover': {
    backgroundColor: 'rgba(232, 245, 233, 1)',
    transform: 'translateY(-1px) scale(1.01)',
    boxShadow: '0 3px 6px rgba(0, 0, 0, 0.1)',
  },

  '&.Mui-selected': {
    backgroundColor: 'rgba(200, 230, 201, 1)',
    fontWeight: 'bold',
  },

  '&.Mui-selected:hover': {
    backgroundColor: 'rgba(165, 214, 167, 1)',
  },
}));

// Price Summary Card component
const PriceSummaryCard = styled(Card)(({ theme }) => ({
  position: 'sticky',
  bottom: 0,
  marginTop: 'auto',
  backgroundColor: 'rgba(255, 255, 255, 0.95)',
  backdropFilter: 'blur(10px)',
  borderTop: '1px solid rgba(0, 0, 0, 0.12)',
  borderRadius: '0 0 12px 12px',
  boxShadow: '0 -3px 8px rgba(0, 0, 0, 0.05)',
}));

// MealDescription component for truncated text
const MealDescription = ({ description, expanded, onToggle }) => {
  const truncateWords = (text, limit) => {
    if (!text) return "";
    const words = text.split(/\s+/);
    if (words.length <= limit) return text;
    return words.slice(0, limit).join(" ") + "...";
  };

  const wordCount = description ? description.split(/\s+/).length : 0;
  const hasMoreContent = wordCount > 25;

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column' }}>
      <Typography sx={{ 
        fontWeight: 500,
        fontSize: '14px',
        color: '#1E293B'
      }}>
        {hasMoreContent ? (
          <>
            {expanded ? description : truncateWords(description, 25)}
            <Button
              onClick={(e) => {
                e.stopPropagation();
                e.preventDefault();
                onToggle();
              }}
              sx={{
                minWidth: 'auto',
                padding: '1px 6px',
                fontSize: '11px',
                marginLeft: '3px',
                color: '#4caf50',
                textTransform: 'none',
                display: 'inline-flex',
                alignItems: 'center',
                '&:hover': {
                  backgroundColor: 'rgba(76, 175, 80, 0.04)',
                },
              }}
            >
              {expanded ? (
                <>Show Less <ExpandLessIcon sx={{ fontSize: 14, ml: 0.5 }} /></>
              ) : (
                <>Show More <ExpandMoreIcon sx={{ fontSize: 14, ml: 0.5 }} /></>
              )}
            </Button>
          </>
        ) : (
          description || "No description available"
        )}
      </Typography>
    </Box>
  );
};

const SpecificMealSelect = ({ value, onChange, selectedMealType, restaurantDetails, disabled }) => {
  const [openModal, setOpenModal] = useState(false);
  const [expandedDescriptions, setExpandedDescriptions] = useState({});
  const [specificMealType, setSpecificMealType] = useState("");
  const [selectedMealIndex, setSelectedMealIndex] = useState(null);
  const [selectedMealIndexes, setSelectedMealIndexes] = useState([]);
  const [selectedMealParts, setSelectedMealParts] = useState([]);
  const [isConfirmDisabled, setIsConfirmDisabled] = useState(true);
  const [totalPrice, setTotalPrice] = useState(0);
  const [paxCounts, setPaxCounts] = useState({ Adults: 0, Children: 0 });
  const [confirmedMealParts, setConfirmedMealParts] = useState([]);

  // Get the search parameters from Redux store for pax counts
  const searchParams = useSelector((state) => state.restaurants.searchParams);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // Update pax counts when search parameters change
  useEffect(() => {
    if (searchParams) {
      setPaxCounts({
        Adults: searchParams.adults || 0,
        Children: searchParams.children || 0
      });
    }
  }, [searchParams]);

  // Add console log for initial props
  useEffect(() => {
    console.log('SpecificMealSelect Props:', {
      value,
      selectedMealType,
      restaurantDetails,
      disabled
    });
  }, [value, selectedMealType, restaurantDetails, disabled]);

  // Calculate total price whenever selections change
  useEffect(() => {
    calculateTotalPrice();
  }, [selectedMealIndex, selectedMealIndexes, selectedMealParts, specificMealType, paxCounts]);

  // Initialize with existing value if available
  useEffect(() => {
    console.log('SpecificMealSelect - Initializing with value:', value);
    
    if (value && typeof value === 'object' && value.specificMealType) {
      console.log('Setting specificMealType from value:', value.specificMealType);
      setSpecificMealType(value.specificMealType);
      setTotalPrice(value.totalPrice || 0);
      
      // If we have items, restore the confirmed meal parts
      if (value.items && Array.isArray(value.items) && value.items.length > 0) {
        console.log('Restoring confirmed meal parts:', value.items);
        setConfirmedMealParts(value.items);
      }
    } else {
      // Only reset if no value is provided
      console.log('Resetting SpecificMealSelect state');
      setSpecificMealType("");
    setSelectedMealIndex(null);
    setSelectedMealIndexes([]);
    setSelectedMealParts([]);
    setIsConfirmDisabled(true);
    setTotalPrice(0);
    }
  }, [value,selectedMealType]); // Remove selectedMealType dependency to prevent conflicts

  // Format currency helper function
  const formatCurrency = (amount) => {
    if (!amount) return '$0.00';
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(amount);
  };

  // Calculate total price based on meal type and selections
  const calculateTotalPrice = () => {
    let total = 0;

    if (specificMealType.toLowerCase() === 'buffet' && selectedMealIndex !== null) {
      const selectedMeal = selectedMealParts[selectedMealIndex];
      if (selectedMeal) {
        // Use paxCounts for buffet calculations
        total = (selectedMeal.adult_price * paxCounts.Adults) + 
                (selectedMeal.child_price * paxCounts.Children);
      }
    } else if (specificMealType.toLowerCase() === 'set menu' && selectedMealIndex !== null) {
      const selectedMeal = selectedMealParts[selectedMealIndex];
      if (selectedMeal) {
        total = selectedMeal.price * selectedMeal.quantity;
      }
    } else if (specificMealType.toLowerCase() === 'a la carte') {
      total = selectedMealParts
        .filter((_, index) => selectedMealIndexes.includes(index))
        .reduce((sum, meal) => sum + (meal.price * meal.quantity), 0);
    }

    console.log('Price Calculation:', {
      specificMealType,
      selectedMealIndex,
      selectedMealParts,
      paxCounts,
      total
    });

    setTotalPrice(total);
    return total;
  };

  // Get meal options based on selected meal type from the meals array
  const getMealOptions = () => {
    if (!selectedMealType || !restaurantDetails?.meals) return [];

    // Get unique meal types
    const uniqueTypes = [...new Set(restaurantDetails.meals
      .filter(meal => meal.meal_period === selectedMealType && meal.is_active === 1)
      .map(meal => meal.type || 'Standard Menu'))];

    console.log('Available Meal Types:', uniqueTypes);
    return uniqueTypes;
  };

  // Get appropriate icon based on meal type
  const getIconForMealType = (type) => {
    switch (type?.toLowerCase()) {
      case 'buffet':
        return <BuffetIcon fontSize="small" />;
      case 'set menu':
        return <MenuBookIcon fontSize="small" />;
      case 'à la carte':
      case 'a la carte':
        return <FastfoodIcon fontSize="small" />;
      default:
        return <MenuBookIcon fontSize="small" />;
    }
  };

  // Add console log for meal selection
  const handleMealSelection = (event) => {
    const selectedType = event.target.value;
    console.log('Selected Meal Type:', selectedType);
    setSpecificMealType(selectedType);

    if (selectedType) {
      const filteredMeals = restaurantDetails?.meals?.filter(
        (meal) =>
          meal.meal_period === selectedMealType &&
          meal.type === selectedType &&
          meal.is_active === 1
      ) || [];

      console.log('Filtered Meals:', filteredMeals);

      const mealParts = filteredMeals.map((meal) => {
        // Check if this meal was previously confirmed
        const confirmedMeal = confirmedMealParts.find(
          confirmed => confirmed.meal_id === meal.id
        );
        
        return {
          meal_id: meal.id,
          name: meal.name,
          description: meal.description,
          price: meal.price,
          adult_price: meal.adult_price,
          child_price: meal.child_price,
          adultCount: confirmedMeal ? confirmedMeal.adultCount || paxCounts.Adults : paxCounts.Adults,
          childCount: confirmedMeal ? confirmedMeal.childCount || paxCounts.Children : paxCounts.Children,
          quantity: confirmedMeal ? confirmedMeal.quantity || 1 : 1,
          checked: confirmedMeal ? true : false // Restore checked state
        };
      });

      // Restore selected indexes based on confirmed selections
      const confirmedIndexes = [];
      let confirmedSingleIndex = null;
      
      mealParts.forEach((part, index) => {
        if (part.checked) {
          confirmedIndexes.push(index);
          if (confirmedSingleIndex === null) {
            confirmedSingleIndex = index;
          }
        }
      });

      console.log('Prepared Meal Parts:', mealParts);
      setSelectedMealParts(mealParts);
      setSelectedMealIndexes(confirmedIndexes);
      setSelectedMealIndex(confirmedSingleIndex);
      setIsConfirmDisabled(confirmedIndexes.length === 0);
      setOpenModal(true);
    }
  };

  // New function to handle meal item clicks (including re-selections)
  const handleMealItemClick = (selectedType) => {
    console.log('Selected Meal Type:', selectedType);

    const filteredMeals = restaurantDetails?.meals?.filter(
      (meal) =>
        meal.meal_period === selectedMealType &&
        meal.type === selectedType &&
        meal.is_active === 1
    ) || [];

    console.log('Filtered Meals:', filteredMeals);

    const mealParts = filteredMeals.map((meal) => {
      // Check if this meal was previously confirmed
      const confirmedMeal = confirmedMealParts.find(
        confirmed => confirmed.meal_id === meal.id
      );
      
      return {
        meal_id: meal.id,
        name: meal.name,
        description: meal.description,
        price: meal.price,
        adult_price: meal.adult_price,
        child_price: meal.child_price,
        adultCount: confirmedMeal ? confirmedMeal.adultCount || paxCounts.Adults : paxCounts.Adults,
        childCount: confirmedMeal ? confirmedMeal.childCount || paxCounts.Children : paxCounts.Children,
        quantity: confirmedMeal ? confirmedMeal.quantity || 1 : 1,
        checked: confirmedMeal ? true : false // Restore checked state
      };
    });

    // Restore selected indexes based on confirmed selections
    const confirmedIndexes = [];
    let confirmedSingleIndex = null;
    
    mealParts.forEach((part, index) => {
      if (part.checked) {
        confirmedIndexes.push(index);
        if (confirmedSingleIndex === null) {
          confirmedSingleIndex = index;
        }
      }
    });

    console.log('Prepared Meal Parts:', mealParts);
    setSelectedMealParts(mealParts);
    setSelectedMealIndexes(confirmedIndexes);
    setSelectedMealIndex(confirmedSingleIndex);
    setIsConfirmDisabled(confirmedIndexes.length === 0);
    setOpenModal(true);
  };

  const handleRadioChange = (index) => {
    setSelectedMealIndex(index);
    setSelectedMealIndexes([index]);
    setIsConfirmDisabled(false);

    // Reset all items' checked status
    setSelectedMealParts((prevParts) =>
      prevParts.map((part, i) => ({
        ...part,
        checked: i === index
      }))
    );
  };

  const handleQuantityChange = (index, change) => {
    if (specificMealType.toLowerCase() === 'set menu' || specificMealType.toLowerCase() === 'a la carte') {
      setSelectedMealParts((prevParts) =>
        prevParts.map((part, i) => {
          if (i === index) {
            const newQuantity = Math.max(1, (part.quantity || 1) + change);
            return {
              ...part,
              quantity: newQuantity
            };
          }
          return part;
        })
      );
    }
  };

  // Add console log for confirm action
  const handleConfirm = () => {
    if (selectedMealIndex !== null || selectedMealIndexes.length > 0) {
      const selectedItems = selectedMealParts
        .filter((_, index) => 
          specificMealType.toLowerCase() === 'buffet' ? 
            index === selectedMealIndex : 
            selectedMealIndexes.includes(index)
        )
        .map(item => ({
          ...item,
          type: specificMealType,
          totalPrice: calculateTotalPrice()
        }));

      console.log('Confirmed Selection:', {
        selectedItems,
        specificMealType,
        totalPrice: calculateTotalPrice()
      });

      if (selectedItems.length > 0) {
        // Store confirmed selections for future reopening
        setConfirmedMealParts(selectedItems);
        
        const selectionData = { 
          target: { 
            value: {
              items: selectedItems,
              specificMealType,
              totalPrice: calculateTotalPrice()
            }
          } 
        };
        console.log('Selection Data Passed to Parent:', selectionData);
        onChange(selectionData);
        setOpenModal(false);
      }
    }
  };

  const toggleDescription = (index) => {
    setExpandedDescriptions(prev => ({
      ...prev,
      [index]: !prev[index]
    }));
  };

  return (
    <>
      <FormControl fullWidth disabled={!selectedMealType || disabled} size="small">
        <InputLabel sx={{ fontSize: '0.8rem' }}>Specific Meal Type</InputLabel>
        <Select
          value={specificMealType}
          label="Specific Meal Type"
          onChange={handleMealSelection}
          sx={{
            height: '42px',
            '& .MuiSelect-select': {
              fontSize: '0.8rem'
            }
          }}
          renderValue={(selected) => {
            if (!selected) return <em>Select a meal type</em>;
            return (
              <Box sx={{ display: 'flex', alignItems: 'center', width: '100%' }}>
                {getIconForMealType(selected)}
                <Typography sx={{ ml: 1, fontSize: '0.8rem', flex: 1 }}>{selected}</Typography>
                {value && value.totalPrice && (
                  <Typography sx={{ fontSize: '0.7rem', color: '#4caf50', fontWeight: 500 }}>
                    ${Math.round(value.totalPrice)}
                  </Typography>
                )}
                {value && value.items && value.items.length > 0 && (
                  <Typography sx={{ ml: 1, fontSize: '0.65rem', color: '#666', fontStyle: 'italic' }}>
                    ({value.items.length} item{value.items.length !== 1 ? 's' : ''})
                  </Typography>
                )}
              </Box>
            );
          }}
        >
          <MenuItem value="" sx={{ fontSize: '0.8rem' }}>
            <em>Select a meal type</em>
          </MenuItem>
          {getMealOptions().map((type) => (
            <StyledMenuItem 
              key={type} 
              value={type}
              onClick={() => handleMealItemClick(type)}
            >
              <Box sx={{ display: 'flex', alignItems: 'center', width: '100%' }}>
                {getIconForMealType(type)}
                <Typography sx={{ ml: 1.5, fontSize: '0.8rem' }}>{type}</Typography>
              </Box>
            </StyledMenuItem>
          ))}
        </Select>
      </FormControl>

      <Modal
        open={openModal}
        onClose={() => setOpenModal(false)}
        closeAfterTransition
        sx={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          padding: '16px',
          backdropFilter: 'blur(5px)',
          backgroundColor: 'rgba(0, 0, 0, 0.6)',
        }}
      >
        <Fade in={openModal}>
          <Paper
            elevation={20}
            sx={{
              width: '90%',
              maxWidth: '800px',
              maxHeight: '85vh',
              borderRadius: '12px',
              overflow: 'hidden',
              boxShadow: '0 8px 32px rgba(0, 0, 0, 0.2)',
              display: 'flex',
              flexDirection: 'column',
            }}
          >
            {/* Modal Header */}
            <Box sx={{ 
              p: 1.5, 
              borderBottom: '1px solid',
              borderColor: 'divider',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              backgroundColor: 'rgba(255, 255, 255, 0.95)',
            }}>
              <Typography variant="subtitle1" component="h2" sx={{ fontSize: '0.9rem' }}>
                Select {specificMealType}
              </Typography>
              <IconButton onClick={() => {
                setOpenModal(false);
                setSpecificMealType("");  // Reset meal type selection
                setConfirmedMealParts([]); // Clear confirmed selections
                // Notify parent to clear the specific meal selection
                onChange({ target: { value: "" } });
              }} size="small">
                <CloseIcon />
              </IconButton>
            </Box>

            {/* Modal Content */}
            <Box sx={{ 
              p: 1.5, 
              overflowY: 'auto',
              flexGrow: 1,
              display: 'flex',
              flexDirection: 'column'
            }}>
              <List>
              {selectedMealParts.map((meal, index) => (
  <ListItem
    key={meal.meal_id}
    sx={{
      mb: 1.5,
      border: '1px solid',
      borderColor: 'divider',
      borderRadius: 1,
      p: 1.5,
      backgroundColor: selectedMealIndex === index ? 'rgba(76, 175, 80, 0.04)' : 'transparent',
    }}
  >
    <ListItemText
      primary={
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.8 }}>
          <Radio
            checked={
              specificMealType.toLowerCase() === 'buffet'
                ? selectedMealIndex === index
                : selectedMealIndexes.includes(index)
            }
            onChange={() => handleRadioChange(index)}
            size="small"
          />
          <Box sx={{ ml: 1 }}>
            <MealDescription
              description={meal.name}
              expanded={expandedDescriptions[index]}
              onToggle={() => toggleDescription(index)}
            />
          </Box>
        </Box>
      }
                             secondary={
                         <Box sx={{ ml: 3 }}>
                           {specificMealType.toLowerCase() === 'buffet' ? (
                             <Box>
                               <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                                 Adult: {PriceHide !== "1" ? formatCurrency(meal.adult_price) : "Price hidden"} × {paxCounts.Adults} = {PriceHide !== "1" ? formatCurrency(meal.adult_price * paxCounts.Adults) : "Price hidden"}
                               </Typography>
                               {paxCounts.Children > 0 && (
                                 <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                                   Child: {PriceHide !== "1" ? formatCurrency(meal.child_price) : "Price hidden"} × {paxCounts.Children} = {PriceHide !== "1" ? formatCurrency(meal.child_price * paxCounts.Children) : "Price hidden"}
                                 </Typography>
                               )}
                             </Box>
                           ) : (
                             <>
                               <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                 <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                                   {PriceHide !== "1" ? formatCurrency(meal.price) : "Price hidden"}
                                 </Typography>
                               </Box>

                               {/* Quantity controls for Set Menu and A la carte */}
                               {(specificMealType.toLowerCase() === 'set menu' ||
                                 specificMealType.toLowerCase() === 'a la carte') &&
                                 selectedMealIndexes.includes(index) && (
                                   <Box
                                     sx={{
                                       display: 'flex',
                                       alignItems: 'center',
                                       gap: 0.5,
                                       ml: 1.5,
                                     }}
                                   >
                                     <IconButton
                                       size="small"
                                       onClick={() => handleQuantityChange(index, -1)}
                                       disabled={meal.quantity <= 1}
                                       sx={{
                                         border: '1px solid',
                                         borderColor: 'divider',
                                         '&:hover': {
                                           backgroundColor: 'rgba(76, 175, 80, 0.04)',
                                         },
                                       }}
                                     >
                                       <RemoveIcon fontSize="small" />
                                     </IconButton>
                                     <Typography
                                       variant="caption"
                                       sx={{
                                         minWidth: '24px',
                                         textAlign: 'center',
                                         fontWeight: 500,
                                         fontSize: '0.7rem',
                                       }}
                                     >
                                       {meal.quantity}
                                     </Typography>
                                     <IconButton
                                       size="small"
                                       onClick={() => handleQuantityChange(index, 1)}
                                       sx={{
                                         border: '1px solid',
                                         borderColor: 'divider',
                                         '&:hover': {
                                           backgroundColor: 'rgba(76, 175, 80, 0.04)',
                                         },
                                       }}
                                     >
                                       <AddIcon fontSize="small" />
                                     </IconButton>
                                     {PriceHide !== "1" ? (
                                       <Typography
                                         variant="caption"
                                         sx={{ ml: 1.5, fontWeight: 500, color: '#4caf50', fontSize: '0.7rem' }}
                                       >
                                         = {formatCurrency(meal.price * meal.quantity)}
                                       </Typography>
                                     ) : (
                                       <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                                         = Price hidden
                                       </Typography>
                                     )}
                                   </Box>
                                 )}
                             </>
                           )}
                         </Box>
                       }
    />
  </ListItem>
))}

              </List>
            </Box>

            {/* Price Summary Card */}
            <PriceSummaryCard>
              <CardContent sx={{ 
                display: 'flex', 
                justifyContent: 'space-between',
                alignItems: 'center',
                py: 1.5
              }}>
                                 <Box sx={{ display: 'flex', alignItems: 'center' }}>
                   <ShoppingCartIcon sx={{ mr: 1.5, color: '#4caf50', fontSize: 20 }} />
                   <Typography variant="subtitle1" component="div" sx={{ fontSize: '0.9rem' }}>
                     Total Price:
                   </Typography>
                 </Box>
                 <Box sx={{ textAlign: 'right' }}>
                   <Typography variant="h6" sx={{ fontWeight: 600, color: '#4caf50', fontSize: '1.1rem' }}>
                     {PriceHide !== "1" ? formatCurrency(Math.round(totalPrice)) : "Price hidden"}
                   </Typography>
                   {specificMealType.toLowerCase() === 'buffet' && (
                     <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
                       {paxCounts.Adults} Adult{paxCounts.Adults !== 1 ? 's' : ''}{paxCounts.Children > 0 ? ` + ${paxCounts.Children} Child${paxCounts.Children !== 1 ? 'ren' : ''}` : ''}
                     </Typography>
                   )}
                 </Box>
              </CardContent>
            </PriceSummaryCard>

            {/* Modal Footer */}
            <Box sx={{ 
              p: 1.5, 
              borderTop: '1px solid',
              borderColor: 'divider',
              display: 'flex',
              justifyContent: 'flex-end',
              gap: 1.5,
              backgroundColor: 'rgba(255, 255, 255, 0.95)',
            }}>
              <Button 
                onClick={() => {
                  setOpenModal(false);
                  setSpecificMealType("");  // Reset meal type selection
                  setConfirmedMealParts([]); // Clear confirmed selections
                  // Notify parent to clear the specific meal selection
                  onChange({ target: { value: "" } });
                }}
                variant="outlined"
                color="inherit"
                size="small"
                sx={{ fontSize: '0.8rem' }}
              >
                Cancel
              </Button>
              <Button
                variant="contained"
                onClick={handleConfirm}
                disabled={isConfirmDisabled}
                startIcon={<ShoppingCartIcon />}
                size="small"
                sx={{
                  bgcolor: '#4caf50',
                  fontSize: '0.8rem',
                  '&:hover': {
                    bgcolor: '#388e3c'
                  }
                }}
              >
                Confirm Selection
              </Button>
            </Box>
          </Paper>
        </Fade>
      </Modal>
    </>
  );
};

export default SpecificMealSelect; 