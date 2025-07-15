import React, { useState, useEffect, useRef, useMemo } from 'react';
import { 
  Box,
  Typography,
  TextField,
  Paper,
  List,
  ListItem,
  ListItemText,
  CircularProgress,
  IconButton,
} from '@mui/material';
import { useSelector } from 'react-redux';
import CloseIcon from '@mui/icons-material/Close';
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';
import SearchIcon from '@mui/icons-material/Search';

const CountrySelect = ({ onChange, value, label = "Country" }) => {
  const [searchValue, setSearchValue] = useState("");
  const [selectedCountry, setSelectedCountry] = useState(value || null);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  
  const countryListRef = useRef(null);
  const inputRef = useRef(null);
  
  // Get user_country from Redux state
  const user_country = useSelector((state) => state.auth.user_country);
  
  // Default countries if user_country isn't available
  const defaultCountries = [
    { name: "India", code: "IN" },
    { name: "Singapore", code: "SG" },
    { name: "Malaysia", code: "MY" },
    { name: "Thailand", code: "TH" },
    { name: "Indonesia", code: "ID" }
  ];

  // Process available countries from user_country
  const availableCountries = useMemo(() => {
    return user_country && typeof user_country === 'string'
      ? user_country.split(',').map((country, index) => ({ 
          name: country.trim(),
          code: country.trim().substring(0, 2).toUpperCase(), 
          key: `country-${index}`
        }))
      : defaultCountries;
  }, [user_country]);

  // Update the selected country when the value prop changes
  useEffect(() => {
    if (value && (!selectedCountry || value.code !== selectedCountry.code)) {
      setSelectedCountry(value);
      setSearchValue(value.name || "");
    } else if (!value && selectedCountry) {
      setSelectedCountry(null);
      setSearchValue("");
    }
  }, [value, selectedCountry]);

  // Filter and suggest countries based on search input
  useEffect(() => {
    if (searchValue && !selectedCountry) {
      const filtered = availableCountries.filter((country) =>
        country.name.toLowerCase().includes(searchValue.toLowerCase())
      );
      setSuggestions(filtered);
      setIsDropdownOpen(true);
    } else if (!selectedCountry) {
      setSuggestions(availableCountries.slice(0, 5));
    } else {
      setIsDropdownOpen(false);
    }
  }, [searchValue, selectedCountry, availableCountries]);

  // Ensure highlighted item is visible in scroll
  useEffect(() => {
    if (countryListRef.current && highlightedIndex !== -1 && isDropdownOpen) {
      const activeItem = countryListRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex, isDropdownOpen]);

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (countryListRef.current && 
          !countryListRef.current.contains(event.target) && 
          !inputRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const handleCountrySelect = (country, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    setSearchValue(country.name);
    setSelectedCountry(country);
    setIsDropdownOpen(false);
    
    if (onChange) {
      onChange(country);
    }
    
    // Remove focus
    if (inputRef.current) {
      inputRef.current.blur();
    }
  };

  const handleInputChange = (e) => {
    // If a country is already selected, prevent typing
    if (selectedCountry) return;
    
    setSearchValue(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleInputFocus = () => {
    if (!selectedCountry) {
      setIsDropdownOpen(true);
    }
  };

  const handleClearSelection = (e) => {
    e.stopPropagation();
    setSelectedCountry(null);
    setSearchValue("");
    setIsDropdownOpen(true);
    
    if (onChange) {
      onChange(null);
    }
    
    // Focus the input
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  const handleKeyDown = (e) => {
    // If a country is already selected, only allow Escape or Backspace to clear
    if (selectedCountry) {
      if (e.key === "Escape" || e.key === "Backspace") {
        handleClearSelection(e);
      }
      return;
    }
    
    if (!suggestions.length) return;

    if (e.key === "ArrowDown") {
      setHighlightedIndex((prev) =>
        prev < suggestions.length - 1 ? prev + 1 : 0
      );
    } else if (e.key === "ArrowUp") {
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : suggestions.length - 1
      );
    } else if (e.key === "Enter" && highlightedIndex !== -1) {
      e.preventDefault();
      handleCountrySelect(suggestions[highlightedIndex], e);
    }
  };

  return (
    <Box sx={{ position: 'relative', minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
      
      <Box 
        sx={{ 
          position: 'relative', 
          display: 'flex',
          border: '1px solid #ddd',
          borderRadius: '4px',
          '&:hover': {
            borderColor: '#aaa',
          },
          '&:focus-within': {
            borderColor: '#1976d2',
            boxShadow: '0 0 0 2px rgba(25, 118, 210, 0.2)',
          },
        }}
      >
        <TextField
          inputRef={inputRef}
          value={searchValue}
          onChange={handleInputChange}
          onFocus={handleInputFocus}
          onKeyDown={handleKeyDown}
          placeholder="Search or select a country"
          fullWidth
          variant="outlined"
          size="small"
          InputProps={{
            startAdornment: (
              <SearchIcon sx={{ color: 'text.secondary', ml: 0.5, mr: 0.5 }} />
            ),
            endAdornment: selectedCountry ? (
              <IconButton 
                size="small" 
                sx={{ mr: -0.5 }} 
                onClick={handleClearSelection}
                aria-label="clear selection"
              >
                <CloseIcon fontSize="small" />
              </IconButton>
            ) : (
              <KeyboardArrowDownIcon sx={{ color: 'text.secondary', mr: 0.5 }} />
            ),
            sx: { 
              pr: 1, 
              '& fieldset': { border: 'none' }
            }
          }}
        />
      </Box>
      
      {/* Dropdown List */}
      {isDropdownOpen && (
        <Paper 
          elevation={3} 
          sx={{
            position: 'absolute',
            top: '100%',
            left: 0,
            right: 0,
            mt: 0.5,
            maxHeight: 250,
            overflow: 'auto',
            zIndex: 1000,
          }}
        >
          <List 
            ref={countryListRef}
            dense 
            disablePadding
            sx={{ py: 0 }}
          >
            {loading ? (
              <ListItem>
                <Box sx={{ display: 'flex', justifyContent: 'center', width: '100%', p: 2 }}>
                  <CircularProgress size={24} />
                </Box>
              </ListItem>
            ) : suggestions.length > 0 ? (
              suggestions.map((country, index) => (
                <ListItem 
                  key={country.key || country.code}
                  button
                  selected={index === highlightedIndex}
                  onClick={(e) => handleCountrySelect(country, e)}
                  dense
                  sx={{
                    py: 1,
                    backgroundColor: index === highlightedIndex ? 'action.hover' : 'transparent',
                  }}
                >
                  <ListItemText 
                    primary={country.name} 
                    primaryTypographyProps={{
                      variant: 'body2',
                    }}
                  />
                </ListItem>
              ))
            ) : (
              <ListItem>
                <ListItemText 
                  primary="No countries found" 
                  primaryTypographyProps={{
                    variant: 'body2',
                    sx: { color: 'text.secondary', fontStyle: 'italic' }
                  }}
                />
              </ListItem>
            )}
          </List>
        </Paper>
      )}
    </Box>
  );
};

export default CountrySelect; 