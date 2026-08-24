import { useEffect } from "react";
import "photoswipe/dist/photoswipe.css";
import Header11 from "@/components/header/header-11";
import Overview from "@/components/hotel-single/Overview";
import PopularFacilities from "@/components/hotel-single/PopularFacilities";
import PropertyHighlights from "@/components/hotel-single/PropertyHighlights";
import StickyHeader from "@/components/hotel-single/StickyHeader";
import SidebarRight from "@/components/hotel-single/SidebarRight";
import AvailableRooms from "@/components/hotel-single/AvailableRooms";
import GalleryOne from "@/components/hotel-single/GalleryOne";
import { useLocation } from "react-router-dom";

import MetaComponent from "@/components/common/MetaComponent";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";
import { useDispatch, useSelector } from "react-redux";
import HotelPolicies from "@/components/hotel-single/hotelPolicies";
import {
  setCheckoutHotel,
  setHotelDetails,
  setHotelImages,
} from "@/slice/hotel/HotelDetailsSlice";
import {
  fetchRoomData,
  setId,
  settourid,
} from "@/slice/hotel/HotelAvailabilitySlice";
import { setPriceMode, setPriceModeId } from "@/slice/hotel/CategorySlice";

const metadata = {
  title: "Hotel Single v1 || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const HotelSingleV1Dynamic = () => {
  const location = useLocation();
  const dispatch = useDispatch();

  const checkoutHotel = useSelector((state) => state.hoteldetails.checkoutHotel);
  const hotelDetails = useSelector(
    (state) => state.hoteldetails.bookingDetails
  );
  const roomData = useSelector((state) => state.rooms.roomDatas);
  const roomsStatus = useSelector((state) => state.rooms.status);
  const roomsHotelId = useSelector((state) => state.rooms.id);
  const roomsTourId = useSelector((state) => state.rooms.tour_id);
  const tourId = useSelector((state) => state.hotels.id);
  const categoryPriceMode = useSelector((state) => state.category.priceMode);
  const categoryPriceModeId = useSelector((state) => state.category.priceModeId);

  const hotel =
    location.state?.hotel ||
    checkoutHotel ||
    (hotelDetails?.hotel_id || hotelDetails?.id || hotelDetails?.hotel_name
      ? hotelDetails
      : null);

  useEffect(() => {
    const stateHotel = location.state?.hotel;
    if (!stateHotel) return;

    dispatch(setCheckoutHotel(stateHotel));
    dispatch(setHotelDetails(stateHotel));
    if (stateHotel.images) dispatch(setHotelImages(stateHotel.images));

    const selectedMode = location.state?.priceMode;
    const selectedModeId = location.state?.priceModeId;
    if (selectedMode) dispatch(setPriceMode(selectedMode));
    if (selectedModeId) dispatch(setPriceModeId(selectedModeId));
    if (stateHotel.id || stateHotel.hotel_id) {
      dispatch(setId(stateHotel.id || stateHotel.hotel_id));
    }
  }, [dispatch, location.state]);

  useEffect(() => {
    const hasRooms = Boolean(roomData?.room_data?.length);
    const hotelId = hotel?.id || hotel?.hotel_id || roomsHotelId;
    if (hasRooms || !hotelId || roomsStatus === "loading") return;

    const selectedMode = location.state?.priceMode
      || (typeof categoryPriceMode === "string"
        ? categoryPriceMode
        : Array.isArray(categoryPriceMode)
          ? categoryPriceMode[0]
          : "dmc");
    const priceModeId =
      location.state?.priceModeId ||
      categoryPriceModeId ||
      hotel?.dmc_id ||
      hotel?.travclicks_id;
    const effectiveTourId = roomsTourId || tourId || 0;

    dispatch(settourid(effectiveTourId));
    dispatch(
      fetchRoomData({
        id: hotelId,
        tour_id: effectiveTourId,
        priceMode: selectedMode,
        priceModeId,
        dmc_id: hotel?.dmc_id,
      })
    );
  }, [
    dispatch,
    roomData,
    roomsStatus,
    hotel,
    roomsHotelId,
    roomsTourId,
    tourId,
    categoryPriceMode,
    categoryPriceModeId,
    location.state,
  ]);

  return (
    <>
      <MetaComponent meta={metadata} />

      <div className="header-margin">
        <CustomStepper />
        <TourStatus />
      </div>

      <Header11 />

      <StickyHeader hotel={hotel} />

      <GalleryOne hotel={hotel} />

      <section className="pt-10">
        <div className="container">
          <div className="row y-gap-30">
            <div className="col-xl-7">
              <div className="row y-gap-40">
                <div className="col-12">
                  <h3 className="text-22 fw-500">Property highlights</h3>
                  <PropertyHighlights hotel={hotel} />
                </div>

                <div id="overview" className="col-12">
                  <Overview hotel={hotel} hotelDetails={hotelDetails} />
                </div>
              </div>
            </div>

            <div className="col-xl-5">
              <SidebarRight hotelDetails={roomData} />
            </div>
          </div>
        </div>
      </section>

      <section id="rooms" className="pt-30">
        <div className="container">
          <div className="row pb-20">
            <div className="col-auto">
              <h3 className="text-22 fw-500">Available Rooms</h3>
            </div>
          </div>

          <AvailableRooms hotel={hotel} />
          <div className="col-12">
            <h3 className="text-22 fw-500 pt-40 border-top-light">
              Most Popular Facilities
            </h3>
            <div className="row y-gap-10 pt-20">
              <PopularFacilities hotelDetails={hotelDetails} />
            </div>
          </div>
        </div>
      </section>

      <section className="pt-40" id="reviews">
        <div className="container">
          <div className="row">
            <div className="col-12">
              <h3 className="text-22 fw-500">Hotel Policies</h3>
            </div>
          </div>

          <HotelPolicies />
        </div>
      </section>
    </>
  );
};

export default HotelSingleV1Dynamic;
