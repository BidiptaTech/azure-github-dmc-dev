import { useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { setPriceMode } from "@/slice/port/pickupDropSlice";
import { setPriceMode1 } from "@/slice/localtour/Localslice";
import { setPriceMode2 } from "@/slice/tourguide/guideslice";
import { Checkbox, FormControlLabel, FormControl, Avatar } from "@mui/material";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "@/slice/dmc/dmcSlice";

const PriceModeFilter = () => {
  const dispatch = useDispatch();
  const [isChecked, setIsChecked] = useState(false); // Local state for checkbox
  const DmcName = useSelector(selectSelectedDmcCompanyName);
  const DmcLogo = useSelector(selectSelectedDmcLogo);
  console.log("DmcName1", DmcName);
  console.log("DmcLogo1", DmcLogo);
  // Dispatch default value "non-checked" when component mounts
  useEffect(() => {
    dispatch(setPriceMode("non-checked"));
    dispatch(setPriceMode1("non-checked"));
    dispatch(setPriceMode2("non-checked"));
  }, [dispatch]);

  const handleChange = (event) => {
    const checked = event.target.checked;
    setIsChecked(checked); // Update local state

    // Dispatch "checked" or "non-checked" based on checkbox state
    dispatch(setPriceMode(checked ? "checked" : "non-checked"));
    dispatch(setPriceMode1(checked ? "checked" : "non-checked"));
    dispatch(setPriceMode2(checked ? "checked" : "non-checked"));
  };

  return (
    <FormControl style={{ display: "flex" }}>
      <div style={{ display: "flex", alignItems: "center" }}>
        <Checkbox checked={isChecked} onChange={handleChange} color="primary" />

        {DmcLogo && (
          <Avatar
            src={DmcLogo}
            alt={`${DmcName} Logo`}
            sx={{
              width: 24,
              height: 24,
              marginRight: "8px",
            }}
          />
        )}

        <FormControlLabel
          control={<span />} // Dummy control to keep structure valid
          label={`${DmcName}'s Mode`}
          style={{ marginLeft: 0 }} // Remove extra spacing
        />
      </div>
    </FormControl>
  );
};

export default PriceModeFilter;
