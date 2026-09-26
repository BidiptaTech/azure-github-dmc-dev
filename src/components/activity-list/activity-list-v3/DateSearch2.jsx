import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { TextField } from "@mui/material";

const DateSearch2 = ({ selectedDate1, setSelectedDate1 }) => {
  // const [dates, setDates] = useState([
  //   new DateObject().setDay(15),
  //   new DateObject().setDay(14).add(1, "month"),
  // ]);

  const checkIn = useSelector(
    (state) =>
      state.hotels?.tourdetails?.check_in_time ||
      state.hotels?.tourdetails?.CheckInTime ||
      state.hotels?.tourdetails?.data?.CheckInTime
  );
  const checkOut = useSelector(
    (state) =>
      state.hotels?.tourdetails?.check_out_time ||
      state.hotels?.tourdetails?.CheckOutTime ||
      state.hotels?.tourdetails?.data?.CheckOutTime
  );
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

  // Now you can apply it to your `checkIn` and `checkOut`
  const formattedCheckIn = formatDateToDDMMYYYY(checkIn);
  const formattedCheckOut = formatDateToDDMMYYYY(checkOut);
  useEffect(() => {
    if (!selectedDate1 && formattedCheckIn) {
      setSelectedDate1(formattedCheckIn);
    }
  }, [selectedDate1, formattedCheckIn, setSelectedDate1]);

  const handleDateChange = (event) => {
    setSelectedDate1(event.target.value);
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <TextField
        fullWidth
        // label="Pickup Date"
        type="date"
        variant="outlined"
        value={selectedDate1 || formattedCheckIn}
        onChange={handleDateChange}
        inputProps={{
          min: formattedCheckIn, // Minimum selectable date
          max: formattedCheckOut, // Maximum selectable date
        }}
        InputLabelProps={{ shrink: true }}
        onClick={(e) => e.target.showPicker()} // Open the calendar on click
        sx={{
          backgroundColor: "none",
          border: "none", // Remove the border
          "& .MuiInputBase-root": {
            borderRadius: "4px", // Optional: set border-radius if you want rounded edges
          },
          "& .MuiOutlinedInput-notchedOutline": {
            border: "none", // Remove the outline border
          },
        }}
      />
    </div>
  );
};

export default DateSearch2;
