import React, { useMemo } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import {
  Box,
  Card,
  CardContent,
  Typography,
  Stack,
  Chip,
  IconButton,
  Divider,
  Button,
  Avatar,
  Grid,
} from "@mui/material";
import DeleteOutlineIcon from "@mui/icons-material/DeleteOutline";
import ShoppingCartOutlinedIcon from "@mui/icons-material/ShoppingCartOutlined";
import DirectionsCarFilledOutlinedIcon from "@mui/icons-material/DirectionsCarFilledOutlined";
import HotelOutlinedIcon from "@mui/icons-material/HotelOutlined";
import AttractionsOutlinedIcon from "@mui/icons-material/AttractionsOutlined";
import RestaurantOutlinedIcon from "@mui/icons-material/RestaurantOutlined";
import PlaceOutlinedIcon from "@mui/icons-material/PlaceOutlined";
import CalendarMonthOutlinedIcon from "@mui/icons-material/CalendarMonthOutlined";
import AccessTimeOutlinedIcon from "@mui/icons-material/AccessTimeOutlined";
import PeopleOutlineIcon from "@mui/icons-material/PeopleOutline";
import FlightTakeoffOutlinedIcon from "@mui/icons-material/FlightTakeoffOutlined";
import EditOutlinedIcon from "@mui/icons-material/EditOutlined";
import AddCircleOutlineIcon from "@mui/icons-material/AddCircleOutline";
import {
  selectCart,
  removeFromCart,
  clearCart,
  clearCartByTrip,
  setCheckoutTripId,
  MAX_CART_TRIPS,
} from "@/slice/cart/carSlice";
import {
  applyTourSearchToRedux,
  formatDestinationLabel,
} from "@/utils/applyTourSearchToRedux";

const BOOKING_TYPE_LABELS = {
  entryport: "Entry Port",
  exitport: "Exit Port",
  hotel: "Hotel",
  attraction: "Attraction",
  restaurant: "Restaurant",
};

const formatPrice = (value) => {
  const num = Number(value) || 0;
  return num.toLocaleString(undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  });
};

const formatDestination = formatDestinationLabel;

const getPickup = (item) =>
  item.entrypickup || item.exitpickup || item.pickup || "—";

const getDropoff = (item) =>
  item.entrydropoff || item.exitdropoff || item.dropoff || "—";

const getDate = (item) =>
  item.bookingDate || item.pickupdate || item.exitpickupdate || item.check_in || "—";

const getTime = (item) => item.entrytime || item.exittime || "—";

const getHotelStay = (item) => {
  if (item.check_in && item.check_out) {
    return `${item.check_in} → ${item.check_out}`;
  }
  return getDate(item);
};

const getHotelRooms = (item) => {
  if (item.room_summary) return item.room_summary;
  if (item.roomCount) {
    return `${item.roomCount} room${item.roomCount === 1 ? "" : "s"}`;
  }
  const rooms = Array.isArray(item.bookingArray) ? item.bookingArray : [];
  if (!rooms.length) return "—";
  return rooms
    .map((room) => room.room_type)
    .filter(Boolean)
    .join(", ");
};

const CartPage = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const cart = useSelector(selectCart);

  const trips = useMemo(
    () =>
      (Array.isArray(cart) ? cart : []).filter(
        (trip) => Array.isArray(trip.bookings) && trip.bookings.length > 0
      ),
    [cart]
  );

  const totals = useMemo(() => {
    let itemCount = 0;
    let amount = 0;
    trips.forEach((trip) => {
      trip.bookings.forEach((item) => {
        itemCount += 1;
        amount += Number(item.totalPrice) || 0;
      });
    });
    return { itemCount, amount };
  }, [trips]);

  const handleRemove = (tripId, cartItemId) => {
    dispatch(removeFromCart({ tripId, cartItemId }));
  };

  /** Same Redux fields as MainFilterSearchBox handleSearch */
  const restoreTripToRedux = (trip) => {
    applyTourSearchToRedux(dispatch, {
      destination: trip.destination,
      country: trip.country,
      searchLocation: trip.searchLocation,
      cityWiseDates: trip.cityWiseDates,
      check_in: trip.check_in,
      check_out: trip.check_out,
      adult: trip.adult,
      child: trip.child,
      infant: trip.infant,
      adultGenders: trip.adultGenders,
      childrenAges: trip.childrenAges,
      tour_id: trip.tour_id,
    });
  };

  const handleEditTrip = (trip) => {
    restoreTripToRedux(trip);
    const locationLabel = Array.isArray(trip.destination)
      ? trip.destination
          .map((d) => (typeof d === "object" ? d?.city : d))
          .filter(Boolean)
          .join(",")
      : trip.destination || "";
    const searchParams = new URLSearchParams({
      location: locationLabel,
      dates: [trip.check_in, trip.check_out].join(","),
      guests: JSON.stringify({
        Adults: trip.adult || 1,
        Children: trip.child || 0,
        Infants: trip.infant || 0,
      }),
    });
    navigate(`/dashboard/db-dashboard/view-hotel-search/0?${searchParams}`, {
      state: {
        editCartTripId: trip.tripId,
        cartTrip: trip,
      },
    });
  };

  const handleTripCheckout = (trip) => {
    restoreTripToRedux(trip);
    dispatch(setCheckoutTripId(trip.tripId));
    navigate("/dashboard/db-dashboard/CheckOut", {
      state: {
        cartTripId: trip.tripId,
        cartTrip: trip,
        checkoutSource: "cart",
      },
    });
  };

  if (trips.length === 0) {
    return (
      <Box
        sx={{
          minHeight: "70vh",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          px: 2,
          background: "linear-gradient(180deg, #f5f7fb 0%, #ffffff 100%)",
        }}
      >
        <Card
          elevation={0}
          sx={{
            maxWidth: 480,
            width: "100%",
            textAlign: "center",
            borderRadius: 3,
            border: "1px solid #e8ecf4",
            p: 4,
          }}
        >
          <Avatar
            sx={{
              width: 72,
              height: 72,
              mx: "auto",
              mb: 2,
              bgcolor: "#eef2ff",
              color: "#3554d1",
            }}
          >
            <ShoppingCartOutlinedIcon sx={{ fontSize: 36 }} />
          </Avatar>
          <Typography variant="h5" fontWeight={700} color="#0f172a" mb={1}>
            Your cart is empty
          </Typography>
          <Typography color="text.secondary" mb={3}>
            Add hotels, attractions, restaurants, or transfers for a trip. You can keep up to {MAX_CART_TRIPS} trips
            in the cart. Use Edit on a trip to add more products.
          </Typography>
          <Button
            variant="contained"
            onClick={() => navigate("/dashboard/db-dashboard/home_1")}
            sx={{
              bgcolor: "#3554d1",
              textTransform: "none",
              px: 3,
              py: 1.2,
              borderRadius: 2,
              "&:hover": { bgcolor: "#2a43b0" },
            }}
          >
            Start New Trip
          </Button>
        </Card>
      </Box>
    );
  }

  return (
    <Box
      sx={{
        minHeight: "100vh",
        background: "linear-gradient(180deg, #f5f7fb 0%, #ffffff 60%)",
        py: { xs: 3, md: 5 },
        px: { xs: 2, md: 4 },
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
            <Typography variant="h4" fontWeight={800} color="#0f172a">
              Shopping Cart
            </Typography>
            <Typography color="text.secondary" mt={0.5}>
              {totals.itemCount} item{totals.itemCount === 1 ? "" : "s"} across{" "}
              {trips.length}/{MAX_CART_TRIPS} trip
              {trips.length === 1 ? "" : "s"}
            </Typography>
          </Box>
          <Stack direction="row" spacing={1.5}>
            {trips.length < MAX_CART_TRIPS && (
              <Button
                variant="outlined"
                startIcon={<AddCircleOutlineIcon />}
                onClick={() => navigate("/dashboard/db-dashboard/home_1")}
                sx={{ textTransform: "none", borderRadius: 2 }}
              >
                Add New Trip
              </Button>
            )}
            <Button
              variant="outlined"
              color="error"
              onClick={() => dispatch(clearCart())}
              sx={{ textTransform: "none", borderRadius: 2 }}
            >
              Clear Cart
            </Button>
          </Stack>
        </Stack>

        <Grid container spacing={3}>
          <Grid item xs={12} md={8}>
            <Stack spacing={3}>
              {trips.map((trip, tripIndex) => {
                const tripTotal = trip.bookings.reduce(
                  (sum, item) => sum + (Number(item.totalPrice) || 0),
                  0
                );
                const cityWiseDates = Array.isArray(trip.cityWiseDates)
                  ? trip.cityWiseDates
                  : [];

                return (
                  <Card
                    key={trip.tripId || tripIndex}
                    elevation={0}
                    sx={{
                      borderRadius: 3,
                      border: "1px solid #e8ecf4",
                      overflow: "hidden",
                    }}
                  >
                    <Box
                      sx={{
                        px: 2.5,
                        py: 1.75,
                        background:
                          "linear-gradient(90deg, #3554d1 0%, #4c6fff 100%)",
                        color: "#fff",
                      }}
                    >
                      <Stack
                        direction={{ xs: "column", sm: "row" }}
                        justifyContent="space-between"
                        alignItems={{ xs: "flex-start", sm: "center" }}
                        spacing={1.5}
                      >
                        <Stack direction="row" spacing={1.5} alignItems="flex-start">
                          <FlightTakeoffOutlinedIcon sx={{ mt: 0.3 }} />
                          <Box>
                            <Typography fontWeight={700}>
                              Trip {tripIndex + 1}:{" "}
                              {formatDestination(trip.destination)}
                            </Typography>
                            <Typography variant="body2" sx={{ opacity: 0.95 }}>
                              {trip.check_in || "—"} → {trip.check_out || "—"}
                            </Typography>
                            <Typography variant="caption" sx={{ opacity: 0.9 }}>
                              {trip.adult || 0} adults · {trip.child || 0}{" "}
                              children · {trip.infant || 0} infants ·{" "}
                              {trip.bookings.length} booking
                              {trip.bookings.length === 1 ? "" : "s"}
                            </Typography>
                            {cityWiseDates.length > 0 && (
                              <Stack
                                direction="row"
                                spacing={0.75}
                                flexWrap="wrap"
                                useFlexGap
                                mt={1}
                              >
                                {cityWiseDates.map((cityDate, idx) => (
                                  <Chip
                                    key={`${cityDate.city}-${idx}`}
                                    size="small"
                                    label={`${cityDate.city}: ${cityDate.checkIn} – ${cityDate.checkOut}`}
                                    sx={{
                                      bgcolor: "rgba(255,255,255,0.18)",
                                      color: "#fff",
                                      fontWeight: 600,
                                      fontSize: "0.7rem",
                                    }}
                                  />
                                ))}
                              </Stack>
                            )}
                          </Box>
                        </Stack>
                        <Stack
                          direction="row"
                          spacing={1}
                          alignItems="center"
                          flexWrap="wrap"
                          useFlexGap
                        >
                          <Chip
                            label={formatPrice(tripTotal)}
                            sx={{
                              bgcolor: "rgba(255,255,255,0.2)",
                              color: "#fff",
                              fontWeight: 700,
                            }}
                          />
                          <Button
                            size="small"
                            startIcon={<EditOutlinedIcon />}
                            onClick={() => handleEditTrip(trip)}
                            sx={{
                              color: "#fff",
                              textTransform: "none",
                              border: "1px solid rgba(255,255,255,0.45)",
                              "&:hover": {
                                borderColor: "#fff",
                                bgcolor: "rgba(255,255,255,0.12)",
                              },
                            }}
                          >
                            Edit / Add more
                          </Button>
                          <Button
                            size="small"
                            onClick={() =>
                              dispatch(clearCartByTrip(trip.tripId))
                            }
                            sx={{
                              color: "#fff",
                              textTransform: "none",
                              border: "1px solid rgba(255,255,255,0.35)",
                              "&:hover": {
                                borderColor: "#fff",
                                bgcolor: "rgba(255,255,255,0.1)",
                              },
                            }}
                          >
                            Clear trip
                          </Button>
                        </Stack>
                      </Stack>
                    </Box>

                    <CardContent sx={{ p: 0 }}>
                      {trip.bookings.map((item, index) => (
                        <Box key={item.cartItemId}>
                          {index > 0 && <Divider />}
                          <Box sx={{ p: 2.5 }}>
                            <Stack
                              direction={{ xs: "column", sm: "row" }}
                              spacing={2}
                              justifyContent="space-between"
                            >
                              <Stack direction="row" spacing={2} flex={1}>
                                <Avatar
                                  variant="rounded"
                                  src={item.image}
                                  alt={
                                    item.restaurantName ||
                                    item.AttractionName ||
                                    item.hotel_name ||
                                    item.vehicles_name
                                  }
                                  sx={{
                                    width: 72,
                                    height: 72,
                                    bgcolor: "#eef2ff",
                                    borderRadius: 2,
                                  }}
                                >
                                  {item.type === "hotel" ? (
                                    <HotelOutlinedIcon />
                                  ) : item.type === "attraction" ? (
                                    <AttractionsOutlinedIcon />
                                  ) : item.type === "restaurant" ? (
                                    <RestaurantOutlinedIcon />
                                  ) : (
                                    <DirectionsCarFilledOutlinedIcon />
                                  )}
                                </Avatar>
                                <Box flex={1}>
                                  <Stack
                                    direction="row"
                                    spacing={1}
                                    alignItems="center"
                                    flexWrap="wrap"
                                    mb={0.75}
                                  >
                                    <Typography fontWeight={700} color="#0f172a">
                                      {item.type === "hotel"
                                        ? item.hotel_name || "Hotel"
                                        : item.type === "attraction"
                                          ? item.AttractionName || "Attraction"
                                          : item.type === "restaurant"
                                            ? item.restaurantName || "Restaurant"
                                            : item.vehicles_name || "Vehicle"}
                                    </Typography>
                                    <Chip
                                      size="small"
                                      label={
                                        BOOKING_TYPE_LABELS[item.type] ||
                                        item.type ||
                                        "Booking"
                                      }
                                      sx={{
                                        bgcolor: "#eef2ff",
                                        color: "#3554d1",
                                        fontWeight: 600,
                                      }}
                                    />
                                    {(item.pricemode || item.Mode || item.priceMode) && (
                                      <Chip
                                        size="small"
                                        label={
                                          item.pricemode ||
                                          item.Mode ||
                                          item.priceMode
                                        }
                                        variant="outlined"
                                        sx={{ fontWeight: 500 }}
                                      />
                                    )}
                                  </Stack>

                                  {item.type === "hotel" ? (
                                    <Stack spacing={0.75}>
                                      <Stack
                                        direction="row"
                                        spacing={1}
                                        alignItems="flex-start"
                                      >
                                        <PlaceOutlinedIcon
                                          sx={{
                                            fontSize: 18,
                                            color: "#64748b",
                                            mt: "2px",
                                          }}
                                        />
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          {item.address || item.location || "—"}
                                        </Typography>
                                      </Stack>
                                      <Stack
                                        direction="row"
                                        spacing={2}
                                        flexWrap="wrap"
                                      >
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <CalendarMonthOutlinedIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {getHotelStay(item)}
                                            {item.nights
                                              ? ` · ${item.nights} night${
                                                  item.nights === 1 ? "" : "s"
                                                }`
                                              : ""}
                                          </Typography>
                                        </Stack>
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <HotelOutlinedIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {getHotelRooms(item)}
                                          </Typography>
                                        </Stack>
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <PeopleOutlineIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {item.adults || 0} adults,{" "}
                                            {item.children || 0} children
                                          </Typography>
                                        </Stack>
                                      </Stack>
                                    </Stack>
                                  ) : item.type === "attraction" ? (
                                    <Stack spacing={0.75}>
                                      <Stack
                                        direction="row"
                                        spacing={1}
                                        alignItems="flex-start"
                                      >
                                        <PlaceOutlinedIcon
                                          sx={{
                                            fontSize: 18,
                                            color: "#64748b",
                                            mt: "2px",
                                          }}
                                        />
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          {item.address || item.location || "—"}
                                          {item.ticketName
                                            ? ` · ${item.ticketName}`
                                            : ""}
                                        </Typography>
                                      </Stack>
                                      <Stack
                                        direction="row"
                                        spacing={2}
                                        flexWrap="wrap"
                                      >
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <CalendarMonthOutlinedIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {item.bookingDate || getDate(item)}
                                          </Typography>
                                        </Stack>
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <AccessTimeOutlinedIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {item.visitTime || getTime(item)}
                                          </Typography>
                                        </Stack>
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <PeopleOutlineIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {item.adults || 0} adults,{" "}
                                            {item.children || 0} children
                                            {item.seniorCount
                                              ? `, ${item.seniorCount} seniors`
                                              : ""}
                                          </Typography>
                                        </Stack>
                                      </Stack>
                                    </Stack>
                                  ) : item.type === "restaurant" ? (
                                    <Stack spacing={0.75}>
                                      <Stack
                                        direction="row"
                                        spacing={1}
                                        alignItems="flex-start"
                                      >
                                        <PlaceOutlinedIcon
                                          sx={{
                                            fontSize: 18,
                                            color: "#64748b",
                                            mt: "2px",
                                          }}
                                        />
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          {item.address || item.location || "—"}
                                          {item.meal_summary
                                            ? ` · ${item.meal_summary}`
                                            : ""}
                                        </Typography>
                                      </Stack>
                                      <Stack
                                        direction="row"
                                        spacing={2}
                                        flexWrap="wrap"
                                      >
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <CalendarMonthOutlinedIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {item.bookingDate || getDate(item)}
                                          </Typography>
                                        </Stack>
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <AccessTimeOutlinedIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {item.visitTime || getTime(item)}
                                          </Typography>
                                        </Stack>
                                        <Stack
                                          direction="row"
                                          spacing={0.75}
                                          alignItems="center"
                                        >
                                          <PeopleOutlineIcon
                                            sx={{ fontSize: 16, color: "#64748b" }}
                                          />
                                          <Typography
                                            variant="body2"
                                            color="text.secondary"
                                          >
                                            {item.adults || 0} adults,{" "}
                                            {item.children || 0} children
                                          </Typography>
                                        </Stack>
                                      </Stack>
                                    </Stack>
                                  ) : (
                                  <Stack spacing={0.75}>
                                    <Stack
                                      direction="row"
                                      spacing={1}
                                      alignItems="flex-start"
                                    >
                                      <PlaceOutlinedIcon
                                        sx={{
                                          fontSize: 18,
                                          color: "#64748b",
                                          mt: "2px",
                                        }}
                                      />
                                      <Typography
                                        variant="body2"
                                        color="text.secondary"
                                      >
                                        <strong>Pickup:</strong> {getPickup(item)}
                                        <br />
                                        <strong>Drop-off:</strong>{" "}
                                        {getDropoff(item)}
                                      </Typography>
                                    </Stack>
                                    <Stack
                                      direction="row"
                                      spacing={2}
                                      flexWrap="wrap"
                                    >
                                      <Stack
                                        direction="row"
                                        spacing={0.75}
                                        alignItems="center"
                                      >
                                        <CalendarMonthOutlinedIcon
                                          sx={{ fontSize: 16, color: "#64748b" }}
                                        />
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          {getDate(item)}
                                        </Typography>
                                      </Stack>
                                      <Stack
                                        direction="row"
                                        spacing={0.75}
                                        alignItems="center"
                                      >
                                        <AccessTimeOutlinedIcon
                                          sx={{ fontSize: 16, color: "#64748b" }}
                                        />
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          {getTime(item)}
                                        </Typography>
                                      </Stack>
                                      <Stack
                                        direction="row"
                                        spacing={0.75}
                                        alignItems="center"
                                      >
                                        <PeopleOutlineIcon
                                          sx={{ fontSize: 16, color: "#64748b" }}
                                        />
                                        <Typography
                                          variant="body2"
                                          color="text.secondary"
                                        >
                                          {item.adults || 0} adults,{" "}
                                          {item.children || 0} children
                                        </Typography>
                                      </Stack>
                                    </Stack>
                                  </Stack>
                                  )}
                                </Box>
                              </Stack>

                              <Stack
                                alignItems={{ xs: "flex-start", sm: "flex-end" }}
                                justifyContent="space-between"
                                spacing={1}
                                minWidth={120}
                              >
                                <Typography
                                  fontWeight={800}
                                  color="#3554d1"
                                  fontSize={18}
                                >
                                  {formatPrice(item.totalPrice)}
                                </Typography>
                                <IconButton
                                  aria-label="Remove item"
                                  onClick={() =>
                                    handleRemove(trip.tripId, item.cartItemId)
                                  }
                                  sx={{
                                    color: "#ef4444",
                                    bgcolor: "#fef2f2",
                                    "&:hover": { bgcolor: "#fee2e2" },
                                  }}
                                >
                                  <DeleteOutlineIcon />
                                </IconButton>
                              </Stack>
                            </Stack>
                          </Box>
                        </Box>
                      ))}

                      <Divider />
                      <Box
                        sx={{
                          p: 2,
                          bgcolor: "#f8fafc",
                          display: "flex",
                          flexDirection: { xs: "column", sm: "row" },
                          gap: 1.5,
                          justifyContent: "flex-end",
                          alignItems: { xs: "stretch", sm: "center" },
                        }}
                      >
                        <Typography
                          fontWeight={700}
                          color="#0f172a"
                          sx={{ mr: { sm: "auto" } }}
                        >
                          Trip total: {formatPrice(tripTotal)}
                        </Typography>
                        <Button
                          variant="outlined"
                          startIcon={<EditOutlinedIcon />}
                          onClick={() => handleEditTrip(trip)}
                          sx={{ textTransform: "none", borderRadius: 2 }}
                        >
                          Add more products
                        </Button>
                        <Button
                          variant="contained"
                          onClick={() => handleTripCheckout(trip)}
                          sx={{
                            bgcolor: "#3554d1",
                            textTransform: "none",
                            borderRadius: 2,
                            fontWeight: 700,
                            "&:hover": { bgcolor: "#2a43b0" },
                          }}
                        >
                          Proceed to Checkout
                        </Button>
                      </Box>
                    </CardContent>
                  </Card>
                );
              })}
            </Stack>
          </Grid>

          <Grid item xs={12} md={4}>
            <Card
              elevation={0}
              sx={{
                borderRadius: 3,
                border: "1px solid #e8ecf4",
                position: { md: "sticky" },
                top: { md: 100 },
              }}
            >
              <CardContent sx={{ p: 3 }}>
                <Typography variant="h6" fontWeight={700} mb={1}>
                  Order Summary
                </Typography>
                <Typography variant="body2" color="text.secondary" mb={2}>
                  Checkout is tour-wise — choose a trip below to continue.
                </Typography>
                <Stack spacing={2} mb={2}>
                  {trips.map((trip, index) => {
                    const sectionTotal = trip.bookings.reduce(
                      (sum, item) => sum + (Number(item.totalPrice) || 0),
                      0
                    );
                    return (
                      <Box
                        key={trip.tripId || index}
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
                          mb={1}
                        >
                          <Box>
                            <Typography variant="body2" fontWeight={700}>
                              Trip {index + 1} ({trip.bookings.length})
                            </Typography>
                            <Typography variant="caption" color="text.secondary">
                              {formatDestination(trip.destination)}
                            </Typography>
                          </Box>
                          <Typography fontWeight={700} color="#3554d1">
                            {formatPrice(sectionTotal)}
                          </Typography>
                        </Stack>
                        <Button
                          fullWidth
                          size="small"
                          variant="contained"
                          onClick={() => handleTripCheckout(trip)}
                          sx={{
                            bgcolor: "#3554d1",
                            textTransform: "none",
                            borderRadius: 1.5,
                            fontWeight: 700,
                            "&:hover": { bgcolor: "#2a43b0" },
                          }}
                        >
                          Checkout Trip {index + 1}
                        </Button>
                      </Box>
                    );
                  })}
                </Stack>
                <Divider sx={{ my: 2 }} />
                <Stack
                  direction="row"
                  justifyContent="space-between"
                  alignItems="center"
                  mb={2}
                >
                  <Typography fontWeight={700}>Cart total</Typography>
                  <Typography fontWeight={800} color="#3554d1" fontSize={22}>
                    {formatPrice(totals.amount)}
                  </Typography>
                </Stack>
                <Button
                  fullWidth
                  variant="text"
                  onClick={() => navigate("/dashboard/db-dashboard/home_1")}
                  disabled={trips.length >= MAX_CART_TRIPS}
                  sx={{ textTransform: "none", color: "#64748b" }}
                >
                  {trips.length >= MAX_CART_TRIPS
                    ? `Trip limit reached (${MAX_CART_TRIPS})`
                    : "Continue Shopping"}
                </Button>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </Box>
    </Box>
  );
};

export default CartPage;
