import React from 'react';
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
  Radio,
  FormControlLabel,
  RadioGroup,
  FormControl,
  Button,
  Chip,
  Paper
} from '@mui/material';
import { useDispatch, useSelector } from 'react-redux';
import { fetchAttractionDetails } from '../../../slice/attractions/attractionSlice';
import { setBookingMode } from '../../../slice/common/commonSlice';
import { useNavigate } from 'react-router-dom';
import LocationOnIcon from '@mui/icons-material/LocationOn';

// Custom styled tooltip
const CustomTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    backgroundColor: 'white',
    color: 'rgba(0, 0, 0, 0.87)',
    maxWidth: 350,
    border: '1px solid #dadde9',
    borderRadius: '8px',
    padding: 0,
    boxShadow: theme.shadows[2]
  },
}));

// Opening hours chip component
const OpeningHoursChip = ({ isOpen, label }) => (
  <Chip
    size="small"
    label={label}
    sx={{
      backgroundColor: isOpen ? 'rgba(76, 175, 80, 0.1)' : 'rgba(0, 0, 0, 0.08)',
      color: isOpen ? '#2e7d32' : 'text.secondary',
      fontSize: '0.7rem',
      height: 20,
      '& .MuiChip-label': {
        px: 0.8,
      },
    }}
  />
);

// Tooltip content component
const TooltipContent = ({ attraction }) => {
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  const openingTimes = [];
  if (attraction.morning_opening === 1) openingTimes.push("Morning");
  if (attraction.afternoon_opening === 1) openingTimes.push("Afternoon");
  if (attraction.evening_opening === 1) openingTimes.push("Evening");
  if (attraction.night_opening === 1) openingTimes.push("Night");

  return (
    <Box>
      {/* Header Image Section */}
      <Box sx={{ position: 'relative', width: '100%', height: 160 }}>
        <Box
          component="img"
          src={attraction.image}
          alt={attraction.attraction_name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '8px',
            borderTopRightRadius: '8px',
          }}
        />
        {attraction.additional_images && attraction.additional_images.length > 0 && (
          <Box
            sx={{
              position: 'absolute',
              top: 6,
              right: 6,
              bgcolor: 'rgba(0, 0, 0, 0.6)',
              color: 'white',
              px: 0.8,
              py: 0.4,
              borderRadius: 0.8,
              fontSize: '0.7rem',
            }}
          >
            +{attraction.additional_images.length} photos
          </Box>
        )}
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 1.5 }}>
        {/* Title and Location */}
        <Typography variant="subtitle1" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1rem', mb: 1 }}>
          {attraction.attraction_name}
        </Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
          <LocationOnIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.5 }} />
          <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.75rem' }}>
            {attraction.city}, {attraction.country}
          </Typography>
        </Box>

        {/* Opening Hours */}
        <Box sx={{ mb: 1.5 }}>
          <Typography variant="body2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem', mb: 0.8 }}>
            Opening Hours
          </Typography>
          <Stack direction="row" spacing={0.8} flexWrap="wrap" useFlexGap>
            <OpeningHoursChip isOpen={attraction.morning_opening === 1} label="Morning" />
            <OpeningHoursChip isOpen={attraction.afternoon_opening === 1} label="Afternoon" />
            <OpeningHoursChip isOpen={attraction.evening_opening === 1} label="Evening" />
            <OpeningHoursChip isOpen={attraction.night_opening === 1} label="Night" />
          </Stack>
        </Box>

        {/* Time Slots */}
        {attraction.time_slots && attraction.time_slots.length > 0 && (
          <Box sx={{ mb: 1.5 }}>
            <Typography variant="body2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem', mb: 0.8 }}>
              Available Time Slots
            </Typography>
            <Stack direction="row" spacing={0.8} flexWrap="wrap" useFlexGap>
              {attraction.time_slots.map((slot, index) => (
                <Chip
                  key={index}
                  size="small"
                  label={slot}
                  sx={{
                    bgcolor: 'rgba(25, 118, 210, 0.08)',
                    color: 'primary.main',
                    fontSize: '0.7rem',
                    height: 20,
                  }}
                />
              ))}
            </Stack>
          </Box>
        )}

        {/* Pricing Section */}
        <Box sx={{ mt: 1.5 }}>
  <Typography variant="body2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem', mb: 1 }}>
    Pricing Details
  </Typography>

  {PriceHide !== "1" ? (
    <>
      <Grid container spacing={1.5}>
        {/* DMC Prices */}
        {attraction.dmc_adult_price > 0 && (
          <Grid item xs={6}>
            <Paper 
              variant="outlined" 
              sx={{ 
                p: 1,
                bgcolor: 'rgba(25, 118, 210, 0.02)',
                borderColor: 'rgba(25, 118, 210, 0.1)'
              }}
            >
              <Typography variant="caption" gutterBottom sx={{ color: 'primary.main', fontWeight: 500, fontSize: '0.75rem' }}>
                DMC Prices
              </Typography>
              <Stack spacing={0.3}>
                <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>Adult: ${attraction.dmc_adult_price}</Typography>
                <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>Child: ${attraction.dmc_child_price}</Typography>
                <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>Senior: ${attraction.dmc_senior_price}</Typography>
              </Stack>
            </Paper>
          </Grid>
        )}

        {/* Travclicks Prices */}
        {attraction.travClicks_adult_price > 0 && (
          <Grid item xs={6}>
            <Paper 
              variant="outlined" 
              sx={{ 
                p: 1,
                bgcolor: 'rgba(76, 175, 80, 0.02)',
                borderColor: 'rgba(76, 175, 80, 0.1)'
              }}
            >
              <Typography variant="caption" gutterBottom sx={{ color: '#2e7d32', fontWeight: 500, fontSize: '0.75rem' }}>
                Travclicks Prices
              </Typography>
              <Stack spacing={0.3}>
                <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>Adult: ${attraction.travClicks_adult_price}</Typography>
                <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>Child: ${attraction.travClicks_child_price}</Typography>
                <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>Senior: ${attraction.travClicks_senior_price}</Typography>
              </Stack>
            </Paper>
          </Grid>
        )}
      </Grid>

      {/* Tax Information */}
      {attraction.tax_percentage && (
        <Typography 
          variant="caption" 
          sx={{ 
            display: 'block',
            mt: 0.8,
            color: 'text.secondary',
            fontStyle: 'italic',
            fontSize: '0.65rem'
          }}
        >
          *Prices are subject to {attraction.tax_percentage}% tax
        </Typography>
      )}
    </>
  ) : (
    <Grid item xs={12}>
      <Typography variant="caption" gutterBottom sx={{ color: 'text.secondary', fontWeight: 500, fontSize: '0.75rem' }}>
        Pricing hidden
      </Typography>
    </Grid>
  )}
</Box>

      </Box>
    </Box>
  );
};

const AttractionListing = ({ attractions, selectedAttraction, onAttractionChange }) => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const bookingMode = useSelector((state) => state.common.bookingMode);
  const dmcName = useSelector((state) => state.auth.DmcName) || "DMC";

  // Filter attractions that have at least one pricing mode
  const filteredAttractions = attractions.filter(attraction => {
    const hasDmcPrice = attraction.dmc_adult_price > 0;
    const hasTravclicksPrice = attraction.travClicks_adult_price > 0;
    return hasDmcPrice || hasTravclicksPrice; // Must have at least one pricing mode
  });

  const handleAttractionClick = (attraction) => {
    if (!attraction) return;
    
    // Determine which pricing mode to use based on availability
    const hasDmcPrice = attraction.dmc_adult_price > 0;
    const hasTravclicksPrice = attraction.travClicks_adult_price > 0;
    
    // Default to DMC if available, otherwise use travclicks
    const mode = hasDmcPrice ? "dmc" : hasTravclicksPrice ? "travclicks" : null;
    
    // If no valid pricing mode, don't proceed
    if (!mode) return;
    
    // Ensure the booking mode is updated in Redux
    dispatch(setBookingMode(mode));

    // Get the appropriate dmc_id based on the mode
    const dmc_id = mode === "dmc" ? attraction.dmc_id : attraction.travclicks_dmc_id;

    // Call the attraction details API
    dispatch(
      fetchAttractionDetails({
        attractionId: attraction.id,
        price_mode: { mode },
        dmc_id: dmc_id,
      })
    );

    // Call the onAttractionChange prop if provided
    if (onAttractionChange) {
      onAttractionChange(attraction.id);
    }
  };

  return (
    <Grid item xs={12} sm={12} md={12}>
      <Autocomplete
        value={filteredAttractions.find(a => a.id === selectedAttraction) || null}
        onChange={(event, newValue) => {
          handleAttractionClick(newValue);
        }}
        options={filteredAttractions}
        getOptionLabel={(option) => option.attraction_name || ''}
        noOptionsText="No attractions with valid pricing available"
        sx={{
          '& .MuiInputBase-input': {
            fontSize: '0.75rem',
            height: '12px',
            paddingBottom: '10px',
            paddingTop: '0px',
          },
        }}
        renderOption={(props, option) => {
          // Extract the key from props and remove it from the rest to avoid React warning
          const { key, ...otherProps } = props;
          
          return (
            <CustomTooltip
              key={key}
              title={<TooltipContent attraction={option} />}
              placement="right"
              arrow
            >
              <Box component="li" {...otherProps}>
                {option.attraction_name}
                {/* Add a small indicator for available pricing modes */}
                <Box sx={{ ml: 'auto', display: 'flex', gap: 0.5 }}>
                  {option.dmc_adult_price > 0 && (
                    <Chip 
                      size="small" 
                      label="DMC"
                      sx={{ 
                        height: 18,
                        fontSize: '0.65rem',
                        bgcolor: 'rgba(25, 118, 210, 0.08)',
                        color: 'primary.main'
                      }}
                    />
                  )}
                  {option.travClicks_adult_price > 0 && (
                    <Chip 
                      size="small" 
                      label="Travclicks"
                      sx={{ 
                        height: 18,
                        fontSize: '0.65rem',
                        bgcolor: 'rgba(76, 175, 80, 0.08)',
                        color: '#2e7d32'
                      }}
                    />
                  )}
                </Box>
              </Box>
            </CustomTooltip>
          );
        }}
        renderInput={(params) => (
          <TextField
            {...params}
            label="Search Attraction"
            fullWidth
          />
        )}
      />
    </Grid>
  );
};

export default AttractionListing; 