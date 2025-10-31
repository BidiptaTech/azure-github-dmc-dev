
import { useSelector } from "react-redux";
import dayjs from "dayjs";
import { Avatar } from "@mui/material";
import { useNavigate } from "react-router-dom";
import { useDispatch } from "react-redux";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";
import { resetVehicles } from "@/slice/port/pickupDropSlice";
import { resetVehicles1 } from "@/slice/localtour/Localslice";
import { resetguide } from "@/slice/tourguide/guideslice";

const OrderSubmittedInfo5 = () => {
  const type = useSelector((state) => state.tourguide.type);
  const navigate = useNavigate();
  const bookingResponse = useSelector((state) => state.pickupDrop.response);
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const currentTax = useSelector((state) => state.auth.currentTax);
  const sgdTax = useSelector((state) => state.auth.sgdTax);
  const usdTax = useSelector((state) => state.auth.usdTax);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  const dispatch = useDispatch();
  const DmcName = useSelector((state) => state.auth.DmcName);
  const DmcLogo = useSelector((state) => state.auth.DmcLogo);
  if (!bookingResponse) {
    return <p>Loading booking details...</p>;
  }

  const { message, order, service } = bookingResponse;
  console.log("order", order);
  const data = order?.data
    ? Array.isArray(order.data)
      ? order.data
      : [order.data]
    : [];
  console.log("data", data);

  //const parsedData = data.length > 0 ? JSON.parse(data[0]) : [];
  // console.log("parsedData", parsedData);

  const getPrice = (type, data) => {
    if (type === "guide") {
      return parseFloat(data?.[0]?.totalPrice) || 0;
    } else if (type === "travelhourly") {
      return parseFloat(data?.[0]?.totalPrice) || 0;
    } else {
      return parseFloat(data?.[0]?.totalPrice) || 0;
    }
  };

  const price = getPrice(type, data);

  // Calculate the total price based on available values
  // const calculatedTotalPrice = Math.ceil(
  //   (Math.ceil(price) + (Math.ceil(price) * currentTax) / 100) * exchangeRate
  // );
  // const calculatedTotalPriceInUSD = Math.ceil(
  //   (Math.ceil(price) + (Math.ceil(price) * usdTax) / 100) * usdExchangeRate
  // );
  const calculatedTotalPrice = Math.ceil(
    (Math.ceil(price) * exchangeRate)
  );
  const calculatedTotalPriceInUSD = Math.ceil(
    (Math.ceil(price) * usdExchangeRate)
  );

  const formatMessage = (message) => {
    if (!message) return "Booking Successful";
    if (message.startsWith("Entry_port"))
      return message.replace("Entry_port", "Entry Port");
    if (message.startsWith("Exit_port"))
      return message.replace("Exit_port", "Exit Port");
    if (message.startsWith("Travel_point"))
      return message.replace("Travel_point", "LocalTour");
    if (message.startsWith("Travel_hourly"))
      return message.replace("Travel_hourly", "LocalTour");
    if (message.startsWith("Local_transport"))
      return message.replace("Local_transport", "Local Transfer");
    return message;
  };

  const handleClick = () => {
    if (type === "entryport" || type === "exitport") {
      dispatch(resetVehicles({}));
      navigate("/dashboard/db-dashboard/pickupdrop");
    } else if (
      type === "travelpoint" ||
      type === "travelhourly" ||
      type === "travelpointzone"
    ) {
      dispatch(resetVehicles1({}));
      navigate("/dashboard/db-dashboard/localtransfer");
    } else if (type === "guide") {
      dispatch(resetguide({}));
      navigate("/dashboard/db-dashboard/tourguide");
    }
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
                {formatMessage(message)}
              </div>
              <div className="text-15 flex-center text-light-1 mt-30">
                <button
                  className="button px-15 py-8 bg-blue-1 text-white rounded position-absolute justify-content-center"
                  style={{
                    zIndex: "10",
                    minHeight: "40px",
                    animation: "blink 1s infinite",
                    WebkitAnimation: "blink 1s infinite",
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

            <div className="border-type-1 rounded-8 px-50 py-35 mt-40">
              <div className="row">
                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Tour ID </div>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {order?.tour_id}
                  </div>
                </div>
                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Date</div>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {data.length > 0
                      ? dayjs(data[0].bookingDate).format("ddd, D MMM'YY")
                      : "Date not available"}
                  </div>
                </div>

                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Total</div>
                  {PriceHide === "0" ? (
                    <>
                      <div
                        className="text-15 lh-12 fw-500 text-blue-1 mt-10"
                        style={{ fontWeight: "700" }}
                      >
                        {data.length > 0
                          ? currencyCode + " " + calculatedTotalPrice
                          : "No price available"}
                      </div>
                      {currencyCode !== "USD" && (
                        <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                          {usdCurrencyCode + " " + calculatedTotalPriceInUSD}
                        </div>
                      )}
                      {currencyCode !== "SGD" && (
                        <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                          SGD{" "}
                          {Math.ceil(
                            Math.ceil(price) + (Math.ceil(price) * sgdTax) / 100
                          )}
                        </div>
                      )}
                    </>
                  ) : (
                    <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                      Price Hidden
                    </div>
                  )}
                </div>

                <div className="col-lg-3 col-md-6">
                  <div className="text-15 lh-12">Booking Mode</div>
                  <div className="text-15 lh-12 fw-500 text-blue-1 mt-10">
                    {data?.[0]?.Mode === "dmc" ? (
                      <>
                        {/* <img
                          src={DmcLogo}
                          alt="Dmc Logo"
                          className="size-20 rounded-full"
                        /> */}
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
                          {DmcName}
                        </span>
                      </>
                    ) : (
                      "TravClicks"
                    )}
                  </div>
                </div>
              </div>
            </div>

            <div className="border-light rounded-8 px-50 py-40 mt-40">
              <h4 className="text-20 fw-500 mb-30">Your Information</h4>
              <div className="row y-gap-10">
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Customer name</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.fullName ||
                          data[0]?.userInfo?.fullName ||
                          "FullName not available"
                        : "FullName not available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Email</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.email ||
                          data[0]?.userInfo?.email ||
                          "No email available"
                        : "No email available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Phone</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.phone ||
                          data[0]?.userInfo?.phone ||
                          "Phone Number not available"
                        : "Phone Number not available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Address line 1</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.address1 ||
                          data[0]?.userInfo?.address1 ||
                          "Address not available"
                        : "Address not available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Address line 2</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.address2 ||
                          data[0]?.userInfo?.address2 ||
                          "Address not available"
                        : "Address not available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">City</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.city ||
                          data[0]?.userInfo?.city ||
                          "City not available"
                        : "City not available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">State/Province/Region</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.state ||
                          data[0]?.userInfo?.state ||
                          "State not available"
                        : "State not available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">ZIP code/Postal code</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.zip || data[0]?.userInfo?.zip
                        : "Pincode not available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Country</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.country ||
                          data[0]?.userInfo?.country ||
                          "Country not available"
                        : "Country not available"}
                    </div>
                  </div>
                </div>
                <div className="col-12">
                  <div className="d-flex justify-between border-top-light pt-10">
                    <div className="text-15 lh-16">Special Requirements</div>
                    <div className="text-15 lh-16 fw-500 text-blue-1">
                      {data.length > 0
                        ? data[0]?.specialRequests ||
                          data[0]?.userInfo?.specialRequests ||
                          "Not available"
                        : "Not available"}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default OrderSubmittedInfo5;
