import React, { useState, useMemo } from 'react';
import { 
  Box, 
  Autocomplete,
  TextField,
  Typography,
  CircularProgress
} from '@mui/material';
import { useSelector } from 'react-redux';

export default function CountrySelect({ onChange, value, label = "Country" }) {
  const [inputValue, setInputValue] = useState('');
  
  // Get user_country from Redux state
  const user_country = useSelector((state) => state.auth.user_country);
  
  // Default countries if user_country isn't available
  const defaultCountries = [
    { code: 'US', label: 'United States' },
    { code: 'GB', label: 'United Kingdom' },
    { code: 'CA', label: 'Canada' },
    { code: 'AU', label: 'Australia' },
    { code: 'IN', label: 'India' },
    { code: 'FR', label: 'France' },
    { code: 'DE', label: 'Germany' },
    { code: 'IT', label: 'Italy' },
    { code: 'JP', label: 'Japan' },
    { code: 'CN', label: 'China' }
  ];

  // Process available countries from user_country
  const availableCountries = useMemo(() => {
    // For debugging, log what we received
    console.log("user_country from Redux:", user_country);
    
    if (user_country && typeof user_country === 'string') {
      return user_country.split(',').map((country, index) => ({
        code: country.trim().toLowerCase(),
        label: country.trim(),
        name: country.trim(), // Add name for API compatibility
        key: `country-${index}`
      }));
    } else if (user_country && Array.isArray(user_country)) {
      return user_country.map((country, index) => {
        // Check if country is an object or string
        if (typeof country === 'object' && country !== null) {
          // Extract country data from object format
          const countryName = country.name || country.label || country.country || '';
          const countryCode = country.code || country.country_code || '';
          
          return {
            code: countryCode.toLowerCase ? countryCode.toLowerCase() : countryCode,
            label: countryName,
            name: countryName, // Add name for API compatibility
            key: `country-${index}`
          };
        } else if (typeof country === 'string') {
          // Handle string format
          return {
            code: country.toLowerCase(),
            label: country,
            name: country, // Add name for API compatibility
            key: `country-${index}`
          };
        } else {
          // Fall back to a default format
          console.warn("Unexpected country format:", country);
          return {
            code: `country-${index}`,
            label: `Country ${index}`,
            name: `Country ${index}`,
            key: `country-${index}`
          };
        }
      });
    } else {
      return defaultCountries;
    }
  }, [user_country]);

  const handleChange = (event, newValue) => {
    if (onChange) {
      onChange(newValue);
    }
  };

  return (
    <Box sx={{ minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
      <Autocomplete
        options={availableCountries}
        autoHighlight
        value={value}
        onChange={handleChange}
        inputValue={inputValue}
        onInputChange={(event, newInputValue) => {
          setInputValue(newInputValue);
        }}
        getOptionLabel={(option) => option.label || ''}
        renderOption={(props, option) => (
          <Box component="li" sx={{ '& > img': { mr: 2, flexShrink: 0 } }} {...props}>
            {option.label}
          </Box>
        )}
        renderInput={(params) => (
          <TextField
            {...params}
            placeholder="Choose a country"
            inputProps={{
              ...params.inputProps,
              autoComplete: 'new-password', // disable autocomplete and autofill
            }}
          />
        )}
      />
    </Box>
  );
} 