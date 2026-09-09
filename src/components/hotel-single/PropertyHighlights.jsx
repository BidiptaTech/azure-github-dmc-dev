import React, { useEffect, useState } from "react";
import { Box, Typography, Chip } from "@mui/material";
import WifiIcon from '@mui/icons-material/Wifi';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import TvIcon from '@mui/icons-material/Tv';
import HotelIcon from '@mui/icons-material/Hotel';
import StarIcon from '@mui/icons-material/Star';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';

const PropertyHighlights = ({ hotel }) => {
  const [highlights, setHighlights] = useState([]);

  useEffect(() => {
    if (hotel) {
      generateHighlights(hotel);
    } else {
      setHighlights(getDefaultHighlights());
    }
  }, [hotel]);

  const getIconComponent = (iconName) => {
    switch (iconName) {
      case "icon-wifi":
        return <WifiIcon className="text-blue-1" />;
      case "icon-airplane":
        return <FlightTakeoffIcon className="text-blue-1" />;
      case "icon-bell-ring":
        return <AccessTimeIcon className="text-blue-1" />;
      case "icon-tv":
        return <TvIcon className="text-blue-1" />;
      case "icon-bed":
        return <HotelIcon className="text-blue-1" />;
      case "icon-star":
        return <StarIcon className="text-blue-1" />;
      case "icon-check":
        return <CheckCircleIcon className="text-green-2" />;
      case "icon-close":
        return <CancelIcon className="text-red-1" />;
      default:
        return null;
    }
  };

  const generateHighlights = (hotelData) => {
    console.log("Generating highlights from hotel data:", hotelData);
    const dynamicHighlights = [];

    // First, always add WiFi if available in category or facility_names
    if (hotelData.category === "Wifi" || 
        (hotelData.facility_names && 
         hotelData.facility_names.some(facility => 
           facility.toLowerCase().includes('wifi')))) {
      dynamicHighlights.push({
        id: dynamicHighlights.length + 1,
        icon: "icon-wifi",
        text: "Free WiFi",
        category: "Wifi"
      });
    }

    // Add cancellation policy if available
    if (hotelData.cancellation === true) {
      dynamicHighlights.push({
        id: dynamicHighlights.length + 1,
        icon: "icon-check",
        text: "Free Cancellation",
        category: "Cancellation"
      });
    } else if (hotelData.cancellation === false) {
      dynamicHighlights.push({
        id: dynamicHighlights.length + 1,
        icon: "icon-close",
        text: "Non-Refundable",
        category: "Cancellation"
      });
    }

    // Add location if available
    if (hotelData.location) {
      dynamicHighlights.push({
        id: dynamicHighlights.length + 1,
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
        category: "Hotel"
      });
    }

    // Add price mode if available
    if (hotelData.price_mode) {
      dynamicHighlights.push({
        id: dynamicHighlights.length + 1,
        icon: "icon-star",
        text: hotelData.price_mode.toUpperCase(),
        category: "Price Mode"
      });
    }

    // Add 24-hour service only if we have less than 4 highlights
    if (dynamicHighlights.length < 4) {
      dynamicHighlights.push({
        id: dynamicHighlights.length + 1,
        icon: "icon-bell-ring",
        text: "Front desk [24-hour]",
        category: "Service"
      });
    }

    // Use all the highlights we generated or default ones if none
    if (dynamicHighlights.length > 0) {
      setHighlights(dynamicHighlights.slice(0, 4)); // Limit to 4 highlights
    } else {
      setHighlights(getDefaultHighlights());
    }
  };

  const getDefaultHighlights = () => {
    return [
      {
        id: 1,
        icon: "icon-city",
        text: "In City Centre",
        category: "Location"
      },
      {
        id: 2,
        icon: "icon-airplane",
        text: "Airport transfer",
        category: "Transport"
      },
      {
        id: 3,
        icon: "icon-bell-ring",
        text: "Front desk [24-hour]",
        category: "Service"
      },
      {
        id: 4,
        icon: "icon-tv",
        text: "Premium TV channels",
        category: "Entertainment"
      },
    ];
  };

  return (
    <div className="row y-gap-20 pt-30">
      {highlights.map((item) => (
        <div className="col-lg-3 col-6" key={item.id}>
          <div className="text-center">
            <i className={`${item.icon} text-24 text-blue-1`} />
            <div className="text-15 lh-1 mt-10">{item.text}</div>
            
            {/* Display category as a chip */}
            <Box sx={{ mt: 1, display: 'flex', justifyContent: 'center' }}>
              <Chip 
                label={item.category}
                size="small"
                variant="outlined"
                color="primary"
                icon={getIconComponent(item.icon)}
              />
            </Box>
          </div>
        </div>
      ))}
    </div>
  );
};

export default PropertyHighlights;
