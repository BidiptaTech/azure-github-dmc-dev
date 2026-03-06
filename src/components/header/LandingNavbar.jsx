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
    { text: 'Register as Agent', icon: <Send />, path: '/register', isButton: true, variant: 'contained' }
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
        bgcolor: '#13357b', 
         p: 2.8,
        
        display: 'flex', 
        alignItems: 'center', 
        justifyContent: 'space-between' 
      }}>
        <Box
          component="img"
          src="Images/travclicklogo.jpeg"
          alt="TravClicks Logo"
          sx={{
            width: 120,
            height: 30,
            objectFit: 'contain'
          }}
        />
        <IconButton 
          onClick={handleDrawerToggle}
          sx={{ color: 'white' }}
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
        sx={{ 
          bgcolor: '#13357b', 
          boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
          py: 1
        }}
      >
        <Container maxWidth="xl" >
          <Toolbar sx={{ justifyContent: 'space-between', minHeight: '70px !important',color: 'white' }}>
            {/* Logo */}
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', mr: { xs: 1, md: 2 } }}>
                <Box
                  component="img"
                  src="Images/travclicklogo.jpeg"
                  alt="TravClicks Logo"
                  sx={{
                    width: { xs: 200, sm: 250, md: 300 },
                    height: { xs: 35, sm: 40, md: 50 },
                    objectFit: 'contain'
                  }}
                />
              </Box>
             
            </Box>

            {/* Navigation Menu */}
            <Box sx={{ display: { xs: 'none', md: 'flex' }, alignItems: 'center', }}>
              <Stack direction="row" spacing={4} sx={{ alignItems: 'center',color: 'white' }}>
                <Link 
                  component={RouterLink} 
                  to="/" 
                  sx={{ 
                   color: 'white',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#86cefa' }
                  }}
                >
                  Home
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#" 
                  sx={{ 
                    color: 'white',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#86cefa' }
                  }}
                >
                  About Us
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#" 
                  sx={{ 
                    color: 'white',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#86cefa' }
                  }}
                >
                  Destination
                </Link>
                <Link 
                  component={RouterLink} 
                  to="/#" 
                  sx={{ 
                    color: 'white',
                    textDecoration: 'none',
                    fontWeight: 500,
                    fontSize: '16px',
                    '&:hover': { color: '#86cefa' }
                  }}
                >
                  Tours List
                </Link>
                <Button
                  component={RouterLink}
                  to="/login"
                  variant="outlined"
                  sx={{
                    bgcolor: 'white',
                    color: '#13357b',
                    borderColor: 'white',
                    borderRadius: '8px',
                    fontWeight: 600,
                    fontSize: '16px',
                    textTransform: 'none',
                    px: 2.5,
                    '&:hover': {
                      backgroundColor: '#86cefa',
                      color: 'white',
                      borderColor: '#86cefa'
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
                color: '#333',
                bgcolor: '#f8f9fa',
                //p: 0.2,
              
                '&:hover': {
                  bgcolor: '#e9ecef'
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