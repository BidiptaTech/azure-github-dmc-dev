import { useNavigate } from "react-router-dom";
import BookingEnquiry from "@/components/booking-enquiry";
import { appPath } from "@/utils/appPaths";

/** Custom quote / enquiry under PartnerLayout. */
export default function BookingEnquiryEmbed() {
  const navigate = useNavigate();

  return (
    <div className="booking-enquiry-page">
      <button
        type="button"
        className="partner-back"
        onClick={() => navigate(appPath("quotes"))}
      >
        ← Quotes &amp; requests
      </button>
      <BookingEnquiry />
    </div>
  );
}
