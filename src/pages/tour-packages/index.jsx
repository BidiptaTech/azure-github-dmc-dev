import TourPackages from "@/components/Tour-Packages";
import React from "react";

/** Standalone fallback — PartnerLayout supplies the header under /app. */
export default function TourPackagesPage() {
  return (
    <div className="tour-packages-page">
      <TourPackages mode="packages" />
    </div>
  );
}
