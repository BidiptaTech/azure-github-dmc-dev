import React, { useState } from "react";
import MainFilterSearchBox from "../hero/hero-2/MainFilterSearchBox";
import BookingEnquiries from "./BookingEnquiries";
import ConfirmDetails from "./ConfirmDetails";

const Stepper = () => {
  const [activeStep, setActiveStep] = useState(0);
  const [bookingOptions, setBookingOptions] = useState({
    hotel: false,
    entryExitPort: false,
    attraction: false,
    localTour: false,
    tourGuide: false,
    restaurant: false,
  });

  const handleNext = () => {
    setActiveStep((prevStep) => Math.min(prevStep + 1, 2));
  };

  const handleBack = () => {
    setActiveStep((prevStep) => Math.max(prevStep - 1, 0));
  };

  const steps = [
    {
      label: "Start",
      component: <MainFilterSearchBox onNext={handleNext} />,
    },
    {
      label: "Booking Enquiries",
      component: (
        <BookingEnquiries
          bookingOptions={bookingOptions}
          setBookingOptions={setBookingOptions}
          onNext={handleNext}
          onBack={handleBack}
        />
      ),
    },
    {
      label: "Confirm Details",
      component: <ConfirmDetails bookingOptions={bookingOptions} onBack={handleBack} />,
    },
  ];

  return (
    <div className="stepper-container mb-20 justify-center">
      <div className="header d-flex justify-between items-center mb-30">
        {steps.map((step, index) => (
          <div
            key={index}
            className={`stepper-step d-flex flex-column items-center ${
              index === activeStep ? "active" : ""
            } ${index < activeStep ? "completed" : ""}`}
          >
            <div className="step-indicator d-flex items-center justify-center">
              {index < activeStep ? (
                <i className="icon-check text-16"></i>
              ) : (
                index + 1
              )}
            </div>
            <div className="step-label mt-10">{step.label}</div>
            {index < steps.length - 1 && <div className="step-connector"></div>}
          </div>
        ))}
      </div>
      <div className="stepper-actions d-flex justify-between mt-50 mb-20">
        <button
          className="button -md -blue-1 bg-blue-1-05 text-blue-1 border-blue-1"
          onClick={handleBack}
          disabled={activeStep === 0}
        >
          <i className="icon-arrow-left text-20 mr-10"></i>
          Back
        </button>

        {activeStep === 0 && (
          <button
            className="button -md -blue-1 bg-blue-1 text-white"
            onClick={handleNext}
          >
            Next
            <i className="icon-arrow-right text-20 ml-10"></i>
          </button>
        )}
        
        {activeStep === 2 && (
          <button className="button -md -dark-1 bg-yellow-1 text-dark-1">
            Submit
            <i className="icon-check text-20 ml-10"></i>
          </button>
        )}
      </div>

      <div className="stepper-content p-20 mb-50">
        {steps[activeStep].component}
      </div>

      <style jsx>{`
        .stepper-container {
          width: 100%;
          max-width: 1200px;
          margin: 0 auto;
          justify-content: center;
          align-items: center;
          padding: 20px;
        }
        .stepper-header {
          position: relative;
        }
        .stepper-header::before {
          content: "";
          position: absolute;
          top: 20px;
          left: 0;
          right: 0;
          height: 2px;
          background-color: #ddd;
          z-index: 1;
        }
        .stepper-step {
          position: relative;
          z-index: 2;
          width: 33.333%;
        }
        .step-indicator {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background-color: #f5f5f5;
          border: 2px solid #ddd;
          color: #666;
          font-weight: 600;
        }
        .stepper-step.active .step-indicator {
          background-color: #3554d1;
          border-color: #3554d1;
          color: white;
        }
        .stepper-step.completed .step-indicator {
          background-color: #4caf50;
          border-color: #4caf50;
          color: white;
        }
        .stepper-step.active .step-label {
          color: #3554d1;
          font-weight: 600;
        }
        .stepper-step.completed .step-label {
          color: #4caf50;
        }
        .step-connector {
          position: absolute;
          top: 20px;
          left: 50%;
          right: -50%;
          height: 2px;
          background-color: #ddd;
          z-index: -1;
        }
        .stepper-step.completed .step-connector {
          background-color: #4caf50;
        }
        .stepper-content {
          overflow: hidden;
          display: flex;
          justify-content: center;
          align-items: center;
        }
      `}</style>
    </div>
  );
};

export default Stepper;
