import React, { useEffect } from "react";
import { useSelector } from "react-redux";
import { TextField } from "@mui/material";

const toYmd = (value) => {
  if (value == null || value === "") return "";

  const dateString = String(value).trim();
  if (!dateString) return "";

  // ISO datetime (e.g. 2025-09-15T00:00:00.000000Z)
  if (dateString.includes("T")) {
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return "";
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  // Already YYYY-MM-DD
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    return dateString;
  }

  // DD-MM-YYYY or DD/MM/YYYY
  const slashOrDash = dateString.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
  if (slashOrDash) {
    const [, day, month, year] = slashOrDash;
    return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
  }

  return "";
};

const DateSearch1 = ({
  selectedDate,
  setSelectedDate,
  disabled = false,
  cityDate = "",
}) => {
  const checkIn = useSelector(
    (state) =>
      state.hotels?.tourdetails?.check_in_time ||
      state.hotels?.tourdetails?.CheckInTime ||
      state.hotels?.tourdetails?.data?.CheckInTime ||
      ""
  );

  const formattedCheckIn = toYmd(checkIn);
  const inputValue = toYmd(selectedDate) || toYmd(cityDate) || formattedCheckIn;

  // Prefer city-wise check-in; fall back to tour check-in
  useEffect(() => {
    const next = toYmd(cityDate) || formattedCheckIn;
    if (!next) return;
    if (toYmd(selectedDate) === next) return;
    setSelectedDate(next);
  }, [cityDate, formattedCheckIn, selectedDate, setSelectedDate]);

  return (
    <div
      className={`text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker ${
        disabled ? "opacity-70" : ""
      }`}
    >
      <TextField
        fullWidth
        type="date"
        variant="outlined"
        value={inputValue}
        InputProps={{
          readOnly: true,
          style: {
            color: disabled ? "#999" : "inherit",
            backgroundColor: disabled ? "#f0f0f0" : "white",
          },
        }}
        InputLabelProps={{ shrink: true }}
        sx={{
          backgroundColor: disabled ? "#f0f0f0" : "none",
          opacity: disabled ? 0.8 : 1,
          width: "100%",
          minWidth: 0,
          "& .MuiInputBase-root": {
            height: "42px",
            minHeight: "42px",
            borderRadius: "7px",
            boxSizing: "border-box",
          },
          "& .MuiOutlinedInput-input": {
            padding: "10px 12px",
            fontSize: "13px",
          },
        }}
        disabled={true}
      />
    </div>
  );
};

export default DateSearch1;
