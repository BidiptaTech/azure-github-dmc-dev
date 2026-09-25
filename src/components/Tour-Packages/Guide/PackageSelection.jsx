import React, { useState, useMemo, useCallback, memo } from 'react';
import { 
  Box, 
  Typography,
  Card,
  CardContent,
  Popover,
  Button,
  styled,
  Chip
} from '@mui/material';
import TimerIcon from '@mui/icons-material/Timer';
import NightsStayIcon from '@mui/icons-material/NightsStay';
import { useSelector } from 'react-redux';

// Styled components
const StyledCard = styled(Card)(({ theme }) => ({
  '&:hover': {
    boxShadow: theme.shadows[2],
    transform: 'translateY(-1px)',
    transition: 'all 0.3s ease'
  },
  transition: 'all 0.3s ease',
  cursor: 'pointer'
}));

const PackageButton = styled(Button)(({ theme, isSelected }) => ({
  width: '100%',
  justifyContent: 'space-between',
  padding: '8px',
  marginBottom: '6px',
  backgroundColor: isSelected 
    ? 'rgba(219, 234, 254, 1)'
    : 'rgba(237, 242, 255, 0.85)',
  color: '#1E3A8A',
  '&:hover': {
    backgroundColor: 'rgba(191, 219, 254, 1)',
    transform: 'translateY(-1px)',
  },
  borderRadius: '6px',
  textTransform: 'none'
}));

const PackageSelection = ({ value, onChange, disabled, pickUpTime, bookingDate, formSection, onBeforeOpen }) => {
  const [anchorEl, setAnchorEl] = useState(null);
  const selectedGuide = useSelector(state => state.tourguide.selectedGuide);
  const exchangeRate = useSelector(state => state.auth.exchangeRate) || 1;
  const currencyCode = useSelector(state => state.auth.currencyCode) || 'USD';
  const PriceHide = useSelector(state => state.auth.PriceHide) || '1';
  
  const sectionBookingDate = formSection?.bookingDate || formSection?.date;
  const effectiveBookingDate = bookingDate || sectionBookingDate;
  const packageHoursValue = value !== '' && value !== null && value !== undefined ? Number(value) : null;

  // Prefer live selectedGuide night windows; fall back to packageData booking
  const nightStartTime =
    selectedGuide?.night_start_time ||
    formSection?.originalData?.Night_Start_Time ||
    "21:00";
  const nightEndTime =
    selectedGuide?.night_end_time ||
    formSection?.originalData?.Night_End_Time ||
    "00:00";
  
  const parseTimeToHour = useCallback((timeStr) => {
    if (!timeStr && timeStr !== 0) return -1;
    if (typeof timeStr === 'number') return timeStr;
    if (timeStr.includes("AM") || timeStr.includes("PM")) {
      const [timePart, period] = timeStr.split(" ");
      let [hours] = timePart.split(":");
      hours = parseInt(hours, 10);
      if (period === "PM" && hours !== 12) hours += 12;
      else if (period === "AM" && hours === 12) hours = 0;
      return hours;
    }
    const [hours] = String(timeStr).split(":");
    return parseInt(hours, 10);
  }, []);

  const nightStartHour = parseTimeToHour(nightStartTime);
  const nightEndHour = parseTimeToHour(nightEndTime);
  
  const isNightHour = useCallback((hour) => {
    let adjustedEndHour = String(nightEndTime).includes(":") && 
      parseInt(String(nightEndTime).split(":")[1], 10) > 0
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
      return [];
    }

    const prices = selectedGuide.prices;

    return [
      { hours: 1, price: prices.dmc_hourly_price || prices.travclicks_hourly_price },
      { hours: 2, price: prices.dmc_two_hour_price || prices.travclicks_two_hour_price },
      { hours: 4, price: prices.dmc_four_hour_price || prices.travclicks_four_hour_price },
      { hours: 6, price: prices.dmc_six_hour_price || prices.travclicks_six_hour_price },
      { hours: 8, price: prices.dmc_eight_hour_price || prices.travclicks_eight_hour_price },
      { hours: 10, price: prices.dmc_ten_hour_price || prices.travclicks_ten_hour_price },
      { hours: 12, price: prices.dmc_twelve_hour_price || prices.travclicks_twelve_hour_price }
    ].filter(pkg => pkg.price > 0);
  }, [selectedGuide]);

  const calculatePackagePriceBreakdown = useCallback((packageHours) => {
    if (!selectedGuide?.prices || !pickUpTime || pickUpTime.hourValue === undefined || pickUpTime.hourValue === null) {
      // Keep existing packageData pricing until guide details + pickup are ready
      if (formSection?.originalData && packageHoursValue === Number(packageHours)) {
        return {
          basePrice: Number(formSection.priceBreakdown?.basePrice ?? formSection.originalData.basePrice) || 0,
          nightSurcharge: Number(formSection.priceBreakdown?.nightSurcharge ?? formSection.originalData.surcharge) || 0,
          totalPrice: Number(formSection.priceBreakdown?.totalPrice ?? formSection.originalData.totalPrice) || 0,
          nightHours: Number(formSection.priceBreakdown?.nightHours) || 0,
          dayHours: Number(formSection.priceBreakdown?.dayHours ?? packageHours) || 0
        };
      }
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
      switch (Number(packageHours)) {
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
    
    let nightHours = 0;
    let dayHours = 0;
    const startHour = Number(pickUpTime.hourValue);
    
    for (let i = 0; i < Number(packageHours); i++) {
      const currentHour = (startHour + i) % 24;
      if (isNightHour(currentHour)) {
        nightHours++;
      } else {
        dayHours++;
      }
    }
    
    const nightSurchargeRate = prices.dmc_night_surcharge || 0;
    const nightSurcharge = nightHours > 0 ? nightSurchargeRate * nightHours : 0;
    
    return {
      basePrice,
      nightSurcharge,
      totalPrice: basePrice + nightSurcharge,
      nightHours,
      dayHours
    };
  }, [pickUpTime, selectedGuide, isNightHour, formSection, packageHoursValue]);

  const handleClick = useCallback((event) => {
    if (disabled) return;
    if (typeof onBeforeOpen === 'function') {
      onBeforeOpen();
    }
    setAnchorEl(event.currentTarget);
  }, [disabled, onBeforeOpen]);

  const handleClose = useCallback(() => {
    setAnchorEl(null);
  }, []);

  const handlePackageSelect = useCallback((hours) => {
    const priceBreakdown = calculatePackagePriceBreakdown(hours);
    
    onChange({ 
      value: hours,
      priceBreakdown: priceBreakdown,
      bookingDate: effectiveBookingDate || new Date().toISOString().split('T')[0]
    });
    handleClose();
  }, [onChange, handleClose, calculatePackagePriceBreakdown, effectiveBookingDate]);

  const open = Boolean(anchorEl);
  const id = open ? 'package-popover' : undefined;

  const currentPriceBreakdown = useMemo(() => {
    if (packageHoursValue == null || Number.isNaN(packageHoursValue)) return null;
    return calculatePackagePriceBreakdown(packageHoursValue);
  }, [packageHoursValue, calculatePackagePriceBreakdown]);

  // Static label for packageData hours even before selectedGuide prices load
  const displayLabel = packageHoursValue != null && !Number.isNaN(packageHoursValue)
    ? `${packageHoursValue} Hour Package`
    : 'Select Duration';

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
            <TimerIcon sx={{ color: 'primary.main', fontSize: 18 }} />
            <Typography sx={{ fontSize: '0.8rem' }}>
              {displayLabel}
            </Typography>
          </Box>
          {currentPriceBreakdown?.nightHours > 0 && (
            <Box sx={{ mt: 0.8, display: 'flex', alignItems: 'center', gap: 0.8 }}>
              <NightsStayIcon fontSize="small" sx={{ color: '#B45309', fontSize: 16 }} />
              <Typography variant="caption" color="#B45309" sx={{ fontSize: '0.7rem' }}>
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
          Select Duration Package
        </Typography>

        {!pickUpTime && (
          <Typography variant="body2" sx={{ mb: 1.5, color: 'warning.main', fontSize: '0.8rem' }}>
            Please select pick-up time first to see price breakdown
          </Typography>
        )}

        <Box sx={{ maxHeight: '300px', overflow: 'auto', pr: 0.8 }}>
          {packages.length === 0 && packageHoursValue != null && (
            <PackageButton
              key={`static-${packageHoursValue}`}
              onClick={() => handlePackageSelect(packageHoursValue)}
              isSelected
            >
              <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start' }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <TimerIcon sx={{ mr: 0.8, fontSize: 18 }} />
                  <Typography variant="body2" sx={{ fontWeight: 500, fontSize: '0.8rem' }}>
                    {packageHoursValue} Hour Package
                  </Typography>
                </Box>
                <Typography variant="caption" color="text.secondary" sx={{ mt: 0.4, fontSize: '0.7rem' }}>
                  Loading guide rates…
                </Typography>
              </Box>
            </PackageButton>
          )}
          {packages.map((pkg) => {
            const isSelected = packageHoursValue === Number(pkg.hours);
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
                    <TimerIcon sx={{ mr: 0.8, fontSize: 18 }} />
                    <Typography variant="body2" sx={{ fontWeight: 500, fontSize: '0.8rem' }}>
                      {pkg.hours} Hour Package
                    </Typography>
                  </Box>
                  {hasNightHours && (
                    <Box sx={{ display: 'flex', alignItems: 'center', mt: 0.4 }}>
                      <NightsStayIcon sx={{ mr: 0.8, fontSize: 14, color: '#B45309' }} />
                      <Typography variant="caption" color="#B45309" sx={{ fontSize: '0.7rem' }}>
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
                      fontWeight: 600,
                      fontSize: '0.7rem',
                      height: 20
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
