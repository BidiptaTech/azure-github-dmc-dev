import React, { useState, useCallback, useMemo, memo } from 'react';
import { 
  Box, 
  Typography,
  Card,
  CardContent,
  Popover,
  Stack,
  Button,
  Paper,
  styled,
  Chip
} from '@mui/material';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import NightsStayIcon from '@mui/icons-material/NightsStay';
import WbSunnyIcon from '@mui/icons-material/WbSunny';
import { useSelector } from 'react-redux';

// Styled components
const StyledCard = styled(Card)(({ theme }) => ({
  '&:hover': {
    boxShadow: theme.shadows[4],
    transform: 'translateY(-2px)',
    transition: 'all 0.3s ease'
  },
  transition: 'all 0.3s ease',
  cursor: 'pointer'
}));

const TimeButton = styled(Button)(({ theme, isNightTime, isSelected, disabled }) => ({
  width: '100%',
  justifyContent: 'flex-start',
  padding: '12px',
  marginBottom: '8px',
  backgroundColor: isSelected 
    ? (isNightTime ? 'rgba(254, 215, 215, 1)' : 'rgba(219, 234, 254, 1)')
    : (isNightTime ? 'rgba(255, 235, 235, 0.85)' : 'rgba(237, 242, 255, 0.85)'),
  color: isNightTime ? '#9A3412' : '#1E3A8A',
  opacity: disabled ? 0.5 : 1,
  pointerEvents: disabled ? 'none' : 'auto',
  '&:hover': {
    backgroundColor: isNightTime 
      ? 'rgba(254, 202, 202, 1)' 
      : 'rgba(191, 219, 254, 1)',
    transform: 'translateY(-2px)',
  },
  borderRadius: '8px',
  textTransform: 'none'
}));

const TimeSelection = ({ value, onChange, disabled }) => {
  const [anchorEl, setAnchorEl] = useState(null);
  const selectedGuide = useSelector(state => state.tourguide.selectedGuide);

  // Parse night time limits from guide data
  const nightStartTime = selectedGuide?.night_start_time || "21:00"; // Default to 9 PM
  const nightEndTime = selectedGuide?.night_end_time || "00:00";    // Default to 12 AM

  const parseTimeToHour = useCallback((timeStr) => {
    if (!timeStr) return 0;
    if (timeStr.includes("AM") || timeStr.includes("PM")) {
      const [timePart, period] = timeStr.split(" ");
      let [hours, minutes] = timePart.split(":");
      hours = parseInt(hours, 10);
      if (period === "PM" && hours !== 12) hours += 12;
      else if (period === "AM" && hours === 12) hours = 0;
      return hours;
    }
    const [hours] = timeStr.split(":");
    return parseInt(hours, 10);
  }, []);

  const nightStartHour = parseTimeToHour(nightStartTime);
  const nightEndHour = parseTimeToHour(nightEndTime);

  const isNightHour = useCallback((hour) => {
    let adjustedEndHour = nightEndTime.includes(":") && 
      parseInt(nightEndTime.split(":")[1], 10) > 0
      ? (nightEndHour + 1) % 24
      : nightEndHour;

    if (nightStartHour < adjustedEndHour) {
      return hour >= nightStartHour && hour < adjustedEndHour;
    } else {
      return hour >= nightStartHour || hour < adjustedEndHour;
    }
  }, [nightStartHour, nightEndHour, nightEndTime]);

  // Get blocked times from guide's booking details
  const blockedTimes = useMemo(() => {
    const blocked = new Set();
    if (selectedGuide?.bookingDetails?.length) {
      selectedGuide.bookingDetails.forEach(booking => {
        const startHour = parseTimeToHour(booking.start_time);
        const endHour = parseTimeToHour(booking.end_time);
        for (let i = startHour; i < endHour; i++) {
          blocked.add(i);
        }
      });
    }
    return blocked;
  }, [selectedGuide, parseTimeToHour]);

  const handleClick = useCallback((event) => {
    if (!disabled) {
      setAnchorEl(event.currentTarget);
    }
  }, [disabled]);

  const handleClose = useCallback(() => {
    setAnchorEl(null);
  }, []);

  const handleTimeSelect = useCallback((timeLabel, hourValue) => {
    onChange({
      value: timeLabel,
      hourValue: hourValue
    });
    handleClose();
  }, [onChange, handleClose]);

  const open = Boolean(anchorEl);
  const id = open ? 'time-popover' : undefined;

  // Format night hours for display
  const formattedNightHours = useMemo(() => {
    const formatTimeStr = (timeStr) => {
      if (timeStr.includes("AM") || timeStr.includes("PM")) {
        return timeStr;
      }
      // Convert 24h format to AM/PM
      const [hours, minutes] = timeStr.split(":");
      const hour = parseInt(hours, 10);
      const min = minutes || "00";
      const period = hour >= 12 ? "PM" : "AM";
      const displayHour = hour % 12 === 0 ? 12 : hour % 12;
      return `${displayHour}:${min} ${period}`;
    };

    const startFormatted = formatTimeStr(nightStartTime);
    const endFormatted = formatTimeStr(nightEndTime);

    return `${startFormatted} - ${endFormatted}`;
  }, [nightStartTime, nightEndTime]);

  return (
    <Box sx={{ flex: 1 }}>
      <StyledCard 
        variant="outlined" 
        onClick={handleClick}
        sx={{ 
          cursor: disabled ? 'not-allowed' : 'pointer',
          border: '1px solid',
          borderColor: 'divider',
          opacity: disabled ? 0.5 : 1
        }}
      >
        <CardContent sx={{ p: 1.5, '&:last-child': { pb: 1.5 } }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.8 }}>
            <AccessTimeIcon sx={{ color: 'primary.main', fontSize: 18 }} />
            <Typography sx={{ fontSize: '0.8rem' }}>
              {value || 'Select Pick-up Time'}
            </Typography>
          </Box>
        </CardContent>
      </StyledCard>

      <Popover
        id={id}
        open={open}
        anchorEl={anchorEl}
        onClose={handleClose}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'left',
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'left',
        }}
        PaperProps={{
          sx: {
            width: '320px',
            mt: 1,
            p: 2.5,
            overflow: 'visible',
            '&:before': {
              content: '""',
              display: 'block',
              position: 'absolute',
              top: 0,
              left: 32,
              width: 8,
              height: 8,
              bgcolor: 'background.paper',
              transform: 'translateY(-50%) rotate(45deg)',
              zIndex: 0,
            },
          }
        }}
      >
        <Typography variant="subtitle1" sx={{ mb: 1.5, fontWeight: 600, fontSize: '0.9rem' }}>
          Select Pick-up Time
        </Typography>

        <Box sx={{ mb: 1.5, p: 0.8, bgcolor: 'rgba(254, 215, 215, 0.5)', borderRadius: 0.8 }}>
          <Typography variant="body2" sx={{ fontWeight: 500, display: 'flex', alignItems: 'center', mb: 0.4, fontSize: '0.8rem' }}>
            <NightsStayIcon sx={{ mr: 0.8, fontSize: 16 }} />
            Night Hours: {formattedNightHours}
          </Typography>
          <Typography variant="caption" color="error" sx={{ fontSize: '0.7rem' }}>
            Night surcharge applies during these hours
          </Typography>
        </Box>

        <Box sx={{ maxHeight: '300px', overflow: 'auto', pr: 0.8 }}>
          {Array.from({ length: 24 }, (_, index) => {
            const hour = index % 12 === 0 ? 12 : index % 12;
            const period = index < 12 ? "AM" : "PM";
            const timeLabel = `${hour.toString().padStart(2, "0")}:00 ${period}`;
            const isDisabled = blockedTimes.has(index);
            const isNight = isNightHour(index);
            const isSelected = timeLabel === value;

            return (
              <TimeButton
                key={index}
                onClick={() => handleTimeSelect(timeLabel, index)}
                disabled={isDisabled}
                isNightTime={isNight}
                isSelected={isSelected}
              >
                <Box sx={{ display: 'flex', flexDirection: 'column', width: '100%' }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.4 }}>
                    {isNight ? (
                      <NightsStayIcon sx={{ mr: 0.8, fontSize: 18 }} />
                    ) : (
                      <WbSunnyIcon sx={{ mr: 0.8, fontSize: 18 }} />
                    )}
                    <Typography variant="body2" sx={{ fontWeight: 500, fontSize: '0.8rem' }}>
                      {timeLabel}
                    </Typography>
                  </Box>
                  <Typography variant="caption" sx={{ color: isNight ? '#B45309' : '#1E40AF', fontSize: '0.7rem' }}>
                    {isNight ? '*Night surcharge applies' : '*Standard rate applies'}
                  </Typography>
                </Box>
              </TimeButton>
            );
          })}
        </Box>
      </Popover>
    </Box>
  );
};

export default memo(TimeSelection); 