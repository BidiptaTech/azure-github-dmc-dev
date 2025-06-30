import React from "react";
import { Box, Typography, Paper } from "@mui/material";
import { IoPersonSharp } from "react-icons/io5";
import RoomCard from "./RoomCard";
import RenderRoomCardsSkeleton from "./RenderRoomCardsSkeleton";
import { useSelector } from "react-redux";

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
  
  if (!basePrice) return null;
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

  if (!basePriceDouble) return null;

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

  return (
    <Box sx={{ width: '100%' }}>
      {Array.from({ length: Math.min(bed?.max_occupancy, pax) || 0 }).map((_, personIndex) => {
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
