import React from 'react';
import {
  AppBar,
  Toolbar,
  Typography,
  Button,
  Box,
  Container,
  Link,
  IconButton,
  Stack,
  Divider
} from '@mui/material';
import {
  Phone,
  Email,
  LocationOn,
  Facebook,
  Twitter,
  LinkedIn,
  YouTube,
  Send
} from '@mui/icons-material';
import { Link as RouterLink } from 'react-router-dom';

const LandingNavbar = () => {
  return (
    <Box>
      {/* Top Contact Bar */}
      {/* <Box sx={{ 
        bgcolor: 'rgba(0, 0, 0, 0.8)', 
        color: 'white',
        py: 1,
        fontSize: '14px'
      }}>
        <Container maxWidth="xl">
          <Box sx={{ 
            display: 'flex', 
            justifyContent: 'space-between', 
            alignItems: 'center',
            flexWrap: 'wrap'
          }}>
           
            <Stack direction="row" spacing={3} sx={{ alignItems: 'center' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <Phone sx={{ fontSize: 16, color: '#f97316' }} />
                <Typography variant="body2">+6108-666-0112</Typography>
              </Box>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <Email sx={{ fontSize: 16, color: '#f97316' }} />
                <Typography variant="body2">info@gmail.com</Typography>
              </Box>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <LocationOn sx={{ fontSize: 16, color: '#f97316' }} />
                <Typography variant="body2">184 Main Collins Street Victoria 8007</Typography>
              </Box>
            </Stack>

            <Stack direction="row" spacing={1}>
              <IconButton size="small" sx={{ color: 'white', '&:hover': { color: '#f97316' } }}>
                <Facebook sx={{ fontSize: 16 }} />
              </IconButton>
              <IconButton size="small" sx={{ color: 'white', '&:hover': { color: '#f97316' } }}>
                <Twitter sx={{ fontSize: 16 }} />
              </IconButton>
              <IconButton size="small" sx={{ color: 'white', '&:hover': { color: '#f97316' } }}>
                <LinkedIn sx={{ fontSize: 16 }} />
              </IconButton>
              <IconButton size="small" sx={{ color: 'white', '&:hover': { color: '#f97316' } }}>
                <YouTube sx={{ fontSize: 16 }} />
              </IconButton>
            </Stack>
          </Box>
        </Container>
      </Box> */}

      {/* Main Navigation */}
      <AppBar 
        position="static" 
        sx={{ 
          bgcolor: 'white', 
          boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
          py: 1
        }}
      >
        <Container maxWidth="xl">
          <Toolbar sx={{ justifyContent: 'space-between', minHeight: '70px !important' }}>
            {/* Logo */}
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', mr: 2 }}>
                <Box
                  component="img"
                  src="Images/travclicklogo.jpeg"
                  alt="TravClicks Logo"
                  sx={{
                    width: 300,
                    height: 50,
                    objectFit: 'contain'
                  }}
                />
              </Box>
             
            </Box>

            {/* Navigation Menu */}
            <Box sx={{ display: { xs: 'none', md: 'flex' }, alignItems: 'center' }}>
              <Stack direction="row" spacing={4} sx={{ alignItems: 'center' }}>
                <Link 
                  component={RouterLink} 
                  to="/" 
                  sx={{ 
                    color: '#333',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#f97316' }
                  }}
                >
                  Home
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#" 
                  sx={{ 
                    color: '#333',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#f97316' }
                  }}
                >
                  About Us
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#" 
                  sx={{ 
                    color: '#333',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#f97316' }
                  }}
                >
                  Destination
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#" 
                  sx={{ 
                    color: '#333',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#f97316' }
                  }}
                >
                  Tours List
                </Link>
                <Button
                  component={RouterLink}
                  to="/login"
                  variant="outlined"
                  sx={{
                    color: '#f97316',
                    borderColor: '#f97316',
                    borderRadius: '8px',
                    fontWeight: 600,
                    fontSize: '16px',
                    textTransform: 'none',
                    px: 2.5,
                    '&:hover': {
                      backgroundColor: '#f97316',
                      color: 'white',
                      borderColor: '#f97316'
                    }
                  }}
                >
                  Login
                </Button>
                {/* <Link 
                  component={RouterLink} 
                  to="/pages" 
                  sx={{ 
                    color: '#333',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#f97316' }
                  }}
                >
                  Pages
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/shop" 
                  sx={{ 
                    color: '#333',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#f97316' }
                  }}
                >
                  Shop
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/news" 
                  sx={{ 
                    color: '#333',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#f97316' }
                  }}
                >
                  News
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/contact" 
                  sx={{ 
                    color: '#333',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#f97316' }
                  }}
                >
                  Contact
                </Link> */}
              </Stack>
            </Box>

            {/* Get In Touch Button */}
            <Button
              variant="contained"
              endIcon={<Send />}
              sx={{
                bgcolor: '#f97316',
                color: 'white',
                borderRadius: '25px',
                px: 3,
                py: 1,
                fontWeight: 600,
                fontSize: '14px',
                textTransform: 'none',
                '&:hover': {
                  bgcolor: '#e5630a'
                }
              }}
            >
              Get In Touch
            </Button>
          </Toolbar>
        </Container>
      </AppBar>
    </Box>
  );
};

export default LandingNavbar; 