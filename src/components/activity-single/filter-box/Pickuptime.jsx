import React, { useState, useEffect } from "react";
import {
  Select,
  FormControl,
  InputLabel,
  MenuItem,
  Box,
  Typography,
} from "@mui/material";
import { useLocation } from "react-router-dom";
import { styled } from "@mui/material/styles";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import NightsStayIcon from "@mui/icons-material/NightsStay";
import WbSunnyIcon from "@mui/icons-material/WbSunny";

// Simple styled components focusing on width and z-index
const StyledFormControl = styled(FormControl)(({ theme }) => ({
  width: '100%',
  minWidth: '180px',
}));

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

const StyledMenuItem = styled(MenuItem)(
  ({ isNightTime, isDisabled, theme }) => ({
    padding: "12px 16px",
    display: "flex",
    alignItems: "center",
    gap: "10px",
    backgroundColor: isNightTime
      ? "rgba(255, 235, 235, 0.85)"
      : "rgba(237, 242, 255, 0.85)",
    color: isNightTime ? "#9A3412" : "#1E3A8A",
    borderRadius: "6px",
    margin: "4px 8px",
    transition: "all 0.2s ease",
    opacity: isDisabled ? 0.5 : 1,

    "&:hover": {
      backgroundColor: isNightTime
        ? "rgba(255, 235, 235, 1)"
        : "rgba(237, 242, 255, 1)",
    },

    "&.Mui-selected": {
      backgroundColor: isNightTime
        ? "rgba(254, 215, 215, 1)"
        : "rgba(219, 234, 254, 1)",
      fontWeight: "600",
    },
  })
);

const SurchargeText = styled(Typography)({
  fontSize: "0.75rem",
  fontWeight: "500",
  color: "#B45309",
  marginLeft: "28px",
});

const DayPriceText = styled(Typography)({
  fontSize: "0.75rem",
  fontWeight: "500",
  color: "#1E40AF",
  marginLeft: "28px",
});

const Pickuptime = ({ entryytime, setentryytime }) => {
  const location = useLocation();
  const { guide } = location.state;
  const [open, setOpen] = useState(false);

  // Extract night time limits from guide data
  const nightStartTime = guide.guide.night_start_time || "22:00";
  const nightEndTime = guide.guide.night_end_time || "06:00";

  // Parse 12-hour format time (with AM/PM) to 24-hour hour value
  const parseTimeToHour = (timeStr) => {
    if (!timeStr) return 0;

    // Convert to string if it's a number
    const timeString = String(timeStr);
    
    console.log(`parseTimeToHour input: ${timeStr} (${typeof timeStr}) -> converted to: ${timeString}`);

    // Check if time is in HH:MM AM/PM format
    if (timeString.includes("AM") || timeString.includes("PM")) {
      const [timePart, period] = timeString.split(" ");
      let [hours, minutes] = timePart.split(":");
      hours = parseInt(hours, 10);

      // Convert to 24-hour format
      if (period === "PM" && hours !== 12) {
        hours += 12;
      } else if (period === "AM" && hours === 12) {
        hours = 0;
      }

      console.log(`AM/PM format parsed: ${timeStr} -> ${hours}`);
      return hours;
    }

    // If it's already in 24-hour format (HH:MM) or just a number
    if (timeString.includes(":")) {
      const [hours] = timeString.split(":");
      const result = parseInt(hours, 10);
      console.log(`HH:MM format parsed: ${timeStr} -> ${result}`);
      return result;
    } else {
      // If it's just a number (like 11), return it directly
      const result = parseInt(timeString, 10);
      console.log(`Number format parsed: ${timeStr} -> ${result}`);
      return result;
    }
  };

  const nightStartHour = parseTimeToHour(nightStartTime);
  const nightEndHour = parseTimeToHour(nightEndTime);

  // Check if a given hour is in night time range
  const isNightHour = (hour) => {
    // For a time like 11:55 PM, ensure the 11 PM hour is included
    // Convert to ceiling hour for end time that has minutes
    let adjustedEndHour =
      nightEndTime.includes(":") && parseInt(nightEndTime.split(":")[1], 10) > 0
        ? (nightEndHour + 1) % 24
        : nightEndHour;

    if (nightStartHour < adjustedEndHour) {
      // Simple range (e.g., 19:00 to 23:59)
      return hour >= nightStartHour && hour < adjustedEndHour;
    } else {
      // Overnight range (e.g., 22:00 to 06:00)
      return hour >= nightStartHour || hour < adjustedEndHour;
    }
  };

  // Convert booking start_time & end_time into an array of blocked hours
  const blockedTimes = new Set();

  if (guide.bookingDetails && guide.bookingDetails.length > 0) {
    guide.bookingDetails.forEach((booking) => {
      const startTime = booking.start_time;
      const endTime = booking.end_time;

      let startHour = startTime ? parseTimeToHour(startTime) : 0;
      let endHour = endTime ? parseTimeToHour(endTime) : 0;

      console.log(`Booking: start_time=${startTime} (${typeof startTime}) -> startHour=${startHour}`);
      console.log(`Booking: end_time=${endTime} (${typeof endTime}) -> endHour=${endHour}`);

      // Handle overnight bookings (e.g., 11 PM to 4 AM)
      if (startHour > endHour) {
        // Block hours from start to midnight (24)
        for (let i = startHour; i < 24; i++) {
          blockedTimes.add(i);
        }
        // Block hours from midnight (0) to end
        for (let i = 0; i < endHour; i++) {
          blockedTimes.add(i);
        }
      } else {
        // Block all hours in this time range
        for (let i = startHour; i < endHour; i++) {
          blockedTimes.add(i);
        }
      }
    });
  }

  return (
    <Box>
      <Typography variant="subtitle2" fontWeight={600} sx={{ mb: 1, color: 'text.primary' }}>
        Pick Up Time
      </Typography>
      <StyledFormControl fullWidth>
        <StyledSelect
          value={entryytime || ''}
          onChange={(e) => setentryytime(e.target.value)}
          displayEmpty
          open={open}
          onOpen={() => setOpen(true)}
          onClose={() => setOpen(false)}
          renderValue={(selected) => {
            if (!selected) {
              return (
                <Typography sx={{ color: '#9ca3af' }}>
                  Select time
                </Typography>
              );
            }
            return (
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <AccessTimeIcon sx={{ fontSize: '1rem', color: '#3554D1' }} />
                <Typography sx={{ fontWeight: 500 }}>
                  {selected}
                </Typography>
              </Box>
            );
          }}
          MenuProps={{
            PaperProps: {
              style: {
                maxHeight: 300,
                width: 'auto',
                minWidth: '200px',
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
          {Array.from({ length: 24 }, (_, index) => {
            const hour = index % 12 === 0 ? 12 : index % 12;
            const period = index < 12 ? "AM" : "PM";
            const timeLabel = `${hour.toString().padStart(2, "0")}:00 ${period}`;
            const isDisabled = blockedTimes.has(index);
            const isNight = isNightHour(index);

            return (
              <StyledMenuItem
                key={index}
                value={timeLabel}
                disabled={isDisabled}
                isNightTime={isNight}
                isDisabled={isDisabled}
              >
                {isNight ? (
                  <NightsStayIcon sx={{ fontSize: '1rem', color: "#9A3412" }} />
                ) : (
                  <WbSunnyIcon sx={{ fontSize: '1rem', color: "#3554D1" }} />
                )}
                <Box>
                  <Typography variant="body2" sx={{ fontWeight: 500 }}>
                    {timeLabel}
                  </Typography>
                  {isNight ? (
                    <SurchargeText>*Night Surcharge Applied</SurchargeText>
                  ) : (
                    <DayPriceText>*Standard Rate</DayPriceText>
                  )}
                </Box>
              </StyledMenuItem>
            );
          })}
        </StyledSelect>
      </StyledFormControl>
    </Box>
  );
};

export default Pickuptime;
