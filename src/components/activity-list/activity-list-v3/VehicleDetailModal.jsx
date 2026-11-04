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

const IMG_HEIGHT = 180;

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
          borderRadius: 1.5,
          maxHeight: "88vh",
        },
      }}
    >
      <DialogTitle
        sx={{
          px: 1.5,
          py: 1,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          gap: 0.5,
          borderBottom: "1px solid",
          borderColor: "divider",
        }}
      >
        <Typography
          variant="subtitle2"
          sx={{
            fontWeight: 600,
            pr: 0.5,
            fontSize: "0.95rem",
            lineHeight: 1.25,
            overflow: "hidden",
            textOverflow: "ellipsis",
            whiteSpace: "nowrap",
          }}
        >
          {vehicles?.vehicle_name || "Vehicle Details"}
        </Typography>
        <IconButton
          onClick={onClose}
          size="small"
          aria-label="Close"
          sx={{ p: 0.5 }}
        >
          <CloseIcon fontSize="small" />
        </IconButton>
      </DialogTitle>

      <DialogContent sx={{ p: 0 }}>
        {loading || !vehicles ? (
          <Box
            sx={{
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
              minHeight: 200,
            }}
          >
            <CircularProgress size={28} />
          </Box>
        ) : (
          <Box sx={{ p: 1.25 }}>
            <Box
              sx={{
                display: "grid",
                gridTemplateColumns: { xs: "1fr", sm: "1fr 1fr" },
                gap: 1.25,
                alignItems: "start",
              }}
            >
              <Box
                sx={{
                  display: "flex",
                  flexDirection: "column",
                  gap: 1.25,
                  minWidth: 0,
                }}
              >
                <Box
                  sx={{
                    borderRadius: 1.5,
                    overflow: "hidden",
                    bgcolor: "grey.100",
                    minHeight: IMG_HEIGHT,
                    flexShrink: 0,
                  }}
                >
                  {imageLoading || loading || !vehicles?.image ? (
                    <Skeleton
                      variant="rectangular"
                      width="100%"
                      height={IMG_HEIGHT}
                      animation="wave"
                    />
                  ) : (
                    <CardMedia
                      component="img"
                      image={vehicles.image}
                      alt={vehicles.vehicle_name}
                      sx={{
                        height: IMG_HEIGHT,
                        width: "100%",
                        objectFit: "cover",
                        display: "block",
                      }}
                    />
                  )}
                </Box>

                <Box>
                  <Typography
                    component="h2"
                    sx={{ fontSize: 16, fontWeight: 600, lineHeight: 1.3 }}
                  >
                    {vehicles?.vehicle_name}
                  </Typography>

                  <Box
                    sx={{
                      display: "flex",
                      flexWrap: "wrap",
                      alignItems: "center",
                      gap: 1.25,
                      mt: 0.5,
                    }}
                  >
                    <Box sx={{ display: "flex", alignItems: "center" }}>
                      <i className="icon-star text-10 text-yellow-1" />
                      <Typography
                        variant="caption"
                        sx={{ ml: 0.75, color: "text.secondary", lineHeight: 1.2 }}
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

                    <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                      <i className="icon-location-2 text-12 text-light-1" />
                      <Typography
                        variant="caption"
                        color="text.secondary"
                        sx={{ lineHeight: 1.2 }}
                      >
                        {vehicles?.location}
                      </Typography>
                    </Box>
                  </Box>

                  <Box sx={{ mt: 1 }}>
                    <Overview2 vehicles={vehicles} compact />
                  </Box>
                </Box>
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
                    paddingLeft: "10px !important",
                    paddingRight: "10px !important",
                  },
                  "& .py-30": {
                    paddingTop: "10px !important",
                    paddingBottom: "10px !important",
                  },
                  "& .pt-30": {
                    paddingTop: "0 !important",
                  },
                  "& .y-gap-20 > [class*='col']": {
                    marginTop: "8px !important",
                  },
                  "& .y-gap-20 > [class*='col']:first-of-type": {
                    marginTop: "0 !important",
                  },
                  "& .shadow-4": {
                    boxShadow: "none !important",
                  },
                  "& .border-light": {
                    borderColor: "rgba(0,0,0,0.08) !important",
                  },
                  "& .searchMenu-date, & .searchMenu-guests": {
                    paddingLeft: "10px !important",
                    paddingRight: "10px !important",
                    paddingTop: "6px !important",
                    paddingBottom: "6px !important",
                  },
                  "& .text-15, & .text-14": {
                    fontSize: "12px !important",
                  },
                  "& .text-16": {
                    fontSize: "13px !important",
                  },
                  "& .mt-15": {
                    marginTop: "8px !important",
                  },
                  "& .py-15": {
                    paddingTop: "8px !important",
                    paddingBottom: "8px !important",
                  },
                  "& .px-20": {
                    paddingLeft: "10px !important",
                    paddingRight: "10px !important",
                  },
                  "& .h-60": {
                    height: "40px !important",
                    minHeight: "40px !important",
                  },
                  "& .button.-dark-1": {
                    paddingTop: "8px !important",
                    paddingBottom: "8px !important",
                    fontSize: "13px !important",
                  },
                }}
              >
                <SidebarRight2 activity={activity} />
              </Box>
            </Box>
          </Box>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default VehicleDetailModal;
