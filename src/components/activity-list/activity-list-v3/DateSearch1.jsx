import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { TextField } from "@mui/material";

const DateSearch1 = ({ selectedDate, setSelectedDate }) => {
  const checkIn = useSelector(
    (state) =>
      state.hotels.tourdetails.check_in_time ||
      state.hotels.tourdetails.CheckInTime ||
      state.hotels.tourdetails.data.CheckInTime
  );
  const checkOut = useSelector(
    (state) =>
      state.hotels.tourdetails.check_out_time ||
      state.hotels.tourdetails.CheckOutTime ||
      state.hotels.tourdetails.data.CheckOutTime
  );

  const formatDateToDDMMYYYY = (dateString) => {
    if (!dateString) return null;
    
    // Check if the date is already in YYYY-MM-DD format
    if (dateString.includes("-") && dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
      return dateString; // Already in correct format
    }
    
    // Handle DD/MM/YYYY format (previous format)
    if (dateString.includes("/")) {
      const [day, month, year] = dateString.split("/");
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    }
    
    // If neither format matches, return as is
    return dateString;
  };

  const formattedCheckIn = formatDateToDDMMYYYY(checkIn);
  const formattedCheckOut = formatDateToDDMMYYYY(checkOut);

  useEffect(() => {
    if (!selectedDate && formattedCheckIn) {
      setSelectedDate(formattedCheckIn);
    }
  }, [selectedDate, formattedCheckIn, setSelectedDate]);

  const handleDateChange = (event) => {
    setSelectedDate(event.target.value);
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <TextField
        fullWidth
        type="date"
        variant="outlined"
        value={selectedDate || formattedCheckIn}
        onChange={handleDateChange}
        inputProps={{
          min: formattedCheckIn,
          max: formattedCheckOut,
        }}
        InputLabelProps={{ shrink: true }}
        onClick={(e) => e.target.showPicker()}
        sx={{
          backgroundColor: "none",
          border: "none",
          "& .MuiInputBase-root": {
            borderRadius: "4px",
          },
          "& .MuiOutlinedInput-notchedOutline": {
            border: "none",
          },
        }}
      />
    </div>
  );
};

export default DateSearch1;
