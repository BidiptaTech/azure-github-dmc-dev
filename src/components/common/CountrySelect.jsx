import React, { useState, useEffect } from 'react';
import { 
  Box, 
  FormControl, 
  InputLabel, 
  Select, 
  MenuItem, 
  Typography,
  CircularProgress
} from '@mui/material';
import { useSelector } from 'react-redux';

const CountrySelect = ({ onChange, value, label = "Country" }) => {
  const [loading, setLoading] = useState(false);
  
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
  const countries = user_country && typeof user_country === 'string'
    ? user_country.split(',').map((country, index) => ({ 
        name: country.trim(),
        code: country.trim().substring(0, 2).toUpperCase(), 
        key: `country-${index}`
      }))
    : defaultCountries;

  const handleChange = (event) => {
    const selectedCountryCode = event.target.value;
    const selectedCountry = countries.find(country => country.code === selectedCountryCode);
    
    if (onChange && selectedCountry) {
      onChange(selectedCountry);
    }
  };

  return (
    <Box sx={{ minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
      <FormControl fullWidth>
        <Select
          value={value?.code || ''}
          onChange={handleChange}
          displayEmpty
          disabled={loading}
          renderValue={(selected) => {
            if (!selected) {
              return <em>Select a country</em>;
            }
            
            const country = countries.find(c => c.code === selected);
            return country ? country.name : selected;
          }}
          MenuProps={{
            PaperProps: {
              style: {
                maxHeight: 300
              }
            }
          }}
        >
          <MenuItem disabled value="">
            <em>Select a country</em>
          </MenuItem>
          {countries.map((country) => (
            <MenuItem key={country.key || country.code} value={country.code}>
              {country.name}
            </MenuItem>
          ))}
        </Select>
      </FormControl>
    </Box>
  );
};

export default CountrySelect; 