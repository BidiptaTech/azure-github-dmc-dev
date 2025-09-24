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
  CircularProgress,
  Badge
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
  Info as InfoIcon,
  Warning as WarningIcon,
  EmojiEvents as TrophyIcon,
  Verified as VerifiedIcon,
} from '@mui/icons-material';
import { styled } from '@mui/material/styles';
import { 
  selectDMCs, 
  selectDMCLoading, 
  selectDMCError, 
  setSelectedDmcId, 
  clearSelectedDmc,
  setSelectedDmcIds,
  addDmcToSelection,
  removeDmcFromSelection,
  clearSelectedDmcs,
  selectSelectedDmcIds,
  selectSelectedDmcsData,
  selectSelectedDmcLogo,
  selectSelectedDmcCompanyName
} from '../../slice/dmc/dmcSlice';

const StyledDialog = styled(Dialog)(({ theme }) => ({
  '& .MuiDialog-paper': {
    borderRadius: { xs: '12px', sm: '16px', md: '20px' },
    padding: '0px',
    width: { xs: '95%', sm: '90%', md: '85%', lg: '80%' },
    maxWidth: { xs: '95vw', sm: '90vw', md: '85vw', lg: '1200px' },
    minWidth: { xs: '320px', sm: '580px', md: '750px', lg: '850px' },
    maxHeight: { xs: '95vh', sm: '90vh', md: '85vh', lg: '90vh' },
    margin: { xs: '8px', sm: '16px', md: '24px', lg: '32px' },
    boxShadow: '0 24px 48px 6px rgba(0,0,0,0.15), 0 12px 32px 8px rgba(0,0,0,0.12), 0 6px 16px -4px rgba(0,0,0,0.2)',
    overflow: 'hidden',
    border: '1px solid rgba(255,255,255,0.1)',
    backdropFilter: 'blur(20px)',
  },
}));

const StyledDialogTitle = styled(DialogTitle)(({ theme }) => ({
  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  color: 'white',
  padding: { xs: '12px 16px', sm: '16px 20px', md: '20px 24px' },
  borderRadius: { xs: '12px 12px 0 0', sm: '16px 16px 0 0', md: '20px 20px 0 0' },
  position: 'relative',
  minHeight: { xs: '60px', sm: '70px', md: '80px' },
  display: 'flex',
  alignItems: 'center',
  '& .MuiIconButton-root': {
    color: 'white',
    backgroundColor: 'rgba(255,255,255,0.15)',
    padding: { xs: '6px', sm: '8px', md: '10px' },
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

const DMCCard = styled(Card)(({ theme, selected }) => ({
  height: { xs: '100px', sm: '95px', md: '90px' },
  width: '100%',
  borderRadius: { xs: '12px', sm: '14px', md: '12px' },
  border: selected ? '2px solid #667eea' : '1px solid #e8eaf6',
  background: selected 
    ? 'linear-gradient(135deg, #f3f4ff 0%, #e8eaf6 100%)' 
    : 'linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)',
  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  cursor: 'pointer',
  position: 'relative',
  overflow: 'hidden',
  minHeight: { xs: '100px', sm: '95px', md: '90px' },
  maxHeight: { xs: '100px', sm: '95px', md: '90px' },
  // Enhanced shadows for small screens only
  boxShadow: { 
    xs: selected 
      ? '0 8px 32px rgba(102, 126, 234, 0.15), 0 2px 8px rgba(0,0,0,0.05)'
      : '0 4px 16px rgba(0,0,0,0.06), 0 1px 4px rgba(0,0,0,0.04)',
    sm: selected 
      ? '0 8px 32px rgba(102, 126, 234, 0.15), 0 2px 8px rgba(0,0,0,0.05)'
      : '0 4px 16px rgba(0,0,0,0.06), 0 1px 4px rgba(0,0,0,0.04)',
    md: selected 
      ? '0 8px 16px rgba(102, 126, 234, 0.2)'
      : '0 4px 12px rgba(0,0,0,0.1)',
  },
  '&:hover': {
    transform: { 
      xs: 'translateY(-3px) scale(1.01)', 
      sm: 'translateY(-4px) scale(1.02)', 
      md: 'translateY(-8px) scale(1.02)' 
    },
    boxShadow: { 
      xs: selected 
        ? '0 12px 40px rgba(102, 126, 234, 0.25)'
        : '0 8px 32px rgba(102, 126, 234, 0.12)',
      sm: selected 
        ? '0 20px 48px rgba(102, 126, 234, 0.3)'
        : '0 16px 40px rgba(102, 126, 234, 0.15)',
      md: selected 
        ? '0 20px 40px rgba(102, 126, 234, 0.3)'
        : '0 16px 32px rgba(0,0,0,0.15)'
    },
    border: '3px solid #667eea',
  },
  '&:active': {
    transform: { xs: 'translateY(-1px) scale(0.98)', sm: 'translateY(-1px) scale(0.99)', md: 'scale(0.99)' },
  },
  // Remove the ::before pseudo-element for large screens to keep original look
  '&::before': selected ? {
    content: { xs: '""', sm: '""', md: 'none' },
    position: 'absolute',
    top: '-3px',
    left: '-3px',
    right: '-3px',
    bottom: '-3px',
    borderRadius: { xs: '19px', sm: '21px' },
    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    zIndex: -1,
    opacity: 0.1,
  } : {},
}));

const SelectionBadge = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: { xs: '-10px', sm: '-10px', md: '-8px' },
  right: { xs: '-10px', sm: '-10px', md: '-8px' },
  backgroundColor: '#4caf50',
  borderRadius: '50%',
  width: { xs: '28px', sm: '28px', md: '24px' },
  height: { xs: '28px', sm: '28px', md: '24px' },
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: { 
    xs: '0 6px 20px rgba(76, 175, 80, 0.4), 0 2px 8px rgba(0,0,0,0.1)',
    sm: '0 6px 20px rgba(76, 175, 80, 0.4), 0 2px 8px rgba(0,0,0,0.1)',
    md: '0 4px 12px rgba(76, 175, 80, 0.4)'
  },
  zIndex: 10,
  border: { xs: '2px solid white', sm: '2px solid white', md: 'none' },
  backdropFilter: { xs: 'blur(10px)', sm: 'blur(10px)', md: 'none' },
  // Only animate on small screens
  animation: { xs: 'pulse 2s infinite', sm: 'pulse 2s infinite', md: 'none' },
  '@keyframes pulse': {
    '0%': {
      boxShadow: '0 6px 20px rgba(76, 175, 80, 0.4), 0 2px 8px rgba(0,0,0,0.1)',
    },
    '50%': {
      boxShadow: '0 6px 20px rgba(76, 175, 80, 0.6), 0 2px 8px rgba(0,0,0,0.1)',
    },
    '100%': {
      boxShadow: '0 6px 20px rgba(76, 175, 80, 0.4), 0 2px 8px rgba(0,0,0,0.1)',
    },
  },
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
  padding: { xs: '12px 16px', sm: '14px 18px', md: '16px 20px' },
  borderRadius: { xs: '8px', sm: '10px', md: '12px' },
  background: 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
  //border: '2px solid rgba(102, 126, 234, 0.1)',
  marginBottom: { xs: '20px', sm: '24px', md: '28px' },
  boxShadow: '0 6px 24px rgba(102, 126, 234, 0.06), 0 2px 6px rgba(0,0,0,0.03)',
  overflow: 'hidden',
  position: 'relative',
  '&::before': {
    content: '""',
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    height: '3px',
    //background: 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)',
    borderRadius: '12px 12px 0 0',
  },
}));

// Remove the fallbackDMCOptions array completely

const DMCSelectionModal = ({ open, onClose, onSelect, searchCriteria, multiSelect = false }) => {
  const [selectedDMC, setSelectedDMC] = useState(''); // For single selection
  const [filterText, setFilterText] = useState('');

  const dispatch = useDispatch();

  // Get DMC data from Redux store
  const apiDMCs = useSelector(selectDMCs);
  const dmcLoading = useSelector(selectDMCLoading);
  const dmcError = useSelector(selectDMCError);
  
  // Get multiple selection data from Redux (for multiSelect mode)
  const selectedDmcIds = useSelector(selectSelectedDmcIds);
  const selectedDmcsData = useSelector(selectSelectedDmcsData);
  
  // Get DMC logo and company name from Redux (for testing)
  const selectedDmcLogo = useSelector(selectSelectedDmcLogo);
  const selectedDmcCompanyName = useSelector(selectSelectedDmcCompanyName);

  // Use API data if available, otherwise return empty array
  const dmcOptions = useMemo(() => {
    if (apiDMCs && apiDMCs.data && Array.isArray(apiDMCs.data)) {
      // Use the new API response format with data array
      return apiDMCs.data.map((dmc, index) => ({
        id: `dmc-${index}`, // Use index for UI identification
        dmcId: dmc.userId || null, // Use userId as dmcId
        name: dmc.company_name || `DMC ${index + 1}`,
        location: dmc.country || 'Unknown Location',
        logo: dmc.logo || '',
       
        description: 'Professional destination management services',
        // Keep original API data for Redux storage
        originalData: dmc,
      }));
    }
    return []; // Return empty array instead of fallback data
  }, [apiDMCs]);

  // Debug logging for modal open state
  // React.useEffect(() => {
  //   console.log('🎯 DMCSelectionModal: open prop changed to:', open);
  //   if (open) {
  //     console.log('🎯 DMCSelectionModal: Modal should be visible now');
  //     console.log('🎯 DMCSelectionModal: DMC Options available:', dmcOptions.length);
  //     console.log('🎯 DMCSelectionModal: Loading state:', dmcLoading);
  //     console.log('🎯 DMCSelectionModal: Error state:', dmcError);
  //   } else {
  //     console.log('🚪 DMCSelectionModal: Modal should be closed now');
  //   }
  // }, [open, dmcOptions, dmcLoading, dmcError]);

  // Console logging for testing current state
  // React.useEffect(() => {
  //   if (open) {
  //     console.log('🔍 DMC Modal: Current Redux State:');
  //     console.log('🔍 DMC Modal: Selected DMC Logo:', selectedDmcLogo);
  //     console.log('🔍 DMC Modal: Selected DMC Company Name:', selectedDmcCompanyName);
  //     console.log('🔍 DMC Modal: Available DMC Options:', dmcOptions);
  //     console.log('🔍 DMC Modal: Current selectedDMC state:', selectedDMC);
  //   }
  // }, [open, selectedDmcLogo, selectedDmcCompanyName, dmcOptions, selectedDMC]);

  // Single selection handlers (for Book Tour)
  const handleSelectionChange = (event) => {
    const uiId = event.target.value;
    setSelectedDMC(uiId);
    
    // Find the selected DMC data
    const selectedDmcData = dmcOptions.find(dmc => dmc.id.toString() === uiId);
    
    console.log('📝 Radio button clicked - UI ID:', uiId);
    console.log('📝 API userId field:', selectedDmcData?.originalData?.userId);
    console.log('📝 Using userId as dmcId:', selectedDmcData?.dmcId);
    console.log('📝 Selected DMC Data:', selectedDmcData);
    
    // Dispatch to Redux store with actual dmcId (can be null)
    dispatch(setSelectedDmcId({
      dmcId: selectedDmcData?.dmcId, // This will be null if original dmcId was 0
      dmcData: selectedDmcData
    }));
    
    // console.log('✅ DMC ID dispatched to Redux store');
  };

  const handleDMCCardClick = (uiId, event) => {
    console.log('🎯 DMC Card clicked - UI ID:', uiId, 'Event:', event);
    
    const uiIdString = uiId.toString();
    
    if (multiSelect) {
      // Handle multiple selection
      const selectedDmcData = dmcOptions.find(dmc => dmc.id.toString() === uiIdString);
      const dmcId = selectedDmcData?.dmcId;
      
      // console.log('🎯 DMC Card clicked (Multi-Select) - UI ID:', uiId);
      // console.log('🎯 dmcId:', dmcId);
      
      // Check if DMC is already selected
      const isSelected = selectedDmcIds.includes(dmcId);
      
      if (isSelected) {
        // Remove from selection
        dispatch(removeDmcFromSelection({ dmcId }));
        // console.log('➖ DMC removed from multi-selection'); 
      } else {
        // Add to selection
        dispatch(addDmcToSelection({ dmcId, dmcData: selectedDmcData }));
        // console.log('➕ DMC added to multi-selection');
      }
    } else {
      // Handle single selection (existing logic)
      setSelectedDMC(uiIdString);
      
      // Find the selected DMC data
      const selectedDmcData = dmcOptions.find(dmc => dmc.id.toString() === uiIdString);
      
      console.log('🎯 API userId field:', selectedDmcData?.originalData?.userId);
      console.log('🎯 Using userId as dmcId:', selectedDmcData?.dmcId);
      console.log('🎯 Selected DMC Data:', selectedDmcData);
      
      // Dispatch to Redux store with actual dmcId (can be null)
      dispatch(setSelectedDmcId({
        dmcId: selectedDmcData?.dmcId, // This will be null if original dmcId was 0
        dmcData: selectedDmcData
      }));
      
      console.log('✅ DMC ID dispatched to Redux store via card click');
    }
  };



  const handleFilterChange = (event) => {
    console.log('🔍 Filter change triggered:', event.target.value);
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
    if (multiSelect) {
      // Multi-select mode: confirm with selected DMCs array
      if (selectedDmcIds.length > 0) {
        onSelect(selectedDmcsData);
        onClose();
        setFilterText('');
      }
    } else {
      // Single select mode: confirm with single DMC
      if (selectedDMC) {
        const selected = dmcOptions.find(dmc => dmc.id.toString() === selectedDMC);
        onSelect(selected);
        onClose();
        setSelectedDMC('');
        setFilterText('');
      }
    }
  };

  const handleClose = () => {
    console.log('🚪 DMC Modal closing - handleClose called');
    onClose();
    setSelectedDMC('');
    setFilterText('');
    // Clear Redux selection if no confirmation
    if (multiSelect) {
      dispatch(clearSelectedDmcs());
    } else {
      dispatch(clearSelectedDmc());
    }
  };

  const getLocationText = () => {
    if (!searchCriteria) return '';
    // Handle both string and object formats for backward compatibility
    if (typeof searchCriteria.country === 'string') {
      return searchCriteria.country;
    }
    return searchCriteria.country?.name || '';
  };

  // Check if a DMC is selected (for styling)
  const isDMCSelected = (dmc) => {
    if (multiSelect) {
      return selectedDmcIds.includes(dmc.dmcId);
    } else {
      return selectedDMC === dmc.id.toString();
    }
  };

  return (
    <StyledDialog 
      open={open} 
      onClose={handleClose}
      maxWidth={false}
      fullWidth={false}
      disableScrollLock={false}
      disableEscapeKeyDown={false}
      BackdropProps={{
        style: {
          backgroundColor: 'rgba(0, 0, 0, 0.5)',
        }
      }}
    >
      <StyledDialogTitle>
        <Box display="flex" alignItems="center" justifyContent="space-between" width="100%">
          <Box display="flex" alignItems="center" gap={{ xs: 1, sm: 2 }}>
            <TravelIcon sx={{ 
              fontSize: { xs: 18, sm: 20, md: 22 }
            }} />
            <Box>
              <Typography 
                variant="h5" 
                component="div" 
                fontWeight="bold" 
                sx={{ 
                  fontSize: { xs: '1rem', sm: '1.1rem', md: '1.25rem' },
                  lineHeight: { xs: 1.2, sm: 1.3, md: 1.4 }
                }}
              >
                {multiSelect ? 'Choose Your DMC Partners' : 'Choose Your DMC Partner'}
              </Typography>
            </Box>
          </Box>
          <IconButton onClick={handleClose}>
            <CloseIcon />
          </IconButton>
        </Box>
      </StyledDialogTitle>

      <DialogContent 
        onClick={() => console.log('📋 DialogContent clicked - modal is interactive')}
        sx={{ 
          padding: { xs: '16px', sm: '20px', md: '24px', lg: '32px' }, 
          maxHeight: { xs: '75vh', sm: '70vh', md: '70vh', lg: '70vh' }, 
          overflowY: 'auto', 
          backgroundColor: 'linear-gradient(135deg, #fafbfc 0%, #f1f5f9 100%)',
          pointerEvents: 'auto',
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
        {searchCriteria && (
          <StyledAlert 
            severity="info" 
            sx={{ 
              mb: { xs: 1.5, sm: 2, md: 2.5 }, 
              mt: { xs: 0, sm: 0.5, md: 1 },
              padding: { xs: '6px 10px', sm: '8px 12px', md: '10px 14px' }
            }}
          >
            <Box display="flex" alignItems="center" justifyContent="center" width="100%" flexDirection={{ xs: 'column', sm: 'row' }} gap={{ xs: 0.5, sm: 1 }}>
              <LocationIcon sx={{ mr: { xs: 0, sm: 1 }, fontSize: { xs: 16, sm: 18, md: 20 } }} />
              <Typography variant="body1" fontWeight="600" sx={{ fontSize: { xs: '0.875rem', sm: '0.9rem', md: '1rem' }, textAlign: { xs: 'center', sm: 'left' } }}>
                Available DMCs in: <Chip label={getLocationText()} sx={{ ml: { xs: 0, sm: 1 }, mt: { xs: 0.5, sm: 0 }, fontWeight: 'bold', backgroundColor: '#1976d2', color: 'white', fontSize: { xs: '0.7rem', sm: '0.75rem', md: '0.8rem' } }} />
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
          <Box display="flex" alignItems="center" gap={{ xs: 1.5, sm: 2 }}>
            <FilterIcon sx={{ color: '#667eea', fontSize: { xs: 18, sm: 20, md: 22 } }} />
            <Typography variant="subtitle1" fontWeight="600" color="#333" sx={{ minWidth: 'fit-content', fontSize: { xs: '0.875rem', sm: '0.9rem', md: '1rem' } }}>
              DMC Filter
            </Typography>
            <TextField
              fullWidth
              variant="outlined"
              placeholder="Search DMCs by name..."
              value={filterText}
              onChange={handleFilterChange}
              onClick={() => console.log('🔍 Search input clicked - input is interactive')}
              size="small"
              disabled={dmcLoading}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <SearchIcon sx={{ color: dmcLoading ? '#ccc' : '#90a4ae', fontSize: { xs: 16, sm: 18, md: 20 } }} />
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
                  fontSize: { xs: '0.7rem', sm: '0.75rem', md: '0.8rem' }
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
            <FormControl component="fieldset" fullWidth sx={{ mt: { xs: 2, sm: 3, md: 2 }}}>
              {!multiSelect ? (
                <RadioGroup
                  value={selectedDMC}
                  onChange={handleSelectionChange}
                >
                  <Grid container spacing={{ xs: 1, sm: 1.5, md: 2 }}>
                    {filteredDMCs.map((dmc) => (
                      <Grid item xs={12} sm={6} md={4} lg={3} xl={2} key={dmc.id}>
                        <FormControlLabel
                          value={dmc.id.toString()}
                          control={<Radio sx={{ display: 'none' }} />}
                          label={
                            <DMCCard 
                              selected={isDMCSelected(dmc)} 
                              elevation={isDMCSelected(dmc) ? 8 : 2}
                              onClick={(e) => handleDMCCardClick(dmc.id, e)}
                            >
                              {isDMCSelected(dmc) && (
                                <SelectionBadge>
                                  <CheckIcon sx={{ color: 'white', fontSize: 16 }} />
                                </SelectionBadge>
                              )}
                              
                              <CardContent sx={{ 
                                padding: { xs: '8px 6px', sm: '10px 8px', md: '12px 10px' } + ' !important', 
                                height: '100%', 
                                display: 'flex', 
                                flexDirection: 'column', 
                                justifyContent: 'space-between',
                                minHeight: { xs: '100px', sm: '95px', md: '90px' },
                                maxHeight: { xs: '100px', sm: '95px', md: '90px' },
                                overflow: 'hidden'
                              }}>
                                {/* Logo and DMC Name - Same Line */}
                                <Box display="flex" alignItems="center" justifyContent="center" mb={0.5} gap={0.5}>
                                  {dmc.logo && dmc.logo.trim() !== '' ? (
                                    <Box
                                      sx={{
                                        width: { xs: 28, sm: 26, md: 28 },
                                        height: { xs: 28, sm: 26, md: 28 },
                                        borderRadius: '50%',
                                        overflow: 'hidden',
                                        border: isDMCSelected(dmc) ? '2px solid #667eea' : '1px solid #90a4ae',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        backgroundColor: 'white',
                                        position: 'relative',
                                        flexShrink: 0,
                                      }}
                                    >
                                      <img
                                        src={dmc.logo}
                                        alt={`${dmc.name} logo`}
                                        style={{
                                          width: '100%',
                                          height: '100%',
                                          objectFit: 'cover',
                                          display: 'block',
                                        }}
                                        onError={(e) => {
                                          // Hide image and show fallback icon
                                          e.target.style.display = 'none';
                                          const fallback = e.target.nextElementSibling;
                                          if (fallback) {
                                            fallback.style.display = 'flex';
                                          }
                                        }}
                                        onLoad={(e) => {
                                          // Hide fallback when image loads successfully
                                          const fallback = e.target.nextElementSibling;
                                          if (fallback) {
                                            fallback.style.display = 'none';
                                          }
                                        }}
                                      />
                                      <Avatar
                                        sx={{
                                          display: 'none',
                                          bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                          width: { xs: 24, sm: 22, md: 24 },
                                          height: { xs: 24, sm: 22, md: 24 },
                                          background: isDMCSelected(dmc) 
                                            ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                            : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                        }}
                                      >
                                        <BusinessIcon sx={{ fontSize: { xs: 10, sm: 12, md: 14 } }} />
                                      </Avatar>
                                    </Box>
                                  ) : (
                                    <Box sx={{ position: 'relative', flexShrink: 0 }}>
                                      <Avatar
                                        sx={{
                                          bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                          width: { xs: 28, sm: 26, md: 28 },
                                          height: { xs: 28, sm: 26, md: 28 },
                                          background: isDMCSelected(dmc) 
                                            ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                            : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                        }}
                                      >
                                        <BusinessIcon sx={{ fontSize: { xs: 12, sm: 14, md: 16 } }} />
                                      </Avatar>
                                    </Box>
                                  )}
                                  <Box sx={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                                    <Typography 
                                      variant="subtitle2" 
                                      fontWeight="600" 
                                      color={isDMCSelected(dmc) ? '#667eea' : '#333'}
                                      sx={{ 
                                        fontSize: { xs: '0.7rem', sm: '0.75rem', md: '0.8rem' },
                                        lineHeight: { xs: 1.1, sm: 1.15, md: 1.2 },
                                        textAlign: 'left',
                                        wordWrap: 'break-word',
                                      }}
                                    >
                                      {dmc.name}
                                    </Typography>
                                   
                                  </Box>
                                </Box>
                                
                                {/* Location Section - Under the logo and name */}
                                <Box display="flex" alignItems="center" justifyContent="center">
                                  <Chip
                                    icon={<LocationIcon sx={{ fontSize: { xs: 8, sm: 9, md: 10 } }} />}
                                    label={dmc.location}
                                    size="small"
                                    sx={{
                                      backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#e8f5e8',
                                      color: isDMCSelected(dmc) ? 'white' : '#2e7d32',
                                      fontSize: { xs: '0.6rem', sm: '0.65rem', md: '0.7rem' },
                                      fontWeight: 600,
                                      height: { xs: '18px', sm: '18px', md: '20px' },
                                      border: isDMCSelected(dmc) ? 'none' : '1px solid #4caf50',
                                      '& .MuiChip-icon': {
                                        color: isDMCSelected(dmc) ? 'white' : '#2e7d32',
                                        fontSize: { xs: 8, sm: 9, md: 10 },
                                      },
                                      '& .MuiChip-label': {
                                        px: { xs: 0.5, sm: 0.75, md: 1 },
                                        fontSize: { xs: '0.55rem', sm: '0.6rem', md: '0.65rem' },
                                      },
                                      '&:hover': {
                                        backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#c8e6c9',
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
                              ) : (
                  // Multi-select mode without checkboxes - just card clicks
                  <Grid container spacing={{ xs: 1, sm: 1.5, md: 2 }}>
                    {filteredDMCs.map((dmc) => (
                      <Grid item xs={12} sm={6} md={4} lg={3} xl={2} key={dmc.id}>
                        <DMCCard 
                          selected={isDMCSelected(dmc)} 
                          elevation={isDMCSelected(dmc) ? 8 : 2}
                          onClick={(e) => handleDMCCardClick(dmc.id, e)}
                        >
                          {isDMCSelected(dmc) && (
                            <SelectionBadge>
                              <CheckIcon sx={{ color: 'white', fontSize: 16 }} />
                            </SelectionBadge>
                          )}
                          
                          <CardContent sx={{ 
                            padding: { xs: '8px 6px', sm: '10px 8px', md: '12px 10px' } + ' !important', 
                            height: '100%', 
                            display: 'flex', 
                            flexDirection: 'column', 
                            justifyContent: 'space-between',
                            minHeight: { xs: '100px', sm: '95px', md: '90px' },
                            maxHeight: { xs: '100px', sm: '95px', md: '90px' },
                            overflow: 'hidden'
                          }}>
                            {/* Logo and DMC Name - Same Line */}
                            <Box display="flex" alignItems="center" justifyContent="center" mb={0.5} gap={0.5}>
                              {dmc.logo && dmc.logo.trim() !== '' ? (
                                <Box
                                  sx={{
                                    width: { xs: 28, sm: 26, md: 28 },
                                    height: { xs: 28, sm: 26, md: 28 },
                                    borderRadius: '50%',
                                    overflow: 'hidden',
                                    border: isDMCSelected(dmc) ? '2px solid #667eea' : '1px solid #90a4ae',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    backgroundColor: 'white',
                                    position: 'relative',
                                    flexShrink: 0,
                                  }}
                                >
                                  <img
                                    src={dmc.logo}
                                    alt={`${dmc.name} logo`}
                                    style={{
                                      width: '100%',
                                      height: '100%',
                                      objectFit: 'cover',
                                      display: 'block',
                                    }}
                                    onError={(e) => {
                                      // Hide image and show fallback icon
                                      e.target.style.display = 'none';
                                      const fallback = e.target.nextElementSibling;
                                      if (fallback) {
                                        fallback.style.display = 'flex';
                                      }
                                    }}
                                    onLoad={(e) => {
                                      // Hide fallback when image loads successfully
                                      const fallback = e.target.nextElementSibling;
                                      if (fallback) {
                                        fallback.style.display = 'none';
                                      }
                                    }}
                                  />
                                  <Avatar
                                    sx={{
                                      display: 'none',
                                      bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                      width: { xs: 24, sm: 22, md: 24 },
                                      height: { xs: 24, sm: 22, md: 24 },
                                      background: isDMCSelected(dmc) 
                                        ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                        : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                    }}
                                  >
                                    <BusinessIcon sx={{ fontSize: { xs: 10, sm: 12, md: 14 } }} />
                                  </Avatar>
                                </Box>
                              ) : (
                                <Box sx={{ position: 'relative', flexShrink: 0 }}>
                                  <Avatar
                                    sx={{
                                      bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                      width: { xs: 28, sm: 26, md: 28 },
                                      height: { xs: 28, sm: 26, md: 28 },
                                      background: isDMCSelected(dmc) 
                                        ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                        : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                    }}
                                  >
                                    <BusinessIcon sx={{ fontSize: { xs: 12, sm: 14, md: 16 } }} />
                                  </Avatar>
                                </Box>
                              )}
                              <Box sx={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                                <Typography 
                                  variant="subtitle2" 
                                  fontWeight="600" 
                                  color={isDMCSelected(dmc) ? '#667eea' : '#333'}
                                  sx={{ 
                                    fontSize: { xs: '0.7rem', sm: '0.75rem', md: '0.8rem' },
                                    lineHeight: 1.2,
                                    textAlign: 'left',
                                    wordWrap: 'break-word',
                                  }}
                                >
                                  {dmc.name}
                                </Typography>
                               
                              </Box>
                            </Box>
                            
                            {/* Location Section - Under the logo and name */}
                            <Box display="flex" alignItems="center" justifyContent="center">
                              <Chip
                                icon={<LocationIcon sx={{ fontSize: { xs: 8, sm: 9, md: 10 } }} />}
                                label={dmc.location}
                                size="small"
                                sx={{
                                  backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#e8f5e8',
                                  color: isDMCSelected(dmc) ? 'white' : '#2e7d32',
                                  fontSize: { xs: '0.6rem', sm: '0.65rem', md: '0.7rem' },
                                  fontWeight: 600,
                                  height: { xs: '18px', sm: '18px', md: '20px' },
                                  border: isDMCSelected(dmc) ? 'none' : '1px solid #4caf50',
                                  '& .MuiChip-icon': {
                                    color: isDMCSelected(dmc) ? 'white' : '#2e7d32',
                                    fontSize: { xs: 8, sm: 9, md: 10 },
                                  },
                                  '& .MuiChip-label': {
                                    px: { xs: 0.5, sm: 0.75, md: 1 },
                                    fontSize: { xs: '0.55rem', sm: '0.6rem', md: '0.65rem' },
                                  },
                                  '&:hover': {
                                    backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#c8e6c9',
                                  },
                                }}
                              />
                            </Box>
                          </CardContent>
                        </DMCCard>
                      </Grid>
                    ))}
                  </Grid>
                )}
            </FormControl>

            {filteredDMCs.length === 0 && !dmcLoading && (
              <Box textAlign="center" py={6}>
                {dmcOptions.length === 0 ? (
                  // No DMCs available from API
                  <Box>
                    <Box
                      sx={{
                        width: 120,
                        height: 120,
                        borderRadius: '50%',
                        background: 'linear-gradient(135deg, #f3f4ff 0%, #e8eaf6 100%)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        margin: '0 auto 24px',
                        border: '3px solid #667eea',
                      }}
                    >
                      <InfoIcon sx={{ fontSize: 48, color: '#667eea' }} />
                    </Box>
                    <Typography variant="h5" color="#333" fontWeight="600" gutterBottom>
                      No DMCs Available
                    </Typography>
                    <Typography variant="body1" color="text.secondary" sx={{ mb: 3, maxWidth: 400, mx: 'auto' }}>
                      Currently, there are no destination management companies available in this region. 
                      Please try a different location or contact our support team for assistance.
                    </Typography>
                    <Box display="flex" gap={2} justifyContent="center" flexWrap="wrap">
                      <Chip
                        icon={<LocationIcon />}
                        label="Try Different Location"
                        variant="outlined"
                        sx={{
                          borderColor: '#667eea',
                          color: '#667eea',
                          '&:hover': {
                            backgroundColor: 'rgba(102, 126, 234, 0.04)',
                          },
                        }}
                      />
                      <Chip
                        icon={<TravelIcon />}
                        label="Contact Support"
                        variant="outlined"
                        sx={{
                          borderColor: '#667eea',
                          color: '#667eea',
                          '&:hover': {
                            backgroundColor: 'rgba(102, 126, 234, 0.04)',
                          },
                        }}
                      />
                    </Box>
                  </Box>
                ) : (
                  // DMCs exist but none match the filter
                  <Box>
                    <Box
                      sx={{
                        width: 100,
                        height: 100,
                        borderRadius: '50%',
                        background: 'linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        margin: '0 auto 24px',
                        border: '3px solid #ff9800',
                      }}
                    >
                      <SearchIcon sx={{ fontSize: 40, color: '#ff9800' }} />
                    </Box>
                    <Typography variant="h5" color="#333" fontWeight="600" gutterBottom>
                      No Matching DMCs
                    </Typography>
                    <Typography variant="body1" color="text.secondary" sx={{ mb: 3, maxWidth: 400, mx: 'auto' }}>
                      No DMCs match your current search criteria. Try adjusting your search terms or browse all available DMCs.
                    </Typography>
                    <Button
                      variant="outlined"
                      onClick={() => setFilterText('')}
                      sx={{
                        borderColor: '#667eea',
                        color: '#667eea',
                        textTransform: 'none',
                        fontWeight: 600,
                        '&:hover': {
                          borderColor: '#5a67d8',
                          backgroundColor: 'rgba(102, 126, 234, 0.04)',
                        },
                      }}
                    >
                      Clear Search
                    </Button>
                  </Box>
                )}
              </Box>
            )}
          </>
        )}
      </DialogContent>

      <Divider sx={{ borderColor: '#e0e0e0' }} />

      <DialogActions sx={{ 
        padding: { xs: '16px 20px', sm: '20px 24px', md: '24px 28px' }, 
        backgroundColor: 'linear-gradient(135deg, #fafbfc 0%, #f1f5f9 100%)',
        flexDirection: { xs: 'column', sm: 'row' },
        gap: { xs: 2, sm: 1.5 },
        borderTop: '1px solid rgba(102, 126, 234, 0.1)',
        '& > *': {
          margin: { xs: '0 !important', sm: '0 !important' },
          width: { xs: '100%', sm: 'auto' },
          minWidth: { xs: '100%', sm: '120px' },
        }
      }}>
        <Button 
          onClick={handleClose}
          variant="outlined"
          size="medium"
          sx={{ 
            borderRadius: { xs: '8px', sm: '10px', md: '12px' },
            textTransform: 'none',
            fontWeight: 600,
            borderColor: 'rgba(102, 126, 234, 0.3)',
            color: '#667eea',
            fontSize: { xs: '0.8rem', sm: '0.85rem', md: '0.9rem' },
            padding: { xs: '8px 16px', sm: '10px 18px', md: '12px 20px' },
            border: '1px solid',
            transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
            '&:hover': {
              borderColor: '#5a67d8',
              backgroundColor: 'rgba(102, 126, 234, 0.08)',
              transform: 'translateY(-1px)',
              boxShadow: '0 4px 16px rgba(102, 126, 234, 0.12)',
            },
          }}
        >
          Cancel
        </Button>
        <Button
          onClick={handleConfirm}
          variant="contained"
          size="medium"
          disabled={
            dmcLoading || 
            (multiSelect ? selectedDmcIds.length === 0 : !selectedDMC)
          }
          sx={{
            borderRadius: { xs: '8px', sm: '10px', md: '12px' },
            textTransform: 'none',
            fontWeight: 600,
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            boxShadow: '0 4px 16px rgba(102, 126, 234, 0.25), 0 2px 6px rgba(0,0,0,0.08)',
            fontSize: { xs: '0.8rem', sm: '0.85rem', md: '0.9rem' },
            padding: { xs: '8px 20px', sm: '10px 22px', md: '12px 24px' },
            border: 'none',
            transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
            '&:hover': {
              background: 'linear-gradient(135deg, #5a67d8 0%, #6b46a3 100%)',
              boxShadow: '0 6px 24px rgba(102, 126, 234, 0.3), 0 2px 8px rgba(0,0,0,0.12)',
              transform: 'translateY(-1px)',
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
          {multiSelect 
            ? `Continue with ${selectedDmcIds.length} DMC${selectedDmcIds.length !== 1 ? 's' : ''}`
            : 'Continue with Selection'
          }
        </Button>
      </DialogActions>
    </StyledDialog>
  );
};

export default DMCSelectionModal; 