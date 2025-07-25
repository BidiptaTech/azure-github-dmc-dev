import React from 'react';
import {
  Box,
  Container,
  Typography,
  Grid,
  Card,
  CardContent,
  Stack
} from '@mui/material';
import {
  VerifiedUser,
  CalendarMonth,
  Public,
  Paragliding
} from '@mui/icons-material';
import { keyframes } from '@mui/system';

// Animation for gas balloon
const floatAnimation = keyframes`
  0% {
    transform: translateY(0px) rotate(-15deg);
  }
  50% {
    transform: translateY(-20px) rotate(-10deg);
  }
  100% {
    transform: translateY(0px) rotate(-15deg);
  }
`;

const WhyChooseUs = () => {
  const features = [
    {
      id: 1,
      icon: <VerifiedUser sx={{ fontSize: 40, color: '#4CAF50' }} />,
      title: "Trusted Travel Guide",
      description: "Professional and experienced guides"
    },
    {
      id: 2,
      icon: <CalendarMonth sx={{ fontSize: 40, color: '#4CAF50' }} />,
      title: "Instant Booking",
      description: "Book your trip instantly online"
    },
    {
      id: 3,
      icon: <Public sx={{ fontSize: 40, color: '#4CAF50' }} />,
      title: "World Class Travel",
      description: "Premium travel experiences worldwide"
    },
    {
      id: 4,
      icon: <Paragliding sx={{ fontSize: 40, color: '#4CAF50' }} />,
      title: "Paragliding Tour",
      description: "Adventure activities and tours"
    }
  ];

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

      {/* Animated Gas Balloon */}
      <Box
        sx={{
          position: 'absolute',
          bottom: '25%',
          left: '8%',
          zIndex: 2,
          animation: `${floatAnimation} 4s ease-in-out infinite`
        }}
      >
        <svg width="80" height="120" viewBox="0 0 80 120">
          {/* Balloon strings */}
          <line x1="15" y1="80" x2="25" y2="110" stroke="#8B4513" strokeWidth="1"/>
          <line x1="25" y1="80" x2="25" y2="110" stroke="#8B4513" strokeWidth="1"/>
          <line x1="35" y1="80" x2="25" y2="110" stroke="#8B4513" strokeWidth="1"/>
          <line x1="45" y1="80" x2="25" y2="110" stroke="#8B4513" strokeWidth="1"/>
          <line x1="55" y1="80" x2="25" y2="110" stroke="#8B4513" strokeWidth="1"/>
          <line x1="65" y1="80" x2="25" y2="110" stroke="#8B4513" strokeWidth="1"/>
          
          {/* Balloon gradient */}
          <defs>
            <radialGradient id="balloonGradient" cx="30%" cy="30%">
              <stop offset="0%" stopColor="#FFD700" />
              <stop offset="50%" stopColor="#FF8C00" />
              <stop offset="100%" stopColor="#FF4500" />
            </radialGradient>
          </defs>
          
          {/* Main balloon */}
          <ellipse cx="40" cy="40" rx="35" ry="45" fill="url(#balloonGradient)" stroke="#333" strokeWidth="1"/>
          
          {/* Balloon highlight */}
          <ellipse cx="30" cy="25" rx="12" ry="15" fill="rgba(255,255,255,0.4)"/>
          
          {/* Basket */}
          <rect x="20" y="110" width="10" height="8" fill="#8B4513" stroke="#654321" strokeWidth="1"/>
          
          {/* Basket details */}
          <line x1="20" y1="113" x2="30" y2="113" stroke="#654321" strokeWidth="0.5"/>
          <line x1="20" y1="116" x2="30" y2="116" stroke="#654321" strokeWidth="0.5"/>
        </svg>
      </Box>

      <Container maxWidth="xl">
        <Grid container spacing={3} alignItems="center">
          {/* Left Side - Images */}
          <Grid item xs={12} md={6}>
            <Box sx={{ position: 'relative', height: '600px' }}>
              {/* Top Left Image - Castle */}
              <Box
                sx={{
                  position: 'absolute',
                  top: 0,
                  left: 0,
                  width: '280px',
                  height: '280px',
                  borderRadius: '30%',
                  overflow: 'hidden',
                  boxShadow: '0 15px 35px rgba(0,0,0,0.2)',
                  zIndex: 2
                }}
              >
                <Box
                  component="img"
                  src="/img/masthead/2/1.png"
                  alt="Castle"
                  sx={{
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover',
                    transition: 'transform 0.3s ease',
                    '&:hover': {
                      transform: 'scale(1.05)'
                    }
                  }}
                />
              </Box>

              {/* Bottom Left Image - Woman in Yellow */}
              <Box
                sx={{
                  position: 'absolute',
                  bottom: 0,
                  left: 0,
                  width: '280px',
                  height: '280px',
                  borderRadius: '50%',
                  overflow: 'hidden',
                  boxShadow: '0 15px 35px rgba(0,0,0,0.2)',
                  zIndex: 2
                }}
              >
                <Box
                  component="img"
                  src="/img/masthead/2/2.png"
                  alt="Woman in Yellow Dress"
                  sx={{
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover',
                    transition: 'transform 0.3s ease',
                    '&:hover': {
                      transform: 'scale(1.05)'
                    }
                  }}
                />
              </Box>

              {/* Right Side Image - Eiffel Tower */}
              <Box
                sx={{
                  position: 'absolute',
                  top: '50%',
                  right: 0,
                  transform: 'translateY(-50%)',
                  width: '320px',
                  height: '600px',
                  borderRadius: '10%',
                  overflow: 'hidden',
                  boxShadow: '0 20px 40px rgba(0,0,0,0.2)',
                  zIndex: 3
                }}
              >
                <Box
                  component="img"
                  src="/img/masthead/1/bg.webp"
                  alt="Eiffel Tower"
                  sx={{
                    borderRadius: '10%',
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover',
                    transition: 'transform 0.3s ease',
                    '&:hover': {
                      transform: 'scale(1.05)'
                    }
                  }}
                />
              </Box>
            </Box>
          </Grid>

          {/* Right Side - Content */}
          <Grid item xs={12} md={6}>
            <Stack spacing={4}>
              {/* Header */}
              <Box>
                <Typography
                  variant="h6"
                  sx={{
                    color: '#f97316',
                    fontWeight: 600,
                    fontSize: '18px',
                    mb: 2
                  }}
                >
                  Why Choose Us
                </Typography>
                
                <Typography
                  variant="h2"
                  sx={{
                    fontSize: { xs: '2rem', md: '2.5rem', lg: '3rem' },
                    fontWeight: 'bold',
                    color: '#333',
                    lineHeight: 1.3,
                    mb: 3
                  }}
                >
                  Get The <span style={{ color: '#4CAF50' }}>Best Travel</span><br />
                  Experience With Travclicks
                </Typography>
                
                <Typography
                  variant="body1"
                  sx={{
                    color: '#666',
                    fontSize: '16px',
                    lineHeight: 1.6,
                    mb: 4
                  }}
                >
                  It is a long established fact that a reader will be distracted by the readable 
                  content of a page when looking at its layout the point.
                </Typography>
              </Box>

              {/* Features Grid */}
              <Grid container spacing={3}>
                {features.map((feature) => (
                  <Grid item xs={12} md={6} key={feature.id}>
                    <Card
                      elevation={0}
                      sx={{
                        bgcolor: 'transparent',
                        border: 'none',
                        '&:hover': {
                          transform: 'translateY(-2px)',
                          transition: 'transform 0.3s ease'
                        }
                      }}
                    >
                      <CardContent sx={{ p: 0 }}>
                        <Box sx={{ display: 'flex', alignItems: 'flex-start', gap: 2 }}>
                          <Box
                            sx={{
                              bgcolor: '#E8F5E8',
                              borderRadius: '12px',
                              p: 1.5,
                              display: 'flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              minWidth: 60
                            }}
                          >
                            {feature.icon}
                          </Box>
                          <Box sx={{ flex: 1 }}>
                            <Typography
                              variant="h6"
                              sx={{
                                fontWeight: 'bold',
                                color: '#333',
                                fontSize: '16px',
                                mb: 0.5
                              }}
                            >
                              {feature.title}
                            </Typography>
                            <Typography
                              variant="body2"
                              sx={{
                                color: '#666',
                                fontSize: '14px',
                                lineHeight: 1.5
                              }}
                            >
                              {feature.description}
                            </Typography>
                          </Box>
                        </Box>
                      </CardContent>
                    </Card>
                  </Grid>
                ))}
              </Grid>
            </Stack>
          </Grid>
        </Grid>
      </Container>
    </Box>
  );
};

export default WhyChooseUs; 