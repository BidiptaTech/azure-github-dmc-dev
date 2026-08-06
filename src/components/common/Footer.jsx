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
    'Singapore',
    'Indonesia',
    'Australia',
  ];

  const usefulLinks = [
    'About Us',
    'Destination',
    'Tours',
    'Login',
    'Register',
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
    <Box sx={{ bgcolor: '#f4f7fb', color: '#13357b', borderTop: '1px solid #dce4f0' }}>
      {/* Top Contact Bar */}
      <Box sx={{ bgcolor: '#e8eef6', py: 3, borderBottom: '1px solid #dce4f0' }}>
        <Container maxWidth="xl">
          <Grid container spacing={4} alignItems="center">
            {/* Logo */}
            <Grid item xs={12} md={3}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <Box
                  component="img"
                  src="/Images/logo.png"
                  alt="TravClicks Logo"
                  sx={{
                    width: { xs: 220, sm: 250, md: 280 },
                    height: { xs: 54, sm: 58, md: 66 },
                    objectFit: 'contain',
                  }}
                />
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
                          bgcolor: '#13357b',
                          color: 'white',
                          borderRadius: '8px',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center'
                        }}
                      >
                        {item.icon}
                      </Box>
                      <Box>
                        <Typography variant="body2" sx={{ color: '#64748b', fontSize: '14px' }}>
                          {item.title}
                        </Typography>
                        {item.href ? (
                          <Link 
                            href={item.href} 
                            sx={{ 
                              color: '#13357b', 
                              textDecoration: 'none',
                              fontSize: '16px',
                              fontWeight: '500',
                              '&:hover': { color: '#0f2d6b' }
                            }}
                          >
                            {item.info}
                          </Link>
                        ) : (
                          <Typography variant="body1" sx={{ color: '#13357b', fontSize: '16px', fontWeight: '500' }}>
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
              <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 3, color: '#13357b' }}>
                About Travclicks
              </Typography>
              <Typography 
                variant="body2" 
                sx={{ 
                  color: '#64748b', 
                  mb: 3, 
                  lineHeight: 1.6,
                  fontSize: '14px'
                }}
              >
                B2B destination management across Singapore, Indonesia, and Australia — hotels, transfers, guides, and packages for travel trade partners.
              </Typography>
              <Box sx={{ display: 'flex', gap: 1 }}>
                {socialLinks.map((social, index) => (
                  <IconButton
                    key={index}
                    href={social.href}
                    sx={{
                      bgcolor: '#e8eef6',
                      color: '#13357b',
                      width: 35,
                      height: 35,
                      '&:hover': {
                        bgcolor: '#13357b',
                        color: 'white',
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
              <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 3, color: '#13357b' }}>
                Destinations
              </Typography>
              <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                {destinations.map((destination, index) => (
                  <Link
                    key={index}
                    href="#destinations"
                    sx={{
                      color: '#64748b',
                      textDecoration: 'none',
                      fontSize: '14px',
                      '&:hover': {
                        color: '#13357b'
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
              <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 3, color: '#13357b' }}>
                Useful Links
              </Typography>
              <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                {usefulLinks.map((link, index) => (
                  <Link
                    key={index}
                    href="#"
                    sx={{
                      color: '#64748b',
                      textDecoration: 'none',
                      fontSize: '14px',
                      '&:hover': {
                        color: '#13357b'
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
              <Typography variant="h6" sx={{ fontWeight: 'bold', mb: 3, color: '#13357b' }}>
                Newsletter
              </Typography>
              <Typography 
                variant="body2" 
                sx={{ 
                  color: '#64748b', 
                  mb: 3, 
                  fontSize: '14px',
                  lineHeight: 1.6
                }}
              >
                Sign up for our weekly newsletter to get the latest updates.
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
                        borderColor: '#dce4f0'
                      },
                      '&:hover fieldset': {
                        borderColor: '#13357b'
                      },
                      '&.Mui-focused fieldset': {
                        borderColor: '#13357b'
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
                    bgcolor: '#13357b',
                    color: 'white',
                    minWidth: '45px',
                    height: '45px',
                    boxShadow: 'none',
                    '&:hover': {
                      bgcolor: '#0f2d6b'
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
                      color: '#13357b',
                      '&.Mui-checked': {
                        color: '#13357b'
                      }
                    }}
                  />
                }
                label={
                  <Typography variant="body2" sx={{ color: '#64748b', fontSize: '14px' }}>
                    I agree to the{' '}
                    <Link href="#" sx={{ color: '#13357b', textDecoration: 'underline' }}>
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