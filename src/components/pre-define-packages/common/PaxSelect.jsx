import React, { useState, useEffect } from 'react';
import { 
  Box, 
  Typography, 
  Button,
  Grid,
  Divider,
  Paper,
  Avatar,
  IconButton,
  List,
  ListItem,
  ListItemText,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Collapse
} from '@mui/material';
import {
  Person as PersonIcon,
  ChildCare as ChildCareIcon,
  BabyChangingStation as InfantIcon,
  Male as MaleIcon,
  Female as FemaleIcon,
  Add as AddIcon,
  Remove as RemoveIcon,
  KeyboardArrowDown as DownIcon
} from '@mui/icons-material';

const Counter = ({
  name,
  value,
  onCounterChange,
  minValue = 0,
  maxValue = 10,
  icon,
  subLabel,
  disabled = false,
  genderCounts,
  onGenderCountChange,
  totalAdults
}) => {
  const handleIncrement = () => {
    onCounterChange(name, value + 1);
  };

  const handleDecrement = () => {
    onCounterChange(name, value - 1);
  };

  const handleGenderIncrement = (gender) => {
    if (gender === "Male") {
      onGenderCountChange("Male", (genderCounts?.maleCount || 0) + 1, "autoBalance");
    } else {
      onGenderCountChange("Female", (genderCounts?.femaleCount || 0) + 1, "autoBalance");
    }
  };

  const handleGenderDecrement = (gender) => {
    if (gender === "Male") {
      onGenderCountChange("Male", (genderCounts?.maleCount || 0) - 1, "autoBalance");
    } else {
      onGenderCountChange("Female", (genderCounts?.femaleCount || 0) - 1, "autoBalance");
    }
  };

  return (
    <Box sx={{ mb: 2 }}>
      <Grid container justifyContent="space-between" alignItems="center">
        <Grid item>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            {icon}
            <Box sx={{ ml: 1 }}>
              <Typography variant="subtitle1" fontWeight={500}>{name}</Typography>
              {subLabel && (
                <Typography variant="caption" color="text.secondary">{subLabel}</Typography>
              )}
            </Box>
          </Box>
        </Grid>
        <Grid item>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <Button 
              variant="outlined" 
              color="primary" 
              size="small"
              onClick={handleDecrement}
              disabled={value <= minValue || disabled}
              sx={{ minWidth: '36px', width: '36px', height: '36px', p: 0 }}
            >
              <RemoveIcon fontSize="small" />
            </Button>
            <Typography sx={{ mx: 2, minWidth: '20px', textAlign: 'center' }}>
              {value}
            </Typography>
            <Button 
              variant="outlined" 
              color="primary" 
              size="small"
              onClick={handleIncrement}
              disabled={value >= maxValue || disabled}
              sx={{ minWidth: '36px', width: '36px', height: '36px', p: 0 }}
            >
              <AddIcon fontSize="small" />
            </Button>
          </Box>
        </Grid>
      </Grid>

      {/* Gender Selection for Adults */}
      {name === "Adults" && value > 0 && (
        <Paper variant="outlined" sx={{ mt: 1, p: 1.5, bgcolor: 'rgba(0,0,0,0.02)' }}>
          <Box>
            {/* Male Count */}
            <Grid container alignItems="center" justifyContent="space-between" sx={{ mb: 1 }}>
              <Grid item>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <Avatar sx={{ bgcolor: 'primary.light', width: 28, height: 28, mr: 1 }}>
                    <MaleIcon fontSize="small" />
                  </Avatar>
                  <Typography variant="body2" fontWeight={500}>Male</Typography>
                </Box>
              </Grid>
              <Grid item>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => handleGenderDecrement("Male")}
                    disabled={(genderCounts?.maleCount || 0) <= 0 || disabled}
                    sx={{ minWidth: '30px', width: '30px', height: '30px', p: 0 }}
                  >
                    <RemoveIcon fontSize="small" />
                  </Button>
                  <Typography sx={{ mx: 2, minWidth: '20px', textAlign: 'center' }}>
                    {genderCounts?.maleCount || 0}
                  </Typography>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => handleGenderIncrement("Male")}
                    disabled={(genderCounts?.maleCount || 0) >= totalAdults || disabled}
                    sx={{ minWidth: '30px', width: '30px', height: '30px', p: 0 }}
                  >
                    <AddIcon fontSize="small" />
                  </Button>
                </Box>
              </Grid>
            </Grid>

            {/* Female Count */}
            <Grid container alignItems="center" justifyContent="space-between">
              <Grid item>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <Avatar sx={{ bgcolor: 'secondary.light', width: 28, height: 28, mr: 1 }}>
                    <FemaleIcon fontSize="small" />
                  </Avatar>
                  <Typography variant="body2" fontWeight={500}>Female</Typography>
                </Box>
              </Grid>
              <Grid item>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => handleGenderDecrement("Female")}
                    disabled={(genderCounts?.femaleCount || 0) <= 0 || disabled}
                    sx={{ minWidth: '30px', width: '30px', height: '30px', p: 0 }}
                  >
                    <RemoveIcon fontSize="small" />
                  </Button>
                  <Typography sx={{ mx: 2, minWidth: '20px', textAlign: 'center' }}>
                    {genderCounts?.femaleCount || 0}
                  </Typography>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => handleGenderIncrement("Female")}
                    disabled={(genderCounts?.femaleCount || 0) >= totalAdults || disabled}
                    sx={{ minWidth: '30px', width: '30px', height: '30px', p: 0 }}
                  >
                    <AddIcon fontSize="small" />
                  </Button>
                </Box>
              </Grid>
            </Grid>
          </Box>
        </Paper>
      )}

      {/* Child Age Selection */}
      {name === "Children" && value > 0 && (
        <Box
          sx={{
            mt: 1,
            maxHeight: '120px',
            overflowY: 'auto',
            pr: 1,
            '&::-webkit-scrollbar': {
              width: '6px',
            },
            '&::-webkit-scrollbar-track': {
              background: '#f1f1f1',
              borderRadius: '10px',
            },
            '&::-webkit-scrollbar-thumb': {
              background: '#888',
              borderRadius: '10px',
            },
          }}
        >
          {Array.from({ length: value }).map((_, i) => (
            <Box
              key={i}
              sx={{
                p: 1,
                mb: 0.5,
                bgcolor: 'rgba(0,0,0,0.02)',
                border: '1px solid #ddd',
                borderRadius: 1,
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center'
              }}
            >
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <Avatar sx={{ bgcolor: 'primary.light', width: 24, height: 24, mr: 1 }}>
                  <ChildCareIcon sx={{ fontSize: 16 }} />
                </Avatar>
                <Typography variant="body2" fontSize="0.85rem">Child {i + 1}</Typography>
              </Box>
              <FormControl size="small" sx={{ minWidth: 80 }}>
                <Select
                  value={genderCounts?.ages?.[i] || ""}
                  onChange={(e) => onCounterChange('childAge', e.target.value, i)}
                  displayEmpty
                  sx={{ height: 28 }}
                >
                  <MenuItem value="" disabled><em>Age</em></MenuItem>
                  {Array.from({ length: 17 }).map((_, age) => (
                    <MenuItem key={age + 1} value={`${age + 1}`}>{age + 1}</MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Box>
          ))}
        </Box>
      )}
    </Box>
  );
};

const PaxSelect = ({ onChange, value = { Adults: 1, Children: 0, Infants: 0 }, label = "Passengers" }) => {
  const [expanded, setExpanded] = useState(false);
  const [guestCounts, setGuestCounts] = useState({
    Adults: value.Adults || 1,
    Children: value.Children || 0,
    Infants: value.Infants || 0,
    maleCount: value.maleCount || 0,
    femaleCount: value.femaleCount || 0,
    ages: value.ages || []
  });

  // Update internal state when props change
  useEffect(() => {
    if (value) {
      setGuestCounts({
        Adults: value.Adults !== undefined ? value.Adults : 1,
        Children: value.Children !== undefined ? value.Children : 0,
        Infants: value.Infants !== undefined ? value.Infants : 0,
        maleCount: value.maleCount !== undefined ? value.maleCount : 0,
        femaleCount: value.femaleCount !== undefined ? value.femaleCount : 0,
        ages: value.ages || []
      });
    }
  }, [value]);

  const handleCounterChange = (name, newValue, index) => {
    // Handle child ages specially
    if (name === 'childAge') {
      const newAges = [...(guestCounts.ages || [])];
      newAges[index] = newValue;
      
      const updatedCounts = {
        ...guestCounts,
        ages: newAges
      };
      
      setGuestCounts(updatedCounts);
      if (onChange) onChange(updatedCounts);
      return;
    }
    
    // Apply constraints for guest counts
    if (name === 'Adults') {
      // Minimum 1 adult
      newValue = Math.max(1, newValue);
      newValue = Math.min(10, newValue); // Max 10 adults
      
      // When changing adult count, handle gender counts
      const currentMaleCount = guestCounts.maleCount || 0;
      const currentFemaleCount = guestCounts.femaleCount || 0;
      const currentTotal = currentMaleCount + currentFemaleCount;
      
      let newMaleCount = currentMaleCount;
      let newFemaleCount = currentFemaleCount;
      
      if (currentTotal > 0) {
        // Adjust the gender counts proportionally
        const maleRatio = currentMaleCount / currentTotal;
        newMaleCount = Math.round(newValue * maleRatio);
        newFemaleCount = newValue - newMaleCount;
      }
      
      const updatedCounts = {
        ...guestCounts,
        Adults: newValue,
        maleCount: newMaleCount,
        femaleCount: newFemaleCount
      };
      
      setGuestCounts(updatedCounts);
      if (onChange) onChange(updatedCounts);
      return;
    } 
    else if (name === 'Children') {
      // Children can be 0 or more
      newValue = Math.max(0, newValue);
      newValue = Math.min(10, newValue); // Max 10 children
      
      // Handle ages array
      const currentAges = [...(guestCounts.ages || [])];
      
      if (newValue > currentAges.length) {
        // Add empty ages for new children
        for (let i = currentAges.length; i < newValue; i++) {
          currentAges.push("");
        }
      } else if (newValue < currentAges.length) {
        // Remove ages for removed children
        currentAges.splice(newValue);
      }
      
      const updatedCounts = {
        ...guestCounts,
        Children: newValue,
        ages: currentAges
      };
      
      setGuestCounts(updatedCounts);
      if (onChange) onChange(updatedCounts);
      return;
    }
    else if (name === 'Infants') {
      // Infants can be 0 or more
      newValue = Math.max(0, newValue);
      newValue = Math.min(5, newValue); // Max 5 infants
    }
    
    // Generic case
    const updatedCounts = {
      ...guestCounts,
      [name]: newValue
    };
    
    setGuestCounts(updatedCounts);
    if (onChange) onChange(updatedCounts);
  };

  const handleGenderCountChange = (gender, count, action = null) => {
    const totalAdults = guestCounts.Adults || 1;
    const currentMaleCount = guestCounts.maleCount || 0;
    const currentFemaleCount = guestCounts.femaleCount || 0;
    
    let newMaleCount = currentMaleCount;
    let newFemaleCount = currentFemaleCount;

    if (action === "autoBalance") {
      if (gender === "Male") {
        // Set the new male count
        newMaleCount = Math.min(Math.max(0, count), totalAdults);
        // Female is always the remainder to make exactly totalAdults
        newFemaleCount = totalAdults - newMaleCount;
      } else {
        // Set the new female count
        newFemaleCount = Math.min(Math.max(0, count), totalAdults);
        // Male is always the remainder to make exactly totalAdults
        newMaleCount = totalAdults - newFemaleCount;
      }
    }

    const updatedCounts = {
      ...guestCounts,
      maleCount: newMaleCount,
      femaleCount: newFemaleCount
    };
    
    setGuestCounts(updatedCounts);
    if (onChange) onChange(updatedCounts);
  };

  // Get display text for the pax selection
  const getPaxDisplayText = () => {
    const parts = [];
    
    if (guestCounts.Adults > 0) {
      const adultText = `${guestCounts.Adults} ${guestCounts.Adults === 1 ? 'Adult' : 'Adults'}`;
      const maleCount = guestCounts.maleCount || 0;
      const femaleCount = guestCounts.femaleCount || 0;
      
      if (maleCount > 0 || femaleCount > 0) {
        parts.push(`${adultText} (${maleCount}M, ${femaleCount}F)`);
      } else {
        parts.push(adultText);
      }
    }
    
    if (guestCounts.Children > 0) {
      parts.push(`${guestCounts.Children} ${guestCounts.Children === 1 ? 'Child' : 'Children'}`);
    }
    
    if (guestCounts.Infants > 0) {
      parts.push(`${guestCounts.Infants} ${guestCounts.Infants === 1 ? 'Infant' : 'Infants'}`);
    }
    
    return parts.join(', ');
  };

  const toggleExpanded = () => {
    setExpanded(!expanded);
  };

  return (
    <Box sx={{ minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
      
      <Box 
        onClick={toggleExpanded}
        sx={{ 
          position: 'relative',
          border: '1px solid #ddd',
          borderRadius: '4px',
          p: 1.5,
          cursor: 'pointer',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          '&:hover': {
            borderColor: '#aaa',
          },
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <PersonIcon sx={{ mr: 1, color: 'text.secondary' }} />
          <Typography>{getPaxDisplayText()}</Typography>
        </Box>
        <IconButton 
          size="small"
          sx={{ 
            transform: expanded ? 'rotate(180deg)' : 'rotate(0deg)',
            transition: 'transform 0.3s'
          }}
        >
          <DownIcon />
        </IconButton>
      </Box>
      
      <Collapse in={expanded}>
        <Paper 
          elevation={3}
          sx={{ 
            mt: 0.5, 
            p: 2,
            boxShadow: '0 4px 20px rgba(0,0,0,0.1)'
          }}
        >
          {/* Adults Counter */}
          <Counter
            name="Adults"
            value={guestCounts.Adults}
            onCounterChange={handleCounterChange}
            minValue={1}
            maxValue={10}
            icon={<PersonIcon color="primary" />}
            subLabel="Ages 12+"
            genderCounts={{
              maleCount: guestCounts.maleCount || 0,
              femaleCount: guestCounts.femaleCount || 0
            }}
            onGenderCountChange={handleGenderCountChange}
            totalAdults={guestCounts.Adults}
          />
          
          <Divider sx={{ my: 1.5 }} />
          
          {/* Children Counter */}
          <Counter
            name="Children"
            value={guestCounts.Children}
            onCounterChange={handleCounterChange}
            minValue={0}
            maxValue={10}
            icon={<ChildCareIcon color="primary" />}
            subLabel="Ages 2-11 years"
            genderCounts={guestCounts}
          />
          
          <Divider sx={{ my: 1.5 }} />
          
          {/* Infants Counter */}
          <Counter
            name="Infants"
            value={guestCounts.Infants}
            onCounterChange={handleCounterChange}
            minValue={0}
            maxValue={5}
            icon={<InfantIcon color="primary" />}
            subLabel="Under 2 years"
          />
          
          {/* <Box sx={{ mt: 2, display: 'flex', justifyContent: 'flex-end' }}>
            <Button 
              variant="contained" 
              color="primary" 
              onClick={toggleExpanded}
              size="small"
            >
              Done
            </Button>
          </Box> */}
        </Paper>
      </Collapse>
    </Box>
  );
};

export default PaxSelect; 