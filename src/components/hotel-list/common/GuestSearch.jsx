import React, { useState, useEffect, useRef } from "react";
import { useSelector } from "react-redux";

const counters = [
  { name: "adults", label: "Adults", minValue: 1 },
  { name: "children", label: "Children", minValue: 0 },
  { name: "infant", label: "Infant", minValue: 0 },
];

const Counter = ({ name, label, value, minValue, maxValue, initialValue, onCounterChange }) => {
  const [count, setCount] = useState(value);

  useEffect(() => {
    setCount(value); // Sync with parent state
  }, [value]);

  const incrementCount = () => {
    if (name === "children" || name === "infant") {
      // Prevent exceeding the initial value for Children and Infant
      if (count < initialValue) {
        setCount(count + 1);
        onCounterChange(name, count + 1);
      }
    } else if (count < maxValue) {
      setCount(count + 1);
      onCounterChange(name, count + 1);
    }
  };

  const decrementCount = () => {
    if (count > minValue) {
      setCount(count - 1);
      onCounterChange(name, count - 1);
    }
  };

  return (
    <>
      <div className="row y-gap-10 justify-between items-center">
        <div className="col-auto">
          <div className="text-15 lh-12 fw-500">{label}</div>
          {name === "children" && (
            <div className="text-14 lh-12 text-light-1 mt-5">Ages 0 - 17</div>
          )}
          {name === "infant" && (
            <div className="text-14 lh-12 text-light-1 mt-5">Ages 0 - 2</div>
          )}
        </div>
        <div className="col-auto">
          <div className="d-flex items-center js-counter">
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-down"
              onClick={decrementCount}
              disabled={count <= minValue}
            >
              <i className="icon-minus text-12" />
            </button>
            <div className="flex-center size-20 ml-15 mr-15">
              <div className="text-15 js-count">{count}</div>
            </div>
            <button
              className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up"
              onClick={incrementCount}
              disabled={name === "children" || name === "infant" ? count >= initialValue : count >= maxValue}
            >
              <i className="icon-plus text-12" />
            </button>
          </div>
        </div>
      </div>
      <div className="border-top-light mt-24 mb-24" />
    </>
  );
};

const GuestSearch = ({ guests, setGuests, maxValues, showDropdown, setShowDropdown, dropdownRef }) => {
  const tourDetails = useSelector((state) => state.hotels.tourdetails);

  // Get default values from tourDetails
  const defaultAdults = tourDetails?.adult > 0 ? tourDetails.adult : 1;
  const defaultChildren = tourDetails?.child ?? 0;
  const defaultInfant = tourDetails?.infant ?? 0;

  const handleCounterChange = (name, value) => {
    setGuests((prev) => ({ ...prev, [name]: value }));
  };

  const handleDropdownClick = (e) => {
    e.preventDefault();
    setShowDropdown(!showDropdown);
  };

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setShowDropdown(false);
      }
    };

    document.addEventListener("click", handleClickOutside);
    return () => {
      document.removeEventListener("click", handleClickOutside);
    };
  }, [setShowDropdown, dropdownRef]);

  return (
    <div className="searchMenu-guests px-30 lg:py-20 sm:px-20 js-form-dd js-form-counters position-relative" ref={dropdownRef}>
      <div onClick={handleDropdownClick} className="cursor-pointer">
        <div className="d-flex">
          <i className="icon-compass text-20 text-light-1 mt-5"></i>
          <div className="ml-10">
            <h4 className="text-15 fw-500 ls-2 lh-16">Guests</h4>
            <div className="text-15 text-light-1 ls-2 lh-16">
              <span className="js-count-adult">{guests.adults}</span> adults -{" "}
              <span className="js-count-child">{guests.children}</span> children -{" "}
              <span className="js-count-infant">{guests.infant}</span> infant
            </div>
          </div>
        </div>
      </div>

      {showDropdown && (
        <div className="shadow-2 dropdown-menu min-width-400 show">
          <div className="bg-white px-30 py-30 rounded-4 counter-box">
            {counters.map((counter) => (
              <Counter
                key={counter.name}
                name={counter.name}
                label={counter.label}
                value={guests[counter.name]}
                minValue={counter.minValue}
                maxValue={counter.name === "adults" ? defaultAdults : 10}
                initialValue={
                  counter.name === "children" ? defaultChildren :
                  counter.name === "infant" ? defaultInfant : null
                }
                onCounterChange={handleCounterChange}
              />
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default GuestSearch;
