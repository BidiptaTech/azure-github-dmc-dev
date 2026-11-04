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

const IMG_HEIGHT = 180;

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
      maxWidth="md"
      fullWidth
      scroll="paper"
      PaperProps={{
        sx: {
          borderRadius: 1.5,
          maxHeight: "88vh",
          width: "100%",
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
        <Box sx={{ minWidth: 0, pr: 0.5 }}>
          <Typography
            variant="subtitle2"
            sx={{
              fontWeight: 600,
              lineHeight: 1.25,
              fontSize: "0.95rem",
              overflow: "hidden",
              textOverflow: "ellipsis",
              whiteSpace: "nowrap",
            }}
          >
            {displayName}
          </Typography>
          {displayLocation && (
            <Typography
              variant="caption"
              color="text.secondary"
              sx={{
                mt: 0.25,
                display: "flex",
                alignItems: "center",
                gap: 0.5,
                lineHeight: 1.2,
              }}
            >
              <i className="icon-placeholder text-12" />
              {displayLocation}
            </Typography>
          )}
        </Box>
        <IconButton onClick={onClose} size="small" aria-label="Close" sx={{ p: 0.5 }}>
          <CloseIcon fontSize="small" />
        </IconButton>
      </DialogTitle>

      <DialogContent sx={{ p: 0 }}>
        {isLoading ? (
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
              <Box sx={{ display: "flex", flexDirection: "column", gap: 1.25, minWidth: 0 }}>
                <Box
                  sx={{
                    borderRadius: 1.5,
                    overflow: "hidden",
                    bgcolor: "grey.100",
                    position: "relative",
                    height: IMG_HEIGHT,
                    flexShrink: 0,
                  }}
                >
                  {images.length > 0 ? (
                    <Box className="relative d-flex justify-center overflow-hidden js-section-slider" sx={{ height: "100%" }}>
                      <Swiper
                        modules={[Navigation]}
                        loop={images.length > 1}
                        navigation={{
                          nextEl: ".attraction-modal-img-next",
                          prevEl: ".attraction-modal-img-prev",
                        }}
                        style={{ height: "100%", width: "100%" }}
                      >
                        {images.map((slide, i) => (
                          <SwiperSlide key={i}>
                            <img
                              src={slide}
                              alt={`${displayName} ${i + 1}`}
                              style={{
                                height: IMG_HEIGHT,
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
                            className="attraction-modal-img-prev section-slider-nav -prev flex-center button -blue-1 bg-white shadow-1 size-30 rounded-full"
                            style={{
                              position: "absolute",
                              left: 6,
                              top: "50%",
                              transform: "translateY(-50%)",
                              zIndex: 2,
                            }}
                          >
                            <i className="icon icon-chevron-left text-12" />
                          </button>
                          <button
                            type="button"
                            className="attraction-modal-img-next section-slider-nav -next flex-center button -blue-1 bg-white shadow-1 size-30 rounded-full"
                            style={{
                              position: "absolute",
                              right: 6,
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
                          className="absolute px-5 py-5 col-12 h-full d-flex justify-end items-end z-2 bottom-0 end-0"
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
                                className="button -blue-1 bg-white text-dark-1 js-gallery"
                                ref={ref}
                                onClick={openGallery}
                                role="button"
                                style={{
                                  pointerEvents: "auto",
                                  fontSize: 11,
                                  padding: "4px 10px",
                                }}
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
                        height: IMG_HEIGHT,
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                      }}
                    >
                      <Typography variant="caption" color="text.secondary">
                        No images available
                      </Typography>
                    </Box>
                  )}
                </Box>

                <Box
                  sx={{
                    "& .shadow-md": {
                      boxShadow: "none !important",
                    },
                    "& > div": {
                      borderRadius: "6px !important",
                      border: "1px solid",
                      borderColor: "divider",
                    },
                  }}
                >
                  <Overview compact />
                </Box>
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
                <SidebarRight tour={tour} />
              </Box>
            </Box>
          </Box>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default AttractionDetailModal;
