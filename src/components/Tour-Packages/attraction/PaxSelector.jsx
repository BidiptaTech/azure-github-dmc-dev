import React, { useState, useEffect } from 'react';
import { 
  Grid, 
  Box,
  Typography,
  TextField,
  Tooltip,
  styled,
  IconButton,
  Popover,
  Stack,
  Button,
  Card,
  CardContent,
  Divider,
  Paper
} from '@mui/material';
import PersonIcon from '@mui/icons-material/Person';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import RemoveCircleOutlineIcon from '@mui/icons-material/RemoveCircleOutline';
import ElderlyIcon from '@mui/icons-material/Elderly';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import GroupIcon from '@mui/icons-material/Group';
import { useSelector } from 'react-redux';

// Styled components
const StyledCard = styled(Card)(({ theme }) => ({
  '&:hover': {
    boxShadow: theme.shadows[4],
    transform: 'translateY(-2px)',
    transition: 'all 0.3s ease'
  },
  transition: 'all 0.3s ease'
}));

const CounterBox = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  padding: theme.spacing(1.5),
  borderRadius: theme.shape.borderRadius,
  backgroundColor: theme.palette.background.paper,
  marginBottom: theme.spacing(2),
  '&:hover': {
    backgroundColor: theme.palette.action.hover
  }
}));

const Counter = ({ name, value, minValue, onCounterChange, maxValue, disabled = false, ageDescription, icon }) => {
  return (
    <CounterBox>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        {icon}
        <Box sx={{ ml: 2 }}>
          <Typography variant="subtitle1" sx={{ fontWeight: 500 }}>
            {name}
          </Typography>
          {ageDescription && (
            <Typography variant="caption" color="text.secondary">
              {ageDescription}
            </Typography>
          )}
        </Box>
      </Box>
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
        <Tooltip title={value <= minValue ? "Minimum reached" : "Decrease"}>
          <span>
            <IconButton
              size="small"
              onClick={() => onCounterChange(name, Math.max(value - 1, minValue))}
              disabled={value <= minValue || disabled}
              sx={{ 
                color: (value <= minValue || disabled) ? 'text.disabled' : 'primary.main',
                '&:hover': {
                  backgroundColor: 'rgba(25, 118, 210, 0.04)'
                }
              }}
            >
              <RemoveCircleOutlineIcon />
            </IconButton>
          </span>
        </Tooltip>
        <Typography 
          sx={{ 
            minWidth: '32px', 
            textAlign: 'center',
            fontWeight: 'bold',
            fontSize: '1.1rem'
          }}
        >
          {value}
        </Typography>
        <Tooltip title={value >= maxValue ? "Maximum reached" : "Increase"}>
          <span>
            <IconButton
              size="small"
              onClick={() => onCounterChange(name, value + 1)}
              disabled={value >= maxValue || disabled}
              sx={{ 
                color: (value >= maxValue || disabled) ? 'text.disabled' : 'primary.main',
                '&:hover': {
                  backgroundColor: 'rgba(25, 118, 210, 0.04)'
                }
              }}
            >
              <AddCircleOutlineIcon />
            </IconButton>
          </span>
        </Tooltip>
      </Box>
    </CounterBox>
  );
};

const PaxSelector = ({ selectedPax, onPaxChange, initialAdults, initialChildren, disabled }) => {
  const [anchorEl, setAnchorEl] = useState(null);
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails || {});

  // Get age limits from attractionDetails or use defaults
  const childMaxAge = attractionDetails.child_max_age || 17;
  const seniorMinAge = attractionDetails.senior_min_age || 60;

  // Store the original default values as maximum limits
  const defaultAdults = initialAdults || 1;
  const defaultChildren = initialChildren || 0;

  // Initialize guest counts
  const [guestCounts, setGuestCounts] = useState({
    Adults: defaultAdults,
    Children: defaultChildren,
    Seniors: 0
  });

  // Track the maximum allowed values
  const [maxValues, setMaxValues] = useState({
    Adults: defaultAdults,
    Children: defaultChildren,
    Seniors: defaultAdults
  });

  // Track the original total adult count
  const [originalAdultCount, setOriginalAdultCount] = useState(defaultAdults);

  useEffect(() => {
    setGuestCounts({
      Adults: defaultAdults,
      Children: defaultChildren,
      Seniors: 0
    });

    setMaxValues({
      Adults: defaultAdults,
      Children: defaultChildren,
      Seniors: defaultAdults
    });

    setOriginalAdultCount(defaultAdults);
  }, [defaultAdults, defaultChildren]);

  useEffect(() => {
    // Only call onPaxChange if the values are actually different from the current props
    const currentPax = selectedPax || { Adults: 0, Children: 0, Seniors: 0 };
    if (
      currentPax.Adults !== guestCounts.Adults ||
      currentPax.Children !== guestCounts.Children ||
      currentPax.Seniors !== guestCounts.Seniors
    ) {
      onPaxChange(guestCounts);
    }
  }, [guestCounts, onPaxChange, selectedPax]);

  const handleClick = (event) => {
    if (!disabled) {
      setAnchorEl(event.currentTarget);
    }
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleCounterChange = (name, value) => {
    // Special logic for Seniors to decrease Adults when Seniors increase
    if (name === "Seniors") {
      const currentSeniors = guestCounts.Seniors;
      const currentAdults = guestCounts.Adults;
      
      // Calculate the change in seniors
      const seniorDiff = value - currentSeniors;
      
      // Calculate new adult value based on senior change
      let newAdultValue = currentAdults - seniorDiff;
      
      // Ensure adults don't go below 0
      if (newAdultValue < 0) {
        newAdultValue = 0;
        // Recalculate seniors based on available adults
        value = currentSeniors + currentAdults;
      }
      
      // Ensure we don't exceed original adult count
      if (newAdultValue + value > originalAdultCount) {
        value = originalAdultCount - newAdultValue;
      }
      
      // Update both seniors and adults
      setGuestCounts(prev => ({
        ...prev,
        Seniors: value,
        Adults: newAdultValue
      }));
      
      return;
    }
    
    // For Adults, ensure we respect the senior count
    if (name === "Adults") {
      // Calculate maximum adults based on seniors and original total
      const maxAdults = originalAdultCount - guestCounts.Seniors;
      if (value > maxAdults) {
        value = maxAdults;
      }
      
      // For manual adjustment, Adults can't go below 1
      if (value < 1) {
        value = 1;
      }
    }
    
    // For Children
    if (name === "Children") {
      // Ensure values never exceed the original maximum
      if (value > maxValues[name]) {
        value = maxValues[name];
      }
      
      // Ensure children never goes below 0
      if (value < 0) {
        value = 0;
      }
    }
    
    setGuestCounts(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const open = Boolean(anchorEl);
  const id = open ? 'pax-popover' : undefined;

  // Create dynamic age descriptions
  const childrenAgeDescription = `Ages 0 - ${childMaxAge}`;
  const seniorsAgeDescription = `Ages ${seniorMinAge}+`;
  const adultsAgeDescription = `Ages ${parseInt(childMaxAge) + 1} - ${parseInt(seniorMinAge) - 1}`;

  return (
    <Grid item xs={12} sm={12} md={12}>
      <StyledCard 
        variant="outlined" 
        onClick={handleClick}
        sx={{ 
          cursor: 'pointer',
          border: '1px solid',
          borderColor: 'divider'
        }}
      >
        <CardContent sx={{ p: 2, '&:last-child': { pb: 2 } }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <PersonIcon sx={{ color: 'primary.main' }} />
              <Typography>
                {guestCounts.Adults}
              </Typography>
            </Box>
            
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <ElderlyIcon sx={{ color: 'primary.main' }} />
              <Typography>
                {guestCounts.Seniors}
              </Typography>
            </Box>

            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <ChildCareIcon sx={{ color: 'primary.main' }} />
              <Typography>
                {guestCounts.Children}
              </Typography>
            </Box>
          </Box>
        </CardContent>
      </StyledCard>

      <Popover
        id={id}
        open={open}
        anchorEl={anchorEl}
        onClose={handleClose}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'left',
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'left',
        }}
        PaperProps={{
          sx: {
            width: '350px',
            mt: 1,
            overflow: 'visible',
            '&:before': {
              content: '""',
              display: 'block',
              position: 'absolute',
              top: 0,
              left: 32,
              width: 10,
              height: 10,
              bgcolor: 'background.paper',
              transform: 'translateY(-50%) rotate(45deg)',
              zIndex: 0,
            },
          }
        }}
      >
        <Paper sx={{ p: 3 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 3 }}>
            <Typography variant="h6" sx={{ fontWeight: 600 }}>
              Number of travelers
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <Typography variant="caption" color="text.secondary">
                Total:
              </Typography>
              <Typography variant="subtitle2" color="primary.main" sx={{ fontWeight: 600 }}>
                {guestCounts.Adults + guestCounts.Seniors + guestCounts.Children}
              </Typography>
            </Box>
          </Box>

          <Counter
            name="Adults"
            value={guestCounts.Adults}
            minValue={1}
            maxValue={originalAdultCount - guestCounts.Seniors}
            onCounterChange={handleCounterChange}
            ageDescription={adultsAgeDescription}
            icon={<PersonIcon sx={{ color: 'primary.main' }} />}
          />

          <Counter
            name="Seniors"
            value={guestCounts.Seniors}
            minValue={0}
            maxValue={originalAdultCount}
            onCounterChange={handleCounterChange}
            ageDescription={seniorsAgeDescription}
            icon={<ElderlyIcon sx={{ color: 'primary.main' }} />}
          />

          <Counter
            name="Children"
            value={guestCounts.Children}
            minValue={0}
            maxValue={maxValues.Children}
            onCounterChange={handleCounterChange}
            ageDescription={childrenAgeDescription}
            icon={<ChildCareIcon sx={{ color: 'primary.main' }} />}
          />

          <Divider sx={{ my: 2 }} />

          <Button 
            variant="contained" 
            fullWidth 
            onClick={handleClose}
            sx={{ 
              mt: 2,
              py: 1.5,
              textTransform: 'none',
              fontSize: '1rem'
            }}
          >
            Done
          </Button>
        </Paper>
      </Popover>
    </Grid>
  );
};

export default PaxSelector; 