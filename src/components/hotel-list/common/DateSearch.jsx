import React from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";

const DateSearch = ({dateRange, setDateRange, handleSelect, initialMinDate, initialMaxDate}) => {
  // Convert dateRange to DateObject format
  const formattedDateRange = dateRange.map(date => 
    date ? new DateObject({ date: date, format: "YYYY-MM-DD" }) : null
  );
  
  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      <DatePicker
        inputClass="custom_input-picker"
        containerClassName="custom_container-picker"
        value={formattedDateRange.filter(date => date !== null)}
        onChange={handleSelect}
        numberOfMonths={2}
        offsetY={10}
        range
        rangeHover
        format="YYYY-MM-DD"
        minDate={initialMinDate ? new DateObject(initialMinDate) : new DateObject()} 
        maxDate={initialMaxDate ? new DateObject(initialMaxDate) : null}  
        editable={false}
      />
    </div>
  );
};

export default DateSearch;
