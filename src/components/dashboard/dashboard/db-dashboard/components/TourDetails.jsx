import React, { useState, useCallback, useMemo } from "react";
import Accordion from "@mui/material/Accordion";
import AccordionSummary from "@mui/material/AccordionSummary";
import AccordionDetails from "@mui/material/AccordionDetails";
import Typography from "@mui/material/Typography";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import HotelIcon from "@mui/icons-material/Hotel";
import LocalShippingIcon from "@mui/icons-material/LocalShipping";
import AttractionsIcon from "@mui/icons-material/Attractions";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import TourIcon from "@mui/icons-material/Tour";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import Box from "@mui/material/Box";
import { styled } from "@mui/material/styles";
import { motion } from "framer-motion";
import HotelBookingsTable from "./HotelBookingsTable"; // Import the HotelBookingsTable component
import AttractionBookingsTable from "./AttractionBookingsTable";
import RestaurantsBookingsTable from "./RestaurantsBookingsTable";
import PickUpDrop from "./PickUpDrop";
import LocalTransfer from "./LocalTransfer";
import TourGuide from "./TourGuide";

// Styled components for customized Accordion
const StyledAccordion = styled(Accordion)(
  ({ theme, expanded, categorycolor }) => ({
    margin: "10px 0 !important",
    borderRadius: "8px !important",
    overflow: "hidden",
    boxShadow: expanded
      ? `0 8px 16px rgba(${categorycolor}, 0.2)`
      : "0 2px 6px rgba(0, 0, 0, 0.05)",
    transition: "all 0.3s ease-in-out",
    border: expanded
      ? `1px solid rgba(${categorycolor}, 0.3)`
      : "1px solid rgba(0, 0, 0, 0.05)",
    "&:hover": {
      boxShadow: `0 6px 18px rgba(${categorycolor}, 0.25)`,
      transform: expanded ? "none" : "translateY(-2px)",
    },
    "&:before": {
      display: "none",
    },
  })
);

const StyledAccordionSummary = styled(AccordionSummary)(
  ({ theme, categorycolor }) => ({
    padding: "0 16px",
    minHeight: "56px !important",
    transition: "background-color 0.3s ease",
    background: `linear-gradient(135deg, rgba(${categorycolor}, 0.8) 0%, rgba(${categorycolor}, 0.65) 100%)`,
    "& .MuiAccordionSummary-content": {
      margin: "8px 0 !important",
      alignItems: "center",
      flexGrow: 0.95,
    },
    "& .MuiAccordionSummary-expandIconWrapper": {
      position: "absolute",
      right: "12px",
    },
  })
);

const IconWrapper = styled(Box)(({ theme }) => ({
  display: "flex",
  alignItems: "center",
  justifyContent: "center",
  backgroundColor: "rgba(255, 255, 255, 0.2)",
  borderRadius: "50%",
  padding: "6px",
  marginRight: "12px",
  width: "30px",
  height: "30px",
  transition: "transform 0.3s ease, background-color 0.3s ease",
  "&:hover": {
    transform: "rotate(10deg)",
    backgroundColor: "rgba(255, 255, 255, 0.3)",
  },
}));

const StyledAccordionDetails = styled(AccordionDetails)(
  ({ theme, categorycolor }) => ({
    padding: "16px",
    backgroundColor: "white",
    position: "relative",
    overflow: "hidden",
    "&:before": {
      content: '""',
      position: "absolute",
      top: 0,
      left: 0,
      width: "100%",
      height: "3px",
      background: `linear-gradient(90deg, rgba(${categorycolor}, 0.7) 0%, rgba(${categorycolor}, 0.2) 100%)`,
    },
  })
);

const CategoryBadge = styled(Box)(({ theme, categorycolor }) => ({
  position: "absolute",
  right: "48px",
  top: "50%",
  transform: "translateY(-50%)",
  backgroundColor: "rgba(255, 255, 255, 0.3)",
  borderRadius: "12px",
  padding: "2px 8px",
  fontSize: "10px",
  fontWeight: "bold",
  color: "white",
  letterSpacing: "0.5px",
  textTransform: "uppercase",
  boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
}));

const CategoryCountBadge = styled(Box)(({ theme, categorycolor }) => ({
  backgroundColor: "rgba(255, 255, 255, 0.2)",
  borderRadius: "50px",
  padding: "2px 6px",
  fontSize: "12px",
  fontWeight: "bold",
  color: "white",
  display: "inline-flex",
  alignItems: "center",
  justifyContent: "center",
  minWidth: "24px",
  height: "24px",
  boxShadow: "0 2px 4px rgba(0,0,0,0.1)",
  marginLeft: "4px",
  marginTop: "-12px",
  verticalAlign: "middle",
}));

export default function CategoriesAccordion() {
  const [expanded, setExpanded] = useState(false);
  const [counts, setCounts] = useState({
    hotels: 0,
    pickupDrop: 0,
    attractions: 0,
    localTransfer: 0,
    tourGuide: 0,
    restaurant: 0,
  });

  // Memoize the updateCounts function
  const updateCounts = useCallback((category, count) => {
    setCounts((prev) => {
      // Only update if the count has actually changed
      if (prev[category] === count) return prev;
      return {
        ...prev,
        [category]: count,
      };
    });
  }, []);

  const handleChange = useCallback(
    (panel) => (event, isExpanded) => {
      setExpanded(isExpanded ? panel : false);
    },
    []
  );

  // Memoize the categories array
  const categories = useMemo(
    () => [
      {
        name: "Hotels",
        content: (
          <HotelBookingsTable
            onCountChange={(count) => updateCounts("hotels", count)}
          />
        ),
        icon: <HotelIcon sx={{ fontSize: "22px", color: "white" }} />,
        color: "41, 98, 255", // Vibrant royal blue
        badge: "Accommodations",
        countKey: "hotels",
      },
      {
        name: "Pickup/Drop",
        content: (
          <PickUpDrop
            onCountChange={(count) => updateCounts("pickupDrop", count)}
          />
        ),
        icon: <LocalShippingIcon sx={{ fontSize: "22px", color: "white" }} />,
        color: "255, 122, 0", // Bright orange
        badge: "Transfers",
        countKey: "pickupDrop",
      },
      {
        name: "Attractions & Experiences",
        content: (
          <AttractionBookingsTable
            onCountChange={(count) => updateCounts("attractions", count)}
          />
        ),
        icon: <AttractionsIcon sx={{ fontSize: "22px", color: "white" }} />,
        color: "0, 171, 142", // Turquoise
        badge: "Activities",
        countKey: "attractions",
      },
      {
        name: "Local Transfer",
        content: (
          <LocalTransfer
            onCountChange={(count) => updateCounts("localTransfer", count)}
          />
        ),
        icon: <DirectionsCarIcon sx={{ fontSize: "22px", color: "white" }} />,
        color: "143, 45, 235", // Vibrant purple
        badge: "Transport",
        countKey: "localTransfer",
      },
      {
        name: "Tour Guide",
        content: (
          <TourGuide
            onCountChange={(count) => updateCounts("tourGuide", count)}
          />
        ),
        icon: <TourIcon sx={{ fontSize: "22px", color: "white" }} />,
        color: "0, 159, 253", // Sky blue
        badge: "Guides",
        countKey: "tourGuide",
      },
      {
        name: "Restaurant",
        content: (
          <RestaurantsBookingsTable
            onCountChange={(count) => updateCounts("restaurant", count)}
          />
        ),
        icon: <RestaurantIcon sx={{ fontSize: "22px", color: "white" }} />,
        color: "244, 67, 54", // Material design red
        badge: "Dining",
        countKey: "restaurant",
      },
    ],
    [updateCounts]
  );

  return (
    <div
      style={{
        width: "95%",
        margin: "20px auto",
        position: "relative",
        zIndex: 1,
      }}
    >
      {/* Background decorative elements - make smaller */}
      <div
        style={{
          position: "absolute",
          top: "10%",
          right: "5%",
          width: "150px",
          height: "150px",
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(25, 118, 210, 0.05) 0%, rgba(25, 118, 210, 0) 70%)",
          zIndex: -1,
        }}
      />
      <div
        style={{
          position: "absolute",
          bottom: "15%",
          left: "5%",
          width: "180px",
          height: "180px",
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(76, 175, 80, 0.05) 0%, rgba(76, 175, 80, 0) 70%)",
          zIndex: -1,
        }}
      />

      <Typography
        variant="h5"
        component="h2"
        sx={{
          textAlign: "center",
          marginBottom: "20px",
          fontWeight: 600,
          backgroundImage: "linear-gradient(45deg, #1976d2, #4dabf5)",
          backgroundClip: "text",
          textFillColor: "transparent",
          WebkitBackgroundClip: "text",
          WebkitTextFillColor: "transparent",
          position: "relative",
          "&:after": {
            content: '""',
            position: "absolute",
            width: "40px",
            height: "3px",
            background: "linear-gradient(45deg, #1976d2, #4dabf5)",
            bottom: "-8px",
            left: "50%",
            transform: "translateX(-50%)",
            borderRadius: "8px",
          },
        }}
      >
        Tour Services
      </Typography>

      {categories.map((category, index) => (
        <motion.div
          key={index}
          initial={{ opacity: 0, y: 15 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: index * 0.08, duration: 0.4 }}
        >
          <StyledAccordion
            expanded={expanded === `panel${index}`}
            onChange={handleChange(`panel${index}`)}
            categorycolor={category.color}
          >
            <StyledAccordionSummary
              expandIcon={
                <ExpandMoreIcon sx={{ color: "white", fontSize: "22px" }} />
              }
              aria-controls={`panel${index}-content`}
              id={`panel${index}-header`}
              categorycolor={category.color}
            >
              <IconWrapper>
                {React.cloneElement(category.icon, {
                  sx: { fontSize: "18px", color: "white" },
                })}
              </IconWrapper>
              <Box
                sx={{
                  display: "flex",
                  alignItems: "center",
                  position: "relative",
                  flexGrow: 1,
                  ml: 2,
                }}
              >
                <Box sx={{ display: "flex", alignItems: "center" }}>
                  <Typography
                    variant="subtitle1"
                    sx={{
                      color: "white",
                      fontWeight: "600",
                      letterSpacing: "0.5px",
                      textShadow: "0 1px 2px rgba(0,0,0,0.2)",
                      fontSize: "1rem",
                    }}
                  >
                    {category.name}
                  </Typography>
                  <CategoryCountBadge categorycolor={category.color}>
                    {counts[category.countKey]}
                  </CategoryCountBadge>
                </Box>
              </Box>
              <CategoryBadge categorycolor={category.color}>
                {category.badge}
              </CategoryBadge>
            </StyledAccordionSummary>
            <StyledAccordionDetails categorycolor={category.color}>
              {category.content}
            </StyledAccordionDetails>
          </StyledAccordion>
        </motion.div>
      ))}
    </div>
  );
}
