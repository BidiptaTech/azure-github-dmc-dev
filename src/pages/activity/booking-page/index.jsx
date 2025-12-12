import CallToActions from "@/components/common/CallToActions";
import Header11 from "@/components/header/header-11";
import DefaultFooter from "@/components/footer/default";
import StepperBooking from "@/components/activity-single/booking-page";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { selectUserInfo } from "@/slice/common/customerInfo";
import { selectIsNavigating, setIsNavigating } from "@/slice/common/commonSlice";
import { useState, useEffect } from "react";

import MetaComponent from "@/components/common/MetaComponent";
import { useLocation } from "react-router-dom";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";
import Index2 from "@/components/activity-single/booking-page/index2";

const metadata = {
  title: "Checkout Page || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const BookingPage5 = () => {
  const dispatch = useDispatch();
  const userInfo = useSelector(selectUserInfo);
  console.log("userInfo", userInfo);
  const isNavigating = useSelector(selectIsNavigating);
  console.log("isNavigating", isNavigating);
  const location = useLocation();

  // Reset isNavigating on mount so second booking shows index2.jsx if userInfo exists
  useEffect(() => {
    dispatch(setIsNavigating(false));
  }, [dispatch]);

  // Only show Index2 component when we have userInfo AND we're not in the process of navigating
  const showBookingDetails = userInfo?.fullName && !isNavigating;
  console.log("showBookingDetails", showBookingDetails);
  return (
    <>
      <MetaComponent meta={metadata} />
      {/* End Page Title */}

      <div className="header-margin">
        <CustomStepper />
        <TourStatus />
      </div>
      {/* header top margin */}

      {/* <Header11 /> */}
      {/* End Header 1 */}

      <section className="pt-40 layout-pb-md">
        <div className="container">
          {showBookingDetails ? <Index2 /> : <StepperBooking />}
          {/* <StepperBooking /> */}
        </div>
        {/* End container */}
      </section>
      {/* End stepper */}

      <CallToActions />
      {/* End Call To Actions Section */}

      {/* <DefaultFooter /> */}
    </>
  );
};

export default BookingPage5;
