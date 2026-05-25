import React, { useEffect } from 'react';
import { 
  Box, 
  Typography, 
  Button, 
  Paper, 
  Divider, 
  Grid,
  Link,
  CircularProgress
} from '@mui/material';
import { 
  Login as CheckInIcon,
  Logout as CheckOutIcon,
  Info as CancellationIcon,
  ChildCare as ChildrenIcon,
  Person as AgeIcon,
  Pets as PetsIcon,
  Policy as PropertyPolicyIcon,
  Gavel as TermsIcon,
  Payments as RefundIcon
} from '@mui/icons-material';
import { useSelector, useDispatch } from 'react-redux';
import { setHotelPolicies } from '../../slice/hotel/HotelDetailsSlice';
import { formatTime, hasExtraBedOption, getPolicyData } from '../../utils/policyUtils';

const HotelPolicies = () => {
  const dispatch = useDispatch();
  
  // Get hotelDetails state with a fallback to empty objects
  const { hotelPolicies = [], bookingDetails = {} } = useSelector((state) => state.hotelDetails || {});
  const roomsStatus = useSelector((state) => state.rooms?.status); // Get loading status
  const roomsData = useSelector((state) => state.rooms?.roomDatas);
  
  console.log("Hotel Policies from Redux:", hotelPolicies);
  console.log("Booking Details from Redux:", bookingDetails);
  
  // Use utility function to get policy data from all available sources
  const { policyData, policyArray, policySource } = getPolicyData(hotelPolicies, roomsData);
  
  console.log("Policy data found from:", policySource);
  console.log("Policy data:", policyData);
  
  // If we found policy data from another source but not in Redux, dispatch it to Redux
  useEffect(() => {
    if (policyArray && policySource !== 'redux' && (!hotelPolicies || hotelPolicies.length === 0)) {
      console.log(`Dispatching policy data from ${policySource} to Redux`);
      dispatch(setHotelPolicies(policyArray));
    }
  }, [dispatch, policyArray, policySource, hotelPolicies]);
  
  // Check if data is still loading
  const isLoading = roomsStatus === 'loading';
  
  // Show loading state only if we don't have policy data yet
  if (isLoading && !policyData) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', p: 4 }}>
        <CircularProgress />
      </Box>
    );
  }
  
  // Fallback to hotel details for check-in/check-out times if available
  const checkInTime = bookingDetails?.check_in_time || 
                      (roomsData && roomsData.check_in_time) || 
                      "15:00:00";
  
  const checkOutTime = bookingDetails?.check_out_time || 
                       (roomsData && roomsData.check_out_time) || 
                       "11:00:00";
  
  // Format times for display
  const formattedCheckInTime = formatTime(checkInTime);
  const formattedCheckOutTime = formatTime(checkOutTime);
  
  // Format pet policy text
  const petAllowed = policyData?.pet_policy?.pet_allowed;
  const petPolicyText = policyData?.pet_policy?.petpolicy || 
    (petAllowed ? "Pets are allowed." : "Pets are not allowed.");
  
  return (
    <Box sx={{ maxWidth: 1000, mx: 'auto', p: 2 }}>
      {/* <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
        <Typography variant="h5" component="h2" fontWeight="bold">
          House rules
        </Typography>
        <Button 
          variant="contained" 
          color="primary" 
          sx={{ textTransform: 'none' }}
        >
          See availability
        </Button>
      </Box>
      
      <Typography variant="body2" sx={{ mb: 2 }}>
        El De Bellwood Near IGI Airport takes special requests - add in the next step!
      </Typography> */}
      
      <PolicyItem 
        icon={<CheckInIcon />} 
        title="Check-in" 
        details={formattedCheckInTime} 
      />
      
      <Divider />
      
      <PolicyItem 
        icon={<CheckOutIcon />} 
        title="Check-out" 
        details={formattedCheckOutTime} 
      />
      
      <Divider />
      
      <PolicyItem 
        icon={<CancellationIcon />} 
        title="Cancellation/ prepayment" 
        details={
          <Box>
            <Typography variant="body2" sx={{ mb: 1 }}>
              {policyData?.cancellation_policy?.policy || 
               "Cancellation and prepayment policies vary according to accommodation type."}
            </Typography>
            {!policyData?.cancellation_policy?.policy && (
              <Typography variant="body2">
                Please check what <Link href="#" color="primary">conditions</Link> may apply to each option when making your selection.
              </Typography>
            )}
          </Box>
        } 
      />
      
      <Divider />
      
      <PolicyItem 
        icon={<ChildrenIcon />} 
        title="Children and beds" 
        details={
          <Box>
            <Typography variant="subtitle2" sx={{ fontWeight: 'bold', mb: 1 }}>
              Child policies
            </Typography>
            <Typography variant="body2" sx={{ mb: 1 }}>
              {policyData?.child_policy?.childpolicy || "Children of any age are welcome."}
            </Typography>
            {!policyData?.child_policy?.childpolicy && (
              <Typography variant="body2" sx={{ mb: 2 }}>
                To see correct prices and occupancy information, please make sure you have added the correct number of children and their ages in your search.
              </Typography>
            )}
            
            <Typography variant="subtitle2" sx={{ fontWeight: 'bold', mb: 1 }}>
              Cot and extra bed policies
            </Typography>
            <Typography variant="body2">
              {hasExtraBedOption(bookingDetails) 
                ? "Cots and extra beds are available at this property." 
                : "Cots and extra beds are not available at this property."}
            </Typography>
          </Box>
        } 
      />
      
      <Divider />
      
      <PolicyItem 
        icon={<AgeIcon />} 
        title="No age restriction" 
        details="There is no age requirement for check-in" 
      />
      
      <Divider />
      
      <PolicyItem 
        icon={<PetsIcon />} 
        title="Pets" 
        details={petPolicyText} 
      />

      {policyData?.property_policy?.policy && (
        <>
          <Divider />
          <PolicyItem 
            icon={<PropertyPolicyIcon />} 
            title="Property Policy" 
            details={policyData.property_policy.policy} 
          />
        </>
      )}

      {policyData?.terms_policy?.termspolicy && (
        <>
          <Divider />
          <PolicyItem 
            icon={<TermsIcon />} 
            title="Terms & Conditions" 
            details={policyData.terms_policy.termspolicy} 
          />
        </>
      )}

      {policyData?.refund_policy?.refundpolicy && (
        <>
          <Divider />
          <PolicyItem 
            icon={<RefundIcon />} 
            title="Refund Policy" 
            details={policyData.refund_policy.refundpolicy} 
          />
        </>
      )}
    </Box>
  );
};

// Policy Item Component
const PolicyItem = ({ icon, title, details }) => {
  return (
    <Grid container sx={{ p: 2 }}>
      <Grid item sx={{ pr: 2, width: 40, color: 'text.secondary' }}>
        {icon}
      </Grid>
      <Grid item sx={{ width: 180, fontWeight: 500 }}>
        <Typography variant="body1" fontWeight="medium">
          {title}
        </Typography>
      </Grid>
      <Grid item xs>
        {typeof details === 'string' ? (
          <Typography variant="body2">{details}</Typography>
        ) : (
          details
        )}
      </Grid>
    </Grid>
  );
};

export default HotelPolicies;
