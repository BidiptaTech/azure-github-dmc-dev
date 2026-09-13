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
  Divider,
  Drawer,
  List as MuiList,
  ListItem,
  ListItemText
} from '@mui/material';
import {
  Phone,
  Email,
  LocationOn,
  Facebook,
  Twitter,
  LinkedIn,
  YouTube,
  Send,
  Menu,
  Home,
  Info,
  Explore,
  List,
  Close
} from '@mui/icons-material';
import { Link as RouterLink } from 'react-router-dom';

const LandingNavbar = () => {
  const [mobileOpen, setMobileOpen] = React.useState(false);

  const handleDrawerToggle = () => {
    setMobileOpen(!mobileOpen);
  };

  const menuItems = [
    { text: 'Home', icon: <Home />, path: '/' },
    { text: 'About Us', icon: <Info />, path: '/#about' },
    { text: 'Destination', icon: <Explore />, path: '/#destinations' },
    { text: 'Tours List', icon: <List />, path: '/#tours' },
    { text: 'Login', icon: <Send />, path: '/login', isButton: true, variant: 'outlined' },
    // { text: 'Register as Agent', icon: <Send />, path: '/register', isButton: true, variant: 'contained' }
  ];

  const drawer = (
    <Box sx={{ 
      width: 280, 
      height: '100%',
      bgcolor: '#f8f9fa',
      display: 'flex',
    
      flexDirection: 'column',
      overflow: 'hidden',
    }}>
      {/* Header */}
      <Box sx={{ 
        bgcolor: '#f4f7fb', 
         p: 2.8,
        borderBottom: '1px solid #dce4f0',
        display: 'flex', 
        alignItems: 'center', 
        justifyContent: 'space-between' 
      }}>
        <Box
          component="img"
          src="/Images/logo.png"
          alt="TravClicks Logo"
          sx={{
            width: 170,
            height: 44,
            objectFit: 'contain',
          }}
        />
        <IconButton 
          onClick={handleDrawerToggle}
          sx={{ color: '#13357b' }}
        >
          <Close />
        </IconButton>
      </Box>

      {/* Navigation Items */}
      <MuiList sx={{ flex: 1, pt: 0,pr: 2 }}>
        {menuItems.map((item, index) => (
          <ListItem 
            key={item.text} 
            component={item.isButton ? 'div' : RouterLink}
            to={item.isButton ? undefined : item.path}
            onClick={item.isButton ? undefined : handleDrawerToggle}
            sx={{
              mx: 1,
              mb: 0,
              borderRadius: '8px',
              cursor: item.isButton ? 'default' : 'pointer',
              transition: 'all 0.3s ease',
              '&:hover': {
                bgcolor: item.isButton ? 'transparent' : '#e8f5e8',
                transform: item.isButton ? 'none' : 'translateX(5px)'
              }
            }}
          >
            {item.isButton ? (
              <Button
                component={RouterLink}
                to={item.path}
                variant={item.variant}
                fullWidth
                startIcon={item.icon}
                onClick={handleDrawerToggle}
                sx={{
                  color: item.variant === 'outlined' ? '#13357b' : 'white',
                  borderColor: item.variant === 'outlined' ? '#13357b' : 'transparent',
                  bgcolor: item.variant === 'contained' ? '#13357b' : 'transparent',
                  borderRadius: '8px',
                  fontWeight: 600,
                  py: 1.5,
                  textTransform: 'none',
                  '&:hover': {
                    backgroundColor: item.variant === 'outlined' ? '#13357b' : '#45a049',
                    color: 'white',
                    borderColor: item.variant === 'outlined' ? '#13357b' : 'transparent'
                  }
                }}
              >
                {item.text}
              </Button>
            ) : (
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                gap: 2,
                width: '100%',
                py: 1
              }}>
                <Box sx={{ 
                  color: '#13357b',
                  display: 'flex',
                  alignItems: 'center'
                }}>
                  {item.icon}
                </Box>
                <ListItemText 
                  primary={item.text}
                  sx={{ 
                    '& .MuiListItemText-primary': {
                      color: '#333',
                      fontWeight: 500,
                      fontSize: '16px'
                    }
                  }}
                />
              </Box>
            )}
          </ListItem>
        ))}
      </MuiList>

    </Box>
  );

  return (
    <Box>
      
      <AppBar 
        position="static" 
        elevation={0}
        sx={{ 
          bgcolor: '#f4f7fb', 
          borderBottom: '1px solid #dce4f0',
          boxShadow: 'none',
          py: 1
        }}
      >
        <Container maxWidth="xl" >
          <Toolbar sx={{ justifyContent: 'space-between', minHeight: '78px !important', color: '#13357b' }}>
            {/* Logo */}
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <Box
                component="img"
                src="/Images/logo.png"
                alt="TravClicks Logo"
                sx={{
                  width: { xs: 200, sm: 240, md: 280 },
                  height: { xs: 52, sm: 58, md: 66 },
                  objectFit: 'contain',
                }}
              />
            </Box>

            {/* Navigation Menu */}
            <Box sx={{ display: { xs: 'none', md: 'flex' }, alignItems: 'center', }}>
              <Stack direction="row" spacing={4} sx={{ alignItems: 'center' }}>
                <Link 
                  component={RouterLink} 
                  to="/" 
                  sx={{ 
                   color: '#13357b',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#0f2d6b' }
                  }}
                >
                  Home
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#" 
                  sx={{ 
                    color: '#13357b',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#0f2d6b' }
                  }}
                >
                  About Us
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#destinations" 
                  sx={{ 
                    color: '#13357b',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#0f2d6b' }
                  }}
                >
                  Destination
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#tours" 
                  sx={{ 
                    color: '#13357b',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#0f2d6b' }
                  }}
                >
                  Tours List
                </Link>
                <Button
                  component={RouterLink}
                  to="/login"
                  variant="contained"
                  sx={{
                    bgcolor: '#13357b',
                    color: 'white',
                    borderRadius: '6px',
                    fontWeight: 600,
                    fontSize: '16px',
                    textTransform: 'none',
                    px: 2.5,
                    boxShadow: 'none',
                    '&:hover': {
                      backgroundColor: '#0f2d6b',
                      boxShadow: 'none',
                    }
                  }}
                >
                  Login
                </Button>
                
              
              </Stack>
            </Box>

            {/* Mobile Menu Button */}
            <IconButton
              aria-label="open drawer"
              edge="start"
              onClick={handleDrawerToggle}
              sx={{ 
                display: { xs: 'block', md: 'none' },
                color: '#13357b',
                bgcolor: 'transparent',
                '&:hover': {
                  bgcolor: '#e8eef6'
                }
              }}
            >
              <Menu />
            </IconButton>

            {/* Get In Touch Button - Hidden on mobile since it's now in the drawer */}
            {/* <Button
                  component={RouterLink}
                  to="/register"
                  variant="contained"
                  sx={{
                    bgcolor: 'white',
                    color: '#13357b',
                    borderRadius: '8px',
                    fontWeight: 600,
                    fontSize: { xs: '12px', sm: '14px', md: '16px' },
                    textTransform: 'none',
                    px: { xs: 1.5, sm: 2, md: 2.5 },
                    py: { xs: 0.5, sm: 1, md: 1 },
                    display: { xs: 'none', sm: 'none', md: 'flex' },
                    '&:hover': {
                      backgroundColor: '#86cefa',
                      color: 'white',
                      borderColor: '#86cefa'
                    }
                  }}
                >
                  Register as an Agent
                </Button> */}
          </Toolbar>
        </Container>
      </AppBar>
      
      {/* Mobile Drawer */}
      <Drawer
        variant="temporary"
        open={mobileOpen}
        onClose={handleDrawerToggle}
        ModalProps={{
          keepMounted: true, // Better open performance on mobile.
        }}
        sx={{
          display: { xs: 'block', md: 'none' },
          '& .MuiDrawer-paper': { 
            boxSizing: 'border-box', 
            width: 280,
            border: 'none',
            boxShadow: '0 8px 32px rgba(0,0,0,0.12)'
          },
        }}
      >
        {drawer}
      </Drawer>
    </Box>
  );
};

export default LandingNavbar; 