import { useNavigate } from "react-router-dom";
import PartnerEnquiryList from "@/components/workspace/PartnerEnquiryList";
import { appPath } from "@/utils/appPaths";

/** Quotes & requests — Quick Enquiries in partner workspace chrome. */
export default function Quotes() {
  const navigate = useNavigate();

  return (
    <div>
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Quotations &amp; enquiries</div>
          <h1 className="partner-title">From first brief to confirmed trip.</h1>
          <p className="partner-lede">
            Compare proposals, request changes and continue with payment.
          </p>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--primary"
          onClick={() => navigate(appPath("booking-enquiry"))}
        >
          + New enquiry
        </button>
      </div>

      <section className="partner-card">
        <PartnerEnquiryList />
      </section>
    </div>
  );
}
