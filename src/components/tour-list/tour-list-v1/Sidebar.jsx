import React, { useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { setFilters, selectFilters, selectFilteredAttractions } from "../../../slice/attractions/attractionSlice"; // Adjust the import path
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../slice/dmc/dmcSlice"; // Import DMC slice selectors
import { Slider, Avatar } from "@mui/material"; // Import Material-UI components
import DmcFilter from "../../hotel-list/sidebar/dmcFilter";

const Sidebar = () => {
  const dispatch = useDispatch();
  const filters = useSelector(selectFilters);
  // console.log(' filters ', filters );
  
  const attractions = useSelector(selectFilteredAttractions);
  
  // console.log('Full attractions array:', attractions);
  // console.log('First attraction:', attractions?.[0]);
  // console.log('DMC user name from first attraction:', attractions?.[0]?.dmc_user_name);
  
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  const haveBooking = useSelector((state) => state.common.haveBooking);
  // Get currency information from Redux store
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  
  // Find first attraction with valid dmc_user_name
  // const dmcUserName = attractions?.find(attraction => attraction.dmc_user_name)?.dmc_user_name || 'DMC';
  
  // console.log('Found DMC user name:', dmcUserName);
  // console.log('DMC Logo from Redux:', dmcLogo);

  const [localPriceRange, setLocalPriceRange] = useState([filters.priceRange.min, filters.priceRange.max]);
  const [selectedTimes, setSelectedTimes] = useState(filters.timeOfDay || []);
  const [showDmcOnly, setShowDmcOnly] = useState(false); // Set default to false
  
  const timeOfDayOptions = [
    { label: "Morning", key: "morning_opening" },
    { label: "Afternoon", key: "afternoon_opening" },
    { label: "Evening", key: "evening_opening" },
    { label: "Night", key: "night_opening" },
  ];

  // Calculate the maximum price from attractions in user's currency
  const maxPrice = attractions.reduce((max, item) => {
    const price = item.dmc_adult_price ?? item.travClicks_adult_price ?? 0;
    return price > max ? price : max;
  }, 0);
  
  // Calculate converted maximum price based on exchange rate
  const convertedMaxPrice = Math.ceil(maxPrice * exchangeRate);

  // Handle price range change
  const handlePriceRangeChange = (event, newValue) => {
    setLocalPriceRange(newValue);
    dispatch(setFilters({ priceRange: { min: newValue[0], max: newValue[1] } }));
  };

  // Handle time of day checkbox change
  const handleTimeChange = (event) => {
    const { value, checked } = event.target;
    let updatedTimes = checked
      ? [...selectedTimes, value]
      : selectedTimes.filter((time) => time !== value);

    setSelectedTimes(updatedTimes);
    
    // Convert selected times to API keys for filtering
    const timeFilters = updatedTimes.map(time => {
      const option = timeOfDayOptions.find(opt => opt.label === time);
      return option?.key;
    });

    dispatch(setFilters({ 
      ...filters,
      timeOfDay: timeFilters 
    }));
  };

  const handlePriceModeChange = (event) => {
    const checked = event.target.checked;
    setShowDmcOnly(checked);
    // If checked, show only DMC, if unchecked show both
    dispatch(setFilters({ 
      ...filters, // Preserve other filter values
      priceMode: checked ? ["dmc"] : ["dmc", "marketplace"]
    }));
  };

  const PriceHide = useSelector((state) => state.auth.PriceHide);

  return (
    <>
      {/* Category Types */}
      {/* <div className="sidebar__item -no-border">
        <h5 className="text-18 fw-500 mb-10">Category Types</h5>
        <div className="sidebar-checkbox">
          <CategoryTypes />
        </div>
      </div> */}

      {/* Other Filters */}
      {/* <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Other</h5>
        <div className="sidebar-checkbox">
          <OthersFilter />
        </div>
      </div> */}

      {/* Price Mode with Single Checkbox */}
      {/* <div className="sidebar__item -no-border">
        <h5 className="text-18 fw-500 mb-10">Price Mode</h5>
        <div className="sidebar-checkbox">
          <div className="row y-gap-10 items-center justify-between">
            <div className="col-auto">
              <div className="form-checkbox d-flex items-center">
                <input
                  type="checkbox"
                  name="priceMode"
                  checked={showDmcOnly}
                  onChange={handlePriceModeChange}
                />
                <div className="form-checkbox__mark">
                  <div className="form-checkbox__icon icon-check" />
                </div>
                <div className="text-15 ml-10 d-flex items-center">
                  {dmcLogo && (
                    <Avatar
                      src={dmcLogo}
                      alt={`${dmcCompanyName} Logo`}
                      sx={{ 
                        width: 24,
                        height: 24,
                        marginRight: '8px',
                      }}
                    />
                  )}
                  {dmcCompanyName}'s Mode
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> */}

      {/* Time of Day */}
      <div className="sidebar__item -no-border">
        <h5 className="text-18 fw-500 mb-10">Time of Day</h5>
        <div className="sidebar-checkbox">
          {timeOfDayOptions.map((option, index) => {
            // Get count from attractions data
            const count = attractions.reduce((acc, attraction) => 
              acc + (attraction[option.key] ? 1 : 0), 0);

            return (
              <div className="row y-gap-10 items-center justify-between" key={index}>
                <div className="col-auto">
                  <div className="form-checkbox d-flex items-center">
                    <input
                      type="checkbox"
                      name="timeOfDay"
                      value={option.label}
                      checked={selectedTimes.includes(option.label)}
                      onChange={handleTimeChange}
                    />
                    <div className="form-checkbox__mark">
                      <div className="form-checkbox__icon icon-check" />
                    </div>
                    <div className="text-15 ml-10">{option.label}</div>
                  </div>
                </div>
                <div className="col-auto">
                  <div className="text-15 text-light-1">{count}</div>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Price Filter */}
      {PriceHide !== "0"? null : (
      <div className="sidebar__item pb-30">
        <h5 className="text-18 fw-500 mb-10">Price</h5>
        <div className="row x-gap-10 y-gap-30">
          <div className="col-12">
            <Slider
              value={localPriceRange}
              onChange={handlePriceRangeChange}
              valueLabelDisplay="auto"
              valueLabelFormat={(value) => `${currencyCode} ${Math.ceil(value * exchangeRate)}`}
              min={0}
              max={maxPrice}
              step={10}
            />
            <div className="d-flex justify-between mt-10">
              <div className="text-15">{currencyCode} 0</div>
              <div className="text-15">{currencyCode} {convertedMaxPrice}</div>
            </div>
          </div>
        </div>
      </div>
      )}
      
      <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">DMC</h5>
        <div className="sidebar-checkbox">
          <DmcFilter />
        </div>
      </div>
      
    </>
  );
};

export default Sidebar;
