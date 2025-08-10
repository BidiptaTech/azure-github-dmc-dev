import { useMemo } from 'react';
import { useSelector } from 'react-redux';
import { MEAL_PLAN_OPTIONS } from '../utils/constants';


/**
 * Custom hook for processing meal plan data from room details
 * @param {string} roomType Selected room type ID
 * @param {string} bedType Selected bed type ID
 * @returns {Array} Available meal plans
 */
const useMealPlanData = (roomType, bedType, personCount = 1) => {
  // Get room data from Redux store
  const { roomDatas } = useSelector(state => state.rooms);
  console.log("roomDatas", roomDatas);
  
  // Extract meal plans from room details (pricing is at room level, not bed level)
  const mealPlans = useMemo(() => {
    if (!roomDatas || !roomType) return MEAL_PLAN_OPTIONS;
    
    // Extract room data from API response structure
    let roomData = null;
    
    if (roomDatas?.room_data && Array.isArray(roomDatas.room_data)) {
      roomData = roomDatas.room_data.find(room => room.room_id.toString() === roomType);
    }
    
    // Fallback to previous data structure if needed
    if (!roomData) {
      roomData = Array.isArray(roomDatas)
        ? roomDatas.find(room => room.id?.toString() === roomType || room.room_type === roomType)
        : null;
    }
    
    // If no room data found, return default options
    if (!roomData) {
      console.warn("No room data found for meal plans, using defaults");
      return MEAL_PLAN_OPTIONS;
    }
    
    console.log("useMealPlanData - Found room data:", roomData);
    
    // Extract base price from room data
    const singlePrice = parseFloat(roomData?.single_price || 0);
    const doublePrice = parseFloat(roomData?.double_price || singlePrice * 2);
    const extraBedPrice = parseFloat(roomData?.bed_details?.[0]?.extra_bed_price || 0);
    
    console.log("useMealPlanData - Extracted prices:", { singlePrice, doublePrice, extraBedPrice });
    
    // Calculate base price based on person count
    const getBasePriceForPersonCount = (count) => {
      if (count <= 1) return singlePrice;
      if (count === 2) return doublePrice;
      // Add extra bed price for additional persons (3rd person and above)
      return doublePrice + (extraBedPrice * (count - 2));
    };
    
    // Use personCount if provided, otherwise default to 1
    const basePrice = getBasePriceForPersonCount(personCount);
    
    // Extract meal availability and pricing
    const breakfast = roomData?.breakfast === 1;
    const lunch = roomData?.lunch === 1;
    const dinner = roomData?.dinner === 1;
    
    const breakfastPrice = parseFloat(roomData?.breakfast_price || 0);
    const lunchPrice = parseFloat(roomData?.lunch_price || 0);
    const dinnerPrice = parseFloat(roomData?.dinner_price || 0);
    
    // Check if breakfast is complimentary
    const isBreakfastFree = roomData?.complemenatry_breakfast_included === true;
    const effectiveBreakfastPrice = isBreakfastFree ? 0 : breakfastPrice;
    
    console.log("Meal availability:", { breakfast, lunch, dinner });
    console.log("Meal prices:", { 
      breakfastPrice, 
      lunchPrice, 
      dinnerPrice, 
      isBreakfastFree,
      effectiveBreakfastPrice 
    });
    
    // Create meal plan options based on room data
    const availableMealPlans = [];
    
    // Add "Room Only" option if room_only flag is set to 1
    if (roomData?.room_only === 1) {
      availableMealPlans.push({
        id: 'self',
        title: "Room Only", 
        description: 'No meals included',
        price: 0, // No additional cost for meals
        totalPrice: basePrice,
        displayPrice: `$${basePrice.toFixed(2)}`,
        personCount: personCount
      });
    }
    
    // Add breakfast option if available
    if (breakfast) {
      // For breakfast, multiply meal price by person count if not free
      const breakfastTotalPrice = isBreakfastFree ? 0 : (effectiveBreakfastPrice * personCount);
      
      availableMealPlans.push({
        id: 'breakfast',
        title: "Room with Breakfast", 
        description: isBreakfastFree ? 'Complimentary breakfast included' : `Breakfast: +$${breakfastTotalPrice.toFixed(2)}`,
        price: breakfastTotalPrice,
        totalPrice: basePrice + breakfastTotalPrice,
        displayPrice: isBreakfastFree 
          ? `$${basePrice.toFixed(2)} (Free Breakfast)` 
          : `$${basePrice.toFixed(2)} + $${breakfastTotalPrice.toFixed(2)}`,
        personCount: personCount
      });
    }
    
    // Add lunch option if available
    if (lunch) {
      // For lunch, multiply meal price by person count
      const lunchTotalPrice = lunchPrice * personCount;
      
      availableMealPlans.push({
        id: 'lunch',
        title: "Room with Lunch", 
        description: `Lunch: +$${lunchTotalPrice.toFixed(2)}`,
        price: lunchTotalPrice,
        totalPrice: basePrice + lunchTotalPrice,
        displayPrice: `$${basePrice.toFixed(2)} + $${lunchTotalPrice.toFixed(2)}`,
        personCount: personCount
      });
    }
    
    // Add dinner option if available
    if (dinner) {
      // For dinner, multiply meal price by person count
      const dinnerTotalPrice = dinnerPrice * personCount;
      
      availableMealPlans.push({
        id: 'dinner',
        title: "Room with Dinner", 
        description: `Dinner: +$${dinnerTotalPrice.toFixed(2)}`,
        price: dinnerTotalPrice,
        totalPrice: basePrice + dinnerTotalPrice,
        displayPrice: `$${basePrice.toFixed(2)} + $${dinnerTotalPrice.toFixed(2)}`,
        personCount: personCount
      });
    }
    
    // Add combination meal options if multiple meals are available
    if (breakfast && lunch) {
      // For breakfast & lunch combo, multiply meal prices by person count
      const breakfastLunchPrice = (isBreakfastFree ? 0 : effectiveBreakfastPrice * personCount) + (lunchPrice * personCount);
      const comboDisplay = isBreakfastFree 
        ? `$${basePrice.toFixed(2)} + $${(lunchPrice * personCount).toFixed(2)} (Free Breakfast)`
        : `$${basePrice.toFixed(2)} + $${(effectiveBreakfastPrice * personCount).toFixed(2)} + $${(lunchPrice * personCount).toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'breakfast_lunch',
        title: "Room with Breakfast & Lunch", 
        description: `Breakfast & Lunch: +$${breakfastLunchPrice.toFixed(2)}`,
        price: breakfastLunchPrice,
        totalPrice: basePrice + breakfastLunchPrice,
        displayPrice: comboDisplay,
        personCount: personCount
      });
    }
    
    if (breakfast && dinner) {
      // For breakfast & dinner combo, multiply meal prices by person count
      const breakfastDinnerPrice = (isBreakfastFree ? 0 : effectiveBreakfastPrice * personCount) + (dinnerPrice * personCount);
      const comboDisplay = isBreakfastFree 
        ? `$${basePrice.toFixed(2)} + $${(dinnerPrice * personCount).toFixed(2)} (Free Breakfast)`
        : `$${basePrice.toFixed(2)} + $${(effectiveBreakfastPrice * personCount).toFixed(2)} + $${(dinnerPrice * personCount).toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'breakfast_dinner',
        title: "Room with Breakfast & Dinner", 
        description: `Breakfast & Dinner: +$${breakfastDinnerPrice.toFixed(2)}`,
        price: breakfastDinnerPrice,
        totalPrice: basePrice + breakfastDinnerPrice,
        displayPrice: comboDisplay,
        personCount: personCount
      });
    }
    
    if (lunch && dinner) {
      // For lunch & dinner combo, multiply meal prices by person count
      const lunchDinnerPrice = (lunchPrice * personCount) + (dinnerPrice * personCount);
      
      availableMealPlans.push({
        id: 'lunch_dinner',
        title: "Room with Lunch & Dinner", 
        description: `Lunch & Dinner: +$${lunchDinnerPrice.toFixed(2)}`,
        price: lunchDinnerPrice,
        totalPrice: basePrice + lunchDinnerPrice,
        displayPrice: `$${basePrice.toFixed(2)} + $${(lunchPrice * personCount).toFixed(2)} + $${(dinnerPrice * personCount).toFixed(2)}`,
        personCount: personCount
      });
    }
    
    // Add all meals option if breakfast, lunch, and dinner are all available
    if (breakfast && lunch && dinner) {
      // For all meals, multiply meal prices by person count
      const allMealsPrice = (isBreakfastFree ? 0 : effectiveBreakfastPrice * personCount) + 
                           (lunchPrice * personCount) + 
                           (dinnerPrice * personCount);
                           
      const allMealsDisplay = isBreakfastFree 
        ? `$${basePrice.toFixed(2)} + $${(lunchPrice * personCount).toFixed(2)} + $${(dinnerPrice * personCount).toFixed(2)} (Free Breakfast)`
        : `$${basePrice.toFixed(2)} + $${(breakfastPrice * personCount).toFixed(2)} + $${(lunchPrice * personCount).toFixed(2)} + $${(dinnerPrice * personCount).toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'fullboard',
        title: "Room with All Meals", 
        description: `All Meals: +$${allMealsPrice.toFixed(2)}`,
        price: allMealsPrice,
        totalPrice: basePrice + allMealsPrice,
        displayPrice: allMealsDisplay,
        personCount: personCount
      });
    }
    
    console.log("useMealPlanData - Generated meal plans:", availableMealPlans);
    
    return availableMealPlans;
  }, [roomDatas, roomType, bedType, personCount]);
  
  return mealPlans;
};

export default useMealPlanData; 