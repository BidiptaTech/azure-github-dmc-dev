import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import { fetchEnquiryList } from "@/slice/common/enquiryListSlice";
import { selectSelectedDmcIds } from "@/slice/dmc/dmcSlice";
import { styled } from "@mui/material/styles";
import {
  Box,
  TextField,
  Typography,
  Paper,
  List,
  ListItem,
  IconButton,
  Chip,
  InputLabel,
  CircularProgress,
  FormControl,
  Select,
  MenuItem,
  Button,
  Collapse
} from "@mui/material";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import CloseIcon from "@mui/icons-material/Close";
import EventIcon from "@mui/icons-material/Event";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import ExpandLessIcon from "@mui/icons-material/ExpandLess";

// Styled components
const SearchContainer = styled(Box)(({ theme }) => ({
  position: "relative",
  marginBottom: theme.spacing(2),
}));

const DayBoxContainer = styled(Box)(({ theme }) => ({
  display: "flex",
  flexWrap: "wrap",
  gap: theme.spacing(1),
  marginBottom: theme.spacing(2),
}));

const DayBox = styled(Paper)(({ theme, selected }) => ({
  padding: theme.spacing(0.75, 1.25),
  cursor: "pointer",
  minWidth: 65,
  textAlign: "center",
  border: selected ? `2px solid ${theme.palette.primary.main}` : `1px solid ${theme.palette.divider}`,
  backgroundColor: selected ? 'rgba(25, 118, 210, 0.15)' : theme.palette.background.paper,
  transition: "all 0.2s",
  "&:hover": {
    borderColor: theme.palette.primary.main,
    backgroundColor: selected ? 'rgba(25, 118, 210, 0.15)' : 'rgba(25, 118, 210, 0.05)',
    transform: 'translateY(-2px)',
    boxShadow: theme.shadows[2],
  },
}));

const DropdownContainer = styled(Paper)(({ theme }) => ({
  position: "absolute",
  width: "100%",
  maxHeight: 300,
  overflowY: "auto",
  overflowX: "hidden",
  zIndex: 20,
  marginTop: theme.spacing(0.5),
  boxShadow: theme.shadows[3],
  scrollbarWidth: "thin",
  "&::-webkit-scrollbar": {
    width: "6px",
  },
  "&::-webkit-scrollbar-track": {
    backgroundColor: theme.palette.grey[100],
  },
  "&::-webkit-scrollbar-thumb": {
    backgroundColor: theme.palette.grey[400],
    borderRadius: "3px",
  },
  paddingBottom: theme.spacing(0.5),
}));

const RestaurantOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "default",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  flexDirection: "column",
  alignItems: "flex-start",
  gap: theme.spacing(1),
  "&:last-child": {
    borderBottom: "none",
  },
}));

const RestaurantInfo = styled(Box)({
  flex: 1,
  width: "100%",
});

const RestaurantMetadata = styled(Box)(({ theme }) => ({
  display: "flex",
  flexWrap: "wrap",
  gap: theme.spacing(1),
  marginBottom: theme.spacing(0.5),
}));

const RestaurantImage = styled(Box)(({ theme }) => ({
  width: 60,
  height: 60,
  flexShrink: 0,
  borderRadius: theme.shape.borderRadius,
  overflow: "hidden",
  "& img": {
    width: "100%",
    height: "100%",
    objectFit: "cover",
  },
}));

const CuisineChip = styled(Chip)(({ theme }) => ({
  height: 22,
  fontSize: 11,
  backgroundColor: theme.palette.grey[100],
  marginBottom: theme.spacing(0.5),
}));

const PriceChip = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.success.light,
  color: theme.palette.success.contrastText,
  fontWeight: 600,
  flexShrink: 0,
}));

const MealPeriodButton = styled(Button)(({ theme, selected }) => ({
  margin: theme.spacing(0, 0.5, 0, 0),
  padding: theme.spacing(0.5, 1.5),
  minWidth: 'auto',
  fontSize: '0.813rem',
  textTransform: 'none',
  borderColor: selected ? theme.palette.primary.main : theme.palette.divider,
  backgroundColor: selected ? 'rgba(25, 118, 210, 0.12)' : 'transparent',
  color: selected ? theme.palette.primary.main : theme.palette.text.secondary,
  fontWeight: selected ? 600 : 400,
  "&:hover": {
    borderColor: theme.palette.primary.main,
    backgroundColor: 'rgba(25, 118, 210, 0.08)',
  },
  '& .MuiButton-startIcon': {
    marginRight: theme.spacing(0.5),
    fontSize: '1rem',
  },
}));

const RestaurantCard = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(1.5),
  marginBottom: theme.spacing(1),
  display: "flex",
  justifyContent: "space-between",
  alignItems: "center",
  gap: theme.spacing(2),
  border: `2px solid #E0E0E0`,
  borderRadius: theme.spacing(1.5),
  backgroundColor: '#FFFFFF',
  transition: 'all 0.2s ease',
  overflow: 'hidden',
  '&:hover': {
    borderColor: '#FF9800',
    backgroundColor: '#FFFAF0',
    boxShadow: '0 2px 8px rgba(255, 152, 0, 0.15)'
  }
}));

const CountBadge = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  marginLeft: theme.spacing(1),
  height: 20,
  fontSize: 12,
}));

// Helper function to parse date string (handles DD/MM/YYYY and ISO formats)
const parseDate = (dateString) => {
  if (!dateString) return null;
  
  // If it's already a Date object
  if (dateString instanceof Date) {
    return dateString;
  }
  
  // If it's in DD/MM/YYYY format
  if (typeof dateString === 'string' && dateString.includes('/')) {
    const parts = dateString.split('/');
    if (parts.length === 3) {
      // DD/MM/YYYY format - convert to Date
      const day = parseInt(parts[0]);
      const month = parseInt(parts[1]) - 1; // Month is 0-indexed
      const year = parseInt(parts[2]);
      return new Date(year, month, day);
    }
  }
  
  // Try parsing as ISO or other standard format
  const date = new Date(dateString);
  return isNaN(date.getTime()) ? null : date;
};

// Helper function to generate dates between checkin and checkout
const generateDateRange = (checkinDate, checkoutDate) => {
  if (!checkinDate || !checkoutDate) return [];
  
  const dates = [];
  // Parse the dates properly
  const currentDate = parseDate(checkinDate);
  const endDate = parseDate(checkoutDate);
  
  if (!currentDate || !endDate) {
    console.warn('Invalid dates provided to generateDateRange:', { checkinDate, checkoutDate });
    return [];
  }
  
  // Normalize to midnight
  currentDate.setHours(0, 0, 0, 0);
  endDate.setHours(0, 0, 0, 0);
  
  // Include both start and end dates
  while (currentDate <= endDate) {
    dates.push(new Date(currentDate));
    currentDate.setDate(currentDate.getDate() + 1);
  }
  
  return dates;
};

// Helper function to format date for display
const formatDate = (date) => {
  // Parse the date first to handle different formats
  const d = parseDate(date);
  if (!d || isNaN(d.getTime())) {
    return 'Invalid Date';
  }
  const options = { month: 'short', day: 'numeric' };
  return d.toLocaleDateString('en-US', options);
};

// Helper function to get date string (YYYY-MM-DD)
const getDateString = (date) => {
  const d = new Date(date);
  return d.toISOString().split('T')[0];
};

const RestaurantSearch = ({ onSelect, value = [], checkinDate: propsCheckinDate, checkoutDate: propsCheckoutDate }) => {
  const dispatch = useDispatch();
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedDateRestaurants, setSelectedDateRestaurants] = useState(value);
  const [selectedDay, setSelectedDay] = useState("");
  const [selectedMealPeriod, setSelectedMealPeriod] = useState("Breakfast");
  const [showAllDays, setShowAllDays] = useState(false);
  const [showSuccessHint, setShowSuccessHint] = useState(false);
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Get restaurants from Redux store
  const { restaurants = [], loading, error } = useSelector(state => state.enquiryList || { restaurants: [], loading: false });
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  const selectedDmcIds = useSelector(selectSelectedDmcIds);
  
  // Use props dates first, fallback to Redux
  const reduxCheckinDate = useSelector(state => state.enquiry?.checkIn);
  const reduxCheckoutDate = useSelector(state => state.enquiry?.checkOut);
  
  const checkinDate = propsCheckinDate || reduxCheckinDate;
  const checkoutDate = propsCheckoutDate || reduxCheckoutDate;
  
  // Generate date range
  const dateRange = checkinDate && checkoutDate 
    ? generateDateRange(checkinDate, checkoutDate) 
    : [];

  // Debug logging for date range
  useEffect(() => {
    console.log('RestaurantSearch - Props Check-in:', propsCheckinDate);
    console.log('RestaurantSearch - Props Check-out:', propsCheckoutDate);
    console.log('RestaurantSearch - Redux Check-in:', reduxCheckinDate);
    console.log('RestaurantSearch - Redux Check-out:', reduxCheckoutDate);
    console.log('RestaurantSearch - Final Check-in:', checkinDate);
    console.log('RestaurantSearch - Final Check-out:', checkoutDate);
    
    if (checkinDate && checkoutDate) {
      const parsedCheckIn = parseDate(checkinDate);
      const parsedCheckOut = parseDate(checkoutDate);
      console.log('RestaurantSearch - Parsed Check-in:', parsedCheckIn);
      console.log('RestaurantSearch - Parsed Check-out:', parsedCheckOut);
      console.log('RestaurantSearch - Date Range Count:', dateRange.length);
      console.log('RestaurantSearch - Date Range:', dateRange.map(d => d.toISOString().split('T')[0]));
    }
  }, [propsCheckinDate, propsCheckoutDate, reduxCheckinDate, reduxCheckoutDate, checkinDate, checkoutDate, dateRange]);

  // Determine how many days to show initially
  const INITIAL_VISIBLE_DAYS = 3;
  const visibleDateRange = showAllDays ? dateRange : dateRange.slice(0, INITIAL_VISIBLE_DAYS);

  // Update internal state when value prop changes
  useEffect(() => {
    setSelectedDateRestaurants(value);
  }, [value]);

  // Initialize with first date if available
  useEffect(() => {
    if (dateRange.length > 0 && !selectedDay) {
      setSelectedDay(getDateString(dateRange[0]));
    }
  }, [dateRange, selectedDay]);

  // Helper function to format price
  const formatPrice = (price) => {
    const actualPrice = parseFloat(price) || 0;
    return actualPrice > 0 ? `SGD ${actualPrice.toLocaleString()}` : "Price on request";
  };

  // Filter restaurants by meal period
  const getRestaurantsByMealPeriod = () => {
    return restaurants.filter(restaurant => {
      const matchesSearch = restaurant.name.toLowerCase().includes(searchTerm.toLowerCase());
      // Check if restaurant has meals for the selected period
      const hasMealPeriod = restaurant.meals && restaurant.meals.some(
        meal => meal.meal_period === selectedMealPeriod
      );
      return matchesSearch && hasMealPeriod;
    });
  };

  const filteredRestaurants = getRestaurantsByMealPeriod();

  // Handle clicking outside to close dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        dropdownRef.current &&
        !dropdownRef.current.contains(event.target) &&
        inputRef.current &&
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

  // Handle restaurant selection with meal
  const handleRestaurantSelect = (restaurant, meal) => {
    if (!selectedDay) return;
    
    // Deep clone the selections to avoid mutation issues
    const updatedSelections = JSON.parse(JSON.stringify(selectedDateRestaurants));
    
    // Find if this date already exists
    const dateIndex = updatedSelections.findIndex(item => item.date === selectedDay);
    
    const newRestaurantEntry = {
      restaurant: restaurant,
      meal: meal,
      mealPeriod: meal.meal_period
    };
    
    if (dateIndex >= 0) {
      // Check if this exact meal is already added
      const mealExists = updatedSelections[dateIndex].restaurants.some(
        r => r.restaurant.restaurant_id === restaurant.restaurant_id && r.meal.meal_id === meal.meal_id
      );
      
      if (!mealExists) {
        // Create a new array with the new entry
        updatedSelections[dateIndex] = {
          ...updatedSelections[dateIndex],
          restaurants: [...updatedSelections[dateIndex].restaurants, newRestaurantEntry]
        };
      }
    } else {
      // Add new date entry
      updatedSelections.push({
        date: selectedDay,
        restaurants: [newRestaurantEntry]
      });
    }
    
    setSelectedDateRestaurants(updatedSelections);
    if (onSelect) onSelect(updatedSelections);
    setSearchTerm("");
    // Keep dropdown open so users can select more meals
    // setIsDropdownOpen(false); // Don't close the dropdown
    
    // Show success hint briefly
    setShowSuccessHint(true);
    setTimeout(() => setShowSuccessHint(false), 3000);
    
    // Refocus the search input to allow immediate next selection
    if (inputRef.current) {
      setTimeout(() => {
        inputRef.current.focus();
      }, 100);
    }
  };

  // Remove a restaurant from a specific date
  const handleRemoveRestaurant = (mealId) => {
    if (!selectedDay) return;
    
    // Deep clone to avoid mutation issues
    const updatedSelections = JSON.parse(JSON.stringify(selectedDateRestaurants));
    const dateIndex = updatedSelections.findIndex(item => item.date === selectedDay);
    
    if (dateIndex >= 0) {
      // Create new filtered array
      const filteredRestaurants = updatedSelections[dateIndex].restaurants.filter(
        r => r.meal.meal_id !== mealId
      );
      
      // Remove date entry if no restaurants left
      if (filteredRestaurants.length === 0) {
        updatedSelections.splice(dateIndex, 1);
      } else {
        // Update with new restaurants array
        updatedSelections[dateIndex] = {
          ...updatedSelections[dateIndex],
          restaurants: filteredRestaurants
        };
      }
      
      setSelectedDateRestaurants(updatedSelections);
      if (onSelect) onSelect(updatedSelections);
    }
  };

  // Get restaurants for the currently selected day
  const getCurrentDayRestaurants = () => {
    if (!selectedDay) return [];
    const dateEntry = selectedDateRestaurants.find(item => item.date === selectedDay);
    return dateEntry ? dateEntry.restaurants : [];
  };

  // Group meals by restaurant for better display
  const getGroupedMealsByRestaurant = () => {
    const currentMeals = getCurrentDayRestaurants();
    const grouped = {};
    
    currentMeals.forEach(entry => {
      const restaurantId = entry.restaurant.restaurant_id;
      if (!grouped[restaurantId]) {
        grouped[restaurantId] = {
          restaurant: entry.restaurant,
          meals: []
        };
      }
      grouped[restaurantId].meals.push({
        meal: entry.meal,
        mealPeriod: entry.mealPeriod
      });
    });
    
    return Object.values(grouped);
  };

  // Get meal price
  const getMealPrice = (meal) => {
    if (meal.set_menu_price) {
      return `SGD ${parseFloat(meal.set_menu_price).toLocaleString()}`;
    }
    if (meal.adult_price) {
      return `SGD ${parseFloat(meal.adult_price).toLocaleString()}/adult`;
    }
    if (meal.child_price) {
      return `SGD ${parseFloat(meal.child_price).toLocaleString()}/child`;
    }
    return "Price on request";
  };

  // Get total count of selected restaurants
  const getTotalSelectedCount = () => {
    return selectedDateRestaurants.reduce((total, dateEntry) => {
      return total + dateEntry.restaurants.length;
    }, 0);
  };

  // Get restaurant count for a specific date
  const getRestaurantCountForDate = (dateString) => {
    const dateEntry = selectedDateRestaurants.find(item => item.date === dateString);
    return dateEntry ? dateEntry.restaurants.length : 0;
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Select Restaurant by Day
          {getTotalSelectedCount() > 0 && (
            <CountBadge label={`${getTotalSelectedCount()} selected`} size="small" variant="outlined" />
          )}
        </InputLabel>
        
        {selectedDmcIds.length === 0 && (
          <Paper sx={{ p: 2, bgcolor: 'warning.light', color: 'text.secondary', mb: 2 }}>
            <Typography variant="body2">
              Please select at least one DMC to view available restaurants.
            </Typography>
          </Paper>
        )}
        
        {dateRange.length === 0 && selectedDmcIds.length > 0 && (
          <Paper sx={{ p: 2, bgcolor: 'warning.light', color: 'text.secondary' }}>
            <Typography variant="body2">
              Please select check-in and check-out dates to add restaurants.
            </Typography>
          </Paper>
        )}
        
        {dateRange.length > 0 && selectedDmcIds.length > 0 && (
          <Box>
            {/* Horizontal Day Selector */}
            <DayBoxContainer>
              {visibleDateRange.map((date, index) => {
                const dateString = getDateString(date);
                const isSelected = selectedDay === dateString;
                const restaurantCount = getRestaurantCountForDate(dateString);
                
                return (
                  <DayBox 
                    key={dateString} 
                    selected={isSelected}
                    onClick={() => setSelectedDay(dateString)}
                    elevation={isSelected ? 2 : 1}
                  >
                    <Typography 
                      variant="caption" 
                      color={isSelected ? "primary" : "text.secondary"} 
                      fontWeight={700}
                      sx={{ fontSize: '0.7rem', display: 'block', mb: 0.25 }}
                    >
                      Day {index + 1}
                    </Typography>
                    <Typography 
                      variant="body2" 
                      fontWeight={500}
                      sx={{ fontSize: '0.8rem', lineHeight: 1.2 }}
                    >
                      {formatDate(date)}
                    </Typography>
                    {restaurantCount > 0 && (
                      <Chip 
                        size="small" 
                        label={restaurantCount}
                        sx={{ 
                          height: 16, 
                          fontSize: 9, 
                          mt: 0.5,
                          bgcolor: 'primary.main',
                          color: 'white',
                          fontWeight: 600
                        }}
                      />
                    )}
                  </DayBox>
                );
              })}
              
              {/* Show More/Less button if there are more than initial days */}
              {dateRange.length > INITIAL_VISIBLE_DAYS && (
                <DayBox 
                  selected={false}
                  onClick={() => setShowAllDays(!showAllDays)}
                  elevation={1}
                  sx={{ 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center',
                    bgcolor: 'grey.50',
                    '&:hover': {
                      bgcolor: 'grey.100',
                    }
                  }}
                >
                  <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                    <Typography variant="caption" fontWeight={700} sx={{ fontSize: '0.7rem' }}>
                      {showAllDays ? 'Less' : 'More'}
                    </Typography>
                    {showAllDays ? <ExpandLessIcon sx={{ fontSize: 16 }} /> : <ExpandMoreIcon sx={{ fontSize: 16 }} />}
                    {!showAllDays && (
                      <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem', fontWeight: 600 }}>
                        +{dateRange.length - INITIAL_VISIBLE_DAYS}
                      </Typography>
                    )}
                  </Box>
                </DayBox>
              )}
            </DayBoxContainer>

            {/* Meal Period Selector */}
            {selectedDay && (
              <Box sx={{ mb: 2 }}>
                <Typography variant="caption" color="text.secondary" sx={{ mb: 1, display: 'block', fontWeight: 500 }}>
                  Select Meal Period:
                </Typography>
                <Box sx={{ display: 'flex', gap: 0, flexWrap: 'nowrap', alignItems: 'center' }}>
                  {['Breakfast', 'Lunch', 'Dinner'].map((period) => (
                    <MealPeriodButton
                      key={period}
                      variant="outlined"
                      size="small"
                      selected={selectedMealPeriod === period}
                      onClick={() => setSelectedMealPeriod(period)}
                      startIcon={
                        <span style={{ fontSize: '14px' }}>
                          {period === 'Breakfast' ? '🌅' :
                           period === 'Lunch' ? '☀️' : '🌙'}
                        </span>
                      }
                    >
                      {period}
                    </MealPeriodButton>
                  ))}
                </Box>
              </Box>
            )}

            {/* Restaurant Search for Selected Day */}
            {selectedDay && (
              <Box>
                <Box sx={{ position: 'relative', mb: 2 }}>
                  <TextField
                    inputRef={inputRef}
                    fullWidth
                    size="small"
                    variant="outlined"
                    placeholder={getCurrentDayRestaurants().length > 0 
                      ? `Add another ${selectedMealPeriod.toLowerCase()} or search...`
                      : `Search ${selectedMealPeriod.toLowerCase()} restaurants...`
                    }
                    value={searchTerm}
                    onChange={(e) => {
                      setSearchTerm(e.target.value);
                      setIsDropdownOpen(true);
                    }}
                    onFocus={() => setIsDropdownOpen(true)}
                    InputProps={{
                      startAdornment: <RestaurantIcon fontSize="small" sx={{ mr: 1, color: 'text.secondary' }} />
                    }}
                  />

                  {isDropdownOpen && (
                    <DropdownContainer ref={dropdownRef}>
                      <List disablePadding>
                        {loading ? (
                          <Box sx={{ p: 2, display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                            <CircularProgress size={20} sx={{ mr: 1 }} />
                            <Typography color="text.secondary">Loading restaurants...</Typography>
                          </Box>
                        ) : error ? (
                          <Box sx={{ p: 2, color: 'error.main', textAlign: 'center' }}>
                            <Typography>Error: {error}</Typography>
                          </Box>
                        ) : filteredRestaurants.length > 0 ? (
                          filteredRestaurants.map((restaurant) => {
                            // Get meals for selected period
                            const availableMeals = restaurant.meals.filter(
                              meal => meal.meal_period === selectedMealPeriod
                            );
                            
                            return (
                              <RestaurantOption key={restaurant.restaurant_id}>
                                <Box sx={{ display: 'flex', width: '100%', gap: 2 }}>
                                  <RestaurantInfo>
                                    <Typography variant="subtitle2" fontWeight={700} sx={{ fontSize: '0.95rem', color: 'text.primary' }}>
                                      {restaurant.name}
                                    </Typography>
                                    <RestaurantMetadata>
                                      {restaurant.city && (
                                        <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary' }}>
                                          <LocationOnIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                                          {restaurant.city}
                                        </Box>
                                      )}
                                    </RestaurantMetadata>
                                  </RestaurantInfo>
                                  {restaurant.master_image && (
                                    <RestaurantImage>
                                      <img src={restaurant.master_image} alt={restaurant.name} />
                                    </RestaurantImage>
                                  )}
                                </Box>
                                
                                {/* Show available meals */}
                                <Box sx={{ width: '100%', mt: 1.5 }}>
                                  <Typography 
                                    variant="caption" 
                                    sx={{ 
                                      mb: 1, 
                                      display: 'block',
                                      fontWeight: 600,
                                      color: 'text.primary',
                                      fontSize: '0.75rem'
                                    }}
                                  >
                                    Available {selectedMealPeriod} Options ({availableMeals.length})
                                  </Typography>
                                  <Box sx={{ 
                                    maxHeight: '200px', 
                                    overflowY: 'auto',
                                    overflowX: 'hidden',
                                    pr: 0.5,
                                    '&::-webkit-scrollbar': {
                                      width: '6px',
                                    },
                                    '&::-webkit-scrollbar-track': {
                                      backgroundColor: '#f1f1f1',
                                      borderRadius: '10px',
                                    },
                                    '&::-webkit-scrollbar-thumb': {
                                      backgroundColor: '#1976d2',
                                      borderRadius: '10px',
                                      '&:hover': {
                                        backgroundColor: '#1565c0',
                                      }
                                    }
                                  }}>
                                  {availableMeals.map((meal, mealIdx) => (
                                    <Box
                                      key={meal.meal_id}
                                      sx={{ 
                                        p: 1.5,
                                        mb: 1,
                                        cursor: 'pointer',
                                        borderRadius: 1.5,
                                        border: '2px solid',
                                        borderColor: '#E0E0E0',
                                        bgcolor: '#FFFFFF',
                                        transition: 'all 0.2s ease',
                                        overflow: 'hidden',
                                        '&:hover': {
                                          borderColor: '#FF9800',
                                          bgcolor: '#FFFAF0',
                                          transform: 'translateX(4px)',
                                          boxShadow: '0 4px 12px rgba(255, 152, 0, 0.15)'
                                        },
                                        '&:last-child': {
                                          mb: 0
                                        }
                                      }}
                                      onClick={() => handleRestaurantSelect(restaurant, meal)}
                                    >
                                      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 2 }}>
                                        <Box sx={{ flex: 1, minWidth: 0 }}>
                                          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 0.75, flexWrap: 'wrap' }}>
                                            <Typography 
                                              variant="body2" 
                                              fontWeight={600} 
                                              sx={{ 
                                                color: 'text.primary', 
                                                fontSize: '0.875rem',
                                                wordBreak: 'break-word'
                                              }}
                                            >
                                              {meal.meal_type}
                                            </Typography>
                                          
                                          </Box>
                                          
                                          <Box sx={{ display: 'flex', gap: 0.5, flexWrap: 'wrap', mb: 0.75 }}>
                                            <Chip 
                                              size="small" 
                                              label={meal.item_type} 
                                              icon={meal.item_type === 'Vegetarian' ? <span>🥗</span> : <span>🍖</span>}
                                              sx={{ 
                                                height: 20, 
                                                fontSize: '0.7rem',
                                                bgcolor: meal.item_type === 'Vegetarian' ? '#C8E6C9' : '#FFCDD2',
                                                color: meal.item_type === 'Vegetarian' ? '#1B5E20' : '#B71C1C',
                                                fontWeight: 600,
                                                border: meal.item_type === 'Vegetarian' ? '1px solid #81C784' : '1px solid #E57373',
                                                '& .MuiChip-label': {
                                                  padding: '0 8px',
                                                },
                                                '& .MuiChip-icon': {
                                                  margin: '0 2px 0 -4px',
                                                  fontSize: '14px'
                                                }
                                              }} 
                                            />
                                            <Chip 
                                              size="small" 
                                              label={meal.category} 
                                              sx={{ 
                                                height: 20, 
                                                fontSize: '0.7rem',
                                                bgcolor: meal.category === 'Alcoholic' ? '#FFE0B2' : '#B3E5FC',
                                                color: meal.category === 'Alcoholic' ? '#E65100' : '#01579B',
                                                fontWeight: 600,
                                                border: meal.category === 'Alcoholic' ? '1px solid #FFB74D' : '1px solid #4FC3F7',
                                                '& .MuiChip-label': {
                                                  padding: '0 8px',
                                                }
                                              }} 
                                            />
                                          </Box>
                                       
                                      </Box>
                                      
                                      {PriceHide === "0" && (
                                        <Box sx={{ 
                                          bgcolor: '#FFF9F0',
                                          px: 1.5,
                                          py: 0.75,
                                          borderRadius: 1.5,
                                          minWidth: 'fit-content',
                                          maxWidth: '140px',
                                          textAlign: 'right',
                                          boxShadow: '0 2px 6px rgba(255, 152, 0, 0.2)'
                                        }}>
                                          <Typography variant="caption" sx={{ display: 'block', fontSize: '0.65rem', color: '#E65100', fontWeight: 600, mb: 0.3 }}>
                                            Price
                                          </Typography>
                                          
                                          {/* Show set menu price */}
                                          {meal.set_menu_price && (
                                            <Typography 
                                              variant="body2" 
                                              fontWeight={700} 
                                              sx={{ 
                                                fontSize: '0.875rem', 
                                                color: '#F57C00',
                                                whiteSpace: 'nowrap',
                                                overflow: 'hidden',
                                                textOverflow: 'ellipsis'
                                              }}
                                            >
                                              SGD {parseFloat(meal.set_menu_price).toLocaleString()}
                                            </Typography>
                                          )}
                                          
                                          {/* Show adult and child prices separately */}
                                          {!meal.set_menu_price && (meal.adult_price || meal.child_price) && (
                                            <Box>
                                              {meal.adult_price && (
                                                <Typography 
                                                  variant="body2" 
                                                  fontWeight={600} 
                                                  sx={{ 
                                                    fontSize: '0.75rem', 
                                                    color: '#F57C00',
                                                    whiteSpace: 'nowrap',
                                                    lineHeight: 1.4
                                                  }}
                                                >
                                                  SGD {parseFloat(meal.adult_price).toLocaleString()}/adult
                                                </Typography>
                                              )}
                                              {meal.child_price && (
                                                <Typography 
                                                  variant="body2" 
                                                  fontWeight={600} 
                                                  sx={{ 
                                                    fontSize: '0.7rem', 
                                                    color: '#FF9800',
                                                    whiteSpace: 'nowrap',
                                                    lineHeight: 1.4
                                                  }}
                                                >
                                                  SGD {parseFloat(meal.child_price).toLocaleString()}/child
                                                </Typography>
                                              )}
                                            </Box>
                                          )}
                                          
                                          {/* Fallback if no price available */}
                                          {!meal.set_menu_price && !meal.adult_price && !meal.child_price && (
                                            <Typography 
                                              variant="caption" 
                                              sx={{ 
                                                fontSize: '0.7rem', 
                                                color: '#F57C00',
                                                fontStyle: 'italic'
                                              }}
                                            >
                                              On request
                                            </Typography>
                                          )}
                                        </Box>
                                      )}
                                    </Box>
                                    </Box>
                                  ))}
                                  </Box>
                                </Box>
                              </RestaurantOption>
                            );
                          })
                        ) : (
                          <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                            <Typography>
                              {restaurants.length > 0 
                                ? `No restaurants found for ${selectedMealPeriod.toLowerCase()}` 
                                : "No restaurants found for this location"}
                            </Typography>
                          </Box>
                        )}
                      </List>
                    </DropdownContainer>
                  )}
                </Box>

                {/* Success hint after selecting a meal */}
                {showSuccessHint && (
                  <Paper sx={{ 
                    p: 1.5, 
                    mb: 2, 
                    bgcolor: '#E8F5E9', 
                    border: '2px solid #81C784',
                    animation: 'slideDown 0.3s ease',
                    '@keyframes slideDown': {
                      from: { opacity: 0, transform: 'translateY(-10px)' },
                      to: { opacity: 1, transform: 'translateY(0)' }
                    }
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <Typography variant="caption" sx={{ fontSize: '0.75rem', color: '#2E7D32', fontWeight: 600 }}>
                        ✅ Meal added successfully! 
                      </Typography>
                      <Typography variant="caption" sx={{ fontSize: '0.7rem', color: '#388E3C', fontStyle: 'italic' }}>
                        (Keep searching to add more meals for this day)
                      </Typography>
                    </Box>
                  </Paper>
                )}

                {/* Selected restaurants for the current day */}
                {getCurrentDayRestaurants().length > 0 ? (
                  <Box>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1.5 }}>
                      <Typography 
                        variant="caption" 
                        sx={{ 
                          display: 'block',
                          fontWeight: 600,
                          color: 'text.primary',
                          fontSize: '0.75rem'
                        }}
                      >
                        ✓ Selected for {formatDate(new Date(selectedDay))} ({getCurrentDayRestaurants().length} meal{getCurrentDayRestaurants().length !== 1 ? 's' : ''})
                      </Typography>
                
                    </Box>
                    {getGroupedMealsByRestaurant().map((restaurantGroup, idx) => (
                      <Paper 
                        key={`${restaurantGroup.restaurant.restaurant_id}-${idx}`}
                        sx={{ 
                          p: 1.5,
                          mb: 1.5,
                          border: '2px solid #E0E0E0',
                          borderRadius: 1.5,
                          bgcolor: '#FFFFFF',
                          transition: 'all 0.2s ease',
                          overflow: 'hidden',
                          '&:hover': {
                            borderColor: '#FF9800',
                            bgcolor: '#FFFAF0',
                            boxShadow: '0 2px 8px rgba(255, 152, 0, 0.15)'
                          }
                        }}
                      >
                        {/* Restaurant Header */}
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 1 }}>
                          <RestaurantIcon fontSize="small" color="action" />
                          <Typography 
                            variant="body2" 
                            fontWeight={700} 
                            sx={{ 
                              color: 'text.primary',
                              overflow: 'hidden',
                              textOverflow: 'ellipsis',
                              whiteSpace: 'nowrap',
                              flex: 1
                            }}
                          >
                            {restaurantGroup.restaurant.name}
                          </Typography>
                          {restaurantGroup.meals.length > 1 && (
                            <Chip 
                              size="small" 
                              label={`${restaurantGroup.meals.length} meals`}
                              sx={{ 
                                height: 18, 
                                fontSize: '0.65rem',
                                bgcolor: '#E8F5E9',
                                color: '#2E7D32',
                                fontWeight: 600
                              }}
                            />
                          )}
                        </Box>
                        
                        {restaurantGroup.restaurant.city && (
                          <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 11, color: 'text.secondary', mb: 1 }}>
                            <LocationOnIcon fontSize="small" sx={{ mr: 0.3, fontSize: 12 }} />
                            {restaurantGroup.restaurant.city}
                          </Box>
                        )}
                        
                        {/* Meals List */}
                        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                          {restaurantGroup.meals.map((mealEntry, mealIdx) => {
                            const mealIcon = mealEntry.mealPeriod === 'Breakfast' ? '🌅' :
                                            mealEntry.mealPeriod === 'Lunch' ? '☀️' : '🌙';
                            
                            return (
                              <Box 
                                key={`${mealEntry.meal.meal_id}-${mealIdx}`}
                                sx={{ 
                                  display: 'flex', 
                                  justifyContent: 'space-between',
                                  alignItems: 'center',
                                  p: 1,
                                  bgcolor: 'grey.50',
                                  borderRadius: 1,
                                  border: '1px solid',
                                  borderColor: 'grey.200'
                                }}
                              >
                                <Box sx={{ display: 'flex', gap: 0.5, flexWrap: 'wrap', alignItems: 'center', flex: 1 }}>
                                  <Chip 
                                    size="small" 
                                    label={`${mealIcon} ${mealEntry.mealPeriod}`}
                                    sx={{ 
                                      height: 20,
                                      fontSize: '0.7rem',
                                      fontWeight: 600,
                                      bgcolor: mealEntry.mealPeriod === 'Breakfast' ? '#FFE0B2' : 
                                               mealEntry.mealPeriod === 'Lunch' ? '#FFF9C4' : '#E1BEE7',
                                      color: mealEntry.mealPeriod === 'Breakfast' ? '#E65100' : 
                                             mealEntry.mealPeriod === 'Lunch' ? '#F57F17' : '#6A1B9A',
                                      border: mealEntry.mealPeriod === 'Breakfast' ? '1px solid #FFB74D' : 
                                              mealEntry.mealPeriod === 'Lunch' ? '1px solid #FFF59D' : '1px solid #CE93D8',
                                      '& .MuiChip-label': {
                                        padding: '0 8px',
                                      }
                                    }}
                                  />
                                  <Chip 
                                    size="small" 
                                    label={mealEntry.meal.meal_type} 
                                    sx={{ 
                                      height: 20, 
                                      fontSize: '0.7rem',
                                      bgcolor: '#BBDEFB',
                                      color: '#0D47A1',
                                      fontWeight: 600,
                                      border: '1px solid #90CAF9',
                                      '& .MuiChip-label': {
                                        padding: '0 8px',
                                      }
                                    }} 
                                  />
                                  <Chip 
                                    size="small" 
                                    label={mealEntry.meal.item_type}
                                    icon={mealEntry.meal.item_type === 'Vegetarian' ? <span>🥗</span> : <span>🍖</span>}
                                    sx={{ 
                                      height: 20, 
                                      fontSize: '0.7rem',
                                      bgcolor: mealEntry.meal.item_type === 'Vegetarian' ? '#C8E6C9' : '#FFCDD2',
                                      color: mealEntry.meal.item_type === 'Vegetarian' ? '#1B5E20' : '#B71C1C',
                                      fontWeight: 600,
                                      border: mealEntry.meal.item_type === 'Vegetarian' ? '1px solid #81C784' : '1px solid #E57373',
                                      '& .MuiChip-label': {
                                        padding: '0 8px',
                                      },
                                      '& .MuiChip-icon': {
                                        margin: '0 2px 0 -4px',
                                        fontSize: '14px'
                                      }
                                    }} 
                                  />
                                </Box>
                                
                                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                                  {PriceHide === "0" && (
                                    <Box sx={{
                                      bgcolor: '#FFF9F0',
                                      px: 1,
                                      py: 0.5,
                                      borderRadius: 1,
                                      minWidth: '90px',
                                      boxShadow: '0 1px 4px rgba(255, 152, 0, 0.2)'
                                    }}>
                                      {/* Show set menu price */}
                                      {mealEntry.meal.set_menu_price && (
                                        <Typography 
                                          variant="body2" 
                                          fontWeight={700} 
                                          sx={{ 
                                            fontSize: '0.75rem', 
                                            color: '#F57C00',
                                            whiteSpace: 'nowrap',
                                            overflow: 'hidden',
                                            textOverflow: 'ellipsis'
                                          }}
                                        >
                                          SGD {parseFloat(mealEntry.meal.set_menu_price).toLocaleString()}
                                        </Typography>
                                      )}
                                      
                                      {/* Show adult and child prices separately */}
                                      {!mealEntry.meal.set_menu_price && (mealEntry.meal.adult_price || mealEntry.meal.child_price) && (
                                        <Box>
                                          {mealEntry.meal.adult_price && (
                                            <Typography 
                                              variant="body2" 
                                              fontWeight={600} 
                                              sx={{ 
                                                fontSize: '0.7rem', 
                                                color: '#F57C00',
                                                whiteSpace: 'nowrap',
                                                lineHeight: 1.3
                                              }}
                                            >
                                              SGD {parseFloat(mealEntry.meal.adult_price).toLocaleString()}/a
                                            </Typography>
                                          )}
                                          {mealEntry.meal.child_price && (
                                            <Typography 
                                              variant="body2" 
                                              fontWeight={600} 
                                              sx={{ 
                                                fontSize: '0.65rem', 
                                                color: '#FF9800',
                                                whiteSpace: 'nowrap',
                                                lineHeight: 1.3
                                              }}
                                            >
                                              SGD {parseFloat(mealEntry.meal.child_price).toLocaleString()}/c
                                            </Typography>
                                          )}
                                        </Box>
                                      )}
                                      
                                      {/* Fallback if no price available */}
                                      {!mealEntry.meal.set_menu_price && !mealEntry.meal.adult_price && !mealEntry.meal.child_price && (
                                        <Typography 
                                          variant="caption" 
                                          sx={{ 
                                            fontSize: '0.65rem', 
                                            color: '#F57C00',
                                            fontStyle: 'italic'
                                          }}
                                        >
                                          On request
                                        </Typography>
                                      )}
                                    </Box>
                                  )}
                                  <IconButton
                                    size="small"
                                    onClick={() => handleRemoveRestaurant(mealEntry.meal.meal_id)}
                                    sx={{
                                      width: 28,
                                      height: 28,
                                      '&:hover': {
                                        bgcolor: 'error.light',
                                        color: 'error.main'
                                      }
                                    }}
                                  >
                                    <CloseIcon sx={{ fontSize: 16 }} />
                                  </IconButton>
                                </Box>
                              </Box>
                            );
                          })}
                        </Box>
                      </Paper>
                    ))}
                  </Box>
                ) : (
                  <Paper sx={{ p: 2, bgcolor: '#FFF9F0', textAlign: 'center', border: '1px dashed #FFB74D' }}>
                    <Typography variant="body2" color="text.secondary" sx={{ mb: 0.5 }}>
                      No meals selected for this day yet
                    </Typography>
                    <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem', fontStyle: 'italic' }}>
                      Select meal period above and choose from available restaurants
                    </Typography>
                  </Paper>
                )}
              </Box>
            )}
          </Box>
        )}
      </Box>
    </SearchContainer>
  );
};

export default RestaurantSearch;
