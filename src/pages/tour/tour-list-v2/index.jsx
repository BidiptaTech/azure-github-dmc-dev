import CallToActions from "@/components/common/CallToActions";
import Header11 from "@/components/header/header-11";
import DefaultFooter from "@/components/footer/default";
// import TopHeaderFilter from "@/components/restaurants/tour-list-v1/TopHeaderFilter";
import TourProperties from "@/components/restaurants/tour-list-v1/TourProperties";
// import Pagination from "@/components/restaurants/common/Pagination";
import Sidebar from "@/components/restaurants/tour-list-v1/Sidebar";
import MainFilterSearchBox from "@/components/restaurants/tour-list-v1/MainFilterSearchBox";
import MetaComponent from "@/components/common/MetaComponent";
import TourStatus from "@/components/common/sub_common/TourStatus";
import CustomStepper from "@/components/common/sub_common/CustomStepper";

const metadata = {
  title: "Restaurant List || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const TourListPage2 = () => {
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
        <div className="container">
          <div className="row">
            <div className="col-12">
              <div className="text-center">
                <h1 className="text-30 fw-600">Restaurants</h1>
              </div>
              {/* End text-center */}
              <MainFilterSearchBox />
            </div>
            {/* End col-12 */}
          </div>
        </div>
      </section>

      <section className="layout-pt-md layout-pb-lg">
        <div className="container">
          <div className="row y-gap-30">
            <div className="col-xl-3">
              <aside
                className="sidebar y-gap-40 xl:d-none"
                style={{ position: "relative", top: "90px" }}
              >
                <Sidebar />
              </aside>
              {/* End sidebar for desktop */}

              <div
                className="offcanvas offcanvas-start"
                tabIndex="-1"
                id="listingSidebar"
              >
                <div className="offcanvas-header">
                  <h5 className="offcanvas-title" id="offcanvasLabel">
                    Filter Tours
                  </h5>
                  <button
                    type="button"
                    className="btn-close"
                    data-bs-dismiss="offcanvas"
                    aria-label="Close"
                  ></button>
                </div>
                {/* End offcanvas header */}

                <div className="offcanvas-body">
                  <aside className="sidebar y-gap-40  xl:d-block">
                    <Sidebar />
                  </aside>
                </div>
                {/* End offcanvas body */}
              </div>
              {/* End mobile menu sidebar */}
            </div>
            {/* End col */}

            <div className="col-xl-9 ">
              {/* <TopHeaderFilter /> */}
              {/* <div className="mt-30"></div> */}
              {/* End mt--30 */}
              <div className="row y-gap-30" style={{ marginTop: "0px", paddingLeft: "2rem" }}>
                <TourProperties />
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
      {/* 
      <DefaultFooter /> */}
    </>
  );
};

export default TourListPage2;
