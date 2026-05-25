import { createSlice } from "@reduxjs/toolkit";

// Utility function to calculate total nights from check-in and check-out dates
const calculateTotalNights = (checkIn, checkOut) => {
  if (!checkIn || !checkOut) return 0;

  const checkInDate = new Date(checkIn);
  const checkOutDate = new Date(checkOut);

  const timeDifference = checkOutDate - checkInDate;
  return timeDifference / (1000 * 3600 * 24); // Convert milliseconds to days
};

const hotelDetailsSlice = createSlice({
  name: "hotelDetails",
  initialState: {
    bookingDetails: {
      hotel_id:"",
      hotel_name: '',
      location: '',
      image: '',
      site_image:[],
      cancellation_charge: [],
      // Add any other default properties you expect
    },
    images: [], // Images for the selected hotel
    totalNights: 0, // Total nights of stay
    totalPrice: 0,
    bookingArray: [], // Add this to store booking data
    priceMode: 'SGD', // default currency
    hotelPolicies:[]
  },
  reducers: {
    setTotalPrice: (state, action) => {
      state.totalPrice = action.payload;
    },
    // calculateTotalPrice: (state) => {
    //   // Calculate total price from all sources
    //   let total = 0;
      
    //   // Iterate through the booking array to sum up all prices
    //   state.bookingArray.forEach(room => {
    //     room.beds.forEach(bed => {
    //       // Add base room price
    //       if (bed.price) {
    //         total += Number(bed.price);
    //       }
          
    //       // Add meal prices from selected meals
    //       if (bed.selectedMeals) {
    //         Object.values(bed.selectedMeals).forEach(meal => {
    //           if (meal && meal.price) {
    //             total += Number(meal.price);
    //           }
    //         });
    //       }
          
    //       // Add baby cot price if applicable
    //       if (bed.baby_cot && bed.baby_cot_price) {
    //         total += Number(bed.baby_cot_price);
    //       }
    //     });
    //   });
      
    //   state.totalPrice = total;
    // },
    setHotelDetails: (state, action) => {
      // Make sure we handle the case where the incoming payload might have 'id' instead of 'hotel_id'
      const payload = action.payload;
      
      // If payload has 'id' but no 'hotel_id', create a hotel_id field from id
      const updatedPayload = {
        ...payload,
        hotel_id: payload.hotel_id || payload.id || "",
      };
      
      state.bookingDetails = updatedPayload;
      // console.log("Hotel details set:", state.bookingDetails);
    },
    setHotelPolicies:(state,action) =>{
     state.hotelPolicies =action.payload
     // console.log("Hotel policies set:", state.hotelPolicies);
    },
    setHotelImages: (state, action) => {
      state.images = action.payload; // Update hotel images
    },
    setBookingArray: (state, action) => {
      state.bookingArray = action.payload;
      // console.log("Booking Array",state.bookingArray);
    },
    updateBookingArray: (state, action) => {
      const { roomId, bedId, operation, data } = action.payload;
    
      const updatedArray = [...state.bookingArray];
      // console.log("Updated Array before changes:", JSON.stringify(updatedArray));
      const roomIndex = updatedArray.findIndex((room) => room.room_id === roomId);

      if (operation === "increment") {
        if (roomIndex !== -1) {
          const bedIndex = updatedArray[roomIndex].beds.findIndex(
            (bed) => bed.bed_id === bedId
          );
          if (bedIndex !== -1) {
            // Update existing bed
            const updatedBed = {
              ...updatedArray[roomIndex].beds[bedIndex],
              head_count: updatedArray[roomIndex].beds[bedIndex].head_count + data.head_count,
              baby_cot: data.baby_cot,
            };

            // Handle meal types
            updatedBed.mealTypes = updatedBed.mealTypes || [];
            if (!updatedBed.mealTypes.includes(data.mealTypes[0])) {
              updatedBed.mealTypes = [...updatedBed.mealTypes, ...data.mealTypes];
            }

            // Handle selected meals - preserve existing meals and add new ones
            updatedBed.selectedMeals = updatedBed.selectedMeals || {};
            
            // Generate a unique key for the new meal selection
            const mealKeys = Object.keys(updatedBed.selectedMeals);
            const newMealKey = `meal_${mealKeys.length + 1}`;
            
            // Add the new meal selection with the generated key
            updatedBed.selectedMeals = {
              ...updatedBed.selectedMeals,
              [newMealKey]: Object.values(data.selectedMeals)[0]
            };

            updatedArray[roomIndex].beds[bedIndex] = updatedBed;
          } else {
            // Add new bed
            updatedArray[roomIndex].beds.push(data);
          }
        } else {
          // Add new room
          updatedArray.push({
            room_id: roomId,
            room_type: data.room_type,
            beds: [data]
          });
        }
      } else if (operation === "decrement") {
        if (roomIndex !== -1) {
          const bedIndex = updatedArray[roomIndex].beds.findIndex(
            (bed) => bed.bed_id === bedId
          );
          if (bedIndex !== -1) {
            const updatedBed = {
              ...updatedArray[roomIndex].beds[bedIndex],
              head_count: updatedArray[roomIndex].beds[bedIndex].head_count - data.head_count
            };

            // Remove the last meal selection
            const mealKeys = Object.keys(updatedBed.selectedMeals);
            if (mealKeys.length > 0) {
              const lastKey = mealKeys[mealKeys.length - 1];
              const { [lastKey]: removed, ...remainingMeals } = updatedBed.selectedMeals;
              updatedBed.selectedMeals = remainingMeals;

              // Update mealTypes based on remaining selections
              updatedBed.mealTypes = [...new Set(
                Object.values(remainingMeals).map(meal => meal.type)
              )];
            }

            if (updatedBed.head_count <= 0) {
              updatedArray[roomIndex].beds.splice(bedIndex, 1);
            } else {
              updatedArray[roomIndex].beds[bedIndex] = updatedBed;
            }

            if (updatedArray[roomIndex].beds.length === 0) {
              updatedArray.splice(roomIndex, 1);
            }
          }
        }
      } else if (operation === "updateBabyCot") {
        // Handle updating baby cot information
        if (roomIndex !== -1) {
          const bedIndex = updatedArray[roomIndex].beds.findIndex(
            (bed) => bed.bed_id == bedId // Use loose equality for type coercion
          );
          
          if (bedIndex !== -1) {
            console.log(`[BABY COT DEBUG] Updating baby cot for bed ${bedId} in room ${roomId}`);
            console.log(`[BABY COT DEBUG] Current bed state:`, JSON.stringify(updatedArray[roomIndex].beds[bedIndex]));
            console.log(`[BABY COT DEBUG] New data:`, JSON.stringify(data));
            
            // Ensure baby_cot_price is a proper number
            const babyCotPrice = data.baby_cot ? parseFloat(data.baby_cot_price || 0) : 0;
            console.log(`[BABY COT DEBUG] Setting baby_cot_price to: ${babyCotPrice} (converted from ${data.baby_cot_price})`);
            
            // Update the bed with the new baby cot information
            updatedArray[roomIndex].beds[bedIndex] = {
              ...updatedArray[roomIndex].beds[bedIndex],
              baby_cot: data.baby_cot,
              baby_cot_price: isNaN(babyCotPrice) ? 0 : babyCotPrice // Ensure it's a valid number
            };
            
            console.log(`[BABY COT DEBUG] Updated bed state:`, JSON.stringify(updatedArray[roomIndex].beds[bedIndex]));
          } else {
            console.error(`[BABY COT DEBUG] Bed ${bedId} not found in room ${roomId}`);
          }
        } else {
          console.error(`[BABY COT DEBUG] Room ${roomId} not found in booking array`);
        }
      }
      
      state.bookingArray = updatedArray;
      // console.log("Updated Array after changes:", JSON.stringify(updatedArray));
      
      // Calculate the new total price after updating the bookingArray
      let total = 0;
      // console.log("[PRICE DEBUG] Starting total price calculation");
      
      // Debug the structure of the booking array
      // console.log("[PRICE DEBUG] Number of rooms in booking array:", updatedArray.length);
      
      updatedArray.forEach((room, roomIdx) => {
        // console.log(`[PRICE DEBUG] Room ${roomIdx}: ${room.room_type}, Beds count: ${room.beds.length}`);
        
        room.beds.forEach((bed, bedIdx) => {
          // console.log(`[PRICE DEBUG] Bed ${bedIdx}: ${bed.bed_type}, Selected meals count: ${Object.keys(bed.selectedMeals || {}).length}`);
          
          // Calculate meal prices
          if (bed.selectedMeals) {
            Object.entries(bed.selectedMeals).forEach(([key, meal]) => {
              if (meal && meal.price) {
                const mealPrice = Number(meal.price);
                // console.log(`[PRICE DEBUG] Adding meal price from ${key}: ${meal.type} - ${mealPrice}`);
                if (!isNaN(mealPrice)) {
                  total += mealPrice;
                }
              }
            });
          }
          
          // Always add baby cot price if applicable - improved error handling
          if (bed.baby_cot && bed.baby_cot_price !== undefined) {
            const babyCotPrice = parseFloat(bed.baby_cot_price);
            // console.log(`[PRICE DEBUG] Baby cot found for bed ${bedIdx}!`);
            // console.log(`[PRICE DEBUG] Baby cot status: ${bed.baby_cot}, price: ${babyCotPrice}, raw value: ${bed.baby_cot_price}`);
            
            if (!isNaN(babyCotPrice) && babyCotPrice > 0) {
              // console.log(`[PRICE DEBUG] Adding baby cot price: ${babyCotPrice}`);
              total += babyCotPrice;
            } else {
              console.log(`[PRICE DEBUG] Invalid baby cot price: ${bed.baby_cot_price}`);
            }
          } else {
            console.log(`[PRICE DEBUG] No baby cot for this bed or price is zero. baby_cot: ${bed.baby_cot}, baby_cot_price: ${bed.baby_cot_price}`);
          }
        });
      });
      
      // console.log(`[PRICE DEBUG] Final calculated total: ${total}`);
      state.totalPrice = total;
    },
    clearHotelDetails: (state) => {
      state.bookingDetails = {
        hotel_id: "",
        hotel_name: '',
        location: '',
        image: '',
        site_image: [],
        cancellation_charge: [],
      };
      state.images = [];
      state.totalNights = 0;
      state.totalPrice = 0;
      state.bookingArray = [];
      state.priceMode = 'SGD';
    },
    setPriceMode: (state, action) => {
      state.priceMode = action.payload;
    },
  },
});

export const { 
  setHotelDetails, 
  setHotelImages, 
  clearHotelDetails, 
  setTotalPrice,
  calculateTotalPrice,
  setBookingArray,
  updateBookingArray,
  setPriceMode,
  setHotelPolicies
} = hotelDetailsSlice.actions;

export default hotelDetailsSlice.reducer;
