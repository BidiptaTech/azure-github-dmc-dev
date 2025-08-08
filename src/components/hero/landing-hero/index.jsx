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
  Paper
} from "@mui/material";
import {
  TravelExplore,
  Explore,
  Send,
  ArrowBackIos,
  ArrowForwardIos,
  PlayArrow,
  LocationOn,
  Star,
  Favorite,
  FavoriteBorder
} from "@mui/icons-material";
import { useTheme } from "@mui/material/styles";

const LandingHero = () => {
  const theme = useTheme();
  const [currentSlide, setCurrentSlide] = useState(0);

  const heroSlides = [
    {
      id: 1,
      image: "/img/masthead/1/bg.webp",
      title: "Discover Your Next Adventure",
      subtitle: "Explore breathtaking destinations around the world",
      location: "Swiss Alps, Switzerland",
      rating: 4.8,
      price: "$1,299",
      isFavorite: false
    },
    {
      id: 2,
      image: "/img/masthead/3/bg.png",
      title: "Tropical Paradise Awaits",
      subtitle: "Escape to pristine beaches and crystal clear waters",
      location: "Maldives, Indian Ocean",
      rating: 4.9,
      price: "$2,499",
      isFavorite: true
    },
    {
      id: 3,
      image: "/img/masthead/4/bg.png",
      title: "Mountain Adventures",
      subtitle: "Conquer peaks and create unforgettable memories",
      location: "Himalayas, Nepal",
      rating: 4.7,
      price: "$1,899",
      isFavorite: false
    },
    {
      id: 4,
      image: "/img/masthead/7/bg.png",
      title: "Cultural Exploration",
      subtitle: "Immerse yourself in rich history and traditions",
      location: "Kyoto, Japan",
      rating: 4.6,
      price: "$1,599",
      isFavorite: true
    }
  ];

  const [favorites, setFavorites] = useState(
    heroSlides.reduce((acc, slide) => {
      acc[slide.id] = slide.isFavorite;
      return acc;
    }, {})
  );

  // Auto-slide functionality
  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
    }, 5000);
    return () => clearInterval(interval);
  }, [heroSlides.length]);

  const handlePrevious = () => {
    setCurrentSlide((prev) => (prev - 1 + heroSlides.length) % heroSlides.length);
  };

  const handleNext = () => {
    setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
  };

  const toggleFavorite = (slideId) => {
    setFavorites((prev) => ({
      ...prev,
      [slideId]: !prev[slideId]
    }));
  };

  const currentSlideData = heroSlides[currentSlide];

  return (
    <Box sx={{ position: 'relative', overflow: 'hidden' }}>
      {/* Hero Slider */}
      <Box
        sx={{
          position: 'relative',
          height: { xs: '80vh', md: '90vh' },
          display: 'flex',
          alignItems: 'center'
        }}
      >
        {/* Background Image with Overlay */}
        <Box
          sx={{
            position: 'absolute',
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            backgroundImage: `url(${currentSlideData.image})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundRepeat: 'no-repeat',
            transition: 'all 0.8s ease-in-out',
            '&::before': {
              content: '""',
              position: 'absolute',
              top: 0,
              left: 0,
              width: '100%',
              height: '100%',
             // background: 'linear-gradient(135deg, rgba(47, 125, 50, 0.4) 0%, rgba(249, 115, 22, 0.4) 100%)',
              zIndex: 1
            }
          }}
        />

        {/* Navigation Arrows */}
        <IconButton
          onClick={handlePrevious}
          sx={{
            position: 'absolute',
            left: { xs: 10, md: 30 },
            top: '50%',
            transform: 'translateY(-50%)',
            bgcolor: 'rgba(255, 255, 255, 0.9)',
            color: theme.palette.primary.main,
            width: 50,
            height: 50,
            zIndex: 3,
            transition: 'all 0.3s ease',
            '&:hover': {
              bgcolor: 'white',
              transform: 'translateY(-50%) scale(1.1)'
            }
          }}
        >
          <ArrowBackIos />
        </IconButton>

        <IconButton
          onClick={handleNext}
          sx={{
            position: 'absolute',
            right: { xs: 10, md: 30 },
            top: '50%',
            transform: 'translateY(-50%)',
            bgcolor: 'rgba(255, 255, 255, 0.9)',
            color: theme.palette.primary.main,
            width: 50,
            height: 50,
            zIndex: 3,
            transition: 'all 0.3s ease',
            '&:hover': {
              bgcolor: 'white',
              transform: 'translateY(-50%) scale(1.1)'
            }
          }}
        >
          <ArrowForwardIos />
        </IconButton>

        {/* Main Content */}
        <Container maxWidth="xl" sx={{ position: 'relative', zIndex: 2 }}>
          <Grid container spacing={4} alignItems="center">
            {/* Left Content */}
            <Grid item xs={12} md={6}>
              <Fade in={true} timeout={800} style={{ marginLeft: '70px' }}>
                <Box>
                  <Typography
                    variant="h1"
                    sx={{
                      fontSize: { xs: '2.5rem', md: '3.5rem', lg: '4rem' },
                      fontWeight: 'bold',
                      color: 'white',
                      mb: 2,
                      textShadow: '2px 2px 4px rgba(0,0,0,0.3)',
                      lineHeight: 1.2
                    }}
                  >
                    {currentSlideData.title}
                  </Typography>
                  
                  <Typography
                    variant="h5"
                    sx={{
                      fontSize: { xs: '1.2rem', md: '1.4rem' },
                      color: 'white',
                      mb: 4,
                      
                      textShadow: '1px 1px 2px rgba(0,0,0,0.3)',
                      opacity: 0.9
                    }}
                  >
                    {currentSlideData.subtitle}
                  </Typography>

                  <Stack direction="row" spacing={2} sx={{ mb: 4 }}>
                    <Button
                      variant="contained"
                      size="large"
                      startIcon={<TravelExplore />}
                      sx={{
                        bgcolor: theme.palette.primary.main,
                        color: 'white',
                        px: 4,
                        py: 1.5,
                        fontSize: '1.1rem',
                        fontWeight: 'bold',
                        borderRadius: '50px',
                        boxShadow: '0 8px 25px rgba(249, 115, 22, 0.3)',
                        transition: 'all 0.3s ease',
                        '&:hover': {
                          bgcolor: '#ea580c',
                          transform: 'translateY(-2px)',
                          boxShadow: '0 12px 35px rgba(249, 115, 22, 0.4)'
                        }
                      }}
                    >
                      Explore Now
                    </Button>
                    
                    <Button
                      variant="outlined"
                      size="large"
                      startIcon={<PlayArrow />}
                      sx={{
                        borderColor: 'white',
                        color: 'white',
                        px: 4,
                        py: 1.5,
                        fontSize: '1.1rem',
                        fontWeight: 'bold',
                        borderRadius: '50px',
                        borderWidth: '2px',
                        transition: 'all 0.3s ease',
                        '&:hover': {
                          bgcolor: 'rgba(255, 255, 255, 0.1)',
                          borderColor: 'white',
                          borderWidth: '2px'
                        }
                      }}
                    >
                      Watch Video
                    </Button>
                  </Stack>

                  {/* Location & Rating */}
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 3 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <LocationOn sx={{ color: theme.palette.primary.main, fontSize: 20 }} />
                      <Typography variant="body1" sx={{ color: 'white', fontWeight: 500 }}>
                        {currentSlideData.location}
                      </Typography>
                    </Box>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <Star sx={{ color: '#ffd700', fontSize: 20 }} />
                      <Typography variant="body1" sx={{ color: 'white', fontWeight: 500 }}>
                        {currentSlideData.rating}
                      </Typography>
                    </Box>
                  </Box>
                </Box>
              </Fade>
            </Grid>

            {/* Right Content - Destination Card */}
            <Grid item xs={12} md={6}>
              <Fade in={true} timeout={1000}>
                <Box sx={{ display: 'flex', justifyContent: 'center' }}>
                  <Paper
                    elevation={20}
                    sx={{
                      p: 3,
                      borderRadius: '20px',
                      bgcolor: 'rgba(255, 255, 255, 0.95)',
                      backdropFilter: 'blur(10px)',
                      border: '1px solid rgba(255, 255, 255, 0.2)',
                      maxWidth: 400,
                      width: '100%'
                    }}
                  >
                    <Box sx={{ position: 'relative', mb: 2 }}>
                      <Box
                        sx={{
                          width: '100%',
                          height: 200,
                          borderRadius: '15px',
                          backgroundImage: `url(${currentSlideData.image})`,
                          backgroundSize: 'cover',
                          backgroundPosition: 'center',
                          position: 'relative'
                        }}
                      >
                        <IconButton
                          onClick={() => toggleFavorite(currentSlideData.id)}
                          sx={{
                            position: 'absolute',
                            top: 10,
                            right: 10,
                            bgcolor: 'rgba(255, 255, 255, 0.9)',
                            color: favorites[currentSlideData.id] ? '#f44336' : '#666',
                            width: 35,
                            height: 35,
                            '&:hover': {
                              bgcolor: 'white'
                            }
                          }}
                        >
                          {favorites[currentSlideData.id] ? <Favorite /> : <FavoriteBorder />}
                        </IconButton>
                      </Box>
                    </Box>

                    <Typography
                      variant="h6"
                      sx={{
                        fontWeight: 'bold',
                        color: '#333',
                        mb: 1
                      }}
                    >
                      {currentSlideData.location}
                    </Typography>

                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                        <Star sx={{ color: '#ffd700', fontSize: 18 }} />
                        <Typography variant="body2" sx={{ color: '#666' }}>
                          {currentSlideData.rating} (2,543 reviews)
                        </Typography>
                      </Box>
                      {/* <Typography
                        variant="h6"
                        sx={{
                          fontWeight: 'bold',
                          color: theme.palette.primary.main
                        }}
                      >
                        {currentSlideData.price}
                      </Typography> */}
                    </Box>
                  </Paper>
                </Box>
              </Fade>
            </Grid>
          </Grid>
        </Container>

        {/* Slide Indicators */}
        <Box
          sx={{
            position: 'absolute',
            bottom: 30,
            left: '50%',
            transform: 'translateX(-50%)',
            display: 'flex',
            gap: 2,
            zIndex: 3
          }}
        >
          {heroSlides.map((_, index) => (
            <Box
              key={index}
              onClick={() => setCurrentSlide(index)}
              sx={{
                width: currentSlide === index ? 30 : 12,
                height: 4,
                borderRadius: '2px',
                bgcolor: currentSlide === index ? theme.palette.primary.main : 'rgba(255, 255, 255, 0.5)',
                cursor: 'pointer',
                transition: 'all 0.3s ease',
                '&:hover': {
                  bgcolor: currentSlide === index ? theme.palette.primary.main : 'rgba(255, 255, 255, 0.8)'
                }
              }}
            />
          ))}
        </Box>
      </Box>
    </Box>
  );
};

export default LandingHero; 