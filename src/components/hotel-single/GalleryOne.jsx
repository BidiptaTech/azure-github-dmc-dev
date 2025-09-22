import ModalVideo from "react-modal-video";
import { Gallery, Item } from "react-photoswipe-gallery";
import { Link, useNavigate, useLocation } from "react-router-dom";
import React, { useCallback, useState } from "react";
import { useSelector } from "react-redux";
import { Button, Typography, Box } from "@mui/material";
import travClikImage from "../../../public/Images/hotel/travclick.jpg";
// import travClikImage from "../../../public/Images/travclicklogo.jpeg";

                


export default function GalleryOne({ hotel }) {
  const [isOpen, setOpen] = useState(false);
  const totalPrice = useSelector((state) => state.hoteldetails.totalPrice);
  const bookingArray = useSelector((state) => state.hoteldetails.bookingArray);

  const bookingDetails = useSelector((state) => state.hoteldetails.bookingDetails);
  console.log("GalleryOne - Full booking details:", bookingDetails);

  const { hotel_name, hotel_id, location,address, image, cancellation_charge, site_image } = useSelector(
    (state) => state.hoteldetails.bookingDetails || {}
  );
  const locationData = useLocation();
  const priceMode = locationData.state?.priceMode;

  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);

  const convertedPrice = Math.ceil(totalPrice * exchangeRate);
  const usdPrice = Math.ceil(totalPrice * usdExchangeRate);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  const navigate = useNavigate();
  

  // Function to ensure we have exactly 5 images (1 main + 4 smaller)
  const getDisplayImages = () => {
    const masterImage = image || travClikImage;
    const siteImages = site_image || [];
    
    // Combine master image with site images
    let allImages = [masterImage, ...siteImages];
    
    // If we have less than 5 images, fill with default image
    while (allImages.length < 5) {
      allImages.push(travClikImage);
    }
    
    // If we have more than 5 images, take only first 5
    return allImages.slice(0, 5);
  };

  const displayImages = getDisplayImages();

  const handleCheckoutPage = useCallback(() => {
    navigate("/dashboard/db-dashboard/hotel-checkout", { 
      state: { 
        bookingArray, 
        totalPrice,
        priceMode 
      } 
    });
  }, [bookingArray, totalPrice, priceMode, navigate]);

  return (
    <>
      <ModalVideo
        channel="youtube"
        autoplay
        isOpen={isOpen}
        videoId="CKJA9blyMUg"
        onClose={() => setOpen(false)}
      />
    
      <section className="pt-40">
      
        <div className="container">
        <button
          className="button px-15 py-8 bg-blue-1 text-white rounded absolute"
          style={{
            left: "80px",
            // zIndex: "10",
            minHeight: "40px",
            marginBottom: "40px",
            marginTop: "-30px",
            //position: "absolute",
          }}
          onClick={() => navigate("/dashboard/db-dashboard/view-hotel-search/:id")}
        >
          ← Back To Listing
        </button>
          <div className="row y-gap-20 justify-between items-end">
            <div className="col-auto">
              <div className="row x-gap-20 items-center">
                <div className="col-auto">
                  <h1 className="text-30 sm:text-25 fw-600">{hotel_name}</h1>
                </div>
                {/* <div className="col-auto">
                  <i className="icon-star text-10 text-yellow-1" />
                  <i className="icon-star text-10 text-yellow-1" />
                  <i className="icon-star text-10 text-yellow-1" />
                  <i className="icon-star text-10 text-yellow-1" />
                  <i className="icon-star text-10 text-yellow-1" />
                </div> */}
              </div>

              <div className="row x-gap-20 y-gap-20 items-center">
                <div className="col-auto">
                  <div className="d-flex items-center text-15 text-light-1">
                    <i className="icon-location-2 text-16 mr-5" />
                    {address}
                  </div>
                </div>
              </div>
            </div>

            {totalPrice > 0 && (
              <div className="col-auto">
                <div className="row x-gap-15 y-gap-15 items-center">
                  {PriceHide == "0" ?
                  (
                  <div className="col-auto">    
                    <div className="text-14">
                      From{" "}
                      <div className="text-22 text-dark-1 fw-500">
                        <Box sx={{ textAlign: 'right' }}>
                          <Typography variant="h6" color="primary">
                            {currencyCode} {convertedPrice}
                          </Typography>
                          <Typography variant="body1" color="text.secondary">
                            {usdCurrencyCode} {usdPrice}
                          </Typography>
                          <Typography variant="body1" color="text.secondary">
                            SGD {totalPrice}
                          </Typography>
                        </Box>
                      </div>
                    </div>
                  </div>
                  ):(
                    <div className="col-auto">
                      <div className="text-14">
                        Price available on request
                      </div>
                    </div>
                  )
                  }
                  <div className="col-auto">
                    <Button
                      onClick={handleCheckoutPage}
                      className="button h-50 px-24 -dark-1 bg-blue-1 text-white"
                    >
                      Checkout <div className="icon-arrow-top-right ml-15" />
                    </Button>
                  </div>
                </div>
              </div>
            )}
          </div>

          <Gallery>
            <div className="pt-20">
              <div className="row g-0">
                {/* Main image - Left side */}
                <div className="col-lg-7 col-md-6 pe-2">
                  <Item
                    original={displayImages[0]}
                    thumbnail={displayImages[0]}
                    width={1200}
                    height={800}
                  >
                    {({ ref, open }) => (
                      <div className="mainImage rounded-4 overflow-hidden">
                        <img
                          src={displayImages[0]}
                          ref={ref}
                          onClick={open}
                          alt="Main hotel image"
                          role="button"
                          className="w-100"
                          style={{ 
                            height: '440px', 
                            width: '100%',
                            objectFit: 'cover',
                            cursor: 'pointer'
                          }}
                        />
                      </div>
                    )}
                  </Item>
                </div>
                
                {/* Small images - Right side */}
                <div className="col-lg-5 col-md-6 ps-0">
                  <div className="row g-0">
                    {displayImages.slice(1).map((image, index) => (
                      <div className="col-6 p-1" key={index}>
                        <Item 
                          original={image} 
                          thumbnail={image} 
                          width={800} 
                          height={500}
                        >
                          {({ ref, open }) => (
                            <div style={{padding: '0 0 0 2px', marginBottom: index < 2 ? '2px' : 0}}>
                              <img
                                ref={ref}
                                onClick={open}
                                src={image}
                                alt={`Hotel image ${index + 2}`}
                                role="button"
                                style={{ 
                                  height: '212px', 
                                  width: '100%',
                                  objectFit: 'cover',
                                  cursor: 'pointer',
                                  borderRadius: '4px'
                                }}
                              />
                            </div>
                          )}
                        </Item>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </Gallery>
        </div>
      </section>
    </>
  );
}
