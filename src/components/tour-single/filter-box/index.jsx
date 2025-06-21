import { useState, useEffect} from "react";
import { useSelector, useDispatch } from "react-redux";
import { useLocation } from "react-router-dom";
import Cookies from "js-cookie";
import dayjs from "dayjs";
import {addAttractionBookings} from "../../../slice/attractions/attractionSlice";
import GuestSearch from "./GuestSearch";
import DateSearch from "./DateSearch";
import TimeSlot from "./TimeSlot";
import TicketSelection from "./TicketSelection";
// import TransportSelection from "./TransportSelection";

import { toast } from "react-toastify";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { useNavigate } from "react-router-dom";
import { setBookingMode } from '../../../slice/common/commonSlice';

const index = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const AgentId = Cookies.get("AgentId") || "0";
  const location = useLocation();
  const attraction = location.state?.attraction || {};
  

   console.log('attraction',attraction);
  

  const attractionDetails = useSelector(
    (state) => state.attractions.attractionDetails || { prices: {} }
  );
      console.log('attractionDetails',attractionDetails);
  

 
  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const searchParams = useSelector((state) => state.attractions.searchParams);

  // Get currency information from Redux store
  // const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode) || 'SGD';
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate) || 1;
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // Extracting prices from the attractionDetails
  // const adult_price =
  //   attractionDetails.prices?.travClicks_adult_price ||
  //   attractionDetails.prices?.dmc_adult_price ||
  //   0;
  // const child_price =
  //   attractionDetails.prices?.travClicks_child_price ||
  //   attractionDetails.prices?.dmc_child_price ||
  //   0;
  // const shared_price =
  //   attractionDetails.prices?.travClicks_shared_price ||
  //   attractionDetails.prices?.dmc_shared_price ||
  //   0;
  // const private_price =
  //   attractionDetails.prices?.travClicks_private_price ||
  //   attractionDetails.prices?.dmc_private_price ||
  //   0;

  // Calculate prices for different modes
  // const withoutTraveller =
  //   searchParams.adults * adult_price + searchParams.children * child_price;

  // const withPrivate = withoutTraveller + private_price;

  // const withShare =
  //   withoutTraveller +
  //   (searchParams.adults + searchParams.children) * shared_price;

  const [selectedDate, setSelectedDate] = useState(null);
  const [selectedTime, setSelectedTime] = useState("");
  const [guestCounts, setGuestCounts] = useState({
    Adults: tourdetails?.adult > 0 ? tourdetails.adult : 1,
    Children: tourdetails?.child || 0,
    Seniors: 0
  });
  
  // New state for ticket selection
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [transportOption, setTransportOption] = useState(null); // null, "private", or "shared"
  const [selectedVehicle, setSelectedVehicle] = useState(null);
  
  // For tracking price updates
  const [priceUpdateTrigger, setPriceUpdateTrigger] = useState(0);
  
  // Get ticket prices and vehicles from attraction details
  const ticketOptions = attractionDetails.ticket_prices || [];
  const vehicles = attractionDetails.vehicles || [];
  
  // Check if transport options are available
  const hasTransportOptions = vehicles && vehicles.length > 0;

  // Removed auto-select first ticket when time is selected to ensure user must make the selection
  
  useEffect(() => {
    setGuestCounts({
      Adults: tourdetails?.adult > 0 ? tourdetails.adult : 1,
      Children: tourdetails?.child || 0,
      Seniors: 0
    });
  }, [tourdetails]);

  useEffect(() => {
    if (searchParams?.date) {
      setSelectedDate(searchParams.date);
    }
  }, [searchParams]);

  // New state for NRI status
  const [nriStatus, setNriStatus] = useState("residential");

  // New useEffect that would typically get nriStatus from attractionDetails or other source
  useEffect(() => {
    // Set NRI status based on attraction details or other criteria
    if (attractionDetails && attractionDetails.nri) {
      setNriStatus(attractionDetails.nri);
    } else {
      setNriStatus("residential");
    }
  }, [attractionDetails]);

  // Force re-render when nriStatus changes to update price calculations
  useEffect(() => {
    setPriceUpdateTrigger(prev => prev + 1);
    console.log("NRI status changed to:", nriStatus);
  }, [nriStatus]);

  // Also force re-render when selectedTicket changes
  useEffect(() => {
    if (selectedTicket) {
      setPriceUpdateTrigger(prev => prev + 1);
    }
  }, [selectedTicket]);

  // Add effect to track ticket selection changes
  useEffect(() => {
    console.log("Selected ticket changed in parent:", selectedTicket);
  }, [selectedTicket]);

  // Calculate total price based on selected options
  const calculateTotalPrice = () => {
    if (!selectedTicket) return 0;
    
    let totalPrice = 0;
    
    // Debug the nriStatus
    console.log("Current NRI status in price calculation:", nriStatus);
    
    // Get numeric values with safe parsing - ensure we're reading the latest nriStatus
    let adultPrice, childPrice, seniorPrice;
    
    if (nriStatus === "nri") {
      // For NRI prices, use 0 if null
      adultPrice = selectedTicket.dmc_adult_price_nri !== null ? 
        parseFloat(selectedTicket.dmc_adult_price_nri || 0) : 0;
      childPrice = selectedTicket.dmc_child_price_nri !== null ? 
        parseFloat(selectedTicket.dmc_child_price_nri || 0) : 0;
      seniorPrice = selectedTicket.dmc_senior_price_nri !== null ? 
        parseFloat(selectedTicket.dmc_senior_price_nri || 0) : 0;
    } else {
      // For residential prices, use normal values
      adultPrice = parseFloat(selectedTicket.dmc_adult_price || 0);
      childPrice = parseFloat(selectedTicket.dmc_child_price || 0);
      seniorPrice = parseFloat(selectedTicket.dmc_senior_price || 0);
    }
    
    // Log the prices being used
    console.log("Using prices:", { adultPrice, childPrice, seniorPrice });
    
    // Ensure these are numbers
    const adultCount = parseInt(guestCounts.Adults || 0, 10);
    const childCount = parseInt(guestCounts.Children || 0, 10);
    const seniorCount = parseInt(guestCounts.Seniors || 0, 10);
    
    // Calculate ticket total
    const ticketTotal = (adultCount * adultPrice) + (childCount * childPrice) + (seniorCount * seniorPrice);
    totalPrice += ticketTotal;
    
    // Add transport price if selected
    if (transportOption && selectedVehicle) {
      if (transportOption === "private") {
        const privatePrice = parseFloat(selectedVehicle.attraction_private_transport_price || 0);
        if (!isNaN(privatePrice)) {
          totalPrice += privatePrice;
        }
      } else if (transportOption === "shared") {
        const sharedPrice = parseFloat(selectedVehicle.attraction_shared_transport_price || 0);
        if (!isNaN(sharedPrice)) {
          const totalPeople = adultCount + childCount + seniorCount;
          totalPrice += totalPeople * sharedPrice;
        }
      }
    }
    
    return isNaN(totalPrice) ? 0 : totalPrice;
  };

  const handleBookNow = () => {
    if (!selectedDate) {
      toast.error("Please select a date for your visit.");
      return;
    }
    
    if (!selectedTime) {
      toast.error("Please select a time slot for your visit.");
      return;
    }
    
    if (!selectedTicket) {
      toast.error("Please select a ticket package.");
      return;
    }
    
    // Removed transport option validation
    // if (hasTransportOptions && !transportOption) {
    //     toast.error("Please select a transportation option.");
    //     return;
    // }
    
    // If transport option is selected but no vehicle is selected
    if (transportOption && !selectedVehicle) {
      toast.error("Please select a vehicle for transportation.");
      return;
    }
    
    // Log the NRI status used for booking
    console.log("Booking with NRI status:", nriStatus);
    
    // Get accurate counts for database
    const adultCount = parseInt(guestCounts.Adults || 0, 10);
    const childCount = parseInt(guestCounts.Children || 0, 10);
    const seniorCount = parseInt(guestCounts.Seniors || 0, 10);
    
    // Get price values, ensuring they are numeric
    let baseTicketPrice, childTicketPrice, seniorTicketPrice;
    
    if (nriStatus === "nri") {
      // For NRI, use 0 if price is null
      baseTicketPrice = selectedTicket.dmc_adult_price_nri !== null ? 
        parseFloat(selectedTicket.dmc_adult_price_nri || 0) : 0;
      childTicketPrice = selectedTicket.dmc_child_price_nri !== null ? 
        parseFloat(selectedTicket.dmc_child_price_nri || 0) : 0;
      seniorTicketPrice = selectedTicket.dmc_senior_price_nri !== null ? 
        parseFloat(selectedTicket.dmc_senior_price_nri || 0) : 0;
    } else {
      // For residential prices
      baseTicketPrice = parseFloat(selectedTicket.dmc_adult_price || 0);
      childTicketPrice = parseFloat(selectedTicket.dmc_child_price || 0);
      seniorTicketPrice = parseFloat(selectedTicket.dmc_senior_price || 0);
    }
    
    // Log the actual prices being used
    console.log("Using ticket prices:", { baseTicketPrice, childTicketPrice, seniorTicketPrice, nriStatus });
    
    // Calculate ticket total - simply multiply price by count, no extra multiplier
    const calculatedTotal = (adultCount * baseTicketPrice) + 
                           (childCount * childTicketPrice) + 
                           (seniorCount * seniorTicketPrice);
    
    // Add transport cost if applicable
    let transportCost = 0;
    if (transportOption && selectedVehicle) {
      if (transportOption === "private") {
        transportCost = parseFloat(selectedVehicle.attraction_private_transport_price || 0);
      } else if (transportOption === "shared") {
        const sharedPrice = parseFloat(selectedVehicle.attraction_shared_transport_price || 0);
        transportCost = (adultCount + childCount + seniorCount) * sharedPrice;
      }
    }
    
    // Calculate final price
    const totalPrice = calculatedTotal + transportCost;
    
    // Determine the Selection value for database
    let selectionValue = "withoutTransport";
    if (transportOption === "private") {
      selectionValue = "withPrivate";
    } else if (transportOption === "shared") {
      selectionValue = "withShare";
    }
    
    // Build transport details if selected
    let transportDetails = null;
    if (transportOption && selectedVehicle) {
      transportDetails = {
        type: transportOption,
        vehicle_id: selectedVehicle.vehicle_id,
        vehicle_name: selectedVehicle.vehicle_name,
        vehicle_type : selectedVehicle.vehicle_type,
        vehicle_capacity: selectedVehicle.seating_capacity,
        price: transportOption === "private" 
          ? parseFloat(selectedVehicle.attraction_private_transport_price || 0) 
          : parseFloat(selectedVehicle.attraction_shared_transport_price || 0),
        is_shared: transportOption === "shared"
      };
    }

    // Determine booking mode based on attraction details
    const mode = transportOption ? transportOption : 
              (selectedTicket.dmc_id ? "dmc" : 
              (selectedTicket.travclicks_id ? "travclicks" : "default"));
    
    // Set the booking mode in Redux for CustomerInfo component
    dispatch(setBookingMode(mode));

    const bookingDetails = {
      agent_id: parseInt(AgentId, 10) || 0,
      data: [
        {
          bookingDate: dayjs(selectedDate).format("YYYY-MM-DD"),
          visitTime: selectedTime,
          adultCount: adultCount,
          childCount: childCount,
          seniorCount: seniorCount,
          AttractionId: attraction?.id,
          AttractionName: attraction?.attraction_name || "Unknown Attraction",
          ticketId: selectedTicket.ticket_id,
          ticketName: selectedTicket.ticket_name,
          ticket_details: {
            adult_price: nriStatus === "nri" ? 
              (selectedTicket.dmc_adult_price_nri !== null ? parseFloat(selectedTicket.dmc_adult_price_nri || 0) : 0) : 
              parseFloat(selectedTicket.dmc_adult_price || 0),
            child_price: nriStatus === "nri" ? 
              (selectedTicket.dmc_child_price_nri !== null ? parseFloat(selectedTicket.dmc_child_price_nri || 0) : 0) : 
              parseFloat(selectedTicket.dmc_child_price || 0),
            senior_price: nriStatus === "nri" ? 
              (selectedTicket.dmc_senior_price_nri !== null ? parseFloat(selectedTicket.dmc_senior_price_nri || 0) : 0) : 
              parseFloat(selectedTicket.dmc_senior_price || 0),
            description: selectedTicket.description || "",
            nri: nriStatus
          },
          Selection: selectionValue,
          transport: transportDetails,
          mode: mode,
          totalPrice: totalPrice,
          dmc_id: selectedTicket.dmc_id || attractionDetails.prices?.dmc_id || null,
          travclicks_id: selectedTicket.travclicks_id || attractionDetails.prices?.travclicks_id || null,
          child_max_age: attractionDetails.child_max_age || 17,
          senior_min_age: attractionDetails.senior_min_age || 60,
        },
      ],
      tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
      type: "attraction",
    };

    // Dispatch booking details to Redux
    dispatch(addAttractionBookings(bookingDetails));

    // Navigate to checkout page
    navigate('/dashboard/db-dashboard/attraction-checkout');
  };

  // Format price in different currencies
  const formatPrice = (price, type) => {
    if (!price) return "0.00";
    
    switch(type) {
      case "main":
        return `${currencyCode} ${formatCurrency(price * exchangeRate)}`;
      case "usd":
        return `USD ${formatCurrency(price * usdExchangeRate)}`;
      case "sgd":
        return `SGD ${formatCurrency(price)}`;
      default:
        return `SGD ${formatCurrency(price)}`;
    }
  };

  // Format currency with 2 decimal places and thousands separator
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SG', {
      style: 'decimal',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(amount);
  };

  // Get accurate counts for display
  const getActualCounts = () => {
    const adultCount = parseInt(guestCounts.Adults || 0, 10);
    const seniorCount = parseInt(guestCounts.Seniors || 0, 10);
    const childCount = parseInt(guestCounts.Children || 0, 10);
    
    return { adultCount, seniorCount, childCount };
  };

  // Show TimeSlot section only if date is selected
  const showTimeSlot = selectedDate !== null;
  
  // Show TicketPackage section only if time is selected
  const showTicketPackage = selectedTime !== "";
  
  // Show Transport section only if ticket is selected
  const showTransport = selectedTicket !== null && hasTransportOptions;
  
  // Show Checkout button and price summary only if all required selections are made
  const showCheckout = selectedTicket !== null;

  return (
    <>
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4">
          <h4 className="text-15 fw-500 ls-2 lh-16">Date</h4>
          <DateSearch setSelectedDate={setSelectedDate} />
        </div>
      </div>
      <div className="col-12">
        <GuestSearch setGuestCounts={setGuestCounts} />
      </div>
      
      {showTimeSlot && (
        <div className="col-12">
          <div className="searchMenu-date px-20 py-10 border-light rounded-4">
            <h4 className="text-15 fw-500 ls-2 lh-16">Time Slot</h4>
            <TimeSlot setSelectedTime={setSelectedTime} />
          </div>
        </div>
      )}

      {showTicketPackage && (
        <div className="col-12">
          <div className="searchMenu-date px-20 py-10 border-light rounded-4">
            <h4 className="text-15 fw-500 ls-2 lh-16">Ticket Package</h4>
            <TicketSelection 
              ticketOptions={ticketOptions}
              selectedTicket={selectedTicket}
              setSelectedTicket={setSelectedTicket}
              isModalOpen={isModalOpen}
              setIsModalOpen={setIsModalOpen}
              nriStatus={nriStatus}
              setNriStatus={setNriStatus}
            />
          </div>
        </div>
      )}
      
      {/* {showTransport && (
        <div className="col-12">
          <div className="searchMenu-date px-20 py-10 border-light rounded-4">
            <h4 className="text-15 fw-500 ls-2 lh-16">Transportation</h4>
            <TransportSelection
              transportOption={transportOption}
              setTransportOption={setTransportOption}
              hasTransportOptions={hasTransportOptions}
              vehicles={vehicles}
              selectedVehicle={selectedVehicle}
              setSelectedVehicle={setSelectedVehicle}
              guestCounts={guestCounts}
              selectedTicket={selectedTicket}
              currencyCode={currencyCode}
              exchangeRate={exchangeRate}
              usdExchangeRate={usdExchangeRate}
            />
          </div>
        </div>
      )} */}
      
      {showCheckout && (
        <div className="col-12 mt-15">
          <div className="py-15 px-20 border-light rounded-4 bg-blue-1-05">
            <div className="d-flex justify-content-between align-items-center">
              <h5 className="text-16 fw-500 text-blue-1 mb-0">Total Price {nriStatus === "nri" ? "(Foreigner)" : "(Local)"}</h5>
              <div className="text-18 fw-500" key={`price-${nriStatus}-${priceUpdateTrigger}`}>
                {formatPrice(calculateTotalPrice(), "main")}
              </div>
            </div>
            
            {/* Alternative currencies - only display if PriceHide is "0" */}
            {PriceHide === "0" && (
              <>
                {currencyCode !== 'USD' && (
                  <div className="d-flex justify-content-end mt-5">
                    <div className="text-14 text-light-1" key={`usd-${nriStatus}-${priceUpdateTrigger}`}>
                      {formatPrice(calculateTotalPrice(), "usd")}
                    </div>
                  </div>
                )}
                
                {currencyCode !== 'SGD' && (
                  <div className="d-flex justify-content-end">
                    <div className="text-14 text-light-1" key={`sgd-${nriStatus}-${priceUpdateTrigger}`}>
                      {formatPrice(calculateTotalPrice(), "sgd")}
                    </div>
                  </div>
                )}
              </>
            )}
            
            {/* Breakdown Details */}
            <div className="border-top-light mt-10 pt-10">
              <div className="text-14">
                <div className="d-flex justify-content-between mb-5">
                  <span>Tickets:</span>
                  <span>
                    {(() => {
                      const { adultCount, seniorCount, childCount } = getActualCounts();
                      let ticketText = `${adultCount} Adults`;
                      
                      if (seniorCount > 0) {
                        ticketText += `, ${seniorCount} Seniors`;
                      }
                      
                      if (childCount > 0) {
                        ticketText += `, ${childCount} Children`;
                      }
                      
                      return ticketText;
                    })()}
                  </span>
                </div>
                
                {/* {transportOption && selectedVehicle && (
                  <div className="d-flex justify-content-between">
                    <span>Transport:</span>
                    <span>
                      {transportOption === "private" ? "Private" : "Shared"} - {selectedVehicle.vehicle_name}
                    </span>
                  </div>
                )} */}
              </div>
            </div>
          </div>
        </div>
      )}

      {showCheckout && (
        <div className="col-12">
          <button
            className="button -dark-1 py-15 px-35 h-60 col-12 rounded-4 bg-blue-1 text-white"
            onClick={handleBookNow}
            style={{
              transition: "all 0.3s ease",
              boxShadow: "0 4px 10px rgba(53, 84, 209, 0.25)",
              fontWeight: "600",
              letterSpacing: "0.5px",
              marginTop: "15px"
            }}
            onMouseOver={(e) => {
              e.currentTarget.style.transform = "translateY(-2px)";
              e.currentTarget.style.boxShadow = "0 6px 15px rgba(53, 84, 209, 0.35)";
            }}
            onMouseOut={(e) => {
              e.currentTarget.style.transform = "translateY(0)";
              e.currentTarget.style.boxShadow = "0 4px 10px rgba(53, 84, 209, 0.25)";
            }}
          >
            Check Out
          </button>
        </div>
      )}
     
      <ToastContainer />
    </>
  );
};

export default index;

