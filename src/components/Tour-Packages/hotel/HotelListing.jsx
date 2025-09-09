import React, { useState, useEffect, useRef, useMemo } from "react";
import { styled } from "@mui/material/styles";
import {
  Box,
  TextField,
  Typography,
  Paper,
  List,
  ListItem,
  CircularProgress,
  Chip,
  InputLabel,
  Avatar,
  Tooltip,
  Autocomplete,
  Stack,
  Grid,
  Divider
} from "@mui/material";
import { useDispatch, useSelector } from "react-redux";
import { fetchRoomData } from "@/slice/hotel/HotelAvailabilitySlice";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import StarIcon from "@mui/icons-material/Star";
import HotelIcon from "@mui/icons-material/Hotel";
import BedIcon from "@mui/icons-material/Bed";
import RestaurantMenuIcon from "@mui/icons-material/RestaurantMenu";

// Custom styled tooltip
const CustomTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    backgroundColor: 'white',
    color: 'rgba(0, 0, 0, 0.87)',
    maxWidth: 350,
    border: '1px solid #dadde9',
    borderRadius: '8px',
    padding: 0,
    boxShadow: theme.shadows[2]
  },
}));

// Styled components
const SearchContainer = styled(Box)(({ theme }) => ({
  position: "relative",
  marginBottom: theme.spacing(1.5),
}));

const DropdownContainer = styled(Paper)(({ theme }) => ({
  position: "absolute",
  width: "100%",
  maxHeight: 300,
  overflowY: "auto",
  zIndex: 20,
  marginTop: theme.spacing(0.5),
  boxShadow: theme.shadows[2],
}));

const HotelOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1, 1.5),
  cursor: "pointer",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  justifyContent: "space-between",
  alignItems: "flex-start",
  gap: theme.spacing(0.8),
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
  },
}));

const HotelInfo = styled(Box)({
  flex: 1,
});

const HotelImage = styled(Box)(({ theme }) => ({
  width: 50,
  height: 50,
  flexShrink: 0,
  borderRadius: theme.shape.borderRadius,
  overflow: "hidden",
  "& img": {
    width: "100%",
    height: "100%",
    objectFit: "cover",
  },
}));

const StarRating = styled(Box)(({ theme }) => ({
  display: "flex",
  marginBottom: theme.spacing(0.3),
  color: "gold",
}));

const SelectedHotel = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(0.8),
  padding: theme.spacing(0.8, 1),
  backgroundColor: theme.palette.grey[50],
  borderRadius: theme.shape.borderRadius,
  borderLeft: `2px solid ${theme.palette.primary.main}`,
  fontSize: 12,
}));

const SelectedLabel = styled("span")(({ theme }) => ({
  fontWeight: 500,
  marginRight: theme.spacing(0.3),
  color: theme.palette.primary.main,
}));

const HotelDetail = styled(Chip)(({ theme }) => ({
  marginLeft: theme.spacing(0.8),
  height: 20,
  fontSize: 11,
}));

const CountBadge = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  marginLeft: theme.spacing(0.8),
  height: 18,
  fontSize: 11,
}));

// Tooltip content component for hotels
const TooltipContent = ({ hotel }) => {
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // Generate star icons based on hotel rating
  const renderStarRating = (stars) => {
    if (!stars) return null;
    
    const starCount = parseInt(stars);
    if (isNaN(starCount) || starCount < 1) return null;
    
    return (
      <StarRating>
        {[...Array(starCount)].map((_, i) => (
          <StarIcon key={i} fontSize="small" sx={{ fontSize: 14 }} />
        ))}
      </StarRating>
    );
  };

  return (
    <Box>
      {/* Header Image Section */}
      <Box sx={{ position: 'relative', width: '100%', height: 150 }}>
        <Box
          component="img"
          src={hotel.main_image || "https://via.placeholder.com/400x150?text=No+Image"}
          alt={hotel.name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '8px',
            borderTopRightRadius: '8px',
          }}
        />
        {hotel.site_image && hotel.site_image.length > 0 && (
          <Box
            sx={{
              position: 'absolute',
              top: 6,
              right: 6,
              bgcolor: 'rgba(0, 0, 0, 0.6)',
              color: 'white',
              px: 0.8,
              py: 0.3,
              borderRadius: 0.8,
              fontSize: '0.65rem',
            }}
          >
            +{hotel.site_image.length} photos
          </Box>
        )}
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 1.5 }}>
        {/* Title and Location */}
        <Typography variant="h6" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1rem' }}>
          {hotel.name}
        </Typography>
        {renderStarRating(hotel.hotel_star_rating)}
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
          <LocationOnIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.4 }} />
          <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.8rem' }}>
            {hotel.address || hotel.location}
          </Typography>
        </Box>

        {/* Room & Amenities Info */}
        <Box sx={{ mb: 1.5 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem' }}>
            Hotel Details
          </Typography>
          <Stack direction="row" spacing={0.8} flexWrap="wrap" useFlexGap sx={{ mb: 0.8 }}>
            <Chip
              size="small"
              icon={<BedIcon sx={{ fontSize: '0.7rem !important' }} />}
              label={`${hotel.hotel_star_rating} Star Hotel`}
              sx={{
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                color: '#2e7d32',
                fontSize: '0.65rem',
                height: 20,
                '& .MuiChip-label': { px: 0.8 },
              }}
            />
            {hotel.category && (
              <Chip
                size="small"
                label={hotel.category}
                sx={{
                  backgroundColor: 'rgba(25, 118, 210, 0.1)',
                  color: 'primary.main',
                  fontSize: '0.65rem',
                  height: 20,
                  '& .MuiChip-label': { px: 0.8 },
                }}
              />
            )}
          </Stack>
        </Box>

        {/* Description */}
        {hotel.description && (
          <Box sx={{ mb: 1.5 }}>
            <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem' }}>
              Description
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ 
              fontSize: '0.75rem',
              display: '-webkit-box',
              WebkitLineClamp: 3,
              WebkitBoxOrient: 'vertical',
              overflow: 'hidden',
              textOverflow: 'ellipsis'
            }}>
              {hotel.description.replace(/<[^>]*>/g, ' ')}
            </Typography>
          </Box>
        )}

        {/* Pricing Section */}
        {(hotel.dmc_price > 0 || hotel.price) && (
          <Box sx={{ mt: 1.5 }}>
            <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem' }}>
              Pricing Details
            </Typography>
            {PriceHide !== "1" ? (
            <Paper 
              variant="outlined" 
              sx={{ 
                p: 1,
                bgcolor: 'rgba(25, 118, 210, 0.02)',
                borderColor: 'rgba(25, 118, 210, 0.1)'
              }}
            >
              <Typography variant="subtitle2" sx={{ color: 'primary.main', fontWeight: 500, fontSize: '0.75rem' }}>
                DMC Prices
              </Typography>
              <Stack spacing={0.3} mt={0.8}>
                <Typography variant="body2" sx={{ fontSize: '0.7rem' }}>
                  Base Price: ${hotel.formatted_price || hotel.dmc_price || hotel.price || 'N/A'}
                </Typography>
                {hotel.dmc_tax_amount > 0 && (
                  <Typography variant="body2" sx={{ fontSize: '0.7rem' }}>Tax: ${hotel.dmc_tax_amount}</Typography>
                )}
                {(hotel.dmc_price > 0 && hotel.dmc_tax_amount > 0) && (
                  <Divider sx={{ my: 0.3 }} />
                )}
                {(hotel.dmc_price > 0 && hotel.dmc_tax_amount > 0) && (
                  <Typography variant="body2" fontWeight={500} sx={{ fontSize: '0.7rem' }}>
                    Total: ${hotel.formatted_price ? 
                      (parseFloat(hotel.dmc_price) + parseFloat(hotel.dmc_tax_amount)).toFixed(1) + 'k' :
                      (parseFloat(hotel.dmc_price) + parseFloat(hotel.dmc_tax_amount)).toFixed(2)}
                  </Typography>
                )}
              </Stack>
            </Paper>
            ):(
              <Typography variant="caption" gutterBottom sx={{ color: 'text.secondary', fontWeight: 500, fontSize: '0.75rem' }}>
                Pricing hidden
              </Typography>
            )}
          </Box>
        )}
      </Box>
    </Box>
  );
};

const HotelListing = ({ onSelect, initialHotels = [],  selectedHotelId }) => {
  const dispatch = useDispatch();
  const { hotels, status, searchState } = useSelector((state) => state.hotels);
  const [searchTerm, setSearchTerm] = useState("");
  const [selectedHotel, setSelectedHotel] = useState(null);
  const tourDetails = useSelector((state) => state.hotels.tourdetails);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
 
  // Removed hotels.length from the dependency array to prevent repeated calls

  // Format number to a readable price
  const formatPrice = (price) => {
    if (!price) return '';
    // Handle extremely large prices (reduce by thousands)
    if (price >= 1000) {
      return (price / 1000).toFixed(1) + 'k';
    }
    return price;
  };
  
  // Debug the raw hotel data to verify what we're receiving
  useEffect(() => {
    if (hotels.length > 0) {
      console.log("Raw hotel data from redux:", 
        hotels.map(h => ({id: h.id, name: h.hotel_name, price: h.dmc_price}))
      );
    }
  }, [hotels]);

  // Filter hotels to show only DMC hotels and format them for display
  const formattedHotels = useMemo(() => {
    console.log(`Processing hotels: ${hotels.length} hotels available`);
    
    if (hotels.length === 0 && initialHotels.length === 0) {
      console.log("No hotels available to display");
      return [];
    }
    
    const filteredHotels = hotels.length > 0 
      ? hotels
          // Make sure we include ALL hotels regardless of dmc_id
          .map(hotel => {
            // Ensure all required properties exist and have proper values
            const formattedHotel = {
              id: hotel.id,
              name: hotel.hotel_name || 'Unnamed Hotel',
              location: hotel.location || 'Location not specified',
              hotel_star_rating: hotel.category || "3",
              address: hotel.location || 'Address not available',
              description: hotel.description || '',
              main_image: hotel.image || (hotel.site_image && hotel.site_image.length > 0 ? hotel.site_image[0] : null),
              // Convert price strings to numbers if needed
              dmc_price: typeof hotel.dmc_price === 'string' ? parseFloat(hotel.dmc_price) : hotel.dmc_price,
              dmc_tax_amount: hotel.dmc_tax_amount || 0,
              site_image: hotel.site_image || [],
              price: hotel.price || 0,
              category: hotel.category || '',
              dmc_id: hotel.dmc_id || 4,
              // Add formatted price for display
              formatted_price: formatPrice(hotel.dmc_price || hotel.price || 0)
            };
            
            return formattedHotel;
          })
      : initialHotels;
      
    console.log(`Formatted ${filteredHotels.length} hotels for display:`, 
      filteredHotels.map(h => ({id: h.id, name: h.name, price: h.dmc_price, formatted: h.formatted_price}))
    );
    return filteredHotels;
  }, [hotels, initialHotels]);

  // Update selected hotel when prop changes
  useEffect(() => {
    if (selectedHotelId) {
      // Find the hotel object from formattedHotels
      const hotelObject = formattedHotels.find(h => h.id === selectedHotelId || h.name === selectedHotelId);
      // Only update if the hotel object is different to prevent unnecessary re-renders
      setSelectedHotel(prevSelected => {
        if (prevSelected?.id !== hotelObject?.id) {
          return hotelObject || null;
        }
        return prevSelected;
      });
    } else {
      setSelectedHotel(prevSelected => prevSelected ? null : prevSelected);
    }
  }, [selectedHotelId]); // Removed formattedHotels dependency

  // Handle hotel selection
  const handleHotelSelect = (hotel) => {
    if (!hotel) {
      setSelectedHotel(null);
      if (onSelect) onSelect(null);
      console.log("Hotel selection cleared");
      return;
    }
    
    console.log("Selected hotel:", hotel);
    setSelectedHotel(hotel);

    // Fetch hotel details using the HotelAvailabilitySlice
    // Extract numeric tour_id from the response data structure
    const tourId = tourDetails?.data?.tour_id || tourDetails?.tour_id || 0;
    console.log("Using tour ID for hotel fetch:", tourId);
    
    // Log the request parameters we're sending
    const requestParams = {
      id: hotel.id,
      tour_id: tourId,
      priceMode: "dmc",
      dmc_id: hotel.dmc_id || 4
    };
    
    console.log("Fetching room data with params:", requestParams);
    
    dispatch(fetchRoomData(requestParams))
      .then((response) => {
        console.log("Room data fetch successful:", response);
      })
      .catch((error) => {
        console.error("Error fetching room data:", error);
      });
    
    if (onSelect) {
      console.log("Notifying parent component of hotel selection:", hotel);
      onSelect(hotel);
    }
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 0 }}>
        <Autocomplete
          value={selectedHotel}
          onChange={(event, newValue) => {
            handleHotelSelect(newValue);
          }}
          options={formattedHotels}
          getOptionLabel={(option) => option.name || ''}
          noOptionsText={status === "loading" ? "Loading hotels..." : "No hotels available"}
          loading={status === "loading"}
          disableListWrap
          ListboxProps={{
            style: { maxHeight: 350, overflow: 'auto' }
          }}
          disableClearable={false}
          autoHighlight
          renderOption={(props, option) => (
            <CustomTooltip
              title={<TooltipContent hotel={option} />}
              placement="right"
              arrow
            >
              <Box component="li" {...props}>
                {option.name}
                {/* Add indicators for star rating and price */}
                <Box sx={{ ml: 'auto', display: 'flex', gap: 0.4 }}>
                  <Chip 
                    size="small" 
                    label={`${option.hotel_star_rating} ★`}
                    sx={{ 
                      height: 18,
                      fontSize: '0.65rem',
                      bgcolor: 'rgba(255, 193, 7, 0.1)',
                      color: '#F9A825'
                    }}
                  />
                  {(option.dmc_price > 0 || option.price > 0) && PriceHide !== "1" && (
                    <Chip 
                      size="small" 
                      label={`$${option.formatted_price || option.dmc_price || option.price}`}
                      sx={{ 
                        height: 18,
                        fontSize: '0.65rem',
                        bgcolor: 'rgba(25, 118, 210, 0.08)',
                        color: 'primary.main',
                        minWidth: '36px'
                      }}
                    />
                  )}
                </Box>
              </Box>
            </CustomTooltip>
          )}
          renderInput={(params) => (
            <TextField
              {...params}
              label="Search hotels"
              fullWidth
              sx={{ height: '56px' }}
              InputProps={{
                ...params.InputProps,
                sx: { height: '56px' },
                endAdornment: (
                  <>
                    {status === "loading" && <CircularProgress color="inherit" size={20} />}
                    {params.InputProps.endAdornment}
                  </>
                ),
              }}
            />
          )}
        />
        
        {/* {selectedHotel && (
          <Box sx={{ mt: 1.5 }}>
            <Paper
              variant="outlined"
              sx={{ 
                p: 1,
                borderRadius: 1,
                borderColor: 'primary.main',
                borderWidth: 1,
                display: 'flex',
                alignItems: 'center',
                gap: 1
              }}
            >
              {selectedHotel.main_image ? (
                <Box
                  component="img"
                  src={selectedHotel.main_image}
                  alt={selectedHotel.name}
                  sx={{ 
                    width: 40, 
                    height: 40, 
                    borderRadius: 0.8,
                    objectFit: 'cover'
                  }}
                />
              ) : (
                <Avatar sx={{ width: 40, height: 40, bgcolor: 'primary.main' }}>
                  <HotelIcon />
                </Avatar>
              )}
              
              <Box sx={{ flexGrow: 1 }}>
                <Typography variant="subtitle2" sx={{ fontWeight: 500, mb: 0.3, fontSize: '0.85rem' }}>
                  {selectedHotel.name}
                </Typography>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <LocationOnIcon sx={{ fontSize: 12, mr: 0.4, color: 'text.secondary' }} />
                  <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                    {selectedHotel.address}
                  </Typography>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', mt: 0.3 }}>
                  <StarRating>
                    {[...Array(parseInt(selectedHotel.hotel_star_rating) || 3)].map((_, i) => (
                      <StarIcon key={i} sx={{ fontSize: 12 }} />
                    ))}
                  </StarRating>
                  
                  {(selectedHotel.dmc_price > 0 || selectedHotel.price > 0) && PriceHide !== "1" && (
                    <Typography variant="body2" fontWeight={500} sx={{ ml: 1.5, fontSize: '0.75rem' }}>
                      ${selectedHotel.formatted_price || selectedHotel.dmc_price || selectedHotel.price}
                      {selectedHotel.dmc_tax_amount > 0 && ` + $${selectedHotel.dmc_tax_amount} tax`}
                    </Typography>
                  )}
                </Box>
              </Box>
            </Paper>
          </Box>
        )} */}
      </Box>
    </SearchContainer>
  );
};

export default HotelListing;