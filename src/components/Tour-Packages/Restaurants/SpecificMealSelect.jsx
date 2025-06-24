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
  padding: '10px 16px',
  display: 'flex',
  alignItems: 'center',
  backgroundColor: 'rgba(232, 245, 233, 0.85)',
  color: '#2e7d32',
  borderRadius: '6px',
  margin: '4px 8px',
  transition: 'all 0.2s ease',

  '&:hover': {
    backgroundColor: 'rgba(232, 245, 233, 1)',
    transform: 'translateY(-2px) scale(1.02)',
    boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)',
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
  borderRadius: '0 0 16px 16px',
  boxShadow: '0 -4px 12px rgba(0, 0, 0, 0.05)',
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
        fontSize: '15px',
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
                padding: '2px 8px',
                fontSize: '12px',
                marginLeft: '4px',
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
                <>Show Less <ExpandLessIcon sx={{ fontSize: 16, ml: 0.5 }} /></>
              ) : (
                <>Show More <ExpandMoreIcon sx={{ fontSize: 16, ml: 0.5 }} /></>
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

  // Get the search parameters from Redux store for pax counts
  const searchParams = useSelector((state) => state.restaurants.searchParams);

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

  // Reset internal state when selectedMealType changes
  useEffect(() => {
    setSpecificMealType("");
    setSelectedMealIndex(null);
    setSelectedMealIndexes([]);
    setSelectedMealParts([]);
    setIsConfirmDisabled(true);
    setTotalPrice(0);
  }, [selectedMealType]);

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
    setSelectedMealIndex(null);
    setSelectedMealIndexes([]);
    setIsConfirmDisabled(true);
    setTotalPrice(0);

    if (selectedType) {
      const filteredMeals = restaurantDetails?.meals?.filter(
        (meal) =>
          meal.meal_period === selectedMealType &&
          meal.type === selectedType &&
          meal.is_active === 1
      ) || [];

      console.log('Filtered Meals:', filteredMeals);

      const mealParts = filteredMeals.map((meal) => ({
        meal_id: meal.id,
        name: meal.name,
        description: meal.description,
        price: meal.price,
        adult_price: meal.adult_price,
        child_price: meal.child_price,
        adultCount: paxCounts.Adults,
        childCount: paxCounts.Children,
        quantity: 1,
        checked: false
      }));

      console.log('Prepared Meal Parts:', mealParts);
      setSelectedMealParts(mealParts);
      setOpenModal(true);
    }
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
      <FormControl fullWidth disabled={!selectedMealType || disabled}>
        <InputLabel>Specific Meal Type</InputLabel>
        <Select
          value={specificMealType}
          label="Specific Meal Type"
          onChange={handleMealSelection}
        >
          <MenuItem value="">
            <em>Select a meal type</em>
          </MenuItem>
          {getMealOptions().map((type) => (
            <StyledMenuItem key={type} value={type}>
              <Box sx={{ display: 'flex', alignItems: 'center', width: '100%' }}>
                {getIconForMealType(type)}
                <Typography sx={{ ml: 2 }}>{type}</Typography>
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
          padding: '20px',
          backdropFilter: 'blur(5px)',
          backgroundColor: 'rgba(0, 0, 0, 0.6)',
        }}
      >
        <Fade in={openModal}>
          <Paper
            elevation={24}
            sx={{
              width: '90%',
              maxWidth: '900px',
              maxHeight: '90vh',
              borderRadius: '16px',
              overflow: 'hidden',
              boxShadow: '0 10px 40px rgba(0, 0, 0, 0.2)',
              display: 'flex',
              flexDirection: 'column',
            }}
          >
            {/* Modal Header */}
            <Box sx={{ 
              p: 2, 
              borderBottom: '1px solid',
              borderColor: 'divider',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              backgroundColor: 'rgba(255, 255, 255, 0.95)',
            }}>
              <Typography variant="h6" component="h2">
                Select {specificMealType}
              </Typography>
              <IconButton onClick={() => setOpenModal(false)} size="small">
                <CloseIcon />
              </IconButton>
            </Box>

            {/* Modal Content */}
            <Box sx={{ 
              p: 2, 
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
                      mb: 2,
                      border: '1px solid',
                      borderColor: 'divider',
                      borderRadius: 1,
                      p: 2,
                      backgroundColor: selectedMealIndex === index ? 'rgba(76, 175, 80, 0.04)' : 'transparent',
                    }}
                  >
                    <ListItemText
                      primary={
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <Radio
                            checked={specificMealType.toLowerCase() === 'buffet' ? 
                              selectedMealIndex === index : 
                              selectedMealIndexes.includes(index)}
                            onChange={() => handleRadioChange(index)}
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
                        <Box sx={{ ml: 4 }}>
                          {specificMealType.toLowerCase() === 'buffet' ? (
                            <Box>
                              <Typography variant="body2" color="text.secondary">
                                Adult: {formatCurrency(meal.adult_price)} × {paxCounts.Adults} = {formatCurrency(meal.adult_price * paxCounts.Adults)}
                              </Typography>
                              {paxCounts.Children > 0 && (
                                <Typography variant="body2" color="text.secondary">
                                  Child: {formatCurrency(meal.child_price)} × {paxCounts.Children} = {formatCurrency(meal.child_price * paxCounts.Children)}
                                </Typography>
                              )}
                            </Box>
                          ) : (
                            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                              <Typography variant="body2" color="text.secondary">
                                {formatCurrency(meal.price)}
                              </Typography>
                              
                              {/* Quantity controls for Set Menu and A la carte */}
                              {(specificMealType.toLowerCase() === 'set menu' || 
                                specificMealType.toLowerCase() === 'a la carte') && 
                               selectedMealIndexes.includes(index) && (
                                <Box sx={{ 
                                  display: 'flex', 
                                  alignItems: 'center',
                                  gap: 1,
                                  ml: 2
                                }}>
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
                                    variant="body2" 
                                    sx={{ 
                                      minWidth: '30px', 
                                      textAlign: 'center',
                                      fontWeight: 500
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
                                  <Typography 
                                    variant="body2" 
                                    sx={{ ml: 2, fontWeight: 500, color: '#4caf50' }}
                                  >
                                    = {formatCurrency(meal.price * meal.quantity)}
                                  </Typography>
                                </Box>
                              )}
                            </Box>
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
                py: 2
              }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <ShoppingCartIcon sx={{ mr: 2, color: '#4caf50' }} />
                  <Typography variant="h6" component="div">
                    Total Price:
                  </Typography>
                </Box>
                <Box sx={{ textAlign: 'right' }}>
                  <Typography variant="h5" sx={{ fontWeight: 600, color: '#4caf50' }}>
                    {formatCurrency(totalPrice)}
                  </Typography>
                  {specificMealType.toLowerCase() === 'buffet' && (
                    <Typography variant="caption" color="text.secondary">
                      {paxCounts.Adults} Adult{paxCounts.Adults !== 1 ? 's' : ''}{paxCounts.Children > 0 ? ` + ${paxCounts.Children} Child${paxCounts.Children !== 1 ? 'ren' : ''}` : ''}
                    </Typography>
                  )}
                </Box>
              </CardContent>
            </PriceSummaryCard>

            {/* Modal Footer */}
            <Box sx={{ 
              p: 2, 
              borderTop: '1px solid',
              borderColor: 'divider',
              display: 'flex',
              justifyContent: 'flex-end',
              gap: 2,
              backgroundColor: 'rgba(255, 255, 255, 0.95)',
            }}>
              <Button 
                onClick={() => setOpenModal(false)}
                variant="outlined"
                color="inherit"
              >
                Cancel
              </Button>
              <Button
                variant="contained"
                onClick={handleConfirm}
                disabled={isConfirmDisabled}
                startIcon={<ShoppingCartIcon />}
                sx={{
                  bgcolor: '#4caf50',
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