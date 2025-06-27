import React, { useRef } from 'react';
import {
  Box,
  Typography,
  Divider,
  Paper,
  Chip
} from '@mui/material';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import FiberManualRecordIcon from '@mui/icons-material/FiberManualRecord';

// Import shared date utility functions from parent component
// Note: In a real application, these would be in a separate utils file
// This is a workaround for this specific implementation
import { formatDate, getItineraryDayDate } from './shared-date-utils';

// Compact day item component for sidebar
const DayItem = ({ day, isActive, onClick }) => {
  return (
    <Box 
      onClick={onClick}
      sx={{
        display: 'flex',
        alignItems: 'center',
        py: 0.8,
        px: 1,
        mb: 0.5,
        borderRadius: '6px',
        cursor: 'pointer',
        backgroundColor: isActive ? 'primary.light' : 'transparent',
        color: isActive ? 'primary.contrastText' : 'text.primary',
        '&:hover': {
          backgroundColor: isActive ? 'primary.main' : 'action.hover',
        },
        transition: 'background-color 0.2s'
      }}
    >
      <FiberManualRecordIcon 
        sx={{ 
          fontSize: 12, 
          color: isActive ? 'inherit' : 'primary.main',
          mr: 1
        }} 
      />
      
      <Box sx={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
        <Typography 
          variant="body2" 
          fontWeight={isActive ? 'bold' : 'medium'}
          sx={{ lineHeight: 1.2 }}
        >
          {`Day ${day.day}`}
        </Typography>
        
        <Typography 
          variant="caption" 
          color={isActive ? 'inherit' : 'text.secondary'}
          sx={{ lineHeight: 1.1 }}
        >
          {day.description}
        </Typography>

        <Typography 
          variant="caption" 
          fontWeight="bold"
          color={isActive ? 'inherit' : 'text.secondary'}
          sx={{ lineHeight: 1.2, mt: 0.2 }}
        >
          {day.date}
        </Typography>
      </Box>
      
      <Chip
        size="small"
        label={`D${day.day}`}
        color={isActive ? "primary" : "default"}
        variant={isActive ? "filled" : "outlined"}
        sx={{ 
          height: 20, 
          '& .MuiChip-label': { 
            px: 0.8, 
            fontSize: '0.65rem' 
          }
        }}
      />
    </Box>
  );
};

const PackageItinerary = ({ packageDetails, activeDay, setActiveDay, contentRef, dayRefs }) => {
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
        description: packageDetails.itinerary && packageDetails.itinerary[index] 
          ? packageDetails.itinerary[index].title.split(' - ')[1] 
          : index === 0 ? 'Arrival' : index === days - 1 ? 'Departure' : 'Exploration'
      };
    });
  };

  const dayItems = generateDayItems();

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
      elevation={1} 
      sx={{ 
        p: 1.5, 
        position: 'sticky', 
        top: 20,
        borderRadius: '12px',
        maxHeight: 'calc(100vh - 40px)',
        overflowY: 'auto'
      }}
    >
      <Typography 
        variant="subtitle1" 
        sx={{ 
          mb: 1, 
          display: 'flex', 
          alignItems: 'center',
          fontSize: '0.9rem',
          fontWeight: 'bold' 
        }}
      >
        <CalendarTodayIcon sx={{ mr: 0.5, fontSize: 16 }} />
        Day by Day Journey
      </Typography>
      <Divider sx={{ mb: 1.5 }} />
      
      {/* Compact day navigation */}
      <Box>
        {dayItems.map((item, index) => (
          <DayItem
            key={index}
            day={item}
            isActive={activeDay === index}
            onClick={() => scrollToDay(index)}
          />
        ))}
      </Box>
    </Paper>
  );
};

export default PackageItinerary; 