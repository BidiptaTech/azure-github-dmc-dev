import Aos from "aos";
import { useEffect } from "react";
import SrollTop from "./components/common/ScrollTop";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/navigation";
import "swiper/css/scrollbar";
import "swiper/css/effect-cards";
import "aos/dist/aos.css";
import "./styles/index.scss";
import { Provider, useDispatch } from "react-redux";
import { store } from "./store/store";
//import { useDispatch } from "react-redux";

if (typeof window !== "undefined") {
  import("bootstrap");
}
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import ScrollTopBehaviour from "./components/common/ScrollTopBehaviour";
import Home from "./pages";
import LandingPage from "./pages/landing";
import Home_1 from "./pages/homes/home_1";
import Home_2 from "./pages/homes/home_2";
import NotFoundPage from "./pages/not-found";
import DBDashboard from "./pages/dashboard/dashboard/db-dashboard";
import DBBooking from "./pages/dashboard/dashboard/db-booking";
import DBWishlist from "./pages/dashboard/dashboard/db-wishlist";
import DBSettings from "./pages/dashboard/dashboard/db-settings";


import HotelListPage1 from "./pages/hotel/hotel-list-v1";
import HotelSingleV1Dynamic from "./pages/hotel/hotel-single-v1";
import BookingPage from "./pages/hotel/booking-page";
import BookingPage1 from "./pages/restaurants/booking-page/index";
import BookingPage2 from "./pages/attractions/index";

import TourListPage1 from "./pages/tour/tour-list-v1";
import TourListPage2 from "./pages/tour/tour-list-v2";
// import TourListPage3 from "./pages/tour/tour-list-v3";
import TourSingleV1Dynamic from "./pages/tour/tour-single";
import ActivityListPage1 from "./pages/activity/activity-list-v1";
import ActivityListPage2 from "./pages/activity/activity-list-v2";
import ActivityListPage3 from "./pages/activity/activity-list-v3";
import ActivitySingleV1Dynamic from "./pages/activity/activity-single";
import Login from "./pages/login/Login";
import Register from "./pages/Register";
import OTPVerification from "./pages/Register/verify";
import loginUser from "./slice/common/authSlices";

import ProtectedRoute from "./ProtectedRoutes/ProtectedRoute";
import RestaurantsDetails from "./pages/tour/tour-list-v2/details";
import OrderSubmittedInfo from "./components/booking-page/OrderSubmittedInfo";
import OrderSubmittedInfo1 from "./components/restaurants/booking-page/OrderSubmittedInfo";
import OrderSubmittedInfo2 from "./components/tour-list/booking-page/OrderSubmittedInfo";
import ActivitySingleV2Dynamic from "./pages/activity/activity-single-1";
import ActivitySingleV3Dynamic from "./pages/activity/activity-single-2";
import ProtectedRoutetour from "./ProtectedRoutes/ProtectedRoutetour";


// import { useEffect } from "react";
// import { useDispatch } from "react-redux";
import { logoutUser } from "./slice/common/authSlices";
import BookingPage5 from "./pages/activity/booking-page";
import OrderSubmittedInfo5 from "./components/activity-single/booking-page/OrderSubmittedInfo5";
import Updatebooking from "./components/dashboard/dashboard/db-dashboard/components/Updatebooking";
import TourPackages from "./pages/tour-packages/index";
import PreDefinePackagesPage from "./pages/pre-define-packages/index";
import Packages from "./pages/packages";
import PackageDetails from "./pages/pre-define-packages/package-details";

import BookingEnquiryPage from "./pages/booking-enquiry";

const AUTO_LOGOUT_TIME = 7 * 24 * 60 * 60 * 1000; //Logout after 1 week

function App() {
  const dispatch = useDispatch();
  useEffect(() => {
    if (!sessionStorage.getItem("alreadyCleared")) {
      // Clear localStorage
      localStorage.clear();

      // Clear sessionStorage
      sessionStorage.clear();

      // Clear cookies
      document.cookie.split(";").forEach((c) => {
        document.cookie = c
          .replace(/^ +/, "")
          .replace(/=.*/, "=;expires=" + new Date(0).toUTCString() + ";path=/");
      });

      // Mark as cleared to avoid infinite reloads
      sessionStorage.setItem("alreadyCleared", "true");

      // Reload the page after clearing
      window.location.reload();
    }
  }, []);

  useEffect(() => {
    const timer = setTimeout(() => {
      dispatch(logoutUser());
    }, AUTO_LOGOUT_TIME);

    return () => clearTimeout(timer); // Clear timer on unmount
  }, [dispatch]);

  // Handle login
  const handleLogin = () => {
    dispatch(loginUser());
  };

  useEffect(() => {
    Aos.init({
      duration: 1200,
      once: true,
    });
  }, []);

  return (
    <main>
      {/* <Provider store={store}> */}
      <BrowserRouter>
        <Routes>
          {/* <Route path="/"> */}
          {/* <Route path="/" element={<Navigate to="/" replace />} /> */}
          <Route path="/login" element={<Login onLogin={handleLogin} />} />
          <Route path="/register" element={<Register />} />
          <Route path="/verify-otp" element={<OTPVerification />} />
          {/* <Route index element={<Home />} /> */}

          {/* <Route path="home_2" element={<Home_2 />} />
              <Route path="home_3" element={<Home_3 />} /> */}
          {/* <Route path="home_4" element={<Home_4 />} /> */}
          {/* <Route path="home_5" element={<Home_5 />} />
              <Route path="home_6" element={<Home_6 />} />
              <Route path="home_7" element={<Home_7 />} />
              <Route path="home_8" element={<Home_8 />} />
              <Route path="home_9" element={<Home_9 />} />
              <Route path="home_10" element={<Home_10 />} /> */}

          {/* <Route path="signup" element={<SignUp />} />
              <Route path="login" element={<LogIn />} /> */}
          {/* <Route path="terms" element={<Terms />} />
          <Route path="invoice" element={<Invoice />} />
          <Route path="contact" element={<Contact />} /> */}
          {/*  <Route path="destinations" element={<Destinations />} /> */}
          <Route
            path="/"
            element={<LandingPage />}
          />
          <Route path="/dashboard/tour-packages" element={
            <ProtectedRoute>
              <TourPackages />
            </ProtectedRoute>
          } />
          <Route path="/dashboard/pre-define-packages" element={
            <ProtectedRoute>
              <PreDefinePackagesPage />
            </ProtectedRoute>
          } />
          <Route path="/package-details/:id" element={
            <ProtectedRoute>
              <PackageDetails />
            </ProtectedRoute>
          } />
          {/* <Route path="/modal-test" element={
            <ModalTestRoute />
          } /> */}
          <Route path="/booking-enquiry" element={
            <ProtectedRoute>
              <BookingEnquiryPage />
            </ProtectedRoute>
          } />
          <Route path="dashboard">
            <Route
              path="db-dashboard/*"
              element={
                <ProtectedRoute>
                  <DBDashboard />
                </ProtectedRoute>
              }
            >
              <Route
                path="*"
                element={<Navigate to="/dashboard/db-dashboard" replace />}
              />
              <Route
                path="updatebooking"
                element={
                <ProtectedRoute>
                  <Updatebooking />
                </ProtectedRoute>
                }
              />

              <Route
                path="home_1"
                element={
                  <ProtectedRoute>
                    <Home_1 />
                  </ProtectedRoute>
                }
              />
              <Route
                path="home_2"
                element={
                  <ProtectedRoute>
                    <Home_2 />
                  </ProtectedRoute>
                }
              />
               <Route
                path="tour-packages"
                element={
                  <ProtectedRoute>
                    <Packages/>
                  </ProtectedRoute>
                }
              />
              <Route
                path="pre-define-packages"
                element={
                  <ProtectedRoute>
                    <PreDefinePackagesPage/>
                  </ProtectedRoute>
                }
              />
              <Route
                path="tour-packages-page"
                element={
                  <ProtectedRoute>
                    <TourPackages/>
                  </ProtectedRoute>
                }
              />
              {/* <Route
                path="tour-packages"
                element={
                  <ProtectedRoute>
                    <TourPackages/>
                  </ProtectedRoute>
                }
              /> */}
              {/* <Route
              path="db-booking"
              element={
                <ProtectedRoute>
                  <DBBooking />
                </ProtectedRoute>
              }
            />
            <Route
              path="db-wishlist"
              element={
                <ProtectedRoute>
                  <DBWishlist />
                </ProtectedRoute>
              }
            />
            <Route
              path="db-settings"
              element={
                <ProtectedRoute>
                  <DBSettings />
                </ProtectedRoute>
              }
            /> */}

              {/* <Route path="vendor-dashboard">
            <Route path="dashboard" element={<VendorDashboard />} />
            <Route path="add-hotel" element={<VendorAddHotel />} />
            <Route path="booking" element={<VendorBooking />} />
            <Route path="hotels" element={<BVVendorHotel />} />
            <Route path="recovery" element={<BDVendorRecovery />} />
          </Route> */}
              {/* //hotel list page */}
              <Route
                path="view-hotel-search/:id"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <HotelListPage1 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              {/* <Route path="hotel-list-v2" element={<HotelListPage2 />} />
              <Route path="hotel-list-v3" element={<HotelListPage3 />} />
              <Route path="hotel-list-v4" element={<HotelListPage4 />} />
              <Route path="hotel-list-v5" element={<HotelListPage5 />} /> */}

              {/* hotel details page */}
              <Route
                path="hotel-details"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <HotelSingleV1Dynamic />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              {/* <Route
                path="hotel-single-v2/:id"
                element={<HotelSingleV2Dynamic />}
              /> */}
              <Route
                path="hotel-checkout"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <BookingPage />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="thank-you"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <OrderSubmittedInfo />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />

              <Route
                path="attractions"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <TourListPage1 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="restaurants"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <TourListPage2 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              {/* <Route path="tour-list-v3" element={<TourListPage3 />} /> */}
              <Route
                path="tour-single/:id"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <TourSingleV1Dynamic />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="restaurants-details/:id"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <RestaurantsDetails />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="restaurants-checkout"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <BookingPage1 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="attraction-checkout"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <BookingPage2 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="restaurants-thank-you"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <OrderSubmittedInfo1 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="attraction-thank-you"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <OrderSubmittedInfo2 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="tourguide"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <ActivityListPage1 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="pickupdrop"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <ActivityListPage2 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="localtransfer"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <ActivityListPage3 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="activity-single"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <ActivitySingleV1Dynamic />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="activity-single-1"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <ActivitySingleV2Dynamic />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="activity-single-2"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <ActivitySingleV3Dynamic />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="CheckOut"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <BookingPage5 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
              <Route
                path="ThankYou"
                element={
                  <ProtectedRoute>
                    <ProtectedRoutetour>
                      <OrderSubmittedInfo5 />
                    </ProtectedRoutetour>
                  </ProtectedRoute>
                }
              />
            </Route>
          </Route>
        </Routes>
        <ScrollTopBehaviour />
      </BrowserRouter>

      <SrollTop />
      {/* </Provider> */}
    </main>
  );
}

export default App;
