import React, { useState, useEffect } from "react";
import GuestSearch from "./GuestSearch";
// import { Link } from "react-router-dom";
// import HourPackage from "./HourlyPackage";
//import Pickuptime from "./Pickuptime";
import { useSelector, useDispatch } from "react-redux";
import {
  setexitpickup,
  setexitdropoff,
  //setexitpickup,
  //setexitdropoff,
  setexitpickupdate,
  //setexitpickupdate,
  setexittime,
  //setexittime,
  //setentrytime1,
  setadult,
  //setlanguagetype1,
  setchildren,
  //settravellers1,
  submitPickupDrop,
  settourId,
  setexitdata,
} from "@/slice/port/pickupDropSlice";
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
import PriceMode from "./PriceMode1";
import Pickuptime1 from "./Pickuptime1";
import { setbookingImage, setbookingType } from "@/slice/tourguide/guideslice";

const Index2 = () => {
  // const pickUpLocation = useSelector((state) => state.pickupDrop.entrypickup);
  // const dropOffLocation = useSelector((state) => state.pickupDrop.entrydropoff);
  const exitpickUpLocation = useSelector(
    (state) => state.pickupDrop.exitpickup
  );
  const exitdropOffLocation = useSelector(
    (state) => state.pickupDrop.exitdropoff
  );
  // const selectedDate = useSelector((state) => state.pickupDrop.pickupdate);
  const exitselectedDate = useSelector((state) => state.pickupDrop.pickupdate);
  const PickupPlaceid = useSelector((state) => state.pickupDrop.PickupPlaceid);
  const DropoffPlaceid = useSelector(
    (state) => state.pickupDrop.DropoffPlaceid
  );
  const entryytime = useSelector((state) => state.pickupDrop.exittime);

  const dispatch = useDispatch();
  const location = useLocation();
  const { vehicles } = location.state;
  const id = useSelector((state) => state.hotels.id);
  // const dayprice = vehicles.prices.dmcDayPrice || vehicles.prices.travClicksDay;
  // const nightprice =
  //   vehicles.prices.dmcNightPrice || vehicles.prices.travClicksNight;
  // const nightStartTime = vehicles.night_start_time;
  // const nightEndTime = vehicles.night_end_time;
  const statemode = useSelector((state) => state.pickupDrop.mode);
  const navigate = useNavigate();
  const seatingCapacity = vehicles.seating_capacity;
  const [isBookNowEnabled, setIsBookNowEnabled] = useState(false);
  const [isNight, setIsNight] = useState(false);
  // Get default values for guests from Redux store
  // Fetch values from Redux
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  console.log("Tour details:", tourDetails);

  const adultsMax = tourDetails?.adult ?? 1; // Use optional chaining with fallback
  console.log("Adults Max:", adultsMax);

  const childrenMax = tourDetails?.child ?? 0; // Use optional chaining with fallback
  console.log("Children Max:", childrenMax);

  const [adults, setAdults] = useState(adultsMax);
  const [children, setChildren] = useState(childrenMax);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const totalGuests = adults + children; // Calculate total guests

  const [mappedData, setMappedData] = useState({
    pickUpLocation: "",
    dropOffLocation: "",
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

  //const [hour, sethours] = useState("");
  //const [hourlyPrice, setHourlyPrice] = useState(0); // Stores selected hourly price
  //const [entryytime, setentryytime] = useState("");
  const mode = statemode[vehicles.id]?.mode || "default_mode"; // Set a default mode if not found
  // Function to update hour and price from HourPackage
  // const handleHourChange = (selectedHour, price) => {
  //   sethours(selectedHour);
  //   setHourlyPrice(price);
  //   setMappedData((prevData) => ({
  //     ...prevData,
  //     hour: { label: selectedHour, price: price },
  //   }));
  // };

  useEffect(() => {
    setIsBookNowEnabled(totalGuests <= seatingCapacity); // Update the button state
    if (entryytime && vehicles.night_start_time && vehicles.night_end_time) {
      console.log("Time check - Entry time:", entryytime);
      console.log("Night start time:", vehicles.night_start_time);
      console.log("Night end time:", vehicles.night_end_time);

      // Parse the time strings
      const [hourStr, period] = entryytime.split(" ");
      let entryHour = parseInt(hourStr);

      // Convert to 24-hour format
      if (period === "PM" && entryHour < 12) entryHour += 12;
      if (period === "AM" && entryHour === 12) entryHour = 0;

      // Parse night start and end times
      const nightStartHour = parseInt(vehicles.night_start_time.split(":")[0]);
      const nightEndHour = parseInt(vehicles.night_end_time.split(":")[0]);

      console.log("Converted entry hour:", entryHour);
      console.log("Night start hour:", nightStartHour);
      console.log("Night end hour:", nightEndHour);

      // Handle cases where night period crosses midnight
      if (nightStartHour > nightEndHour) {
        // Night period crosses midnight (e.g., 20:00 to 05:00)
        // Time is in night hours if it's after start time OR before end time
        setIsNight(entryHour >= nightStartHour || entryHour <= nightEndHour);
        console.log(
          "Night crosses midnight. Is night:",
          entryHour >= nightStartHour || entryHour <= nightEndHour
        );
      } else {
        // Night period is within same day (e.g., 18:00 to 23:00)
        // Time is in night hours if it's between start and end time
        setIsNight(entryHour >= nightStartHour && entryHour <= nightEndHour);
        console.log(
          "Night within same day. Is night:",
          entryHour >= nightStartHour && entryHour <= nightEndHour
        );
      }
    }
  }, [
    adults,
    children,
    seatingCapacity,
    entryytime,
    vehicles.night_start_time,
    vehicles.night_end_time,
  ]);

  const handleGuestChange = (updatedAdults, updatedChildren) => {
    setAdults(updatedAdults);
    setChildren(updatedChildren);
    // setMappedData((prevData) => ({
    //   ...prevData,
    //   adultsMax: updatedAdults,
    //   childrenMax: updatedChildren,
    // }));
  };

  // const convertTo24HourDate = (timeStr) => {
  //   const [time, period] = timeStr.split(" ");
  //   let [hours, minutes] = time.split(":").map(Number);

  //   if (period === "PM" && hours !== 12) hours += 12;
  //   if (period === "AM" && hours === 12) hours = 0;

  //   return new Date(2000, 0, 1, hours, minutes); // Fixed Date
  // };
  // const convertTimeToDate = (timeStr) => {
  //   const [hours, minutes, seconds] = timeStr.split(":").map(Number);
  //   return new Date(2000, 0, 1, hours, minutes);
  // };
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

  // const { Price } = calculateTotalBill();
  const [pricemode, setpricemode] = useState(""); // Set a default mode if not found
  console.log("pricemodeee", pricemode);
  const Price =
    pricemode === "Sharable"
      ? vehicles.prices.sharablePrice * totalGuests
      : vehicles.prices.privatePrice;
  console.log("Price pricemode", Price);

  const handlesubmit0 = () => {
    // const data = {
    //   exitpickUpLocation,
    //   exitdropOffLocation,
    //   exitselectedDate,
    //   entryytime,
    //   adultsMax: adults, // ✅ Only send current selection, not changing max
    //   childrenMax: children,
    //   price: Price,
    // };

    if (
      !exitpickUpLocation ||
      !exitdropOffLocation ||
      !exitselectedDate ||
      !entryytime ||
      adults === null ||
      adults === undefined ||
      children === null ||
      children === undefined
    ) {
      toast.error("Please Properly fill and check your input.", {
        position: "top-center",
        autoClose: 3000,
      });
      return;
    }
    dispatch(setexitpickup(exitpickUpLocation));
    dispatch(setexitdropoff(exitdropOffLocation));
    //dispatch(setexittime(entryytime));
    dispatch(setexitpickupdate(exitselectedDate));
    dispatch(setadult(adults));
    dispatch(setchildren(children));
    // dispatch(setTotalPrice(totalPrice));
    dispatch(settourId(id));
    dispatch(setbookingImage(vehicles.image));
    dispatch(setbookingType("exitport"));
    const details = {
      bookingDate: dayjs(exitselectedDate).format("YYYY-MM-DD"),
      vehicles_id: vehicles.id,
      vehicles_name: vehicles.vehicle_name,
      dmc_id: vehicles.prices.dmc_id,
      Mode: mode,
      type: pricemode,
      image: vehicles.image,
      exitpickup: exitpickUpLocation,
      exitdropoff: exitdropOffLocation,
      PickupPlaceid: PickupPlaceid,
      DropoffPlaceid: DropoffPlaceid,
      exitpickupdate: exitselectedDate,
      entrytime: entryytime,
      adults: Number(adults),
      children: Number(children),
      totalPrice: Math.ceil(Price),
      Tax: vehicles.tax_percentage,
      distance: vehicles.$distanceInKM,
      Night_Start_Time: vehicles.night_start_time,
      Night_End_Time: vehicles.night_end_time,
      city: vehicles.city,
      country: vehicles.country,
      seatingCapacity: seatingCapacity,
      // hours: hour,
      // totalPrice: totalPrice,
    };
    dispatch(setexitdata(details));
    navigate(`/dashboard/db-dashboard/CheckOut`, {
      state: { vehicles: vehicles },
    });
    // setMappedData(data); // Update state with final values
    // setIsModalOpen(true);
    // console.log("Submitted Data:", data); // Debugging: Check final values before submission
  };

  // const handleFinalSubmit = async () => {
  //   if (
  //     !exitpickUpLocation ||
  //     !exitdropOffLocation ||
  //     !exitselectedDate ||
  //     !entryytime ||
  //     adults === null ||
  //     adults === undefined ||
  //     children === null ||
  //     children === undefined
  //   ) {
  //     toast.error("Please Properly fill and check your input.", {
  //       position: "top-center",
  //       autoClose: 3000,
  //     });
  //     return;
  //   }

  //   // Get calculated bill values
  //   // const { totalPrice } = calculateTotalBill();

  //   dispatch(setexitpickup(exitpickUpLocation));
  //   dispatch(setexitdropoff(exitdropOffLocation));
  //   dispatch(setexittime(entryytime));
  //   dispatch(setexitpickupdate(exitselectedDate));
  //   dispatch(setadult(adults));
  //   dispatch(setchildren(children));
  //   // dispatch(setTotalPrice(totalPrice));
  //   dispatch(settourId(id));

  //   // Determine price based on mode
  //   //const selectedPrice = mode === "dmc" ? dmcprice : travclickprice;

  //   const details = [
  //     {
  //       bookingDate: dayjs(exitselectedDate).format("YYYY-MM-DD"),
  //       vehicles_id: vehicles.id,
  //       vehicles_name: vehicles.vehicle_name,
  //       Mode: mode,
  //       exitpickup: exitpickUpLocation,
  //       exitdropoff: exitdropOffLocation,
  //       PickupPlaceid: PickupPlaceid,
  //       DropoffPlaceid: DropoffPlaceid,
  //       exitpickupdate: exitselectedDate,
  //       exittime: entryytime,
  //       adults: adults,
  //       children: children,
  //       price: Price,
  //       distance: vehicles.$distanceInKM,
  //       Night_Start_Time: vehicles.night_start_time,
  //       Night_End_Time: vehicles.night_end_time,
  //       city: vehicles.city,
  //       country: vehicles.country,
  //       // hours: hour,
  //       // totalPrice: totalPrice,
  //     },
  //   ];

  //   dispatch(setdata1(details));

  // try {
  //   const response = await dispatch(
  //     submitPickupDrop({ selectedType: "exit" })
  //   ).unwrap();
  //   if (response?.service?.date_service) {
  //     dispatch(setDateService(response?.service?.date_service));
  //     toast.success(response.message, {
  //       position: "top-center",
  //       autoClose: 3000,
  //     });
  //     handleModalClose();
  //   }
  // } catch (error) {
  //   console.error("Error during submission:", error);
  //   toast.error("Something went wrong. Please try again later.", {
  //     position: "top-center",
  //     autoClose: 3000,
  //   });
  // }
  // };

  // const handleModalClose = () => {
  //   setIsModalOpen(false);
  //   //setIsModalOpen1(false);
  // };

  return (
    <>
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4 -right js-form-dd js-calendar">
          <div>
            {/* <h4 className="text-15 fw-500 ls-2 lh-16">Pick Up Time </h4> */}
            <Pickuptime1 entryytime={entryytime} isStatic={true} />
          </div>
        </div>
      </div>

      {/* <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4 -right js-form-dd js-calendar">
          <div>
            <h4 className="text-15 fw-500 ls-2 lh-16">Hourly Package</h4>
            <HourPackage
              hour={hour}
              sethours={sethours}
              setHourlyPrice={setHourlyPrice} // Pass function to update price
              onHourChange={handleHourChange} // Pass function to handle hour change
            />
          </div>
        </div>
      </div> */}
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4 -right js-form-dd js-calendar">
          <div>
            <h4 className="text-15 fw-500 ls-2 lh-16">Select Price Mode</h4>
            <PriceMode pricemode={pricemode} setpricemode={setpricemode} />
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
            Vehicle Name:{vehicles.vehicle_name}
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
                <td>Drop-off Location</td>
                <td>{exitdropOffLocation || "Not selected"}</td>
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
              </tr> */}
      {/* <tr>
                <td>Hourly Package</td>
                <td>
                  {hour.label
                    ? `${hour.label} - ${hour.price} USD`
                    : "Not selected"}
                </td>
              </tr> */}
      {/* <tr>
                <td>Adults</td>
                <td>{mappedData.adultsMax}</td>
              </tr>
              <tr>
                <td>Children</td>
                <td>{mappedData.childrenMax}</td>
              </tr>
              <tr>
                <td>Price</td>
                <td>{mappedData.price}</td>
              </tr>
            </tbody>
          </table> */}

      {/* Bill Breakdown Section */}
      {/* <Typography sx={{ fontWeight: "bold", color: "#333", mb: 1 }}>
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
          })()} */}
      {/* </DialogContent>

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

export default Index2;
