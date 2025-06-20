import React, { useState } from "react";
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import MaleIcon from '@mui/icons-material/Male';
import FemaleIcon from '@mui/icons-material/Female';

const counters = [
  { name: "Adults", defaultValue: 1 },
  { name: "Children", defaultValue: 0 },
  { name: "Infants", defaultValue: 0 },
];

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

  return (
    <>
      <div className="row y-gap-10 justify-between items-center">
        <div className="col-auto">
          <div className="text-15 lh-12 fw-500">{name}</div>
          {name === "Children" && (
            <div className="text-14 lh-12 text-light-1 mt-5">Ages 1 - 17</div>
          )}
          {name === "Infants" && (
            <div className="text-14 lh-12 text-light-1 mt-5">Younger than 1 year old</div>
          )}
        </div>
        <div className="col-auto">
          <div className="d-flex items-center js-counter">
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down"
              onClick={decrementCount}
            >
              <i className="icon-minus text-12" />
            </button>
            <div className="flex-center size-20 ml-15 mr-15">
              <div className="text-15 js-count">{count}</div>
            </div>
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up"
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
          <div className="gender-counter-item">
            <div className="gender-counter-header">
              <MaleIcon className="custom-icon male-icon" />
              <span className="gender-label">Male</span>
            </div>
            <div className="d-flex items-center js-counter">
              <button
                className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down"
                onClick={() => decrementGenderCount("Male")}
                disabled={maleCount === 0}
              >
                <i className="icon-minus text-12" />
              </button>
              <div className="flex-center size-20 ml-15 mr-15">
                <div className="text-15 js-count">{maleCount}</div>
              </div>
              <button
                className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up"
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
                className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down"
                onClick={() => decrementGenderCount("Female")}
                disabled={femaleCount === 0}
              >
                <i className="icon-minus text-12" />
              </button>
              <div className="flex-center size-20 ml-15 mr-15">
                <div className="text-15 js-count">{femaleCount}</div>
              </div>
              <button
                className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up"
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

        button:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }
      `}</style>

      <div className="border-top-light mt-24 mb-24" />
    </>
  );
};

const GuestSearch = ({ onGuestChange, guestCounts }) => {
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

    if (name === "Infants") {
      // Just update the count, no age selection needed
    }

    onGuestChange(updatedGuestCounts);
  };

  const handleGenderCountChange = (gender, count, action = null) => {
    const updatedGuestCounts = { ...guestCounts };
    const totalAdults = guestCounts.Adults || 1;
    const currentMaleCount = guestCounts.maleCount || 0;
    const currentFemaleCount = guestCounts.femaleCount || 0;
    
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
    } else {
      // Manual mode (shouldn't be used with current UI)
      if (gender === "Male") {
        updatedGuestCounts.maleCount = Math.min(count, totalAdults);
        // Ensure we don't exceed total
        if (updatedGuestCounts.maleCount + currentFemaleCount > totalAdults) {
          updatedGuestCounts.femaleCount = totalAdults - updatedGuestCounts.maleCount;
        }
      } else {
        updatedGuestCounts.femaleCount = Math.min(count, totalAdults);
        // Ensure we don't exceed total
        if (updatedGuestCounts.maleCount + updatedGuestCounts.femaleCount > totalAdults) {
          updatedGuestCounts.maleCount = totalAdults - updatedGuestCounts.femaleCount;
        }
      }
    }
    
    onGuestChange(updatedGuestCounts);
  };

  const handleAgeChange = (index, selectedAge) => {
    const updatedAges = [...(guestCounts.ages || [])];
    updatedAges[index] = selectedAge;
    onGuestChange({ ...guestCounts, ages: updatedAges });
  };

  const handleInfantAgeChange = (index, selectedAge) => {
    const updatedInfantAges = [...(guestCounts.infantAges || [])];
    updatedInfantAges[index] = selectedAge;
    onGuestChange({ ...guestCounts, infantAges: updatedInfantAges });
  };

  return (
    <div className="searchMenu-guests px-30 lg:py-20 lg:px-0 js-form-dd js-form-counters position-relative">
      <div
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false"
        data-bs-offset="0,22"
      >
        <h4 className="text-15 fw-500 ls-2 lh-16">Guest</h4>
        <div className="text-15 text-light-1 ls-2 lh-16">
          <span className="js-count-adult">{guestCounts.Adults}</span> adults 
          (<span className="js-count-male">{guestCounts.maleCount || 0}</span> male, 
          <span className="js-count-female">{guestCounts.femaleCount || 0}</span> female) - 
          <span className="js-count-child">{guestCounts.Children}</span>{" "}
          children -{" "}
          <span className="js-count-room">{guestCounts.Infants}</span> infants
        </div>
      </div>

      <div
        className="shadow-2 dropdown-menu min-width-400"
        style={{
          maxHeight: '600px',
          border: 'none',
          borderRadius: '16px',
          boxShadow: '0 20px 40px rgba(0,0,0,0.1)',
          animation: 'fadeIn 0.3s ease-in-out'
        }}
      >
        <div
          className="bg-white px-30 py-30 rounded-4 counter-box"
          style={{
            maxHeight: '600px',
            background: 'linear-gradient(to bottom, #ffffff, #f8f9fa)',
            borderRadius: '16px',
            position: 'relative'
          }}
        >
          <style jsx>{`
            .button.-outline-blue-1 {
              transition: all 0.3s ease;
              border: 2px solid #3554d1;
            }
            
            .button.-outline-blue-1:hover {
              background: #3554d1;
              color: white !important;
              transform: scale(1.05);
            }

            .form-select {
              border-radius: 8px;
              border: 2px solid #e9ecef;
              padding: 8px 12px;
              transition: all 0.3s ease;
              background: white;
              box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .form-select:hover {
              border-color: #3554d1;
            }

            .form-select:focus {
              border-color: #3554d1;
              box-shadow: 0 0 0 2px rgba(53,84,209,0.2);
            }

            @keyframes fadeIn {
              from { opacity: 0; transform: translateY(-10px); }
              to { opacity: 1; transform: translateY(0); }
            }
          `}</style>

          {counters.map((counter) => (
            <Counter
              key={counter.name}
              name={counter.name}
              defaultValue={guestCounts[counter.name] || counter.defaultValue}
              onCounterChange={handleCounterChange}
              onGenderCountChange={handleGenderCountChange}
              maleCount={guestCounts.maleCount || 0}
              femaleCount={guestCounts.femaleCount || 0}
              totalAdults={guestCounts.Adults || 1}
              ages={counter.name === "Children" ? (guestCounts.ages || []) : 
                    counter.name === "Infants" ? (guestCounts.infantAges || []) : []}
              onAgeChange={counter.name === "Children" ? handleAgeChange : handleInfantAgeChange}
            />
          ))}
        </div>
      </div>
      <style jsx>{`
        :global(.searchMenu-guests .dropdown-menu) {
          top: 100% !important;
          bottom: auto !important;
          transform: none !important;
        }
      `}</style>
    </div>
  );
};

export default GuestSearch;
