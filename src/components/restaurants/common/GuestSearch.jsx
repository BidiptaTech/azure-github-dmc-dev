import React, { useState, useEffect } from "react";
import { useSelector } from "react-redux";

const counters = [
  { name: "Adults", minValue: 1 },
  { name: "Children", minValue: 0 },
];

const Counter = ({ name, value, minValue, maxValue, initialValue, onCounterChange }) => {
  const [count, setCount] = useState(value);

  useEffect(() => {
    setCount(value); // Sync with parent state
  }, [value]);

  const incrementCount = () => {
    if (name === "Children") {
      // Prevent exceeding the initial value for Children
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
          <div className="text-15 lh-12 fw-500">{name}</div>
          {name === "Children" && (
            <div className="text-14 lh-12 text-light-1 mt-5">Ages 0 - 17</div>
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
              disabled={name === "Children" ? count >= initialValue : count >= maxValue}
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

const GuestSearch = ({ setGuestCounts }) => {
  const tourdetails = useSelector((state) => state.hotels.tourdetails);

  const defaultAdults = tourdetails?.adult > 0 ? tourdetails.adult : 1;
  const defaultChildren = tourdetails?.child ?? 0;

  const [guestCounts, setLocalGuestCounts] = useState({
    Adults: defaultAdults,
    Children: defaultChildren,
  });

  useEffect(() => {
    setLocalGuestCounts({
      Adults: defaultAdults,
      Children: defaultChildren,
    });
  }, [tourdetails]);

  const handleCounterChange = (name, value) => {
    setLocalGuestCounts((prev) => ({ ...prev, [name]: value }));
    setGuestCounts((prev) => ({ ...prev, [name]: value }));
  };

  return (
    <div className="searchMenu-guests px-30 lg:py-20 sm:px-20 js-form-dd js-form-counters position-relative">
      <div
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false"
        data-bs-offset="0,22"
      >
        <div className="d-flex">
          <i className="icon-compass text-20 text-light-1 mt-5"></i>
          <div className="ml-10">
            <h4 className="text-15 fw-500 ls-2 lh-16">Tour Type</h4>
            <div className="text-15 text-light-1 ls-2 lh-16">
              <span className="js-count-adult">{guestCounts.Adults}</span> adults -{" "}
              <span className="js-count-child">{guestCounts.Children}</span> children
            </div>
          </div>
        </div>
      </div>

      <div className="shadow-2 dropdown-menu min-width-400">
        <div className="bg-white px-30 py-30 rounded-4 counter-box">
          {counters.map((counter) => (
            <Counter
              key={counter.name}
              name={counter.name}
              value={guestCounts[counter.name]}
              minValue={counter.minValue}
              maxValue={counter.name === "Adults" ? defaultAdults : 10}
              initialValue={counter.name === "Children" ? defaultChildren : null}
              onCounterChange={handleCounterChange}
            />
          ))}
        </div>
      </div>
    </div>
  );
};

export default GuestSearch;
