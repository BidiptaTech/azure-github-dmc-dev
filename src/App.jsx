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
import { useDispatch } from "react-redux";

if (typeof window !== "undefined") {
  import("bootstrap");
}
import { BrowserRouter, Routes, Route, Navigate, useParams } from "react-router-dom";
import ScrollTopBehaviour from "./components/common/ScrollTopBehaviour";
import LandingPage from "./pages/landing";
import Home_1 from "./pages/homes/home_1";
import Home_2 from "./pages/homes/home_2";
import HotelListPage1 from "./pages/hotel/hotel-list-v1";
import HotelSingleV1Dynamic from "./pages/hotel/hotel-single-v1";
import BookingPage from "./pages/hotel/booking-page";
import BookingPage1 from "./pages/restaurants/booking-page/index";
import BookingPage2 from "./pages/attractions/index";

import TourListPage1 from "./pages/tour/tour-list-v1";
import TourListPage2 from "./pages/tour/tour-list-v2";
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

import { logoutUser } from "./slice/common/authSlices";
import BookingPage5 from "./pages/activity/booking-page";
import OrderSubmittedInfo5 from "./components/activity-single/booking-page/OrderSubmittedInfo5";
import Updatebooking from "./components/dashboard/dashboard/db-dashboard/components/Updatebooking";
import PackageDetailsEmbed from "./pages/workspace/PackageDetailsEmbed";

import DemoPage from "./pages/demo";
import CartPage from "./pages/cart/cartPage";
import CartCheckout from "./pages/cart/cartCheckout";

import PartnerLayout from "./layouts/PartnerLayout";
import Overview from "./pages/workspace/Overview";
import PlanTrip from "./pages/workspace/PlanTrip";
import Bookings from "./pages/workspace/Bookings";
import Quotes from "./pages/workspace/Quotes";
import Wallet from "./pages/workspace/Wallet";
import PackagesPage from "./pages/workspace/PackagesPage";
import TourPackagesEmbed from "./pages/workspace/TourPackagesEmbed";
import PreDefinePackagesEmbed from "./pages/workspace/PreDefinePackagesEmbed";
import BookingEnquiryEmbed from "./pages/workspace/BookingEnquiryEmbed";
import LegacyDashboardRedirect from "./pages/workspace/LegacyDashboardRedirect";

const AUTO_LOGOUT_TIME = 7 * 24 * 60 * 60 * 1000;

function PackageDetailsRedirect() {
  const { id } = useParams();
  return <Navigate to={`/app/packages/package-details/${id}`} replace />;
}

function App() {
  const dispatch = useDispatch();

  useEffect(() => {
    const timer = setTimeout(() => {
      dispatch(logoutUser());
    }, AUTO_LOGOUT_TIME);

    return () => clearTimeout(timer);
  }, [dispatch]);

  const handleLogin = () => {
    dispatch(loginUser());
  };

  useEffect(() => {
    Aos.init({
      duration: 1200,
      once: true,
    });
  }, []);

  const withTour = (element) => (
    <ProtectedRoute>
      <ProtectedRoutetour>{element}</ProtectedRoutetour>
    </ProtectedRoute>
  );

  const withAuth = (element) => <ProtectedRoute>{element}</ProtectedRoute>;

  return (
    <main>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<Login onLogin={handleLogin} />} />
          <Route path="/register" element={<Register />} />
          <Route path="/verify-otp" element={<OTPVerification />} />
          <Route path="/" element={<LandingPage />} />
          <Route path="/demo" element={<DemoPage />} />

          {/* Legacy redirects into partner workspace */}
          <Route
            path="/package-details/:id"
            element={<PackageDetailsRedirect />}
          />
          <Route
            path="/booking-enquiry"
            element={<Navigate to="/app/quotes/booking-enquiry" replace />}
          />
          <Route
            path="/app/booking-enquiry"
            element={<Navigate to="/app/quotes/booking-enquiry" replace />}
          />
          <Route
            path="/dashboard/db-dashboard"
            element={<Navigate to="/app" replace />}
          />
          <Route
            path="/dashboard/db-dashboard/*"
            element={<LegacyDashboardRedirect />}
          />
          <Route
            path="/dashboard/tour-packages"
            element={
              <Navigate to="/app/packages/tour-packages-page" replace />
            }
          />
          <Route
            path="/dashboard/pre-define-packages"
            element={
              <Navigate to="/app/packages/pre-define-packages" replace />
            }
          />
          <Route
            path="/app/tour-packages-page"
            element={
              <Navigate to="/app/packages/tour-packages-page" replace />
            }
          />
          <Route
            path="/app/pre-define-packages"
            element={
              <Navigate to="/app/packages/pre-define-packages" replace />
            }
          />
          <Route
            path="/app/package-details/:id"
            element={<PackageDetailsRedirect />}
          />

          <Route
            path="/app"
            element={withAuth(<PartnerLayout />)}
          >
            <Route index element={<Overview />} />
            <Route path="plan" element={<PlanTrip />} />
            <Route path="bookings" element={<Bookings />} />
            <Route path="quotes" element={<Quotes />} />
            <Route
              path="quotes/booking-enquiry"
              element={<BookingEnquiryEmbed />}
            />
            <Route path="wallet" element={<Wallet />} />
            <Route path="packages" element={<PackagesPage />} />
            <Route
              path="packages/pre-define-packages"
              element={<PreDefinePackagesEmbed />}
            />
            <Route
              path="packages/tour-packages-page"
              element={<TourPackagesEmbed />}
            />
            <Route
              path="packages/package-details/:id"
              element={<PackageDetailsEmbed />}
            />

            <Route
              path="tour-packages"
              element={<Navigate to="/app/packages" replace />}
            />
            <Route path="home_1" element={withAuth(<Home_1 />)} />
            <Route path="home_2" element={withAuth(<Home_2 />)} />
            <Route path="updatebooking" element={withAuth(<Updatebooking />)} />

            <Route
              path="view-hotel-search/:id"
              element={withTour(<HotelListPage1 />)}
            />
            <Route
              path="hotel-details"
              element={withTour(<HotelSingleV1Dynamic />)}
            />
            <Route path="hotel-checkout" element={withTour(<BookingPage />)} />
            <Route path="thank-you" element={withTour(<OrderSubmittedInfo />)} />

            <Route path="attractions" element={withTour(<TourListPage1 />)} />
            <Route path="restaurants" element={withTour(<TourListPage2 />)} />
            <Route
              path="tour-single/:id"
              element={withTour(<TourSingleV1Dynamic />)}
            />
            <Route
              path="restaurants-details/:id"
              element={withTour(<RestaurantsDetails />)}
            />
            <Route
              path="restaurants-checkout"
              element={withTour(<BookingPage1 />)}
            />
            <Route
              path="attraction-checkout"
              element={withTour(<BookingPage2 />)}
            />
            <Route
              path="restaurants-thank-you"
              element={withTour(<OrderSubmittedInfo1 />)}
            />
            <Route
              path="attraction-thank-you"
              element={withTour(<OrderSubmittedInfo2 />)}
            />
            <Route path="tourguide" element={withTour(<ActivityListPage1 />)} />
            <Route path="pickupdrop" element={withTour(<ActivityListPage2 />)} />
            <Route
              path="localtransfer"
              element={withTour(<ActivityListPage3 />)}
            />
            <Route
              path="activity-single"
              element={withTour(<ActivitySingleV1Dynamic />)}
            />
            <Route
              path="activity-single-1"
              element={withTour(<ActivitySingleV2Dynamic />)}
            />
            <Route
              path="activity-single-2"
              element={withTour(<ActivitySingleV3Dynamic />)}
            />
            <Route path="CheckOut" element={withTour(<BookingPage5 />)} />
            <Route path="cart" element={withAuth(<CartPage />)} />
            <Route path="cart-checkout" element={withTour(<CartCheckout />)} />
            <Route path="ThankYou" element={withTour(<OrderSubmittedInfo5 />)} />
          </Route>
        </Routes>
        <ScrollTopBehaviour />
      </BrowserRouter>

      <SrollTop />
    </main>
  );
}

export default App;
