import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  fetchLocalZone,
  setPicktype,
  setSelectbooking,
} from "@/slice/localtour/Localslice";

const AttractionRestaurantSearch = ({ onSelect, dayIndex = 0 }) => {
  const dispatch = useDispatch();
  const attractions = useSelector((state) => state.attractions.attractions);
  const restaurants = useSelector((state) => state.restaurants.restaurants);
  
  const [searchTerm, setSearchTerm] = useState("");
  const [showDropdown, setShowDropdown] = useState(false);
  const [selectedItem, setSelectedItem] = useState(null);
  const dropdownRef = useRef(null);
  
  // Create unique ID for the input
  const inputId = `attraction-restaurant-search-day-${dayIndex}`;

  // Filter attractions and restaurants based on search term
  const filteredAttractions = attractions?.filter(attraction => 
    attraction.attraction_name.toLowerCase().includes(searchTerm.toLowerCase())
  ) || [];
  
  const filteredRestaurants = restaurants?.filter(restaurant => 
    restaurant.restaurant_name.toLowerCase().includes(searchTerm.toLowerCase())
  ) || [];

  // Handle input change
  const handleInputChange = (e) => {
    setSearchTerm(e.target.value);
    setShowDropdown(true);
  };

  // Handle selection of an attraction or restaurant
  const handleSelect = (item, type) => {
    const selectedData = {
      id: item.id,
      name: type === 'attraction' ? item.attraction_name : item.restaurant_name,
      type: type
    };
    
    setSelectedItem(selectedData);
    setSearchTerm(selectedData.name);
    setShowDropdown(false);
    
    // Create booking object for dispatch
    const booking = {
      service_details: {
        id: selectedData.id,
        name: selectedData.name
      }
    };
    
    // Call dispatch functions (from handleBookTransfer)
    dispatch(fetchLocalZone({ id: selectedData.id, type: selectedData.type }));
    dispatch(setSelectbooking(booking));
    dispatch(setPicktype(selectedData.type));
    
    // Call parent onSelect if provided
    if (onSelect) {
      onSelect(selectedData);
    }
  };

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setShowDropdown(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  return (
    <div className="searchMenu-loc pr-10 pl-10 mb-20 js-form-dd js-liverSearch">
      <div className="d-flex">
        <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
        <div className="ml-10 flex-grow-1">
          <h4 className="text-15 fw-500 ls-2 lh-16 mb-15">
            Pick Up Location
          </h4>
          <div className="text-15 text-light-1 ls-2 lh-16 position-relative" ref={dropdownRef}>
            <input
              id={inputId}
              type="text"
              placeholder="where is your pickup?"
              className="js-search js-dd-focus w-full"
              value={searchTerm}
              onChange={handleInputChange}
              onClick={() => setShowDropdown(true)}
            />
            
            {showDropdown && (searchTerm.length > 0 || filteredAttractions.length > 0 || filteredRestaurants.length > 0) && (
              <div className="location-dropdown">
                {/* Attractions Section */}
                {filteredAttractions.length > 0 && (
                  <div className="location-category">
                    <div className="category-heading">Attractions</div>
                    {filteredAttractions.map((attraction) => (
                      <div
                        key={`attraction-${attraction.id}-day-${dayIndex}`}
                        className="location-item"
                        onClick={() => handleSelect(attraction, 'attraction')}
                      >
                        {attraction.attraction_name}
                      </div>
                    ))}
                  </div>
                )}

                {/* Restaurants Section */}
                {filteredRestaurants.length > 0 && (
                  <div className="location-category">
                    <div className="category-heading">Restaurants</div>
                    {filteredRestaurants.map((restaurant) => (
                      <div
                        key={`restaurant-${restaurant.id}-day-${dayIndex}`}
                        className="location-item"
                        onClick={() => handleSelect(restaurant, 'restaurant')}
                      >
                        {restaurant.restaurant_name}
                      </div>
                    ))}
                  </div>
                )}

                {/* No results message */}
                {searchTerm.length > 0 && filteredAttractions.length === 0 && filteredRestaurants.length === 0 && (
                  <div className="no-results">No matching results found</div>
                )}
              </div>
            )}
          </div>
        </div>
      </div>

      {/* CSS for the dropdown */}
      <style>
        {`
          .position-relative {
            position: relative !important;
          }

          .location-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 10001;
          }

          .location-category {
            margin-bottom: 10px;
          }

          .category-heading {
            padding: 8px 12px;
            font-weight: 600;
            background-color: #f5f5f5;
            color: #333;
            border-bottom: 1px solid #eee;
          }

          .location-item {
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.2s;
          }

          .location-item:hover {
            background-color: #f0f7ff;
          }

          .no-results {
            padding: 12px;
            color: #666;
            text-align: center;
            font-style: italic;
          }
        `}
      </style>
    </div>
  );
};

export default AttractionRestaurantSearch; 