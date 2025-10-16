import React, { useState, useEffect, useRef, useCallback } from 'react';
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
import { useSelector, shallowEqual } from 'react-redux';

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
  padding: theme.spacing(1),
  borderRadius: theme.shape.borderRadius,
  backgroundColor: theme.palette.background.paper,
  marginBottom: theme.spacing(1.5),
  '&:hover': {
    backgroundColor: theme.palette.action.hover
  }
}));

const Counter = ({ name, value, minValue, onCounterChange, maxValue, disabled = false, ageDescription, icon }) => {
  return (
    <CounterBox>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        {icon}
        <Box sx={{ ml: 1.5 }}>
          <Typography variant="body2" sx={{ fontWeight: 500, fontSize: '0.8rem' }}>
            {name}
          </Typography>
          {ageDescription && (
            <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
              {ageDescription}
            </Typography>
          )}
        </Box>
      </Box>
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
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
              <RemoveCircleOutlineIcon sx={{ fontSize: 18 }} />
            </IconButton>
          </span>
        </Tooltip>
        <Typography 
          sx={{ 
            minWidth: '28px', 
            textAlign: 'center',
            fontWeight: 'bold',
            fontSize: '1rem'
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
              <AddCircleOutlineIcon sx={{ fontSize: 18 }} />
            </IconButton>
          </span>
        </Tooltip>
      </Box>
    </CounterBox>
  );
};

const PaxSelector = ({ selectedPax, onPaxChange, initialAdults, initialChildren, disabled }) => {
  const [anchorEl, setAnchorEl] = useState(null);
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails, shallowEqual) || {};

  // Get age limits from attractionDetails or use defaults
  const childMaxAge = attractionDetails.child_max_age || 17;
  const seniorMinAge = attractionDetails.senior_min_age || 60;

  // Store the original default values as maximum limits
  const defaultAdults = initialAdults || 1;
  const defaultChildren = initialChildren || 0;

  // Initialize guest counts - prioritize selectedPax if available
  const [guestCounts, setGuestCounts] = useState({
    Adults: selectedPax?.Adults !== undefined ? selectedPax.Adults : defaultAdults,
    Children: selectedPax?.Children !== undefined ? selectedPax.Children : defaultChildren,
    Seniors: selectedPax?.Seniors !== undefined ? selectedPax.Seniors : 0
  });

  // Store max limits as immutable state (initialized once, never updated)
  const [maxLimits] = useState(() => {
    const totalFromSelected = selectedPax 
      ? (selectedPax.Adults || 0) + (selectedPax.Seniors || 0)
      : defaultAdults;
    
    return {
      Adults: Math.max(totalFromSelected, defaultAdults),
      Children: selectedPax?.Children || defaultChildren,
      Seniors: Math.max(totalFromSelected, defaultAdults),
      originalAdultCount: Math.max(totalFromSelected, defaultAdults)
    };
  });

  // Sync with selectedPax when it changes - WITH GUARDS to prevent loops
  useEffect(() => {
    if (selectedPax && (selectedPax.Adults !== undefined || selectedPax.Children !== undefined || selectedPax.Seniors !== undefined)) {
      // Only update if values actually differ
      if (
        selectedPax.Adults !== guestCounts.Adults ||
        selectedPax.Children !== guestCounts.Children ||
        selectedPax.Seniors !== guestCounts.Seniors
      ) {
        console.log('PaxSelector syncing from selectedPax:', selectedPax);
        setGuestCounts({
          Adults: selectedPax.Adults || 0,
          Children: selectedPax.Children || 0,
          Seniors: selectedPax.Seniors || 0
        });
      }
    }
  }, [selectedPax]); // REMOVED guestCounts from dependencies to break feedback loop

  // Memoized callback to notify parent - WITH GUARDS
  const notifyParent = useCallback((counts) => {
    if (onPaxChange && 
        (!selectedPax || 
          selectedPax.Adults !== counts.Adults ||
          selectedPax.Children !== counts.Children ||
          selectedPax.Seniors !== counts.Seniors)) {
      console.log('PaxSelector calling onPaxChange:', { from: selectedPax, to: counts });
      onPaxChange(counts);
    }
  }, [onPaxChange, selectedPax]); // REMOVED guestCounts from dependencies

  const handleClick = (event) => {
    if (!disabled) {
      setAnchorEl(event.currentTarget);
    }
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleCounterChange = (name, value) => {
    let newCounts = { ...guestCounts };
    
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
      if (newAdultValue + value > maxLimits.originalAdultCount) {
        value = maxLimits.originalAdultCount - newAdultValue;
      }
      
      // Update both seniors and adults
      newCounts = {
        ...newCounts,
        Seniors: value,
        Adults: newAdultValue
      };
    }
    // For Adults, ensure we respect the senior count
    else if (name === "Adults") {
      // Calculate maximum adults based on seniors and original total
      const maxAdults = maxLimits.originalAdultCount - guestCounts.Seniors;
      if (value > maxAdults) {
        value = maxAdults;
      }
      
      // For manual adjustment, Adults can't go below 1
      if (value < 1) {
        value = 1;
      }
      
      newCounts[name] = value;
    }
    // For Children
    else if (name === "Children") {
      // Ensure values never exceed the original maximum
      if (value > maxLimits[name]) {
        value = maxLimits[name];
      }
      
      // Ensure children never goes below 0
      if (value < 0) {
        value = 0;
      }
      
      newCounts[name] = value;
    }
    
    // Only update state if values changed
    if (
      newCounts.Adults !== guestCounts.Adults ||
      newCounts.Children !== guestCounts.Children ||
      newCounts.Seniors !== guestCounts.Seniors
    ) {
      setGuestCounts(newCounts);
      // Immediately notify parent with new values
      notifyParent(newCounts);
    }
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
        <CardContent sx={{ p: 1.5, '&:last-child': { pb: 1.5 } }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <PersonIcon sx={{ color: 'primary.main', fontSize: 18 }} />
              <Typography sx={{ fontSize: '0.8rem' }}>
                {guestCounts.Adults}
              </Typography>
            </Box>
            
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <ElderlyIcon sx={{ color: 'primary.main', fontSize: 18 }} />
              <Typography sx={{ fontSize: '0.8rem' }}>
                {guestCounts.Seniors}
              </Typography>
            </Box>

            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <ChildCareIcon sx={{ color: 'primary.main', fontSize: 18 }} />
              <Typography sx={{ fontSize: '0.8rem' }}>
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
            width: '320px',
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
        <Paper sx={{ p: 2.5 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2.5 }}>
            <Typography variant="subtitle1" sx={{ fontWeight: 600, fontSize: '0.9rem' }}>
              Number of travelers
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                Total:
              </Typography>
              <Typography variant="body2" color="primary.main" sx={{ fontWeight: 600, fontSize: '0.8rem' }}>
                {guestCounts.Adults + guestCounts.Seniors + guestCounts.Children}
              </Typography>
            </Box>
          </Box>

          <Counter
            name="Adults"
            value={guestCounts.Adults}
            minValue={1}
            maxValue={maxLimits.originalAdultCount - guestCounts.Seniors}
            onCounterChange={handleCounterChange}
            ageDescription={adultsAgeDescription}
            icon={<PersonIcon sx={{ color: 'primary.main', fontSize: 18 }} />}
          />

          <Counter
            name="Seniors"
            value={guestCounts.Seniors}
            minValue={0}
            maxValue={maxLimits.originalAdultCount}
            onCounterChange={handleCounterChange}
            ageDescription={seniorsAgeDescription}
            icon={<ElderlyIcon sx={{ color: 'primary.main', fontSize: 18 }} />}
          />

          <Counter
            name="Children"
            value={guestCounts.Children}
            minValue={0}
            maxValue={maxLimits.Children}
            onCounterChange={handleCounterChange}
            ageDescription={childrenAgeDescription}
            icon={<ChildCareIcon sx={{ color: 'primary.main', fontSize: 18 }} />}
          />

          <Divider sx={{ my: 1.5 }} />

          <Button 
            variant="contained" 
            fullWidth 
            onClick={handleClose}
            sx={{ 
              mt: 1.5,
              py: 1.2,
              textTransform: 'none',
              fontSize: '0.9rem'
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