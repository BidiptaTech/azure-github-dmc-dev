import React, { useEffect, useState } from "react";
import { Box, Typography, Card, CardContent, Divider, Chip } from "@mui/material";
import WifiIcon from '@mui/icons-material/Wifi';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import TvIcon from '@mui/icons-material/Tv';
import LocationCityIcon from '@mui/icons-material/LocationCity';
import LocalParkingIcon from '@mui/icons-material/LocalParking';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import PoolIcon from '@mui/icons-material/Pool';
import StarIcon from '@mui/icons-material/Star';
import HotelIcon from '@mui/icons-material/Hotel';

const PropertyHighlights2 = ({ hotel }) => {
  const [highlights, setHighlights] = useState([]);
  const [categories, setCategories] = useState([]);

  useEffect(() => {
    // Log hotel object to help debug
    console.log("Hotel object in PropertyHighlights2:", hotel);
    
    // Extract categories and generate highlights
    if (hotel) {
      extractCategories(hotel);
      generateHighlights(hotel);
    } else {
      setHighlights(getDefaultHighlights());
      setCategories([]);
    }
  }, [hotel]);

  const extractCategories = (hotelData) => {
    const extractedCategories = [];
    console.log("Starting category extraction from:", hotelData);
    
    // Add "Wifi" category if available in the data
    if (hotelData.category === "Wifi" || 
        (hotelData.facility_names && 
         hotelData.facility_names.some(facility => 
           facility.toLowerCase().includes('wifi')))) {
      console.log("Found Wifi category in data");
      extractedCategories.push({
        name: "Wifi",
        icon: <WifiIcon />,
        description: "Free WiFi available"
      });
    }

    // If the hotel has a category property directly
    if (hotelData.category && !extractedCategories.some(cat => cat.name === hotelData.category)) {
      console.log("Found direct category property:", hotelData.category);
      extractedCategories.push({
        name: hotelData.category,
        icon: getCategoryIcon(hotelData.category),
        description: `${hotelData.category} available`
      });
    }

    // Extract star rating if available
    if (hotelData.ratings) {
      console.log("Found ratings:", hotelData.ratings);
      extractedCategories.push({
        name: "Rating",
        icon: <StarIcon />,
        description: `${hotelData.ratings}-star hotel`
      });
    }

    // Assume 4-star based on description (from provided sample)
    if (!hotelData.ratings && hotelData.description && 
        hotelData.description.toLowerCase().includes('4-star')) {
      console.log("Found 4-star mention in description");
      extractedCategories.push({
        name: "Rating",
        icon: <StarIcon />,
        description: "4-star hotel"
      });
    }

    // Extract location data if available
    if (hotelData.location) {
      console.log("Found location:", hotelData.location);
      extractedCategories.push({
        name: "Location",
        icon: <LocationCityIcon />,
        description: hotelData.location
      });
    }

    // Process facility names to create categories
    if (hotelData.facility_names && Array.isArray(hotelData.facility_names)) {
      console.log("Processing facility_names:", hotelData.facility_names);
      hotelData.facility_names.forEach(facility => {
        if (facility.toLowerCase() !== 'wifi' && 
            !extractedCategories.some(cat => cat.name === facility)) {
          console.log("Adding facility as category:", facility);
          extractedCategories.push({
            name: facility,
            icon: getCategoryIcon(facility),
            description: `${facility} available`
          });
        }
      });
    }

    // If price_mode exists, add it as a category
    if (hotelData.price_mode) {
      // console.log("Found price_mode:", hotelData.price_mode);
      extractedCategories.push({
        name: hotelData.price_mode.toUpperCase(),
        icon: <StarIcon />,
        description: `${hotelData.price_mode.toUpperCase()} pricing`
      });
    }

    console.log("Extracted categories:", extractedCategories);
    setCategories(extractedCategories);
  };

  const getCategoryIcon = (category) => {
    if (!category) return <HotelIcon />;
    
    const categoryLower = category.toLowerCase();
    
    if (categoryLower.includes('wifi') || categoryLower.includes('internet')) 
      return <WifiIcon />;
    if (categoryLower.includes('airport') || categoryLower.includes('shuttle')) 
      return <FlightTakeoffIcon />;
    if (categoryLower.includes('time') || categoryLower.includes('24')) 
      return <AccessTimeIcon />;
    if (categoryLower.includes('tv') || categoryLower.includes('television')) 
      return <TvIcon />;
    if (categoryLower.includes('city') || categoryLower.includes('location')) 
      return <LocationCityIcon />;
    if (categoryLower.includes('park')) 
      return <LocalParkingIcon />;
    if (categoryLower.includes('restaurant') || categoryLower.includes('dining')) 
      return <RestaurantIcon />;
    if (categoryLower.includes('pool') || categoryLower.includes('swim')) 
      return <PoolIcon />;
    
    // Default icon
    return <HotelIcon />;
  };

  const generateHighlights = (hotelData) => {
    if (!hotelData) {
      console.log("Hotel data is not available for highlights");
      setHighlights(getDefaultHighlights());
      return;
    }

    // Parse facilities
    let facilitiesArray = [];
    if (hotelData.facilities) {
      if (Array.isArray(hotelData.facilities)) {
        facilitiesArray = hotelData.facilities;
      } else if (typeof hotelData.facilities === 'string') {
        facilitiesArray = hotelData.facilities
          .split(',')
          .map(item => item.trim())
          .filter(item => item !== '');
      }
    }
    
    // Add facility_names if available
    if (hotelData.facility_names && Array.isArray(hotelData.facility_names)) {
      facilitiesArray = [...facilitiesArray, ...hotelData.facility_names];
    }

    console.log("Facilities array for highlights:", facilitiesArray);

    // Create dynamic highlights based on available data
    const dynamicHighlights = [];

    // Add location highlight if available
    if (hotelData.location) {
      dynamicHighlights.push({
        id: 1,
        icon: "icon-city",
        text: `In ${hotelData.location}`,
        category: "Location"
      });
    }

    // Add hotel name if available
    if (hotelData.hotel_name) {
      dynamicHighlights.push({
        id: dynamicHighlights.length + 1,
        icon: "icon-bed",
        text: hotelData.hotel_name,
        category: "Hotel Name"
      });
    }

    // Check for common facilities and add them as highlights
    const facilityMappings = [
      { keywords: ['wifi', 'internet', 'wi-fi'], icon: "icon-wifi", text: "Free WiFi", category: "Wifi" },
      { keywords: ['airport', 'shuttle', 'transfer'], icon: "icon-airplane", text: "Airport transfer", category: "Transport" },
      { keywords: ['front desk', '24-hour', 'reception'], icon: "icon-bell-ring", text: "Front desk [24-hour]", category: "Service" },
      { keywords: ['tv', 'television', 'premium', 'cable'], icon: "icon-tv", text: "Premium TV channels", category: "Entertainment" },
      { keywords: ['parking', 'park'], icon: "icon-parking", text: "Parking available", category: "Parking" },
      { keywords: ['restaurant', 'dining'], icon: "icon-food", text: "Restaurant", category: "Dining" },
      { keywords: ['pool', 'swimming'], icon: "icon-pool", text: "Swimming pool", category: "Leisure" },
      { keywords: ['breakfast', 'meal'], icon: "icon-food", text: "Breakfast included", category: "Dining" }
    ];

    // Match facilities with our mappings
    facilityMappings.forEach(mapping => {
      // Check if any facility contains any of the mapping keywords
      const matchedFacility = facilitiesArray.find(facility => 
        mapping.keywords.some(keyword => 
          facility.toLowerCase().includes(keyword)
        )
      );
      
      if (matchedFacility) {
        dynamicHighlights.push({
          id: dynamicHighlights.length + 1,
          icon: mapping.icon,
          text: mapping.text,
          category: mapping.category
        });
      }
    });

    // Don't add default highlights if we have at least 1 dynamic highlight
    if (dynamicHighlights.length > 0) {
      // Limit to 3 highlights maximum for display
      setHighlights(dynamicHighlights.slice(0, 3));
    } else {
      // Only use default highlights if we found nothing
      setHighlights(getDefaultHighlights());
    }
  };

  const getDefaultHighlights = () => {
    return [
      {
        id: 1,
        icon: "icon-city",
        text: `In City Centre`,
        category: "Location"
      },
      {
        id: 2,
        icon: "icon-airplane",
        text: `Airport transfer`,
        category: "Transport"
      },
      {
        id: 3,
        icon: "icon-bell-ring",
        text: `Front desk [24-hour]`,
        category: "Service"
      }
    ];
  };

  // Extract brief description from HTML if available
  const getCleanDescription = (html) => {
    if (!html) return "";
    
    // Simple regex to extract text from HTML
    const textOnly = html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    
    // Return first 100 characters with ellipsis if longer
    return textOnly.length > 100 ? textOnly.substring(0, 100) + '...' : textOnly;
  };

  return (
    <div className="px-30 py-30 border-light rounded-4 mt-30">
      <div className="text-18 fw-500 pb-30">Property highlights</div>
      
      {/* Display hotel description if available */}
      {hotel?.description && (
        <div className="mb-20">
          <Typography variant="body2" color="text.secondary" className="mb-10">
            {getCleanDescription(hotel.description)}
          </Typography>
          <Divider className="my-15" />
        </div>
      )}

      {/* Display property categories as chips */}
      {categories.length > 0 && (
        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mb: 3 }}>
          {categories.map((category, index) => (
            <Chip
              key={index}
              icon={category.icon}
              label={category.name}
              variant="outlined"
              color="primary"
              size="small"
            />
          ))}
        </Box>
      )}

      {/* Traditional highlights display */}
      {highlights.map((item) => (
        <div className="row x-gap-20 y-gap-20" key={item.id}>
          <div className="col-auto">
            <i className={`${item.icon} text-24 text-blue-1`} />
          </div>
          <div className="col-auto">
            <div className="text-15 ">{item.text}</div>
            <div className="text-14 text-light-1">{item.category}</div>
          </div>
        </div>
      ))}
    </div>
  );
};

export default PropertyHighlights2;
