import React, { useEffect } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";

const DateSearch = ({
  onDateSelect,
  minDate = null,
  maxDate = null,
  value = null,
}) => {
  const resolvedMin = minDate instanceof DateObject ? minDate : null;
  const resolvedMax = maxDate instanceof DateObject ? maxDate : null;
  const resolvedValue = value instanceof DateObject ? value : null;
  const isAvailable = resolvedMin && resolvedMax;

  useEffect(() => {
    if (resolvedValue) {
      onDateSelect(resolvedValue);
    }
  }, [resolvedValue, onDateSelect]);

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker">
      {isAvailable ? (
        <DatePicker
          inputClass="custom_input-picker"
          containerClassName="custom_container-picker"
          value={resolvedValue}
          onChange={(newDate) => {
            onDateSelect(newDate);
          }}
          numberOfMonths={2}
          offsetY={10}
          format="DD/MM/YYYY"
          minDate={resolvedMin}
          maxDate={resolvedMax}
          editable={false}
        />
      ) : (
        <div className="text-warning">Booking dates are unavailable</div>
      )}
    </div>
  );
};

export default DateSearch;
