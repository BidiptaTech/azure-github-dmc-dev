import React, { useState, useEffect, useCallback, memo } from 'react';
import { 
  Box,
  Typography,
  Tooltip,
  styled,
  IconButton,
  Popover,
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

const Counter = memo(({ name, value, minValue, onCounterChange, maxValue, disabled = false, ageDescription, icon }) => {
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
        <Tooltip title={value >= maxValue ? "Maximum limit reached" : "Increase"}>
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
});

const PassengerSelection = ({ value, onChange, disabled }) => {
  const [anchorEl, setAnchorEl] = useState(null);
  
  // Get the search parameters from Redux store
  const searchParams = useSelector((state) => state.tourguide.searchParams);
  const initialAdults = searchParams?.adults || 1;
  const initialChildren = searchParams?.children || 0;
  const totalAllowed = initialAdults + initialChildren; // Total passengers allowed from search

  // Initialize guest counts with maximum values from search parameters
  const [guestCounts, setGuestCounts] = useState({
    Adults: value?.Adults !== undefined ? value.Adults : initialAdults,
    Children: value?.Children !== undefined ? value.Children : initialChildren
  });

  // Store initial values as maximum limits
  const [maxLimits] = useState({
    Adults: initialAdults,
    Children: initialChildren
  });

  // Update guest counts when value prop changes, but prevent unnecessary updates
  useEffect(() => {
    if (
      value && 
      (guestCounts.Adults !== value.Adults || 
       guestCounts.Children !== value.Children)
    ) {
      setGuestCounts({
        Adults: value.Adults,
        Children: value.Children
      });
    }
  }, [value]);

  // Update guest counts when search parameters change, but only when needed
  useEffect(() => {
    if (searchParams) {
      const newAdults = searchParams.adults || 1;
      const newChildren = searchParams.children || 0;
      
      // Only update if there's a real change and no value prop
      if (!value && (
          guestCounts.Adults !== newAdults || 
          guestCounts.Children !== newChildren
        )) {
        setGuestCounts({
          Adults: newAdults,
          Children: newChildren
        });
      }
    }
  }, [searchParams, value, guestCounts]);

  // Update parent component when guest counts change, but prevent infinite loops
  const notifyParent = useCallback((counts) => {
    if (onChange && 
        (!value || 
          value.Adults !== counts.Adults || 
          value.Children !== counts.Children)) {
      onChange(counts);
    }
  }, [onChange, value]);

  const handleClick = useCallback((event) => {
    if (!disabled) {
      setAnchorEl(event.currentTarget);
    }
  }, [disabled]);

  const handleClose = useCallback(() => {
    setAnchorEl(null);
  }, []);

  const handleCounterChange = useCallback((name, newValue) => {
    const currentTotal = name === "Adults" ? 
      newValue + guestCounts.Children : 
      guestCounts.Adults + newValue;

    // Don't allow changes that would exceed total allowed
    if (currentTotal > totalAllowed) {
      return;
    }

    // For Adults
    if (name === "Adults") {
      // Adults can't go below 1
      if (newValue < 1) {
        newValue = 1;
      }
      
      // Ensure values never exceed the initial maximum
      if (newValue > maxLimits.Adults) {
        newValue = maxLimits.Adults;
      }

      // If reducing adults would make total less than children, adjust children
      const remainingForChildren = totalAllowed - newValue;
      if (guestCounts.Children > remainingForChildren) {
        const updatedCounts = {
          Adults: newValue,
          Children: remainingForChildren
        };
        setGuestCounts(updatedCounts);
        notifyParent(updatedCounts);
        return;
      }
    }
    
    // For Children
    if (name === "Children") {
      // Ensure values never exceed the initial maximum or remaining space
      const maxAllowedChildren = Math.min(
        maxLimits.Children,
        totalAllowed - guestCounts.Adults
      );
      
      if (newValue > maxAllowedChildren) {
        newValue = maxAllowedChildren;
      }
      
      // Children can go down to 0
      if (newValue < 0) {
        newValue = 0;
      }
    }
    
    const updatedCounts = {
      ...guestCounts,
      [name]: newValue
    };
    
    setGuestCounts(updatedCounts);
    notifyParent(updatedCounts);
  }, [guestCounts, maxLimits, totalAllowed, notifyParent]);

  const open = Boolean(anchorEl);
  const id = open ? 'pax-popover' : undefined;

  // Create dynamic age descriptions
  const childrenAgeDescription = `Ages 0 - 11`;
  const adultsAgeDescription = `Ages 12+`;

  // Calculate remaining spots
  const remainingSpots = totalAllowed - (guestCounts.Adults + guestCounts.Children);

  return (
    <Box sx={{ flex: 1 }}>
      <Card 
        variant="outlined" 
        onClick={handleClick}
        sx={{ 
          cursor: disabled ? 'not-allowed' : 'pointer',
          border: '1px solid',
          borderColor: 'divider',
          opacity: disabled ? 0.5 : 1
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
              <ChildCareIcon sx={{ color: 'primary.main' }} />
              <Typography>
                {guestCounts.Children}
              </Typography>
            </Box>

            <Typography variant="caption" color="text.secondary" sx={{ ml: 'auto' }}>
              {remainingSpots > 0 ? `${remainingSpots} spots left` : 'Full'}
            </Typography>
          </Box>
        </CardContent>
      </Card>

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
              Number of guests
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <Typography variant="caption" color="text.secondary">
                {remainingSpots > 0 ? `${remainingSpots} spots left` : 'Full'}
              </Typography>
              <Typography variant="subtitle2" color="primary.main" sx={{ fontWeight: 600 }}>
                {guestCounts.Adults + guestCounts.Children}/{totalAllowed}
              </Typography>
            </Box>
          </Box>

          <Counter
            name="Adults"
            value={guestCounts.Adults}
            minValue={1}
            maxValue={Math.min(
              maxLimits.Adults,
              totalAllowed - guestCounts.Children
            )}
            onCounterChange={handleCounterChange}
            ageDescription={adultsAgeDescription}
            icon={<PersonIcon sx={{ color: 'primary.main' }} />}
          />

          <Counter
            name="Children"
            value={guestCounts.Children}
            minValue={0}
            maxValue={Math.min(
              maxLimits.Children,
              totalAllowed - guestCounts.Adults
            )}
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
    </Box>
  );
};

export default memo(PassengerSelection); 