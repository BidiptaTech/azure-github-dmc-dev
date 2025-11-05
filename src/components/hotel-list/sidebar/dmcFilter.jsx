import React, { useEffect, useState } from 'react';
import {
  FormControl,
  FormLabel,
  RadioGroup,
  FormControlLabel,
  Radio,
  Typography,
  Box,
  Avatar,
  CircularProgress,
  Alert,
  Paper
} from '@mui/material';
import { useDispatch, useSelector } from 'react-redux';
import { fetchDMCsByCountry, setSelectedDmcId } from '../../../slice/dmc/dmcSlice';
import BusinessIcon from '@mui/icons-material/Business';

const DmcFilter = () => {
  const dispatch = useDispatch();
  
  // Get destination from bookings slice with proper null checking
  const destination = useSelector((state) => {
    // Try hotels.tourdetails first (single object, not array)
    if (state.hotels?.tourdetails?.destination) {
      return state.hotels.tourdetails.destination;
    }
    // Fallback to bookings.bookings
    if (state.bookings?.bookings && state.bookings.bookings.length > 0 && state.bookings.bookings[0]?.destination) {
      return state.bookings.bookings[0].destination;
    }
    
    return null;
  });
  
  // Get dmc_id - default to first DMC's userId if dmcs array exists
  const dmc_id = useSelector((state) => {
    // Default to first DMC's userId if dmcs array exists
    if (state.dmc?.dmcs?.data && state.dmc.dmcs.data.length > 0) {
      return state.dmc.dmcs.data[0].userId;
    }
    return null;
  });
  
  const tourdetails = useSelector((state) => state.hotels?.tourdetails);
  console.log(tourdetails, "tourdetails");
  console.log(destination, "destination");
  console.log(dmc_id, "dmc_id");
  
  // Get DMC data from Redux
  const dmcs = useSelector((state) => state.dmc?.dmcs);
  console.log(dmcs, "dmcs");
  const loading = useSelector((state) => state.dmc?.loading);
  const error = useSelector((state) => state.dmc?.error);
  
  const [localSelectedDmc, setLocalSelectedDmc] = useState('');

  // Fetch DMCs when destination changes
  useEffect(() => {
    if (destination) {
      console.log('🔍 DMC Filter - Fetching DMCs for destination:', destination);
      
      // Dispatch the API call with destination array
      dispatch(fetchDMCsByCountry(destination))
        .unwrap()
        .then((response) => {
          console.log('✅ DMC Filter - DMCs fetched successfully:', response);
        })
        .catch((err) => {
          console.error('❌ DMC Filter - Error fetching DMCs:', err);
        });
    }
  }, [destination, dispatch]);

  // Auto-select first DMC when dmcs are loaded
  useEffect(() => {
    if (dmcs?.data && dmcs.data.length > 0 && dmc_id && !localSelectedDmc) {
      const firstDmc = dmcs.data[0];
      const firstDmcId = firstDmc.userId;
      
      // Get the country from destination or from the DMC data
      let selectedCountry = firstDmc.country || 'Unknown Location';
      
      // Handle destination (could be array or string)
      if (destination) {
        if (Array.isArray(destination) && destination.length > 0) {
          selectedCountry = destination[0];
        } else if (typeof destination === 'string') {
          selectedCountry = destination;
        }
      }
      
      // Create DMC data object with price_hide and zone_on
      const dmcData = {
        id: `dmc-${firstDmc.userId}`,
        dmcId: firstDmc.userId,
        name: firstDmc.company_name || firstDmc.name || `DMC ${firstDmc.userId}`,
        location: selectedCountry,
        logo: firstDmc.logo || '',
        description: 'Default selected DMC',
        originalData: {
          ...firstDmc,
          price_hide: firstDmc.price_hide || 0,
          zone_on: firstDmc.zone_on || 0
        }
      };
      
      console.log('🔄 DMC Filter - Auto-selecting first DMC by default:', dmcData);
      console.log('🔄 DMC Filter - price_hide:', firstDmc.price_hide, 'zone_on:', firstDmc.zone_on);
      
      // Dispatch to Redux (will set Cookies for PriceHide and zone_on)
      setLocalSelectedDmc(firstDmcId);
      dispatch(setSelectedDmcId({ dmcId: parseInt(firstDmcId), dmcData }));
    }
  }, [dmcs, destination, dispatch, dmc_id, localSelectedDmc]);

  // Handle DMC selection change
  const handleDmcChange = (event) => {
    const dmcId = event.target.value;
    setLocalSelectedDmc(dmcId);
    
    // Find the selected DMC data
    const selectedDmc = dmcs?.data?.find(dmc => dmc.userId === parseInt(dmcId));
    
    if (selectedDmc) {
      // Get the country from destination or from the DMC data
      let selectedCountry = selectedDmc.country || 'Unknown Location';
      
      // Handle destination (could be array or string)
      if (destination) {
        if (Array.isArray(destination) && destination.length > 0) {
          selectedCountry = destination[0];
        } else if (typeof destination === 'string') {
          selectedCountry = destination;
        }
      }
      
      // Create DMC data object with price_hide and zone_on
      const dmcData = {
        id: `dmc-${selectedDmc.userId}`,
        dmcId: selectedDmc.userId,
        name: selectedDmc.company_name || selectedDmc.name || `DMC ${selectedDmc.userId}`,
        location: selectedCountry,
        logo: selectedDmc.logo || '',
        description: selectedDmc.description || 'Professional DMC Service',
        originalData: {
          ...selectedDmc,
          price_hide: selectedDmc.price_hide || 0,
          zone_on: selectedDmc.zone_on || 0
        }
      };
      
      console.log('📌 DMC Filter - Selected DMC:', dmcData);
      console.log('📌 DMC Filter - price_hide:', selectedDmc.price_hide, 'zone_on:', selectedDmc.zone_on);
      
      // Dispatch to Redux (will set Cookies for PriceHide and zone_on)
      dispatch(setSelectedDmcId({ dmcId: parseInt(dmcId), dmcData }));
    }
  };

  // Handle "All DMCs" selection (clear filter)
  const handleClearSelection = () => {
    setLocalSelectedDmc('');
    dispatch(setSelectedDmcId({ dmcId: null, dmcData: null }));
  };

  // Show loading state
  if (loading) {
    return (
      <Box sx={{ p: 2, textAlign: 'center' }}>
        <CircularProgress size={24} />
        <Typography variant="caption" sx={{ display: 'block', mt: 1 }}>
          Loading DMCs...
        </Typography>
      </Box>
    );
  }

  // Show error state
  if (error) {
    return (
      <Box sx={{ p: 2 }}>
        <Alert severity="error" sx={{ fontSize: '0.75rem' }}>
          {error}
        </Alert>
      </Box>
    );
  }

  // Check if we have DMC data
  const dmcList = dmcs?.data || [];
  
  // Don't show filter if no DMCs available
  if (dmcList.length === 0) {
    return null;
  }

  return (
    <Box sx={{ mb: 3 }}>
      <Paper elevation={0} sx={{ p: 2, bgcolor: 'background.paper', borderRadius: 2 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
          <BusinessIcon sx={{ mr: 1, color: 'primary.main', fontSize: 20 }} />
          <Typography variant="subtitle2" fontWeight={600}>
            Filter by DMC
          </Typography>
        </Box>
        
        <FormControl component="fieldset" fullWidth>
          <RadioGroup
            value={localSelectedDmc}
            onChange={handleDmcChange}
          >
            {/* "All DMCs" option */}
            {/* <FormControlLabel
              value=""
              control={
                <Radio 
                  size="small"
                  sx={{
                    '&.Mui-checked': {
                      color: '#3554D1',
                    },
                  }}
                />
              }
              label={
                <Typography variant="body2" sx={{ fontSize: '0.875rem' }}>
                  All DMCs
                </Typography>
              }
              onClick={handleClearSelection}
            /> */}
            
            {/* Individual DMC options */}
            {dmcList.map((dmc) => (
              <FormControlLabel
                key={dmc.userId}
                value={dmc.userId}
                control={
                  <Radio 
                    size="small"
                    sx={{
                      '&.Mui-checked': {
                        color: '#3554D1',
                      },
                    }}
                  />
                }
                label={
                  <Box sx={{ display: 'flex', alignItems: 'center', py: 0.5 }}>
                    {dmc.logo && (
                      <Avatar 
                        src={dmc.logo} 
                        alt={`${dmc.company_name} Logo`} 
                        sx={{ 
                          width: 24, 
                          height: 24, 
                          marginRight: 1.5,
                          border: '1px solid',
                          borderColor: 'divider'
                        }}
                      />
                    )}
                    <Box sx={{ flex: 1 }}>
                      <Typography variant="body2" sx={{ fontSize: '0.875rem', fontWeight: 500 }}>
                        {dmc.company_name || dmc.name || `DMC ${dmc.userId}`}
                      </Typography>
                      {dmc.country && (
                        <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.75rem' }}>
                          {dmc.country}
                        </Typography>
                      )}
                    </Box>
                  </Box>
                }
              />
            ))}
          </RadioGroup>
        </FormControl>
        
        {/* Show selected count */}
        {localSelectedDmc && (
          <Box sx={{ mt: 2, pt: 2, borderTop: '1px solid', borderColor: 'divider' }}>
            <Typography variant="caption" color="primary.main" sx={{ fontSize: '0.75rem' }}>
              Showing results for selected DMC
            </Typography>
          </Box>
        )}
      </Paper>
    </Box>
  );
};

export default DmcFilter;



