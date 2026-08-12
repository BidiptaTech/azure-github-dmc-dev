// import "photoswipe/dist/photoswipe.css";
// import toursData from "@/data/tours";
// import Header11 from "@/components/header/header-11";
// import TourGallery from "@/components/tour-single/TourGallery";
// import { useSelector } from "react-redux";
// import { useParams } from "react-router-dom";
// import TourStatus from "@/components/common/sub_common/TourStatus";
// import CustomStepper from "@/components/common/sub_common/CustomStepper";

// const TourSingleV1Dynamic = () => {
//   const { id } = useParams();
//   const tour = toursData.find((item) => item.id == id) || toursData[0];

//   const attractionDetails = useSelector(
//     (state) => state.attractions.attractionDetails
//   );

//   return (
//     <>
//       <div className="header-margin"></div>

//       <Header11 />

//       <CustomStepper />
//       <TourStatus />

//       <section className="pt-40">
//         <div className="container">
//           <div className="row y-gap-20 justify-between items-end">
//             <div className="col-auto">
//               <h1 className="text-30 fw-600">{attractionDetails?.name}</h1>
//               <div className="row x-gap-20 y-gap-20 items-center pt-10">
//                 <div className="col-auto">
//                   <div className="row x-gap-10 items-center">
//                     <div className="col-auto">
//                       <div className="d-flex x-gap-5 items-center">
//                         <i className="icon-placeholder text-16 text-light-1"></i>
//                         <div className="text-15 text-light-1">
//                           {attractionDetails?.location},{" "}
//                           {attractionDetails?.country}
//                         </div>
//                       </div>
//                     </div>
//                   </div>
//                 </div>
//               </div>
//             </div>
//           </div>
//         </div>
//       </section>

//       <TourGallery tour={tour} />
//     </>
//   );
// };

// export default TourSingleV1Dynamic;

import "photoswipe/dist/photoswipe.css";
import toursData from "@/data/tours";
import Header11 from "@/components/header/header-11";
import TourGallery from "@/components/tour-single/TourGallery";
import { useSelector } from "react-redux";
import { useParams, useNavigate } from "react-router-dom";
import TourStatus from "@/components/common/sub_common/TourStatus";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "Attraction || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const TourSingleV1Dynamic = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const tour = toursData.find((item) => item.id == id) || toursData[0];

  const attractionDetails = useSelector(
    (state) => state.attractions.attractionDetails
  );

  return (
    <>
      <MetaComponent meta={metadata} />
      <div className="header-margin"></div>

      <Header11 />

      <CustomStepper />
      <TourStatus />

      <section className="pt-40">
        <div className="container">
          <div className="row y-gap-20 justify-between items-end">
            <div className="col-auto">
              {/* <button 
                className="btn btn-outline-primary mb-3"
                onClick={() => navigate("/dashboard/db-dashboard/attractions")}
              >
                Back
              </button> */}
              <div
                className="back-button-container"
                style={{
                  display: "flex",
                  alignItems: "flex-start",
                  padding: "20px",
                }}
              >
                <button
                  className="button px-4 py-2 bg-blue-1 text-white rounded back-button-responsive"
                  style={{
                    minHeight: "40px",
                    position: "relative",
                    left: "-100px", // Moves it 100px to the left
                  }}
                  onClick={() =>
                    navigate("/dashboard/db-dashboard/attractions")
                  }
                >
                  ← Back To Listing
                </button>
              </div>

              <h1 className="text-30 fw-600">{attractionDetails?.name}</h1>
              <div className="row x-gap-20 y-gap-20 items-center pt-10">
                <div className="col-auto">
                  <div className="row x-gap-10 items-center">
                    <div className="col-auto">
                      <div className="d-flex x-gap-5 items-center">
                        <i className="icon-placeholder text-16 text-light-1"></i>
                        <div className="text-15 text-light-1">
                          {attractionDetails?.location},{" "}
                          {attractionDetails?.country}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <TourGallery tour={tour} />
    </>
  );
};

export default TourSingleV1Dynamic;
