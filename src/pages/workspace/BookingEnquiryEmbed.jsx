import { useNavigate } from "react-router-dom";
import BookingEnquiry from "@/components/booking-enquiry";
import { appPath } from "@/utils/appPaths";

/** Custom quote / enquiry under PartnerLayout. */
export default function BookingEnquiryEmbed() {
  const navigate = useNavigate();

  return (
    <div className="booking-enquiry-page">
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Quotations &amp; enquiries</div>
          <h1 className="partner-title">Build a quick enquiry.</h1>
          <p className="partner-lede">
            Choose DMC partners and the services you need — then send for pricing.
          </p>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--secondary"
          onClick={() => navigate(appPath("quotes"))}
        >
          ← Back to quotes
        </button>
      </div>
      <BookingEnquiry />
    </div>
  );
}
