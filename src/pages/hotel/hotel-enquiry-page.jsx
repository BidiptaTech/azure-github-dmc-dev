import React, { useState } from "react";
import EnquirySearchWithPreferences from "../../components/hero/hero-2/EnquirySearchWithPreferences";
import DefaultHeader from "../../components/header/default-header";
import CallToActions from "../../components/common/CallToActions";
import DefaultFooter from "../../components/footer/default";
import HotelsSearchResults from "../../components/hotel-list/HotelsSearchResults";

const HotelEnquiryPage = () => {
  // State to track the current step of the enquiry process
  const [currentStep, setCurrentStep] = useState("search"); // search, results, etc.

  // Handle moving to the results step after search is complete
  const handleSearchComplete = () => {
    setCurrentStep("results");
  };

  return (
    <>
      <DefaultHeader />

      <div className="hero-section bg-light-2 pt-40 pb-40">
        <div className="container">
          <div className="row justify-content-center">
            <div className="col-xl-10">
              <h1 className="text-30 fw-600 text-center mb-10">
                Find Your Perfect Hotel
              </h1>
              <p className="text-center text-18 text-light-1 mb-20">
                Search, select your preferred hotels, and create your custom travel enquiry
              </p>

              {currentStep === "search" && (
                <div className="search-form-wrapper">
                  <EnquirySearchWithPreferences onNext={handleSearchComplete} />
                </div>
              )}

              {currentStep === "results" && (
                <div className="search-results-wrapper">
                  <HotelsSearchResults />
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      <CallToActions />
      <DefaultFooter />
    </>
  );
};

export default HotelEnquiryPage; 