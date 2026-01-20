import React, { useState, useEffect } from 'react';
import {
  Box,
  Typography,
  TextField,
  Paper,
  Card,
  CardContent,
  Avatar,
  CircularProgress,
  Chip,
  Badge,
} from '@mui/material';
import { styled } from '@mui/material/styles';
import {
  LocationOn as LocationIcon,
  Search as SearchIcon,
  Info as InfoIcon,
  CheckCircle as CheckCircleIcon,
  Business as BusinessIcon,
  TravelExplore as TravelIcon,
} from '@mui/icons-material';
import { useDispatch, useSelector } from 'react-redux';
import {
  fetchDMCsByCountry,
  addDmcToSelection,
  removeDmcFromSelection,
  selectDMCs,
  selectDMCLoading,
  selectDMCError,
  selectSelectedDmcIds,
  selectSelectedDmcsData,
} from '../../slice/dmc/dmcSlice';

// Styled components
const DMCSelectionPanel = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(3),
  height: 'fit-content',
  maxHeight: '80vh',
  overflowY: 'auto',
  position: 'sticky',
  top: theme.spacing(2),
  background: 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
  border: '1px solid rgba(102, 126, 234, 0.1)',
  borderRadius: theme.spacing(2),
  boxShadow: '0 8px 32px rgba(102, 126, 234, 0.1)',
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
}));

const DMCCard = styled(Card)(({ theme, selected }) => ({
  marginBottom: theme.spacing(1.5),
  border: selected ? '2px solid #667eea' : '1px solid #e0e3e8',
  backgroundColor: selected ? 'rgba(102, 126, 234, 0.05)' : 'white',
  cursor: 'pointer',
  transition: 'all 0.3s ease',
  borderRadius: theme.spacing(1.5),
  '&:hover': {
    boxShadow: '0 4px 12px rgba(102, 126, 234, 0.15)',
    transform: 'translateY(-2px)',
    border: '2px solid #667eea',
  },
}));

const DMCSelectionComponent = ({ 
  mode = 'full', // 'full' for selection mode, 'summary' for display only
  activeLocation = null,
  showLocationSection = true 
}) => {
  const dispatch = useDispatch();
  
  // Redux selectors
  const apiDMCs = useSelector(selectDMCs);
  const dmcLoading = useSelector(selectDMCLoading);
  const dmcError = useSelector(selectDMCError);
  const selectedDmcIds = useSelector(selectSelectedDmcIds);
  const selectedDmcsData = useSelector(selectSelectedDmcsData);
  const enquiryListLoading = useSelector((state) => state.enquiryList.loading);
  
  // Local state
  const [dmcOptions, setDmcOptions] = useState([]);
  const [filterText, setFilterText] = useState('');

  // Process DMC data for display
  useEffect(() => {
    if (apiDMCs && apiDMCs.data && Array.isArray(apiDMCs.data)) {
      const processedDMCs = apiDMCs.data.map((dmc, index) => ({
        id: `dmc-${index}`,
        dmcId: dmc.userId || null,
        name: dmc.company_name || `DMC ${index + 1}`,
        location: dmc.country || 'Unknown Location',
        logo: dmc.logo || '',
        description: 'Professional destination management services',
        originalData: dmc,
      }));
      
      setDmcOptions(processedDMCs);
    } else {
      setDmcOptions([]);
    }
  }, [apiDMCs]);

  // DMC Selection Handlers
  const handleDMCCardClick = (dmc) => {
    if (mode === 'summary') return; // Don't allow selection in summary mode
    
    console.log('🏢 DMC card clicked:', dmc);
    const isSelected = selectedDmcIds.includes(dmc.dmcId);
    
    if (isSelected) {
      dispatch(removeDmcFromSelection({ dmcId: dmc.dmcId }));
    } else {
      dispatch(addDmcToSelection({ dmcId: dmc.dmcId, dmcData: dmc }));
    }
  };

  const handleFilterChange = (event) => {
    setFilterText(event.target.value);
  };

  // Filter DMCs based on search text
  const filteredDMCs = dmcOptions.filter(dmc => 
    dmc.name.toLowerCase().includes(filterText.toLowerCase()) ||
    dmc.location.toLowerCase().includes(filterText.toLowerCase())
  );

  // Check if a DMC is selected
  const isDMCSelected = (dmc) => {
    return selectedDmcIds.includes(dmc.dmcId);
  };

  // Render summary mode (only selected DMCs)
  if (mode === 'summary') {
    return (
      <DMCSelectionPanel elevation={3}>
        <Box sx={{ mb: 2 }}>
          <Typography variant="subtitle2" sx={{ mb: 2, fontWeight: 700, color: '#333', fontSize: '0.8rem' }}>
            🏢 Selected DMC Partners
          </Typography>
          
          {selectedDmcsData.length === 0 ? (
            <Box sx={{ 
              p: 2, 
              bgcolor: 'rgba(255, 152, 0, 0.08)', 
              borderRadius: 2, 
              border: '1px solid rgba(255, 152, 0, 0.2)',
              display: 'flex',
              alignItems: 'center'
            }}>
              <InfoIcon sx={{ fontSize: 18, color: '#ff9800', mr: 1 }} />
              <Typography variant="body2" sx={{ color: '#666', fontSize: '0.875rem' }}>
                No DMCs selected for this enquiry
              </Typography>
            </Box>
          ) : (
            <Box>
              {selectedDmcsData.map((dmc) => (
                <DMCCard
                  key={dmc.id}
                  selected={true}
                  sx={{ cursor: 'default', '&:hover': { transform: 'none' } }}
                >
                  <CardContent sx={{ p: 1.5 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                      {dmc.logo ? (
                        <Avatar
                          src={dmc.logo}
                          alt={dmc.name}
                          sx={{ width: 32, height: 32, mr: 1.5 }}
                        >
                          <BusinessIcon fontSize="small" />
                        </Avatar>
                      ) : (
                        <Avatar sx={{ width: 32, height: 32, mr: 1.5, bgcolor: '#667eea' }}>
                          <BusinessIcon fontSize="small" />
                        </Avatar>
                      )}
                      
                      <Box sx={{ flex: 1, minWidth: 0 }}>
                        <Typography variant="subtitle2" sx={{ 
                          fontWeight: 600, 
                          color: '#667eea',
                          overflow: 'hidden',
                          textOverflow: 'ellipsis',
                          whiteSpace: 'nowrap',
                          fontSize: '0.875rem',
                          lineHeight: 1.2
                        }}>
                          {dmc.name}
                        </Typography>
                        <Typography variant="body2" color="text.secondary" sx={{ 
                          fontSize: '0.75rem',
                          overflow: 'hidden',
                          textOverflow: 'ellipsis',
                          whiteSpace: 'nowrap',
                          lineHeight: 1.2
                        }}>
                          📍 {dmc.location}
                        </Typography>
                      </Box>
                      
                      <CheckCircleIcon sx={{ color: '#4caf50', fontSize: 18 }} />
                    </Box>
                  </CardContent>
                </DMCCard>
              ))}
            </Box>
          )}
        </Box>
      </DMCSelectionPanel>
    );
  }

  // Render full selection mode
  return (
    <DMCSelectionPanel elevation={3}>
      {/* Location Section */}
      {showLocationSection && (
        <Box sx={{ mb: 3 }}>
          <Typography variant="subtitle2" sx={{ mb: 2, fontWeight: 600, color: '#333', fontSize: '0.9rem' }}>
            📍 Destination
          </Typography>
          
          {activeLocation ? (
            <Box sx={{ 
              p: 2, 
              bgcolor: 'rgba(102, 126, 234, 0.08)', 
              borderRadius: 2, 
              border: '1px solid rgba(102, 126, 234, 0.2)',
              display: 'flex',
              alignItems: 'center'
            }}>
              <LocationIcon sx={{ fontSize: 18, color: '#667eea', mr: 1 }} />
              <Box>
                <Typography variant="body2" sx={{ fontWeight: 500, color: '#333' }}>
                  {activeLocation.country || activeLocation.countryName || 'Location Selected'}
                </Typography>
              </Box>
            </Box>
          ) : (
            <Box sx={{ 
              p: 2, 
              bgcolor: 'rgba(255, 152, 0, 0.08)', 
              borderRadius: 2, 
              border: '1px solid rgba(255, 152, 0, 0.2)',
              display: 'flex',
              alignItems: 'center'
            }}>
              <InfoIcon sx={{ fontSize: 18, color: '#ff9800', mr: 1 }} />
              <Typography variant="body2" sx={{ color: '#666', fontSize: '0.875rem' }}>
                Please select a location from the search form first
              </Typography>
            </Box>
          )}
        </Box>
      )}

      {/* DMC Filter & Selection */}
      <Box sx={{ mb: 3 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
          <Typography variant="subtitle2" sx={{ fontWeight: 600, color: '#333', fontSize: '0.9rem' }}>
            🏢 DMC Partners
          </Typography>
          {selectedDmcsData.length > 0 && (
            <Badge badgeContent={selectedDmcsData.length} color="primary" />
          )}
        </Box>
        
        {activeLocation && (
          <TextField
            fullWidth
            size="small"
            placeholder="Search DMCs..."
            value={filterText}
            onChange={handleFilterChange}
            InputProps={{
              startAdornment: <SearchIcon sx={{ color: '#999', mr: 0.5, fontSize: '1.2rem' }} />
            }}
            sx={{ 
              mb: 2,
              '& .MuiOutlinedInput-input': {
                fontSize: '0.875rem'
              }
            }}
          />
        )}
      </Box>

      {/* DMC List */}
      <Box sx={{ maxHeight: '400px', overflowY: 'auto' }}>
        {dmcLoading && (
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', py: 3 }}>
            <CircularProgress size={20} sx={{ mr: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              Loading DMCs...
            </Typography>
          </Box>
        )}

        {dmcError && (
          <Box sx={{ p: 2, bgcolor: '#fff3e0', borderRadius: 1, mb: 2, border: '1px solid #ffb74d' }}>
            <Typography variant="body2" color="error" sx={{ fontSize: '0.875rem' }}>
              Error: {dmcError}
            </Typography>
          </Box>
        )}

        {!activeLocation && !dmcLoading && (
          <Box sx={{ textAlign: 'center', py: 3 }}>
            <TravelIcon sx={{ fontSize: 36, color: '#ddd', mb: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              Select a destination first
            </Typography>
          </Box>
        )}

        {activeLocation && !dmcLoading && filteredDMCs.length === 0 && dmcOptions.length === 0 && (
          <Box sx={{ textAlign: 'center', py: 3 }}>
            <InfoIcon sx={{ fontSize: 36, color: '#ddd', mb: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              No DMCs available
            </Typography>
          </Box>
        )}

        {filteredDMCs.length === 0 && dmcOptions.length > 0 && (
          <Box sx={{ textAlign: 'center', py: 3 }}>
            <SearchIcon sx={{ fontSize: 36, color: '#ddd', mb: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              No matches found
            </Typography>
          </Box>
        )}

        {filteredDMCs.map((dmc) => (
          <DMCCard
            key={dmc.id}
            selected={isDMCSelected(dmc)}
            onClick={() => handleDMCCardClick(dmc)}
          >
            <CardContent sx={{ p: 1.5 }}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                {dmc.logo ? (
                  <Avatar
                    src={dmc.logo}
                    alt={dmc.name}
                    sx={{ width: 32, height: 32, mr: 1.5 }}
                  >
                    <BusinessIcon fontSize="small" />
                  </Avatar>
                ) : (
                  <Avatar sx={{ width: 32, height: 32, mr: 1.5, bgcolor: '#667eea' }}>
                    <BusinessIcon fontSize="small" />
                  </Avatar>
                )}
                
                <Box sx={{ flex: 1, minWidth: 0 }}>
                  <Typography variant="subtitle2" sx={{ 
                    fontWeight: 600, 
                    color: isDMCSelected(dmc) ? '#667eea' : '#333',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    whiteSpace: 'nowrap',
                    fontSize: '0.875rem',
                    lineHeight: 1.2
                  }}>
                    {dmc.name}
                  </Typography>
                  <Typography variant="body2" color="text.secondary" sx={{ 
                    fontSize: '0.75rem',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    whiteSpace: 'nowrap',
                    lineHeight: 1.2
                  }}>
                    📍 {dmc.location}
                  </Typography>
                </Box>
                
                {isDMCSelected(dmc) && (
                  <CheckCircleIcon sx={{ color: '#4caf50', fontSize: 18 }} />
                )}
              </Box>
            </CardContent>
          </DMCCard>
        ))}
      </Box>

      {/* Selected Summary */}
      {selectedDmcsData.length > 0 && (
        <Box sx={{ mt: 3, pt: 2, borderTop: '1px solid #e0e3e8' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1 }}>
            <Typography variant="subtitle2" sx={{ fontWeight: 600, color: '#333', fontSize: '0.9rem' }}>
              ✅ Selected ({selectedDmcsData.length})
            </Typography>
            {enquiryListLoading && (
              <CircularProgress size={12} sx={{ color: '#667eea' }} />
            )}
          </Box>
          {enquiryListLoading && (
            <Typography variant="caption" sx={{ color: '#667eea', fontSize: '0.7rem', mb: 1, display: 'block' }}>
              Updating data...
            </Typography>
          )}
          <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
            {selectedDmcsData.slice(0, 3).map((dmc) => (
              <Chip
                key={dmc.id}
                label={dmc.name}
                size="small"
                color="primary"
                variant="outlined"
                sx={{ fontSize: '0.75rem' }}
              />
            ))}
            {selectedDmcsData.length > 3 && (
              <Chip
                label={`+${selectedDmcsData.length - 3} more`}
                size="small"
                color="primary"
                sx={{ fontSize: '0.75rem' }}
              />
            )}
          </Box>
        </Box>
      )}
    </DMCSelectionPanel>
  );
};

export default DMCSelectionComponent;

