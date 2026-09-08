import React, { useState, useEffect } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import { Box, Typography } from "@mui/material";

// Import the default styles
import "react-multi-date-picker/styles/layouts/mobile.css";

const DateRangeSelect = ({ onChange, value, label = "Date Range" }) => {
  // Set default date range if not provided
  const [dates, setDates] = useState(() => {
    if (value && Array.isArray(value) && value.length === 2) {
      return value.map(date => new DateObject(date));
    }
    return [new DateObject(), new DateObject().add(1, "day")];
  });

  useEffect(() => {
    if (value && Array.isArray(value) && value.length === 2 && 
        (dates[0].format("YYYY-MM-DD") !== value[0] || 
         dates[1].format("YYYY-MM-DD") !== value[1])) {
      setDates(value.map(date => new DateObject(date)));
    }
  }, [value]);

  const handleDateChange = (newDates) => {
    setDates(newDates);
    
    // Reset to default if all dates are cleared
    if (!newDates || (Array.isArray(newDates) && newDates.length === 0)) {
      const today = new DateObject();
      const tomorrow = new DateObject().add(1, "day");
      setDates([today, tomorrow]);
      
      if (onChange) {
        onChange([today.format("YYYY-MM-DD"), tomorrow.format("YYYY-MM-DD")]);
      }
      return;
    }
    
    // Call parent's onChange with formatted dates
    if (onChange && Array.isArray(newDates) && newDates.length === 2) {
      onChange(newDates.map(date => date.format("YYYY-MM-DD")));
    }
  };

  return (
    <Box sx={{ minWidth: 200, maxWidth: '100%' }}>
      <Typography variant="subtitle2" sx={{ mb: 1 }}>{label}</Typography>
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
          format="YYYY-MM-DD"
          minDate={new DateObject()}  // Today as minimum date
          editable={false}
          style={{
            width: '100%',
            boxShadow: 'none',
            border: '1px solid #ddd',
            borderRadius: '4px',
            padding: '8px 15px',
            fontSize: '14px'
          }}
        />
      </div>

      <style jsx>{`
        .custom_input-picker {
          width: 100%;
          height: 40px;
          border: 1px solid #ddd;
          border-radius: 4px;
          padding: 8px 14px;
          font-size: 14px;
          color: #666;
          cursor: pointer;
          background-color: white;
        }

        .custom_input-picker:focus {
          outline: none;
          border-color: #1976d2;
          box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
        }

        .rmdp-wrapper {
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
          border-radius: 8px !important;
          border: none !important;
        }

        .rmdp-day.rmdp-selected span {
          background-color: #1976d2 !important;
        }

        .rmdp-day.rmdp-today span {
          background-color: rgba(25, 118, 210, 0.2) !important;
          color: #1976d2 !important;
        }

        .rmdp-range {
          background-color: rgba(25, 118, 210, 0.7) !important;
        }
      `}</style>
    </Box>
  );
};

export default DateRangeSelect; 