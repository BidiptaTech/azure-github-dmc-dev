import React, { useState, useRef, useEffect } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import { Button, Box, Popper, Paper, ClickAwayListener } from "@mui/material";
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
  const buttonRef = useRef(null);
  
  // Keep track of the last applied filter
  const [appliedFilter, setAppliedFilter] = useState(null);

  // Reset dates when external clear happens
  useEffect(() => {
    // If isOpen is false and there's no date range from parent, reset our internal state
    if (!isOpen && !onDateChange) {
      setDates([today, tomorrow]);
      setAppliedFilter(null);
    }
  }, [isOpen, onDateChange]);

  // Handle date changes
  const handleDateChange = (newDates) => {
    // Reset to default if all dates are cleared
    if (!newDates || (Array.isArray(newDates) && newDates.length === 0)) {
      const defaultDates = [today, tomorrow];
      setDates(defaultDates);
      setAppliedFilter(defaultDates);
      
      // Notify parent with formatted dates
      if (onDateChange && typeof onDateChange === 'function') {
        onDateChange(defaultDates);
      }
      return;
    }
    
    // Update internal state
    setDates(newDates);
    setAppliedFilter(newDates);
    
    // Notify parent component with the new dates
    if (onDateChange && typeof onDateChange === 'function') {
      onDateChange(newDates);
    }
  };

  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  const handleClickAway = (event) => {
    // Only close if clicking outside of the calendar
    if (isOpen && !event.target.closest('.rmdp-container') && 
        !event.target.closest('.rmdp-calendar') && 
        !buttonRef.current.contains(event.target)) {
      // Close the calendar but keep the filter applied
      onToggle();
    }
  };
  
  // When the calendar is closed, ensure the filter stays applied
  useEffect(() => {
    if (!isOpen && appliedFilter) {
      // Make sure the filter remains applied when calendar is closed
      if (onDateChange && typeof onDateChange === 'function') {
        onDateChange(appliedFilter);
      }
    }
  }, [isOpen, appliedFilter, onDateChange]);

  return (
    <div style={{ position: 'relative', zIndex: 9999 }}>
      <Button 
        ref={buttonRef}
        startIcon={<CalendarToday />} 
        variant="outlined" 
        size="small"
        color={appliedFilter ? "primary" : "inherit"}
        onClick={onToggle}
        style={{ zIndex: 1 }}
      >
        {isOpen ? "Apply Filter" : appliedFilter ? "Date Filter Applied" : "Date Filter"}
      </Button>
      
      {isOpen && (
        <ClickAwayListener onClickAway={handleClickAway}>
          <div>
            <Popper
              open={isOpen}
              anchorEl={buttonRef.current}
              placement="bottom-end"
              style={{
                zIndex: 9999,
                marginTop: '5px',
              }}
              modifiers={[
                {
                  name: 'preventOverflow',
                  enabled: true,
                  options: {
                    boundary: document.body,
                  },
                },
              ]}
            >
              <Paper 
                elevation={3}
                sx={{
                  padding: '10px',
                  borderRadius: '8px',
                  backgroundColor: 'white',
                  maxWidth: '600px'
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
                  minDate={null}     // Allow past dates for filtering historical data
                  editable={false}    // Prevent manual typing while still allowing calendar selection
                  className="light-blue-theme"
                />
              </Paper>
            </Popper>
          </div>
        </ClickAwayListener>
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
        
        /* Fix z-index issues */
        .rmdp-container {
          z-index: 9999 !important;
        }
        
        .rmdp-calendar {
          z-index: 9999 !important;
        }
        
        .rmdp-wrapper {
          z-index: 9999 !important;
          position: relative !important;
        }
        
        .rmdp-overlay {
          z-index: 9999 !important;
        }
        
        .MuiPopper-root {
          z-index: 9999 !important;
        }
      `}</style>
    </div>
  );
};

export default DateFilter; 