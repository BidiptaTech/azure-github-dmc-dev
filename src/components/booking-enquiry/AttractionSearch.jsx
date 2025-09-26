import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import { fetchEnquiryList } from "@/slice/common/enquiryListSlice";
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
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import CloseIcon from "@mui/icons-material/Close";

// Styled components
const SearchContainer = styled(Box)(({ theme }) => ({
  position: "relative",
  marginBottom: theme.spacing(2),
}));

const DropdownContainer = styled(Paper)(({ theme }) => ({
  position: "absolute",
  width: "100%",
  maxHeight: 210,
  overflowY: "auto",
  zIndex: 20,
  marginTop: theme.spacing(0.5),
  boxShadow: theme.shadows[3],
  // Fix scrolling issues
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
  // Ensure proper padding at bottom
  paddingBottom: theme.spacing(0.5),
}));

const AttractionOption = styled(ListItem)(({ theme }) => ({
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
    marginBottom: theme.spacing(0.5), // Add extra margin to last item
  },
}));

const AttractionInfo = styled(Box)({
  flex: 1,
});

const AttractionMetadata = styled(Box)(({ theme }) => ({
  display: "flex",
  flexWrap: "wrap",
  gap: theme.spacing(1),
  marginBottom: theme.spacing(0.5),
}));

const AttractionImage = styled(Box)(({ theme }) => ({
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

const PriceChip = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.success.light,
  color: theme.palette.success.contrastText,
  fontWeight: 600,
  flexShrink: 0,
}));

const AttractionSearch = ({ onSelect, value = [] }) => {
  const dispatch = useDispatch();
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedAttractions, setSelectedAttractions] = useState(value);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherAttractionName, setOtherAttractionName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Update internal state when value prop changes
  useEffect(() => {
    setSelectedAttractions(value);
  }, [value]);

  // Get attractions from Redux store
  const { attractions = [], loading, error } = useSelector(state => state.enquiryList || { attractions: [], loading: false });
  const { searchLocation } = useSelector(state => state.enquiry || { searchLocation: {} });
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // Helper function to format price
  const formatPrice = (price) => {
    const actualPrice = parseFloat(price) || 0;
    return actualPrice > 0 ? `SGD ${actualPrice.toLocaleString()}` : "Price on request";
  };

  // Manual fetch button for testing
  // const handleManualFetch = () => {
  //   if (searchLocation && searchLocation.country && searchLocation.city) {
  //     dispatch(fetchEnquiryList({
  //       country: searchLocation.country,
  //       city: searchLocation.city
  //     }));
  //   }
  // };

  // Filter attractions based on search term and price
  const filteredAttractions = attractions ? attractions.filter((attraction) => {
    // Filter by search term
    const matchesSearch = attraction.name.toLowerCase().includes(searchTerm.toLowerCase());
    
    // Filter by price - only show attractions with base_price > 0
    const hasValidPrice = parseFloat(attraction.base_price) > 0;
    
    return matchesSearch && hasValidPrice;
  }) : [];

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

  // Handle attraction selection
  const handleAttractionSelect = (attraction) => {
    if (attraction === "others") {
      setShowOthersInput(true);
    } else {
      // Check if this attraction is already selected
      const isAlreadySelected = selectedAttractions.some(item => item.id === attraction.id);
      
      if (!isAlreadySelected) {
        const updatedSelections = [...selectedAttractions, attraction];
        setSelectedAttractions(updatedSelections);
        if (onSelect) onSelect(updatedSelections);
      }
    }
    setSearchTerm("");
    setIsDropdownOpen(false);
  };

  // Handle other attraction name submission
  const handleOtherAttractionSubmit = () => {
    if (otherAttractionName.trim()) {
      const customAttraction = { 
        id: `custom-${Date.now()}`, 
        name: otherAttractionName.trim() 
      };
      const updatedSelections = [...selectedAttractions, customAttraction];
      setSelectedAttractions(updatedSelections);
      if (onSelect) onSelect(updatedSelections);
      setOtherAttractionName("");
      setShowOthersInput(false);
    }
  };

  // Remove a selected attraction
  const handleRemoveAttraction = (attractionId) => {
    const updatedSelections = selectedAttractions.filter(
      attraction => attraction.id !== attractionId
    );
    setSelectedAttractions(updatedSelections);
    if (onSelect) onSelect(updatedSelections);
  };

  // Generate a placeholder for the attraction description
  const getDescriptionSnippet = (description) => {
    if (!description) return "No description available";
    
    // Remove HTML tags and limit to 100 characters
    const plainText = description.replace(/<[^>]*>/g, ' ').trim();
    return plainText.length > 100 ? plainText.substring(0, 100) + '...' : plainText;
  };

  // Format opening hours
  const formatHours = (openTime, closeTime) => {
    if (!openTime || !closeTime || !Array.isArray(openTime) || !Array.isArray(closeTime) || openTime.length === 0 || closeTime.length === 0) {
      return null;
    }
    
    return `${openTime[0]} - ${closeTime[0]}`;
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Attractions {attractions.length > 0 && (
            <CountBadge label={attractions.length} size="small" variant="outlined" />
          )}
        </InputLabel>
        
        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for attractions..."
          value={searchTerm}
          onChange={(e) => {
            setSearchTerm(e.target.value);
            setIsDropdownOpen(true);
          }}
          onFocus={() => setIsDropdownOpen(true)}
        />

        {/* {process.env.NODE_ENV !== 'production' && (
          <button 
            className="debug-fetch-btn" 
            onClick={handleManualFetch}
            title="Manually fetch attractions"
          >
            Refresh Data
          </button>
        )} */}

        {isDropdownOpen && (
          <DropdownContainer ref={dropdownRef}>
            <List disablePadding>
              {loading ? (
                <Box sx={{ p: 2, display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                  <CircularProgress size={20} sx={{ mr: 1 }} />
                  <Typography color="text.secondary">Loading attractions...</Typography>
                </Box>
              ) : error ? (
                <Box sx={{ p: 2, color: 'error.main', textAlign: 'center', borderLeft: 3, borderColor: 'error.main', bgcolor: 'error.light', opacity: 0.05 }}>
                  <Typography>Error: {error}</Typography>
                </Box>
              ) : filteredAttractions.length > 0 ? (
                filteredAttractions.map((attraction) => (
                  <AttractionOption
                    key={attraction.attraction_id}
                    onClick={() => handleAttractionSelect(attraction)}
                  >
                    <AttractionInfo>
                      <Typography variant="subtitle2" fontWeight={500}>{attraction.name}</Typography>
                      <AttractionMetadata>
                        {attraction.location && (
                          <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary' }}>
                            <LocationOnIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                            {attraction.location}
                          </Box>
                        )}
                        {formatHours(attraction.open_time, attraction.close_time) && (
                          <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary' }}>
                            <AccessTimeIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                            {formatHours(attraction.open_time, attraction.close_time)}
                          </Box>
                        )}
                      </AttractionMetadata>
                      {attraction.description && (
                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12, lineHeight: 1.4 }}>
                          {getDescriptionSnippet(attraction.description)}
                        </Typography>
                      )}
                          {PriceHide === "0" ? (
                      attraction.base_price && (
                        <PriceChip 
                          label={`${formatPrice(attraction.base_price)}/person`}
                          size="small"
                          sx={{ mt: 1 }}
                        />
                      )):(
                        <div className="text-12 text-dark-1 fw-500">
                          Price available on request
                        </div>
                      )}
                    </AttractionInfo>
                    {attraction.master_image && (
                      <AttractionImage>
                        <img src={attraction.master_image} alt={attraction.name} />
                      </AttractionImage>
                    )}
                  </AttractionOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>
                    {attractions.length > 0 
                      ? "No attractions match your search" 
                      : "No attractions found for this location"}
                  </Typography>
                </Box>
              )}
            </List>
          </DropdownContainer>
        )}

        {selectedAttractions.length > 0 && (
          <SelectedContainer>
            <SelectedHeader>
              <Typography variant="subtitle2" color="primary" fontWeight={500}>
                {selectedAttractions.length} Attraction{selectedAttractions.length !== 1 ? 's' : ''} Selected
              </Typography>
            </SelectedHeader>
            <SelectedItems>
              {selectedAttractions.map((attraction) => (
                <SelectedItem key={attraction.attraction_id}>
                  <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                    <Typography variant="body2" fontWeight={500}>{attraction.name}</Typography>
                    {attraction.location && (
                      <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary', mt: 0.5 }}>
                        <LocationOnIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                        {attraction.location}
                      </Box>
                    )}
                  </Box>
                  <IconButton 
                    size="small"
                    onClick={() => handleRemoveAttraction(attraction.attraction_id)}
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

export default AttractionSearch;
