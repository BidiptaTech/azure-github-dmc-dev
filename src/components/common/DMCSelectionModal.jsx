import React, { useState, useMemo } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Radio,
  RadioGroup,
  FormControlLabel,
  FormControl,
  Button,
  Typography,
  Box,
  IconButton,
  Paper,
  Divider,
  Avatar,
  Grid,
  Alert,
  Chip,
  Card,
  CardContent,
  TextField,
  InputAdornment,
  CircularProgress
} from '@mui/material';
import {
  Close as CloseIcon,
  Business as BusinessIcon,
  LocationOn as LocationIcon,
  Star as StarIcon,
  TravelExplore as TravelIcon,
  CheckCircle as CheckIcon,
  Search as SearchIcon,
  FilterList as FilterIcon,
} from '@mui/icons-material';
import { styled } from '@mui/material/styles';
import { selectDMCs, selectDMCLoading, selectDMCError, setSelectedDmcId, clearSelectedDmc } from '../../slice/dmc/dmcSlice';

const StyledDialog = styled(Dialog)(({ theme }) => ({
  '& .MuiDialog-paper': {
    borderRadius: '20px',
    padding: '0px',
    width: '80%',
    maxWidth: 'none',
    minWidth: '900px',
    maxHeight: '90vh',
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

const DMCCard = styled(Card)(({ theme, selected }) => ({
  height: '100px',
  width: '100%',
  borderRadius: '16px',
  border: selected ? '3px solid #667eea' : '2px solid #e8eaf6',
  background: selected 
    ? 'linear-gradient(135deg, #f3f4ff 0%, #e8eaf6 100%)' 
    : 'linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)',
  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  cursor: 'pointer',
  position: 'relative',
  overflow: 'visible',
  '&:hover': {
    transform: 'translateY(-8px) scale(1.02)',
    boxShadow: selected 
      ? '0 20px 40px rgba(102, 126, 234, 0.3)' 
      : '0 16px 32px rgba(0,0,0,0.15)',
    border: '3px solid #667eea',
  },
  '&::before': selected ? {
    content: '""',
    position: 'absolute',
    top: '-2px',
    left: '-2px',
    right: '-2px',
    bottom: '-2px',
    borderRadius: '18px',
    zIndex: -1,
  } : {},
}));

const SelectionBadge = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: '-8px',
  right: '-8px',
  backgroundColor: '#4caf50',
  borderRadius: '50%',
  width: '24px',
  height: '24px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: '0 4px 12px rgba(76, 175, 80, 0.4)',
  zIndex: 10,
}));

const StyledAlert = styled(Alert)(({ theme }) => ({
  borderRadius: '16px',
  border: '1px solid #2196f3',
  background: 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%)',
  boxShadow: '0 8px 24px rgba(33, 150, 243, 0.2)',
  '& .MuiAlert-icon': {
    color: '#1976d2',
  },
}));

const FilterBox = styled(Paper)(({ theme }) => ({
  padding: '16px 20px',
  borderRadius: '12px',
  background: 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)',
  border: '1px solid #dee2e6',
  marginBottom: '24px',
  boxShadow: '0 4px 12px rgba(0,0,0,0.08)',
}));

// Fallback DMC options in case API data is not available
const fallbackDMCOptions = [
  {
    id: 'dmc1',
    name: 'Premium Travel DMC',
    location: 'Dubai, UAE',
    rating: 4.8,
    description: 'Specializing in premium travel experiences with personalized service',
  },
  {
    id: 'dmc2',
    name: 'Global Explorer DMC',
    location: 'Singapore',
    rating: 4.6,
    description: 'Your trusted partner for comprehensive travel solutions',
  },
  {
    id: 'dmc3',
    name: 'Adventure Seekers DMC',
    location: 'Thailand',
    rating: 4.9,
    description: 'Creating unforgettable adventure experiences in exotic locations',
  },
  {
    id: 'dmc4',
    name: 'Heritage Tours DMC',
    location: 'India',
    rating: 4.7,
    description: 'Discover rich cultural heritage with authentic local experiences',
  },
  {
    id: 'dmc5',
    name: 'Alpine Adventures DMC',
    location: 'Switzerland',
    rating: 4.9,
    description: 'Experience breathtaking mountain adventures in the Swiss Alps',
  },
  {
    id: 'dmc6',
    name: 'Tropical Paradise DMC',
    location: 'Maldives',
    rating: 4.8,
    description: 'Ultimate tropical getaway with pristine beaches and luxury resorts',
  },
];

const DMCSelectionModal = ({ open, onClose, onSelect, searchCriteria }) => {
  const [selectedDMC, setSelectedDMC] = useState('');
  const [filterText, setFilterText] = useState('');

  const dispatch = useDispatch();

  // Get DMC data from Redux store
  const apiDMCs = useSelector(selectDMCs);
  const dmcLoading = useSelector(selectDMCLoading);
  const dmcError = useSelector(selectDMCError);

  // Use API data if available, otherwise fallback to hardcoded data
  const dmcOptions = useMemo(() => {
    if (apiDMCs && typeof apiDMCs === 'object' && apiDMCs.dmcs) {
      // Convert dmcs object to array and transform API data to match expected format
      const dmcsArray = Object.values(apiDMCs.dmcs);
      return dmcsArray.map((dmc, index) => ({
        id: `dmc-${index}`, // Use index for UI identification
        dmcId: (dmc.dmcId && dmc.dmcId !== 0) ? dmc.dmcId : null, // Actual dmcId - null if 0
        name: dmc.company_name || `DMC ${index + 1}`,
        location: dmc.country || 'Unknown Location',
        logo: dmc.logo || '',
        rating: 4.5, // Default rating since not provided in API
        description: 'Professional destination management services',
        // Keep original API data for Redux storage
        originalData: dmc,
      }));
    }
    return fallbackDMCOptions;
  }, [apiDMCs]);

  const handleSelectionChange = (event) => {
    const uiId = event.target.value;
    setSelectedDMC(uiId);
    
    // Find the selected DMC data
    const selectedDmcData = dmcOptions.find(dmc => dmc.id.toString() === uiId);
    
    console.log('📝 Radio button clicked - UI ID:', uiId);
    console.log('📝 API dmcId field:', selectedDmcData?.originalData?.dmcId);
    console.log('📝 Logic: dmcId === 0 → set to null, dmcId > 0 → use dmcId');
    console.log('📝 Final dmcId to store:', selectedDmcData?.dmcId);
    console.log('📝 Selected DMC Data:', selectedDmcData);
    
    // Dispatch to Redux store with actual dmcId (can be null)
    dispatch(setSelectedDmcId({
      dmcId: selectedDmcData?.dmcId, // This will be null if original dmcId was 0
      dmcData: selectedDmcData
    }));
    
    console.log('✅ DMC ID dispatched to Redux store');
  };

  const handleDMCCardClick = (uiId) => {
    const uiIdString = uiId.toString();
    setSelectedDMC(uiIdString);
    
    // Find the selected DMC data
    const selectedDmcData = dmcOptions.find(dmc => dmc.id.toString() === uiIdString);
    
    console.log('🎯 DMC Card clicked - UI ID:', uiId);
    console.log('🎯 API dmcId field:', selectedDmcData?.originalData?.dmcId);
    console.log('🎯 Logic: dmcId === 0 → set to null, dmcId > 0 → use dmcId');
    console.log('🎯 Final dmcId to store:', selectedDmcData?.dmcId);
    console.log('🎯 Selected DMC Data:', selectedDmcData);
    
    // Dispatch to Redux store with actual dmcId (can be null)
    dispatch(setSelectedDmcId({
      dmcId: selectedDmcData?.dmcId, // This will be null if original dmcId was 0
      dmcData: selectedDmcData
    }));
    
    console.log('✅ DMC ID dispatched to Redux store via card click');
  };

  const handleFilterChange = (event) => {
    setFilterText(event.target.value);
  };

  // Filter DMCs based on search text (name only)
  const filteredDMCs = useMemo(() => {
    if (!filterText.trim()) {
      return dmcOptions;
    }
    return dmcOptions.filter(dmc => 
      dmc.name.toLowerCase().includes(filterText.toLowerCase())
    );
  }, [filterText, dmcOptions]);

  const handleConfirm = () => {
    if (selectedDMC) {
      const selected = dmcOptions.find(dmc => dmc.id.toString() === selectedDMC);
      onSelect(selected);
      onClose();
      setSelectedDMC('');
      setFilterText('');
    }
  };

  const handleClose = () => {
    onClose();
    setSelectedDMC('');
    setFilterText('');
    // Clear Redux selection if no confirmation
    dispatch(clearSelectedDmc());
  };

  const getLocationText = () => {
    if (!searchCriteria) return '';
    return searchCriteria.country || '';
  };

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
              Choose Your DMC Partner
            </Typography>
            <Typography variant="body2" sx={{ color: 'white', mt: 0.5 }}>
              Select the perfect destination management company for your journey
            </Typography>
          </Box>
        </Box>
        <IconButton onClick={handleClose}>
          <CloseIcon />
        </IconButton>
      </StyledDialogTitle>

      <DialogContent sx={{ padding: '32px', maxHeight: '60vh', overflowY: 'auto', backgroundColor: '#fafbfc' }}>
        {searchCriteria && (
          <StyledAlert 
            severity="info" 
            sx={{ mb: 4, mt: 3 }}
          >
            <Box display="flex" alignItems="center" justifyContent="center" width="100%">
              <LocationIcon sx={{ mr: 2, fontSize: 24 }} />
              <Typography variant="h6" fontWeight="600">
                Available DMCs in: <Chip label={getLocationText()} sx={{ ml: 1, fontWeight: 'bold', backgroundColor: '#1976d2', color: 'white' }} />
              </Typography>
            </Box>
          </StyledAlert>
        )}

        {dmcError && (
          <Alert severity="error" sx={{ mb: 3, borderRadius: '8px' }}>
            <Typography variant="body2">
              Error loading DMCs: {dmcError}
            </Typography>
          </Alert>
        )}

        <FilterBox elevation={0}>
          <Box display="flex" alignItems="center" gap={2}>
            <FilterIcon sx={{ color: '#667eea', fontSize: 24 }} />
            <Typography variant="h6" fontWeight="600" color="#333" sx={{ minWidth: 'fit-content' }}>
              DMC Filter
            </Typography>
            <TextField
              fullWidth
              variant="outlined"
              placeholder="Search DMCs by name..."
              value={filterText}
              onChange={handleFilterChange}
              size="small"
              disabled={dmcLoading}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <SearchIcon sx={{ color: dmcLoading ? '#ccc' : '#90a4ae', fontSize: 20 }} />
                  </InputAdornment>
                ),
              }}
              sx={{
                '& .MuiOutlinedInput-root': {
                  borderRadius: '8px',
                  backgroundColor: 'white',
                  '&:hover': {
                    boxShadow: dmcLoading ? 'none' : '0 2px 8px rgba(0,0,0,0.1)',
                  },
                  '&.Mui-focused': {
                    boxShadow: dmcLoading ? 'none' : '0 2px 12px rgba(102, 126, 234, 0.2)',
                  },
                },
              }}
            />
            {filteredDMCs.length !== dmcOptions.length && (
              <Chip
                label={`${filteredDMCs.length} of ${dmcOptions.length}`}
                size="small"
                sx={{
                  backgroundColor: '#667eea',
                  color: 'white',
                  fontWeight: 'bold',
                  minWidth: 'fit-content',
                }}
              />
            )}
          </Box>
        </FilterBox>

        {dmcLoading ? (
          <Box display="flex" justifyContent="center" alignItems="center" py={6}>
            <CircularProgress size={40} sx={{ color: '#667eea' }} />
            <Typography variant="h6" sx={{ ml: 2, color: '#667eea' }}>
              Loading DMCs...
            </Typography>
          </Box>
        ) : (
          <>
            <FormControl component="fieldset" fullWidth>
              <RadioGroup
                value={selectedDMC}
                onChange={handleSelectionChange}
              >
                <Grid container spacing={3}>
                  {filteredDMCs.map((dmc) => (
                    <Grid item xs={12} sm={6} md={4} lg={2} xl={1.5} key={dmc.id}>
                      <FormControlLabel
                        value={dmc.id.toString()}
                        control={<Radio sx={{ display: 'none' }} />}
                        label={
                          <DMCCard 
                            selected={selectedDMC === dmc.id.toString()} 
                            elevation={selectedDMC === dmc.id.toString() ? 8 : 2}
                            onClick={() => handleDMCCardClick(dmc.id)}
                          >
                            {selectedDMC === dmc.id.toString() && (
                              <SelectionBadge>
                                <CheckIcon sx={{ color: 'white', fontSize: 16 }} />
                              </SelectionBadge>
                            )}
                            
                            <CardContent sx={{ padding: '16px 12px !important', height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
                              {/* Logo and DMC Name - Same Line */}
                              <Box display="flex" alignItems="center" justifyContent="center" mb={1} gap={0.5}>
                                {dmc.logo ? (
                                  <Box
                                    sx={{
                                      width: 28,
                                      height: 28,
                                      borderRadius: '50%',
                                      overflow: 'hidden',
                                      border: selectedDMC === dmc.id.toString() ? '2px solid #667eea' : '2px solid #90a4ae',
                                      display: 'flex',
                                      alignItems: 'center',
                                      justifyContent: 'center',
                                      backgroundColor: 'white',
                                    }}
                                  >
                                    <img
                                      src={dmc.logo}
                                      alt={`${dmc.name} logo`}
                                      style={{
                                        width: '100%',
                                        height: '100%',
                                        objectFit: 'cover',
                                      }}
                                      onError={(e) => {
                                        // Fallback to icon if image fails to load
                                        e.target.style.display = 'none';
                                        e.target.nextSibling.style.display = 'flex';
                                      }}
                                    />
                                    <Avatar
                                      sx={{
                                        display: 'none',
                                        bgcolor: selectedDMC === dmc.id.toString() ? '#667eea' : '#90a4ae',
                                        width: 24,
                                        height: 24,
                                        background: selectedDMC === dmc.id.toString() 
                                          ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                          : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                      }}
                                    >
                                      <BusinessIcon sx={{ fontSize: 12 }} />
                                    </Avatar>
                                  </Box>
                                ) : (
                                  <Avatar
                                    sx={{
                                      bgcolor: selectedDMC === dmc.id.toString() ? '#667eea' : '#90a4ae',
                                      width: 28,
                                      height: 28,
                                      background: selectedDMC === dmc.id.toString() 
                                        ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                        : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                    }}
                                  >
                                    <BusinessIcon sx={{ fontSize: 14 }} />
                                  </Avatar>
                                )}
                                <Typography 
                                  variant="subtitle2" 
                                  fontWeight="700" 
                                  color={selectedDMC === dmc.id.toString() ? '#667eea' : '#333'}
                                  sx={{ 
                                    fontSize: '0.8rem',
                                    lineHeight: 1.2,
                                    textAlign: 'left',
                                    flex: 1,
                                    wordWrap: 'break-word',
                                  }}
                                >
                                  {dmc.name}
                                </Typography>
                              </Box>
                              
                              {/* Location Section - Under the logo and name */}
                              <Box display="flex" alignItems="center" justifyContent="center">
                                <Chip
                                  icon={<LocationIcon sx={{ fontSize: 12 }} />}
                                  label={dmc.location}
                                  size="small"
                                  sx={{
                                    backgroundColor: selectedDMC === dmc.id.toString() ? '#667eea' : '#e3f2fd',
                                    color: selectedDMC === dmc.id.toString() ? 'white' : '#1976d2',
                                    fontSize: '0.68rem',
                                    fontWeight: 500,
                                    height: '20px',
                                    '& .MuiChip-icon': {
                                      color: selectedDMC === dmc.id.toString() ? 'white' : '#1976d2',
                                      fontSize: 12,
                                    },
                                    '& .MuiChip-label': {
                                      px: 1,
                                      fontSize: '0.68rem',
                                    },
                                  }}
                                />
                              </Box>
                            </CardContent>
                          </DMCCard>
                        }
                        sx={{ 
                          margin: 0,
                          width: '100%',
                          height: '100%',
                          '& .MuiFormControlLabel-label': {
                            width: '100%',
                            height: '100%',
                          },
                        }}
                      />
                    </Grid>
                  ))}
                </Grid>
              </RadioGroup>
            </FormControl>

            {filteredDMCs.length === 0 && !dmcLoading && (
              <Box textAlign="center" py={4}>
                <SearchIcon sx={{ fontSize: 48, color: '#e0e0e0', mb: 2 }} />
                <Typography variant="h6" color="text.secondary" gutterBottom>
                  No DMCs Found
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Try adjusting your search criteria
                </Typography>
              </Box>
            )}
          </>
        )}
      </DialogContent>

      <Divider sx={{ borderColor: '#e0e0e0' }} />

      <DialogActions sx={{ padding: '24px 32px', backgroundColor: '#fafbfc' }}>
        <Button 
          onClick={handleClose}
          variant="outlined"
          size="large"
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
          }}
        >
          Cancel
        </Button>
        <Button
          onClick={handleConfirm}
          variant="contained"
          size="large"
          disabled={!selectedDMC || dmcLoading}
          sx={{
            borderRadius: '12px',
            textTransform: 'none',
            fontWeight: 600,
            minWidth: '160px',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            boxShadow: '0 8px 24px rgba(102, 126, 234, 0.4)',
            '&:hover': {
              background: 'linear-gradient(135deg, #5a67d8 0%, #6b46a3 100%)',
              boxShadow: '0 12px 32px rgba(102, 126, 234, 0.6)',
              transform: 'translateY(-2px)',
            },
            '&:disabled': {
              background: '#e0e0e0',
              color: '#9e9e9e',
              boxShadow: 'none',
            },
          }}
        >
          Continue with Selection
        </Button>
      </DialogActions>
    </StyledDialog>
  );
};

export default DMCSelectionModal; 