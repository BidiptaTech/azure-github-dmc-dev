import React from "react";
import { useSelector } from "react-redux";
import { Box, Typography, Card, CardContent, Divider } from "@mui/material";

const PricingSummary = ({ totalPrice }) => {
  // Get tax percentages from Redux
  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);
  
  // Get price mode and currency information from Redux store
  const priceMode = useSelector((state) => state.hotels.searchState.priceMode) || 'dmc';
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  
  // Step 1: Ceiling the base price in all currencies
  const sgdPrice = Math.ceil(totalPrice);
  const usdPrice = Math.ceil(totalPrice * usdExchangeRate);
  const convertedPrice = Math.ceil(totalPrice * (priceMode === "dmc" ? exchangeRate : usdExchangeRate));
  
  // Step 2: Calculate tax amount using percentages from Redux
  const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
  const usdTaxAmount = Math.ceil((usdPrice * usdTax) / 100);
  const currentTaxAmount = Math.ceil((convertedPrice * currentTax) / 100);
  
  // Step 3: Calculate the grand totals
  const sgdGrandTotal = sgdPrice + sgdTaxAmount;
  const usdGrandTotal = usdPrice + usdTaxAmount;
  const convertedGrandTotal = convertedPrice + currentTaxAmount;

  // Check if current tax matches SGD or USD tax to hide respective portions
  const showSgdPortion = currentTax !== sgdTax;
  const showUsdPortion = currentTax !== usdTax;
  
  // Format price with commas for thousands
  const formatPrice = (price) => {
    if (typeof price !== "number") return "0.00";
    return price.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  return (
    <div className="px-30 py-30 border-light rounded-4 mt-30">
      <div className="text-20 fw-500 mb-20">Your price summary</div>
      
      {PriceHide === "0" ? (
        // Show prices when PriceHide is "0"
        <>
          <div className="row y-gap-5 justify-between">
            <div className="col-auto">
              <div className="text-15">Total Price</div>
            </div>
            <div className="col-auto">
              <div className="text-15">
                {currencyCode} {convertedPrice}
                {showUsdPortion && (
                  <div className="text-14 text-light-1">
                    {usdCurrencyCode} {usdPrice}
                  </div>
                )}
                {showSgdPortion && (
                  <div className="text-14 text-light-1">SGD {sgdPrice}</div>
                )}
              </div>
            </div>
          </div>
          {/* End .row */}

          {/* <div className="row y-gap-5 justify-between pt-5">
            <div className="col-auto">
              <div className="text-15">Taxes and fees ({currentTax}%)</div>
            </div>
            <div className="col-auto">
              <div className="text-15">
                {currencyCode} {currentTaxAmount}
                {showUsdPortion && (
                  <div className="text-14 text-light-1">
                    {usdCurrencyCode} {usdTaxAmount} ({usdTax}%)
                  </div>
                )}
                {showSgdPortion && (
                  <div className="text-14 text-light-1">SGD {sgdTaxAmount} ({sgdTax}%)</div>
                )}
              </div>
            </div>
          </div> */}
          {/* End .row */}

          {/* <div className="px-20 py-20 bg-blue-2 rounded-4 mt-20">
            <div className="row y-gap-5 justify-between">
              <div className="col-auto">
                <div className="text-18 lh-13 fw-500">Total Price</div>
              </div>
              <div className="col-auto">
                <div className="text-18 lh-13 fw-500">
                  {currencyCode} {convertedGrandTotal}
                  {showUsdPortion && (
                    <div className="text-14 text-light-1">
                      {usdCurrencyCode} {usdGrandTotal}
                    </div>
                  )}
                  {showSgdPortion && (
                    <div className="text-14 text-light-1">SGD {sgdGrandTotal}</div>
                  )}
                </div>
              </div>
            </div>
          </div> */}
          {/* End .row */}
        </>
      ) : (
        // Show message when PriceHide is not "0"
        <div className="px-20 py-20 bg-blue-2 rounded-4 mt-20">
          <div className="row justify-between">
            <div className="col-auto">
              <div className="text-18 lh-13 fw-500 text-center w-100">
                Price available on request
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default PricingSummary;
