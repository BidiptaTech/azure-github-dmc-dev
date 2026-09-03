import { useEffect } from "react";
import { Navigate, useParams, useLocation } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { setTourIdd } from "@/slice/common/authSlices";
import { setTourId } from "@/slice/common/stepsSlice";
import { setTourId1 } from "@/slice/common/EditSlice";
import { setId } from "@/slice/hotel/hotelSlice";
import { loadTourSession } from "@/utils/tourSession";

// Paths inside the booking module (may open before a tour_id exists)
const BOOKING_FLOW_PATHS = [
  "/view-hotel-search/",
  "/hotel-details",
  "/hotel-checkout",
  "/thank-you",
  "/attractions",
  "/restaurants",
  "/pickupdrop",
  "/localtransfer",
  "/tourguide",
  "/tour-single/",
  "/restaurants-details/",
  "/activity-single",
  "/restaurants-checkout",
  "/attraction-checkout",
  "/restaurants-thank-you",
  "/attraction-thank-you",
  "/CheckOut",
  "/ThankYou",
  "/cart",
];

const ProtectedRoutetour = ({ children }) => {
  const dispatch = useDispatch();
  const { tourId } = useSelector((state) => state.auth);
  const { id } = useParams();
  const location = useLocation();
  const path = location?.pathname || "";

  const saved = loadTourSession();
  const effectiveTourId = tourId || saved?.tourId || null;

  const isBookingFlowPath = BOOKING_FLOW_PATHS.some((p) => path.includes(p));

  // Rehydrate tour IDs from sessionStorage after refresh (Redux is empty)
  useEffect(() => {
    if (tourId || !saved?.tourId) return;
    dispatch(setTourIdd(saved.tourId));
    dispatch(setTourId(saved.tourId));
    dispatch(setTourId1(saved.tourId));
    dispatch(setId(saved.tourId));
  }, [tourId, saved?.tourId, dispatch]);

  // Allow listing/search before first booking, and any in-flow page on refresh
  if (!effectiveTourId) {
    if (id === "0" || isBookingFlowPath) {
      return children;
    }
    console.log("Redirecting to dashboard due to missing tourId.");
    return <Navigate to="/dashboard/db-dashboard" replace />;
  }

  return children;
};

export default ProtectedRoutetour;
