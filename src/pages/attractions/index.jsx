import CallToActions from "@/components/common/CallToActions";
import Header11 from "@/components/header/header-11";
import StepperBooking from "@/components/tour-list/booking-page/index";
import StepperBooking2 from "@/components/tour-list/booking-page/index2";
import MetaComponent from "@/components/common/MetaComponent";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { selectUserInfo } from "@/slice/common/customerInfo";
import { selectIsNavigating } from "@/slice/common/commonSlice";
import { useEffect, useState } from "react";


const metadata = {
  title: "Checkout Page || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const BookingPage = () => {
  const navigate = useNavigate();
  const isNavigating = useSelector(selectIsNavigating);
  const userInfo = useSelector(selectUserInfo);
  
  // Use a state to track which component to show, initialize based on userInfo
  const [currentStep, setCurrentStep] = useState(userInfo?.fullName ? 'confirmation' : 'booking');
  
  useEffect(() => {
    // Only change steps when we're not in the middle of navigating
    // This prevents flickering between components
    if (!isNavigating && userInfo?.fullName && currentStep === 'booking') {
      setCurrentStep('confirmation');
    }
  }, [isNavigating, userInfo, currentStep]);

  // Render the appropriate component based on currentStep
  const renderBookingComponent = () => {
    if (currentStep === 'confirmation') {
      return <StepperBooking2 />;
    }
    return <StepperBooking />;
  };

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
        <div className="container">
          {renderBookingComponent()}
        </div>
      </section>
      {/* End stepper */}

      <CallToActions />
      {/* End Call To Actions Section */}
    </>
  );
};

export default BookingPage;
