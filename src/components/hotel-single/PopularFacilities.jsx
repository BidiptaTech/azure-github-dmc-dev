import React, { useEffect, useState } from "react";
import { Box, Typography, Grid, Skeleton } from "@mui/material";
import WifiIcon from '@mui/icons-material/Wifi';
import SmokeFreeIcon from '@mui/icons-material/SmokeFree';
import LocalParkingIcon from '@mui/icons-material/LocalParking';
import KitchenIcon from '@mui/icons-material/Kitchen';
import WeekendIcon from '@mui/icons-material/Weekend';
import SecurityIcon from '@mui/icons-material/Security';
import HotTubIcon from '@mui/icons-material/HotTub';
import PoolIcon from '@mui/icons-material/Pool';
import FitnessCenterIcon from '@mui/icons-material/FitnessCenter';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import AcUnitIcon from '@mui/icons-material/AcUnit';
import RoomServiceIcon from '@mui/icons-material/RoomService';

const PopularFacilities = ({ hotelDetails }) => {
  const [loading, setLoading] = useState(true);
  const [categorizedFacilities, setCategorizedFacilities] = useState([]);

  // Use useEffect to process facilities after the component mounts and whenever hotel changes
  console.log("PopularFacilities - hotelDetails:", hotelDetails);
  
  useEffect(() => {
    setLoading(true);
    
    if (!hotelDetails) {
      console.log("Hotel details is null or undefined");
      setCategorizedFacilities([]);
      setLoading(false);
      return;
    }
    
    // console.log("Facilities from hotelDetails:", hotelDetails.facilities);
    
    if (hotelDetails.facilities && Array.isArray(hotelDetails.facilities)) {
      // console.log("Using hotelDetails.facilities");
      setCategorizedFacilities(hotelDetails.facilities);
    } else if (hotelDetails.data?.facilities) {
      // console.log("Using hotelDetails.data.facilities");
      setCategorizedFacilities(hotelDetails.data.facilities);
    } else {
      // console.log("No facilities found in hotelDetails");
      // Sample data as fallback
      const sampleFacilitiesByCategory = [
        {
          "category": "Accommodation Facilities",
          "facilities": [
            "Interconnecting Rooms Available",
            "Balcony Rooms Available"
          ]
        },
        {
          "category": "Food & Beverage, Dining Facilities",
          "facilities": [
            "Lounge & Bar (On Site)",
            "Kids Menu Available",
            "A La Carte Restaurant Available",
            "Coffee Shop & Cafe",
            "On Site Restaurant Available"
          ]
        },
        {
          "category": "Recreation and Leisure Activity Facilities",
          "facilities": [
            "Outdoor Swimming Pool"
          ]
        },
        {
          "category": "Health & Wellness Facilities & Services",
          "facilities": [
            "Gym / Fitness Room"
          ]
        },
        {
          "category": "Transport & Parking Facilities",
          "facilities": [
            "Airport Shuttle Service (Chargeable)",
            "EV Charging Station Available (On Site)",
            "Car Rental Desk Available (On Site)",
            "Taxi Booking Assistance Available"
          ]
        }
      ];
      setCategorizedFacilities(sampleFacilitiesByCategory);
    }
    
    setLoading(false);
  }, [hotelDetails]);
  
  // Function to get appropriate icon for facility
  const getFacilityIcon = (facility) => {
    if (!facility) return <RoomServiceIcon />;
    
    // If facility is not a string (could be an object in the new structure), return default
    if (typeof facility !== 'string') return <RoomServiceIcon />;
    
    const facilityLower = facility.trim().toLowerCase();
    
    if (facilityLower.includes('wifi') || facilityLower.includes('internet')) return <WifiIcon />;
    if (facilityLower.includes('smoke') || facilityLower.includes('smoking')) return <SmokeFreeIcon />;
    if (facilityLower.includes('park')) return <LocalParkingIcon />;
    if (facilityLower.includes('kitchen')) return <KitchenIcon />;
    if (facilityLower.includes('living') || facilityLower.includes('lounge')) return <WeekendIcon />;
    if (facilityLower.includes('security') || facilityLower.includes('safe')) return <SecurityIcon />;
    if (facilityLower.includes('spa') || facilityLower.includes('jacuzzi')) return <HotTubIcon />;
    if (facilityLower.includes('pool') || facilityLower.includes('swimming')) return <PoolIcon />;
    if (facilityLower.includes('gym') || facilityLower.includes('fitness')) return <FitnessCenterIcon />;
    if (facilityLower.includes('restaurant') || facilityLower.includes('dining')) return <RestaurantIcon />;
    if (facilityLower.includes('ac') || facilityLower.includes('air')) return <AcUnitIcon />;
    
    // Default icon for other facilities
    return <RoomServiceIcon />;
  };

  // If still loading, show skeleton
  if (loading) {
    return (
      <Grid container spacing={3}>
        {[1, 2, 3].map((categoryIndex) => (
          <Grid item xs={12} md={4} key={`category-${categoryIndex}`}>
            <Skeleton variant="text" width={200} height={30} />
            <Skeleton variant="text" width="90%" />
            <Skeleton variant="text" width="80%" />
            <Skeleton variant="text" width="85%" />
          </Grid>
        ))}
      </Grid>
    );
  }

  // Render facilities by category or a fallback message
  return (
    <>
      {categorizedFacilities && categorizedFacilities.length > 0 ? (
        <Grid container spacing={3}>
          {categorizedFacilities.map((category, categoryIndex) => (
            <Grid item xs={12} md={4} key={categoryIndex}>
              <Typography 
                variant="h6" 
                fontWeight="bold"
                mb={1}
              >
                {category.category}
              </Typography>
              
              {category.facilities && category.facilities.length > 0 ? (
                category.facilities.map((facility, facilityIndex) => (
                  <Box 
                    key={`${categoryIndex}-${facilityIndex}`}
                    display="flex" 
                    alignItems="center" 
                    gap={1} 
                    mb={0.5}
                  >
                    {getFacilityIcon(facility)}
                    <Typography variant="body2">{facility}</Typography>
                  </Box>
                ))
              ) : (
                <Typography variant="body2" color="text.secondary">
                  No facilities in this category
                </Typography>
              )}
            </Grid>
          ))}
        </Grid>
      ) : (
        <Box textAlign="center" py={2}>
          <Typography variant="body2" color="text.secondary">
            No facilities information available
          </Typography>
          {hotelDetails && hotelDetails.facilities === "" && (
            <Typography variant="caption" display="block" color="text.secondary" mt={1}>
              (Facilities data is empty)
            </Typography>
          )}
          {!hotelDetails && (
            <Typography variant="caption" display="block" color="error" mt={1}>
              Hotel data is not loaded yet
            </Typography>
          )}
        </Box>
      )}
    </>
  );
};

export default PopularFacilities;
