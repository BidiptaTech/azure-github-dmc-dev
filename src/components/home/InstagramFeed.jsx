import React, { useState } from 'react';
import {
  Box,
  Container,
  Typography,
  IconButton,
  Fade
} from '@mui/material';
import {
  Instagram,
  ArrowBackIos,
  ArrowForwardIos
} from '@mui/icons-material';

const InstagramFeed = () => {
  const [currentSlide, setCurrentSlide] = useState(0);

  const instagramPosts = [
    {
      id: 1,
      image: "/img/destinations/1/1.png",
      instagramUrl: "https://www.instagram.com/gotur_travel/",
      alt: "Mountain adventure travel"
    },
    {
      id: 2,
      image: "/img/destinations/1/2.png",
      instagramUrl: "https://www.instagram.com/gotur_travel/",
      alt: "Tropical paradise with palm trees"
    },
    {
      id: 3,
      image: "/img/destinations/1/3.png",
      instagramUrl: "https://www.instagram.com/gotur_travel/",
      alt: "Couple enjoying scenic view"
    },
    {
      id: 4,
      image: "/img/destinations/1/4.png",
      instagramUrl: "https://www.instagram.com/gotur_travel/",
      alt: "Sunset mountain landscape"
    },
    {
      id: 5,
      image: "/img/destinations/1/1.png",
      instagramUrl: "https://www.instagram.com/gotur_travel/",
      alt: "Beautiful boat on crystal waters"
    }
  ];

  const handlePrevious = () => {
    setCurrentSlide(prev => prev === 0 ? 0 : prev - 1);
  };

  const handleNext = () => {
    const maxSlide = instagramPosts.length - 5; // Show 5 images at once
    setCurrentSlide(prev => prev >= maxSlide ? maxSlide : prev + 1);
  };

  const handleImageClick = (instagramUrl) => {
    window.open(instagramUrl, '_blank', 'noopener,noreferrer');
  };

  return (
    <Box sx={{ py: 8, bgcolor: '#f8f9fa' }}>
      <Container maxWidth="xl">
        {/* Header */}
        <Box sx={{ textAlign: 'center', mb: 6 }}>
          <Typography
            variant="h4"
            sx={{
              fontWeight: 'bold',
              color: '#333',
              fontSize: { xs: '2rem', md: '2.5rem' }
            }}
          >
            Follow Instagram
          </Typography>
        </Box>

        {/* Instagram Feed Carousel */}
        <Box sx={{ position: 'relative' }}>
          {/* Navigation Arrows */}
          {currentSlide > 0 && (
            <IconButton
              onClick={handlePrevious}
              sx={{
                position: 'absolute',
                left: -20,
                top: '50%',
                transform: 'translateY(-50%)',
                bgcolor: 'white',
                boxShadow: '0 4px 15px rgba(0,0,0,0.1)',
                zIndex: 2,
                '&:hover': {
                  bgcolor: '#f5f5f5'
                }
              }}
            >
              <ArrowBackIos sx={{ fontSize: 20 }} />
            </IconButton>
          )}

          {currentSlide < instagramPosts.length - 5 && (
            <IconButton
              onClick={handleNext}
              sx={{
                position: 'absolute',
                right: -20,
                top: '50%',
                transform: 'translateY(-50%)',
                bgcolor: 'white',
                boxShadow: '0 4px 15px rgba(0,0,0,0.1)',
                zIndex: 2,
                '&:hover': {
                  bgcolor: '#f5f5f5'
                }
              }}
            >
              <ArrowForwardIos sx={{ fontSize: 20 }} />
            </IconButton>
          )}

          {/* Images Container */}
          <Box
            sx={{
              display: 'flex',
              gap: 2,
              overflow: 'hidden',
              px: 2
            }}
          >
            <Box
              sx={{
                display: 'flex',
                gap: 2,
                transition: 'transform 0.3s ease',
                transform: `translateX(-${currentSlide * (100 / 5)}%)`
              }}
            >
              {instagramPosts.map((post, index) => (
                <Box
                  key={post.id}
                  sx={{
                    position: 'relative',
                    minWidth: { xs: '250px', sm: '280px', md: '260px' },
                    height: { xs: '160px', sm: '180px', md: '200px' },
                    borderRadius: '12px',
                    overflow: 'hidden',
                    cursor: 'pointer',
                    transition: 'transform 0.3s ease',
                    '&:hover': {
                      transform: 'scale(1.05)',
                      '& .instagram-overlay': {
                        opacity: 1
                      }
                    }
                  }}
                  //onClick={() => handleImageClick(post.instagramUrl)}
                >
                  {/* Background Image */}
                  <Box
                    sx={{
                      width: '100%',
                      height: '100%',
                      backgroundImage: `url(${post.image})`,
                      backgroundSize: 'cover',
                      backgroundPosition: 'center',
                      backgroundRepeat: 'no-repeat'
                    }}
                  />

                  {/* Instagram Icon Overlay */}
                  {index === 0 && (
                    <Box
                      sx={{
                        position: 'absolute',
                        top: '50%',
                        left: '50%',
                        transform: 'translate(-50%, -50%)',
                        width: 60,
                        height: 60,
                        borderRadius: '50%',
                        bgcolor: '#E4405F',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        boxShadow: '0 4px 15px rgba(228, 64, 95, 0.3)',
                        zIndex: 1
                      }}
                    >
                      <Instagram sx={{ fontSize: 30, color: 'white' }} />
                    </Box>
                  )}

                  {/* Hover Overlay */}
                  <Fade in={true}>
                    <Box
                      className="instagram-overlay"
                      sx={{
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        bgcolor: 'rgba(0, 0, 0, 0.3)',
                        opacity: 0,
                        transition: 'opacity 0.3s ease',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                      }}
                    >
                      <Instagram 
                        sx={{ 
                          fontSize: 40, 
                          color: 'white',
                          filter: 'drop-shadow(0 2px 4px rgba(0,0,0,0.3))'
                        }} 
                      />
                    </Box>
                  </Fade>

                  {/* Gradient Overlay */}
                  <Box
                    sx={{
                      position: 'absolute',
                      bottom: 0,
                      left: 0,
                      right: 0,
                      height: '40%',
                      background: 'linear-gradient(to top, rgba(0,0,0,0.5), transparent)',
                      pointerEvents: 'none'
                    }}
                  />
                </Box>
              ))}
            </Box>
          </Box>

          {/* Slide Indicators */}
          <Box
            sx={{
              display: 'flex',
              justifyContent: 'center',
              gap: 1,
              mt: 4
            }}
          >
            {Array.from({ length: Math.max(1, instagramPosts.length - 4) }).map((_, index) => (
              <Box
                key={index}
                onClick={() => setCurrentSlide(index)}
                sx={{
                  width: 8,
                  height: 8,
                  borderRadius: '50%',
                  bgcolor: currentSlide === index ? '#E4405F' : '#ddd',
                  cursor: 'pointer',
                  transition: 'background-color 0.3s ease',
                  '&:hover': {
                    bgcolor: currentSlide === index ? '#E4405F' : '#bbb'
                  }
                }}
              />
            ))}
          </Box>
        </Box>

        {/* Follow Button */}
        <Box sx={{ textAlign: 'center', mt: 6 }}>
          <IconButton
            onClick={() => handleImageClick("https://www.instagram.com/gotur_travel/")}
            sx={{
              bgcolor: '#E4405F',
              color: 'white',
              width: 60,
              height: 60,
              '&:hover': {
                bgcolor: '#d73a56',
                transform: 'scale(1.1)'
              },
              transition: 'all 0.3s ease'
            }}
          >
            <Instagram sx={{ fontSize: 30 }} />
          </IconButton>
          <Typography
            variant="body1"
            sx={{
              mt: 2,
              color: '#666',
              fontSize: '16px'
            }}
          >
            Follow us for more travel inspiration
          </Typography>
        </Box>
      </Container>
    </Box>
  );
};

export default InstagramFeed; 