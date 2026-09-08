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
  Chip,
  InputLabel,
  CircularProgress,
  IconButton
} from "@mui/material";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import CloseIcon from "@mui/icons-material/Close";

// Styled components (reuse from AttractionSearch)
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
}));
const PackageOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  flexDirection: "column",
  alignItems: "flex-start",
  gap: theme.spacing(1),
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
  },
}));
const IncludedAttractions = styled(Box)(({ theme }) => ({
  display: "flex",
  flexWrap: "wrap",
  gap: theme.spacing(1),
  marginTop: theme.spacing(1),
}));
const AttractionChip = styled(Chip)(({ theme }) => ({
  fontSize: 12,
  backgroundColor: theme.palette.grey[100],
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
  overflowX: "auto",
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

const PackageAttractionSearch = ({ onSelect, value = [] }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedPackages, setSelectedPackages] = useState(value);
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Update internal state when value prop changes
  useEffect(() => {
    setSelectedPackages(value);
  }, [value]);

  // Get packaged attractions from Redux store
  const { packaged_attractions = [], loading, error } = useSelector(state => state.enquiryList || { packaged_attractions: [], loading: false });
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // Filter packages based on search term and price
  const filteredPackages = packaged_attractions.filter(pkg => {
    // Filter by search term
    const matchesSearch = pkg.name.toLowerCase().includes(searchTerm.toLowerCase());
    
    // Filter by price - only show packages with base_price > 0
    const hasValidPrice = parseFloat(pkg.base_price) > 0;
    
    return matchesSearch && hasValidPrice;
  });

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

  // Handle package selection
  const handlePackageSelect = (pkg) => {
    // Check if already selected
    const isAlreadySelected = selectedPackages.some(item => item.id === pkg.id);
    if (!isAlreadySelected) {
      const updatedSelections = [...selectedPackages, pkg];
      setSelectedPackages(updatedSelections);
      if (onSelect) onSelect(updatedSelections);
    }
    setSearchTerm("");
    setIsDropdownOpen(false);
  };

  // Remove a selected package
  const handleRemovePackage = (packageId) => {
    const updatedSelections = selectedPackages.filter(pkg => pkg.id !== packageId);
    setSelectedPackages(updatedSelections);
    if (onSelect) onSelect(updatedSelections);
  };

  // Helper function to format price
  const formatPrice = (price) => {
    const actualPrice = parseFloat(price) || 0;
    return actualPrice > 0 ? `SGD ${actualPrice.toLocaleString()}` : "Price on request";
  };

  // Helper to strip HTML from description
  const getDescriptionSnippet = (description) => {
    if (!description) return "No description available";
    const plainText = description.replace(/<[^>]*>/g, ' ').trim();
    return plainText.length > 100 ? plainText.substring(0, 100) + '...' : plainText;
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Packaged Attractions {packaged_attractions.length > 0 && (
            <CountBadge label={packaged_attractions.length} size="small" variant="outlined" />
          )}
        </InputLabel>
        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for packaged attractions..."
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
                  <Typography color="text.secondary">Loading packages...</Typography>
                </Box>
              ) : error ? (
                <Box sx={{ p: 2, color: 'error.main', textAlign: 'center', borderLeft: 3, borderColor: 'error.main', bgcolor: 'error.light', opacity: 0.05 }}>
                  <Typography>Error: {error}</Typography>
                </Box>
              ) : filteredPackages.length > 0 ? (
                filteredPackages.map((pkg) => (
                  <PackageOption key={pkg.id} onClick={() => handlePackageSelect(pkg)}>
                    <Box sx={{ display: 'flex', width: '100%', alignItems: 'center', gap: 2 }}>
                      <Box sx={{ flex: 1 }}>
                        <Typography variant="subtitle2" fontWeight={500}>{pkg.name}</Typography>
                        <Typography variant="body2" color="text.secondary" sx={{ fontSize: 12, lineHeight: 1.4 }}>
                          {getDescriptionSnippet(pkg.description)}
                        </Typography>
                        <Box sx={{ display: 'flex', gap: 2, alignItems: 'center', mt: 1 }}>
                          {PriceHide === "0" ? (
                          pkg.base_price && (
                            <PriceChip label={`Adult: ${formatPrice(pkg.base_price)}`} size="small" />
                            )):(
                            <div className="text-12 text-dark-1 fw-500">
                              Price available on request
                            </div>
                            )}
                          
                          {PriceHide === "0" ? (
                          pkg.child_price && (
                            <PriceChip label={`Child: ${formatPrice(pkg.child_price)}`} size="small" />
                            )):(
                            <div className="text-12 text-dark-1 fw-500">
                              Price available on request
                            </div>
                            )}
                        </Box>
                        <IncludedAttractions>
                          {pkg.attractions && pkg.attractions.length > 0 && (
                            pkg.attractions.map((att) => (
                              <AttractionChip
                                key={att.attraction_id}
                                label={att.name}
                                icon={<LocationOnIcon fontSize="small" />}
                              />
                            ))
                          )}
                        </IncludedAttractions>
                      </Box>
                      {pkg.master_image && (
                        <AttractionImage>
                          <img src={pkg.master_image} alt={pkg.name} />
                        </AttractionImage>
                      )}
                    </Box>
                  </PackageOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>
                    {packaged_attractions.length > 0
                      ? "No packages match your search"
                      : "No packaged attractions found for this location"}
                  </Typography>
                </Box>
              )}
            </List>
          </DropdownContainer>
        )}
        {selectedPackages.length > 0 && (
          <SelectedContainer>
            <SelectedHeader>
              <Typography variant="subtitle2" color="primary" fontWeight={500}>
                {selectedPackages.length} Package{selectedPackages.length !== 1 ? 's' : ''} Selected
              </Typography>
            </SelectedHeader>
            <SelectedItems>
              {selectedPackages.map((pkg) => (
                <SelectedItem key={pkg.id}>
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.5, flex: 1 }}>
                      <Typography variant="body2" fontWeight={500}>{pkg.name}</Typography>
                      <IncludedAttractions>
                        {pkg.attractions && pkg.attractions.length > 0 && (
                          pkg.attractions.map((att) => (
                            <AttractionChip
                              key={att.attraction_id}
                              label={att.name}
                              icon={<LocationOnIcon fontSize="small" />}
                            />
                          ))
                        )}
                      </IncludedAttractions>
                    </Box>
                    {/* {pkg.master_image && (
                      <AttractionImage>
                        <img src={pkg.master_image} alt={pkg.name} />
                      </AttractionImage>
                    )} */}
                  </Box>
                  <IconButton
                    size="small"
                    onClick={() => handleRemovePackage(pkg.id)}
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

export default PackageAttractionSearch; 