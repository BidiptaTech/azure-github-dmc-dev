import React, { useState, useEffect } from "react";
import {
  Box,
  Container,
  Typography,
  Button,
  Stack,
  Grid,
  IconButton,
  Fade,
} from "@mui/material";
import {
  ArrowBackIos,
  ArrowForwardIos,
  LocationOn,
  Login as LoginIcon,
} from "@mui/icons-material";
import { useNavigate } from "react-router-dom";

const CORPORATE_NAVY = "#13357b";

const LandingHero = ({ heroSlides: heroSlidesProp }) => {
  const navigate = useNavigate();
  const [currentSlide, setCurrentSlide] = useState(0);
  const [touchStart, setTouchStart] = useState(null);
  const [touchEnd, setTouchEnd] = useState(null);

  const heroSlides = heroSlidesProp ?? [
    {
      id: 1,
      image: "/img/masthead/1/bg.webp",
      title: "Destination Management Partner",
      subtitle:
        "Professional ground services across Singapore, Indonesia, and Australia.",
      location: "Asia Pacific",
    },
  ];

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
    }, 6000);
    return () => clearInterval(interval);
  }, [heroSlides.length]);

  const handlePrevious = () => {
    setCurrentSlide((prev) => (prev - 1 + heroSlides.length) % heroSlides.length);
  };

  const handleNext = () => {
    setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
  };

  const handleTouchStart = (e) => {
    setTouchEnd(null);
    setTouchStart(e.targetTouches[0].clientX);
  };

  const handleTouchMove = (e) => {
    setTouchEnd(e.targetTouches[0].clientX);
  };

  const handleTouchEnd = () => {
    if (!touchStart || !touchEnd) return;
    const distance = touchStart - touchEnd;
    if (distance > 50) handleNext();
    if (distance < -50) handlePrevious();
  };

  const currentSlideData = heroSlides[currentSlide];

  return (
    <Box sx={{ position: "relative", overflow: "hidden" }}>
      <Box
        onTouchStart={handleTouchStart}
        onTouchMove={handleTouchMove}
        onTouchEnd={handleTouchEnd}
        sx={{
          position: "relative",
          height: { xs: "72vh", md: "85vh" },
          minHeight: { xs: 480, md: 560 },
          display: "flex",
          alignItems: "center",
        }}
      >
        <Box
          sx={{
            position: "absolute",
            inset: 0,
            backgroundImage: `url(${currentSlideData.image})`,
            backgroundSize: "cover",
            backgroundPosition: "center",
            transition: "background-image 0.8s ease-in-out",
            "&::before": {
              content: '""',
              position: "absolute",
              inset: 0,
              background:
                "linear-gradient(105deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.35) 45%, rgba(0, 0, 0, 0.25) 100%)",
              zIndex: 1,
            },
          }}
        />

        {heroSlides.length > 1 && (
          <>
            <IconButton
              onClick={handlePrevious}
              aria-label="Previous slide"
              sx={{
                position: "absolute",
                left: { xs: 8, md: 24 },
                top: "50%",
                transform: "translateY(-50%)",
                bgcolor: "rgba(255,255,255,0.12)",
                color: "white",
                border: "1px solid rgba(255,255,255,0.25)",
                width: { xs: 40, md: 48 },
                height: { xs: 40, md: 48 },
                zIndex: 3,
                display: { xs: "none", sm: "flex" },
                borderRadius: "8px",
                "&:hover": { bgcolor: "rgba(255,255,255,0.22)" },
              }}
            >
              <ArrowBackIos sx={{ fontSize: 18, ml: 0.5 }} />
            </IconButton>
            <IconButton
              onClick={handleNext}
              aria-label="Next slide"
              sx={{
                position: "absolute",
                right: { xs: 8, md: 24 },
                top: "50%",
                transform: "translateY(-50%)",
                bgcolor: "rgba(255,255,255,0.12)",
                color: "white",
                border: "1px solid rgba(255,255,255,0.25)",
                width: { xs: 40, md: 48 },
                height: { xs: 40, md: 48 },
                zIndex: 3,
                display: { xs: "none", sm: "flex" },
                borderRadius: "8px",
                "&:hover": { bgcolor: "rgba(255,255,255,0.22)" },
              }}
            >
              <ArrowForwardIos sx={{ fontSize: 18 }} />
            </IconButton>
          </>
        )}

        <Container maxWidth="lg" sx={{ position: "relative", zIndex: 2 }}>
          <Grid container>
            <Grid item xs={12} md={10} lg={8}>
              <Fade in key={currentSlideData.id} timeout={700}>
                <Box>
                  <Typography
                    sx={{
                      color: "rgba(255,255,255,0.75)",
                      fontWeight: 600,
                      fontSize: "0.8rem",
                      letterSpacing: "0.14em",
                      textTransform: "uppercase",
                      mb: 2,
                    }}
                  >
                    TravClicks · B2B Destination Management
                  </Typography>

                  <Typography
                    variant="h1"
                    sx={{
                      fontSize: { xs: "1.85rem", sm: "2.4rem", md: "3.25rem" },
                      fontWeight: 700,
                      color: "white",
                      mb: 2,
                      lineHeight: 1.2,
                      letterSpacing: "-0.02em",
                    }}
                  >
                    {currentSlideData.title}
                  </Typography>

                  <Typography
                    sx={{
                      fontSize: { xs: "1rem", md: "1.15rem" },
                      color: "rgba(255,255,255,0.88)",
                      mb: 4,
                      maxWidth: 560,
                      lineHeight: 1.65,
                      fontWeight: 400,
                    }}
                  >
                    {currentSlideData.subtitle}
                  </Typography>

                  <Stack
                    direction={{ xs: "column", sm: "row" }}
                    spacing={1.5}
                    sx={{ mb: 3 }}
                  >
                    <Button
                      variant="contained"
                      size="large"
                      startIcon={<LoginIcon />}
                      onClick={() => navigate("/login")}
                      sx={{
                        bgcolor: "white",
                        color: CORPORATE_NAVY,
                        px: 3.5,
                        py: 1.35,
                        fontSize: "0.95rem",
                        fontWeight: 600,
                        borderRadius: "6px",
                        textTransform: "none",
                        boxShadow: "0 4px 14px rgba(0,0,0,0.18)",
                        minWidth: { xs: "100%", sm: 160 },
                        "&:hover": {
                          bgcolor: "#f0f4fa",
                          boxShadow: "0 6px 18px rgba(0,0,0,0.22)",
                        },
                      }}
                    >
                      Sign In
                    </Button>
                  </Stack>

                  <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                    <LocationOn sx={{ color: "rgba(255,255,255,0.7)", fontSize: 18 }} />
                    <Typography
                      sx={{
                        color: "rgba(255,255,255,0.75)",
                        fontSize: "0.9rem",
                        fontWeight: 500,
                      }}
                    >
                      Currently operating in {currentSlideData.location}
                    </Typography>
                  </Box>
                </Box>
              </Fade>
            </Grid>
          </Grid>
        </Container>

        <Box
          sx={{
            position: "absolute",
            bottom: { xs: 20, md: 28 },
            left: "50%",
            transform: "translateX(-50%)",
            display: "flex",
            gap: 1.25,
            zIndex: 3,
          }}
        >
          {heroSlides.map((_, index) => (
            <Box
              key={index}
              onClick={() => setCurrentSlide(index)}
              sx={{
                width: currentSlide === index ? 28 : 8,
                height: 3,
                borderRadius: 1,
                bgcolor:
                  currentSlide === index
                    ? "white"
                    : "rgba(255,255,255,0.35)",
                cursor: "pointer",
                transition: "all 0.3s ease",
              }}
            />
          ))}
        </Box>
      </Box>
    </Box>
  );
};

export default LandingHero;
