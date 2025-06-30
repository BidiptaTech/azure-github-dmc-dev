import React, { useState, useEffect, useRef } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import "./dateRangePickerStyles.css";

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const DateRangePicker = ({ onDateChange }) => {
  const today = new DateObject(); // Current date
  const tomorrow = new DateObject().add(1, "day"); // Tomorrow's date

  const [dates, setDates] = useState([today, tomorrow]); // Default selection: today and tomorrow
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const prevDatesRef = useRef([today, tomorrow]);

  useEffect(() => {
    // Only call onDateChange if dates actually changed
    const prevDatesStr = JSON.stringify(prevDatesRef.current);
    const currentDatesStr = JSON.stringify(dates);
    
    if (prevDatesStr !== currentDatesStr) {
      onDateChange(dates);
      prevDatesRef.current = dates;
    }
  }, [dates]);

  // Handle date changes
  const handleDateChange = (newDates) => {
    setDates(newDates);
    
    // Reset to default if all dates are cleared
    if (!newDates || (Array.isArray(newDates) && newDates.length === 0)) {
      const defaultDates = [today, tomorrow];
      setDates(defaultDates);
    }
  };

  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <DatePicker
        inputClass="custom_input-picker"
        containerClassName="custom_container-picker"
        value={dates}
        onChange={handleDateChange}
        numberOfMonths={2}
        offsetY={10}
        range
        rangeHover
        format="MMMM DD"
        minDate={today}     // Keep minimum date as today
        // Remove maxDate prop to allow selection of any future date
        editable={false}    // Prevent manual typing while still allowing calendar selection
      />
      <Snackbar
        open={openSnackbar}
        autoHideDuration={4000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: "top", horizontal: "center" }}
      >
        <Alert onClose={handleCloseSnackbar} severity="warning">
          {snackbarMessage}
        </Alert>
      </Snackbar>
    </div>
  );
};

export default DateRangePicker; 