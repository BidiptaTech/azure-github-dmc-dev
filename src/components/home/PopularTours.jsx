import React, { useState } from 'react';
import {
  Box,
  Container,
  Typography,
  Grid,
  Card,
  CardContent,
  CardMedia,
  IconButton,
  Chip,
  Rating,
  Stack,
  Badge,
  Button
} from '@mui/material';
import {
  FavoriteBorder,
  Favorite,
  LocationOn,
  Schedule,
  ArrowBack,
  ArrowForward
} from '@mui/icons-material';

const PopularTours = () => {
  const [favorites, setFavorites] = useState([]);
  const [currentIndex, setCurrentIndex] = useState(0);

  const toggleFavorite = (tourId) => {
    setFavorites(prev => 
      prev.includes(tourId) 
        ? prev.filter(id => id !== tourId)
        : [...prev, tourId]
    );
  };

  const allTours = [
    {
      id: 1,
      title: "All Inclusive Ultimate Circle Island Day With Lunch",
      location: "Slingerland",
      duration: "6 Days, 3 Night",
      rating: 4.0,
      reviews: 10,
      image: "/Images/landingpage/destination/Vietnam/6.jpg",
      discount: "40% Off",
      featured: true,
      price: "$59.00"
    },
    {
      id: 2,
      title: "Molokini And Turtle Town Snorkeling Adventure Aboard",
      location: "Slingerland",
      duration: "6 Days, 3 Night",
      rating: 4.0,
      reviews: 8,
      image: "/Images/landingpage/destination/Vietnam/4.jpg",
      discount: null,
      featured: true,
      price: "$450.00"
    },
    {
      id: 3,
      title: "All Inclusive Ultimate Circle Island Day With Lunch",
      location: "Slingerland",
      duration: "6 Days, 3 Night",
      rating: 4.2,
      reviews: 12,
      image: "/Images/landingpage/destination/Thailand/3.jpg",
      discount: null,
      featured: true,
      price: "$380.00"
    },
    {
      id: 4,
      title: "Mountain Hiking Adventure Experience",
      location: "Switzerland",
      duration: "5 Days, 4 Night",
      rating: 4.8,
      reviews: 25,
      image: "/Images/landingpage/destination/Malaysia/2.jpg",
      discount: "25% Off",
      featured: true,
      price: "$899.00"
    },
    {
      id: 5,
      title: "Tropical Beach Resort Getaway",
      location: "Maldives",
      duration: "7 Days, 6 Night",
      rating: 4.5,
      reviews: 18,
      image: "/Images/landingpage/destination/Malaysia/3.jpg", 
      discount: "30% Off",
      featured: false,
      price: "$1200.00"
    },
    {
      id: 6,
      title: "Cultural Heritage City Tour",
      location: "Kyoto",
      duration: "4 Days, 3 Night",
      rating: 4.3,
      reviews: 15,
      image: "/Images/landingpage/destination/Malaysia/5.jpg",
      discount: null,
      featured: true,
      price: "$650.00"
    }
  ];

  const toursPerPage = 3;
  const totalPages = Math.ceil(allTours.length / toursPerPage);

  const handlePrevious = () => {
    setCurrentIndex(prev => prev === 0 ? totalPages - 1 : prev - 1);
  };

  const handleNext = () => {
    setCurrentIndex(prev => prev === totalPages - 1 ? 0 : prev + 1);
  };

  const getCurrentTours = () => {
    const start = currentIndex * toursPerPage;
    const end = start + toursPerPage;
    return allTours.slice(start, end);
  };

  const tours = getCurrentTours();

  return (
    <Box sx={{ py: 10, bgcolor: '#f8f9fa', position: 'relative' }}>
      {/* Decorative Elements */}
      <Box
        sx={{
          position: 'absolute',
          top: '20%',
          left: '3%',
          width: 50,
          height: 50,
          borderRadius: '50%',
          bgcolor: '#4CAF50',
          opacity: 0.3,
          zIndex: 1
        }}
      />

      <Container maxWidth="xl">
        {/* Header */}
        <Box sx={{ mb: 6 }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <Box>
              <Typography
                variant="h6"
                sx={{
                  color: '#f97316',
                  fontWeight: 600,
                  fontSize: '18px',
                  mb: 1
                }}
              >
                Popular tours
              </Typography>
              
              <Typography
                variant="h2"
                sx={{
                  fontSize: { xs: '2rem', md: '2.5rem', lg: '3rem' },
                  fontWeight: 'bold',
                  color: '#333'
                }}
              >
                Most Popular <span style={{ color: '#4CAF50' }}>Tours</span>
              </Typography>
            </Box>

            {/* Navigation Arrows */}
            <Box sx={{ display: 'flex', gap: 2 }}>
              <IconButton
                onClick={handlePrevious}
                sx={{
                  width: 50,
                  height: 50,
                  border: '2px solid #4CAF50',
                  color: '#4CAF50',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    bgcolor: '#4CAF50',
                    color: 'white',
                    transform: 'scale(1.1)'
                  }
                }}
              >
                <ArrowBack />
              </IconButton>
              <IconButton
                onClick={handleNext}
                sx={{
                  width: 50,
                  height: 50,
                  border: '2px solid #4CAF50',
                  color: '#4CAF50',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    bgcolor: '#4CAF50',
                    color: 'white',
                    transform: 'scale(1.1)'
                  }
                }}
              >
                <ArrowForward />
              </IconButton>
            </Box>
          </Box>
        </Box>

        {/* Tours Grid */}
        <Grid container spacing={{ xs: 2, sm: 3, md: 4 }}>
          {tours.map((tour) => (
            <Grid item xs={12} md={6} lg={4} key={tour.id}>
              <Card
                sx={{
                  height: '100%',
                  borderRadius: '20px',
                  overflow: 'hidden',
                  position: 'relative',
                  cursor: 'pointer',
                  transition: 'transform 0.3s ease, box-shadow 0.3s ease',
                  '&:hover': {
                    transform: 'translateY(-5px)',
                    boxShadow: '0 15px 40px rgba(0,0,0,0.2)'
                  }
                }}
              >
                {/* Card Image */}
                <Box sx={{ position: 'relative', height: { xs: 200, sm: 240, md: 280 } }}>
                  <CardMedia
                    component="img"
                    height={280}
                    image={tour.image}
                    alt={tour.title}
                    sx={{ 
                      objectFit: 'cover',
                      height: { xs: 200, sm: 240, md: 280 }
                    }}
                  />
                  
                  {/* Gradient Overlay */}
                  <Box
                    sx={{
                      position: 'absolute',
                      top: 0,
                      left: 0,
                      right: 0,
                      bottom: 0,
                      background: 'linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.6) 100%)'
                    }}
                  />

                  {/* Badges */}
                  <Box
                    sx={{
                      position: 'absolute',
                      top: 20,
                      left: 20,
                      display: 'flex',
                      flexDirection: 'column',
                      gap: 1
                    }}
                  >
                    {/* {tour.discount && (
                      <Chip
                        label={`-${tour.discount}`}
                        sx={{
                          bgcolor: '#f97316',
                          color: 'white',
                          fontWeight: 'bold',
                          fontSize: '12px',
                          borderRadius: '15px',
                          px: 1
                        }}
                      />
                    )} */}
                    {tour.featured && (
                      <Chip
                        label="Featured"
                        sx={{
                          bgcolor: '#4CAF50',
                          color: 'white',
                          fontWeight: 'bold',
                          fontSize: '12px',
                          borderRadius: '15px',
                          px: 1
                        }}
                      />
                    )}
                  </Box>

                  {/* Favorite Button */}
                  <IconButton
                    onClick={() => toggleFavorite(tour.id)}
                    sx={{
                      position: 'absolute',
                      top: 20,
                      right: 20,
                      bgcolor: 'rgba(255, 255, 255, 0.9)',
                      color: favorites.includes(tour.id) ? '#f44336' : '#666',
                      width: 40,
                      height: 40,
                      '&:hover': {
                        bgcolor: 'white',
                        color: '#f44336',
                        transform: 'scale(1.1)'
                      }
                    }}
                  >
                    {favorites.includes(tour.id) ? <Favorite /> : <FavoriteBorder />}
                  </IconButton>

                  {/* Location and Duration */}
                  <Box
                    sx={{
                      position: 'absolute',
                      bottom: 20,
                      left: 20,
                      right: 20,
                      bgcolor: '#f97316',
                      borderRadius: '25px',
                      px: 3,
                      py: 1.5,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center'
                    }}
                  >
                    <Stack direction="row" spacing={2} alignItems="center">
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                        <LocationOn sx={{ fontSize: 16, color: 'white' }} />
                        <Typography variant="body2" sx={{ color: 'white', fontWeight: 500 }}>
                          {tour.location}
                        </Typography>
                      </Box>
                      <Box sx={{ color: 'white', fontSize: '14px' }}>|</Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                        <Schedule sx={{ fontSize: 16, color: 'white' }} />
                        <Typography variant="body2" sx={{ color: 'white', fontWeight: 500 }}>
                          {tour.duration}
                        </Typography>
                      </Box>
                    </Stack>
                  </Box>
                </Box>

                {/* Card Content */}
                <CardContent sx={{ p: { xs: 2, sm: 2.5, md: 3 } }}>
                  {/* Rating */}
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 2 }}>
                    <Rating
                      value={tour.rating}
                      readOnly
                      precision={0.1}
                      size="small"
                      sx={{
                        '& .MuiRating-icon': {
                          color: '#FFD700'
                        }
                      }}
                    />
                    <Typography variant="body2" sx={{ color: '#666', fontSize: '14px' }}>
                      ({tour.reviews} Review)
                    </Typography>
                  </Box>

                  {/* Title */}
                  <Typography
                    variant="h6"
                    sx={{
                      fontWeight: 'bold',
                      color: '#333',
                      fontSize: { xs: '16px', sm: '17px', md: '18px' },
                      lineHeight: 1.4,
                      mb: { xs: 2, sm: 2.5, md: 3 }
                    }}
                  >
                    {tour.title}
                  </Typography>

                  {/* Price and Book Now */}
                  <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Box>
                      <Typography variant="body2" sx={{ color: '#666', fontSize: '14px' }}>
                        Per Day
                      </Typography>
                      <Typography
                        variant="h6"
                        sx={{
                          fontWeight: 'bold',
                          color: '#333',
                          fontSize: '20px'
                        }}
                      >
                        {tour.price}
                      </Typography>
                    </Box>
                    <Button
                      variant="contained"
                      sx={{
                        bgcolor: '#4CAF50',
                        color: 'white',
                        borderRadius: '25px',
                        px: 3,
                        py: 1,
                        fontWeight: 'bold',
                        fontSize: '14px',
                        textTransform: 'none',
                        '&:hover': {
                          bgcolor: '#45a049',
                          transform: 'scale(1.05)'
                        }
                      }}
                    >
                      Book Now
                    </Button>
                  </Box>
                </CardContent>
              </Card>
            </Grid>
          ))}
        </Grid>

        {/* Page Indicator */}
        <Box sx={{ display: 'flex', justifyContent: 'center', mt: 4 }}>
          <Box sx={{ display: 'flex', gap: 1 }}>
            {Array.from({ length: totalPages }, (_, index) => (
              <Box
                key={index}
                sx={{
                  width: 12,
                  height: 12,
                  borderRadius: '50%',
                  bgcolor: index === currentIndex ? '#4CAF50' : '#ddd',
                  cursor: 'pointer',
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    bgcolor: index === currentIndex ? '#4CAF50' : '#bbb'
                  }
                }}
                onClick={() => setCurrentIndex(index)}
              />
            ))}
          </Box>
        </Box>
      </Container>
    </Box>
  );
};

export default PopularTours; 