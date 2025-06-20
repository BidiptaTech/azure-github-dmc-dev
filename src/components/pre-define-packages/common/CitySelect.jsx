import React, { useState, useEffect, useRef } from 'react';
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
import { useSelector, useDispatch } from 'react-redux';
import { fetchCitiesByCountry } from '@/slice/common/citiesSlice';
import CloseIcon from '@mui/icons-material/Close';
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';
import LocationCityIcon from '@mui/icons-material/LocationCity';

const CitySelect = ({ onChange, value, selectedCountry, label = "City" }) => {
  const dispatch = useDispatch();
  const [searchValue, setSearchValue] = useState("");
  const [selectedCity, setSelectedCity] = useState(value || null);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [cityList, setCityList] = useState([]);
  
  const cityListRef = useRef(null);
  const inputRef = useRef(null);
  
  // Get cities from Redux state
  const { cities, loading: citiesLoading, error: citiesError } = useSelector((state) => state.cities || { cities: [], loading: false, error: null });

  // Update the selectedCity when the value prop changes
  useEffect(() => {
    if (value) {
      setSelectedCity(value);
      setSearchValue(value.name || "");
    } else if (!value && selectedCity) {
      setSelectedCity(null);
      setSearchValue("");
    }
  }, [value]);

  // Fetch cities when a country is selected
  useEffect(() => {
    if (selectedCountry?.name) {
      setLoading(true);
      setCityList([]);
      setSuggestions([]);
      setSelectedCity(null);
      setSearchValue("");
      
      dispatch(fetchCitiesByCountry(selectedCountry.name))
        .then((response) => {
          if (response.payload && Array.isArray(response.payload)) {
            const formattedCities = response.payload.map((city, index) => ({
              name: city,
              code: `${selectedCountry.code}-${city.replace(/\s+/g, '').substring(0, 3).toUpperCase()}`,
              key: `city-${index}`
            }));
            setCityList(formattedCities);
            setSuggestions(formattedCities);
          }
        })
        .finally(() => setLoading(false));
    } else {
      setCityList([]);
      setSuggestions([]);
    }
  }, [dispatch, selectedCountry]);

  // Filter cities based on search input
  useEffect(() => {
    if (selectedCountry && searchValue && !selectedCity) {
      const filtered = cityList.filter((city) =>
        city.name.toLowerCase().includes(searchValue.toLowerCase())
      );
      setSuggestions(filtered);
      setIsDropdownOpen(filtered.length > 0);
    } else if (selectedCountry && !selectedCity) {
      setSuggestions(cityList);
      setIsDropdownOpen(cityList.length > 0);
    } else {
      setIsDropdownOpen(false);
    }
  }, [searchValue, selectedCity, selectedCountry, cityList]);

  // Ensure highlighted item is visible in scroll
  useEffect(() => {
    if (cityListRef.current && highlightedIndex !== -1 && isDropdownOpen) {
      const activeItem = cityListRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex, isDropdownOpen]);

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (cityListRef.current && 
          !cityListRef.current.contains(event.target) && 
          !inputRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const handleCitySelect = (city, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    setSearchValue(city.name);
    setSelectedCity(city);
    setIsDropdownOpen(false);
    
    if (onChange) {
      onChange(city);
    }
    
    // Remove focus
    if (inputRef.current) {
      inputRef.current.blur();
    }
  };

  const handleInputChange = (e) => {
    // If a city is already selected, prevent typing
    if (selectedCity) return;
    
    setSearchValue(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleInputFocus = () => {
    if (selectedCountry && !selectedCity && cityList.length > 0) {
      setIsDropdownOpen(true);
    }
  };

  const handleClearSelection = (e) => {
    e.stopPropagation();
    setSelectedCity(null);
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
    // If a city is already selected, only allow Escape or Backspace to clear
    if (selectedCity) {
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
      handleCitySelect(suggestions[highlightedIndex], e);
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
          placeholder={selectedCountry ? "Search or select a city" : "Select a country first"}
          fullWidth
          variant="outlined"
          size="small"
          disabled={!selectedCountry || loading || citiesLoading}
          InputProps={{
            startAdornment: (
              <LocationCityIcon sx={{ color: 'text.secondary', ml: 0.5, mr: 0.5 }} />
            ),
            endAdornment: selectedCity ? (
              <IconButton 
                size="small" 
                sx={{ mr: -0.5 }} 
                onClick={handleClearSelection}
                aria-label="clear selection"
                disabled={!selectedCountry}
              >
                <CloseIcon fontSize="small" />
              </IconButton>
            ) : (loading || citiesLoading ? (
              <CircularProgress size={20} sx={{ mr: 1 }} />
            ) : (
              <KeyboardArrowDownIcon sx={{ color: 'text.secondary', mr: 0.5 }} />
            )),
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
            ref={cityListRef}
            dense 
            disablePadding
            sx={{ py: 0 }}
          >
            {loading || citiesLoading ? (
              <ListItem>
                <Box sx={{ display: 'flex', justifyContent: 'center', width: '100%', p: 2 }}>
                  <CircularProgress size={24} />
                </Box>
              </ListItem>
            ) : suggestions.length > 0 ? (
              suggestions.map((city, index) => (
                <ListItem 
                  key={city.key || city.code}
                  button
                  selected={index === highlightedIndex}
                  onClick={(e) => handleCitySelect(city, e)}
                  dense
                  sx={{
                    py: 1,
                    backgroundColor: index === highlightedIndex ? 'action.hover' : 'transparent',
                  }}
                >
                  <ListItemText 
                    primary={city.name} 
                    primaryTypographyProps={{
                      variant: 'body2',
                    }}
                  />
                </ListItem>
              ))
            ) : (
              <ListItem>
                <ListItemText 
                  primary={selectedCountry ? "No cities found" : "Select a country first"} 
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

      {citiesError && (
        <Typography color="error" variant="caption">
          Error loading cities: {citiesError}
        </Typography>
      )}
    </Box>
  );
};

export default CitySelect; 