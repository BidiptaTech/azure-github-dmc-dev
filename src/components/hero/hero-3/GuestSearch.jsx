import React, { useState, useRef, useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import MaleIcon from '@mui/icons-material/Male';
import FemaleIcon from '@mui/icons-material/Female';
import BabyChangingStationIcon from '@mui/icons-material/BabyChangingStation';
import * as commonActions from "../../../slice/common/commonSlice";

const counters = [
  { name: "Adults", defaultValue: 1 },
  { name: "Children", defaultValue: 0 },
  { name: "Infants", defaultValue: 0 },
];

const Counter = ({ 
  name, 
  defaultValue, 
  onCounterChange,
  ages = [], 
  onAgeChange,
  totalAdults = 1,
  maleCount = 0,
  femaleCount = 0,
  onGenderCountChange
}) => {
  const [count, setCount] = useState(defaultValue);
  
  // Ensure count stays in sync with defaultValue from props
  useEffect(() => {
    setCount(defaultValue);
  }, [defaultValue]);

  const incrementCount = () => {
    // For Adults, prevent going above 10
    if (name === "Adults" && count >= 50) return;
    // For Children, prevent going above 10
    if (name === "Children" && count >= 20) return;
    // For Infants, prevent going above 5
    if (name === "Infants" && count >= 5) return;

    const newCount = count + 1;
    setCount(newCount);
    
    // Special handling for Adults - check if we need to adjust gender counts
    if (name === "Adults") {
      const currentMale = maleCount || 0;
      const currentFemale = femaleCount || 0;
      const totalGender = currentMale + currentFemale;
      
      // If no gender has been selected yet, automatically set male count equal to new adult count
      if (totalGender === 0) {
        onGenderCountChange("Male", newCount, null);
      }
      // If gender total equals current count, we need to keep them in sync
      // This allows incrementing Adults without breaking gender validation
      else if (totalGender === count && totalGender > 0) {
        // Maintain the same ratio
        const maleRatio = currentMale / count;
        // Distribute the new count proportionally
        const newMale = Math.round(newCount * maleRatio);
        const newFemale = newCount - newMale;
        onGenderCountChange("Male", newMale, null);
      }
    }
    
    onCounterChange(name, newCount);
  };
  
  const decrementCount = () => {
    // For Adults, prevent going below 1
    if (name === "Adults" && count <= 1) return;
    
    // For other categories, prevent going below 0
    if (count > 0) {
      const newCount = count - 1;
      setCount(newCount);
      
      // Special handling for Adults - check if we need to adjust gender counts
      if (name === "Adults") {
        const currentMale = maleCount || 0;
        const currentFemale = femaleCount || 0;
        const totalGender = currentMale + currentFemale;
        
        // If gender total equals current count, we need to keep them in sync
        // This ensures we don't have more gender selections than total adults
        if (totalGender === count && totalGender > 0) {
          // Maintain the same ratio
          const maleRatio = currentMale / count;
          // Distribute the new count proportionally
          const newMale = Math.round(newCount * maleRatio);
          const newFemale = newCount - newMale;
          onGenderCountChange("Male", newMale, null);
        }
        
        // If reducing adults would create an invalid state where 
        // gender count > adults count, adjust the genders
        if (totalGender > newCount && totalGender > 0) {
          // Maintain the same ratio but scale down
          const maleRatio = currentMale / totalGender;
          const newMale = Math.round(newCount * maleRatio);
          const newFemale = newCount - newMale;
          onGenderCountChange("Male", newMale, null);
        }
      }
      
      onCounterChange(name, newCount);
    }
  };

  const incrementGenderCount = (gender) => {
    if (gender === "Male") {
      // When incrementing male, automatically adjust female to maintain total
      onGenderCountChange("Male", maleCount + 1, "autoBalance");
    } else {
      // When incrementing female, automatically adjust male to maintain total
      onGenderCountChange("Female", femaleCount + 1, "autoBalance");
    }
  };

  const decrementGenderCount = (gender) => {
    if (gender === "Male" && maleCount > 0) {
      // When decrementing male, automatically adjust female
      onGenderCountChange("Male", maleCount - 1, "autoBalance");
    } else if (gender === "Female" && femaleCount > 0) {
      // When decrementing female, automatically adjust male
      onGenderCountChange("Female", femaleCount - 1, "autoBalance");
    }
  };

  // Determine appropriate icon
  let icon = <PersonIcon />;
  if (name === "Children") icon = <ChildCareIcon />;
  if (name === "Infants") icon = <BabyChangingStationIcon />;

  return (
    <>
      <div className="row y-gap-10 justify-between items-center">
        <div className="col-auto">
          <div className="text-15 lh-12 fw-500">
            {icon} {name}
          </div>
          {name === "Children" && (
            <div className="text-14 lh-12 text-light-1 mt-5">Ages 1 - 17</div>
          )}
          {name === "Infants" && (
            <div className="text-14 lh-12 text-light-1 mt-5">Younger than 1 year old</div>
          )}
        </div>
        {/* End .col-auto */}
        <div className="col-auto">
          <div className="d-flex items-center js-counter">
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down"
              onClick={decrementCount}
            >
              <i className="icon-minus text-12" />
            </button>
            {/* decrement button */}
            <div className="flex-center size-20 ml-15 mr-15">
              <div className="text-15 js-count">{count}</div>
            </div>
            {/* counter text  */}
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up"
              onClick={incrementCount}
            >
              <i className="icon-plus text-12" />
            </button>
            {/* increment button */}
          </div>
        </div>
        {/* End .col-auto */}
      </div>
      {/* End .row */}

      {/* Gender Selection for Adults */}
      {name === "Adults" && count > 0 && (
        <div className="gender-counters mt-15">
          {maleCount === 0 && femaleCount === 0 && (
            <div className="gender-prompt mb-10">
              Please select gender breakdown
            </div>
          )}
          <div className="gender-counter-item">
            <div className="gender-counter-header">
              <MaleIcon className="custom-icon male-icon" />
              <span className="gender-label">Male</span>
            </div>
            <div className="d-flex items-center js-counter">
              <button
                className="button -outline-blue-1 text-blue-1 size-30 rounded-4"
                onClick={() => decrementGenderCount("Male")}
                disabled={maleCount === 0}
              >
                <i className="icon-minus text-10" />
              </button>
              <div className="flex-center size-20 ml-10 mr-10">
                <div className="text-15 js-count">{maleCount}</div>
              </div>
              <button
                className="button -outline-blue-1 text-blue-1 size-30 rounded-4"
                onClick={() => incrementGenderCount("Male")}
                disabled={maleCount === totalAdults}
              >
                <i className="icon-plus text-10" />
              </button>
            </div>
          </div>

          <div className="gender-counter-item mt-10">
            <div className="gender-counter-header">
              <FemaleIcon className="custom-icon female-icon" />
              <span className="gender-label">Female</span>
            </div>
            <div className="d-flex items-center js-counter">
              <button
                className="button -outline-blue-1 text-blue-1 size-30 rounded-4"
                onClick={() => decrementGenderCount("Female")}
                disabled={femaleCount === 0}
              >
                <i className="icon-minus text-10" />
              </button>
              <div className="flex-center size-20 ml-10 mr-10">
                <div className="text-15 js-count">{femaleCount}</div>
              </div>
              <button
                className="button -outline-blue-1 text-blue-1 size-30 rounded-4"
                onClick={() => incrementGenderCount("Female")}
                disabled={femaleCount === totalAdults}
              >
                <i className="icon-plus text-10" />
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Age Selection for Children */}
      {name === "Children" && count > 0 && (
        <div
          style={{
            maxHeight: "150px",
            overflowY: "auto",
            overflowX: "hidden",
            width: "100%",
            paddingRight: "10px",
            marginTop: "15px",
          }}
          className="custom-scroll"
          id="child-age-container"
        >
          {Array.from({ length: count }).map((_, i) => (
            <div
              key={i}
              className="guest-select-item"
              id={`child-age-item-${i}`}
            >
              <div className="col-auto">
                <div className="text-15 lh-12 fw-500">
                  <span className="icon-wrapper">
                    <ChildCareIcon className="custom-icon" />
                  </span>
                  Child {i + 1} Age
                </div>
              </div>
              <div className="col-auto">
                <select
                  className="custom-select"
                  value={ages[i] || ""}
                  onChange={(e) => {
                    onAgeChange(i, e.target.value);
                    // Auto scroll to next item after selection
                    if (i < count - 1) {
                      setTimeout(() => {
                        const nextItem = document.getElementById(`child-age-item-${i + 1}`);
                        if (nextItem) {
                          nextItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                      }, 100);
                    }
                  }}
                >
                  <option value="">Select Age</option>
                  {[...Array(17)].map((_, age) => (
                    <option key={age + 1} value={age + 1}>
                      {age + 1} {age === 0 ? "year" : "years"}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          ))}
        </div>
      )}

      <div className="border-top-light mt-24 mb-24" />
    </>
  );
};

const GuestSearch = ({ onGuestChange, guestCounts: propGuestCounts }) => {
  const dispatch = useDispatch();
  const reduxGuestCounts = useSelector(commonActions.selectGuestCounts);
  const shouldReset = useSelector(commonActions.selectShouldResetGuestSearch);
  
  const [isDropdownVisible, setIsDropdownVisible] = useState(false);
  const [guestCounts, setGuestCounts] = useState({
    Adults: propGuestCounts?.Adults || reduxGuestCounts?.Adults || 1,
    Children: propGuestCounts?.Children || reduxGuestCounts?.Children || 0,
    Infants: propGuestCounts?.Infants || reduxGuestCounts?.Infants || 0,
    maleCount: propGuestCounts?.maleCount || reduxGuestCounts?.maleCount || 0,
    femaleCount: propGuestCounts?.femaleCount || reduxGuestCounts?.femaleCount || 0,
    ages: propGuestCounts?.ages || reduxGuestCounts?.ages || []
  });
  const dropdownRef = useRef(null);
  const buttonRef = useRef(null);

  // Update local state when props change
  useEffect(() => {
    if (propGuestCounts) {
      setGuestCounts({
        Adults: propGuestCounts.Adults !== undefined ? propGuestCounts.Adults : guestCounts.Adults,
        Children: propGuestCounts.Children !== undefined ? propGuestCounts.Children : guestCounts.Children,
        Infants: propGuestCounts.Infants !== undefined ? propGuestCounts.Infants : guestCounts.Infants,
        maleCount: propGuestCounts.maleCount !== undefined ? propGuestCounts.maleCount : guestCounts.maleCount,
        femaleCount: propGuestCounts.femaleCount !== undefined ? propGuestCounts.femaleCount : guestCounts.femaleCount,
        ages: propGuestCounts.ages || guestCounts.ages || []
      });
    }
  }, [propGuestCounts]);

  // Handle reset from Redux
  useEffect(() => {
    if (shouldReset) {
      const defaultCounts = {
        Adults: 1,
        Children: 0,
        Infants: 0,
        maleCount: 0,
        femaleCount: 0,
        ages: []
      };
      setGuestCounts(defaultCounts);
      dispatch(commonActions.setGuestCounts(defaultCounts));
      dispatch(commonActions.clearGuestSearchReset());
      
      // Notify parent component of the reset
      if (onGuestChange) {
        onGuestChange(defaultCounts);
      }
    }
  }, [shouldReset, dispatch, onGuestChange]);

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (
        dropdownRef.current && 
        !dropdownRef.current.contains(event.target) && 
        buttonRef.current && 
        !buttonRef.current.contains(event.target)
      ) {
        setIsDropdownVisible(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const handleCounterChange = (name, value) => {
    const updatedGuestCounts = { ...guestCounts, [name]: value };
                 
    if (name === "Adults") {
      // Do NOT adjust male/female counts when changing adults
      // Leave them as they are - user must explicitly set these
    }

    if (name === "Children") {
      const currentAges = [...(guestCounts.ages || [])];
      if (value > currentAges.length) {
        // Add empty ages for new children
        const newAges = [...currentAges];
        for (let i = currentAges.length; i < value; i++) {
          newAges.push("");
        }
        updatedGuestCounts.ages = newAges;
      } else {
        // Remove ages for removed children
        updatedGuestCounts.ages = currentAges.slice(0, value);
      }
    }

    setGuestCounts(updatedGuestCounts);
    
    // Call the onGuestChange prop function
    if (onGuestChange) {
      onGuestChange(updatedGuestCounts);
    }
  };

  const handleGenderCountChange = (gender, count, action = null) => {
    const totalAdults = guestCounts.Adults || 1;
    
    let newMaleCount = guestCounts.maleCount || 0;
    let newFemaleCount = guestCounts.femaleCount || 0;

    if (action === "autoBalance") {
      if (gender === "Male") {
        // Set the new male count
        newMaleCount = Math.min(Math.max(0, count), totalAdults);
        // Female is always the remainder to make exactly totalAdults
        newFemaleCount = totalAdults - newMaleCount;
      } else {
        // Set the new female count
        newFemaleCount = Math.min(Math.max(0, count), totalAdults);
        // Male is always the remainder to make exactly totalAdults
        newMaleCount = totalAdults - newFemaleCount;
      }
    }

    // Ensure gender counts don't exceed total Adults
    if (newMaleCount + newFemaleCount > totalAdults) {
      // If they would exceed, scale them back proportionally
      const ratio = totalAdults / (newMaleCount + newFemaleCount);
      newMaleCount = Math.floor(newMaleCount * ratio);
      newFemaleCount = totalAdults - newMaleCount; // Ensure they add up exactly
    }

    const updatedGuestCounts = {
      ...guestCounts,
      maleCount: newMaleCount,
      femaleCount: newFemaleCount
    };

    setGuestCounts(updatedGuestCounts);
    
    // Call the onGuestChange prop function
    if (onGuestChange) {
      onGuestChange(updatedGuestCounts);
    }
  };

  const handleAgeChange = (index, selectedAge) => {
    const updatedAges = [...(guestCounts.ages || [])];
    updatedAges[index] = selectedAge;
    
    const updatedGuestCounts = {
      ...guestCounts,
      ages: updatedAges
    };

    setGuestCounts(updatedGuestCounts);
    
    // Call the onGuestChange prop function
    if (onGuestChange) {
      onGuestChange(updatedGuestCounts);
    }
  };

  const toggleDropdown = () => {
    setIsDropdownVisible(!isDropdownVisible);
  };

  // Get display text for guests
  const getGuestsDisplayText = () => {
    const adultText = `${guestCounts.Adults} ${guestCounts.Adults === 1 ? 'adult' : 'adults'}`;
    const maleText = `${guestCounts.maleCount || 0}M`;
    const femaleText = `${guestCounts.femaleCount || 0}F`;
    const childText = guestCounts.Children > 0 ? `${guestCounts.Children} ${guestCounts.Children === 1 ? 'child' : 'children'}` : '';
    const infantText = guestCounts.Infants > 0 ? `${guestCounts.Infants} ${guestCounts.Infants === 1 ? 'infant' : 'infants'}` : '';
    
    let parts = [];
    
    if (guestCounts.maleCount === 0 && guestCounts.femaleCount === 0) {
      parts.push(`${adultText} (select gender)`);
    } else {
      parts.push(`${adultText} (${maleText}, ${femaleText})`);
    }
    
    if (childText) parts.push(childText);
    if (infantText) parts.push(infantText);
    
    return parts.join(' - ');
  };

  return (
    <div className="searchMenu-guests px-30 lg:py-20 lg:px-0 js-form-dd js-form-counters position-relative">
      <div
        ref={buttonRef}
        onClick={toggleDropdown}
        style={{ cursor: 'pointer' }}
      >
        <h4 className="text-15 fw-500 ls-2 lh-16">Guest</h4>
        <div className="text-15 text-light-1 ls-2 lh-16">
          {getGuestsDisplayText()}
        </div>
      </div>
      {/* End guest */}

      <div 
        className={`shadow-2 dropdown-menu min-width-400 ${isDropdownVisible ? 'show' : ''}`}
        style={{
          position: 'absolute',
          zIndex: 9999,
          display: isDropdownVisible ? 'block' : 'none',
          top: '100%',
          left: 0,
          width: '100%',
          maxWidth: '400px'
        }}
        ref={dropdownRef}
      >
        <div className="bg-white px-30 py-30 rounded-4 counter-box">
          {counters.map((counter) => (
            <Counter
              key={counter.name}
              name={counter.name}
              defaultValue={guestCounts[counter.name] || counter.defaultValue}
              onCounterChange={handleCounterChange}
              onGenderCountChange={handleGenderCountChange}
              maleCount={guestCounts.maleCount || 0}
              femaleCount={guestCounts.femaleCount || 0}
              totalAdults={guestCounts.Adults}
              ages={guestCounts.ages || []}
              onAgeChange={handleAgeChange}
            />
          ))}
          
          {/* <div className="d-flex justify-content-end">
            <button 
              className="button -dark-1 py-15 px-35 h-60 col-auto rounded-4 bg-blue-1 text-white"
              onClick={() => setIsDropdownVisible(false)}
            >
              Done
            </button>
          </div> */}
        </div>
      </div>

      <style jsx>{`
        .guest-select-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 12px 15px;
          margin-bottom: 8px;
          background: rgba(53, 84, 209, 0.05);
          border-radius: 10px;
          transition: all 0.3s ease;
        }

        .guest-select-item:hover {
          background: rgba(53, 84, 209, 0.1);
          transform: translateX(5px);
        }

        .icon-wrapper {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          width: 24px;
          height: 24px;
          background: rgba(53, 84, 209, 0.1);
          border-radius: 50%;
          margin-right: 10px;
          transition: all 0.3s ease;
        }

        .guest-select-item:hover .icon-wrapper {
          background: rgba(53, 84, 209, 0.2);
          transform: scale(1.1);
        }

        :global(.custom-icon) {
          font-size: 16px;
          color: #3554d1;
        }
        
        :global(.male-icon) {
          color: #2563eb;
        }
        
        :global(.female-icon) {
          color: #db2777;
        }

        .custom-select {
          min-width: 140px;
          padding: 8px 12px;
          border: 2px solid #e9ecef;
          border-radius: 8px;
          background: white;
          font-size: 14px;
          color: #3554d1;
          cursor: pointer;
          transition: all 0.3s ease;
          appearance: none;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%233554d1' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 12px center;
          padding-right: 30px;
        }

        .custom-select:hover {
          border-color: #3554d1;
          box-shadow: 0 2px 4px rgba(53, 84, 209, 0.1);
        }

        .custom-select:focus {
          outline: none;
          border-color: #3554d1;
          box-shadow: 0 0 0 3px rgba(53, 84, 209, 0.2);
        }

        .gender-counters {
          background: rgba(53, 84, 209, 0.05);
          border-radius: 10px;
          padding: 15px;
        }
        
        .gender-counter-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
        
        .gender-counter-header {
          display: flex;
          align-items: center;
        }
        
        .gender-label {
          margin-left: 10px;
          font-weight: 500;
        }
        
        .gender-prompt {
          text-align: center;
          color: #e53e3e;
          font-size: 14px;
          font-weight: 500;
          padding: 5px;
          background: rgba(229, 62, 62, 0.08);
          border-radius: 5px;
          animation: pulse 1.5s infinite ease-in-out;
        }
        
        @keyframes pulse {
          0%, 100% { opacity: 0.8; }
          50% { opacity: 1; }
        }
        
        button:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }
        
        .custom-scroll {
          scrollbar-width: thin;
          scrollbar-color: #3554d1 #f0f2f5;
        }
        
        .custom-scroll::-webkit-scrollbar {
          width: 6px;
        }
        
        .custom-scroll::-webkit-scrollbar-track {
          background: #f0f2f5;
          border-radius: 10px;
        }
        
        .custom-scroll::-webkit-scrollbar-thumb {
          background: #3554d1;
          border-radius: 10px;
        }

        .mr-10 {
          margin-right: 10px;
        }
      `}</style>
    </div>
  );
};
export default GuestSearch;
