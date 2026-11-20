import { useNavigate } from "react-router-dom";
import PreDefinePackages from "@/components/pre-define-packages";
import { appPath } from "@/utils/appPaths";

/** Package search — same Plan & book / Quick Enquiry hero design. */
export default function PreDefinePackagesEmbed() {
  const navigate = useNavigate();

  return (
    <div className="partner-packages partner-package-search partner-plan-hero">
      <button
        type="button"
        className="partner-back"
        onClick={() => navigate(appPath("packages"))}
      >
        ← Packages
      </button>
      <PreDefinePackages variant="hero" />
    </div>
  );
}
