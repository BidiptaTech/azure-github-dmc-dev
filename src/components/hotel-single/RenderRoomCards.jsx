import React from "react";
import { Box, Typography, Paper, Card, CardContent, Alert, Chip } from "@mui/material";
import { IoPersonSharp } from "react-icons/io5";
import RoomCard from "./RoomCard";
import RenderRoomCardsSkeleton from "./RenderRoomCardsSkeleton";
import { useSelector } from "react-redux";
import InfoOutlinedIcon from '@mui/icons-material/InfoOutlined';
import HotelIcon from '@mui/icons-material/Hotel';
import ContactSupportIcon from '@mui/icons-material/ContactSupport';

const RenderRoomCards = ({
  data,
  pax,
  bed,
  bedIndex,
  babyCot,
  bedArray,
  setbedArray,
  occupancyArray,
  setOccupancyArray,
  occupancyCount,
  setOccupancyCount,
  globalCount,
  setGlobalCount,
  priceArr,
  setPriceArr,
  //setTotalPrice,
  maxOccupancy,
  mealType,
  setMealType,
  hotelType,
  setHotelType,
  roomTypeIndex,
  totalPersonCount,
  setTotalPersonCount,
  acctualOccupency,
  setPaxCount,
  paxCount,
  isLoading = false,
 //personCount,
  //setPersonCount
 
 }) => {
  // If loading, show skeleton
  if (isLoading) {
    return <RenderRoomCardsSkeleton count={6} personCount={Math.min(bed?.max_occupancy, pax) || 1} />;
  }

  // console.log("Initial maxOccupancy:", maxOccupancy);
  // console.log("Bed data:", bed);

  const basePrice = data?.single_price;
  
  // Check if essential data is missing
  if (!data) {
    return (
      <Box sx={{ width: '100%', mt: 2 }}>
        <Alert 
          severity="warning" 
          sx={{ 
            borderRadius: '12px',
            '& .MuiAlert-message': {
              width: '100%'
            }
          }}
        >
          <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 1 }}>
            Room Data Not Available
          </Typography>
          <Typography variant="body2">
            Room information is currently unavailable. Please try refreshing the page or contact support.
          </Typography>
        </Alert>
      </Box>
    );
  }

  // Check if price data is missing
  if (!basePrice) {
    return (
      <Box sx={{ width: '100%', mt: 2 }}>
        <Card 
          elevation={2} 
          sx={{ 
            borderRadius: '12px',
            border: '2px dashed #FFA726',
            backgroundColor: '#FFF8E1',
            transition: 'all 0.3s ease',
          }}
        >
          <CardContent sx={{ 
            p: 3, 
            textAlign: 'center',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            gap: 2
          }}>
            <Box sx={{ 
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              width: 60,
              height: 60,
              borderRadius: '50%',
              backgroundColor: 'rgba(255, 167, 38, 0.2)',
              mb: 1
            }}>
              <InfoOutlinedIcon 
                sx={{ 
                  fontSize: 30,
                  color: '#FFA726'
                }} 
              />
            </Box>
            
            <Typography 
              variant="h6" 
              sx={{ 
                fontWeight: 'bold',
                color: '#F57C00',
                mb: 1
              }}
            >
              Pricing Information Unavailable
            </Typography>
            
            <Typography 
              variant="body2" 
              color="text.secondary"
              sx={{ 
                maxWidth: 350,
                lineHeight: 1.6
              }}
            >
              Room pricing details are not available at this time. Please contact our booking team for current rates and availability.
            </Typography>
            
            <Box sx={{ 
              display: 'flex',
              gap: 1,
              mt: 2,
              flexWrap: 'wrap',
              justifyContent: 'center'
            }}>
              <Chip 
                icon={<HotelIcon />}
                label="Room Available"
                color="primary"
                variant="outlined"
                size="small"
              />
              <Chip 
                icon={<ContactSupportIcon />}
                label="Contact for Pricing"
                color="warning"
                variant="outlined"
                size="small"
              />
            </Box>
          </CardContent>
        </Card>
      </Box>
    );
  }

  // Check if bed or occupancy data is missing
  if (!bed || !maxOccupancy || maxOccupancy <= 0) {
    return (
      <Box sx={{ width: '100%', mt: 2 }}>
        <Alert 
          severity="info" 
          sx={{ 
            borderRadius: '12px',
            '& .MuiAlert-message': {
              width: '100%'
            }
          }}
        >
          <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 1 }}>
            Bed Configuration Not Available
          </Typography>
          <Typography variant="body2">
            Detailed bed and occupancy information is not available for this room. Please contact us for more details.
          </Typography>
        </Alert>
      </Box>
    );
  }

  // Check if pax (passenger count) is invalid
  if (!pax || pax <= 0) {
    return (
      <Box sx={{ width: '100%', mt: 2 }}>
        <Alert 
          severity="error" 
          sx={{ 
            borderRadius: '12px',
            '& .MuiAlert-message': {
              width: '100%'
            }
          }}
        >
          <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 1 }}>
            Invalid Occupancy Count
          </Typography>
          <Typography variant="body2">
            Please specify a valid number of guests to see available room options.
          </Typography>
        </Alert>
      </Box>
    );
  }

  const mealOptions = [
    { 
      title: "Room Only", 
      extraPrice: 0,
      totalPrice: parseFloat(basePrice)
    },
    { 
      title: "Room with Breakfast", 
      extraPrice: data?.breakfast_price, 
      condition: data?.breakfast,
      totalPrice: parseFloat(basePrice) + parseFloat(data?.breakfast_price || 0)
    },
    { 
      title: "Room with Lunch", 
      extraPrice: data?.lunch_price, 
      condition: data?.lunch,
      totalPrice: parseFloat(basePrice) + parseFloat(data?.lunch_price || 0)
    },
    { 
      title: "Room with Dinner", 
      extraPrice: data?.dinner_price, 
      condition: data?.dinner,
      totalPrice: parseFloat(basePrice) + parseFloat(data?.dinner_price || 0)
    },
    { 
      title: "Room with Breakfast & Lunch", 
      extraPrice: parseFloat(data?.breakfast_price || 0) + parseFloat(data?.lunch_price || 0), 
      condition: data?.breakfast && data?.lunch,
      totalPrice: parseFloat(basePrice) + parseFloat(data?.breakfast_price || 0) + parseFloat(data?.lunch_price || 0)
    },
    { 
      title: "Room with Breakfast & Dinner", 
      extraPrice: parseFloat(data?.breakfast_price || 0) + parseFloat(data?.dinner_price || 0), 
      condition: data?.breakfast && data?.dinner,
      totalPrice: parseFloat(basePrice) + parseFloat(data?.breakfast_price || 0) + parseFloat(data?.dinner_price || 0)
    },
    { 
      title: "Room with Lunch & Dinner", 
      extraPrice: parseFloat(data?.lunch_price || 0) + parseFloat(data?.dinner_price || 0), 
      condition: data?.lunch && data?.dinner,
      totalPrice: parseFloat(basePrice) + parseFloat(data?.lunch_price || 0) + parseFloat(data?.dinner_price || 0)
    },
    { 
      title: "Room with All Meals", 
      extraPrice: parseFloat(data?.breakfast_price || 0) + parseFloat(data?.lunch_price || 0) + parseFloat(data?.dinner_price || 0), 
      condition: data?.breakfast && data?.lunch && data?.dinner,
      totalPrice: parseFloat(basePrice) + parseFloat(data?.breakfast_price || 0) + parseFloat(data?.lunch_price || 0) + parseFloat(data?.dinner_price || 0)
    },
  ];

  const basePriceDouble = data?.double_price || basePrice*2;
  const extraBedPrice = data?.bed_details?.[0]?.extra_bed_price || 0;

  const getExtraBedPriceForPersonCount = (personCount) => {
    if (personCount <= 2) return 0;
    // Add extra bed price for additional persons (3rd person = 1 extra bed, 4th person = 2 extra beds, etc.)
    return extraBedPrice * (personCount - 2);
  };

  const mealOptionsDouble = [
    { 
      title: "Room Only", 
      extraPrice: 0,
      totalPrice: (personCount) => parseFloat(basePriceDouble) + getExtraBedPriceForPersonCount(personCount)
    },
    { 
      title: "Room with Breakfast", 
      extraPrice: data?.breakfast_price, 
      condition: data?.breakfast,
      totalPrice: (personCount) => parseFloat(basePriceDouble) + parseFloat(data?.breakfast_price || 0) + getExtraBedPriceForPersonCount(personCount)
    },
    { 
      title: "Room with Lunch", 
      extraPrice: data?.lunch_price, 
      condition: data?.lunch,
      totalPrice: (personCount) => parseFloat(basePriceDouble) + parseFloat(data?.lunch_price || 0) + getExtraBedPriceForPersonCount(personCount)
    },
    { 
      title: "Room with Dinner", 
      extraPrice: data?.dinner_price, 
      condition: data?.dinner,
      totalPrice: (personCount) => parseFloat(basePriceDouble) + parseFloat(data?.dinner_price || 0) + getExtraBedPriceForPersonCount(personCount)
    },
    { 
      title: "Room with Breakfast & Lunch", 
      extraPrice: parseFloat(data?.breakfast_price || 0) + parseFloat(data?.lunch_price || 0), 
      condition: data?.breakfast && data?.lunch,
      totalPrice: (personCount) => parseFloat(basePriceDouble) + parseFloat(data?.breakfast_price || 0) + parseFloat(data?.lunch_price || 0) + getExtraBedPriceForPersonCount(personCount)
    },
    { 
      title: "Room with Breakfast & Dinner", 
      extraPrice: parseFloat(data?.breakfast_price || 0) + parseFloat(data?.dinner_price || 0), 
      condition: data?.breakfast && data?.dinner,
      totalPrice: (personCount) => parseFloat(basePriceDouble) + parseFloat(data?.breakfast_price || 0) + parseFloat(data?.dinner_price || 0) + getExtraBedPriceForPersonCount(personCount)
    },
    { 
      title: "Room with Lunch & Dinner", 
      extraPrice: parseFloat(data?.lunch_price || 0) + parseFloat(data?.dinner_price || 0), 
      condition: data?.lunch && data?.dinner,
      totalPrice: (personCount) => parseFloat(basePriceDouble) + parseFloat(data?.lunch_price || 0) + parseFloat(data?.dinner_price || 0) + getExtraBedPriceForPersonCount(personCount)
    },
    { 
      title: "Room with All Meals", 
      extraPrice: parseFloat(data?.breakfast_price || 0) + parseFloat(data?.lunch_price || 0) + parseFloat(data?.dinner_price || 0), 
      condition: data?.breakfast && data?.lunch && data?.dinner,
      totalPrice: (personCount) => parseFloat(basePriceDouble) + parseFloat(data?.breakfast_price || 0) + parseFloat(data?.lunch_price || 0) + parseFloat(data?.dinner_price || 0) + getExtraBedPriceForPersonCount(personCount)
    },
  ];

  const maxAvailableOccupancy = Math.min(bed?.max_occupancy, pax);

  // Check if no room options are available
  if (maxAvailableOccupancy <= 0) {
    return (
      <Box sx={{ width: '100%', mt: 2 }}>
        <Alert 
          severity="warning" 
          sx={{ 
            borderRadius: '12px',
            '& .MuiAlert-message': {
              width: '100%'
            }
          }}
        >
          <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 1 }}>
            No Available Options
          </Typography>
          <Typography variant="body2">
            This room cannot accommodate your group size. Please try a different room type or contact support for assistance.
          </Typography>
        </Alert>
      </Box>
    );
  }

  return (
    <Box sx={{ width: '100%' }}>
      {Array.from({ length: maxAvailableOccupancy }).map((_, personIndex) => {
        const personCount = personIndex + 1;

        return (
       <>
            {/* Person Count Header */}
            <Box
              display="flex"
              alignItems="center"
              gap={1}
              mt={2}
              mb={1}
              sx={{
                backgroundColor: '#e6f2ff',
                padding: '5px 5px',
                borderRadius: '8px',
                boxShadow: '0 2px 4px rgba(0,0,0,0.05)'
              }}
            >
              <Box display="flex" alignItems="center">
                {Array.from({ length: personCount }).map((_, iconIndex) => (
                  <IoPersonSharp 
                    key={iconIndex}
                    size={20}
                    style={{ 
                      color: '#3554D1',
                      marginRight: '2px'
                    }}
                  />
                ))}
              </Box>
              {/* <Typography 
                variant="subtitle1" 
                sx={{ 
                  fontWeight: 500,
                  color: '#333'
                }}
              >
                {`For ${personCount} Person(s)`}
              </Typography> */}
            </Box>

            {/* Room Cards Grid */}
            <Box
              display="grid"
              gap={2}
            >
              {/* Render options based on person count */}
              {personCount === 1 ? (
                // Single Person Options
                mealOptions
                  .filter(({ condition }) => condition !== false)
                  .map(({ title, extraPrice, totalPrice }, mealIndex) => {
                    return (
                      <Box key={mealIndex} sx={{ width: '100%' }}>
                        <RoomCard
                          babyCot={babyCot}
                          bedArray={bedArray}
                          pax={pax}
                          paxCount={paxCount}
                          setPaxCount={setPaxCount}
                          bedIndex={bedIndex}
                          globalCount={globalCount}
                          setGlobalCount={setGlobalCount}
                          setbedArray ={setbedArray}
                          setOccupancyArray={setOccupancyArray}
                          occupancyArray={occupancyArray}
                          occupancyCount ={occupancyCount}
                          setOccupancyCount={setOccupancyCount}
                          personCount1={personCount}  
                          priceArr={priceArr}
                          setPriceArr ={setPriceArr}
                          maxOccupancy={maxOccupancy}   
                          mealType={mealType}     
                          setMealType={setMealType}
                          hotelType={hotelType}
                          setHotelType={setHotelType}
                          roomTypeIndex={roomTypeIndex}
                          totalPersonCount={totalPersonCount}
                          setTotalPersonCount={setTotalPersonCount}
                          acctualOccupency={acctualOccupency}
                     
                          title={title}
                          price={totalPrice}
                          data={data}
                        />
                      </Box>
                    );
                  })
              ) : (
                // Multiple Person Options
                mealOptionsDouble
                  .filter(({ condition }) => condition !== false)
                  .map(({ title, extraPrice, totalPrice }, mealIndex) => {
                    // Calculate the total price including any extra bed costs
                    const calculatedPrice = typeof totalPrice === 'function' ? totalPrice(personCount) : totalPrice;
                    
                    return (
                      <Box key={mealIndex} sx={{ width: '100%' }}>
                        <RoomCard
                          babyCot={babyCot}
                          bedArray={bedArray}
                          pax={pax}
                          paxCount={paxCount}
                          setPaxCount={setPaxCount}
                          bedIndex={bedIndex}
                          setbedArray ={setbedArray}
                          setOccupancyArray={setOccupancyArray}
                          occupancyArray={occupancyArray}
                          occupancyCount ={occupancyCount}
                         setOccupancyCount={setOccupancyCount}
                         globalCount={globalCount}
                         setGlobalCount={setGlobalCount}
                         personCount1={personCount}  
                         priceArr={priceArr}
                         setPriceArr ={setPriceArr}
                         maxOccupancy={maxOccupancy}  
                           mealType={mealType}     
                        setMealType={setMealType}   
                         hotelType={hotelType}
                         setHotelType={setHotelType}    
                         roomTypeIndex={roomTypeIndex}
                         totalPersonCount={totalPersonCount}
                         acctualOccupency={acctualOccupency}
                      
                          title={title}
                          price={calculatedPrice}
                          data={data}
                        />
                      </Box>
                    );
                  })
              )}
            </Box>
          </>
        );
      })}
    </Box>
  );
};

export default RenderRoomCards;
