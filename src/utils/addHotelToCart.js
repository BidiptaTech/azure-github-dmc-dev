import { addToCart } from "@/slice/cart/carSlice";
import { store } from "@/store/store";

const cloneRooms = (bookingArray) => {
  try {
    return JSON.parse(JSON.stringify(bookingArray || []));
  } catch {
    return Array.isArray(bookingArray) ? [...bookingArray] : [];
  }
};

const countNights = (checkIn, checkOut) => {
  if (!checkIn || !checkOut) return 0;
  const start = new Date(checkIn);
  const end = new Date(checkOut);
  const diff = end - start;
  if (Number.isNaN(diff) || diff <= 0) return 0;
  return Math.round(diff / (1000 * 3600 * 24));
};

export const buildHotelCartItem = ({
  bookingArray,
  totalPrice,
  priceMode,
  hotelDetails,
  tourDetails,
  searchState,
}) => {
  const rooms = cloneRooms(bookingArray);
  const check_in =
    tourDetails?.check_in ||
    tourDetails?.CheckInTime ||
    searchState?.ucheckIn ||
    "";
  const check_out =
    tourDetails?.check_out ||
    tourDetails?.CheckOutTime ||
    searchState?.ucheckOut ||
    "";

  const roomCount = rooms.reduce((sum, room) => {
    const beds = Array.isArray(room.beds) ? room.beds : [];
    const fromBeds = beds.reduce(
      (bedSum, bed) => bedSum + (Number(bed.head_count) || 0),
      0
    );
    return sum + (fromBeds || 1);
  }, 0);

  return {
    hotel_id: hotelDetails?.hotel_id || hotelDetails?.id || "",
    hotel_name: hotelDetails?.hotel_name || "",
    image: hotelDetails?.image || "",
    address: hotelDetails?.address || hotelDetails?.location || "",
    location: hotelDetails?.location || "",
    bookingArray: rooms,
    totalPrice: Math.ceil(Number(totalPrice) || 0),
    priceMode: priceMode || null,
    pricemode: priceMode || null,
    check_in,
    check_out,
    bookingDate: check_in,
    adults: Number(tourDetails?.adult ?? searchState?.guests?.adults ?? 0),
    children: Number(tourDetails?.child ?? searchState?.guests?.children ?? 0),
    infant: Number(tourDetails?.infant ?? searchState?.guests?.infant ?? 0),
    nights: countNights(check_in, check_out),
    roomCount,
    room_summary: rooms
      .map((room) => room.room_type)
      .filter(Boolean)
      .join(", "),
  };
};

export const addHotelBookingToCart = (dispatch, payload) => {
  if (!Array.isArray(payload?.bookingArray) || payload.bookingArray.length === 0) {
    return "Please select a room first.";
  }

  dispatch(
    addToCart({
      bookingType: "hotel",
      item: buildHotelCartItem(payload),
      tourDetails: payload.tourDetails,
    })
  );

  return store.getState().cart?.lastActionError || null;
};
