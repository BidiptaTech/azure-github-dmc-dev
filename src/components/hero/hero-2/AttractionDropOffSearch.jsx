import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import { fetchEnquiryList } from "../../../slice/common/enquiryListSlice";
import { styled } from "@mui/material/styles";
import { 
  Box,
  TextField,
  Paper,
  Typography,
  CircularProgress,
  Button,
  IconButton,
  Divider,
  InputAdornment,
  List,
  ListItem,
  ListItemText,
  Chip
} from "@mui/material";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import CloseIcon from "@mui/icons-material/Close";
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

const AttractionOption = styled(ListItem)(({ theme }) => ({
  cursor: "pointer",
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

const OthersOption = styled(ListItem)(({ theme }) => ({
  display: "flex",
  alignItems: "center",
  color: theme.palette.primary.main,
  fontWeight: 500,
  cursor: "pointer",
  borderTop: `1px dashed ${theme.palette.divider}`,
  fontSize: 13,
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
}));

const OthersInputContainer = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(1),
  display: "flex",
  gap: theme.spacing(1),
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

const AttractionDropOffSearch = ({ onSelect }) => {
  const dispatch = useDispatch();
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedAttraction, setSelectedAttraction] = useState(null);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherAttractionName, setOtherAttractionName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Get attractions from Redux store
  const { attractions = [], loading, error } = useSelector(state => state.enquiryList || { attractions: [], loading: false });
  const { searchLocation } = useSelector(state => state.enquiry || { searchLocation: {} });

  // Filter attractions based on search term
  const filteredAttractions = attractions ? attractions.filter((attraction) =>
    attraction && attraction.name && attraction.name.toLowerCase().includes(searchTerm.toLowerCase())
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

  // Handle attraction selection
  const handleAttractionSelect = (attraction) => {
    if (attraction === "others") {
      setShowOthersInput(true);
      setSelectedAttraction(null);
    } else {
      setSelectedAttraction(attraction);
      setShowOthersInput(false);
      setSearchTerm(attraction.name);
      if (onSelect) onSelect(attraction);
    }
    setIsDropdownOpen(false);
  };

  // Handle other attraction name submission
  const handleOtherAttractionSubmit = () => {
    if (otherAttractionName.trim()) {
      const customAttraction = { 
        attraction_id: `custom-${Date.now()}`, 
        name: otherAttractionName.trim() 
      };
      setSelectedAttraction(customAttraction);
      setSearchTerm(otherAttractionName.trim());
      if (onSelect) onSelect(customAttraction);
      setShowOthersInput(false);
    }
  };

  // Remove selected attraction
  const handleRemoveAttraction = () => {
    setSelectedAttraction(null);
    setSearchTerm("");
    if (onSelect) onSelect(null);
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
      <Box>
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
      </Box>

      {isDropdownOpen && (
        <DropdownContainer ref={dropdownRef}>
          <List disablePadding>
            {loading ? (
              <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', p: 2 }}>
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
                  key={attraction.attraction_id || `attraction-${Math.random()}`}
                  onClick={() => handleAttractionSelect(attraction)}
                  divider
                >
                  <AttractionInfo>
                    <Typography variant="subtitle1" fontWeight={500}>{attraction.name}</Typography>
                    <AttractionMetadata>
                      {attraction.location && (
                        <Box sx={{ display: 'flex', alignItems: 'center', color: 'text.secondary', fontSize: 12 }}>
                          <LocationOnIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                          {attraction.location}
                        </Box>
                      )}
                      {formatHours(attraction.open_time, attraction.close_time) && (
                        <Box sx={{ display: 'flex', alignItems: 'center', color: 'text.secondary', fontSize: 12 }}>
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
            <OthersOption onClick={() => handleAttractionSelect("others")}>
              <AddIcon fontSize="small" sx={{ mr: 1 }} />
              <Typography variant="body2">Can't find your attraction? Add it manually</Typography>
            </OthersOption>
          </List>
        </DropdownContainer>
      )}

      {showOthersInput && (
        <OthersInputContainer>
          <TextField
            fullWidth
            size="small"
            placeholder="Enter attraction name"
            value={otherAttractionName}
            onChange={(e) => setOtherAttractionName(e.target.value)}
          />
          <Button 
            variant="contained" 
            color="primary" 
            onClick={handleOtherAttractionSubmit}
          >
            Add Attraction
          </Button>
        </OthersInputContainer>
      )}

      {selectedAttraction && (
        <SelectedContainer>
          <SelectedHeader>
            <Typography variant="subtitle2" color="primary" fontWeight={500}>
              Selected Attraction
            </Typography>
          </SelectedHeader>
          <SelectedItems>
            <SelectedItem>
              <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                <Typography variant="body2" fontWeight={500}>{selectedAttraction.name}</Typography>
                {selectedAttraction.location && (
                  <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary', mt: 0.5 }}>
                    <LocationOnIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                    {selectedAttraction.location}
                  </Box>
                )}
              </Box>
              <IconButton 
                size="small"
                onClick={handleRemoveAttraction}
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
          </SelectedItems>
        </SelectedContainer>
      )}
    </SearchContainer>
  );
};

export default AttractionDropOffSearch; 