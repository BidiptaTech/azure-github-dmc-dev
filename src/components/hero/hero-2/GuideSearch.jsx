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
  InputLabel,
  CircularProgress,
  Chip,
  Avatar,
  IconButton
} from "@mui/material";
import WorkIcon from "@mui/icons-material/Work";
import PersonIcon from "@mui/icons-material/Person";
import BookmarkIcon from "@mui/icons-material/Bookmark";
import CloseIcon from "@mui/icons-material/Close";

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

const GuideOption = styled(ListItem)(({ theme }) => ({
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

const GuideInfo = styled(Box)({
  flex: 1,
});

const GuideMetadata = styled(Box)(({ theme }) => ({
  display: "flex",
  flexWrap: "wrap",
  gap: theme.spacing(1),
  marginBottom: theme.spacing(0.5),
}));

const GuideImage = styled(Box)(({ theme }) => ({
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

const SelectedGuidesContainer = styled(Paper)(({ theme }) => ({
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

const MetadataTag = styled(Chip)(({ theme }) => ({
  height: 22,
  fontSize: 11,
  backgroundColor: theme.palette.grey[100],
}));

const PriceChip = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.success.light,
  color: theme.palette.success.contrastText,
  fontWeight: 600,
  flexShrink: 0,
}));

const OthersInputContainer = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(1),
  display: "flex",
  gap: theme.spacing(1),
}));

const GuideSearch = ({ onSelect }) => {
  const dispatch = useDispatch();
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedGuides, setSelectedGuides] = useState([]);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherGuideName, setOtherGuideName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Get guides from Redux store
  const { guides = [], loading, error } = useSelector(state => state.enquiryList || { guides: [], loading: false });
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

  // Filter guides based on search term
  const filteredGuides = guides ? guides.filter((guide) =>
    guide.name.toLowerCase().includes(searchTerm.toLowerCase())
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

  // Handle guide selection
  const handleGuideSelect = (guide) => {
    if (guide === "others") {
      setShowOthersInput(true);
    } else {
      // Check if this guide is already selected
      const isAlreadySelected = selectedGuides.some(item => item.guide_id === guide.guide_id);
      
      if (!isAlreadySelected) {
        // Create a clean guide object to avoid React rendering issues
        let cleanGuide = {
          guide_id: guide.guide_id,
          name: guide.name,
          experience_years: guide.experience_years,
          city: guide.city,
          country: guide.country
        };
        
        // Properly handle languages array
        if (guide.languages && Array.isArray(guide.languages)) {
          cleanGuide.languages = guide.languages.map(lang => ({
            language: lang.language,
            proficiency: lang.proficiency
          }));
        }
        
        const updatedSelections = [...selectedGuides, cleanGuide];
        setSelectedGuides(updatedSelections);
        if (onSelect) onSelect(updatedSelections);
      }
    }
    setSearchTerm("");
    setIsDropdownOpen(false);
  };

  // Handle other guide name submission
  const handleOtherGuideSubmit = () => {
    if (otherGuideName.trim()) {
      const customGuide = { 
        guide_id: `custom-${Date.now()}`, 
        name: otherGuideName.trim() 
      };
      const updatedSelections = [...selectedGuides, customGuide];
      setSelectedGuides(updatedSelections);
      if (onSelect) onSelect(updatedSelections);
      setOtherGuideName("");
      setShowOthersInput(false);
    }
  };

  // Remove a selected guide
  const handleRemoveGuide = (guideId) => {
    const updatedSelections = selectedGuides.filter(
      guide => guide.guide_id !== guideId
    );
    setSelectedGuides(updatedSelections);
    if (onSelect) onSelect(updatedSelections);
  };

  // Get service type label
  const getServiceTypeLabel = (type) => {
    if (!type) return null;
    
    const serviceTypes = {
      1: "General Guide",
      2: "Specialized Guide"
    };
    
    return serviceTypes[type] || "Guide";
  };

  // Generate a placeholder for the guide description
  const getDescriptionSnippet = (description) => {
    if (!description) return "No description available";
    
    // Remove HTML tags and limit to 100 characters
    const plainText = description.replace(/<[^>]*>/g, ' ').trim();
    return plainText.length > 100 ? plainText.substring(0, 100) + '...' : plainText;
  };

  // Format languages for display
  const formatLanguages = (languages) => {
    if (!languages || !Array.isArray(languages) || languages.length === 0) {
      return null;
    }
    
    return languages.map(lang => 
      `${lang.language}${lang.proficiency ? ` (${lang.proficiency})` : ''}`
    ).join(', ');
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Tour Guides {guides.length > 0 && (
            <CountBadge label={guides.length} size="small" variant="outlined" />
          )}
        </InputLabel>
        
        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for guides..."
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
                  <Typography color="text.secondary">Loading guides...</Typography>
                </Box>
              ) : error ? (
                <Box sx={{ p: 2, color: 'error.main', textAlign: 'center', borderLeft: 3, borderColor: 'error.main', bgcolor: 'error.light', opacity: 0.05 }}>
                  <Typography>Error: {error}</Typography>
                </Box>
              ) : filteredGuides.length > 0 ? (
                filteredGuides.map((guide) => (
                  <GuideOption
                    key={guide.guide_id}
                    onClick={() => handleGuideSelect(guide)}
                  >
                    <GuideInfo>
                      <Typography variant="subtitle2" fontWeight={500}>
                        {guide.salutation && <span style={{ color: 'text.secondary', fontWeight: 'normal' }}>{guide.salutation}</span>} {guide.name}
                      </Typography>
                      <GuideMetadata>
                        {guide.experience_years && (
                          <MetadataTag
                            size="small"
                            icon={<WorkIcon fontSize="small" />}
                            label={`${guide.experience_years} years experience`}
                          />
                        )}
                        {guide.service_type && (
                          <MetadataTag
                            size="small"
                            icon={<PersonIcon fontSize="small" />}
                            label={getServiceTypeLabel(guide.service_type)}
                          />
                        )}
                        {guide.government_license_no && (
                          <MetadataTag
                            size="small"
                            icon={<BookmarkIcon fontSize="small" />}
                            label={`License: ${guide.government_license_no}`}
                          />
                        )}
                      </GuideMetadata>
                      {guide.description && (
                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12, lineHeight: 1.4 }}>
                          {getDescriptionSnippet(guide.description)}
                        </Typography>
                      )}
                      {guide.base_price && (
                        <PriceChip 
                          label={`${formatPrice(guide.base_price)}/hour`}
                          size="small"
                          sx={{ mt: 1 }}
                        />
                      )}
                    </GuideInfo>
                    {guide.image && (
                      <GuideImage>
                        <img src={guide.image} alt={guide.name} />
                      </GuideImage>
                    )}
                  </GuideOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>
                    {guides.length > 0 
                      ? "No guides match your search" 
                      : "No guides found for this location"}
                  </Typography>
                </Box>
              )}
            </List>
          </DropdownContainer>
        )}
        
        {selectedGuides.length > 0 && (
          <SelectedGuidesContainer>
            <SelectedHeader>
              <Typography variant="subtitle2" color="primary" fontWeight={500}>
                {selectedGuides.length} Guide{selectedGuides.length !== 1 ? 's' : ''} Selected
              </Typography>
            </SelectedHeader>
            <SelectedItems>
              {selectedGuides.map((guide) => (
                <SelectedItem key={guide.guide_id}>
                  <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                    <Typography variant="body2" fontWeight={500}>
                      {guide.salutation && <span style={{ color: 'text.secondary', fontWeight: 'normal' }}>{guide.salutation}</span>} {guide.name}
                    </Typography>
                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mt: 0.5 }}>
                      {guide.experience_years && (
                        <Typography variant="caption" color="text.secondary" sx={{ display: 'flex', alignItems: 'center' }}>
                          <WorkIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                          {guide.experience_years} years exp.
                        </Typography>
                      )}
                      {guide.service_type && (
                        <MetadataTag
                          size="small"
                          label={getServiceTypeLabel(guide.service_type)}
                          sx={{ ml: 0.5 }}
                        />
                      )}
                    </Box>
                  </Box>
                  <IconButton 
                    size="small"
                    onClick={() => handleRemoveGuide(guide.guide_id)}
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
          </SelectedGuidesContainer>
        )}
      </Box>
    </SearchContainer>
  );
};

export default GuideSearch; 