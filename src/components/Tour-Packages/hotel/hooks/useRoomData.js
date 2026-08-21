import { useMemo } from 'react';
import { useSelector } from 'react-redux';

/**
 * Custom hook for processing room and bed data
 * @param {string} roomType Selected room type ID
 * @param {string} bedType Selected bed type ID
 * @param {string} selectedHotel Selected hotel ID
 * @returns {Object} Processed room and bed data
 */
const useRoomData = (roomType, bedType, selectedHotel) => {
  // Get room data from Redux store
  const { roomDatas, status: roomDataStatus } = useSelector(state => state.rooms);
  
  // Debug: Log what we get from Redux
  console.log("useRoomData - roomDataStatus:", roomDataStatus, "hotel:", selectedHotel);
  
  // Extract room types from API response
  const roomTypes = useMemo(() => {
    // Check if roomDatas is an object with room_data array (from the actual API response)
    if (roomDatas?.room_data && Array.isArray(roomDatas.room_data)) {
      const processedRooms = roomDatas.room_data.map((room) => {
        const processedRoom = {
          id: room.room_id.toString(),
          name: room.room_type,
          hotel_id: selectedHotel,
          description: roomDatas.description || '',
          image: room.room_image && room.room_image.length > 0 ? room.room_image[0] : null,
          price: room.single_price || room.double_price || '0',
          tax_percentage: room.tax_percentage,
          complemenatry_breakfast: room.complemenatry_breakfast_included,
          breakfast: room.breakfast,
          lunch: room.lunch,
          dinner: room.dinner,
          roomOptions: room.bed_details || []
        };
        return processedRoom;
      });
      console.log("useRoomData - Processed", processedRooms.length, "room types");
      return processedRooms;
    }
    
    // Fallback to direct roomDatas if it's an array (might be from a different API format)
    if (roomDatas && Array.isArray(roomDatas) && roomDatas.length > 0) {
      console.log("useRoomData - Using fallback array format");
      return roomDatas.map((room, index) => ({
        id: room.id?.toString() || index.toString(),
        name: room.room_type || `Room Type ${index + 1}`,
        hotel_id: selectedHotel,
        description: room.description || '',
        roomOptions: room.roomOptions || []
      }));
    }
    
    // Return empty array if no valid data
    console.log("useRoomData - No valid room data found");
    return [];
  }, [roomDatas, selectedHotel]);
  
  // Extract bed types from API response based on selected room
  const bedTypes = useMemo(() => {
    if (!roomDatas || !roomType) return [];
    
    // Check if roomDatas is an object with room_data array (from the actual API response)
    if (roomDatas?.room_data && Array.isArray(roomDatas.room_data)) {
      const selectedRoomData = roomDatas.room_data.find(room => room.room_id.toString() === roomType);
      
      if (!selectedRoomData || !Array.isArray(selectedRoomData.bed_details)) {
        return [];
      }
      
      return selectedRoomData.bed_details.map((bed) => ({
        id: bed.bed_id.toString(),
        name: bed.bed_type,
        room_type_id: roomType,
        max_occupancy: bed.max_occupancy || (bed.adult_count + bed.child_count) || 2,
        extra_bed: bed.extra_bed,
        extra_bed_price: bed.extra_bed_price,
        baby_cot: bed.baby_cot,
        baby_cot_price: bed.baby_cot_price,
        adult_count: bed.adult_count,
        child_count: bed.child_count,
        price: bed.price || 0
      }));
    }
    
    // Fallback to previous format
    const selectedRoomData = Array.isArray(roomDatas) 
      ? roomDatas.find(room => room.id?.toString() === roomType || room.room_type === roomType)
      : null;
    
    if (!selectedRoomData || !selectedRoomData.roomOptions) {
      return [];
    }
    
    return selectedRoomData.roomOptions.map((option, index) => ({
      id: option.id?.toString() || index.toString(),
      name: option.bed_type || option.name || `Bed Type ${index + 1}`,
      room_type_id: roomType,
      max_occupancy: option.max_occupancy || option.guests || 2,
      price: option.price || 0,
      description: option.description || ''
    }));
  }, [roomDatas, roomType]);
  
  // Get the selected bed type details
  const selectedBedType = useMemo(() => {
    if (!bedType || bedTypes.length === 0) return null;
    return bedTypes.find(bed => bed.id === bedType) || null;
  }, [bedType, bedTypes]);
  
  // Get the selected room type details
  const selectedRoomType = useMemo(() => {
    if (!roomType || roomTypes.length === 0) return null;
    return roomTypes.find(room => room.id === roomType) || null;
  }, [roomType, roomTypes]);
  
  return {
    roomTypes,
    bedTypes,
    selectedRoomType,
    selectedBedType,
    roomDataStatus
  };
};

export default useRoomData; 