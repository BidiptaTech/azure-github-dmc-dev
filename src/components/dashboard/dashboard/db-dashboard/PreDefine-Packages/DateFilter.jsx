import React, { useState } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import { Button, Box } from "@mui/material";
import { CalendarToday } from "@mui/icons-material";

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const DateFilter = ({ onDateChange, isOpen, onToggle }) => {
  const today = new DateObject(); // Current date
  const tomorrow = new DateObject().add(1, "day"); // Tomorrow's date

  const [dates, setDates] = useState([today, tomorrow]); // Default selection: today and tomorrow
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");

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
    <Box sx={{ position: 'relative', zIndex: 100 }}>
      <Button 
        startIcon={<CalendarToday />} 
        variant="outlined" 
        size="small"
        color={isOpen ? "primary" : "inherit"}
        onClick={onToggle}
      >
        {isOpen ? "Close Calendar" : "Date Filter"}
      </Button>
      
      {isOpen && (
        <Box sx={{
          position: 'absolute',
          right: 0,
          top: '45px',
          zIndex: 9999,
          backgroundColor: 'white',
          padding: '5px',
          boxShadow: '0 4px 20px rgba(0, 0, 0, 0.15)',
          borderRadius: '8px'
        }}>
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
            minDate={null}     // Allow past dates for filtering historical data
            editable={false}    // Prevent manual typing while still allowing calendar selection
            calendarStyle={{ 
              boxShadow: 'none',
            }}
            className="light-blue-theme"
          />
        </Box>
      )}
      
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
    </Box>
  );
};

export default DateFilter; 