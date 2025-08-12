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
    maxWidth: 400,
    border: '1px solid #dadde9',
    borderRadius: '12px',
    padding: 0,
    boxShadow: theme.shadows[3]
  },
}));

// Styled components
const SearchContainer = styled(Box)(({ theme }) => ({
  position: "relative",
  marginBottom: theme.spacing(2),
}));

const DropdownContainer = styled(Paper)(({ theme }) => ({
  position: "absolute",
  width: "100%",
  maxHeight: 350,
  overflowY: "auto",
  zIndex: 20,
  marginTop: theme.spacing(0.5),
  boxShadow: theme.shadows[3],
}));

const HotelOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  justifyContent: "space-between",
  alignItems: "flex-start",
  gap: theme.spacing(1),
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
  width: 60,
  height: 60,
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
  marginBottom: theme.spacing(0.5),
  color: "gold",
}));

const SelectedHotel = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(1),
  padding: theme.spacing(1, 1.5),
  backgroundColor: theme.palette.grey[50],
  borderRadius: theme.shape.borderRadius,
  borderLeft: `3px solid ${theme.palette.primary.main}`,
  fontSize: 13,
}));

const SelectedLabel = styled("span")(({ theme }) => ({
  fontWeight: 500,
  marginRight: theme.spacing(0.5),
  color: theme.palette.primary.main,
}));

const HotelDetail = styled(Chip)(({ theme }) => ({
  marginLeft: theme.spacing(1),
  height: 22,
  fontSize: 12,
}));

const CountBadge = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  marginLeft: theme.spacing(1),
  height: 20,
  fontSize: 12,
}));

// Tooltip content component for hotels
const TooltipContent = ({ hotel }) => {
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
      <Box sx={{ position: 'relative', width: '100%', height: 200 }}>
        <Box
          component="img"
          src={hotel.main_image || "https://via.placeholder.com/400x200?text=No+Image"}
          alt={hotel.name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '12px',
            borderTopRightRadius: '12px',
          }}
        />
        {hotel.site_image && hotel.site_image.length > 0 && (
          <Box
            sx={{
              position: 'absolute',
              top: 8,
              right: 8,
              bgcolor: 'rgba(0, 0, 0, 0.6)',
              color: 'white',
              px: 1,
              py: 0.5,
              borderRadius: 1,
              fontSize: '0.75rem',
            }}
          >
            +{hotel.site_image.length} photos
          </Box>
        )}
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 2 }}>
        {/* Title and Location */}
        <Typography variant="h6" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1.1rem' }}>
          {hotel.name}
        </Typography>
        {renderStarRating(hotel.hotel_star_rating)}
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
          <LocationOnIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 0.5 }} />
          <Typography variant="body2" color="text.secondary">
            {hotel.address || hotel.location}
          </Typography>
        </Box>

        {/* Room & Amenities Info */}
        <Box sx={{ mb: 2 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
            Hotel Details
          </Typography>
          <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap sx={{ mb: 1 }}>
            <Chip
              size="small"
              icon={<BedIcon sx={{ fontSize: '0.8rem !important' }} />}
              label={`${hotel.hotel_star_rating} Star Hotel`}
              sx={{
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                color: '#2e7d32',
                fontSize: '0.75rem',
                height: 24,
                '& .MuiChip-label': { px: 1 },
              }}
            />
            {hotel.category && (
              <Chip
                size="small"
                label={hotel.category}
                sx={{
                  backgroundColor: 'rgba(25, 118, 210, 0.1)',
                  color: 'primary.main',
                  fontSize: '0.75rem',
                  height: 24,
                  '& .MuiChip-label': { px: 1 },
                }}
              />
            )}
          </Stack>
        </Box>

        {/* Description */}
        {hotel.description && (
          <Box sx={{ mb: 2 }}>
            <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
              Description
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ 
              fontSize: '0.875rem',
              display: '-webkit-box',
              WebkitLineClamp: 4,
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
          <Box sx={{ mt: 2 }}>
            <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
              Pricing Details
            </Typography>
            <Paper 
              variant="outlined" 
              sx={{ 
                p: 1.5,
                bgcolor: 'rgba(25, 118, 210, 0.02)',
                borderColor: 'rgba(25, 118, 210, 0.1)'
              }}
            >
              <Typography variant="subtitle2" sx={{ color: 'primary.main', fontWeight: 500 }}>
                DMC Prices
              </Typography>
              <Stack spacing={0.5} mt={1}>
                <Typography variant="body2">
                  Base Price: ${hotel.formatted_price || hotel.dmc_price || hotel.price || 'N/A'}
                </Typography>
                {hotel.dmc_tax_amount > 0 && (
                  <Typography variant="body2">Tax: ${hotel.dmc_tax_amount}</Typography>
                )}
                {(hotel.dmc_price > 0 && hotel.dmc_tax_amount > 0) && (
                  <Divider sx={{ my: 0.5 }} />
                )}
                {(hotel.dmc_price > 0 && hotel.dmc_tax_amount > 0) && (
                  <Typography variant="body2" fontWeight={500}>
                    Total: ${hotel.formatted_price ? 
                      (parseFloat(hotel.dmc_price) + parseFloat(hotel.dmc_tax_amount)).toFixed(1) + 'k' :
                      (parseFloat(hotel.dmc_price) + parseFloat(hotel.dmc_tax_amount)).toFixed(2)}
                  </Typography>
                )}
              </Stack>
            </Paper>
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
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Select Hotel {formattedHotels.length > 0 && (
            <CountBadge label={formattedHotels.length} size="small" variant="outlined" />
          )}
        </InputLabel>
        
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
                <Box sx={{ ml: 'auto', display: 'flex', gap: 0.5 }}>
                  <Chip 
                    size="small" 
                    label={`${option.hotel_star_rating} ★`}
                    sx={{ 
                      height: 20,
                      fontSize: '0.7rem',
                      bgcolor: 'rgba(255, 193, 7, 0.1)',
                      color: '#F9A825'
                    }}
                  />
                  {(option.dmc_price > 0 || option.price > 0) && (
                    <Chip 
                      size="small" 
                      label={`$${option.formatted_price || option.dmc_price || option.price}`}
                      sx={{ 
                        height: 20,
                        fontSize: '0.7rem',
                        bgcolor: 'rgba(25, 118, 210, 0.08)',
                        color: 'primary.main',
                        minWidth: '40px'
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
              InputProps={{
                ...params.InputProps,
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
        
        {selectedHotel && (
          <Box sx={{ mt: 2 }}>
            <Paper
              variant="outlined"
              sx={{ 
                p: 1.5,
                borderRadius: 1,
                borderColor: 'primary.main',
                borderWidth: 1,
                display: 'flex',
                alignItems: 'center',
                gap: 1.5
              }}
            >
              {selectedHotel.main_image ? (
                <Box
                  component="img"
                  src={selectedHotel.main_image}
                  alt={selectedHotel.name}
                  sx={{ 
                    width: 50, 
                    height: 50, 
                    borderRadius: 1,
                    objectFit: 'cover'
                  }}
                />
              ) : (
                <Avatar sx={{ width: 50, height: 50, bgcolor: 'primary.main' }}>
                  <HotelIcon />
                </Avatar>
              )}
              
              <Box sx={{ flexGrow: 1 }}>
                <Typography variant="subtitle1" sx={{ fontWeight: 500, mb: 0.5 }}>
                  {selectedHotel.name}
                </Typography>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <LocationOnIcon sx={{ fontSize: 14, mr: 0.5, color: 'text.secondary' }} />
                  <Typography variant="body2" color="text.secondary">
                    {selectedHotel.address}
                  </Typography>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', mt: 0.5 }}>
                  <StarRating>
                    {[...Array(parseInt(selectedHotel.hotel_star_rating) || 3)].map((_, i) => (
                      <StarIcon key={i} sx={{ fontSize: 14 }} />
                    ))}
                  </StarRating>
                  
                  {(selectedHotel.dmc_price > 0 || selectedHotel.price > 0) && (
                    <Typography variant="body2" fontWeight={500} sx={{ ml: 2 }}>
                      ${selectedHotel.formatted_price || selectedHotel.dmc_price || selectedHotel.price}
                      {selectedHotel.dmc_tax_amount > 0 && ` + $${selectedHotel.dmc_tax_amount} tax`}
                    </Typography>
                  )}
                </Box>
              </Box>
            </Paper>
          </Box>
        )}
      </Box>
    </SearchContainer>
  );
};

export default HotelListing;