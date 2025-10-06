import React, { useState, useEffect, useRef } from 'react';
import { 
  Grid, 
  Box,
  Typography,
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
                color: (value <= minValue || disabled) ? 'text.disabled' : '#2e7d32',
                '&:hover': {
                  backgroundColor: 'rgba(46, 125, 50, 0.04)'
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
        <Tooltip title={value >= maxValue ? "Maximum limit reached" : "Increase"}>
          <span>
            <IconButton
              size="small"
              onClick={() => onCounterChange(name, value + 1)}
              disabled={value >= maxValue || disabled}
              sx={{ 
                color: (value >= maxValue || disabled) ? 'text.disabled' : '#2e7d32',
                '&:hover': {
                  backgroundColor: 'rgba(46, 125, 50, 0.04)'
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

const PaxSelector = ({ selectedPax, onPaxChange, disabled, initialAdults, initialChildren }) => {
  const [anchorEl, setAnchorEl] = useState(null);
  
  // Get the search parameters from Redux store as fallback
  const searchParams = useSelector((state) => state.restaurants.searchParams);
  const fallbackAdults = searchParams?.adults || 1;
  const fallbackChildren = searchParams?.children || 0;

  // Use props first, then fallback to Redux state
  const effectiveAdults = initialAdults || fallbackAdults;
  const effectiveChildren = initialChildren || fallbackChildren;

  // Initialize guest counts with selectedPax if available, otherwise use effective values
  const [guestCounts, setGuestCounts] = useState({
    Adults: selectedPax?.Adults || effectiveAdults,
    Children: selectedPax?.Children || effectiveChildren
  });

  // Store initial values as maximum limits
  const [maxLimits] = useState({
    Adults: effectiveAdults,
    Children: effectiveChildren
  });

  // Use a ref to track if we're updating from selectedPax to prevent loops
  const isUpdatingFromPropsRef = useRef(false);

  // Update guest counts when selectedPax changes
  useEffect(() => {
    if (selectedPax) {
      console.log('Restaurant PaxSelector updating from selectedPax:', selectedPax);
      isUpdatingFromPropsRef.current = true; // Set flag to prevent onPaxChange call
      
      setGuestCounts({
        Adults: selectedPax.Adults || effectiveAdults,
        Children: selectedPax.Children || effectiveChildren
      });
    }
  }, [selectedPax, effectiveAdults, effectiveChildren]);

  useEffect(() => {
    // Skip calling onPaxChange if we're updating from selectedPax props
    if (isUpdatingFromPropsRef.current) {
      isUpdatingFromPropsRef.current = false;
      return;
    }

    // Only call onPaxChange if the values are actually different from the current props
    const currentPax = selectedPax || { Adults: 0, Children: 0 };
    if (
      currentPax.Adults !== guestCounts.Adults ||
      currentPax.Children !== guestCounts.Children
    ) {
      console.log('Restaurant PaxSelector calling onPaxChange:', { from: currentPax, to: guestCounts });
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
    // For Adults
    if (name === "Adults") {
      // Adults can't go below 1
      if (value < 1) {
        value = 1;
      }
      // Ensure values never exceed the initial maximum
      if (value > maxLimits.Adults) {
        value = maxLimits.Adults;
      }
    }
    
    // For Children
    if (name === "Children") {
      // Ensure values never exceed the initial maximum
      if (value > maxLimits.Children) {
        value = maxLimits.Children;
      }
      // Children can go down to 0
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
  const childrenAgeDescription = `Ages 0 - 11`;
  const adultsAgeDescription = `Ages 12+`;

  return (
    <Box sx={{ flex: 1 }}>
      <StyledCard 
        variant="outlined" 
        onClick={handleClick}
        sx={{ 
          cursor: disabled ? 'not-allowed' : 'pointer',
          border: '1px solid',
          borderColor: 'divider',
          opacity: disabled ? 0.5 : 1
        }}
      >
        <CardContent sx={{ p: 1.5, '&:last-child': { pb: 1.5 } }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <PersonIcon sx={{ color: '#2e7d32', fontSize: 18 }} />
              <Typography sx={{ fontSize: '0.8rem' }}>
                {guestCounts.Adults}
              </Typography>
            </Box>

            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <ChildCareIcon sx={{ color: '#2e7d32', fontSize: 18 }} />
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
              Number of guests
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                Total:
              </Typography>
              <Typography variant="body2" color="#2e7d32" sx={{ fontWeight: 600, fontSize: '0.8rem' }}>
                {guestCounts.Adults + guestCounts.Children}
              </Typography>
            </Box>
          </Box>

          <Counter
            name="Adults"
            value={guestCounts.Adults}
            minValue={1}
            maxValue={maxLimits.Adults}
            onCounterChange={handleCounterChange}
            ageDescription={adultsAgeDescription}
            icon={<PersonIcon sx={{ color: '#2e7d32', fontSize: 18 }} />}
          />

          <Counter
            name="Children"
            value={guestCounts.Children}
            minValue={0}
            maxValue={maxLimits.Children}
            onCounterChange={handleCounterChange}
            ageDescription={childrenAgeDescription}
            icon={<ChildCareIcon sx={{ color: '#2e7d32', fontSize: 18 }} />}
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
              fontSize: '0.9rem',
              bgcolor: '#4caf50',
              '&:hover': {
                bgcolor: '#388e3c'
              }
            }}
          >
            Done
          </Button>
        </Paper>
      </Popover>
    </Box>
  );
};

export default PaxSelector; 