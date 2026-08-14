import React, { useState, useEffect } from "react";
import GuestSearch from "./GuestSearch";
import PriceMode from "./PriceMode3";
//import { Link } from "react-router-dom";
import HourPackage from "./HourlyPackage";
import { useSelector, useDispatch } from "react-redux";
import {
  setexitpickup,
  setpickdate,
  setentrytime1,
  setadult,
  setchildren,
  settourId,
  sethourlydata,
  Localtourslice,
  sethour,
} from "@/slice/localtour/Localslice";
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
import Pickuptime1 from "./Pickuptime1";
import { setbookingImage, setbookingType } from "@/slice/tourguide/guideslice";

const Index4 = () => {
  const dispatch = useDispatch();
  const location = useLocation();
  // const mode = useSelector((state) => state.localtour.mode);
  const { vehicles } = location.state;
  // const dayprice = vehicles.prices.dmcDayPrice || vehicles.prices.travClicksDay;
  // const nightprice =
  //   vehicles.prices.dmcNightPrice || vehicles.prices.travClicksNight;

  const exitpickUpLocation = useSelector((state) => state.localtour.exitpickup);
  const exitselectedDate = useSelector((state) => state.localtour.pickdate);
  const PickupPlaceid = useSelector((state) => state.localtour.PickupPlaceid);
  const id = useSelector((state) => state.hotels.id);
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);

  const adultsMax = tourDetails?.adult ?? 1;
  const childrenMax = tourDetails?.child ?? 0;

  const [adults, setAdults] = useState(adultsMax);
  const [children, setChildren] = useState(childrenMax);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const seatingCapacity = vehicles.seating_capacity;
  const [isBookNowEnabled, setIsBookNowEnabled] = useState(false);
  const [isNight, setIsNight] = useState(false);

  const [mappedData, setMappedData] = useState({
    pickUpLocation: "",
    selectedDate: "",
    entryytime: "",
    // hour: { label: "", price: 0 },
    adultsMax: "",
    childrenMax: "",
    price: "",
  });
  useEffect(() => {
    console.log(mappedData); // Log to check if the "hour" field is updated properly
  }, [mappedData]);

  const statemode = useSelector((state) => state.localtour.mode);
  const mode = statemode[vehicles.id]?.mode || "default_mode"; // Set a default mode if not found
  const navigate = useNavigate();

  // State for selected hour and total price
  const [selectedHours, setSelectedHours] = useState(1); // Default to 1 (valid option)
  const [totalHourlyPrice, setTotalHourlyPrice] = useState(0);
  const totalGuests = adults + children; // Calculate total guests
  // const convertTo24HourDate = (timeStr) => {
  //   const [time, period] = timeStr.split(" ");
  //   let [hours, minutes] = time.split(":").map(Number);

  //   if (period === "PM" && hours !== 12) hours += 12;
  //   if (period === "AM" && hours === 12) hours = 0;

  //   return new Date(2000, 0, 1, hours, minutes); // Fixed Date
  // };
  // const convertTimeToDate = (timeStr) => {
  //   if (!timeStr) {
  //     console.error("Invalid time string:", timeStr);
  //     return null; // Or handle the case appropriately
  //   }

  //   const [hours, minutes, seconds] = timeStr.split(":").map(Number);
  //   return new Date(2000, 0, 1, hours, minutes);
  // };

  // console.log("nightStartTime:", nightStartTime);
  // console.log("nightEndTime:", nightEndTime);
  useEffect(() => {
    setIsBookNowEnabled(totalGuests <= seatingCapacity); // Update the button state
  }, [adults, children, seatingCapacity]); // Dependencies

  // const calculateTotalBill = () => {
  //   if (!entryytime) return { Price: "0.00" };

  //   const entryTimeDate = convertTo24HourDate(entryytime);
  //   console.log("entrytimed", entryTimeDate);

  //   const nightStartDate = convertTimeToDate(nightStartTime);
  //   console.log("timed", nightStartDate);

  //   const nightEndDate = convertTimeToDate(nightEndTime);
  //   console.log("timed1", nightEndDate);

  //   let Price = parseFloat(dayprice); // Default to day price

  //   // ✅ If night period crosses midnight
  //   if (nightStartDate > nightEndDate) {
  //     if (entryTimeDate >= nightStartDate || entryTimeDate < nightEndDate) {
  //       Price = parseFloat(nightprice); // ✅ Use night price
  //     }
  //   }
  //   // ✅ If night period is within the same day
  //   else if (entryTimeDate >= nightStartDate && entryTimeDate <= nightEndDate) {
  //     Price = parseFloat(nightprice);
  //   }

  //   return {
  //     Price: Price.toFixed(2),
  //   };
  // };

  //
  const entryytime = useSelector((state) => state.localtour.entrytime1);
  console.log("entryytime", entryytime);
  const [pricemode, setpricemode] = useState("Sharable");
  // const Price =
  //   pricemode === "Sharable"
  //     ? vehicles.prices.day_sharable_price
  //     : vehicles.prices.day_private_price;
  // console.log("Price pricemode", Price);
  // const nightStartTime = vehicles.night_start_time;
  // const nightEndTime = vehicles.night_end_time;
  // const nightSharablePrice = vehicles.prices.night_sharable_price;
  // const nightPrivatePrice = vehicles.prices.night_private_price;

  /**
   * Calculate the total price based on selected hours, price mode, and time periods (day/night)
   * @returns {number} - The calculated total price
   */
  const calculateHourlyPrice = (hours = selectedHours) => {
    // Use the hours parameter instead of selectedHours
    let totalPrice = 0;
    let currentTime = entryytime; // Using existing entryytime variable in format "11:00 AM"
    let hasNightHours = false; // Track if any night hours are found

    // Set base prices based on price mode
    const dayPrice =
      pricemode === "Sharable"
        ? vehicles.prices.day_sharable_price * totalGuests
        : vehicles.prices.day_private_price;
    const daybaseprice =
      pricemode === "Sharable"
        ? vehicles.prices.sharable_day_base_price
        : vehicles.prices.private_day_base_price;

    const nightPrice =
      pricemode === "Sharable"
        ? vehicles.prices.night_sharable_price * totalGuests
        : vehicles.prices.night_private_price;
    const nightbaseprice =
      pricemode === "Sharable"
        ? vehicles.prices.sharable_night_base_price
        : vehicles.prices.private_night_base_price;

    // Parse night start and end times - format "20:00:00", "05:00:00"
    const startNightTime = vehicles.night_start_time
      .split(":")
      .slice(0, 2)
      .join(":");
    const endNightTime = vehicles.night_end_time
      .split(":")
      .slice(0, 2)
      .join(":");

    console.log("startNightTime", startNightTime);
    console.log("endNightTime", endNightTime);
    console.log("Using hours value:", hours); // Log the hours being used

    // Helper function to convert 12-hour format to 24-hour format
    const convertTo24Hour = (time12h) => {
      const [time, period] = time12h.split(" ");
      let [hours, minutes] = time.split(":").map(Number);

      if (period === "PM" && hours !== 12) {
        hours += 12;
      } else if (period === "AM" && hours === 12) {
        hours = 0;
      }

      return `${hours.toString().padStart(2, "0")}:${minutes
        .toString()
        .padStart(2, "0")}`;
    };

    // Convert initial time to 24-hour format
    let currentTime24h = convertTo24Hour(currentTime);
    console.log("Initial time in 24h format:", currentTime24h);

    // Helper function to add hours to time (in 24-hour format)
    const addHour = (time24h) => {
      const [hours, minutes] = time24h.split(":").map(Number);

      // Add 1 hour
      let newHours = hours + 1;

      // Handle overflow (24-hour format)
      if (newHours >= 24) {
        newHours = newHours - 24;
      }

      return `${newHours.toString().padStart(2, "0")}:${minutes
        .toString()
        .padStart(2, "0")}`;
    };

    // Helper function to check if a time is between night start and end
    const isNightTime = (time24h) => {
      // Handle case where night period crosses midnight
      if (startNightTime > endNightTime) {
        return time24h >= startNightTime || time24h < endNightTime;
      } else {
        return time24h >= startNightTime && time24h < endNightTime;
      }
    };

    console.log("dayPrice", dayPrice);
    console.log("daybaseprice", daybaseprice);

    // Loop through each hour and calculate price
    for (let i = 0; i < hours; i++) {
      // Check if current time is within night hours
      if (isNightTime(currentTime24h)) {
        totalPrice += parseFloat(nightPrice) + parseFloat(nightbaseprice);
        hasNightHours = true; // Mark that we found a night hour
        console.log(`Hour ${i + 1}: Night price applied - ${currentTime24h}`);
      } else {
        totalPrice += parseFloat(dayPrice) + parseFloat(daybaseprice);
        console.log(`Hour ${i + 1}: Day price applied - ${currentTime24h}`);
      }

      // Add 1 hour to current time for next iteration
      currentTime24h = addHour(currentTime24h);
    }

    // Set isNight based on whether any night hours were found
    setIsNight(hasNightHours);
    console.log("Night hours found:", hasNightHours);

    return totalPrice;
  };

  // Update the handleHourChange function to pass the new selectedHour value
  const handleHourChange = (selectedHour) => {
    setSelectedHours(selectedHour);

    // Calculate the price with the new selectedHour value explicitly
    const calculatedPrice = calculateHourlyPrice(selectedHour);

    // Update the price state
    setTotalHourlyPrice(calculatedPrice);
  };
  useEffect(() => {
    const calculatedPrice = calculateHourlyPrice(selectedHours);
    setTotalHourlyPrice(calculatedPrice);
  }, [selectedHours, pricemode, entryytime]);

  const handleGuestChange = (updatedAdults, updatedChildren) => {
    setAdults(updatedAdults);
    setChildren(updatedChildren);
  };

  const handlesubmit0 = () => {
    if (
      !exitpickUpLocation ||
      !exitselectedDate ||
      !entryytime ||
      adults === null ||
      adults === undefined ||
      children === null ||
      children === undefined
    ) {
      toast.error("Failed to create booking. Please check your input.", {
        position: "top-center",
        autoClose: 3000,
      });
      return;
    }

    dispatch(setexitpickup(exitpickUpLocation));
    //dispatch(setentrytime1(entryytime));
    dispatch(setpickdate(exitselectedDate));
    dispatch(sethour(selectedHours));
    dispatch(setadult(adults));
    dispatch(setchildren(children));
    dispatch(settourId(id));
    dispatch(setbookingType("travelhourly"));
    dispatch(setbookingImage(vehicles.image));

    const details = {
      bookingDate: dayjs(exitselectedDate).format("YYYY-MM-DD"),
      vehicles_id: vehicles.id,
      vehicles_name: vehicles.vehicle_name,
      image: vehicles.image,
      dmc_id: vehicles.prices.dmc_id,
      Mode: mode,
      type: pricemode,
      entrypickup: exitpickUpLocation,
      PickupPlaceid: PickupPlaceid,
      exitpickupdate: exitselectedDate,
      entrytime: entryytime,
      adults: adults,
      children: children,
      selectedHours,
      totalPrice: Math.ceil(totalHourlyPrice),
      Tax: vehicles.tax_percentage,
      Night_Start_Time: vehicles.night_start_time,
      Night_End_Time: vehicles.night_end_time,
      city: vehicles.city,
      country: vehicles.country,
    };

    dispatch(sethourlydata(details));
    navigate(`/dashboard/db-dashboard/CheckOut`, {
      state: { vehicles: vehicles },
    });

    // setMappedData(data); // Update state with final values
    // setIsModalOpen(true);
  };

  // const handleFinalSubmit = async () => {
  //   if (
  //     !exitpickUpLocation ||
  //     !exitselectedDate ||
  //     !entryytime ||
  //     adults === null ||
  //     adults === undefined ||
  //     children === null ||
  //     children === undefined
  //   ) {
  //     toast.error("Failed to create booking. Please check your input.", {
  //       position: "top-center",
  //       autoClose: 3000,
  //     });
  //     return;
  //   }

  //   dispatch(setexitpickup(exitpickUpLocation));
  //   dispatch(setentrytime1(entryytime));
  //   dispatch(setpickdate(exitselectedDate));
  //   dispatch(sethour(selectedHours));
  //   dispatch(setadult(adults));
  //   dispatch(setchildren(children));
  //   dispatch(settourId(id));

  //   const details = [
  //     {
  //       bookingDate: dayjs(exitselectedDate).format("YYYY-MM-DD"),
  //       vehicles_id: vehicles.id,
  //       vehicles_name: vehicles.vehicle_name,
  //       Mode: mode,
  //       entrypickup: exitpickUpLocation,
  //       PickupPlaceid: PickupPlaceid,
  //       exitpickupdate: exitselectedDate,
  //       entrytime: entryytime,
  //       adults: adults,
  //       children: children,
  //       selectedHours,
  //       totalHourlyPrice,
  //       Night_Start_Time: vehicles.night_start_time,
  //       Night_End_Time: vehicles.night_end_time,
  //       city: vehicles.city,
  //       country: vehicles.country,
  //     },
  //   ];

  //   dispatch(setdata1(details));

  //   try {
  //     const response = await dispatch(Localtourslice()).unwrap();
  //     if (response?.service?.date_service) {
  //       dispatch(setDateService(response.service.date_service));
  //       toast.success(response.message, {
  //         position: "top-center",
  //         autoClose: 3000,
  //       });
  //       handleModalClose();
  //     }
  //   } catch (error) {
  //     console.error("Error during submission:", error);
  //     toast.error("Something went wrong. Please try again later.", {
  //       position: "top-center",
  //       autoClose: 3000,
  //     });
  //   }
  // };

  // const handleModalClose = () => {
  //   setIsModalOpen(false);
  // };

  return (
    <>
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4">
          {/* <h4 className="text-15 fw-500 ls-2 lh-16">Pick Up Time </h4> */}
          <Pickuptime1 entryytime={entryytime} isStatic={true} />
        </div>
      </div>

      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4">
          <h4 className="text-15 fw-500 ls-2 lh-16">Hourly Package</h4>
          <HourPackage
            hour={selectedHours}
            sethours={setSelectedHours}
            onHourChange={handleHourChange}
          />
        </div>
      </div>
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4 -right js-form-dd js-calendar">
          <div>
            <h4 className="text-15 fw-500 ls-2 lh-16">Select Price Mode</h4>
            <PriceMode pricemode={pricemode} setpricemode={setpricemode} />
          </div>
        </div>
      </div>

      <div className="col-12">
        <GuestSearch
          adultCount={adults}
          childrenCount={children}
          adultsMax={adultsMax}
          childrenMax={childrenMax}
          onGuestChange={handleGuestChange}
        />
        {!isBookNowEnabled && (
          <div
            className="text-14 text-danger mt-5"
            style={{ color: "#E53935" }}
          >
            *Currently Total Guest: {adults + children} and Car Seating
            Capacity: {seatingCapacity}, Decrease the Guest Number to Book
          </div>
        )}
      </div>

      <div className="col-12">
        <button
          onClick={handlesubmit0}
          className={`button -dark-1 py-15 px-35 h-60 col-12 rounded-4 ${
            isBookNowEnabled ? "bg-blue-1 text-white" : "bg-gray-1 text-gray-3"
          }`}
          disabled={!isBookNowEnabled} // Disable button if not enabled
        >
          Check Out
        </button>
        {isNight && (
          <div className="text-14 mt-10" style={{ color: "#E53935" }}>
            *Dynamic Night Charges is added with the Price Based on{" "}
            {vehicles.vehicle_name}'s Night Start Time and Night End Time
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
            Vehicle Name: {vehicles.vehicle_name}
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
                <td>{exitpickUpLocation || "Not selected"}</td>
              </tr>
              <tr>
                <td>Selected Date</td>
                <td>
                  {exitselectedDate
                    ? exitselectedDate.split("-").reverse().join("-")
                    : "Not selected"}
                </td>
              </tr>
              <tr>
                <td>Pick-up Time</td>
                <td>{entryytime || "Not selected"}</td>
              </tr>
              <tr>
                <td>Hourly Package</td>
                <td>
                  {selectedHours ? `${selectedHours} Hours` : "Not selected"}
                </td>
              </tr>
              <tr>
                <td>Total Hourly Price</td>
                <td>{totalHourlyPrice.toFixed(2)} USD</td>
              </tr>
              <tr>
                <td>Adults</td>
                <td>{adults}</td>
              </tr>
              <tr>
                <td>Children</td>
                <td>{children}</td>
              </tr>
            </tbody>
          </table>
        </DialogContent>

        <DialogActions>
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

export default Index4;
