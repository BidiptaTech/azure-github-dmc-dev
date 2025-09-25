import "photoswipe/dist/photoswipe.css";
import { useEffect } from "react";
import toursData from "@/data/tours";
import Header11 from "@/components/header/header-11";
// import TopBreadCrumb from "@/components/tour-single/TopBreadCrumb";
// import ReviewProgress2 from "@/components/tour-single/guest-reviews/ReviewProgress2";
// import DetailsReview2 from "@/components/tour-single/guest-reviews/DetailsReview2";
// import ReplyForm from "@/components/tour-single/ReplyForm";
// import ReplyFormReview2 from "@/components/tour-single/ReplyFormReview2";
// import CallToActions from "@/components/common/CallToActions";
// import DefaultFooter from "@/components/footer/default";
// import Tours from "@/components/tours/Tours";
// import Faq from "@/components/faq/Faq";
import { useParams } from "react-router-dom";
// import Itinerary from "@/components/tour-single/itinerary";
// import ImportantInfo from "@/components/tour-single/ImportantInfo";
import TourGallery from "@/components/restaurants/filter-box/TourGallery";
// import { useLocation } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { fetchRestaurantsDetails } from "@/slice/restaurant/RestaurantsSlice";
import TourStatus from "@/components/common/sub_common/TourStatus";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import { useNavigate } from "react-router-dom";

import MetaComponent from "@/components/common/MetaComponent";

const metadata = {
  title: "Restaurant || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const RestaurantsDetails = () => {
  // let params = useParams();
  // const id = params.id;
  // const tour = toursData.find((item) => item.id == id) || toursData[0];

  // const location = useLocation();
  // const restaurant = location.state?.restaurants || {};
  //   console.log('restaurant11',restaurant);
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { id } = useParams();
  const tour = toursData.find((item) => item.id == id) || toursData[0];

  // const location = useLocation();
  // const attraction = location.state?.attraction || {};
  // console.log("Attraction Data imran786:", attraction);

  const restaurantsDetails = useSelector(
    (state) => state.restaurants.selectedRestaurant
  );
  //  const restaurantDetails = useSelector((state) => state.restaurants.restaurantDetails);

  const loading = useSelector((state) => state.restaurants.loading);
  const error = useSelector((state) => state.restaurants.error);

  //  useEffect(() => {
  //   if (id) {
  //     dispatch(fetchRestaurantsDetails({ restaurantId: id }));
  //   }
  // }, [dispatch, id]);

  console.log("Fetched restaurants Details:787", restaurantsDetails);

  return (
    <>
      <MetaComponent meta={metadata} />
      {/* End Page Title */}

      <div className="header-margin"></div>
      {/* header top margin */}

      <Header11 />
      {/* End Header 1 */}
      <CustomStepper />
      <TourStatus />
      {/* <TopBreadCrumb /> */}
      {/* End top breadcrumb */}

      <section className="pt-40">
        <div className="container">
          <div className="row y-gap-20 justify-between items-end">
            <div className="col-auto">
              {/* <button
                className="button px-15 py-8 bg-blue-1 text-white rounded position-absolute"
                style={{
                  top: "450px",
                  left: "40px",
                  zIndex: "10",
                  minHeight: "40px",
                }}
                onClick={() => navigate("/dashboard/db-dashboard/restaurants")}
              >
                ← Back To Listing
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
                    navigate("/dashboard/db-dashboard/restaurants")
                  }
                >
                  ← Back To Listing
                </button>
              </div>
              
              <style jsx global>{`
                .back-button-responsive {
                  left: -100px;
                  font-size: 14px;
                  padding: 12px 24px;
                  min-height: 40px;
                  transition: all 0.3s ease;
                  boxShadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                  border: none;
                  cursor: pointer;
                  display: flex;
                  alignItems: center;
                  gap: 6px;
                  whiteSpace: nowrap;
                  zIndex: 10;
                }
                
                /* Tablet styles */
                @media (max-width: 768px) {
                  .back-button-responsive {
                    left: -50px;
                    font-size: 12px;
                    padding: 10px 18px;
                    min-height: 36px;
                  }
                }
                
                /* Mobile styles */
                @media (max-width: 480px) {
                  .back-button-responsive {
                    left: -20px;
                    font-size: 11px;
                    padding: 8px 14px;
                    min-height: 32px;
                  }
                }
                
                /* Extra small mobile */
                @media (max-width: 360px) {
                  .back-button-responsive {
                    left: -10px;
                    font-size: 10px;
                    padding: 6px 12px;
                    min-height: 28px;
                  }
                }
              `}</style>
              <h1 className="text-30 fw-600">{restaurantsDetails?.name}</h1>
              <div className="row x-gap-20 y-gap-20 items-center pt-10">
                {/* <div className="col-auto">
                  <div className="d-flex items-center">
                    <div className="d-flex x-gap-5 items-center">
                      <i className="icon-star text-10 text-yellow-1"></i>

                      <i className="icon-star text-10 text-yellow-1"></i>

                      <i className="icon-star text-10 text-yellow-1"></i>

                      <i className="icon-star text-10 text-yellow-1"></i>

                      <i className="icon-star text-10 text-yellow-1"></i>
                    </div>

                    <div className="text-14 text-light-1 ml-10">
                      {tour?.numberOfReviews} reviews
                    </div>
                  </div>
                </div> */}

                <div className="col-auto">
                  <div className="row x-gap-10 items-center">
                    <div className="col-auto">
                      <div className="d-flex x-gap-5 items-center">
                        <i className="icon-placeholder text-16 text-light-1"></i>
                        <div className="text-15 text-light-1">
                          {restaurantsDetails?.city},{" "}
                          {restaurantsDetails?.country}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            {/* End .col */}
          </div>
        </div>
      </section>

      <TourGallery tour={tour} />

      {/* End single page content */}

      {/* <DefaultFooter /> */}
    </>
  );
};

export default RestaurantsDetails;
