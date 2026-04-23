import React, { useState, useEffect } from "react";
import "photoswipe/dist/photoswipe.css";
import activityData from "@/data/activity";
import Header11 from "@/components/header/header-11";
import Overview from "@/components/activity-single/Overview";
import TourSnapShot from "@/components/activity-single/TourSnapShot";
import TopBreadCrumb from "@/components/activity-single/TopBreadCrumb";
import SidebarRight from "@/components/activity-single/SidebarRight";
import ReviewProgress2 from "@/components/activity-single/guest-reviews/ReviewProgress2";
import DetailsReview2 from "@/components/activity-single/guest-reviews/DetailsReview2";
import ReplyForm from "@/components/activity-single/ReplyForm";
import ReplyFormReview2 from "@/components/activity-single/ReplyFormReview2";
import CallToActions from "@/components/common/CallToActions";
import DefaultFooter from "@/components/footer/default";
import Tours from "@/components/tours/Tours";
import { Link, useParams } from "react-router-dom";
import ImportantInfo from "@/components/activity-single/ImportantInfo";
//import SlideGallery from "@/components/activity-single/SlideGallery";
import { Skeleton, CardMedia, Card, Typography } from "@mui/material";
import MapPropertyFinder from "@/components/activity-single/MapPropertyFinder";
import { useLocation } from "react-router-dom";
import { useNavigate } from "react-router-dom";

import MetaComponent from "@/components/common/MetaComponent";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";

const metadata = {
  title: "Tour Guide || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

const ActivitySingleV1Dynamic = () => {
  let params = useParams();
  const id = params.id;
  const activity =
    activityData.find((item) => item.id == id) || activityData[0];

  const location = useLocation();
  const { guide } = location.state;
  const [isLoading, setIsLoading] = useState(true); // For skeleton loader
  const navigate = useNavigate();
  useEffect(() => {
    // Simulate a loading delay
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 2000); // Adjust timeout as needed
    return () => clearTimeout(timer);
  }, []);

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

      {/* <TopBreadCrumb /> */}
      {/* End top breadcrumb */}

      <section className="pt-20">
        <button
          className="button px-15 py-8 bg-blue-1 text-white rounded position-relative"
          style={{
            left: "80px",
            // zIndex: "10",
            minHeight: "40px",
            marginBottom: "20px",
          }}
          onClick={() => navigate("/dashboard/db-dashboard/tourguide")}
        >
          ← Back To Listing
        </button>
        <div className="container">
          <div className="row">
            <div className="col-xl-8 p-0 d-flex">
              {/* <SlideGallery /> */}
              <Card sx={{ boxShadow: 3, borderRadius: 2, flex: 1 }}>
                {isLoading ? (
                  <Skeleton
                    variant="rectangular"
                    height={510}
                    sx={{ borderRadius: 2, objectFit: "cover" }}
                  />
                ) : (
                  <CardMedia
                    component="img"
                    image={guide.guide.image}
                    alt={guide.guide.name}
                    sx={{
                      height: 510,
                      width: "100%",
                      objectFit: "cover",
                      borderRadius: 2,
                      cursor: "pointer",
                    }}
                  />
                )}
              </Card>
            </div>
            <div className="col-xl-4 d-flex">
              <SidebarRight activity={activity} style={{ flex: 1 }} />
            </div>
          </div>
        </div>
      </section>
      {/* End gallery grid wrapper */}

      <section className="pt-40 js-pin-container position-relative">
        <div className="container position-relative">
          <div className="row y-gap-30 mt-40">
            <div className="col-xl-12">
              <div className="row y-gap-20 justify-between items-end">
                <div className="col-auto">
                  <h1 className="text-26 fw-600">{guide?.guide.name}</h1>
                  <div className="row x-gap-10 y-gap-20 items-center pt-10">
                    <div className="col-auto">
                      <div className="d-flex items-center">
                        <i className="icon-star text-10 text-yellow-1"></i>

                        <div className="text-14 text-light-1 ml-10">
                          <span className="text-15 fw-500 text-dark-1">
                            {guide?.guide.ratings}
                          </span>
                          {guide?.guide.numberOffReviews} reviews
                        </div>
                      </div>
                    </div>

                    <div className="col-auto">
                      <div className="row x-gap-10 items-center">
                        <div className="col-auto">
                          <div className="d-flex x-gap-5 items-center">
                            <i className="icon-location-2 text-16 text-light-1"></i>
                            <div className="text-15 text-light-1">
                              {guide?.guide.location}
                            </div>
                          </div>
                        </div>

                        {/* <div className="col-auto">
                          <button className="text-blue-1 text-15 underline">
                            Show on map
                          </button>
                        </div> */}
                      </div>
                    </div>
                  </div>
                </div>

                <div className="col-auto">
                  <div className="row x-gap-10 y-gap-10">
                    {/* <div className="col-auto">
                      <button className="button px-15 py-10 -blue-1">
                        <i className="icon-share mr-10"></i>
                        Share
                      </button>
                    </div> */}

                    {/* <div className="col-auto">
                      <button className="button px-15 py-10 -blue-1 bg-light-2">
                        <i className="icon-heart mr-10"></i>
                        Save
                      </button>
                    </div> */}
                  </div>
                </div>
              </div>

              <div className="border-top-light mt-40 mb-40"></div>

              <Overview />
            </div>
          </div>
        </div>
      </section>
      {/* End single page content */}

      <section className="pt-40">
        <div className="container">
          {/* <div className="pt-40 border-top-light">
            <div className="row x-gap-40 y-gap-40">
              <div className="col-auto">
                <h3 className="text-22 fw-500">Important information</h3>
              </div>
            </div> */}
          {/* End row */}
          {/* <ImportantInfo />
          </div> */}
          {/* End pt-40 */}
        </div>
        {/* End .container */}
      </section>
      {/* End important info */}

    </>
  );
};

export default ActivitySingleV1Dynamic;
