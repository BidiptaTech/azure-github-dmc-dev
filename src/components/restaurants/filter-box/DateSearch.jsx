import React, { useState } from "react";
import { useSelector } from "react-redux";
import DatePicker, { DateObject } from "react-multi-date-picker";

const DateSearch = ({ setSelectedDate }) => {
  // Fetch search params from Redux state
  const searchParams = useSelector((state) => state.restaurants.searchParams);

  //  console.log("Search Params from Redux:", searchParams);

  // Use searchParams to set the default date
  const defaultDate = searchParams?.date || null;

  //  console.log("defaultDate", defaultDate);

  // Ensure default date value is in a valid format, if not available, use today's date
  const selectedDate = defaultDate
    ? new DateObject(defaultDate)
    : new DateObject(); // Use today's date if no valid date is available

  // State to store the selected date, defaulting to the selected date
  const [date, setDate] = useState(selectedDate);

 

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <DatePicker
        inputClass="custom_input-picker"
        containerClassName="custom_container-picker"
        value={date} // Ensure the DatePicker displays the default date
        onChange={(newDate) => {
          setDate(newDate);
          setSelectedDate(newDate);
        }}
        numberOfMonths={2}
        offsetY={10}
        format="DD/MM/YYYY"
        readOnly // This disables the input field
      />
    </div>
  );
};

export default DateSearch;
