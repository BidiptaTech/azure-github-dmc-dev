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
import { useSelector } from "react-redux";
import HotelPolicies from "@/components/hotel-single/hotelPolicies";

const metadata = {
  title: "Hotel Single v1 || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const HotelSingleV1Dynamic = () => {
  const location = useLocation();
  const hotel = location.state?.hotel;

  const hotelDetails = useSelector(
    (state) => state.hoteldetails.bookingDetails
  );

  const roomData = useSelector((state) => state.rooms.roomDatas);

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
