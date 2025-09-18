import React from 'react';
import {
  Box,
  Container,
  Typography,
  Grid,
  Card,
  CardContent,
  Stack,
  Fade,
  Grow
} from '@mui/material';
import {
  TravelExplore,
  EmojiPeople,
  EmojiEvents,
  StarRate,
  FlightTakeoff,
  Luggage
} from '@mui/icons-material';
import { keyframes } from '@mui/system';

// Animation for the travel journey badge
const pulseAnimation = keyframes`
  0% {
    transform: scale(1) rotate(-10deg);
    box-shadow: 0 15px 40px rgba(249, 115, 22, 0.4), 0 5px 15px rgba(255, 165, 0, 0.3);
  }
  25% {
    transform: scale(1.08) rotate(-8deg);
    box-shadow: 0 20px 50px rgba(249, 115, 22, 0.6), 0 8px 20px rgba(255, 165, 0, 0.4);
  }
  50% {
    transform: scale(1.15) rotate(-12deg);
    box-shadow: 0 25px 60px rgba(249, 115, 22, 0.8), 0 10px 25px rgba(255, 165, 0, 0.5);
  }
  75% {
    transform: scale(1.08) rotate(-8deg);
    box-shadow: 0 20px 50px rgba(249, 115, 22, 0.6), 0 8px 20px rgba(255, 165, 0, 0.4);
  }
  100% {
    transform: scale(1) rotate(-10deg);
    box-shadow: 0 15px 40px rgba(249, 115, 22, 0.4), 0 5px 15px rgba(255, 165, 0, 0.3);
  }
`;

// Text animation for individual words
const textGlowAnimation = keyframes`
  0% {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.4), 0 0 10px rgba(255, 255, 255, 0.3);
  }
  50% {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.4), 0 0 20px rgba(255, 255, 255, 0.6);
  }
  100% {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.4), 0 0 10px rgba(255, 255, 255, 0.3);
  }
`;

// Floating animation for decorative elements
const floatAnimation = keyframes`
  0% {
    transform: translateY(0px) rotate(0deg);
    opacity: 0.7;
  }
  50% {
    transform: translateY(-10px) rotate(5deg);
    opacity: 1;
  }
  100% {
    transform: translateY(0px) rotate(0deg);
    opacity: 0.7;
  }
`;

const StatsSection = () => {
  const stats = [
    {
      id: 1,
      number: "30k+",
      label: "Tours Success",
      icon: <TravelExplore sx={{ fontSize: 30, color: '#4CAF50' }} />
    },
    {
      id: 2,
      number: "6,500+",
      label: "Happy Traveler",
      icon: <EmojiPeople sx={{ fontSize: 30, color: '#4CAF50' }} />
    },
    {
      id: 3,
      number: "6,561+",
      label: "Awards Winning",
      icon: <EmojiEvents sx={{ fontSize: 30, color: '#4CAF50' }} />
    },
    {
      id: 4,
      number: "25+",
      label: "Our Experience",
      icon: <StarRate sx={{ fontSize: 30, color: '#4CAF50' }} />
    }
  ];

  return (
    <Box 
      sx={{ 
        py: 2, 
        background: `url('/Images/landingpage/bgimg2.jpg') no-repeat center center`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        position: 'relative',
        overflow: 'hidden',
        minHeight: '600px',
        display: 'flex',
        alignItems: 'center',
        borderRadius: '30px',
        margin: '0 auto',
        maxWidth: '95%'
      }}
    >
      {/* Dark overlay for better content visibility */}
      <Box
        sx={{
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
         // background: 'linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.5) 100%)',
          zIndex: 1,
          borderRadius: '30px'
        }}
      />

      {/* Reduce and adjust background travel elements for better contrast */}
      <Box
        sx={{
          position: 'absolute',
          top: '10%',
          left: '5%',
          fontSize: '60px',
          opacity: 0.15,
          zIndex: 2,
          filter: 'brightness(200%) contrast(150%)',
          color: 'rgba(255, 255, 255, 0.8)'
        }}
      >
        🗽
      </Box>
      
      <Box
        sx={{
          position: 'absolute',
          top: '20%',
          right: '12%',
          fontSize: '50px',
          opacity: 0.15,
          zIndex: 2,
          filter: 'brightness(200%) contrast(150%)',
          color: 'rgba(255, 255, 255, 0.8)'
        }}
      >
        🗼
      </Box>
      
      <Box
        sx={{
          position: 'absolute',
          top: '30%',
          right: '30%',
          fontSize: '35px',
          opacity: 0.2,
          zIndex: 2,
          animation: `${pulseAnimation} 3s ease-in-out infinite`,
          filter: 'brightness(200%)',
          color: 'rgba(255, 255, 255, 0.9)'
        }}
      >
        ✈️
      </Box>
      
      <Box
        sx={{
          position: 'absolute',
          bottom: '30%',
          right: '18%',
          fontSize: '45px',
          opacity: 0.15,
          zIndex: 2,
          filter: 'brightness(200%) contrast(150%)',
          color: 'rgba(255, 255, 255, 0.8)'
        }}
      >
        🧳
      </Box>
      
      <Box
        sx={{
          position: 'absolute',
          bottom: '40%',
          right: '10%',
          fontSize: '30px',
          opacity: 0.2,
          zIndex: 2,
          transform: 'rotate(15deg)',
          filter: 'brightness(200%)',
          color: 'rgba(255, 255, 255, 0.8)'
        }}
      >
        📄
      </Box>

      <Container maxWidth="xl" sx={{ position: 'relative', zIndex: 3 }}>
        <Grid container spacing={6} alignItems="center" sx={{ minHeight: '500px' }}>
          {/* Left Side - Stats Card */}
          <Grid item xs={12} md={6}>
            <Fade in={true} timeout={1000}>
              <Card
                sx={{
                  borderRadius: '25px',
                  boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
                  bgcolor: 'rgba(255, 255, 255, 0.98)',
                  backdropFilter: 'blur(15px)',
                  border: '2px solid rgba(255, 255, 255, 0.3)',
                  p: 3,
                  maxWidth: '480px',
                  mx: { xs: 'auto', md: 0 }
                }}
              >
                <CardContent sx={{ p: 3 }}>
                  <Grid container spacing={3}>
                    {stats.map((stat, index) => (
                      <Grid item xs={6} key={stat.id}>
                        <Grow in={true} timeout={1200 + index * 200}>
                          <Box
                            sx={{
                              textAlign: 'center',
                              py: 2.5,
                              px: 2,
                              borderRadius: '15px',
                              transition: 'transform 0.3s ease',
                              '&:hover': {
                                transform: 'translateY(-5px)',
                                boxShadow: '0 8px 25px rgba(0,0,0,0.1)'
                              }
                            }}
                          >
                            <Box
                              sx={{
                                mb: 2,
                                display: 'flex',
                                justifyContent: 'center'
                              }}
                            >
                              {stat.icon}
                            </Box>
                            
                            <Typography
                              variant="h3"
                              sx={{
                                fontWeight: 'bold',
                                color: '#333',
                                fontSize: { xs: '1.6rem', md: '2rem' },
                                mb: 1
                              }}
                            >
                              {stat.number}
                            </Typography>
                            
                            <Typography
                              variant="body1"
                              sx={{
                                color: '#666',
                                fontWeight: 500,
                                fontSize: '13px'
                              }}
                            >
                              {stat.label}
                            </Typography>
                          </Box>
                        </Grow>
                      </Grid>
                    ))}
                  </Grid>
                </CardContent>
              </Card>
            </Fade>
          </Grid>

          {/* Right Side - Travel is a Journey Animation */}
          <Grid item xs={12} md={6}>
            <Box
              sx={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                position: 'relative',
                height: '400px'
              }}
            >
              {/* Travel is a Journey Badge */}
              <Fade in={true} timeout={1500}>
                <Box
                  sx={{
                    width: 280,
                    height: 280,
                    borderRadius: '50%',
                    background: 'linear-gradient(135deg, #f97316 0%, #ff8c00 50%, #ff7b00 100%)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 4,
                    transform: 'rotate(-10deg)',
                    animation: `${pulseAnimation} 3s ease-in-out infinite`,
                    position: 'relative',
                    border: '4px solid rgba(255, 255, 255, 0.6)',
                    '&::before': {
                      content: '""',
                      position: 'absolute',
                      top: '-8px',
                      left: '-8px',
                      right: '-8px',
                      bottom: '-8px',
                      borderRadius: '50%',
                      background: 'linear-gradient(135deg, rgba(249, 115, 22, 0.3), rgba(255, 165, 0, 0.3))',
                      zIndex: -1
                    }
                  }}
                >
                  <Box sx={{ textAlign: 'center', color: 'white', transform: 'rotate(10deg)' }}>
                    <Typography 
                      variant="h4" 
                      sx={{ 
                        fontWeight: 'bold',
                        fontFamily: '"Brush Script MT", cursive, "Arial Black", sans-serif',
                        fontSize: '32px', 
                        lineHeight: 1.1,
                        animation: `${textGlowAnimation} 2s ease-in-out infinite`,
                        letterSpacing: '1px',
                        mb: 0.5
                      }}
                    >
                      TRAVEL
                    </Typography>
                    <Typography 
                      variant="body1" 
                      sx={{ 
                        fontSize: '18px', 
                        fontStyle: 'italic',
                        fontFamily: '"Brush Script MT", cursive, sans-serif',
                        my: 1,
                        textShadow: '1px 1px 2px rgba(0,0,0,0.5)',
                        opacity: 0.9
                      }}
                    >
                      - is a -
                    </Typography>
                    <Typography 
                      variant="h3" 
                      sx={{ 
                        fontWeight: 'bold',
                        fontFamily: '"Brush Script MT", cursive, "Arial Black", sans-serif',
                        fontSize: '36px',
                        fontStyle: 'italic',
                        animation: `${textGlowAnimation} 2s ease-in-out infinite 0.5s`,
                        lineHeight: 1.1,
                        letterSpacing: '1px'
                      }}
                    >
                      Journey
                    </Typography>
                  </Box>

                  {/* Enhanced decorative elements around the badge */}
                  <Box
                    sx={{
                      position: 'absolute',
                      top: '-25px',
                      right: '-25px',
                      width: '50px',
                      height: '50px',
                      borderRadius: '50%',
                      background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.6))',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: '24px',
                      boxShadow: '0 6px 20px rgba(0,0,0,0.3)',
                      animation: `${floatAnimation} 3s ease-in-out infinite`
                    }}
                  >
                    ⭐
                  </Box>

                  <Box
                    sx={{
                      position: 'absolute',
                      bottom: '-20px',
                      left: '-20px',
                      width: '40px',
                      height: '40px',
                      borderRadius: '50%',
                      background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.6))',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: '20px',
                      boxShadow: '0 6px 20px rgba(0,0,0,0.3)',
                      animation: `${floatAnimation} 3s ease-in-out infinite 1s`
                    }}
                  >
                    ✨
                  </Box>

                  <Box
                    sx={{
                      position: 'absolute',
                      top: '15%',
                      left: '-30px',
                      width: '35px',
                      height: '35px',
                      borderRadius: '50%',
                      background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.5))',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: '16px',
                      boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
                      animation: `${floatAnimation} 3s ease-in-out infinite 2s`
                    }}
                  >
                    💫
                  </Box>
                </Box>
              </Fade>

            
              {/* Floating travel elements around the badge */}
              <Box
                sx={{
                  position: 'absolute',
                  top: '15%',
                  left: '5%',
                  fontSize: '45px',
                  opacity: 0.8,
                  animation: `${floatAnimation} 4s ease-in-out infinite`,
                  animationDelay: '0.5s',
                  filter: 'drop-shadow(3px 3px 6px rgba(0,0,0,0.4))',
                  transition: 'transform 0.3s ease',
                  '&:hover': {
                    transform: 'scale(1.2)',
                    opacity: 1
                  }
                }}
              >
                🌍
              </Box>

              <Box
                sx={{
                  position: 'absolute',
                  bottom: '25%',
                  right: '15%',
                  fontSize: '40px',
                  opacity: 0.8,
                  animation: `${floatAnimation} 5s ease-in-out infinite`,
                  animationDelay: '1.5s',
                  filter: 'drop-shadow(3px 3px 6px rgba(0,0,0,0.4))',
                  transition: 'transform 0.3s ease',
                  '&:hover': {
                    transform: 'scale(1.2)',
                    opacity: 1
                  }
                }}
              >
                🎯
              </Box>

              <Box
                sx={{
                  position: 'absolute',
                  top: '8%',
                  right: '2%',
                  fontSize: '38px',
                  opacity: 0.8,
                  animation: `${floatAnimation} 6s ease-in-out infinite`,
                  animationDelay: '2.5s',
                  filter: 'drop-shadow(3px 3px 6px rgba(0,0,0,0.4))',
                  transition: 'transform 0.3s ease',
                  '&:hover': {
                    transform: 'scale(1.2)',
                    opacity: 1
                  }
                }}
              >
                🏆
              </Box>

              <Box
                sx={{
                  position: 'absolute',
                  bottom: '8%',
                  left: '8%',
                  fontSize: '35px',
                  opacity: 0.8,
                  animation: `${floatAnimation} 4.5s ease-in-out infinite`,
                  animationDelay: '3s',
                  filter: 'drop-shadow(3px 3px 6px rgba(0,0,0,0.4))',
                  transition: 'transform 0.3s ease',
                  '&:hover': {
                    transform: 'scale(1.2)',
                    opacity: 1
                  }
                }}
              >
                🗺️
              </Box>
            </Box>
          </Grid>
        </Grid>
      </Container>
    </Box>
  );
};

export default StatsSection; 