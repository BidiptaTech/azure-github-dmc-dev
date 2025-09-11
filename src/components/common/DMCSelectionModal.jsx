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

  // Console logging for testing current state
  // React.useEffect(() => {
  //   if (open) {
  //     console.log('🔍 DMC Modal: Current Redux State:');
  //     console.log('🔍 DMC Modal: Selected DMC Logo:', selectedDmcLogo);
  //     console.log('🔍 DMC Modal: Selected DMC Company Name:', selectedDmcCompanyName);
  //     console.log('🔍 DMC Modal: Available DMC Options:', dmcOptions);
  //   }
  // }, [open, selectedDmcLogo, selectedDmcCompanyName, dmcOptions]);

  // Single selection handlers (for Book Tour)
  const handleSelectionChange = (event) => {
    const uiId = event.target.value;
    setSelectedDMC(uiId);
    
    // Find the selected DMC data
    const selectedDmcData = dmcOptions.find(dmc => dmc.id.toString() === uiId);
    
    // console.log('📝 Radio button clicked - UI ID:', uiId);
    // console.log('📝 API userId field:', selectedDmcData?.originalData?.userId);
    // console.log('📝 Using userId as dmcId:', selectedDmcData?.dmcId);
    // console.log('📝 Selected DMC Data:', selectedDmcData);
    
    // Dispatch to Redux store with actual dmcId (can be null)
    dispatch(setSelectedDmcId({
      dmcId: selectedDmcData?.dmcId, // This will be null if original dmcId was 0
      dmcData: selectedDmcData
    }));
    
    // console.log('✅ DMC ID dispatched to Redux store');
  };

  const handleDMCCardClick = (uiId) => {
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
      
      // console.log('🎯 DMC Card clicked - UI ID:', uiId);
      // console.log('🎯 API userId field:', selectedDmcData?.originalData?.userId);
      // console.log('🎯 Using userId as dmcId:', selectedDmcData?.dmcId);
      // console.log('🎯 Selected DMC Data:', selectedDmcData);
      
      // Dispatch to Redux store with actual dmcId (can be null)
      dispatch(setSelectedDmcId({
        dmcId: selectedDmcData?.dmcId, // This will be null if original dmcId was 0
        dmcData: selectedDmcData
      }));
      
      // console.log('✅ DMC ID dispatched to Redux store via card click');
    }
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
    >
      <StyledDialogTitle>
        <Box display="flex" alignItems="center">
          <TravelIcon sx={{ mr: 2, fontSize: 32 }} />
          <Box>
            <Typography variant="h4" component="div" fontWeight="bold" sx={{ fontSize: '1.5rem' }}>
              {multiSelect ? 'Choose Your DMC Partners' : 'Choose Your DMC Partner'}
            </Typography>
            <Typography variant="body2" sx={{ color: 'white', mt: 0.5 }}>
              {multiSelect 
                ? 'Select multiple destination management companies for your enquiry' 
                : 'Select the perfect destination management company for your journey'
              }
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
                {/* {multiSelect && selectedDmcIds.length > 0 && (
                  <Badge 
                    badgeContent={selectedDmcIds.length} 
                    color="success" 
                    sx={{ ml: 2 }}
                  >
                    <Chip 
                      label="Selected" 
                      sx={{ 
                        fontWeight: 'bold', 
                        backgroundColor: '#4caf50', 
                        color: 'white' 
                      }} 
                    />
                  </Badge>
                )} */}
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
              {!multiSelect ? (
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
                              selected={isDMCSelected(dmc)} 
                              elevation={isDMCSelected(dmc) ? 8 : 2}
                              onClick={() => handleDMCCardClick(dmc.id)}
                            >
                              {isDMCSelected(dmc) && (
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
                                        width: 32,
                                        height: 32,
                                        borderRadius: '50%',
                                        overflow: 'hidden',
                                        border: isDMCSelected(dmc) ? '2px solid #667eea' : '2px solid #90a4ae',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        backgroundColor: 'white',
                                        position: 'relative',
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
                                          bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                          width: 28,
                                          height: 28,
                                          background: isDMCSelected(dmc) 
                                            ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                            : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                        }}
                                      >
                                        <BusinessIcon sx={{ fontSize: 14 }} />
                                      </Avatar>
                                    </Box>
                                  ) : (
                                    <Box sx={{ position: 'relative' }}>
                                      <Avatar
                                        sx={{
                                          bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                          width: 32,
                                          height: 32,
                                          background: isDMCSelected(dmc) 
                                            ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                            : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                        }}
                                      >
                                        <BusinessIcon sx={{ fontSize: 16 }} />
                                      </Avatar>
                                    </Box>
                                  )}
                                  <Box sx={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                                    <Typography 
                                      variant="subtitle2" 
                                      fontWeight="700" 
                                      color={isDMCSelected(dmc) ? '#667eea' : '#333'}
                                      sx={{ 
                                        fontSize: '0.8rem',
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
                                    icon={<LocationIcon sx={{ fontSize: 12 }} />}
                                    label={dmc.location}
                                    size="small"
                                    sx={{
                                      backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#e8f5e8',
                                      color: isDMCSelected(dmc) ? 'white' : '#2e7d32',
                                      fontSize: '0.68rem',
                                      fontWeight: 600,
                                      height: '22px',
                                      border: isDMCSelected(dmc) ? 'none' : '1px solid #4caf50',
                                      '& .MuiChip-icon': {
                                        color: isDMCSelected(dmc) ? 'white' : '#2e7d32',
                                        fontSize: 12,
                                      },
                                      '& .MuiChip-label': {
                                        px: 1,
                                        fontSize: '0.68rem',
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
                  <Grid container spacing={3}>
                    {filteredDMCs.map((dmc) => (
                      <Grid item xs={12} sm={6} md={4} lg={2} xl={1.5} key={dmc.id}>
                        <DMCCard 
                          selected={isDMCSelected(dmc)} 
                          elevation={isDMCSelected(dmc) ? 8 : 2}
                          onClick={() => handleDMCCardClick(dmc.id)}
                        >
                          {isDMCSelected(dmc) && (
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
                                    width: 32,
                                    height: 32,
                                    borderRadius: '50%',
                                    overflow: 'hidden',
                                    border: isDMCSelected(dmc) ? '2px solid #667eea' : '2px solid #90a4ae',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    backgroundColor: 'white',
                                    position: 'relative',
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
                                      bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                      width: 28,
                                      height: 28,
                                      background: isDMCSelected(dmc) 
                                        ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                        : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                    }}
                                  >
                                    <BusinessIcon sx={{ fontSize: 14 }} />
                                  </Avatar>
                                </Box>
                              ) : (
                                <Box sx={{ position: 'relative' }}>
                                  <Avatar
                                    sx={{
                                      bgcolor: isDMCSelected(dmc) ? '#667eea' : '#90a4ae',
                                      width: 32,
                                      height: 32,
                                      background: isDMCSelected(dmc) 
                                        ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                        : 'linear-gradient(135deg, #90a4ae 0%, #78909c 100%)',
                                    }}
                                  >
                                    <BusinessIcon sx={{ fontSize: 16 }} />
                                  </Avatar>
                                </Box>
                              )}
                              <Box sx={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 0.5 }}>
                                <Typography 
                                  variant="subtitle2" 
                                  fontWeight="700" 
                                  color={isDMCSelected(dmc) ? '#667eea' : '#333'}
                                  sx={{ 
                                    fontSize: '0.8rem',
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
                                icon={<LocationIcon sx={{ fontSize: 12 }} />}
                                label={dmc.location}
                                size="small"
                                sx={{
                                  backgroundColor: isDMCSelected(dmc) ? '#667eea' : '#e8f5e8',
                                  color: isDMCSelected(dmc) ? 'white' : '#2e7d32',
                                  fontSize: '0.68rem',
                                  fontWeight: 600,
                                  height: '22px',
                                  border: isDMCSelected(dmc) ? 'none' : '1px solid #4caf50',
                                  '& .MuiChip-icon': {
                                    color: isDMCSelected(dmc) ? 'white' : '#2e7d32',
                                    fontSize: 12,
                                  },
                                  '& .MuiChip-label': {
                                    px: 1,
                                    fontSize: '0.68rem',
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
          disabled={
            dmcLoading || 
            (multiSelect ? selectedDmcIds.length === 0 : !selectedDMC)
          }
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