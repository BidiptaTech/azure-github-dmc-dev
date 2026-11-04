import React from "react";
import {
  Modal,
  Box,
  Typography,
  IconButton,
  Stack,
  CircularProgress,
  alpha,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import PlaceOutlinedIcon from "@mui/icons-material/PlaceOutlined";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import { useSelector } from "react-redux";
import TourGallery from "@/components/restaurants/filter-box/TourGallery";

const modalSx = {
  position: "absolute",
  top: "50%",
  left: "50%",
  transform: "translate(-50%, -50%)",
  width: { xs: "96%", sm: "94%", md: "92%" },
  maxWidth: 1200,
  maxHeight: "92vh",
  bgcolor: "background.paper",
  borderRadius: 2,
  boxShadow: "0 8px 32px rgba(0, 0, 0, 0.18)",
  display: "flex",
  flexDirection: "column",
  overflow: "hidden",
  outline: "none",
};

export default function RestaurantDetailsModal({ open, onClose, tour }) {
  const restaurantsDetails = useSelector(
    (state) => state.restaurants.selectedRestaurant
  );
  const loading = useSelector((state) => state.restaurants.loading);
  const error = useSelector((state) => state.restaurants.error);

  const name = restaurantsDetails?.name || "Restaurant Details";
  const location = [restaurantsDetails?.city, restaurantsDetails?.country]
    .filter(Boolean)
    .join(", ");

  return (
    <Modal
      open={open}
      onClose={onClose}
      aria-labelledby="restaurant-details-modal-title"
    >
      <Box sx={modalSx}>
        <Box
          sx={{
            background: "linear-gradient(90deg, #1976d2 0%, #0D47A1 100%)",
            color: "white",
            px: { xs: 1.5, sm: 2.5 },
            py: 1.25,
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
            gap: 1.5,
            flexShrink: 0,
          }}
        >
          <Stack direction="row" spacing={1.25} alignItems="center" sx={{ minWidth: 0 }}>
            <Box
              sx={{
                width: 36,
                height: 36,
                borderRadius: "50%",
                bgcolor: "white",
                color: "#1976d2",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                flexShrink: 0,
              }}
            >
              <RestaurantIcon fontSize="small" />
            </Box>
            <Box sx={{ minWidth: 0 }}>
              <Typography
                id="restaurant-details-modal-title"
                variant="h6"
                fontWeight={600}
                fontSize={{ xs: "1rem", sm: "1.15rem" }}
                noWrap
              >
                {name}
              </Typography>
              {location && (
                <Stack direction="row" spacing={0.5} alignItems="center">
                  <PlaceOutlinedIcon sx={{ fontSize: 14, opacity: 0.9 }} />
                  <Typography
                    variant="body2"
                    sx={{ opacity: 0.9, fontSize: "0.8rem" }}
                    noWrap
                  >
                    {location}
                  </Typography>
                </Stack>
              )}
            </Box>
          </Stack>

          <IconButton
            onClick={onClose}
            size="small"
            aria-label="Close restaurant details"
            sx={{
              color: "white",
              bgcolor: "rgba(255,255,255,0.12)",
              flexShrink: 0,
              "&:hover": { bgcolor: "rgba(255,255,255,0.22)" },
            }}
          >
            <CloseIcon fontSize="small" />
          </IconButton>
        </Box>

        <Box
          sx={{
            flex: 1,
            overflowY: "auto",
            bgcolor: "#fafafa",
            "&::-webkit-scrollbar": { width: 6 },
            "&::-webkit-scrollbar-thumb": {
              background: "#bdbdbd",
              borderRadius: 8,
            },
          }}
        >
          {loading && !restaurantsDetails ? (
            <Box
              sx={{
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                minHeight: 280,
                gap: 1.5,
              }}
            >
              <CircularProgress size={28} />
              <Typography color="text.secondary">Loading details…</Typography>
            </Box>
          ) : error && !restaurantsDetails ? (
            <Box sx={{ p: 3, textAlign: "center" }}>
              <Typography color="error">
                Failed to load restaurant details. Please try again.
              </Typography>
            </Box>
          ) : (
            <TourGallery tour={tour} compact />
          )}
        </Box>

        <Box
          sx={{
            px: 2,
            py: 1,
            borderTop: `1px solid ${alpha("#000", 0.08)}`,
            bgcolor: "white",
            flexShrink: 0,
            display: "flex",
            justifyContent: "flex-end",
          }}
        >
          <Typography
            component="button"
            onClick={onClose}
            sx={{
              border: "none",
              background: "transparent",
              color: "#1976d2",
              fontWeight: 600,
              fontSize: "0.875rem",
              cursor: "pointer",
              px: 1.5,
              py: 0.75,
              borderRadius: 1,
              "&:hover": { bgcolor: alpha("#1976d2", 0.08) },
            }}
          >
            Close
          </Typography>
        </Box>
      </Box>
    </Modal>
  );
}
