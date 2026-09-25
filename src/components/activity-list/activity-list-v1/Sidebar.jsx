import ActivityTypes from "../sidebar/ActivityTypes";
import OthersFilter from "../sidebar/OthersFilter";
import Duration from "../sidebar/Duration";
import Languages from "../sidebar/Languages";
import PriceSlider from "../sidebar/PirceSlider"; // Fixed typo
import PopularAttractions from "../sidebar/PopularAttractions";
import PriceModeFilter from "../sidebar/PriceModeFilter";
import { useSelector, useDispatch } from "react-redux";
import { useEffect } from "react";
import DmcFilter from "../../hotel-list/sidebar/dmcFilter";
import { setPriceMode } from "@/slice/port/pickupDropSlice";
import { setPriceMode1 } from "@/slice/localtour/Localslice";
import { setPriceMode2 } from "@/slice/tourguide/guideslice";
const Sidebar = ({ hourlyPrices, priceRange, setPriceRange }) => {
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  const dispatch = useDispatch();

  useEffect(() => {
    dispatch(setPriceMode("checked"));
    dispatch(setPriceMode1("checked"));
    dispatch(setPriceMode2("checked"));
  }, [dispatch]);
  return (
    <>
      {/* <div className="sidebar__item -no-border">
        <h5 className="text-18 fw-500 mb-10">Activity Types</h5>
        <div className="sidebar-checkbox">
          <ActivityTypes />
        </div>
      </div>

      <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Other</h5>
        <div className="sidebar-checkbox">
          <OthersFilter />
        </div>
      </div> */}

      {/* <div className="sidebar__item -no-border">
        <h5 className="text-18 fw-500 mb-10">Mode</h5>
        <div className="sidebar-checkbox">
          <PriceModeFilter />
        </div>
      </div> */}

      <div className="sidebar__item -no-border">
        {PriceHide === "0" && (
          <>
            <h5 className="text-18 fw-500 mb-10">Price</h5>
            <div className="row x-gap-10 y-gap-30">
              <div className="col-12">
                <PriceSlider
                  hourlyPrices={hourlyPrices}
                  priceRange={priceRange}
                  setPriceRange={setPriceRange}
                />
              </div>
            </div>
          </>
        )}
      </div>
      <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">DMC</h5>
        <div className="sidebar-checkbox">
          <DmcFilter />
        </div>
      </div>
      {/* <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Duration</h5>
        <div className="sidebar-checkbox">
          <Duration />
        </div>
      </div>

      <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Languages</h5>
        <div className="sidebar-checkbox">
          <Languages />
        </div>
      </div>

      <div className="sidebar__item">
        <h5 className="text-18 fw-500 mb-10">Popular Attractions</h5>
        <div className="sidebar-checkbox">
          <PopularAttractions />
        </div>
      </div> */}
    </>
  );
};

export default Sidebar;
