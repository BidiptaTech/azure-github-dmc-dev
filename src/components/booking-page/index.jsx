import React, { useState, useEffect, useRef } from "react";
import CustomerInfo from "./CustomerInfo";
import PaymentInfo from "./PaymentInfo";
import OrderSubmittedInfo from "./OrderSubmittedInfo";
import { useLocation, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import {
  setHotelBooking,
  hottelBookingDataSubmit,
  setHotelService,
} from "@/slice/hotel/hotelSlice";
import {
  setTotalPrice,
  clearHotelDetails,
  setHotelDetails,
} from "@/slice/hotel/HotelDetailsSlice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { ToastContainer, toast } from "react-toastify";
import dayjs from "dayjs";
import { setPriceMode } from "@/slice/hotel/CategorySlice";
//import { setHotelService } from "@/slice/common/BookingSlice";
import { setUserInfo, setBookingResponse } from "@/slice/common/customerInfo";
import { setBookingType, setIsNavigating } from "@/slice/common/commonSlice";

const Index = () => {
  const location1 = useLocation();
  const rooms = location1.state?.bookingArray;
  const totalPrice = location1.state?.totalPrice;
  //const priceMode = location1.state?.priceMode;
  const priceMode = useSelector((state) => state.category.priceMode);
  const priceModeId = useSelector((state) => state.category.priceModeId);
  // console.log(priceMode, "priceMode");
  console.log(priceModeId, "priceModeId");

  const [responseData, setResponseData] = useState(null);
  const [formData, setFormData] = useState({});
  // Add enquiry-related state
  // const [enquiryAmount, setEnquiryAmount] = useState(() => {
  //   return totalPrice || 0;
  // });
  // const [enquiryComment, setEnquiryComment] = useState("");

  console.log(responseData, "hotelData");

  const handleFormChange = (data) => {
    setFormData(data);
    console.log("Form data updated:", data);
  };

  // Add these handlers for enquiry data
  // const handleEnquiryAmountChange = (amount) => {
  //   setEnquiryAmount(amount);
  // };

  // const handleEnquiryCommentChange = (comment) => {
  //   setEnquiryComment(comment);
  // };

  const navigate = useNavigate();
  const dispatch = useDispatch();

  const hotelDetails = useSelector(
    (state) => state.hoteldetails.bookingDetails
  );

  console.log("Current hotel details in index.jsx:", hotelDetails);

  const tourDetails = useSelector((state) => state.hotels.tourdetails);
  console.log(tourDetails, "tourDetailss");

  const bookingDate = useSelector((state) => state.hotels.searchState);
  console.log(bookingDate, "boooking date");

  const customerInfoRef = useRef(null);

  const handleSubmit = () => {
    if (!customerInfoRef.current?.isFormValid()) {
      toast.error("Please fill all required fields correctly");
      return;
    }

    if (!formData || Object.keys(formData).length === 0) {
      toast.error("Customer information is missing");
      return;
    }
    dispatch(setIsNavigating(true));
    const payload = {
      ...formData,
      rooms: rooms || [],
      totalPrice: totalPrice || 0,
      bookingType: "booking",
      priceMode: priceMode || "",
      priceModeId: priceModeId || "",
      hotelDetails: {
        hotel_id: hotelDetails?.hotel_id || "",
        hotel_name: hotelDetails?.hotel_name || "",
        checkInTime: hotelDetails?.check_in_time || "",
        checkOutTime: hotelDetails?.check_out_time || "",
        location: hotelDetails?.location || "",
        image: hotelDetails?.image || "",
        cancellation_charge: hotelDetails?.cancellation_charge || "",
      },
      bookingDate: [bookingDate?.ucheckIn, bookingDate?.ucheckOut],
    };

    dispatch(setHotelDetails(payload.hotelDetails));
    dispatch(setHotelBooking(payload));
    dispatch(
      hottelBookingDataSubmit({
        bookingType: "booking",
      })
    )
      .unwrap()
      .then((response) => {
        setResponseData(response);
        dispatch(setBookingType(response.order?.bookingType));
        dispatch(setIsNavigating(false));
        if (response?.service?.date_service) {
          dispatch(setDateService(response.service.date_service));
          dispatch(setHotelService(response?.service?.data));

          toast.success(response.message, {
            position: "top-center",
            autoClose: 3000,
          });
        }
        dispatch(setUserInfo(response?.service?.data));
        dispatch(setBookingResponse(response));
        dispatch(setPriceMode("both"));

        navigate("/dashboard/db-dashboard/thank-you", { replace: true });
      })
      .catch((error) => {
        console.error("API call failed:", error);
        dispatch(setIsNavigating(false));
        toast.error("Something went wrong. Please try again later.", {
          position: "top-center",
          autoClose: 3000,
        });
      });
  };

  // Add a separate function for handling enquiry submission
  const handleEnquirySubmit = () => {
    if (!customerInfoRef.current?.isFormValid()) {
      toast.error("Please fill all required fields correctly");
      return;
    }

    if (!formData || Object.keys(formData).length === 0) {
      toast.error("Customer information is missing");
      return;
    }

    // if (!enquiryComment.trim()) {
    //   toast.error("Please enter a comment for your enquiry");
    //   return;
    // }
    dispatch(setIsNavigating(true));
    // Create a payload with enquiry-specific data
    const payload = {
      ...formData,
      rooms: rooms || [],
      totalPrice: totalPrice || 0,
      // Add enquiry-specific fields to the payload
      //enquiryPrice: enquiryAmount,
      //comment: enquiryComment,
      bookingType: "enquiry",
      priceMode: priceMode || "",
      priceModeId: priceModeId || "",
      hotelDetails: {
        hotel_id: hotelDetails?.hotel_id || "",
        hotel_name: hotelDetails?.hotel_name || "",
        checkInTime: hotelDetails?.check_in_time || "",
        checkOutTime: hotelDetails?.check_out_time || "",
        location: hotelDetails?.location || "",
        image: hotelDetails?.image || "",
        cancellation_charge: hotelDetails?.cancellation_charge || "",
      },
      bookingDate: [bookingDate?.ucheckIn, bookingDate?.ucheckOut],
    };

    // Log the enquiry details for debugging
    console.log("Enquiry details:", {
      // enquiryPrice: enquiryAmount,
      // comment: enquiryComment,
      totalPrice: totalPrice,
    });

    dispatch(setHotelDetails(payload.hotelDetails));
    dispatch(setHotelBooking(payload));
    dispatch(
      hottelBookingDataSubmit({
        bookingType: "enquiry",
        // enquiryPrice: enquiryAmount,
        // comment: enquiryComment,
      })
    )
      .unwrap()
      .then((response) => {
        setResponseData(response);
        dispatch(setBookingType(response.order?.bookingType));
        dispatch(setIsNavigating(false));
        if (response?.service?.date_service) {
          dispatch(setDateService(response.service.date_service));
          dispatch(setHotelService(response?.service?.data));

          toast.success("Enquiry submitted successfully!", {
            position: "top-center",
            autoClose: 3000,
          });
        }
        dispatch(setUserInfo(response?.service?.data));
        dispatch(setBookingResponse(response));
        dispatch(setPriceMode("both"));

        navigate("/dashboard/db-dashboard/thank-you", { replace: true });
      })
      .catch((error) => {
        console.error("API call failed:", error);
        dispatch(setIsNavigating(false));
        toast.error("Something went wrong. Please try again later.", {
          position: "top-center",
          autoClose: 3000,
        });
      });
  };

  return (
    <>
      <section className="pt-20">
        <button
          className="button px-15 py-8 bg-blue-1 text-white rounded position-relative "
          style={{
            left: "-80px",
            minHeight: "40px",
          }}
          onClick={() => navigate("/dashboard/db-dashboard/hotel-details")}
        >
          ← Back To Details
        </button>
        <div className="row x-gap-40">
          <CustomerInfo
            ref={customerInfoRef}
            roomDetails={rooms}
            tourDetails={tourDetails}
            totalPrice={totalPrice}
            onFormChange={handleFormChange}
            responseData={responseData}
            handleSubmit={handleSubmit}
            handleEnquirySubmit={handleEnquirySubmit}
            //enquiryAmount={enquiryAmount}
            //enquiryComment={enquiryComment}
            // onEnquiryAmountChange={handleEnquiryAmountChange}
            // onEnquiryCommentChange={handleEnquiryCommentChange}
          />
        </div>
      </section>

      {/* <div className="row x-gap-20 y-gap-20 mt-5">
        <div className="col-auto">
          <button
            className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
            onClick={handleSubmit}
          >
            Submit <div className="icon-arrow-top-right ml-15" />
          </button>
        </div>
      </div> */}
      <ToastContainer />
    </>
  );
};

export default Index;
