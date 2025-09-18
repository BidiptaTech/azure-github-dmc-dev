import React, { useState, useRef, useEffect } from 'react';
import { Box, Typography, Grid, Button } from '@mui/material';
import { useSelector } from 'react-redux';
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

const Overview = ({ hotel }) => {
  // Get hotel details from Redux store
  const hotelDetails = useSelector((state) => state.hoteldetails.bookingDetails);
  const [expanded, setExpanded] = useState(false);
  const descriptionRef = useRef(null);
  const [showSeeMore, setShowSeeMore] = useState(false);
  
  const createMarkup = (htmlContent) => {
    return { __html: htmlContent };
  };

  // Check if description height exceeds 5 lines (roughly 120px)
  useEffect(() => {
    if (descriptionRef.current) {
      const lineHeight = parseInt(getComputedStyle(descriptionRef.current).lineHeight);
      const maxHeight = lineHeight * 5; // 5 lines
      
      if (descriptionRef.current.scrollHeight > maxHeight) {
        setShowSeeMore(true);
      }
    }
  }, [hotelDetails]);

  // Function to get appropriate icon for facility category
  const getCategoryIcon = (category) => {
    if (!category) return <RoomServiceIcon />;
    
    const categoryLower = category.trim().toLowerCase();
    
    if (categoryLower.includes('wifi') || categoryLower.includes('internet')) return <WifiIcon />;
    if (categoryLower.includes('smoke') || categoryLower.includes('smoking')) return <SmokeFreeIcon />;
    if (categoryLower.includes('park') || categoryLower.includes('transport')) return <LocalParkingIcon />;
    if (categoryLower.includes('kitchen') || categoryLower.includes('food') || categoryLower.includes('dining')) return <RestaurantIcon />;
    if (categoryLower.includes('living') || categoryLower.includes('lounge') || categoryLower.includes('accommodation')) return <WeekendIcon />;
    if (categoryLower.includes('security') || categoryLower.includes('safe')) return <SecurityIcon />;
    if (categoryLower.includes('spa') || categoryLower.includes('wellness')) return <HotTubIcon />;
    if (categoryLower.includes('pool') || categoryLower.includes('swimming') || categoryLower.includes('recreation')) return <PoolIcon />;
    if (categoryLower.includes('gym') || categoryLower.includes('fitness') || categoryLower.includes('health')) return <FitnessCenterIcon />;
    if (categoryLower.includes('ac') || categoryLower.includes('air')) return <AcUnitIcon />;
    
    // Default icon for other facilities
    return <RoomServiceIcon />;
  };

  return (
    <>
      <h3 className="text-22 fw-500 pt-40 border-top-light">Overview</h3>
      
      {/* If description exists, render it using dangerouslySetInnerHTML */}
      {hotelDetails?.description ? (
        <Box className="mt-20">
          <Box 
            ref={descriptionRef}
            dangerouslySetInnerHTML={createMarkup(hotelDetails.description)}
            className="text-dark-1 text-15"
            sx={{
              maxHeight: expanded ? 'none' : '92px', // Approximately 5 lines
              overflow: expanded ? 'visible' : 'hidden',
              position: 'relative',
              transition: 'max-height 0.3s ease'
            }}
          />
          {showSeeMore && (
            <Button 
              variant="text" 
              color="primary" 
              onClick={() => setExpanded(!expanded)}
              sx={{ 
                padding: '4px 0', 
                marginTop: '8px',
                textTransform: 'none',
                fontWeight: 500,
                fontSize: '14px'
              }}
            >
              {expanded ? 'Show less' : 'See more'}
            </Button>
          )}
        </Box>
      ) : hotel?.description ? (
        <Box className="mt-20">
          <Box 
            ref={descriptionRef}
            dangerouslySetInnerHTML={createMarkup(hotel.description)} 
            className="text-dark-1 text-15"
            sx={{
              maxHeight: expanded ? 'none' : '120px', // Approximately 5 lines
              overflow: expanded ? 'visible' : 'hidden',
              position: 'relative',
              transition: 'max-height 0.3s ease'
            }}
          />
          {showSeeMore && (
            <Button 
              variant="text" 
              color="primary" 
              onClick={() => setExpanded(!expanded)}
              sx={{ 
                padding: '4px 0', 
                marginTop: '8px',
                textTransform: 'none',
                fontWeight: 500,
                fontSize: '14px'
              }}
            >
              {expanded ? 'Show less' : 'See more'}
            </Button>
          )}
        </Box>
      ) : hotel?.Overview ? (
        <div>
          {hotel?.Overview}
        </div>
      ) : (
        <>
          <p className="text-dark-1 text-15 mt-20">
            You can directly book the best price if your travel dates are available,
            all discounts are already included. In the following house description
            you will find all information about our listing.
            <br />
            <br />
            2-room terraced house on 2 levels. Comfortable and cosy furnishings: 1
            room with 1 french bed and radio. Shower, sep. WC. Upper floor: (steep
            stair) living/dining room with 1 sofabed (110 cm, length 180 cm), TV.
            Exit to the balcony. Small kitchen (2 hot plates, oven,
          </p>
          <a
            href="#"
            className="d-block text-14 text-blue-1 fw-500 underline mt-10"
          >
            Show More
          </a>
        </>
      )}
      
      {/* Highlights section styled like Agoda */}
      {hotelDetails?.facilities && hotelDetails.facilities.length > 0 ? (
        <Box className="mt-30 border-top-light pt-30">
          <h3 className="text-22 fw-500 pt-40 border-top-light py-20">Highlights</h3>
          
          <Grid container spacing={3}>
            {hotelDetails.facilities.slice(0, 3).map((facility, index) => (
              <Grid item xs={12} sm={6} md={4} key={index}>
                <Box 
                  sx={{ 
                    display: 'flex',
                    alignItems: 'flex-start',
                    mb: 1,
                    border: '1px solid #e9e9e9',
                    borderRadius: '8px',
                    padding: '10px',
                    height: '90%',
                    transition: 'all 0.3s ease',
                    '&:hover': {
                      boxShadow: '0px 4px 16px rgba(0, 0, 0, 0.08)',
                    }
                  }}
                >
                  <Box sx={{ 
                    color: 'primary.main', 
                    mr: 2,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    backgroundColor: 'rgba(0, 101, 183, 0.1)',
                    borderRadius: '50%',
                    p: 1
                  }}>
                    {getCategoryIcon(facility.category)}
                  </Box>
                  <Box>
                    <Typography variant="subtitle2" fontWeight="" gutterBottom>
                      {facility.category}
                    </Typography>
                    {/* {facility.facilities && facility.facilities.length > 0 && (
                      <Typography variant="body2" color="text.secondary">
                        {facility.facilities.slice(0, 2).join(', ')}
                        {facility.facilities.length > 2 && '...'}
                      </Typography>
                    )} */}
                  </Box>
                </Box>
              </Grid>
            ))}
          </Grid>
        </Box>
      ) : hotel?.facility_names && hotel.facility_names.length > 0 && (
        <Box className="mt-20">
          <Typography variant="subtitle1" className="fw-500 mb-10">
          Highlights
          </Typography>
          <ul className="text-15 pt-10">
            {hotel.facility_names.map((facility, index) => (
              <li className="d-flex items-center" key={index}>
                <i className="icon-check text-10 mr-20" />
                {facility}
              </li>
            ))}
          </ul>
        </Box>
      )}
      
      {/* If hotel has other important details, display them */}
      {/* {hotelDetails?.hotel_name ? (
        <Box className="mt-20">
          <Typography variant="subtitle1" className="fw-500 mb-10">
            Hotel Details:
          </Typography>
          <ul className="text-15 pt-10">
            <li className="d-flex items-center">
              <i className="icon-check text-10 mr-20" />
              Hotel Name: {hotelDetails.hotel_name}
            </li>
            {hotelDetails.category && (
              <li className="d-flex items-center">
                <i className="icon-check text-10 mr-20" />
                Category: {hotelDetails.category}
              </li>
            )}
            {hotelDetails.location && (
              <li className="d-flex items-center">
                <i className="icon-check text-10 mr-20" />
                Location: {hotelDetails.location}
              </li>
            )}
          </ul>
        </Box>
      )} */}
    </>
  );
};

export default Overview;
