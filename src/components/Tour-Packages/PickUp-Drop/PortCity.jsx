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
  const handleSelect = useCallback((event, newValue) => {
    if (!newValue || disabled) {
      setSelectedItem(null);
      onLocationSelect(null);
      if (setError) setError(false);
      return;
    }

    setSelectedItem(newValue);
    onLocationSelect(newValue);
    if (setError) setError(false);
  }, [disabled, onLocationSelect, setError]);

  // Render option component
  const renderOption = useCallback((props, option) => {
    return (
      <Box 
        component="li" 
        {...props}
        sx={{
          '&:hover': {
            bgcolor: alpha('#000', 0.05),
          },
          borderBottom: `1px solid ${alpha('#000', 0.05)}`,
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', gap: 1, pl: 2 }}>
          <LocationOn sx={{ fontSize: '1rem', color: '#1976d2' }} />
          <Box>
            <Typography noWrap sx={{ fontWeight: 500 }}>
              {option.name}
            </Typography>
            <Typography noWrap variant="body2" sx={{ color: 'text.secondary', fontSize: '0.75rem' }}>
              {option.address}
            </Typography>
          </Box>
        </Box>
      </Box>
    );
  }, []);

  // Get option label
  const getOptionLabel = useCallback((option) => {
    return option?.name || '';
  }, []);

  // Check if option equals value
  const isOptionEqualToValue = useCallback((option, value) => {
    if (!option || !value) return false;
    return option.id === value.id;
  }, []);

  // Get unique key for options
  const getOptionKey = useCallback((option) => `city-${option.id}`, []);

  return (
    <Box sx={{ width: '100%', position: 'relative' }}>
      <Autocomplete
        value={selectedItem}
        onChange={handleSelect}
        options={options}
        getOptionLabel={getOptionLabel}
        getOptionKey={getOptionKey}
        isOptionEqualToValue={isOptionEqualToValue}
        noOptionsText="No cities available"
        disabled={disabled || options.length === 0}
        renderOption={renderOption}
        sx={{
          '& .MuiInputBase-input': {
            fontSize: '0.75rem',
            height: '16px',
          },
        }}
        renderInput={(params) => (
          <TextField
            {...params}
            fullWidth
            placeholder="Search for a city"
            disabled={disabled}
            error={hasError}
            helperText={hasError ? "*Select city from dropdown" : ""}
            InputProps={{
              ...params.InputProps,
              startAdornment: (
                <InputAdornment position="start">
                  <LocationOn 
                    sx={{ 
                      color: disabled ? 'action.disabled' : '#1976d2',
                      fontSize: 20 
                    }} 
                  />
                </InputAdornment>
              ),
              sx: {
                backgroundColor: disabled ? 'action.hover' : 'background.paper',
                '& .MuiOutlinedInput-root': {
                  '& fieldset': {
                    borderColor: hasError ? '#ef4444' : (disabled ? 'action.disabled' : 'divider'),
                  },
                  '& .MuiInputBase-input': {
                    fontSize: '0.75rem',
                    height: '16px',
                  },
                },
              }
            }}
          />
        )}
        ListboxProps={{
          style: {
            maxHeight: '300px'
          }
        }}
        slotProps={{
          popper: {
            sx: {
              zIndex: 999999
            }
          }
        }}
        forcePopupIcon={false}
      />
    </Box>
  );
};

export default PortCity;
