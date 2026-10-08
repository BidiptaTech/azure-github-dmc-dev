import React, { useEffect, useRef } from "react";
import { FormControl, Select, MenuItem, Box, Typography } from "@mui/material";
import { styled } from "@mui/material/styles";
import WbSunnyIcon from "@mui/icons-material/WbSunny";
import NightsStayIcon from "@mui/icons-material/NightsStay";

// Styled Menu Item for consistent styling
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

// Custom Select component with styling
const StyledSelect = styled(Select)(({ theme }) => ({
  height: "48px",
  borderRadius: "8px",
  fontSize: "16px",
  padding: "0 10px",
  backgroundColor: "#f5f7fb",
  "& .MuiOutlinedInput-notchedOutline": {
    borderColor: "#e2e8f0",
  },
  "&:hover .MuiOutlinedInput-notchedOutline": {
    borderColor: "#3554D1",
  },
  "&.Mui-focused .MuiOutlinedInput-notchedOutline": {
    borderColor: "#3554D1",
  },
  "& .MuiSelect-select": {
    display: "flex",
    alignItems: "center",
    gap: "8px",
  },
}));

const Pickuptime1 = ({
  entryytime,
  setentryytime,
  isStatic = false,
  setTime,
}) => {
  const prevTimeRef = useRef(!!entryytime);

  useEffect(() => {
    if (setTime) {
      const currentTimeState = !!entryytime;
      if (currentTimeState !== prevTimeRef.current) {
        setTime(currentTimeState);
        prevTimeRef.current = currentTimeState;
      }
    }
  }, [entryytime, setTime]);

  // Function to determine if the selected time is during night hours
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

  // Handle time selection
  const handleTimeChange = (e) => {
    if (!isStatic) {
      setentryytime(e.target.value);
    }
  };

  return (
    <FormControl fullWidth variant="outlined">
      <label
        htmlFor="pickup-time-select"
        className="text-15 fw-500 ls-2 lh-16 mt-5"
      >
        Select the Pick Up Time
      </label>
      <StyledSelect
        id="pickup-time-select"
        value={entryytime}
        onChange={handleTimeChange}
        displayEmpty
        disabled={isStatic}
        renderValue={(selected) => {
          if (!selected) {
            return (
              <Typography sx={{ color: "#999" }}>Select The Time</Typography>
            );
          }

          const isNight = isSelectedTimeNight();

          return (
            <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
              {isNight ? (
                <NightsStayIcon sx={{ color: "#555" }} />
              ) : (
                <WbSunnyIcon sx={{ color: "#4a90e2" }} />
              )}
              <Typography
                sx={{
                  color: isNight ? "#000000" : "#0047AB",
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
              maxHeight: 300,
              borderRadius: "10px",
              backgroundColor: "rgba(255, 255, 255, 0.98)",
              boxShadow: "0 8px 16px rgba(0,0,0,0.15)",
            },
          },
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
