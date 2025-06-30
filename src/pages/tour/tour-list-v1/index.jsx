// import { useState } from "react";
// import CallToActions from "@/components/common/CallToActions";
// import Header11 from "@/components/header/header-11";
// import DefaultFooter from "@/components/footer/default";
// import MainFilterSearchBox from "@/components/tour-list/tour-list-v1/MainFilterSearchBox";
// // import TopHeaderFilter from "@/components/tour-list/tour-list-v1/TopHeaderFilter";
// import TourProperties from "@/components/tour-list/tour-list-v1/TourProperties";
// // import Pagination from "@/components/tour-list/common/Pagination";
// import Sidebar from "@/components/tour-list/tour-list-v1/Sidebar";

// import MetaComponent from "@/components/common/MetaComponent";
// import TourStatus from "@/components/common/sub_common/TourStatus";

// const metadata = {
//   title: "Tour List v1 || GoTrip - Travel & Tour ReactJs Template",
//   description: "GoTrip - Travel & Tour ReactJs Template",
// };

// const TourListPage1 = () => {
//   const [filters, setFilters] = useState({
//     priceRange: { min: 0, max: 1000 },
//     reviewScores: [],
//     timeOfDay: [],
//   });
//   return (
//     <>
//       <MetaComponent meta={metadata} />
//       {/* End Page Title */}

//       <div className="header-margin"></div>
//       {/* header top margin */}

//       <Header11 />
//       {/* End Header 1 */}
//       <TourStatus/>

//       <section className="pt-40 pb-40 bg-light-2">
//         <div className="container">
//           <div className="row">
//             <div className="col-12">
//               <div className="text-center">
//                 <h1 className="text-30 fw-600">Attraction</h1>
//               </div>
//               {/* End text-center */}
//               <MainFilterSearchBox />
//             </div>
//             {/* End col-12 */}
//           </div>
//         </div>
//       </section>
//       {/* Top SearchBanner */}

//       <section className="layout-pt-md layout-pb-lg">
//         <div className="container">
//           <div className="row y-gap-30">
//             <div className="col-xl-3">
//               <aside className="sidebar y-gap-40 xl:d-none">
//                 <Sidebar />
//               </aside>
//               {/* End sidebar for desktop */}

//               <div
//                 className="offcanvas offcanvas-start"
//                 tabIndex="-1"
//                 id="listingSidebar"
//               >
//                 <div className="offcanvas-header">
//                   <h5 className="offcanvas-title" id="offcanvasLabel">
//                     Filter Tours
//                   </h5>
//                   <button
//                     type="button"
//                     className="btn-close"
//                     data-bs-dismiss="offcanvas"
//                     aria-label="Close"
//                   ></button>
//                 </div>
//                 {/* End offcanvas header */}

//                 <div className="offcanvas-body">
//                   <aside className="sidebar y-gap-40  xl:d-block">
//                     <Sidebar filters={filters} setFilters={setFilters} />
//                   </aside>
//                 </div>
//                 {/* End offcanvas body */}
//               </div>
//               {/* End mobile menu sidebar */}
//             </div>
//             {/* End col */}

//             <div className="col-xl-9 ">
//               {/* <TopHeaderFilter /> */}
//               <div className="mt-30"></div>
//               {/* End mt--30 */}
//               <div className="row y-gap-30">
//                 <TourProperties filters={filters}  />
//               </div>
//               {/* End .row */}
//               {/* <Pagination /> */}
//             </div>
//             {/* End .col for right content */}
//           </div>
//           {/* End .row */}
//         </div>
//         {/* End .container */}
//       </section>
//       {/* End layout for listing sidebar and content */}

//       <CallToActions />
//       {/* End Call To Actions Section */}

//       <DefaultFooter />
//     </>
//   );
// };

// export default TourListPage1;

import { useState } from "react";
import CallToActions from "@/components/common/CallToActions";
import Header11 from "@/components/header/header-11";
import DefaultFooter from "@/components/footer/default";
import MainFilterSearchBox from "@/components/tour-list/tour-list-v1/MainFilterSearchBox";
import TourProperties from "@/components/tour-list/tour-list-v1/TourProperties";
import Sidebar from "@/components/tour-list/tour-list-v1/Sidebar";
import MetaComponent from "@/components/common/MetaComponent";
import TourStatus from "@/components/common/sub_common/TourStatus";
import CustomStepper from "@/components/common/sub_common/CustomStepper";

const metadata = {
  title: "Attraction List || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const TourListPage1 = () => {
  const [filters, setFilters] = useState({
    priceRange: { min: 0, max: 1000 },
    reviewScores: [],
    timeOfDay: [],
  });

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
                <h1 className="text-30 fw-600">Attraction & Experiences</h1>
              </div>
              <MainFilterSearchBox />
            </div>
          </div>
        </div>
      </section>

      <section className="layout-pt-md layout-pb-lg">
        <div className="container">
          <div className="row y-gap-30">
            <div className="col-xl-3">
              <aside
                className="sidebar y-gap-40 xl:d-none"
                style={{ marginTop: "50px" }}
              >
                <Sidebar />
              </aside>

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
                <div className="offcanvas-body">
                  <Sidebar />
                </div>
              </div>
            </div>

            <div className="col-xl-9">
              <TourProperties />
            </div>
          </div>
        </div>
      </section>

      <CallToActions />
      {/* <DefaultFooter /> */}
    </>
  );
};

export default TourListPage1;
