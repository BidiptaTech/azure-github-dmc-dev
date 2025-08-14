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
  Chip,
  InputLabel,
  CircularProgress,
  Avatar
} from "@mui/material";
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

const GuideImage = styled(Avatar)(({ theme }) => ({
  width: 50,
  height: 50,
  flexShrink: 0,
}));

const SelectedGuidesContainer = styled(Box)(({ theme }) => ({
  display: "flex",
  flexWrap: "wrap",
  gap: theme.spacing(1),
  marginTop: theme.spacing(1),
}));

const GuideChip = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
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
}));

const PreferredGuidesSearch = ({ onSelect }) => {
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
  const PriceHide = useSelector((state) => state.auth.PriceHide);
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
      // Check if guide is already selected
      if (
        !selectedGuides.some((selectedGuide) => selectedGuide.guide_id === guide.guide_id)
      ) {
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
        
        const updatedGuides = [...selectedGuides, cleanGuide];
        setSelectedGuides(updatedGuides);
        if (onSelect) onSelect(updatedGuides);
      }
    }
    setSearchTerm("");
    setIsDropdownOpen(false);
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



  // Remove a guide from selection
  const handleRemoveGuide = (guideId) => {
    const updatedGuides = selectedGuides.filter(
      (guide) => guide.guide_id !== guideId
    );
    setSelectedGuides(updatedGuides);
    if (onSelect) onSelect(updatedGuides);
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Preferred Guides {guides.length > 0 && (
            <CountBadge label={guides.length} size="small" variant="outlined" />
          )}
        </InputLabel>
        
        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for preferred guides..."
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
                      <Typography variant="subtitle2" fontWeight={500}>{guide.name}</Typography>
                      <Typography variant="body2" color="text.secondary">{guide.guide_gender}</Typography>
                      <Typography variant="body2" color="text.secondary">Age: {guide.guide_age}</Typography>

                      {guide.specialty && (
                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12 }}>
                          Specialty: {guide.specialty}
                        </Typography>
                      )}
                      {guide.languages && (
                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12 }}>
                          Languages: {formatLanguages(guide.languages)}
                        </Typography>
                      )}
                      {PriceHide === "0" ? (
                      guide.base_price && (
                        <PriceChip 
                          label={`${formatPrice(guide.base_price)}/hour`}
                          size="small"
                          sx={{ mt: 1 }}
                        />
                      )):(
                        <div className="text-12 text-dark-1 fw-500">
                          Price available on request
                        </div>
                      )}
                    </GuideInfo>
                    {guide.image && (
                      <GuideImage src={guide.image} alt={guide.name} />
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
            {selectedGuides.map((guide) => (
              <GuideChip
                key={guide.guide_id}
                label={guide.name}
                onDelete={() => handleRemoveGuide(guide.guide_id)}
                deleteIcon={<CloseIcon fontSize="small" />}
              />
            ))}
          </SelectedGuidesContainer>
        )}
      </Box>
    </SearchContainer>
  );
};

export default PreferredGuidesSearch;
