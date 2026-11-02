import Packages from "@/pages/packages";

/** Packages chooser under PartnerLayout (no extra Header11). */
export default function PackagesPage() {
  return (
    <div className="partner-packages-embed">
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Packages</div>
          <h1 className="partner-title">Ready-made journeys, made flexible.</h1>
          <p className="partner-lede">
            Explore fixed itineraries or build every service yourself.
          </p>
        </div>
      </div>
      <style>{`
        .partner-packages-embed .packages-container {
          padding: 24px 0 !important;
        }
        .partner-packages-embed .pre-define-packages-background {
          background: transparent !important;
          min-height: auto !important;
        }
      `}</style>
      <Packages />
    </div>
  );
}
