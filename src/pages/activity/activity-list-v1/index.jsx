import CallToActions from "@/components/common/CallToActions";
import Header11 from "@/components/header/header-11";
import DefaultFooter from "@/components/footer/default";
import MainFilterSearchBox from "@/components/activity-list/activity-list-v1/MainFilterSearchBox";
import TopHeaderFilter from "@/components/activity-list/activity-list-v1/TopHeaderFilter";
import ActivityProperties from "@/components/activity-list/activity-list-v1/ActivityProperties";
import Sidebar from "@/components/activity-list/activity-list-v1/Sidebar";
import { useSelector, useDispatch } from "react-redux";
import { useState, useEffect, useMemo } from "react";
import {
  selectGuidesStatus,
  selectGuidesError,
  SelectedGuide,
  selectSearchText,
  selectFilters,
  selectSortBy,
  setSelectedGuide,
  setMode,
  fetchGuideDetails,
  resetguide,
} from "@/slice/tourguide/guideslice";

import MetaComponent from "@/components/common/MetaComponent";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";
import { FaAngleUp, FaAngleDown } from "react-icons/fa";

const metadata = {
  title: "Tour Guide List || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const ActivityListPage1 = () => {
  const dispatch = useDispatch();
  const searchText = useSelector(selectSearchText);
  const guides = useSelector(SelectedGuide) || []; // ✅ Default to empty array
  console.log("guidessss", guides);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const bookingType = useSelector((state) => state.common.bookingType);
  console.log("gui", guides);
  const [priceRange, setPriceRange] = useState({ min: 0, max: 500 });
  const filters = useSelector(selectFilters);
  const sortBy = useSelector(selectSortBy);
  const status = useSelector(selectGuidesStatus);
  const error = useSelector(selectGuidesError);
  const Location = useSelector((state) => state.bookings.searchLocation);

  console.log("Country Names:", Location);

  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 5;
  const [sortOrder, setSortOrder] = useState("asc");
  const [sortedGuides, setSortedGuides] = useState([]);
  const [isFilterVisible, setIsFilterVisible] = useState(false);
  const mode = useSelector((state) => state.tourguide.mode);
  const priceMode = useSelector((state) => state.tourguide.pricemode);

  useEffect(() => {
    const defaultMode =
      priceMode === "checked" || priceMode === "non-checked"
        ? "dmc"
        : "travclicks";
    if (!guides || !Array.isArray(guides)) return; // ✅ Prevent error

    const initialModes = guides.reduce((acc, guide) => {
      // ❌ guide was incorrectly named guides
      acc[guide.id] = { mode: defaultMode, dmcId: guide.dmc_id };
      return acc;
    }, {});

    dispatch(setMode(initialModes));
  }, [guides, dispatch, priceMode]);

  const getPriceField = (guide) => {
    const guideMode = mode[guide.id]?.mode || "dmc"; // ✅ Fetch mode from Redux
    return guideMode === "dmc"
      ? guide.dmc_day_rate
        ? parseFloat(guide.dmc_day_rate)
        : 0
      : guide.travClicks_day_rate
      ? parseFloat(guide.travClicks_day_rate)
      : 0;
  };

  // Memoizing prices to avoid unnecessary re-renders
  const hourlyPrices = useMemo(() => {
    return guides.map((guide) => getPriceField(guide));
  }, [guides, mode]); // ✅ Fix dependency array

  console.log("Hourly Prices:", hourlyPrices);

  useEffect(() => {
    setSortedGuides([...guides]);
  }, [guides]);

  // Sorting function
  const handleSort = () => {
    const sorted = [...guides]
      .map((guide) => ({
        ...guide,
        price: getPriceField(guide), // Use dynamic price field
      }))
      .sort((a, b) =>
        sortOrder === "asc" ? a.price - b.price : b.price - a.price
      );

    setSortedGuides(sorted);
    setSortOrder(sortOrder === "asc" ? "desc" : "asc");
  };

  console.log("sortedguides", sortedGuides);

  // Set default price range when data loads
  useEffect(() => {
    if (hourlyPrices.length > 0) {
      setPriceRange({
        min: Math.min(...hourlyPrices),
        max: Math.max(...hourlyPrices),
      });
    }
  }, [hourlyPrices]); // ✅ Ensure price range updates dynamically

  console.log("Hourly Prices Array:", hourlyPrices);

  console.log("Price Range:", priceRange);

  // Filter guides based on selected price range
  const filteredGuides = sortedGuides.filter((guide) => {
    const rate = getPriceField(guide); // Default to 0 if NaN
    return rate >= priceRange.min && rate <= priceRange.max;
  });

  console.log("filterguiddd", filteredGuides);

  // Function to handle page change
  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  // Function to handle guide selection
  const handleGuideClick = (guides, navigate) => {
    const guideMode = mode[guides.id]?.mode || "dmc"; // ✅ Get correct mode
    const dmcId = mode[guides.id]?.dmcId || guides.dmc_id; // ✅ Get correct dmcId
    dispatch(setSelectedGuide({ id: guides.id, mode: guideMode, dmcId }));
    
    // Get the location from the array and handle different data structures
    let city = "";
    if (Array.isArray(Location)) {
      // If Location is an array of country codes, we need to get the city from somewhere else
      // For now, we'll use Singapore as the default city for SG
      if (Location.includes('SG')) {
        city = "Singapore";
      } else {
        console.error("Error: Location array does not contain valid city data", Location);
        return; // Exit early if we don't have valid location data
      }
    } else if (typeof Location === 'object' && Location !== null) {
      city = Location.city || Location.name || "";
    } else if (typeof Location === 'string') {
      city = Location;
    }

    if (!city) {
      console.error("Error: No valid city found in location data. Location data:", Location);
      return; // Exit early if we don't have a city
    }

    // Get the required parameters from state
    const searchParams = {
      guide_id: guides.id,
      mode: guideMode,
      dmc_id: dmcId,
      pickup: city,
      date: guides.date || new Date().toISOString().split('T')[0]
    };
    
    dispatch(fetchGuideDetails(searchParams))
      .unwrap()
      .then((guide) => {
        navigate(`/dashboard/db-dashboard/activity-single`, {
          state: { guide: guide },
        });
      })
      .catch((error) => {
        console.error("Error fetching guide details:", error);
      });
  };

  const handleModeChange = (id, newMode, dmcId) => {
    dispatch(setMode({ [id]: { mode: newMode, dmcId } })); // ✅ Update Redux mode for specific vehicle
  };

  // Calculate the actual displayed guide count after all conditions
  const displayedGuides = filteredGuides.filter((guide) => {
    const dmcPrice = guide.dmc_day_rate
      ? parseFloat(guide.dmc_day_rate) * exchangeRate
      : 0;
    const travClicksPrice = guide.travClicks_day_rate
      ? parseFloat(guide.travClicks_day_rate) * exchangeRate
      : 0;

    // Apply the same filtering conditions as in ActivityProperties
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

  const displayedGuideCount = displayedGuides.length;

  return (
    <>
      <MetaComponent meta={metadata} />
      <div className="header-margin">
        <CustomStepper />
        <TourStatus />
      </div>
      {/* <Header11 /> */}
      <section className="pt-40 pb-40 bg-light-2">
        <div className="container">
          <div className="row">
            <div className="col-12">
              <div className="text-center">
                <h1 className="text-30 fw-600">Tour Guide</h1>
              </div>
              <MainFilterSearchBox Location={Location} />
            </div>
          </div>
        </div>
      </section>
      <section className="layout-pt-md layout-pb-lg">
        <div className="container">
          <div className="row y-gap-30">
            <div className="col-xl-3  d-xl-block">
              {/* Desktop Sidebar */}
              <aside className="sidebar y-gap-40 xl:d-none">
                <Sidebar
                  hourlyPrices={hourlyPrices}
                  priceRange={priceRange}
                  setPriceRange={setPriceRange}
                />
              </aside>
            </div>

            {/* Mobile Sidebar */}
            <div className="col-12 d-xl-none">
              <div className="filter-mobile-section">
                <button
                  className="filter-button"
                  onClick={() => setIsFilterVisible(!isFilterVisible)}
                  aria-expanded={isFilterVisible}
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

            <div className="col-xl-9 ">
              <TopHeaderFilter
                Location={Location}
                guides={guides}
                handleSort={handleSort}
                sortOrder={sortOrder}
                filteredCount={displayedGuideCount}
              />

              <div className="mt-30"></div>

              <div className="row y-gap-15">
                <ActivityProperties
                  guides={filteredGuides}
                  searchText={searchText}
                  filters={filters}
                  sortBy={sortBy}
                  status={status}
                  error={error}
                  currentPage={currentPage}
                  itemsPerPage={itemsPerPage}
                  onPageChange={handlePageChange}
                  onGuideClick={handleGuideClick}
                  selectedModes={mode} // ✅ Pass selected modes
                  setSelectedModes={handleModeChange} // ✅ Allow child to update mode
                  priceMode={priceMode}
                />
              </div>
            </div>
          </div>
        </div>
      </section>
      <CallToActions />
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
      ;
    </>
  );
};

export default ActivityListPage1;
