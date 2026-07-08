import React, { useState } from "react";
import { FormControl, InputLabel, MenuItem, Select } from "@mui/material";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import TimerIcon from "@mui/icons-material/Timer";
import { alpha } from "@mui/material/styles";

const HourPackage = ({ hour, sethours, onHourChange }) => {
  const hours = Array.from({ length: 10 }, (_, i) => i + 1);
  const [open, setOpen] = useState(false); // State to control dropdown open/close

  const handleHourChange = (e) => {
    const selectedHour = e.target.value;
    sethours(selectedHour);
    onHourChange(selectedHour);
  };

  return (
    <FormControl fullWidth sx={{ backgroundColor: "none" }}>
      {!hour && (
        <InputLabel
          id="hours-label"
          shrink={false}
          sx={{
            display: "flex",
            alignItems: "center",
            "& .MuiSvgIcon-root": {
              marginRight: "8px",
              color: "#3554D1",
            },
          }}
        >
          <TimerIcon
            sx={{ fontSize: "20px", marginRight: "10px", color: "#3554D1" }}
          />
          Select the Package
        </InputLabel>
      )}
      <Select
        labelId="hours-label"
        id="hours"
        value={hour || ""}
        onChange={handleHourChange}
        open={open}
        onOpen={() => setOpen(true)}
        onClose={(e) => {
          setOpen(false);
          e.stopPropagation();
        }}
        // startAdornment={
        //   hour ? (
        //     <AccessTimeIcon sx={{ color: "#3554D1", ml: 1, mr: 1 }} />
        //   ) : null
        // }
        sx={{
          backgroundColor: "transparent",
          border: "2px solid #F5F5F5",
          borderRadius: "8px",
          height: "48px",
          "&.Mui-focused": {
            borderColor: "#3554D1",
            boxShadow: "0 0 0 2px rgba(53, 84, 209, 0.1)",
          },
          "&:hover": {
            borderColor: "#3554D1",
          },
          "&.Mui-disabled": {
            backgroundColor: alpha("#F5F5F5", 0.5),
            color: "rgba(0, 0, 0, 0.38)",
          },
          "& .MuiSelect-select": {
            display: "flex",
            alignItems: "center",
            padding: "12px 16px",
            fontWeight: hour ? "500" : "normal",
            color: hour ? "#3554D1" : "inherit",
          },
          "& .MuiSelect-icon": {
            display: "block",
            color: "#3554D1",
          },
          "& fieldset": { border: "none" },
        }}
        MenuProps={{
          PaperProps: {
            sx: {
              mt: 1,
              boxShadow: "0px 8px 20px rgba(0, 0, 0, 0.1)",
              borderRadius: "8px",
              "& .MuiMenuItem-root": {
                padding: "10px 16px",
                display: "flex",
                alignItems: "center",
                borderRadius: "4px",
                margin: "2px 4px",
                transition: "all 0.2s ease-in-out",
                "&:hover": {
                  backgroundColor: "rgba(53, 84, 209, 0.08)",
                  transform: "translateY(-2px)",
                  boxShadow: "0px 4px 8px rgba(0, 0, 0, 0.05)",
                },
                "&.Mui-selected": {
                  backgroundColor: "rgba(53, 84, 209, 0.12)",
                  "&:hover": {
                    backgroundColor: "rgba(53, 84, 209, 0.16)",
                  },
                },
              },
            },
          },
        }}
      >
        {hours.map((hourOption) => (
          <MenuItem key={hourOption} value={hourOption}>
            <TimerIcon sx={{ color: "#3554D1", mr: 1.5, fontSize: "18px" }} />
            <span style={{ fontWeight: 500 }}>
              {`${hourOption} Hour${hourOption > 1 ? "s" : ""}`}
            </span>
          </MenuItem>
        ))}
      </Select>
    </FormControl>
  );
};

export default HourPackage;
