import React, { useState } from "react";
import SidebarRight from "@/components/tour-single/SidebarRight";
import Overview from "@/components/tour-single/Overview";
import { Gallery, Item } from "react-photoswipe-gallery";
import { Swiper, SwiperSlide } from "swiper/react";
import { Navigation } from "swiper";
import ModalVideo from "react-modal-video";
import {  useSelector } from "react-redux";
// import { useParams } from "react-router-dom";
// import { fetchAttractionDetails } from "@/slice/attractions/attractionSlice";

export default function TourGallery({ tour }) {
  // const dispatch = useDispatch();
  // const { id } = useParams();

  // Fetch attraction details from Redux
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
  //  console.log('attractionDetails backend',attractionDetails);
  //  console.log('attractionDetails backend',attractionDetails.additional_images);
   
  
  const loading = useSelector((state) => state.attractions.loading);
  const error = useSelector((state) => state.attractions.error);

  // useEffect(() => {
  //   if (id) {
  //     dispatch(fetchAttractionDetails({ attractionId: id }));
  //   }
  // }, [dispatch, id]);

  // console.log("Fetched Attraction Details123:", attractionDetails);

  // Extract images safely from attractionDetails
  const { master_image, additional_images = [] } = attractionDetails || {};

  // Combine additional_images and master_image into one array
  const images = [...additional_images];
  if (master_image && !images.includes(master_image)) {
    images.unshift(master_image); // Ensure master_image is the first image
  }

  const [isOpen, setOpen] = useState(false);

  return (
    <>
      <ModalVideo
        channel="youtube"
        autoplay
        isOpen={isOpen}
        videoId="oqNZOOWF8qM"
        onClose={() => setOpen(false)}
      />

      <section className="pt-40 js-pin-container">
        <div className="container">
          <div className="row y-gap-30">
            <div className="col-xl-8">
              <div className="relative d-flex justify-center overflow-hidden js-section-slider">
                {loading ? (
                  <p>Loading images...</p>
                ) : error ? (
                  <p>Error loading images</p>
                ) : (
                  <Swiper
                    modules={[Navigation]}
                    loop={true}
                    navigation={{
                      nextEl: ".js-img-next",
                      prevEl: ".js-img-prev",
                    }}
                  >
                    {images.map((slide, i) => (
                      <SwiperSlide key={i}>
                        <img
                          src={slide}
                          alt={`Attraction Image ${i + 1}`}
                          style={{ height: "501px" }}
                          className="rounded-4 col-12 cover object-cover"
                        />
                      </SwiperSlide>
                    ))}
                  </Swiper>
                )}

                {/* Gallery for viewing larger images */}
                <Gallery>
                  {images.map((slide, i) => (
                    <div
                      className="absolute px-10 py-10 col-12 h-full d-flex justify-end items-end z-2 bottom-0 end-0"
                      key={i}
                    >
                      <Item original={slide} thumbnail={slide} width={451} height={450}>
                        {({ ref, open }) => (
                          <div
                            className="button -blue-1 px-24 py-15 bg-white text-dark-1 js-gallery"
                            ref={ref}
                            onClick={open}
                            role="button"
                          >
                            See All Photos
                          </div>
                        )}
                      </Item>
                    </div>
                  ))}
                </Gallery>

                <div className="absolute h-full col-11">
                  <button className="section-slider-nav -prev flex-center button -blue-1 bg-white shadow-1 size-40 rounded-full sm:d-none js-img-prev">
                    <i className="icon icon-chevron-left text-12" />
                  </button>
                  <button className="section-slider-nav -next flex-center button -blue-1 bg-white shadow-1 size-40 rounded-full sm:d-none js-img-next">
                    <i className="icon icon-chevron-right text-12" />
                  </button>
                </div>
              </div>

              <div className="border-top-light mt-40 mb-40"></div>
              <Overview />
            </div>

            <div className="col-xl-4">
              <SidebarRight tour={tour} />
            </div>
          </div>
        </div>
      </section>
    </>
  );
}



// import React, { useState, useEffect } from "react";
// import SidebarRight from "@/components/tour-single/SidebarRight";
// import Overview from "@/components/tour-single/Overview";
// import { Gallery, Item } from "react-photoswipe-gallery";
// import { Swiper, SwiperSlide } from "swiper/react";
// import { Navigation } from "swiper";
// import ModalVideo from "react-modal-video";
// import { useDispatch, useSelector } from "react-redux";
// import { useParams } from "react-router-dom";
// import { fetchAttractionDetails } from "@/slice/attractions/attractionSlice";

// export default function TourGallery({ tour }) {
//   const dispatch = useDispatch();
//   const { id } = useParams();

//   const attractionDetails = useSelector((state) => state.attractions.attractionDetails);

//   const [images, setImages] = useState([]);
//   const [isOpen, setOpen] = useState(false);

//   // Fetch attraction details on mount
//   // useEffect(() => {
//   //   if (id) {
//   //     dispatch(fetchAttractionDetails({ attractionId: id }));
//   //   }
//   // }, [dispatch, id]);

//   // Update images when attractionDetails change
//   useEffect(() => {
//     if (attractionDetails) {
//       const { master_image, additional_images = [] } = attractionDetails;

//       const combinedImages = [
//         ...(master_image ? [master_image] : []),
//         ...additional_images.filter((img) => img && typeof img === "string"),
//       ];

//       setImages(combinedImages);
//     }
//   }, [attractionDetails]);

//   return (
//     <>
//       <ModalVideo
//         channel="youtube"
//         autoplay
//         isOpen={isOpen}
//         videoId="oqNZOOWF8qM"
//         onClose={() => setOpen(false)}
//       />

//       <section className="pt-40 js-pin-container">
//         <div className="container">
//           <div className="row y-gap-30">
//             <div className="col-xl-8">
//               <div className="relative d-flex justify-center overflow-hidden js-section-slider">
//                 {images.length > 0 && (
//                   <>
//                     <Swiper
//                       modules={[Navigation]}
//                       loop={true}
//                       navigation={{
//                         nextEl: ".js-img-next",
//                         prevEl: ".js-img-prev",
//                       }}
//                     >
//                       {images.map((slide, i) => (
//                         <SwiperSlide key={i}>
//                           <img
//                             src={slide}
//                             alt={`Attraction Image ${i + 1}`}
//                             style={{ height: "501px" }}
//                             className="rounded-4 col-12 cover object-cover"
//                           />
//                         </SwiperSlide>
//                       ))}
//                     </Swiper>

//                     <Gallery>
//                       <div className="absolute px-10 py-10 col-12 h-full d-flex justify-end items-end z-2 bottom-0 end-0">
//                         <Item original={images[0]} thumbnail={images[0]} width={451} height={450}>
//                           {({ ref, open }) => (
//                             <div
//                               className="button -blue-1 px-24 py-15 bg-white text-dark-1 js-gallery"
//                               ref={ref}
//                               onClick={open}
//                               role="button"
//                             >
//                               See All Photos
//                             </div>
//                           )}
//                         </Item>
//                       </div>
//                     </Gallery>
//                   </>
//                 )}

//                 <div className="absolute h-full col-11">
//                   <button className="section-slider-nav -prev flex-center button -blue-1 bg-white shadow-1 size-40 rounded-full sm:d-none js-img-prev">
//                     <i className="icon icon-chevron-left text-12" />
//                   </button>
//                   <button className="section-slider-nav -next flex-center button -blue-1 bg-white shadow-1 size-40 rounded-full sm:d-none js-img-next">
//                     <i className="icon icon-chevron-right text-12" />
//                   </button>
//                 </div>
//               </div>

//               <div className="border-top-light mt-40 mb-40"></div>
//               <Overview />
//             </div>

//             <div className="col-xl-4">
//               <SidebarRight tour={tour} />
//             </div>
//           </div>
//         </div>
//       </section>
//     </>
//   );
// }
