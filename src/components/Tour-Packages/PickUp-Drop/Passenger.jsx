import React, { useState, useEffect } from 'react';
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
  Paper,
  Alert
} from '@mui/material';
import { useSelector, useDispatch } from "react-redux";
import PersonIcon from '@mui/icons-material/Person';
import AddCircleOutlineIcon from '@mui/icons-material/AddCircleOutline';
import RemoveCircleOutlineIcon from '@mui/icons-material/RemoveCircleOutline';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import GroupIcon from '@mui/icons-material/Group';
import AirlineSeatReclineNormalIcon from '@mui/icons-material/AirlineSeatReclineNormal';
import { setAdultCount, setChildCount } from "../../../slice/port/pickupDropSlice";

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

const Counter = ({ name, value, minValue, onCounterChange, maxValue, ageDescription, icon, disabled }) => {
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
              disabled={value <= minValue}
              sx={{ 
                color: value <= minValue ? 'text.disabled' : 'primary.main',
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
        <Tooltip title={disabled ? "Maximum seating capacity reached" : value >= maxValue ? "Maximum reached" : "Increase"}>
          <span>
            <IconButton
              size="small"
              onClick={() => onCounterChange(name, Math.min(value + 1, maxValue))}
              disabled={disabled || value >= maxValue}
              sx={{ 
                color: (disabled || value >= maxValue) ? 'text.disabled' : 'primary.main',
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

const Passenger = ({ 
  adultsMax, 
  childrenMax, 
  seatingCapacity,
  initialAdults,
  initialChildren,
  onAdultChange,
  onChildChange
}) => {
  const dispatch = useDispatch();
  const [anchorEl, setAnchorEl] = useState(null);
  
  // Default to 1 and 0 if no max values provided
  const maxAdults = adultsMax || 1;
  const maxChildren = childrenMax || 0;
  
  // Set initial values from props or get from Redux store
  const reduxAdultCount = useSelector((state) => state.pickupDrop.adultCount);
  const reduxChildCount = useSelector((state) => state.pickupDrop.childCount);
  
  // Use provided initial values if available, otherwise use Redux state
  const [adults, setAdults] = useState(initialAdults !== undefined ? initialAdults : reduxAdultCount || 1);
  const [children, setChildren] = useState(initialChildren !== undefined ? initialChildren : reduxChildCount || 0);
  const [showCapacityAlert, setShowCapacityAlert] = useState(false);

  // Update the component state when Redux state changes
  // Only if we're not handling local state independently (no custom handlers)
  useEffect(() => {
    // Only sync with Redux if we're not handling state independently
    if (initialAdults === undefined && reduxAdultCount !== undefined && reduxAdultCount !== adults) {
      setAdults(reduxAdultCount);
    }
  }, [reduxAdultCount, adults, initialAdults]);
  
  useEffect(() => {
    // Only sync with Redux if we're not handling state independently
    if (initialChildren === undefined && reduxChildCount !== undefined && reduxChildCount !== children) {
      setChildren(reduxChildCount);
    }
  }, [reduxChildCount, children, initialChildren]);
  
  // Calculate total passengers and remaining capacity
  const totalPassengers = adults + children;
  const atCapacity = seatingCapacity > 0 && totalPassengers >= seatingCapacity;
  
  // Check if we're at capacity whenever totalPassengers or seatingCapacity changes
  useEffect(() => {
    if (seatingCapacity > 0) {
      if (totalPassengers > seatingCapacity) {
        // If we somehow exceed capacity, adjust values
        const excessPassengers = totalPassengers - seatingCapacity;
        if (children >= excessPassengers) {
          setChildren(children - excessPassengers);
        } else {
          // If we can't reduce children enough, adjust adults (keeping minimum of 1)
          const remainingExcess = excessPassengers - children;
          setChildren(0);
          setAdults(Math.max(1, adults - remainingExcess));
        }
        setShowCapacityAlert(true);
      } else if (totalPassengers === seatingCapacity) {
        setShowCapacityAlert(true);
      } else {
        setShowCapacityAlert(false);
      }
    }
  }, [totalPassengers, seatingCapacity, adults, children]);

  // Update Redux when values change if callbacks aren't provided
  useEffect(() => {
    if (onAdultChange) {
      onAdultChange(adults);
    } else {
      dispatch(setAdultCount(adults));
    }
  }, [adults, dispatch, onAdultChange]);

  useEffect(() => {
    if (onChildChange) {
      onChildChange(children);
    } else {
      dispatch(setChildCount(children));
    }
  }, [children, dispatch, onChildChange]);

  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleCounterChange = (name, value) => {
    if (name === "Adults") {
      // Calculate new total if we change adults
      const newTotal = value + children;
      
      // Only allow the change if it doesn't exceed seating capacity
      if (seatingCapacity === 0 || newTotal <= seatingCapacity) {
        setAdults(value);
      }
    } else if (name === "Children") {
      // Calculate new total if we change children
      const newTotal = adults + value;
      
      // Only allow the change if it doesn't exceed seating capacity
      if (seatingCapacity === 0 || newTotal <= seatingCapacity) {
        setChildren(value);
      }
    }
  };

  // Calculate maximum allowed values based on remaining capacity
  const getMaxValue = (type) => {
    if (seatingCapacity === 0) {
      // If no seating capacity is set, use the provided max values
      return type === "Adults" ? maxAdults : maxChildren;
    }
    
    const remainingCapacity = seatingCapacity - totalPassengers;
    
    if (type === "Adults") {
      // For adults, the max is current value + remaining capacity (but not exceeding maxAdults)
      return Math.min(adults + remainingCapacity, maxAdults);
    } else {
      // For children, the max is current value + remaining capacity (but not exceeding maxChildren)
      return Math.min(children + remainingCapacity, maxChildren);
    }
  };

  const open = Boolean(anchorEl);
  const id = open ? 'pax-popover' : undefined;

  return (
    <Grid item xs={12} sm={6} md={3}>
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
                {adults}
              </Typography>
            </Box>

            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <ChildCareIcon sx={{ color: 'primary.main' }} />
              <Typography>
                {children}
              </Typography>
            </Box>
            
            {seatingCapacity > 0 && (
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, ml: 'auto' }}>
                <AirlineSeatReclineNormalIcon sx={{ color: atCapacity ? 'error.main' : 'text.secondary' }} />
                <Typography color={atCapacity ? 'error.main' : 'text.secondary'}>
                  {totalPassengers}/{seatingCapacity}
                </Typography>
              </Box>
            )}
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
              Number of passengers
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              {seatingCapacity > 0 && (
                <>
                  <AirlineSeatReclineNormalIcon 
                    fontSize="small" 
                    sx={{ color: atCapacity ? 'error.main' : 'text.secondary' }} 
                  />
                  <Typography variant="subtitle2" color={atCapacity ? 'error.main' : 'text.secondary'}>
                    {totalPassengers}/{seatingCapacity}
                  </Typography>
                </>
              )}
            </Box>
          </Box>

          {showCapacityAlert && seatingCapacity > 0 && (
            <Alert 
              severity="warning" 
              sx={{ mb: 2 }}
              onClose={() => setShowCapacityAlert(false)}
            >
              Maximum seating capacity reached
            </Alert>
          )}

          <Counter
            name="Adults"
            value={adults}
            minValue={1}
            maxValue={getMaxValue("Adults")}
            onCounterChange={handleCounterChange}
            ageDescription="Age 12+"
            icon={<PersonIcon sx={{ color: 'primary.main' }} />}
            disabled={atCapacity}
          />

          <Counter
            name="Children"
            value={children}
            minValue={0}
            maxValue={getMaxValue("Children")}
            onCounterChange={handleCounterChange}
            ageDescription="Ages 0-11"
            icon={<ChildCareIcon sx={{ color: 'primary.main' }} />}
            disabled={atCapacity}
          />

          {seatingCapacity > 0 && (
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mt: 2, mb: 2 }}>
              <AirlineSeatReclineNormalIcon fontSize="small" sx={{ color: 'text.secondary' }} />
              <Typography variant="body2" color="text.secondary">
                Vehicle capacity: {seatingCapacity} passengers
              </Typography>
            </Box>
          )}

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

export default Passenger;