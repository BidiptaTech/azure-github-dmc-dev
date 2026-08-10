import React, { useState, useEffect } from "react";
import GuestSearch from "./GuestSearch";
import { Link } from "react-router-dom";
import HourPackage from "./HourlyPackage";
import Pickuptime from "./Pickuptime";
import PriceMode from "./PriceMode";
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
  setadult,
  //setlanguagetype1,
  setchildren,
  //settravellers1,
  submitPickupDrop,
  settourId,
  //setTotalPrice,
  setentrydata,
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
import { setbookingImage, setbookingType } from "@/slice/tourguide/guideslice";

const Index1Zone = () => {
  const pickUpLocation = useSelector((state) => state.pickupDrop.entrypickup);
  const dropOffLocation = useSelector((state) => state.pickupDrop.entrydropoff);
  const statemode = useSelector((state) => state.pickupDrop.mode);
  const entrytime = useSelector((state) => state.pickupDrop.entrytime);
  const selectedDate = useSelector((state) => state.pickupDrop.pickupdate);

  const PickupPlaceid = useSelector((state) => state.pickupDrop.PickupPlaceid);
  const DropoffPlaceid = useSelector(
    (state) => state.pickupDrop.DropoffPlaceid
  );
  const dispatch = useDispatch();
  const location = useLocation();
  const { vehicles } = location.state;
  const seatingCapacity = vehicles.seating_capacity;
 
  const navigate = useNavigate();

  const id = useSelector((state) => state.hotels.id);
  // const dayprice = vehicles.prices.DayPrice;
  // const nightprice = vehicles.prices.NightPrice;
  // const nightStartTime = vehicles.night_start_time;
  // const nightEndTime = vehicles.night_end_time;

  // Get default values for guests from Redux store
  // Fetch values from Redux
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  

  const adultsMax = tourDetails?.adult ?? 1; // Use optional chaining with fallback
  

  const childrenMax = tourDetails?.child ?? 0; // Use optional chaining with fallback
  

  const [adults, setAdults] = useState(adultsMax);
  const [children, setChildren] = useState(childrenMax);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isBookNowEnabled, setIsBookNowEnabled] = useState(false);

  

  //const [pricemode, setpricemode] = useState(""); // Set a default mode if not found

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
  

  // const [hour, sethours] = useState("");
  // const [hourlyPrice, setHourlyPrice] = useState(0); // Stores selected hourly price
  //const [entryytime, setentryytime] = useState("");
  const mode = statemode[vehicles.id]?.mode || "default_mode"; // Set a default mode if not found
  
  const totalGuests = adults + children; // Calculate total guests
  // Function to update hour and price from HourPackage
  // const handleHourChange = (selectedHour, price) => {
  //   sethours(selectedHour);
  //   setHourlyPrice(price);
  //   setMappedData((prevData) => ({
  //     ...prevData,
  //     hour: { label: selectedHour, price: price },
  //   }));
  // };

  // useEffect to update isBookNowEnabled based on total guests
  useEffect(() => {
    setIsBookNowEnabled(totalGuests <= seatingCapacity); // Update the button state

    
  }, [
    totalGuests,
    seatingCapacity
  ]);

  // Update handleGuestChange to check seating capacity
  const handleGuestChange = (updatedAdults, updatedChildren) => {
    setAdults(updatedAdults);
    setChildren(updatedChildren);
    // The useEffect will handle the button state update
  };

  
  const [pricemode, setpricemode] = useState(""); // Set a default mode if not found
  
  const Price =
    pricemode === "Sharable" ? vehicles.shared_price * totalGuests
     : vehicles.private_price;
 
  const handlesubmit0 = () => {
    // const data = {
    //   pickUpLocation,
    //   dropOffLocation,
    //   selectedDate,
    //   entryytime,
    //   adultsMax: adults,
    //   childrenMax: children,
    //   price: Price, // ✅ Add price based on mode
    // };
    if (
      !pickUpLocation?.trim() ||
      !dropOffLocation?.trim() ||
      !selectedDate ||
      !entrytime ||
      adults === null ||
      adults === undefined ||
      children === null ||
      children === undefined
    ) {
      toast.error(" Please Properly fill and check your input.");
      return;
    }
    //dispatch(setentrydata([]));
    dispatch(setentrypickup(pickUpLocation));
    dispatch(setentrydropoff(dropOffLocation));
    //dispatch(setentrytime(entryytime));
    dispatch(setpickupdate(selectedDate));
    dispatch(setadult(adults));
    dispatch(setchildren(children));
    dispatch(settourId(id));
    dispatch(setbookingType("entryport"));
    dispatch(setbookingImage(vehicles.image));

    const details = {
      bookingDate: dayjs(selectedDate).format("YYYY-MM-DD"),
      vehicles_id: vehicles.id,
      image: vehicles.image,
      dmc_id: vehicles.dmc_id,
      vehicles_name: vehicles.vehicle_name,
      Mode: mode,
      type: pricemode,
      entrypickup: pickUpLocation,
      entrydropoff: dropOffLocation,
      PickupPlaceid: PickupPlaceid,
      DropoffPlaceid: DropoffPlaceid,
      pickupdate: selectedDate,
      entrytime: entrytime,
      adults: Number(adults),
      children: Number(children),
      totalPrice: Math.ceil(Price), // ✅ Add price dynamically
      Tax: vehicles.tax_percentage,
      city: vehicles.city,
      country: vehicles.country,
      to_zone_id: Number(vehicles.to_zone_id),
      from_zone_id: Number(vehicles.from_zone_id),
      seatingCapacity: Number(vehicles.seating_capacity),
    };

    dispatch(setentrydata(details));
    navigate(`/dashboard/db-dashboard/CheckOut`, {
      state: { vehicles: vehicles },
    });
    // setMappedData(data);
    // setIsModalOpen(true);
    // console.log("Submitted Data:", data);
  };

 
  return (
    <>
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4 -right js-form-dd js-calendar">
          <div>
            {/* <h4 className="text-15 fw-500 ls-2 lh-16">Pick Up Time </h4> */}
            <Pickuptime entryytime={entrytime} isStatic={true} />
          </div>
        </div>
      </div>

     
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4 -right js-form-dd js-calendar">
          <div>
            <h4 className="text-15 fw-500 ls-2 lh-16">Select Price Mode</h4>
            <PriceMode
              pricemode={pricemode}
              setpricemode={setpricemode} // Pass function to update mode
            />
          </div>
        </div>
      </div>

      <div className="col-12">
        {/* Pass adultCount and childrenCount as props */}
        <GuestSearch
          adultCount={adults}
          childrenCount={children}
          adultsMax={adultsMax}
          childrenMax={childrenMax}
          onGuestChange={handleGuestChange}
        />

        {/* Warning message for seating capacity */}
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
          disabled={!isBookNowEnabled}
        >
          Check Out
        </button>

        {/* Night charges warning message */}
        {/* {isNight && (
          <div className="text-14 mt-10" style={{ color: "#E53935" }}>
            *Dynamic Night Charges is added with the Price Based on{" "}
            {vehicles.vehicle_name}'s Night Start Time and Night End Time
          </div>
        )} */}
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
                <td>{pickUpLocation || "Not selected"}</td>
              </tr>
              <tr>
                <td>Drop-off Location</td>
                <td>{dropOffLocation || "Not selected"}</td>
              </tr>
              <tr>
                <td>Selected Date</td>
                <td>
                  {selectedDate
                    ? selectedDate.split("-").reverse().join("-")
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

export default Index1Zone;
