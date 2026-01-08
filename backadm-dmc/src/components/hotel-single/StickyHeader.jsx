import { Link,useNavigate } from "react-router-dom";
import { useCallback, useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { Button, Switch, Typography } from "@mui/material";
import { Box } from "@mui/material";

const StickyHeader = ({ hotel }) => {

const currencySymbol = useSelector((state) => state.auth.currencySymbol);
const currencyCode = useSelector((state) => state.auth.currencyCode);
const exchangeRate = useSelector((state) => state.auth.exchangeRate);
const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);

// Calculate prices with exchange rates


  const [header, setHeader] = useState(false);
  const { priceMode } = location.state || {};

  

  const navigate =useNavigate()

  
  const dispatch = useDispatch();
  const totalPrice = useSelector((state) => state.hoteldetails.totalPrice);
  const bookingArray = useSelector((state) => state.hoteldetails.bookingArray);

  const convertedPrice = Math.ceil(totalPrice * exchangeRate);
  const usdPrice = Math.ceil(totalPrice * usdExchangeRate);
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  const changeBackground = () => {
    if (window.scrollY >= 200) {
      setHeader(true);
    } else {
      setHeader(false);
    }
  };

  useEffect(() => {
    window.addEventListener("scroll", changeBackground);
  }, []);

  const handleCheckoutPage = useCallback(() => {
    navigate("/dashboard/db-dashboard/hotel-checkout", { 
      state: { 
        bookingArray, 
        totalPrice,
        priceMode 
      } 
    });
  }, [bookingArray, totalPrice]);

  return (
    <div className={`singleMenu js-singleMenu ${header ? "-is-active" : ""}`}>
      <div className="singleMenu__content">
        <div className="container">
          <div className="row y-gap-20 justify-between items-center">
            <div className="col-auto">
              <div className="singleMenu__links row x-gap-30 y-gap-10">
                <div className="col-auto">
                  <a href="#overview">Overview</a>
                </div>
                <div className="col-auto">
                  <a href="#rooms">Rooms</a>
                </div>
                <div className="col-auto">
                  <a href="#reviews">Reviews</a>
                </div>
                <div className="col-auto">
                  <a href="#facilities">Facilities</a>
                </div>
                <div className="col-auto">
                  <a href="#faq">Faq</a>
                </div>
              </div>
            </div>
            {/* End .col */}

            {totalPrice > 0 &&(
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
                          SGD {totalPrice}
                        </Typography>
                        <Typography variant="body1" color="text.secondary">
                          {usdCurrencyCode} {usdPrice}
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
                    // to="/dashboard/hotel-checkout"
                     className="button h-50 px-24 -dark-1 bg-blue-1 text-white"
                     onClick={handleCheckoutPage}
                   >
                    Checkout <div className="icon-arrow-top-right ml-15" />
                   </Button>
                 </div>
               </div>
             </div>
            )}

           
            {/* End .col */}
          </div>
          {/* End .row */}
        </div>
        {/* End .container */}
      </div>
      {/* End .singleMenu__content */}
    </div>
  );
};

export default StickyHeader;
