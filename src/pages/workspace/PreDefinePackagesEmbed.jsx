import { useNavigate } from "react-router-dom";
import PreDefinePackages from "@/components/pre-define-packages";
import { appPath } from "@/utils/appPaths";

/** Pre-define packages search under PartnerLayout (no Header11). */
export default function PreDefinePackagesEmbed() {
  const navigate = useNavigate();

  return (
    <div className="partner-packages">
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Fixed itinerary packages</div>
          <h1 className="partner-title">Start with a journey already planned.</h1>
          <p className="partner-lede">
            Choose a sample package, then customise services and request
            confirmation.
          </p>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--secondary"
          onClick={() => navigate(appPath("packages"))}
        >
          ← Back to packages
        </button>
      </div>

      <section className="partner-card partner-packages__search">
        <div className="partner-packages__embed">
          <PreDefinePackages />
        </div>
      </section>
    </div>
  );
}
