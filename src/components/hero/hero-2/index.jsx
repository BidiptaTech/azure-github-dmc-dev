import { useSelector, useDispatch } from "react-redux";
import { addCurrentTab } from "../../../features/hero/findPlaceSlice";
import MainFilterSearchBox from "./MainFilterSearchBox";
import BookingEnquiries from "./BookingEnquiries";
import ConfirmDetails from "./ConfirmDetails";
import ThankYouModal from "./ThankYouModal";
import { useState, useEffect } from "react";
import { Box } from "@mui/material";

const Index = () => {
  const { tabs, currentTab } = useSelector((state) => state.hero) || {};
  const dispatch = useDispatch();
  const [bookingOptions, setBookingOptions] = useState({
    hotel: false,
    entryExitPort: false,
    attraction: false,
    localTour: false,
    tourGuide: false,
    restaurant: false
  });
  
  // Add state for modal
  const [showThankYouModal, setShowThankYouModal] = useState(false);
  
  // Use currentPage to track which component to display
  const [currentPage, setCurrentPage] = useState("search"); // Options: "search", "bookingEnquiries", "confirmDetails"

  // Check if we have a submitted enquiry in localStorage on component mount
  useEffect(() => {
    const enquirySubmitted = localStorage.getItem('enquirySubmitted');
    
    if (enquirySubmitted === 'true') {
      setShowThankYouModal(true);
      // Clear the flag when showing the modal to ensure it's a one-time display
      localStorage.removeItem('enquirySubmitted');
    } else {
      // If we're returning to the enquiry page, make sure modal won't show
      localStorage.removeItem('enquirySubmitted');
      localStorage.removeItem('enquiryData');
    }
  }, []);

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
  };

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
            <h3 className="text-22 text-white mb-5">Select Your Travel Services</h3>
            <p className="text-14 text-white opacity-80 mb-15">Customize your trip with hotels, transportation, attractions and more.</p>
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
            <h3 className="text-22 text-white mb-5">Review and Submit Your Itinerary</h3>
            <p className="text-14 text-white opacity-80 mb-15">Verify all your selected services before finalizing your booking.</p>
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
          <div className="text-center mb-10">
            <h1 className="z-2 text-50 lg:text-40 md:text-30 fw-600">
              <span className="gradient-text">Customize Your Travel Experience</span>
            </h1>
            <p className="z-2 text-white mt-5 mx-auto" style={{maxWidth: "600px"}}>
              Tailor your journey with premium services and exclusive options
            </p>
          </div>
        );
      case "confirmDetails":
        return (
          <div className="text-center mb-10">
            <h1 className="z-2 text-50 lg:text-40 md:text-30 fw-600">
              <span className="gradient-text">Finalize Your Perfect Itinerary</span>
            </h1>
            <p className="z-2 text-white mt-5 mx-auto" style={{maxWidth: "600px"}}>
              Review your selections and confirm your personalized travel plan
            </p>
          </div>
        );
      default:
        return null;
    }
  };

  return (
    <section className="masthead -type-2 z-2">
      {/* Background image only for search page */}
      {currentPage === "search" && (
        <div className="masthead__bg">
          <img alt="image" src="/img/masthead/4/bg.png" className="js-lazy" style={{width: "100%", height: "120%"}} />
        </div>
      )}
      <div className="container">
        <Box sx={{ py: 2 }}>
          <div className="masthead__content">
            <div className="row y-gap-5">
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
          background: rgba(255, 255, 255, 0.05);
          padding: 15px;
          border-radius: 12px;
          min-height: 200px;
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
      `}</style>
    </section>
  );
};

export default Index;
