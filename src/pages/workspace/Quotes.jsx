import { useNavigate } from "react-router-dom";
import PartnerEnquiryList from "@/components/workspace/PartnerEnquiryList";
import { QUOTES_ENQUIRY_PATH } from "@/utils/appPaths";

const QUOTE_STEPS = [
  { n: "1", label: "Share your brief" },
  { n: "2", label: "Compare proposals" },
  { n: "3", label: "Confirm & pay" },
];

/** Quotes & requests — Quick Enquiries in partner workspace chrome. */
export default function Quotes() {
  const navigate = useNavigate();

  return (
    <div className="partner-quotes">
      <div className="partner-hero-row partner-quotes__hero">
        <div className="partner-quotes__intro">
          <div className="partner-kicker">Quotations &amp; enquiries</div>
          <h1 className="partner-title">From first brief to confirmed trip.</h1>
          <p className="partner-lede">
            Compare proposals, request changes and continue with payment — all in
            one place.
          </p>
          <ol className="partner-quotes__steps" aria-label="How quotes work">
            {QUOTE_STEPS.map((step) => (
              <li key={step.n} className="partner-quotes__step">
                <span className="partner-quotes__step-n" aria-hidden="true">
                  {step.n}
                </span>
                <span className="partner-quotes__step-label">{step.label}</span>
              </li>
            ))}
          </ol>
        </div>

        <section className="partner-quote-cta partner-quotes__cta">
          <div className="partner-quotes__cta-badge">New request</div>
          <h2 className="partner-quote-cta__title">Need a tailor-made quote?</h2>
          <p className="partner-quote-cta__sub">
            Share destination, dates and travellers — your DMC builds the
            proposal.
          </p>
          <button
            type="button"
            className="partner-btn partner-btn--primary"
            onClick={() => navigate(QUOTES_ENQUIRY_PATH)}
          >
            + New enquiry
          </button>
        </section>
      </div>

      <section className="partner-card partner-quotes__panel">
        <header className="partner-quotes__panel-head">
          <div>
            <h2 className="partner-card__title">Your quotes &amp; requests</h2>
            <p className="partner-card__sub">
              Open enquiries and proposals ready for your review.
            </p>
          </div>
        </header>
        <PartnerEnquiryList />
      </section>
    </div>
  );
}
