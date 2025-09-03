import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { TextField } from "@mui/material";

const DateSearch1 = ({ selectedDate, setSelectedDate, disabled = false }) => {
  const checkIn = useSelector(
    (state) =>
      state.hotels.tourdetails.check_in_time ||
      state.hotels.tourdetails.CheckInTime || state.hotels.tourdetails.data.CheckInTime
  );
  console.log("checkIn (original):", checkIn);  
 
  const formatDateToDDMMYYYY = (dateString) => {
    if (!dateString) return null;
    
    // Handle ISO datetime format (e.g., 2025-09-15T00:00:00.000000Z)
    if (dateString.includes("T") && dateString.includes("Z")) {
      const date = new Date(dateString);
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }
    
    // Check if the date is already in YYYY-MM-DD format
    if (dateString.includes("-") && dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
      return dateString; // Already in correct format
    }
    
    // Handle DD/MM/YYYY format (previous format)
    if (dateString.includes("/")) {
      const [day, month, year] = dateString.split("/");
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    }
    
    // If neither format matches, return as is
    return dateString;
  };

  const formattedCheckIn = formatDateToDDMMYYYY(checkIn);
  console.log("checkIn (formatted):", formattedCheckIn);

  // Automatically update selectedDate when checkIn changes
  useEffect(() => {
    if (formattedCheckIn) {
      setSelectedDate(formattedCheckIn);
    }
  }, [formattedCheckIn, setSelectedDate]);

  return (
    <div className={`text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker ${disabled ? 'opacity-70' : ''}`}>
      <TextField
        fullWidth
        type="date"
        variant="outlined"
        value={selectedDate} // Use selectedDate state
        InputProps={{ 
          readOnly: true,
          style: { 
            color: disabled ? '#999' : 'inherit',
            backgroundColor: disabled ? '#f0f0f0' : 'white' 
          }
        }}
        InputLabelProps={{ shrink: true }}
        sx={{
          backgroundColor: disabled ? "#f0f0f0" : "none",
          opacity: disabled ? 0.8 : 1,
          border: "none",
          "& .MuiInputBase-root": {
            borderRadius: "4px",
          },
          "& .MuiOutlinedInput-notchedOutline": {
            border: "none",
          },
        }}
        disabled={true}
      />
    </div>
  );
};

export default DateSearch1;
