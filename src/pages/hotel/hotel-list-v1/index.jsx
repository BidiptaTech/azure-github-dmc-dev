import CallToActions from "@/components/common/CallToActions";
import MainFilterSearchBox from "@/components/hotel-list/hotel-list-v1/MainFilterSearchBox";
import TopHeaderFilter from "@/components/hotel-list/hotel-list-v1/TopHeaderFilter";
import HotelProperties from "@/components/hotel-list/hotel-list-v1/HotelProperties";
import Sidebar from "@/components/hotel-list/hotel-list-v1/Sidebar";

import MetaComponent from "@/components/common/MetaComponent";

import { useSelector } from "react-redux";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import { useLocation } from "react-router-dom";
import TourStatus from "@/components/common/sub_common/TourStatus";
import "@/styles/hotelListHero.css";

const metadata = {
  title: "Hotel List || Travclick - Travel & Tour",
  description: "Travclick - Travel & Tour",
};

const HERO_BG = "/img/masthead/hotel-list-hero.jpg";

const HotelListPage1 = () => {
  const hotelsdata = useSelector((state) => state.hotels);
  const location = useLocation();
  const hotel = location.state?.hotel;

  return (
    <>
      <MetaComponent meta={metadata} />
      {/* Stepper and Tour Status — header-margin clears the fixed header */}
      <div className="header-margin">
        <div className="hotel-list-lite-stack">
          <div className="hotel-list-lite-panel">
            <CustomStepper variant="lite" />
            <TourStatus variant="lite" />
          </div>
        </div>
      </div>


      <div className="hotel-list-top">
        <div className="container">
          <section className="hotel-list-hero">
            <div
              className="hotel-list-hero__bg"
              style={{
                backgroundImage: `
                  linear-gradient(
                    105deg,
                    rgba(255, 255, 255, 0.92) 0%,
                    rgba(255, 255, 255, 0.78) 36%,
                    rgba(232, 242, 255, 0.28) 58%,
                    rgba(255, 255, 255, 0.1) 100%
                  ),
                  url(${HERO_BG})
                `,
              }}
              aria-hidden="true"
            />
            <div className="hotel-list-hero__inner">
              <div className="hotel-list-hero__search-wrap">
                <MainFilterSearchBox layout="hero" />
              </div>
            </div>
          </section>
        </div>
      </div>

      

      <section className="hotel-list-results layout-pb-lg">
        <div className="container">
          <div className="row y-gap-30">
            <div className="col-xl-3">
              <aside className="sidebar y-gap-40 xl:d-none">
                <Sidebar />
              </aside>

              <div
                className="offcanvas offcanvas-start"
                tabIndex="-1"
                id="listingSidebar"
              >
                <div className="offcanvas-header">
                  <h5 className="offcanvas-title" id="offcanvasLabel">
                    Filter Hotels
                  </h5>
                  <button
                    type="button"
                    className="btn-close"
                    data-bs-dismiss="offcanvas"
                    aria-label="Close"
                  ></button>
                </div>

                <div className="offcanvas-body">
                  <aside className="sidebar y-gap-40 xl:d-block">
                    <Sidebar />
                  </aside>
                </div>
              </div>
            </div>

            <div className="col-xl-9 ">
              <TopHeaderFilter
                hotel={hotel}
                hotelsdata={hotelsdata}
                location={location}
              />
              <div className="row y-gap-30">
                <HotelProperties />
              </div>
            </div>
          </div>
        </div>
      </section>

      <CallToActions />
    </>
  );
};

export default HotelListPage1;
