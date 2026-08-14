import CallToActions from "@/components/common/CallToActions";
import TopHeaderFilter from "@/components/activity-list/activity-list-v2/TopHeaderFilter";
import ActivityProperties from "@/components/activity-list/activity-list-v2/ActivityProperties";
import Pagination from "@/components/activity-list/common/Pagination";
import Sidebar from "@/components/activity-list/activity-list-v2/Sidebar";
import { useState, useEffect, useMemo } from "react";
import { useSelector, useDispatch } from "react-redux";
import { FaAngleDown, FaAngleUp } from "react-icons/fa";
import MetaComponent from "@/components/common/MetaComponent";
import {
  setSelectedVehicle,
  setSelectedVehicle1,
  setMode,
  fetchVehicleDetails,
  fetchVehicles,
  fetchZoneVehicles,
} from "@/slice/port/pickupDropSlice";
import MainFilterSearchBox from "@/components/activity-list/activity-list-v2/MainFilterSearchBox";
import MainFilterSearchBox2 from "@/components/activity-list/activity-list-v2/MainFilterSearchBox2";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";

const metadata = {
  title:
    "Entry/Exit Port Vehicle List || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const ActivityListPage2 = () => {
  console.log("ActivityListPage2");
  const dispatch = useDispatch();
  const selectionType = useSelector((state) => state.pickupDrop.selectionType);
  const vehicles = selectionType === "Entry Port" ? useSelector((state) => state.pickupDrop.vehicles) : useSelector((state) => state.pickupDrop.vehicles1);
  const status = useSelector((state) => state.pickupDrop.status);

  // Get zone_on from Redux state and convert to a number
  const rawZoneOn = useSelector((state) => state.auth.zone_on);
  const zone_on = rawZoneOn !== null ? Number(rawZoneOn) : null;

  // Add debugging with useEffect to ensure it runs after render
  // useEffect(() => {
  //   console.log("COMPONENT MOUNTED - ActivityListPage2");
  //   console.log("zone_onnav INSIDE useEffect", zone_on, "| Type:", typeof zone_on);
    
  //   // Force a console log even if something's wrong with the component
  //   setTimeout(() => {
  //     console.log("DELAYED LOG - zone_on:", zone_on);
  //     console.log("DELAYED LOG - Is component mounted? YES");
  //   }, 2000);
  // }, []); // Empty dependency array so it only runs once on mount

  console.log("zone_onnav", zone_on, "| Type:", typeof zone_on);
  console.log("Zone_on JSON:", JSON.stringify(zone_on));
  const Location = useSelector((state) => state.bookings.searchLocation);
  console.log("countryNames", Location);

  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 5; // Adjust as needed
  const [sortOrder, setSortOrder] = useState("asc");
  const [sortedVehicles, setSortedVehicles] = useState([]);
  const [priceRange, setPriceRange] = useState({ min: 0, max: 500 });
  const [hasMore, setHasMore] = useState(true);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const mode = useSelector((state) => state.pickupDrop.mode);
  const priceMode = useSelector((state) => state.pickupDrop.pricemode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const bookingType = useSelector((state) => state.common.bookingType);
  const portZoneType = useSelector((state) => state.pickupDrop.portZoneType);
  console.log("m1", priceMode);
  // const [selectedMode, setSelectedMode] = useState("dmc"); // Default to both modes
  // console.log(selectedMode, "selectedMode");
  // const handleModeChange1 = (mode) => {
  //   setSelectedMode(mode);
  //   dispatch(setPriceMode(mode)); // Dispatch the selected mode to Redux
  // Add your filtering logic here based on the selected mode
  // };

  // const [selectedModes, setSelectedModes] = useState(() => {
  //   return vehicles.reduce((acc, vehicle) => {
  //     acc[vehicle.id] = "dmc";
  //     return acc;
  //   }, {});
  // });

  useEffect(() => {
    const defaultMode =
      priceMode === "checked" || priceMode === "non-checked"
        ? "dmc"
        : "travclicks";
    const initialModes = vehicles.reduce((acc, vehicle) => {
      acc[vehicle.id] = { mode: defaultMode, dmcId: vehicle.dmc_id };
      return acc;
    }, {});

    dispatch(setMode(initialModes)); // Ensure Redux is updated
  }, [vehicles, priceMode, dispatch]);

  // Function to get the correct price field based on mode
  const getPriceField = (vehicle) => {
    const vehicleMode = mode[vehicle.id]?.mode || "dmc"; // Default mode
    return vehicleMode === "dmc"
      ? vehicle.dmc_sharable_price
        ? parseFloat(vehicle.dmc_sharable_price)
        : vehicle.dmc_private_price
        ? parseFloat(vehicle.dmc_private_price)
        : 0
      : vehicle.trav_sharable_price
      ? parseFloat(vehicle.trav_sharable_price)
      : vehicle.trav_private_price
      ? parseFloat(vehicle.trav_private_price)
      : 0;
  };

  const hourlyPrices = useMemo(
    () => vehicles.map((vehicle) => getPriceField(vehicle)),
    [vehicles, mode] // ✅ Use Redux mode instead of selectedModes
  );

  console.log("Hourly Prices:", hourlyPrices);

  useEffect(() => {
    setSortedVehicles([...vehicles]); // Initialize sorted guides
  }, [vehicles]);

  // Sorting function
  // Sorting function based on selected pricing mode
  const handleSort = () => {
    const sorted = [...vehicles]
      .map((vehicle) => ({
        ...vehicle,
        price: getPriceField(vehicle), // Use dynamic price field
      }))
      .sort((a, b) =>
        sortOrder === "asc" ? a.price - b.price : b.price - a.price
      );

    setSortedVehicles(sorted);
    setSortOrder(sortOrder === "asc" ? "desc" : "asc");
  };

  useEffect(() => {
    if (hourlyPrices && hourlyPrices.length > 0) {
      // ✅ Ensure it's not undefined
      setPriceRange({
        min: Math.min(...hourlyPrices),
        max: Math.max(...hourlyPrices),
      });
    }
  }, [hourlyPrices]);

  // Filter guides based on selected price range
  const filteredVehicles = sortedVehicles.filter((vehicle) => {
    const rate = getPriceField(vehicle);
    return rate >= priceRange.min && rate <= priceRange.max;
  });

  // Function to handle page change
  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  // Function to handle guide selection
  const handleVehicleClick = (vehicles, navigate) => {
    const vehicleMode = mode[vehicles.id]?.mode || "dmc"; // ✅ Get correct mode
    const dmcId = mode[vehicles.id]?.dmcId || vehicles.dmc_id; // ✅ Get correct dmcId
    if(selectionType === "Entry Port"){
    dispatch(setSelectedVehicle({ id: vehicles.id, mode: vehicleMode, dmcId })); // ✅ Store only selected vehicle's ID & mode
    }
    else{
      dispatch(setSelectedVehicle1({ id: vehicles.id, mode: vehicleMode, dmcId })); // ✅ Store only selected vehicle's ID & mode
    }
    dispatch(
      fetchVehicleDetails({ city: vehicles.city, country: vehicles.country,type:portZoneType })
    )
      .unwrap() // Unwrap the promise to handle the payload directly
      .then((data) => {
        navigate(`/dashboard/db-dashboard/activity-single-1`, {
          state: { vehicles: data }, // ✅ Pass correct mode
        });
      });
  };

  // Function to handle mode change and update Redux
  const handleModeChange = (id, newMode, dmcId) => {
    dispatch(setMode({ [id]: { mode: newMode, dmcId } })); // ✅ Update Redux mode for specific vehicle
  };

  // Reset current page when vehicles are cleared (new search)
  useEffect(() => {
    if (vehicles.length === 0) {
      setCurrentPage(1);
      setHasMore(true);
    }
  }, [vehicles.length]);

  // Scroll detection for infinite scroll
  useEffect(() => {
    const handleScroll = () => {
      if (
        window.innerHeight + document.documentElement.scrollTop >=
        document.documentElement.offsetHeight - 1000 && // Load more when 1000px from bottom
        !isLoadingMore &&
        hasMore &&
        status !== "loading" &&
        vehicles.length > 0 // Only load more if we have vehicles
      ) {
        setCurrentPage(prev => prev + 1);
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [isLoadingMore, hasMore, status, vehicles.length]);

  // Load more effect for infinite scroll
  useEffect(() => {
    if (currentPage > 1 && vehicles.length > 0) {
      setIsLoadingMore(true);
      // Dispatch action to fetch more vehicles based on selection type
      const start = (currentPage - 1) * itemsPerPage;
      
      if (zone_on === 1) {
        // For zone vehicles, use fetchZoneVehicles
        dispatch(fetchZoneVehicles({ start, limit: itemsPerPage })).then((result) => {
          setIsLoadingMore(false);
          // Check if we have more data based on the response
          if (result.payload && Array.isArray(result.payload)) {
            if (result.payload.length < itemsPerPage) {
              setHasMore(false);
            }
          } else {
            setHasMore(false);
          }
        }).catch(() => {
          setIsLoadingMore(false);
          setHasMore(false);
        });
      } else {
        // For regular vehicles, use fetchVehicles
        dispatch(fetchVehicles({ start, limit: itemsPerPage })).then((result) => {
          setIsLoadingMore(false);
          // Check if we have more data based on the response
          if (result.payload && Array.isArray(result.payload)) {
            if (result.payload.length < itemsPerPage) {
              setHasMore(false);
            }
          } else {
            setHasMore(false);
          }
        }).catch(() => {
          setIsLoadingMore(false);
          setHasMore(false);
        });
      }
    }
  }, [currentPage, vehicles.length, itemsPerPage, dispatch, zone_on]);

  // Sync Redux mode when selectedModes changes
  // useEffect(() => {
  //   const lastSelectedMode = Object.values(selectedModes).pop() || "dmc";
  //   dispatch(setMode(lastSelectedMode));
  // }, [selectedModes, dispatch]);

  const [isFilterVisible, setIsFilterVisible] = useState(false);
  const displayedVehicles = filteredVehicles.filter((vehicle) => {
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

    if (priceMode === "checked" && dmcPrice <= 0) {
      return false;
    }

    if (
      (dmcPrice === 0 && travClicksPrice === 0) ||
      (bookingType === "enquiry" && dmcPrice === 0)
    ) {
      return false;
    }

    return true;
  });

  const displayedVehicleCount = displayedVehicles.length;

  return (
    <>
      <MetaComponent meta={metadata} />
      {/* End Page Title */}

      <div className="header-margin">
        <CustomStepper />
        <TourStatus />
      </div>
      {/* header top margin */}

      {/* <Header11 /> */}
      {/* End Header 1 */}

      <section className="pt-40 pb-40 bg-light-2">
        <div className="container-xxl padding-left-right-5">
          {/* Visual debugging information */}
          {/* <div className="row mb-20">
            <div className="col-12">
              <div className="border border-danger rounded-4 p-20 bg-light-2">
                <h3 className="text-18 fw-500 text-danger mb-10">Debug Info - Activity List V2</h3>
                <div className="text-14">
                  <p><strong>zone_on value:</strong> {zone_on !== undefined ? zone_on : "undefined"}</p>
                  <p><strong>typeof zone_on:</strong> {typeof zone_on}</p>
                  <p><strong>rawZoneOn (from Redux):</strong> {rawZoneOn !== undefined ? rawZoneOn : "undefined"}</p>
                  <p><strong>typeof rawZoneOn:</strong> {typeof rawZoneOn}</p>
                  <p><strong>rawZoneOn == 0:</strong> {rawZoneOn == 0 ? "true" : "false"}</p>
                  <p><strong>rawZoneOn === 0:</strong> {rawZoneOn === 0 ? "true" : "false"}</p>
                  <p><strong>zone_on == 0:</strong> {zone_on == 0 ? "true" : "false"}</p>
                  <p><strong>zone_on === 0:</strong> {zone_on === 0 ? "true" : "false"}</p>
                  <p><strong>Component:</strong> ActivityListPage2</p>
                </div>
              </div>
            </div>
          </div> */}
          {/* End visual debugging */}
          
          <div className="row">
            <div className="col-12" style={{ padding: "20px" }}>
              <div className="text-center">
                <h1 className="text-30 fw-600">Entry/Exit Port</h1>
              </div>
              {/* End text-center */}
              {/* <MainFilterSearchBox Location={Location} /> */}
              {/* Debugging section with test button for zone_on */}
              {/* <div className="row mb-20">
                <div className="col-12">
                  <div className="d-flex gap-10">
                    <button 
                      className="button -md -dark-1 bg-blue-1 text-white"
                      onClick={() => {
                        console.log("Button clicked, zone_on value:", zone_on);
                        alert(`Current zone_on value: ${zone_on}, type: ${typeof zone_on}`);
                      }}
                    >
                      Test zone_on Value
                    </button>
                    <span className="ml-10 d-flex align-items-center">
                      Current value: <strong className="ml-5">{zone_on !== null ? zone_on : "null"}</strong>
                    </span>
                  </div>
                </div>
              </div> */}
              {/* End debugging section */}
              {/* Conditional rendering based on zone_on value */}
              {zone_on === 1 ? (
                <MainFilterSearchBox2 Location={Location} />
              ) : (
                <MainFilterSearchBox Location={Location} />
              )}
            </div>
            {/* End col-12 */}
          </div>
        </div>
      </section>

      <section className="layout-pt-md layout-pb-lg">
        <div className="container-xxl padding-left-right-5">
          <div className="row y-gap-30" style={{ padding: "20px" }}>
            {/* Desktop Sidebar */}
            <div className="col-xl-3  d-xl-block">
              <aside className="sidebar y-gap-40 xl:d-none">
                <Sidebar
                  hourlyPrices={hourlyPrices}
                  priceRange={priceRange}
                  setPriceRange={setPriceRange}
                />
              </aside>
            </div>

            {/* Mobile Filter Section */}
            <div className="col-12 d-xl-none">
              <div className="filter-mobile-section">
                <button
                  className="filter-button"
                  onClick={() => setIsFilterVisible(!isFilterVisible)}
                >
                  <div className="button-content">
                    <div className="left-content">
                      <i className="icon-filter text-20" />
                      <span>Filter</span>
                    </div>
                    <div className="arrow-icon">
                      {isFilterVisible ? (
                        <FaAngleUp className="arrow-icon-svg" />
                      ) : (
                        <FaAngleDown className="arrow-icon-svg" />
                      )}
                    </div>
                  </div>
                </button>

                {/* Collapsible Filter Content */}
                <div
                  className={`filter-content ${isFilterVisible ? "show" : ""}`}
                >
                  <aside className="sidebar y-gap-40 mb-30">
                    <Sidebar
                      hourlyPrices={hourlyPrices}
                      priceRange={priceRange}
                      setPriceRange={setPriceRange}
                    />
                  </aside>
                </div>
              </div>
            </div>

            {/* Main Content */}
            <div className="col-xl-9">
              <TopHeaderFilter
                Location={Location}
                vehicles={vehicles}
                handleSort={handleSort}
                sortOrder={sortOrder}
                filteredCount={displayedVehicleCount}
              />
              <div className="mt-30"></div>
              {/* End mt--30 */}
              <div className="row y-gap-30" style={{ marginTop: "0px", paddingLeft: "2rem" }}>
                              <ActivityProperties
                vehicles={filteredVehicles}
                status={status}
                onVehicleClick={handleVehicleClick}
                selectedModes={mode}
                setSelectedModes={handleModeChange}
                priceMode={priceMode}
                hasMore={hasMore}
                isLoadingMore={isLoadingMore}
              />
              </div>
              {/* End .row */}
              {/* <Pagination /> */}
            </div>
            {/* End .col for right content */}
          </div>
          {/* End .row */}
        </div>
        {/* End .container */}
      </section>
      {/* End layout for listing sidebar and content */}

      <CallToActions />
      {/* End Call To Actions Section */}

      {/* <DefaultFooter /> */}

      <style jsx>{`
        .filter-mobile-section {
          position: relative;
          margin-bottom: 20px;
        }

        .filter-button {
          width: 100%;
          background-color: #3554d1;
          border: none;
          border-radius: 4px;
          padding: 15px 20px;
          cursor: pointer;
          transition: all 0.3s ease;
        }

        .filter-button:hover {
          background-color: #284bc1;
        }

        .button-content {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .left-content {
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .left-content i {
          color: white;
        }

        .left-content span {
          color: white;
          font-size: 16px;
          font-weight: 500;
        }

        .arrow-icon {
          color: white;
          display: flex;
          align-items: center;
        }

        .arrow-icon-svg {
          width: 20px;
          height: 20px;
          color: white;
          transition: transform 0.3s ease;
        }

        .filter-content {
          max-height: 0;
          overflow: hidden;
          transition: max-height 0.3s ease-out;
          background-color: white;
          border-radius: 4px;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-content.show {
          max-height: 2000px;
          transition: max-height 0.5s ease-in;
          padding: 20px;
          margin-top: 15px;
        }

        @media (max-width: 1199px) {
          .sidebar {
            margin-bottom: 30px;
          }
        }
      `}</style>
    </>
  );
};

//export default ActivityListPage2;

export default ActivityListPage2;
