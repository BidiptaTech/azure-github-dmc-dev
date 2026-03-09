import React, { useState, useEffect } from "react";
import CustomerInfo from "./CustomerInfo";
import PaymentInfo from "./PaymentInfo";
//import OrderSubmittedInfo from "./OrderSubmittedInfo1";
import { useLocation, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import {
  setHotelBooking,
  hottelBookingDataSubmit,
} from "@/slice/hotel/hotelSlice";
import { setTotalPrice } from "@/slice/hotel/HotelDetailsSlice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { guideslice, setData } from "@/slice/tourguide/guideslice";
import {
  Localtourslice,
  setpointdata,
  sethourlydata,
} from "@/slice/localtour/Localslice";
import {
  submitPickupDrop,
  setentrydata,
  setexitdata,
  setResponse,
} from "@/slice/port/pickupDropSlice";
import { ToastContainer, toast } from "react-toastify";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";

const Index = () => {
  const location = useLocation();
  const guide = location.state?.guide;
  const vehicles = location.state?.vehicles;
  const type = useSelector((state) => state.tourguide.type);
  console.log("booking type", type);
  // const rooms = location1.state?.bookingArray;
  // const totalPrice = location1.state?.totalPrice;
  // const priceMode = location1.state?.priceMode;
  // console.log(priceMode, "priceMode");

  //const [responseData, setResponseData] = useState(null);
  const [formData, setFormData] = useState({});
  const [formValid, setFormValid] = useState(false);

  const handleClick = () => {
    if (type === "entryport" || type === "exitport") {
      navigate("/dashboard/db-dashboard/activity-single-1", {
        state: { vehicles: vehicles },
      });
    } else if (type === "travelpoint" || type === "travelhourly" || type === "travelpointzone") {
      navigate("/dashboard/db-dashboard/activity-single-2", {
        state: { vehicles: vehicles },
      });
    } else if (type === "guide") {
      navigate("/dashboard/db-dashboard/activity-single", {
        state: { guide: guide },
      });
    }
  };

  const handleFormChange = (data) => {
    setFormData(data);

    if (type === "guide") {
      dispatch(setData(data));
    } else if (type === "entryport") {
      dispatch(setentrydata(data));
    } else if (type === "exitport") {
      dispatch(setexitdata(data));
    } else if (type === "travelpoint") {
      dispatch(setpointdata(data));
    } else if (type === "travelhourly") {
      dispatch(sethourlydata(data));
    } else if (type === "zone") {
      dispatch(setpointdata(data));
    } else {
      console.error("Invalid booking type:", type);
    }
  };

  const navigate = useNavigate();
  const dispatch = useDispatch();

  //const hotelDetails = useSelector((state) => state.hoteldetails.details);

  const image = useSelector((state) => state.tourguide.image);
  console.log("booking image", image);

  const guideBookingDetails = useSelector((state) => state.tourguide.details);
  console.log("guidddee", guideBookingDetails);
  const entryportBookingDetails = useSelector(
    (state) => state.pickupDrop.details
  );
  console.log("entryportt", entryportBookingDetails);

  const exitportBookingDetails = useSelector(
    (state) => state.pickupDrop.details1
  );
  console.log("exitportt", exitportBookingDetails);
  const travelpointBookingDetails = useSelector(
    (state) => state.localtour.details
  );
  console.log("travelpointtdetailss", travelpointBookingDetails);

  const travelhourlyBookingDetails = useSelector(
    (state) => state.localtour.details1
  );
  console.log("travelhourlyydetailss", travelpointBookingDetails);
  const travelpointzoneBookingDetails = useSelector(
    (state) => state.localtour.details
  );
  console.log("travelpointzoneBookingDetails", travelpointzoneBookingDetails);
  //const tourDetails = useSelector((state) => state.hotels.tourdetails);
  const rawBookingDetails =
    type === "guide"
      ? guideBookingDetails
      : type === "entryport"
      ? entryportBookingDetails
      : type === "exitport"
      ? exitportBookingDetails
      : type === "travelpoint"
      ? travelpointBookingDetails
      : type === "travelhourly"
      ? travelhourlyBookingDetails
      : type === "travelpointzone"
      ? travelpointzoneBookingDetails
      : null;

  const bookingDetails = Array.isArray(rawBookingDetails)
    ? rawBookingDetails
    : rawBookingDetails
    ? [rawBookingDetails]
    : [];

  const enquiryAmount = bookingDetails[0]?.totalPrice;

  return (
    <>
      {/* <div className="header-margin">
        <CustomStepper />
        <TourStatus />
      </div> */}
      <section className="pt-20">
        <button
          className="button px-15 py-8 bg-blue-1 text-white rounded position-relative"
          style={{
            left: "-80px",
            // zIndex: "10",
            minHeight: "40px",
          }}
          onClick={handleClick}
        >
          ← Back To Details
        </button>
        <div className="row x-gap-40 items-center">
          <CustomerInfo
            image={image}
            bookingDetails={bookingDetails}
            onFormChange={handleFormChange}
            setFormValid={setFormValid}
          enquiryAmount={enquiryAmount}
            formData={formData}
            formValid={formValid}
            dispatch={dispatch}
            navigate={navigate}
            type={type}
          />
        </div>
      </section>
      <ToastContainer />
    </>
  );
};

export default Index;
