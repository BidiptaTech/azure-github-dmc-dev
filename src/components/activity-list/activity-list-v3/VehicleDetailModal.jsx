import React, { useState, useEffect } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  IconButton,
  Box,
  Skeleton,
  CardMedia,
  Typography,
  CircularProgress,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import activityData from "@/data/activity";
import SidebarRight2 from "@/components/activity-single/SidebarRight2";
import Overview2 from "@/components/activity-single/Overview2";

const VehicleDetailModal = ({ open, onClose, vehicles, loading = false }) => {
  const [imageLoading, setImageLoading] = useState(true);
  const activity = activityData[0];

  useEffect(() => {
    if (open) {
      setImageLoading(true);
      const timer = setTimeout(() => setImageLoading(false), 600);
      return () => clearTimeout(timer);
    }
  }, [open, vehicles?.id, vehicles?.image]);

  return (
    <Dialog
      open={open}
      onClose={onClose}
      maxWidth="md"
      fullWidth
      scroll="paper"
      PaperProps={{
        sx: {
          borderRadius: 2,
          maxHeight: "90vh",
        },
      }}
    >
      <DialogTitle
        sx={{
          px: 2.5,
          py: 1.5,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          borderBottom: "1px solid",
          borderColor: "divider",
        }}
      >
        <Typography variant="subtitle1" sx={{ fontWeight: 600, pr: 2 }}>
          {vehicles?.vehicle_name || "Vehicle Details"}
        </Typography>
        <IconButton onClick={onClose} size="small" aria-label="Close">
          <CloseIcon />
        </IconButton>
      </DialogTitle>

      <DialogContent sx={{ p: 0 }}>
        {loading || !vehicles ? (
          <Box
            sx={{
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
              minHeight: 320,
            }}
          >
            <CircularProgress size={36} />
          </Box>
        ) : (
          <Box sx={{ p: { xs: 2, sm: 2.5 } }}>
            <Box
              sx={{
                display: "grid",
                gridTemplateColumns: { xs: "1fr", md: "1.1fr 0.9fr" },
                gap: 2,
                alignItems: "start",
              }}
            >
              <Box
                sx={{
                  borderRadius: 2,
                  overflow: "hidden",
                  boxShadow: 1,
                  bgcolor: "grey.100",
                  minHeight: 220,
                }}
              >
                {imageLoading || loading || !vehicles?.image ? (
                  <Skeleton
                    variant="rectangular"
                    width="100%"
                    height={240}
                    animation="wave"
                  />
                ) : (
                  <CardMedia
                    component="img"
                    image={vehicles.image}
                    alt={vehicles.vehicle_name}
                    sx={{
                      height: 240,
                      width: "100%",
                      objectFit: "cover",
                      display: "block",
                    }}
                  />
                )}
              </Box>

              <Box
                className="vehicle-detail-modal-sidebar"
                sx={{
                  "& .js-pin-content": {
                    justifyContent: "stretch !important",
                    width: "100%",
                  },
                  "& .w-360": {
                    width: "100% !important",
                    maxWidth: "100%",
                  },
                  "& .px-30": {
                    paddingLeft: "16px !important",
                    paddingRight: "16px !important",
                  },
                  "& .py-30": {
                    paddingTop: "16px !important",
                    paddingBottom: "16px !important",
                  },
                  "& .pt-30": {
                    paddingTop: "12px !important",
                  },
                  "& .shadow-4": {
                    boxShadow: "none !important",
                  },
                }}
              >
                <SidebarRight2 activity={activity} />
              </Box>
            </Box>

            <Box
              sx={{
                mt: 2.5,
                pt: 2,
                borderTop: "1px solid",
                borderColor: "divider",
              }}
            >
              <Typography
                component="h2"
                sx={{ fontSize: 20, fontWeight: 600, lineHeight: 1.3 }}
              >
                {vehicles?.vehicle_name}
              </Typography>

              <Box
                sx={{
                  display: "flex",
                  flexWrap: "wrap",
                  alignItems: "center",
                  gap: 2,
                  mt: 1,
                }}
              >
                <Box sx={{ display: "flex", alignItems: "center" }}>
                  <i className="icon-star text-10 text-yellow-1" />
                  <Typography
                    variant="body2"
                    sx={{ ml: 1, color: "text.secondary" }}
                  >
                    <Box
                      component="span"
                      sx={{ fontWeight: 500, color: "text.primary" }}
                    >
                      {vehicles?.ratings}
                    </Box>{" "}
                    {vehicles?.numberOffReviews} reviews
                  </Typography>
                </Box>

                <Box sx={{ display: "flex", alignItems: "center", gap: 0.75 }}>
                  <i className="icon-location-2 text-16 text-light-1" />
                  <Typography variant="body2" color="text.secondary">
                    {vehicles?.location}
                  </Typography>
                </Box>
              </Box>

              <Box sx={{ mt: 2 }}>
                <Overview2 vehicles={vehicles} compact />
              </Box>
            </Box>
          </Box>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default VehicleDetailModal;
