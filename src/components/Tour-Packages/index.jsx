import React, { useState, useEffect } from 'react';
import { Container, Typography, Box, Grid, Paper, Button } from '@mui/material';
import { useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import SearchForm from './common/SearchForm';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import TravelExploreIcon from '@mui/icons-material/TravelExplore';
import { setSearchCriteria} from '@/slice/tour-packages/tourPackageSlice';
import { useDispatch } from 'react-redux';


// Import service components
import Itinerary from './common/Itinerary';

// Import icons
import ViewTimelineIcon from '@mui/icons-material/ViewTimeline';
import { settourdetails,updateSearchState } from '@/slice/hotel/hotelSlice';
import { fetchCitiesByCountry } from '@/slice/common/citiesSlice';

export default function TourPackages() {
  const [currentStep, setCurrentStep] = useState(1);
  const dispatch = useDispatch();
  const packageData = useSelector((state) => state.tourPackages.packageData);

   // Fetch cities when a country is selected
   
  console.log("packageData", packageData);
  // useEffect(() => {
  //   if(packageData){
  //     dispatch(settourdetails(packageData.tour));
  //     // dispatch(fetchCitiesByCountry(packageData.tour.destination))
  //     // .unwrap()
  //     // .then((response) => {
  //     //   dispatch(setCity(response.data));
  //     // })
  //     // .catch((error) => {
  //     //   console.error("Error fetching cities:", error);
  //     // });
  //     // fetchCities(packageData.tour.destination);
  //     dispatch(updateSearchState({ location: packageData.tour.destination }));
  //     dispatch(setSearchCriteria({
  //       destination: packageData.tour.destination,
  //       checkIn: packageData.tour.check_in_time,
  //       checkOut: packageData.tour.check_out_time,
  //       guests: {
  //         adults: packageData.tour.adult,
  //         children: packageData.tour.child,
  //         infants: packageData.tour.infant,
  //         maleCount: packageData.tour.male_count,
  //         femaleCount: packageData.tour.female_count,
  //         childrenAges: packageData.tour.child_ages ? packageData.tour.child_ages.split(', ') : [],
  //       }
  //     }));
      
  //     // Dispatch the booking services if they exist
  //     // if (packageData.tour.booking && Array.isArray(packageData.tour.booking)) {
  //     //   dispatch(setAllServices(packageData.tour.booking));
  //     // }
      
  //     //setCurrentStep(2);
  //   }
  // }, [packageData]);



  const handleNext = () => {
    setCurrentStep(currentStep + 1);
  };

  const handleBookingSuccess = () => {
    setCurrentStep(1);
  };

  return (
    <Box sx={{ bgcolor: '#f8fafc', minHeight: '102vh' }}>
      <Container maxWidth="lg" sx={{ py: 2 }}>
        {/* Compact Header */}
        <Box 
          sx={{ 
            display: 'flex', 
            alignItems: 'center', 
            justifyContent: 'space-between',
            mb: 2,
            bgcolor: 'white',
            p: 1.5,
            borderRadius: 2,
            boxShadow: '0 1px 4px rgba(0,0,0,0.04)'
          }}
        >
          <Button 
            component={Link}
            to="/dashboard/db-dashboard/tour-packages"
            variant="outlined"
            startIcon={<ArrowBackIcon />}
            size="small"
            sx={{ 
              borderColor: '#e2e8f0',
              color: '#64748b',
              fontSize: '0.875rem',
              '&:hover': { 
                borderColor: '#cbd5e1',
                bgcolor: '#f8fafc'
              }
            }}
          >
            Back
          </Button>
          
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <TravelExploreIcon sx={{ color: '#3b82f6', fontSize: 24 }} />
            <Typography
              variant="h6"
              component="h1"
              sx={{ 
                mt: 1,
                fontWeight: 600,
                color: '#1e293b'
              }}
            >
              Tour Packages
            </Typography>
          </Box>
          
          <Box sx={{ width: '70px' }}></Box>
        </Box>

      <Box sx={{ mb: 5 }}>
        <Paper
          elevation={0}
          sx={{
            borderRadius: 3,
            overflow: 'hidden',
            backgroundColor: 'transparent'
          }}
        >
          <Box
            sx={{
              background: 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
              p: 2,
              position: 'relative'
            }}
          >
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
              <TravelExploreIcon sx={{ color: 'white', fontSize: 24 }} />
              <Box>
                <Typography 
                  variant="h6" 
                  component="div" 
                  sx={{ 
                    color: 'white', 
                    fontWeight: 600,
                    fontSize: '1.1rem'
                  }}
                >
                  {packageData ? "Update Tour Packages" : "Create Tour Packages"}
                </Typography>
                <Typography 
                  variant="caption" 
                  component="div" 
                  sx={{ 
                    color: 'rgba(255,255,255,0.9)', 
                    fontWeight: 400
                  }}
                >
                  {packageData ? " " : "Search and customize your packages"}
                </Typography>
              </Box>
            </Box>
          </Box>

          {/* Search Form Section */}
          <Box sx={{ 
            p: 2, 
            bgcolor: '#ffffff',
            overflow: 'visible',
            position: 'relative'
          }}>
           <SearchForm onNext={handleNext} packageData={packageData}/>
          </Box>
        </Paper>
      </Box>

      {/* Render services when search is completed */}
      {currentStep > 1 && (
        <Box sx={{ mt: 4 }}>
          {/* Service content area */}
          <Box sx={{ py: 2 }}>
            <Itinerary onBookingSuccess={handleBookingSuccess} />
          </Box>
        </Box>
      )}
    </Container>
    </Box>
  );
} 