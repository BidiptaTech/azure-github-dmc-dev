import { useEffect } from "react";
import { useSelector } from "react-redux";
import { useNavigate, useLocation } from "react-router-dom";

const DASHBOARD_PATH = "/dashboard/db-dashboard";
const REDIRECT_DELAY_MS = 3500;

const OrderSubmittedInfo5 = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const bookingResponseFromRedux = useSelector(
    (state) => state.pickupDrop.response
  );

  const navState = location.state || {};
  const bookingResponse =
    bookingResponseFromRedux || navState.bookingResponse || null;
  const isCartCheckout = !!navState.cartCheckout;

  // Auto-redirect to dashboard after successful booking (~3.5s)
  useEffect(() => {
    const timer = setTimeout(() => {
      navigate(DASHBOARD_PATH, { replace: true });
    }, REDIRECT_DELAY_MS);
    return () => clearTimeout(timer);
  }, [navigate]);

  if (!bookingResponse && !isCartCheckout) {
    return <p>Loading booking details...</p>;
  }

  const { message } = bookingResponse || {};

  const formatMessage = (msg) => {
    if (!msg) {
      if (isCartCheckout) {
        return navState.bookingType === "enquiry"
          ? "Enquiry Submitted Successfully"
          : "Booking Successful";
      }
      return "Booking Successful";
    }
    if (msg.startsWith("Entry_port"))
      return msg.replace("Entry_port", "Entry Port");
    if (msg.startsWith("Exit_port"))
      return msg.replace("Exit_port", "Exit Port");
    if (msg.startsWith("Travel_point"))
      return msg.replace("Travel_point", "LocalTour");
    if (msg.startsWith("Travel_hourly"))
      return msg.replace("Travel_hourly", "LocalTour");
    if (msg.startsWith("Local_transport"))
      return msg.replace("Local_transport", "Local Transfer");
    return msg;
  };

  return (
    <>
      <div className="header-margin">
        <div className="col-xl-12 col-lg-12 d-flex justify-content-center">
          <div className="col-xl-6 col-lg-6">
            <div className="order-completed-wrapper">
              <div className="d-flex flex-column items-center mt-40 lg:md-40 sm:mt-24">
                <div className="size-80 flex-center rounded-full bg-dark-3">
                  <i className="icon-check text-30 text-white" />
                </div>
                <div className="text-30 lh-1 fw-600 mt-20">
                  {formatMessage(message)}
                </div>
                <div className="text-15 text-light-1 mt-20">
                  Redirecting to dashboard…
                </div>
              </div>

              {/*
                Tour ID / Date / Total / Booking Mode + Your Information
                sections removed — success screen only, then auto redirect.
              */}
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default OrderSubmittedInfo5;
