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
    
    // console.log('Component mount - checking localStorage:', { enquirySubmitted, hasEnquiryData: !!enquiryData });
    // console.log('Current Redux enquiry state:', { enquiryId, multiEnqId, tourId, id });
    
    if (enquirySubmitted === 'true' && enquiryData) {
      // Only show modal if we have both the flag and the actual enquiry data
      try {
        const parsedData = JSON.parse(enquiryData);
        // console.log('Found valid enquiry data, showing modal:', parsedData);
        
        // Additional check: ensure we have a valid enquiry ID in Redux state
        const hasValidEnquiryId = multiEnqId || enquiryId || tourId || id;
        if (hasValidEnquiryId) {
          // console.log('Valid enquiry ID found in Redux, showing modal');
          setShowThankYouModal(true);
        } else {
          //console.log('No valid enquiry ID in Redux state, not showing modal');
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
      restaurant: false
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
                className="button -md -blue-1 bg-blue-1-05 text-blue-1 border-blue-1 responsive-nav-button" 
                onClick={goToSearch}
              >
                <i className="icon-arrow-left text-20 mr-10"></i>
                <span className="nav-button-text">Back to Search</span>
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
            <h1 className="z-2 text-50 lg:text-40 md:text-30 sm:text-28 xs:text-24 fw-600">
              <span className="gradient-text">Discover Your Dream Destination</span>
            </h1>
            {/* <h3 className="z-2 text-40 lg:text-30 md:text-20 sm:text-18 xs:text-16 fw-600">
              <span style={{color: "orange"}}>with Quick Enquiry</span>
            </h3> */}
          </div>
        );
      case "bookingEnquiries":
        return (
          <div className="page-heading-container">
            <div className="page-heading-bg">
              <div className="page-heading-content">
                <h1 className="z-2 text-50 lg:text-40 md:text-30 sm:text-28 xs:text-24 fw-600">
                  <span className="booking-gradient-text">Customize Your Travel Experience</span>
                </h1>
                <p className="page-subtitle">Choose your preferred DMC or select multiple or all DMC before Selecting your preferred services and customize your travel experience</p>
              </div>
            </div>
          </div>
        );
      case "confirmDetails":
        return (
          <div className="page-heading-container">
            <div className="page-heading-bg">
              <div className="page-heading-content">
                <h1 className="z-2 text-50 lg:text-40 md:text-30 sm:text-28 xs:text-24 fw-600">
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
      {/* Background images for different pages - Hidden on mobile */}
      {currentPage === "search" && (
        <div className="masthead__bg desktop-bg">
          <img 
            alt="image" 
            src="/img/masthead/4/bg.png" 
            className="js-lazy responsive-bg-image" 
            style={{
              width: "100%", 
              height: "130%",
              objectFit: "cover",
              objectPosition: "center"
            }} 
          />
        </div>
      )}
      {currentPage === "bookingEnquiries" && (
        <div className="masthead__bg desktop-bg">
          <img 
            alt="image" 
            src="/img/masthead/9/bg.png" 
            className="js-lazy responsive-bg-image" 
            style={{
              width: "100%", 
              height: "350px",
              objectFit: "cover",
              objectPosition: "center"
            }} 
          />
        </div>
      )}
      {currentPage === "confirmDetails" && (  
        <div className="masthead__bg desktop-bg">
          <img 
            alt="image" 
            src="/img/masthead/9/bg.png" 
            className="js-lazy responsive-bg-image" 
            style={{
              width: "100%", 
              height: "350px",
              objectFit: "cover",
              objectPosition: "center"
            }} 
          />
        </div>
      )}
      
      {/* Mobile-friendly gradient backgrounds */}
      <div className="mobile-bg-gradient"></div>
      <div className="container">
        <Box sx={{ 
          py: { xs: 0.5, sm: 1, md: 1.5 },
          px: { xs: 1, sm: 2, md: 3 }
        }}>
          <div className="masthead__content">
            <div className="row y-gap-3">
              <div className="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12" data-aos="fade-up" data-aos-offset="0">
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
        }
        
        /* Hide background images on mobile and small screens */
        @media (max-width: 899px) {
          .desktop-bg {
            display: none !important;
          }
          .masthead__bg {
            display: none !important;
          }
        }
        
        /* Show background images on medium screens and up */
        @media (min-width: 900px) {
          .desktop-bg {
            display: block !important;
          }
          .masthead__bg {
            display: block !important;
          }
        }
        
        /* Mobile and tablet spacing adjustments */
        @media (max-width: 899px) {
          .masthead {
            padding: 0 !important;
            background: transparent !important;
          }
          .container {
            padding: 0 8px !important;
          }
          
          /* Mobile heading container spacing */
          .page-heading-container {
            margin-top: 0 !important;
            padding-top: 5px !important;
            margin-bottom: 0 !important;
          }
          
          .page-heading-content {
            padding-top: 0 !important;
            margin-top: 0 !important;
            padding-bottom: 0 !important;
          }
          
          /* Mobile heading text spacing */
          h1 {
            margin-top: 0 !important;
            margin-bottom: 8px !important;
            line-height: 1.2 !important;
          }
          
          .page-subtitle {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
          }
          
          /* Mobile and tablet color scheme */
          .booking-gradient-text {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
            text-shadow: 0 2px 4px rgba(255, 107, 107, 0.3) !important;
          }
          
          .page-subtitle {
            color: #666 !important;
            font-weight: 500 !important;
            text-shadow: none !important;
          }
          
          .page-heading-bg {
            background: transparent !important;
            backdrop-filter: none !important;
            border-radius: 0 !important;
            padding: 12px !important;
            margin: 3px 0 !important;
            border: none !important;
          }
        }
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
          margin-top: 15px;
        }
        
        .responsive-nav-button {
          transition: all 0.3s ease;
          min-height: 44px;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        
        .nav-button-text {
          white-space: nowrap;
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
        
        /* Desktop background images - Hidden on mobile */
        .desktop-bg {
          display: block;
        }
        
        .responsive-bg-image {
          width: 100%;
          height: auto;
          min-height: 200px;
          object-fit: cover;
          object-position: center;
        }
        
        /* Mobile-friendly gradient background */
        .mobile-bg-gradient {
          display: none;
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          z-index: 1;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
          .page-heading-bg {
            height: 280px;
          }
        }
        
        @media (max-width: 992px) {
          .page-heading-bg {
            height: 250px;
          }
          
          .page-heading-content h1 {
            font-size: 32px !important;
          }
          
          .page-subtitle {
            font-size: 17px;
          }
        }
        
        @media (max-width: 768px) {
          /* Hide desktop background images on mobile */
          .desktop-bg {
            display: none !important;
          }
          
          /* Show mobile gradient background */
          // .mobile-bg-gradient {
          //   display: block !important;
          //   background: linear-gradient(135deg, 
          //     #667eea 0%, 
          //     #764ba2 25%, 
          //     #f093fb 50%, 
          //     #f5576c 75%, 
          //     #4facfe 100%);
          //   background-size: 400% 400%;
          //   animation: gradientShift 15s ease infinite;
          // }
          
          @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
          }
          
          .page-heading-bg {
            height: 220px;
            background: transparent;
          }
          
          .page-heading-content h1 {
            font-size: 28px !important;
          }
          
          .page-subtitle {
            font-size: 16px;
            padding: 0 20px;
          }
          
          .page-container {
            padding: 8px;
            margin-top: 3px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            backdrop-filter: blur(10px);
          }
          
          .responsive-nav-button {
            padding: 8px 16px;
            font-size: 14px;
          }
          
          .nav-button-text {
            font-size: 14px;
          }
        }
        
        @media (max-width: 576px) {
          /* Ensure desktop backgrounds are hidden */
          .desktop-bg {
            display: none !important;
          }
          
          .page-heading-bg {
            height: 200px;
            background: transparent;
          }
          
          .page-heading-content h1 {
            font-size: 24px !important;
            line-height: 1.2;
          }
          
          .page-subtitle {
            font-size: 14px;
            padding: 0 15px;
            margin-top: 10px;
          }
          
          .page-heading-container {
            margin-bottom: 5px;
          }
          
          .page-container {
            padding: 6px;
            margin-top: 2px;
            min-height: 120px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            backdrop-filter: blur(10px);
          }
          
          .responsive-nav-button {
            padding: 6px 12px;
            font-size: 13px;
            min-height: 40px;
          }
          
          .nav-button-text {
            font-size: 13px;
          }
          
          .navigation-buttons {
            margin-top: 10px;
          }
          
          .gradient-text,
          .booking-gradient-text,
          .confirm-gradient-text {
            line-height: 1.1;
          }
        }
        
        @media (max-width: 480px) {
          /* Ensure desktop backgrounds are hidden */
          .desktop-bg {
            display: none !important;
          }
          
          .page-heading-bg {
            height: 180px;
            background: transparent;
          }
          
          .page-heading-content h1 {
            font-size: 22px !important;
            line-height: 1.1;
          }
          
          .page-subtitle {
            font-size: 13px;
            padding: 0 10px;
            margin-top: 8px;
          }
          
          .page-heading-container {
            margin-bottom: 3px;
          }
          
          .page-container {
            padding: 4px;
            margin-top: 1px;
            min-height: 100px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            backdrop-filter: blur(10px);
          }
          
          .responsive-nav-button {
            padding: 5px 10px;
            font-size: 12px;
            min-height: 36px;
          }
          
          .nav-button-text {
            font-size: 12px;
          }
          
          .navigation-buttons {
            margin-top: 8px;
          }
        }
        
        @media (max-width: 360px) {
          .page-heading-bg {
            height: 160px;
          }
          
          .page-heading-content h1 {
            font-size: 20px !important;
            line-height: 1.0;
          }
          
          .page-subtitle {
            font-size: 12px;
            padding: 0 8px;
            margin-top: 6px;
          }
          
          .responsive-nav-button {
            padding: 4px 8px;
            font-size: 11px;
            min-height: 32px;
          }
          
          .nav-button-text {
            font-size: 11px;
          }
          
          .navigation-buttons {
            margin-top: 6px;
          }
        }
      `}</style>
    </section>
  );
};

export default Index;
