import {
  Skeleton,
  Box,
  FormControl,
  RadioGroup,
  FormControlLabel,
  Radio,
  Typography,
  Button,
  Avatar,
} from "@mui/material";
import { useNavigate } from "react-router-dom";

import { useSelector } from "react-redux";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import PeopleIcon from "@mui/icons-material/People";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "@/slice/dmc/dmcSlice";

const ActivityProperties = ({
  vehicles,
  status,
  onVehicleClick,
  selectedModes,
  setSelectedModes,
  priceMode,
  hasMore,
  isLoadingMore,
}) => {
  const navigate = useNavigate();
  const DmcName = useSelector(selectSelectedDmcCompanyName);
  const DmcLogo = useSelector(selectSelectedDmcLogo);
  const bookingType = useSelector((state) => state.common.bookingType);
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const error = useSelector((state) => state.pickupDrop.error?.error);
  console.log("errrrrror", error);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  console.log("PriceHide", PriceHide, "type ", typeof PriceHide);
  const usdCurrencySymbol = useSelector(
    (state) => state.auth.usdCurrencySymbol
  );
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
console.log("vehicles123", vehicles);


  return (
    <>
      {/* Skeleton Loading State */}
      {status === "loading" &&
        Array.from({ length: 5 }).map((_, index) => (
          <div key={index} className="col-12">
            <div className="border-top-light pt-30">
              <div className="row x-gap-20 y-gap-20">
                {/* Image Placeholder */}
                <div className="col-md-auto">
                  <Skeleton
                    variant="rectangular"
                    width={250}
                    height={200}
                    className="rounded-4"
                  />
                </div>

                {/* Vehicle Details Placeholder */}
                <div className="col-md">
                  <Skeleton variant="text" width="60%" height={30} />
                  <Skeleton variant="text" width="40%" height={20} />
                  <Skeleton variant="text" width="50%" height={20} />
                </div>

                {/* Price Selection Placeholder */}
                <div className="col-md-auto text-right md:text-left">
                  <Skeleton
                    variant="rectangular"
                    width={160}
                    height={130}
                    className="rounded-4"
                  />
                  <Skeleton
                    variant="rectangular"
                    width={160}
                    height={130}
                    className="rounded-4 mt-2"
                  />
                  <Skeleton variant="text" width={100} height={30} />
                  <Skeleton
                    variant="rectangular"
                    width={120}
                    height={40}
                    className="mt-10"
                  />
                </div>
              </div>
            </div>
          </div>
        ))}
      {status === "idle" && (
        <div
          className="no-hotels-message"
          style={{ textAlign: "center", marginTop: "2rem" }}
        >
          <div className="MuiBox-root css-t4lhjn">
            <div className="MuiBox-root css-d0j5jx">
              <img
                src="/icons/travellers.png" // Using travel.png from the icons folder
                alt="Travel Icon"
                className="your-icon-class"
                style={{ width: "200px", height: "200px" }} // Adjust size as needed
              />
            </div>
            <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
              Please Give Pick Up, Drop Off Location and Date of Journey and
              Search...
            </h5>
          </div>
        </div>
      )}
      {status === "succeeded" && vehicles.length === 0 && (
        <div
          className="no-hotels-message"
          style={{ textAlign: "center", marginTop: "2rem" }}
        >
          <div className="MuiBox-root css-t4lhjn">
            <div className="MuiBox-root css-d0j5jx">
            <img
                src="/icons/travellers.png" // Using travel.png from the icons folder
                alt="Travel Icon"
                className="your-icon-class"
                style={{ width: "200px", height: "200px" }} // Adjust size as needed
              />
            </div>
            <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
              No Vehicle Found in Given Location...
            </h5>
          </div>
        </div>
      )}
      {status === "failed" && (
        <div
          className="no-hotels-message"
          style={{ textAlign: "center", marginTop: "2rem" }}
        >
          <div className="MuiBox-root css-t4lhjn">
            <div className="MuiBox-root css-d0j5jx">
            <img
                src="/icons/travellers.png" // Using travel.png from the icons folder
                alt="Travel Icon"
                className="your-icon-class"
                style={{ width: "200px", height: "200px" }} // Adjust size as needed
              />
            </div>
            <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
              {error ||
                "An error occurred while searching for vehicles. Please try again."}
            </h5>
          </div>
        </div>
      )}
      

      {/* Render Vehicles */}
      {status === "succeeded" &&
        vehicles.map((vehicle) => {
          const dmcPrice = vehicle.dmc_sharable_price
            ? parseFloat(vehicle.dmc_sharable_price) * exchangeRate
            : vehicle.dmc_private_price
            ? parseFloat(vehicle.dmc_private_price) * exchangeRate
            : 0;
          const travClicksPrice = vehicle.trav_sharable_price
            ? parseFloat(vehicle.trav_sharable_price) * exchangeRate
            : vehicle.trav_private_price
            ? parseFloat(vehicle.trav_private_price) * exchangeRate
            : 0;
          const usddmcPrice = vehicle.dmc_sharable_price
            ? parseFloat(vehicle.dmc_sharable_price) * usdExchangeRate
            : vehicle.dmc_private_price
            ? parseFloat(vehicle.dmc_private_price) * usdExchangeRate
            : 0;
          const usdtravClicksPrice = vehicle.trav_sharable_price
            ? parseFloat(vehicle.trav_sharable_price) * usdExchangeRate
            : vehicle.trav_private_price
            ? parseFloat(vehicle.trav_private_price) * usdExchangeRate
            : 0;

          // Filter logic based on priceMode
          if (priceMode === "checked" && dmcPrice <= 0) {
            return null; // Skip vehicles with DMC price of 0 when in "checked" mode
          }

          // Filter out vehicles with both prices as 0
          if (
            (dmcPrice === 0 && travClicksPrice === 0) ||
            (bookingType === "enquiry" && dmcPrice === 0)
          ) {
            return null; // Skip this vehicle
          }

          // Determine the default selected mode based on price availability
          let defaultMode = "dmc"; // Default to DMC
          if (dmcPrice <= 0 && travClicksPrice > 0) {
            defaultMode = "travclicks"; // If DMC price is 0 but travClicks price exists, default to travClicks
          }

          // Get current mode for this vehicle (or use default if none exists)
          let currentMode = selectedModes[vehicle.id]?.mode || defaultMode;

          // Force travclicks mode if DMC price is 0 but travClicks price exists
          if (dmcPrice <= 0 && travClicksPrice > 0 && currentMode === "dmc") {
            // If DMC price is 0 and travClicks price exists, but mode is set to DMC,
            // update it to travclicks (this handles existing selections that might be incorrect)
            const dmcId = vehicle.travclicks_dmc_id;
            setSelectedModes(vehicle.id, "travclicks", dmcId);
            currentMode = "travclicks"; // Update current mode to reflect the change
          }

          // If no mode is set for this vehicle yet, initialize with the correct mode
          if (!selectedModes[vehicle.id]) {
            const dmcId =
              defaultMode === "dmc"
                ? vehicle.dmc_id
                : vehicle.travclicks_dmc_id;
            setSelectedModes(vehicle.id, defaultMode, dmcId);
          }

          return (
            <div key={vehicle.id} className="col-12">
              <div className="border-top-light pt-30">
                <div className="row x-gap-20 y-gap-20">
                  {/* Image Section */}
                  <div className="col-md-auto">
                    <div className="cardImage ratio ratio-1:1 w-250 md:w-1/1 rounded-4">
                      <div className="cardImage__content">
                        <img
                          className="rounded-4"
                          src={vehicle.image}
                          alt={vehicle.vehicle_name}
                          style={{
                            width: "100%",
                            height: "200px",
                            objectFit: "cover",
                          }}
                        />
                      </div>
                    </div>
                  </div>

                  {/* Vehicle Details */}
                  <div className="col-md">
                    <h3>{vehicle.vehicle_name}</h3>

                    <div
                      style={{
                        display: "flex",
                        alignItems: "center",
                        marginBottom: "4px",
                      }}
                    >
                      <PeopleIcon
                        style={{ marginRight: "6px", color: "#1976d2" }}
                      />
                      <span>Seating Capacity: {vehicle.seating_capacity}</span>
                    </div>

                    <div
                      style={{
                        display: "flex",
                        alignItems: "center",
                        marginBottom: "4px",
                      }}
                    >
                      <DirectionsCarIcon
                        style={{ marginRight: "6px", color: "#1976d2" }}
                      />
                      <span>Vehicle Type: {vehicle.vehicle_type}</span>
                    </div>

                    <h6 style={{ marginTop: "10px" }}>
                      {vehicle.sharable === 3 && (
                        <>
                          <div
                            style={{ display: "flex", alignItems: "center" }}
                          >
                            <CheckCircleIcon
                              color="success"
                              style={{ marginRight: "6px" }}
                            />
                            <span>Sharable Available</span>
                          </div>
                          <div
                            style={{ display: "flex", alignItems: "center" }}
                          >
                            <CheckCircleIcon
                              color="success"
                              style={{ marginRight: "6px" }}
                            />
                            <span>Private Available</span>
                          </div>
                        </>
                      )}
                      {vehicle.sharable === 2 && (
                        <div style={{ display: "flex", alignItems: "center" }}>
                          <CheckCircleIcon
                            color="success"
                            style={{ marginRight: "6px" }}
                          />
                          <span>Sharable Available</span>
                        </div>
                      )}
                      {vehicle.sharable === 1 && (
                        <div style={{ display: "flex", alignItems: "center" }}>
                          <CheckCircleIcon
                            color="success"
                            style={{ marginRight: "6px" }}
                          />
                          <span>Private Available</span>
                        </div>
                      )}
                    </h6>
                  </div>

                  {/* Pricing and Selection */}
                  <div className="col-md-auto text-right md:text-left">
                    <Box sx={{ mt: 1, fontSize: "14px" }}>
                      <FormControl component="fieldset">
                        <RadioGroup
                          row
                          value={currentMode}
                          onChange={(e) => {
                            const newMode = e.target.value;
                            const dmcId =
                              newMode === "dmc"
                                ? vehicle.dmc_id
                                : vehicle.travclicks_dmc_id;

                            setSelectedModes(vehicle.id, newMode, dmcId);
                          }}
                        >
                          {/* For "checked" mode, only show DMC option if price > 0 */}
                          {priceMode === "checked" && dmcPrice > 0 && (
                            <Box
                              sx={{
                                p: 1,
                                border: "2px solid #ccc",
                                borderRadius: "12px",
                                m: 1,
                                minWidth: "180px",
                                maxWidth: "10px",
                                height: "auto",
                                bgcolor:
                                  currentMode === "dmc" ? "#e6f2ff" : "#fff",
                                transform:
                                  currentMode === "dmc"
                                    ? "scale(1.05)"
                                    : "scale(1)",
                                transition: "transform 0.2s ease-in-out",
                                display: "flex",
                                flexDirection: "column",
                                flexWrap: "wrap",
                                justifyContent: "center",
                                textAlign: "left",
                              }}
                            >
                              <FormControlLabel
                                value="dmc"
                                control={<Radio />}
                                label={
                                  <span
                                    style={{
                                      display: "flex",
                                      alignItems: "center",
                                      fontSize: "14px",
                                    }}
                                  >
                                    {DmcLogo && (
                                      <Avatar
                                        src={DmcLogo}
                                        alt={`${DmcName} Logo`}
                                        sx={{
                                          width: 24,
                                          height: 24,
                                          marginRight: "2px",
                                        }}
                                      />
                                    )}
                                    {`${DmcName}'s Mode`}
                                  </span>
                                }
                              />
                              {PriceHide === "0" && (
                                <>
                                  <Typography variant="body2">
                                    {vehicle.sharable === 3 ? (
                                      <span> Sharable Price:</span>
                                    ) : vehicle.sharable === 1 ? (
                                      <span> Private Price:</span>
                                    ) : vehicle.sharable === 2 ? (
                                      <span> Sharable Price:</span>
                                    ) : null}
                                  </Typography>
                                  <Typography
                                    variant="body2"
                                    sx={{ fontSize: "14px", fontWeight: "700" }}
                                  >
                                    {currencyCode} {Math.ceil(dmcPrice)}
                                  </Typography>
                                  {currencyCode !== "USD" && (
                                    <Typography variant="body2">
                                      {usdCurrencyCode} {Math.ceil(usddmcPrice)}
                                    </Typography>
                                  )}
                                  {currencyCode !== "SGD" && (
                                    <Typography variant="body2">
                                      SGD{" "}
                                      {Math.ceil(vehicle.dmc_sharable_price) ||
                                        Math.ceil(vehicle.dmc_private_price)}
                                    </Typography>
                                  )}
                                </>
                              )}
                            </Box>
                          )}

                          {/* For non-checked or both modes */}
                          {(priceMode === "non-checked" ||
                            priceMode === "both") && (
                            <>
                              {/* Show DMC option only if dmcPrice > 0 */}
                              {dmcPrice > 0 &&
                                (bookingType === "booking" ||
                                  bookingType === "enquiry" ||
                                  bookingType === "null") && (
                                  <Box
                                    sx={{
                                      p: 1,
                                      border: "2px solid #ccc",
                                      borderRadius: "12px",
                                      m: 1,
                                      minWidth: "180px",
                                      maxWidth: "10px",
                                      height: "auto",
                                      bgcolor:
                                        currentMode === "dmc"
                                          ? "#e6f2ff"
                                          : "#fff",
                                      transform:
                                        currentMode === "dmc"
                                          ? "scale(1.05)"
                                          : "scale(1)",
                                      transition: "transform 0.2s ease-in-out",
                                      display: "flex",
                                      flexDirection: "column",
                                      flexWrap: "wrap",
                                      justifyContent: "center",
                                      textAlign: "left",
                                    }}
                                  >
                                    <FormControlLabel
                                      value="dmc"
                                      control={<Radio />}
                                      label={
                                        <span
                                          style={{
                                            display: "flex",
                                            alignItems: "center",
                                            fontSize: "14px",
                                          }}
                                        >
                                          {DmcLogo && (
                                            <Avatar
                                              src={DmcLogo}
                                              alt={`${DmcName} Logo`}
                                              sx={{
                                                width: 24,
                                                height: 24,
                                                marginRight: "2px",
                                              }}
                                            />
                                          )}
                                          {`${DmcName}'s Mode`}
                                        </span>
                                      }
                                    />
                                    {PriceHide === "0" && (
                                      <>
                                        <Typography variant="body2">
                                          {vehicle.sharable === 3 ? (
                                            <span> Sharable Price:</span>
                                          ) : vehicle.sharable === 1 ? (
                                            <span> Private Price:</span>
                                          ) : vehicle.sharable === 2 ? (
                                            <span> Sharable Price:</span>
                                          ) : null}
                                        </Typography>
                                        <Typography
                                          variant="body2"
                                          sx={{
                                            fontSize: "14px",
                                            fontWeight: "700",
                                          }}
                                        >
                                          {currencyCode} {Math.ceil(dmcPrice)}
                                        </Typography>
                                        {currencyCode !== "USD" && (
                                          <Typography variant="body2">
                                            {usdCurrencyCode}{" "}
                                            {Math.ceil(usddmcPrice)}
                                          </Typography>
                                        )}
                                        {currencyCode !== "SGD" && (
                                          <Typography variant="body2">
                                            SGD{" "}
                                            {Math.ceil(
                                              vehicle.dmc_sharable_price
                                            ) ||
                                              Math.ceil(
                                                vehicle.dmc_private_price
                                              )}
                                          </Typography>
                                        )}
                                      </>
                                    )}
                                  </Box>
                                )}

                              {/* Show TravClicks option only if travClicksPrice > 0 */}
                              {travClicksPrice > 0 &&
                                (bookingType === "booking" ||
                                  bookingType === "null") && (
                                  <Box
                                    sx={{
                                      p: 1,
                                      border: "2px solid #ccc",
                                      borderRadius: "12px",
                                      m: 1,
                                      minWidth: "180px",
                                      maxWidth: "10px",
                                      height: "auto",
                                      bgcolor:
                                        currentMode === "travclicks"
                                          ? "#e6f2ff"
                                          : "#fff",
                                      transform:
                                        currentMode === "travclicks"
                                          ? "scale(1.05)"
                                          : "scale(1)",
                                      transition: "transform 0.2s ease-in-out",
                                      display: "flex",
                                      flexDirection: "column",
                                      flexWrap: "wrap",
                                      justifyContent: "center",
                                      textAlign: "left",
                                    }}
                                  >
                                    <FormControlLabel
                                      value="travclicks"
                                      control={<Radio />}
                                      label={
                                        <span style={{ fontSize: "14px" }}>
                                          Travclicks
                                        </span>
                                      }
                                    />
                                    {PriceHide === "0" && (
                                      <>
                                        <Typography variant="body2">
                                          {vehicle.sharable === 3 ? (
                                            <span> Sharable Price:</span>
                                          ) : vehicle.sharable === 1 ? (
                                            <span> Private Price:</span>
                                          ) : vehicle.sharable === 2 ? (
                                            <span> Sharable Price:</span>
                                          ) : null}
                                        </Typography>
                                        <Typography
                                          variant="body2"
                                          sx={{
                                            fontSize: "14px",
                                            fontWeight: "700",
                                          }}
                                        >
                                          {currencyCode}{" "}
                                          {Math.ceil(travClicksPrice)}
                                        </Typography>
                                        {currencyCode !== "USD" && (
                                          <Typography variant="body2">
                                            {usdCurrencyCode}{" "}
                                            {Math.ceil(usdtravClicksPrice)}
                                          </Typography>
                                        )}
                                        {currencyCode !== "SGD" && (
                                          <Typography variant="body2">
                                            SGD{" "}
                                            {Math.ceil(
                                              vehicle.trav_sharable_price
                                            ) ||
                                              Math.ceil(
                                                vehicle.trav_private_price
                                              )}
                                          </Typography>
                                        )}
                                      </>
                                    )}
                                  </Box>
                                )}
                            </>
                          )}
                        </RadioGroup>
                      </FormControl>
                    </Box>

                    {/* Remove any warning message inside the RadioGroup */}
                    {currentMode === "travclicks" && travClicksPrice > 0 && 
                      (bookingType === "booking" || bookingType === "null") && (
                      <Typography variant="caption" sx={{ color: 'red', fontSize: '0.75rem', mt: 1, mb: 1, display: 'block' }}>
                        This marketplace product isn't eligible for DMC enquiry.
                      </Typography>
                    )}

                    <Button
                      onClick={() => onVehicleClick(vehicle, navigate)}
                      variant="contained"
                      color="primary"
                      className="button -md -dark-1 bg-blue-1 text-white mt-20 mb-10"
                    >
                      View Detail
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          );
        })}

      {/* Loading More Indicator */}
      {isLoadingMore && (
        <div className="col-12 text-center mt-30">
          <div className="border-top-light pt-30">
            <div className="row justify-content-center">
              <div className="col-md-auto">
                <div className="d-flex align-items-center justify-content-center">
                  <div className="spinner-border text-primary me-3" role="status">
                    <span className="visually-hidden">Loading...</span>
                  </div>
                  <span className="text-15 fw-500">Loading more vehicles...</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* No More Data Indicator */}
      {!hasMore && vehicles.length > 0 && (
        <div className="col-12 text-center mt-30">
          <div className="border-top-light pt-30">
            <div className="row justify-content-center">
              <div className="col-md-auto">
                <span className="text-15 fw-500 text-muted">No more vehicles to load</span>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default ActivityProperties;
