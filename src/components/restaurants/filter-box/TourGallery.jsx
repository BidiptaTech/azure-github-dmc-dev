import React, { useState} from "react";
// import { useParams } from "react-router-dom";
import { useSelector } from "react-redux";
// import { fetchRestaurantsDetails } from "@/slice/restaurant/RestaurantsSlice";
import SidebarRight from "@/components/restaurants/SidebarRight";
import Overview from "@/components/restaurants/filter-box/Overview";
import { Gallery, Item } from "react-photoswipe-gallery";
import { Swiper, SwiperSlide } from "swiper/react";
import { Navigation } from "swiper";
import ModalVideo from "react-modal-video";

export default function TourGallery({ tour }) {
  // const dispatch = useDispatch();
  // const { id } = useParams();
  // const location = useLocation();

  const restaurantsDetails = useSelector((state) => state.restaurants.selectedRestaurant);
  // const loading = useSelector((state) => state.restaurants.loading);
  // const error = useSelector((state) => state.restaurants.error);

  // useEffect(() => {
  //   if (id) {
  //     dispatch(fetchRestaurantsDetails({ restaurantId: id }));
  //   }
  // }, [dispatch, id]);

  // console.log("Fetched restaurants Details:", restaurantsDetails);

  // Extract images from fetched restaurant details
  const master_image = restaurantsDetails?.master_image || "";
  const additional_images = restaurantsDetails?.additional_images || [];

  // Combine images, ensuring master_image is included
  const images = [...additional_images];
  if (master_image && !images.includes(master_image)) {
    images.unshift(master_image);
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
                        alt={`restaurants_Image ${i + 1}`}
                        style={{ height: "501px" }}
                        className="rounded-4 col-12 cover object-cover"
                      />
                    </SwiperSlide>
                  ))}
                </Swiper>

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
