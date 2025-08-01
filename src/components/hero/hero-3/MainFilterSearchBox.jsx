import { useSelector, useDispatch } from "react-redux";
import { addCurrentTab } from "../../../features/hero/findPlaceSlice";
import DateSearch from "./DateSearch";
import GuestSearch from "./GuestSearch";
import LocationSearch from "./LocationSearch";
import { useNavigate } from "react-router-dom";
import { setSelectedCity } from "../../../slice/common/commonSlice";
import { useState } from "react";

const MainFilterSearchBox = ({ onSearch }) => {
  const { tabs, currentTab } = useSelector((state) => state.hero) || {};
  const dispatch = useDispatch();
  const navigate = useNavigate();

  // Add state for selected location
  const [selectedLocation, setSelectedLocation] = useState(null);

  // Handle location selection
  const handleLocationSelect = (location) => {
    console.log("MainFilterSearchBox: Selected location:", location);
    setSelectedLocation(location);
  };

  // Handle search button click
  const handleSearch = () => {
    // Set the selected city in Redux
    if (selectedLocation) {
      console.log("MainFilterSearchBox: Setting selected city:", selectedLocation);
      dispatch(setSelectedCity(selectedLocation));
    }

    if (onSearch && typeof onSearch === 'function') {
      // If onSearch prop is provided, call it
      onSearch();
    } else {
      // Otherwise use default behavior
      navigate("/hotel-list-v3");
    }
  };

  return (
    <>
      <div className="tabs -bookmark js-tabs">
        <div className="tabs__controls d-flex items-center js-tabs-controls">
          {tabs?.map((tab) => (
            <button
              key={tab?.id}
              className={`tabs__button px-30 py-20 rounded-4 fw-600 text-white js-tabs-button ${
                tab?.name === currentTab ? "is-tab-el-active" : ""
              }`}
              onClick={() => dispatch(addCurrentTab(tab?.name))}
            >
              <i className={`${tab.icon} text-20 mr-10`}></i>
              {tab?.name}
            </button>
          ))}
        </div>
      </div>

      <div className="tabs__content js-tabs-content">
        <div className="mainSearch bg-white pr-20 py-20 lg:px-20 lg:pt-5 lg:pb-20 rounded-4">
          <div className="button-grid items-center">
            <LocationSearch onLocationSelect={handleLocationSelect} />
            {/* End Location */}

            <div className="searchMenu-date px-30 lg:py-20 lg:px-0 js-form-dd js-calendar">
              <div>
                <h4 className="text-15 fw-500 ls-2 lh-16">
                  Check in - Check out
                </h4>
                <DateSearch />
              </div>
            </div>
            {/* End check-in-out */}

            <GuestSearch guestCounts={{Adults: 1, Children: 0, Infants: 0, maleCount: 0, femaleCount: 0, ages: []}} />
            {/* End guest */}

            <div className="button-item">
              <button
                className="mainSearch__submit button -dark-1 py-15 px-35 h-60 col-12 rounded-4 bg-blue-1 text-white"
                onClick={handleSearch}
              >
                <i className="icon-search text-20 mr-10" />
                Search
              </button>
            </div>
            {/* End search button_item */}
          </div>
        </div>
        {/* End .mainSearch */}
      </div>
      {/* End serarchbox tab-content */}
    </>
  );
};

export default MainFilterSearchBox;
