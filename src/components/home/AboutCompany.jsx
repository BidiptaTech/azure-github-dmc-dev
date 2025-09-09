import React from 'react';
import {
  Box,
  Container,
  Typography,
  Button,
  Grid,
  Card,
  CardContent,
  Stack,
  Avatar,
  Chip,
  IconButton,
  Fade,
  Grow
} from '@mui/material';
import {
  TrendingUp,
  Visibility,
  EmojiEvents,
  Star,
  ArrowForward,
  CheckCircle,
  Groups,
  LocationOn
} from '@mui/icons-material';
import { keyframes } from '@mui/system';
import { useTheme } from '@mui/material/styles';

// Animation for ferris wheel rotation
const rotateAnimation = keyframes`
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
`;

const AboutCompany = () => {
  const theme = useTheme();

  return (
    <Box sx={{ py: 10, bgcolor: '#fafafa', position: 'relative', overflow: 'hidden' }}>
      {/* Background Ferris Wheel Animation */}
      <Box
        sx={{
          position: 'absolute',
          top: '10%',
          right: '5%',
          width: '200px',
          height: '200px',
          opacity: 0.1,
          animation: `${rotateAnimation} 20s linear infinite`,
          zIndex: 1
        }}
      >
        <svg width="200" height="200" viewBox="0 0 200 200" fill="none">
          <circle cx="100" cy="100" r="80" stroke="#4CAF50" strokeWidth="3" fill="none"/>
          <circle cx="100" cy="100" r="5" fill="#4CAF50"/>
          {/* Ferris wheel spokes */}
          {Array.from({ length: 8 }).map((_, i) => {
            const angle = (i * 45) * Math.PI / 180;
            const x1 = 100 + 15 * Math.cos(angle);
            const y1 = 100 + 15 * Math.sin(angle);
            const x2 = 100 + 75 * Math.cos(angle);
            const y2 = 100 + 75 * Math.sin(angle);
            return (
              <line key={i} x1={x1} y1={y1} x2={x2} y2={y2} stroke="#4CAF50" strokeWidth="2"/>
            );
          })}
          {/* Ferris wheel cars */}
          {Array.from({ length: 8 }).map((_, i) => {
            const angle = (i * 45) * Math.PI / 180;
            const x = 100 + 75 * Math.cos(angle);
            const y = 100 + 75 * Math.sin(angle);
            return (
              <rect key={i} x={x-6} y={y-4} width="12" height="8" rx="2" fill="#4CAF50"/>
            );
          })}
        </svg>
      </Box>

      <Container maxWidth="xl">
        <Grid container spacing={6} alignItems="center">
          {/* Left Side - Images */}
          <Grid item xs={12} md={6}>
            <Box sx={{ position: 'relative', height: '600px' }}>
              {/* Main Large Image - Jumping People */}
              <Grow in={true} timeout={1000}>
                <Box
                  sx={{
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    width: '75%',
                    height: '70%',
                    borderRadius: '25px',
                    overflow: 'hidden',
                    boxShadow: '0 20px 40px rgba(0,0,0,0.15)',
                    zIndex: 3,
                    background: 'linear-gradient(135deg, #87CEEB 0%, #4682B4 100%)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center'
                  }}
                >
                  {/* Placeholder for jumping people image */}
                  <Box
                    sx={{
                      width: '100%',
                      height: '100%',
                      backgroundImage: 'url(/img/masthead/2/1.png)',
                      backgroundSize: 'cover',
                      backgroundPosition: 'center',
                      backgroundRepeat: 'no-repeat',
                      position: 'relative',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center'
                    }}
                  >
                    {/* Overlay for better text visibility */}
                    <Box
                      sx={{
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        width: '100%',
                        height: '100%',
                        background: 'linear-gradient(135deg, rgba(135, 206, 235, 0.3) 0%, rgba(70, 130, 180, 0.3) 100%)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                      }}
                    >
                      <Box sx={{ textAlign: 'center', color: 'white', textShadow: '0 2px 4px rgba(0,0,0,0.5)' }}>
                        {/* <Typography variant="h4" sx={{ fontWeight: 'bold', mb: 2, fontSize: '3rem' }}>
                          🏃‍♂️🏃‍♀️
                        </Typography> */}
                        <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                          Adventure Begins
                        </Typography>
                        <Typography variant="body2" sx={{ opacity: 1, mt: 1, color: 'white' }}>
                          Join thousands of happy travelers
                        </Typography>
                      </Box>
                    </Box>
                  </Box>
                </Box>
              </Grow>

              {/* Smaller Image - Woman in Boat */}
              <Grow in={true} timeout={1200}>
                <Box
                  sx={{
                    position: 'absolute',
                    bottom: '5%',
                    right: '5%',
                    width: '45%',
                    height: '40%',
                    borderRadius: '20px',
                    overflow: 'hidden',
                    boxShadow: '0 15px 35px rgba(0,0,0,0.15)',
                    zIndex: 3,
                    border: '4px solid white'
                  }}
                >
                  <Box
                    sx={{
                      width: '100%',
                      height: '100%',
                      backgroundImage: 'url(/img/masthead/2/2.png)',
                      backgroundSize: 'cover',
                      backgroundPosition: 'center',
                      backgroundRepeat: 'no-repeat',
                      position: 'relative'
                    }}
                  >
                    {/* Overlay with scenic water theme */}
                    <Box
                      sx={{
                        position: 'absolute',
                        bottom: 0,
                        left: 0,
                        width: '100%',
                        height: '50%',
                        background: 'linear-gradient(to top, rgba(0,0,0,0.4), transparent)',
                        display: 'flex',
                        alignItems: 'flex-end',
                        justifyContent: 'center',
                        pb: 1
                      }}
                    >
                      <Typography variant="body2" sx={{ color: 'white', fontWeight: 'bold', textShadow: '0 1px 3px rgba(0,0,0,0.5)' }}>
                        Peaceful Journeys
                      </Typography>
                    </Box>
                  </Box>
                </Box>
              </Grow>

              {/* 25 Years Experience Badge */}
              <Fade in={true} timeout={1500}>
                <Box
                  sx={{
                    position: 'absolute',
                    bottom: '25%',
                    left: '10%',
                    bgcolor: '#4CAF50',
                    borderRadius: '15px',
                    px: 3,
                    py: 2,
                    color: 'white',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 1,
                    zIndex: 4,
                    boxShadow: '0 8px 25px rgba(76, 175, 80, 0.3)',
                    transform: 'rotate(-5deg)'
                  }}
                >
                  <EmojiEvents sx={{ fontSize: 24 }} />
                  <Box>
                    <Typography variant="h5" sx={{ fontWeight: 'bold', lineHeight: 1 }}>
                      25
                    </Typography>
                    <Typography variant="caption" sx={{ lineHeight: 1, fontSize: '10px' }}>
                      Years Of Experience
                    </Typography>
                  </Box>
                </Box>
              </Fade>

              {/* Progress Circle - 14% */}
              {/* <Fade in={true} timeout={1800}>
                <Box
                  sx={{
                    position: 'absolute',
                    top: '60%',
                    right: '25%',
                    width: '60px',
                    height: '60px',
                    borderRadius: '50%',
                    border: '3px solid #4CAF50',
                    bgcolor: 'white',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 4,
                    boxShadow: '0 4px 15px rgba(0,0,0,0.1)'
                  }}
                >
                  <Typography variant="h6" sx={{ fontWeight: 'bold', color: '#4CAF50' }}>
                    14%
                  </Typography>
                </Box>
              </Fade> */}

              {/* Decorative Elements */}
              <Box
                sx={{
                  position: 'absolute',
                  top: '15%',
                  right: '15%',
                  width: '20px',
                  height: '20px',
                  borderRadius: '50%',
                  bgcolor: theme.palette.primary.main,
                  opacity: 0.6,
                  zIndex: 2
                }}
              />
              <Box
                sx={{
                  position: 'absolute',
                  bottom: '40%',
                  left: '5%',
                  width: '15px',
                  height: '15px',
                  borderRadius: '50%',
                  bgcolor: '#4CAF50',
                  opacity: 0.6,
                  zIndex: 2
                }}
              />
            </Box>
          </Grid>

          {/* Right Side - Content */}
          <Grid item xs={12} md={6}>
            <Box sx={{ pl: { md: 4 } }}>
              <Fade in={true} timeout={800}>
                <Chip
                  label="ABOUT OUR COMPANY"
                  sx={{
                    bgcolor: 'rgba(249, 115, 22, 0.1)',
                    color: theme.palette.primary.main,
                    fontWeight: 'bold',
                    fontSize: '12px',
                    mb: 3,
                    px: 2
                  }}
                />
              </Fade>

              <Fade in={true} timeout={1000}>
                <Typography
                  variant="h3"
                  sx={{
                    fontWeight: 'bold',
                    mb: 3,
                    color: '#333',
                    lineHeight: 1.2,
                    fontSize: { xs: '2rem', md: '2.5rem' }
                  }}
                >
                  Get The Best Travel Experience With{' '}
                  <Box component="span" sx={{ color: '#4CAF50' }}>
                    Travclicks
                  </Box>
                </Typography>
              </Fade>

              <Fade in={true} timeout={1200}>
                <Typography
                  variant="body1"
                  sx={{
                    color: '#666',
                    mb: 4,
                    lineHeight: 1.8,
                    fontSize: '16px'
                  }}
                >
                  Available, But The Majority Have Suffered Alteration In Some Form By Injected Humour, Or Randomised Words Which Don't Look Even Slightly Believable.
                </Typography>
              </Fade>

              <Grid container spacing={3} sx={{ mb: 4 }}>
                <Grid item xs={12} sm={6}>
                  <Fade in={true} timeout={1400}>
                    <Card
                      elevation={0}
                      sx={{
                        p: 3,
                        bgcolor: 'rgba(249, 115, 22, 0.05)',
                        border: '1px solid rgba(249, 115, 22, 0.1)',
                        borderRadius: '15px',
                        height: '100%'
                      }}
                    >
                      <CheckCircle sx={{ color: theme.palette.primary.main, mb: 2, fontSize: 30 }} />
                      <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 1, color: '#333' }}>
                        Trusted Travel Guide
                      </Typography>
                      <Typography variant="body2" sx={{ color: '#666', lineHeight: 1.6 }}>
                        Expert guides with years of experience
                      </Typography>
                    </Card>
                  </Fade>
                </Grid>
                <Grid item xs={12} sm={6}>
                  <Fade in={true} timeout={1600}>
                    <Card
                      elevation={0}
                      sx={{
                        p: 3,
                        bgcolor: 'rgba(76, 175, 80, 0.05)',
                        border: '1px solid rgba(76, 175, 80, 0.1)',
                        borderRadius: '15px',
                        height: '100%'
                      }}
                    >
                      <Visibility sx={{ color: '#4CAF50', mb: 2, fontSize: 30 }} />
                      <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 1, color: '#333' }}>
                        Mission & Vision
                      </Typography>
                      <Typography variant="body2" sx={{ color: '#666', lineHeight: 1.6 }}>
                        Creating unforgettable travel experiences
                      </Typography>
                    </Card>
                  </Fade>
                </Grid>
              </Grid>

              <Fade in={true} timeout={1800}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 3, mb: 4 }}>
                  <Button
                    variant="contained"
                    endIcon={<ArrowForward />}
                    sx={{
                      bgcolor: theme.palette.primary.main,
                      color: 'white',
                      px: 4,
                      py: 1.5,
                      borderRadius: '50px',
                      fontWeight: 'bold',
                      textTransform: 'none',
                      boxShadow: '0 8px 25px rgba(249, 115, 22, 0.3)',
                      '&:hover': {
                        bgcolor: '#e5630a',
                        transform: 'translateY(-2px)',
                        boxShadow: '0 12px 30px rgba(249, 115, 22, 0.4)'
                      }
                    }}
                  >
                    Discover More
                  </Button>
                </Box>
              </Fade>
{/* 
              <Fade in={true} timeout={2000}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 3 }}>
                  <Avatar
                    src="/img/avatars/1.png"
                    sx={{
                      width: 50,
                      height: 50,
                      border: '3px solid white',
                      boxShadow: '0 4px 15px rgba(0,0,0,0.1)'
                    }}
                  />
                  <Box>
                    <Typography variant="body1" sx={{ fontWeight: 'bold', color: '#333' }}>
                      Ronald Richards
                    </Typography>
                    <Typography variant="body2" sx={{ color: '#666' }}>
                      Founder
                    </Typography>
                  </Box>
                </Box>
              </Fade> */}
            </Box>
          </Grid>
        </Grid>
      </Container>
    </Box>
  );
};

export default AboutCompany;