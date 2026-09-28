import React, { useState, useEffect } from "react";
import { useSelector } from "react-redux";
import PersonIcon from '@mui/icons-material/Person';
import Typography from '@mui/material/Typography';

const Counter = ({ name, value, minValue, onCounterChange, maxValue, disabled = false, ageDescription }) => {
  return (
    <>
      <div className="row y-gap-10 justify-between items-center">
        <div className="col-auto">
          <div className="text-15 lh-12 fw-500">{name}</div>
          {ageDescription && (
            <div className="text-14 lh-12 text-light-1 mt-5">{ageDescription}</div>
          )}
        </div>
        {/* End .col-auto */}
        <div className="col-auto">
          <div className="d-flex items-center js-counter">
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down"
              onClick={() => onCounterChange(name, Math.max(value - 1, minValue))}
              disabled={value <= minValue || disabled}
            >
              <i className="icon-minus text-12" />
            </button>
            <div className="flex-center size-20 ml-15 mr-15">
              <div className="text-15 js-count">{value}</div>
            </div>
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up"
              onClick={() => onCounterChange(name, value + 1)}
              disabled={value >= maxValue || disabled}
            >
              <i className="icon-plus text-12" />
            </button>
          </div>
        </div>
        {/* End .col-auto */}
      </div>
      {/* End .row */}
      <div className="border-top-light mt-24 mb-24" />
    </>
  );
};

const GuestSearch = ({ setGuestCounts }) => {
  // Fetch searchParams and attractionDetails from Redux state
  const searchParams = useSelector((state) => state.attractions.searchParams);
  const attractionDetails = useSelector(
    (state) => state.attractions.attractionDetails || {}
  );
  
  const [isOpen, setIsOpen] = useState(false);
  
  // Get age limits from attractionDetails or use defaults
  const childMaxAge = attractionDetails.child_max_age || 17;
  const seniorMinAge = attractionDetails.senior_min_age || 60;

  // Store the original default values as maximum limits
  const defaultAdults = searchParams?.adults > 0 ? searchParams.adults : 1;
  const defaultChildren = searchParams?.children || 0;

  // Initialize guestCounts with the correct values from searchParams
  const [guestCounts, setLocalGuestCounts] = useState({
    Adults: defaultAdults,
    Children: defaultChildren,
    Seniors: 0,
  });

  // Track the maximum allowed values (the original ones from searchParams)
  const [maxValues, setMaxValues] = useState({
    Adults: defaultAdults,
    Children: defaultChildren,
    Seniors: defaultAdults, // Seniors can be at most the original adult count
  });

  // Track the original total adult count to maintain balance between adults and seniors
  const [originalAdultCount, setOriginalAdultCount] = useState(defaultAdults);

  // Use effect to update guestCounts when searchParams changes
  useEffect(() => {
    const newDefaultAdults = searchParams?.adults > 0 ? searchParams.adults : 1;
    const newDefaultChildren = searchParams?.children || 0;

    setLocalGuestCounts({
      Adults: newDefaultAdults,
      Children: newDefaultChildren,
      Seniors: 0,
    });

    setMaxValues({
      Adults: newDefaultAdults,
      Children: newDefaultChildren,
      Seniors: newDefaultAdults, // Seniors can be at most the original adult count
    });

    setOriginalAdultCount(newDefaultAdults);
  }, [searchParams]);

  useEffect(() => {
    // Update parent component's state when local state changes
    setGuestCounts({
      Adults: guestCounts.Adults,
      Children: guestCounts.Children,
      Seniors: guestCounts.Seniors
    });
  }, [guestCounts, setGuestCounts]);

  const handleCounterChange = (name, value) => {
    // Special logic for Seniors to decrease Adults when Seniors increase
    if (name === "Seniors") {
      const currentSeniors = guestCounts.Seniors;
      const currentAdults = guestCounts.Adults;
      
      // Calculate the change in seniors
      const seniorDiff = value - currentSeniors;
      
      // Calculate new adult value based on senior change
      let newAdultValue = currentAdults - seniorDiff;
      
      // Ensure adults don't go below 0
      if (newAdultValue < 0) {
        newAdultValue = 0;
        // Recalculate seniors based on available adults
        value = currentSeniors + currentAdults;
      }
      
      // Ensure we don't exceed original adult count
      if (newAdultValue + value > originalAdultCount) {
        value = originalAdultCount - newAdultValue;
      }
      
      // Update both seniors and adults
      setLocalGuestCounts(prev => ({
        ...prev,
        Seniors: value,
        Adults: newAdultValue
      }));
      
      return;
    }
    
    // For Adults, ensure we respect the senior count
    if (name === "Adults") {
      // Calculate maximum adults based on seniors and original total
      const maxAdults = originalAdultCount - guestCounts.Seniors;
      if (value > maxAdults) {
        value = maxAdults;
      }
      
      // For manual adjustment, Adults can't go below 1
      if (value < 1) {
        value = 1;
      }
    }
    
    // For Children
    if (name === "Children") {
      // Ensure values never exceed the original maximum
      if (value > maxValues[name]) {
        value = maxValues[name];
      }
      
      // Ensure children never goes below 0
      if (value < 0) {
        value = 0;
      }
    }
    
    setLocalGuestCounts(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const toggleDropdown = () => {
    setIsOpen(!isOpen);
  };

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      const dropdown = document.getElementById('guest-dropdown-container');
      if (dropdown && !dropdown.contains(event.target)) {
        setIsOpen(false);
      }
    };

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isOpen]);

  // Create dynamic age descriptions based on attractionDetails
  const childrenAgeDescription = `Ages 0 - ${childMaxAge}`;
  const seniorsAgeDescription = `Ages ${seniorMinAge}+`;

  return (
    <div className="searchMenu-guests px-20 py-10 border-light rounded-4" id="guest-dropdown-container">
      <h4 className="text-15 fw-500 ls-2 lh-16">Number of travelers</h4>
      <div 
        style={{ 
          display: "flex", 
          alignItems: "center",
          backgroundColor: "rgba(255, 255, 255, 0.9)",
          borderRadius: "10px",
          boxShadow: "0 4px 10px rgba(0, 0, 0, 0.05)",
          padding: "15px 14px",
          transition: "all 0.3s ease",
          cursor: "pointer"
        }}
        onClick={toggleDropdown}
      >
        <PersonIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
        <Typography sx={{ fontWeight: 500 }}>
          <span className="js-count-adult">{guestCounts.Adults + guestCounts.Seniors}</span> adults 
          {guestCounts.Seniors > 0 && ` (incl. ${guestCounts.Seniors} seniors)`} - {" "}
          <span className="js-count-child">{guestCounts.Children}</span> children
        </Typography>
      </div>

      {isOpen && (
        <div className="shadow-2 dropdown-menu min-width-400" style={{ display: 'block', position: 'absolute', zIndex: 1000, marginTop: '10px', backgroundColor: 'white', borderRadius: '8px' }}>
          <div className="bg-white px-30 py-30 rounded-4 counter-box">
            <Counter
              name="Adults"
              value={guestCounts.Adults}
              minValue={1}
              maxValue={originalAdultCount - guestCounts.Seniors}
              onCounterChange={handleCounterChange}
             ageDescription={`Ages ${parseInt(childMaxAge) + 1} - ${parseInt(seniorMinAge) - 1}`}
            />
            <Counter
              name="Seniors"
              value={guestCounts.Seniors}
              minValue={0}
              maxValue={originalAdultCount}
              onCounterChange={handleCounterChange}
              ageDescription={seniorsAgeDescription}
            />
            <Counter
              name="Children"
              value={guestCounts.Children}
              minValue={0}
              maxValue={maxValues.Children}
              onCounterChange={handleCounterChange}
              ageDescription={childrenAgeDescription}
            />
          </div>
        </div>
      )}
    </div>
  );
};

export default GuestSearch;
