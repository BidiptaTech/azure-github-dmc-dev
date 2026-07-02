import React from 'react';
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import {
  Flight as FlightIcon,
  Luggage as LuggageIcon,
  Explore as ExploreIcon,
  Hotel as HotelIcon,
  Restaurant as RestaurantIcon,
  DirectionsCar as CarIcon,
  LocationOn as LocationIcon,
  CameraAlt as CameraIcon,
  BeachAccess as BeachIcon,
} from '@mui/icons-material';

const BackgroundContainer = styled(Box)(({ theme }) => ({
  position: 'fixed',
  top: 0,
  left: 0,
  width: '100%',
  height: '100%',
  overflow: 'hidden',
  zIndex: 0,
  pointerEvents: 'none',
  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%)',
  backgroundSize: '400% 400%',
  animation: 'gradientShift 15s ease infinite',
  '@keyframes gradientShift': {
    '0%': { backgroundPosition: '0% 50%' },
    '50%': { backgroundPosition: '100% 50%' },
    '100%': { backgroundPosition: '0% 50%' },
  },
  '&::before': {
    content: '""',
    position: 'absolute',
    top: 0,
    left: 0,
    width: '100%',
    height: '100%',
    background: 'radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%)',
    animation: 'pulse 8s ease-in-out infinite',
    '@keyframes pulse': {
      '0%, 100%': { opacity: 0.5 },
      '50%': { opacity: 0.8 },
    },
  },
}));

const FloatingIcon = styled(Box)(({ delay, duration, startX, startY, endX, endY, size, opacity }) => ({
  position: 'absolute',
  fontSize: `${size}px`,
  color: 'rgba(255, 255, 255, 0.15)',
  animation: `float ${duration}s ease-in-out infinite`,
  animationDelay: `${delay}s`,
  left: `${startX}%`,
  top: `${startY}%`,
  opacity: opacity || 0.15,
  '@keyframes float': {
    '0%': {
      transform: `translate(0, 0) rotate(0deg)`,
      opacity: opacity || 0.15,
    },
    '25%': {
      transform: `translate(${endX * 0.5}px, ${endY * 0.5}px) rotate(90deg)`,
      opacity: (opacity || 0.15) * 1.2,
    },
    '50%': {
      transform: `translate(${endX}px, ${endY}px) rotate(180deg)`,
      opacity: opacity || 0.15,
    },
    '75%': {
      transform: `translate(${endX * 0.5}px, ${endY * 0.5}px) rotate(270deg)`,
      opacity: (opacity || 0.15) * 1.2,
    },
    '100%': {
      transform: `translate(0, 0) rotate(360deg)`,
      opacity: opacity || 0.15,
    },
  },
  transition: 'all 0.3s ease',
  '&:hover': {
    opacity: (opacity || 0.15) * 1.5,
  },
}));

const Particle = styled(Box)(({ delay, duration, startX, startY, size }) => ({
  position: 'absolute',
  width: `${size}px`,
  height: `${size}px`,
  borderRadius: '50%',
  background: 'rgba(255, 255, 255, 0.1)',
  left: `${startX}%`,
  top: `${startY}%`,
  animation: `particleFloat ${duration}s ease-in-out infinite`,
  animationDelay: `${delay}s`,
  boxShadow: '0 0 10px rgba(255, 255, 255, 0.2)',
  '@keyframes particleFloat': {
    '0%': {
      transform: 'translateY(0) scale(1)',
      opacity: 0,
    },
    '10%': {
      opacity: 0.3,
    },
    '90%': {
      opacity: 0.3,
    },
    '100%': {
      transform: `translateY(-100vh) scale(0)`,
      opacity: 0,
    },
  },
}));

const TravelAnimatedBackground = () => {
  // Generate random travel icons with different animations
  const travelIcons = [
    { icon: FlightIcon, delay: 0, duration: 20, startX: 10, startY: 20, endX: 100, endY: -50, size: 40, opacity: 0.12 },
    { icon: LuggageIcon, delay: 2, duration: 25, startX: 85, startY: 30, endX: -80, endY: 60, size: 35, opacity: 0.1 },
    { icon: ExploreIcon, delay: 4, duration: 30, startX: 20, startY: 60, endX: 120, endY: -30, size: 45, opacity: 0.12 },
    { icon: HotelIcon, delay: 1, duration: 22, startX: 70, startY: 50, endX: -60, endY: 40, size: 38, opacity: 0.1 },
    { icon: RestaurantIcon, delay: 3, duration: 28, startX: 15, startY: 80, endX: 90, endY: -40, size: 32, opacity: 0.1 },
    { icon: CarIcon, delay: 5, duration: 24, startX: 90, startY: 70, endX: -100, endY: 50, size: 36, opacity: 0.1 },
    { icon: LocationIcon, delay: 1.5, duration: 26, startX: 30, startY: 40, endX: 70, endY: -60, size: 42, opacity: 0.12 },
    { icon: CameraIcon, delay: 3.5, duration: 27, startX: 60, startY: 15, endX: -50, endY: 80, size: 30, opacity: 0.1 },
    { icon: BeachIcon, delay: 2.5, duration: 29, startX: 40, startY: 75, endX: 80, endY: -50, size: 40, opacity: 0.1 },
    { icon: FlightIcon, delay: 4.5, duration: 23, startX: 75, startY: 25, endX: -70, endY: 70, size: 35, opacity: 0.1 },
  ];

  // Generate floating particles
  const particles = Array.from({ length: 15 }, (_, i) => ({
    delay: i * 0.5,
    duration: 10 + Math.random() * 10,
    startX: Math.random() * 100,
    startY: 100 + Math.random() * 20,
    size: 3 + Math.random() * 4,
  }));

  return (
    <BackgroundContainer>
      {/* Floating travel icons */}
      {travelIcons.map((item, index) => {
        const IconComponent = item.icon;
        return (
          <FloatingIcon
            key={`icon-${index}`}
            delay={item.delay}
            duration={item.duration}
            startX={item.startX}
            startY={item.startY}
            endX={item.endX}
            endY={item.endY}
            size={item.size}
            opacity={item.opacity}
          >
            <IconComponent sx={{ fontSize: 'inherit' }} />
          </FloatingIcon>
        );
      })}

      {/* Floating particles */}
      {particles.map((particle, index) => (
        <Particle
          key={`particle-${index}`}
          delay={particle.delay}
          duration={particle.duration}
          startX={particle.startX}
          startY={particle.startY}
          size={particle.size}
        />
      ))}
    </BackgroundContainer>
  );
};

export default TravelAnimatedBackground;

