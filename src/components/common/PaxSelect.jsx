import React, { useState, useEffect } from 'react';
import { 
  Box, 
  Typography, 
  IconButton, 
  styled,
  Paper
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import BabyChangingStationIcon from '@mui/icons-material/BabyChangingStation';

const CounterBox = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  padding: theme.spacing(1),
  marginBottom: theme.spacing(2),
  borderBottom: `1px solid ${theme.palette.divider}`
}));

const PaxSelect = ({ onChange, value = { Adults: 1, Children: 0, Infants: 0 }, label = "Passengers" }) => {
  const [guestCounts, setGuestCounts] = useState({
    Adults: value.Adults || 1,
    Children: value.Children || 0,
    Infants: value.Infants || 0
  });

  // Update internal state when props change
  useEffect(() => {
    if (value) {
      setGuestCounts({
        Adults: value.Adults !== undefined ? value.Adults : 1,
        Children: value.Children !== undefined ? value.Children : 0,
        Infants: value.Infants !== undefined ? value.Infants : 0
      });
    }
  }, [value]);

  const handleCounterChange = (name, newValue) => {
    // Apply constraints
    if (name === 'Adults') {
      // Minimum 1 adult
      newValue = Math.max(1, newValue);
    } else {
      // Children and infants can be 0 or more
      newValue = Math.max(0, newValue);
    }
    
    // Maximum value constraints (optional)
    if (name === 'Adults') {
      newValue = Math.min(10, newValue); // Maximum 10 adults
    } else if (name === 'Children') {
      newValue = Math.min(10, newValue); // Maximum 10 children
    } else if (name === 'Infants') {
      newValue = Math.min(5, newValue); // Maximum 5 infants
    }
    
    const updatedCounts = {
      ...guestCounts,
      [name]: newValue
    };
    
    setGuestCounts(updatedCounts);
    
    if (onChange) {
      onChange(updatedCounts);
    }
  };

  // Get display text for the pax selection
  const getPaxDisplayText = () => {
    const parts = [];
    if (guestCounts.Adults > 0) {
      parts.push(`${guestCounts.Adults} ${guestCounts.Adults === 1 ? 'Adult' : 'Adults'}`);
    }
    if (guestCounts.Children > 0) {
      parts.push(`${guestCounts.Children} ${guestCounts.Children === 1 ? 'Child' : 'Children'}`);
    }
    if (guestCounts.Infants > 0) {
      parts.push(`${guestCounts.Infants} ${guestCounts.Infants === 1 ? 'Infant' : 'Infants'}`);
    }
    return parts.join(', ');
  };

  return (
    <Box sx={{ minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
      
      <Paper elevation={0} sx={{ 
        p: 2, 
        border: '1px solid #ddd', 
        borderRadius: 1 
      }}>
        {/* Adults Counter */}
        <CounterBox>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <PersonIcon sx={{ mr: 1 }} />
            <Typography>Adults</Typography>
          </Box>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <IconButton 
              size="small"
              onClick={() => handleCounterChange('Adults', guestCounts.Adults - 1)}
              disabled={guestCounts.Adults <= 1} // Min 1 adult
            >
              <RemoveIcon fontSize="small" />
            </IconButton>
            <Typography sx={{ mx: 2 }}>{guestCounts.Adults}</Typography>
            <IconButton 
              size="small"
              onClick={() => handleCounterChange('Adults', guestCounts.Adults + 1)}
              disabled={guestCounts.Adults >= 10} // Max 10 adults
            >
              <AddIcon fontSize="small" />
            </IconButton>
          </Box>
        </CounterBox>
        
        {/* Children Counter */}
        <CounterBox>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <ChildCareIcon sx={{ mr: 1 }} />
            <Typography>Children</Typography>
            <Typography variant="caption" color="text.secondary" sx={{ ml: 1 }}>
              (2-11 years)
            </Typography>
          </Box>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <IconButton 
              size="small"
              onClick={() => handleCounterChange('Children', guestCounts.Children - 1)}
              disabled={guestCounts.Children <= 0} // Min 0 children
            >
              <RemoveIcon fontSize="small" />
            </IconButton>
            <Typography sx={{ mx: 2 }}>{guestCounts.Children}</Typography>
            <IconButton 
              size="small"
              onClick={() => handleCounterChange('Children', guestCounts.Children + 1)}
              disabled={guestCounts.Children >= 10} // Max 10 children
            >
              <AddIcon fontSize="small" />
            </IconButton>
          </Box>
        </CounterBox>
        
        {/* Infants Counter */}
        <CounterBox sx={{ borderBottom: 'none', mb: 0 }}>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <BabyChangingStationIcon sx={{ mr: 1 }} />
            <Typography>Infants</Typography>
            <Typography variant="caption" color="text.secondary" sx={{ ml: 1 }}>
              (0-2 years)
            </Typography>
          </Box>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <IconButton 
              size="small"
              onClick={() => handleCounterChange('Infants', guestCounts.Infants - 1)}
              disabled={guestCounts.Infants <= 0} // Min 0 infants
            >
              <RemoveIcon fontSize="small" />
            </IconButton>
            <Typography sx={{ mx: 2 }}>{guestCounts.Infants}</Typography>
            <IconButton 
              size="small"
              onClick={() => handleCounterChange('Infants', guestCounts.Infants + 1)}
              disabled={guestCounts.Infants >= 5} // Max 5 infants
            >
              <AddIcon fontSize="small" />
            </IconButton>
          </Box>
        </CounterBox>
      </Paper>

      <Typography variant="body2" sx={{ mt: 1 }}>
        {getPaxDisplayText()}
      </Typography>
    </Box>
  );
};

export default PaxSelect; 