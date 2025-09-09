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
  InputLabel,
  CircularProgress,
  Chip
} from "@mui/material";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import StarIcon from "@mui/icons-material/Star";
import AddIcon from "@mui/icons-material/Add";

// Styled components
const SearchContainer = styled(Box)(({ theme }) => ({
  position: "relative",
  marginBottom: theme.spacing(2),
}));
const PriceChip = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.success.light,
  color: theme.palette.success.contrastText,
  fontWeight: 600,
  flexShrink: 0,
  marginTop: theme.spacing(0.5),
}));
const DropdownContainer = styled(Paper)(({ theme }) => ({
  position: "absolute",
  width: "100%",
  maxHeight: 210,
  overflowY: "auto",
  zIndex: 20,
  marginTop: theme.spacing(0.5),
  boxShadow: theme.shadows[3],
}));

const HotelOption = styled(ListItem)(({ theme }) => ({
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

const HotelInfo = styled(Box)({
  flex: 1,
});

const HotelImage = styled(Box)(({ theme }) => ({
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

const StarRating = styled(Box)(({ theme }) => ({
  display: "flex",
  marginBottom: theme.spacing(0.5),
  color: "gold",
}));

const SelectedHotel = styled(Box)(({ theme }) => ({
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

const HotelDetail = styled(Chip)(({ theme }) => ({
  marginLeft: theme.spacing(1),
  height: 22,
  fontSize: 12,
}));

const CountBadge = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  marginLeft: theme.spacing(1),
  height: 20,
  fontSize: 12,
}));

const OthersInputContainer = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(1),
  display: "flex",
  gap: theme.spacing(1),
}));

const HotelDropOffSearch = ({ onSelect, value = null }) => {
  const dispatch = useDispatch();
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedHotel, setSelectedHotel] = useState(value);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherHotelName, setOtherHotelName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Update internal state when value prop changes
  useEffect(() => {
    setSelectedHotel(value);
  }, [value]);

  // Get hotels from Redux store
  const { hotels = [], loading, error } = useSelector(state => state.enquiryList || { hotels: [], loading: false });
  const { searchLocation } = useSelector(state => state.enquiry || { searchLocation: {} });
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // Manual fetch button for testing
  const formatPrice = (price) => {
    const actualPrice = parseFloat(price) || 0;
    return actualPrice > 0 ? `$${actualPrice.toLocaleString()}` : "Price on request";
  };
  const handleManualFetch = () => {
    if (searchLocation && searchLocation.country && searchLocation.city) {
      dispatch(fetchEnquiryList({
        country: searchLocation.country,
        city: searchLocation.city
      }));
    }
  };

  // Filter hotels based on search term
  const filteredHotels = hotels ? hotels.filter((hotel) =>
    hotel.name.toLowerCase().includes(searchTerm.toLowerCase())
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

  // Handle hotel selection
  const handleHotelSelect = (hotel) => {
    if (hotel === "others") {
      setShowOthersInput(true);
      setSelectedHotel(null);
    } else {
      setSelectedHotel(hotel);
      setShowOthersInput(false);
      setSearchTerm(hotel.name);
      if (onSelect) onSelect(hotel);
    }
    setIsDropdownOpen(false);
  };

  // Handle other hotel name submission
  const handleOtherHotelSubmit = () => {
    if (otherHotelName.trim()) {
      const customHotel = { id: "custom", name: otherHotelName.trim() };
      setSelectedHotel(customHotel);
      setSearchTerm(otherHotelName.trim());
      if (onSelect) onSelect(customHotel);
      setShowOthersInput(false);
    }
  };

  // Generate star icons based on hotel rating
  const renderStarRating = (stars) => {
    if (!stars) return null;
    
    const starCount = parseInt(stars);
    if (isNaN(starCount) || starCount < 1) return null;
    
    return (
      <StarRating>
        {[...Array(starCount)].map((_, i) => (
          <StarIcon key={i} fontSize="small" sx={{ fontSize: 14 }} />
        ))}
      </StarRating>
    );
  };

  // Generate a placeholder for the hotel description
  const getDescriptionSnippet = (description) => {
    if (!description) return "No description available";
    
    // Remove HTML tags and limit to 100 characters
    const plainText = description.replace(/<[^>]*>/g, ' ').trim();
    return plainText.length > 100 ? plainText.substring(0, 100) + '...' : plainText;
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Hotels (Drop Off Location) {hotels.length > 0 && (
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
        />

        {isDropdownOpen && (
          <DropdownContainer ref={dropdownRef}>
            <List disablePadding>
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
                    <HotelInfo>
                      <Typography variant="subtitle2" fontWeight={500}>{hotel.name}</Typography>
                      {renderStarRating(hotel.hotel_star_rating)}
                      {hotel.address && (
                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12, display: 'flex', alignItems: 'center' }}>
                          <LocationOnIcon sx={{ fontSize: 14, mr: 0.5 }} />
                          {hotel.address}
                        </Typography>
                      )}
                      {hotel.description && (
                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12, lineHeight: 1.4 }}>
                          {getDescriptionSnippet(hotel.description)}
                        </Typography>
                      )}
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

                    </HotelInfo>
                    {hotel.main_image && (
                      <HotelImage>
                        <img src={hotel.main_image} alt={hotel.name} />
                      </HotelImage>
                    )}
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

        {selectedHotel && (
          <SelectedHotel>
            <SelectedLabel>Selected:</SelectedLabel> {selectedHotel.name}
            {selectedHotel.hotel_star_rating && (
              <HotelDetail 
                size="small" 
                label={`${selectedHotel.hotel_star_rating} Stars`}
                variant="outlined"
              />
            )}
          </SelectedHotel>
        )}
      </Box>
    </SearchContainer>
  );
};

export default HotelDropOffSearch;
