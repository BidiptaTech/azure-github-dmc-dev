import React, { useState } from "react";
import {
  Box,
  Typography,
  Button,
  Grid,
  Divider,
  Select,
  MenuItem,
  FormControl,
  Paper,
  Avatar,
} from "@mui/material";
import {
  Person as PersonIcon,
  ChildCare as ChildCareIcon,
  Male as MaleIcon,
  Female as FemaleIcon,
  Add as AddIcon,
  Remove as RemoveIcon,
} from '@mui/icons-material';

const counters = [
  { name: "Adults", defaultValue: 1 },
  { name: "Children", defaultValue: 0 },
  { name: "Infants", defaultValue: 0 },
];

const Counter = ({
  name,
  defaultValue,
  onCounterChange,
  ages,
  onAgeChange,
  onGenderCountChange,
  maleCount = 0,
  femaleCount = 0,
  totalAdults = 1,
}) => {
  const [count, setCount] = useState(defaultValue);

  const incrementCount = () => {
    const newCount = count + 1;
    setCount(newCount);
    onCounterChange(name, newCount);
  };

  const decrementCount = () => {
    // For Adults, prevent going below 1
    if (name === "Adults" && count <= 1) {
      return;
    }
    // For other categories, prevent going below 0
    if (count > 0) {
      const newCount = count - 1;
      setCount(newCount);
      onCounterChange(name, newCount);
    }
  };

  const incrementGenderCount = (gender) => {
    if (gender === "Male") {
      // When incrementing male, automatically adjust female to maintain total
      onGenderCountChange("Male", maleCount + 1, "autoBalance");
    } else {
      // When incrementing female, automatically adjust male to maintain total
      onGenderCountChange("Female", femaleCount + 1, "autoBalance");
    }
  };

  const decrementGenderCount = (gender) => {
    if (gender === "Male" && maleCount > 0) {
      // When decrementing male, automatically adjust female
      onGenderCountChange("Male", maleCount - 1, "autoBalance");
    } else if (gender === "Female" && femaleCount > 0) {
      // When decrementing female, automatically adjust male
      onGenderCountChange("Female", femaleCount - 1, "autoBalance");
    }
  };

  return (
    <Box sx={{ mb: 1 }}>
      <Grid container justifyContent="space-between" alignItems="center">
        <Grid item>
          <Box>
            <Typography variant="subtitle1" fontWeight={500}>{name}</Typography>
            {name === "Children" && (
              <Typography variant="caption" color="text.secondary">Ages 1 - 17</Typography>
            )}
            {name === "Infants" && (
              <Typography variant="caption" color="text.secondary">Younger than 1 year old</Typography>
            )}
          </Box>
        </Grid>
        <Grid item>
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <Button 
              variant="outlined" 
              color="primary" 
              size="small"
              onClick={decrementCount}
              disabled={(name === "Adults" && count <= 1) || (name !== "Adults" && count <= 0)}
              sx={{ minWidth: '36px', width: '36px', height: '36px', p: 0 }}
            >
              <RemoveIcon fontSize="small" />
            </Button>
            <Typography sx={{ mx: 2, minWidth: '20px', textAlign: 'center' }}>
              {count}
            </Typography>
            <Button 
              variant="outlined" 
              color="primary" 
              size="small"
              onClick={incrementCount}
              sx={{ minWidth: '36px', width: '36px', height: '36px', p: 0 }}
            >
              <AddIcon fontSize="small" />
            </Button>
          </Box>
        </Grid>
      </Grid>

      {/* Gender Selection for Adults */}
      {name === "Adults" && count > 0 && (
        <Paper variant="outlined" sx={{ mt: 1, p: 1.5, bgcolor: 'rgba(0,0,0,0.02)' }}>
          <Box>
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
                    onClick={() => decrementGenderCount("Male")}
                    disabled={maleCount === 0}
                    sx={{ minWidth: '30px', width: '30px', height: '30px', p: 0 }}
                  >
                    <RemoveIcon fontSize="small" />
                  </Button>
                  <Typography sx={{ mx: 2, minWidth: '20px', textAlign: 'center' }}>
                    {maleCount}
                  </Typography>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => incrementGenderCount("Male")}
                    disabled={maleCount === totalAdults}
                    sx={{ minWidth: '30px', width: '30px', height: '30px', p: 0 }}
                  >
                    <AddIcon fontSize="small" />
                  </Button>
                </Box>
              </Grid>
            </Grid>

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
                    onClick={() => decrementGenderCount("Female")}
                    disabled={femaleCount === 0}
                    sx={{ minWidth: '30px', width: '30px', height: '30px', p: 0 }}
                  >
                    <RemoveIcon fontSize="small" />
                  </Button>
                  <Typography sx={{ mx: 2, minWidth: '20px', textAlign: 'center' }}>
                    {femaleCount}
                  </Typography>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => incrementGenderCount("Female")}
                    disabled={femaleCount === totalAdults}
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

      {/* Age Selection for Children */}
      {name === "Children" && count > 0 && (
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
          {Array.from({ length: count }).map((_, i) => (
            <Paper 
              key={i} 
              variant="outlined" 
              sx={{ 
                p: 0.75, 
                mb: 0.5, 
                bgcolor: 'rgba(0,0,0,0.02)',
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
              <Box>
                <select
                  className="custom-select"
                  value={ages[i] || ""}
                  onChange={(e) => onAgeChange(i, e.target.value)}
                  style={{
                    minWidth: '100px',
                    padding: '4px 8px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    fontSize: '0.85rem',
                    height: '32px',
                    background: 'white',
                    cursor: 'pointer',
                    appearance: 'none',
                    backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%233554d1' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E")`,
                    backgroundRepeat: 'no-repeat',
                    backgroundPosition: 'right 8px center',
                    paddingRight: '24px',
                  }}
                >
                  <option value="">Age</option>
                  {[...Array(17)].map((_, age) => (
                    <option key={age + 1} value={age + 1}>
                      {age + 1} {age === 0 ? "year" : "years"}
                    </option>
                  ))}
                </select>
              </Box>
            </Paper>
          ))}
        </Box>
      )}

      <Divider sx={{ mt: 1, mb: 1 }} />
    </Box>
  );
};

const PaxSelector = ({ onGuestChange, guestCounts = { Adults: 1, Children: 0, Infants: 0, maleCount: 0, femaleCount: 0 } }) => {
  const handleCounterChange = (name, value) => {
    const updatedGuestCounts = { ...guestCounts, [name]: value };
                 
    if (name === "Adults") {
      // When changing adults, adjust the gender counts if they're already set
      const currentMaleCount = guestCounts.maleCount || 0;
      const currentFemaleCount = guestCounts.femaleCount || 0;
      const currentTotal = currentMaleCount + currentFemaleCount;
      
      // If we already have gender distribution, maintain the proportions
      if (currentTotal > 0) {
        const maleRatio = currentMaleCount / currentTotal;
        const femaleRatio = currentFemaleCount / currentTotal;
        
        updatedGuestCounts.maleCount = Math.round(value * maleRatio);
        updatedGuestCounts.femaleCount = value - updatedGuestCounts.maleCount; // Ensure they add up exactly
      } else {
        // Keep them at 0 if they were 0 before
        updatedGuestCounts.maleCount = 0;
        updatedGuestCounts.femaleCount = 0;
      }
    }

    if (name === "Children") {
      const newAges = [...(guestCounts.ages || [])];
      if (value > newAges.length) {
        newAges.push(...new Array(value - newAges.length).fill(""));
      } else {
        newAges.splice(value);
      }
      updatedGuestCounts.ages = newAges;
    }

    if (name === "Infants") {
      // Just update the count, no age selection needed
    }

    onGuestChange(updatedGuestCounts);
  };

  const handleGenderCountChange = (gender, count, action = null) => {
    const updatedGuestCounts = { ...guestCounts };
    const totalAdults = guestCounts.Adults || 1;
    const currentMaleCount = guestCounts.maleCount || 0;
    const currentFemaleCount = guestCounts.femaleCount || 0;
    
    if (action === "autoBalance") {
      // Auto-balance mode: when increasing one gender, automatically set the other
      if (gender === "Male") {
        // Set the new male count
        updatedGuestCounts.maleCount = Math.min(count, totalAdults);
        // Female is always the remainder to make exactly totalAdults
        updatedGuestCounts.femaleCount = totalAdults - updatedGuestCounts.maleCount;
      } else {
        // Set the new female count
        updatedGuestCounts.femaleCount = Math.min(count, totalAdults);
        // Male is always the remainder to make exactly totalAdults
        updatedGuestCounts.maleCount = totalAdults - updatedGuestCounts.femaleCount;
      }
    } else {
      // Manual mode (shouldn't be used with current UI)
      if (gender === "Male") {
        updatedGuestCounts.maleCount = Math.min(count, totalAdults);
        // Ensure we don't exceed total
        if (updatedGuestCounts.maleCount + currentFemaleCount > totalAdults) {
          updatedGuestCounts.femaleCount = totalAdults - updatedGuestCounts.maleCount;
        }
      } else {
        updatedGuestCounts.femaleCount = Math.min(count, totalAdults);
        // Ensure we don't exceed total
        if (updatedGuestCounts.maleCount + updatedGuestCounts.femaleCount > totalAdults) {
          updatedGuestCounts.maleCount = totalAdults - updatedGuestCounts.femaleCount;
        }
      }
    }
    
    onGuestChange(updatedGuestCounts);
  };

  const handleAgeChange = (index, selectedAge) => {
    const updatedAges = [...(guestCounts.ages || [])];
    updatedAges[index] = selectedAge;
    onGuestChange({ ...guestCounts, ages: updatedAges });
  };

  const handleInfantAgeChange = (index, selectedAge) => {
    const updatedInfantAges = [...(guestCounts.infantAges || [])];
    updatedInfantAges[index] = selectedAge;
    onGuestChange({ ...guestCounts, infantAges: updatedInfantAges });
  };

  return (
    <Box className="searchMenu-guests px-30 lg:py-20 lg:px-0 js-form-dd js-form-counters position-relative" sx={{ zIndex: 1000 }}>
      <Box
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false"
        data-bs-offset="0,22"
      >
        <Typography variant="subtitle2" sx={{ mb: 0.5 }}>Guest</Typography>
        <Typography variant="body2" color="text.secondary">
          {guestCounts.Adults} adults
          ({guestCounts.maleCount || 0} male, {guestCounts.femaleCount || 0} female) -
          {guestCounts.Children} children -
          {guestCounts.Infants} infants
        </Typography>
      </Box>

      <Box
        className="shadow-2 dropdown-menu min-width-400"
        sx={{
          maxHeight: '80vh',
          border: 'none',
          borderRadius: '16px',
          boxShadow: '0 20px 40px rgba(0,0,0,0.1)',
          zIndex: 1100, 
          position: 'absolute !important',
        }}
      >
        <Box
          className="bg-white px-30 py-30 rounded-4 counter-box"
          sx={{
            maxHeight: '80vh',
            overflowY: 'auto',
            background: 'linear-gradient(to bottom, #ffffff, #f8f9fa)',
            borderRadius: '16px',
            position: 'relative',
            py: 1.5,
            px: 2,
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
          {counters.map((counter) => (
            <Counter
              key={counter.name}
              name={counter.name}
              defaultValue={guestCounts[counter.name] || counter.defaultValue}
              onCounterChange={handleCounterChange}
              onGenderCountChange={handleGenderCountChange}
              maleCount={guestCounts.maleCount || 0}
              femaleCount={guestCounts.femaleCount || 0}
              totalAdults={guestCounts.Adults || 1}
              ages={counter.name === "Children" ? (guestCounts.ages || []) : 
                    counter.name === "Infants" ? (guestCounts.infantAges || []) : []}
              onAgeChange={counter.name === "Children" ? handleAgeChange : handleInfantAgeChange}
            />
          ))}
        </Box>
      </Box>

      <style jsx>{`
        :global(.searchMenu-guests .dropdown-menu) {
          top: 100% !important;
          bottom: auto !important;
          transform: none !important;
          z-index: 9999 !important;
        }
      `}</style>
    </Box>
  );
};

export default PaxSelector;
