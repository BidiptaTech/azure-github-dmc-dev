import React, { useState, useEffect } from 'react';
import { 
  Box, 
  FormControl,
  Select,
  MenuItem, 
  Typography,
  CircularProgress
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import { fetchCitiesByCountry } from '@/slice/common/citiesSlice';

const CitySelect = ({ onChange, value, selectedCountry, label = "City" }) => {
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false);
  
  // Get cities from Redux state
  const { cities, loading: citiesLoading, error: citiesError } = useSelector((state) => state.cities || { cities: [], loading: false, error: null });
  
  // Fetch cities when a country is selected
  useEffect(() => {
    if (selectedCountry?.name) {
      setLoading(true);
      dispatch(fetchCitiesByCountry(selectedCountry.name))
        .finally(() => setLoading(false));
    }
  }, [dispatch, selectedCountry]);
  
  const handleChange = (event) => {
    const selectedCityName = event.target.value;
    
    if (onChange) {
      onChange({
        name: selectedCityName,
        code: selectedCityName.replace(/\s+/g, '').substring(0, 3).toUpperCase(),
        countryCode: selectedCountry?.code
      });
    }
  };
  
  return (
    <Box sx={{ minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
      <FormControl fullWidth>
        <Select
          value={value?.name || ''}
          onChange={handleChange}
          displayEmpty
          disabled={loading || citiesLoading || !selectedCountry}
          renderValue={(selected) => {
            if (!selected) {
              return <em>{selectedCountry ? "Select a city" : "Select a country first"}</em>;
            }
            return selected;
          }}
          MenuProps={{
            PaperProps: {
              style: {
                maxHeight: 300
              }
            }
          }}
          endAdornment={
            loading || citiesLoading ? (
              <CircularProgress color="inherit" size={20} sx={{ position: 'absolute', right: 32 }} />
            ) : null
          }
        >
          <MenuItem disabled value="">
            <em>{selectedCountry ? "Select a city" : "Select a country first"}</em>
          </MenuItem>
          {cities.map((city, index) => (
            <MenuItem key={`city-${index}`} value={city}>
              {city}
            </MenuItem>
          ))}
        </Select>
        {citiesError && (
          <Typography color="error" variant="caption">
            Error loading cities: {citiesError}
          </Typography>
        )}
      </FormControl>
    </Box>
  );
};

export default CitySelect; 