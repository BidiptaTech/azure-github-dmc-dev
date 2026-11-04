import React from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  IconButton,
  Box,
  Typography,
  CircularProgress,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import { useSelector } from "react-redux";
import { Gallery, Item } from "react-photoswipe-gallery";
import { Swiper, SwiperSlide } from "swiper/react";
import { Navigation } from "swiper";
import "photoswipe/dist/photoswipe.css";
import SidebarRight from "@/components/tour-single/SidebarRight";
import Overview from "@/components/tour-single/Overview";
import toursData from "@/data/tours";

const AttractionDetailModal = ({ open, onClose, attraction, loading = false }) => {
  const attractionDetails = useSelector(
    (state) => state.attractions.attractionDetails
  );
  const detailsLoading = useSelector((state) => state.attractions.loading);
  const tour =
    toursData.find((item) => item.id == attraction?.id) || toursData[0];

  const { master_image, additional_images = [] } = attractionDetails || {};
  const images = [...additional_images];
  if (master_image && !images.includes(master_image)) {
    images.unshift(master_image);
  }

  const detailsId =
    attractionDetails?.id ?? attractionDetails?.attraction_id;
  const isMatchingDetails =
    !!attractionDetails &&
    (detailsId == null ||
      attraction?.id == null ||
      String(detailsId) === String(attraction.id));

  const displayName =
    (isMatchingDetails && attractionDetails?.name) ||
    attraction?.attraction_name ||
    attraction?.name ||
    "Attraction Details";
  const displayLocation = isMatchingDetails
    ? [attractionDetails?.location, attractionDetails?.country]
        .filter(Boolean)
        .join(", ")
    : [attraction?.city, attraction?.country].filter(Boolean).join(", ");

  const isLoading =
    loading || detailsLoading || !attractionDetails || !isMatchingDetails;

  return (
    <Dialog
      open={open}
      onClose={onClose}
      maxWidth="lg"
      fullWidth
      scroll="paper"
      PaperProps={{
        sx: {
          borderRadius: 2,
          maxHeight: "92vh",
        },
      }}
    >
      <DialogTitle
        sx={{
          px: 2.5,
          py: 1.5,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "flex-start",
          gap: 1,
          borderBottom: "1px solid",
          borderColor: "divider",
        }}
      >
        <Box sx={{ minWidth: 0, pr: 1 }}>
          <Typography
            variant="subtitle1"
            sx={{
              fontWeight: 600,
              lineHeight: 1.3,
              overflow: "hidden",
              textOverflow: "ellipsis",
              whiteSpace: "nowrap",
            }}
          >
            {displayName}
          </Typography>
          {displayLocation && (
            <Typography
              variant="body2"
              color="text.secondary"
              sx={{
                mt: 0.5,
                display: "flex",
                alignItems: "center",
                gap: 0.5,
              }}
            >
              <i className="icon-placeholder text-14" />
              {displayLocation}
            </Typography>
          )}
        </Box>
        <IconButton onClick={onClose} size="small" aria-label="Close">
          <CloseIcon />
        </IconButton>
      </DialogTitle>

      <DialogContent sx={{ p: 0 }}>
        {isLoading ? (
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
                gridTemplateColumns: { xs: "1fr", md: "1.15fr 0.85fr" },
                gap: 2,
                alignItems: "start",
              }}
            >
              <Box
                sx={{
                  borderRadius: 2,
                  overflow: "hidden",
                  bgcolor: "grey.100",
                  position: "relative",
                  minHeight: 220,
                }}
              >
                {images.length > 0 ? (
                  <Box className="relative d-flex justify-center overflow-hidden js-section-slider">
                    <Swiper
                      modules={[Navigation]}
                      loop={images.length > 1}
                      navigation={{
                        nextEl: ".attraction-modal-img-next",
                        prevEl: ".attraction-modal-img-prev",
                      }}
                    >
                      {images.map((slide, i) => (
                        <SwiperSlide key={i}>
                          <img
                            src={slide}
                            alt={`${displayName} ${i + 1}`}
                            style={{
                              height: 280,
                              width: "100%",
                              objectFit: "cover",
                              display: "block",
                            }}
                          />
                        </SwiperSlide>
                      ))}
                    </Swiper>

                    {images.length > 1 && (
                      <>
                        <button
                          type="button"
                          className="attraction-modal-img-prev section-slider-nav -prev flex-center button -blue-1 bg-white shadow-1 size-40 rounded-full"
                          style={{
                            position: "absolute",
                            left: 8,
                            top: "50%",
                            transform: "translateY(-50%)",
                            zIndex: 2,
                          }}
                        >
                          <i className="icon icon-chevron-left text-12" />
                        </button>
                        <button
                          type="button"
                          className="attraction-modal-img-next section-slider-nav -next flex-center button -blue-1 bg-white shadow-1 size-40 rounded-full"
                          style={{
                            position: "absolute",
                            right: 8,
                            top: "50%",
                            transform: "translateY(-50%)",
                            zIndex: 2,
                          }}
                        >
                          <i className="icon icon-chevron-right text-12" />
                        </button>
                      </>
                    )}

                    <Gallery>
                      <div
                        className="absolute px-10 py-10 col-12 h-full d-flex justify-end items-end z-2 bottom-0 end-0"
                        style={{ pointerEvents: "none" }}
                      >
                        <Item
                          original={images[0]}
                          thumbnail={images[0]}
                          width={900}
                          height={600}
                        >
                          {({ ref, open: openGallery }) => (
                            <div
                              className="button -blue-1 px-16 py-10 bg-white text-dark-1 js-gallery"
                              ref={ref}
                              onClick={openGallery}
                              role="button"
                              style={{ pointerEvents: "auto", fontSize: 13 }}
                            >
                              See All Photos
                            </div>
                          )}
                        </Item>
                      </div>
                    </Gallery>
                  </Box>
                ) : (
                  <Box
                    sx={{
                      height: 280,
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                    }}
                  >
                    <Typography variant="body2" color="text.secondary">
                      No images available
                    </Typography>
                  </Box>
                )}
              </Box>

              <Box
                className="attraction-detail-modal-sidebar"
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
                <SidebarRight tour={tour} />
              </Box>
            </Box>

            <Box
              sx={{
                mt: 2.5,
                pt: 2,
                borderTop: "1px solid",
                borderColor: "divider",
                "& .shadow-md": {
                  boxShadow: "none !important",
                },
                "& .rounded-2xl": {
                  borderRadius: "8px !important",
                },
                "& .p-6": {
                  padding: "16px !important",
                },
                "& .mb-6": {
                  marginBottom: "12px !important",
                },
                "& .text-2xl": {
                  fontSize: "1.15rem !important",
                },
              }}
            >
              <Overview compact />
            </Box>
          </Box>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default AttractionDetailModal;
