import React, { useState } from 'react';
import {
  Box,
  Container,
  Grid,
  Typography,
  TextField,
  Button,
  IconButton,
  Link,
  Checkbox,
  FormControlLabel,
  Divider
} from '@mui/material';
import {
  Email,
  Phone,
  AccessTime,
  Facebook,
  Twitter,
  Instagram,
  YouTube,
  Send,
  Flight
} from '@mui/icons-material';

const Footer = () => {
  const [email, setEmail] = useState('');
  const [agreed, setAgreed] = useState(false);

  const handleSubscribe = () => {
    if (email && agreed) {
      console.log('Subscribe:', email);
      // Handle newsletter subscription
    }
  };

  const contactInfo = [
    {
      icon: <Email />,
      title: 'Send Email',
      info: 'admin@travclicks.com',
      href: 'mailto:admin@travclicks.com'
    },
    {
      icon: <Phone />,
      title: 'Contact Us',
      info: '+91 123 654 900',
      href: 'tel:+911236549000'
    },
    {
      icon: <AccessTime />,
      title: 'Opening Time',
      info: '24/7',
      href: null
    }
  ];

  const destinations = [
    'South America',
    'Middle East',
    'San Franc Rica',
    'New York',
    'Tokyo'
  ];

  const usefulLinks = [
    'About Us',
    'Destination',
    'News & blog',
    'Meet the Guide',
    'Contacts'
  ];

  const socialLinks = [
    { icon: <Facebook />, href: '#' },
    { icon: <Twitter />, href: '#' },
    { icon: <Instagram />, href: '#' },
    { icon: <YouTube />, href: '#' }
  ];

  const paymentMethods = [
    { name: 'Visa', color: '#1A1F71' },
    { name: 'Mastercard', color: '#EB001B' },
    { name: 'Discover', color: '#FF6000' },
    { name: 'PayPal', color: '#003087' },
    { name: 'JCB', color: '#0E4C96' }
  ];

  return (
    <Box sx={{ bgcolor: '#2C3E50', color: 'white' }}>
      {/* Top Contact Bar */}
      <Box sx={{ bgcolor: '#34495E', py: 3 }}>
        <Container maxWidth="xl">
          <Grid container spacing={4} alignItems="center">
            {/* Logo */}
            <Grid item xs={12} md={3}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', mr: 2 }}>
                <Box
                  component="img"
                  src="public/Images/travclicklogo.jpeg"
                  alt="TravClicks Logo"
                  sx={{
                    width: { xs: 200, sm: 250, md: 300 },
                    height: { xs: 35, sm: 40, md: 50 },
                    objectFit: 'contain'
                  }}
                />
              </Box>
             
            </Box>
            </Grid>

            {/* Contact Info */}
            <Grid item xs={12} md={9}>
              <Grid container spacing={{ xs: 2, sm: 3, md: 3 }}>
                {contactInfo.map((item, index) => (
                  <Grid item xs={12} md={4} key={index}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                      <Box
                        sx={{
                          width: 40,
                          height: 40,
                          bgcolor: '#4CAF50',
                          borderRadius: '50%',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center'
                        }}
                      >
                        {item.icon}
                      </Box>
                      <Box>
                        <Typography variant="body2" sx={{ color: '#BDC3C7', fontSize: '14px' }}>
                          {item.title}
                        </Typography>
                        {item.href ? (
                          <Link 
                            href={item.href} 
                            sx={{ 
                              color: 'white', 
                              textDecoration: 'none',
                              fontSize: '16px',
                              fontWeight: '500',
                              '&:hover': { color: '#4CAF50' }
                            }}
                          >
                            {item.info}
                          </Link>
                        ) : (
                          <Typography variant="body1" sx={{ color: 'white', fontSize: '16px', fontWeight: '500' }}>
                            {item.info}
                          </Typography>
                        )}
                      </Box>
                    </Box>
                  </Grid>
                ))}
              </Grid>
            </Grid>
          </Grid>
        </Container>
      </Box>

      {/* Main Footer Content */}
      <Box sx={{ py: 6 }}>
        <Container maxWidth="xl">
          <Grid container spacing={{ xs: 3, sm: 4, md: 4 }}>
            {/* About Gotur */}
            <Grid item xs={12} md={3}>
              <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 3, color: 'white' }}>
                About Travclicks
              </Typography>
              <Typography 
                variant="body2" 
                sx={{ 
                  color: '#BDC3C7', 
                  mb: 3, 
                  lineHeight: 1.6,
                  fontSize: '14px'
                }}
              >
                Available, But The Majority Have Suffered Alteration In Some Form By Injected Humour, Or
              </Typography>
              <Box sx={{ display: 'flex', gap: 1 }}>
                {socialLinks.map((social, index) => (
                  <IconButton
                    key={index}
                    href={social.href}
                    sx={{
                      bgcolor: '#34495E',
                      color: 'white',
                      width: 35,
                      height: 35,
                      '&:hover': {
                        bgcolor: '#4CAF50'
                      }
                    }}
                  >
                    {social.icon}
                  </IconButton>
                ))}
              </Box>
            </Grid>

            {/* Destinations */}
            <Grid item xs={12} md={2}>
              <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 3, color: 'white' }}>
                Destinations
              </Typography>
              <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                {destinations.map((destination, index) => (
                  <Link
                    key={index}
                    href="#"
                    sx={{
                      color: '#BDC3C7',
                      textDecoration: 'none',
                      fontSize: '14px',
                      '&:hover': {
                        color: '#4CAF50'
                      }
                    }}
                  >
                    {destination}
                  </Link>
                ))}
              </Box>
            </Grid>

            {/* Useful Links */}
            <Grid item xs={12} md={2}>
              <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 3, color: 'white' }}>
                Useful Links
              </Typography>
              <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                {usefulLinks.map((link, index) => (
                  <Link
                    key={index}
                    href="#"
                    sx={{
                      color: '#BDC3C7',
                      textDecoration: 'none',
                      fontSize: '14px',
                      '&:hover': {
                        color: '#4CAF50'
                      }
                    }}
                  >
                    {link}
                  </Link>
                ))}
              </Box>
            </Grid>

            {/* Newsletter */}
            <Grid item xs={12} md={5}>
              <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 3, color: 'white' }}>
                Newsletter
              </Typography>
              <Typography 
                variant="body2" 
                sx={{ 
                  color: '#BDC3C7', 
                  mb: 3, 
                  fontSize: '14px',
                  lineHeight: 1.6
                }}
              >
                Sign up to searing weekly newsletter to get the latest updates.
              </Typography>
              
              <Box sx={{ display: 'flex', gap: 1, mb: 2 }}>
                <TextField
                  placeholder="Your email address"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  variant="outlined"
                  sx={{
                    flex: 1,
                    '& .MuiOutlinedInput-root': {
                      bgcolor: 'white',
                      height: '45px',
                      '& fieldset': {
                        borderColor: '#ddd'
                      },
                      '&:hover fieldset': {
                        borderColor: '#4CAF50'
                      },
                      '&.Mui-focused fieldset': {
                        borderColor: '#4CAF50'
                      }
                    },
                    '& .MuiInputBase-input': {
                      fontSize: '14px'
                    }
                  }}
                />
                <Button
                  onClick={handleSubscribe}
                  sx={{
                    bgcolor: '#FF9800',
                    color: 'white',
                    minWidth: '45px',
                    height: '45px',
                    '&:hover': {
                      bgcolor: '#F57C00'
                    }
                  }}
                >
                  <Send />
                </Button>
              </Box>

              <FormControlLabel
                control={
                  <Checkbox
                    checked={agreed}
                    onChange={(e) => setAgreed(e.target.checked)}
                    sx={{
                      color: '#4CAF50',
                      '&.Mui-checked': {
                        color: '#4CAF50'
                      }
                    }}
                  />
                }
                label={
                  <Typography variant="body2" sx={{ color: '#BDC3C7', fontSize: '14px' }}>
                    I agree to the{' '}
                    <Link href="#" sx={{ color: 'white', textDecoration: 'underline' }}>
                      Privacy Policy
                    </Link>
                    .
                  </Typography>
                }
              />
            </Grid>
          </Grid>
        </Container>
      </Box>

      {/* Bottom Copyright Bar */}
      {/* <Box sx={{ bgcolor: '#4CAF50', py: 2 }}>
        <Container maxWidth="xl">
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <Typography variant="body2" sx={{ color: 'white', fontSize: '14px' }}>
              © Copyright 2025 by Travclicks NextJS Template.
            </Typography>
            
            <Box sx={{ display: 'flex', gap: 1 }}>
              {paymentMethods.map((method, index) => (
                <Box
                  key={index}
                  sx={{
                    width: 40,
                    height: 25,
                    bgcolor: 'white',
                    borderRadius: '4px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    border: '1px solid #ddd'
                  }}
                >
                  <Typography 
                    variant="caption" 
                    sx={{ 
                      color: method.color, 
                      fontWeight: 'bold',
                      fontSize: '10px'
                    }}
                  >
                    {method.name}
                  </Typography>
                </Box>
              ))}
            </Box>
          </Box>
        </Container>
      </Box> */}
    </Box>
  );
};

export default Footer; 