import React, { useState, useEffect, useRef } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const DateSearch = ({ onDateChange, disabled = false }) => {
  const today = new DateObject(); // Current date
  const tomorrow = new DateObject().add(1, "day"); // Tomorrow's date

  const [dates, setDates] = useState([today, tomorrow]); // Default selection: today and tomorrow
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const datePickerRef = useRef(null);

  // Close date picker when disabled
  useEffect(() => {
    if (disabled) {
      // Force close all date picker calendars
      const calendarElements = document.querySelectorAll('.rmdp-calendar');
      calendarElements.forEach(calendar => {
        calendar.style.display = 'none';
        calendar.classList.remove('rmdp-calendar-open');
      });
      
      // Remove focus from any date picker inputs
      const datePickerInputs = document.querySelectorAll('.rmdp-input');
      datePickerInputs.forEach(input => {
        input.blur();
      });
      
      // Additional fallback: Force close any open date pickers
      setTimeout(() => {
        const allCalendars = document.querySelectorAll('.rmdp-calendar');
        allCalendars.forEach(calendar => {
          if (calendar.style.display !== 'none') {
            calendar.style.display = 'none';
            calendar.classList.remove('rmdp-calendar-open');
          }
        });
      }, 10);
    }
  }, [disabled]);

  // Handle date changes
  const handleDateChange = (newDates) => {
    // Reset to default if all dates are cleared
    if (!newDates || (Array.isArray(newDates) && newDates.length === 0)) {
      const defaultDates = [today, tomorrow];
      setDates(defaultDates);
      
      // Notify parent with formatted dates
      if (onDateChange && typeof onDateChange === 'function') {
        onDateChange(defaultDates);
      }
      return;
    }
    
    // Update internal state
    setDates(newDates);
    
    // Notify parent component with the new dates
    if (onDateChange && typeof onDateChange === 'function') {
      onDateChange(newDates);
    }
  };

  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <DatePicker
        ref={datePickerRef}
        inputClass={`custom_input-picker ${disabled ? 'disabled' : ''}`}
        containerClassName={`custom_container-picker ${disabled ? 'disabled' : ''}`}
        value={dates}
        onChange={handleDateChange}
        numberOfMonths={2}
        offsetY={10}
        range
        rangeHover
        format="MMM DD"
        minDate={today}     // Keep minimum date as today
        editable={false}    // Prevent manual typing while still allowing calendar selection
        disabled={disabled}  // Disable the date picker
        style={{ 
          position: 'relative', 
          zIndex: 40,
          opacity: disabled ? 0.6 : 1,
          pointerEvents: disabled ? 'none' : 'auto'
        }}
        calendarStyle={{ 
          position: 'absolute', 
          zIndex: 9999,
          boxShadow: '0 4px 20px rgba(0, 0, 0, 0.1)',
        }}
        className="light-blue-theme"
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

      <style jsx>{`
        .rmdp-wrapper {
          z-index: 9999 !important;
          position: absolute !important;
        }
        .rmdp-calendar {
          z-index: 9999 !important;
        }
        .rmdp-container {
          z-index: 9999 !important;
        }
      `}</style>

      <style jsx global>{`
        /* Light blue theme for date picker */
        .light-blue-theme .rmdp-range {
          background-color: #e6f4ff !important; 
          box-shadow: none !important;
        }
        
        .light-blue-theme .rmdp-selected {
          background-color: #4dabf7 !important;
          box-shadow: none !important;
        }
        
        .light-blue-theme .rmdp-range-hover {
          background-color: #e6f4ff !important;
          color: #333 !important;
        }
        
        .light-blue-theme .rmdp-day:not(.rmdp-disabled, .rmdp-day-hidden):hover {
          background-color: #c5e4ff !important;
          color: #333 !important;
        }
        
        .light-blue-theme .rmdp-arrow {
          border: solid #4dabf7 !important;
          border-width: 0 2px 2px 0 !important;
        }
        
        .light-blue-theme .rmdp-arrow-container:hover {
          background-color: #e6f4ff !important;
          box-shadow: none !important;
        }
      `}</style>
    </div>
  );
};

export default DateSearch;
