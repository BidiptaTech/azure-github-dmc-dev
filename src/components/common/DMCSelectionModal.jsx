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
  height: { xs: '90px', sm: '95px', md: '100px' },
  width: '100%',
  borderRadius: { xs: '12px', sm: '14px', md: '16px' },
  border: selected ? '2px solid #667eea' : '1px solid #e8eaf6',
  background: selected 
    ? 'linear-gradient(135deg, #f8f9ff 0%, #e8eaf6 100%)' 
    : 'linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)',
  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
  cursor: 'pointer',
  position: 'relative',
  overflow: 'hidden',
  minHeight: { xs: '90px', sm: '95px', md: '100px' },
  maxHeight: { xs: '90px', sm: '95px', md: '100px' },
  boxShadow: selected 
    ? '0 8px 32px rgba(102, 126, 234, 0.25), 0 4px 16px rgba(0,0,0,0.1)'
    : '0 4px 20px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.04)',
  '&:hover': {
    transform: 'translateY(-4px) scale(1.02)',
    boxShadow: selected 
      ? '0 16px 48px rgba(102, 126, 234, 0.35), 0 8px 24px rgba(0,0,0,0.15)'
      : '0 12px 40px rgba(102, 126, 234, 0.2), 0 6px 20px rgba(0,0,0,0.1)',
    border: selected ? '3px solid #667eea' : '2px solid #667eea',
  },
  '&:active': {
    transform: 'translateY(-2px) scale(0.98)',
  },
}));

const SelectionBadge = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: { xs: '-8px', sm: '-8px', md: '-6px' },
  right: { xs: '-8px', sm: '-8px', md: '-6px' },
  backgroundColor: '#4caf50',
  borderRadius: '50%',
  width: { xs: '32px', sm: '32px', md: '28px' },
  height: { xs: '32px', sm: '32px', md: '28px' },
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  boxShadow: '0 4px 16px rgba(76, 175, 80, 0.4), 0 2px 8px rgba(0,0,0,0.1)',
  zIndex: 10,
  border: '3px solid white',
  backdropFilter: 'blur(10px)',
  animation: 'pulse 2s infinite',
  '@keyframes pulse': {
    '0%': {
      boxShadow: '0 4px 16px rgba(76, 175, 80, 0.4), 0 2px 8px rgba(0,0,0,0.1)',
    },
    '50%': {
      boxShadow: '0 4px 16px rgba(76, 175, 80, 0.6), 0 2px 8px rgba(0,0,0,0.1)',
    },
    '100%': {
      boxShadow: '0 4px 16px rgba(76, 175, 80, 0.4), 0 2px 8px rgba(0,0,0,0.1)',
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
                  <Grid container spacing={{ xs: 2, sm: 2.5, md: 3 }}>
                    {filteredDMCs.map((dmc) => (
                      <Grid item xs={12} sm={6} md={4} lg={3} xl={2.4} key={dmc.id}>
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
                                  <CheckIcon sx={{ color: 'white', fontSize: { xs: 18, sm: 18, md: 16 } }} />
                                </SelectionBadge>
                              )}
                              
                              <CardContent sx={{ 
                                padding: { xs: '8px', sm: '10px', md: '12px' } + ' !important', 
                                height: '100%', 
                                display: 'flex', 
                                flexDirection: 'column', 
                                justifyContent: 'space-between',
                                minHeight: { xs: '90px', sm: '95px', md: '100px' },
                                maxHeight: { xs: '90px', sm: '95px', md: '100px' },
                                overflow: 'hidden'
                              }}>
                                {/* Header Section with Logo and Name */}
                                <Box display="flex" alignItems="flex-start" gap={1} mb={1}>
                                  {/* Logo */}
                                  {dmc.logo && dmc.logo.trim() !== '' ? (
                                    <Box
                                      sx={{
                                        width: { xs: 32, sm: 34, md: 36 },
                                        height: { xs: 32, sm: 34, md: 36 },
                                        borderRadius: '8px',
                                        overflow: 'hidden',
                                        border: isDMCSelected(dmc) ? '2px solid #667eea' : '1px solid #e0e0e0',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        backgroundColor: 'white',
                                        position: 'relative',
                                        flexShrink: 0,
                                        boxShadow: '0 1px 4px rgba(0,0,0,0.1)',
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
                                          e.target.style.display = 'none';
                                          const fallback = e.target.nextElementSibling;
                                          if (fallback) {
                                            fallback.style.display = 'flex';
                                          }
                                        }}
                                        onLoad={(e) => {
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
                                          width: '100%',
                                          height: '100%',
                                          background: isDMCSelected(dmc) 
                                            ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                            : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                        }}
                                      >
                                        <BusinessIcon sx={{ fontSize: { xs: 14, sm: 16, md: 18 } }} />
                                      </Avatar>
                                    </Box>
                                  ) : (
                                    <Box sx={{ position: 'relative', flexShrink: 0 }}>
                                      <Avatar
                                        sx={{
                                          bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                          width: { xs: 32, sm: 34, md: 36 },
                                          height: { xs: 32, sm: 34, md: 36 },
                                          borderRadius: '8px',
                                          background: isDMCSelected(dmc) 
                                            ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                            : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                          boxShadow: '0 1px 4px rgba(0,0,0,0.1)',
                                        }}
                                      >
                                        <BusinessIcon sx={{ fontSize: { xs: 14, sm: 16, md: 18 } }} />
                                      </Avatar>
                                    </Box>
                                  )}
                                  
                                  {/* Company Name */}
                                  <Box sx={{ flex: 1, minWidth: 0 }}>
                                    <Typography 
                                      variant="subtitle1" 
                                      fontWeight="700" 
                                      color={isDMCSelected(dmc) ? '#667eea' : '#1a1a1a'}
                                      sx={{ 
                                        fontSize: { xs: '0.75rem', sm: '0.8rem', md: '0.85rem' },
                                        lineHeight: 1.1,
                                        textAlign: 'left',
                                        wordWrap: 'break-word',
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
                                        display: '-webkit-box',
                                        WebkitLineClamp: 2,
                                        WebkitBoxOrient: 'vertical',
                                      }}
                                    >
                                      {dmc.name}
                                    </Typography>
                                  </Box>
                                </Box>
                                
                                {/* Location Section */}
                                <Box display="flex" alignItems="center" justifyContent="flex-start">
                                  <Chip
                                    icon={<LocationIcon sx={{ fontSize: { xs: 10, sm: 11, md: 12 } }} />}
                                    label={dmc.location}
                                    size="small"
                                    sx={{
                                      backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#f0f9ff',
                                      color: isDMCSelected(dmc) ? 'white' : '#0369a1',
                                      fontSize: { xs: '0.65rem', sm: '0.7rem', md: '0.75rem' },
                                      fontWeight: 600,
                                      height: { xs: '20px', sm: '22px', md: '24px' },
                                      borderRadius: '10px',
                                      border: isDMCSelected(dmc) ? 'none' : '1px solid #0ea5e9',
                                      '& .MuiChip-icon': {
                                        color: isDMCSelected(dmc) ? 'white' : '#0369a1',
                                        fontSize: { xs: 10, sm: 11, md: 12 },
                                      },
                                      '& .MuiChip-label': {
                                        px: { xs: 0.75, sm: 1, md: 1.25 },
                                        fontSize: { xs: '0.65rem', sm: '0.7rem', md: '0.75rem' },
                                      },
                                      '&:hover': {
                                        backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#e0f2fe',
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
                  <Grid container spacing={{ xs: 2, sm: 2.5, md: 3 }}>
                    {filteredDMCs.map((dmc) => (
                      <Grid item xs={12} sm={6} md={4} lg={3} xl={2.4} key={dmc.id}>
                        <DMCCard 
                          selected={isDMCSelected(dmc)} 
                          elevation={isDMCSelected(dmc) ? 8 : 2}
                          onClick={(e) => handleDMCCardClick(dmc.id, e)}
                        >
                          {isDMCSelected(dmc) && (
                            <SelectionBadge>
                              <CheckIcon sx={{ color: 'white', fontSize: { xs: 18, sm: 18, md: 16 } }} />
                            </SelectionBadge>
                          )}
                          
                          <CardContent sx={{ 
                            padding: { xs: '8px', sm: '10px', md: '12px' } + ' !important', 
                            height: '100%', 
                            display: 'flex', 
                            flexDirection: 'column', 
                            justifyContent: 'space-between',
                            minHeight: { xs: '90px', sm: '95px', md: '100px' },
                            maxHeight: { xs: '90px', sm: '95px', md: '100px' },
                            overflow: 'hidden'
                          }}>
                            {/* Header Section with Logo and Name */}
                            <Box display="flex" alignItems="flex-start" gap={1} mb={1}>
                              {/* Logo */}
                              {dmc.logo && dmc.logo.trim() !== '' ? (
                                <Box
                                  sx={{
                                    width: { xs: 32, sm: 34, md: 36 },
                                    height: { xs: 32, sm: 34, md: 36 },
                                    borderRadius: '8px',
                                    overflow: 'hidden',
                                    border: isDMCSelected(dmc) ? '2px solid #667eea' : '1px solid #e0e0e0',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    backgroundColor: 'white',
                                    position: 'relative',
                                    flexShrink: 0,
                                    boxShadow: '0 1px 4px rgba(0,0,0,0.1)',
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
                                      e.target.style.display = 'none';
                                      const fallback = e.target.nextElementSibling;
                                      if (fallback) {
                                        fallback.style.display = 'flex';
                                      }
                                    }}
                                    onLoad={(e) => {
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
                                      width: '100%',
                                      height: '100%',
                                      background: isDMCSelected(dmc) 
                                        ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                        : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                    }}
                                  >
                                    <BusinessIcon sx={{ fontSize: { xs: 14, sm: 16, md: 18 } }} />
                                  </Avatar>
                                </Box>
                              ) : (
                                <Box sx={{ position: 'relative', flexShrink: 0 }}>
                                  <Avatar
                                    sx={{
                                      bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                      width: { xs: 32, sm: 34, md: 36 },
                                      height: { xs: 32, sm: 34, md: 36 },
                                      borderRadius: '8px',
                                      background: isDMCSelected(dmc) 
                                        ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                        : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                      boxShadow: '0 1px 4px rgba(0,0,0,0.1)',
                                    }}
                                  >
                                    <BusinessIcon sx={{ fontSize: { xs: 14, sm: 16, md: 18 } }} />
                                  </Avatar>
                                </Box>
                              )}
                              
                              {/* Company Name */}
                              <Box sx={{ flex: 1, minWidth: 0 }}>
                                <Typography 
                                  variant="subtitle1" 
                                  fontWeight="700" 
                                  color={isDMCSelected(dmc) ? '#667eea' : '#1a1a1a'}
                                  sx={{ 
                                    fontSize: { xs: '0.75rem', sm: '0.8rem', md: '0.85rem' },
                                    lineHeight: 1.1,
                                    textAlign: 'left',
                                    wordWrap: 'break-word',
                                    overflow: 'hidden',
                                    textOverflow: 'ellipsis',
                                    display: '-webkit-box',
                                    WebkitLineClamp: 2,
                                    WebkitBoxOrient: 'vertical',
                                  }}
                                >
                                  {dmc.name}
                                </Typography>
                              </Box>
                            </Box>
                            
                            {/* Location Section */}
                            <Box display="flex" alignItems="center" justifyContent="flex-start">
                              <Chip
                                icon={<LocationIcon sx={{ fontSize: { xs: 10, sm: 11, md: 12 } }} />}
                                label={dmc.location}
                                size="small"
                                sx={{
                                  backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#f0f9ff',
                                  color: isDMCSelected(dmc) ? 'white' : '#0369a1',
                                  fontSize: { xs: '0.65rem', sm: '0.7rem', md: '0.75rem' },
                                  fontWeight: 600,
                                  height: { xs: '20px', sm: '22px', md: '24px' },
                                  borderRadius: '10px',
                                  border: isDMCSelected(dmc) ? 'none' : '1px solid #0ea5e9',
                                  '& .MuiChip-icon': {
                                    color: isDMCSelected(dmc) ? 'white' : '#0369a1',
                                    fontSize: { xs: 10, sm: 11, md: 12 },
                                  },
                                  '& .MuiChip-label': {
                                    px: { xs: 0.75, sm: 1, md: 1.25 },
                                    fontSize: { xs: '0.65rem', sm: '0.7rem', md: '0.75rem' },
                                  },
                                  '&:hover': {
                                    backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#e0f2fe',
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