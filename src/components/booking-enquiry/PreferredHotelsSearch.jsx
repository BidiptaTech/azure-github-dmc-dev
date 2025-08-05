import React, { useState, useEffect, useRef } from "react";
import { styled } from "@mui/material/styles";
import {
  Box,
  TextField,
  Typography,
  Paper,
  List,
  ListItem,
  Button,
  Chip,
  InputLabel
} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import CloseIcon from "@mui/icons-material/Close";

// Styled components
const SearchContainer = styled(Box)(({ theme }) => ({
  position: "relative",
  marginBottom: theme.spacing(2),
}));

const DropdownContainer = styled(Paper)(({ theme }) => ({
  position: "absolute",
  width: "100%",
  maxHeight: 250,
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
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
  },
}));

const OthersOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  display: "flex",
  alignItems: "center",
  color: theme.palette.primary.main,
  fontWeight: 500,
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
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
  marginTop: theme.spacing(1),
}));

const HotelChip = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  "& .MuiChip-deleteIcon:hover": {
    color: theme.palette.error.main,
  },
}));

const PreferredHotelsSearch = ({ onSelect }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedHotels, setSelectedHotels] = useState([]);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherHotelName, setOtherHotelName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Sample hotel data - this would typically come from an API
  const hotels = [
    { hotel_unique_id: 1, name: "Hilton Hotel" },
    { hotel_unique_id: 2, name: "Marriott" },
    { hotel_unique_id: 3, name: "Hyatt" },
    { hotel_unique_id: 4, name: "Sheraton" },
    { hotel_unique_id: 5, name: "InterContinental" },
    { hotel_unique_id: 6, name: "Four Seasons" },
    { hotel_unique_id: 7, name: "Ritz-Carlton" },
    { hotel_unique_id: 8, name: "Radisson" },
  ];

  // Filter hotels based on search term
  const filteredHotels = hotels.filter((hotel) =>
    hotel.name.toLowerCase().includes(searchTerm.toLowerCase())
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

  // Handle hotel selection
  const handleHotelSelect = (hotel) => {
    if (hotel === "others") {
      setShowOthersInput(true);
    } else {
      // Check if hotel is already selected
      if (!selectedHotels.some(selectedHotel => selectedHotel.hotel_unique_id === hotel.hotel_unique_id)) {
        const updatedHotels = [...selectedHotels, hotel];
        setSelectedHotels(updatedHotels);
        if (onSelect) onSelect(updatedHotels);
      }
    }
    setSearchTerm("");
    setIsDropdownOpen(false);
  };

  // Handle other hotel name submission
  const handleOtherHotelSubmit = () => {
    if (otherHotelName.trim()) {
      const customHotel = { hotel_unique_id: `custom-${Date.now()}`, name: otherHotelName.trim() };
      const updatedHotels = [...selectedHotels, customHotel];
      setSelectedHotels(updatedHotels);
      if (onSelect) onSelect(updatedHotels);
      setOtherHotelName("");
      setShowOthersInput(false);
    }
  };

  // Remove a hotel from selection
  const handleRemoveHotel = (hotelId) => {
    const updatedHotels = selectedHotels.filter(hotel => hotel.hotel_unique_id !== hotelId);
    setSelectedHotels(updatedHotels);
    if (onSelect) onSelect(updatedHotels);
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Preferred Hotels
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
              {filteredHotels.length > 0 ? (
                filteredHotels.map((hotel) => (
                  <HotelOption
                    key={hotel.hotel_unique_id}
                    onClick={() => handleHotelSelect(hotel)}
                  >
                    <Typography variant="body1">{hotel.name}</Typography>
                  </HotelOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>No hotels found</Typography>
                </Box>
              )}
              <OthersOption onClick={() => handleHotelSelect("others")}>
                <AddIcon fontSize="small" sx={{ mr: 1 }} />
                <Typography variant="body2">Add Other Hotel</Typography>
              </OthersOption>
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
              autoFocus
            />
            <Button 
              variant="contained" 
              color="primary" 
              onClick={handleOtherHotelSubmit}
            >
              Add
            </Button>
          </OthersInputContainer>
        )}
      </Box>
      
      {selectedHotels.length > 0 && (
        <SelectedHotelsContainer>
          {selectedHotels.map(hotel => (
            <HotelChip
              key={hotel.hotel_unique_id}
              label={hotel.name}
              onDelete={() => handleRemoveHotel(hotel.hotel_unique_id)}
              deleteIcon={<CloseIcon fontSize="small" />}
            />
          ))}
        </SelectedHotelsContainer>
      )}
    </SearchContainer>
  );
};

export default PreferredHotelsSearch;