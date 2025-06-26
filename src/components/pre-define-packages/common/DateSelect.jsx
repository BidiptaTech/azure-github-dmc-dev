import React, { useState, useEffect } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";

const DateSelect = ({ onChange, value }) => {
  // State to store the selected date
  const [date, setDate] = useState(
    value ? new DateObject({ date: value, format: "YYYY-MM-DD" }) : new DateObject()
  );

  useEffect(() => {
    if (date) {
      // Format date to YYYY-MM-DD and pass to parent
      // This will be used as the arrival date for package calculations
      const formattedDate = date instanceof DateObject ? 
        date.format("YYYY-MM-DD") : date;
      onChange(formattedDate);
    }
  }, [date, onChange]);

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <DatePicker
        inputClass="custom_input-picker"
        containerClassName="custom_container-picker"
        value={date}
        onChange={(newDate) => {
          setDate(newDate);
        }}
        numberOfMonths={1}
        offsetY={10}
        format="DD/MM/YYYY" // Display format
        minDate={new DateObject()} // Minimum date is today
        editable={false}
        placeholder="Select arrival date"
      />
    </div>
  );
};

export default DateSelect; 