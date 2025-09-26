import React, { useState } from "react";
import { useSelector } from "react-redux";
import DatePicker, { DateObject } from "react-multi-date-picker";
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import Typography from '@mui/material/Typography';

const DateSearch = ({ setSelectedDate }) => {
  // Fetch search params from Redux state
  const searchParams = useSelector((state) => state.attractions.searchParams);

  //  console.log("Search Params from Redux:", searchParams);

  // Use searchParams to set the default date
  const defaultDate = searchParams?.date || null;

  // console.log("defaultDate", defaultDate);

  // Ensure default date value is in a valid format, if not available, use today's date
  const selectedDate = defaultDate
    ? new DateObject(defaultDate)
    : new DateObject(); // Use today's date if no valid date is available

  // State to store the selected date, defaulting to the selected date
  const [date, setDate] = useState(selectedDate);

  // Function to format date as "Sun, 11 May'25"
  const formatDisplayDate = (dateObj) => {
    if (!dateObj) return "Select Date";
    
    const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
    // Get JavaScript Date object to correctly get day of week
    const jsDate = dateObj.toDate ? dateObj.toDate() : new Date(dateObj);
    
    const dayName = days[jsDate.getDay()];
    const day = jsDate.getDate();
    const month = months[jsDate.getMonth()];
    const year = `'${jsDate.getFullYear().toString().slice(2)}`;
    
    return `${dayName}, ${day} ${month}${year}`;
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <div 
        style={{ 
          display: "flex", 
          alignItems: "center",
          backgroundColor: "rgba(255, 255, 255, 0.9)",
          borderRadius: "10px",
          boxShadow: "0 4px 10px rgba(0, 0, 0, 0.05)",
          padding: "15px 14px",
          transition: "all 0.3s ease",
        }}
      >
        <CalendarMonthIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
        <Typography sx={{ fontWeight: 500 }}>
          {formatDisplayDate(date)}
        </Typography>
      </div>
      
      <div style={{ position: "absolute", top: 0, left: 0, right: 0, bottom: 0, opacity: 0 }}>
        <DatePicker
          inputClass="custom_input-picker"
          containerClassName="custom_container-picker"
          value={date}
          onChange={(newDate) => {
            setDate(newDate);
            setSelectedDate(newDate);
          }}
          numberOfMonths={2}
          offsetY={10}
          format="DD/MM/YYYY"
        />
      </div>
    </div>
  );
};

export default DateSearch;
