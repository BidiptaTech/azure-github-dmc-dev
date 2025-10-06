import React, { useState, useEffect } from 'react';
import { 
  Box, 
  Autocomplete,
  TextField,
  Typography,
  CircularProgress
} from '@mui/material';
import { endpoints } from "@/services/api";

export default function CitySelect({ onChange, value, selectedCountry, label = "City" }) {
  const [inputValue, setInputValue] = useState("");
  const [cityOptions, setCityOptions] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fetch cities when a country is selected
  useEffect(() => {
    if (!selectedCountry) {
      setCityOptions([]);
      return;
    }

    const fetchCities = async () => {
      setLoading(true);
      setError(null);

      try {
        console.log("Fetching cities for country:", selectedCountry);
        // Get the country name from the selected country
        const countryName = selectedCountry.name || selectedCountry.label;
        
        if (!countryName) {
          throw new Error("Country name is required");
        }
        
        // Make API call to fetch cities for the selected country
        const response = await endpoints.getCities(countryName);
        console.log('Raw city API response:', response.data);
        
        let formattedCities = [];
        
        // Process the response based on different possible data structures
        if (response.data && Array.isArray(response.data.cities)) {
          // Format 1: { cities: [...] }
          formattedCities = response.data.cities.map((cityName, index) => ({
            id: `${selectedCountry.code}-${cityName.replace(/\s+/g, '')}`,
            label: cityName,
            name: cityName,
            key: `city-${index}`
          }));
        } else if (response.data && Array.isArray(response.data)) {
          // Format 2: Direct array of city names
          formattedCities = response.data.map((cityName, index) => ({
            id: `${selectedCountry.code}-${cityName.replace(/\s+/g, '')}`,
            label: cityName,
            name: cityName,
            key: `city-${index}`
          }));
        } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
          // Format 3: Nested data { data: [...] }
          formattedCities = response.data.data.map((city, index) => {
            const cityName = typeof city === 'string' ? city : city.name || city.city_name;
            return {
              id: city.id || `${selectedCountry.code}-${cityName.replace(/\s+/g, '')}`,
              label: cityName,
              name: cityName,
              key: `city-${index}`
            };
          });
        } else {
          // Fallback - try to find any array in the response
          console.warn("Non-standard cities data format:", response.data);
          const findFirstArray = (obj) => {
            if (!obj) return null;
            if (Array.isArray(obj)) return obj;
            
            if (typeof obj === 'object') {
              for (const key in obj) {
                if (Array.isArray(obj[key]) && obj[key].length > 0) {
                  return obj[key];
                }
                const nestedArray = findFirstArray(obj[key]);
                if (nestedArray) return nestedArray;
              }
            }
            return null;
          };
          
          const citiesArray = findFirstArray(response.data);
          if (citiesArray) {
            formattedCities = citiesArray.map((city, index) => {
              const cityName = typeof city === 'string' ? city : city.name || city.city_name || city.label || JSON.stringify(city);
              return {
                id: `${selectedCountry.code}-${index}`,
                label: cityName,
                name: cityName,
                key: `city-${index}`
              };
            });
          } else {
            throw new Error("Could not extract city data from response");
          }
        }
        
        console.log('Formatted cities:', formattedCities);
        setCityOptions(formattedCities);
      } catch (error) {
        console.error("Error fetching cities:", error);
        setError("Failed to fetch cities: " + (error.message || "Unknown error"));
        setCityOptions([]);
      } finally {
        setLoading(false);
      }
    };

    fetchCities();
  }, [selectedCountry]);

  const handleChange = (event, newValue) => {
    if (onChange) {
      onChange(newValue);
    }
  };

  return (
    <Box sx={{ minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
      <Autocomplete
        options={cityOptions}
        autoHighlight
        value={value}
        onChange={handleChange}
        inputValue={inputValue}
        onInputChange={(event, newInputValue) => {
          setInputValue(newInputValue);
        }}
        getOptionLabel={(option) => option.label || ''}
        loading={loading}
        disabled={!selectedCountry}
        renderOption={(props, option) => (
          <Box component="li" {...props}>
            {option.label}
          </Box>
        )}
        renderInput={(params) => (
          <TextField
            {...params}
            placeholder={selectedCountry ? "Choose a city" : "Select a country first"}
            InputProps={{
              ...params.InputProps,
              endAdornment: (
                <React.Fragment>
                  {loading ? <CircularProgress color="inherit" size={20} /> : null}
                  {params.InputProps.endAdornment}
                </React.Fragment>
              ),
            }}
            inputProps={{
              ...params.inputProps,
              autoComplete: 'new-password',
            }}
            error={!!error}
            helperText={error}
          />
        )}
        noOptionsText={
          !selectedCountry 
            ? "Please select a country first" 
            : loading 
              ? "Loading cities..." 
              : "No cities found for this country"
        }
      />
    </Box>
  );
} 