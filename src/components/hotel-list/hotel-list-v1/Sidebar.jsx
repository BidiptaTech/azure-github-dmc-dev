import DealsFilter from "../sidebar/DealsFilter";
import Map from "../sidebar/Map";
import SearchBox from "../sidebar/SearchBox";
import PopularFilters from "../sidebar/PopularFilters";
import AminitesFilter from "../sidebar/AminitesFilter";
import RatingsFilter from "../sidebar/RatingsFilter";
import GuestRatingFilters from "../sidebar/GuestRatingFilters";
import StyleFilter from "../sidebar/StyleFilter";
import NeighborhoddFilter from "../sidebar/NeighborhoddFilter";
import PirceSlider from "../sidebar/PirceSlider";
import { useDispatch, useSelector } from "react-redux";
import { useState, useEffect, useMemo } from "react";
import { setFilter, clearFilters, setPriceMode, setPriceBounds } from "@/slice/hotel/CategorySlice";
import PriceModeFilter from "../sidebar/PriceModeFilter";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../slice/dmc/dmcSlice"; // Import DMC slice selectors


const Sidebar = () => {
  const dispatch = useDispatch();
  const filters = useSelector((state) => state.category);
  const priceBounds = useSelector((state) => state.category.priceBounds); // Get price bounds from Redux
  const reduxPriceRange = useSelector((state) => state.category.priceRange); // Get price range from Redux
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  const hotels = useSelector((state) => state.hotels.hotels);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  
  // Flag to track if API data has been loaded
  const [apiDataLoaded, setApiDataLoaded] = useState(false);
  
  // Initialize price range from Redux or priceBounds - use useMemo to prevent unnecessary updates
  const initialPriceRange = useMemo(() => {
    if (reduxPriceRange && 'min' in reduxPriceRange && 'max' in reduxPriceRange) {
      return [reduxPriceRange.min, reduxPriceRange.max];
    } else if (priceBounds && 'min' in priceBounds && 'max' in priceBounds) {
      return [priceBounds.min, priceBounds.max];
    } else {
      return [0, 1000];
    }
  }, []);
  
  // Component state for price range
  const [priceRange, setPriceRange] = useState(initialPriceRange);
  const PriceHide = useSelector((state) => state.category.priceHide);
  
  // Set the showDmcOnly state based on priceMode
  const showDmcOnly = useMemo(() => {
    return filters.priceMode && 
      Array.isArray(filters.priceMode) && 
      filters.priceMode.length === 1 && 
      filters.priceMode[0] === "dmc";
  }, [filters.priceMode]);
  
  // Sync with Redux price range when it changes externally
  useEffect(() => {
    if (reduxPriceRange && typeof reduxPriceRange === 'object' && 
        'min' in reduxPriceRange && 'max' in reduxPriceRange &&
        (priceRange[0] !== reduxPriceRange.min || priceRange[1] !== reduxPriceRange.max)) {
      setPriceRange([reduxPriceRange.min, reduxPriceRange.max]);
    }
  }, [reduxPriceRange]);
  
  // Check if we already have valid price bounds
  useEffect(() => {
    if (!apiDataLoaded && priceBounds && 
        priceBounds.min !== undefined && priceBounds.max !== undefined &&
        (priceBounds.min > 0 || priceBounds.max > 1000)) {
      setApiDataLoaded(true);
    }
  }, [priceBounds, apiDataLoaded]);
  
  // Calculate price bounds when hotels are loaded
  useEffect(() => {
    if (hotels && hotels.length > 0 && !apiDataLoaded) {
      calculatePriceBounds();
    }
  }, [hotels, apiDataLoaded]);
  
  // Handler for price mode change
  const handlePriceModeChange = (event) => {
    const checked = event.target.checked;
    // If checked, show only DMC, if unchecked show both
    dispatch(setPriceMode(checked ? ["dmc"] : ["dmc", "travclick"]));
    
    // After changing price mode, recalculate bounds if needed
    if (hotels && hotels.length > 0) {
      calculatePriceBounds();
    }
  };
  
  // Handler for manual price input change
  const handleManualPriceChange = (event, index) => {
    // Ensure we're working with a numeric value
    let newValue = parseInt(event.target.value, 10);
    
    // If value is not a number, use previous value
    if (isNaN(newValue)) {
      newValue = index === 0 ? priceRange[0] : priceRange[1];
    }
    
    const updatedPriceRange = [...priceRange];
    
    if (index === 0) {
      // Min price: ensure it's not negative and not higher than max
      updatedPriceRange[0] = Math.max(0, Math.min(newValue, updatedPriceRange[1]));
    } else {
      // Max price: ensure it's not lower than min
      updatedPriceRange[1] = Math.max(updatedPriceRange[0], newValue);
    }
    
    // Check if there's an actual change before updating state and Redux
    if (updatedPriceRange[0] !== priceRange[0] || updatedPriceRange[1] !== priceRange[1]) {
      // Update local state
      setPriceRange(updatedPriceRange);
      
      // Update Redux
      dispatch(setFilter({ 
        filterName: "priceRange", 
        value: { min: updatedPriceRange[0], max: updatedPriceRange[1] }
      }));
    }
  };

  const locations = [
    { label: "Berlin Tegel Airport" },
    { label: "Berlin Main Train Station" },
    { label: "Berlin City Center" },
  ];

  const filterNames = {
    payAtHotel: "Pay at Hotel",
    topBrands: "Top Brands - Ibis, Marriott & more",
    budgetProperties: "Budget Properties",
    userRating: "User Rating",
    price1: "₹ 0 - ₹ 4000", // Updated for new range
    price2: "₹ 4000 - ₹ 10000",
    price3: "Above ₹ 10000",
    location1: "Berlin Tegel Airport",
    location2: "Berlin Main Train Station",
    location3: "Berlin City Center",
    star1: "3 Star",
    star2: "4 Star",
    star3: "5 Star",
  };

  const initialFilterState = locations.reduce((acc, location, index) => {
    acc[`location${index + 1}`] = false;
    return acc;
  }, {});

  const [filterslocation, setFilterslocation] = useState(initialFilterState);

  const handleClearFilters = () => {
    setFilterslocation(initialFilterState);
    
    // Reset to the API-derived price bounds if available
    if (priceBounds && priceBounds.min !== undefined && priceBounds.max !== undefined) {
      setPriceRange([priceBounds.min, priceBounds.max]);
      dispatch(setFilter({ 
        filterName: "priceRange", 
        value: { min: priceBounds.min, max: priceBounds.max }
      }));
    } else {
      // Otherwise reset to default range
      setPriceRange([0, 1000]);
      dispatch(setFilter({ 
        filterName: "priceRange", 
        value: { min: 0, max: 1000 }
      }));
    }
    
    // Clear all filters in the Redux store while preserving priceBounds
    dispatch(clearFilters()); 
    
    // Restore default price mode after clearing filters
    dispatch(setPriceMode(["dmc", "travclick"]));
  };

  const handleCheckboxChange = (event) => {
    const { id, checked } = event.target;

    // Dispatch filters for price, location, and star filters
    if (id.startsWith("price") || id.startsWith("location") || id.startsWith("star")) {
      dispatch(setFilter({ filterName: id, value: checked }));
    }

    if (id.startsWith("location")) {
      setFilterslocation((prevFilters) => ({
        ...prevFilters,
        [id]: checked,
      }));
    }
  };

  // Handler for price range slider change
  const handlePriceChange = (event, newValue) => {
    // Validate the new values
    if (!Array.isArray(newValue) || newValue.length !== 2 || 
        !isFinite(newValue[0]) || !isFinite(newValue[1])) {
      return;
    }
    
    // Ensure max is not less than min
    if (newValue[1] < newValue[0]) {
      newValue[1] = newValue[0];
    }
    
    // Only update if the values actually changed
    if (priceRange[0] !== newValue[0] || priceRange[1] !== newValue[1]) {
      // Update local state first (array format for slider)
      setPriceRange(newValue);
      
      // Then dispatch to Redux in object format
      dispatch(setFilter({ 
        filterName: "priceRange", 
        value: { min: newValue[0], max: newValue[1] }
      }));
    }
  };

  const handleRemoveFilter = (filterKey) => {
    dispatch(setFilter({ filterName: filterKey, value: false }));

    if (filterKey.startsWith("location")) {
      setFilterslocation((prevFilters) => ({
        ...prevFilters,
        [filterKey]: false,
      }));
    }
  };

  const handleResetPriceRange = () => {
    // Use the stored price bounds from API data
    if (priceBounds && priceBounds.min !== undefined && priceBounds.max !== undefined) {
      setPriceRange([priceBounds.min, priceBounds.max]);
      dispatch(setFilter({ 
        filterName: "priceRange", 
        value: { min: priceBounds.min, max: priceBounds.max }
      }));
    } else if (hotels && hotels.length > 0) {
      // Recalculate if needed (fallback)
      calculatePriceBounds();
    } else {
      // Fallback to default values if no API data
      const defaultPriceRange = [0, 1000];
      setPriceRange(defaultPriceRange);
      dispatch(setFilter({ 
        filterName: "priceRange", 
        value: { min: defaultPriceRange[0], max: defaultPriceRange[1] }
      }));
    }
  };
  
  // Helper function to calculate price bounds from hotel data
  const calculatePriceBounds = () => {
    if (!hotels || hotels.length === 0) return;
    
    const isDmcOnly = filters.priceMode && 
                     Array.isArray(filters.priceMode) && 
                     filters.priceMode.length === 1 && 
                     filters.priceMode[0] === "dmc";
    
    let minPrice = Number.MAX_VALUE;
    let maxPrice = 0;
    
    hotels.forEach(hotel => {
      let hotelPrice = 0;
      
      if (isDmcOnly && hotel.dmc_price > 0) {
        hotelPrice = parseFloat(hotel.dmc_price);
      } else {
        const dmcPrice = hotel.dmc_price > 0 ? parseFloat(hotel.dmc_price) : Number.MAX_VALUE;
        const travclicksPrice = hotel.travclicks_price > 0 ? parseFloat(hotel.travclicks_price) : Number.MAX_VALUE;
        hotelPrice = Math.min(dmcPrice, travclicksPrice);
      }
      
      const convertedPrice = hotelPrice * exchangeRate;
      
      if (convertedPrice > 0 && convertedPrice < minPrice && isFinite(convertedPrice)) {
        minPrice = convertedPrice;
      }
      if (convertedPrice > maxPrice && isFinite(convertedPrice)) {
        maxPrice = convertedPrice;
      }
    });
    
    minPrice = Math.max(0, Math.floor(minPrice / 100) * 100);
    maxPrice = Math.ceil(maxPrice / 100) * 100;
    
    if (minPrice === Number.MAX_VALUE || !isFinite(minPrice)) minPrice = 0;
    if (maxPrice === 0 || !isFinite(maxPrice)) maxPrice = 25000;
    
    // Add a reasonable cap to prevent extreme values
    maxPrice = Math.min(maxPrice, 1000000);
    
    // Only update if the values have actually changed
    if (priceBounds?.min !== minPrice || priceBounds?.max !== maxPrice) {
      // Update price bounds in Redux
      dispatch(setPriceBounds({ 
        min: minPrice, 
        max: maxPrice 
      }));
      
      // Only update the filter range if it hasn't been set by the user yet
      if (!apiDataLoaded) {
        // Update price range in component state
        setPriceRange([minPrice, maxPrice]);
        
        // Update Redux store
        dispatch(setFilter({ 
          filterName: "priceRange", 
          value: { min: minPrice, max: maxPrice }
        }));
      }
      
      // Mark API data as loaded
      setApiDataLoaded(true);
    }
  };
  
  
  return (
    <>
      <div className="sidebar__item -no-border position-relative">
        {/* <Map /> */}
      </div>
      {/* End find map */}

      {/* <div className="sidebar__item -no-border">
        <h5 className="text-18 fw-500 mb-10">Search by property name</h5>
        <SearchBox />
      </div> */}
      {/* End search box */}
      
      <div className="sidebar__item pr-10">
        <h5 className="text-18 fw-500 mb-10">Price Mode</h5>
        <div className="sidebar-checkbox">
          <PriceModeFilter 
            showDmcOnly={showDmcOnly} 
            onChange={handlePriceModeChange}
          />
        </div>
      </div>
      {/* End price mode filter */}

      {/* <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Deals</h5>
        <div className="sidebar-checkbox">
          <div className="row y-gap-5 items-center">
            <DealsFilter />
          </div>
        </div>
      </div> */}
      {/* End deals filter */}

      {/* <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Popular Filters</h5>
        <div className="sidebar-checkbox">
          <PopularFilters />
        </div>
      </div> */}
        {/* End Sidebar-checkbox */}
      {/* End popular filter */}
      {PriceHide === "0" && (
      <div className="sidebar__item pb-30 pr-10">
        <div className="d-flex justify-content-between align-items-center">
          <h5 className="text-18 fw-500 mb-0">Price Range</h5>
          <button 
            onClick={handleResetPriceRange}
            className="button -dark-1 py-10 px-15 rounded-1 bg-blue-1 text-white"
          >
            Reset
          </button>
        </div>

        <div className="row x-gap-10 y-gap-30">
          <div className="col-12">
            <PirceSlider
              priceRange={priceRange}
              setPriceRange={setPriceRange}
              handlePriceChange={handlePriceChange}
              handleManualPriceChange={handleManualPriceChange}
              priceBounds={priceBounds} // Pass price bounds to the slider
            />
          </div>
        </div>
      </div>
      )}
      {/* End Nightly priceslider */}
{/* 
      <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Aminities</h5>
        <div className="sidebar-checkbox">
          <AminitesFilter />
        </div>
      </div> */}
        {/* End Sidebar-checkbox */}
      {/* End Aminities filter */}

      {/* <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Star Rating</h5>
        <div className="row x-gap-10 y-gap-10 pt-10">
          <RatingsFilter />
        </div>
      </div> */}
      {/* End rating filter */}

      {/* <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Guest Rating</h5>
        <div className="sidebar-checkbox">
          <GuestRatingFilters />
        </div>
      </div> */}
      {/* End Guest Rating */}

      {/* <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Style</h5>
        <div className="sidebar-checkbox">
          <StyleFilter />
        </div>
      </div> */}
    

      {/* <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Neighborhood</h5>
        <div className="sidebar-checkbox">
          <NeighborhoddFilter />
        </div>
       
      </div> */}
      
    </>
  );
};

export default Sidebar;
