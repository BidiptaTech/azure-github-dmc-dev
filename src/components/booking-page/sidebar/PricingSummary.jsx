import { useSelector } from "react-redux";

const PricingSummary = ({ totalPrice, rooms }) => {
  // Get tax percentages from Redux
  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);
  
  // Get currency information from Redux store
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  
  // Get price mode from state
  const priceMode = useSelector((state) => state.hotels.searchState.priceMode) || 'dmc';
  
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  
  // Step 1: Ceiling the base price in all currencies
  const sgdPrice = Math.ceil(Number(totalPrice) || 0);
  const usdPrice = Math.ceil(sgdPrice * usdExchangeRate);
  const convertedPrice = Math.ceil(sgdPrice * (priceMode === "dmc" ? exchangeRate : usdExchangeRate));
  
  // Step 2: Calculate tax amount using percentages from Redux (COMMENTED OUT)
  // const sgdTaxAmount = Math.ceil((sgdPrice * sgdTax) / 100);
  // const usdTaxAmount = Math.ceil((usdPrice * usdTax) / 100);
  // const currentTaxAmount = Math.ceil((convertedPrice * currentTax) / 100);
  
  // Step 3: Calculate the grand totals (WITHOUT TAX)
  const sgdGrandTotal = sgdPrice; // + sgdTaxAmount;
  const usdGrandTotal = usdPrice; // + usdTaxAmount;
  const convertedGrandTotal = convertedPrice; // + currentTaxAmount;

  // Format price with commas for thousands
  const formatPrice = (price) => {
    if (typeof price !== 'number') return '0.00';
    
    return price.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
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
              <div className="text-15">Price</div>
            </div>
            <div className="col-auto text-right">
              <div className="text-15 fw-500">
                {currencyCode} {formatPrice(convertedPrice)}
              </div>
              <div className="text-14 text-light-1">
                {usdCurrencyCode} {formatPrice(usdPrice)}
              </div>
              <div className="text-14 text-light-1">
                SGD {formatPrice(sgdPrice)}
              </div>
            </div>
          </div>

          {/* <div className="row y-gap-5 justify-between pt-5">
            <div className="col-auto">
              <div className="text-15">Taxes and fees ({currentTax}%)</div>
            </div>
            <div className="col-auto text-right">
              <div className="text-15 fw-500">
                {currencyCode} {formatPrice(currentTaxAmount)}
              </div>
              <div className="text-14 text-light-1">
                {usdCurrencyCode} {formatPrice(usdTaxAmount)} ({usdTax}%)
              </div>
              <div className="text-14 text-light-1">
                SGD {formatPrice(sgdTaxAmount)} ({sgdTax}%)
              </div>
            </div>
          </div> */}

          <div className="py-20 bg-blue-2 rounded-4 mt-20">
            <div className="row y-gap-5 justify-between">
              <div className="col-auto">
                <div className="text-18 lh-13 fw-500 text-right">Total Price</div>
              </div>
              <div className="col-auto">
                <div className="text-18 lh-13 fw-500 text-right">
                  {currencyCode} {formatPrice(convertedGrandTotal)}
                </div>
                <div className="text-14 lh-13 text-light-1 text-right">
                  {usdCurrencyCode} {formatPrice(usdGrandTotal)}
                </div>
                <div className="text-14 lh-13 text-light-1 text-right">
                  SGD {formatPrice(sgdGrandTotal)}
                </div>
              </div>
            </div>
          </div>
        </>
      ) : (
        // Show message when PriceHide is not "0"
        <div className="py-20 bg-blue-2 rounded-4 mt-20">
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
