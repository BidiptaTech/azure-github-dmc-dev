import React, { useMemo } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  IconButton,
  Box,
  Typography,
  CircularProgress,
  Rating,
  Button,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import { useDispatch, useSelector } from "react-redux";
import moment from "moment";
import { toast } from "react-toastify";
import AvailableRooms from "@/components/hotel-single/AvailableRooms";
import {
  setBookingArray,
  setTotalPrice,
} from "@/slice/hotel/HotelDetailsSlice";
import { addHotelBookingToCart } from "@/utils/addHotelToCart";

function formatStaySummary({ location, checkIn, checkOut, rooms = 1 }) {
  const parts = [];
  if (location) parts.push(location);
  const a = moment(checkIn, ["DD/MM/YYYY", "YYYY-MM-DD"]);
  const b = moment(checkOut, ["DD/MM/YYYY", "YYYY-MM-DD"]);
  if (a.isValid() && b.isValid()) {
    const nights = Math.max(0, b.diff(a, "days"));
    parts.push(`${nights} night${nights === 1 ? "" : "s"}`);
  }
  parts.push(`${rooms} room(s)`);
  return parts.join(" · ");
}

const HotelRoomSelectModal = ({
  open,
  onClose,
  hotel,
  loading = false,
  priceMode = null,
  priceModeId = null,
}) => {
  const dispatch = useDispatch();
  const roomsStatus = useSelector((state) => state.rooms.status);
  const roomsId = useSelector((state) => state.rooms.id);
  const tourdetails = useSelector((state) => state.hotels?.tourdetails);
  const searchState = useSelector((state) => state.hotels?.searchState);
  const bookingArray = useSelector((state) => state.hoteldetails.bookingArray);
  const totalPrice = useSelector((state) => state.hoteldetails.totalPrice);
  const hotelDetails = useSelector((state) => state.hoteldetails.bookingDetails);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const categoryPriceMode = useSelector((state) => state.category.priceMode);

  const resolvedPriceMode =
    priceMode ||
    (typeof categoryPriceMode === "string"
      ? categoryPriceMode
      : Array.isArray(categoryPriceMode)
        ? categoryPriceMode[0]
        : categoryPriceMode);

  const starRating = useMemo(
    () => parseFloat((hotel?.category || "").replace(/[^\d.]/g, "")) || 0,
    [hotel?.category]
  );

  const displayName = hotel?.hotel_name || hotel?.name || "Select room";
  const displayLocation = hotel?.location || "";

  const staySummary = useMemo(() => {
    const checkIn = tourdetails?.CheckInTime || searchState?.ucheckIn;
    const checkOut = tourdetails?.CheckOutTime || searchState?.ucheckOut;
    const shortLocation =
      displayLocation?.split(",")[0]?.trim() || displayLocation || "";
    return formatStaySummary({
      location: shortLocation,
      checkIn,
      checkOut,
      rooms: 1,
    });
  }, [displayLocation, tourdetails, searchState]);

  const isMatchingHotel =
    !!hotel && roomsId != null && String(roomsId) === String(hotel.id);

  const isLoading =
    loading ||
    roomsStatus === "loading" ||
    !hotel ||
    (!isMatchingHotel && roomsStatus !== "failed");

  const convertedPrice = Math.ceil((totalPrice || 0) * (exchangeRate || 1));
  const canAdd = Array.isArray(bookingArray) && bookingArray.length > 0;

  const handleClose = () => {
    dispatch(setBookingArray([]));
    dispatch(setTotalPrice(0));
    onClose?.();
  };

  const handleAddToCart = () => {
    const cartError = addHotelBookingToCart(dispatch, {
      bookingArray,
      totalPrice,
      priceMode: resolvedPriceMode,
      hotelDetails,
      tourDetails: tourdetails,
      searchState,
    });
    if (cartError) {
      toast.error(cartError);
      return;
    }
    toast.success("Added to cart successfully.");
    handleClose();
  };

  return (
    <Dialog
      open={open}
      onClose={handleClose}
      maxWidth="sm"
      fullWidth
      scroll="paper"
      PaperProps={{
        sx: {
          borderRadius: 2.5,
          maxHeight: "78vh",
          width: "100%",
          maxWidth: 520,
        },
      }}
    >
      <DialogTitle
        sx={{
          px: 2,
          py: 1.25,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "flex-start",
          gap: 1,
          borderBottom: "1px solid",
          borderColor: "divider",
        }}
      >
        <Box sx={{ minWidth: 0, pr: 1 }}>
          {starRating > 0 && (
            <Rating
              value={starRating}
              readOnly
              precision={0.5}
              size="small"
              sx={{ mb: 0.25, fontSize: 16 }}
            />
          )}
          <Typography
            variant="subtitle1"
            sx={{
              fontWeight: 700,
              lineHeight: 1.25,
              overflow: "hidden",
              textOverflow: "ellipsis",
              whiteSpace: "nowrap",
              fontSize: "1.05rem",
            }}
          >
            {displayName}
          </Typography>
          {staySummary && (
            <Typography
              variant="caption"
              color="text.secondary"
              sx={{ mt: 0.25, display: "block" }}
            >
              {staySummary}
            </Typography>
          )}
        </Box>
        <IconButton onClick={handleClose} size="small" aria-label="Close">
          <CloseIcon fontSize="small" />
        </IconButton>
      </DialogTitle>

      <DialogContent
        dividers
        sx={{
          p: 0,
          bgcolor: "#f7f8fb",
          "&.MuiDialogContent-root": { py: 0 },
        }}
      >
        {isLoading ? (
          <Box
            sx={{
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
              minHeight: 220,
            }}
          >
            <CircularProgress size={32} />
          </Box>
        ) : roomsStatus === "failed" ? (
          <Box sx={{ p: 2.5, textAlign: "center" }}>
            <Typography color="text.secondary" variant="body2">
              Unable to load rooms for this hotel. Please try again.
            </Typography>
          </Box>
        ) : (
          <Box sx={{ p: 1.5 }}>
            <AvailableRooms
              hotel={hotel}
              priceMode={resolvedPriceMode}
              priceModeId={priceModeId}
              inModal
              hideCartActions
            />
          </Box>
        )}
      </DialogContent>

      {!isLoading && roomsStatus !== "failed" && (
        <DialogActions
          sx={{
            px: 2,
            py: 1.25,
            borderTop: "1px solid",
            borderColor: "divider",
            justifyContent: "space-between",
            gap: 1.5,
            bgcolor: "background.paper",
          }}
        >
          <Box sx={{ minWidth: 0 }}>
            <Typography variant="caption" color="text.secondary" display="block">
              Trip total
            </Typography>
            {PriceHide === "0" ? (
              <Typography variant="subtitle1" color="primary" sx={{ fontWeight: 700, lineHeight: 1.2 }}>
                {currencyCode} {convertedPrice}
              </Typography>
            ) : (
              <Typography variant="body2" color="text.secondary">
                On request
              </Typography>
            )}
          </Box>
          <Button
            variant="contained"
            onClick={handleAddToCart}
            disabled={!canAdd}
            sx={{
              textTransform: "none",
              fontWeight: 600,
              px: 2.5,
              borderRadius: 2,
              bgcolor: "#3554D1",
              "&:hover": { bgcolor: "#2a43a8" },
            }}
          >
            Add to Cart
          </Button>
        </DialogActions>
      )}
    </Dialog>
  );
};

export default HotelRoomSelectModal;
