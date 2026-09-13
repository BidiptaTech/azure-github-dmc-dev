import { useEffect } from "react";
import Slider from "@mui/material/Slider";
import { useSelector } from "react-redux";

const PriceSlider = ({ hourlyPrices = [], priceRange, setPriceRange }) => {
  // Ensure hourlyPrices is always an array
  const validPrices = Array.isArray(hourlyPrices) ? hourlyPrices : [];

  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);

  // Prevent errors if hourlyPrices is empty
  const minPrice = validPrices.length ? Math.min(...validPrices) : 0;
  const maxPrice = validPrices.length ? Math.max(...validPrices) : 500;

  // Ensure priceRange is always valid
  const safePriceRange =
    priceRange && typeof priceRange.min !== "undefined"
      ? priceRange
      : { min: minPrice, max: maxPrice };

  // Ensure priceRange syncs with the min & max from vehicles
  useEffect(() => {
    if (validPrices.length) {
      setPriceRange((prev) => ({
        min: prev?.min !== undefined ? prev.min : minPrice,
        max: prev?.max !== undefined ? prev.max : maxPrice,
      }));
    } else {
      // Set default values when no valid prices are available
      setPriceRange({ min: 0, max: 500 });
    }
  }, [validPrices, setPriceRange, minPrice, maxPrice]);

  const handleChange = (event, newValue) => {
    setPriceRange({ min: newValue[0], max: newValue[1] });
  };

  return (
    <div style={{  padding: '16px', borderRadius: '10px' }}>
      
      <Slider
        value={[safePriceRange.min, safePriceRange.max]}
        onChange={handleChange}
        valueLabelDisplay="auto"
        min={minPrice}
        max={maxPrice}
        size="small"
        sx={{
          height: 4,
          '& .MuiSlider-thumb': {
            width: 16,
            height: 16,
          },
          '& .MuiSlider-valueLabel': {
            fontSize: '0.75rem',
            padding: '2px 6px',
          },
        }}
      />
      <p style={{ fontSize: '0.75rem', marginTop: '8px', marginBottom: 0 }}>
        Price Range: {currencySymbol}
        {Math.ceil(safePriceRange.min * exchangeRate)} - {currencySymbol}
        {Math.ceil(safePriceRange.max * exchangeRate)}
      </p>
    </div>
  );
};

export default PriceSlider;
