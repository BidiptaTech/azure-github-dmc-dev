import React, { useEffect, useState } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { fetchCitiesByCountry } from '@/slice/common/citiesSlice';
import { fetchEnquiryList } from '@/slice/common/enquiryListSlice';

const CitySelector = () => {
  const dispatch = useDispatch();
  const [selectedCity, setSelectedCity] = useState('');
  
  // Get user_country from auth state
  const userCountry = useSelector((state) => state.auth.user_country);
  console.log("userCountry", userCountry);
  
  // Get cities from cities state
  const { cities, loading: citiesLoading, error: citiesError } = useSelector((state) => state.cities);
  
  // Get enquiry list data
  const { 
    hotels, 
    attractions, 
    restaurants, 
    guides, 
    drivers,
    loading: enquiryLoading, 
    error: enquiryError 
  } = useSelector((state) => state.enquiryList);

  // Fetch cities when component mounts or userCountry changes
  useEffect(() => {
    if (userCountry) {
      dispatch(fetchCitiesByCountry(userCountry));
    }
  }, [dispatch, userCountry]);

  // Handle city selection
  const handleCityChange = (e) => {
    const cityValue = e.target.value;
    setSelectedCity(cityValue);
    
    // Fetch enquiry list data when both country and city are available
    if (userCountry && cityValue) {
      dispatch(fetchEnquiryList({ country: userCountry, city: cityValue }));
    }
  };

  return (
    <div className="city-selector">
      <h2>Select a City</h2>
      
      {citiesLoading && <p>Loading cities...</p>}
      {citiesError && <p className="error">Error loading cities: {citiesError}</p>}
      
      {userCountry ? (
        <>
          <p>Country: {userCountry}</p>
          
          <select 
            value={selectedCity} 
            onChange={handleCityChange}
            disabled={citiesLoading || !cities.length}
          >
            <option value="">Select a city</option>
            {cities.map((city, index) => (
              <option key={index} value={city}>
                {city}
              </option>
            ))}
          </select>
          
          {enquiryLoading && <p>Loading data for {selectedCity}...</p>}
          {enquiryError && <p className="error">Error: {enquiryError}</p>}
          
          {/* Display results when available */}
          {selectedCity && !enquiryLoading && (
            <div className="results">
              <h3>Results for {selectedCity}</h3>
              
              {hotels.length > 0 && (
                <div className="section">
                  <h4>Hotels ({hotels.length})</h4>
                  <ul>
                    {hotels.map((hotel) => (
                      <li key={hotel.id}>{hotel.name}</li>
                    ))}
                  </ul>
                </div>
              )}
              
              {attractions.length > 0 && (
                <div className="section">
                  <h4>Attractions ({attractions.length})</h4>
                  <ul>
                    {attractions.map((attraction) => (
                      <li key={attraction.id}>{attraction.name}</li>
                    ))}
                  </ul>
                </div>
              )}
              
              {restaurants.length > 0 && (
                <div className="section">
                  <h4>Restaurants ({restaurants.length})</h4>
                  <ul>
                    {restaurants.map((restaurant) => (
                      <li key={restaurant.id}>{restaurant.name}</li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          )}
        </>
      ) : (
        <p>No country selected. Please set a country in your profile.</p>
      )}
    </div>
  );
};

export default CitySelector; 