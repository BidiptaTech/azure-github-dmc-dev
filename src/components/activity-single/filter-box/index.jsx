import React, { useState, useEffect, useCallback } from "react";
import GuestSearch from "./GuestSearch";
import { Link, Navigate } from "react-router-dom";
import HourPackage from "./HourlyPackage";
import Pickuptime from "./Pickuptime";
import { useSelector, useDispatch } from "react-redux";
import {
  setentrypickup,
  setentrydropoff,
  //setexitpickup,
  //setexitdropoff,
  setpickupdate,
  //setexitpickupdate,
  setentrytime,
  //setexittime,
  //setentrytime1,
  sethour,
  setadult,
  //setlanguagetype1,
  setchildren,
  //settravellers1,
  guideslice,
  settourId,
  setTotalPrice,
  setData,
  setbookingType,
  setbookingImage,
} from "@/slice/tourguide/guideslice";
import { useLocation, useNavigate } from "react-router-dom";
import { ToastContainer, toast } from "react-toastify";
import {
  Typography,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Button,
} from "@mui/material";
import dayjs from "dayjs";
import { setDateService } from "@/slice/common/dateServicesSlice";

const Index = () => {
  const pickUpLocation = useSelector((state) => state.tourguide.entrypickup);
 

  //const dropOffLocation = useSelector((state) => state.tourguide.entrydropoff);
  const selectedDate = useSelector((state) => state.tourguide.pickupdate);
  const PickupPlaceid = useSelector((state) => state.tourguide.PickupPlaceid);
  const DropoffPlaceid = useSelector((state) => state.tourguide.DropoffPlaceid);
  const country = useSelector((state) => state.hotels.tourdetails.destination);
  const statemode = useSelector((state) => state.tourguide.mode);
  const dispatch = useDispatch();
  const location = useLocation();
  const navigate = useNavigate();
  const { guide } = location.state;
 
  const id = useSelector((state) => state.hotels.id);

  // Get default values for guests from Redux store
  // Fetch values from Redux
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
 

  const adultsMax = tourDetails?.adult ?? 1; // Use optional chaining with fallback
 

  const childrenMax = tourDetails?.child ?? 0; // Use optional chaining with fallback
 

  const mode = statemode[guide.guide.guide_id]?.mode || "default_mode"; // Set a default mode if not found
 

  const [adults, setAdults] = useState(adultsMax);
  const [children, setChildren] = useState(childrenMax);
  //const [isModalOpen, setIsModalOpen] = useState(false);
  const globalid =
    guide.guide.prices.dmc_id || guide.guide.prices.travclicks_id;

  const [mappedData, setMappedData] = useState({
    pickUpLocation: "",
    //dropOffLocation: "",
    selectedDate: "",
    entryytime: "",
    hour: { label: "", price: 0 },
    adultsMax: "",
    childrenMax: "",
  });
 

  const [hour, sethours] = useState("");
  const [hourlyPrice, setHourlyPrice] = useState(0); // Stores selected hourly price
  const [entryytime, setentryytime] = useState("");
  const [isNight, setIsNight] = useState(false);

  // Function to update hour and price from HourPackage
  const handleHourChange = useCallback((selectedHour, price) => {
    sethours(selectedHour);
    setHourlyPrice(price);
    // setMappedData((prevData) => ({
    //   ...prevData,
    //   hour: { label: selectedHour, price: price },
    // }));
  }, []);

  const handleGuestChange = useCallback((updatedAdults, updatedChildren) => {
    setAdults(updatedAdults);
    setChildren(updatedChildren);
    // setMappedData((prevData) => ({
    //   ...prevData,
    //   adultsMax: updatedAdults,
    //   childrenMax: updatedChildren,
    // }));
  }, []);

  const calculateTotalBill = () => {
    if (!entryytime || !hour || !guide)
      return { totalPrice: "0.00", surcharge: "0.00", basePrice: "0.00" };

    const convertToDate = (timeStr) => {
      const [time, period] = timeStr.split(" ");
      let [hours, minutes] = time.split(":").map(Number);
      if (period === "PM" && hours !== 12) hours += 12;
      if (period === "AM" && hours === 12) hours = 0;
      const date = new Date(2000, 0, 1, hours, minutes);
      return date;
    };

    // Convert entry time and night time boundaries to Date objects
    const entryTimeDate = convertToDate(entryytime);
    const nightStartDate = convertToDate(guide.guide.night_start_time);
    const nightEndDate = convertToDate(guide.guide.night_end_time);

    // If night end is before night start, adjust night end to next day
    if (nightEndDate < nightStartDate) {
      nightEndDate.setDate(nightEndDate.getDate() + 1);
    }

    // Get number of hours from the selected package
    //const selectedHours = parseInt(hour.split(" ")[0]);

    // Calculate end time by adding selected hours to entry time
    const endTimeDate = new Date(entryTimeDate);
    endTimeDate.setHours(endTimeDate.getHours() + hour);

    let nightHours = 0;
    let currentTime = new Date(entryTimeDate);

    // Check each hour in the duration
    while (currentTime < endTimeDate) {
      // Create copies for comparison that are on the same day
      const compareTime = new Date(currentTime);
      const compareNightStart = new Date(nightStartDate);
      const compareNightEnd = new Date(nightEndDate);

      // Adjust dates to match the current day being checked
      compareNightStart.setFullYear(compareTime.getFullYear());
      compareNightStart.setMonth(compareTime.getMonth());
      compareNightStart.setDate(compareTime.getDate());

      compareNightEnd.setFullYear(compareTime.getFullYear());
      compareNightEnd.setMonth(compareTime.getMonth());
      compareNightEnd.setDate(compareTime.getDate());

      // If night end is before night start, it means it's crossing midnight
      if (compareNightEnd < compareNightStart) {
        if (compareTime >= compareNightStart || compareTime < compareNightEnd) {
          nightHours++;
        }
      } else {
        // Regular night time check
        if (compareTime >= compareNightStart && compareTime < compareNightEnd) {
          nightHours++;
        }
      }

      // Move to next hour
      currentTime.setHours(currentTime.getHours() + 1);
    }

    console.log(`Total night hours: ${nightHours}`);

    let basePrice = parseFloat(hourlyPrice);
    let surcharge =
      nightHours *
      (parseFloat(guide.guide.prices.dmc_night_surcharge) ||
        parseFloat(guide.guide.prices.travclicks_night_surcharge));

    const totalPrice = basePrice + surcharge;

    return {
      totalPrice: totalPrice.toFixed(2),
      surcharge: surcharge.toFixed(2),
      basePrice: basePrice.toFixed(2),
      nightHours: nightHours, // Added for debugging/display purposes
    };
  };
  useEffect(() => {
    if (entryytime && hour && guide) {
      const { nightHours } = calculateTotalBill();
      setIsNight(nightHours > 0);
      console.log(
        `Parameters changed. Night hours: ${nightHours}. isNight set to: ${
          nightHours > 0
        }`
      );
    }
  }, [entryytime, hour, guide]);

  // Function to check if there's a booking conflict
  const checkBookingConflict = () => {
    if (!entryytime || !hour || !guide?.bookingDetails) {
      return false; // No conflict if data is missing
    }

    // Convert time in "8:00 AM" format to decimal hours (8.0)
    const convertTo24Hour = (time12h) => {
      if (!time12h) return 0;

      const [timePart, period] = time12h.split(" ");
      let [hours, minutes] = timePart.split(":").map(Number);

      // If only hour is provided (like "8 AM"), add :00
      if (isNaN(minutes)) {
        minutes = 0;
      }

      if (period === "PM" && hours !== 12) {
        hours += 12;
      } else if (period === "AM" && hours === 12) {
        hours = 0;
      }

      return hours + minutes / 60; // Return as decimal hours
    };

    // Get start time of current selection
    const startTimeDecimal = convertTo24Hour(entryytime);
    // Get end time of current selection (user selected hours)
    const endTimeDecimal = startTimeDecimal + parseInt(hour);

    console.log(
      `Current selection: ${entryytime} to ${endTimeDecimal} (${hour} hours)`
    );

    // Check each booking for conflicts
    const userDate = dayjs(selectedDate).format("YYYY-MM-DD");
    const conflicts = guide.bookingDetails.some((booking) => {
      // Only check bookings on the same date
      if (booking.date !== userDate) {
        return false;
      }

      const bookingStartDecimal = convertTo24Hour(booking.start_time);
      const bookingEndDecimal = convertTo24Hour(booking.end_time);

      console.log(
        `Checking against booking: ${booking.start_time} (${bookingStartDecimal}) to ${booking.end_time} (${bookingEndDecimal})`
      );

      // Check for overlap
      // Conflict if: start time falls within existing booking OR end time falls within existing booking
      // OR if new booking completely contains existing booking
      const hasOverlap =
        (startTimeDecimal >= bookingStartDecimal &&
          startTimeDecimal < bookingEndDecimal) ||
        (endTimeDecimal > bookingStartDecimal &&
          endTimeDecimal <= bookingEndDecimal) ||
        (startTimeDecimal <= bookingStartDecimal &&
          endTimeDecimal >= bookingEndDecimal);

      if (hasOverlap) {
        console.log(
          `Conflict found: ${booking.start_time}-${booking.end_time} conflicts with selected ${entryytime}-${hour} hours`
        );
      }

      return hasOverlap;
    });

    return conflicts;
  };

  const handlesubmit0 = () => {
    if (
      !pickUpLocation ||
      // !dropOffLocation ||
      !selectedDate ||
      !entryytime ||
      adults === null ||
      adults === undefined ||
      children === null ||
      children === undefined ||
      !hour
    ) {
      toast.error("Please Properly fill and check your input.", {
        position: "top-center",
        autoClose: 3000,
      });
      return;
    }

    // Check for booking conflicts
    if (checkBookingConflict()) {
      toast.error(
        "Choosen Time and Hourly Package Conflicting with other Bookings of This Guide..",
        {
          position: "top-center",
          autoClose: 5000,
        }
      );
      return;
    }

    const selectedHourData = {
      label: hour,
      price: hourlyPrice,
    };
    console.log("hourly pricedh", selectedHourData);
    const { basePrice, surcharge, totalPrice } = calculateTotalBill();
    // const data = {
    //   pickUpLocation,
    //   //dropOffLocation,
    //   selectedDate,
    //   entryytime,
    //   adultsMax: adults, // ✅ Only send current selection, not changing max
    //   childrenMax: children,
    //   hour: selectedHourData,
    // };
    dispatch(setentrypickup(pickUpLocation));
    // dispatch(setentrydropoff(dropOffLocation));
    dispatch(setentrytime(entryytime));
    dispatch(setpickupdate(selectedDate));
    dispatch(setadult(adults));
    dispatch(sethour(hour));
    dispatch(setchildren(children));
    dispatch(setTotalPrice(totalPrice));
    dispatch(settourId(id));
    dispatch(setbookingType("guide"));
    dispatch(setbookingImage(guide.guide.image));
    const details = {
      bookingDate: dayjs(selectedDate).format("YYYY-MM-DD"),
      guide_id: guide.guide.guide_id,
      guide_name: guide.guide.name,
      image: guide.guide.image,
      dmc_Id: globalid,
      Mode: mode,
      entrypickup: pickUpLocation,
      // entrydropoff: dropOffLocation,
      PickupPlaceid: PickupPlaceid,
      DropoffPlaceid: DropoffPlaceid,
      pickupdate: selectedDate,
      entrytime: entryytime,
      adults: adults,
      children: children,
      hours: hour,
      basePrice: basePrice,
      surcharge: surcharge,
      totalPrice: Math.ceil(totalPrice),
      Tax: guide.guide.tax_percentage,
      Night_Start_Time: guide.guide.night_start_time,
      Night_End_Time: guide.guide.night_end_time,
      city: pickUpLocation,
      country: country,
    };
    console.log("guide details", details);
    dispatch(setData(details));
    navigate(`/dashboard/db-dashboard/CheckOut`, {
      state: { guide: guide },
    });
   
  };

  
  return (
    <>
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4 -right js-form-dd js-calendar">
          <div>
            {/* <h4 className="text-15 fw-500 ls-2 lh-16">Pick Up Time </h4> */}
            <Pickuptime entryytime={entryytime} setentryytime={setentryytime} />
          </div>
        </div>
      </div>                             

      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4 -right js-form-dd js-calendar">
          <div>
            <h4 className="text-15 fw-500 ls-2 lh-16">Hourly Package</h4>
            <HourPackage
              hour={hour}
              sethours={sethours}
              setHourlyPrice={setHourlyPrice}
              onHourChange={handleHourChange}
              entryytime={entryytime}
            />
          </div>
        </div>
      </div>

      <div className="col-12">
        {/* Pass adultCount and childrenCount as props */}
        <GuestSearch
          adultCount={adults} // ✅ Pass current selection, not max
          childrenCount={children}
          adultsMax={adultsMax} // ✅ Pass fixed max values
          childrenMax={childrenMax}
          onGuestChange={handleGuestChange}
        />
      </div>

      <div className="col-12">
        <button
          onClick={handlesubmit0}
          className="button -dark-1 py-15 px-35 h-60 col-12 rounded-4 bg-blue-1 text-white"
        >
          Check Out
        </button>
        {isNight && (
          <div className="text-14 mt-10" style={{ color: "#E53935" }}>
            *Dynamic Night Surcharge is added with the Price Based on Choosen
            Hourly Package
          </div>
        )}
      </div>

      {/* <Dialog
        open={isModalOpen}
        onClose={handleModalClose}
        maxWidth="sm"
        fullWidth
      >
        <DialogTitle className="tabs -underline-2 js-tabs custom-modal">
          Booking Details
        </DialogTitle>

        <DialogContent className="tabs__content pt-30 js-tabs-content modal-content">
          <Typography sx={{ fontWeight: "bold", color: "#333", mb: 1 }}>
            Guide Name: {guide?.guide.name || "No guide selected"}
          </Typography>

          <table className="table-3 -border-bottom col-12">
            <thead className="bg-light-2">
              <tr>
                <th>Field</th>
                <th>Details</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Pick-up Location</td>
                <td>{mappedData.pickUpLocation || "Not selected"}</td>
              </tr>
              
              <tr>
                <td>Selected Date</td>
                <td>{mappedData.selectedDate || "Not selected"}</td>
              </tr>
              <tr>
                <td>Pick-up Time</td>
                <td>{mappedData.entryytime || "Not selected"}</td>
              </tr>
              <tr>
                <td>Hourly Package</td>
                <td>
                  {mappedData.hour?.label
                    ? `${mappedData.hour.label} - ${mappedData.hour.price} USD`
                    : "Not selected"}
                </td>
              </tr>
              <tr>
                <td>Adults</td>
                <td>{mappedData.adultsMax ?? "0"}</td>
              </tr>
              <tr>
                <td>Children</td>
                <td>{mappedData.childrenMax ?? "0"}</td>
              </tr>
            </tbody>
          </table>

         
          <Typography sx={{ fontWeight: "bold", color: "#333", mb: 1 }}>
            Bill Breakdown
          </Typography>

          {(() => {
            const { basePrice, surcharge, totalPrice } = calculateTotalBill();
            return (
              <>
                <Typography sx={{ color: "#333" }}>
                  Base Price: {basePrice} USD
                </Typography>
                <Typography sx={{ color: "#333" }}>
                  Surcharge: {surcharge} USD
                </Typography>
                <Typography
                  sx={{
                    fontWeight: "bold",
                    color: "#333",
                    mt: 1,
                  }}
                >
                  Total Bill Price: {totalPrice} USD
                </Typography>
              </>
            );
          })()}
        </DialogContent>

        <DialogActions className="modal-footer">
          <Button
            className="button bg-light-2 px-10 py-4"
            onClick={handleModalClose}
          >
            Cancel
          </Button>
          <Button
            className="button bg-blue-1 px-10 py-4 text-white"
            onClick={handleFinalSubmit}
          >
            Confirm
          </Button>
        </DialogActions>
      </Dialog> */}

      <ToastContainer />
    </>
  );
};

export default Index;
