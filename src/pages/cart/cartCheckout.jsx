import React, { useMemo, useRef, useState } from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import { useLocation, useNavigate } from "react-router-dom";
import {
  Box,
  Button,
  Card,
  CardContent,
  Chip,
  CircularProgress,
  Divider,
  Grid,
  Stack,
  Typography,
} from "@mui/material";
import ArrowBackIcon from "@mui/icons-material/ArrowBack";
import { toast, ToastContainer } from "react-toastify";
import CartCustomerInfo from "@/components/cart/CartCustomerInfo";
import {
  clearCartByTrip,
  selectCart,
  selectCheckoutTripId,
  setTripCustomerInfo,
} from "@/slice/cart/carSlice";
import { setUserInfo } from "@/slice/common/customerInfo";
import { setIsNavigating } from "@/slice/common/commonSlice";
import { formatDestinationLabel } from "@/utils/applyTourSearchToRedux";
import { submitCartTripBookings } from "@/utils/submitCartTripBookings";

const BOOKING_TYPE_LABELS = {
  entryport: "Entry Port",
  exitport: "Exit Port",
  hotel: "Hotel",
  attraction: "Attraction",
  restaurant: "Restaurant",
  guide: "Guide",
  travelhourly: "Travel Hourly",
  travelpointzone: "Travel Zone",
  local_transport: "Travel Zone",
  travel_hourly: "Travel Hourly",
  travel_point: "Travel Point",
  travelpoint: "Travel Point",
};

const formatPrice = (value) => {
  const num = Number(value) || 0;
  return num.toLocaleString(undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  });
};

const itemTitle = (item) =>
  item.hotel_name ||
  item.AttractionName ||
  item.restaurantName ||
  item.guide_name ||
  item.vehicles_name ||
  BOOKING_TYPE_LABELS[item.type] ||
  item.type ||
  "Booking";

const itemMeta = (item) => {
  if (item.type === "hotel") {
    return [item.check_in, item.check_out].filter(Boolean).join(" → ") || "—";
  }
  if (item.type === "attraction" || item.type === "restaurant") {
    return [item.bookingDate, item.visitTime].filter(Boolean).join(" · ") || "—";
  }
  if (item.type === "guide") {
    return (
      [item.bookingDate || item.pickupdate, item.entrytime, item.hours && `${item.hours}h`]
        .filter(Boolean)
        .join(" · ") || "—"
    );
  }
  return (
    [
      item.bookingDate || item.pickupdate || item.exitpickupdate,
      item.entrytime,
    ]
      .filter(Boolean)
      .join(" · ") || "—"
  );
};

const CartCheckout = () => {
  const dispatch = useDispatch();
  const store = useStore();
  const navigate = useNavigate();
  const location = useLocation();
  const cart = useSelector(selectCart);
  const checkoutTripId = useSelector(selectCheckoutTripId);
  const customerRef = useRef(null);
  const [formData, setFormData] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isEnquiring, setIsEnquiring] = useState(false);
  const [progress, setProgress] = useState(null);
  const bookingType = useSelector((state) => state.common?.bookingType);
  const hasBookingType = Boolean(String(bookingType || "").trim());
  const showBookNow = !hasBookingType || bookingType === "booking";
  const showEnquiry = !hasBookingType || bookingType === "enquiry";

  const trip = useMemo(() => {
    const id =
      location.state?.cartTripId ||
      location.state?.cartTrip?.tripId ||
      checkoutTripId;
    // Prefer live cart so customerInfo from Redux is current
    if (id) {
      const fromCart = (Array.isArray(cart) ? cart : []).find(
        (t) => t.tripId === id
      );
      if (fromCart) return fromCart;
    }
    return location.state?.cartTrip || null;
  }, [location.state, cart, checkoutTripId]);

  const cartCustomerInfo = trip?.customerInfo || null;

  const tripTotal = useMemo(() => {
    if (!trip?.bookings) return 0;
    return trip.bookings.reduce(
      (sum, item) => sum + (Number(item.totalPrice) || 0),
      0
    );
  }, [trip]);

  const runCheckout = async (bookingType) => {
    const kind = bookingType === "enquiry" ? "enquiry" : "booking";
    const isEnquiry = kind === "enquiry";

    if (isSubmitting || isEnquiring) return;
    if (!trip?.bookings?.length) {
      toast.error("No trip to checkout.");
      navigate("/dashboard/db-dashboard/cart");
      return;
    }
    if (!customerRef.current?.isFormValid()) {
      toast.error("Please fill all required fields correctly");
      return;
    }
    const baseForm = customerRef.current.getFormData?.() || formData;
    if (!baseForm?.fullName) {
      toast.error("Customer information is missing");
      return;
    }

    // Same as activity CustomerInfo: attach bookingType on the form payload
    const form = { ...baseForm, bookingType: kind };

    if (isEnquiry) setIsEnquiring(true);
    else setIsSubmitting(true);

    dispatch(setIsNavigating(true));
    // Persist customer with cart trip services, then mirror to shared customerInfo
    dispatch(
      setTripCustomerInfo({
        tripId: trip.tripId,
        customerInfo: baseForm,
      })
    );
    dispatch(setUserInfo(form));

    try {
      const tripWithCustomer = {
        ...trip,
        customerInfo: baseForm,
      };
      const results = await submitCartTripBookings(dispatch, store.getState, {
        trip: tripWithCustomer,
        form,
        bookingType: kind,
        onProgress: setProgress,
      });
      dispatch(clearCartByTrip(trip.tripId));
      toast.success(
        isEnquiry
          ? results.length === 1
            ? "Enquiry submitted successfully."
            : `${results.length} enquiries submitted.`
          : results.length === 1
            ? "Booking confirmed."
            : `${results.length} bookings confirmed.`
      );
      navigate("/dashboard/db-dashboard/ThankYou", {
        replace: true,
        state: {
          bookingResponse: results[results.length - 1]?.response,
          cartCheckout: true,
          bookedCount: results.length,
          bookingType: kind,
        },
      });
    } catch (error) {
      console.error("Cart checkout failed:", error);
      dispatch(setIsNavigating(false));
      toast.error(
        error?.message ||
          error?.payload?.message ||
          (isEnquiry
            ? "Something went wrong while submitting the enquiry. Please try again."
            : "Something went wrong while booking. Please try again.")
      );
    } finally {
      setIsSubmitting(false);
      setIsEnquiring(false);
      setProgress(null);
    }
  };

  const handleBookNow = () => runCheckout("booking");
  const handleEnquirySubmit = () => runCheckout("enquiry");

  if (!trip?.bookings?.length) {
    return (
      <Box
        sx={{
          minHeight: "70vh",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          px: 2,
        }}
      >
        <Card elevation={0} sx={{ p: 4, maxWidth: 420, textAlign: "center", border: "1px solid #e8ecf4", borderRadius: 3 }}>
          <Typography variant="h6" fontWeight={700} mb={1}>
            Nothing to checkout
          </Typography>
          <Typography color="text.secondary" mb={3}>
            Your cart trip is empty or missing. Go back to the cart to continue.
          </Typography>
          <Button
            variant="contained"
            onClick={() => navigate("/dashboard/db-dashboard/cart")}
            sx={{ bgcolor: "#3554d1", textTransform: "none" }}
          >
            Back to cart
          </Button>
        </Card>
        <ToastContainer />
      </Box>
    );
  }

  return (
    <Box
      sx={{
        minHeight: "100vh",
        background: "linear-gradient(180deg, #f5f7fb 0%, #ffffff 55%)",
        py: { xs: 3, md: 5 },
        px: { xs: 2, md: 4 },
        pb: { xs: 12, md: 5 },
      }}
    >
      <Box sx={{ maxWidth: 1100, mx: "auto" }}>
        <Stack
          direction={{ xs: "column", sm: "row" }}
          justifyContent="space-between"
          alignItems={{ xs: "flex-start", sm: "center" }}
          spacing={2}
          mb={3}
        >
          <Box>
            <Button
              startIcon={<ArrowBackIcon />}
              onClick={() => navigate("/dashboard/db-dashboard/cart")}
              sx={{ textTransform: "none", color: "#64748b", mb: 1, px: 0 }}
            >
              Back to cart
            </Button>
            <Typography variant="h4" fontWeight={800} color="#0f172a">
              Checkout
            </Typography>
            <Typography color="text.secondary" mt={0.5}>
              {formatDestinationLabel(trip.destination)} ·{" "}
              {trip.check_in || "—"} → {trip.check_out || "—"}
            </Typography>
          </Box>
        </Stack>

        <Grid container spacing={3}>
          <Grid item xs={12} md={7}>
            <CartCustomerInfo
              ref={customerRef}
              initialCustomerInfo={cartCustomerInfo}
              onFormChange={(next) => {
                setFormData(next);
              }}
            />
          </Grid>

          <Grid item xs={12} md={5}>
            <Card
              elevation={0}
              sx={{
                borderRadius: 3,
                border: "1px solid #e8ecf4",
                position: { md: "sticky" },
                top: { md: 96 },
              }}
            >
              <CardContent sx={{ p: 3 }}>
                <Typography variant="h6" fontWeight={700} mb={0.5}>
                  Order summary
                </Typography>
                <Typography variant="body2" color="text.secondary" mb={2}>
                  {trip.bookings.length} booking
                  {trip.bookings.length === 1 ? "" : "s"} in this trip
                </Typography>

                <Stack spacing={1.5} mb={2}>
                  {trip.bookings.map((item) => (
                    <Box
                      key={item.cartItemId}
                      sx={{
                        p: 1.5,
                        borderRadius: 2,
                        border: "1px solid #e8ecf4",
                        bgcolor: "#fafbff",
                      }}
                    >
                      <Stack
                        direction="row"
                        justifyContent="space-between"
                        alignItems="flex-start"
                        spacing={1}
                      >
                        <Box>
                          <Stack direction="row" spacing={1} alignItems="center" mb={0.5}>
                            <Typography variant="body2" fontWeight={700}>
                              {itemTitle(item)}
                            </Typography>
                            <Chip
                              size="small"
                              label={
                                BOOKING_TYPE_LABELS[item.type] || item.type
                              }
                              sx={{
                                height: 22,
                                fontSize: "0.7rem",
                                bgcolor: "#eef2ff",
                                color: "#3554d1",
                              }}
                            />
                          </Stack>
                          <Typography variant="caption" color="text.secondary">
                            {itemMeta(item)}
                          </Typography>
                        </Box>
                        <Typography fontWeight={700} color="#3554d1">
                          {formatPrice(item.totalPrice)}
                        </Typography>
                      </Stack>
                    </Box>
                  ))}
                </Stack>

                <Divider sx={{ my: 2 }} />

                <Stack
                  direction="row"
                  justifyContent="space-between"
                  alignItems="center"
                  mb={2}
                >
                  <Typography fontWeight={700}>Trip total</Typography>
                  <Typography fontWeight={800} color="#3554d1" fontSize={22}>
                    {formatPrice(tripTotal)}
                  </Typography>
                </Stack>

                {progress && (
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    mb={1.5}
                    textAlign="center"
                  >
                    {progress.bookingType === "enquiry" ? "Enquiry" : "Booking"}{" "}
                    {progress.index + 1} of {progress.total}
                    {progress.label ? `: ${progress.label}` : ""}
                  </Typography>
                )}

                <Stack spacing={1.25}>
                  {showBookNow && (
                    <Button
                      fullWidth
                      variant="contained"
                      disabled={isSubmitting}
                      onClick={handleBookNow}
                      sx={{
                        bgcolor: "#3554d1",
                        textTransform: "none",
                        borderRadius: 2,
                        fontWeight: 700,
                        py: 1.4,
                        opacity: isSubmitting ? 0.7 : 1,
                        "&:hover": { bgcolor: "#2a43b0" },
                      }}
                    >
                      {isSubmitting ? (
                        <Stack direction="row" spacing={1} alignItems="center">
                          <CircularProgress size={18} color="inherit" />
                          <span>Booking…</span>
                        </Stack>
                      ) : (
                        "Book Now"
                      )}
                    </Button>
                  )}
                  {showEnquiry && (
                    <Button
                      fullWidth
                      variant="contained"
                      disabled={isEnquiring}
                      onClick={handleEnquirySubmit}
                      sx={{
                        bgcolor: "#3554d1",
                        textTransform: "none",
                        borderRadius: 2,
                        fontWeight: 700,
                        py: 1.4,
                        opacity: isEnquiring ? 0.7 : 1,
                        "&:hover": { bgcolor: "#2a43b0" },
                      }}
                    >
                      {isEnquiring ? (
                        <Stack direction="row" spacing={1} alignItems="center">
                          <CircularProgress size={18} color="inherit" />
                          <span>Enquiring…</span>
                        </Stack>
                      ) : (
                        "Make an Enquiry"
                      )}
                    </Button>
                  )}
                </Stack>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </Box>

      {/* Mobile sticky CTA */}
      <Box
        sx={{
          display: { xs: "block", md: "none" },
          position: "fixed",
          left: 0,
          right: 0,
          bottom: 0,
          p: 2,
          bgcolor: "rgba(255,255,255,0.96)",
          borderTop: "1px solid #e8ecf4",
          zIndex: 20,
        }}
      >
        <Stack spacing={1} direction="row">
          {showBookNow && (
            <Button
              fullWidth
              variant="contained"
              disabled={isSubmitting}
              onClick={handleBookNow}
              sx={{
                bgcolor: "#3554d1",
                textTransform: "none",
                borderRadius: 2,
                fontWeight: 700,
                py: 1.4,
                opacity: isSubmitting ? 0.7 : 1,
              }}
            >
              {isSubmitting ? "Booking…" : "Book Now"}
            </Button>
          )}
          {showEnquiry && (
            <Button
              fullWidth
              variant="contained"
              disabled={isEnquiring}
              onClick={handleEnquirySubmit}
              sx={{
                bgcolor: "#3554d1",
                textTransform: "none",
                borderRadius: 2,
                fontWeight: 700,
                py: 1.4,
                opacity: isEnquiring ? 0.7 : 1,
              }}
            >
              {isEnquiring ? "Enquiring…" : "Make an Enquiry"}
            </Button>
          )}
        </Stack>
      </Box>

      <ToastContainer />
    </Box>
  );
};

export default CartCheckout;
