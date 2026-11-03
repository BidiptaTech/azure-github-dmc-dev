import React, { useState } from "react";
import { Box } from "@mui/material";
import { useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import Hero1 from "@/components/hero/hero-1";
import SearchForm from "./common/SearchForm";
import Itinerary from "./common/Itinerary";
import { appPath } from "@/utils/appPaths";

export default function TourPackages({ mode = "packages" } = {}) {
  const [currentStep, setCurrentStep] = useState(1);
  const navigate = useNavigate();
  const packageData = useSelector((state) => state.tourPackages.packageData);

  const handleNext = () => {
    window.scrollTo(0, 200);
    setCurrentStep((step) => step + 1);
  };

  const handleBookingSuccess = () => {
    setCurrentStep(1);
  };

  const isBuilding = currentStep > 1;
  const isPlanMode = mode === "plan";

  const heroTitle = packageData
    ? isPlanMode
      ? "Update this trip for your clients"
      : "Update your tour package"
    : isPlanMode
      ? "Plan & book tours for your clients"
      : "Create your own tour package";

  const heroSubtitle = packageData
    ? "Adjust destination, dates and travellers — then refine the package itinerary"
    : isPlanMode
      ? "Set destinations, dates and pax — then add hotels, transfers and experiences"
      : "Pick destination, dates and travellers — then build a sellable package itinerary";

  const footerNote = isPlanMode
    ? "Build complete itineraries — hotels, attractions, transfers, guides & more"
    : "Package itineraries — hotels, attractions, transfers, guides & more";

  return (
    <div className={`partner-tour-packages${isBuilding ? " is-building" : ""}`}>
      {!isBuilding ? (
        <div className="partner-plan-hero partner-tour-packages__hero">
          {!isPlanMode ? (
            <div className="partner-tour-packages__back">
              <button
                type="button"
                className="partner-btn partner-btn--secondary"
                onClick={() => navigate(appPath("packages"))}
              >
                ← Back to packages
              </button>
            </div>
          ) : null}
          <Hero1
            title={heroTitle}
            subtitle={heroSubtitle}
            footerNote={footerNote}
            searchSlot={
              <div className="tp-hero-search">
                <SearchForm
                  onNext={handleNext}
                  packageData={packageData}
                  mode={mode}
                />
              </div>
            }
          />
        </div>
      ) : (
        <>
          <div className="partner-hero">
            <div>
              <div className="partner-kicker">
                {isPlanMode ? "Trip builder" : "Tour packages"}
              </div>
              <h1 className="partner-title">
                {isPlanMode ? "Build your itinerary" : "Build your package itinerary"}
              </h1>
              <p className="partner-lede">
                {isPlanMode
                  ? "Add hotels, ports, attractions, dining and more into one journey."
                  : "Add hotels, ports, attractions, dining and more into one package."}
              </p>
            </div>
            <button
              type="button"
              className="partner-btn partner-btn--secondary"
              onClick={() => setCurrentStep(1)}
            >
              ← Edit search
            </button>
          </div>

          <section className="partner-card partner-tour-packages__itinerary">
            <Box className="partner-tour-packages__itinerary-body" sx={{ py: 1 }}>
              <Itinerary onBookingSuccess={handleBookingSuccess} />
            </Box>
          </section>
        </>
      )}
    </div>
  );
}
