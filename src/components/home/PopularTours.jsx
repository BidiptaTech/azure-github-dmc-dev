import React, { useState } from "react";
import {
  Box,
  Container,
  Typography,
  Grid,
  Card,
  CardContent,
  CardMedia,
  IconButton,
  Stack,
} from "@mui/material";
import { LocationOn, Schedule, ArrowBack, ArrowForward } from "@mui/icons-material";

const CORPORATE_NAVY = "#13357b";

const PopularTours = ({ tours: toursProp }) => {
  const [currentIndex, setCurrentIndex] = useState(0);

  const allTours = toursProp ?? [];

  const toursPerPage = 3;
  const totalPages = Math.max(1, Math.ceil(allTours.length / toursPerPage));

  const handlePrevious = () => {
    setCurrentIndex((prev) => (prev === 0 ? totalPages - 1 : prev - 1));
  };

  const handleNext = () => {
    setCurrentIndex((prev) => (prev === totalPages - 1 ? 0 : prev + 1));
  };

  const getCurrentTours = () => {
    const start = currentIndex * toursPerPage;
    return allTours.slice(start, start + toursPerPage);
  };

  const tours = getCurrentTours();

  return (
    <Box sx={{ py: { xs: 8, md: 10 }, bgcolor: "white" }} id="tours">
      <Container maxWidth="lg">
        <Box
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: { xs: "flex-start", sm: "flex-end" },
            flexDirection: { xs: "column", sm: "row" },
            gap: 2,
            mb: 5,
          }}
        >
          <Box>
            <Typography
              sx={{
                color: CORPORATE_NAVY,
                fontWeight: 600,
                fontSize: "0.8rem",
                letterSpacing: "0.12em",
                textTransform: "uppercase",
                mb: 1,
              }}
            >
              Sample Programmes
            </Typography>
            <Typography
              variant="h2"
              sx={{
                fontSize: { xs: "1.75rem", md: "2.25rem" },
                fontWeight: 700,
                color: "#1a2332",
                letterSpacing: "-0.02em",
              }}
            >
              Featured Itineraries
            </Typography>
            <Typography
              sx={{
                color: "#5a6577",
                fontSize: "0.95rem",
                mt: 1,
                maxWidth: 480,
                lineHeight: 1.6,
              }}
            >
              Illustrative packages across our operating markets. Final pricing
              and availability are shared with registered partners.
            </Typography>
          </Box>

          {totalPages > 1 && (
            <Box sx={{ display: "flex", gap: 1 }}>
              <IconButton
                onClick={handlePrevious}
                aria-label="Previous"
                sx={{
                  width: 42,
                  height: 42,
                  border: `1px solid ${CORPORATE_NAVY}`,
                  color: CORPORATE_NAVY,
                  borderRadius: "6px",
                  "&:hover": {
                    bgcolor: CORPORATE_NAVY,
                    color: "white",
                  },
                }}
              >
                <ArrowBack fontSize="small" />
              </IconButton>
              <IconButton
                onClick={handleNext}
                aria-label="Next"
                sx={{
                  width: 42,
                  height: 42,
                  border: `1px solid ${CORPORATE_NAVY}`,
                  color: CORPORATE_NAVY,
                  borderRadius: "6px",
                  "&:hover": {
                    bgcolor: CORPORATE_NAVY,
                    color: "white",
                  },
                }}
              >
                <ArrowForward fontSize="small" />
              </IconButton>
            </Box>
          )}
        </Box>

        <Grid container spacing={3}>
          {tours.map((tour) => (
            <Grid item xs={12} md={6} lg={4} key={tour.id}>
              <Card
                elevation={0}
                sx={{
                  height: "100%",
                  borderRadius: "8px",
                  overflow: "hidden",
                  border: "1px solid #e2e8f0",
                  boxShadow: "none",
                  transition: "box-shadow 0.25s ease, border-color 0.25s ease",
                  "&:hover": {
                    boxShadow: "0 8px 24px rgba(19, 53, 123, 0.08)",
                    borderColor: "#c5d0e0",
                  },
                }}
              >
                <Box sx={{ position: "relative", height: { xs: 180, md: 200 } }}>
                  <CardMedia
                    component="img"
                    image={tour.image}
                    alt={tour.title}
                    sx={{
                      height: "100%",
                      objectFit: "cover",
                    }}
                  />
                  <Box
                    sx={{
                      position: "absolute",
                      inset: 0,
                      background:
                        "linear-gradient(to top, rgba(10,30,70,0.55) 0%, transparent 55%)",
                    }}
                  />
                  <Stack
                    direction="row"
                    spacing={2}
                    sx={{
                      position: "absolute",
                      bottom: 14,
                      left: 16,
                      right: 16,
                    }}
                  >
                    <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                      <LocationOn sx={{ fontSize: 15, color: "white" }} />
                      <Typography
                        sx={{ color: "white", fontSize: "0.8rem", fontWeight: 500 }}
                      >
                        {tour.location}
                      </Typography>
                    </Box>
                    <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                      <Schedule sx={{ fontSize: 15, color: "white" }} />
                      <Typography
                        sx={{ color: "white", fontSize: "0.8rem", fontWeight: 500 }}
                      >
                        {tour.duration}
                      </Typography>
                    </Box>
                  </Stack>
                </Box>

                <CardContent sx={{ p: 2.5 }}>
                  <Typography
                    sx={{
                      color: "#1a2332",
                      fontSize: "1rem",
                      lineHeight: 1.45,
                      fontWeight: 600,
                    }}
                  >
                    {tour.title}
                  </Typography>
                </CardContent>
              </Card>
            </Grid>
          ))}
        </Grid>

        {totalPages > 1 && (
          <Box sx={{ display: "flex", justifyContent: "center", mt: 4, gap: 1 }}>
            {Array.from({ length: totalPages }, (_, index) => (
              <Box
                key={index}
                onClick={() => setCurrentIndex(index)}
                sx={{
                  width: index === currentIndex ? 24 : 8,
                  height: 3,
                  borderRadius: 1,
                  bgcolor: index === currentIndex ? CORPORATE_NAVY : "#cbd5e1",
                  cursor: "pointer",
                  transition: "all 0.25s ease",
                }}
              />
            ))}
          </Box>
        )}
      </Container>
    </Box>
  );
};

export default PopularTours;
