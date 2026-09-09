import React from "react";
import { 
  FormControl, 
  Select, 
  MenuItem, 
  Box, 
  Typography
} from "@mui/material";
import { styled } from "@mui/material/styles";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import WbSunnyIcon from "@mui/icons-material/WbSunny";
import NightsStayIcon from "@mui/icons-material/NightsStay";

// Simple styled components focusing on width and z-index
const StyledFormControl = styled(FormControl)(({ theme }) => ({
  width: '100%',
  minWidth: '180px',
}));

const StyledMenuItem = styled(MenuItem)(({ theme, isNight }) => ({
  display: "flex",
  alignItems: "center",
  gap: "10px",
  padding: "12px 16px",
  backgroundColor: isNight
    ? "rgba(255, 235, 235, 0.85)"
    : "rgba(237, 242, 255, 0.85)",
  color: isNight ? "#9A3412" : "#1E3A8A",
  borderRadius: "6px",
  margin: "4px 8px",
  transition: "all 0.2s ease",
  "&:hover": {
    backgroundColor: isNight
      ? "rgba(255, 235, 235, 1)"
      : "rgba(237, 242, 255, 1)",
  },
  "&.Mui-selected": {
    backgroundColor: isNight
      ? "rgba(254, 215, 215, 1)"
      : "rgba(219, 234, 254, 1)",
    fontWeight: "600",
  },
}));

// Custom Select component with styling
const StyledSelect = styled(Select)(({ theme }) => ({
  '& .MuiSelect-select': {
    padding: '12px 14px',
    display: 'flex',
    alignItems: 'center',
    gap: '8px',
  },
  '& .MuiOutlinedInput-notchedOutline': {
    borderColor: '#e2e8f0',
  },
  '&:hover .MuiOutlinedInput-notchedOutline': {
    borderColor: '#3554D1',
  },
  '&.Mui-focused .MuiOutlinedInput-notchedOutline': {
    borderColor: '#3554D1',
  },
}));

const Pickuptimezone = ({
  entryytime,
  setentryytime,
  isStatic = false,
  setTime,
}) => {
  // Function to determine if the selected time is during night hours (6 PM to 6 AM)
  const isSelectedTimeNight = () => {
    if (!entryytime) return false;
    const hour = parseInt(entryytime.split(":")[0]);
    const period = entryytime.split(" ")[1];
    return (
      (period === "PM" && hour >= 6 && hour <= 11) ||
      (period === "AM" && (hour === 12 || hour <= 3))
    );
  };

  // Function to check if a specific time is during night hours
  const isNightHour = (hour, period) => {
    return (
      (period === "PM" && hour >= 6 && hour <= 11) ||
      (period === "AM" && (hour === 12 || hour <= 3))
    );
  };

  // Handle time selection without using useEffect
  const handleTimeChange = (e) => {
    if (!isStatic) {
      const newValue = e.target.value;
      
      // Update parent time state if needed - this avoids the useEffect approach
      if (setTime) {
        // Only call setTime if there's a change between having a value and not having one
        const hasCurrentValue = !!entryytime;
        const hasNewValue = !!newValue;
        
        if (hasCurrentValue !== hasNewValue) {
          setTime(hasNewValue);
        }
      }
      
      // Update the actual time value
      setentryytime(newValue);
    }
  };

  return (
    <Box>
      <Typography variant="subtitle2" fontWeight={600} sx={{ mb: 1, color: 'text.primary' }}>
        Pick Up Time
      </Typography>
      <StyledFormControl fullWidth>
        <StyledSelect
          value={entryytime || ''}
          onChange={handleTimeChange}
          displayEmpty
          disabled={isStatic}
          renderValue={(selected) => {
            if (!selected) {
              return (
                <Typography sx={{ color: '#9ca3af' }}>
                  Select time
                </Typography>
              );
            }

            const isNight = isSelectedTimeNight();

            return (
              <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                {isNight ? (
                  <NightsStayIcon sx={{ fontSize: '1rem', color: "#9A3412" }} />
                ) : (
                  <WbSunnyIcon sx={{ fontSize: '1rem', color: "#3554D1" }} />
                )}
                <Typography
                  sx={{
                    color: isNight ? "#9A3412" : "#1E3A8A",
                    fontWeight: 500,
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
                maxHeight: 300,
                width: '300px',
                minWidth: '300px',
                borderRadius: "8px",
                marginTop: "4px",
                zIndex: 9999,
              },
            },
            anchorOrigin: {
              vertical: 'bottom',
              horizontal: 'left',
            },
            transformOrigin: {
              vertical: 'top',
              horizontal: 'left',
            },
          }}
        >
          {/* Generate time options from 12 AM to 11 PM */}
          {Array.from({ length: 24 }, (_, index) => {
            const hour = index % 12 === 0 ? 12 : index % 12;
            const period = index < 12 ? "AM" : "PM";
            const timeLabel = `${hour.toString().padStart(2, "0")}:00 ${period}`;
            const isNight = isNightHour(hour, period);

            return (
              <StyledMenuItem key={index} value={timeLabel} isNight={isNight}>
                {isNight ? (
                  <NightsStayIcon sx={{ fontSize: '1rem', color: "#9A3412" }} />
                ) : (
                  <WbSunnyIcon sx={{ fontSize: '1rem', color: "#3554D1" }} />
                )}
                <Box>
                  <Typography
                    sx={{
                      fontWeight: 500,
                      color: isNight ? "#9A3412" : "#1E3A8A",
                    }}
                  >
                    {timeLabel}
                  </Typography>
                  <Typography
                    sx={{
                      fontSize: '0.75rem',
                      color: isNight ? "#B45309" : "#1E40AF",
                      mt: 0.5,
                    }}
                  >
                    {isNight ? "*Night Surcharge Applied" : "*Standard Rate"}
                  </Typography>
                </Box>
              </StyledMenuItem>
            );
          })}
        </StyledSelect>
      </StyledFormControl>
    </Box>
  );
};

export default Pickuptimezone;
