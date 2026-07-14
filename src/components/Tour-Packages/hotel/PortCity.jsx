import React, { useState, useEffect, useCallback, useMemo } from "react";
import { useSelector } from "react-redux";
import {
  Autocomplete,
  TextField,
  Box,
  Typography,
  InputAdornment,
  alpha,
} from '@mui/material';
import { LocationOn } from '@mui/icons-material';

const PortCity = ({ onLocationSelect, hasError, setError, disabled = false }) => {
  const cityData = useSelector((state) => state.city.city);
  const [selectedItem, setSelectedItem] = useState(null);

  // Transform city data to match the expected format
  const options = useMemo(() => {
    if (!cityData || !Array.isArray(cityData)) return [];
    
    return cityData.map((city, index) => ({
      id: index + 1,
      name: city.split(",")[0],
      address: city,
      country: city.split(",").length > 1 ? city.split(",")[1].trim() : "",
    }));
  }, [cityData]);

  // Handle selection
  const handleSelection = useCallback((event, newValue) => {
    console.log("PortCity - Selection changed:", newValue);
    setSelectedItem(newValue);
    
    if (newValue) {
      if (setError) setError(false);
      if (onLocationSelect) {
        onLocationSelect(newValue);
      }
    } else {
      if (onLocationSelect) {
        onLocationSelect(null);
      }
    }
  }, [onLocationSelect, setError]);

  // Handle input change
  const handleInputChange = useCallback((event, newInputValue) => {
    // You can add any input change logic here if needed
    console.log("PortCity - Input changed:", newInputValue);
  }, []);

  return (
    <Box sx={{ width: '100%' }}>
      <Autocomplete
        value={selectedItem}
        onChange={handleSelection}
        onInputChange={handleInputChange}
        options={options}
        getOptionLabel={(option) => option?.name || ''}
        isOptionEqualToValue={(option, value) => option?.id === value?.id}
        disabled={disabled}
        noOptionsText="No cities available"
        renderInput={(params) => (
          <TextField
            {...params}
            placeholder="Select a city"
            variant="outlined"
            size="small"
            error={hasError}
            helperText={hasError ? "Please select a city" : ""}
            InputProps={{
              ...params.InputProps,
              startAdornment: (
                <InputAdornment position="start">
                  <LocationOn 
                    sx={{ 
                      color: disabled ? 'action.disabled' : 'primary.main',
                      fontSize: 20 
                    }} 
                  />
                </InputAdornment>
              ),
            }}
            sx={{
              '& .MuiOutlinedInput-root': {
                '& fieldset': {
                  borderColor: hasError ? 'error.main' : 'rgba(0, 0, 0, 0.23)',
                },
                '&:hover fieldset': {
                  borderColor: hasError ? 'error.main' : 'primary.main',
                },
                '&.Mui-focused fieldset': {
                  borderColor: hasError ? 'error.main' : 'primary.main',
                },
                '&.Mui-disabled': {
                  backgroundColor: alpha('#000', 0.04),
                },
              },
            }}
          />
        )}
        renderOption={(props, option) => (
          <Box component="li" {...props}>
            <Box sx={{ display: 'flex', alignItems: 'center', width: '100%' }}>
              <LocationOn sx={{ mr: 1, color: 'primary.main', fontSize: 18 }} />
              <Box>
                <Typography variant="body2" sx={{ fontWeight: 500 }}>
                  {option.name}
                </Typography>
                {option.country && (
                  <Typography variant="caption" color="text.secondary">
                    {option.country}
                  </Typography>
                )}
              </Box>
            </Box>
          </Box>
        )}
        sx={{
          '& .MuiAutocomplete-inputRoot': {
            '&.Mui-disabled': {
              backgroundColor: alpha('#000', 0.04),
            },
          },
        }}
      />
    </Box>
  );
};

export default PortCity;
