import { useNavigate } from "react-router-dom";
import PreDefinePackages from "@/components/pre-define-packages";
import { appPath } from "@/utils/appPaths";

/** Package search — same Plan & book / Quick Enquiry hero design. */
export default function PreDefinePackagesEmbed() {
  const navigate = useNavigate();

  return (
    <div className="partner-packages partner-package-search partner-plan-hero">
      <div className="partner-tour-packages__hero">
        <div className="partner-tour-packages__back">
          <button
            type="button"
            className="partner-btn partner-btn--secondary"
            onClick={() => navigate(appPath("packages"))}
          >
            ← Back to bookings
          </button>
        </div>
        <PreDefinePackages variant="hero" />
      </div>
    </div>
  );
}
