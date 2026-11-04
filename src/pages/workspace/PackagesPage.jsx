import { useNavigate } from "react-router-dom";
import PartnerPackageBookings from "@/components/workspace/PartnerPackageBookings";
import { PACKAGES_SEARCH_PATH } from "@/utils/appPaths";

/** Packages — booking list in the same partner chrome as Quotes & enquiries. */
export default function PackagesPage() {
  const navigate = useNavigate();

  return (
    <div className="partner-packages">
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Package bookings</div>
          <h1 className="partner-title">From ready-made itinerary to confirmed trip.</h1>
          <p className="partner-lede">
            Review ongoing, upcoming and past package bookings — or search for a
            package to book next.
          </p>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--primary"
          onClick={() => navigate(PACKAGES_SEARCH_PATH)}
        >
          Search for a package
        </button>
      </div>

      <section className="partner-card">
        <PartnerPackageBookings />
      </section>
    </div>
  );
}
