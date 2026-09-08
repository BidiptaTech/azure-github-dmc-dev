import React, { useState, useEffect, useRef } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import { Box, Typography, Paper, styled } from "@mui/material";

// Import the default styles directly
import "react-multi-date-picker/styles/layouts/mobile.css";

// Custom styles for date indicators
const CalendarStyles = styled("style")({
  children: `
    .shared-date {
      background-color: rgba(76, 175, 80, 0.3) !important;
      border: 1px solid rgba(76, 175, 80, 0.6) !important;
    }
    
    .removed-date {
      background-color: rgba(244, 67, 54, 0.3) !important;
      border: 1px solid rgba(244, 67, 54, 0.6) !important;
    }
    
    .new-date {
      background-color: rgba(33, 150, 243, 0.3) !important;
      border: 1px solid rgba(33, 150, 243, 0.6) !important;
    }
    
    .rmdp-day:not(.rmdp-disabled):hover {
      background-color: rgba(33, 150, 243, 0.2) !important;
      color: #2196f3 !important;
    }
    
    .rmdp-range {
      background-color: rgba(33, 150, 243, 0.15) !important;
    }
    
    .rmdp-range-hover {
      background-color: rgba(33, 150, 243, 0.1) !important;
    }
    
    .rmdp-wrapper {
      box-shadow: none !important;
      border: none !important;
    }
    
    .rmdp-header-values {
      font-weight: 600 !important;
      color: #2c3e50 !important;
    }
    
    .rmdp-day {
      height: 38px !important;
      width: 38px !important;
    }
    
    .rmdp-selected span {
      background-color: #3366ff !important;
      color: white !important;
      box-shadow: 0 4px 6px rgba(51, 102, 255, 0.25) !important;
    }
  `
});

// Styled components for the calendar container
const StyledCalendarContainer = styled(Box)(({ theme }) => ({
  '& .rmdp-wrapper': {
    boxShadow: 'none',
    width: '100%',
  },
  '& .rmdp-calendar': {
    width: '100%',
  }
}));

// Styled components for the calendar inputs
const StyledDateInput = styled("input")({
  width: '100%',
  height: '50px',
  padding: '0 15px',
  borderRadius: '4px',
  border: '1px solid #ddd',
  fontSize: '15px',
  color: '#051036',
  fontWeight: '500',
  boxShadow: '0px 3px 10px rgba(0, 0, 0, 0.03)',
  transition: 'all 0.3s ease',
  cursor: 'pointer',
  '&:hover': {
    borderColor: '#3366ff',
  },
  '&:focus': {
    outline: 'none',
    borderColor: '#3366ff',
    boxShadow: '0px 3px 10px rgba(51, 102, 255, 0.15)',
  }
});

// Helper function to create a date range array from two dates
const createDateRangeArray = (startDate, endDate) => {
  if (!startDate || !endDate) return [];
  
  const dates = [];
  let currentDate = new DateObject(startDate);
  const endDateObj = new DateObject(endDate);
  
  // Add start date
  dates.push(currentDate.format("YYYY-MM-DD"));
  
  // Add all dates in between
  while (currentDate.toUnix() < endDateObj.toUnix()) {
    currentDate = new DateObject(currentDate).add(1, "day");
    dates.push(currentDate.format("YYYY-MM-DD"));
  }
  
  return dates;
};

const DateRangeSelector = ({ initialCheckin, initialCheckout, onDateChange }) => {
  const calendarRef = useRef(null);
  const renderCountRef = useRef(0);
  
  // Increment render count on each render
  useEffect(() => {
    renderCountRef.current += 1;
  });
  
  // Helper function to create properly formatted DateObject
  const createDateObject = (dateString) => {
    if (!dateString) return null;
    
    try {
      const [year, month, day] = dateString.split("-").map(num => parseInt(num, 10));
      return new DateObject({
        year,
        month,
        day
      });
    } catch (e) {
      console.error("Error creating DateObject:", e);
      return null;
    }
  };
  
  // Convert initial dates to DateObject if provided
  const getInitialDates = () => {
    const today = new DateObject();
    const tomorrow = new DateObject().add(1, "day");
    
    if (initialCheckin && initialCheckout) {
      // Create date objects
      const checkinDate = createDateObject(initialCheckin);
      const checkoutDate = createDateObject(initialCheckout);
      
      if (checkinDate && checkoutDate) {
        return [checkinDate, checkoutDate];
      }
    }
    
    return [today, tomorrow]; // Default fallback
  };

  // Initialize state with separate variables for initial and current dates
  const [initialDateObjects, setInitialDateObjects] = useState(getInitialDates());
  const [selectedDateObjects, setSelectedDateObjects] = useState(getInitialDates());
  const [userHasSelectedDates, setUserHasSelectedDates] = useState(false);
  
  // Create date range arrays for the component
  const [initialDateRangeArray, setInitialDateRangeArray] = useState([]);
  const [selectedDateRangeArray, setSelectedDateRangeArray] = useState([]);
  
  // Initialize initial date objects only once
  useEffect(() => {
    if (initialCheckin && initialCheckout) {
      const checkinDate = createDateObject(initialCheckin);
      const checkoutDate = createDateObject(initialCheckout);
      
      if (checkinDate && checkoutDate) {
        setInitialDateObjects([checkinDate, checkoutDate]);
      }
      if (!userHasSelectedDates && checkinDate && checkoutDate) {
        setSelectedDateObjects([checkinDate, checkoutDate]);
      }
    }
  }, [initialCheckin, initialCheckout, userHasSelectedDates]);
  
  // Update initial date range array when initialDateObjects change
  useEffect(() => {
    if (initialDateObjects && initialDateObjects.length === 2) {
      const newRange = createDateRangeArray(initialDateObjects[0], initialDateObjects[1]);
      setInitialDateRangeArray(newRange);
    }
  }, [initialDateObjects]);
  
  // Update selected date range array when selectedDateObjects change
  useEffect(() => {
    if (selectedDateObjects && selectedDateObjects.length === 2) {
      const newRange = createDateRangeArray(selectedDateObjects[0], selectedDateObjects[1]);
      setSelectedDateRangeArray(newRange);
    }
  }, [selectedDateObjects]);
  
  // Apply CSS classes to calendar days based on date ranges
  useEffect(() => {
    // Wait for both date ranges to be available
    if (initialDateRangeArray.length === 0 || selectedDateRangeArray.length === 0) {
      return;
    }
    
    // Get the calendar container
    const calendarContainer = document.querySelector('.rmdp-wrapper');
    if (!calendarContainer) {
      return;
    }
    
    // Get all day elements
    const dayElements = calendarContainer.querySelectorAll('.rmdp-day:not(.rmdp-disabled)');
    
    // Process each day element
    dayElements.forEach(dayElement => {
      // Get date info from the element
      const dateAttribute = dayElement.getAttribute('data-date');
      if (!dateAttribute) return;
      
      // Parse the date attribute
      try {
        const dateObj = new DateObject(dateAttribute);
        const formattedDate = dateObj.format("YYYY-MM-DD");
        
        // Reset classes
        dayElement.classList.remove('shared-date', 'removed-date', 'new-date');
        
        // Apply classes based on date ranges
        const inInitialRange = initialDateRangeArray.includes(formattedDate);
        const inSelectedRange = selectedDateRangeArray.includes(formattedDate);
        
        if (inInitialRange && inSelectedRange) {
          dayElement.classList.add('shared-date');
        } else if (inInitialRange) {
          dayElement.classList.add('removed-date');
        } else if (inSelectedRange) {
          dayElement.classList.add('new-date');
        }
      } catch (e) {
        console.error("Error processing date element:", e);
      }
    });
  }, [initialDateRangeArray, selectedDateRangeArray]);
  
  // Color constants for date types
  const dateColors = {
    overlap: 'rgba(76, 175, 80, 0.3)', // Light green
    removed: 'rgba(244, 67, 54, 0.3)', // Light red
    new: 'rgba(33, 150, 243, 0.3)', // Light blue
  };

  // Handle date changes
  const handleDateChange = (newDates) => {
    setUserHasSelectedDates(true);
    // Only update the selected dates, not the initial dates
    if (!newDates || (Array.isArray(newDates) && newDates.length === 0)) {
      // Reset to initial dates if all dates are cleared
      setSelectedDateObjects([...initialDateObjects]);
      return;
    }
    
    // Update the selected date objects
    setSelectedDateObjects(newDates);
    
    // Call onDateChange when the user changes the dates
    if (onDateChange && newDates.length === 2) {
      onDateChange(newDates);
    }
  };

  // Handle calendar open
  const handleCalendarOpen = () => {
    // Schedule the color application after a short delay to ensure the calendar is rendered
    setTimeout(() => {
      // Trigger a re-run of the effect that applies classes
      setSelectedDateRangeArray([...selectedDateRangeArray]);
    }, 100);
  };

  return (
    <div className="form-datepicker js-form-datepicker">
      <CalendarStyles />
      
      <div className="form-datepicker__field">
        <div className="text-14 fw-500 text-dark-1 mb-10">Select Date Range</div>
        
        <StyledCalendarContainer>
          <DatePicker
            ref={calendarRef}
            value={selectedDateObjects}
            onChange={handleDateChange}
            onOpen={handleCalendarOpen}
            range
            rangeHover
            numberOfMonths={1}
            format="YYYY-MM-DD"
            render={(dateObject, openCalendar) => {
              return (
                <div className="date-input-container" onClick={openCalendar}>
                  {/* <div className="icon-calendar text-15 text-light-1 absolute" style={{ left: '15px', top: '50%', transform: 'translateY(-50%)' }}></div> */}
                  <StyledDateInput
                    value={Array.isArray(selectedDateObjects) ? 
                      selectedDateObjects.map(date => date.format("YYYY-MM-DD")).join(" → ") : 
                      ""
                    }
                    readOnly
                    placeholder="Check-in — Check-out"
                    style={{
                      paddingLeft: '40px'
                    }}
                  />
                </div>
              );
            }}
            arrow={false}
            calendarPosition="bottom-left"
            offsetY={10}
            fullYear={false}
          />
        </StyledCalendarContainer>
      </div>
      
      {/* Legend */}
      {/* <div className="d-flex mt-10">
        <div className="d-flex align-items-center mr-20">
          <div style={{ 
            width: '12px', 
            height: '12px', 
            backgroundColor: dateColors.overlap, 
            marginRight: '6px',
            borderRadius: '2px',
            border: '1px solid rgba(76, 175, 80, 0.6)'
          }}></div>
          <div className="text-12 text-light-1">Current Dates</div>
        </div>
        <div className="d-flex align-items-center mr-20">
          <div style={{ 
            width: '12px', 
            height: '12px', 
            backgroundColor: dateColors.new, 
            marginRight: '6px',
            borderRadius: '2px',
            border: '1px solid rgba(33, 150, 243, 0.6)'
          }}></div>
          <div className="text-12 text-light-1">Selected Dates</div>
        </div>
      </div> */}
    </div>
  );
};

export default DateRangeSelector; 