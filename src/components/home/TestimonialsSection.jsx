import React, { useState } from 'react';
import { Box } from '@mui/material';
import TestimonialsLeft from './TestimonialsLeft';
import TestimonialsRight from './TestimonialsRight';

const TestimonialsSection = () => {
  const [currentTestimonial, setCurrentTestimonial] = useState(0);

  const testimonials = [
    {
      id: 1,
      name: "Courtney Henry",
      position: "Marketing Coordinator",
      rating: 5,
      review: "Consectetur adipiscing elit. Integer nunc viverra laoreet est the is porta pretium metus aliquam eget maecenas porta is nunc viverra Aenean pulvinar maximus leo",
      image: "/img/avatars/1/1.png"
    },
    {
      id: 2,
      name: "Michael Chen",
      position: "Travel Blogger",
      rating: 5,
      review: "Amazing experience with professional service. The attention to detail and customer care exceeded our expectations completely throughout the entire journey.",
      image: "/img/avatars/1/2.png"
    },
    {
      id: 3,
      name: "Emma Rodriguez", 
      position: "Adventure Seeker",
      rating: 5,
      review: "Best travel agency I've ever used. They handled everything seamlessly and made our dream vacation come true with perfect planning and execution.",
      image: "/img/avatars/1/3.png"
    }
  ];

  const handlePrevious = () => {
    setCurrentTestimonial(prev => 
      prev === 0 ? testimonials.length - 1 : prev - 1
    );
  };

  const handleNext = () => {
    setCurrentTestimonial(prev => 
      prev === testimonials.length - 1 ? 0 : prev + 1
    );
  };

  return (
    <Box 
      sx={{ 
        minHeight: '100vh', 
        position: 'relative',
        overflow: 'hidden',
        display: 'flex',
        flexDirection: { xs: 'column', md: 'row' }
      }}
    >
      {/* Left Side Component */}
      <Box sx={{ 
        width: { xs: '100%', md: '50%' },
        height: { xs: '50vh', md: '100vh' },
        order: { xs: 2, md: 1 }
      }}>
        <TestimonialsLeft />
      </Box>
      
      {/* Right Side Component */}
      <Box sx={{ 
        width: { xs: '100%', md: '50%' },
        height: { xs: '50vh', md: '100vh' },
        order: { xs: 1, md: 2 }
      }}>
        <TestimonialsRight 
          testimonials={testimonials}
          currentTestimonial={currentTestimonial}
          onPrevious={handlePrevious}
          onNext={handleNext}
        />
      </Box>
    </Box>
  );
};

export default TestimonialsSection; 