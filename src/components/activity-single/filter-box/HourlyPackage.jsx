import React, { useState, useEffect, useMemo } from "react";
import { useLocation } from "react-router-dom";
import { Select, FormControl, InputLabel, MenuItem } from "@mui/material";
import { useSelector } from "react-redux";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import TimerIcon from "@mui/icons-material/Timer";
import { alpha } from "@mui/material/styles";

const HourPackage = ({ hour, sethours, onHourChange, entryytime }) => {
  const location = useLocation();
  const { guide } = location.state;
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  const [open, setOpen] = useState(false); // State to control dropdown open/close
  const [availableHours, setAvailableHours] = useState([]); // State for available hours

  const hourlyPrices = useMemo(() => ({
    1:
      guide.guide.prices.dmc_hourly_price ||
      guide.guide.prices.travclicks_hourly_price,
    2:
      guide.guide.prices.dmc_two_hour_price ||
      guide.guide.prices.travclicks_two_hour_price,
    4:
      guide.guide.prices.dmc_four_hour_price ||
      guide.guide.prices.travclicks_four_hour_price,
    6:
      guide.guide.prices.dmc_six_hour_price ||
      guide.guide.prices.travclicks_six_hour_price,
    8:
      guide.guide.prices.dmc_eight_hour_price ||
      guide.guide.prices.travclicks_eight_hour_price,
    10:
      guide.guide.prices.dmc_ten_hour_price ||
      guide.guide.prices.travclicks_ten_hour_price,
    12:
      guide.guide.prices.dmc_twelve_hour_price ||
      guide.guide.prices.travclicks_twelve_hour_price,
  }), [guide.guide.prices]);

  // Calculate available hours based on selected entry time
  useEffect(() => {
    if (!entryytime) {
      setAvailableHours([]);
      return;
    }

    // Parse entry time
    let entryTimeParts = entryytime.match(/(\d+):(\d+)\s*(AM|PM)/i);
    if (!entryTimeParts) return;

    let hours = parseInt(entryTimeParts[1]);
    const minutes = parseInt(entryTimeParts[2]);
    const period = entryTimeParts[3].toUpperCase();

    // Convert to 24-hour format
    if (period === "PM" && hours < 12) {
      hours += 12;
    } else if (period === "AM" && hours === 12) {
      hours = 0;
    }

    // Calculate available hours
    // End time should be <= 24 (midnight)
    const availableHoursUntilMidnight = Math.floor(
      24 - hours - (minutes > 0 ? 1 : 0)
    );

    // Get all possible hours
    const allHours = Object.keys(hourlyPrices)
      .map(Number)
      .sort((a, b) => a - b);

    // Filter hours that fit within available time
    const filteredHours = allHours.filter(
      (h) => h <= availableHoursUntilMidnight
    );

    setAvailableHours(filteredHours);

    // Reset selected hour if it's no longer available
    if (hour && !filteredHours.includes(Number(hour))) {
      sethours("");
      onHourChange("", 0);
    }
  }, [entryytime, hour, hourlyPrices, sethours]);

  const handleHourChange = (e) => {
    const selectedHour = e.target.value;
    const selectedPrice = hourlyPrices[selectedHour] || 0; // Default to 0 if not found

    sethours(selectedHour);
    onHourChange(selectedHour, selectedPrice); // Update parent state
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <FormControl fullWidth sx={{ backgroundColor: "none" }}>
        {hour === "" ? (
          <InputLabel
            id="hours"
            shrink={false}
            sx={{
              display: "flex",
              alignItems: "center",
              "& .MuiSvgIcon-root": {
                marginRight: "8px",
                color: "#3554D1",
              },
            }}
          >
            {entryytime ? (
              <TimerIcon
                sx={{ fontSize: "20px", marginRight: "10px", color: "#3554D1" }}
              />
            ) : null}
            {!entryytime ? "Select Pick-up Time First" : "Select the Package"}
          </InputLabel>
        ) : null}
        <Select
          labelId="hours"
          id="hours"
          value={hour}
          onChange={handleHourChange}
          open={open}
          onOpen={() => setOpen(true)}
          onClose={() => setOpen(false)}
          disabled={!entryytime || availableHours.length === 0}
          // startAdornment={
          //   hour
          //   // <AccessTimeIcon
          //   //   sx={{ color: "#3554D1", ml: 1, mr: 1, fontSize: "20px" }}
          //   // />
          // }
          sx={{
            backgroundColor: "transparent",
            border: "2px solid #F5F5F5",
            borderRadius: "8px",
            height: "48px",
            "&.Mui-focused": {
              borderColor: "#3554D1",
              boxShadow: "0 0 0 2px rgba(53, 84, 209, 0.1)",
            },
            "&:hover": {
              borderColor: "#3554D1",
            },
            "&.Mui-disabled": {
              backgroundColor: alpha("#F5F5F5", 0.5),
              color: "rgba(0, 0, 0, 0.38)",
            },
            "& .MuiSelect-select": {
              display: "flex",
              alignItems: "center",
              padding: "12px 16px",
              fontWeight: hour ? "500" : "normal",
              color: hour ? "#3554D1" : "inherit",
            },
            "& .MuiSelect-icon": {
              display: !entryytime ? "none" : "block",
              color: "#3554D1",
            },
            "& fieldset": { border: "none" },
          }}
          MenuProps={{
            PaperProps: {
              sx: {
                mt: 1,
                boxShadow: "0px 8px 20px rgba(0, 0, 0, 0.1)",
                borderRadius: "8px",
                "& .MuiMenuItem-root": {
                  padding: "10px 16px",
                  display: "flex",
                  alignItems: "center",
                  borderRadius: "4px",
                  margin: "2px 4px",
                  transition: "all 0.2s ease-in-out",
                  "&:hover": {
                    backgroundColor: "rgba(53, 84, 209, 0.08)",
                    transform: "translateY(-2px)",
                    boxShadow: "0px 4px 8px rgba(0, 0, 0, 0.05)",
                  },
                  "&.Mui-selected": {
                    backgroundColor: "rgba(53, 84, 209, 0.12)",
                    "&:hover": {
                      backgroundColor: "rgba(53, 84, 209, 0.16)",
                    },
                  },
                },
              },
            },
          }}
        >
          {availableHours.map((option, index) => {
            const price = hourlyPrices[option] * exchangeRate; // Multiply price by exchangeRate
            return (
              <MenuItem key={index} value={option}>
                <TimerIcon
                  sx={{ color: "#3554D1", mr: 0.5, fontSize: "18px" }}
                />
                <span style={{ fontWeight: 500 }}>{`${option} Hours`}</span>
                {PriceHide === "0" && (
                  <span
                    style={{
                      marginLeft: "auto",
                      fontWeight: 600,
                      color: "#3554D1",
                    }}
                  >
                    {`${Math.ceil(price)} ${currencyCode}`}
                  </span>
                )}
              </MenuItem>
            );
          })}
          {availableHours.length === 0 && entryytime && (
            <MenuItem disabled value="">
              <AccessTimeIcon
                sx={{ color: "#9e9e9e", mr: 1.5, fontSize: "18px" }}
              />
              No packages available for this time
            </MenuItem>
          )}
        </Select>
      </FormControl>
    </div>
  );
};

export default HourPackage;
