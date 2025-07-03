import { useMemo } from 'react';
import { useSelector } from 'react-redux';
import { MEAL_PLAN_OPTIONS } from '../utils/constants';


/**
 * Custom hook for processing meal plan data
 * @param {string} roomType Selected room type ID
 * @param {string} bedType Selected bed type ID
 * @returns {Array} Available meal plans
 */
const useMealPlanData = (roomType, bedType) => {
  // Get room data from Redux store
  const { roomDatas } = useSelector(state => state.rooms);
  
  // Extract meal plans from API response
  const mealPlans = useMemo(() => {
    if (!roomDatas || !roomType) return MEAL_PLAN_OPTIONS;
    
    // Extract meal plan information from the API response structure
    if (roomDatas?.room_data && Array.isArray(roomDatas.room_data)) {
      const selectedRoomData = roomDatas.room_data.find(room => room.room_id.toString() === roomType);
      
      if (!selectedRoomData) return MEAL_PLAN_OPTIONS;
      
      // Create dynamic meal plan options based on room data
      const availableMealPlans = [];
      
      // Always add Room Only option
      availableMealPlans.push({
        id: 'self',
        title: 'Room Only',
        description: 'No meals included',
        price: 0
      });
      
      // Add breakfast option if available
      if (selectedRoomData.breakfast === 1) {
        availableMealPlans.push({
          id: 'breakfast',
          title: `Room with ${selectedRoomData.breakfast_type || 'Breakfast'}`,
          description: selectedRoomData.complemenatry_breakfast_included 
            ? 'Complimentary breakfast included' 
            : `Breakfast: $${selectedRoomData.breakfast_price || 0}`,
          price: selectedRoomData.complemenatry_breakfast_included ? 0 : parseFloat(selectedRoomData.breakfast_price || 0)
        });
      }
      
      // Add lunch option if available
      if (selectedRoomData.lunch === 1) {
        availableMealPlans.push({
          id: 'lunch',
          title: `Room with ${selectedRoomData.lunch_type || 'Lunch'}`,
          description: `Lunch: $${selectedRoomData.lunch_price || 0}`,
          price: parseFloat(selectedRoomData.lunch_price || 0)
        });
      }
      
      // Add dinner option if available
      if (selectedRoomData.dinner === 1) {
        availableMealPlans.push({
          id: 'dinner',
          title: `Room with ${selectedRoomData.dinner_type || 'Dinner'}`,
          description: `Dinner: $${selectedRoomData.dinner_price || 0}`,
          price: parseFloat(selectedRoomData.dinner_price || 0)
        });
      }
      
      // Add full board option (breakfast, lunch, dinner) if all are available
      if (selectedRoomData.breakfast === 1 && selectedRoomData.lunch === 1 && selectedRoomData.dinner === 1) {
        const fullBoardPrice = 
          (selectedRoomData.complemenatry_breakfast_included ? 0 : parseFloat(selectedRoomData.breakfast_price || 0)) + 
          parseFloat(selectedRoomData.lunch_price || 0) + 
          parseFloat(selectedRoomData.dinner_price || 0);
        
        availableMealPlans.push({
          id: 'fullboard',
          title: 'Room with Full Board',
          description: `Includes breakfast, lunch and dinner: $${fullBoardPrice.toFixed(2)}`,
          price: fullBoardPrice
        });
      }
      
      return availableMealPlans.length > 0 ? availableMealPlans : MEAL_PLAN_OPTIONS;
    }
    
    // Fallback to previous logic
    const selectedRoomData = Array.isArray(roomDatas)
      ? roomDatas.find(room => room.id?.toString() === roomType || room.room_type === roomType)
      : null;
      
    if (!selectedRoomData || !selectedRoomData.roomOptions) return MEAL_PLAN_OPTIONS;
    
    const selectedBedOption = selectedRoomData.roomOptions.find(option => option.id?.toString() === bedType);
    if (!selectedBedOption || !selectedBedOption.meal_plans) return MEAL_PLAN_OPTIONS;
    
    // If API provides meal plans, use those
    if (Array.isArray(selectedBedOption.meal_plans) && selectedBedOption.meal_plans.length > 0) {
      return selectedBedOption.meal_plans.map((meal, index) => ({
        id: meal.id?.toString() || meal.type || index.toString(),
        title: meal.name || meal.type || `Meal Plan ${index + 1}`,
        description: meal.description || '',
        price: meal.price || 0
      }));
    }
    
    // Fallback to default meal plans
    return MEAL_PLAN_OPTIONS;
  }, [roomDatas, roomType, bedType]);
  
  return mealPlans;
};

export default useMealPlanData; 