import React, { useState, useEffect } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import { Box, Typography, Snackbar } from "@mui/material";
import MuiAlert from "@mui/material/Alert";

// Import the default styles
import "react-multi-date-picker/styles/layouts/mobile.css";

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const DateRangeSelect = ({ onChange, value, label = "Date Range" }) => {
  const today = new DateObject(); // Current date
  const tomorrow = new DateObject().add(1, "day"); // Tomorrow's date

  // Set default date range if not provided
  const [dates, setDates] = useState(() => {
    if (value && Array.isArray(value) && value.length === 2) {
      return value.map(date => new DateObject(date));
    }
    return [today, tomorrow];
  });
  
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("warning");

  // Update internal dates when props change
  useEffect(() => {
    if (value && Array.isArray(value) && value.length === 2) {
      const newDates = value.map(date => new DateObject(date));
      
      // Only update if the dates have actually changed
      if (!dates.length || 
          dates[0].format("YYYY-MM-DD") !== newDates[0].format("YYYY-MM-DD") ||
          dates[1].format("YYYY-MM-DD") !== newDates[1].format("YYYY-MM-DD")) {
        setDates(newDates);
      }
    }
  }, [value]);
  
  // Notify parent component when dates change
  useEffect(() => {
    if (onChange && dates && dates.length === 2) {
      onChange(dates.map(date => date.format("YYYY-MM-DD")));
    }
  }, [dates, onChange]);

  const handleDateChange = (newDates) => {
    // Validate dates
    if (!newDates || (Array.isArray(newDates) && newDates.length === 0)) {
      // Reset to default if all dates are cleared
      setDates([today, tomorrow]);
      return;
    }

    // For single date, add tomorrow as the second date
    if (Array.isArray(newDates) && newDates.length === 1) {
      const nextDay = new DateObject(newDates[0]).add(1, "day");
      setDates([newDates[0], nextDay]);
      return;
    }

    // Normal case: array with two dates
    if (Array.isArray(newDates) && newDates.length === 2) {
      // Check if check-out is before check-in
      if (newDates[1].toUnix() <= newDates[0].toUnix()) {
        setSnackbarMessage("Check-out date must be after check-in date");
        setSnackbarSeverity("warning");
        setOpenSnackbar(true);
        return;
      }

      // Check if dates are in the past
      if (newDates[0].toUnix() < today.toUnix()) {
        setSnackbarMessage("Can't select dates in the past");
        setSnackbarSeverity("warning");
        setOpenSnackbar(true);
        // Reset to today and tomorrow
        setDates([today, tomorrow]);
        return;
      }

      setDates(newDates);
    }
  };
  
  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  return (
    <Box sx={{ minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
      
      <Box
        sx={{
          position: 'relative',
          border: '1px solid #ddd',
          borderRadius: '4px',
          '&:hover': {
            borderColor: '#aaa',
          },
          '&:focus-within': {
            borderColor: '#1976d2',
            boxShadow: '0 0 0 2px rgba(25, 118, 210, 0.2)',
          },
        }}
      >
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
          minDate={today}      // Minimum date is today
          editable={false}     // Prevent manual typing
          style={{ width: '100%' }}
          render={(value, openCalendar) => {
            const displayValue = value && Array.isArray(value) && value.length === 2
              ? `${value[0].format("MMMM DD")} — ${value[1].format("MMMM DD")}`
              : "Select dates";
            
            return (
              <Box 
                sx={{ 
                  display: 'flex',
                  alignItems: 'center',
                  padding: '8px 12px',
                  cursor: 'pointer',
                  width: '100%',
                  height: '40px',
                }}
                onClick={openCalendar}
              >
                <Typography>{displayValue}</Typography>
              </Box>
            );
          }}
        />
      </Box>

      <Snackbar
        open={openSnackbar}
        autoHideDuration={4000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: "top", horizontal: "center" }}
      >
        <Alert onClose={handleCloseSnackbar} severity={snackbarSeverity}>
          {snackbarMessage}
        </Alert>
      </Snackbar>

      <style jsx>{`
        .custom_input-picker {
          width: 100%;
          height: 40px;
          border: none;
          padding: 8px 14px;
          font-size: 14px;
          color: #666;
          cursor: pointer;
          background-color: transparent;
        }

        .custom_input-picker:focus {
          outline: none;
        }

        .rmdp-wrapper {
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
          border-radius: 8px !important;
          border: none !important;
        }

        .rmdp-calendar {
          padding: 8px !important;
        }

        .rmdp-day.rmdp-selected span {
          background-color: #1976d2 !important;
        }

        .rmdp-day.rmdp-today span {
          background-color: rgba(25, 118, 210, 0.2) !important;
          color: #1976d2 !important;
        }

        .rmdp-day:hover span {
          background-color: rgba(25, 118, 210, 0.1) !important;
        }

        .rmdp-day.rmdp-range span {
          background-color: rgba(25, 118, 210, 0.7) !important;
        }

        .rmdp-arrow-container:hover {
          background-color: rgba(25, 118, 210, 0.1) !important;
          box-shadow: none !important;
        }
      `}</style>
    </Box>
  );
};

export default DateRangeSelect; 