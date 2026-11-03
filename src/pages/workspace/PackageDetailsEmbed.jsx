import { useNavigate } from "react-router-dom";
import PackageDetailsContainer from "@/components/pre-define-packages/details";
import { appPath } from "@/utils/appPaths";

/** Package details under PartnerLayout — same chrome as other sections. */
export default function PackageDetailsEmbed() {
  const navigate = useNavigate();

  return (
    <div className="partner-packages">
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Package details</div>
          <h1 className="partner-title">Review and customise this journey.</h1>
          <p className="partner-lede">
            Check inclusions, adjust services, then continue to confirmation.
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

      <section className="partner-card" style={{ padding: 12 }}>
        <PackageDetailsContainer />
      </section>
    </div>
  );
}
