import { useSelector } from "react-redux";
import dayjs from "dayjs";
import CustomStepper from "../common/sub_common/CustomStepper";
import TourStatus from "../common/sub_common/TourStatus";
import { useNavigate } from "react-router-dom";
import React from "react";
import { Avatar } from "@mui/material";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../slice/dmc/dmcSlice"; // Import DMC slice selectors

const OrderSubmittedInfo = () => {
  const bookingResponse = useSelector((state) => state.hotels.bookingResponse);
  const navigate = useNavigate();
  // Get currency information from Redux store
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  
  // Get tax values from auth slice
  const currentTax = useSelector((state) => state.auth.currentTax || 0);
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const usdTax = useSelector((state) => state.auth.usdTax || 0);
  
  // Get price mode from state
  const priceMode = useSelector((state) => state.hotels.searchState.priceMode) || 'dmc';
  const PriceHide = useSelector((state) => state.auth.PriceHide);


  if (!bookingResponse) {
    return <p>Loading booking details...</p>;
  }

  const { message, order, service } = bookingResponse;
  
  // Get the latest booking from service.data array
  const latestBooking = service?.data?.[service.data.length - 1] || {};
  
  // Extract check-in and check-out dates from bookingDate array
  const checkInDate = latestBooking.bookingDate?.[0] || null;
  const checkOutDate = latestBooking.bookingDate?.[1] || null;
  
  // Format price with commas for thousands
  const formatPrice = (price) => {
    if (typeof price !== 'number') return '0.00';
    
    return price.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  };
  
  // Calculate prices with ceiling and taxes
  const basePrice = parseFloat(latestBooking.totalPrice) || 0;
  
  // Step 1: Ceiling the base prices
  const sgdPrice = Math.ceil(basePrice);
  const usdPrice = Math.ceil(basePrice * usdExchangeRate);
  const convertedPrice = Math.ceil(basePrice * exchangeRate);
  
  // Step 2: Calculate tax amounts for all currencies
  const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
  const usdTaxAmount = Math.ceil((usdPrice * usdTax) / 100);
  const convertedTaxAmount = Math.ceil((convertedPrice * currentTax) / 100);
  
  // Step 3: Calculate grand totals for all currencies
  const sgdGrandTotal = sgdPrice + sgdTaxAmount;
  const usdGrandTotal = usdPrice + usdTaxAmount;
  const convertedGrandTotal = convertedPrice + convertedTaxAmount;
  
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC'; 

  const handleClick = () => {
    console.log("Clicked");
    navigate("/dashboard/db-dashboard/view-hotel-search/:id");
  };
  return (
    <>
      <div className="header-margin">
        <CustomStepper />
        <TourStatus />
      </div>
      <div className="col-xl-12 col-lg-12 d-flex justify-content-center">
        <div className="col-xl-6 col-lg-6">
          <div className="order-completed-wrapper">
            <div className="d-flex flex-column items-center mt-40 lg:md-40 sm:mt-24">
              <div className="size-80 flex-center rounded-full bg-dark-3">
                <i className="icon-check text-30 text-white" />
              </div>
              <div className="text-30 lh-1 fw-600 mt-20">
                {message}
                <div className="text-15 flex-center text-light-1 mt-30">
                {/* Booking details have been sent to:{" "}
                {parsedData.length > 0
                  ? parsedData[0].email
                  : "No email available"} */}
                <button
                  className="button px-15 py-8 bg-blue-1 text-white rounded position-absolute justify-content-center"
                  style={{
                    //top: "100px",
                    //left: "40px",
                    zIndex: "10",
                    minHeight: "40px",
                    animation: "blink 1s infinite",
                    WebkitAnimation: "blink 1s infinite", // For better browser support
                  }}
                  onClick={handleClick}
                >
                  Add More
                  <style>
                    {`
      @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
      }
    `}
                  </style>
                </button>
              </div>
              </div>
              {/* <div className="text-15 text-light-1 mt-20">
                {latestBooking.email && `Booking details has been sent to: ${latestBooking.email}`}
              </div> */}
            </div>

            <div className="border-type-1 rounded-8 px-50 py-35 mt-40">
              <div className="row">
                <div className="col-lg-2 col-md-6">
                  <div className="text-15 lh-12">Booking Number</div>
                  <div className="text-12 lh-12 fw-500 text-blue-1 mt-10">
                    {order?.tour_id}
                  </div>
                </div>
                {/* <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Booking Date</div>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {order.created_at ? dayjs(order.created_at).format("DD MMM YYYY") : "Date not available"}
                  </div>
                </div> */}
                <div className="col-lg-2 col-md-6">
                  <div className="text-15 lh-12">Check In</div>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {checkInDate ? dayjs(checkInDate).format("DD MMM YYYY") : "Date not available"}
                  </div>
                </div>
                <div className="col-lg-2 col-md-6">
                  <div className="text-15 lh-12">Check Out</div>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {checkOutDate ? dayjs(checkOutDate).format("DD MMM YYYY") : "Date not available"}
                  </div>
                </div>
                <div className="col-lg-3 col-md-6">
                 
                  
                  <div className="text-15 lh-12">Total Price</div>
                  {PriceHide === "0" ?
                  (
                  <>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {currencyCode} {formatPrice(convertedGrandTotal)} <span className="text-13 text-light-1">(incl. {currentTax}% tax)</span>
                  </div>
                 
                  <div className="text-13 lh-12 text-light-1">
                    {usdCurrencyCode} {formatPrice(usdGrandTotal)} <span className="text-12">(incl. {usdTax}% tax)</span>
                  </div>
                  <div className="text-13 lh-12 text-light-1">
                    SGD {formatPrice(sgdGrandTotal)} <span className="text-12">(incl. {sgdTax}% tax)</span>
                  </div>
                  </>
                  ):(
                    <div className="text-15 lh-12">
                      Price available on request
                    </div>
                  )
                }
                </div>
                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Booking Mode</div>
                  <div 
                    className="text-15 lh-12 fw-500 text-blue-1 mt-10"
                    style={{ display: "flex", alignItems: "center" }}
                  >
                    {latestBooking.priceMode === "dmc" || (Array.isArray(latestBooking.priceMode) && latestBooking.priceMode[0] === "dmc") ? (
                      <div style={{ 
                        display: 'flex', 
                        alignItems: 'center', 
                        gap: '8px' 
                      }}>
                        {dmcLogo && (
                          <Avatar
                            src={dmcLogo}
                            alt={`${dmcCompanyName} Logo`}
                            sx={{ 
                              width: 24,
                              height: 24,
                            }}
                          />
                        )}
                        <span>{`${dmcCompanyName}'s Mode`}</span>
                      </div>
                    ) : latestBooking.priceMode === "travClicks" || 
                        latestBooking.priceMode === "travclicks" || 
                        (Array.isArray(latestBooking.priceMode) && latestBooking.priceMode.includes("travclick")) ? (
                      "Travclicks"
                    ) : (
                      latestBooking.priceMode && typeof latestBooking.priceMode === 'string' ? 
                        latestBooking.priceMode.charAt(0).toUpperCase() + 
                        latestBooking.priceMode.slice(1) : 
                        "Mode not available"
                    )}
                  </div>
                </div>
              </div>
            </div>

            <div className="border-light rounded-8 px-50 py-40 mt-40">
              <h4 className="text-20 fw-500 mb-30">Customer Information</h4>
              <div className="row y-gap-10">
                {/* <div className="col-12">
                  <div className="d-flex justify-between">
                    <div className="text-15 lh-16">Agent ID</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {order?.agent_id}
                    </div>
                  </div>
                </div> */}
                {/* <div className="col-12">
                  <div className="border-top-light my-10"></div>
                </div> */}
                <div className="col-12">
                  <div className="d-flex justify-between">
                    <div className="text-15 lh-16">Full Name</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {latestBooking.fullName}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Email</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {latestBooking.email || 'Not provided'}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Phone</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {latestBooking.phone}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Address line 1</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {latestBooking.address1}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Address line 2</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {latestBooking.address2 || 'Not provided'}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">State</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {latestBooking.state}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">ZIP code</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {latestBooking.zip}
                    </div>
                  </div>
                </div>
                {latestBooking.specialRequests && (
                  <div className="col-12">
                    <div className="d-flex justify-between border-top-light pt-10">
                      <div className="text-15 lh-16">Special Requirements</div>
                      <div className="text-15 lh-16 fw-500 text-blue-1">
                        {latestBooking.specialRequests}
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default OrderSubmittedInfo;
