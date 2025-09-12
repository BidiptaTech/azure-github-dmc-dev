import React from 'react';
import {
  Box
} from '@mui/material';
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

const TestimonialsLeft = () => {
  return (
    <Box
      sx={{
        width: '50%',
        height: '100vh',
        background: 'linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        position: 'relative',
        '&::after': {
          content: '""',
          position: 'absolute',
          top: 0,
          right: 0,
          width: '100px',
          height: '100%',
          background: 'linear-gradient(135deg, transparent 0%, white 100%)',
          transform: 'skewX(-10deg)',
          transformOrigin: 'top right'
        }
      }}
    >
      {/* Real Travelers Image with Unique Design */}
      <Box
        sx={{
          position: 'relative',
          width: '90%',
          height: '80%',
          borderRadius: '30px',
          overflow: 'hidden',
          boxShadow: '0 25px 50px rgba(0,0,0,0.15)'
        }}
      >
        {/* Main Travelers Image */}
        <Box
          component="img"
          src="/img/masthead/2/1.png"
          alt="Happy Travelers"
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            transition: 'transform 0.3s ease'
          }}
        />
        
        {/* Overlay with gradient */}
        <Box
          sx={{
            position: 'absolute',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            background: 'linear-gradient(135deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.3) 100%)'
          }}
        />
        
        {/* Decorative Cut Elements */}
        <Box
          sx={{
            position: 'absolute',
            top: '10%',
            right: '10%',
            width: '120px',
            height: '120px',
            borderRadius: '50%',
            overflow: 'hidden',
            border: '5px solid white',
            boxShadow: '0 10px 25px rgba(0,0,0,0.2)'
          }}
        >
          <Box
            component="img"
            src="/img/masthead/2/3.png"
            alt="Travel Detail"
            sx={{
              width: '100%',
              height: '100%',
              objectFit: 'cover'
            }}
          />
        </Box>
        
        {/* Bottom Cut Element */}
        <Box
          sx={{
            position: 'absolute',
            bottom: '15%',
            left: '15%',
            width: '100px',
            height: '100px',
            borderRadius: '20px',
            overflow: 'hidden',
            border: '4px solid white',
            boxShadow: '0 8px 20px rgba(0,0,0,0.2)',
            transform: 'rotate(-10deg)'
          }}
        >
          <Box
            component="img"
            src="/img/masthead/5/1.png"
            alt="Destination"
            sx={{
              width: '100%',
              height: '100%',
              objectFit: 'cover'
            }}
          />
        </Box>
      </Box>
    </Box>
  );
};

export default TestimonialsLeft; 