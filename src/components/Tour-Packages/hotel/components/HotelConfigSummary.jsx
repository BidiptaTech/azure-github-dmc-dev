import React, { useState, useEffect } from 'react';
import { 
  Box, Typography, Grid, Card, CardContent, Paper, Chip, Avatar,
  Accordion, AccordionSummary, AccordionDetails, IconButton, Alert, Button, Divider,
  Tooltip, ButtonGroup, Badge, LinearProgress, Stepper, Step, StepLabel, StepContent
} from '@mui/material';
import HotelIcon from '@mui/icons-material/Hotel';
import RestaurantMenuIcon from '@mui/icons-material/RestaurantMenu';
import PersonIcon from '@mui/icons-material/Person';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import DeleteIcon from '@mui/icons-material/Delete';
import WarningIcon from '@mui/icons-material/Warning';
import UnfoldLessIcon from '@mui/icons-material/UnfoldLess';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import RoomIcon from '@mui/icons-material/Room';
import EditIcon from '@mui/icons-material/Edit';
import AddIcon from '@mui/icons-material/Add';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import PendingIcon from '@mui/icons-material/Pending';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import TaskAltIcon from '@mui/icons-material/TaskAlt';
import PlayCircleOutlineIcon from '@mui/icons-material/PlayCircleOutline';
import AddBusinessIcon from '@mui/icons-material/AddBusiness';
import { useSelector } from 'react-redux';

/**
 * Enhanced Hotel Configuration Summary component with better UX
 */
const HotelConfigSummary = ({ 
  hotelConfigurations, 
  activeHotelIndex, 
  selectHotelConfiguration, 
  removeHotelConfiguration,
  onAddMoreRooms,
  onAddNewHotel,
  tourDateRange 
}) => {
  const [expandedAccordions, setExpandedAccordions] = useState(new Set());
  const PriceHide = useSelector((state) => state.auth.PriceHide);   
  // Get tour dates from Redux state to check for date conflicts
  const searchCriteria = useSelector(state => state.tourPackages?.searchCriteria);
  const tourDates = searchCriteria?.dates || [];
  const tourStatus = useSelector((state) => state.tourPackages.tourStatus);
  // Enhanced booking status calculation with validation logic
  const bookingStatus = React.useMemo(() => {
    if (!hotelConfigurations || hotelConfigurations.length === 0) {
      return { 
        status: 'empty', 
        message: 'No hotels added yet',
        completedRooms: 0,
        totalRooms: 0,
        progress: 0,
        canAddNewHotel: true,
        incompleteHotels: [],
        showStatusSection: false // Don't show status section when empty
      };
    }

    // Check if there are any meaningful hotel configurations (with actual hotel selections)
    const meaningfulConfigurations = hotelConfigurations.filter(config => config.hotelId);
    
    // If no hotels have been actually selected yet, treat as empty state
    // This handles the case where hotel component always maintains at least one empty placeholder
    if (meaningfulConfigurations.length === 0) {
      return { 
        status: 'empty', 
        message: 'No hotels selected yet',
        completedRooms: 0,
        totalRooms: 0,
        progress: 0,
        canAddNewHotel: true,
        incompleteHotels: [],
        showStatusSection: false // Don't show status section until a hotel is selected
      };
    }

    const totalRooms = hotelConfigurations.length;
    const completedRooms = hotelConfigurations.filter(config => 
      config.hotelId && config.roomTypeId && config.bedTypeId
    ).length;
    
    // Find incomplete hotels (those without hotelId - not selected yet)
    const incompleteHotels = hotelConfigurations
      .map((config, index) => ({ ...config, index }))
      .filter(config => !config.hotelId);
    
    // Find partially configured hotels (selected hotel but missing room/bed)
    const partiallyConfigured = hotelConfigurations.filter(config => 
      config.hotelId && (!config.roomTypeId || !config.bedTypeId)
    );
    
    const progress = totalRooms > 0 ? (completedRooms / totalRooms) * 100 : 0;
    
    // Can only add new hotel if there are no incomplete hotels
    const canAddNewHotel = incompleteHotels.length === 0;

    if (incompleteHotels.length > 0) {
      return { 
        status: 'incomplete', 
        message: `${incompleteHotels.length} hotel${incompleteHotels.length > 1 ? 's' : ''} need${incompleteHotels.length === 1 ? 's' : ''} to be selected first`,
        completedRooms,
        totalRooms,
        progress,
        canAddNewHotel,
        incompleteHotels,
        showStatusSection: true // Show status section when there are meaningful configurations
      };
    } else if (completedRooms === 0) {
      return { 
        status: 'started', 
        message: 'Hotels selected, configure rooms and beds',
        completedRooms,
        totalRooms,
        progress,
        canAddNewHotel,
        incompleteHotels,
        showStatusSection: true
      };
    } else if (completedRooms === totalRooms) {
      return { 
        status: 'complete', 
        message: 'All hotels fully configured',
        completedRooms,
        totalRooms,
        progress,
        canAddNewHotel,
        incompleteHotels,
        showStatusSection: true
      };
    } else {
      return { 
        status: 'partial', 
        message: 'Some rooms need configuration',
        completedRooms,
        totalRooms,
        progress,
        canAddNewHotel,
        incompleteHotels,
        showStatusSection: true
      };
    }
  }, [hotelConfigurations]);

  // Group configurations by hotel ID - Enhanced to handle incomplete configurations
  const groupedConfigurations = React.useMemo(() => {
    const grouped = {};
    const unconfiguredHotels = [];
    
    // Only process configurations if there are meaningful hotel selections
    const meaningfulConfigurations = hotelConfigurations.filter(config => config.hotelId);
    
    // If no meaningful configurations, return empty object (will show empty state)
    if (meaningfulConfigurations.length === 0) {
      return {};
    }
    
    hotelConfigurations.forEach((config, index) => {
      if (config.hotelId) {
        // Configured hotels with valid hotelId
        if (!grouped[config.hotelId]) {
          grouped[config.hotelId] = {
            hotelDetails: config.hotelDetails || {},
            configurations: [],
            isConfigured: true
          };
        }
        grouped[config.hotelId].configurations.push({
          ...config,
          originalIndex: index
        });
      } else {
        // Only show unconfigured hotels if there are also configured ones
        // This prevents showing empty placeholder when no hotels selected yet
        if (meaningfulConfigurations.length > 0) {
          const unconfiguredGroupKey = `unconfigured_${index}`;
          grouped[unconfiguredGroupKey] = {
            hotelDetails: { hotel_name: `Hotel ${unconfiguredHotels.length + 1} (Not Selected)` },
            configurations: [{
              ...config,
              originalIndex: index
            }],
            isConfigured: false,
            isUnconfigured: true
          };
          unconfiguredHotels.push(config);
        }
      }
    });
    
    return grouped;
  }, [hotelConfigurations]);

  // Update expanded accordions when activeHotelIndex changes
  useEffect(() => {
    if (activeHotelIndex !== null && activeHotelIndex !== undefined) {
      const activeConfig = hotelConfigurations[activeHotelIndex];
      if (activeConfig && activeConfig.hotelId) {
        setExpandedAccordions(prev => new Set([...prev, activeConfig.hotelId]));
      }
    }
  }, [activeHotelIndex, hotelConfigurations]);

  // Ensure at least one accordion is expanded if there are hotels
  useEffect(() => {
    if (Object.keys(groupedConfigurations).length > 0 && expandedAccordions.size === 0) {
      const firstHotelId = Object.keys(groupedConfigurations)[0];
      setExpandedAccordions(new Set([firstHotelId]));
    }
  }, [groupedConfigurations, expandedAccordions.size]);

  // Empty state with enhanced onboarding
  if (Object.keys(groupedConfigurations).length === 0) {
    return (
      <Paper 
        elevation={0} 
        sx={{ 
          p: 1.5,
          textAlign: 'center', 
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          color: 'white',
          borderRadius: 1.5,
          position: 'relative',
          overflow: 'hidden'
        }}
      >
        {/* Background decoration */}
        <Box
          sx={{
            position: 'absolute',
            top: -20,
            right: -20,
            width: 80,
            height: 80,
            borderRadius: '50%',
            background: 'rgba(255, 255, 255, 0.1)',
            zIndex: 0
          }}
        />
        
        <Box sx={{ position: 'relative', zIndex: 1 }}>
          <HotelIcon sx={{ fontSize: 36, mb: 1, opacity: 0.9 }} />
          <Typography variant="h6" gutterBottom sx={{ fontWeight: 600, fontSize: '1.1rem' }}>
            Let's Book Your Hotels! 🏨
          </Typography>
          
          {/* Tour date display */}
          {tourDateRange && tourDateRange.startDate && tourDateRange.endDate && (
            <Paper 
              sx={{ 
                p: 0.5,
                mb: 1,
                bgcolor: 'rgba(255, 255, 255, 0.15)', 
                backdropFilter: 'blur(10px)',
                borderRadius: 1.5,
                display: 'inline-block'
              }}
            >
              <Typography variant="caption" sx={{ display: 'flex', alignItems: 'center', color: 'white', fontSize: '0.65rem', lineHeight: 1.2 }}>
                <CalendarTodayIcon sx={{ mr: 0.5, fontSize: 12 }} />
                Tour Dates: {new Date(tourDateRange.startDate).toLocaleDateString()} - {new Date(tourDateRange.endDate).toLocaleDateString()} | Hotels will use these dates by default until you select specific nights
              </Typography>
            </Paper>
          )}
        </Box>
      </Paper>
    );
  }

  // Enhanced Status indicator component with validation info
  const StatusIndicator = ({ status, progress, completedRooms, totalRooms, message, canAddNewHotel, incompleteHotels }) => {
    const getStatusConfig = () => {
      switch (status) {
        case 'complete':
          return {
            icon: <TaskAltIcon />,
            color: 'success',
            bgcolor: '#e8f5e8',
            borderColor: '#4caf50'
          };
        case 'incomplete':
          return {
            icon: <ErrorOutlineIcon />,
            color: 'error',
            bgcolor: '#ffebee',
            borderColor: '#f44336'
          };
        case 'partial':
          return {
            icon: <PendingIcon />,
            color: 'warning',
            bgcolor: '#fff8e1',
            borderColor: '#ff9800'
          };
        default:
          return {
            icon: <PlayCircleOutlineIcon />,
            color: 'primary',
            bgcolor: '#e3f2fd',
            borderColor: '#2196f3'
          };
      }
    };

    const config = getStatusConfig();

    return (
      <Paper 
        elevation={1}
        sx={{ 
          p: 1.5, 
          mb: 1.5, 
          bgcolor: config.bgcolor,
          border: `1px solid ${config.borderColor}`,
          borderRadius: 1.5
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
          <Avatar sx={{ bgcolor: config.borderColor, mr: 1, width: 28, height: 28 }}>
            {config.icon}
          </Avatar>
          <Box sx={{ flex: 1 }}>
            <Typography variant="subtitle1" sx={{ color: config.borderColor, fontWeight: 600, fontSize: '0.8rem' }}>
              Hotel Booking Status
            </Typography>
            <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
              {message}
            </Typography>
          </Box>
          <Chip 
            label={`${Math.round(progress)}% Complete`}
            color={config.color}
            variant="filled"
            sx={{ fontWeight: 600, fontSize: '0.65rem', height: '20px' }}
          />
        </Box>
        
        {/* Show incomplete hotel warning */}
        {incompleteHotels && incompleteHotels.length > 0 && (
          <Alert severity="warning" sx={{ mb: 1, py: 0.4 }}>
            <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>
              <strong>Action Required:</strong> Please select a hotel for Room {incompleteHotels[0].index + 1} before adding more hotels.
            </Typography>
          </Alert>
        )}
        
        <LinearProgress 
          variant="determinate" 
          value={progress} 
          sx={{ 
            height: 6, 
            borderRadius: 3,
            bgcolor: 'rgba(0,0,0,0.1)',
            '& .MuiLinearProgress-bar': {
              borderRadius: 3
            }
          }}
        />
      </Paper>
    );
  };

  // Enhanced Quick action buttons with validation
  const QuickActions = ({ canAddNewHotel, incompleteHotels }) => (
    <Paper elevation={1} sx={{ p: 1.5, mb: 1.5, borderRadius: 1.5, bgcolor: '#f8f9fa' }}>
      <Typography variant="subtitle1" gutterBottom sx={{ display: 'flex', alignItems: 'center', color: 'primary.main', fontSize: '0.8rem' }}>
        <EditIcon sx={{ mr: 0.6, fontSize: '1rem' }} />
        Quick Actions
      </Typography>
      
      <Grid container spacing={1}>
        <Grid item xs={12} sm={6}>
          <Button
            fullWidth
            variant="outlined"
            size="small"
            startIcon={<AddIcon />}
            onClick={onAddMoreRooms}
            disabled={!onAddMoreRooms || !canAddNewHotel}
            sx={{ 
              py: 0.8,
              justifyContent: 'flex-start',
              textTransform: 'none',
              fontWeight: 500,
              fontSize: '0.75rem',
              opacity: !canAddNewHotel ? 0.6 : 1
            }}
          >
            <Box sx={{ textAlign: 'left', ml: 0.6 }}>
              <Typography variant="subtitle2" sx={{ fontSize: '0.75rem' }}>Add More Rooms</Typography>
              <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
                {!canAddNewHotel ? 'Select hotel first' : 'Same hotel, additional rooms'}
              </Typography>
            </Box>
          </Button>
        </Grid>
        
        <Grid item xs={12} sm={6}>
          <Tooltip 
            title={!canAddNewHotel ? `Please select a hotel for the current configuration first` : 'Add a different hotel'}
            arrow
          >
            <span>
              <Button
                fullWidth
                variant={canAddNewHotel ? "contained" : "outlined"}
                size="small"
                startIcon={canAddNewHotel ? <AddBusinessIcon /> : <WarningIcon />}
                onClick={canAddNewHotel ? onAddNewHotel : null}
                disabled={!canAddNewHotel}
                color={canAddNewHotel ? "primary" : "warning"}
                sx={{ 
                  py: 0.8,
                  justifyContent: 'flex-start',
                  textTransform: 'none',
                  fontWeight: 500,
                  fontSize: '0.75rem',
                  opacity: !canAddNewHotel ? 0.7 : 1
                }}
              >
                <Box sx={{ textAlign: 'left', ml: 0.6 }}>
                  <Typography variant="subtitle2" color={canAddNewHotel ? "white" : "warning.main"} sx={{ fontSize: '0.75rem' }}>
                    {canAddNewHotel ? 'Add Different Hotel' : 'Complete Current Hotel'}
                  </Typography>
                  <Typography variant="caption" sx={{ 
                    color: canAddNewHotel ? 'rgba(255,255,255,0.8)' : 'rgba(237, 108, 2, 0.7)', 
                    fontSize: '0.65rem' 
                  }}>
                    {canAddNewHotel ? 'New hotel location' : 'Select hotel first'}
                  </Typography>
                </Box>
              </Button>
            </span>
          </Tooltip>
        </Grid>
      </Grid>
      
      {/* Show validation message */}
      {!canAddNewHotel && incompleteHotels && incompleteHotels.length > 0 && (
        <Alert severity="info" sx={{ mt: 1, py: 0.4 }}>
          <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>
            💡 <strong>Tip:</strong> Click on "Room {incompleteHotels[0].index + 1}" below to select a hotel, then you can add more hotels or rooms.
          </Typography>
        </Alert>
      )}
    </Paper>
  );

  // Function to check if hotel overall dates are within tour date range
  const isHotelDateInRange = (overallCheckIn, overallCheckOut) => {
    if (!tourDateRange || !tourDateRange.startDate || !tourDateRange.endDate) {
      return { valid: true, message: '' };
    }

    if (!overallCheckIn || !overallCheckOut) {
      return { valid: true, message: '' };
    }

    const hotelCheckIn = new Date(overallCheckIn);
    const hotelCheckOut = new Date(overallCheckOut);
    const tourStart = new Date(tourDateRange.startDate);
    const tourEnd = new Date(tourDateRange.endDate);

    hotelCheckIn.setHours(0, 0, 0, 0);
    hotelCheckOut.setHours(0, 0, 0, 0);
    tourStart.setHours(0, 0, 0, 0);
    tourEnd.setHours(0, 0, 0, 0);

    if (hotelCheckIn < tourStart) {
      return { 
        valid: false, 
        message: `Check-in date is before tour start date.`
      };
    }

    if (hotelCheckOut > tourEnd) {
      return { 
        valid: false, 
        message: `Check-out date is after tour end date.`
      };
    }

    return { valid: true, message: '' };
  };

  // Handle accordion toggle
  const handleAccordionChange = (hotelId) => {
    const newExpanded = new Set(expandedAccordions);
    if (newExpanded.has(hotelId)) {
      newExpanded.delete(hotelId);
    } else {
      newExpanded.add(hotelId);
    }
    setExpandedAccordions(newExpanded);
  };

  // Format date for display
  const formatDate = (dateString) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  // Get hotel summary statistics
  const getHotelSummary = (configurations) => {
    // Hotel nights should be the same for all rooms in the same hotel, so take from first room
    const hotelNights = configurations.length > 0 ? (configurations[0].nights || 0) : 0;
    const totalGuests = configurations.reduce((sum, config) => sum + (config.selectedGuests || 0), 0);
    const totalRoomCost = configurations.reduce((sum, config) => {
      // Use total price (room + meals) for hotel summary
      const roomTotalPrice = config.mealPlanDetails?.totalPrice || 0;
      return sum + roomTotalPrice;
    }, 0);
    
    const dateRanges = configurations
      .filter(config => config.checkInDate && config.checkOutDate)
      .map(config => ({
        checkIn: formatDate(config.checkInDate),
        checkOut: formatDate(config.checkOutDate),
        checkInDate: new Date(config.checkInDate),
        checkOutDate: new Date(config.checkOutDate)
      }));
    
    let overallCheckIn = null;
    let overallCheckOut = null;
    let isUsingTourDates = false;
    
    if (dateRanges.length > 0) {
      // Use specific hotel dates if available
      overallCheckIn = new Date(Math.min(...dateRanges.map(range => range.checkInDate.getTime())));
      overallCheckOut = new Date(Math.max(...dateRanges.map(range => range.checkOutDate.getTime())));
      overallCheckIn = formatDate(overallCheckIn);
      overallCheckOut = formatDate(overallCheckOut);
    } else if (tourDateRange && tourDateRange.startDate && tourDateRange.endDate) {
      // Fallback to tour dates if no specific hotel dates are selected
      overallCheckIn = formatDate(tourDateRange.startDate);
      overallCheckOut = formatDate(tourDateRange.endDate);
      isUsingTourDates = true;
    }
    
    return { 
      totalRooms: configurations.length,
      hotelNights, 
      totalGuests, 
      totalRoomCost, // Updated variable name to reflect total room cost (room + meals)
      dateRanges,
      overallCheckIn,
      overallCheckOut,
      isUsingTourDates
    };
  };
  
  // Helper function to detect date conflicts
  const hasDateConflict = (config, tourDates) => {
    if (!config.selectedNightIndices || config.selectedNightIndices.length === 0 || !tourDates || tourDates.length === 0) {
      return false;
    }

    const maxNightIndex = tourDates.length - 2; // -1 for length to index, -1 because last date is checkout
    return config.selectedNightIndices.some(nightIndex => nightIndex > maxNightIndex);
  };

  // Helper function to get status info including date conflicts
  const getHotelStatusInfo = (config, tourDates) => {
    const hasRooms = config.rooms && config.rooms.length > 0;
    const hasNights = config.selectedNightIndices && config.selectedNightIndices.length > 0;
    const hasConflict = hasDateConflict(config, tourDates);
    const hotelName = config.hotelDetails?.hotel_name || `Hotel ${config.id}`;
    
    // Date conflict takes priority
    if (hasConflict) {
      return {
        status: 'conflict',
        color: 'error',
        icon: <WarningIcon />,
        text: 'Date Issue',
        fullText: `${hotelName} - Date Conflict`,
        description: 'Selected nights are outside the current date range'
      };
    }
    
    if (hasRooms && hasNights) {
      return {
        status: 'complete',
        color: 'success',
        icon: <CheckCircleIcon />,
        text: 'Complete',
        fullText: `${hotelName} - Fully Configured`,
        description: 'Hotel, rooms, and nights selected'
      };
    } else if (hasRooms || hasNights) {
      return {
        status: 'partial',
        color: 'warning',
        icon: <PendingIcon />,
        text: 'Partial',
        fullText: `${hotelName} - Partially Configured`,
        description: hasRooms ? 'Rooms selected, choose nights' : 'Nights selected, choose rooms'
      };
    } else {
      return {
        status: 'incomplete',
        color: 'default',
        icon: <PendingIcon />,
        text: 'Not Started',
        fullText: `${hotelName} - Not Started`,
        description: 'No configuration yet'
      };
    }
  };

  return (
    <Box sx={{ mb: 2 }}>
      {/* Only show status and configuration sections when user has started hotel selection */}
      {bookingStatus.showStatusSection && (
        <>
          {/* Status Overview */}
          <StatusIndicator 
            status={bookingStatus.status}
            progress={bookingStatus.progress}
            completedRooms={bookingStatus.completedRooms}
            totalRooms={bookingStatus.totalRooms}
            message={bookingStatus.message}
            canAddNewHotel={bookingStatus.canAddNewHotel}
            incompleteHotels={bookingStatus.incompleteHotels}
          />

          {/* Quick Actions */}
          <QuickActions 
            canAddNewHotel={bookingStatus.canAddNewHotel}
            incompleteHotels={bookingStatus.incompleteHotels}
          />

          {/* Hotel Configurations Header */}
          <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
            <HotelIcon sx={{ mr: 0.8, color: 'primary.main', fontSize: '1.2rem' }} />
            Your Hotel Bookings ({Object.keys(groupedConfigurations).length})
          </Typography>
        </>
      )}
      
      {Object.entries(groupedConfigurations).map(([hotelId, hotelGroup]) => {
        const hotel = hotelGroup.hotelDetails;
        const configurations = hotelGroup.configurations;
        const isUnconfigured = hotelGroup.isUnconfigured;
        const hotelSummary = getHotelSummary(configurations);
        
        const hotelDateValidation = isHotelDateInRange(
          hotelSummary.overallCheckIn, 
          hotelSummary.overallCheckOut
        );
        
        const hasDateIssues = !hotelDateValidation.valid;
        const isActiveHotel = configurations.some(config => config.originalIndex === activeHotelIndex);
        
        // Check for date conflicts in any room configuration
        const hasDateConflicts = configurations.some(config => hasDateConflict(config, tourDates));
        
        // Calculate completion status for this hotel
        const completedRooms = configurations.filter(config => 
          config.hotelId && config.roomTypeId && config.bedTypeId
        ).length;
        const totalRooms = configurations.length;
        const hotelProgress = totalRooms > 0 ? (completedRooms / totalRooms) * 100 : 0;
        
        // Get the overall status for this hotel
        const statusInfo = getHotelStatusInfo(configurations[0], tourDates);
        const hasAnyIssues = hasDateIssues || hasDateConflicts;
        
        const isExpanded = expandedAccordions.has(hotelId);
        
        return (
                     <Accordion 
            key={hotelId}
            expanded={isExpanded}
            onChange={() => handleAccordionChange(hotelId)}
            elevation={isActiveHotel ? 2 : 1}
            sx={{ 
              mb: 1,
              border: isUnconfigured
                ? '2px dashed #f44336'
                : hasDateConflicts 
                  ? '1px solid #f44336' 
                  : isActiveHotel 
                    ? '1px solid #3554D1' 
                    : hasDateIssues 
                      ? '1px solid #ff9800' 
                      : '1px solid rgba(0, 0, 0, 0.12)',
              borderRadius: 1.5,
              background: isUnconfigured
                ? 'linear-gradient(135deg, rgba(244, 67, 54, 0.08) 0%, rgba(244, 67, 54, 0.03) 100%)'
                : hasDateConflicts
                  ? 'linear-gradient(135deg, rgba(244, 67, 54, 0.05) 0%, rgba(244, 67, 54, 0.02) 100%)'
                  : isActiveHotel 
                    ? 'linear-gradient(135deg, rgba(53, 84, 209, 0.05) 0%, rgba(53, 84, 209, 0.02) 100%)'
                    : 'white',
              transition: 'all 0.3s ease',
              '&:before': {
                display: 'none' // Remove default MUI accordion border
              }
            }}
          >
            {/* Unconfigured Hotel Alert */}
            {isUnconfigured && (
              <Alert 
                severity="error" 
                icon={<ErrorOutlineIcon />}
                sx={{ borderRadius: '8px 8px 0 0', border: 'none', bgcolor: '#ffebee' }}
              >
                <Typography variant="body2">
                  <strong>Hotel Selection Required:</strong> Please select a hotel for this room configuration.
                  <br />
                  <em>Click on the room below and choose your preferred hotel to continue.</em>
                </Typography>
              </Alert>
            )}
            
            {/* Date Range Warning */}
            {!isUnconfigured && (hasDateIssues || hasDateConflicts) && (
              <Alert 
                severity={hasDateConflicts ? "error" : "warning"} 
                icon={<WarningIcon />}
                sx={{ borderRadius: '8px 8px 0 0', border: 'none' }}
              >
                <Typography variant="body2">
                  {hasDateConflicts ? (
                    <>
                      <strong>Night Selection Issue:</strong> Some selected nights are outside the updated tour date range.
                      <br />
                      <em>Please update your night selection in the configuration section below.</em>
                    </>
                  ) : (
                    <>
                      <strong>Date Conflict:</strong> {hotelDateValidation.message}
                      <br />
                      <em>Please adjust the room dates to fit within your tour schedule.</em>
                    </>
                  )}
                </Typography>
              </Alert>
            )}
            
             <AccordionSummary
              expandIcon={
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.4 }}>
                  {isUnconfigured && <ErrorOutlineIcon sx={{ color: 'error.main', fontSize: 14 }} />}
                  {!isUnconfigured && hasDateConflicts && <ErrorOutlineIcon sx={{ color: 'error.main', fontSize: 14 }} />}
                  {!isUnconfigured && hasDateIssues && !hasDateConflicts && <WarningIcon sx={{ color: 'warning.main', fontSize: 14 }} />}
                  {isActiveHotel && <EditIcon sx={{ color: 'primary.main', fontSize: 12 }} />}
                  <ExpandMoreIcon />
                </Box>
              }
               sx={{ 
                 p: 1,
                 minHeight: '40px',
                 '& .MuiAccordionSummary-content': { 
                   margin: 0,
                   '&.Mui-expanded': { margin: 0 }
                 },
                 '&:hover': {
                   bgcolor: 'rgba(0, 0, 0, 0.02)'
                 }
               }}
             >
                             {/* Hotel Header - Compact Summary */}
              <Box sx={{ display: 'flex', alignItems: 'center', flex: 1, pr: 1 }}>
                <Avatar 
                  sx={{
                    bgcolor: isUnconfigured 
                      ? '#f44336' 
                      : hasDateConflicts 
                        ? '#f44336' 
                        : hasDateIssues 
                          ? '#ff9800' 
                          : '#3554D1', 
                    width: 28,
                    height: 28,
                    mr: 0.8,
                    border: isUnconfigured ? '2px solid #f44336' : 'none'
                  }}
                >
                  {isUnconfigured ? <ErrorOutlineIcon fontSize="small" /> : <HotelIcon fontSize="small" />}
                </Avatar>
                 
                 <Box sx={{ flex: 1 }}>
                   <Box sx={{ display: 'flex', alignItems: 'center', flexWrap: 'wrap', gap: 0.6 }}>
                     <Typography variant="subtitle1" sx={{ fontWeight: 600, fontSize: '0.8rem' }}>
                       {hotel.hotel_name || hotel.name || 'Hotel'}
                     </Typography>
                     
                     {isActiveHotel && (
                       <Chip label="Editing" size="small" color="primary" sx={{ fontSize: '0.65rem', height: '20px' }} />
                     )}
                     
                     {isUnconfigured && (
                       <Chip 
                         label="Select Hotel" 
                         size="small" 
                         color="error" 
                         icon={<ErrorOutlineIcon />}
                         sx={{ fontSize: '0.65rem', height: '20px', animation: 'pulse 1.5s infinite' }}
                       />
                     )}
                     
                     {!isUnconfigured && hasDateConflicts && (
                       <Chip 
                         label="Date Issue" 
                         size="small" 
                         color="error" 
                         icon={<ErrorOutlineIcon />}
                         sx={{ fontSize: '0.65rem', height: '20px' }}
                       />
                     )}
                     
                     {!isUnconfigured && hasDateIssues && !hasDateConflicts && (
                       <Chip label="Issue" size="small" color="warning" sx={{ fontSize: '0.65rem', height: '20px' }} />
                     )}
                     
                     {!isUnconfigured && (
                       <Chip 
                         label={`${Math.round(hotelProgress)}% Complete`}
                         size="small" 
                         color={hotelProgress === 100 ? 'success' : hotelProgress > 0 ? 'warning' : 'default'}
                         sx={{ fontSize: '0.65rem', height: '20px' }}
                       />
                     )}
                   </Box>
                   
                   <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.4, mt: 0.4 }}>
                     <Chip 
                       size="small" 
                       label={`${totalRooms} Room${totalRooms !== 1 ? 's' : ''}`}
                       variant="outlined"
                       color="primary"
                       sx={{ fontSize: '0.65rem', height: '20px' }}
                     />
                     {hotelSummary.hotelNights > 0 ? (
                       <Chip 
                         size="small" 
                         label={`${hotelSummary.hotelNights} Night${hotelSummary.hotelNights !== 1 ? 's' : ''}`}
                         variant="outlined"
                         color="secondary"
                         sx={{ fontSize: '0.65rem', height: '20px' }}
                       />
                     ) : hotelSummary.isUsingTourDates ? (
                       <Chip 
                         size="small" 
                         label="Tour Dates"
                         variant="outlined"
                         color="warning"
                         sx={{ fontStyle: 'italic', fontSize: '0.65rem', height: '20px' }}
                       />
                     ) : null}
                     <Chip 
                       size="small" 
                       label={`${hotelSummary.totalGuests} Guest${hotelSummary.totalGuests !== 1 ? 's' : ''}`}
                       variant="outlined"
                       color="info"
                       sx={{ fontSize: '0.65rem', height: '20px' }}
                     />
                   </Box>
                 </Box>
                 
                                 {/* Price Display - Compact */}
               {isUnconfigured ? (
                  <Chip
                    label="Select Hotel First"
                    size="small"
                    color="error"
                    variant="outlined"
                    sx={{ fontWeight: 600, fontSize: '0.65rem', height: '20px' }}
                  />
                ) : hotelSummary.totalRoomCost > 0 && PriceHide === "0" ? (
                  <Chip
                    label={`$${hotelSummary.totalRoomCost.toFixed(2)}`}
                    size="small"
                    color="success"
                    variant="filled"
                    sx={{ fontWeight: 600, fontSize: '0.65rem', height: '20px' }}
                  />
                ) : (
                  <Chip
                    label="Price hide"
                    size="small"
                    color="success"
                    variant="filled"
                    sx={{ fontWeight: 600, fontSize: '0.65rem', height: '20px' }}
                  />
                )}
               </Box>
            </AccordionSummary>

                         <AccordionDetails sx={{ p: 1 }}>
               {/* Full Hotel Details */}
               <Box sx={{ p: 1.5 }}>
                 <Typography variant="body2" color="text.secondary" gutterBottom sx={{ fontSize: '0.8rem' }}>
                   📍 {hotel.address || hotel.location || 'Hotel Location'}
                 </Typography>
                 
                 {hotelSummary.overallCheckIn && hotelSummary.overallCheckOut && (
                   <Typography variant="body2" color="text.secondary" gutterBottom sx={{ fontSize: '0.8rem' }}>
                     📅 {hotelSummary.overallCheckIn} - {hotelSummary.overallCheckOut}
                     {hotelSummary.isUsingTourDates && (
                       <Typography component="span" variant="caption" sx={{ 
                         ml: 0.8, 
                         color: 'warning.main', 
                         fontStyle: 'italic',
                         fontSize: '0.7rem'
                       }}>
                         (Tour dates - select nights to customize)
                       </Typography>
                     )}
                   </Typography>
                 )}
               </Box>

               {/* Room Cards */}
               <Typography variant="subtitle1" gutterBottom sx={{ fontWeight: 600, mb: 1, fontSize: '0.85rem' }}>
                 Room Configurations
               </Typography>

               <Grid container spacing={1}>
                {configurations.map((config, configIndex) => {
                  const isActive = config.originalIndex === activeHotelIndex;
                  const isComplete = config.hotelId && config.roomTypeId && config.bedTypeId;

                  return (
                    <Grid item xs={12} sm={6} md={4} key={config.id}>
                                             <Card
                         elevation={isActive ? 3 : 1}
                         sx={{
                           cursor: 'pointer',
                           border: isActive ? '2px solid #3554D1' : '1px solid rgba(0, 0, 0, 0.12)',
                           borderRadius: 1.5,
                           transition: 'all 0.2s ease',
                           background: isActive 
                             ? 'linear-gradient(135deg, rgba(53, 84, 209, 0.08) 0%, rgba(53, 84, 209, 0.02) 100%)'
                             : 'white',
                           '&:hover': {
                             transform: 'translateY(-1px)',
                             boxShadow: 3
                           }
                         }}
                         onClick={() => selectHotelConfiguration(config.originalIndex)}
                       >
                         <CardContent sx={{ p: 0.8 }}>
                           {/* Room Header */}
                           <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 0.8 }}>
                             <Box sx={{ display: 'flex', alignItems: 'center' }}>
                               <Avatar sx={{ 
                                 bgcolor: isComplete ? '#4caf50' : '#ff9800', 
                                 width: 24, 
                                 height: 24, 
                                 mr: 0.6 
                               }}>
                                 {isComplete ? <CheckCircleIcon fontSize="small" /> : <PendingIcon fontSize="small" />}
                               </Avatar>
                               <Typography variant="caption" sx={{ fontWeight: 600, fontSize: '0.75rem' }}>
                                 Room {configIndex + 1}
                               </Typography>
                             </Box>
                             {(tourStatus !== "Confirmed" && tourStatus !== "Definite" && tourStatus !== "Actual") && (
                             <IconButton
                               size="small"
                               color="error"
                               onClick={(e) => {
                                 e.stopPropagation();
                                 removeHotelConfiguration(config.originalIndex);
                               }}
                               sx={{ width: 24, height: 24 }}
                             >
                               <DeleteIcon fontSize="small" />
                             </IconButton>
                             )}
                           </Box>

                                                     {/* Room Details */}
                           <Box sx={{ mb: 0.8 }}>
                             <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 0.4, fontSize: '0.65rem' }}>
                               <strong>Room:</strong> {config.roomTypeName || 'Not selected'}
                             </Typography>
                             <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 0.4, fontSize: '0.65rem' }}>
                               <strong>Bed:</strong> {config.bedTypeName || 'Not selected'}
                             </Typography>
                             <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 0.4, fontSize: '0.65rem' }}>
                               <strong>Guests:</strong> {config.selectedGuests || 0} • <strong>Nights:</strong> {config.nights || 0}
                             </Typography>
                             
                                                         {/* Room Meal Plan */}
                            <Box sx={{ mt: 0.6 }}>
                              <Typography variant="caption" sx={{ 
                                fontWeight: 600, 
                                color: 'primary.main',
                                display: 'flex',
                                alignItems: 'center',
                                fontSize: '0.65rem'
                              }}>
                                <RestaurantMenuIcon sx={{ fontSize: 10, mr: 0.3 }} />
                                Room Meal Plan:
                              </Typography>
                              
                              {config.selectedMealPlan && config.mealPlanDetails ? (
                                <Box sx={{ mt: 0.4 }}>
                                  <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 0.4 }}>
                                    <Typography variant="caption" sx={{ 
                                      color: 'text.secondary',
                                      fontSize: '0.65rem',
                                      flex: 1
                                    }}>
                                      {config.mealPlanDetails.title || 'Room Only'}
                                    </Typography>
                                    {config.mealPlanDetails.totalPrice > 0 && PriceHide === "0" ? (
                                      <Typography variant="caption" sx={{ 
                                        color: 'success.main',
                                        fontSize: '0.6rem',
                                        fontWeight: 600
                                      }}>
                                        ${config.mealPlanDetails.totalPrice.toFixed(2)}
                                      </Typography>
                                    ) : PriceHide !== "0" ? (
                                      <Typography variant="caption" sx={{ 
                                        color: 'success.main',
                                        fontSize: '0.6rem',
                                        fontWeight: 600
                                      }}>
                                        Price hidden
                                      </Typography>
                                    ) : null}
                                  </Box>
                                  
                                  {/* Guest Count & Per Person Price Info */}
                                  {config.selectedGuests > 1 && config.mealPlanDetails.totalPrice > 0 && (
                                    <Box sx={{ 
                                      pt: 0.4, 
                                      borderTop: '1px dashed rgba(76, 175, 80, 0.3)',
                                      display: 'flex', 
                                      justifyContent: 'space-between',
                                      alignItems: 'center'
                                    }}>
                                      <Typography variant="caption" sx={{ 
                                        fontSize: '0.6rem',
                                        color: 'text.secondary'
                                      }}>
                                        For {config.selectedGuests} guests:
                                      </Typography>
                                      {PriceHide === "0" && (
                                        <Typography variant="caption" sx={{ 
                                          fontSize: '0.6rem',
                                          color: 'success.main'
                                        }}>
                                          ${(config.mealPlanDetails.totalPrice / config.selectedGuests).toFixed(2)} per guest
                                        </Typography>
                                      )}
                                    </Box>
                                  )}
                                  
                                  {/* Meal Plan Breakdown */}
                                  {config.mealPlanDetails.price > 0 && PriceHide === "0" && (
                                    <Box sx={{ 
                                      pt: 0.4, 
                                      mt: 0.4,
                                      borderTop: '1px dashed rgba(76, 175, 80, 0.2)',
                                      display: 'flex', 
                                      justifyContent: 'space-between',
                                      alignItems: 'center'
                                    }}>
                                      <Typography variant="caption" sx={{ 
                                        fontSize: '0.6rem',
                                        color: 'text.secondary'
                                      }}>
                                        Meals only:
                                      </Typography>
                                      <Typography variant="caption" sx={{ 
                                        fontSize: '0.6rem',
                                        color: 'text.secondary'
                                      }}>
                                        ${config.mealPlanDetails.price.toFixed(2)}
                                      </Typography>
                                    </Box>
                                  )}
                                  
                                  {/* Room + Meal Total */}
                                  {config.mealPlanDetails.totalPrice > 0 && (
                                    <Box sx={{ 
                                      pt: 0.4, 
                                      mt: 0.4,
                                      borderTop: '1px solid rgba(76, 175, 80, 0.3)',
                                      display: 'flex', 
                                      justifyContent: 'space-between',
                                      alignItems: 'center'
                                    }}>
                                      <Typography variant="caption" sx={{ 
                                        fontWeight: 600,
                                        fontSize: '0.65rem',
                                        color: 'success.main'
                                      }}>
                                        Room + Meal Total:
                                      </Typography>
                                      {PriceHide === "0" ? (
                                        <Chip 
                                          label={`$${config.mealPlanDetails.totalPrice.toFixed(2)}`}
                                          size="small" 
                                          color="success"
                                          variant="filled"
                                          sx={{ 
                                            height: 16, 
                                            fontSize: '0.6rem',
                                            fontWeight: 600
                                          }}
                                        />
                                      ) : (
                                        <Chip 
                                          label="Price hidden"
                                          size="small"
                                          color="success"
                                          variant="filled"
                                          sx={{ fontWeight: 600, fontSize: '0.6rem', height: '16px' }}
                                        />
                                      )}
                                    </Box>
                                  )}
                                </Box>
                              ) : (
                                <Box sx={{ mt: 0.4 }}>
                                  <Typography variant="caption" sx={{ 
                                    color: 'warning.main',
                                    fontSize: '0.65rem',
                                    fontStyle: 'italic'
                                  }}>
                                    No meal plan selected yet
                                  </Typography>
                                </Box>
                              )}
                            </Box>
                           </Box>

                                                     {/* Action Indicator */}
                           <Box sx={{ textAlign: 'center' }}>
                             {isActive ? (
                               <Chip 
                                 label="Currently Editing" 
                                 size="small" 
                                 color="primary" 
                                 variant="filled"
                                 sx={{ fontSize: '0.65rem', height: '20px' }}
                               />
                             ) : (
                               <Chip 
                                 label="Click to Edit" 
                                 size="small" 
                                 variant="outlined"
                                 sx={{ fontSize: '0.65rem', height: '20px', '&:hover': { bgcolor: 'primary.main', color: 'white' } }}
                               />
                             )}
                           </Box>
                        </CardContent>
                      </Card>
                    </Grid>
                  );
                })}
              </Grid>

                             {/* Hotel-specific Actions */}
               <Box sx={{ mt: 1, pt: 0.8, borderTop: '1px dashed rgba(0, 0, 0, 0.12)' }}>
                 <Grid container spacing={1}>
                   <Grid item xs={12} sm={6}>
                     <Button
                       fullWidth
                       variant="outlined"
                       size="small"
                       startIcon={<AddIcon />}
                       onClick={() => onAddMoreRooms && onAddMoreRooms()}
                       sx={{ textTransform: 'none', fontSize: '0.7rem', py: 0.6 }}
                     >
                       Add More Rooms to This Hotel
                     </Button>
                   </Grid>
                   <Grid item xs={12} sm={6}>
                    {(tourStatus !== "Confirmed" && tourStatus !== "Definite" && tourStatus !== "Actual") && (
                     <Button
                       fullWidth
                       variant="text"
                       size="small"
                       color="error"
                       startIcon={<DeleteIcon />}
                       onClick={() => {
                         // Remove all rooms of this hotel
                         configurations.forEach(config => {
                           removeHotelConfiguration(config.originalIndex);
                         });
                       }}
                       sx={{ textTransform: 'none', fontSize: '0.7rem', py: 0.6 }}
                     >
                       Remove This Hotel
                     </Button>
                     )}
                   </Grid>
                 </Grid>
               </Box>
            </AccordionDetails>
          </Accordion>
        );
      })}
      
      {/* Show hotel configurations only when there are any */}
      {!bookingStatus.showStatusSection && Object.keys(groupedConfigurations).length === 0 && (
        <Box sx={{ textAlign: 'center', py: 2 }}>
          <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.9rem' }}>
            🏨 No hotels selected yet. Hotels are optional - you can add them anytime during your tour planning.
          </Typography>
        </Box>
      )}
    </Box>
  );
};

export default HotelConfigSummary;