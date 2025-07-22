import React, { useState } from "react";
import { useDispatch } from "react-redux";
import MainFilterSearchBox from "./MainFilterSearchBox";
import PreferredHotelsDropdown from "./PreferredHotelsDropdown";
import { setPreferredHotels } from "../../../slice/common/BookingSlice";

const EnquirySearchWithPreferences = ({ onNext }) => {
  const dispatch = useDispatch();
  const [selectedHotels, setSelectedHotels] = useState([]);

  // Handle when hotels are selected from dropdown
  const handleHotelSelect = (hotels) => {
    setSelectedHotels(hotels);
    // Also update the Redux store
    dispatch(setPreferredHotels(hotels));
  };

  // Handle moving to next step with all data
  const handleNext = () => {
    // Ensure selected hotels are in Redux before moving to next step
    dispatch(setPreferredHotels(selectedHotels));
    
    // Call the parent component's onNext function
    if (onNext) {
      onNext();
    }
  };

  return (
    <div className="enquiry-search-container">
      {/* Main Search Box Component */}
      <MainFilterSearchBox onNext={handleNext} />
      
      {/* Preferred Hotels Dropdown */}
      <div className="preferred-hotels-wrapper mt-20 container">
        <div className="row justify-content-center">
          <div className="col-lg-9">
            <PreferredHotelsDropdown onSelect={handleHotelSelect} />
          </div>
        </div>
      </div>
      
      <style jsx>{`
        .enquiry-search-container {
          display: flex;
          flex-direction: column;
          width: 100%;
        }
        
        .preferred-hotels-wrapper {
          margin-bottom: 30px;
        }
      `}</style>
    </div>
  );
};

export default EnquirySearchWithPreferences; 