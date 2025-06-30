import React, { useRef, useState, useEffect } from "react";
import { 
  Box, 
  Typography, 
  Button, 
  Alert,
  Card,
  CardContent,
  Chip,
  Divider,
  IconButton,
  Tooltip,
  Paper
} from '@mui/material';
import { useDispatch, useSelector } from "react-redux";
import { setTotalPrice, updateBookingArray } from "@/slice/hotel/HotelDetailsSlice";
import InfoOutlinedIcon from '@mui/icons-material/InfoOutlined';
import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';

const RoomCard = ({
  title,
  price,
  taxAmount,
  data,
  personCount1,
  bedIndex,
  pax,
  occupancyArray,
  setOccupancyArray,
  occupancyCount,
  setOccupancyCount,
  priceArr,
  setPriceArr,
  //setTotalPrice,
  mealType,
  setMealType,
  hotelType,
  setHotelType,
  roomTypeIndex,
  maxOccupancy,
  max_occupancy,
  setMax_occupancy,
  totalPersonCount,
  setTotalPersonCount,
  acctualOccupency,
  setPaxCount,
  paxCount,

  babyCot
}) => {
  const [globalCount, setGlobalCount] = useState(0);
  const [error, setError] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [personCount, setPersonCount] = useState(personCount1);
  const dispatch = useDispatch();
//   console.log(personCount,"personCount>>>>");
let totalPersonCountRef = useRef(acctualOccupency);
// console.log(totalPersonCount,"totalPersonCount");

const [showError, setShowError] = useState(error); // Control alert visibility

useEffect(() => {
  if (error) {
    setShowError(true);
    const timer = setTimeout(() => {
      setShowError(false);
    }, 5000); // Hide after 5 seconds

    return () => clearTimeout(timer); // Cleanup
  }
}, [error]); // Runs when `error` changes

// Get currency information from Redux store
const currencySymbol = useSelector((state) => state.auth.currencySymbol);
const currencyCode = useSelector((state) => state.auth.currencyCode);
const exchangeRate = useSelector((state) => state.auth.exchangeRate);
const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);

// Calculate prices with exchange rates
const convertedPrice = price * exchangeRate;
const usdPrice = price * usdExchangeRate;
const PriceHide = useSelector((state) => state.auth.PriceHide);


const handleRoomUpdate = (
  roomId,
  bedId,
  personCount,
  price,
  mealType,
  bedType,
  roomType,
  operation
) => {
  setOccupancyArray((prevArray) => {
    const updatedArray = [...prevArray];
    const roomIndex = updatedArray.findIndex((room) => room.room_id === roomId);

    if (roomIndex !== -1) {
      const bedIndex = updatedArray[roomIndex].beds.findIndex(
        (bed) => bed.bed_id === bedId
      );

      if (bedIndex !== -1) {
        const updatedBed = { ...updatedArray[roomIndex].beds[bedIndex] };
        
        // Initialize mealTypes array if it doesn't exist
        updatedBed.mealTypes = updatedBed.mealTypes || [];
        updatedBed.selectedMeals = updatedBed.selectedMeals || {};

        if (operation === "increment") {
          // Add new meal selection
          const mealKey = `meal_${globalCount + 1}`;
          updatedBed.selectedMeals[mealKey] = {
            type: mealType,
            price: price
          };
          
          // Update mealTypes array
          if (!updatedBed.mealTypes.includes(mealType)) {
            updatedBed.mealTypes.push(mealType);
          }

          // Rest of your increment logic
          if (personCount <= updatedBed.max_occupancy) {
            if (personCount <= paxCount) {
              setPaxCount((prev) => prev - personCount);
              updatedBed.max_occupancy -= personCount;
              setGlobalCount((prev) => prev + 1);
              
              // Update local state
              setPriceArr((prevPriceArr) => [...prevPriceArr, price]);
              
              // Update Redux state
              console.log(`[DEBUG] Dispatching updateBookingArray (increment) - price: ${price}, mealType: ${mealType}, roomType: ${roomType}, bedType: ${bedType}`);
              dispatch(updateBookingArray({
                roomId,
                bedId,
                operation,
                data: {
                  bed_id: bedId,
                  bed_type: bedType,
                  max_occupancy: personCount,
                  mealTypes: [mealType],
                  selectedMeals: {
                    [`meal_${globalCount + 1}`]: {
                      type: mealType,
                      price: price
                    }
                  },
                  head_count: personCount,
                  price,
                  baby_cot: babyCot,
                  room_type: roomType
                }
              }));
            } else {
              setError(true);
              setErrorMessage("Person count exceeds total available occupancy.");
              return prevArray;
            }
          } else {
            setError(true);
            setErrorMessage("Not enough availability for this bed!");
            return prevArray;
          }
        } else if (operation === "decrement") {
          // Remove last meal selection
          const mealKey = `meal_${globalCount}`;
          delete updatedBed.selectedMeals[mealKey];
          
          // Update mealTypes array
          const remainingMealTypes = Object.values(updatedBed.selectedMeals)
            .map(meal => meal.type);
          updatedBed.mealTypes = [...new Set(remainingMealTypes)];

          // Rest of your decrement logic
          setPaxCount((prev) => prev + personCount);
          updatedBed.max_occupancy += personCount;
          setGlobalCount((prev) => prev - 1);
          
          // Update local state
          setPriceArr((prevPriceArr) => {
            let updatedPriceArr = [...prevPriceArr];
            const index = prevPriceArr.indexOf(price);
            if (index !== -1) {
              updatedPriceArr.splice(index, 1);
            }
            return updatedPriceArr;
          });
          
          // Update Redux state
          console.log(`[DEBUG] Dispatching updateBookingArray (decrement) - price: ${price}, mealType: ${mealType}, roomType: ${roomType}, bedType: ${bedType}`);
          dispatch(updateBookingArray({
            roomId,
            bedId,
            operation,
            data: {
              bed_id: bedId,
              bed_type: bedType,
              max_occupancy: personCount,
              mealTypes: [],
              selectedMeals: {},
              head_count: personCount,
              price,
              baby_cot: babyCot,
              room_type: roomType
            }
          }));
        }

        updatedArray[roomIndex].beds[bedIndex] = updatedBed;
      } else if (operation === "increment") {
        // Add a new bed to an existing room
        updatedArray[roomIndex].beds.push({
          bed_id: bedId,
          bed_type: bedType,
          max_occupancy: personCount,
          mealType: [`${mealType}*1`],
          head_count: personCount,
          price,
          baby_cot: babyCot,
        });
        
        // Update local state
        setPriceArr((prevPriceArr) => [...prevPriceArr, price]);
        setGlobalCount((prev) => prev + 1);
        setPaxCount((prev) => prev - personCount);
        
        // Update Redux state only once
        console.log(`[DEBUG] Dispatching updateBookingArray (add bed) - price: ${price}, mealType: ${mealType}, roomType: ${roomType}, bedType: ${bedType}`);
        dispatch(updateBookingArray({
          roomId,
          bedId,
          operation,
          data: {
            bed_id: bedId,
            bed_type: bedType,
            max_occupancy: personCount,
            mealTypes: [mealType],
            selectedMeals: {
              [`meal_${globalCount + 1}`]: {
                type: mealType,
                price: price
              }
            },
            head_count: personCount,
            price,
            baby_cot: babyCot,
            room_type: roomType
          }
        }));
      }
    } else if (operation === "increment") {
      // Add a new room
      updatedArray.push({
        room_id: roomId,
        room_type: roomType,
        beds: [
          {
            bed_id: bedId,
            bed_type: bedType,
            max_occupancy: personCount,
            mealType: [`${mealType}*1`],
            head_count: personCount,
            price,
            baby_cot: babyCot,
          },
        ],
      });
      
      // Update local state
      setPriceArr((prevPriceArr) => [...prevPriceArr, price]);
      setGlobalCount((prev) => prev + 1);
      setPaxCount((prev) => prev - personCount);
      
      // Update Redux state only once
      console.log(`[DEBUG] Dispatching updateBookingArray (add room) - price: ${price}, mealType: ${mealType}, roomType: ${roomType}, bedType: ${bedType}`);
      dispatch(updateBookingArray({
        roomId,
        bedId,
        operation,
        data: {
          bed_id: bedId,
          bed_type: bedType,
          max_occupancy: personCount,
          mealTypes: [mealType],
          selectedMeals: {
            [`meal_${globalCount + 1}`]: {
              type: mealType,
              price: price
            }
          },
          head_count: personCount,
          price,
          baby_cot: babyCot,
          room_type: roomType
        }
      }));
    }

    return updatedArray;
  });
};


//console.log(occupancyArray,"occupency arrray ");

// Handler functions (unchanged)
const handleAddRoom = (event) => {
  const roomId = Number(event.currentTarget.getAttribute("data-room-id"));
  const bedId = Number(event.currentTarget.getAttribute("data-bed-id"));
  const personCount = parseInt(event.currentTarget.getAttribute("data-head"));
  const price = parseInt(event.currentTarget.getAttribute("data-price"));
  const bedType = event.currentTarget.getAttribute("data-bed-type");
  const mealType = event.currentTarget.getAttribute("data-meal-type");
  const roomType = event.currentTarget.getAttribute("data-room-type");

  console.log(`[DEBUG] handleAddRoom called - price: ${price}, mealType: ${mealType}`);

  if (!roomId || !bedId) {
    setError(true);
    setErrorMessage("Selected room or bed not found.");
    return;
  }

  if (personCount <= paxCount) {
    handleRoomUpdate(
      roomId,
      bedId,
      personCount,
      price,
      mealType,
      bedType,
      roomType,
      "increment"
    );
  } else {
    setError(true);
    setErrorMessage("Not enough total occupancy available.");
  }
};

const handleIncrement = (event) => {
  const roomId = Number(event.currentTarget.getAttribute("data-room-id"));
  const bedId = Number(event.currentTarget.getAttribute("data-bed-id"));
  const personCount = parseInt(event.currentTarget.getAttribute("data-head"));
  const price = parseInt(event.currentTarget.getAttribute("data-price"));
  const mealType = event.currentTarget.getAttribute("data-meal-type");
  const bedType = event.currentTarget.getAttribute("data-bed-type");
  const roomType = event.currentTarget.getAttribute("data-room-type");

  console.log(`[DEBUG] handleIncrement called - price: ${price}, mealType: ${mealType}`);

  const room = occupancyArray.find((room) => room.room_id === roomId);
  const bed = room?.beds.find((bed) => bed.bed_id === bedId);

  if (!room || !bed) {
    setError(true);
    setErrorMessage("Selected room or bed not found.");
    return;
  }

  if ( personCount <= bed.max_occupancy) {
    handleRoomUpdate(
      roomId,
      bedId,
      personCount,
      price,
      mealType,
      bedType,
      roomType,
      "increment"
    );
  } else {
    setError(true);
    setErrorMessage(
      bed.max_occupancy <= 0
        ? "No occupancy available for this bed."
        : "Person count exceeds bed's max occupancy."
    );
  }
};

const handleDecrement = (event) => {
  const roomId = Number(event.currentTarget.getAttribute("data-room-id"));
  const bedId = Number(event.currentTarget.getAttribute("data-bed-id"));
  const bedType = event.currentTarget.getAttribute("data-bed-type");
  const personCount = parseInt(event.currentTarget.getAttribute("data-head"));
  const price = parseInt(event.currentTarget.getAttribute("data-price"));
  const mealType = event.currentTarget.getAttribute("data-meal-type");
  const roomType = event.currentTarget.getAttribute("data-room-type");

  console.log(`[DEBUG] handleDecrement called - price: ${price}, mealType: ${mealType}`);

  const room = occupancyArray.find((room) => room.room_id === roomId);
  const bed = room?.beds.find((bed) => bed.bed_id === bedId);

  if (!room || !bed) {
    setError(true);
    setErrorMessage("Selected room or bed not found.");
    return;
  }

   handleRoomUpdate(
      roomId,
      bedId,
      personCount,
      price,
      mealType,
      bedType,
      roomType,
      "decrement"
    );
    setError(false);
};


  

  return (
    <Paper
      elevation={2}
      sx={{
        width: '100%',
        borderRadius: '16px',
        overflow: 'hidden',
        transition: 'transform 0.3s ease, box-shadow 0.3s ease',
        '&:hover': {
          transform: 'translateY(-4px)',
          boxShadow: '0 8px 24px rgba(0,0,0,0.12)',
        }
      }}
    >
      <Box
        sx={{
          background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
          padding: '10px 20px',
          color: 'white',
        }}
      >
        <Typography variant="h6" sx={{ fontWeight: 600,fontSize:"16px" }}>
          {title}
        </Typography>
     
      </Box>

      <CardContent sx={{ padding: '10px 20px 10px 20px !important' }}>

        <Box display="flex" justifyContent="space-between" alignItems="center">
          {PriceHide =="0"? (  
            <Box>
              {/* <Typography variant="body2" color="text.secondary">
              Price per room
            </Typography> */}
            <Box display="flex" alignItems="baseline" gap={1}>
              <Typography variant="h5" fontSize={"18px"} fontWeight="bold" color="primary">
              {currencyCode}  {convertedPrice.toFixed(2)}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                /night
              </Typography>
            </Box>
         
            <Box display="flex" alignItems="baseline" gap={1}>
              <Typography variant="caption" fontWeight="bold" color="text.secondary">
              {usdCurrencyCode} {usdPrice.toFixed(2)}
              </Typography>
              <Typography variant="caption" fontWeight="bold" color="text.secondary">
                /night
              </Typography>
            </Box>
            <Box display="flex" alignItems="baseline" gap={1}>
              <Typography variant="caption" fontWeight="bold" color="text.secondary">
                      SGD {price.toFixed(2)}
              </Typography>
              <Typography variant="caption" fontWeight="bold" color="text.secondary">
                /night
              </Typography>
            </Box>
              <Tooltip title="Tax Included" arrow placement="top">
              <IconButton size="small" sx={{ ml: -1 }}>
                <InfoOutlinedIcon fontSize="small" />
              </IconButton>
            </Tooltip>
            <Typography variant="caption" color="text.secondary">
              Tax Excluded
              </Typography>
          </Box>
          ):(
            <div className="py-10 bg-blue-2 rounded-4 mt-10">
          <div className="row justify-between">
            <div className="col-auto">
              <div className="text-15 lh-13 fw-500 text-center w-100">
                Price available on request
              </div>
            </div>
          </div>
        </div>
          )}

          <Box sx={{marginLeft:'5px'}}>
            {globalCount === 0 ? (
              <Button
                variant="contained"
                onClick={handleAddRoom}
                startIcon={<AddIcon />}
                data-price-id={data?.bed_details[bedIndex]?.room_id || []}
                data-price={price}
                data-bed-id={data?.bed_details[bedIndex]?.bed_id || []}
                data-bed-type={data?.bed_details[bedIndex]?.bed_type || []}
                data-room-type={data?.room_type || []}
                data-meal-type={title}
                data-room-id={data?.room_id || []}
                data-head={personCount}
                sx={{
                  borderRadius: '12px',
                  textTransform: 'none',
                  px: 3,
                  py: 1,
                  backgroundColor: '#3554D1',
                  '&:hover': {
                    backgroundColor: '#2442B0',
                  }
                }}
              >
                Select
              </Button>
            ) : (
              <Box
                sx={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: 1,
                  bgcolor: '#F5F5F5',
                  borderRadius: '12px',
                  p: 0.5,
                }}
              >
                <IconButton
                  onClick={handleDecrement}
                  data-price={price}
                  data-price-id={data?.bed_details[bedIndex]?.room_id || []}
                  data-bed-id={data?.bed_details[bedIndex]?.bed_id || []}
                  data-bed-type={data?.bed_details[bedIndex]?.bed_type || []}
                  data-room-type={data?.room_type || []}
                  data-room-id={data?.room_id || []}
                  data-head={personCount}
                  data-meal-type={title}
                  size="small"
                  sx={{ 
                    bgcolor: 'white',
                    boxShadow: 1,
                    '&:hover': { bgcolor: '#EAEAEA' }
                  }}
                >
                  <RemoveIcon fontSize="small" />
                </IconButton>
                <Typography variant="h6" sx={{ minWidth: '40px', textAlign: 'center' }}>
                  {globalCount}
                </Typography>
                <IconButton
                  onClick={handleIncrement}
                  data-price={price}
                  data-price-id={data?.bed_details[bedIndex]?.room_id || []}
                  data-bed-id={data?.bed_details[bedIndex]?.bed_id || []}
                  data-bed-type={data?.bed_details[bedIndex]?.bed_type || []}
                  data-room-type={data?.room_type || []}
                  data-room-id={data?.room_id || []}
                  data-head={personCount}
                  data-meal-type={title}
                  data-personcount-type={setPersonCount}
                  size="small"
                  sx={{ 
                    bgcolor: 'white',
                    boxShadow: 1,
                    '&:hover': { bgcolor: '#EAEAEA' }
                  }}
                >
                  <AddIcon fontSize="small" />
                </IconButton>
              </Box>
            )}
          </Box>
        </Box>

        {/* <Divider sx={{ my: 2 }} /> */}
{/* 
        <Box display="flex" gap={1} flexWrap="wrap">
          <Chip 
            label="Free Cancellation" 
            size="small" 
            color="success"
            variant="outlined"
          />
          <Chip 
            label="Breakfast Included" 
            size="small"
            variant="outlined"
          />
        </Box> */}

        {showError && (
          <Alert 
            severity="error" 
            sx={{ 
              mt: 2,
              borderRadius: '8px',
              '& .MuiAlert-message': {
                width: '100%'
              }
            }}
          >
            {errorMessage}
          </Alert>
        )}
      </CardContent>
    </Paper>
  );
};

export default RoomCard;
