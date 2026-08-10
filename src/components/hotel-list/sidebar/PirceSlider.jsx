import { Box, Slider, TextField, Typography } from "@mui/material";
import { useState, useEffect, useMemo } from "react";
import { useSelector } from "react-redux";

const PirceSlider = ({
  priceRange,
  setPriceRange,
  handlePriceChange,
  handleManualPriceChange,
  priceBounds
}) => {
  // Get currency information from Redux store
  const currencySymbol = useSelector((state) => state.auth.currencySymbol) || "₹";
  
  // Ensure price range values are safe numbers - use useMemo to prevent recalculations
  const safeValues = useMemo(() => {
    const safeMinPrice = isFinite(priceRange[0]) ? priceRange[0] : 0;
    const safeMaxPrice = isFinite(priceRange[1]) ? priceRange[1] : 1000;
    
    // Get bounds from props or use defaults
    const boundMin = priceBounds && isFinite(priceBounds.min) ? priceBounds.min : 0;
    const boundMax = priceBounds && isFinite(priceBounds.max) ? priceBounds.max : 1000;
    
    // Calculate step size based on price range
    let step = 50;
    const range = boundMax - boundMin;
    if (range > 100000) step = 1000;
    else if (range > 10000) step = 500;
    else if (range > 1000) step = 100;
    
    return {
      safeMinPrice,
      safeMaxPrice,
      boundMin,
      boundMax,
      step,
      safePriceRange: [safeMinPrice, safeMaxPrice]
    };
  }, [priceRange, priceBounds]);
  
  // Extract values from the memoized object
  const { safeMinPrice, safeMaxPrice, boundMin, boundMax, step, safePriceRange } = safeValues;
  
  // State to track if user is currently interacting with the slider
  const [isSliding, setIsSliding] = useState(false);
  
  // Wrap the change handlers to ensure they receive valid values
  const handleSafeSliderChange = (event, newValue) => {
    if (!Array.isArray(newValue) || newValue.length !== 2 || 
        !isFinite(newValue[0]) || !isFinite(newValue[1])) {
      return;
    }
    handlePriceChange(event, newValue);
  };
  
  const handleSliderStart = () => {
    setIsSliding(true);
  };
  
  const handleSliderEnd = () => {
    setIsSliding(false);
  };
  
  const handleSafeInputChange = (event, index) => {
    // Validate input before passing to parent handler
    const inputValue = event.target.value;
    
    if (inputValue === "" || !isNaN(inputValue)) {
      handleManualPriceChange(event, index);
    }
  };
  
  // Format large numbers with commas
  const formatPrice = (price) => {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  };
  
  // Create a stable slider ID that only changes when bounds change
  const sliderId = useMemo(() => 
    `price-slider-${boundMin}-${boundMax}`, 
    [boundMin, boundMax]
  );
  
  return (
    <div className="price-range-slider">
      <Typography variant="body2" gutterBottom>
        {currencySymbol}{formatPrice(safeMinPrice)} - {currencySymbol}{formatPrice(safeMaxPrice)}
      </Typography>
      
      <Box sx={{ px: 0.5, pt: 0.5, pb: 1 }}>
        <Slider
          key={sliderId}
          value={safePriceRange}
          onChange={handleSafeSliderChange}
          onChangeCommitted={handleSliderEnd}
          onMouseDown={handleSliderStart}
          onTouchStart={handleSliderStart}
          valueLabelDisplay="auto"
          valueLabelFormat={(value) => `${currencySymbol}${formatPrice(value)}`}
          min={boundMin}
          max={boundMax}
          step={step}
          size="small"
          sx={{
            color: "#007bff",
            '& .MuiSlider-thumb': {
              height: 16,
              width: 16,
            },
            '& .MuiSlider-valueLabel': {
              backgroundColor: '#007bff',
              fontSize: '0.7rem',
              padding: '0.15rem 0.35rem',
            },
            '& .MuiSlider-track': {
              height: 4
            },
            '& .MuiSlider-rail': {
              height: 4
            }
          }}
        />
        
        <Box sx={{ display: "flex", justifyContent: "space-between", mt: 1.5, gap: 1 }}>
          <TextField
            label="Min"
            variant="outlined"
            type="number"
            fullWidth
            value={safeMinPrice}
            onChange={(e) => handleSafeInputChange(e, 0)}
            size="small"
            inputProps={{
              min: boundMin,
              max: safeMaxPrice,
              step: step,
            }}
          />
          <TextField
            label="Max"
            variant="outlined"
            type="number"
            fullWidth
            value={safeMaxPrice}
            onChange={(e) => handleSafeInputChange(e, 1)}
            size="small"
            inputProps={{
              min: safeMinPrice,
              max: boundMax,
              step: step,
            }}
          />
        </Box>
      </Box>
    </div>
  );
};

export default PirceSlider;
