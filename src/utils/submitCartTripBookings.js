import axios from "axios";
import Cookies from "js-cookie";
import { BASE_URL } from "@/services/api";
import { selectDmcId } from "@/slice/dmc/dmcSlice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { setHaveBooking, setBookingType } from "@/slice/common/commonSlice";
import { setBookingResponse } from "@/slice/common/customerInfo";
import { setTourIdd } from "@/slice/common/authSlices";
import { setTourId, updateStepStatus } from "@/slice/common/stepsSlice";
import { setId } from "@/slice/hotel/hotelSlice";

const customerFields = (form) => ({
  fullName: form.fullName || "",
  email: form.email || "",
  phone: form.phone || "",
  countryCode: form.countryCode || "",
  address1: form.address1 || "",
  address2: form.address2 || "",
  state: form.state || "",
  zip: form.zip || "",
  specialRequests: form.specialRequests || "",
});

const isOnlineHotelBooking = (item) => {
  if (item?.type && item.type !== "hotel") return false;
  if (item?.data_mode === "online" || item?.online === true) return true;
  if (item?.data_mode === "offline") return false;
  const rooms = Array.isArray(item?.bookingArray) ? item.bookingArray : [];
  return rooms.some((room) => {
    const roomId = String(room?.room_id ?? "");
    if (roomId && !/^\d+$/.test(roomId)) return true;
    const beds = Array.isArray(room?.beds) ? room.beds : [];
    return beds.some((bed) => {
      const bedId = String(bed?.bed_id ?? "");
      return bedId && !/^\d+$/.test(bedId);
    });
  });
};

const enrichOnlineHotelBooking = (item, state, customerInfo, agent) => {
  if (!isOnlineHotelBooking(item)) return item;

  const details = state.hoteldetails?.bookingDetails || {};
  const sameHotel =
    details?.data_mode === "online" &&
    String(details?.hotel_id || details?.id || "") ===
      String(item?.hotel_id || item?.id || "");
  const sourceRooms = sameHotel && Array.isArray(details.rooms) ? details.rooms : [];
  const bookingArray = (Array.isArray(item.bookingArray) ? item.bookingArray : []).map(
    (selected) => {
      const match = sourceRooms.find(
        (room) => String(room?.room_id) === String(selected?.room_id)
      );
      if (!match) return selected;
      return {
        ...selected,
        rate_plan_id: selected.rate_plan_id || match.rate_plan_id || null,
        rate_plan_name: selected.rate_plan_name || match.rate_plan_name || match.meal_plan || null,
        supplier_code:
          selected.supplier_code || match.supplier_code || match.booking?.supplier_code || null,
        session_id: selected.session_id || match.booking?.session_id || details.session_id || null,
        currency: selected.currency || match.booking?.currency || details.currency || null,
        booking: selected.booking || match.booking || null,
        meal_plan: selected.meal_plan || match.meal_plan || null,
      };
    }
  );
  const lead = bookingArray[0] || {};
  const agentId = agent?.agentId ?? null;
  const agentUser = {
    id: agentId,
    agent_id: agentId,
    email: agent?.agentEmail || customerInfo.email || "",
    name: agent?.agentName || customerInfo.fullName || "",
  };

  return {
    ...item,
    ...customerInfo,
    data_mode: "online",
    bookingArray,
    rooms: bookingArray,
    session_id: item.session_id || (sameHotel ? details.session_id : null) || lead.session_id || null,
    supplier_code:
      item.supplier_code || (sameHotel ? details.supplier_code : null) || lead.supplier_code || null,
    currency: item.currency || (sameHotel ? details.currency : null) || lead.currency || null,
    rate_plan_id: item.rate_plan_id || lead.rate_plan_id || null,
    agent_id: agentId,
    user_id: agentId,
    email: customerInfo.email || agentUser.email,
    guest_email: customerInfo.email,
    guest_name: customerInfo.fullName,
    guest_phone: customerInfo.phone,
    agent_email: agentUser.email,
    customerInfo,
    userInfo: customerInfo,
    user: agentUser,
    agent: agentUser,
    hotelDetails: {
      hotel_id: item.hotel_id || details.hotel_id || "",
      hotel_name: item.hotel_name || details.hotel_name || "",
      location: item.location || details.location || item.city || "",
      city: item.city || "",
      address: item.address || details.address || "",
      image: item.image || details.image || "",
      check_in: item.check_in || "",
      check_out: item.check_out || "",
      data_mode: "online",
      session_id: item.session_id || details.session_id || lead.session_id || null,
      supplier_code: item.supplier_code || details.supplier_code || lead.supplier_code || null,
      dmc_id: item.dmc_id || details.dmc_id || null,
    },
    bookingDate: [item.check_in, item.check_out].filter(Boolean),
  };
};

const resolveAgentId = (state) => {
  const userRole = state.auth?.userRole;
  const agentID = state.editing?.agentId;
  const dmcRoles = [
    "Sales Head(DMC)",
    "Sales Manager (DMC)",
    "Assistant Manager (DMC)",
    "DMC Assistant Operational Manager",
    "DMC Operational Manager",
    "Operational Head(DMC)",
  ];
  if (dmcRoles.includes(userRole) && agentID) return agentID;
  return Cookies.get("AgentId");
};

const applyBookAllResponse = (dispatch, response) => {
  if (response?.service?.date_service) {
    dispatch(setDateService(response.service.date_service));
  }
  if (response?.order?.bookingType) {
    dispatch(setBookingType(response.order.bookingType));
  }
  dispatch(setHaveBooking(true));
  dispatch(setBookingResponse(response));

  const rawTourId = response?.order?.tour_id || response?.tour_id;
  const tourIdMatch =
    rawTourId != null ? String(rawTourId).match(/\d+$/) : null;
  const tourId = tourIdMatch ? tourIdMatch[0] : rawTourId;
  if (tourId) {
    dispatch(setId(tourId));
    dispatch(setTourId(tourId));
    dispatch(setTourIdd(tourId));
  }
};

/**
 * Submit cart as stored in Redux/localStorage to /book-all.
 * Payload matches cart trip shape: trip meta + bookings[] + customerInfo.
 */
export async function submitCartTripBookings(
  dispatch,
  getState,
  { trip, form, bookingType = "booking", onProgress }
) {
  const bookings = Array.isArray(trip?.bookings) ? trip.bookings : [];
  if (!bookings.length) {
    throw new Error("No bookings in this trip.");
  }

  const customerInfo = customerFields(
    form?.fullName
      ? form
      : { ...(trip?.customerInfo || {}), ...(form || {}) }
  );
  if (!customerInfo.fullName) {
    throw new Error("Customer information is missing on this trip.");
  }

  const kind = bookingType === "enquiry" ? "enquiry" : "booking";
  const state = getState();
  const authToken = Cookies.get("authToken");
  const AgentId = resolveAgentId(state);
  const dmcId = selectDmcId(state);

  if (!authToken || !AgentId) {
    throw new Error("Authorization and AgentId are missing.");
  }

  onProgress?.({
    index: 0,
    total: 1,
    label: "All cart services",
    type: "book-all",
    bookingType: kind,
  });

  dispatch(setBookingType(kind));

  // Same shape as state.cart JSON (array of trips)
  const adultGenders = Array.isArray(trip.adultGenders)
    ? trip.adultGenders
    : undefined;
  const maleCount = Number(
    trip.maleCount ??
      trip.male_count ??
      (adultGenders
        ? adultGenders.filter((g) => String(g).toLowerCase() === "male").length
        : 0)
  );
  const femaleCount = Number(
    trip.femaleCount ??
      trip.female_count ??
      (adultGenders
        ? adultGenders.filter((g) => String(g).toLowerCase() === "female")
            .length
        : 0)
  );
  const city_type =
    trip.city_type === "multi" || trip.city_type === "single"
      ? trip.city_type
      : Array.isArray(trip.destination) && trip.destination.length > 1
        ? "multi"
        : "single";

  const agentEmail = Cookies.get("Email") || state.auth?.Email || "";
  const agentName = Cookies.get("Username") || state.auth?.Username || "";
  const preparedBookings = bookings.map((item) => {
    if (item?.type !== "hotel") return item;
    const withAgent = {
      ...item,
      agent_id: Number(AgentId) || AgentId,
      user_id: Number(AgentId) || AgentId,
    };
    return enrichOnlineHotelBooking(withAgent, state, customerInfo, {
      agentId: Number(AgentId) || AgentId,
      agentEmail,
      agentName,
    });
  });

  const payload = [
    {
      tripId: trip.tripId,
      check_in: trip.check_in || "",
      check_out: trip.check_out || "",
      destination: trip.destination ?? [],
      country: trip.country || "",
      searchLocation: Array.isArray(trip.searchLocation)
        ? trip.searchLocation
        : [],
      adult: Number(trip.adult ?? 0),
      child: Number(trip.child ?? 0),
      infant: Number(trip.infant ?? 0),
      maleCount,
      femaleCount,
      city_type,
      cityWiseDates: Array.isArray(trip.cityWiseDates)
        ? trip.cityWiseDates
        : [],
      tour_id: trip.tour_id ?? null,
      bookings: preparedBookings,
      customerInfo,
      customer: customerInfo,
      email: customerInfo.email,
      guest_email: customerInfo.email,
      guest_name: customerInfo.fullName,
      guest_phone: customerInfo.phone,
      agent_email: agentEmail,
      agent_id: Number(AgentId) || AgentId,
      user_id: Number(AgentId) || AgentId,
      user: {
        id: Number(AgentId) || AgentId,
        agent_id: Number(AgentId) || AgentId,
        email: agentEmail || customerInfo.email,
        name: agentName,
      },
      agent: {
        id: Number(AgentId) || AgentId,
        agent_id: Number(AgentId) || AgentId,
        email: agentEmail || customerInfo.email,
        name: agentName,
      },
      bookingType: kind,
      dmc_id: dmcId,
      ...(adultGenders ? { adultGenders } : {}),
      ...(Array.isArray(trip.childrenAges)
        ? { childrenAges: trip.childrenAges }
        : {}),
    },
  ];

  try {
    const response = await axios.post(`${BASE_URL}/book-all`, payload, {
      headers: {
        Authorization: `Bearer ${authToken}`,
        "Content-Type": "application/json",
        "agent-id": AgentId,
      },
    });

    const data = response.data;
    const failed = data?.results?.find((result) => result?.error || result?.success === false);
    if (data?.success === false || Number(data?.failed_count) > 0 || failed?.error) {
      const detail =
        failed?.error ||
        failed?.message ||
        data?.message ||
        "Failed to book services.";
      throw new Error(detail);
    }
    applyBookAllResponse(dispatch, data);

    const types = new Set(bookings.map((b) => b.type));
    if (types.has("hotel")) {
      dispatch(updateStepStatus({ key: "hotel", status: 3 }));
    }
    if (types.has("guide")) {
      dispatch(updateStepStatus({ key: "guide", status: 3 }));
    }

    return [
      {
        type: "book-all",
        label: "All cart services",
        response: data,
        bookingType: kind,
        bookedCount: bookings.length,
      },
    ];
  } catch (error) {
    const body = error?.response?.data;
    const failed = Array.isArray(body?.results)
      ? body.results.find((result) => result?.error)
      : null;
    const message =
      failed?.error ||
      body?.message ||
      (typeof body === "string" ? body : null) ||
      error?.message ||
      "Failed to submit cart bookings.";
    throw new Error(message);
  }
}
