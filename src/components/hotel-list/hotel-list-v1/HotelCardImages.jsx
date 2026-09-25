

import React, { useState, useRef } from "react";
import {
  Box,
  CardMedia,
  Dialog,
  DialogTitle,
  DialogContent,
  Tabs,
  Tab,
  Grid,
  IconButton,
  Button,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import Slider from "react-slick"; // Importing slider library
import "slick-carousel/slick/slick.css"; // Import slider styles
import "slick-carousel/slick/slick-theme.css";
// import "../hotelComponentsCSS/HotelCardImages.css";
import ArrowBackIosIcon from "@mui/icons-material/ArrowBackIos";
import ArrowForwardIosIcon from "@mui/icons-material/ArrowForwardIos";

export default function HotelCardImages({ images }) {
  const [openModal, setOpenModal] = useState(false);
  const [imageModal, setImageModal] = useState({ open: false, src: "" });
  const [activeTab, setActiveTab] = useState(0); // 0: All, 1: Rooms, 2: Property View, 3: Dining, 4: Other, 5: Slider
  const sliderRef = useRef(null); // Create a reference to the slider

  const thumbnails = images?.map((image) => image) || [];

  const handleOpenModal = () => setOpenModal(true);
  const handleCloseModal = () => setOpenModal(false);
  const handleTabChange = (event, newValue) => setActiveTab(newValue);

  const handleImageClick = (src) => {
    setImageModal({ open: true, src });
  };

  const handleCloseImageModal = () => {
    setImageModal({ open: false, src: "" });
  };

  const getImagesByTab = () => {
    switch (activeTab) {
      case 0:
        return thumbnails; // All images
      case 1:
        return thumbnails.filter((img) => img.category === "rooms"); // Filter for rooms
      case 2:
        return thumbnails.filter((img) => img.category === "property"); // Filter for property view
      case 3:
        return thumbnails.filter((img) => img.category === "dining"); // Filter for dining
      case 4:
        return thumbnails.filter((img) => img.category === "other"); // Filter for other
      default:
        return thumbnails;
    }
  };

  // Slider settings
  const sliderSettings = {
    dots: false, // Disable default dots
    infinite: true, // Infinite scrolling (it will loop back to the first image after reaching the last one)
    speed: 500,
    slidesToShow: 1,
    slidesToScroll: 1,
    adaptiveHeight: true,
    pauseOnHover: true, // Pauses the autoplay when the user hovers over the slider
    autoplay: true, // Enables automatic sliding
    autoplaySpeed: 2000, // Time in milliseconds before the next slide (3 seconds in this case)
  };

  // Handle "Next" button click
  const handleNext = () => {
    if (sliderRef.current) {
      sliderRef.current.slickNext(); // Trigger next slide
    }
  };

  // Handle "Previous" button click
  const handlePrev = () => {
    if (sliderRef.current) {
      sliderRef.current.slickPrev(); // Trigger previous slide
    }
  };

  return (
    <Box
      sx={{
        padding: 2,
        border: "1px solid #e0e0e0",
        borderRadius: "8px",
        boxShadow: "0 2px 5px rgba(0, 0, 0, 0.1)",
      }}
    >
      <Box
        sx={{
          display: "flex",
          flexWrap: "wrap",
          gap: 1,
        }}
      >
        {thumbnails.slice(0, 4).map((src, index) => (
          <Box
            key={index}
            sx={{
              position: "relative",
              flex: "1 1 calc(25% - 8px)",
              maxWidth: "calc(25% - 8px)",
            }}
          >
            <CardMedia
              component="img"
              image={src}
              alt={`hotel_image_${index + 1}`}
              onClick={handleOpenModal}
              sx={{
                width: "100%",
                height: 50,
                borderRadius: "8px",
                cursor: "pointer",
                transition: "opacity 0.3s ease",
                "&:hover + .view-all-overlay": {
                  opacity: 1,
                },
              }}
            />
            <Box
              className="view-all-overlay"
              sx={{
                position: "absolute",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                backgroundColor: "rgba(0, 0, 0, 0.5)",
                opacity: 0,
                transition: "opacity 0.3s ease",
                borderRadius: "8px",
                "&:hover": {
                  opacity: 1,
                },
              }}
            >
              <a
                href="#"
                style={{
                  color: "#fff",
                  textDecoration: "none",
                  fontWeight: "bold",
                }}
              >
                View All
              </a>
            </Box>
          </Box>
        ))}
      </Box>
    

      {/* Modal for Image Display */}
      <Dialog open={openModal} onClose={handleCloseModal} fullWidth maxWidth="md">
        <DialogTitle
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
          }}
        >
          Images
          <IconButton onClick={handleCloseModal}>
            <CloseIcon />
          </IconButton>
        </DialogTitle>
        <DialogContent>
          <Tabs
            value={activeTab}
            onChange={handleTabChange}
            variant="scrollable"
            scrollButtons="auto"
          >
            <Tab label="All" />
            <Tab label="Rooms" />
            <Tab label="Property View" />
            <Tab label="Dining" />
            <Tab label="Other" />
            <Tab label="Slider" /> {/* New Tab */}
          </Tabs>
          {/* Image display based on selected tab */}
          {activeTab === 5 ? (
            <Box sx={{ position: "relative" }}>
              <Slider {...sliderSettings} ref={sliderRef}>
                {thumbnails.map((src, index) => (
                  <Box key={index}>
                    <CardMedia
                      component="img"
                      image={src}
                      alt={`slider_image_${index + 1}`}
                      onClick={() => handleImageClick(src)} // Added click handler
                      sx={{
                        width: "100%",
                        maxHeight: "500px",
                        objectFit: "cover",
                      }}
                    />
                  </Box>
                ))}
              </Slider>
              {/* Custom Navigation Buttons in Slider Tab */}
              <Button
                onClick={handlePrev}
                sx={{
                  position: "absolute",
                  top: "50%",
                  left: "20px",
                  transform: "translateY(-50%)",
                  zIndex: 1000,
                  color: "#fff",
                }}
              >
                <ArrowBackIosIcon />
              </Button>

              <Button
                onClick={handleNext}
                sx={{
                  position: "absolute",
                  top: "50%",
                  right: "20px",
                  transform: "translateY(-50%)",
                  zIndex: 1000,
                  color: "#fff",
                }}
              >
                <ArrowForwardIosIcon />
              </Button>
            </Box>
          ) : (
            <Grid container spacing={2}>
              {getImagesByTab().map((src, index) => (
                <Grid item xs={4} key={index}>
                  <CardMedia
                    component="img"
                    image={src}
                    alt={`hotel_modal_image_${index + 1}`}
                    onClick={() => handleImageClick(src)} // Added click handler
                    sx={{
                      width: "100%",
                      height: "200px", // Ensures the height is the same for all images
                      objectFit: "cover", // Keeps the aspect ratio intact
                      cursor: "pointer",
                    }}
                  />
                </Grid>
              ))}
            </Grid>
          )}
        </DialogContent>
      </Dialog>

      {/* Modal for Individual Image */}
      <Dialog
        open={imageModal.open}
        onClose={handleCloseImageModal}
        maxWidth="xl" // Allows the modal to expand fully
        fullWidth
      >
        <DialogContent
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            bgcolor: "#f4f4f4", // Light background for focus
            padding: "16px",
          }}
        >
          <IconButton
            onClick={handleCloseImageModal}
            sx={{
              position: "absolute",
              top: 8,
              right: 8,
              bgcolor: "#fff",
              "&:hover": {
                bgcolor: "#f0f0f0",
              },
            }}
          >
            <CloseIcon />
          </IconButton>
          <CardMedia
            component="img"
            image={imageModal.src}
            alt="Selected Image"
            sx={{
              width: "100%",
              maxHeight: "90vh", // Ensures the image doesn't overflow vertically
              objectFit: "contain", // Maintains aspect ratio
              borderRadius: "8px", // Slightly rounded corners for better aesthetics
              boxShadow: "0px 4px 10px rgba(0, 0, 0, 0.2)", // Subtle shadow
            }}
          />
        </DialogContent>
      </Dialog>
    </Box>
  );
}


