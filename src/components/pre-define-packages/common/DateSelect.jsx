import React, { useState, useEffect } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import { useTheme, useMediaQuery } from "@mui/material";

const DateSelect = ({ onChange, value, portal = true }) => {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down("md"));
  const [date, setDate] = useState(
    value
      ? new DateObject({ date: value, format: "YYYY-MM-DD" })
      : new DateObject()
  );

  useEffect(() => {
    if (!date || !onChange) return;
    const formattedDate =
      date instanceof DateObject ? date.format("YYYY-MM-DD") : date;
    onChange(formattedDate);
    // Intentionally omit onChange — parent often passes inline setters
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [date]);

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker pp-date-select">
      <DatePicker
        inputClass="custom_input-picker"
        containerClassName="custom_container-picker pp-date-select__calendar"
        value={date}
        onChange={(newDate) => {
          setDate(newDate);
        }}
        numberOfMonths={isMobile ? 1 : 2}
        offsetY={10}
        format="DD/MM/YYYY"
        minDate={new DateObject()}
        editable={false}
        placeholder="Select arrival date"
        portal={portal}
        zIndex={30000}
        calendarPosition="bottom-center"
      />
    </div>
  );
};

export default DateSelect;
