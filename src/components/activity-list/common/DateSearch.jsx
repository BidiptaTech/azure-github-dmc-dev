import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { TextField } from "@mui/material";

const DateSearch = ({
  selectedDate,
  setSelectedDate,
  minDate = null,
  maxDate = null,
  value = null,
}) => {
  const checkIn = useSelector(
    (state) =>
      state.hotels.tourdetails.check_in_time ||
      state.hotels.tourdetails.CheckInTime
  );
  const checkOut = useSelector(
    (state) =>
      state.hotels.tourdetails.check_out_time ||
      state.hotels.tourdetails.CheckOutTime
  );

  const formatDateToYmd = (dateString) => {
    if (!dateString) return null;
    if (String(dateString).includes("-") && !String(dateString).includes("/")) {
      return String(dateString);
    }
    const [day, month, year] = String(dateString).split("/");
    if (!day || !month || !year) return null;
    return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
  };

  const formattedCheckIn = formatDateToYmd(checkIn);
  const formattedCheckOut = formatDateToYmd(checkOut);
  const effectiveMin = minDate || formattedCheckIn;
  const effectiveMax = maxDate || formattedCheckOut;
  const effectiveValue =
    value || selectedDate || formattedCheckIn || "";

  useEffect(() => {
    if (value && value !== selectedDate) {
      setSelectedDate(value);
      return;
    }
    if (!selectedDate && formattedCheckIn) {
      setSelectedDate(formattedCheckIn);
    }
  }, [value, selectedDate, formattedCheckIn, setSelectedDate]);

  const handleDateChange = (event) => {
    setSelectedDate(event.target.value);
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <TextField
        fullWidth
        type="date"
        variant="outlined"
        value={effectiveValue}
        onChange={handleDateChange}
        inputProps={{
          min: effectiveMin,
          max: effectiveMax,
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

export default DateSearch;
