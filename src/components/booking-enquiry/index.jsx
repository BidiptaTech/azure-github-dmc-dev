import React, { useState, useEffect } from "react";
import { useSelector } from "react-redux";
import Hero1 from "@/components/hero/hero-1";
import MainFilterSearchBox from "../hero/hero-2/MainFilterSearchBox";
import BookingEnquiries from "./BookingEnquiries";
import ConfirmDetails from "./ConfirmDetails";
import ThankYouModal from "./ThankYouModal";

const Index = () => {
  // Get enquiry state from Redux
  const enquiryState = useSelector((state) => state.enquiry);
  const { enquiryId, multiEnqId, tourId, id } = enquiryState;
  
  // Scroll to top when component mounts
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);
  
  const [bookingOptions, setBookingOptions] = useState({
    hotel: false,
    entryExitPort: false,
    attraction: false,
    // localTour: false,
    // tourGuide: false,
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
      // localTour: false,
      // tourGuide: false,
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

  // Render the current page based on state (non-search steps)
  const renderCurrentPage = () => {
    switch (currentPage) {
      case "bookingEnquiries":
        return (
          <div className="page-container">
            <div className="container">
            <BookingEnquiries
              bookingOptions={bookingOptions}
              setBookingOptions={setBookingOptions}
              onNext={goToConfirmDetails}
              onBack={goToSearch}
            />
            <div className="navigation-buttons mt-15 d-flex justify-between">
              <button 
                className="button -md -blue-1 bg-blue-1-05 text-white border-white responsive-nav-button" 
                onClick={goToSearch}
              >
                <i className="icon-arrow-left text-20 mr-10"></i>
                <span className="nav-button-text">Back to Search</span>
              </button>
            </div>
            </div>
          </div>
        );

      case "confirmDetails":
        return (
          <div className="page-container">
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

  // Search step — same Hero1 trip-card UI as Plan a trip
  if (currentPage === "search") {
    return (
      <div className="partner-plan-hero">
        <Hero1
          pageKicker="Quotations & enquiries"
          pageTitle="Ask your DMC for a tailored quote."
          pageSubtitle="Share destinations, dates and travellers — then choose hotels, transfers and experiences."
          kicker="Your client enquiry"
          title="Where are we taking them?"
          subtitle="Find stays, add experiences and build a complete trip with your DMC."
          footerNote="Send enquiries for hotels, attractions, transfers, guides & more"
          searchSlot={<MainFilterSearchBox onNext={goToBookingEnquiries} />}
        />
        <ThankYouModal
          open={showThankYouModal}
          onClose={handleCloseModal}
          delay={7}
        />
      </div>
    );
  }

  return (
    <section
      className="enquiry-corp-page"
      style={{
        position: "relative",
        minHeight: "100%",
        background: "transparent",
        paddingTop: 0,
        paddingBottom: 8,
        overflow: "visible",
      }}
    >
      {renderCurrentPage()}

      <ThankYouModal
        open={showThankYouModal}
        onClose={handleCloseModal}
        delay={7}
      />

      <style>{`
        @media (max-width: 768px) {
          .enquiry-corp-page {
            padding-top: 100px !important;
          }
        }
      `}</style>
      <style jsx>{`
        .page-container {
          padding: 0;
          min-height: 150px;
          margin-top: 0;
        }
        .navigation-buttons {
          display: flex;
          justify-content: flex-start;
          margin-top: 16px;
          padding: 0 0 16px;
        }
        .responsive-nav-button {
          transition: all 0.2s ease;
          min-height: 44px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #0f2744 !important;
          border: 1px solid #0f2744 !important;
          color: #fff !important;
          border-radius: 8px;
        }
        .nav-button-text {
          white-space: nowrap;
        }
        @media (max-width: 768px) {
          .responsive-nav-button {
            padding: 8px 14px;
            font-size: 14px;
          }
        }
      `}</style>
    </section>
  );
};

export default Index;
