import React, { useState } from 'react';
import {
  Box,
  Container,
  Typography,
  Grid,
  Card,
  CardContent,
  CardMedia,
  Chip,
  Stack,
  Button,
  Avatar,
  Rating,
  Fade,
  Grow
} from '@mui/material';
import { Link } from "react-router-dom";
import { useTheme } from '@mui/material/styles';

const PopularDestinations = () => {
  const theme = useTheme();
  const [selectedRegion, setSelectedRegion] = useState('Asia');

  const regions = [
    { name: 'Europe', active: false },
    { name: 'Asia', active: true },
    { name: 'Africa', active: false },
    { name: 'South America', active: false },
    { name: 'Australia', active: false }
  ];

  const destinations = [
    {
      id: 1,
      name: "Bangkok",
      image: "/img/destinations/1/1.png",
      price: "From $599",
      rating: 4.8,
      reviews: 2847,
      description: "Temple and cultural experiences",
      region: "Asia"
    },
    {
      id: 2,
      name: "Tokyo",
      image: "/img/destinations/1/2.png",
      price: "From $899",
      rating: 4.9,
      reviews: 3291,
      description: "Modern city with traditional culture",
      region: "Asia"
    },
    {
      id: 3,
      name: "Kashmir",
      image: "/img/destinations/1/3.png",
      price: "From $1200",
      rating: 4.7,
      reviews: 1956,
      description: "Mountain landscapes and scenic beauty",
      region: "Asia"
    },
    {
      id: 4,
      name: "Indonesia",
      image: "/img/destinations/1/4.png",
      price: "From $1500",
      rating: 4.8,
      reviews: 2134,
      description: "Tropical paradise with crystal waters",
      region: "Asia"
    },
    {
      id: 5,
      name: "Paris",
      image: "/img/destinations/1/5.png",
      price: "From $1100",
      rating: 4.6,
      reviews: 2847,
      description: "City of lights and romance",
      region: "Europe"
    },
    {
      id: 6,
      name: "Rome",
      image: "/img/destinations/1/1.png",
      price: "From $950",
      rating: 4.5,
      reviews: 1923,
      description: "Ancient history and culture",
      region: "Europe"
    },
    {
      id: 7,
      name: "Cairo",
      image: "/img/destinations/1/2.png",
      price: "From $750",
      rating: 4.4,
      reviews: 1456,
      description: "Pyramids and ancient wonders",
      region: "Africa"
    },
    {
      id: 8,
      name: "Cape Town",
      image: "/img/destinations/1/3.png",
      price: "From $850",
      rating: 4.7,
      reviews: 2134,
      description: "Beautiful landscapes and wine",
      region: "Africa"
    },
    {
      id: 9,
      name: "Buenos Aires",
      image: "/img/destinations/1/4.png",
      price: "From $700",
      rating: 4.3,
      reviews: 1678,
      description: "Tango and vibrant culture",
      region: "South America"
    },
    {
      id: 10,
      name: "Sydney",
      image: "/img/destinations/1/5.png",
      price: "From $1300",
      rating: 4.8,
      reviews: 2945,
      description: "Harbor views and beaches",
      region: "Australia"
    }
  ];

  const filteredDestinations = destinations.filter(dest => dest.region === selectedRegion);

  return (
    <Box sx={{ py: 10, bgcolor: '#f8f9fa', position: 'relative' }}>
      {/* Background decorative elements */}
      <Box
        sx={{
          position: 'absolute',
          top: '10%',
          left: '5%',
          width: '100px',
          height: '100px',
          borderRadius: '50%',
          background: 'rgba(76, 175, 80, 0.1)',
          zIndex: 1
        }}
      />
      <Box
        sx={{
          position: 'absolute',
          bottom: '20%',
          right: '8%',
          width: '80px',
          height: '80px',
          borderRadius: '50%',
          background: 'rgba(249, 115, 22, 0.1)',
          zIndex: 1
        }}
      />

   
      <Container maxWidth="xl" sx={{ position: 'relative', zIndex: 2 }}>
        {/* Header Section */}
        <Fade in={true} timeout={800}>
          <Box sx={{ textAlign: 'center', mb: 6 }}>
            <Typography
              variant="h6"
              sx={{
                color: theme.palette.primary.main,
                fontWeight: 600,
                fontSize: '18px',
                mb: 2
              }}
            >
              Popular Destinations
            </Typography>
            
            <Typography
              variant="h2"
              sx={{
                fontSize: { xs: '2rem', md: '2.5rem', lg: '3rem' },
                fontWeight: 'bold',
                color: '#333',
                mb: 3
              }}
            >
              Popular <span style={{ color: '#4CAF50' }}>Destinations</span>
            </Typography>
            
            <Typography
              variant="body1"
              sx={{
                color: '#666',
                fontSize: '16px',
                lineHeight: 1.6,
                maxWidth: '800px',
                mx: 'auto',
                mb: 4
              }}
            >
              The Island Of Crete Offers A Rare Mix Of Splendid Beaches, Amazing Mountain Landscapes, Vibrant 
              Towns And Cosy Villages Inhabited By Warm-Hearted Locals.
            </Typography>

            {/* Region Filter Chips */}
            <Stack
              direction="row"
              spacing={2}
              sx={{
                justifyContent: 'center',
                flexWrap: 'wrap',
                gap: 2,
                mb: 6
              }}
            >
              {regions.map((region) => (
                <Chip
                  key={region.name}
                  label={region.name}
                  onClick={() => setSelectedRegion(region.name)}
                  sx={{
                    bgcolor: selectedRegion === region.name ? theme.palette.primary.main : '#333',
                    color: 'white',
                    fontWeight: 600,
                    fontSize: '14px',
                    px: 3,
                    py: 1.5,
                    borderRadius: '25px',
                    transition: 'all 0.3s ease',
                    '&:hover': {
                      bgcolor: selectedRegion === region.name ? '#e5630a' : '#555',
                      transform: 'translateY(-2px)'
                    },
                    cursor: 'pointer'
                  }}
                />
              ))}
            </Stack>
          </Box>
        </Fade>

        {/* Destinations Grid */}
        <Box sx={{ display: 'flex', justifyContent: 'center', mb: 6 }}>
          <Grid container spacing={4} sx={{ maxWidth: '1000px' }}>
            {filteredDestinations.slice(0, 4).map((destination, index) => (
              <Grid item xs={12} sm={6} md={6} lg={3} key={destination.id}>
                <Grow in={true} timeout={1000 + index * 200}>
                  <Box
                    sx={{
                      display: 'flex',
                      flexDirection: 'column',
                      alignItems: 'center',
                      cursor: 'pointer',
                      transition: 'transform 0.3s ease',
                      '&:hover': {
                        transform: 'translateY(-10px)'
                      }
                    }}
                  >
                    {/* Circular Image Container */}
                    <Box
                      sx={{
                        position: 'relative',
                        width: '220px',
                        height: '220px',
                        borderRadius: '50%',
                        overflow: 'hidden',
                        boxShadow: '0 15px 35px rgba(0,0,0,0.15)',
                        mb: 3,
                        border: '4px solid white'
                      }}
                    >
                      <Box
                        sx={{
                          width: '100%',
                          height: '100%',
                          backgroundImage: `url(${destination.image})`,
                          backgroundSize: 'cover',
                          backgroundPosition: 'center',
                          backgroundRepeat: 'no-repeat',
                          position: 'relative'
                        }}
                      >
                        {/* Gradient overlay for better text visibility */}
                        <Box
                          sx={{
                            position: 'absolute',
                            bottom: 0,
                            left: 0,
                            width: '100%',
                            height: '40%',
                            background: 'linear-gradient(to top, rgba(0,0,0,0.3), transparent)'
                          }}
                        />
                      </Box>

                      {/* Destination Name Label */}
                      <Box
                        sx={{
                          position: 'absolute',
                          bottom: -15,
                          left: '50%',
                          transform: 'translateX(-50%)',
                          bgcolor: 'white',
                          borderRadius: '20px',
                          px: 3,
                          py: 1,
                          boxShadow: '0 4px 15px rgba(0,0,0,0.1)',
                          border: '1px solid #f0f0f0'
                        }}
                      >
                        <Typography
                          variant="h6"
                          sx={{
                            fontWeight: 'bold',
                            color: theme.palette.primary.main,
                            fontSize: '16px',
                            textAlign: 'center'
                          }}
                        >
                          {destination.name}
                        </Typography>
                      </Box>
                    </Box>

                    {/* Hidden Details for Hover */}
                    <Box
                      sx={{
                        textAlign: 'center',
                        opacity: 0,
                        transition: 'opacity 0.3s ease',
                        mt: 2,
                        '&:hover': {
                          opacity: 1
                        }
                      }}
                    >
                      <Typography
                        variant="body2"
                        sx={{
                          color: '#666',
                          fontSize: '14px',
                          mb: 1
                        }}
                      >
                        {destination.description}
                      </Typography>
                      
                      <Stack
                        direction="row"
                        spacing={1}
                        sx={{
                          alignItems: 'center',
                          justifyContent: 'center',
                          mb: 1
                        }}
                      >
                        <Rating
                          value={destination.rating}
                          readOnly
                          precision={0.1}
                          size="small"
                          sx={{
                            '& .MuiRating-icon': {
                              color: '#FFD700'
                            }
                          }}
                        />
                        <Typography variant="body2" sx={{ fontSize: '12px', color: '#666' }}>
                          ({destination.reviews})
                        </Typography>
                      </Stack>

                      <Typography
                        variant="body2"
                        sx={{
                          fontWeight: 'bold',
                          color: theme.palette.primary.main,
                          fontSize: '16px'
                        }}
                      >
                        {destination.price}
                      </Typography>
                    </Box>
                  </Box>
                </Grow>
              </Grid>
            ))}
          </Grid>
        </Box>

        {/* View All Button */}
        {/* <Fade in={true} timeout={1600}>
          <Box sx={{ textAlign: 'center' }}>
            <Button
              // component={Link}
              // to="/destinations"
              variant="contained"
              sx={{
                bgcolor: theme.palette.primary.main,
                color: 'white',
                borderRadius: '50px',
                px: 6,
                py: 2,
                fontWeight: 'bold',
                fontSize: '16px',
                textTransform: 'none',
                boxShadow: '0 8px 25px rgba(249, 115, 22, 0.3)',
                transition: 'all 0.3s ease',
                '&:hover': {
                  bgcolor: '#e5630a',
                  transform: 'translateY(-2px)',
                  boxShadow: '0 12px 30px rgba(249, 115, 22, 0.4)'
                }
              }}
            >
              View All Destinations
            </Button>
          </Box>
        </Fade> */}
      </Container>
    </Box>
  );
};

export default PopularDestinations; 