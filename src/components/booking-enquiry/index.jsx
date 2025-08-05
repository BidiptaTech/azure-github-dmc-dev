import React, { useState, useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import { addCurrentTab } from "../../features/hero/findPlaceSlice";
import MainFilterSearchBox from "../hero/hero-2/MainFilterSearchBox";
import BookingEnquiries from "./BookingEnquiries";
import ConfirmDetails from "./ConfirmDetails";
import ThankYouModal from "./ThankYouModal";
import { Box } from "@mui/material";

const Index = () => {
  // Get enquiry state from Redux
  const enquiryState = useSelector((state) => state.enquiry);
  const { enquiryId, multiEnqId, tourId, id } = enquiryState;
  
  const dispatch = useDispatch();
  
  // Scroll to top when component mounts
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);
  
  const [bookingOptions, setBookingOptions] = useState({
    hotel: false,
    entryExitPort: false,
    attraction: false,
    packagedAttractions: false,
    localTour: false,
    tourGuide: false,
    restaurant: false,
  });
  
  // Add state for modal
  const [showThankYouModal, setShowThankYouModal] = useState(false);
  
  // Use currentPage to track which component to display
  const [currentPage, setCurrentPage] = useState("search"); // Options: "search", "bookingEnquiries", "confirmDetails"

  // Scroll to top when currentPage changes
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [currentPage]);

  // Check if we have a submitted enquiry in localStorage on component mount
  useEffect(() => {
    const enquirySubmitted = localStorage.getItem('enquirySubmitted');
    const enquiryData = localStorage.getItem('enquiryData');
    
    console.log('Component mount - checking localStorage:', { enquirySubmitted, hasEnquiryData: !!enquiryData });
    console.log('Current Redux enquiry state:', { enquiryId, multiEnqId, tourId, id });
    
    if (enquirySubmitted === 'true' && enquiryData) {
      // Only show modal if we have both the flag and the actual enquiry data
      try {
        const parsedData = JSON.parse(enquiryData);
        console.log('Found valid enquiry data, showing modal:', parsedData);
        
        // Additional check: ensure we have a valid enquiry ID in Redux state
        const hasValidEnquiryId = multiEnqId || enquiryId || tourId || id;
        if (hasValidEnquiryId) {
          console.log('Valid enquiry ID found in Redux, showing modal');
          setShowThankYouModal(true);
        } else {
          console.log('No valid enquiry ID in Redux state, not showing modal');
        }
        
        // Clear the flag when showing the modal to ensure it's a one-time display
        localStorage.removeItem('enquirySubmitted');
      } catch (error) {
        console.error('Error parsing enquiry data:', error);
        // Clear invalid data
        localStorage.removeItem('enquirySubmitted');
        localStorage.removeItem('enquiryData');
      }
    } else {
      // If we're returning to the enquiry page, make sure modal won't show
      console.log('No valid enquiry data found, clearing localStorage');
      localStorage.removeItem('enquirySubmitted');
      localStorage.removeItem('enquiryData');
    }
  }, [enquiryId, multiEnqId, tourId, id]);

  // Navigation functions
  const goToSearch = () => {
    // Reset booking options when returning to search page
    resetBookingOptions();
    setCurrentPage("search");
  };
  
  const goToBookingEnquiries = () => setCurrentPage("bookingEnquiries");
  const goToConfirmDetails = () => setCurrentPage("confirmDetails");
  
  // Function to reset booking options
  const resetBookingOptions = () => {
    setBookingOptions({
      hotel: false,
      entryExitPort: false,
      attraction: false,
      localTour: false,
      tourGuide: false,
      restaurant: false,
      packagedAttractions: false
    });
  };
  
  // Function to handle completion of booking process
  const handleBookingComplete = () => {
    resetBookingOptions();
    setShowThankYouModal(true);
  };
  
  // Function to handle modal close
  const handleCloseModal = () => {
    setShowThankYouModal(false);
    setCurrentPage("search");
    // Reset booking options when closing modal
    resetBookingOptions();
    // Clear any remaining localStorage data
    localStorage.removeItem('enquirySubmitted');
    localStorage.removeItem('enquiryData');
  };

  // Cleanup effect to clear localStorage when component unmounts
  useEffect(() => {
    return () => {
      // Clean up localStorage when component unmounts to prevent stale data
      localStorage.removeItem('enquirySubmitted');
      localStorage.removeItem('enquiryData');
    };
  }, []);

  // Render the current page based on state
  const renderCurrentPage = () => {
    switch (currentPage) {
      case "search":
        return (
          <div className="page-container">
            <MainFilterSearchBox onNext={goToBookingEnquiries} />
          </div>
        );

      case "bookingEnquiries":
        return (
          <div className="page-container">
            {/* <h3 className="text-22 text-white mb-5">Select Your Travel Services</h3>
            <p className="text-14 text-white opacity-80 mb-15">Customize your trip with hotels, transportation, attractions and more.</p> */}
            <BookingEnquiries
              bookingOptions={bookingOptions}
              setBookingOptions={setBookingOptions}
              onNext={goToConfirmDetails}
              onBack={goToSearch}
            />
            <div className="navigation-buttons mt-15 d-flex justify-between">
              <button 
                className="button -md -blue-1 bg-blue-1-05 text-blue-1 border-blue-1" 
                onClick={goToSearch}
              >
                <i className="icon-arrow-left text-20 mr-10"></i>
                Back to Search
              </button>
            </div>
          </div>
        );

      case "confirmDetails":
        return (
          <div className="page-container">
            {/* <h3 className="text-22 text-white mb-5">Review and Submit Your Itinerary</h3>
            <p className="text-14 text-white opacity-80 mb-15">Verify all your selected services before finalizing your booking.</p> */}
            <ConfirmDetails 
              bookingOptions={bookingOptions} 
              onBack={goToBookingEnquiries}
              onComplete={handleBookingComplete}
              resetBookingOptions={resetBookingOptions}
            />
          </div>
        );

      default:
        return null;
    }
  };

  // Get dynamic heading content based on current page
  const getPageHeading = () => {
    switch(currentPage) {
      case "search":
        return (
          <div className="text-center mb-10">
            <h1 className="z-2 text-50 lg:text-40 md:text-30 fw-600">
              <span className="gradient-text">Discover Your Dream Destination</span>
            </h1>
          </div>
        );
      case "bookingEnquiries":
        return (
          <div className="page-heading-container">
            <div className="page-heading-bg">
              <div className="page-heading-content">
                <h1 className="z-2 text-50 lg:text-40 md:text-30 fw-600">
                  <span className="booking-gradient-text">Customize Your Travel Experience</span>
                </h1>
                <p className="page-subtitle">Select your preferred services and customize your travel experience</p>
              </div>
            </div>
          </div>
        );
      case "confirmDetails":
        return (
          <div className="page-heading-container">
            <div className="page-heading-bg">
          
              <div className="page-heading-content">
                <h1 className="z-2 text-50 lg:text-40 md:text-30 fw-600">
                  <span className="confirm-gradient-text">Finalize Your Perfect Itinerary</span>
                </h1>
                <p className="page-subtitle">Review your selections and confirm your personalized travel plan</p>
              </div>
            </div>
          </div>
        );
      default:
        return null;
    }
  };

  return (
    <section className="masthead -type-2 z-2" style={{marginTop: "10px"}}>
      {/* Background images for different pages */}
      {currentPage === "search" && (
        <div className="masthead__bg">
          <img alt="image" src="/img/masthead/4/bg.png" className="js-lazy" style={{width: "100%", height: "130%"}} />
        </div>
      )}
      {currentPage === "bookingEnquiries" && (
        <div className="masthead__bg">
          <img alt="image" src="/img/masthead/9/bg.png" className="js-lazy" style={{width: "100%", height: "350px"}} />
        </div>
      )}
      {currentPage === "confirmDetails" && (  
        <div className="masthead__bg">
          <img alt="image" src="/img/masthead/9/bg.png" className="js-lazy" style={{width: "100%", height: "350px"}} />
        </div>
      )}
      <div className="container">
        <Box sx={{ py: 1 }}>
          <div className="masthead__content">
            <div className="row y-gap-3">
              <div className="col-xl-12" data-aos="fade-up" data-aos-offset="0">
                {/* Dynamic heading based on current page */}
                {getPageHeading()}

                {/* Render the current page */}
                {renderCurrentPage()}

              </div>
            </div>
          </div>
        </Box>
      </div>

      {/* Thank You Modal */}
      <ThankYouModal 
        open={showThankYouModal} 
        onClose={handleCloseModal}
        delay={7} 
      />

      <style jsx>{`
        .page-navigation {
          margin-top: 15px;
          margin-bottom: 20px;
        }
        .nav-item {
          position: relative;
          padding: 8px 15px;
          z-index: 2;
        }
        .nav-button {
          background: none;
          border: none;
          color: #fff;
          font-weight: 500;
          opacity: 0.7;
          transition: all 0.3s;
          cursor: pointer;
        }
        .nav-button.active {
          opacity: 1;
          font-weight: 600;
          color: #3554d1;
        }
        .nav-item.active {
          background: rgba(255, 255, 255, 0.1);
          border-radius: 20px;
        }
        .nav-separator {
          width: 40px;
          height: 2px;
          background: rgba(255, 255, 255, 0.2);
          margin-top: 15px;
        }
                  .page-container {
            //background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border-radius: 12px;
            min-height: 150px;
            margin-top: 5px;
          }
        .navigation-buttons {
          display: flex;
          justify-content: flex-end;
        }
        .gradient-text {
          background: linear-gradient(135deg, #ffcc00 0%, #ff9500 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          text-fill-color: transparent;
          display: inline-block;
          text-shadow: 0px 2px 10px rgba(255, 204, 0, 0.3);
        }
        
        /* Page heading container styles */
        .page-heading-container {
          position: relative;
          margin-bottom: 10px;
          width: 100%;
        }
        
        .page-heading-bg {
          position: relative;
          width: 100%;
          height: 200px;
          overflow: hidden;
        }
        
        .page-bg-image {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }
        
        .page-heading-overlay {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: linear-gradient(135deg, 
            rgba(30, 58, 138, 0.85) 0%, 
            rgba(37, 99, 235, 0.75) 30%, 
            rgba(59, 130, 246, 0.65) 70%, 
            rgba(96, 165, 250, 0.55) 100%);
        }
        
        .page-heading-content {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          text-align: center;
          z-index: 10;
          width: 100%;
        }
        
        .booking-gradient-text {
          background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 40%, #dbeafe 80%, #bfdbfe 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          text-fill-color: transparent;
          display: inline-block;
          text-shadow: 0px 3px 15px rgba(255, 255, 255, 0.6);
          font-weight: 700;
        }
        
        .confirm-gradient-text {
          background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 40%, #e0f2fe 80%, #bae6fd 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          text-fill-color: transparent;
          display: inline-block;
          text-shadow: 0px 3px 15px rgba(255, 255, 255, 0.6);
          font-weight: 700;
        }
        
        .page-subtitle {
          color: rgba(255, 255, 255, 0.95);
          font-size: 18px;
          font-weight: 500;
          margin-top: 15px;
          text-shadow: 0px 2px 8px rgba(0, 0, 0, 0.3);
          max-width: 600px;
          margin-left: auto;
          margin-right: auto;
          line-height: 1.4;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
          .page-heading-bg {
            height: 220px;
          }
          
          .page-heading-content h1 {
            font-size: 28px !important;
          }
          
          .page-subtitle {
            font-size: 16px;
          }
        }
        
        @media (max-width: 480px) {
          .page-heading-bg {
            height: 180px;
          }
          
          .page-heading-content h1 {
            font-size: 24px !important;
          }
          
          .page-subtitle {
            font-size: 14px;
          }
          
          .page-heading-container {
            margin-bottom: 5px;
          }
        }
      `}</style>
    </section>
  );
};

export default Index;
