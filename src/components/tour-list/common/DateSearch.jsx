import React, { useState, useEffect } from "react";
import { useSelector } from "react-redux";
import DatePicker, { DateObject } from "react-multi-date-picker";

const DateSearch = ({ onDateSelect }) => {
  // Fetch tour details from Redux state
  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  // console.log("tourdetails123", tourdetails);

  // Convert CheckInTime and CheckOutTime to DateObject format
  const checkInDate = tourdetails?.CheckInTime
    ? new DateObject({ date: tourdetails.CheckInTime, format: "DD/MM/YYYY" })
    : null;
  const checkOutDate = tourdetails?.CheckOutTime
    ? new DateObject({ date: tourdetails.CheckOutTime, format: "DD/MM/YYYY" })
    : null;

  // State to store the selected date, set default to check-in date if available
  const [date, setDate] = useState(checkInDate || null);

  // Check if tourdetails are available for date selection
  const isTourDetailsAvailable = checkInDate && checkOutDate;

  useEffect(() => {
    if (date) {
      onDateSelect(date);  // Pass the selected date to the parent component
    }
  }, [date, onDateSelect]);

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      {isTourDetailsAvailable ? (
        <DatePicker
          inputClass="custom_input-picker"
          containerClassName="custom_container-picker"
          value={date}
          onChange={(newDate) => {
            setDate(newDate);
          }}
          numberOfMonths={2}
          offsetY={10}
          format="DD/MM/YYYY"
          minDate={checkInDate} // Prevents selection before check-in
          maxDate={checkOutDate} // Prevents selection after check-out
          editable={false}    // Prevent manual typing while still allowing calendar selection
        />
      ) : (
        <div className="text-warning">Booking dates are unavailable</div>
      )}
    </div>
  );
};

export default DateSearch;
