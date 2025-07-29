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
import { fetchDMCsByCountry, setSelectedCountries, clearError } from '../../slice/dmc/dmcSlice';

const StyledDialog = styled(Dialog)(({ theme }) => ({
  '& .MuiDialog-paper': {
    borderRadius: '20px',
    padding: '0px',
    width: '60%',
    maxWidth: '700px',
    minWidth: '600px',
    boxShadow: '0 24px 38px 3px rgba(0,0,0,0.14), 0 9px 46px 8px rgba(0,0,0,0.12), 0 11px 15px -7px rgba(0,0,0,0.2)',
  },
}));

const StyledDialogTitle = styled(DialogTitle)(({ theme }) => ({
  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  color: 'white',
  padding: '24px 32px',
  borderRadius: '20px 20px 0 0',
  position: 'relative',
  '& .MuiIconButton-root': {
    color: 'white',
    position: 'absolute',
    right: '20px',
    top: '50%',
    transform: 'translateY(-50%)',
    backgroundColor: 'rgba(255,255,255,0.1)',
    '&:hover': {
      backgroundColor: 'rgba(255,255,255,0.2)',
    },
  },
}));

const SearchCard = styled(Card)(({ theme }) => ({
  borderRadius: '16px',
  background: 'linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)',
  border: '1px solid rgba(255,255,255,0.2)',
  boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
  marginBottom: '20px',
}));

const StyledTextField = styled(TextField)(({ theme }) => ({
  '& .MuiOutlinedInput-root': {
    borderRadius: '12px',
    backgroundColor: 'white',
    transition: 'all 0.3s ease',
    '&:hover': {
      boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
    },
    '&.Mui-focused': {
      boxShadow: '0 4px 20px rgba(102, 126, 234, 0.25)',
    },
  },
  '& .MuiInputLabel-root': {
    fontWeight: 500,
  },
}));

const PreviewCard = styled(Paper)(({ theme }) => ({
  padding: '16px 20px',
  borderRadius: '12px',
  background: 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%)',
  border: '2px solid #2196f3',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: '0 4px 12px rgba(33, 150, 243, 0.3)',
}));

const SearchLocationModal = ({ open, onClose, onSearch }) => {
  const [selectedCountry, setSelectedCountry] = useState('');
  const [countries, setCountries] = useState([]);
  
  const dispatch = useDispatch();
  const user_country = useSelector((state) => state.auth.user_country);
  const { loading: dmcLoading, error: dmcError } = useSelector((state) => state.dmc);
  
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
    console.log('User country from Redux:', user_country);
    
    const locationContent = user_country && Array.isArray(user_country)
      ? user_country.map((country, index) => ({
          name: country.name,
          code: country.code,
          key: `country-${index}`
        }))
      : defaultCountries;
    
    // Extract just the country names for the autocomplete
    const countryNames = locationContent.map(country => country.name);
    setCountries(countryNames);
    
    console.log('Location content:', locationContent);
  }, [user_country]);

  const handleCountryChange = (event, newValue) => {
    setSelectedCountry(newValue || '');
    // Clear any previous errors
    if (dmcError) {
      dispatch(clearError());
    }
  };

  const handleSearch = async () => {
    if (selectedCountry) {
      try {
        // Set selected countries in the store
        dispatch(setSelectedCountries([selectedCountry]));
        
        // Fetch DMCs for the selected country
        const result = await dispatch(fetchDMCsByCountry([selectedCountry]));
        
        if (fetchDMCsByCountry.fulfilled.match(result)) {
          // API call was successful, proceed to DMC selection
          onSearch({
            country: selectedCountry,
          });
          onClose();
          // Reset form
          setSelectedCountry('');
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
    onClose();
    // Reset form and clear errors
    setSelectedCountry('');
    if (dmcError) {
      dispatch(clearError());
    }
  };

  const isSearchDisabled = !selectedCountry || dmcLoading;

  return (
    <StyledDialog 
      open={open} 
      onClose={handleClose}
      maxWidth={false}
      fullWidth={false}
    >
      <StyledDialogTitle>
        <Box display="flex" alignItems="center">
          <TravelIcon sx={{ mr: 2, fontSize: 32 }} />
          <Box>
            <Typography variant="h4" component="div" fontWeight="bold" sx={{ fontSize: '1.5rem' }}>
              Find Your Perfect Destination
            </Typography>
            <Typography variant="body2" sx={{ color: 'white', mt: 0.5 }}>
              Search for the best DMCs in your preferred location
            </Typography>
          </Box>
        </Box>
        <IconButton onClick={handleClose}>
          <CloseIcon />
        </IconButton>
      </StyledDialogTitle>

      <DialogContent sx={{ padding: '32px', backgroundColor: '#fafbfc' }}>
        {dmcError && (
          <Alert severity="error" sx={{ mb: 3, borderRadius: '8px' }}>
            <Typography variant="body2">
              {dmcError}
            </Typography>
          </Alert>
        )}

        <SearchCard>
          <CardContent sx={{ padding: '28px !important' }}>
            <Box textAlign="center" mb={3}>
              <TravelIcon sx={{ fontSize: 40, color: '#667eea', mb: 1 }} />
              <Typography variant="h6" fontWeight="600" color="#333" gutterBottom>
                Select Your Travel Destination
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Choose your country to find the perfect DMC partners
              </Typography>
            </Box>

            <Grid container spacing={3} justifyContent="center">
              <Grid item xs={12} md={8}>
                <Autocomplete
                  options={countries}
                  value={selectedCountry}
                  onChange={handleCountryChange}
                  disabled={dmcLoading}
                  renderInput={(params) => (
                    <StyledTextField
                      {...params}
                      label="Select Country"
                      InputProps={{
                        ...params.InputProps,
                        startAdornment: (
                          <InputAdornment position="start">
                            <PublicIcon sx={{ color: dmcLoading ? '#ccc' : '#667eea' }} />
                          </InputAdornment>
                        ),
                      }}
                    />
                  )}
                />
              </Grid>
            </Grid>

            {selectedCountry && (
              <Box mt={3}>
                <PreviewCard elevation={0}>
                  <TravelIcon sx={{ color: '#2196f3', fontSize: 24, mr: 2 }} />
                  <Typography variant="h6" color="#1976d2" fontWeight="600">
                    Destination: {selectedCountry}
                  </Typography>
                </PreviewCard>
              </Box>
            )}
          </CardContent>
        </SearchCard>

        <Box textAlign="center">
          <Typography variant="body2" color="text.secondary" sx={{ fontStyle: 'italic' }}>
            Our AI will match you with the most suitable DMC partners in your selected country
          </Typography>
        </Box>
      </DialogContent>

      <DialogActions sx={{ padding: '20px 32px 32px', backgroundColor: '#fafbfc' }}>
        <Button 
          onClick={handleClose}
          variant="outlined"
          size="large"
          disabled={dmcLoading}
          sx={{ 
            borderRadius: '12px',
            textTransform: 'none',
            fontWeight: 600,
            minWidth: '120px',
            borderColor: '#667eea',
            color: '#667eea',
            '&:hover': {
              borderColor: '#5a67d8',
              backgroundColor: 'rgba(102, 126, 234, 0.04)',
            },
            '&:disabled': {
              borderColor: '#e0e0e0',
              color: '#9e9e9e',
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
          startIcon={dmcLoading ? <CircularProgress size={16} color="inherit" /> : <SearchIcon />}
          sx={{
            borderRadius: '12px',
            textTransform: 'none',
            fontWeight: 600,
            minWidth: '140px',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            boxShadow: '0 4px 15px rgba(102, 126, 234, 0.4)',
            '&:hover': {
              background: 'linear-gradient(135deg, #5a67d8 0%, #6b46a3 100%)',
              boxShadow: '0 6px 20px rgba(102, 126, 234, 0.6)',
            },
            '&:disabled': {
              background: '#e0e0e0',
              color: '#9e9e9e',
              boxShadow: 'none',
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