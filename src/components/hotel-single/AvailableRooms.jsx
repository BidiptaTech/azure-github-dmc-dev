import { useCallback, useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { 
  clearHotelDetails, 
  updateBookingArray, 
  setBookingArray,
  setTotalPrice
} from "@/slice/hotel/HotelDetailsSlice";
import RenderRoomCards from "./RenderRoomCards";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { Button, Switch, Typography, Stack, Paper, Divider, Card, CardContent } from "@mui/material";
import { FaBed, FaPlus, FaTimes } from "react-icons/fa";
import { IoPersonSharp } from "react-icons/io5";
//import { GiKingBed, GiQueenBed, GiBunkBeds, GiSingleBed } from "react-icons/gi";
import BedOutlinedIcon from '@mui/icons-material/BedOutlined';
import KingBedOutlinedIcon from '@mui/icons-material/KingBedOutlined';
import BedroomParentOutlinedIcon from '@mui/icons-material/BedroomParentOutlined';
import SingleBedOutlinedIcon from '@mui/icons-material/SingleBedOutlined';
import DoNotDisturbAltOutlinedIcon from '@mui/icons-material/DoNotDisturbAltOutlined';
import PersonIcon from '@mui/icons-material/Person';
import WomanIcon from '@mui/icons-material/Woman';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import Chip from '@mui/material/Chip';
import Box from '@mui/material/Box';
import Avatar from '@mui/material/Avatar';
import AvatarGroup from '@mui/material/AvatarGroup';
import CurrencyExchangeIcon from '@mui/icons-material/CurrencyExchange';
import BabyChangingStationIcon from '@mui/icons-material/BabyChangingStation';
import HotelIcon from '@mui/icons-material/Hotel';
import GroupIcon from '@mui/icons-material/Group';
import CribIcon from '@mui/icons-material/Crib';

const AvailableRooms = ({ hotel }) => {
  const dispatch = useDispatch();
  const totalPrice = useSelector((state) => state.hoteldetails.totalPrice);
  // console.log("totalPrice",totalPrice);
  
  const { roomDatas } = useSelector((state) => state.rooms);
  // console.log(roomDatas,"roomdatas");
  const searchState = useSelector((state) => state.hotels.searchState);
  // console.log(searchState,"searchState");
   const location = useLocation();
   const currencySymbol = useSelector((state) => state.auth.currencySymbol);
const currencyCode = useSelector((state) => state.auth.currencyCode);
const exchangeRate = useSelector((state) => state.auth.exchangeRate);
const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
const convertedPrice = Math.ceil(totalPrice * exchangeRate);
const usdPrice = Math.ceil(totalPrice * usdExchangeRate);
   const [pax,setPax] =useState(1);
   const [paxCount,setPaxCount] =useState(1);
   
   const { priceMode } = location.state || {};
// console.log(priceMode);


    

  const [bedArray, setbedArray] = useState({});
  
  // pax = roomDatas?.tour_adult || 1;

 /// console.log(pax,"paxxxx");
  
  


  const [open, setOpen] = useState(false);
  const [openDialog, setOpenDialog] = useState(false);
  const [babyCotStatus, setBabyCotStatus] = useState({});
  const [globalCount,setGlobalCount] =useState(0)


  const [priceArr,setPriceArr] =useState([])
  const [occupancyArray, setOccupancyArray] = useState([]);
  const [occupancyCount, setOccupancyCount] = useState(0);
  const [totalPersonCount, setTotalPersonCount] = useState(0);
  //const [totalPrice, setTotalPrice] = useState(0);
  const [mealType,setMealType] =useState([])
  //const [priceMode,setPriceMode] =useState({mode})
  const [hotelType,setHotelType] =useState([])
  const [babyCotSelected, setBabyCotSelected] = useState(false);

  const PriceHide = useSelector((state) => state.auth.PriceHide);
  

  //console.log(priceMode,"priceMdee");
  
  const bookingArray = useSelector((state) => state.hoteldetails.bookingArray);

  // Clear booking-related data but preserve hotel details when component mounts
  useEffect(() => {
    // console.log("Initializing booking data...");
    
    // Instead of clearing all hotel details, we'll selectively reset booking data
    dispatch(setBookingArray([]));
    dispatch(setTotalPrice(0));
    
    // Reset local states
    setOccupancyCount(0);
    setTotalPersonCount(0);
    setPriceArr([]);
    setOccupancyArray([]);
    setGlobalCount(0);
    setBabyCotStatus({});
  }, []);
  
  // Empty dependency array means this runs once when component mounts

  useEffect(() => {
    setPax(roomDatas?.tour_adult);
   // setPax(1);

    setPaxCount(roomDatas?.tour_adult);
    //setPaxCount(1);

  },[roomDatas]);

  // console.log(totalPersonCount,"parentComponent");
  

  useEffect(() => {
    if (roomDatas?.room_data?.length > 0) {
      const tempOccupancyArray = roomDatas.room_data.map((room) => {
        if (Array.isArray(room.bed_details) && room.bed_details.length > 0) {
          return {
            room_id: room.room_id,
            room_type: room.room_type,
            beds: room.bed_details.map((bed) => ({
              bed_id: bed.bed_id,
              bed_type: bed.bed_type,
              max_occupancy: bed.max_occupancy,
              baby_cot: bed.baby_cot,
              mealTypes: [], // Initialize empty array for meal types
              selectedMeals: {} // Add this to track selected meals per room count
            })),
          };
        }
        return null;
      }).filter((room) => room !== null);

      setOccupancyArray(tempOccupancyArray);
    } else {
      setOccupancyArray([]);
    }
  }, [roomDatas]);
  //console.log(occupancyArray,"occ");
  
  
  // console.log(totalPrice,"totalPrice");
  
  // console.log(occupancyArray,"occupency array Grand Father");
  
const handleBabyCot = (e) => {
  const checked = e.target.checked;
  const bedId = e.target.name;
  
  // If no rooms are selected yet, prevent toggling the baby cot
  if (bookingArray.length === 0) {
    // console.log(`[DEBUG] Cannot add baby cot - no rooms selected yet`);
    // Reset the switch to unchecked state since we can't proceed
    e.target.checked = false;
    return;
  }
  
  // Ensure bedId is properly parsed to match the data structure (string or number)
  // This comparison is crucial as sometimes IDs may be stored as strings or numbers
  // and === comparison will fail if types don't match
  // console.log(`[DEBUG] Looking for bed with ID: ${bedId}, type: ${typeof bedId}`);
  
  // Get current bed's baby cot price - use proper type comparison with loose equality
  let currentBed = null;
  if (roomDatas?.room_data) {
    for (const room of roomDatas.room_data) {
      if (room.bed_details) {
        for (const bed of room.bed_details) {
          if (bed.bed_id == bedId) { // Use loose equality (==) to handle string/number comparisons
            currentBed = bed;
            // console.log(`[DEBUG] Found bed with ID ${bedId} in room data:`, currentBed);
            break;
          }
        }
        if (currentBed) break;
      }
    }
  }
  
  if (!currentBed) {
    console.error(`[DEBUG] Cannot find bed with ID ${bedId} in room data`);
    e.target.checked = false;
    return;
  }
  
  // Parse baby cot price as a number and check if it's valid
  const rawBabyCotPrice = currentBed?.baby_cot_price;
  const babyCotPrice = Number(rawBabyCotPrice || 0);
  
  if (isNaN(babyCotPrice)) {
    // console.error(`[DEBUG] Invalid baby cot price: ${rawBabyCotPrice}`);
    // Consider showing an error or fallback to a default value
  }
  
  // console.log(`[DEBUG] handleBabyCot: bedId=${bedId}, checked=${checked}, raw price=${rawBabyCotPrice}, parsed price=${babyCotPrice}`);
  
  // Update baby cot status in local state
  setBabyCotStatus(prevStatus => ({
    ...prevStatus,
    [bedId]: checked
  }));
  
  // Find the relevant room and bed in bookingArray - use loose equality
  let bedInBooking = null;
  let roomWithBed = null;
  
  for (const room of bookingArray) {
    for (const bed of room.beds) {
      if (bed.bed_id == bedId) { // Use loose equality
        bedInBooking = bed;
        roomWithBed = room;
        break;
      }
    }
    if (bedInBooking) break;
  }
  
  if (bedInBooking && roomWithBed) {
    console.log(`[DEBUG] Dispatching updateBookingArray (updateBabyCot) - bedId: ${bedId}, checked: ${checked}, price: ${checked ? babyCotPrice : 0}, roomId: ${roomWithBed.room_id}`);
    
    // Dispatch the update action with the correct operation name
    dispatch(updateBookingArray({
      roomId: roomWithBed.room_id,
      bedId: bedId,
      operation: "updateBabyCot", // This MUST match exactly what's in the reducer
      data: {
        baby_cot: checked,
        baby_cot_price: checked ? babyCotPrice : 0
      }
    }));
    
    // Calculate total price including baby cot
    // console.log(`[DEBUG] Current total price: ${totalPrice}, Adding baby cot price: ${checked ? babyCotPrice : -babyCotPrice}`);
  } else {
    // console.log(`[DEBUG] Cannot update baby cot for bed ${bedId} - bed not found in bookingArray. Please select a room first.`);
    // Reset the switch to unchecked state since we can't proceed
    e.target.checked = false;
  }
};

//console.log(occupancyArray,"occ");

  const handleOpen = () => {
    //setSelectedImage(url);
    setOpen(true);
  };
  const handleOpenDialog = () => {
    //setSelectedImage(url);
    setOpenDialog(true);
  };
  const handleClose = () => setOpen(false);
  const handleDialogClose = () => setOpenDialog(false);
  //console.log(roomDatas,"paxxx");
  const navigate = useNavigate();


  const handleCheckoutPage = useCallback(() => {
    if (bookingArray.length === 0) {
      // Show error or alert if no rooms are selected
      return;
    }
    
    // Navigate to checkout page with relevant state data
    // Using replace: false ensures that when we navigate back, 
    // we come back to this component and it mounts again
    navigate("/dashboard/db-dashboard/hotel-checkout", { 
      state: { 
        bookingArray, 
        totalPrice,
        priceMode 
      },
      replace: false
    });
  }, [bookingArray, totalPrice, priceMode, navigate]);
  //  console.log(occupancyArray,"grand Parent");
  
  // console.log(bookingArray,"booking array");
  
  
  useEffect(() => {
    // Cleanup function - this was a problem!
    // We should NOT clear hotel details when navigating away
    return () => {
      // Only clear booking array and price, but preserve hotel information
      dispatch(setBookingArray([]));
      dispatch(setTotalPrice(0));
    };
  }, []); // This will run when component unmounts
  
  return (
    <>
      <div className="border-light rounded-4 px-30 py-30 sm:px-20 sm:py-20">
        <div className="row y-gap-20">
        {roomDatas?.room_data?.map((data, index1) => (
          <div className="col-12">
            <Box sx={{ 
              display: 'flex',
              alignItems: 'center',
              mb: 3,
              mt: 2,
              position: 'relative',
              borderRadius: '12px',
              overflow: 'hidden',
              boxShadow: '0 4px 20px rgba(0,0,0,0.08)',
              background: 'linear-gradient(135deg, #d3d9f3 0%, #5472e8c7 100%)',
              padding: '16px 20px',
            }}>
              <HotelIcon 
                sx={{ 
                  fontSize: 32, 
                  color: '#3554D1', 
                  marginRight: 2,
                  filter: 'drop-shadow(0 2px 4px rgba(0,0,0,0.2))'
                }} 
              />
              <Typography 
                variant="h5" 
                sx={{ 
                  fontWeight: 600, 
                  color: '#3554D1',
                  textShadow: '0 2px 4px rgba(0,0,0,0.2)'
                }}
              >
                {data.room_type}
              </Typography>
              <Box 
                sx={{ 
                  position: 'absolute', 
                  right: -15, 
                  bottom: -15, 
                  opacity: .30,
                }}
              >
                <KingBedOutlinedIcon sx={{ fontSize: 100 }} />
              </Box>
            </Box>
            <div className="roomGrid">
              <div className="roomGrid__header" style={{
                gridTemplateColumns:'180px 1fr 60px 170px 239px'
               // grid-template-columns: 180px 1fr 60px 170px 239px;
              }}>
             
                <div>Room Type </div>
                {/* <div>Benefits</div> */}
                <div>Bed Details</div>
                {/* <div>Price for 5 nights</div> */}
                <div>Reserve</div>
                {/* <div>Room Price</div> */}

                <div />
              </div>
              {/* End .roomGrid__header */}

              <div className="roomGrid__grid" style={{gridTemplateColumns: "180px auto 210px"}}>
                <div >
                  <div className="ratio ratio-1:1">
                    <img
                      src={roomDatas?.image}
                      alt="image"
                      className="img-ratio rounded-4"
                    />
                  </div>
                  {/* End image */}
                  {/* <div className="y-gap-5 mt-20">
                    <div className="d-flex items-center">
                      <i className="icon-no-smoke text-20 mr-10" />
                      <div className="text-15">Non-smoking rooms</div>
                    </div>
                    <div className="d-flex items-center">
                      <i className="icon-wifi text-20 mr-10" />
                      <div className="text-15">Free WiFi</div>
                    </div>
                    <div className="d-flex items-center">
                      <i className="icon-parking text-20 mr-10" />
                      <div className="text-15">Parking</div>
                    </div>
                    <div className="d-flex items-center">
                      <i className="icon-kitchen text-20 mr-10" />
                      <div className="text-15">Kitchen</div>
                    </div>
                  </div> */}
                  {/* End room features */}
                  {/* <a
                    href="#"
                    className="d-block text-15 fw-500 underline text-blue-1 mt-15"
                  >
                    Show Room Information
                  </a>
                  <div>
                      <div className="text-15 fw-500 mb-10">
                        Your price includes:
                      </div>
                      <div className="y-gap-8">
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">Pay at the hotel</div>
                        </div>
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">
                            Pay nothing until March 30, 2022
                          </div>
                        </div>
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">
                            Free cancellation before April 1, 2022
                          </div>
                        </div>
                      </div>
                    </div> */}
                </div>
                
                {/* End roomgrid inner */}

                <div className="y-gap-30">
                  {/* <div className="roomGrid__content">
                   

                    <div>
                      <div className="d-flex items-center text-light-1">

              
                        <div className="icon-man text-24" />
                       
                      </div>
                    </div>

                    <div>
                      <div className="text-18 lh-15 fw-500">
                        US${hotel?.price}
                      </div>
                      <div className="text-14 lh-18 text-light-1">
                        Includes taxes and charges
                      </div>
                    </div>

                    <div>
                      <div className="dropdown js-dropdown js-price-1-active">
                        <select className="form-select dropdown__button d-flex items-center rounded-4 border-light px-15 h-50 text-14">
                          <option value="1" defaultValue>
                            1 (US$ 3,120)
                          </option>
                          <option value="2">2 (US$ 3,120)</option>
                          <option value="3"> 3 (US$ 3,120)</option>
                          <option value="4"> 4 (US$ 3,120)</option>
                          <option value="5"> 5 (US$ 3,120)</option>
                        </select>
                      </div>
                    </div>
                  </div> */}
                  {/* End romm Grid horizontal content */}

{/*.............start from here Bed Type ..............*/}
{data.bed_details.map((bed, index2) => {
  const bedIndex = index2;
  const maxOccupancy = bed?.max_occupancy;
  const bedId = bed?.bed_id;
  const roomTypeIndex = index1;

  // State to manage baby cot selection

  // Map bed types to icons
  const bedTypeIcons = {
    king: <KingBedOutlinedIcon size={20} />,
    queen: <BedOutlinedIcon size={20} />,
    twin: <BedroomParentOutlinedIcon size={20} />,
    single: <SingleBedOutlinedIcon size={20} />,
  };

  // Determine the icon to use based on the bed type
const normalizedBedType = bed?.bed_type.split(' ')[0].toLowerCase();
 // console.log(`Normalized Bed Type: ${normalizedBedType}`);

  const bedTypeIcon = bedTypeIcons[normalizedBedType] || <FaBed size={20} />;

  return (
    <div className="roomGrid__content" style={{ gridTemplateColumns: "1fr 350px" }}>
      <div>
        {/* Bed Type, Max Occupancy and Actual Occupancy - Redesigned */}
        <Box sx={{ 
          display: 'flex', 
          flexDirection: 'column', 
          gap: 2, 
          mb: 2 
        }}>
          {/* Bed Type Card */}
          <Card 
            elevation={1} 
            sx={{ 
              borderRadius: '12px',
              transition: 'transform 0.2s, box-shadow 0.2s',
              '&:hover': {
                transform: 'translateY(-3px)',
                boxShadow: '0 8px 16px rgba(0,0,0,0.1)'
              },
              backgroundColor: 'rgba(53, 84, 209, 0.05)'
            }}
          >
            <CardContent sx={{ p: 2 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <HotelIcon 
                    sx={{ 
                      color: '#3554D1',
                      fontSize: 28,
                      mr: 1
                    }} 
                  />
                  <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                    {bed?.bed_type.charAt(0).toUpperCase() + bed?.bed_type.slice(1).replace(/_/g, " ")}
                  </Typography>
                </Box>
                <Avatar
                  sx={{
                    bgcolor: 'white',
                    border: '2px solid #3554D1',
                    color: '#3554D1',
                    width: 40,
                    height: 40
                  }}
                >
                  {bedTypeIcon}
                </Avatar>
              </Box>
            </CardContent>
          </Card>

          {/* Max Occupancy Card */}
          <Card 
            elevation={1} 
            sx={{ 
              borderRadius: '12px',
              transition: 'transform 0.2s, box-shadow 0.2s',
              '&:hover': {
                transform: 'translateY(-3px)',
                boxShadow: '0 8px 16px rgba(0,0,0,0.1)'
              }
            }}
          >
            <CardContent sx={{ p: 2 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <GroupIcon 
                  sx={{ 
                    color: '#3554D1',
                    fontSize: 28,
                    mr: 1
                  }} 
                />
                <Typography variant="h6" sx={{ fontWeight: 'bold',fontSize:'16px' }}>
                  Max Occupancy ({maxOccupancy})
                </Typography>
              </Box>
              
              <Divider sx={{ my: 1 }} />
              
              <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
                <AvatarGroup max={5} sx={{ '& .MuiAvatar-root': { width: 35, height: 35 } }}>
                  {Array.from({ length: maxOccupancy }).map((_, i) => (
                    <Avatar 
                      key={`max-bed-${bedId}-${i}`} 
                      sx={{ 
                        bgcolor: 'rgba(53, 84, 209, 0.1)',
                        color: '#3554D1',
                      }}
                    >
                      <BedOutlinedIcon fontSize="small" />
                    </Avatar>
                  ))}
                </AvatarGroup>
                {/* <Typography variant="body1" sx={{ ml: 2, fontWeight: 'medium' }}>
                  This room can accommodate up to {maxOccupancy} {maxOccupancy === 1 ? 'person' : 'people'}
                </Typography> */}
              </Box>
            </CardContent>
          </Card>

          {/* Actual Occupancy Card */}
          <Card 
            elevation={1} 
            sx={{ 
              borderRadius: '12px',
              transition: 'transform 0.2s, box-shadow 0.2s',
              '&:hover': {
                transform: 'translateY(-3px)',
                boxShadow: '0 8px 16px rgba(0,0,0,0.1)'
              }
            }}
          >
            <CardContent sx={{ p: 2 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1 }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <PersonIcon 
                    sx={{ 
                      color: '#3554D1',
                      fontSize: 28,
                      mr: 1
                    }} 
                  />
                  <Typography variant="h6" sx={{ fontWeight: 'bold', fontSize:'16px' }}>
                    Total Occupancy: {(roomDatas?.tour_adult || 0)}
                  </Typography>
                </Box>
                
                {/* Display breakdown counts */}
                {/* <Box sx={{ display: 'flex', gap: 1 }}>
                  {roomDatas?.tour_adult > 0 && (
                    <Chip
                      icon={<PersonIcon />}
                      label={`${roomDatas.tour_adult} ${roomDatas.tour_adult === 1 ? 'Adult' : 'Adults'}`}
                      color="primary"
                      size="small"
                    />
                  )}
                  {roomDatas?.tour_child > 0 && (
                    <Chip
                      icon={<ChildCareIcon />}
                      label={`${roomDatas.tour_child} ${roomDatas.tour_child === 1 ? 'Child' : 'Children'}`}
                      color="warning"
                      size="small"
                    />
                  )}
                  {roomDatas?.tour_infant > 0 && (
                    <Chip
                      icon={<CribIcon />}
                      label={`${roomDatas.tour_infant} ${roomDatas.tour_infant === 1 ? 'Infant' : 'Infants'}`}
                      color="secondary"
                      size="small"
                    />
                  )}
                </Box> */}
              </Box>
              
              <Divider sx={{ my: 1 }} />
              
              <Box sx={{ 
                display: 'flex', 
                flexDirection: 'column',
                gap: 1.5, 
                mt: 1
              }}>
                {/* Adults section - combining male and female */}
                {(roomDatas?.tour_male > 0 || roomDatas?.tour_female > 0) && (
                  <Box sx={{ 
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 1,
                    p: 1,
                    borderRadius: '8px',
                    bgcolor: 'rgba(53, 84, 209, 0.05)',
                  }}>
                    <Typography variant="subtitle2" color="primary">
                      Adults ({(roomDatas?.tour_male || 0) + (roomDatas?.tour_female || 0)})
                    </Typography>
                    
                    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
                      {/* Male occupancy */}
                      {roomDatas?.tour_male > 0 && (
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                          <Chip
                            label="Male"
                            sx={{
                              backgroundColor: 'rgba(53, 84, 209, 0.1)',
                              color: '#3554D1',
                              fontWeight: 'bold',
                              minWidth: '70px',
                            }}
                            size="small"
                          />
                          <AvatarGroup max={5} sx={{ '& .MuiAvatar-root': { width: 28, height: 28, fontSize: '0.7rem' } }}>
                            {Array.from({ length: roomDatas.tour_male }).map((_, i) => (
                              <Avatar 
                                key={`male-avatar-${i}`} 
                                sx={{ 
                                  bgcolor: '#3554D1',
                                  color: 'white',
                                }}
                              >
                                <PersonIcon fontSize="small" />
                              </Avatar>
                            ))}
                          </AvatarGroup>
                        </Box>
                      )}
                      
                      {/* Female occupancy */}
                      {roomDatas?.tour_female > 0 && (
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                          <Chip
                            label="Female"
                            sx={{
                              backgroundColor: 'rgba(233, 30, 99, 0.1)',
                              color: '#E91E63',
                              fontWeight: 'bold',
                              minWidth: '70px',
                            }}
                            size="small"
                          />
                          <AvatarGroup max={5} sx={{ '& .MuiAvatar-root': { width: 28, height: 28, fontSize: '0.7rem' } }}>
                            {Array.from({ length: roomDatas.tour_female }).map((_, i) => (
                              <Avatar 
                                key={`female-avatar-${i}`} 
                                sx={{ 
                                  bgcolor: '#E91E63',
                                  color: 'white',
                                }}
                              >
                                <WomanIcon fontSize="small" />
                              </Avatar>
                            ))}
                          </AvatarGroup>
                        </Box>
                      )}
                    </Box>
                  </Box>
                )}
                
                {/* Children section */}
                {roomDatas?.tour_child > 0 && (
                  <Box sx={{ 
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 1,
                    p: 1,
                    borderRadius: '8px',
                    bgcolor: 'rgba(255, 152, 0, 0.05)',
                  }}>
                    <Typography variant="subtitle2" color="warning.main">
                      Children ({roomDatas.tour_child})
                    </Typography>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <AvatarGroup max={5} sx={{ '& .MuiAvatar-root': { width: 28, height: 28, fontSize: '0.7rem' } }}>
                        {Array.from({ length: roomDatas.tour_child }).map((_, i) => (
                          <Avatar 
                            key={`child-avatar-${i}`} 
                            sx={{ 
                              bgcolor: '#FF9800',
                              color: 'white',
                            }}
                          >
                            <ChildCareIcon fontSize="small" />
                          </Avatar>
                        ))}
                      </AvatarGroup>
                    </Box>
                  </Box>
                )}
                
                {/* Child Adults section (if present) */}
                {roomDatas?.tour_child_adult > 0 && (
                  <Box sx={{ 
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 1,
                    p: 1,
                    borderRadius: '8px',
                    bgcolor: 'rgba(76, 175, 80, 0.05)',
                  }}>
                    <Typography variant="subtitle2" color="success.main">
                      Child Adults ({roomDatas.tour_child_adult})
                    </Typography>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <AvatarGroup max={5} sx={{ '& .MuiAvatar-root': { width: 28, height: 28, fontSize: '0.7rem' } }}>
                        {Array.from({ length: roomDatas.tour_child_adult }).map((_, i) => (
                          <Avatar 
                            key={`child-adult-avatar-${i}`} 
                            sx={{ 
                              bgcolor: '#4CAF50',
                              color: 'white',
                            }}
                          >
                            <ChildCareIcon fontSize="small" />
                          </Avatar>
                        ))}
                      </AvatarGroup>
                    </Box>
                  </Box>
                )}
                
                {/* Infants section */}
                {roomDatas?.tour_infant > 0 && (
                  <Box sx={{ 
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 1,
                    p: 1,
                    borderRadius: '8px',
                    bgcolor: 'rgba(156, 39, 176, 0.05)',
                  }}>
                    <Typography variant="subtitle2" color="secondary.main">
                      Infants ({roomDatas.tour_infant})
                    </Typography>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <AvatarGroup max={5} sx={{ '& .MuiAvatar-root': { width: 28, height: 28, fontSize: '0.7rem' } }}>
                        {Array.from({ length: roomDatas.tour_infant }).map((_, i) => (
                          <Avatar 
                            key={`infant-avatar-${i}`} 
                            sx={{ 
                              bgcolor: '#9C27B0',
                              color: 'white',
                            }}
                          >
                            <CribIcon fontSize="small" />
                          </Avatar>
                        ))}
                      </AvatarGroup>
                    </Box>
                  </Box>
                )}
              </Box>
            </CardContent>
          </Card>
        </Box>

        {/* Extra Bed and Baby Cot Section - Redesigned */}
        <Box sx={{ 
          display: 'flex', 
          flexDirection: 'column', 
          gap: 2, 
          mt: 2, 
          mb: 2 
        }}>
          {/* Extra Bed Card */}
          {/* <Card 
            elevation={1} 
            sx={{ 
              borderRadius: '12px',
              transition: 'transform 0.2s, box-shadow 0.2s',
              '&:hover': {
                transform: 'translateY(-3px)',
                boxShadow: '0 8px 16px rgba(0,0,0,0.1)'
              }
            }}
          >
            <CardContent sx={{ p: 2 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <SingleBedOutlinedIcon 
                  sx={{ 
                    color: bed.extra_bed ? '#3554D1' : 'text.disabled',
                    fontSize: 28,
                    mr: 1
                  }} 
                />
                <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                  Extra Bed
                </Typography>
              </Box>
              
              <Divider sx={{ my: 1 }} />
              
              {bed.extra_bed ? (
                <Box sx={{ mt: 1 }}>
                  <Box sx={{ 
                    display: 'flex', 
                    alignItems: 'center', 
                    flexWrap: 'wrap',
                    gap: 1,
                    mb: 1
                  }}>
                    <Chip 
                      // icon={<CurrencyExchangeIcon />} 
                      label={`${currencyCode} ${(bed.extra_bed_price * exchangeRate).toFixed(2)}`}
                      color="primary"
                      variant="outlined"
                      sx={{ fontWeight: 'medium' }}
                    />
                    <Chip 
                      label={`${usdCurrencyCode} ${(bed.extra_bed_price * usdExchangeRate).toFixed(2)}`}
                      variant="outlined"
                      sx={{ fontWeight: 'medium' }}
                    />
                    <Chip 
                      label={`SGD ${bed.extra_bed_price}`}
                      variant="outlined"
                      sx={{ fontWeight: 'medium' }}
                    />
                  </Box>
                  <Typography variant="body2" color="text.secondary">
                    Add an extra bed to accommodate additional guests
                  </Typography>
                </Box>
              ) : (
                <Box sx={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  color: 'text.secondary',
                  mt: 1
                }}>
                  <DoNotDisturbAltOutlinedIcon sx={{ mr: 1 }} />
                  <Typography variant="body2">
                    Extra bed is not available for this room type
                  </Typography>
                </Box>
              )}
            </CardContent>
          </Card> */}

          {/* Baby Cot Card */}
          {bed.baby_cot && (
            <Card 
              elevation={1} 
              sx={{ 
                borderRadius: '12px',
                transition: 'transform 0.2s, box-shadow 0.2s',
                '&:hover': {
                  transform: 'translateY(-3px)',
                  boxShadow: '0 8px 16px rgba(0,0,0,0.1)'
                },
                border: babyCotStatus[bed.bed_id] ? '2px solid #3554D1' : 'none'
              }}
            >
              {/* Debug logs for baby cot price data */}
              {/* {console.log(`[BABY COT UI DEBUG] Baby cot for bed ${bed.bed_id}:`)}
              {console.log(`[BABY COT UI DEBUG] - Raw price: ${bed.baby_cot_price}`)}
              {console.log(`[BABY COT UI DEBUG] - Price type: ${typeof bed.baby_cot_price}`)}
              {console.log(`[BABY COT UI DEBUG] - Parsed price: ${Number(bed.baby_cot_price)}`)}
              {console.log(`[BABY COT UI DEBUG] - Status: ${babyCotStatus[bed.bed_id]}`)} */}
              
              <CardContent sx={{ p: 2 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <BabyChangingStationIcon 
                      sx={{ 
                        color: babyCotStatus[bed.bed_id] ? '#3554D1' : 'text.secondary',
                        fontSize: 28,
                        mr: 1
                      }} 
                    />
                    <Typography variant="h6" sx={{ fontWeight: 'bold' }}>
                      Baby Cot
                    </Typography>
                  </Box>
                  <Switch
                    checked={!!babyCotStatus[bed.bed_id]}
                    onChange={handleBabyCot}
                    color="primary"
                    disabled={bookingArray.length === 0}
                    inputProps={{ 'aria-label': 'controlled' }}
                    name={String(bed.bed_id)}
                  />
                </Box>
                
                <Divider sx={{ my: 1 }} />
                
                {babyCotStatus[bed.bed_id] ? (
                  <Box sx={{ mt: 1 }}>
                    {PriceHide =="0" ?
                    (
                    <Box sx={{ 
                      display: 'flex', 
                      alignItems: 'center', 
                      flexWrap: 'wrap',
                      gap: 1,
                      mb: 1
                    }}>
                      <Chip 
                        //icon={<CurrencyExchangeIcon />} 
                        label={`${currencyCode} ${(parseFloat(bed.baby_cot_price || 0) * exchangeRate).toFixed(2)}`}
                        color="primary"
                        variant="outlined"
                        sx={{ fontWeight: 'medium' }}
                      />
                      <Chip 
                        label={`${usdCurrencyCode} ${(parseFloat(bed.baby_cot_price || 0) * usdExchangeRate).toFixed(2)}`}
                        variant="outlined"
                        sx={{ fontWeight: 'medium' }}
                      />
                      <Chip 
                        label={`SGD ${parseFloat(bed.baby_cot_price || 0).toFixed(2)}`}
                        variant="outlined"
                        sx={{ fontWeight: 'medium' }}
                      />
                      </Box>  ):(
                        <div className="text-16 text-dark-1 fw-500">
                          Price available on request
                        </div>
                      )
                    }
                    <Typography variant="body2" color="primary">
                      Baby cot has been added to your selection
                    </Typography>
                  </Box>
                ) : (
                  <Typography variant="body2" color="text.secondary">
                    Toggle the switch to add a baby cot to your room
                    {bookingArray.length === 0 && (
                      <Typography variant="caption" color="error" sx={{ display: 'block', mt: 1 }}>
                        Please select a room first before adding a baby cot
                      </Typography>
                    )}
                  </Typography>
                )}
              </CardContent>
            </Card>
          )}
        </Box>
      </div>

      <div className="text-14 lh-18 text-light-1">
        <RenderRoomCards
          data={data}
          bedIndex={bedIndex}
          pax={pax}
          paxCount={paxCount}
          setPaxCount={setPaxCount}
          bed={bed}
          roomTypeIndex={roomTypeIndex}
          babyCot={babyCotStatus[bedId] || 0}
          bedArray={bedArray}
          setbedArray={setbedArray}
          occupancyArray={occupancyArray}
          setOccupancyArray={setOccupancyArray}
          occupancyCount={occupancyCount}
          setOccupancyCount={setOccupancyCount}
          globalCount={globalCount}
          setGlobalCount={setGlobalCount}
          maxOccupancy={maxOccupancy}
          bedId={bedId}
          priceArr={priceArr}
          setPriceArr={setPriceArr}
          mealType={mealType}
          setMealType={setMealType}
          hotelType={hotelType}
          setHotelType={setHotelType}
          totalPersonCount={totalPersonCount}
          setTotalPersonCount={setTotalPersonCount}
          acctualOccupency={pax}
          bookingArray={bookingArray}
        />
      </div>
    </div>
  );
})}
                  {/* End romm Grid horizontal content */}

                  {/* <div className="roomGrid__content">
                   
                    <div>
                      <div className="d-flex items-center text-light-1">
                        <div className="icon-man text-24" />
                        <div className="icon-man text-24" />
                      </div>
                    </div>
                    <div>
                      <div className="text-18 lh-15 fw-500">
                        US${hotel?.price}
                      </div>
                      <div className="text-14 lh-18 text-light-1">
                        Includes taxes and charges
                      </div>
                    </div>
                    <div>
                      <div className="dropdown js-dropdown js-price-1-active">
                        <select className="form-select dropdown__button d-flex items-center rounded-4 border-light px-15 h-50 text-14">
                          <option value="1" defaultValue>
                            1 (US$ 3,120)
                          </option>
                          <option value="2">2 (US$ 3,120)</option>
                          <option value="3"> 3 (US$ 3,120)</option>
                          <option value="4"> 4 (US$ 3,120)</option>
                          <option value="5"> 5 (US$ 3,120)</option>
                        </select>
                      </div>
                    </div>
                  </div> */}
                  {/* End romm Grid horizontal content */}
                </div>
                {/* End price features */}

                <div>
                  {/* <div className="text-14 lh-1">{occupancyCount} rooms for</div> */}
                  <div className="text-22 fw-500 lh-17 mt-5">
                    {PriceHide =="0" ?
                    (
                      <div className="text-22 text-dark-1 fw-500">
                      <Box sx={{ textAlign: 'right' }}>
                        <Typography variant="h6" color="primary">
                          {currencyCode} {convertedPrice}
                        </Typography>
                        <Typography variant="body1" color="text.secondary">
                          SGD {totalPrice}
                        </Typography>
                        <Typography variant="body1" color="text.secondary">
                          {usdCurrencyCode} {usdPrice}
                        </Typography>
                      </Box>
                      
                      {/* Show baby cot inclusion info if any baby cot is selected */}
                      {Object.values(babyCotStatus).some(status => status) && (
                        <Box sx={{ 
                          display: 'flex', 
                          alignItems: 'center', 
                          mt: 1,
                          p: 1,
                          borderRadius: '4px',
                          bgcolor: 'rgba(53, 84, 209, 0.08)',
                        }}>
                          <BabyChangingStationIcon sx={{ color: '#3554D1', mr: 1, fontSize: 18 }} />
                          <Typography variant="caption" sx={{ color: '#3554D1', fontWeight: 'medium' }}>
                            Baby cot price included in total
                          </Typography>
                        </Box>
                      )}
                    </div>
                    ):(
                      <div className="text-18 text-dark-1 fw-500">
                        Price not available
                      </div>
                    )
                    }
                 
                  </div>
                  <Button
                   onClick={handleCheckoutPage}
                  
                    className="button h-50 px-24 -dark-1 bg-blue-1 text-white mt-10"
                  >
                    Checkout <div className="icon-arrow-top-right ml-15" />
                  </Button>
                  <div className="text-15 fw-500 mt-30">
                    You&lsquo;ll be taken to the next step
                  </div>
                  {/* <ul className="list-disc y-gap-4 pt-5">
                    <li className="text-14">Confirmation is immediate</li>
                    <li className="text-14">No registration required</li>
                    <li className="text-14">No booking or credit card fees!</li>
                  </ul> */}
                </div>
                {/* End right price info */}
              </div>
            </div>
            {/* End .roomGrid */}
          </div>
        ))}
          {/* End .col-12 */}
        </div>
        {/* End .row */}
      </div>
      {/* End standard twin room */}

      {/* <div className="border-light rounded-4 px-30 py-30 sm:px-20 sm:py-20 mt-20">
        <div className="row y-gap-20">
          <div className="col-12">
            <h3 className="text-18 fw-500 mb-15">Deluxe King Room</h3>
            <div className="roomGrid">
              <div className="roomGrid__header">
                <div>Room Type</div>
                <div>Benefits</div>
                <div>Sleeps</div>
                <div>Price for 5 nights</div>
                <div>Select Rooms</div>
                <div />
              </div>
         

              <div className="roomGrid__grid">
                <div>
                  <div className="ratio ratio-1:1">
                    <img
                      src="/img/backgrounds/1.png"
                      alt="image"
                      className="img-ratio rounded-4"
                    />
                  </div>
                 
                  <div className="y-gap-5 mt-20">
                    <div className="d-flex items-center">
                      <i className="icon-no-smoke text-20 mr-10" />
                      <div className="text-15">Non-smoking rooms</div>
                    </div>
                    <div className="d-flex items-center">
                      <i className="icon-wifi text-20 mr-10" />
                      <div className="text-15">Free WiFi</div>
                    </div>
                    <div className="d-flex items-center">
                      <i className="icon-parking text-20 mr-10" />
                      <div className="text-15">Parking</div>
                    </div>
                    <div className="d-flex items-center">
                      <i className="icon-kitchen text-20 mr-10" />
                      <div className="text-15">Kitchen</div>
                    </div>
                  </div>
             
                  <a
                    href="#"
                    className="d-block text-15 fw-500 underline text-blue-1 mt-15"
                  >
                    Show Room Information
                  </a>
                </div>
            

               <div className="y-gap-30">
                  <div className="roomGrid__content">
                    <div>
                      <div className="text-15 fw-500 mb-10">
                        Your price includes:
                      </div>
                      <div className="y-gap-8">
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">Pay at the hotel</div>
                        </div>
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">
                            Pay nothing until March 30, 2022
                          </div>
                        </div>
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">
                            Free cancellation before April 1, 2022
                          </div>
                        </div>
                      </div>
                    </div>

                    <div>
                      <div className="d-flex items-center text-light-1">
                        <div className="icon-man text-24" />
                        <div className="icon-man text-24" />
                      </div>
                    </div>

                    <div>
                      <div className="text-18 lh-15 fw-500">
                        US${hotel?.price}
                      </div>
                      <div className="text-14 lh-18 text-light-1">
                        Includes taxes and charges
                      </div>
                    </div>

                    <div>
                      <div className="dropdown js-dropdown js-price-1-active">
                        <select className="form-select dropdown__button d-flex items-center rounded-4 border-light px-15 h-50 text-14">
                          <option value="1" defaultValue>
                            1 (US$ 3,120)
                          </option>
                          <option value="2">2 (US$ 3,120)</option>
                          <option value="3"> 3 (US$ 3,120)</option>
                          <option value="4"> 4 (US$ 3,120)</option>
                          <option value="5"> 5 (US$ 3,120)</option>
                        </select>
                      </div>
                    </div>
                  </div>
     

                  <div className="roomGrid__content">
                    <div>
                      <div className="text-15 fw-500 mb-10">
                        Your price includes:
                      </div>
                      <div className="y-gap-8">
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">Pay at the hotel</div>
                        </div>
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">
                            Pay nothing until March 30, 2022
                          </div>
                        </div>
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">
                            Free cancellation before April 1, 2022
                          </div>
                        </div>
                      </div>
                    </div>
                    <div>
                      <div className="d-flex items-center text-light-1">
                        <div className="icon-man text-24" />
                        <div className="icon-man text-24" />
                      </div>
                    </div>
                    <div>
                      <div className="text-18 lh-15 fw-500">
                        US${hotel?.price}
                      </div>
                      <div className="text-14 lh-18 text-light-1">
                        Includes taxes and charges
                      </div>
                    </div>
                    <div>
                      <div className="dropdown js-dropdown js-price-1-active">
                        <select className="form-select dropdown__button d-flex items-center rounded-4 border-light px-15 h-50 text-14">
                          <option value="1" defaultValue>
                            1 (US$ 3,120)
                          </option>
                          <option value="2">2 (US$ 3,120)</option>
                          <option value="3"> 3 (US$ 3,120)</option>
                          <option value="4"> 4 (US$ 3,120)</option>
                          <option value="5"> 5 (US$ 3,120)</option>
                        </select>
                      </div>
                    </div>
                  </div>
           

                  <div className="roomGrid__content">
                    <div>
                      <div className="text-15 fw-500 mb-10">
                        Your price includes:
                      </div>
                      <div className="y-gap-8">
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">Pay at the hotel</div>
                        </div>
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">
                            Pay nothing until March 30, 2022
                          </div>
                        </div>
                        <div className="d-flex items-center text-green-2">
                          <i className="icon-check text-12 mr-10" />
                          <div className="text-15">
                            Free cancellation before April 1, 2022
                          </div>
                        </div>
                      </div>
                    </div>
                    <div>
                      <div className="d-flex items-center text-light-1">
                        <div className="icon-man text-24" />
                        <div className="icon-man text-24" />
                      </div>
                    </div>
                    <div>
                      <div className="text-18 lh-15 fw-500">
                        US${hotel?.price}
                      </div>
                      <div className="text-14 lh-18 text-light-1">
                        Includes taxes and charges
                      </div>
                    </div>
                    <div>
                      <div className="dropdown js-dropdown js-price-1-active">
                        <select className="form-select dropdown__button d-flex items-center rounded-4 border-light px-15 h-50 text-14">
                          <option value="1" defaultValue>
                            1 (US$ 3,120)
                          </option>
                          <option value="2">2 (US$ 3,120)</option>
                          <option value="3"> 3 (US$ 3,120)</option>
                          <option value="4"> 4 (US$ 3,120)</option>
                          <option value="5"> 5 (US$ 3,120)</option>
                        </select>
                      </div>
                    </div>
                  </div>
               
                </div> 
              
                <div>
                  <div className="text-14 lh-1">3 rooms for</div>
                  <div className="text-22 fw-500 lh-17 mt-5">
                    US${hotel?.price}
                  </div>
                  <a
                    href="#"
                    className="button h-50 px-24 -dark-1 bg-blue-1 text-white mt-10"
                  >
                    Reserve <div className="icon-arrow-top-right ml-15" />
                  </a>
                  <div className="text-15 fw-500 mt-30">
                    You&lsquo;ll be taken to the next step
                  </div>
                  <ul className="list-disc y-gap-4 pt-5">
                    <li className="text-14">Confirmation is immediate</li>
                    <li className="text-14">No registration required</li>
                    <li className="text-14">No booking or credit card fees!</li>
                  </ul>
                </div> 
       
              </div>
            </div>
        
          </div>
        
        </div>
    
      </div> */}
 
    </>
  );
};

export default AvailableRooms;
