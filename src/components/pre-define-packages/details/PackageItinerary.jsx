import React, { useRef, useEffect } from 'react';
import {
  Box,
  Typography,
  Divider,
  Paper,
  Chip
} from '@mui/material';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';


// Import shared date utility functions from parent component
// Note: In a real application, these would be in a separate utils file
// This is a workaround for this specific implementation
import { formatDate, getItineraryDayDate } from './shared-date-utils';

// Compact day item component for sidebar
const DayItem = ({ day, isActive, onClick }) => {
  // Define gradient colors based on active state
  const bgColor = isActive 
    ? 'linear-gradient(90deg, #1976d2 0%, #2196f3 100%)'
    : 'linear-gradient(90deg, rgba(240,242,245,0.6) 0%, rgba(250,252,255,0.6) 100%)';
  
  const hoverBgColor = isActive
    ? 'linear-gradient(90deg, #1565c0 0%, #1e88e5 100%)'
    : 'linear-gradient(90deg, rgba(235,238,242,0.9) 0%, rgba(242,245,250,0.9) 100%)';
    
  return (
    <Box 
      onClick={onClick}
      sx={{
        display: 'flex',
        alignItems: 'center',
        py: 0.8,
        px: 1,
        mb: 0.5,
        borderRadius: '8px',
        cursor: 'pointer',
        background: bgColor,
        color: isActive ? 'white' : 'text.primary',
        boxShadow: isActive ? '0 2px 4px rgba(25, 118, 210, 0.2)' : 'none',
        border: '1px solid',
        borderColor: isActive ? 'primary.main' : 'transparent',
        '&:hover': {
          background: hoverBgColor,
          boxShadow: '0 2px 5px rgba(0,0,0,0.08)',
        },
        transition: 'all 0.3s ease'
      }}
    >
      {/* Calendar icon */}
      <CalendarTodayIcon 
        sx={{ 
          fontSize: 14,
          color: isActive ? 'white' : 'primary.main',
          mr: 0.75
        }} 
      />
      
      {/* Day chip */}
      <Chip
        label={`D${day.day}`}
        size="small"
        sx={{ 
          height: 20, 
          fontSize: '0.7rem',
          fontWeight: 'bold',
          mr: 0.75,
          backgroundColor: isActive ? 'rgba(255,255,255,0.2)' : 'rgba(25,118,210,0.1)',
          color: isActive ? 'white' : 'primary.main',
          border: '1px solid',
          borderColor: isActive ? 'rgba(255,255,255,0.3)' : 'transparent',
          '& .MuiChip-label': { 
            px: 0.75,
            py: 0
          }
        }}
      />
      
      {/* Date */}
      <Typography 
        variant="caption" 
        sx={{ 
          fontSize: '0.7rem',
          color: isActive ? 'rgba(255,255,255,0.9)' : 'text.secondary',
          ml: 'auto'
        }}
      >
        {day.date}
      </Typography>
    </Box>
  );
};

const PackageItinerary = ({ packageDetails, activeDay, setActiveDay, contentRef, dayRefs }) => {
  // Ref for sidebar scroll container
  const sidebarRef = useRef(null);
  // Refs for each day item in the sidebar
  const dayItemRefs = useRef([]);
  
  // Generate day items for sidebar navigation
  const generateDayItems = () => {
    const days = packageDetails.duration_days || 1;
    
    return Array(days).fill().map((_, index) => {
      // Use the shared date calculation function
      const dayDate = getItineraryDayDate(packageDetails, index);
      
      return {
        day: index + 1,
        label: `Day ${index + 1}`,
        date: formatDate(dayDate),
        description: packageDetails.itinerary && 
          packageDetails.itinerary[index] && 
          packageDetails.itinerary[index].title && 
          typeof packageDetails.itinerary[index].title === 'string' ?
            packageDetails.itinerary[index].title.split(' - ')[1] || '' : 
            index === 0 ? '' : index === days - 1 ? '' : ''
      };
    });
  };

  const dayItems = generateDayItems();
  
  // Initialize refs for day items
  useEffect(() => {
    dayItemRefs.current = Array(dayItems.length)
      .fill()
      .map((_, i) => dayItemRefs.current[i] || React.createRef());
  }, [dayItems.length]);
  
  // Scroll active day into view when it changes
  useEffect(() => {
    if (sidebarRef.current && dayItemRefs.current[activeDay]?.current) {
      const sidebarContainer = sidebarRef.current;
      const activeItem = dayItemRefs.current[activeDay].current;
      
      const containerRect = sidebarContainer.getBoundingClientRect();
      const activeItemRect = activeItem.getBoundingClientRect();
      
      // Check if the active item is not fully visible
      const isAbove = activeItemRect.top < containerRect.top;
      const isBelow = activeItemRect.bottom > containerRect.bottom;
      
      if (isAbove || isBelow) {
        // Scroll to make the active day visible with a small offset
        activeItem.scrollIntoView({
          behavior: 'smooth',
          block: isAbove ? 'start' : 'end'
        });
        
        // Add a small offset to avoid the day being right at the edge
        if (isAbove) {
          setTimeout(() => {
            sidebarContainer.scrollTop -= 10; // Scroll up a bit to add some margin
          }, 300);
        } else if (isBelow) {
          setTimeout(() => {
            sidebarContainer.scrollTop += 10; // Scroll down a bit to add some margin
          }, 300);
        }
      }
    }
  }, [activeDay]);

  // Scroll to specific day in itinerary
  const scrollToDay = (dayIndex) => {
    setActiveDay(dayIndex);
    
    if (dayRefs.current[dayIndex] && dayRefs.current[dayIndex].current && contentRef.current) {
      const contentContainer = contentRef.current;
      const dayElement = dayRefs.current[dayIndex].current;
      
      // Calculate the day element's position relative to the content container
      const dayRect = dayElement.getBoundingClientRect();
      const containerRect = contentContainer.getBoundingClientRect();
      
      // Calculate the correct scroll position
      const scrollPosition = contentContainer.scrollTop + (dayRect.top - containerRect.top);
      
      // Add a small offset for better visibility
      const scrollOffset = -20;
      
      // Scroll to the calculated position
      contentContainer.scrollTo({
        top: scrollPosition + scrollOffset,
        behavior: 'smooth'
      });
    }
  };

  return (
    <Paper 
      elevation={2}
      sx={{ 
        p: 2, 
        position: 'sticky', 
        top: 20,
        height: '100%',
        display: 'flex',
        flexDirection: 'column',
        borderRadius: '12px',
        maxHeight: '100%',
        overflowY: 'auto',
        background: 'linear-gradient(180deg, #ffffff 0%, #f8faff 100%)',
        border: '1px solid',
        borderColor: 'divider'
      }}
    >
      <Typography 
        variant="h6" 
        sx={{ 
          mb: 1.5, 
          display: 'flex', 
          alignItems: 'center',
          fontSize: '1.1rem',
          fontWeight: 'bold',
          color: 'primary.main'
        }}
      >
        <CalendarTodayIcon sx={{ mr: 1, fontSize: 20, color: 'primary.main' }} />
        Your Itinerary
      </Typography>
      <Divider sx={{ mb: 2, borderColor: 'rgba(25, 118, 210, 0.2)' }} />
      
      {/* Compact day navigation */}
      <Box 
        ref={sidebarRef}
        sx={{ 
          flexGrow: 1, 
          overflowY: 'auto',
          pr: 0.5,
          // Custom scrollbar styling
          '&::-webkit-scrollbar': {
            width: '6px',
          },
          '&::-webkit-scrollbar-track': {
            backgroundColor: 'rgba(0,0,0,0.03)',
            borderRadius: '6px',
          },
          '&::-webkit-scrollbar-thumb': {
            backgroundColor: 'rgba(25,118,210,0.2)',
            borderRadius: '6px',
            '&:hover': {
              backgroundColor: 'rgba(25,118,210,0.3)',
            },
          },
        }}
      >
        {dayItems.map((item, index) => (
          <Box
            ref={dayItemRefs.current[index]}
            key={index}
          >
            <DayItem
            day={item}
            isActive={activeDay === index}
            onClick={() => scrollToDay(index)}
          />
          </Box>
        ))}
      </Box>
      
      <Box sx={{ mt: 2, display: 'flex', justifyContent: 'center' }}>
        <Chip
          icon={<CalendarTodayIcon fontSize="small" />}
          label={`${dayItems.length} Days Journey`}
          color="primary"
          variant="outlined"
          size="small"
          sx={{ 
            fontSize: '0.75rem',
            fontWeight: 'medium', 
            boxShadow: '0 2px 4px rgba(0,0,0,0.05)',
            borderRadius: '16px',
            py: 0.5
          }}
        />
      </Box>
    </Paper>
  );
};

export default PackageItinerary; 