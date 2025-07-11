import React from 'react';
import {
  Box,
  Container,
  Typography,
  Card,
  CardContent,
  Avatar,
  Rating,
  IconButton
} from '@mui/material';
import {
  FormatQuote,
  KeyboardArrowDown,
  KeyboardArrowUp
} from '@mui/icons-material';

const TestimonialsRight = ({ testimonials, currentTestimonial,onPrevious, onNext }) => {
  return (
    <Box
      sx={{
        width: '60%',
        height: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        py: 8,
        position: 'relative',
        overflow: 'hidden'
      }}
    >
      {/* Background Image */}
      <Box
        component="img"
        src="/img/masthead/1/bg.webp"
        alt="Background"
        sx={{
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          zIndex: 1
        }}
      />

      {/* Dark Overlay for Content Readability */}
      <Box
        sx={{
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          background: 'linear-gradient(135deg, rgba(44, 62, 80, 0.85) 0%, rgba(44, 62, 80, 0.75) 100%)',
          zIndex: 2
        }}
      />
      {/* Navigation Arrows */}
      <Box
        sx={{
          position: 'absolute',
          left: '50px',
          top: '50%',
          transform: 'translateY(-50%)',
          display: 'flex',
          flexDirection: 'column',
          gap: 1,
          zIndex: 5
        }}
      >
        <IconButton
          onClick={onPrevious}
          sx={{
            width: 45,
            height: 45,
            bgcolor: 'white',
            color: '#666',
            boxShadow: '0 6px 20px rgba(0,0,0,0.15)',
            border: '2px solid #e0e0e0',
            '&:hover': {
              bgcolor: '#4CAF50',
              color: 'white',
              borderColor: '#4CAF50',
              transform: 'scale(1.1)'
            },
            transition: 'all 0.3s ease'
          }}
        >
          <KeyboardArrowUp sx={{ fontSize: 20 }} />
        </IconButton>
        
        <IconButton
          onClick={onNext}
          sx={{
            width: 45,
            height: 45,
            bgcolor: 'white',
            color: '#666',
            boxShadow: '0 6px 20px rgba(0,0,0,0.15)',
            border: '2px solid #e0e0e0',
            '&:hover': {
              bgcolor: '#4CAF50',
              color: 'white',
              borderColor: '#4CAF50',
              transform: 'scale(1.1)'
            },
            transition: 'all 0.3s ease'
          }}
        >
          <KeyboardArrowDown sx={{ fontSize: 20 }} />
        </IconButton>
      </Box>
      
      {/* Cut Effect Elements */}
      <Box
        sx={{
          position: 'absolute',
          top: '8%',
          right: '8%',
          width: '150px',
          height: '150px',
          borderRadius: '50%',
          overflow: 'hidden',
          border: '6px solid rgba(255, 255, 255, 0.3)',
          boxShadow: '0 15px 35px rgba(0,0,0,0.3)',
          zIndex: 3
        }}
      >
        <Box
          component="img"
          src="/img/destinations/1/2.png"
          alt="Destination Cut"
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover'
          }}
        />
      </Box>

      <Box
        sx={{
          position: 'absolute',
          bottom: '10%',
          left: '140px',
          width: '100px',
          height: '100px',
          borderRadius: '25px',
          overflow: 'hidden',
          border: '4px solid rgba(255, 255, 255, 0.4)',
          boxShadow: '0 12px 30px rgba(0,0,0,0.3)',
          transform: 'rotate(15deg)',
          zIndex: 3
        }}
      >
        <Box
          component="img"
          src="/img/destinations/1/3.png"
          alt="Travel Cut"
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover'
          }}
        />
      </Box>

      {/* Floating Decorative Elements */}
      <Box
        sx={{
          position: 'absolute',
          top: '20%',
          left: '180px',
          width: '80px',
          height: '80px',
          borderRadius: '50%',
          background: 'linear-gradient(135deg, rgba(76, 175, 80, 0.3), rgba(76, 175, 80, 0.1))',
          backdropFilter: 'blur(10px)',
          border: '2px solid rgba(255, 255, 255, 0.2)',
          zIndex: 3
        }}
      />

      <Box
        sx={{
          position: 'absolute',
          bottom: '25%',
          right: '15%',
          width: '60px',
          height: '60px',
          borderRadius: '50%',
          background: 'linear-gradient(135deg, rgba(249, 115, 22, 0.3), rgba(249, 115, 22, 0.1))',
          backdropFilter: 'blur(10px)',
          border: '2px solid rgba(255, 255, 255, 0.2)',
          zIndex: 3
        }}
      />

      <Container maxWidth="md" sx={{ position: 'relative', zIndex: 4 }}>
        <Box sx={{ display: 'flex', gap: 4, alignItems: 'center' }}>
          {/* Current Testimonial */}
          <Card
            sx={{
              flex: 1,
              borderRadius: '20px',
              bgcolor: 'rgba(255, 255, 255, 0.95)',
              backdropFilter: 'blur(10px)',
              p: 4,
              boxShadow: '0 25px 50px rgba(0,0,0,0.2)',
              position: 'relative',
              border: '1px solid rgba(255, 255, 255, 0.3)'
            }}
          >
            {/* Quote Icon */}
            <Box
              sx={{
                position: 'absolute',
                top: 20,
                left: 20,
                color: '#4CAF50',
                fontSize: '40px'
              }}
            >
              <FormatQuote sx={{ fontSize: 40, transform: 'scaleX(-1)' }} />
            </Box>

            <CardContent sx={{ pt: 4 }}>
              {/* Profile Section */}
              <Box sx={{ textAlign: 'center', mb: 3 }}>
                <Avatar
                  src={testimonials[currentTestimonial].image}
                  alt={testimonials[currentTestimonial].name}
                  sx={{
                    width: 80,
                    height: 80,
                    mx: 'auto',
                    mb: 2,
                    border: '4px solid #4CAF50'
                  }}
                />
                
                {/* Rating */}
                <Rating
                  value={testimonials[currentTestimonial].rating}
                  readOnly
                  sx={{
                    '& .MuiRating-icon': {
                      color: '#FF9800'
                    },
                    mb: 2
                  }}
                />
                
                <Typography
                  variant="h6"
                  sx={{
                    fontWeight: 'bold',
                    color: '#4CAF50',
                    fontSize: '20px',
                    mb: 0.5
                  }}
                >
                  {testimonials[currentTestimonial].name}
                </Typography>
                
                <Typography
                  variant="body2"
                  sx={{
                    color: '#666',
                    fontSize: '14px'
                  }}
                >
                  {testimonials[currentTestimonial].position}
                </Typography>
              </Box>

              {/* Review Text */}
              <Typography
                variant="body1"
                sx={{
                  fontStyle: 'italic',
                  color: '#666',
                  fontSize: '16px',
                  lineHeight: 1.6,
                  textAlign: 'center'
                }}
              >
                {testimonials[currentTestimonial].review}
              </Typography>
            </CardContent>
          </Card>

          {/* Next Testimonial Preview */}
          {testimonials.length > 1 && (
            <Card
              sx={{
                width: '300px',
                borderRadius: '20px',
                bgcolor: 'rgba(255, 255, 255, 0.15)',
                backdropFilter: 'blur(15px)',
                p: 2,
                border: '1px solid rgba(255, 255, 255, 0.3)',
                position: 'relative'
              }}
            >
              <CardContent>
                <Box sx={{ textAlign: 'center' }}>
                  <Avatar
                    src={testimonials[currentTestimonial + 1] ? testimonials[currentTestimonial + 1].image : testimonials[0].image}
                    alt={testimonials[currentTestimonial + 1] ? testimonials[currentTestimonial + 1].name : testimonials[0].name}
                    sx={{
                      width: 60,
                      height: 60,
                      mx: 'auto',
                      mb: 2,
                      border: '3px solid rgba(255, 255, 255, 0.4)'
                    }}
                  />
                  
                  <Box
                    sx={{
                      position: 'absolute',
                      top: 15,
                      left: 15,
                      color: '#4CAF50',
                      fontSize: '30px'
                    }}
                  >
                    <FormatQuote sx={{ fontSize: 30, transform: 'scaleX(-1)' }} />
                  </Box>
                  
                  <Typography
                    variant="body2"
                    sx={{
                      color: 'rgba(255, 255, 255, 0.9)',
                      fontSize: '14px',
                      fontStyle: 'italic',
                      lineHeight: 1.4
                    }}
                  >
                    {testimonials[currentTestimonial + 1] 
                      ? testimonials[currentTestimonial + 1].review.substring(0, 100) + '...'
                      : testimonials[0].review.substring(0, 100) + '...'
                    }
                  </Typography>
                </Box>
              </CardContent>
            </Card>
          )}
        </Box>
      </Container>
    </Box>
  );
};

export default TestimonialsRight; 