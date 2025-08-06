import { useSelector } from "react-redux";

const PricingSummary = ({ selectedRooms, totalPrice, priceMode }) => {
  // Get currency information from Redux store
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);

  // Calculate converted prices
  const convertedTotalPrice = totalPrice * (priceMode === "dmc" ? exchangeRate : usdExchangeRate);
  const usdTotalPrice = totalPrice * usdExchangeRate;

  return (
    <div className="px-30 py-30 border-light rounded-4 mt-30">
      <div className="text-20 fw-500 mb-20">Your booking summary</div>
      
      {selectedRooms.map((room, index) => {
        // Calculate room price with exchange rates
        const roomPrice = priceMode === "dmc" 
          ? parseFloat(room.dmc_price || 0) 
          : parseFloat(room.travclicks_price || 0);
        
        const convertedRoomPrice = roomPrice * (priceMode === "dmc" ? exchangeRate : usdExchangeRate);
        const usdRoomPrice = roomPrice * usdExchangeRate;
        
        return (
          <div key={index} className="row y-gap-5 justify-between">
            <div className="col-auto">
              <div className="text-15">{room.room_name}</div>
            </div>
            <div className="col-auto">
              <div className="text-15">
                {currencyCode} {currencySymbol}{convertedRoomPrice.toFixed(2)}
              </div>
              <div className="text-14 text-light-1">
                SGD S${roomPrice.toFixed(2)}
              </div>
              <div className="text-14 text-light-1">
                {usdCurrencyCode} {usdCurrencySymbol}{usdRoomPrice.toFixed(2)}
              </div>
            </div>
          </div>
        );
      })}
      
      <div className="row y-gap-5 justify-between pt-5">
        <div className="col-auto">
          <div className="text-15">Taxes and fees</div>
        </div>
        <div className="col-auto">
          <div className="text-15">Included</div>
        </div>
      </div>
      
      <div className="row y-gap-5 justify-between pt-10">
        <div className="col-auto">
          <div className="text-18 fw-500">Total</div>
        </div>
        <div className="col-auto">
          <div className="text-18 fw-500">
            {currencyCode} {currencySymbol}{convertedTotalPrice.toFixed(2)}
          </div>
          <div className="text-15">
            SGD S${totalPrice.toFixed(2)}
          </div>
          <div className="text-15">
            {usdCurrencyCode} {usdCurrencySymbol}{usdTotalPrice.toFixed(2)}
          </div>
        </div>
      </div>
    </div>
  );
};

export default PricingSummary; 