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
    console.log("useMealPlanData - Bed details:", roomData?.bed_details);
    
    // Validate API response structure
    const validateApiResponse = (data) => {
      const issues = [];
      
      // Check room-level required fields
      if (!data.room_id) issues.push("Missing room_id");
      if (!data.room_type) issues.push("Missing room_type");
      if (!data.single_price) issues.push("Missing single_price");
      if (!data.double_price) issues.push("Missing double_price");
      
      // Check meal fields
      if (data.breakfast === undefined) issues.push("Missing breakfast flag");
      if (data.lunch === undefined) issues.push("Missing lunch flag");
      if (data.dinner === undefined) issues.push("Missing dinner flag");
      
      // Check bed_details array
      if (!Array.isArray(data.bed_details) || data.bed_details.length === 0) {
        issues.push("Missing or empty bed_details array");
      } else {
        const bedDetail = data.bed_details[0];
        if (!bedDetail.bed_id) issues.push("Missing bed_id in bed_details");
        if (!bedDetail.max_occupancy) issues.push("Missing max_occupancy in bed_details");
        if (!bedDetail.extra_bed_price) issues.push("Missing extra_bed_price in bed_details");
      }
      
      if (issues.length > 0) {
        console.warn("useMealPlanData - API Response Validation Issues:", issues);
      } else {
        console.log("useMealPlanData - API Response validation passed ✓");
      }
      
      return issues;
    };
    
    validateApiResponse(roomData);
    
    // Extract base price from room data (API returns prices as strings)
    const singlePrice = parseFloat(roomData?.single_price || 0);
    const doublePrice = parseFloat(roomData?.double_price || singlePrice * 2);
    const extraBedPrice = parseFloat(roomData?.bed_details?.[0]?.extra_bed_price || roomData?.extra_bed_price || 0);
    const extraBedAvailable = roomData?.extra_bed !== false && parseFloat(roomData?.extra_bed_price || roomData?.bed_details?.[0]?.extra_bed_price || 0) > 0;
    
    console.log("useMealPlanData - Raw price data from API:", {
      single_price: roomData?.single_price,
      double_price: roomData?.double_price,
      extra_bed_price: roomData?.bed_details?.[0]?.extra_bed_price || roomData?.extra_bed_price,
      extra_bed: roomData?.extra_bed,
      rooms_only: roomData?.rooms_only,
      breakfast: roomData?.breakfast,
      complemenatry_breakfast_included: roomData?.complemenatry_breakfast_included
    });
    
    console.log("useMealPlanData - Parsed prices:", { singlePrice, doublePrice, extraBedPrice, extraBedAvailable });
    
    // Calculate base price based on person count for room
    // 1 guest: single price, 2 guests: double price, 3+ guests: handle extra bed logic
    const getBasePriceForPersonCount = (count) => {
      if (count <= 1) return singlePrice;
      if (count === 2) return doublePrice;
      
      // For 3+ guests: check extra bed availability
      if (count >= 3) {
        // If extra bed is available and has a price, use it
        if (extraBedAvailable) {
          // For each additional person beyond 2, add extra bed price
          return doublePrice + (extraBedPrice * (count - 2));
        } else {
          // If extra bed is not available or free, use single price for additional guests
          return doublePrice + (singlePrice * (count - 2));
        }
      }
      
      return doublePrice;
    };
    
    // Calculate base room price for the given person count
    const basePrice = getBasePriceForPersonCount(personCount);
    
    // Extract meal availability and pricing
    const breakfast = roomData?.breakfast === 1;
    const lunch = roomData?.lunch === 1;
    const dinner = roomData?.dinner === 1;
    
    const breakfastPrice = parseFloat(roomData?.breakfast_price || 0);
    const lunchPrice = parseFloat(roomData?.lunch_price || 0);
    const dinnerPrice = parseFloat(roomData?.dinner_price || 0);
    
    // Extract meal types for better descriptions
    const breakfastType = roomData?.breakfast_type || '';
    const lunchType = roomData?.lunch_type || '';
    const dinnerType = roomData?.dinner_type || '';
    
    // Check if breakfast is complimentary
    const isBreakfastFree = roomData?.complemenatry_breakfast_included === true;
    const effectiveBreakfastPrice = isBreakfastFree ? 0 : breakfastPrice;
    
    console.log("useMealPlanData - Raw meal data from API:", {
      breakfast_price: roomData?.breakfast_price,
      lunch_price: roomData?.lunch_price,
      dinner_price: roomData?.dinner_price,
      breakfast_type: roomData?.breakfast_type,
      lunch_type: roomData?.lunch_type,
      dinner_type: roomData?.dinner_type
    });
    
    console.log("useMealPlanData - Meal availability:", { breakfast, lunch, dinner });
    console.log("useMealPlanData - Parsed meal prices:", { 
      breakfastPrice, 
      lunchPrice, 
      dinnerPrice, 
      isBreakfastFree,
      effectiveBreakfastPrice 
    });
    
    // Create meal plan options based on room data
    const availableMealPlans = [];
    
    // Add "Room Only" option if rooms_only flag is set to 1
    if (roomData?.rooms_only === 1) {
      availableMealPlans.push({
        id: 'self',
        title: "Room Only", 
        description: `Room only - Total: $${basePrice.toFixed(2)}`,
        price: 0, // No additional cost for meals
        totalPrice: basePrice, // Just room price
        displayPrice: `$${basePrice.toFixed(2)}`,
        personCount: personCount
      });
    }
    
    // Add breakfast option if available
    if (breakfast) {
      // For breakfast, multiply meal price by person count if not free
      const breakfastTotalPrice = isBreakfastFree ? 0 : (effectiveBreakfastPrice * personCount);
      
      const breakfastDescription = isBreakfastFree 
        ? `Room + Complimentary breakfast${breakfastType ? ` (${breakfastType})` : ''} - Total: $${(basePrice + breakfastTotalPrice).toFixed(2)}`
        : `Room + Breakfast${breakfastType ? ` (${breakfastType})` : ''} - Total: $${(basePrice + breakfastTotalPrice).toFixed(2)}`;
        
      availableMealPlans.push({
        id: 'breakfast',
        title: "Room with Breakfast", 
        description: breakfastDescription,
        price: breakfastTotalPrice, // Meal portion only
        totalPrice: basePrice + breakfastTotalPrice, // Room + meals
        displayPrice: isBreakfastFree 
          ? `$${basePrice.toFixed(2)} (Free Breakfast)` 
          : `$${basePrice.toFixed(2)} + $${breakfastTotalPrice.toFixed(2)}`,
        personCount: personCount,
        mealType: breakfastType
      });
    }
    
    // Add lunch option if available
    if (lunch) {
      // For lunch, multiply meal price by person count
      const lunchTotalPrice = lunchPrice * personCount;
      
      availableMealPlans.push({
        id: 'lunch',
        title: "Room with Lunch", 
        description: `Room + Lunch${lunchType ? ` (${lunchType})` : ''} - Total: $${(basePrice + lunchTotalPrice).toFixed(2)}`,
        price: lunchTotalPrice, // Meal portion only
        totalPrice: basePrice + lunchTotalPrice, // Room + meals
        displayPrice: `$${basePrice.toFixed(2)} + $${lunchTotalPrice.toFixed(2)}`,
        personCount: personCount,
        mealType: lunchType
      });
    }
    
    // Add dinner option if available
    if (dinner) {
      // For dinner, multiply meal price by person count
      const dinnerTotalPrice = dinnerPrice * personCount;
      
      availableMealPlans.push({
        id: 'dinner',
        title: "Room with Dinner", 
        description: `Room + Dinner${dinnerType ? ` (${dinnerType})` : ''} - Total: $${(basePrice + dinnerTotalPrice).toFixed(2)}`,
        price: dinnerTotalPrice, // Meal portion only
        totalPrice: basePrice + dinnerTotalPrice, // Room + meals
        displayPrice: `$${basePrice.toFixed(2)} + $${dinnerTotalPrice.toFixed(2)}`,
        personCount: personCount,
        mealType: dinnerType
      });
    }
    
    // Add combination meal options if multiple meals are available
    if (breakfast && lunch) {
      // For breakfast & lunch combo, multiply meal prices by person count
      const breakfastLunchPrice = (isBreakfastFree ? 0 : effectiveBreakfastPrice * personCount) + (lunchPrice * personCount);
      const comboDisplay = isBreakfastFree 
        ? `$${basePrice.toFixed(2)} + $${(lunchPrice * personCount).toFixed(2)} (Free Breakfast)`
        : `$${basePrice.toFixed(2)} + $${(effectiveBreakfastPrice * personCount).toFixed(2)} + $${(lunchPrice * personCount).toFixed(2)}`;
      
      const comboDescription = `Room + Breakfast${breakfastType ? ` (${breakfastType})` : ''} & Lunch${lunchType ? ` (${lunchType})` : ''} - Total: $${(basePrice + breakfastLunchPrice).toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'breakfast_lunch',
        title: "Room with Breakfast & Lunch", 
        description: comboDescription,
        price: breakfastLunchPrice, // Meals portion only
        totalPrice: basePrice + breakfastLunchPrice, // Room + meals
        displayPrice: comboDisplay,
        personCount: personCount,
        mealTypes: [breakfastType, lunchType].filter(Boolean)
      });
    }
    
    if (breakfast && dinner) {
      // For breakfast & dinner combo, multiply meal prices by person count
      const breakfastDinnerPrice = (isBreakfastFree ? 0 : effectiveBreakfastPrice * personCount) + (dinnerPrice * personCount);
      const comboDisplay = isBreakfastFree 
        ? `$${basePrice.toFixed(2)} + $${(dinnerPrice * personCount).toFixed(2)} (Free Breakfast)`
        : `$${basePrice.toFixed(2)} + $${(effectiveBreakfastPrice * personCount).toFixed(2)} + $${(dinnerPrice * personCount).toFixed(2)}`;
      
      const comboDinnerDescription = `Room + Breakfast${breakfastType ? ` (${breakfastType})` : ''} & Dinner${dinnerType ? ` (${dinnerType})` : ''} - Total: $${(basePrice + breakfastDinnerPrice).toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'breakfast_dinner',
        title: "Room with Breakfast & Dinner", 
        description: comboDinnerDescription,
        price: breakfastDinnerPrice, // Meals portion only
        totalPrice: basePrice + breakfastDinnerPrice, // Room + meals
        displayPrice: comboDisplay,
        personCount: personCount,
        mealTypes: [breakfastType, dinnerType].filter(Boolean)
      });
    }
    
    if (lunch && dinner) {
      // For lunch & dinner combo, multiply meal prices by person count
      const lunchDinnerPrice = (lunchPrice * personCount) + (dinnerPrice * personCount);
      
      const lunchDinnerDescription = `Room + Lunch${lunchType ? ` (${lunchType})` : ''} & Dinner${dinnerType ? ` (${dinnerType})` : ''} - Total: $${(basePrice + lunchDinnerPrice).toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'lunch_dinner',
        title: "Room with Lunch & Dinner", 
        description: lunchDinnerDescription,
        price: lunchDinnerPrice, // Meals portion only
        totalPrice: basePrice + lunchDinnerPrice, // Room + meals
        displayPrice: `$${basePrice.toFixed(2)} + $${(lunchPrice * personCount).toFixed(2)} + $${(dinnerPrice * personCount).toFixed(2)}`,
        personCount: personCount,
        mealTypes: [lunchType, dinnerType].filter(Boolean)
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
      
      const allMealsTypes = [breakfastType, lunchType, dinnerType].filter(Boolean);
      const allMealsDescription = allMealsTypes.length > 0 
        ? `Room + All Meals (${allMealsTypes.join(', ')}) - Total: $${(basePrice + allMealsPrice).toFixed(2)}`
        : `Room + All Meals - Total: $${(basePrice + allMealsPrice).toFixed(2)}`;
      
      availableMealPlans.push({
        id: 'fullboard',
        title: "Room with All Meals", 
        description: allMealsDescription,
        price: allMealsPrice, // Meals portion only
        totalPrice: basePrice + allMealsPrice, // Room + meals
        displayPrice: allMealsDisplay,
        personCount: personCount,
        mealTypes: allMealsTypes
      });
    }
    
    console.log("useMealPlanData - Generated meal plans:", availableMealPlans);
    
    return availableMealPlans;
  }, [roomDatas, roomType, bedType, personCount]);
  
  return mealPlans;
};

export default useMealPlanData; 