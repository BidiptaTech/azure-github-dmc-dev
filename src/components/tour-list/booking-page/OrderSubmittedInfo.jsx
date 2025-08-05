import React from "react";
import { useNavigate } from "react-router-dom";
import dayjs from "dayjs";
import TourStatus from "@/components/common/sub_common/TourStatus";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import { useLocation } from "react-router-dom";
import { useSelector } from "react-redux";
import { Avatar, Box, Chip, Typography } from "@mui/material";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../slice/dmc/dmcSlice"; // Import DMC slice selectors

const OrderSubmittedInfo = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const bookingResponse = location.state?.bookingResponse;

  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  const priceMode =
    useSelector((state) => state.hotels.searchState.priceMode) || "dmc";
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  
  // Get tax values from auth slice instead of attractions
  const currentTax = useSelector((state) => state.auth.currentTax || 0);
  const sgdTax = useSelector((state) => state.auth.sgdTax || 0);
  const usdTax = useSelector((state) => state.auth.usdTax || 0);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // console.log("Received booking response:", bookingResponse);

  if (!bookingResponse) {
    return <p>Loading booking details...</p>;
  }

  const { message, order, service } = bookingResponse;
  const orderData = order?.data || [];  // Assuming order.data is already an array

  const handleClick = () => {
    navigate("/dashboard/db-dashboard/attractions");
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
                {message === "Attraction_package Booking created successfully." 
                  ? "Attraction package booked successfully." 
                  : message}
              </div>
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
            {/* End header */}

            <div className="border-type-1 rounded-8 px-50 py-35 mt-40">
              <div className="row">
                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Booking Number</div>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {order?.booking_id}
                  </div>
                </div>
                {/* End .col */}
                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Booking Date</div>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {orderData.length > 0
                      ? orderData.map((item) => dayjs(item.bookingDate).format("ddd, D MMM'YY")).join(", ")
                      : "Booking date not available"}
                  </div>
                </div>
                {/* End .col */}
                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Total Price</div>
                  {/* We don't need this block as we'll show tax per currency */}
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {orderData.length > 0
                      ? orderData.map((item, index) => {
                          if (PriceHide !== "0") {
                            return (
                              <React.Fragment key={index}>
                                {index > 0 && ", "}
                                <div className="d-flex flex-column" style={{ gap: '2px' }}>
                                  <div style={{ fontWeight: 'bold', color: '#3554D1' }}>
                                    Price Hidden
                                  </div>
                                </div>
                              </React.Fragment>
                            );
                          }

                          const basePrice = parseFloat(item.totalPrice) || 0;
                          
                          // Step 1: Ceiling the base prices
                          const sgdPrice = Math.ceil(basePrice);
                          const usdPrice = Math.ceil(basePrice * usdExchangeRate);
                          const convertedPrice = Math.ceil(basePrice * exchangeRate);
                          
                          // Step 2: Calculate tax amount based on ceiling prices using auth slice tax values
                          const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
                          const usdTaxAmount = Math.ceil((usdPrice * usdTax) / 100);
                          const convertedTaxAmount = Math.ceil((convertedPrice * currentTax) / 100);
                          
                          // Step 3: Calculate grand totals
                          const sgdGrandTotal = sgdPrice + sgdTaxAmount;
                          const usdGrandTotal = usdPrice + usdTaxAmount;
                          const convertedGrandTotal = convertedPrice + convertedTaxAmount;

                          // Always show SGD, then current currency (if not SGD), then USD (if not already shown)
                          const isSgdCurrency = currencyCode === 'SGD';
                          const isUsdCurrency = currencyCode === 'USD' || currencyCode === usdCurrencyCode;

                          return (
                            <React.Fragment key={index}>
                              {index > 0 && ", "}
                              <div>
                                {/* Always show SGD price first */}
                                <div className={isSgdCurrency ? "text-16 fw-500" : "text-14 text-light-1"}>
                                  SGD {sgdGrandTotal.toFixed(2)} <span className="text-12">(incl. {sgdTax}% tax)</span>
                                </div>

                                {/* Show current currency if not SGD */}
                                {!isSgdCurrency && (
                                  <div className="text-16 fw-500 mt-5">
                                    {currencyCode} {convertedGrandTotal.toFixed(2)} <span className="text-13 text-light-1">(incl. {currentTax}% tax)</span>
                                  </div>
                                )}

                                {/* Show USD if not already shown */}
                                {!isUsdCurrency && currencyCode !== usdCurrencyCode && (
                                  <div className="text-14 text-light-1">
                                    {usdCurrencyCode} {usdGrandTotal.toFixed(2)} <span className="text-12">(incl. {usdTax}% tax)</span>
                                  </div>
                                )}
                              </div>
                            </React.Fragment>
                          );
                        })
                      : "Price not available"}
                  </div>
                </div>

                {/* End .col */}
                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Booking Mode</div>
                  <div
                    className="text-15 lh-12 fw-500 text-blue-1 mt-10"
                    style={{ display: "flex", alignItems: "center" }}
                  >
                    {orderData.length > 0
                      ? orderData.map((item, index) => {
                          const type = String(item.mode);
                          return (
                            <React.Fragment key={index}>
                              {index > 0 && ", "}
                              {type === "dmc" ? (
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
                              ) : type === "travClicks" ||
                                type === "travclicks" ? (
                                "Travclicks"
                              ) : (
                                type.charAt(0).toUpperCase() + type.slice(1)
                              )}
                            </React.Fragment>
                          );
                        })
                      : "Mode not available"}
                  </div>
                </div>

                {/* End .col */}
              </div>
            </div>
            {/* order price info */}

            <div className="border-light rounded-8 px-50 py-40 mt-40">
              <h4 className="text-20 fw-500 mb-30">Your Information</h4>
              <div className="row y-gap-10">
                {/* <div className="col-12">
                  <div className="d-flex justify-between ">
                    <div className="text-15 lh-16">Agent Id</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {order.agent_id}
                    </div>
                  </div>
                </div> */}
                {/* End .col */}
                <div className="col-12">
                  <div className="d-flex justify-between  pt-10">
                    <div className="text-15 lh-16">Name</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {orderData.length > 0
                        ? orderData.map((item) => item.fullName).join(", ")
                        : "Name not available"}
                    </div>
                  </div>
                </div>
                {/* End .col */}
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Email</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {orderData.length > 0
                        ? orderData.map((item) => item.email || "Not available").join(", ")
                        : "Email not available"}
                    </div>
                  </div>
                </div>
                {/* End .col */}
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Phone</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {orderData.length > 0
                        ? orderData.map((item) => `${item.countryCode} ${item.phone}`).join(", ")
                        : "Phone not available"}
                    </div>
                  </div>
                </div>
                {/* End .col */}
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Address line 1</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {orderData.length > 0
                        ? orderData.map((item) => item.address1).join(", ")
                        : "Address line 1 not available"}
                    </div>
                  </div>
                </div>
                {/* End .col */}
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Address line 2</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {orderData.length > 0
                        ? orderData.map((item) => item.address2 || "Not available").join(", ")
                        : "Address line 2 not available"}
                    </div>
                  </div>
                </div>
                {/* End .col */}
                {/* <div className="col-12">
                    <div className="d-flex justify-between border-top-light pt-10">
                      <div className="text-15 lh-16">City</div>
                      <div className="text-15 lh-16 fw-500 text-blue-1">
                      {order.data
                        ? JSON.parse(order.data)
                            .map((item) => `${item.fullName ||"Not available"}`)
                            .join(", ")
                        : "Price not available"}
                      </div>
                    </div>
                  </div> */}
                {/* End .col */}
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">State/Province/Region</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {orderData.length > 0
                        ? orderData.map((item) => item.state).join(", ")
                        : "State not available"}
                    </div>
                  </div>
                </div>
                {/* End .col */}
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">ZIP code/Postal code</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {orderData.length > 0
                        ? orderData.map((item) => item.zip).join(", ")
                        : "ZIP code not available"}
                    </div>
                  </div>
                </div>
                {/* End .col */}
                {/* <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Country</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                    {order.data
                      ? JSON.parse(order.data)
                          .map((item) => `${item.fullName}`)
                          .join(", ")
                      : "Price not available"}
                    </div>
                  </div>
                </div> */}
                {/* End .col */}
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Special Requirements</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {orderData.length > 0
                        ? orderData.map((item) => item.specialRequests || "Not available").join(", ")
                        : "Special requirements not available"}
                    </div>
                  </div>
                </div>
                {/* End .col */}
              </div>
              {/* End .row */}
            </div>
            {/* End order information */}
          </div>
        </div>
      </div>
    </>
  );
};

export default OrderSubmittedInfo;
