import React, { useCallback, useMemo, useRef } from 'react';
import { 
  Grid, 
  Autocomplete, 
  TextField, 
  Tooltip, 
  Box,
  Typography,
  Card,
  CardMedia,
  CardContent,
  Stack,
  Divider,
  styled,
  IconButton,
  Chip,
  Paper
} from '@mui/material';
import { useDispatch, useSelector } from 'react-redux';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import WorkIcon from '@mui/icons-material/Work';
import TranslateIcon from '@mui/icons-material/Translate';
import BadgeIcon from '@mui/icons-material/Badge';
import VerifiedIcon from '@mui/icons-material/Verified';
import CategoryIcon from '@mui/icons-material/Category';
import { fetchGuideDetails } from '../../../slice/tourguide/guideslice';

// Custom styled tooltip
const CustomTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    backgroundColor: 'white',
    color: 'rgba(0, 0, 0, 0.87)',
    maxWidth: 400,
    border: '1px solid #dadde9',
    borderRadius: '12px',
    padding: 0,
    boxShadow: theme.shadows[3]
  },
}));

// Tooltip content component
const TooltipContent = ({ guide }) => {
  return (
    <Box>
      {/* Header Image Section */}
      <Box sx={{ position: 'relative', width: '100%', height: 200 }}>
        <Box
          component="img"
          src={guide.guide_image}
          alt={guide.guide_name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '12px',
            borderTopRightRadius: '12px',
          }}
        />
        {guide.license_image && (
          <Box
            sx={{
              position: 'absolute',
              top: 8,
              right: 8,
              bgcolor: 'rgba(0, 0, 0, 0.6)',
              color: 'white',
              px: 1,
              py: 0.5,
              borderRadius: 1,
              fontSize: '0.75rem',
              display: 'flex',
              alignItems: 'center',
              gap: 0.5
            }}
          >
            <VerifiedIcon sx={{ fontSize: 16 }} />
            Licensed Guide
          </Box>
        )}
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 2 }}>
        {/* Title and Location */}
        <Typography variant="h6" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1.1rem' }}>
          {guide.guide_name}
        </Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
          <LocationOnIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 0.5 }} />
          <Typography variant="body2" color="text.secondary">
            {guide.city}, {guide.country}
          </Typography>
        </Box>

        {/* Experience and Service Type */}
        <Box sx={{ mb: 2 }}>
          <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap sx={{ mb: 1 }}>
            <Chip
              key="experience"
              size="small"
              icon={<WorkIcon />}
              label={`${guide.experience_years} years experience`}
              sx={{
                bgcolor: 'rgba(25, 118, 210, 0.08)',
                color: 'primary.main',
                height: 24
              }}
            />
            <Chip
              key="service-type"
              size="small"
              icon={<CategoryIcon />}
              label={guide.service_type === 1 ? 'General Guide' : 'Specialized Guide'}
              sx={{
                bgcolor: 'rgba(76, 175, 80, 0.08)',
                color: '#2e7d32',
                height: 24
              }}
            />
          </Stack>
        </Box>

        {/* Languages */}
        {guide.languages && guide.languages.length > 0 && (
          <Box sx={{ mb: 2 }}>
            <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
              Languages
            </Typography>
            <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap>
              {guide.languages.map((lang, index) => (
                <Chip
                  key={`${lang.language}-${index}`}
                  size="small"
                  icon={<TranslateIcon sx={{ fontSize: 16 }} />}
                  label={`${lang.language} (${lang.proficiency})`}
                  sx={{
                    bgcolor: 'rgba(25, 118, 210, 0.08)',
                    color: 'primary.main',
                    fontSize: '0.75rem',
                    height: 24
                  }}
                />
              ))}
            </Stack>
          </Box>
        )}

        {/* Description */}
        {/* {guide.description && (
          <Box sx={{ mb: 2 }}>
            <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
              About
            </Typography>
            <Typography 
              variant="body2" 
              color="text.secondary"
              sx={{ 
                fontSize: '0.875rem',
                lineHeight: 1.5,
                // Remove HTML tags for display
                '& p': { margin: 0 }
              }}
              dangerouslySetInnerHTML={{ __html: guide.description }}
            />
          </Box>
        )} */}

        {/* Pricing Section */}
        <Box sx={{ mt: 2 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
            Pricing Details
          </Typography>
          <Grid container spacing={2}>
            {/* DMC Prices */}
            {guide.dmc_day_rate > 0 && (
              <Grid item xs={6} key="dmc-price">
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1.5,
                    bgcolor: 'rgba(25, 118, 210, 0.02)',
                    borderColor: 'rgba(25, 118, 210, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: 'primary.main', fontWeight: 500 }}>
                    DMC Rate
                  </Typography>
                  <Typography variant="body2">
                    ${guide.dmc_day_rate}/day
                  </Typography>
                  <Typography variant="caption" color="text.secondary">
                    Provider: {guide.dmc_user_name}
                  </Typography>
                </Paper>
              </Grid>
            )}

            {/* Travclicks Prices */}
            {guide.travClicks_day_rate > 0 && (
              <Grid item xs={6} key="travclicks-price">
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1.5,
                    bgcolor: 'rgba(76, 175, 80, 0.02)',
                    borderColor: 'rgba(76, 175, 80, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: '#2e7d32', fontWeight: 500 }}>
                    Travclicks Rate
                  </Typography>
                  <Typography variant="body2">
                    ${guide.travClicks_day_rate}/day
                  </Typography>
                </Paper>
              </Grid>
            )}
          </Grid>

          {/* Tax Information */}
          {guide.tax_percentage && (
            <Typography 
              variant="caption" 
              sx={{ 
                display: 'block',
                mt: 1,
                color: 'text.secondary',
                fontStyle: 'italic'
              }}
            >
              *Prices are subject to {guide.tax_percentage}% tax
            </Typography>
          )}
        </Box>
      </Box>
    </Box>
  );
};

const GuideListing = ({ value, onChange, disabled }) => {
  const dispatch = useDispatch();
  const guides = useSelector((state) => state.tourguide.Guides) || [];
  const searchParams = useSelector((state) => state.tourguide.searchParams);
  const prevGuideIdRef = useRef(null);
  
  console.log('GuideListing debug:', value);
  console.log('GuideListing - Received value details:', {
    value: value,
    valueType: typeof value,
    isNull: value === null,
    isUndefined: value === undefined,
    isEmpty: value === '',
    availableGuides: guides.length,
    guidesWithPricing: guides.filter(g => g.dmc_day_rate > 0 || g.travClicks_day_rate > 0).length
  });
  
  // Filter guides once
  const filteredGuides = useMemo(() => {
    const filtered = guides.filter(guide => {
      const hasDmcPrice = guide.dmc_day_rate > 0;
      const hasTravclicksPrice = guide.travClicks_day_rate > 0;
      return hasDmcPrice || hasTravclicksPrice;
    });
    
    console.log('GuideListing - Filtered guides:', {
      totalGuides: guides.length,
      filteredCount: filtered.length,
      filteredIds: filtered.map(g => g.id)
    });
    
    return filtered;
  }, [guides]);

  // Find selected guide once
  const selectedGuide = useMemo(() => {
    if (!value || !filteredGuides || filteredGuides.length === 0) {
      console.log('GuideListing - No selected guide because:', {
        noValue: !value,
        noFilteredGuides: !filteredGuides || filteredGuides.length === 0,
        value: value,
        filteredGuidesLength: filteredGuides?.length || 0
      });
      return null;
    }
    
    // Try different ID matching approaches
    const foundGuide = filteredGuides.find(g => g.id === value) || 
                      filteredGuides.find(g => String(g.id) === String(value)) ||
                      filteredGuides.find(g => parseInt(g.id, 10) === parseInt(value, 10));
    
    console.log('GuideListing - Guide lookup result:', {
      searchingForId: value,
      foundGuide: foundGuide ? {
        id: foundGuide.id,
        name: foundGuide.guide_name
      } : null,
      availableIds: filteredGuides.map(g => ({ id: g.id, name: g.guide_name }))
    });
    
    return foundGuide || null;
  }, [filteredGuides, value]);

  // Handle guide selection - fix unnecessary dispatch calls
  const handleGuideSelect = useCallback((event, newValue) => {
    // If no value selected, clear the selection
    if (!newValue) {
      onChange('guide', null);
      prevGuideIdRef.current = null;
      return;
    }

    // Update the selected guide ID
    onChange('guide', newValue.id);
    
    // Prevent duplicate fetches for the same guide
    if (prevGuideIdRef.current === newValue.id) {
      return;
    }
    
    prevGuideIdRef.current = newValue.id;

    // Only fetch guide details when we have all the necessary info
    if (!searchParams?.location?.city || !searchParams?.date) {
      return;
    }

    // Fetch guide details if we have all required data
    const hasDmcPrice = newValue.dmc_day_rate > 0;
    const hasTravclicksPrice = newValue.travClicks_day_rate > 0;
    const mode = hasDmcPrice ? "dmc" : hasTravclicksPrice ? "travclicks" : null;

    if (mode) {
      dispatch(fetchGuideDetails({
        guide_id: newValue.id,
        mode: mode,
        dmc_id: mode === 'dmc' ? newValue.dmc_id : newValue.travclicks_dmc_id,
        pickup: searchParams.location.city,
        date: searchParams.date
      }));
    }
  }, [dispatch, onChange, searchParams]);

  // Render option component - add proper key prop handling
  const renderOption = useCallback((props, option, state) => {
    // We don't need to manually add a key to the root element, MUI will handle this with getOptionKey
    return (
      <CustomTooltip
        title={<TooltipContent guide={option} />}
        placement="right"
        arrow
      >
        <Box component="li" {...props}>
          <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', justifyContent: 'space-between' }}>
            <Typography noWrap>{option.guide_name}</Typography>
            <Box sx={{ display: 'flex', gap: 0.5, ml: 1, flexShrink: 0 }}>
              {option.dmc_day_rate > 0 && (
                <Chip 
                  size="small" 
                  label="DMC"
                  sx={{ 
                    height: 20,
                    fontSize: '0.7rem',
                    bgcolor: 'rgba(25, 118, 210, 0.08)',
                    color: 'primary.main'
                  }}
                />
              )}
              {option.travClicks_day_rate > 0 && (
                <Chip 
                  size="small" 
                  label="Travclicks"
                  sx={{ 
                    height: 20,
                    fontSize: '0.7rem',
                    bgcolor: 'rgba(76, 175, 80, 0.08)',
                    color: '#2e7d32'
                  }}
                />
              )}
            </Box>
          </Box>
        </Box>
      </CustomTooltip>
    );
  }, []);

  // Render input component
  const renderInput = useCallback((params) => (
    <TextField
      {...params}
      label="Search Guide"
      fullWidth
      disabled={disabled}
      InputProps={{
        ...params.InputProps,
        sx: {
          '& .MuiAutocomplete-input': {
            height: '1.4375em',
          }
        }
      }}
    />
  ), [disabled]);
  
  // Memoize getOptionLabel function
  const getOptionLabel = useCallback((option) => {
    return option?.guide_name || '';
  }, []);
  
  // Memoize isOptionEqualToValue function
  const isOptionEqualToValue = useCallback((option, value) => {
    if (!option || !value) return false;
    return option.id === value.id;
  }, []);

  // Function to get keys for options
  const getOptionKey = useCallback((option) => {
    return option?.id || '';
  }, []);

  return (
    <Grid item xs={12} sm={6} md={12}>
      <Autocomplete
        value={selectedGuide}
        onChange={handleGuideSelect}
        options={filteredGuides}
        getOptionLabel={getOptionLabel}
        getOptionKey={getOptionKey}
        isOptionEqualToValue={isOptionEqualToValue}
        noOptionsText="No guides with valid pricing available"
        disabled={disabled || filteredGuides.length === 0}
        renderOption={renderOption}
        renderInput={renderInput}
        ListboxProps={{
          style: {
            maxHeight: '300px'
          }
        }}
        slotProps={{
          popper: {
            sx: {
              zIndex: 999999
            }
          },
          option: {
            key: getOptionKey
          }
        }}
        forcePopupIcon={false}
      />
    </Grid>
  );
};

// Make sure the component is memoized to prevent unnecessary re-renders
export default React.memo(GuideListing, (prevProps, nextProps) => {
  // Only re-render if these props change
  return (
    prevProps.value === nextProps.value &&
    prevProps.disabled === nextProps.disabled
  );
}); 