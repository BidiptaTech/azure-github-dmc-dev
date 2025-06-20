import { useEffect, useState } from "react";
import { styled } from '@mui/material/styles';
import { Box, Typography, List, Card, CardContent,Grid, Chip, Badge, AvatarGroup, Avatar, Divider } from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle"; // Import CheckCircleIcon
import CancelIcon from '@mui/icons-material/Cancel'; // Import CancelIcon for cross badge
import DoNotDisturbIcon from '@mui/icons-material/DoNotDisturb';
import dayjs from "dayjs";
import { useDispatch, useSelector } from "react-redux";
import { setHotelDetails } from "@/slice/hotel/HotelDetailsSlice";
import PricingSummary from "./PricingSummary";
import BedIcon from '@mui/icons-material/Bed';
import PersonIcon from '@mui/icons-material/Person';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import BabyChangingStationIcon from '@mui/icons-material/BabyChangingStation';
import WomanIcon from '@mui/icons-material/Woman';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import CribIcon from '@mui/icons-material/Crib';

const RoomCard = styled(Card)(({ theme }) => ({
  background: 'white',
  borderRadius: '15px',
  marginBottom: '20px',
  transition: 'transform 0.2s ease-in-out',
  '&:hover': {
    transform: 'translateY(-5px)',
    boxShadow: '0 8px 20px rgba(53, 84, 209, 0.15)',
  },
}));

const RoomTypeHeader = styled(Box)(({ theme }) => ({
  background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
  padding: '15px 20px',
  borderTopLeftRadius: '15px',
  borderTopRightRadius: '15px',
  color: 'white',
}));

const BedTypeChip = styled(Box)(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  background: '#E6F2FF',
  padding: '8px 15px',
  borderRadius: '20px',
  marginRight: '10px',
  color: '#3554D1',
  fontWeight: 500,
  '& .MuiSvgIcon-root': {
    marginRight: '5px',
  },
}));

const groupMealsByBedType = (bed) => {
  console.log('Bed data:', bed);

  if (!bed.selectedMeals) {
    return {};
  }

  // Initialize meal counts and prices
  let mealData = {};
  
  // Process each selected meal individually
  Object.entries(bed.selectedMeals).forEach(([key, meal]) => {
    const mealType = meal.type;
    const mealPrice = parseFloat(meal.price) || 0;
    
    // If this meal type already exists, update the count
    if (mealData[mealType]) {
      mealData[mealType].count += 1;
      // Add the price for each additional meal
      mealData[mealType].totalPrice += mealPrice;
    } else {
      // Create a new entry for this meal type
      mealData[mealType] = {
        count: 1,
        type: mealType,
        price: mealPrice,
        totalPrice: mealPrice // Initialize total price
      };
    }
  });

  return mealData;
};

const BookingDetails = ({rooms,tourDetails,totalPrice}) => {
  // Get currency information from Redux store
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  
  // Get price mode from state

  const bookingDate = useSelector((state) => state.hotels.searchState);
  
  const priceMode = useSelector((state) => state.hotels.searchState.priceMode) || 'dmc';

  const [roomsDetails, setRoomDetails] = useState(rooms || []);
  const { roomDatas } = useSelector((state) => state.rooms);
const {check_in_time,check_out_time}=roomDatas
console.log(check_in_time,check_out_time,"room datas");

  //console.log(roomsDetails,"details");
  
console.log(bookingDate,"booking date");

  //const {CheckInTime,CheckOutTime,total_pax}=tourDetails
  //console.log(CheckInTime,CheckOutTime,"hotel booking date");
  const CheckInTime =bookingDate?.ucheckIn
  console.log(CheckInTime,"checkIn time");
  
  const CheckOutTime =bookingDate?.ucheckOut
  console.log(CheckOutTime,"checkoutTime");
  

  const dispatch = useDispatch();

  const calculateTotalNights = (CheckInTime, CheckOutTime) => {
    if (!CheckInTime || !CheckOutTime) return 0;
  
    // Convert from "DD/MM/YYYY" to a valid date format
    const checkInDate = dayjs(CheckInTime).toDate();
    const checkOutDate = dayjs(CheckOutTime).toDate();
  
    console.log(checkInDate, checkOutDate, "datess");
  
    const timeDifference = checkOutDate - checkInDate;
    let nights = timeDifference / (1000 * 3600 * 24); // Convert milliseconds to days
  
    return nights > 0 ? Math.round(nights) : 1; // Ensure at least 1 night
  };
  
  // Format price with commas for thousands
  const formatPrice = (price) => {
    if (typeof price !== 'number') return '0.00';
    
    return price.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  };

  // const totalNights = useSelector((state) => state.hoteldetails.totalNights);
  const { ucheckIn, ucheckOut } = useSelector(
    (state) => state.hotels.searchState
  );

  // console.log(ucheckIn,ucheckOut,"chekouttttt");
  
  // Get hotel details from Redux store with optional chaining
  const { hotel_name,hotel_id, location, image, cancellation_charge,site_image } = useSelector(
    (state) => state.hoteldetails.bookingDetails || {}
  );
 // console.log(hotel_name, location, image, cancellation_charge,"hotel details final");

  const cancellationString =
    cancellation_charge && cancellation_charge.length > 0
      ? `Cancellation charge ₹${cancellation_charge[0].price} before ${cancellation_charge[0].duration} hours`
      : "No cancellation charges";

  // This effect was overwriting hotel details - commenting it out
  /*
  useEffect(() => {
    if (ucheckIn && ucheckOut) {
      dispatch(
        setHotelDetails({
          ucheckIn,
          ucheckOut,
          hotel_name,
          image,
          location,
          hotel_id,
          cancellation_charge,
          site_image
        })
      );
    }
  }, [dispatch, ucheckIn, ucheckOut, hotel_name, image, cancellation_charge]);
  */

  // Format date in "1st Jan 2025" format
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const date = dayjs(dateString);
    if (!date.isValid()) return dateString;
    
    // Get day with ordinal suffix
    const day = date.date();
    const suffix = getDaySuffix(day);
    
    // Format as "1st Jan 2025"
    return `${day}${suffix} ${date.format('MMM YYYY')}`;
  };
  
  // Helper function to get day suffix (st, nd, rd, th)
  const getDaySuffix = (day) => {
    if (day > 3 && day < 21) return 'th';
    switch (day % 10) {
      case 1: return 'st';
      case 2: return 'nd';
      case 3: return 'rd';
      default: return 'th';
    }
  };

  // Format time to 12-hour format with AM/PM
  const formatTime = (timeString) => {
    if (!timeString) return '';
    
    // Check if the time is already in a standard format
    let hours, minutes, period;
    let original24Hours;
    
    // Try to extract time from different formats (HH:MM, HH:MM:SS, etc.)
    const timeRegex = /(\d{1,2})[:\.](\d{2})(?::\d{2})?\s*(am|pm|AM|PM)?/;
    const match = timeString.match(timeRegex);
    
    if (match) {
      hours = parseInt(match[1], 10);
      minutes = match[2];
      period = match[3] ? match[3].toUpperCase() : null;
      
      // Store the original 24-hour format
      original24Hours = period ? 
        (period.toUpperCase() === 'PM' && hours < 12 ? hours + 12 : hours) : 
        hours;
      
      // If no AM/PM is provided but hours are in 24-hour format
      if (!period) {
        period = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12; // Convert to 12-hour format
      }
    } else {
      // If we can't parse the time, return the original string
      return timeString;
    }
    
    // Format 24-hour time
    const hours24 = original24Hours || hours;
    const formattedHours24 = hours24.toString().padStart(2, '0');
    const formattedMinutes = minutes.padStart(2, '0');
    
    return `${hours}:${minutes} ${period} (${formattedHours24}:${formattedMinutes})`;
  };

  return (
    <div className="px-30 py-30 border-light rounded-4">
      <div className="text-20 fw-500 mb-30">Your booking details</div>
      <div className="row x-gap-15 y-gap-20">
        <div className="col-auto">
          <img
            src={image || 'default-image-url'}
            alt="image"
            className="size-140 rounded-4 object-cover"
          />
        </div>
        {/* End .col */}
        <div className="col">
          <div className="d-flex x-gap-5 pb-10">
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
          </div>
          {/* End ratings */}
          <div className="lh-17 fw-500">
            {hotel_name || 'Hotel Name Not Available'}
          </div>
          <div className="text-14 lh-15 mt-5">{location || 'Location Not Available'}</div>
          {/* <div className="row x-gap-10 y-gap-10 items-center pt-10">
            <div className="col-auto">
              <div className="d-flex items-center">
                <div className="size-30 flex-center bg-blue-1 rounded-4">
                  <div className="text-12 fw-600 text-white">4.8</div>
                </div>
                <div className="text-14 fw-500 ml-10">Exceptional</div>
              </div>
            </div>
            <div className="col-auto">
              <div className="text-14">3,014 reviews</div>
            </div>
          </div> */}
        </div>
        {/* End .col */}
      </div>
      {/* End .row */}

      <div className="border-top-light mt-30 mb-20" />
      <div className="row y-gap-20 justify-between">
        <div className="col-auto">
          <div className="text-15">Check-in</div>
          <div className="fw-500">{formatDate(CheckInTime)}</div>
          <div className="text-15 text-light-1">{formatTime(check_in_time)}</div>
        </div>
        <div className="col-auto md:d-none">
          <div className="h-full w-1 bg-border" />
        </div>
        <div className="col-auto text-right md:text-left">
          <div className="text-15">Check-out</div>
          <div className="fw-500">{formatDate(CheckOutTime)}</div>
          <div className="text-15 text-light-1">{formatTime(check_out_time)}</div>
        </div>
      </div>
      {/* End row */}

      <div className="border-top-light mt-30 mb-20" />
      <div className="d-flex align-items-center gap-2">
        {/* Label */}
        <span className="text-15 fw-bold">Total length of stay:</span>

        {/* Nights Count */}
        <span className="fw-500 text-primary">
          {`${calculateTotalNights(CheckInTime, CheckOutTime)} Night${
            calculateTotalNights(CheckInTime, CheckOutTime) > 1 ? "s" : ""
          }`}
        </span>
      </div>

      <div className="border-top-light mt-30 mb-20" />

 
      <div className="border-top-light mt-30 mb-20" />
      <Box mt={2}>
      {roomsDetails?.map((room, index) => (
        <RoomCard key={index}>
          <RoomTypeHeader>
            <Typography variant="h6" sx={{ fontWeight: 600, fontSize: '1.1rem' }}>
              {room.room_type}
            </Typography>
          </RoomTypeHeader>

          <CardContent sx={{ p: 3 }}>
            {room.beds.map((bed) => {
              const groupedMeals = groupMealsByBedType(bed);
              const totalOccupancyForBed = bed.head_count;
              
              // Calculate prices with exchange rates
              const basePrice = parseFloat(bed.price) || 0;
              const totalBedPrice = basePrice * totalOccupancyForBed;
              
              // Convert prices
              const conversionRate = priceMode === "dmc" ? exchangeRate : usdExchangeRate;
              const convertedTotalBedPrice = totalBedPrice * conversionRate;
              const usdTotalBedPrice = totalBedPrice * usdExchangeRate;

              return (
                <Box
                  key={bed.bed_id}
                  sx={{
                    mb: 3,
                    pb: 2,
                    borderBottom: '1px dashed rgba(53, 84, 209, 0.2)',
                    '&:last-child': {
                      borderBottom: 'none',
                      mb: 0,
                      pb: 0,
                    },
                  }}
                >
                  <Grid container spacing={2}>
                    <Grid item xs={12}>
                      <BedTypeChip>
                        <BedIcon />
                        {bed.bed_type}
                      </BedTypeChip>
                      
                      <Typography variant="subtitle1" sx={{ mt: 2, color: '#3554D1', fontWeight: 500 }}>
                        Selected Options:
                      </Typography>
                    </Grid>

                    <Grid item xs={12}>
                      <Box sx={{ pl: 0.5 }}>
                        {Object.values(groupedMeals).map((mealDetails) => {
                          // Calculate meal prices with exchange rates
                          const mealPrice = parseFloat(mealDetails.price) || 0;
                          const mealCount = mealDetails.count || 1;
                          
                          // Calculate total price for this meal type (price × count)
                          const totalMealPrice = mealDetails.totalPrice || (mealPrice * mealCount);
                          
                          // Convert prices
                          const convertedMealPrice = mealPrice * conversionRate;
                          const convertedTotalMealPrice = totalMealPrice * conversionRate;
                          const usdMealPrice = mealPrice * usdExchangeRate;
                          const usdTotalMealPrice = totalMealPrice * usdExchangeRate;
                          
                          return (
                            <Box 
                              key={mealDetails.type} 
                              sx={{ 
                                display: 'flex', 
                                alignItems: 'center',
                                mb: 1,
                                backgroundColor: '#F8FAFF',
                                p: 1,
                                borderRadius: '8px',
                                border: '1px solid rgba(53, 84, 209, 0.1)'
                              }}
                            >
                              <Box sx={{ display: 'flex', alignItems: 'center', flex: 1 }}>
                                {/* Restaurant icon with meal count badge */}
                                
                                <Box sx={{ position: 'relative', mr: 2 }}>
                                  {/* Status badge above restaurant icon */}
                                  <Box 
                                    sx={{ 
                                      position: 'absolute', 
                                      top: -15, 
                                      right: -10, 
                                      zIndex: 1,
                                      backgroundColor: mealDetails.type.toLowerCase() === "room only" ? '#FFF2F0' : '#F6FFED',
                                      borderRadius: '50%',
                                      width: 20,
                                      height: 20,
                                      display: 'flex',
                                      justifyContent: 'center',
                                      alignItems: 'center',
                                      border: `1px solid ${mealDetails.type.toLowerCase() === "room only" ? '#FFCCC7' : '#B7EB8F'}`
                                    }}
                                  >
                                    {mealDetails.type.toLowerCase() === "room only" ? (
                                      <DoNotDisturbIcon 
                                        sx={{ 
                                          color: '#FF4D4F', 
                                          fontSize: '0.8rem',
                                        }}
                                      />
                                    ) : (
                                      <CheckCircleIcon 
                                        sx={{ 
                                          color: '#52C41A', 
                                          fontSize: '0.8rem',
                                        }}
                                      />
                                    )}
                                  </Box>
                                  
                                  <RestaurantIcon sx={{ color: '#3554D1', fontSize: '1.5rem',  }} />
                                </Box>
                                
                                <Badge 
                                  badgeContent={mealDetails.count} 
                                  color="primary"
                                  sx={{ 
                                    '& .MuiBadge-badge': { 
                                      fontSize: '0.7rem', 
                                      height: '20px', 
                                      minWidth: '20px' 
                                    } 
                                  }}
                                >
                                <Typography variant="body1" sx={{ ml: 1}}>
                                  {mealDetails.type}
                                </Typography>
                                </Badge>
                              </Box>
                              
                              {/* Person count display - this is for the head_count per meal type */}
                              {/* <Box sx={{ display: 'flex', alignItems: 'center', mr: 2 }}>
                              
                                {Array.from({ length: Math.min(bed.head_count, 3) }).map((_, i) => (
                                  <PersonIcon 
                                    key={`person-${mealDetails.type}-${i}`} 
                                    sx={{ 
                                      color: '#3554D1', 
                                      fontSize: '1.2rem',
                                      mr: 0.5 
                                    }} 
                                  />
                                ))}
                                {bed.head_count > 3 && (
                                  <Typography variant="caption" sx={{ color: '#3554D1', fontWeight: 'bold' }}>
                                    +{bed.head_count - 3}
                                  </Typography>
                                )}
                              </Box> */}
                              
                              <Box sx={{ textAlign: 'right' }}>
                                {/* Show unit price */}
                                {/* <Typography variant="body2" sx={{ color: 'text.secondary' }}>
                                  {currencyCode} {formatPrice(convertedMealPrice)} × {mealCount}
                                </Typography>
                                 */}
                                {/* Show total price for this meal type */}
                                <Typography variant="body1" sx={{ fontWeight: 500, color: '#3554D1' }}>
                                  {currencyCode} {formatPrice(convertedTotalMealPrice)}/night
                                </Typography>
                                <Typography variant="caption" sx={{ display: 'block', color: 'text.secondary' }}>
                                  {usdCurrencyCode} {formatPrice(usdTotalMealPrice)}/night
                                </Typography>
                                <Typography variant="caption" sx={{ display: 'block', color: 'text.secondary' }}>
                                  SGD {formatPrice(totalMealPrice)}/night
                                </Typography>
                              </Box>
                            </Box>
                          );
                        })}
                      </Box>
                    </Grid>

                    {/* Baby Cot Section - Only show if baby cot is selected */}
                    {(bed.baby_cot === true || bed.baby_cot === 1) && (
                      <Grid item xs={12}>
                        <Box sx={{ pl: 0.5 }}>
                          <Box 
                            sx={{ 
                              display: 'flex', 
                              alignItems: 'center',
                              mb: 1,
                              backgroundColor: 'rgba(76, 175, 80, 0.08)',
                              p: 1,
                              borderRadius: '8px',
                              border: '1px solid rgba(76, 175, 80, 0.2)'
                            }}
                          >
                            <Box sx={{ display: 'flex', alignItems: 'center', flex: 1 }}>
                              <BabyChangingStationIcon sx={{ color: '#4CAF50', mr: 1 }} />
                              <Typography variant="body1" sx={{ ml: 2 }}>
                                Baby Cot
                              </Typography>
                            </Box>
                            
                            <Box sx={{ textAlign: 'right' }}>
                              <Typography variant="body1" sx={{ fontWeight: 500, color: '#4CAF50' }}>
                                {currencyCode} {formatPrice(parseFloat(bed.baby_cot_price || 0) * conversionRate)}/night
                              </Typography>
                              <Typography variant="caption" sx={{ display: 'block', color: 'text.secondary' }}>
                                {usdCurrencyCode} {formatPrice(parseFloat(bed.baby_cot_price || 0) * usdExchangeRate)}/night
                              </Typography>
                              <Typography variant="caption" sx={{ display: 'block', color: 'text.secondary' }}>
                                SGD {formatPrice(parseFloat(bed.baby_cot_price || 0))}/night
                              </Typography>
                            </Box>
                          </Box>
                        </Box>
                      </Grid>
                    )}

                    <Grid item xs={12}>
                      <Card 
                        elevation={1} 
                        sx={{ 
                          borderRadius: '12px',
                          transition: 'transform 0.2s, box-shadow 0.2s',
                          '&:hover': {
                            transform: 'translateY(-3px)',
                            boxShadow: '0 8px 16px rgba(0,0,0,0.1)'
                          }
                        }}
                      >
                        <CardContent sx={{ p: 2 }}>
                          {/* Occupancy Header */}
                          <Box sx={{ 
                            display: 'flex', 
                            alignItems: 'center', 
                            justifyContent: 'space-between', 
                            mb: 2,
                            backgroundColor: '#F8FAFF',
                            background: '#E6F2FF',
                            p: 2,
                            borderRadius: '10px',
                            color: 'white'
                          }}>
                            
                            <Box sx={{ display: 'flex', alignItems: 'center', }}>
                              <Typography variant="h6" sx={{ fontWeight: 'bold', fontSize:'16px', mr: 2 ,color:"#3554D1"}}>
                                Adults:
                              </Typography>
                              <Badge 
                                badgeContent={totalOccupancyForBed} 
                                color="info"
                                sx={{ 
                                  '& .MuiBadge-badge': { 
                                    fontSize: '0.9rem', 
                                    height: '24px', 
                                    minWidth: '24px',
                                    backgroundColor: '#FF4D4F',
                                    fontWeight: 'bold',
                                    right: -8,
                                    top: 0
                                  } 
                                }}
                                overlap="circular"
                              >
                                <Avatar
                                  sx={{
                                    bgcolor: '#3554D1',
                                    width: 38,
                                    height: 38
                                  }}
                                >
                                  <PersonIcon 
                                    sx={{ 
                                      fontSize: 24,
                                      color: 'white'
                                    }} 
                                  />
                                </Avatar>
                              </Badge>
                            </Box>
{/*                             
                            <Box sx={{ 
                              display: 'flex', 
                              alignItems: 'center', 
                              bgcolor: 'rgba(255, 255, 255, 0.2)',
                              px: 2, 
                              py: 0.5, 
                              borderRadius: '16px',
                            }}>
                              <Typography variant="body1" sx={{ fontWeight: 'bold' }}>
                                Total: {totalOccupancyForBed}
                              </Typography>
                            </Box> */}
                          </Box>
                          
                          {/* Baby Cot included indicator */}
                          {(bed.baby_cot === true || bed.baby_cot === 1) && (
                            <Box 
                              sx={{ 
                                display: 'flex', 
                                alignItems: 'center', 
                                mb: 3,
                                p: 1.5,
                                borderRadius: '8px',
                                bgcolor: 'rgba(76, 175, 80, 0.08)',
                                border: '1px solid rgba(76, 175, 80, 0.2)'
                              }}
                            >
                              <Box sx={{ position: 'relative', mr: 2 }}>
                                <Box 
                                  sx={{ 
                                    position: 'absolute', 
                                    top: -10, 
                                    right: -5, 
                                    zIndex: 1,
                                    backgroundColor: '#F6FFED',
                                    borderRadius: '50%',
                                    width: 18,
                                    height: 18,
                                    display: 'flex',
                                    justifyContent: 'center',
                                    alignItems: 'center',
                                    border: '1px solid #B7EB8F'
                                  }}
                                >
                                  <CheckCircleIcon 
                                    sx={{ 
                                      color: '#52C41A', 
                                      fontSize: '0.7rem',
                                    }}
                                  />
                                </Box>
                                <BabyChangingStationIcon sx={{ color: '#4CAF50', fontSize: '1.5rem' }} />
                              </Box>
                              <Typography variant="body2" sx={{ fontWeight: 'bold', color: '#4CAF50' }}>
                                Baby Cot Included
                              </Typography>
                            </Box>
                          )}
                          
                          {/* Guest details section */}
                          {(() => {
                            const tourData = useSelector((state) => state.hotels.tourdetails || {});
                            const childCount = bed.child_count || tourData?.tour_child_adult || 0;
                            const infantCount = bed.infant_count || tourData?.tour_infant || 0;
                            
                            // Only render this section if there are children or infants
                            if (childCount > 0 || infantCount > 0) {
                              return (
                                <Box sx={{ mb: 3 }}>
                                  <Typography variant="subtitle1" sx={{ 
                                    fontWeight: 'bold', 
                                    color: '#3554D1', 
                                    mb: 1.5,
                                    borderBottom: '1px dashed rgba(53, 84, 209, 0.2)',
                                    pb: 0.5
                                  }}>
                                    Additional Guests
                                  </Typography>
                                  
                                  <Grid container spacing={2}>
                                    {childCount > 0 && (
                                      <Grid item xs={6}>
                                        <Box sx={{ 
                                          display: 'flex', 
                                          alignItems: 'center', 
                                          gap: 1,
                                          p: 1.5,
                                          borderRadius: '8px',
                                          bgcolor: 'rgba(255, 152, 0, 0.1)',
                                          border: '1px solid rgba(255, 152, 0, 0.3)'
                                        }}>
                                          <Badge badgeContent={childCount} color="warning">
                                            <ChildCareIcon sx={{ color: '#FF9800' }} />
                                          </Badge>
                                          <Typography variant="body2" sx={{ fontWeight: 'bold', ml: 1 }}>
                                            {childCount} {childCount === 1 ? 'Child' : 'Children'}
                                          </Typography>
                                        </Box>
                                      </Grid>
                                    )}
                                    
                                    {infantCount > 0 && (
                                      <Grid item xs={6}>
                                        <Box sx={{ 
                                          display: 'flex', 
                                          alignItems: 'center', 
                                          gap: 1,
                                          p: 1.5,
                                          borderRadius: '8px',
                                          bgcolor: 'rgba(156, 39, 176, 0.1)',
                                          border: '1px solid rgba(156, 39, 176, 0.3)'
                                        }}>
                                          <Badge badgeContent={infantCount} color="secondary">
                                            <CribIcon sx={{ color: '#9C27B0' }} />
                                          </Badge>
                                          <Typography variant="body2" sx={{ fontWeight: 'bold', ml: 1 }}>
                                            {infantCount} {infantCount === 1 ? 'Infant' : 'Infants'}
                                          </Typography>
                                        </Box>
                                      </Grid>
                                    )}
                                  </Grid>
                                </Box>
                              );
                            }
                            return null;
                          })()}

                         
                          
                        </CardContent>
                      </Card>
                    </Grid>
                  </Grid>
                </Box>
              );
            })}
          </CardContent>
        </RoomCard>
      ))}
    </Box>

    <PricingSummary totalPrice={totalPrice || 0} rooms={rooms} />

      {/* End row */}
    </div>
    // End px-30
  );
};

export default BookingDetails;
