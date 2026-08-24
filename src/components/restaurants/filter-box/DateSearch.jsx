import React, { useState, useEffect } from "react";
import { useSelector } from "react-redux";
import DatePicker, { DateObject } from "react-multi-date-picker";

const DateSearch = ({ setSelectedDate, selectedDate: selectedDateProp }) => {
  const searchParams = useSelector((state) => state.restaurants.searchParams);

  const defaultDate = selectedDateProp || searchParams?.date || null;

  const initialDate = defaultDate
    ? new DateObject(defaultDate)
    : new DateObject();

  const [date, setDate] = useState(initialDate);

  // Keep parent in sync so meal/time sections stay visible after refresh
  useEffect(() => {
    if (defaultDate) {
      const next = new DateObject(defaultDate);
      setDate(next);
      setSelectedDate(next);
    } else if (date) {
      setSelectedDate(date);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [defaultDate]);

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
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
        readOnly
      />
    </div>
  );
};

export default DateSearch;
