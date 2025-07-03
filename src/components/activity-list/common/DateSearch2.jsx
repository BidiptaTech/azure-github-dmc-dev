import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { TextField } from "@mui/material";

const DateSearch2 = ({ selectedDate1, setSelectedDate1, disabled = false }) => {
  // const checkIn = useSelector(
  //   (state) =>
  //     state.hotels.tourdetails.check_in_time ||
  //     state.hotels.tourdetails.CheckInTime
  // );
  const checkOut = useSelector(
    (state) =>
      state.hotels.tourdetails.check_out_time ||
      state.hotels.tourdetails.CheckOutTime ||
      state.hotels.tourdetails.data.CheckOutTime
  );
  console.log("checkOut (original):", checkOut);

  const formatDateToDDMMYYYY = (dateString) => {
    if (!dateString) return null;
    
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

  // const formattedCheckIn = formatDateToDDMMYYYY(checkIn);
  const formattedCheckOut = formatDateToDDMMYYYY(checkOut);
  console.log("checkOut (formatted):", formattedCheckOut);

  // Automatically update selectedDate1 when checkOut changes
  useEffect(() => {
    if (formattedCheckOut) {
      setSelectedDate1(formattedCheckOut);
    }
  }, [formattedCheckOut, setSelectedDate1]);

  return (
    <div className={`text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker ${disabled ? 'opacity-70' : ''}`}>
      <TextField
        fullWidth
        type="date"
        variant="outlined"
        value={selectedDate1} // Use selectedDate1 state
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

export default DateSearch2;
