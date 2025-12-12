import React, { useState, useEffect, useRef } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import './DateSearch.css';

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
  const [isMobile, setIsMobile] = useState(false);
  const datePickerRef = useRef(null);

  // Mobile detection
  useEffect(() => {
    const checkMobile = () => {
      setIsMobile(window.innerWidth < 768);
    };
    
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    return () => window.removeEventListener('resize', checkMobile);
  }, []);

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
        inputClass="custom_input-picker"
        containerClassName={`custom_container-picker ${disabled ? 'disabled' : ''}`}
        value={dates}
        onChange={handleDateChange}
        numberOfMonths={isMobile ? 1 : 2}
        offsetY={10}
        range
        rangeHover
        format="MMM DD"
        minDate={today}
        editable={false}
        disabled={disabled}
        style={{ 
          position: 'relative', 
          zIndex: 40,
          opacity: disabled ? 0.6 : 1,
          pointerEvents: disabled ? 'none' : 'auto'
        }}
        calendarStyle={{ 
          position: 'fixed', 
          zIndex: 999999,
          boxShadow: '0 8px 32px rgba(0, 0, 0, 0.12)',
          borderRadius: '12px',
          border: '1px solid rgba(0, 0, 0, 0.08)',
          backgroundColor: '#ffffff',
          backdropFilter: 'blur(10px)',
        }}
        className="enhanced-date-picker"
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

export default DateSearch;
