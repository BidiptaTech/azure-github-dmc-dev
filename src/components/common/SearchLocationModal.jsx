import React, { useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Box,
  IconButton,
  TextField,
  Autocomplete,
  Grid,
  Paper,
  InputAdornment,
  Card,
  CardContent,
  CircularProgress,
  Alert,
} from '@mui/material';
import {
  Close as CloseIcon,
  Search as SearchIcon,
  LocationOn as LocationIcon,
  Public as PublicIcon,
  TravelExplore as TravelIcon,
} from '@mui/icons-material';
import { styled } from '@mui/material/styles';
import { fetchDMCsByCountry, setSelectedCountries, clearError, clearSelectedDmc, clearSelectedDmcs } from '../../slice/dmc/dmcSlice';

const StyledDialog = styled(Dialog)(({ theme }) => ({
  '& .MuiDialog-paper': {
    borderRadius: { xs: '16px', sm: '20px', md: '24px' },
    padding: '0px',
    width: { xs: '96%', sm: '88%', md: '75%', lg: '65%' },
    maxWidth: { xs: '96vw', sm: '88vw', md: '750px', lg: '750px' },
    minWidth: { xs: '340px', sm: '420px', md: '520px', lg: '620px' },
    maxHeight: { xs: '96vh', sm: '92vh', md: '88vh', lg: '85vh' },
    margin: { xs: '12px', sm: '20px', md: '32px', lg: '40px' },
    boxShadow: '0 32px 64px 8px rgba(0,0,0,0.18), 0 16px 48px 12px rgba(0,0,0,0.15), 0 8px 24px -8px rgba(0,0,0,0.25)',
    zIndex: { xs: 1300, sm: 1300, md: 1300, lg: 1300 },
    position: 'relative',
    overflow: 'hidden',
    border: '1px solid rgba(255,255,255,0.1)',
    backdropFilter: 'blur(20px)',
  },
}));

const StyledDialogTitle = styled(DialogTitle)(({ theme }) => ({
  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  color: 'white',
  padding: { xs: '20px 24px', sm: '24px 28px', md: '28px 32px' },
  borderRadius: { xs: '12px 12px 0 0', sm: '16px 16px 0 0', md: '20px 20px 0 0' },
  position: 'relative',
  minHeight: { xs: '80px', sm: '90px', md: '100px' },
  display: 'flex',
  alignItems: 'center',
  '& .MuiIconButton-root': {
    color: 'white',
    backgroundColor: 'rgba(255,255,255,0.15)',
    padding: { xs: '10px', sm: '12px', md: '14px' },
    borderRadius: '50%',
    backdropFilter: 'blur(10px)',
    border: '1px solid rgba(255,255,255,0.2)',
    transition: 'all 0.3s ease',
    '&:hover': {
      backgroundColor: 'rgba(255,255,255,0.25)',
      transform: 'scale(1.05)',
      boxShadow: '0 4px 20px rgba(0,0,0,0.2)',
    },
  },
}));

const SearchCard = styled(Card)(({ theme }) => ({
  borderRadius: { xs: '16px', sm: '18px', md: '20px' },
  background: 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
  border: '2px solid rgba(102, 126, 234, 0.1)',
  boxShadow: '0 12px 40px rgba(102, 126, 234, 0.08), 0 4px 16px rgba(0,0,0,0.04)',
  marginBottom: { xs: '24px', sm: '28px', md: '32px' },
  overflow: 'hidden',
  position: 'relative',
  '&::before': {
    content: '""',
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    height: '4px',
    background: 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)',
    borderRadius: '20px 20px 0 0',
  },
}));

const StyledTextField = styled(TextField)(({ theme }) => ({
  '& .MuiOutlinedInput-root': {
    borderRadius: { xs: '12px', sm: '14px', md: '16px' },
    backgroundColor: 'white',
    border: '2px solid rgba(102, 126, 234, 0.1)',
    transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
    '&:hover': {
      borderColor: 'rgba(102, 126, 234, 0.3)',
      boxShadow: '0 8px 32px rgba(102, 126, 234, 0.12)',
    },
    '&.Mui-focused': {
      borderColor: '#667eea',
      boxShadow: '0 8px 32px rgba(102, 126, 234, 0.25)',
    },
    '& fieldset': {
      border: 'none',
    },
  },
  '& .MuiInputLabel-root': {
    fontWeight: 600,
    fontSize: { xs: '0.9rem', sm: '0.95rem', md: '1rem' },
    color: '#64748b',
  },
  '& .MuiInputBase-input': {
    fontSize: { xs: '0.9rem', sm: '0.95rem', md: '1rem' },
    padding: { xs: '16px 18px', sm: '16px 20px', md: '18px 22px' },
    color: '#1e293b',
  },
}));

const PreviewCard = styled(Paper)(({ theme }) => ({
  padding: { xs: '16px 20px', sm: '18px 24px', md: '20px 28px' },
  borderRadius: { xs: '12px', sm: '14px', md: '16px' },
  background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)',
  border: '2px solid rgba(102, 126, 234, 0.2)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: '0 8px 32px rgba(102, 126, 234, 0.15), 0 2px 8px rgba(0,0,0,0.05)',
  transition: 'all 0.3s ease',
  '&:hover': {
    transform: 'translateY(-2px)',
    boxShadow: '0 12px 40px rgba(102, 126, 234, 0.2), 0 4px 16px rgba(0,0,0,0.08)',
  },
}));

const SearchLocationModal = ({ open, onClose, onSearch }) => {
  // Debug modal open/close state
  
  const [selectedCountry, setSelectedCountry] = useState(null);
  const [countries, setCountries] = useState([]);
  //console.log('countries',selectedCountry);
  const dispatch = useDispatch();
  const global_countries = useSelector((state) => state.auth.global_countries);
  const { loading: dmcLoading, error: dmcError } = useSelector((state) => state.dmc);
  const currentDmcData = useSelector((state) => state.dmc.selectedDmcData);
  
  const defaultCountries = [
    { name: "India", code: "in" },
    { name: "Singapore", code: "SG" },
    { name: "UAE", code: "AE" },
    { name: "Thailand", code: "TH" },
    { name: "Switzerland", code: "CH" },
    { name: "Maldives", code: "MV" },
    { name: "Japan", code: "JP" },
    { name: "France", code: "FR" },
    { name: "Italy", code: "IT" },
    { name: "Spain", code: "ES" },
  ];

  useEffect(() => {
    const locationContent = global_countries && Array.isArray(global_countries)
      ? global_countries.map((country, index) => ({
          name: country.name,
          country_code: country.country_code,
          code:country.code,    
          key: `country-${index}`
        }))
      : defaultCountries;
    
    setCountries(locationContent);
  }, [global_countries]);

  const handleCountryChange = (event, newValue) => {
    console.log('handleCountryChange - newValue:', newValue);
    setSelectedCountry(newValue || null);
    // Clear any previous errors
    if (dmcError) {
      dispatch(clearError());
    }
  };

  const handleSearch = async () => {
    if (selectedCountry) {
      try {
        // Check if we're searching for the same country as the current DMC
        const isSameCountry = currentDmcData && currentDmcData.location === selectedCountry.name;
        
        if (!isSameCountry) {
          // Clear any previously selected DMCs before starting new search
          dispatch(clearSelectedDmc());
          dispatch(clearSelectedDmcs());
        }
        
        // Set selected countries in the store (store complete country object)
        dispatch(setSelectedCountries([selectedCountry]));
        
        // Fetch DMCs for the selected country (pass country name to API)
        const result = await dispatch(fetchDMCsByCountry([selectedCountry.name]));
        
        if (fetchDMCsByCountry.fulfilled.match(result)) {
          // Check if there's only 1 DMC available
          const dmcData = result.payload;
          if (dmcData && dmcData.data && dmcData.data.length === 1) {
            // Only 1 DMC available - auto-select it and skip DMC modal
            const singleDMC = dmcData.data[0];
            const selectedDMCData = {
              id: `dmc-auto-${singleDMC.userId}`,
              dmcId: singleDMC.userId,
              name: singleDMC.company_name || singleDMC.name || `DMC ${singleDMC.userId}`,
              location: singleDMC.country || 'Unknown Location',
              logo: singleDMC.logo || '',
              description: 'Automatically selected DMC',
              originalData: singleDMC
            };
            
            // Pass the auto-selected DMC data to the parent component
            onSearch({
              country: selectedCountry,
              skipDMCModal: true,
              selectedDMC: selectedDMCData
            });
          } else {
            // Multiple DMCs available - proceed with normal DMC selection modal
            console.log('🔍 Multiple DMCs found, opening DMC selection modal...');
            // Add small delay for mobile devices to ensure smooth modal transition
            setTimeout(() => {
              console.log('🚀 Calling onSearch with country:', selectedCountry);
              onSearch(selectedCountry);
            }, 100);
          }
          
          onClose();
          // Reset form
          setSelectedCountry(null);
        } else {
          // API call failed, error will be shown in UI
          console.error('Failed to fetch DMCs:', result.payload);
        }
      } catch (error) {
        console.error('Unexpected error during search:', error);
      }
    }
  };

  const handleClose = () => {
    console.log('🔍 SearchLocationModal: handleClose called');
    onClose();
    // Reset form and clear errors
    setSelectedCountry(null);
    if (dmcError) {
      dispatch(clearError());
    }
    // Clear any DMC selections when closing the modal
    dispatch(clearSelectedDmc());
    dispatch(clearSelectedDmcs());
  };

  const isSearchDisabled = !selectedCountry || dmcLoading;
  
  // console.log('selectedCountry:', selectedCountry);
  // console.log('countries:', countries);

  return (
    <StyledDialog 
      open={open} 
      onClose={handleClose}
      maxWidth={false}
      fullWidth={false}
    >
      <StyledDialogTitle>
        <Box display="flex" alignItems="center" justifyContent="space-between" width="100%">
          <Box display="flex" alignItems="center" gap={{ xs: 1, sm: 2 }}>
            <TravelIcon sx={{ 
              fontSize: { xs: 20, sm: 24, md: 28 }
            }} />
            <Box>
              <Typography 
                variant="h4" 
                component="div" 
                fontWeight="bold" 
                sx={{ 
                  fontSize: { xs: '1.1rem', sm: '1.25rem', md: '1.5rem' },
                  lineHeight: { xs: 1.2, sm: 1.4, md: 1.5 }
                }}
              >
                Find Your Perfect Destination
              </Typography>
             
            </Box>
          </Box>
          <IconButton onClick={handleClose}>
            <CloseIcon />
          </IconButton>
        </Box>
      </StyledDialogTitle>

      <DialogContent sx={{ 
        padding: { xs: '24px', sm: '28px', md: '32px', lg: '40px' }, 
        backgroundColor: 'linear-gradient(135deg, #fafbfc 0%, #f1f5f9 100%)',
        maxHeight: { xs: '75vh', sm: '70vh', md: '65vh' },
        overflowY: 'auto',
        paddingBottom: { xs: '32px', sm: '36px', md: '40px' },
        '&::-webkit-scrollbar': {
          width: '6px',
        },
        '&::-webkit-scrollbar-track': {
          background: 'rgba(0,0,0,0.05)',
          borderRadius: '3px',
        },
        '&::-webkit-scrollbar-thumb': {
          background: 'rgba(102, 126, 234, 0.3)',
          borderRadius: '3px',
          '&:hover': {
            background: 'rgba(102, 126, 234, 0.5)',
          },
        },
      }}>
        {dmcError && (
          <Alert severity="error" sx={{ mb: 3, borderRadius: '8px' }}>
            <Typography variant="body2">
              {dmcError}
            </Typography>
          </Alert>
        )}

        <SearchCard>
          <CardContent sx={{ padding: { xs: '16px', sm: '20px', md: '24px', lg: '28px' } + ' !important' }}>
            <Box textAlign="center" mb={{ xs: 2, sm: 2.5, md: 3 }}>
              {/* <TravelIcon sx={{ 
                fontSize: { xs: 32, sm: 36, md: 40 }, 
                color: '#667eea', 
                mb: 1 
              }} /> */}
              <Typography 
                variant="h6" 
                fontWeight="600" 
                color="#333" 
                gutterBottom
                sx={{ 
                  fontSize: { xs: '1rem', sm: '1.1rem', md: '1.25rem' },
                  lineHeight: { xs: 1.3, sm: 1.4, md: 1.5 }
                }}
              >
                Select Your Travel Destination
              </Typography>
              {/* <Typography 
                variant="body2" 
                color="text.secondary"
                sx={{ 
                  fontSize: { xs: '0.8rem', sm: '0.875rem', md: '1rem' },
                  lineHeight: { xs: 1.4, sm: 1.5, md: 1.6 }
                }}
              >
                Choose your country to find the perfect DMC partners
              </Typography> */}
            </Box>

            <Grid container spacing={{ xs: 2, sm: 2.5, md: 3 }} justifyContent="center">
              <Grid item xs={12} sm={10} md={8}>
                <Autocomplete
                  options={countries}
                  value={selectedCountry || null}
                  onChange={handleCountryChange}
                  disabled={dmcLoading}
                  getOptionLabel={(option) => {
                    if (!option) return '';
                    if (typeof option === 'string') return option;
                    return option?.name || '';
                  }}
                  isOptionEqualToValue={(option, value) => {
                    if (!option || !value) return false;
                    if (typeof option === 'string' && typeof value === 'string') {
                      return option === value;
                    }
                    return option.key === value.key;
                  }}
                  renderOption={(props, option) => (
                    <li {...props} key={option.key}>
                      {option.name}
                    </li>
                  )}
                  renderInput={(params) => (
                    <StyledTextField
                      {...params}
                      label="Select Country"
                      InputProps={{
                        ...params.InputProps,
                        startAdornment: (
                          <InputAdornment position="start">
                            <PublicIcon sx={{ 
                              color: dmcLoading ? '#ccc' : '#667eea',
                              fontSize: { xs: 20, sm: 22, md: 24 }
                            }} />
                          </InputAdornment>
                        ),
                      }}
                    />
                  )}
                />
              </Grid>
            </Grid>

            {selectedCountry && (
              <Box mt={{ xs: 2, sm: 2.5, md: 3 }}>
                <PreviewCard elevation={0}>
                  <TravelIcon sx={{ 
                    color: '#2196f3', 
                    fontSize: { xs: 20, sm: 22, md: 24 }, 
                    mr: { xs: 1, sm: 1.5, md: 2 } 
                  }} />
                  <Typography 
                    variant="h6" 
                    color="#1976d2" 
                    fontWeight="600"
                    sx={{ 
                      fontSize: { xs: '0.9rem', sm: '1rem', md: '1.25rem' },
                      lineHeight: { xs: 3.3, sm: 3.4, md: 2.5 }
                    }}
                  >
                    Destination: {selectedCountry.name}
                  </Typography>
                </PreviewCard>
              </Box>
            )}
          </CardContent>
        </SearchCard>

        <Box textAlign="center">
          <Typography 
            variant="body2" 
            color="text.secondary" 
            sx={{ 
              fontStyle: 'italic',
              fontSize: { xs: '0.75rem', sm: '0.8rem', md: '0.875rem' },
              lineHeight: { xs: 1.4, sm: 1.5, md: 1.6 },
              px: { xs: 1, sm: 2, md: 0 }
            }}
          >
            Our AI will match you with the most suitable DMC partners in your selected country
          </Typography>
        </Box>
      </DialogContent>

      <DialogActions sx={{ 
        //padding: { xs: '32px 28px', sm: '36px 32px', md: '40px 40px', lg: '40px 40px 48px' }, 
        backgroundColor: 'linear-gradient(135deg, #fafbfc 0%, #f1f5f9 100%)',
        flexDirection: { xs: 'column', sm: 'row' },
        gap: { xs: 3, sm: 2 },
        borderTop: '1px solid rgba(102, 126, 234, 0.1)',
        //marginTop: { xs: '16px', sm: '20px', md: '24px' },
        '& > *': {
          margin: { xs: '0 !important', sm: '0 !important' },
          width: { xs: '100%', sm: 'auto' },
          minWidth: { xs: '100%', sm: '140px' },
        }
      }}>
        <Button 
          onClick={handleClose}
          variant="outlined"
          size="large"
          disabled={dmcLoading}
          sx={{ 
            borderRadius: { xs: '12px', sm: '14px', md: '16px' },
            textTransform: 'none',
            fontWeight: 600,
            borderColor: 'rgba(102, 126, 234, 0.3)',
            color: '#667eea',
            fontSize: { xs: '0.9rem', sm: '0.95rem', md: '1rem' },
            padding: { xs: '12px 20px', sm: '14px 24px', md: '16px 28px' },
            border: '2px solid',
            transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
            '&:hover': {
              borderColor: '#5a67d8',
              backgroundColor: 'rgba(102, 126, 234, 0.08)',
              transform: 'translateY(-2px)',
              boxShadow: '0 8px 32px rgba(102, 126, 234, 0.15)',
            },
            '&:disabled': {
              borderColor: 'rgba(0,0,0,0.12)',
              color: 'rgba(0,0,0,0.26)',
              backgroundColor: 'rgba(0,0,0,0.04)',
            },
          }}
        >
          Cancel
        </Button>
        <Button
          onClick={handleSearch}
          variant="contained"
          size="large"
          disabled={isSearchDisabled}
          startIcon={dmcLoading ? <CircularProgress size={18} color="inherit" /> : <SearchIcon />}
          sx={{
            borderRadius: { xs: '12px', sm: '14px', md: '16px' },
            textTransform: 'none',
            fontWeight: 600,
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            boxShadow: '0 8px 32px rgba(102, 126, 234, 0.3), 0 2px 8px rgba(0,0,0,0.1)',
            fontSize: { xs: '0.9rem', sm: '0.95rem', md: '1rem' },
            padding: { xs: '12px 24px', sm: '14px 28px', md: '16px 32px' },
            border: 'none',
            transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
            '&:hover': {
              background: 'linear-gradient(135deg, #5a67d8 0%, #6b46a3 100%)',
              boxShadow: '0 12px 48px rgba(102, 126, 234, 0.4), 0 4px 16px rgba(0,0,0,0.15)',
              transform: 'translateY(-2px)',
            },
            '&:active': {
              transform: 'translateY(0px)',
            },
            '&:disabled': {
              background: 'linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%)',
              color: 'rgba(0,0,0,0.38)',
              boxShadow: 'none',
              transform: 'none',
            },
          }}
        >
          {dmcLoading ? 'Searching...' : 'Find DMCs'}
        </Button>
      </DialogActions>
    </StyledDialog>
  );
};

export default SearchLocationModal; 