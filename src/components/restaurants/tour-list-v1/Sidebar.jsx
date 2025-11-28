import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { setFilters, selectFilters, selectRestaurants } from "../../../slice/restaurant/RestaurantsSlice";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../slice/dmc/dmcSlice"; // Import DMC slice selectors
import { Slider, RadioGroup, FormControlLabel, Radio, FormControl, FormLabel, Avatar } from "@mui/material";
import DmcFilter from "../../hotel-list/sidebar/dmcFilter";

const Sidebar = () => {
  const dispatch = useDispatch();
  const filters = useSelector(selectFilters);
  const restaurants = useSelector(selectRestaurants);
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

  // Calculate max price from all restaurants
  const maxPrice = restaurants.reduce((max, item) => {
    const maxBreakfast = Math.max(item.dmc_breakfast_price || 0, item.travClicks_breakfast_price || 0);
    const maxLunch = Math.max(item.dmc_lunch_price || 0, item.travClicks_lunch_price || 0);
    const maxDinner = Math.max(item.dmc_dinner_price || 0, item.travClicks_dinner_price || 0);
    const totalHighestForRestaurant = maxBreakfast + maxLunch + maxDinner;
    return totalHighestForRestaurant > max ? totalHighestForRestaurant : max;
  }, 0);
  
  // Calculate converted maximum price based on exchange rate
  const convertedMaxPrice = Math.ceil(maxPrice * exchangeRate);

  // Initialize local price range with max price
  const [localPriceRange, setLocalPriceRange] = useState([0, maxPrice]);
  const [selectedReviewScores, setSelectedReviewScores] = useState(filters.reviewScores || []);
  const [selectedCuisines, setSelectedCuisines] = useState(filters.cuisines || []);
  const [showDmcOnly, setShowDmcOnly] = useState(false);

  // Update filters with max price on component mount and when maxPrice changes
  useEffect(() => {
    if (maxPrice > 0) {
      setLocalPriceRange([0, maxPrice]);
      dispatch(setFilters({ 
        ...filters,
        priceRange: { min: 0, max: maxPrice } 
      }));
    }
  }, [maxPrice]);

  const cuisines = Array.from(
    new Set(restaurants.map((restaurant) => restaurant.cuisine))
  ).map((cuisine) => ({
    label: cuisine,
    count: restaurants.filter((restaurant) => restaurant.cuisine === cuisine).length,
  }));

  const reviewScores = [
    { label: "5 stars", count: 20 },
    { label: "4 stars", count: 50 },
    { label: "3 stars", count: 80 },
    { label: "2 stars", count: 100 },
  ];

  const handlePriceRangeChange = (event, newValue) => {
    setLocalPriceRange(newValue);
    dispatch(setFilters({ 
      ...filters,
      priceRange: { min: newValue[0], max: newValue[1] } 
    }));
  };

  const handleCuisineChange = (event) => {
    const { value, checked } = event.target;
    const updatedCuisines = checked
      ? [...selectedCuisines, value]
      : selectedCuisines.filter((cuisine) => cuisine !== value);

    setSelectedCuisines(updatedCuisines);
    dispatch(setFilters({ 
      ...filters,
      cuisines: updatedCuisines 
    }));
  };

  const handleReviewScoreChange = (event) => {
    const { value, checked } = event.target;
    const updatedReviewScores = checked
      ? [...selectedReviewScores, value]
      : selectedReviewScores.filter((score) => score !== value);

    setSelectedReviewScores(updatedReviewScores);
    dispatch(setFilters({ 
      ...filters,
      reviewScores: updatedReviewScores 
    }));
  };

  const handlePriceModeChange = (event) => {
    const checked = event.target.checked;
    setShowDmcOnly(checked);
    dispatch(setFilters({ 
      ...filters,
      priceMode: checked ? ["dmc"] : ["dmc", "marketplace"]
    }));
  };

  useEffect(() => {
    setSelectedReviewScores(filters.reviewScores || []);
    setSelectedCuisines(filters.cuisines || []);
    setShowDmcOnly(filters.priceMode?.length === 1 && filters.priceMode[0] === "dmc");
  }, [filters]);

   const PriceHide = useSelector((state) => state.auth.PriceHide); 

  return (
    <>
      {/* Review Score */}
      {/* <div className="sidebar__item -no-border">
        <h5 className="text-18 fw-500 mb-10">Review Score</h5>
        <div className="sidebar-checkbox">
          {reviewScores.map((option, index) => (
            <div className="row y-gap-10 items-center justify-between" key={index}>
              <div className="col-auto">
                <div className="form-checkbox d-flex items-center">
                  <input
                    type="checkbox"
                    name="reviewScore"
                    value={option.label}
                    checked={selectedReviewScores.includes(option.label)}
                    onChange={handleReviewScoreChange}
                  />
                  <div className="form-checkbox__mark">
                    <div className="form-checkbox__icon icon-check" />
                  </div>
                  <div className="text-15 ml-10">{option.label}</div>
                </div>
              </div>
              <div className="col-auto">
                <div className="text-15 text-light-1">{option.count}</div>
              </div>
            </div>
          ))}
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
                                      <span>{dmcCompanyName}'s Mode</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> */}

      {/* Cuisine Filter */}
      <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Cuisine</h5>
        <div className="sidebar-checkbox">
          {cuisines.map((option, index) => (
            <div className="row y-gap-10 items-center justify-between" key={index}>
              <div className="col-auto">
                <div className="form-checkbox d-flex items-center">
                  <input
                    type="checkbox"
                    name="cuisine"
                    value={option.label}
                    checked={selectedCuisines.includes(option.label)}
                    onChange={handleCuisineChange}
                  />
                  <div className="form-checkbox__mark">
                    <div className="form-checkbox__icon icon-check" />
                  </div>
                  <div className="text-15 ml-10">{option.label}</div>
                </div>
              </div>
              <div className="col-auto">
                <div className="text-15 text-light-1">{option.count}</div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Price Filter */}
      {PriceHide !== "0"? null : (
      <div className="sidebar__item pb-30">
        <h5 className="text-18 fw-500 mb-10">Price</h5>
        <Slider
          value={localPriceRange}
          onChange={handlePriceRangeChange}
          valueLabelDisplay="auto"
          valueLabelFormat={(value) => `${currencyCode} ${Math.ceil(value * exchangeRate)}`}
          min={0}
          max={maxPrice}
          step={100}
        />
        <div className="d-flex justify-between mt-10">
          <div className="text-15">{currencyCode} 0</div>
          <div className="text-15">{currencyCode} {convertedMaxPrice}</div>
        </div>
      </div>
      )}

{haveBooking === false && (
<div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">DMC</h5>
        <div className="sidebar-checkbox">
          <DmcFilter />
        </div>
      </div>
      )}
    </>
  );
};

export default Sidebar;
