import React, { useState, useEffect } from "react";
import { createPortal } from "react-dom";
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
  ChildCare as ChildCareIcon,
  Male as MaleIcon,
  Female as FemaleIcon,
  Add as AddIcon,
  Remove as RemoveIcon,
} from "@mui/icons-material";

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
  disabled = false,
}) => {
  const [count, setCount] = useState(defaultValue);

  const incrementCount = () => {
    if (disabled) return;
    const newCount = count + 1;
    setCount(newCount);
    onCounterChange(name, newCount);
  };

  const decrementCount = () => {
    if (disabled) return;
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
    <Box sx={{ mb: { xs: 0.5, sm: 1 } }}>
      <Grid container justifyContent="space-between" alignItems="center">
        <Grid item xs={6} sm={8}>
          <Box>
            <Typography 
              variant="subtitle1" 
              fontWeight={500}
              sx={{ fontSize: { xs: '0.875rem', sm: '1rem' } }}
            >
              {name}
            </Typography>
            {name === "Children" && (
              <Typography 
                variant="caption" 
                color="text.secondary"
                sx={{ fontSize: { xs: '0.7rem', sm: '0.75rem' } }}
              >
                Ages 1 - 17
              </Typography>
            )}
            {name === "Infants" && (
              <Typography 
                variant="caption" 
                color="text.secondary"
                sx={{ fontSize: { xs: '0.7rem', sm: '0.75rem' } }}
              >
                Younger than 1 year old
              </Typography>
            )}
          </Box>
        </Grid>
        <Grid item xs={6} sm={4}>
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end' }}>
            <Button 
              variant="outlined" 
              color="primary" 
              size="small"
              onClick={decrementCount}
              disabled={disabled || (name === "Adults" && count <= 1) || (name !== "Adults" && count <= 0)}
              sx={{ 
                minWidth: { xs: '32px', sm: '36px' }, 
                width: { xs: '32px', sm: '36px' }, 
                height: { xs: '32px', sm: '36px' }, 
                p: 0 
              }}
            >
              <RemoveIcon sx={{ fontSize: { xs: '0.875rem', sm: '1rem' } }} />
            </Button>
            <Typography sx={{ 
              mx: { xs: 1.5, sm: 2 }, 
              minWidth: '20px', 
              textAlign: 'center',
              fontSize: { xs: '0.875rem', sm: '1rem' }
            }}>
              {count}
            </Typography>
            <Button 
              variant="outlined" 
              color="primary" 
              size="small"
              onClick={incrementCount}
              disabled={disabled}
              sx={{ 
                minWidth: { xs: '32px', sm: '36px' }, 
                width: { xs: '32px', sm: '36px' }, 
                height: { xs: '32px', sm: '36px' }, 
                p: 0 
              }}
            >
              <AddIcon sx={{ fontSize: { xs: '0.875rem', sm: '1rem' } }} />
            </Button>
          </Box>
        </Grid>
      </Grid>

      {/* Gender Selection for Adults */}
      {name === "Adults" && count > 0 && (
        <Paper variant="outlined" sx={{ 
          mt: { xs: 0.5, sm: 1 }, 
          p: { xs: 1, sm: 1.5 }, 
          bgcolor: 'rgba(0,0,0,0.02)' 
        }}>
          <Box>
            <Grid container alignItems="center" justifyContent="space-between" sx={{ mb: { xs: 0.5, sm: 1 } }}>
              <Grid item xs={6}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <Avatar sx={{ 
                    bgcolor: '#42A5F5', 
                    color: '#fff',
                    width: { xs: 24, sm: 28 }, 
                    height: { xs: 24, sm: 28 }, 
                    mr: { xs: 0.5, sm: 1 } 
                  }}>
                    <MaleIcon sx={{ fontSize: { xs: '0.875rem', sm: '1rem' }, color: '#fff' }} />
                  </Avatar>
                  <Typography 
                    variant="body2" 
                    fontWeight={500}
                    sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }}
                  >
                    Male
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={6}>
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end' }}>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => decrementGenderCount("Male")}
                    disabled={maleCount === 0}
                    sx={{ 
                      minWidth: { xs: '28px', sm: '30px' }, 
                      width: { xs: '28px', sm: '30px' }, 
                      height: { xs: '28px', sm: '30px' }, 
                      p: 0 
                    }}
                  >
                    <RemoveIcon sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }} />
                  </Button>
                  <Typography sx={{ 
                    mx: { xs: 1, sm: 2 }, 
                    minWidth: '20px', 
                    textAlign: 'center',
                    fontSize: { xs: '0.75rem', sm: '0.875rem' }
                  }}>
                    {maleCount > 0 ? maleCount : ''}
                  </Typography>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => incrementGenderCount("Male")}
                    disabled={maleCount === totalAdults}
                    sx={{ 
                      minWidth: { xs: '28px', sm: '30px' }, 
                      width: { xs: '28px', sm: '30px' }, 
                      height: { xs: '28px', sm: '30px' }, 
                      p: 0 
                    }}
                  >
                    <AddIcon sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }} />
                  </Button>
                </Box>
              </Grid>
            </Grid>

            <Grid container alignItems="center" justifyContent="space-between">
              <Grid item xs={6}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <Avatar sx={{ 
                    bgcolor: '#BA68C8', 
                    color: '#fff',
                    width: { xs: 24, sm: 28 }, 
                    height: { xs: 24, sm: 28 }, 
                    mr: { xs: 0.5, sm: 1 } 
                  }}>
                    <FemaleIcon sx={{ fontSize: { xs: '0.875rem', sm: '1rem' }, color: '#fff' }} />
                  </Avatar>
                  <Typography 
                    variant="body2" 
                    fontWeight={500}
                    sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }}
                  >
                    Female
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={6}>
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end' }}>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => decrementGenderCount("Female")}
                    disabled={femaleCount === 0}
                    sx={{ 
                      minWidth: { xs: '28px', sm: '30px' }, 
                      width: { xs: '28px', sm: '30px' }, 
                      height: { xs: '28px', sm: '30px' }, 
                      p: 0 
                    }}
                  >
                    <RemoveIcon sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }} />
                  </Button>
                  <Typography sx={{ 
                    mx: { xs: 1, sm: 2 }, 
                    minWidth: '20px', 
                    textAlign: 'center',
                    fontSize: { xs: '0.75rem', sm: '0.875rem' }
                  }}>
                    {femaleCount > 0 ? femaleCount : ''}
                  </Typography>
                  <Button 
                    variant="outlined" 
                    color="primary" 
                    size="small"
                    onClick={() => incrementGenderCount("Female")}
                    disabled={femaleCount === totalAdults}
                    sx={{ 
                      minWidth: { xs: '28px', sm: '30px' }, 
                      width: { xs: '28px', sm: '30px' }, 
                      height: { xs: '28px', sm: '30px' }, 
                      p: 0 
                    }}
                  >
                    <AddIcon sx={{ fontSize: { xs: '0.75rem', sm: '0.875rem' } }} />
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
            mt: { xs: 0.5, sm: 1 }, 
            maxHeight: { xs: '100px', sm: '120px' }, 
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
                p: { xs: 0.5, sm: 0.75 }, 
                mb: { xs: 0.25, sm: 0.5 }, 
                bgcolor: 'rgba(0,0,0,0.02)',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center'
              }}
            >
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <Avatar sx={{ 
                  bgcolor: 'primary.light', 
                  width: { xs: 20, sm: 24 }, 
                  height: { xs: 20, sm: 24 }, 
                  mr: { xs: 0.5, sm: 1 } 
                }}>
                  <ChildCareIcon sx={{ fontSize: { xs: 14, sm: 16 } }} />
                </Avatar>
                <Typography 
                  variant="body2" 
                  sx={{ fontSize: { xs: '0.75rem', sm: '0.85rem' } }}
                >
                  Child {i + 1}
                </Typography>
              </Box>
              <Box>
                <select
                  className="custom-select"
                  value={ages[i] || ""}
                  onChange={(e) => onAgeChange(i, e.target.value)}
                  style={{
                    minWidth: { xs: '80px', sm: '100px' },
                    padding: '4px 8px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    fontSize: { xs: '0.75rem', sm: '0.85rem' },
                    height: { xs: '28px', sm: '32px' },
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

const GuestSearch = ({ onGuestChange, guestCounts, disabled = false }) => {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    if (disabled) setOpen(false);
  }, [disabled]);

  useEffect(() => {
    if (!open) return undefined;
    const onKey = (e) => {
      if (e.key === "Escape") setOpen(false);
    };
    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    document.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = prevOverflow;
      document.removeEventListener("keydown", onKey);
    };
  }, [open]);

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

  const totalPax =
    (guestCounts.Adults || 0) +
    (guestCounts.Children || 0) +
    (guestCounts.Infants || 0);

  return (
    <Box
      className="searchMenu-guests js-form-dd js-form-counters position-relative"
      sx={{ zIndex: 1000, padding: "8px 14px" }}
    >
      <button
        type="button"
        className="guest-search-trigger cursor-pointer"
        aria-haspopup="dialog"
        aria-expanded={open}
        disabled={disabled}
        onClick={() => {
          if (!disabled) setOpen(true);
        }}
        style={{
          border: 0,
          background: "transparent",
          padding: 0,
          textAlign: "left",
          width: "100%",
          opacity: disabled ? 0.6 : 1,
          cursor: disabled ? "not-allowed" : "pointer",
        }}
      >
        <div
          style={{
            fontSize: "11px",
            fontWeight: 600,
            letterSpacing: "0.04em",
            textTransform: "uppercase",
            color: "#64748b",
            marginBottom: 4,
          }}
        >
          Travellers
        </div>
        <div
          style={{
            fontSize: "18px",
            fontWeight: 700,
            color: disabled ? "#94a3b8" : "#0f172a",
            lineHeight: 1.2,
          }}
        >
          {totalPax} Traveller{totalPax === 1 ? "" : "s"}
        </div>
        <div style={{ fontSize: "11px", color: "#64748b", marginTop: 1 }}>
          <span className="js-count-adult">{guestCounts.Adults}</span> Adult
          {guestCounts.Adults === 1 ? "" : "s"}
          {guestCounts.Children > 0 && (
            <>
              {" · "}
              <span className="js-count-child">{guestCounts.Children}</span> Child
            </>
          )}
          {guestCounts.Infants > 0 && (
            <>
              {" · "}
              <span className="js-count-room">{guestCounts.Infants}</span> Infant
            </>
          )}
        </div>
      </button>

      {open &&
        createPortal(
          <div
            className="guest-search-modal-root"
            role="presentation"
            onClick={() => setOpen(false)}
            style={{
              position: "fixed",
              inset: 0,
              zIndex: 10050,
              background: "rgba(15, 23, 42, 0.45)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              padding: 16,
            }}
          >
            <div
              role="dialog"
              aria-modal="true"
              aria-label="Travellers"
              className="guest-search-modal"
              onClick={(e) => e.stopPropagation()}
              style={{
                width: "100%",
                maxWidth: 620,
                maxHeight: "min(640px, calc(100vh - 48px))",
                overflowY: "auto",
                background: "linear-gradient(to bottom, #ffffff, #f8f9fa)",
                borderRadius: 16,
                boxShadow: "0 24px 48px rgba(15, 23, 42, 0.22)",
                padding: "24px 28px 20px",
              }}
            >
              <div
                style={{
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between",
                  gap: 12,
                  marginBottom: 18,
                }}
              >
                <div>
                  <div
                    style={{
                      fontSize: 11,
                      fontWeight: 700,
                      letterSpacing: "0.08em",
                      textTransform: "uppercase",
                      color: "#7788ad",
                    }}
                  >
                    Travellers
                  </div>
                  <div
                    style={{
                      fontSize: 20,
                      fontWeight: 700,
                      color: "#192944",
                      marginTop: 4,
                    }}
                  >
                    Who is travelling?
                  </div>
                </div>
                <button
                  type="button"
                  aria-label="Close travellers"
                  onClick={() => setOpen(false)}
                  style={{
                    width: 36,
                    height: 36,
                    borderRadius: 8,
                    border: "1px solid #e2e8f0",
                    background: "#fff",
                    color: "#64748b",
                    cursor: "pointer",
                    fontSize: 20,
                    lineHeight: 1,
                  }}
                >
                  ×
                </button>
              </div>

              {counters.map((counter) => (
                <Counter
                  key={counter.name}
                  name={counter.name}
                  defaultValue={guestCounts[counter.name] || counter.defaultValue}
                  onCounterChange={handleCounterChange}
                  onGenderCountChange={handleGenderCountChange}
                  disabled={disabled}
                  maleCount={guestCounts.maleCount || 0}
                  femaleCount={guestCounts.femaleCount || 0}
                  totalAdults={guestCounts.Adults || 1}
                  ages={
                    counter.name === "Children"
                      ? guestCounts.ages || []
                      : counter.name === "Infants"
                        ? guestCounts.infantAges || []
                        : []
                  }
                  onAgeChange={
                    counter.name === "Children"
                      ? handleAgeChange
                      : handleInfantAgeChange
                  }
                />
              ))}

              <button
                type="button"
                onClick={() => setOpen(false)}
                style={{
                  width: "100%",
                  marginTop: 8,
                  border: 0,
                  borderRadius: 10,
                  background: "#234adc",
                  color: "#fff",
                  fontWeight: 700,
                  fontSize: 14,
                  padding: "14px 18px",
                  cursor: "pointer",
                }}
              >
                Done
              </button>
            </div>
          </div>,
          document.body
        )}
    </Box>
  );
};

export default GuestSearch;
