import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import { fetchEnquiryList } from "../../../slice/common/enquiryListSlice";
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
  CircularProgress
} from "@mui/material";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import CloseIcon from "@mui/icons-material/Close";
import EventIcon from "@mui/icons-material/Event";

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

const RestaurantOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  justifyContent: "space-between",
  alignItems: "flex-start",
  gap: theme.spacing(1),
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
  },
}));

const RestaurantInfo = styled(Box)({
  flex: 1,
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

const SelectedContainer = styled(Paper)(({ theme }) => ({
  marginTop: theme.spacing(2),
  borderRadius: theme.shape.borderRadius,
  overflow: "hidden",
  border: `1px solid ${theme.palette.divider}`,
}));

const SelectedHeader = styled(Box)(({ theme }) => ({
  padding: theme.spacing(1, 2),
  backgroundColor: `${theme.palette.primary.light}20`,
  borderBottom: `1px solid ${theme.palette.divider}`,
}));

const SelectedItems = styled(Box)(({ theme }) => ({
  maxHeight: 200,
  overflowY: "auto",
}));

const SelectedItem = styled(ListItem)(({ theme }) => ({
  display: "flex",
  justifyContent: "space-between",
  alignItems: "center",
  padding: theme.spacing(1, 2),
  borderBottom: `1px solid ${theme.palette.divider}`,
  "&:last-child": {
    borderBottom: "none",
  },
}));

const CountBadge = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  marginLeft: theme.spacing(1),
  height: 20,
  fontSize: 12,
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

const RestaurantSearch = ({ onSelect }) => {
  const dispatch = useDispatch();
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedRestaurants, setSelectedRestaurants] = useState([]);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherRestaurantName, setOtherRestaurantName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Get restaurants from Redux store
  const { restaurants = [], loading, error } = useSelector(state => state.enquiryList || { restaurants: [], loading: false });
  const { searchLocation } = useSelector(state => state.enquiry || { searchLocation: {} });

  // Helper function to format price
  const formatPrice = (price) => {
    const actualPrice = parseFloat(price) || 0;
    return actualPrice > 0 ? `$${actualPrice.toLocaleString()}` : "Price on request";
  };

  // Manual fetch button for testing
  const handleManualFetch = () => {
    if (searchLocation && searchLocation.country && searchLocation.city) {
      dispatch(fetchEnquiryList({
        country: searchLocation.country,
        city: searchLocation.city
      }));
    }
  };

  // Filter restaurants based on search term
  const filteredRestaurants = restaurants ? restaurants.filter((restaurant) =>
    restaurant.name.toLowerCase().includes(searchTerm.toLowerCase())
  ) : [];

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

  // Handle restaurant selection
  const handleRestaurantSelect = (restaurant) => {
    if (restaurant === "others") {
      setShowOthersInput(true);
    } else {
      // Check if this restaurant is already selected
      const isAlreadySelected = selectedRestaurants.some(item => item.restaurant_id === restaurant.restaurant_id);
      
      if (!isAlreadySelected) {
        const updatedSelections = [...selectedRestaurants, restaurant];
        setSelectedRestaurants(updatedSelections);
        if (onSelect) onSelect(updatedSelections);
      }
    }
    setSearchTerm("");
    setIsDropdownOpen(false);
  };

  // Handle other restaurant name submission
  const handleOtherRestaurantSubmit = () => {
    if (otherRestaurantName.trim()) {
      const customRestaurant = { 
        restaurant_id: `custom-${Date.now()}`, 
        name: otherRestaurantName.trim() 
      };
      const updatedSelections = [...selectedRestaurants, customRestaurant];
      setSelectedRestaurants(updatedSelections);
      if (onSelect) onSelect(updatedSelections);
      setOtherRestaurantName("");
      setShowOthersInput(false);
    }
  };

  // Remove a selected restaurant
  const handleRemoveRestaurant = (restaurantId) => {
    const updatedSelections = selectedRestaurants.filter(
      restaurant => restaurant.restaurant_id !== restaurantId
    );
    setSelectedRestaurants(updatedSelections);
    if (onSelect) onSelect(updatedSelections);
  };

  // Generate a placeholder for the restaurant description
  const getDescriptionSnippet = (description) => {
    if (!description) return "No description available";
    
    // Remove HTML tags and limit to 100 characters
    const plainText = description.replace(/<[^>]*>/g, ' ').trim();
    return plainText.length > 100 ? plainText.substring(0, 100) + '...' : plainText;
  };

  // Format meal availability
  const getMealAvailability = (restaurant) => {
    const meals = [];
    if (restaurant.breakfast_available) meals.push("Breakfast");
    if (restaurant.lunch_available) meals.push("Lunch");
    if (restaurant.dinner_available) meals.push("Dinner");
    
    return meals.length > 0 ? meals.join(", ") : null;
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Restaurants {restaurants.length > 0 && (
            <CountBadge label={restaurants.length} size="small" variant="outlined" />
          )}
        </InputLabel>
        
        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for restaurants..."
          value={searchTerm}
          onChange={(e) => {
            setSearchTerm(e.target.value);
            setIsDropdownOpen(true);
          }}
          onFocus={() => setIsDropdownOpen(true)}
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
                <Box sx={{ p: 2, color: 'error.main', textAlign: 'center', borderLeft: 3, borderColor: 'error.main', bgcolor: 'error.light', opacity: 0.05 }}>
                  <Typography>Error: {error}</Typography>
                </Box>
              ) : filteredRestaurants.length > 0 ? (
                filteredRestaurants.map((restaurant) => (
                  <RestaurantOption
                    key={restaurant.restaurant_id}
                    onClick={() => handleRestaurantSelect(restaurant)}
                  >
                    <RestaurantInfo>
                      <Typography variant="subtitle2" fontWeight={500}>{restaurant.name}</Typography>
                      <RestaurantMetadata>
                        {restaurant.city && (
                          <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary' }}>
                            <LocationOnIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                            {restaurant.city}
                          </Box>
                        )}
                        {getMealAvailability(restaurant) && (
                          <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary' }}>
                            <EventIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                            {getMealAvailability(restaurant)}
                          </Box>
                        )}
                      </RestaurantMetadata>
                      {restaurant.cuisine && (
                        <CuisineChip size="small" label={restaurant.cuisine} />
                      )}
                      {restaurant.description && (
                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12, lineHeight: 1.4 }}>
                          {getDescriptionSnippet(restaurant.description)}
                        </Typography>
                      )}
                      {restaurant['base-price'] && (
                        <PriceChip 
                          label={`${formatPrice(restaurant['base-price'])}/person`}
                          size="small"
                          sx={{ mt: 1 }}
                        />
                      )}
                    </RestaurantInfo>
                    {restaurant.master_image && (
                      <RestaurantImage>
                        <img src={restaurant.master_image} alt={restaurant.name} />
                      </RestaurantImage>
                    )}
                  </RestaurantOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>
                    {restaurants.length > 0 
                      ? "No restaurants match your search" 
                      : "No restaurants found for this location"}
                  </Typography>
                </Box>
              )}
            </List>
          </DropdownContainer>
        )}
      
        {selectedRestaurants.length > 0 && (
          <SelectedContainer>
            <SelectedHeader>
              <Typography variant="subtitle2" color="primary" fontWeight={500}>
                {selectedRestaurants.length} Restaurant{selectedRestaurants.length !== 1 ? 's' : ''} Selected
              </Typography>
            </SelectedHeader>
            <SelectedItems>
              {selectedRestaurants.map((restaurant) => (
                <SelectedItem key={restaurant.restaurant_id}>
                  <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                    <Typography variant="body2" fontWeight={500}>{restaurant.name}</Typography>
                    {restaurant.cuisine && (
                      <CuisineChip 
                        size="small" 
                        label={restaurant.cuisine} 
                        sx={{ mt: 0.5, mr: 'auto' }}
                      />
                    )}
                    {restaurant.city && (
                      <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary', mt: 0.5 }}>
                        <LocationOnIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                        {restaurant.city}
                      </Box>
                    )}
                  </Box>
                  <IconButton 
                    size="small"
                    onClick={() => handleRemoveRestaurant(restaurant.restaurant_id)}
                    sx={{ 
                      width: 24, 
                      height: 24,
                      '&:hover': { 
                        bgcolor: 'action.hover',
                        color: 'error.main' 
                      } 
                    }}
                  >
                    <CloseIcon fontSize="small" />
                  </IconButton>
                </SelectedItem>
              ))}
            </SelectedItems>
          </SelectedContainer>
        )}
      </Box>
    </SearchContainer>
  );
};

export default RestaurantSearch;
