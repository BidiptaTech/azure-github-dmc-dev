import React, { useState, useEffect, useRef } from "react";
import { useSelector } from "react-redux";
import { styled } from "@mui/material/styles";
import {
  Box,
  TextField,
  Typography,
  Paper,
  List,
  ListItem,
  ListItemText,
  ListItemAvatar,
  Avatar,
  Chip,
  CircularProgress,
  InputAdornment,
  Button,
  InputLabel
} from "@mui/material";
import SearchIcon from "@mui/icons-material/Search";
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
  borderRadius: theme.spacing(1),
  // Fix scrolling issues
  scrollbarWidth: "thin",
  "&::-webkit-scrollbar": {
    width: "6px",
  },
  "&::-webkit-scrollbar-track": {
    backgroundColor: "transparent",
  },
  "&::-webkit-scrollbar-thumb": {
    backgroundColor: theme.palette.grey[400],
    borderRadius: "3px",
    "&:hover": {
      backgroundColor: theme.palette.grey[600],
    },
  },
}));

const HotelOption = styled(ListItem)(({ theme }) => ({
  cursor: "pointer",
  padding: theme.spacing(1.5, 2),
  borderBottom: `1px solid ${theme.palette.divider}`,
  minHeight: "auto",
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
    paddingBottom: theme.spacing(2), // Extra padding for last item
  },
  "&:first-child": {
    paddingTop: theme.spacing(1.5),
  },
}));

const OthersInputContainer = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(1),
  display: "flex",
  gap: theme.spacing(1),
}));

const SelectedHotelsContainer = styled(Box)(({ theme }) => ({
  display: "flex",
  flexWrap: "wrap",
  gap: theme.spacing(1),
  marginTop: theme.spacing(2),
}));

const HotelChip = styled(Chip)(({ theme }) => ({
  "& .MuiChip-deleteIcon:hover": {
    color: theme.palette.error.main,
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
  marginTop: theme.spacing(0.5),
}));

const PreferredHotelsDropdown = ({ onSelect, value = [] }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedHotels, setSelectedHotels] = useState(value);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherHotelName, setOtherHotelName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Update internal state when value prop changes
  useEffect(() => {
    setSelectedHotels(value);
  }, [value]);

  const { hotels = [], loading, error } = useSelector((state) => state.enquiryList || {});
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // Helper function to format price
  const formatPrice = (price) => {
    const actualPrice = parseFloat(price) || 0;
    return actualPrice > 0 ? `SGD ${actualPrice.toLocaleString()}` : "Price on request";
  };
  const filteredHotels = hotels.filter((hotel) => {
    // Filter by search term
    const matchesSearch = hotel.name.toLowerCase().includes(searchTerm.toLowerCase());
    
    // Filter by price - only show hotels with single_base_price > 0
    const hasValidPrice = parseFloat(hotel.single_base_price) > 0;
    
    return matchesSearch && hasValidPrice;
  });

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
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handleHotelSelect = (hotel) => {
    if (hotel === "others") {
      setShowOthersInput(true);
      setIsDropdownOpen(false);
    } else {
      // Replace with single hotel (not add to array)
      const updated = [hotel];
      setSelectedHotels(updated);
      onSelect?.(updated);
      setSearchTerm("");
      setIsDropdownOpen(false);
    }
  };

  const handleOtherHotelSubmit = () => {
    if (otherHotelName.trim()) {
      const customHotel = {
        hotel_unique_id: `custom-${Date.now()}`,
        name: otherHotelName.trim(),
        address: "Custom Address"
      };
      const updated = [...selectedHotels, customHotel];
      setSelectedHotels(updated);
      onSelect?.(updated);
      setOtherHotelName("");
      setShowOthersInput(false);
    }
  };

  const handleRemoveHotel = (hotel_unique_id) => {
    const updated = selectedHotels.filter(h => h.hotel_unique_id !== hotel_unique_id);
    setSelectedHotels(updated);
    onSelect?.(updated);
  };

  const handleKeyDown = (e) => {
    if (e.key === "Enter" && showOthersInput) handleOtherHotelSubmit();
    else if (e.key === "Escape") {
      setIsDropdownOpen(false);
      setShowOthersInput(false);
    }
  };

  const renderStars = (count) =>
    [...Array(parseInt(count) || 0)].map((_, i) => (
      <span key={i} style={{ color: "gold", fontSize: 12 }}>★</span>
    ));

  const getDescriptionSnippet = (desc) => {
    const text = desc?.replace(/<[^>]*>/g, " ").trim() || "";
    return text.length > 100 ? text.slice(0, 100) + "..." : text;
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Preferred Hotel (Select One) {hotels.length > 0 && (
            <CountBadge label={hotels.length} size="small" variant="outlined" />
          )}
        </InputLabel>

        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for hotels..."
          value={searchTerm}
          onChange={(e) => {
            setSearchTerm(e.target.value);
            setIsDropdownOpen(true);
          }}
          onFocus={() => setIsDropdownOpen(true)}
          onKeyDown={handleKeyDown}
          InputProps={{
            startAdornment: (
              <InputAdornment position="start">
                <SearchIcon />
              </InputAdornment>
            )
          }}
        />

        {isDropdownOpen && !showOthersInput && (
          <DropdownContainer ref={dropdownRef}>
            <List 
              disablePadding 
              sx={{ 
                maxHeight: "100%", 
                overflow: "visible",
                paddingBottom: 0,
                "& .MuiListItem-root:last-child": {
                  marginBottom: 0,
                }
              }}
            >
              {loading ? (
                <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', p: 2 }}>
                  <CircularProgress size={20} sx={{ mr: 1 }} />
                  <Typography color="text.secondary">Loading hotels...</Typography>
                </Box>
              ) : error ? (
                <Box sx={{ p: 2, color: 'error.main', textAlign: 'center', borderLeft: 3, borderColor: 'error.main', bgcolor: 'error.light', opacity: 0.05 }}>
                  <Typography>Error: {error}</Typography>
                </Box>
              ) : filteredHotels.length > 0 ? (
                filteredHotels.map((hotel) => (
                  <HotelOption
                    key={hotel.hotel_unique_id}
                    onClick={() => handleHotelSelect(hotel)}
                  >
                    <ListItemAvatar>
                      <Avatar variant="rounded" src={hotel.main_image} />
                    </ListItemAvatar>
                    <ListItemText
                      primary={
                        <>
                          <Typography variant="subtitle2" fontWeight={500}>
                            {hotel.name}
                          </Typography>
                          {renderStars(hotel.hotel_star_rating)}
                        </>
                      }
                      secondary={
                        <>
                          <Typography variant="body2" color="text.secondary">
                            {hotel.address}
                          </Typography>
                          <Typography variant="caption" color="text.secondary">
                            {getDescriptionSnippet(hotel.description)}
                          </Typography>
                          {PriceHide === "0" ? (
                          hotel.single_base_price && (
                            <PriceChip 
                              label={`${formatPrice(hotel.single_base_price)}/night`}
                              size="small"
                            />
                          )):(
                            <div className="text-12 text-dark-1 fw-500">
                              Price available on request
                            </div>
                            )}
                        </>
                      }
                    />
                  </HotelOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>
                    {hotels.length > 0 
                      ? "No hotels match your search" 
                      : "No hotels found for this location"}
                  </Typography>
                </Box>
              )}
            </List>
          </DropdownContainer>
        )}

        {showOthersInput && (
          <OthersInputContainer>
            <TextField
              fullWidth 
              size="small"
              placeholder="Enter hotel name..."
              value={otherHotelName}
              onChange={(e) => setOtherHotelName(e.target.value)}
              onKeyDown={handleKeyDown}
            />
            <Button variant="contained" onClick={handleOtherHotelSubmit}>
              Add
            </Button>
          </OthersInputContainer>
        )}

        {selectedHotels.length > 0 && (
          <SelectedHotelsContainer>
            {selectedHotels.map((hotel) => (
              <HotelChip
                key={hotel.hotel_unique_id}
                label={hotel.name}
                onDelete={() => handleRemoveHotel(hotel.hotel_unique_id)}
                color="primary"
                variant="outlined"
                deleteIcon={<CloseIcon fontSize="small" />}
              />
            ))}
          </SelectedHotelsContainer>
        )}
      </Box>
    </SearchContainer>
  );
};

export default PreferredHotelsDropdown;
