import React, { useState, useMemo, useCallback, memo } from 'react';
import { 
  Box, 
  Typography,
  Card,
  CardContent,
  Popover,
  Button,
  Paper,
  styled,
  Chip
} from '@mui/material';
import TimerIcon from '@mui/icons-material/Timer';
import NightsStayIcon from '@mui/icons-material/NightsStay';
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

const PackageButton = styled(Button)(({ theme, isSelected }) => ({
  width: '100%',
  justifyContent: 'space-between',
  padding: '12px',
  marginBottom: '8px',
  backgroundColor: isSelected 
    ? 'rgba(219, 234, 254, 1)'
    : 'rgba(237, 242, 255, 0.85)',
  color: '#1E3A8A',
  '&:hover': {
    backgroundColor: 'rgba(191, 219, 254, 1)',
    transform: 'translateY(-2px)',
  },
  borderRadius: '8px',
  textTransform: 'none'
}));

const PackageSelection = ({ value, onChange, disabled, pickUpTime }) => {
  const [anchorEl, setAnchorEl] = useState(null);
  const selectedGuide = useSelector(state => state.tourguide.selectedGuide);
  const exchangeRate = useSelector(state => state.auth.exchangeRate) || 1;
  const currencyCode = useSelector(state => state.auth.currencyCode) || 'USD';
  const PriceHide = useSelector(state => state.auth.PriceHide) || '1';
  
  // Parse night time limits from guide data
  const nightStartTime = selectedGuide?.night_start_time || "21:00"; // Default to 9 PM
  const nightEndTime = selectedGuide?.night_end_time || "00:00"; // Default to 12 AM
  
  // Parse pickup time to calculate night hours
  const parseTimeToHour = useCallback((timeStr) => {
    if (!timeStr) return -1;
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

  const packages = useMemo(() => {
    if (!selectedGuide?.prices) {
      console.log("No prices available in selectedGuide:", selectedGuide);
      return [];
    }

    const prices = selectedGuide.prices;

    const hourlyPackages = [
      { hours: 1, price: prices.dmc_hourly_price || prices.travclicks_hourly_price },
      { hours: 2, price: prices.dmc_two_hour_price || prices.travclicks_two_hour_price },
      { hours: 4, price: prices.dmc_four_hour_price || prices.travclicks_four_hour_price },
      { hours: 6, price: prices.dmc_six_hour_price || prices.travclicks_six_hour_price },
      { hours: 8, price: prices.dmc_eight_hour_price || prices.travclicks_eight_hour_price },
      { hours: 10, price: prices.dmc_ten_hour_price || prices.travclicks_ten_hour_price },
      { hours: 12, price: prices.dmc_twelve_hour_price || prices.travclicks_twelve_hour_price }
    ].filter(pkg => pkg.price > 0);

    return hourlyPackages;
  }, [selectedGuide]);

  // Calculate price breakdown between day and night hours
  const calculatePackagePriceBreakdown = useCallback((packageHours) => {
    if (!selectedGuide?.prices || !pickUpTime || pickUpTime.hourValue === undefined) {
      return {
        basePrice: 0,
        nightSurcharge: 0,
        totalPrice: 0,
        nightHours: 0,
        dayHours: 0
      };
    }
    
    const prices = selectedGuide.prices;
    const basePrice = (() => {
      switch (packageHours) {
        case 1: return prices.dmc_hourly_price || prices.travclicks_hourly_price || 0;
        case 2: return prices.dmc_two_hour_price || prices.travclicks_two_hour_price || 0;
        case 4: return prices.dmc_four_hour_price || prices.travclicks_four_hour_price || 0;
        case 6: return prices.dmc_six_hour_price || prices.travclicks_six_hour_price || 0;
        case 8: return prices.dmc_eight_hour_price || prices.travclicks_eight_hour_price || 0;
        case 10: return prices.dmc_ten_hour_price || prices.travclicks_ten_hour_price || 0;
        case 12: return prices.dmc_twelve_hour_price || prices.travclicks_twelve_hour_price || 0;
        default: return 0;
      }
    })();
    
    // Calculate how many hours fall within night hours
    let nightHours = 0;
    let dayHours = 0;
    const startHour = pickUpTime.hourValue; // 0-23 hour format
    
    for (let i = 0; i < packageHours; i++) {
      const currentHour = (startHour + i) % 24;
      if (isNightHour(currentHour)) {
        nightHours++;
      } else {
        dayHours++;
      }
    }
    
    // Calculate night surcharge based on hourly rate
    const nightSurchargeRate = prices.dmc_night_surcharge || 0;
    const nightSurcharge = nightHours > 0 ? nightSurchargeRate * nightHours : 0;
    
    return {
      basePrice,
      nightSurcharge,
      totalPrice: basePrice + nightSurcharge,
      nightHours,
      dayHours
    };
  }, [pickUpTime, selectedGuide, isNightHour]);

  const handleClick = useCallback((event) => {
    if (!disabled) {
      setAnchorEl(event.currentTarget);
    }
  }, [disabled]);

  const handleClose = useCallback(() => {
    setAnchorEl(null);
  }, []);

  const handlePackageSelect = useCallback((hours) => {
    // Calculate price breakdown for the selected package
    const priceBreakdown = calculatePackagePriceBreakdown(hours);
    
    onChange({ 
      value: hours,
      priceBreakdown: priceBreakdown 
    });
    handleClose();
  }, [onChange, handleClose, calculatePackagePriceBreakdown]);

  const open = Boolean(anchorEl);
  const id = open ? 'package-popover' : undefined;

  const selectedPackage = useMemo(() => {
    return packages.find(pkg => pkg.hours === value);
  }, [packages, value]);

  // Calculate price breakdown for currently selected package
  const currentPriceBreakdown = useMemo(() => {
    if (!value) return null;
    return calculatePackagePriceBreakdown(value);
  }, [value, calculatePackagePriceBreakdown]);

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
        <CardContent sx={{ p: 2, '&:last-child': { pb: 2 } }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <TimerIcon sx={{ color: 'primary.main' }} />
            <Typography>
              {selectedPackage 
                ? `${selectedPackage.hours} Hour Package`
                : 'Select Duration'}
            </Typography>
          </Box>
          {currentPriceBreakdown?.nightHours > 0 && (
            <Box sx={{ mt: 1, display: 'flex', alignItems: 'center', gap: 1 }}>
              <NightsStayIcon fontSize="small" sx={{ color: '#B45309' }} />
              <Typography variant="caption" color="#B45309">
                Includes {currentPriceBreakdown.nightHours} night hour{currentPriceBreakdown.nightHours > 1 ? 's' : ''}
              </Typography>
            </Box>
          )}
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
            width: '350px',
            mt: 1,
            p: 3,
            overflow: 'visible',
            '&:before': {
              content: '""',
              display: 'block',
              position: 'absolute',
              top: 0,
              left: 32,
              width: 10,
              height: 10,
              bgcolor: 'background.paper',
              transform: 'translateY(-50%) rotate(45deg)',
              zIndex: 0,
            },
          }
        }}
      >
        <Typography variant="h6" sx={{ mb: 2, fontWeight: 600 }}>
          Select Duration Package
        </Typography>

        {!pickUpTime && (
          <Typography variant="body2" sx={{ mb: 2, color: 'warning.main' }}>
            Please select pick-up time first to see price breakdown
          </Typography>
        )}

        <Box sx={{ maxHeight: '400px', overflow: 'auto', pr: 1 }}>
          {packages.map((pkg) => {
            const isSelected = value === pkg.hours;
            const priceBreakdown = pickUpTime ? calculatePackagePriceBreakdown(pkg.hours) : null;
            const hasNightHours = priceBreakdown && priceBreakdown.nightHours > 0;
            const adjustedPrice = priceBreakdown ? 
              Math.ceil(priceBreakdown.totalPrice * exchangeRate) :
              Math.ceil(pkg.price * exchangeRate);

            return (
              <PackageButton
                key={pkg.hours}
                onClick={() => handlePackageSelect(pkg.hours)}
                isSelected={isSelected}
              >
                <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start' }}>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <TimerIcon sx={{ mr: 1, fontSize: 20 }} />
                    <Typography variant="body1" sx={{ fontWeight: 500 }}>
                      {pkg.hours} Hour Package
                    </Typography>
                  </Box>
                  {hasNightHours && (
                    <Box sx={{ display: 'flex', alignItems: 'center', mt: 0.5 }}>
                      <NightsStayIcon sx={{ mr: 1, fontSize: 16, color: '#B45309' }} />
                      <Typography variant="caption" color="#B45309">
                        Includes {priceBreakdown.nightHours} night hour{priceBreakdown.nightHours > 1 ? 's' : ''}
                      </Typography>
                    </Box>
                  )}
                </Box>
                {PriceHide === '0' && (
                  <Chip 
                    label={`${adjustedPrice} ${currencyCode}`}
                    sx={{ 
                      backgroundColor: 'rgba(25, 118, 210, 0.08)',
                      color: 'primary.main',
                      fontWeight: 600
                    }}
                  />
                )}
              </PackageButton>
            );
          })}
        </Box>
      </Popover>
    </Box>
  );
};

export default memo(PackageSelection); 