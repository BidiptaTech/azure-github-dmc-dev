import React, { useState, useEffect } from "react";

const Counter = ({ name, maxValue, minValue, onCounterChange }) => {
  const [count, setCount] = useState(maxValue);

  // ✅ Update count when maxValue (prop) changes
  useEffect(() => {
    setCount(maxValue);
  }, [maxValue]);

  const incrementCount = () => {
    if (count < maxValue) {
      const newCount = count + 1;
      setCount(newCount);
      onCounterChange(name, newCount);
    }
  };

  const decrementCount = () => {
    if (count > minValue) {
      const newCount = count - 1;
      setCount(newCount);
      onCounterChange(name, newCount);
    }
  };

  return (
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
            disabled={count === minValue}
          >
            <i className="icon-minus text-12" />
          </button>
          <div className="flex-center size-20 ml-15 mr-15">
            <div className="text-15 js-count">{count}</div>
          </div>
          <button
            className="button -outline-blue-1 text-blue-1 size-38 rounded-4 js-up"
            onClick={incrementCount}
            disabled={count === maxValue}
          >
            <i className="icon-plus text-12" />
          </button>
        </div>
      </div>
    </div>
  );
};

//import React, { useState, useEffect } from "react";

const GuestSearch = ({
  adultCount,
  childrenCount,
  adultsMax,
  childrenMax,
  onGuestChange,
}) => {
  const [guestCounts, setGuestCounts] = useState({
    Adults: adultCount,
    Children: childrenCount,
  });

  // ✅ Update guestCounts when parent changes adultCount or childrenCount
  useEffect(() => {
    setGuestCounts({ Adults: adultCount, Children: childrenCount });
  }, [adultCount, childrenCount]);

  const handleCounterChange = (name, value) => {
    const updatedCounts = { ...guestCounts, [name]: value };
    setGuestCounts(updatedCounts);
    onGuestChange(updatedCounts.Adults, updatedCounts.Children);
  };

  return (
    <div className="searchMenu-guests px-20 py-10 border-light rounded-4 js-form-dd js-form-counters">
      <div
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false"
        data-bs-offset="0,22"
      >
        <h4 className="text-15 fw-500 ls-2 lh-16">Number of travelers</h4>
        <div className="text-15 text-light-1 ls-2 lh-16">
          <span className="js-count-adult">{guestCounts.Adults}</span> adults -{" "}
          <span className="js-count-child">{guestCounts.Children}</span>{" "}
          children
        </div>
      </div>

      <div className="shadow-2 dropdown-menu min-width-400">
        <div className="bg-white px-30 py-30 rounded-4 counter-box">
          <Counter
            name="Adults"
            maxValue={adultsMax} // ✅ Use fixed max from Redux
            minValue={1}
            onCounterChange={handleCounterChange}
          />
          <Counter
            name="Children"
            maxValue={childrenMax} // ✅ Use fixed max from Redux
            minValue={0}
            onCounterChange={handleCounterChange}
          />
        </div>
      </div>
    </div>
  );
};

export default GuestSearch;
