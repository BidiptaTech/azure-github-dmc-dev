import { useMemo } from 'react';
import { useSelector } from 'react-redux';
import { MEAL_PLAN_OPTIONS } from '../utils/constants';


/**
 * Custom hook for processing meal plan data from room details
 * @param {string} roomType Selected room type ID
 * @param {string} bedType Selected bed type ID
 * @returns {Array} Available meal plans
 */
const useMealPlanData = (roomType, bedType) => {
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
    
    console.log("useMealPlanData - Extracted single price:", singlePrice);
    
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
    const availableMealPlans = [
      { 
        id: 'self',
        title: "Room Only", 
        description: 'No meals included',
        price: singlePrice, // For Room Only, price should be the single_price
        totalPrice: singlePrice,
        displayPrice: `$${singlePrice.toFixed(2)}`
      }
    ];
    
    // Add breakfast option if available
    if (breakfast) {
      availableMealPlans.push({
        id: 'breakfast',
        title: "Room with Breakfast", 
        description: isBreakfastFree ? 'Complimentary breakfast included' : `Breakfast: +$${breakfastPrice.toFixed(2)}`,
        price: effectiveBreakfastPrice,
        totalPrice: singlePrice + effectiveBreakfastPrice,
        displayPrice: isBreakfastFree 
          ? `$${singlePrice.toFixed(2)} (Free Breakfast)` 
          : `$${singlePrice.toFixed(2)} + $${breakfastPrice.toFixed(2)}`
      });
    }
    
    // Add lunch option if available
    if (lunch) {
      availableMealPlans.push({
        id: 'lunch',
        title: "Room with Lunch", 
        description: `Lunch: +$${lunchPrice.toFixed(2)}`,
        price: lunchPrice,
        totalPrice: singlePrice + lunchPrice,
        displayPrice: `$${singlePrice.toFixed(2)} + $${lunchPrice.toFixed(2)}`
      });
    }
    
    // Add dinner option if available
    if (dinner) {
      availableMealPlans.push({
        id: 'dinner',
        title: "Room with Dinner", 
        description: `Dinner: +$${dinnerPrice.toFixed(2)}`,
        price: dinnerPrice,
        totalPrice: singlePrice + dinnerPrice,
        displayPrice: `$${singlePrice.toFixed(2)} + $${dinnerPrice.toFixed(2)}`
      });
    }
    
    // Add combination meal options if multiple meals are available
    if (breakfast && lunch) {
      const comboPrice = effectiveBreakfastPrice + lunchPrice;
      const comboDisplay = isBreakfastFree 
        ? `$${singlePrice.toFixed(2)} + $${lunchPrice.toFixed(2)} (Free Breakfast)`
        : `$${singlePrice.toFixed(2)} + $${breakfastPrice.toFixed(2)} + $${lunchPrice.toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'breakfast_lunch',
        title: "Room with Breakfast & Lunch", 
        description: `Breakfast & Lunch: +$${comboPrice.toFixed(2)}`,
        price: comboPrice,
        totalPrice: singlePrice + comboPrice,
        displayPrice: comboDisplay
      });
    }
    
    if (breakfast && dinner) {
      const comboPrice = effectiveBreakfastPrice + dinnerPrice;
      const comboDisplay = isBreakfastFree 
        ? `$${singlePrice.toFixed(2)} + $${dinnerPrice.toFixed(2)} (Free Breakfast)`
        : `$${singlePrice.toFixed(2)} + $${breakfastPrice.toFixed(2)} + $${dinnerPrice.toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'breakfast_dinner',
        title: "Room with Breakfast & Dinner", 
        description: `Breakfast & Dinner: +$${comboPrice.toFixed(2)}`,
        price: comboPrice,
        totalPrice: singlePrice + comboPrice,
        displayPrice: comboDisplay
      });
    }
    
    if (lunch && dinner) {
      const comboPrice = lunchPrice + dinnerPrice;
      availableMealPlans.push({
        id: 'lunch_dinner',
        title: "Room with Lunch & Dinner", 
        description: `Lunch & Dinner: +$${comboPrice.toFixed(2)}`,
        price: comboPrice,
        totalPrice: singlePrice + comboPrice,
        displayPrice: `$${singlePrice.toFixed(2)} + $${lunchPrice.toFixed(2)} + $${dinnerPrice.toFixed(2)}`
      });
    }
    
    // Add all meals option if breakfast, lunch, and dinner are all available
    if (breakfast && lunch && dinner) {
      const allMealsPrice = effectiveBreakfastPrice + lunchPrice + dinnerPrice;
      const allMealsDisplay = isBreakfastFree 
        ? `$${singlePrice.toFixed(2)} + $${lunchPrice.toFixed(2)} + $${dinnerPrice.toFixed(2)} (Free Breakfast)`
        : `$${singlePrice.toFixed(2)} + $${breakfastPrice.toFixed(2)} + $${lunchPrice.toFixed(2)} + $${dinnerPrice.toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'fullboard',
        title: "Room with All Meals", 
        description: `All Meals: +$${allMealsPrice.toFixed(2)}`,
        price: allMealsPrice,
        totalPrice: singlePrice + allMealsPrice,
        displayPrice: allMealsDisplay
      });
    }
    
    console.log("useMealPlanData - Generated meal plans:", availableMealPlans);
    
    return availableMealPlans;
  }, [roomDatas, roomType, bedType]);
  
  return mealPlans;
};

export default useMealPlanData; 