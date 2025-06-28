import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { TextField } from "@mui/material";

const DateSearch1 = ({ selectedDate, setSelectedDate, disabled = false }) => {
  const checkIn = useSelector(
    (state) =>
      state.hotels.tourdetails.check_in_time ||
      state.hotels.tourdetails.CheckInTime || state.hotels.tourdetails.data.CheckInTime
  );
  console.log("checkIn", checkIn);  
 
  const formatDateToDDMMYYYY = (dateString) => {
    if (!dateString) return null;
    const [day, month, year] = dateString.split("/");
    return `${year}-${month}-${day}`;
  };

  const formattedCheckIn = formatDateToDDMMYYYY(checkIn);

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
