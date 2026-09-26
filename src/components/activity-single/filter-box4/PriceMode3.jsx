import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useLocation } from "react-router-dom";

const PriceMode = ({ pricemode, setpricemode }) => {
  const location = useLocation();
  // const dispatch = useDispatch();
  const checkoutVehicle = useSelector((state) => state.localtour.checkoutVehicle);
  const vehicles = location.state?.vehicles || checkoutVehicle;
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  // State to hold the selected price mode
  const [selectedPriceMode, setSelectedPriceMode] = useState("");

  // Effect to set the default price mode based on vehicles.sharable
  useEffect(() => {
    if (!vehicles) return;
    console.log("vehicles.sharable:", vehicles.sharable);
    if (vehicles.sharable === 3) {
      setSelectedPriceMode("Sharable");
      setpricemode("Sharable"); // Set default price mode
    } else if (vehicles.sharable === 2) {
      setSelectedPriceMode("Sharable");
      setpricemode("Sharable"); // Set default price mode
    } else if (vehicles.sharable === 1) {
      setSelectedPriceMode("Private");
      setpricemode("Private"); // Set default price mode
    }
  }, [vehicles, setpricemode]);

  const handleChange = (event) => {
    console.log("Selected option:", event.target.value);
    setSelectedPriceMode(event.target.value);
    setpricemode(event.target.value); // Update the parent state
  };

  console.log("Vehicles:", vehicles);
  console.log("Selected Price Mode:", selectedPriceMode);
  console.log("Price Mode:", pricemode);

  if (!vehicles) return null;

  return (
    <div>
      <select value={selectedPriceMode} onChange={handleChange}>
        {vehicles.sharable === 3 && (
          <>
            <option value="Sharable">
              Sharable
              {PriceHide === "0" && (
                <>
                  -{currencyCode}{" "}
                  {Math.ceil(vehicles.prices.day_sharable_price * exchangeRate)}
                </>
              )}
            </option>
            <option value="Private">
              Private
              {PriceHide === "0" && (
                <>
                  -{currencyCode}{" "}
                  {Math.ceil(vehicles.prices.day_private_price * exchangeRate)}
                </>
              )}
            </option>
          </>
        )}
        {vehicles.sharable === 2 && (
          <option value="Sharable">
            Sharable
            {PriceHide === "0" && (
              <>
                -{currencyCode}{" "}
                {Math.ceil(vehicles.prices.day_sharable_price * exchangeRate)}
              </>
            )}
          </option>
        )}
        {vehicles.sharable === 1 && (
          <option value="Private">
            Private
            {PriceHide === "0" && (
              <>
                -{currencyCode}{" "}
                {Math.ceil(vehicles.prices.day_private_price * exchangeRate)}
              </>
            )}
          </option>
        )}
      </select>
    </div>
  );
};

export default PriceMode;
