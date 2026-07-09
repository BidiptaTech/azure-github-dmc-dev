import { useSelector } from "react-redux";

const PricingSummary = ({ totalPrice, Tax }) => {
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);

  return (
    <div className="px-30 py-30 border-light rounded-4 mt-30">
      <div className="text-20 fw-500 mb-20">Your price summary</div>
      <div className="row y-gap-5 justify-between">
        <div className="col-auto">
          <div className="text-15">Price</div>
        </div>
        {/* End col */}
        <div className="col-auto">
          <div className="text-15">
            {currencyCode} {Math.ceil(totalPrice * exchangeRate)}
          </div>
          {currencyCode !== "USD" && (
            <div className="text-15 body-2">
              {usdCurrencyCode} {Math.ceil(totalPrice * usdExchangeRate)}
            </div>
          )}
          {currencyCode !== "SGD" && (
            <div className="text-15 body-2">SGD {Math.ceil(totalPrice)}</div>
          )}
        </div>
        {/* End col */}
      </div>
      {/* End .row */}

      {/* <div className="row y-gap-5 justify-between pt-5">
        <div className="col-auto">
          <div className="text-15">Taxes and fees</div>
        </div>
        <div className="col-auto">
          <div className="text-15 body-2">
            {currencyCode}{" "}
            {Math.ceil(
              ((Math.ceil(totalPrice) * currentTax) / 100) * exchangeRate
            )}
          </div>
          {currencyCode !== "USD" && (
            <div className="text-15 body-2">
              {usdCurrencyCode}{" "}
              {Math.ceil(
                ((Math.ceil(totalPrice) * usdTax) / 100) * usdExchangeRate
              )}
            </div>
          )}
          {currencyCode !== "SGD" && (
            <div className="text-15 body-2">
              SGD{" "}
              {Math.ceil(
                Math.ceil(totalPrice) + (Math.ceil(totalPrice) * sgdTax) / 100
              )}
            </div>
          )}
        </div>
      </div> */}
      {/* End .row */}

      {/* <div className="row y-gap-5 justify-between pt-5">
        <div className="col-auto">
          <div className="text-15">Booking fees</div>
        </div>
        <div className="col-auto">
          <div className="text-15">FREE</div>
        </div>
      </div> */}
      {/* End .row */}

      {/* <div className="px-20 py-20 bg-blue-2 rounded-4 mt-20">
        <div className="row y-gap-5 justify-between">
          <div className="col-auto">
            <div className="text-18 lh-13 fw-500">Price</div>
          </div>
          <div className="col-auto">
            <div
              className="text-16"
              style={{ fontSize: "16px", fontWeight: "700" }}
            >
              {currencyCode}{" "}
              {Math.ceil(
                (Math.ceil(totalPrice) +
                  (Math.ceil(totalPrice) * currentTax) / 100) *
                  exchangeRate
              )}
            </div>
            {currencyCode !== "USD" && (
              <div className="text-16">
                {usdCurrencyCode}{" "}
                {Math.ceil(
                  (Math.ceil(totalPrice) +
                    (Math.ceil(totalPrice) * usdTax) / 100) *
                    usdExchangeRate
                )}
              </div>
            )}
            {currencyCode !== "SGD" && (
              <div className="text-16">
                SGD{" "}
                {Math.ceil(
                  Math.ceil(totalPrice) + (Math.ceil(totalPrice) * sgdTax) / 100
                )}
              </div>
            )}
          </div>
        </div>
      </div> */}
      {/* End .row */}
    </div>
    // End px-30
  );
};

export default PricingSummary;
