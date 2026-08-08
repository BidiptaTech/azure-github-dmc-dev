import React, { useState, useEffect, useRef } from "react";
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import MaleIcon from '@mui/icons-material/Male';
import FemaleIcon from '@mui/icons-material/Female';
import BabyChangingStationIcon from '@mui/icons-material/BabyChangingStation';
import GroupIcon from '@mui/icons-material/Group';
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';

const Counter = ({
  name,
  defaultValue,
  onCounterChange,
  ages,
  onAgeChange,
  onGenderCountChange,
  maleCount = 0,
  femaleCount = 0,
  totalAdults = 1,
}) => {
  const [count, setCount] = useState(defaultValue);

  useEffect(() => {
    setCount(defaultValue);
  }, [defaultValue]);

  const incrementCount = () => {
    const newCount = count + 1;
    setCount(newCount);
    onCounterChange(name, newCount);
  };

  const decrementCount = () => {
    // For Adults, prevent going below 1
    if (name === "Adults" && count <= 1) {
      return;
    }
    // For other categories, prevent going below 0
    if (count > 0) {
      const newCount = count - 1;
      setCount(newCount);
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

  // Get the icon based on counter type
  const getCounterIcon = () => {
    switch(name) {
      case "Adults": return <PersonIcon className="custom-icon" />;
      case "Children": return <ChildCareIcon className="custom-icon" />;
      case "Infants": return <BabyChangingStationIcon className="custom-icon" />;
      default: return <GroupIcon className="custom-icon" />;
    }
  };

  return (
    <>
      <div className="row y-gap-10 justify-between items-center">
        <div className="col-auto">
          <div className="counter-label-container">
            {getCounterIcon()}
            <div className="counter-text-container">
              <div className="text-15 lh-12 fw-500">{name}</div>
              {name === "Children" && (
                <div className="text-14 lh-12 text-light-1 mt-5">Ages 1 - 17</div>
              )}
              {name === "Infants" && (
                <div className="text-14 lh-12 text-light-1 mt-5">Younger than 1 year old</div>
              )}
            </div>
          </div>
        </div>
        <div className="col-auto">
          <div className="d-flex items-center js-counter">
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down counter-btn"
              onClick={decrementCount}
              disabled={name === "Adults" ? count <= 1 : count <= 0}
            >
              <i className="icon-minus text-12" />
            </button>
            <div className="flex-center size-20 ml-15 mr-15">
              <div className="text-15 js-count fw-500">{count}</div>
            </div>
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up counter-btn"
              onClick={incrementCount}
            >
              <i className="icon-plus text-12" />
            </button>
          </div>
        </div>
      </div>

     
      {/* Gender Selection for Adults */}
      {name === "Adults" && count > 0 && (
        <div className="gender-counters mt-15">
          <div className="gender-counter-title">
            <GroupIcon className="custom-icon mr-10" />
            <span className="text-14 fw-500">Gender Distribution</span>
          </div>
          
          <div className="gender-counter-item">
            <div className="gender-counter-header">
              <MaleIcon className="custom-icon male-icon" />
              <span className="gender-label">Male</span>
            </div>
            <div className="d-flex items-center js-counter">
              <button
                className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down counter-btn"
                onClick={() => decrementGenderCount("Male")}
                disabled={maleCount === 0}
              >
                <i className="icon-minus text-12" />
              </button>
              <div className="flex-center size-20 ml-15 mr-15">
                <div className="text-15 js-count fw-500">{maleCount}</div>
              </div>
              <button
                className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up counter-btn"
                onClick={() => incrementGenderCount("Male")}
                disabled={maleCount === totalAdults}
              >
                <i className="icon-plus text-12" />
              </button>
            </div>
          </div>

          <div className="gender-counter-item mt-15">
            <div className="gender-counter-header">
              <FemaleIcon className="custom-icon female-icon" />
              <span className="gender-label">Female</span>
            </div>
            <div className="d-flex items-center js-counter">
              <button
                className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down counter-btn"
                onClick={() => decrementGenderCount("Female")}
                disabled={femaleCount === 0}
              >
                <i className="icon-minus text-12" />
              </button>
              <div className="flex-center size-20 ml-15 mr-15">
                <div className="text-15 js-count fw-500">{femaleCount}</div>
              </div>
              <button
                className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up counter-btn"
                onClick={() => incrementGenderCount("Female")}
                disabled={femaleCount === totalAdults}
              >
                <i className="icon-plus text-12" />
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Age Selection for Children */}
      {name === "Children" && count > 0 && (
        <div
          className="custom-scroll child-ages-container"
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

      <style jsx>{`
        .counter-label-container {
          display: flex;
          align-items: center;
        }
        
        .counter-text-container {
          margin-left: 10px;
        }
        
        .counter-btn {
          transition: all 0.3s ease;
          box-shadow: 0 3px 10px rgba(51, 102, 255, 0.1);
        }
        
        .counter-btn:hover:not(:disabled) {
          transform: translateY(-2px);
          box-shadow: 0 5px 15px rgba(51, 102, 255, 0.15);
        }
        
        .counter-btn:active:not(:disabled) {
          transform: translateY(0);
        }
        
        .guest-select-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 12px 15px;
          margin-bottom: 8px;
          background: rgba(53, 84, 209, 0.05);
          border-radius: 10px;
          transition: all 0.3s ease;
          border-left: 3px solid rgba(53, 84, 209, 0.3);
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

        .child-ages-container {
          max-height: 200px;
          overflow-y: auto;
          overflow-x: hidden;
          width: 100%;
          padding-right: 10px;
          margin-top: 15px;
        }

        .mr-10 {
          margin-right: 10px;
        }
        
        .gender-counters {
          background: rgba(53, 84, 209, 0.05);
          border-radius: 10px;
          padding: 15px;
        }
        
        .gender-counter-title {
          display: flex;
          align-items: center;
          margin-bottom: 15px;
          padding-bottom: 10px;
          border-bottom: 1px dashed rgba(53, 84, 209, 0.15);
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
        
        .gender-progress-container {
          width: 100%;
        }
        
        .gender-ratio-text {
          display: flex;
          justify-content: space-between;
          margin-bottom: 5px;
          font-size: 12px;
        }
        
        .ratio-male {
          color: #2563eb;
          font-weight: 500;
        }
        
        .ratio-female {
          color: #db2777;
          font-weight: 500;
        }
        
        .gender-progress-bar {
          height: 6px;
          background-color: rgba(219, 39, 119, 0.3);
          border-radius: 3px;
          overflow: hidden;
        }
        
        .gender-progress-male {
          height: 100%;
          background-color: rgba(37, 99, 235, 0.8);
          border-radius: 3px;
          transition: width 0.3s ease;
        }

        button:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }
      `}</style>

      <div className="border-top-light mt-24 mb-24" />
    </>
  );
};

const GuestSelector = ({ 
  initialAdult = 1, 
  initialChild = 0, 
  initialInfant = 0, 
  initialMale = 0, 
  initialFemale = 0,
  initialChildAges = [],
  onGuestChange 
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef(null);
  
  // Parse child ages if provided as string
  const parseChildAges = (childAges) => {
    if (typeof childAges === 'string' && childAges.trim()) {
      return childAges.split(',').map(age => age.trim());
    }
    return Array.isArray(childAges) ? childAges : [];
  };

  const [guestCounts, setGuestCounts] = useState({
    Adults: initialAdult,
    Children: initialChild,
    Infants: initialInfant,
    maleCount: initialMale,
    femaleCount: initialFemale,
    ages: parseChildAges(initialChildAges)
  });

  useEffect(() => {
    // Update with initial values if they change
    setGuestCounts({
      Adults: initialAdult,
      Children: initialChild,
      Infants: initialInfant,
      maleCount: initialMale,
      femaleCount: initialFemale,
      ages: parseChildAges(initialChildAges)
    });
  }, [initialAdult, initialChild, initialInfant, initialMale, initialFemale, initialChildAges]);

  // Handle clicks outside to close the dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };

    // Add event listener when dropdown is open
    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    
    // Clean up
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isOpen]);

  const handleCounterChange = (name, value) => {
    const updatedGuestCounts = { ...guestCounts, [name]: value };
                 
    if (name === "Adults") {
      // When changing adults, adjust the gender counts if they're already set
      const currentMaleCount = guestCounts.maleCount || 0;
      const currentFemaleCount = guestCounts.femaleCount || 0;
      const currentTotal = currentMaleCount + currentFemaleCount;
      
      // If we already have gender distribution, maintain the proportions
      if (currentTotal > 0) {
        const maleRatio = currentMaleCount / currentTotal;
        const femaleRatio = currentFemaleCount / currentTotal;
        
        updatedGuestCounts.maleCount = Math.round(value * maleRatio);
        updatedGuestCounts.femaleCount = value - updatedGuestCounts.maleCount; // Ensure they add up exactly
      } else {
        // Keep them at 0 if they were 0 before
        updatedGuestCounts.maleCount = 0;
        updatedGuestCounts.femaleCount = 0;
      }
    }

    if (name === "Children") {
      const newAges = [...(guestCounts.ages || [])];
      if (value > newAges.length) {
        newAges.push(...new Array(value - newAges.length).fill(""));
      } else {
        newAges.splice(value);
      }
      updatedGuestCounts.ages = newAges;
    }

    setGuestCounts(updatedGuestCounts);
    
    if (onGuestChange) {
      onGuestChange(updatedGuestCounts);
    }
  };

  const handleGenderCountChange = (gender, count, action = null) => {
    const updatedGuestCounts = { ...guestCounts };
    const totalAdults = guestCounts.Adults || 1;
    
    if (action === "autoBalance") {
      // Auto-balance mode: when increasing one gender, automatically set the other
      if (gender === "Male") {
        // Set the new male count
        updatedGuestCounts.maleCount = Math.min(count, totalAdults);
        // Female is always the remainder to make exactly totalAdults
        updatedGuestCounts.femaleCount = totalAdults - updatedGuestCounts.maleCount;
      } else {
        // Set the new female count
        updatedGuestCounts.femaleCount = Math.min(count, totalAdults);
        // Male is always the remainder to make exactly totalAdults
        updatedGuestCounts.maleCount = totalAdults - updatedGuestCounts.femaleCount;
      }
    }
    
    setGuestCounts(updatedGuestCounts);
    
    if (onGuestChange) {
      onGuestChange(updatedGuestCounts);
    }
  };

  const handleAgeChange = (index, selectedAge) => {
    const updatedAges = [...(guestCounts.ages || [])];
    updatedAges[index] = selectedAge;
    
    const updatedGuestCounts = { ...guestCounts, ages: updatedAges };
    setGuestCounts(updatedGuestCounts);
    
    if (onGuestChange) {
      onGuestChange(updatedGuestCounts);
    }
  };

  // Toggle dropdown on click
  const toggleDropdown = (e) => {
    setIsOpen(!isOpen);
  };

  // Close dropdown on double click
  const handleDoubleClick = () => {
    if (isOpen) {
      setIsOpen(false);
    }
  };

  // Generate guest summary text
  const getGuestSummary = () => {
    const parts = [];
    
    if (guestCounts.Adults) {
      parts.push(`${guestCounts.Adults} Adult${guestCounts.Adults > 1 ? 's' : ''}`);
    }
    
    if (guestCounts.Children) {
      parts.push(`${guestCounts.Children} Child${guestCounts.Children > 1 ? 'ren' : ''}`);
    }
    
    if (guestCounts.Infants) {
      parts.push(`${guestCounts.Infants} Infant${guestCounts.Infants > 1 ? 's' : ''}`);
    }
    
    return parts.join(', ');
  };

  return (
    <div 
      className="guest-selector-wrapper" 
      ref={dropdownRef}
      onDoubleClick={handleDoubleClick}
    >
      {/* Dropdown Trigger */}
      <div 
        className={`guest-selector-trigger ${isOpen ? 'active' : ''}`}
        onClick={toggleDropdown}
      >
        <div className="trigger-content">
          <div className="trigger-icon">
            <GroupIcon />
          </div>
          <div className="trigger-text">
            <div className="trigger-label">Guests</div>
            <div className="trigger-value">{getGuestSummary()}</div>
          </div>
        </div>
        <div className={`trigger-arrow ${isOpen ? 'active' : ''}`}>
          <KeyboardArrowDownIcon />
        </div>
      </div>
      
      {/* Dropdown Content */}
      {isOpen && (
        <div className="guest-dropdown-content">
          <div className="guest-info-header">
            <div className="d-flex align-items-center">
              <div className="info-icon-box">
                <GroupIcon className="info-icon" />
              </div>
              <h4 className="text-16 fw-500">Guest Information</h4>
            </div>
            
            <div className="guest-count-pills">
              <div className="guest-count-pill">
                <PersonIcon className="pill-icon" />
                <span>{guestCounts.Adults}</span>
              </div>
              <div className="guest-count-pill">
                <ChildCareIcon className="pill-icon" />
                <span>{guestCounts.Children}</span>
              </div>
              <div className="guest-count-pill">
                <BabyChangingStationIcon className="pill-icon" />
                <span>{guestCounts.Infants}</span>
              </div>
            </div>
          </div>
          
          <div className="guest-counter-box">
            <Counter
              name="Adults"
              defaultValue={guestCounts.Adults}
              onCounterChange={handleCounterChange}
              onGenderCountChange={handleGenderCountChange}
              maleCount={guestCounts.maleCount}
              femaleCount={guestCounts.femaleCount}
              totalAdults={guestCounts.Adults}
            />
            <Counter
              name="Children"
              defaultValue={guestCounts.Children}
              onCounterChange={handleCounterChange}
              ages={guestCounts.ages || []}
              onAgeChange={handleAgeChange}
            />
            <Counter
              name="Infants"
              defaultValue={guestCounts.Infants}
              onCounterChange={handleCounterChange}
            />
          </div>

          <div className="apply-section">
            <button 
              className="button -dark-1 bg-blue-1 text-white apply-btn"
              onClick={() => setIsOpen(false)}
            >
              Apply
            </button>
          </div>
        </div>
      )}
      
      <style jsx>{`
        .guest-selector-wrapper {
          position: relative;
          width: 100%;
        }
        
        .guest-selector-trigger {
          display: flex;
          align-items: center;
          justify-content: space-between;
          width: 100%;
          min-height: 50px;
          padding: 10px 15px;
          border: 1px solid #ddd;
          border-radius: 4px;
          background-color: white;
          cursor: pointer;
          transition: all 0.3s ease;
        }
        
        .guest-selector-trigger:hover {
          border-color: #3554d1;
          box-shadow: 0 3px 10px rgba(53, 84, 209, 0.1);
        }
        
        .guest-selector-trigger.active {
          border-color: #3554d1;
          box-shadow: 0 3px 15px rgba(53, 84, 209, 0.15);
        }
        
        .trigger-content {
          display: flex;
          align-items: center;
        }
        
        .trigger-icon {
          margin-right: 10px;
          color: #3554d1;
        }
        
        .trigger-text {
          display: flex;
          flex-direction: column;
        }
        
        .trigger-label {
          font-size: 12px;
          color: #697488;
          font-weight: 500;
        }
        
        .trigger-value {
          font-size: 15px;
          color: #051036;
          font-weight: 500;
        }
        
        .trigger-arrow {
          color: #3554d1;
          transition: transform 0.3s ease;
        }
        
        .trigger-arrow.active {
          transform: rotate(180deg);
        }
        
        .guest-dropdown-content {
          position: absolute;
          top: calc(100% + 10px);
          left: 0;
          right: 0;
          background-color: white;
          border-radius: 8px;
          box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
          z-index: 100;
          overflow: hidden;
          animation: fadeIn 0.2s ease;
        }
        
        @keyframes fadeIn {
          from { opacity: 0; transform: translateY(-10px); }
          to { opacity: 1; transform: translateY(0); }
        }
        
        .guest-info-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 18px 20px;
          background: linear-gradient(to right, rgba(53, 84, 209, 0.03), rgba(53, 84, 209, 0.08));
          border-bottom: 1px solid rgba(53, 84, 209, 0.1);
        }
        
        .info-icon-box {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 32px;
          height: 32px;
          border-radius: 8px;
          background: rgba(53, 84, 209, 0.1);
          margin-right: 12px;
        }
        
        :global(.info-icon) {
          font-size: 18px;
          color: #3554d1;
        }
        
        .guest-count-pills {
          display: flex;
          align-items: center;
        }
        
        .guest-count-pill {
          display: flex;
          align-items: center;
          padding: 4px 10px;
          border-radius: 20px;
          background-color: white;
          margin-left: 8px;
          box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
          font-size: 13px;
          color: #051036;
          font-weight: 500;
        }
        
        :global(.pill-icon) {
          font-size: 14px;
          color: #3554d1;
          margin-right: 5px;
        }
        
        .guest-counter-box {
          padding: 20px;
          background-color: white;
        }
        
        .apply-section {
          display: flex;
          justify-content: flex-end;
          padding: 15px 20px;
          border-top: 1px solid rgba(53, 84, 209, 0.1);
          background-color: #f9fafc;
        }
        
        .apply-btn {
          min-width: 100px;
          transition: all 0.3s ease;
        }
        
        .apply-btn:hover {
          transform: translateY(-2px);
          box-shadow: 0 5px 15px rgba(51, 102, 255, 0.2);
        }
      `}</style>
    </div>
  );
};

export default GuestSelector; 