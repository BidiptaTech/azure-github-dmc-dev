import CallToActions from "@/components/common/CallToActions";
import Header11 from "@/components/header/header-11";
import StepperBooking from "@/components/booking-page";
import StepperBooking2 from "@/components/booking-page/index2";
import MetaComponent from "@/components/common/MetaComponent";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { useEffect } from "react";
import {
  selectBookingResponse,
  clearUserInfo,
  selectUserInfo,
} from "@/slice/common/customerInfo";
import { selectIsNavigating, setIsNavigating } from "@/slice/common/commonSlice";
const metadata = {
  title: "Hotel Booking Page || GoTrip - Travel & Tour ReactJs Template",
  description: "GoTrip - Travel & Tour ReactJs Template",
};

const BookingPage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();

  // Change this to use booking response instead of userInfo
  const bookingResponse = useSelector(selectUserInfo);
  const isNavigating = useSelector(selectIsNavigating);

  // Reset isNavigating on mount so second booking shows index2.jsx if userInfo exists
  useEffect(() => {
    dispatch(setIsNavigating(false));
  }, [dispatch]);

  // Only show StepperBooking2 if we have a complete booking response
  const showBookingDetails = bookingResponse?.fullName && !isNavigating;
  
  console.log("🔍 PARENT RENDER:", {
    bookingResponse_fullName: bookingResponse?.fullName,
    isNavigating,
    showBookingDetails,
    willRender: showBookingDetails ? "index2.jsx" : "index.jsx"
  });

  return (
    <>
      <MetaComponent meta={metadata} />
      {/* End Page Title */}

      <div className="header-margin">
        <CustomStepper />
        <TourStatus />
      </div>
      {/* header top margin */}

      <Header11 />
      {/* End Header 1 */}

      <section className="pt-40 layout-pb-md">
        {/* <div
          style={{ display: "flex", alignItems: "flex-start", padding: "20px" }}
        >
          <button
            className="button px-4 py-2 bg-blue-1 text-white rounded"
            style={{
              minHeight: "40px",
              marginRight: "20px", // Ensures space from content
            }}
            onClick={() => {
              dispatch(clearUserInfo());
              navigate("/dashboard/db-dashboard/hotel-details");
            }}
          >
            ← Back To Details
          </button>
        </div> */}
        <div className="container">
          {showBookingDetails ? <StepperBooking2 /> : <StepperBooking />}
        </div>
      </section>
      {/* End stepper */}

      <CallToActions />
      {/* End Call To Actions Section */}
    </>
  );
};

export default BookingPage;
