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
      cityWiseDates: Array.isArray(trip.cityWiseDates)
        ? trip.cityWiseDates
        : [],
      tour_id: trip.tour_id ?? null,
      bookings,
      customerInfo,
      bookingType: kind,
      agent_id: Number(AgentId) || AgentId,
      dmc_id: dmcId,
      ...(Array.isArray(trip.adultGenders)
        ? { adultGenders: trip.adultGenders }
        : {}),
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
    const message =
      error?.response?.data?.message ||
      error?.response?.data ||
      error?.message ||
      "Failed to submit cart bookings.";
    throw typeof message === "string" ? new Error(message) : error;
  }
}
