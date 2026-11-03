import { useNavigate } from "react-router-dom";
import { useDispatch } from "react-redux";
import PreDefinePackages from "@/components/pre-define-packages";
import { clearPackages, setPackageData } from "@/slice/tour-packages/tourPackageSlice";
import { resetConvertState } from "@/slice/enquiries/enquiryToTourSlice";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";
import { appPath } from "@/utils/appPaths";

/** Packages under PartnerLayout — same chrome as Bookings / Quotes / Wallet. */
export default function PackagesPage() {
  const navigate = useNavigate();
  const dispatch = useDispatch();

  const handleCreateOwn = () => {
    dispatch(setPackageData(null));
    dispatch(clearPackages());
    dispatch(resetPackages());
    dispatch(resetConvertState());
    navigate(appPath("tour-packages-page"));
  };

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
          className="partner-btn partner-btn--primary"
          onClick={handleCreateOwn}
        >
          + Create your own package
        </button>
      </div>

      <section className="partner-card partner-packages__search">
        <h2 className="partner-card__title">Find fixed itineraries</h2>
        <p className="partner-card__sub">
          Search by city, date and guests — then open a package to customise.
        </p>
        <div className="partner-packages__embed">
          <PreDefinePackages />
        </div>
      </section>
    </div>
  );
}
