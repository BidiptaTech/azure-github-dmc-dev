// import React, { useEffect } from 'react';
// import { useParams, useNavigate } from 'react-router-dom';
// import { useSelector, useDispatch } from 'react-redux';
// import { fetchPackageDetails } from '../../../slice/tour-packages/prePackagesSlice';
// import { 
//   Container, 
//   Box, 
//   Button,
//   CircularProgress,
//   Alert,
//   Typography,
//   Tabs,
//   Tab
// } from '@mui/material';

// // Import components
// import PackageHeader from '../../../components/pre-define-packages/details/PackageHeader';
// import PackageOverview from '../../../components/pre-define-packages/details/PackageOverview';
// import InclusionsExclusions from '../../../components/pre-define-packages/details/InclusionsExclusions';
// import PackageItinerary from '../../../components/pre-define-packages/details/PackageItinerary';
// import AccommodationDetails from '../../../components/pre-define-packages/details/AccommodationDetails';
// import AttractionsDetails from '../../../components/pre-define-packages/details/AttractionsDetails';
// import RestaurantsDetails from '../../../components/pre-define-packages/details/RestaurantsDetails';
// import GuideDetails from '../../../components/pre-define-packages/details/GuideDetails';
// import PackagePricing from '../../../components/pre-define-packages/details/PackagePricing';
// import TermsConditions from '../../../components/pre-define-packages/details/TermsConditions';
// import { useState } from 'react';

// const PackageDetails = () => {
//   const { id } = useParams();
//   const dispatch = useDispatch();
//   const navigate = useNavigate();
  
//   const [activeTab, setActiveTab] = useState(0);
  
//   const handleTabChange = (event, newValue) => {
//     setActiveTab(newValue);
//   };
  
//   const { packageDetails, loadingDetails, errorDetails } = useSelector(state => state.prePackages);
  
//   useEffect(() => {
//     if (id) {
//       dispatch(fetchPackageDetails(id));
//     }
//   }, [dispatch, id]);
  
//   if (loadingDetails) {
//     return (
//       <Container>
//         <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '50vh' }}>
//           <CircularProgress />
//           <Typography variant="h6" sx={{ ml: 2 }}>Loading package details...</Typography>
//         </Box>
//       </Container>
//     );
//   }
  
//   if (errorDetails) {
//     return (
//       <Container>
//         <Box sx={{ mt: 4 }}>
//           <Alert severity="error">{errorDetails}</Alert>
//           <Button 
//             variant="outlined" 
//             color="primary" 
//             sx={{ mt: 2 }}
//             onClick={() => navigate(-1)}
//           >
//             Go Back
//           </Button>
//         </Box>
//       </Container>
//     );
//   }
  
//   if (!packageDetails) {
//     return (
//       <Container>
//         <Box sx={{ mt: 4 }}>
//           <Alert severity="info">No package details found.</Alert>
//           <Button 
//             variant="outlined" 
//             color="primary" 
//             sx={{ mt: 2 }}
//             onClick={() => navigate(-1)}
//           >
//             Go Back
//           </Button>
//         </Box>
//       </Container>
//     );
//   }
  
//   return (
//     <Container maxWidth="lg" sx={{ py: 4 }}>
//       <Button 
//         variant="outlined" 
//         sx={{ mb: 3 }}
//         onClick={() => navigate(-1)}
//       >
//         Back to Packages
//       </Button>
      
//       {/* Package Header with Image, Title and Basic Info */}
//       <PackageHeader packageData={packageDetails} />
      
//       {/* Tabs for different sections */}
//       <Box sx={{ mt: 3 }}>
//         <Tabs
//           value={activeTab}
//           onChange={handleTabChange}
//           variant="scrollable"
//           scrollButtons="auto"
//           aria-label="package details tabs"
//         >
//           <Tab label="Overview" />
//           <Tab label="Inclusions & Exclusions" />
//           <Tab label="Itinerary" />
//           <Tab label="Accommodation" />
//           <Tab label="Attractions" />
//           <Tab label="Restaurants" />
//           <Tab label="Tour Guide" />
//           <Tab label="Terms & Conditions" />
//         </Tabs>
        
//         <Box sx={{ mt: 3 }}>
//           {/* Overview Tab */}
//           {activeTab === 0 && (
//             <Box>
//               <PackageOverview packageData={packageDetails} />
//               <PackagePricing packageData={packageDetails} />
//             </Box>
//           )}
          
//           {/* Inclusions/Exclusions Tab */}
//           {activeTab === 1 && (
//             <InclusionsExclusions packageData={packageDetails} />
//           )}
          
//           {/* Itinerary Tab */}
//           {activeTab === 2 && (
//             <PackageItinerary packageData={packageDetails} />
//           )}
          
//           {/* Accommodation Tab */}
//           {activeTab === 3 && (
//             <AccommodationDetails packageData={packageDetails} />
//           )}
          
//           {/* Attractions Tab */}
//           {activeTab === 4 && (
//             <AttractionsDetails packageData={packageDetails} />
//           )}
          
//           {/* Restaurants Tab */}
//           {activeTab === 5 && (
//             <RestaurantsDetails packageData={packageDetails} />
//           )}
          
//           {/* Tour Guide Tab */}
//           {activeTab === 6 && (
//             <GuideDetails packageData={packageDetails} />
//           )}
          
//           {/* Terms & Conditions Tab */}
//           {activeTab === 7 && (
//             <TermsConditions packageData={packageDetails} />
//           )}
//         </Box>
//       </Box>
//     </Container>
//   );
// };

// export default PackageDetails; 