import React, { useEffect, useState } from 'react';
import { Box } from '@mui/material';

// Animation wrapper component for dashboard elements
export const AnimatedBox = ({ 
  children, 
  direction = 'up', 
  delay = 0, 
  duration = 0.8,
  className = '',
  bgColor = 'rgba(255, 255, 255, 0.1)',
  ...props 
}) => {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsVisible(true);
    }, delay);

    return () => clearTimeout(timer);
  }, [delay]);

  const getTransform = () => {
    const distance = '60px';
    switch (direction) {
      case 'up':
        return `translateY(${distance})`;
      case 'down':
        return `translateY(-${distance})`;
      case 'left':
        return `translateX(${distance})`;
      case 'right':
        return `translateX(-${distance})`;
      case 'up-left':
        return `translate(${distance}, ${distance})`;
      case 'up-right':
        return `translate(-${distance}, ${distance})`;
      case 'down-left':
        return `translate(${distance}, -${distance})`;
      case 'down-right':
        return `translate(-${distance}, -${distance})`;
      default:
        return `translateY(${distance})`;
    }
  };

  return (
    <Box
      className={`dashboard-animated-box ${className}`}
      sx={{
        opacity: isVisible ? 1 : 0,
        transform: isVisible ? 'translate(0, 0)' : getTransform(),
        transition: `all ${duration}s cubic-bezier(0.25, 0.46, 0.45, 0.94)`,
        willChange: 'transform, opacity',
        background: bgColor,
        backdropFilter: 'blur(10px)',
        borderRadius: 3,
        border: '1px solid rgba(255, 255, 255, 0.2)',
        position: 'relative',
        overflow: 'visible',
        '&::before': {
          content: '""',
          position: 'absolute',
          top: 0,
          left: '-100%',
          width: '100%',
          height: '100%',
          background: 'linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent)',
          transition: 'left 0.5s ease-in-out',
        },
        '&:hover::before': {
          left: '100%',
        },
        '&:hover': {
          transform: 'translateY(-5px)',
          boxShadow: '0 20px 40px rgba(0, 0, 0, 0.1)',
        },
        ...props.sx
      }}
      {...props}
    >
      {children}
    </Box>
  );
};

// Bubble animation background
export const BubbleBackground = ({ 
  bubbleCount = 20, 
  className = '',
  colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7', '#dda0dd'],
  ...props 
}) => {
  const bubbles = Array.from({ length: bubbleCount }, (_, i) => ({
    id: i,
    size: Math.random() * 60 + 20,
    left: Math.random() * 100,
    delay: Math.random() * 5,
    duration: Math.random() * 10 + 10,
    color: colors[Math.floor(Math.random() * colors.length)],
    opacity: Math.random() * 0.7 + 0.3,
  }));

  return (
    <Box
      className={`bubble-background ${className}`}
      sx={{
        position: 'absolute',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        overflow: 'hidden',
        pointerEvents: 'none',
        background: 'linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%)',

        ...props.sx
      }}
      {...props}
    >
      {bubbles.map((bubble) => (
        <Box
          key={bubble.id}
          sx={{
            position: 'absolute',
            bottom: '-100px',
            left: `${bubble.left}%`,
            width: `${bubble.size}px`,
            height: `${bubble.size}px`,
            backgroundColor: bubble.color,
            borderRadius: '50%',
            opacity: bubble.opacity,
            animation: `bubbleFloat ${bubble.duration}s infinite linear`,
            animationDelay: `${bubble.delay}s`,
            filter: 'blur(0.5px)',
            '@keyframes bubbleFloat': {
              '0%': {
                transform: 'translateY(0) rotate(0deg)',
                opacity: bubble.opacity,
              },
              '50%': {
                transform: 'translateY(-50vh) rotate(180deg)',
                opacity: bubble.opacity * 0.8,
              },
              '100%': {
                transform: 'translateY(-100vh) rotate(360deg)',
                opacity: 0,
              },
            },
          }}
        />
      ))}
    </Box>
  );
};

// Enhanced animated container with gradient background
export const GradientAnimatedBox = ({ 
  children, 
  direction = 'up', 
  delay = 0, 
  duration = 0.8,
  className = '',
  gradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  ...props 
}) => {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsVisible(true);
    }, delay);

    return () => clearTimeout(timer);
  }, [delay]);

  const getTransform = () => {
    const distance = '60px';
    switch (direction) {
      case 'up':
        return `translateY(${distance})`;
      case 'down':
        return `translateY(-${distance})`;
      case 'left':
        return `translateX(${distance})`;
      case 'right':
        return `translateX(-${distance})`;
      default:
        return `translateY(${distance})`;
    }
  };

  return (
    <Box
      className={`gradient-animated-box ${className}`}
      sx={{
        opacity: isVisible ? 1 : 0,
        transform: isVisible ? 'translate(0, 0)' : getTransform(),
        transition: `all ${duration}s cubic-bezier(0.25, 0.46, 0.45, 0.94)`,
        willChange: 'transform, opacity',
        background: gradient,
        borderRadius: 4,
        padding: 3,
        position: 'relative',
        overflow: 'hidden',
        boxShadow: '0 10px 30px rgba(0, 0, 0, 0.1)',
        '&::before': {
          content: '""',
          position: 'absolute',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          background: 'linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%)',
          opacity: 0,
          transition: 'opacity 0.3s ease',
        },
        '&:hover::before': {
          opacity: 1,
        },
        '&:hover': {
          transform: 'translateY(-8px) scale(1.02)',
          boxShadow: '0 20px 40px rgba(0, 0, 0, 0.15)',
        },
        ...props.sx
      }}
      {...props}
    >
      {children}
    </Box>
  );
};

// Staggered animation container
export const StaggeredContainer = ({ 
  children, 
  staggerDelay = 0.1,
  className = '',
  ...props 
}) => {
  return (
    <Box
      className={`dashboard-staggered-container ${className}`}
      sx={{
        display: 'flex',
        flexDirection: 'column',
        gap: 2,
        overflow: 'visible', // Ensure container allows overflow
        position: 'relative',
        ...props.sx
      }}
      {...props}
    >
      {React.Children.map(children, (child, index) => {
        if (React.isValidElement(child) && child.type === AnimatedBox) {
          return React.cloneElement(child, {
            delay: child.props.delay || (index * staggerDelay * 1000)
          });
        }
        return child;
      })}
    </Box>
  );
};

// Grid animation container
export const AnimatedGrid = ({ 
  children, 
  columns = 1,
  staggerDelay = 0.1,
  className = '',
  ...props 
}) => {
  return (
    <Box
      className={`dashboard-animated-grid ${className}`}
      sx={{
        display: 'grid',
        gridTemplateColumns: `repeat(${columns}, 1fr)`,
        gap: 3,
        ...props.sx
      }}
      {...props}
    >
      {React.Children.map(children, (child, index) => {
        if (React.isValidElement(child) && child.type === AnimatedBox) {
          return React.cloneElement(child, {
            delay: child.props.delay || (index * staggerDelay * 1000)
          });
        }
        return child;
      })}
    </Box>
  );
};

// Dashboard entrance animation wrapper with enhanced background
export const DashboardEntrance = ({ children, className = '', ...props }) => {
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoaded(true);
    }, 100);

    return () => clearTimeout(timer);
  }, []);

  return (
    <Box
      className={`dashboard-entrance ${className}`}
      sx={{
        opacity: isLoaded ? 1 : 0,
        transform: isLoaded ? 'scale(1)' : 'scale(0.95)',
        transition: 'all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
        position: 'relative',
        background: 'linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%)',
        minHeight: '100vh',
        ...props.sx
      }}
      {...props}
    >
      {children}
    </Box>
  );
};

// Pulse animation for loading states with enhanced colors
export const PulseBox = ({ children, className = '', color = '#4ecdc4', ...props }) => {
  return (
    <Box
      className={`dashboard-pulse-box ${className}`}
      sx={{
        animation: 'dashboardPulse 2s ease-in-out infinite',
        '@keyframes dashboardPulse': {
          '0%, 100%': {
            opacity: 1,
            transform: 'scale(1)',
            boxShadow: `0 0 0 0 ${color}40`
          },
          '50%': {
            opacity: 0.8,
            transform: 'scale(1.02)',
            boxShadow: `0 0 0 10px ${color}00`
          }
        },
        ...props.sx
      }}
      {...props}
    >
      {children}
    </Box>
  );
};

// Floating animation for cards with enhanced effects
export const FloatingBox = ({ children, className = '', ...props }) => {
  return (
    <Box
      className={`dashboard-floating-box ${className}`}
      sx={{
        animation: 'dashboardFloat 6s ease-in-out infinite',
        background: 'rgba(255, 255, 255, 0.9)',
        backdropFilter: 'blur(20px)',
        borderRadius: 3,
        border: '1px solid rgba(255, 255, 255, 0.3)',
        boxShadow: '0 8px 32px rgba(0, 0, 0, 0.1)',
        '@keyframes dashboardFloat': {
          '0%, 100%': {
            transform: 'translateY(0px) rotate(0deg)',
            boxShadow: '0 8px 32px rgba(0, 0, 0, 0.1)'
          },
          '50%': {
            transform: 'translateY(-10px) rotate(1deg)',
            boxShadow: '0 12px 40px rgba(0, 0, 0, 0.15)'
          }
        },
        ...props.sx
      }}
      {...props}
    >
      {children}
    </Box>
  );
};

// Enhanced shimmer loading effect
export const ShimmerBox = ({ className = '', ...props }) => {
  return (
    <Box
      className={`dashboard-shimmer-box ${className}`}
      sx={{
        background: 'linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%)',
        backgroundSize: '200% 100%',
        animation: 'dashboardShimmer 1.5s infinite',
        borderRadius: 2,
        '@keyframes dashboardShimmer': {
          '0%': {
            backgroundPosition: '-200% 0'
          },
          '100%': {
            backgroundPosition: '200% 0'
          }
        },
        ...props.sx
      }}
      {...props}
    />
  );
};

// Glass morphism card effect
export const GlassMorphismBox = ({ children, className = '', ...props }) => {
  return (
    <Box
      className={`glass-morphism-box ${className}`}
      sx={{
        background: 'rgba(255, 255, 255, 0.25)',
        backdropFilter: 'blur(10px)',
        borderRadius: 3,
        border: '1px solid rgba(255, 255, 255, 0.18)',
        boxShadow: '0 8px 32px 0 rgba(31, 38, 135, 0.37)',
        transition: 'all 0.3s ease',
        '&:hover': {
          background: 'rgba(255, 255, 255, 0.35)',
          transform: 'translateY(-5px)',
          boxShadow: '0 12px 40px 0 rgba(31, 38, 135, 0.5)',
        },
        ...props.sx
      }}
      {...props}
    >
      {children}
    </Box>
  );
};

// Bubble floating animation
export const BubbleFloatingBox = ({ children, className = '', ...props }) => {
  return (
    <Box
      className={`bubble-floating-box ${className}`}
      sx={{
        position: 'relative',
        animation: 'bubbleFloat 4s ease-in-out infinite',
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        borderRadius: '50%',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: 'white',
        fontWeight: 'bold',
        boxShadow: '0 10px 30px rgba(102, 126, 234, 0.4)',
        '&::before': {
          content: '""',
          position: 'absolute',
          top: '-50%',
          left: '-50%',
          width: '200%',
          height: '200%',
          background: 'radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)',
          borderRadius: '50%',
          animation: 'bubbleShine 3s ease-in-out infinite',
        },
        '@keyframes bubbleFloat': {
          '0%, 100%': {
            transform: 'translateY(0px) scale(1)',
          },
          '50%': {
            transform: 'translateY(-20px) scale(1.05)',
          }
        },
        '@keyframes bubbleShine': {
          '0%, 100%': {
            opacity: 0.3,
            transform: 'rotate(0deg)',
          },
          '50%': {
            opacity: 0.8,
            transform: 'rotate(180deg)',
          }
        },
        ...props.sx
      }}
      {...props}
    >
      {children}
    </Box>
  );
};

export default {
  AnimatedBox,
  BubbleBackground,
  GradientAnimatedBox,
  StaggeredContainer,
  AnimatedGrid,
  DashboardEntrance,
  PulseBox,
  FloatingBox,
  ShimmerBox,
  GlassMorphismBox,
  BubbleFloatingBox
}; 