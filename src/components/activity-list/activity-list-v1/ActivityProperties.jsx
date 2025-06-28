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
// import { Link } from "react-router-dom";
// import {
//   setSelectedGuide,
//   selectGuidesStatus,
//   selectGuidesError,
//   SelectedGuide,
//   selectSearchText,
//   selectFilters,
//   selectSortBy,
// } from "@/slice/tourguide/guideslice";
import Pagination from "../common/Pagination"; // Import your pagination component
import { useEffect } from "react";

const ActivityProperties = ({
  guides,
  searchText,
  filters,
  sortBy,
  status,
  error,
  currentPage,
  itemsPerPage,
  onPageChange,
  onGuideClick,
  selectedModes,
  setSelectedModes,
  priceMode,
}) => {
  console.log("guidesabc", guides);
  const navigate = useNavigate();
  console.log("status", status);
  const DmcName = useSelector((state) => state.auth.DmcName);
  const DmcLogo = useSelector((state) => state.auth.DmcLogo);
  const bookingType = useSelector((state) => state.common.bookingType);
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  console.log("currencySymbol", currencySymbol);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  const currencyCode = useSelector((state) => state.auth.currencyCode);
  console.log("currencyCode", currencyCode);

  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  console.log("exchangeRate", exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector(
    (state) => state.auth.usdCurrencySymbol
  );
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  // const searchText = useSelector(selectSearchText);
  // const guides = useSelector(SelectedGuide);
  // const filters = useSelector(selectFilters);
  // const sortBy = useSelector(selectSortBy);
  // const status = useSelector(selectGuidesStatus);
  // const error = useSelector(selectGuidesError);

  // const [currentPage, setCurrentPage] = useState(1);
  // const itemsPerPage = 5; // Adjust as needed

  // useEffect(() => {
  //   if (status === "idle") {
  //     dispatch(fetchGuides());
  //   }
  // }, [dispatch, status]);

  // Apply filters and sorting
  // const sortedGuides = [...guides]
  //   .filter((guide) => {
  //     const matchesCategory =
  //       filters.category === "All" || guide.category === filters.category;
  //     const matchesPrice =
  //       (!filters.priceRange?.min ||
  //         parseFloat(guide.day_rate || 0) >= filters.priceRange.min) &&
  //       (!filters.priceRange?.max ||
  //         parseFloat(guide.day_rate || 0) <= filters.priceRange.max);
  //     const matchesRating =
  //       !filters.rating || parseFloat(guide.rating || 0) >= filters.rating;
  //     return matchesCategory && matchesPrice && matchesRating;
  //   })
  //   .sort((a, b) => {
  //     const aMatchesSearchText = a.guide_name
  //       ?.toLowerCase()
  //       .includes(searchText?.toLowerCase());
  //     const bMatchesSearchText = b.guide_name
  //       ?.toLowerCase()
  //       .includes(searchText?.toLowerCase());

  //     if (aMatchesSearchText && !bMatchesSearchText) return -1;
  //     if (!aMatchesSearchText && bMatchesSearchText) return 1;

  //     switch (sortBy) {
  //       case "ratingHighToLow":
  //         return b.rating - a.rating;
  //       case "ratingLowToHigh":
  //         return a.rating - b.rating;
  //       case "priceLowToHigh":
  //         return parseFloat(a.day_rate || 0) - parseFloat(b.day_rate || 0);
  //       case "priceHighToLow":
  //         return parseFloat(b.day_rate || 0) - parseFloat(a.day_rate || 0);
  //       default:
  //         return a.guide_name.localeCompare(b.guide_name);
  //     }
  //   });

  // Pagination logic
  const totalItems = guides.length;
  const totalPages = Math.ceil(totalItems / itemsPerPage);
  const paginatedGuides = guides.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );
  console.log("pagegui", paginatedGuides);

  // const handlePageChange = (page) => {
  //   setCurrentPage(page);
  // };

  // const handleGuideClick = (guide) => {
  //   dispatch(setSelectedGuide(guide));
  //   navigate(`/activity-single/${guide.id}`, { state: { guide } });
  // };

  return (
    <>
      {/* Loading Skeletons */}
      {status === "loading" &&
        Array.from({ length: 5 }).map((_, index) => (
          <div className="col-12" key={index}>
            <div className="border-top-light pt-15">
              <div className="row x-gap-20 y-gap-20">
                <div className="col-md-auto">
                  <Skeleton
                    variant="rectangular"
                    width={250}
                    height={250}
                    className="rounded-4"
                  />
                </div>
                <div className="col-md">
                  <Skeleton width="60%" height={20} />
                  <Skeleton width="80%" height={25} />
                  <Skeleton width="50%" height={20} />
                  <Skeleton width="40%" height={20} className="mt-20" />
                </div>
                <div className="col-md-auto text-right md:text-left">
                  <Skeleton width="80px" height={25} />
                  <Skeleton width="100px" height={30} className="mt-10" />
                  <Skeleton width="120px" height={40} className="mt-20" />
                </div>
              </div>
            </div>
          </div>
        ))}

      {/* Error Handling */}
      {status === "failed" && (
        <div className="alert alert-danger mt-2">{error}</div>
      )}
      
      {status === "idle" && !guides?.length && (
        <div
          className="no-hotels-message"
          style={{ textAlign: "center", marginTop: "2rem" }}
        >
          <div className="MuiBox-root css-t4lhjn">
            <div className="MuiBox-root css-d0j5jx">
              <img
                src="/icons/tour-guide.png"
                alt="Tour Guide Icon"
                className="your-icon-class"
                style={{ width: "100px", height: "100px" }}
              />
            </div>
            <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
              Please select a valid location from the dropdown
            </h5>
            <p className="MuiTypography-root MuiTypography-body1 css-1q7m9c1-MuiTypography-root">
              Select your destination and travel date to find available guides.
            </p>
          </div>
        </div>
      )}

      {/* Render Guides */}
      {status === "succeeded" &&
        paginatedGuides.map((guide) => {
          // const selectedMode =
          //   selectedModes[guide.id] ||
          //   (priceMode === "marketplace" ? "travclicks" : "dmc");
          // const price =
          //   selectedMode === "dmc"
          //     ? guide.dmc_day_rate * exchangeRate
          //     : guide.travClick_day_rate * exchangeRate;
          const dmcPrice = guide.dmc_day_rate
            ? parseFloat(guide.dmc_day_rate) * exchangeRate
            : 0;
          console.log("dmcPrice", dmcPrice);
          const travClicksPrice = guide.travClicks_day_rate
            ? parseFloat(guide.travClicks_day_rate) * exchangeRate
            : 0;
          console.log("travClicksPrice", travClicksPrice);
          const usddmcPrice = guide.dmc_day_rate
            ? parseFloat(guide.dmc_day_rate) * usdExchangeRate
            : 0;
          const usdtravClicksPrice = guide.travClicks_day_rate
            ? parseFloat(guide.travClicks_day_rate) * usdExchangeRate
            : 0;
          // Filter logic based on priceMode
          if (priceMode === "checked" && dmcPrice <= 0) {
            return null; // Skip vehicles with DMC price of 0 when in "checked" mode
          }

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
          let currentMode = selectedModes[guide.id]?.mode || defaultMode;
          console.log("currentMode", currentMode);
          // Force travclicks mode if DMC price is 0 but travClicks price exists
          if (dmcPrice <= 0 && travClicksPrice > 0 && currentMode === "dmc") {
            // If DMC price is 0 and travClicks price exists, but mode is set to DMC,
            // update it to travclicks (this handles existing selections that might be incorrect)
            const dmcId = guide.travclicks_dmc_id;
            setSelectedModes(guide.id, "travclicks", dmcId);
            currentMode = "travclicks"; // Update current mode to reflect the change
          }

          if (!selectedModes[guide.id]) {
            const dmcId =
              defaultMode === "dmc" ? guide.dmc_id : guide.travclicks_dmc_id;
            setSelectedModes(guide.id, defaultMode, dmcId);
          }

          return (
            <div key={guide.id} className="col-12" data-aos="fade">
              <div className="border-top-light pt-30">
                <div className="row x-gap-20 y-gap-20">
                  <div className="col-md-auto">
                    <div className="cardImage ratio ratio-1:1 w-250 md:w-1/1 rounded-4">
                      <div className="cardImage__content">
                        <img
                          className="rounded-4"
                          src={guide.guide_image}
                          alt={guide.guide_name}
                          style={{
                            width: "100%",
                            height: "200px",
                            objectFit: "cover",
                          }}
                        />
                      </div>
                    </div>
                  </div>
                  <div className="col-md">
                    <h3>{guide.guide_name}</h3>
                    {/* <p
                      dangerouslySetInnerHTML={{ __html: guide.description }}
                    /> */}
                    <div>Taking safety measures</div>
                    <div className="text-green-2">Free cancellation</div>
                  </div>
                  <div className="col-md-auto text-right md:text-left">
                    <div className="d-flex items-center">
                      <i className="icon-star text-10 text-yellow-1" />
                      <span className="ml-5">{guide.ratings}</span>
                      <span className="text-light-1">
                        {guide.numberOfReviews} reviews
                      </span>
                    </div>
                    <Box sx={{ mt: 1, fontSize: "14px" }}>
                      <FormControl component="fieldset">
                        <RadioGroup
                          row
                          value={currentMode}
                          onChange={(e) => {
                            const newMode = e.target.value;
                            const dmcId =
                              newMode === "dmc"
                                ? guide.dmc_id
                                : guide.travclicks_dmc_id;

                            setSelectedModes(guide.id, newMode, dmcId);
                          }}
                        >
                          {priceMode === "checked" && dmcPrice > 0 && (
                            <Box
                              sx={{
                                p: 1,
                                border: "2px solid #ccc",
                                borderRadius: "12px",
                                m: 1,
                                //width: "160px",
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
                              {/* <Typography variant="body2">Price:</Typography> */}
                              {PriceHide === "0" && (
                                <>
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
                                      SGD {Math.ceil(guide.dmc_day_rate)}
                                    </Typography>
                                  )}
                                </>
                              )}
                            </Box>
                          )}

                          {(priceMode === "non-checked" ||
                            priceMode === "both") && (
                            <>
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
                                      //width: "160px",
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
                                    {/* <Typography variant="body2">
                                      Price:
                                    </Typography> */}
                                    {PriceHide === "0" && (
                                      <>
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
                                            SGD {Math.ceil(guide.dmc_day_rate)}
                                          </Typography>
                                        )}
                                      </>
                                    )}
                                  </Box>
                                )}
                              {travClicksPrice > 0 &&
                                (bookingType === "booking" ||
                                  bookingType === "null") && (
                                  <Box
                                    sx={{
                                      p: 1,
                                      border: "2px solid #ccc",
                                      borderRadius: "12px",
                                      m: 1,
                                      //width: "160px",
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
                                    {/* <Typography variant="body2">
                                      Price:
                                    </Typography> */}
                                    {PriceHide === "0" && (
                                      <>
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
                                              guide.travClicks_day_rate
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

                    {/* Add warning message here, only when Travclicks mode is selected */}
                    {currentMode === "travclicks" &&
                      travClicksPrice > 0 &&
                      (bookingType === "booking" || bookingType === "null") && (
                        <Typography
                          variant="caption"
                          sx={{
                            color: "red",
                            fontSize: "0.75rem",
                            mt: 1,
                            mb: 1,
                            display: "block",
                          }}
                        >
                          This marketplace product isn't eligible for DMC
                          enquiry.
                        </Typography>
                      )}

                    <Button
                      onClick={() => onGuideClick(guide, navigate)}
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

      {/* Pagination Component */}
      {totalPages > 1 && status === "succeeded" && (
        <Pagination
          currentPage={currentPage}
          totalPages={totalPages}
          onPageChange={onPageChange}
        />
      )}
    </>
  );
};

export default ActivityProperties;
