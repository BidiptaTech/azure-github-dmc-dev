import React, { useState } from "react";
// import DatePicker, { DateObject } from "react-multi-date-picker";
import { useLocation } from "react-router-dom";
import { Select, FormControl, InputLabel, MenuItem } from "@mui/material";

const HourPackage = ({ hour, sethours, onHourChange }) => {
  // const [dates, setDates] = useState([
  //   new DateObject({ year: 2023, month: 1, day: 22 }),
  //   "December 09 2020",
  //   1597994736000, //unix time in milliseconds (August 21 2020)
  // ]);
  // const [dates, setDates] = useState([
  //   new DateObject().setDay(5),
  //   new DateObject().setDay(14).add(1, "month"),
  // ]);
  const location = useLocation();
  const { guide } = location.state;
  const [open, setOpen] = useState(false); // State to control dropdown open/close

  const hourlyPrices = {
    "1 Hour": guide.hourly_price,
    "2 Hour": guide.two_hour_price,
    "4 Hour": guide.four_hour_price,
    "6 Hour": guide.six_hour_price,
    "8 Hour": guide.eight_hour_price,
    "10 Hour": guide.ten_hour_price,
    "12 Hour": guide.twelve_hour_price,
  };
  const hours = Object.keys(hourlyPrices);
  const handleHourChange = (e) => {
    const selectedHour = e.target.value;
    const selectedPrice = hourlyPrices[selectedHour] || 0; // Default to 0 if not found

    sethours(selectedHour);
    onHourChange(selectedHour, selectedPrice); // Update parent state
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      {/* <DatePicker
        inputClass="custom_input-picker"
        containerClassName="custom_container-picker"
        value={dates}
        onChange={setDates}
        numberOfMonths={2}
        offsetY={10}
        range
        rangeHover
        format="MMMM DD"
      /> */}

      <FormControl fullWidth sx={{ backgroundColor: "none" }}>
        {hour === "" ? ( // Only show the label when no value is selected
          <InputLabel
            id="hours"
            shrink={false} // Prevents the label from floating
          >
            Select the Package
          </InputLabel>
        ) : null}
        <Select
          labelId="hours"
          id="hours"
          value={hour}
          onChange={handleHourChange}
          open={open}
          onOpen={() => setOpen(true)} // Open the dropdown when the select is clicked
          onClose={() => setOpen(false)} // Close the dropdown when it loses focus or a selection is made
          sx={{
            backgroundColor: "transparent", // Transparent background
            border: "none", // Remove border
            "& .MuiSelect-icon": {
              display: "none", // Optional: remove the dropdown arrow
            },
            "& fieldset": {
              border: "none", // Remove border of the select fieldset
            },
          }}
        >
          {hours.map((option, index) => (
            <MenuItem key={index} value={option}>
              {`${option} - ${hourlyPrices[option]} USD`}
            </MenuItem>
          ))}
        </Select>
      </FormControl>
    </div>
  );
};

export default HourPackage;
