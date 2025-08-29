import React, { useEffect, useRef } from "react";
import { 
  FormControl, 
  Select, 
  MenuItem, 
  Box, 
  Typography
} from "@mui/material";
import { styled } from "@mui/material/styles";
import WbSunnyIcon from "@mui/icons-material/WbSunny";
import NightsStayIcon from "@mui/icons-material/NightsStay";

// Styled Menu Item for consistent styling (matching Pickuptime)
const StyledMenuItem = styled(MenuItem)(({ theme, isNight }) => ({
  display: "flex",
  alignItems: "center",
  gap: "10px",
  padding: "10px 15px",
  fontWeight: "800px",
  backgroundColor: isNight
    ? "rgba(55, 55, 60, 0.08)"
    : "rgba(220, 242, 255, 0.5)",
  color: isNight ? "#000000" : "#0047AB",
  transition: "all 0.2s ease",
  "&:hover": {
    backgroundColor: isNight
      ? "rgba(0, 0, 0, 0.15)"
      : "rgba(220, 242, 255, 0.9)",
    color: "#ffff",
    transform: "translateY(-2px) scale(1.01)",
    boxShadow: "0 3px 5px rgba(0,0,0,0.1)",
  },
  "& .MuiSvgIcon-root": {
    color: isNight ? "#555" : "#4a90e2",
    fontSize: "1.2rem",
  },
}));

// Custom Select component with styling (matching Pickuptime)
const StyledSelect = styled(Select)(({ theme, disabled }) => ({
  height: "48px",
  borderRadius: "8px",
  fontSize: "16px",
  overflow: "hidden",
  position: "relative",
  maxHeight: "240px",
  width: "100%", // Full width to fill parent container
  minWidth: "120px", // Minimum width for very small containers
  fontFamily: "inherit",
  padding: "0 10px",
  backgroundColor: disabled ? "#f0f0f0" : "#f5f7fb",
  opacity: disabled ? 0.8 : 1,
  "& .MuiOutlinedInput-notchedOutline": {
    borderColor: disabled ? "#ddd" : "#e2e8f0",
  },
  "&:hover .MuiOutlinedInput-notchedOutline": {
    borderColor: disabled ? "#ddd" : "#3554D1",
  },
  "&.Mui-focused .MuiOutlinedInput-notchedOutline": {
    borderColor: disabled ? "#ddd" : "#3554D1",
  },
  "& .MuiSelect-select": {
    display: "flex",
    alignItems: "center",
    gap: "8px",
    color: disabled ? "#999" : "inherit",
  },
  
  // Media queries for responsive design
  [theme.breakpoints.down('xl')]: {
    height: "46px",
    fontSize: "15px",
    padding: "0 8px",
  },
  
  [theme.breakpoints.down('lg')]: {
    height: "44px",
    fontSize: "14px",
    padding: "0 8px",
  },
  
  [theme.breakpoints.down('md')]: {
    height: "42px",
    fontSize: "14px",
    padding: "0 6px",
    borderRadius: "6px",
  },
  
  [theme.breakpoints.down('sm')]: {
    height: "40px",
    fontSize: "13px",
    padding: "0 6px",
    borderRadius: "6px",
  },
  
  [theme.breakpoints.down('xs')]: {
    height: "38px",
    fontSize: "12px",
    padding: "0 5px",
    borderRadius: "5px",
  },
}));

const Pickuptime1 = ({
  entryytime,
  setentryytime,
  isStatic = false,
  disabled = false,
  setTime,
}) => {
  const prevTimeRef = useRef(entryytime);

  useEffect(() => {
    if (setTime) {
      // Always update the time state when entryytime changes
      const hasTime = !!entryytime;
      console.log("Pickuptime1 - Setting time state:", hasTime, "Current time value:", entryytime);
      setTime(hasTime);
      prevTimeRef.current = entryytime;
    }
  }, [entryytime, setTime]);

  // Updated function to correctly determine night hours (6 PM to 11 PM only)
  const isSelectedTimeNight = () => {
    if (!entryytime) return false;
    const hour = parseInt(entryytime.split(":")[0]);
    const period = entryytime.split(" ")[1];
    return (
      (period === "PM" && hour >= 6 && hour <= 11) ||
      (period === "AM" && (hour === 12 || hour <= 3))
    );
  };

  // Updated function to correctly check night hours
  const isNightHour = (hour, period) => {
    return (
      (period === "PM" && hour >= 6 && hour <= 11) ||
      (period === "AM" && (hour === 12 || hour <= 3))
    );
  };

  // Handle time selection
  const handleTimeChange = (e) => {
    if (!isStatic && !disabled) {
      const selectedTime = e.target.value;
      setentryytime(selectedTime);
      
      // Explicitly set time1 state to true when a time is selected
      if (setTime && selectedTime) {
        console.log("Pickuptime1 - Explicitly setting time state to true for:", selectedTime);
        setTime(true);
      }
    }
  };

  return (
    <FormControl fullWidth>
      <StyledSelect
        value={entryytime || ''}
        onChange={handleTimeChange}
        displayEmpty
        disabled={isStatic || disabled}
        renderValue={(selected) => {
          if (!selected) {
            return (
              <Typography sx={{ color: disabled ? "#bbb" : "#999" }}>Select The Time</Typography>
            );
          }

          const isNight = isSelectedTimeNight();

          return (
            <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
              {isNight ? (
                <NightsStayIcon sx={{ color: disabled ? "#bbb" : "#555" }} />
              ) : (
                <WbSunnyIcon sx={{ color: disabled ? "#bbb" : "#4a90e2" }} />
              )}
              <Typography
                sx={{
                  color: disabled ? "#bbb" : (isNight ? "#000000" : "#0047AB"),
                  fontWeight: 600,
                }}
              >
                {selected}
              </Typography>
            </Box>
          );
        }}
        MenuProps={{
          PaperProps: {
            style: {
              maxHeight: 280,
              borderRadius: "10px",
              backgroundColor: "rgba(255, 255, 255, 0.98)",
              boxShadow: "0 8px 16px rgba(0,0,0,0.15)",
            },
            sx: {
              /* Custom scrollbar for time picker */
              "&::-webkit-scrollbar": {
                width: "8px",
              },
              "&::-webkit-scrollbar-track": {
                background: "#f1f1f1",
                borderRadius: "4px",
              },
              "&::-webkit-scrollbar-thumb": {
                background: "#ff9800",
                borderRadius: "4px",
                transition: "background 0.3s ease",
                "&:hover": {
                  background: "#f57c00",
                },
              },
              /* Firefox scrollbar */
              scrollbarWidth: "thin",
              scrollbarColor: "#ff9800 #f1f1f1",
            },
          },
          className: "time-dropdown",
        }}
      >
        {/* Only show the placeholder when no time is selected */}
        {entryytime === "" && (
          <MenuItem value="" disabled>
            Select The Time
          </MenuItem>
        )}

        {/* Generate time options from 12 AM to 11 PM */}
        {Array.from({ length: 24 }, (_, index) => {
          const hour = index % 12 === 0 ? 12 : index % 12;
          const period = index < 12 ? "AM" : "PM";
          const timeLabel = `${hour.toString().padStart(2, "0")}:00 ${period}`;
          const isNight = isNightHour(hour, period);

          return (
            <StyledMenuItem key={index} value={timeLabel} isNight={isNight}>
              {isNight ? <NightsStayIcon /> : <WbSunnyIcon />}
              <Box>
                <Typography
                  sx={{
                    fontWeight: 600,
                    color: isNight ? "#000000" : "#0047AB", // Deep blue for day, dark black for night
                  }}
                >
                  {timeLabel}
                </Typography>
              </Box>
            </StyledMenuItem>
          );
        })}
      </StyledSelect>
    </FormControl>
  );
};

export default Pickuptime1;
