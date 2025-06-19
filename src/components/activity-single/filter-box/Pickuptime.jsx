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

// Styled components for custom styling
const StyledFormControl = styled(FormControl)(({ theme }) => ({
  backgroundColor: "rgba(255, 255, 255, 0.9)",
  borderRadius: "10px",
  boxShadow: "0 4px 10px rgba(0, 0, 0, 0.05)",
  transition: "all 0.3s ease",
  "&:hover": {
    boxShadow: "0 6px 15px rgba(0, 0, 0, 0.1)",
    transform: "translateY(-2px)",
  },
}));

const StyledSelect = styled(Select)(({ theme }) => ({
  "& .MuiSelect-select": {
    padding: "15px 14px",
    display: "block",
    alignItems: "center",
    gap: "10px",
  },
  "& .MuiOutlinedInput-notchedOutline": {
    borderColor: "rgba(53, 84, 209, 0.2)",
  },
  "&:hover .MuiOutlinedInput-notchedOutline": {
    borderColor: "rgba(53, 84, 209, 0.5)",
    borderWidth: "2px",
  },
  "&.Mui-focused .MuiOutlinedInput-notchedOutline": {
    borderColor: "#3554D1",
  },
}));

const StyledMenuItem = styled(MenuItem)(
  ({ isNightTime, isDisabled, theme }) => ({
    padding: "12px 16px",
    display: "flex",
    flexDirection: "column",
    alignItems: "flex-start",
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
      transform: "translateY(-2px) scale(1.02)",
      boxShadow: "0 4px 8px rgba(0, 0, 0, 0.1)",
    },

    "&.Mui-selected": {
      backgroundColor: isNightTime
        ? "rgba(254, 215, 215, 1)"
        : "rgba(219, 234, 254, 1)",
      fontWeight: "bold",
    },

    "&.Mui-selected:hover": {
      backgroundColor: isNightTime
        ? "rgba(254, 202, 202, 1)"
        : "rgba(191, 219, 254, 1)",
    },
  })
);

const SurchargeText = styled(Typography)({
  fontSize: "10px",
  fontWeight: "500",
  color: "#B45309",
  marginTop: "2px",
});

const DayPriceText = styled(Typography)({
  fontSize: "10px",
  fontWeight: "500",
  color: "#1E40AF",
  marginTop: "2px",
});

const StyledInputLabel = styled(InputLabel)({
  color: "#64748B",
  "&.Mui-focused": {
    color: "#3554D1",
  },
});

const Pickuptime = ({ entryytime, setentryytime }) => {
  const location = useLocation();
  const { guide } = location.state;
  const [open, setOpen] = useState(false);

  // Extract night time limits from guide data
  const nightStartTime = guide.guide.night_start_time || "22:00";
  console.log(nightStartTime);
  const nightEndTime = guide.guide.night_end_time || "06:00";
  console.log(nightEndTime);

  // Parse 12-hour format time (with AM/PM) to 24-hour hour value
  const parseTimeToHour = (timeStr) => {
    if (!timeStr) return 0;

    // Check if time is in HH:MM AM/PM format
    if (timeStr.includes("AM") || timeStr.includes("PM")) {
      const [timePart, period] = timeStr.split(" ");
      let [hours, minutes] = timePart.split(":");
      hours = parseInt(hours, 10);

      // Convert to 24-hour format
      if (period === "PM" && hours !== 12) {
        hours += 12;
      } else if (period === "AM" && hours === 12) {
        hours = 0;
      }

      return hours;
    }

    // If it's already in 24-hour format (HH:MM)
    const [hours] = timeStr.split(":");
    return parseInt(hours, 10);
  };

  const nightStartHour = parseTimeToHour(nightStartTime);
  const nightEndHour = parseTimeToHour(nightEndTime);

  console.log("Night start hour (24h format):", nightStartHour);
  console.log("Night end hour (24h format):", nightEndHour);

  // Check if a given hour is in night time range
  const isNightHour = (hour) => {
    // For a time like 11:55 PM, ensure the 11 PM hour is included
    // Convert to ceiling hour for end time that has minutes
    let adjustedEndHour =
      nightEndTime.includes(":") && parseInt(nightEndTime.split(":")[1], 10) > 0
        ? (nightEndHour + 1) % 24
        : nightEndHour;

    console.log(`Checking hour ${hour}, adjusted end hour: ${adjustedEndHour}`);

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

      // Block all hours in this time range
      for (let i = startHour; i < endHour; i++) {
        blockedTimes.add(i);
      }
    });
  }

  return (
    <StyledFormControl fullWidth>
      <StyledInputLabel id="pickup-time-label" shrink={!!entryytime}>
        {!entryytime && "Select the Pick Up Time"}
      </StyledInputLabel>
      <StyledSelect
        labelId="pickup-time-label"
        id="pickup-time-select"
        value={entryytime}
        onChange={(e) => setentryytime(e.target.value)}
        open={open}
        onOpen={() => setOpen(true)}
        onClose={() => setOpen(false)}
        MenuProps={{
          PaperProps: {
            style: {
              maxHeight: 350,
              width: "auto",
              borderRadius: "12px",
              padding: "8px",
              marginTop: "8px",
              backgroundColor: "rgba(255, 255, 255, 0.95)",
              boxShadow: "0 8px 20px rgba(0, 0, 0, 0.15)",
            },
          },
          TransitionProps: {
            style: {
              transition: "all 0.2s ease",
            },
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
              <Box
                sx={{ display: "flex", alignItems: "center", width: "100%" }}
              >
                {isNight ? (
                  <NightsStayIcon
                    sx={{ mr: 1, fontSize: 20, color: "#9A3412" }}
                  />
                ) : (
                  <WbSunnyIcon sx={{ mr: 1, fontSize: 20, color: "#3554D1" }} />
                )}
                <Typography variant="body1" sx={{ fontWeight: 500 }}>
                  {timeLabel}
                </Typography>
              </Box>

              {isNight ? (
                <SurchargeText>*Dynamic Night Surcharge Applied</SurchargeText>
              ) : (
                <DayPriceText>*Day Price Applied</DayPriceText>
              )}
            </StyledMenuItem>
          );
        })}
      </StyledSelect>
    </StyledFormControl>
  );
};

export default Pickuptime;
